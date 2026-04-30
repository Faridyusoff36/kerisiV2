<script setup lang="ts">
/**
 * Purchasing / Setup / List Of Jobscope (menu 1932).
 * Data from `jobscope` (mysql_secondary); modal field order matches legacy Jobscope Details.
 */
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from "vue";
import {
  FileDown,
  FileSpreadsheet,
  Filter,
  Pencil,
  Plus,
  Search,
  X,
} from "lucide-vue-next";
import AdminLayout from "@/layouts/AdminLayout.vue";
import {
  createPurchasingJobscope,
  getPurchasingJobscope,
  listPurchasingJobscope,
  purchasingJobscopeFormOptions,
  purchasingJobscopeParentOptions,
  updatePurchasingJobscope,
  type JobscopeDropdownOpt,
} from "@/api/cms";
import { getKerisiMenuTrailByMenuId } from "@/config/kerisi-menu-resolve";
import { useToast } from "@/composables/useToast";

const MENU_ID = 1932;
const toast = useToast();

const pageHeading = computed(() => {
  const trail = getKerisiMenuTrailByMenuId(MENU_ID);
  return trail?.length ? trail.join(" / ") : "Purchasing / Setup / List Of Jobscope";
});

function goPrev() {
  if (page.value <= 1) return;
  page.value -= 1;
  void loadRows();
}

function goNext() {
  if (page.value >= totalPages.value) return;
  page.value += 1;
  void loadRows();
}

const levels = ref<JobscopeDropdownOpt[]>([]);
const categories = ref<JobscopeDropdownOpt[]>([]);
const statuses = ref<JobscopeDropdownOpt[]>([]);

const rows = ref<Record<string, unknown>[]>([]);
const total = ref(0);
const page = ref(1);
const limit = ref(10);
const q = ref("");
const loading = ref(false);
let searchDebounce: ReturnType<typeof setTimeout> | null = null;

const showSmartFilter = ref(false);
const sfCode = ref("");
const sfLevel = ref("");
const sfCategory = ref("");
const sfStatus = ref("");

function buildListParams(): string {
  const p = new URLSearchParams({
    page: String(page.value),
    limit: String(limit.value),
  });
  const t = q.value.trim();
  if (t) p.set("q", t);
  if (sfCode.value.trim()) p.set("sf_0", sfCode.value.trim());
  if (sfLevel.value.trim()) p.set("sf_1", sfLevel.value.trim());
  if (sfCategory.value.trim()) p.set("sf_2", sfCategory.value.trim());
  if (sfStatus.value.trim()) p.set("sf_3", sfStatus.value.trim());

  return `?${p.toString()}`;
}

async function loadRows() {
  loading.value = true;
  try {
    const res = await listPurchasingJobscope(buildListParams());
    rows.value = Array.isArray(res.data) ? (res.data as Record<string, unknown>[]) : [];
    total.value = Number(res.meta?.total ?? 0);
  } catch (e) {
    toast.error("Load failed", e instanceof Error ? e.message : "Unable to load jobscope.");
    rows.value = [];
    total.value = 0;
  } finally {
    loading.value = false;
  }
}

function pick(row: Record<string, unknown>, ...keys: string[]): string {
  for (const k of keys) {
    const x = row[k];
    if (x !== undefined && x !== null && String(x) !== "") return String(x);
  }
  return "";
}

function cellCode(row: Record<string, unknown>) {
  return pick(row, "jbsJobscopeCode", "jbs_jobscope_code");
}
function cellName(row: Record<string, unknown>) {
  return pick(row, "jbsJobName", "jbs_job_name");
}
function cellLevel(row: Record<string, unknown>) {
  return pick(row, "jbsLevel", "jbs_level");
}
function cellCategory(row: Record<string, unknown>) {
  return pick(row, "jbcCategory", "jbc_category");
}
function cellParent(row: Record<string, unknown>) {
  return pick(row, "jbsJobCodeParent", "jbs_job_code_parent");
}
function cellStatus(row: Record<string, unknown>) {
  return pick(row, "jbsStatus", "jbs_status");
}
function rowId(row: Record<string, unknown>): number | null {
  const v = row.jbsId ?? row.jbs_id;
  if (typeof v === "number" && !Number.isNaN(v)) return v;
  if (typeof v === "string" && /^\d+$/.test(v)) return Number(v);
  return null;
}

const totalPages = computed(() => (total.value > 0 ? Math.ceil(total.value / Math.max(1, limit.value)) : 1));

