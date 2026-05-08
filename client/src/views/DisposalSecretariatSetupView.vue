<script setup lang="ts">
/**
 * Kerisi MENUID 3118 / PAGEID 2571 — Disposal Secretariat Setup.
 */
import { computed, onMounted, onUnmounted, ref, watch } from "vue";
import { Download, FileDown, FileSpreadsheet, MoreVertical, Pencil, Plus, Search, Trash2, X } from "lucide-vue-next";

import AdminLayout from "@/layouts/AdminLayout.vue";
import FimsListTable, { type FimsColumn } from "@/components/fims/FimsListTable.vue";
import { useDatatableFeatures } from "@/composables/useDatatableFeatures";
import type { DatatableRefApi } from "@/composables/useDatatableFeatures";
import {
  createDisposalSecretariat,
  deleteDisposalSecretariat,
  getDisposalSecretariat,
  getDisposalSecretariatOptions,
  listDisposalSecretariats,
  updateDisposalSecretariat,
} from "@/api/cms";
import { useConfirmDialog } from "@/composables/useConfirmDialog";
import { useToast } from "@/composables/useToast";
import type { DisposalSecretariatInput, DisposalSecretariatRow } from "@/types";

const PAGE_TABLE = "Secretariat List";
const PAGE_FORM = "Secretariat Details";
const PAGE_BREADCRUMB = "Asset / Setup / General / Disposal Secretariat Setup";

const toast = useToast();
const confirmDialog = useConfirmDialog();

const rows = ref<DisposalSecretariatRow[]>([]);
const loading = ref(false);
const total = ref(0);
const page = ref(1);
const limit = ref(5);
const q = ref("");
const sortBy = ref("isc_type");
const sortDir = ref<"asc" | "desc">("asc");

const datatableRef = ref<DatatableRefApi | null>(null);
const overflowOpen = ref(false);
const overflowRoot = ref<HTMLElement | null>(null);

const itemSubcats = ref<{ value: string; label: string }[]>([]);
const staffOptions = ref<{ value: string; label: string }[]>([]);
const staffPickQ = ref("");
let staffPickDebounce: ReturnType<typeof setTimeout> | null = null;

const editingId = ref<number | null>(null);
const saving = ref(false);
const detail = ref<DisposalSecretariatInput>({
  iscType: "",
  stfStaffId: "",
  stfStaffIdSuperior: "",
  stfStaffIdHod: "",
  astStatus: 1,
});

const detailTitle = computed(() => `${editingId.value == null ? "Add" : "Update"} ${PAGE_FORM}`);

const totalPages = computed(() => (total.value ? Math.max(1, Math.ceil(total.value / limit.value)) : 1));
const startIdx = computed(() => (total.value === 0 ? 0 : (page.value - 1) * limit.value + 1));
const endIdx = computed(() => Math.min(page.value * limit.value, total.value));

const columns: FimsColumn<DisposalSecretariatRow>[] = [
  { key: "no", label: "No", value: (r) => r.index },
  {
    key: "iscTypeDisplay",
    label: "Item Subcategory Type",
    sortable: true,
    sortKey: "isc_type",
    hideable: true,
    value: (r) => r.iscTypeDisplay ?? "",
  },
  {
    key: "staffLabel",
    label: "Staff",
    sortable: true,
    sortKey: "stf_staff_id",
    hideable: true,
    value: (r) => r.staffLabel ?? "",
  },
  {
    key: "superiorLabel",
    label: "Superior",
    sortable: true,
    sortKey: "stf_staff_id_superior",
    hideable: true,
    value: (r) => r.superiorLabel ?? "",
  },
  {
    key: "hodLabel",
    label: "Head of Department",
    sortable: true,
    sortKey: "stf_staff_id_hod",
    hideable: true,
    value: (r) => r.hodLabel ?? "",
  },
  {
    key: "astStatus",
    label: "Status",
    sortable: true,
    sortKey: "ast_status",
    hideable: true,
    value: (r) => r.astStatus ?? "",
  },
  {
    key: "createdDate",
    label: "Created Date",
    sortable: true,
    sortKey: "created_date",
    hideable: true,
    value: (r) => r.createdDate ?? "",
  },
  { key: "action", label: "Action" },
];

async function loadStaffOptions() {
  try {
    const params = staffPickQ.value.trim() ? `?staff_q=${encodeURIComponent(staffPickQ.value.trim())}` : "";
    const res = await getDisposalSecretariatOptions(params);
    itemSubcats.value = res.data.itemSubcats;
    staffOptions.value = res.data.staff;
  } catch (e) {
    toast.error("Options failed", e instanceof Error ? e.message : "Unable to load lookups.");
  }
}

