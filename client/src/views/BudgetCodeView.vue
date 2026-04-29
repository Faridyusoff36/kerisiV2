<script setup lang="ts">
import { nextTick, onMounted, onUnmounted, ref, watch } from "vue";
import { useRoute } from "vue-router";
import { Download, FileDown, FileSpreadsheet, Filter, MoreVertical, Plus, Search, X } from "lucide-vue-next";
import AdminLayout from "@/layouts/AdminLayout.vue";
import {
  createBudgetCode,
  getBudgetCode,
  getBudgetCodeOptions,
  listBudgetCodes,
  updateBudgetCode,
} from "@/api/cms";
import { useDatatableFeatures } from "@/composables/useDatatableFeatures";
import type { DatatableRefApi } from "@/composables/useDatatableFeatures";
import { useToast } from "@/composables/useToast";
import type { BudgetCodeInput, BudgetCodeOptions, BudgetCodeRow } from "@/types";

const props = withDefaults(
  defineProps<{
    /** Hidden entry MENUID 1304 (PAGEID 1044): open the create modal after load. */
    openCreateOnMount?: boolean;
    pageHeading?: string;
    cardTitle?: string;
    exportPageName?: string;
  }>(),
  {
    openCreateOnMount: false,
    pageHeading: "Budget / Setup / Budget Code",
    cardTitle: "Budget Code",
    exportPageName: "Budget Code",
  },
);

const route = useRoute();
const toast = useToast();
const rows = ref<BudgetCodeRow[]>([]);
const page = ref(1);
const limit = ref(10);
const q = ref("");
const total = ref(0);
const showSmartFilter = ref(false);
const showModal = ref(false);
const editId = ref<number | null>(null);
const smartFilter = ref({
  lbcLevelFilter: "",
  lbcBudgetCodeFilter: "",
  lbcDescriptionFilter: "",
  lbcStatusFilter: "",
});
const options = ref<BudgetCodeOptions>({
  smartFilter: { level: [], budgetCode: [], status: [] },
  popupModal: { level: [], budgetCode: [], status: [] },
});
const form = ref<BudgetCodeInput>({
  lbcLevel: 3,
  lbcBudgetCode: "",
  lbcDescription: "",
  lbcStatus: "ACTIVE",
});

async function loadOptions() {
  const res = await getBudgetCodeOptions();
  options.value = res.data;
}

async function loadRows() {
  const params = new URLSearchParams({
    page: String(page.value),
    limit: String(limit.value),
    ...(q.value ? { q: q.value } : {}),
    ...(smartFilter.value.lbcLevelFilter ? { lbcLevelFilter: smartFilter.value.lbcLevelFilter } : {}),
    ...(smartFilter.value.lbcBudgetCodeFilter ? { lbcBudgetCodeFilter: smartFilter.value.lbcBudgetCodeFilter } : {}),
    ...(smartFilter.value.lbcDescriptionFilter ? { lbcDescriptionFilter: smartFilter.value.lbcDescriptionFilter } : {}),
    ...(smartFilter.value.lbcStatusFilter ? { lbcStatusFilter: smartFilter.value.lbcStatusFilter } : {}),
  });
  const res = await listBudgetCodes(`?${params.toString()}`);
  rows.value = res.data;
  total.value = Number(res.meta?.total ?? 0);
}

async function openEdit(id: number) {
  const res = await getBudgetCode(id);
  editId.value = id;
  form.value = {
    lbcLevel: res.data.lbcLevel,
    lbcBudgetCode: res.data.lbcBudgetCode,
    lbcDescription: res.data.lbcDescription ?? "",
    lbcStatus: res.data.lbcStatus,
  };
  showModal.value = true;
}

function openCreate() {
  editId.value = null;
  form.value = { lbcLevel: 3, lbcBudgetCode: "", lbcDescription: "", lbcStatus: "ACTIVE" };
  showModal.value = true;
}

async function saveItem() {
  try {
    if (editId.value == null) {
      await createBudgetCode(form.value);
      toast.success("Budget Code created");
    } else {
      await updateBudgetCode(editId.value, form.value);
      toast.success("Budget Code updated");
    }
    showModal.value = false;
    await loadRows();
  } catch (e) {
    const msg = e instanceof Error ? e.message : "Save failed";
    toast.error("Save failed", msg);
  }
}