function onSearchDebounced() {
  if (searchDebounce) clearTimeout(searchDebounce);
  searchDebounce = setTimeout(() => {
    page.value = 1;
    void loadRows();
  }, 350);
}

function flushSearchFromInput() {
  if (searchDebounce) clearTimeout(searchDebounce);
  page.value = 1;
  void loadRows();
}

function clearSearch() {
  q.value = "";
  flushSearchFromInput();
}

function resetSmartFilter() {
  sfCode.value = "";
  sfLevel.value = "";
  sfCategory.value = "";
  sfStatus.value = "";
}

function applySmartFilter() {
  showSmartFilter.value = false;
  page.value = 1;
  void loadRows();
}

async function loadFormOptions() {
  try {
    const res = await purchasingJobscopeFormOptions();
    const d = res.data;
    levels.value = Array.isArray(d.levels) ? d.levels : [];
    categories.value = Array.isArray(d.categories) ? d.categories : [];
    statuses.value = Array.isArray(d.statuses) ? d.statuses : [];
  } catch (e) {
    toast.error("Options", e instanceof Error ? e.message : "Unable to load dropdowns.");
    levels.value = [
      { value: "1", label: "1" },
      { value: "2", label: "2" },
      { value: "3", label: "3" },
    ];
    statuses.value = [
      { value: "1", label: "ACTIVE" },
      { value: "0", label: "INACTIVE" },
    ];
  }
}

const modalOpen = ref(false);
const modalMode = ref<"add" | "edit">("add");
const editingId = ref<number | null>(null);
const modalLevel = ref("");
const modalCategory = ref("");
const modalParent = ref("");
const modalCode = ref("");
const modalName = ref("");
/** Values `1` / `0` (legacy ACTIVE / INACTIVE) */
const modalStatus = ref("1");

const parentOpts = ref<JobscopeDropdownOpt[]>([]);
const loadingParents = ref(false);

watch([modalLevel, modalCategory], () => {
  void refreshParentDropdown();
});

async function refreshParentDropdown() {
  const lv = modalLevel.value.trim();
  const cat = modalCategory.value.trim();

  if (cat === "") {
    parentOpts.value = [];
    return;
  }

  if (lv !== "1" && lv !== "2" && lv !== "3") {
    parentOpts.value = [];
    return;
  }

  loadingParents.value = true;
  try {
    const qp = new URLSearchParams({ level: lv, category: cat });
    const res = await purchasingJobscopeParentOptions(`?${qp.toString()}`);
    parentOpts.value = Array.isArray(res.data) ? res.data : [];
    const vals = parentOpts.value.map((o) => o.value);
    if (lv === "1") {
      modalParent.value = "";
    } else if (modalParent.value !== "" && !vals.includes(modalParent.value)) {
      modalParent.value = "";
    }
  } catch (e) {
    toast.error("Parent list", e instanceof Error ? e.message : "Unable to load parent options.");
    parentOpts.value = [];
  } finally {
    loadingParents.value = false;
  }
}

function openAdd() {
  modalMode.value = "add";
  editingId.value = null;
  modalLevel.value = "";
  modalCategory.value = "";
  modalParent.value = "";
  modalCode.value = "";
  modalName.value = "";
  modalStatus.value = "1";
  parentOpts.value = [];
  modalOpen.value = true;
}

async function openEdit(row: Record<string, unknown>) {
  const id = rowId(row);
  if (id === null) {
    toast.error("Edit", "Missing row id.");
    return;
  }
  modalMode.value = "edit";
  editingId.value = id;
  try {
    const res = await getPurchasingJobscope(id);
    const d = res.data as Record<string, unknown>;
    modalLevel.value = String(d.level ?? "").trim();
    modalCategory.value = String(d.category ?? "").trim();
    modalParent.value = String(d.parent ?? "").trim();
    modalCode.value = String(d.code ?? "").trim();
    modalName.value = String(d.name ?? "").trim();
    modalStatus.value = String(d.status ?? "1").trim();
    modalOpen.value = true;
    await nextTick();
    await refreshParentDropdown();
    if ((modalParent.value === "" || modalParent.value === "-") && String(d.parent ?? "").trim()) {
      modalParent.value = String(d.parent ?? "").trim();
    }
  } catch (e) {
    toast.error("Edit", e instanceof Error ? e.message : "Unable to load record.");
  }
}

function closeModal() {
  modalOpen.value = false;
}