async function loadRows() {
  loading.value = true;
  try {
    const params = new URLSearchParams({
      page: String(page.value),
      limit: String(limit.value),
      sort_by: sortBy.value,
      sort_dir: sortDir.value,
      ...(q.value.trim() ? { q: q.value.trim() } : {}),
    });
    const res = await listDisposalSecretariats(`?${params.toString()}`);
    rows.value = res.data;
    total.value = Number(res.meta?.total ?? 0);
  } catch (e) {
    toast.error("Load failed", e instanceof Error ? e.message : "Unable to load secretariat rows.");
  } finally {
    loading.value = false;
  }
}

function onSort(sortKey: string) {
  if (sortBy.value === sortKey) sortDir.value = sortDir.value === "asc" ? "desc" : "asc";
  else {
    sortBy.value = sortKey;
    sortDir.value = "asc";
  }
  page.value = 1;
  void loadRows();
}

function prevPage() {
  if (page.value > 1) {
    page.value -= 1;
    void loadRows();
  }
}
function nextPage() {
  if (page.value < totalPages.value) {
    page.value += 1;
    void loadRows();
  }
}

function resetDetail() {
  editingId.value = null;
  detail.value = {
    iscType: "",
    stfStaffId: "",
    stfStaffIdSuperior: "",
    stfStaffIdHod: "",
    astStatus: 1,
  };
}

async function editRow(astId: number) {
  try {
    const res = await getDisposalSecretariat(astId);
    editingId.value = astId;
    detail.value = {
      iscType: res.data.iscType,
      stfStaffId: res.data.stfStaffId,
      stfStaffIdSuperior: res.data.stfStaffIdSuperior,
      stfStaffIdHod: res.data.stfStaffIdHod,
      astStatus: Number(res.data.astStatus),
    };
  } catch (e) {
    toast.error("Load failed", e instanceof Error ? e.message : "Unable to load secretariat.");
  }
}

async function deleteRow(astId: number) {
  const ok = await confirmDialog.confirm({
    title: "Delete secretariat?",
    message: "This will remove the selected secretariat assignment.",
    confirmText: "Delete",
    destructive: true,
  });
  if (!ok) return;
  try {
    await deleteDisposalSecretariat(astId);
    toast.success("Deleted");
    if (editingId.value === astId) resetDetail();
    await loadRows();
  } catch (e) {
    toast.error("Delete failed", e instanceof Error ? e.message : "Unable to delete.");
  }
}

async function saveDetail() {
  const v = detail.value;
  if (!v.iscType?.trim() || !v.stfStaffId || !v.stfStaffIdSuperior || !v.stfStaffIdHod) {
    toast.error("Validation", "Please complete all required fields.");
    return;
  }
  saving.value = true;
  try {
    const payload: DisposalSecretariatInput = {
      iscType: v.iscType.trim(),
      stfStaffId: v.stfStaffId,
      stfStaffIdSuperior: v.stfStaffIdSuperior,
      stfStaffIdHod: v.stfStaffIdHod,
      astStatus: Number(v.astStatus) === 0 ? 0 : 1,
    };
    if (editingId.value == null) {
      await createDisposalSecretariat(payload);
      toast.success("Saved");
    } else {
      await updateDisposalSecretariat(editingId.value, payload);
      toast.success("Updated");
    }
    resetDetail();
    await loadRows();
  } catch (e) {
    toast.error("Save failed", e instanceof Error ? e.message : "Unable to save.");
  } finally {
    saving.value = false;
  }
}

const {
  templateFileInputRef,
  isGrouped,
  handleSaveTemplate,
  handleLoadTemplate,
  onTemplateFileChange,
  handleUngroupList,
  handleGroupList,
  handleDownloadPDF,
  handleDownloadCSV,
} = useDatatableFeatures({
  pageName: PAGE_TABLE,
  apiDataPath: "/asset/disposal-secretariat",
  defaultExportColumns: [
    "Item Subcategory Type",
    "Staff",
    "Superior",
    "Head of Department",
    "Status",
    "Created Date",
  ],
  getFilteredList: () => (datatableRef.value?.getExportConfig?.()?.data as Record<string, unknown>[]) ?? [],
  datatableRef,
  searchKeyword: q,
  applyFilters: () => void loadRows(),
});