const exportColumns = ["Level", "Budget Code", "Description", "Status"];

function toExportRow(r: BudgetCodeRow): Record<string, string | number> {
  return {
    Level: r.lbcLevel ?? "",
    "Budget Code": r.lbcBudgetCode ?? "",
    Description: r.lbcDescription ?? "",
    Status: r.lbcStatus ?? "",
  };
}

const datatableRef = ref<DatatableRefApi | null>(null);
const { templateFileInputRef, onTemplateFileChange, handleDownloadPDF, handleDownloadCSV } = useDatatableFeatures({
  pageName: props.exportPageName,
  apiDataPath: "/budget/budget-code",
  defaultExportColumns: exportColumns,
  getFilteredList: () => rows.value.map(toExportRow),
  datatableRef,
  searchKeyword: q,
  smartFilter,
  applyFilters: () => void loadRows(),
});

async function exportExcel() {
  try {
    if (rows.value.length === 0) {
      toast.info("No data", "There is nothing to export.");
      return;
    }
    const ExcelJS = await import("exceljs");
    const wb = new ExcelJS.Workbook();
    const ws = wb.addWorksheet("Budget Code");
    ws.addRow(["No", ...exportColumns]);
    rows.value.forEach((r, idx) => {
      const row = toExportRow(r);
      ws.addRow([idx + 1, ...exportColumns.map((c) => row[c] ?? "")]);
    });
    const buf = await wb.xlsx.writeBuffer();
    const blob = new Blob([buf], { type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" });
    const url = URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.href = url;
    a.download = `${props.exportPageName.replace(/\s+/g, "_")}_${new Date().toISOString().slice(0, 10)}.xlsx`;
    a.click();
    URL.revokeObjectURL(url);
    toast.success("Excel downloaded");
  } catch (e) {
    toast.error("Export failed", e instanceof Error ? e.message : "Excel export failed.");
  }
}

let searchDebounce: ReturnType<typeof setTimeout> | null = null;
watch(q, () => {
  if (searchDebounce) clearTimeout(searchDebounce);
  searchDebounce = setTimeout(() => {
    searchDebounce = null;
    page.value = 1;
    void loadRows();
  }, 350);
});

function applySmartFilter() {
  page.value = 1;
  showSmartFilter.value = false;
  void loadRows();
}

function resetSmartFilter() {
  smartFilter.value = {
    lbcLevelFilter: "",
    lbcBudgetCodeFilter: "",
    lbcDescriptionFilter: "",
    lbcStatusFilter: "",
  };
}

onMounted(async () => {
  await loadOptions();
  await loadRows();
  if (props.openCreateOnMount) {
    await nextTick();
    openCreate();
  }
});
watch(
  () => `${route.path}::${props.openCreateOnMount}`,
  async (_nv, oldKey) => {
    if (oldKey === undefined) return;
    page.value = 1;
    await loadRows();
    if (props.openCreateOnMount) {
      await nextTick();
      openCreate();
    }
  },
);
onUnmounted(() => {
  if (searchDebounce) clearTimeout(searchDebounce);
});
</script>

<template>
  <AdminLayout>
    <div class="space-y-4">
      <input
        ref="templateFileInputRef"
        type="file"
        accept=".json,application/json"
        class="hidden"
        @change="onTemplateFileChange"
      />
      <h1 class="page-title">{{ pageHeading }}</h1>
      <article class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
          <h1 class="text-base font-semibold text-slate-900">{{ cardTitle }}</h1>
          <button class="rounded-lg p-2 text-slate-500 hover:bg-slate-100" aria-label="More">
            <MoreVertical class="h-4 w-4" />
          </button>
        </div>
        <div class="space-y-4 p-4">
          <div class="flex flex-wrap items-end justify-between gap-4">
            <div class="flex items-center gap-2">
              <label class="text-xs font-medium text-slate-600">Display</label>
              <select
                v-model.number="limit"
                class="rounded-lg border border-slate-300 px-2 py-1.5 text-sm"
                @change="page = 1; void loadRows()"
              >
                <option v-for="n in [5, 10, 25, 50, 100]" :key="n" :value="n">{{ n }}</option>
              </select>
            </div>
            <div class="flex items-center gap-2">
              <label class="text-xs font-medium text-slate-600">Search</label>
              <div class="relative">
                <Search class="pointer-events-none absolute left-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400" />
                <input
                  v-model="q"
                  type="search"
                  placeholder="Filter rows..."
                  class="w-56 rounded-lg border border-slate-300 py-1.5 pl-8 pr-8 text-sm"
                  @keyup.enter="page = 1; void loadRows()"
                />
                <button
                  v-if="q"
                  type="button"
                  class="absolute right-1 top-1/2 -translate-y-1/2 rounded p-0.5 text-slate-400 hover:bg-slate-100"
                  aria-label="Clear search"
                  @click="q = ''"
                >
                  <X class="h-3.5 w-3.5" />
                </button>
              </div>
              <button
                class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm"
                @click="showSmartFilter = true"
              >
                <Filter class="h-4 w-4" />Filter
              </button>
            </div>
          </div>
          <div class="overflow-x-auto rounded-lg border border-slate-200">
            <div :class="rows.length > 10 ? 'max-h-[420px] overflow-y-auto' : ''">
              <table class="w-full min-w-[900px] text-sm">
                <thead class="sticky top-0 bg-slate-50">
                  <tr class="border-b border-slate-200 text-left">
                    <th class="px-3 py-2 text-xs font-semibold uppercase">No</th>
                    <th class="px-3 py-2 text-xs font-semibold uppercase">Level</th>
                    <th class="px-3 py-2 text-xs font-semibold uppercase">Budget Code</th>
                    <th class="px-3 py-2 text-xs font-semibold uppercase">Description</th>
                    <th class="px-3 py-2 text-xs font-semibold uppercase">Status</th>
                    <th class="px-3 py-2 text-xs font-semibold uppercase">Action</th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="row in rows"
                    :key="row.lbcId"
                    class="border-b border-slate-100 hover:bg-slate-50"
                  >
                    <td class="px-3 py-2">{{ row.index }}</td>
                    <td class="px-3 py-2">{{ row.lbcLevel }}</td>
                    <td class="px-3 py-2">{{ row.lbcBudgetCode }}</td>
                    <td class="px-3 py-2">{{ row.lbcDescription }}</td>
                    <td class="px-3 py-2">{{ row.lbcStatus }}</td>
                    <td class="px-3 py-2">
                      <button
                        class="rounded p-1 text-slate-500 hover:bg-slate-100"
                        title="Edit"
                        @click="openEdit(row.lbcId)"
                      >
                        ✎
                      </button>
                    </td>
                  </tr>
                  <tr v-if="rows.length === 0">
                    <td colspan="6" class="px-3 py-6 text-center text-xs text-slate-500">No data</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          <div class="flex flex-wrap items-center justify-end gap-2 border-t border-slate-100 pt-3">
            <button
              type="button"
              class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium hover:bg-slate-50"
              @click="handleDownloadPDF"
            >
              <Download class="h-3.5 w-3.5" />PDF
            </button>
            <button
              type="button"
              class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium hover:bg-slate-50"
              @click="handleDownloadCSV"
            >
              <FileDown class="h-3.5 w-3.5" />CSV
            </button>
            <button
              type="button"
              class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium hover:bg-slate-50"
              @click="exportExcel"
            >
              <FileSpreadsheet class="h-3.5 w-3.5" />Excel
            </button>
            <button
              type="button"
              class="inline-flex items-center gap-1.5 rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-medium text-white hover:bg-slate-800"
              @click="openCreate"
            >
              <Plus class="h-3.5 w-3.5" />Add
            </button>
          </div>
        </div>
      </article>
    </div>

    <Teleport to="body">
      <div
        v-if="showSmartFilter"
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm"
        @click.self="showSmartFilter = false"
      >
        <div class="w-full max-w-md rounded-lg border border-slate-200 bg-white shadow-2xl">
          <div class="border-b border-slate-100 px-4 py-3">
            <h3 class="text-base font-semibold text-slate-900">Smart filter</h3>
          </div>
          <div class="space-y-4 p-4">
            <div>
              <label class="mb-1 block text-sm font-medium text-slate-700">Level</label>
              <select
                v-model="smartFilter.lbcLevelFilter"
                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
              >
                <option value="">Any</option>
                <option v-for="opt in options.smartFilter.level" :key="opt.id" :value="opt.id">{{ opt.label }}</option>
              </select>
            </div>
            <div>
              <label class="mb-1 block text-sm font-medium text-slate-700">Budget Code</label>
              <select
                v-model="smartFilter.lbcBudgetCodeFilter"
                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
              >
                <option value="">Any</option>
                <option v-for="opt in options.smartFilter.budgetCode" :key="opt.id" :value="opt.id">{{ opt.label }}</option>
              </select>
            </div>
            <div>
              <label class="mb-1 block text-sm font-medium text-slate-700">Description</label>
              <input
                v-model="smartFilter.lbcDescriptionFilter"
                type="text"
                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
                placeholder="Contains..."
              />
            </div>
            <div>
              <label class="mb-1 block text-sm font-medium text-slate-700">Status</label>
              <select
                v-model="smartFilter.lbcStatusFilter"
                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
              >
                <option value="">Any</option>
                <option v-for="opt in options.smartFilter.status" :key="opt.id" :value="opt.id">{{ opt.label }}</option>
              </select>
            </div>
          </div>
          <div class="flex justify-end gap-2 border-t border-slate-100 px-4 py-3">
            <button
              type="button"
              class="rounded-lg border border-slate-300 px-4 py-2 text-sm"
              @click="resetSmartFilter"
            >
              Reset
            </button>
            <button
              type="button"
              class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white"
              @click="applySmartFilter"
            >
              OK
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <Teleport to="body">
      <div
        v-if="showModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm"
        @click.self="showModal = false"
      >
        <div class="w-full max-w-lg rounded-lg border border-slate-200 bg-white shadow-2xl">
          <div class="border-b border-slate-100 px-4 py-3">
            <h3 class="text-base font-semibold text-slate-900">
              {{ editId == null ? "Add" : "Edit" }} Budget Code
            </h3>
          </div>
          <div class="space-y-4 p-4">
            <div v-if="editId != null">
              <label class="mb-1 block text-sm font-medium text-slate-700">ID</label>
              <input
                :value="editId"
                type="text"
                disabled
                class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-500"
              />
            </div>
            <div>
              <label class="mb-1 block text-sm font-medium text-slate-700">Level <span class="text-rose-500">*</span></label>
              <select
                v-model.number="form.lbcLevel"
                :disabled="editId != null"
                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm disabled:bg-slate-50 disabled:text-slate-500"
              >
                <option
                  v-for="opt in options.popupModal.level"
                  :key="opt.id"
                  :value="Number(opt.id)"
                >
                  {{ opt.label }}
                </option>
              </select>
            </div>
            <div>
              <label class="mb-1 block text-sm font-medium text-slate-700">Budget Code <span class="text-rose-500">*</span></label>
              <input
                v-model="form.lbcBudgetCode"
                :disabled="editId != null"
                type="text"
                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm disabled:bg-slate-50 disabled:text-slate-500"
                placeholder="e.g. 1000"
              />
            </div>
            <div>
              <label class="mb-1 block text-sm font-medium text-slate-700">Description</label>
              <textarea
                v-model="form.lbcDescription"
                rows="3"
                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
              />
            </div>
            <div>
              <label class="mb-1 block text-sm font-medium text-slate-700">Status <span class="text-rose-500">*</span></label>
              <select
                v-model="form.lbcStatus"
                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
              >
                <option v-for="opt in options.popupModal.status" :key="opt.id" :value="opt.id">{{ opt.label }}</option>
              </select>
            </div>
          </div>
          <div class="flex justify-end gap-2 border-t border-slate-100 px-4 py-3">
            <button
              type="button"
              class="rounded-lg border border-slate-300 px-4 py-2 text-sm"
              @click="showModal = false"
            >
              Cancel
            </button>
            <button
              type="button"
              class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white"
              @click="saveItem"
            >
              Save
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </AdminLayout>
</template>