async function saveModal() {
  const lv = modalLevel.value.trim();
  const cat = modalCategory.value.trim();
  if (!lv || !cat || !modalCode.value.trim() || !modalName.value.trim()) {
    toast.error("Validation", "Level, Category, Code and Name are required.");
    return;
  }

  if ((lv === "2" || lv === "3") && !modalParent.value.trim()) {
    toast.error("Validation", "Parent is required for Level 2 and Level 3.");
    return;
  }
  const body = {
    level: lv,
    category: cat,
    parent: lv === "1" ? "" : modalParent.value.trim(),
    code: modalCode.value.trim().toUpperCase(),
    name: modalName.value.trim(),
    status: modalStatus.value.trim() === "0" ? "0" : "1",
  };

  try {
    if (modalMode.value === "add") {
      await createPurchasingJobscope(body);
      toast.success("Saved", "Jobscope created.");
    } else if (editingId.value !== null) {
      await updatePurchasingJobscope(editingId.value, body);
      toast.success("Saved", "Jobscope updated.");
    }
    closeModal();
    await loadRows();
  } catch (e) {
    const msg = e instanceof Error ? e.message : "Save failed.";
    toast.error("Save failed", msg);
  }
}

function exportCsv() {
  if (!rows.value.length) {
    toast.error("Export", "No data to export.");
    return;
  }
  const headers = ["No", "Code", "Name", "Level", "Category", "Parent", "Status"];
  const lines = [
    headers.join(","),
    ...rows.value.map((row, idx) =>
      [
        String((page.value - 1) * limit.value + idx + 1),
        `"${cellCode(row).replace(/"/g, '""')}"`,
        `"${cellName(row).replace(/"/g, '""')}"`,
        cellLevel(row),
        `"${cellCategory(row).replace(/"/g, '""')}"`,
        `"${cellParent(row).replace(/"/g, '""')}"`,
        cellStatus(row),
      ].join(","),
    ),
  ];
  const blob = new Blob([lines.join("\n")], { type: "text/csv;charset=utf-8;" });
  const url = URL.createObjectURL(blob);
  const a = document.createElement("a");
  a.href = url;
  a.download = "list-of-jobscope.csv";
  a.click();
  URL.revokeObjectURL(url);
}

function exportPdfPlaceholder() {
  toast.success("PDF", "PDF export can be wired to the legacy report when ready.");
}

onMounted(() => {
  void loadFormOptions();
  void loadRows();
});

onUnmounted(() => {
  if (searchDebounce) clearTimeout(searchDebounce);
});
</script>