async function exportExcel() {
  try {
    const cfg = datatableRef.value?.getExportConfig?.();
    const columnsOut = cfg?.columns ?? [];
    const data = (cfg?.data as Record<string, unknown>[]) ?? [];
    if (data.length === 0) {
      toast.info("No data", "There is nothing to export.");
      return;
    }
    const ExcelJS = await import("exceljs");
    const wb = new ExcelJS.Workbook();
    const ws = wb.addWorksheet(PAGE_TABLE);
    ws.addRow(["No", ...columnsOut]);
    data.forEach((row, idx) => {
      const values = columnsOut.map((c) => (row[c] ?? "") as string | number);
      ws.addRow([idx + 1, ...values]);
    });
    const buf = await wb.xlsx.writeBuffer();
    const blob = new Blob([buf], { type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" });
    const url = URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.href = url;
    a.download = `Disposal_Secretariat_${new Date().toISOString().slice(0, 10)}.xlsx`;
    a.click();
    URL.revokeObjectURL(url);
    toast.success("Excel downloaded");
  } catch (e) {
    toast.error("Export failed", e instanceof Error ? e.message : "Excel export failed.");
  }
}

function onClickOutside(event: MouseEvent) {
  if (!overflowOpen.value) return;
  if (!overflowRoot.value?.contains(event.target as Node)) overflowOpen.value = false;
}

let qSearchDebounce: ReturnType<typeof setTimeout> | null = null;
watch(q, () => {
  if (qSearchDebounce) clearTimeout(qSearchDebounce);
  qSearchDebounce = setTimeout(() => {
    qSearchDebounce = null;
    page.value = 1;
    void loadRows();
  }, 350);
});

watch(staffPickQ, () => {
  if (staffPickDebounce) clearTimeout(staffPickDebounce);
  staffPickDebounce = setTimeout(() => {
    staffPickDebounce = null;
    void loadStaffOptions();
  }, 350);
});

onMounted(() => {
  void loadStaffOptions();
  void loadRows();
  document.addEventListener("click", onClickOutside);
});

onUnmounted(() => {
  if (qSearchDebounce) clearTimeout(qSearchDebounce);
  if (staffPickDebounce) clearTimeout(staffPickDebounce);
  document.removeEventListener("click", onClickOutside);
});
</script>

<template>
  <AdminLayout>
    <div class="space-y-4">
      <input ref="templateFileInputRef" type="file" accept=".json,application/json" class="hidden" @change="onTemplateFileChange" />

      <h1 class="page-title">{{ PAGE_BREADCRUMB }}</h1>

      <article class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between gap-2 border-b border-slate-100 px-4 py-3">
          <h1 class="text-base font-semibold text-slate-900">{{ PAGE_TABLE }}</h1>
          <div ref="overflowRoot" class="relative">
            <button type="button" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100" @click.stop="overflowOpen = !overflowOpen">
              <MoreVertical class="h-4 w-4" />
            </button>
            <div v-if="overflowOpen" class="absolute right-0 z-30 mt-1 w-44 rounded-lg border border-slate-200 bg-white py-1 shadow-lg" @click.stop>
              <button type="button" class="block w-full px-3 py-2 text-left text-xs text-slate-700 hover:bg-slate-50" @click="overflowOpen = false; handleSaveTemplate()">Save template</button>
              <button type="button" class="block w-full px-3 py-2 text-left text-xs text-slate-700 hover:bg-slate-50" @click="overflowOpen = false; handleLoadTemplate()">Load template</button>
              <button v-if="isGrouped" type="button" class="block w-full px-3 py-2 text-left text-xs text-slate-700 hover:bg-slate-50" @click="overflowOpen = false; handleUngroupList()">Ungroup list</button>
              <button v-else type="button" class="block w-full px-3 py-2 text-left text-xs text-slate-700 hover:bg-slate-50" @click="overflowOpen = false; handleGroupList()">Group list</button>
            </div>
          </div>
        </div>

        <div class="space-y-4 p-4">
          <div class="flex flex-wrap items-end justify-between gap-4">
            <div class="flex flex-wrap items-center gap-2">
              <label class="text-xs font-medium text-slate-600">Display</label>
              <select v-model.number="limit" class="rounded-lg border border-slate-300 px-2 py-1.5 text-sm shadow-sm" @change="page = 1; loadRows()">
                <option v-for="n in [5, 10, 15, 25, 50, 100]" :key="n" :value="n">{{ n }}</option>
              </select>
            </div>
            <div class="flex flex-wrap items-center gap-2">
              <label class="text-xs font-medium text-slate-600">Search</label>
              <div class="relative">
                <Search class="pointer-events-none absolute left-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400" />
                <input
                  v-model="q"
                  type="search"
                  placeholder="Filter rows…"
                  class="w-52 rounded-lg border border-slate-300 py-1.5 pl-8 pr-8 text-sm shadow-sm"
                  @keyup.enter="page = 1; void loadRows()"
                />
                <button v-if="q" type="button" class="absolute right-1 top-1/2 -translate-y-1/2 rounded p-0.5 text-slate-400 hover:bg-slate-100" @click="q = ''; page = 1; loadRows()">
                  <X class="h-3.5 w-3.5" />
                </button>
              </div>
            </div>
          </div>

          <div v-if="loading" class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-6 text-center text-sm text-slate-600">Loading…</div>
          <FimsListTable
            v-else
            ref="datatableRef"
            :rows="rows"
            :columns="columns"
            :grouped="isGrouped"
            :sort-by="sortBy"
            :sort-dir="sortDir"
            :row-key="(r) => r.astId"
            :group-by="(r) => `Status ${r.astStatus ?? ''}`"
            min-width="980px"
            @sort="onSort"
          >
            <template #action="{ row }">
              <span class="inline-flex gap-1">
                <button type="button" class="rounded p-1 text-slate-500 hover:bg-slate-100" title="Edit" @click="editRow((row as DisposalSecretariatRow).astId)">
                  <Pencil class="h-3.5 w-3.5" />
                </button>
                <button type="button" class="rounded p-1 text-rose-500 hover:bg-rose-50" title="Delete" @click="deleteRow((row as DisposalSecretariatRow).astId)">
                  <Trash2 class="h-3.5 w-3.5" />
                </button>
              </span>
            </template>
          </FimsListTable>

          <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-3">
            <div class="text-xs text-slate-500">Showing {{ startIdx }}-{{ endIdx }} of {{ total }}</div>
            <div class="flex flex-wrap items-center gap-2">
              <button type="button" :disabled="page <= 1" class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium disabled:opacity-50" @click="prevPage">
                Prev
              </button>
              <span class="text-xs text-slate-600">Page {{ page }} / {{ totalPages }}</span>
              <button type="button" :disabled="page >= totalPages" class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium disabled:opacity-50" @click="nextPage">
                Next
              </button>
              <div class="mx-2 h-5 w-px bg-slate-200" />
              <button type="button" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium shadow-sm hover:bg-slate-50" @click="handleDownloadPDF">
                <Download class="h-3.5 w-3.5" />
                PDF
              </button>
              <button type="button" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium shadow-sm hover:bg-slate-50" @click="handleDownloadCSV">
                <FileDown class="h-3.5 w-3.5" />
                CSV
              </button>
              <button type="button" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium shadow-sm hover:bg-slate-50" @click="exportExcel">
                <FileSpreadsheet class="h-3.5 w-3.5" />
                Excel
              </button>
              <button type="button" class="inline-flex items-center gap-1.5 rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-medium text-white shadow-sm hover:bg-slate-800" @click="resetDetail">
                <Plus class="h-3.5 w-3.5" />
                Add
              </button>
            </div>
          </div>
        </div>
      </article>

      <article class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-4 py-3">
          <h1 class="text-base font-semibold text-slate-900">{{ detailTitle }}</h1>
        </div>
        <div class="space-y-4 p-4">
          <div class="grid gap-4 md:grid-cols-2">
            <div class="md:col-span-2">
              <label class="mb-1 block text-sm font-medium text-slate-700">Filter staff pick-lists</label>
              <input v-model="staffPickQ" type="search" placeholder="Type to narrow staff dropdowns…" class="w-full max-w-md rounded-lg border border-slate-300 px-3 py-2 text-sm" />
            </div>
            <div class="md:col-span-2">
              <label class="mb-1 block text-sm font-medium text-slate-700">Item Subcategory *</label>
              <select v-model="detail.iscType" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="" disabled>Select…</option>
                <option v-for="o in itemSubcats" :key="o.value" :value="o.value">{{ o.label }}</option>
              </select>
            </div>
            <div>
              <label class="mb-1 block text-sm font-medium text-slate-700">Staff *</label>
              <select v-model="detail.stfStaffId" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="" disabled>Select…</option>
                <option v-for="o in staffOptions" :key="`s-${o.value}`" :value="o.value">{{ o.label }}</option>
              </select>
            </div>
            <div>
              <label class="mb-1 block text-sm font-medium text-slate-700">Superior *</label>
              <select v-model="detail.stfStaffIdSuperior" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="" disabled>Select…</option>
                <option v-for="o in staffOptions" :key="`sup-${o.value}`" :value="o.value">{{ o.label }}</option>
              </select>
            </div>
            <div>
              <label class="mb-1 block text-sm font-medium text-slate-700">Head of Department *</label>
              <select v-model="detail.stfStaffIdHod" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="" disabled>Select…</option>
                <option v-for="o in staffOptions" :key="`hod-${o.value}`" :value="o.value">{{ o.label }}</option>
              </select>
            </div>
            <div>
              <label class="mb-1 block text-sm font-medium text-slate-700">Status *</label>
              <select v-model.number="detail.astStatus" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option :value="1">ACTIVE</option>
                <option :value="0">INACTIVE</option>
              </select>
            </div>
          </div>
          <div class="flex flex-wrap justify-end gap-2 border-t border-slate-100 pt-3">
            <button type="button" class="rounded-lg border border-slate-300 px-4 py-2 text-sm" @click="resetDetail">Clear</button>
            <button type="button" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white disabled:opacity-50" :disabled="saving" @click="saveDetail">Save</button>
          </div>
        </div>
      </article>
    </div>
  </AdminLayout>
</template>