<template>
  <AdminLayout>
    <div class="space-y-4">
      <h1 class="page-title">{{ pageHeading }}</h1>

      <article class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-wrap items-end gap-3 border-b border-slate-100 px-4 py-3">
          <label class="flex shrink-0 items-center gap-2 text-sm">
            <span class="text-slate-600">Display</span>
            <select
              v-model.number="limit"
              class="rounded-lg border border-slate-300 bg-white px-2 py-1.5 text-sm"
              @change="
                page = 1;
                loadRows();
              "
            >
              <option :value="10">10</option>
              <option :value="25">25</option>
              <option :value="50">50</option>
              <option :value="100">100</option>
            </select>
          </label>
          <div class="relative min-w-[12rem] flex-1 sm:max-w-md">
            <Search class="pointer-events-none absolute left-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
            <input
              v-model="q"
              type="search"
              placeholder="Filter rows…"
              class="w-full rounded-lg border border-slate-300 py-2 pl-9 pr-8 text-sm"
              autocomplete="off"
              @input="onSearchDebounced"
              @keydown.enter.prevent="flushSearchFromInput"
            />
            <button
              v-if="q.trim()"
              type="button"
              class="absolute right-2 top-1/2 -translate-y-1/2 rounded p-0.5 text-slate-500 hover:bg-slate-100"
              aria-label="Clear search"
              @click="clearSearch"
            >
              <X class="h-4 w-4" />
            </button>
          </div>
          <button
            type="button"
            class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 hover:bg-slate-50"
            @click="showSmartFilter = true"
          >
            <Filter class="h-4 w-4" />
            Filter
          </button>
        </div>

        <div class="overflow-x-auto px-4 py-3">
          <table class="admin-table-kitchen min-w-full border-collapse text-sm">
            <thead class="admin-table-thead-sticky">
              <tr>
                <th class="whitespace-nowrap">No</th>
                <th class="whitespace-nowrap">Code</th>
                <th class="whitespace-nowrap">Name</th>
                <th class="whitespace-nowrap">Level</th>
                <th class="whitespace-nowrap">Category</th>
                <th class="whitespace-nowrap">Parent</th>
                <th class="whitespace-nowrap">Status</th>
                <th class="whitespace-nowrap">Action</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="loading">
                <td colspan="8" class="px-3 py-6 text-center text-slate-500">Loading…</td>
              </tr>
              <tr v-for="(row, ri) in rows" v-else :key="rowId(row) ?? ri" class="border-b border-slate-100">
                <td class="px-3 py-2 text-slate-700">{{ (page - 1) * limit + ri + 1 }}</td>
                <td class="px-3 py-2 font-medium text-slate-900">{{ cellCode(row) }}</td>
                <td class="px-3 py-2">{{ cellName(row) }}</td>
                <td class="px-3 py-2">{{ cellLevel(row) }}</td>
                <td class="px-3 py-2">{{ cellCategory(row) }}</td>
                <td class="px-3 py-2">{{ cellParent(row) }}</td>
                <td class="px-3 py-2">{{ cellStatus(row) }}</td>
                <td class="px-3 py-2">
                  <button
                    type="button"
                    class="rounded border border-slate-200 p-1.5 text-slate-700 hover:bg-slate-50"
                    title="Edit"
                    @click="openEdit(row)"
                  >
                    <Pencil class="h-4 w-4" />
                  </button>
                </td>
              </tr>
              <tr v-if="!loading && !rows.length">
                <td colspan="8" class="px-3 py-8 text-center text-slate-500">No records</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 px-4 py-3">
          <p class="text-sm text-slate-600">{{ total }} records</p>
          <div class="flex flex-wrap items-center gap-2">
            <button
              type="button"
              :disabled="page <= 1"
              class="rounded-lg border px-3 py-1 text-sm disabled:opacity-40"
              :class="
                page > 1
                  ? 'border-slate-300 text-slate-700 hover:bg-slate-50'
                  : 'border-slate-200 text-slate-400'
              "
              @click="goPrev"
            >
              Prev
            </button>
            <span class="text-sm text-slate-600">Page {{ page }} / {{ totalPages }}</span>
            <button
              type="button"
              :disabled="page >= totalPages"
              class="rounded-lg border px-3 py-1 text-sm disabled:opacity-40"
              :class="
                page < totalPages
                  ? 'border-slate-300 text-slate-700 hover:bg-slate-50'
                  : 'border-slate-200 text-slate-400'
              "
              @click="goNext"
            >
              Next
            </button>
          </div>
        </div>

        <div class="flex flex-wrap justify-end gap-2 border-t border-slate-100 px-4 py-3">
          <button
            type="button"
            class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm hover:bg-slate-50"
            @click="exportPdfPlaceholder"
          >
            <FileDown class="h-4 w-4" />
            Download PDF
          </button>
          <button
            type="button"
            class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm hover:bg-slate-50"
            @click="exportCsv"
          >
            <FileSpreadsheet class="h-4 w-4" />
            Download CSV
          </button>
          <button
            type="button"
            class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
            @click="openAdd"
          >
            <Plus class="h-4 w-4" />
            Add
          </button>
        </div>
      </article>

      <!-- Smart Filter (legacy sf_0 … sf_3) -->
      <Teleport to="body">
        <div
          v-if="showSmartFilter"
          class="fixed inset-0 z-50 flex items-center justify-center bg-black/40"
          @click.self="showSmartFilter = false"
        >
          <div class="w-full max-w-lg rounded-xl border border-slate-200 bg-white shadow-xl">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
              <h3 class="text-base font-semibold text-slate-900">Smart Filter</h3>
              <button type="button" class="text-slate-400 hover:text-slate-700" @click="showSmartFilter = false">
                <X class="h-4 w-4" />
              </button>
            </div>
            <div class="grid gap-3 p-5">
              <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">Code</label>
                <input
                  v-model="sfCode"
                  type="text"
                  class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm uppercase"
                  placeholder="Code contains…"
                  autocomplete="off"
                />
              </div>
              <div class="grid gap-3 sm:grid-cols-2">
                <div>
                  <label class="mb-1 block text-xs font-medium text-slate-600">Level</label>
                  <select v-model="sfLevel" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
                    <option value="">— All —</option>
                    <option v-for="o in levels" :key="'lv-' + o.value" :value="o.value">{{ o.label }}</option>
                  </select>
                </div>
                <div>
                  <label class="mb-1 block text-xs font-medium text-slate-600">Category</label>
                  <select v-model="sfCategory" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
                    <option value="">— All —</option>
                    <option v-for="o in categories" :key="'cat-' + o.value" :value="o.value">{{ o.label }}</option>
                  </select>
                </div>
              </div>
              <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">Status</label>
                <select v-model="sfStatus" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
                  <option value="">— All —</option>
                  <option value="ACTIVE">ACTIVE</option>
                  <option value="INACTIVE">INACTIVE</option>
                </select>
              </div>
            </div>
            <div class="flex justify-end gap-2 border-t border-slate-100 px-5 py-4">
              <button
                type="button"
                class="rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50"
                @click="resetSmartFilter"
              >
                Reset
              </button>
              <button
                type="button"
                class="rounded-lg bg-slate-900 px-4 py-2 text-sm text-white hover:bg-slate-700"
                @click="applySmartFilter"
              >
                OK
              </button>
            </div>
          </div>
        </div>
      </Teleport>

      <!-- Jobscope Details -->
      <Teleport to="body">
        <div
          v-if="modalOpen"
          class="fixed inset-0 z-50 flex items-center justify-center bg-black/40"
          role="dialog"
          aria-modal="true"
          @click.self="closeModal"
        >
          <div class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-xl border border-slate-200 bg-white shadow-xl">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
              <h3 class="text-base font-semibold text-slate-900">Jobscope Details</h3>
              <button type="button" class="text-slate-400 hover:text-slate-700" aria-label="Close" @click="closeModal">
                <X class="h-4 w-4" />
              </button>
            </div>
            <div class="grid gap-3 p-5">
              <div>
                <label class="mb-1 block text-xs font-medium text-slate-700"
                  ><span class="text-red-600">*</span> Level</label
                >
                <select
                  v-model="modalLevel"
                  class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm"
                >
                  <option value="">— Select —</option>
                  <option v-for="o in levels" :key="'ml-' + o.value" :value="o.value">{{ o.label }}</option>
                </select>
              </div>
              <div>
                <label class="mb-1 block text-xs font-medium text-slate-700"
                  ><span class="text-red-600">*</span> Category</label
                >
                <select
                  v-model="modalCategory"
                  class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm"
                >
                  <option value="">— Select —</option>
                  <option v-for="o in categories" :key="'mc-' + o.value" :value="o.value">{{ o.label }}</option>
                </select>
              </div>
              <div>
                <label class="mb-1 block text-xs font-medium text-slate-700"
                  ><span class="text-red-600">*</span> Parent</label
                >
                <select
                  v-model="modalParent"
                  class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm disabled:bg-slate-50 disabled:text-slate-500"
                  :disabled="loadingParents"
                >
                  <option v-if="parentOpts.length === 0" value="">
                    {{
                      loadingParents
                        ? "Loading…"
                        : !modalCategory.trim() || !["1", "2", "3"].includes(modalLevel.trim())
                          ? "— Select level & category —"
                          : "— No parents —"
                    }}
                  </option>
                  <option v-for="o in parentOpts" :key="'mp-' + o.value + o.label" :value="o.value">
                    {{ o.label }}
                  </option>
                </select>
              </div>
              <div>
                <label class="mb-1 block text-xs font-medium text-slate-700"
                  ><span class="text-red-600">*</span> Code</label
                >
                <input
                  v-model="modalCode"
                  type="text"
                  class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm uppercase"
                  maxlength="100"
                  autocomplete="off"
                />
              </div>
              <div class="sm:col-span-2">
                <label class="mb-1 block text-xs font-medium text-slate-700"
                  ><span class="text-red-600">*</span> Name</label
                >
                <input
                  v-model="modalName"
                  type="text"
                  class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
                  maxlength="500"
                  autocomplete="off"
                />
              </div>
              <div>
                <label class="mb-1 block text-xs font-medium text-slate-700">Status</label>
                <select v-model="modalStatus" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
                  <option v-for="st in statuses" :key="'st-' + st.value" :value="st.value">{{ st.label }}</option>
                </select>
              </div>
            </div>
            <div class="flex justify-end gap-2 border-t border-slate-100 px-5 py-4">
              <button
                type="button"
                class="rounded-lg bg-red-600 px-4 py-2 text-sm text-white hover:bg-red-700"
                @click="closeModal"
              >
                Cancel
              </button>
              <button
                type="button"
                class="rounded-lg bg-indigo-600 px-4 py-2 text-sm text-white hover:bg-indigo-700"
                @click="saveModal"
              >
                Save
              </button>
            </div>
          </div>
        </div>
      </Teleport>
    </div>
  </AdminLayout>
</template>
