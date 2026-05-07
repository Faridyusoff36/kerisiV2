<script setup lang="ts">
/** Kerisi menu 2455 — Depreciation Group (DEPR_GROUP lookup). */
import { computed, onMounted, ref, watch } from "vue";
import { Download, Eye, FileDown, FileSpreadsheet, Pencil, Plus, Search, Trash2, X } from "lucide-vue-next";

import AdminLayout from "@/layouts/AdminLayout.vue";
import FimsListTable, { type FimsColumn } from "@/components/fims/FimsListTable.vue";
import { useDatatableFeatures } from "@/composables/useDatatableFeatures";
import type { DatatableRefApi } from "@/composables/useDatatableFeatures";
import {
  createAssetDepreciationGroup,
  deleteAssetDepreciationGroup,
  getAssetDepreciationGroup,
  listAssetDepreciationGroups,
  updateAssetDepreciationGroup,
} from "@/api/cms";
import { useToast } from "@/composables/useToast";
import type { AssetDepreciationGroupInput, AssetDepreciationGroupRow } from "@/types";

const PAGE_NAME = "Depreciation group";
const PAGE_BREADCRUMB = "Asset / Setup / General / Depreciation Group";

const exportColumnLabels = ["Code", "Description", "Depr %", "Est. life", "Ext. status", "Status"] as const;

const toast = useToast();
const rows = ref<AssetDepreciationGroupRow[]>([]);
const loading = ref(false);
const total = ref(0);
const page = ref(1);
const limit = ref(15);
const q = ref("");
const sortBy = ref("lde_value");
const sortDir = ref<"asc" | "desc">("asc");
const datatableRef = ref<DatatableRefApi | null>(null);

const totalPages = computed(() => (total.value ? Math.max(1, Math.ceil(total.value / limit.value)) : 1));
const startIdx = computed(() => (total.value === 0 ? 0 : (page.value - 1) * limit.value + 1));
const endIdx = computed(() => Math.min(page.value * limit.value, total.value));

const showModal = ref(false);
const modalReadOnly = ref(false);
const editId = ref<number | null>(null);
const form = ref<AssetDepreciationGroupInput>({
  ldeValue: "",
  ldeDescription: "",
  deprRate: "",
  estimatedLife: "",
  statusDesc: "",
  ldeStatus: "1",
});

const columns: FimsColumn<AssetDepreciationGroupRow>[] = [
  { key: "no", label: "No", value: (r) => r.index },
  { key: "ldeValue", label: "Code", sortable: true, sortKey: "lde_value", value: (r) => r.ldeValue },
  { key: "ldeDescription", label: "Description", sortable: true, sortKey: "lde_description", value: (r) => r.ldeDescription },
  { key: "deprRate", label: "Depr %", value: (r) => r.deprRate },
  { key: "estimatedLife", label: "Est. life", value: (r) => r.estimatedLife },
  { key: "statusDesc", label: "Ext. status", value: (r) => r.statusDesc },
  { key: "ldeStatus", label: "Status", sortable: true, sortKey: "lde_status", value: (r) => r.ldeStatus },
  { key: "action", label: "Action" },
];

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
    const res = await listAssetDepreciationGroups(`?${params.toString()}`);
    rows.value = res.data;
    total.value = Number(res.meta?.total ?? 0);
  } catch (e) {
    toast.error("Load failed", e instanceof Error ? e.message : "Unable to load.");
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

const { handleDownloadPDF, handleDownloadCSV } = useDatatableFeatures({
  pageName: PAGE_NAME,
  apiDataPath: "/asset/depreciation-groups",
  defaultExportColumns: [...exportColumnLabels],
  getFilteredList: () => (datatableRef.value?.getExportConfig?.()?.data as Record<string, unknown>[]) ?? [],
  datatableRef,
  searchKeyword: q,
  applyFilters: () => void loadRows(),
});

async function exportExcel() {
  try {
    const cfg = datatableRef.value?.getExportConfig?.();
    const columnsOut = cfg?.columns ?? [...exportColumnLabels];
    const data = (cfg?.data as Record<string, unknown>[]) ?? [];
    if (data.length === 0) {
      toast.info("No data", "There is nothing to export.");
      return;
    }
    const ExcelJS = await import("exceljs");
    const wb = new ExcelJS.Workbook();
    const ws = wb.addWorksheet(PAGE_NAME);
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
    a.download = `Depreciation_Group_${new Date().toISOString().slice(0, 10)}.xlsx`;
    a.click();
    URL.revokeObjectURL(url);
    toast.success("Excel downloaded");
  } catch (e) {
    toast.error("Export failed", e instanceof Error ? e.message : "Excel export failed.");
  }
}

function openCreate() {
  modalReadOnly.value = false;
  editId.value = null;
  form.value = { ldeValue: "", ldeDescription: "", deprRate: "", estimatedLife: "", statusDesc: "", ldeStatus: "1" };
  showModal.value = true;
}

async function openView(id: number) {
  modalReadOnly.value = true;
  editId.value = id;
  try {
    const res = await getAssetDepreciationGroup(id);
    const d = res.data;
    form.value = {
      ldeValue: d.ldeValue,
      ldeDescription: d.ldeDescription,
      deprRate: d.deprRate ?? "",
      estimatedLife: d.estimatedLife ?? "",
      statusDesc: d.statusDesc ?? "",
      ldeStatus: d.ldeStatus,
    };
    showModal.value = true;
  } catch (e) {
    toast.error("Load failed", e instanceof Error ? e.message : "");
  }
}

async function openEdit(id: number) {
  modalReadOnly.value = false;
  editId.value = id;
  try {
    const res = await getAssetDepreciationGroup(id);
    const d = res.data;
    form.value = {
      ldeValue: d.ldeValue,
      ldeDescription: d.ldeDescription,
      deprRate: d.deprRate ?? "",
      estimatedLife: d.estimatedLife ?? "",
      statusDesc: d.statusDesc ?? "",
      ldeStatus: d.ldeStatus,
    };
    showModal.value = true;
  } catch (e) {
    toast.error("Load failed", e instanceof Error ? e.message : "");
  }
}

async function saveModal() {
  if (modalReadOnly.value) return;
  if (!form.value.ldeValue.trim() || !form.value.ldeDescription.trim()) {
    toast.error("Validation", "Code and description required.");
    return;
  }
  try {
    if (editId.value == null) {
      await createAssetDepreciationGroup({ ...form.value });
      toast.success("Saved");
    } else {
      await updateAssetDepreciationGroup(editId.value, { ...form.value });
      toast.success("Updated");
    }
    showModal.value = false;
    await loadRows();
  } catch (e) {
    toast.error("Save failed", e instanceof Error ? e.message : "");
  }
}

async function onDelete(id: number) {
  if (!confirm("Delete this depreciation group?")) return;
  try {
    await deleteAssetDepreciationGroup(id);
    toast.success("Deleted");
    await loadRows();
  } catch (e) {
    toast.error("Delete failed", e instanceof Error ? e.message : "");
  }
}

let deb: ReturnType<typeof setTimeout> | null = null;
watch(q, () => {
  if (deb) clearTimeout(deb);
  deb = setTimeout(() => {
    deb = null;
    page.value = 1;
    void loadRows();
  }, 350);
});

onMounted(() => void loadRows());
</script>

<template>
  <AdminLayout>
    <div class="space-y-4">
      <h1 class="page-title">{{ PAGE_BREADCRUMB }}</h1>

      <article class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 px-4 py-3">
          <h2 class="text-base font-semibold text-slate-900">{{ PAGE_NAME }}</h2>
        </div>

        <div class="space-y-4 p-4">
          <div class="flex flex-wrap items-end justify-between gap-4">
            <div class="flex flex-wrap items-center gap-2">
              <label class="text-xs font-medium text-slate-600">Display</label>
              <select
                v-model.number="limit"
                class="rounded-lg border border-slate-300 px-2 py-1.5 text-sm shadow-sm"
                @change="
                  page = 1;
                  loadRows();
                "
              >
                <option v-for="n in [5, 10, 15, 25, 50]" :key="n" :value="n">{{ n }}</option>
              </select>
            </div>
            <div class="relative">
              <Search class="pointer-events-none absolute left-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400" />
              <input
                v-model="q"
                type="search"
                placeholder="Search…"
                class="w-52 rounded-lg border border-slate-300 py-1.5 pl-8 pr-8 text-sm shadow-sm"
              />
              <button v-if="q" type="button" class="absolute right-1 top-1/2 -translate-y-1/2 rounded p-0.5 text-slate-400 hover:bg-slate-100" @click="q = ''">
                <X class="h-3.5 w-3.5" />
              </button>
            </div>
          </div>

          <div v-if="loading" class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-6 text-center text-sm text-slate-600">Loading…</div>
          <FimsListTable
            v-else
            ref="datatableRef"
            :rows="rows"
            :columns="columns"
            :grouped="false"
            :sort-by="sortBy"
            :sort-dir="sortDir"
            :row-key="(r) => r.ldeId"
            min-width="900px"
            @sort="onSort"
          >
            <template #action="{ row }">
              <span class="inline-flex gap-1">
                <button type="button" class="rounded p-1 text-slate-500 hover:bg-slate-100" title="View" @click="openView((row as AssetDepreciationGroupRow).ldeId)">
                  <Eye class="h-3.5 w-3.5" />
                </button>
                <button type="button" class="rounded p-1 text-slate-500 hover:bg-slate-100" title="Edit" @click="openEdit((row as AssetDepreciationGroupRow).ldeId)">
                  <Pencil class="h-3.5 w-3.5" />
                </button>
                <button type="button" class="rounded p-1 text-red-500 hover:bg-red-50" title="Delete" @click="onDelete((row as AssetDepreciationGroupRow).ldeId)">
                  <Trash2 class="h-3.5 w-3.5" />
                </button>
              </span>
            </template>
          </FimsListTable>

          <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-3">
            <div class="text-xs text-slate-500">Showing {{ startIdx }}-{{ endIdx }} of {{ total }}</div>
            <div class="flex flex-wrap items-center gap-2">
              <button
                type="button"
                :disabled="page <= 1"
                class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium disabled:opacity-50"
                @click="prevPage"
              >
                Prev
              </button>
              <span class="text-xs text-slate-600">Page {{ page }} / {{ totalPages }}</span>
              <button
                type="button"
                :disabled="page >= totalPages"
                class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium disabled:opacity-50"
                @click="nextPage"
              >
                Next
              </button>
              <div class="mx-2 h-5 w-px bg-slate-200" />
              <button
                type="button"
                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium shadow-sm hover:bg-slate-50"
                @click="handleDownloadPDF"
              >
                <Download class="h-3.5 w-3.5" />
                PDF
              </button>
              <button
                type="button"
                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium shadow-sm hover:bg-slate-50"
                @click="handleDownloadCSV"
              >
                <FileDown class="h-3.5 w-3.5" />
                CSV
              </button>
              <button
                type="button"
                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium shadow-sm hover:bg-slate-50"
                @click="exportExcel"
              >
                <FileSpreadsheet class="h-3.5 w-3.5" />
                Excel
              </button>
              <button
                type="button"
                class="inline-flex items-center gap-1.5 rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-medium text-white shadow-sm hover:bg-slate-800"
                @click="openCreate"
              >
                <Plus class="h-3.5 w-3.5" />
                Add
              </button>
            </div>
          </div>
        </div>
      </article>
    </div>

    <div
      v-if="showModal"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
      role="dialog"
      aria-modal="true"
      @click.self="showModal = false"
    >
      <div class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-xl bg-white p-5 shadow-xl">
        <h3 class="mb-4 text-lg font-semibold text-slate-900">{{ modalReadOnly ? "View" : editId ? "Edit" : "New" }} depreciation group</h3>
        <div class="space-y-3 text-sm">
          <label class="block">
            <span class="text-xs font-medium text-slate-600">Code</span>
            <input v-model="form.ldeValue" :disabled="modalReadOnly" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2" />
          </label>
          <label class="block">
            <span class="text-xs font-medium text-slate-600">Description</span>
            <input v-model="form.ldeDescription" :disabled="modalReadOnly" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2" />
          </label>
          <label class="block">
            <span class="text-xs font-medium text-slate-600">Depreciation %</span>
            <input v-model="form.deprRate" :disabled="modalReadOnly" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2" />
          </label>
          <label class="block">
            <span class="text-xs font-medium text-slate-600">Estimated life</span>
            <input v-model="form.estimatedLife" :disabled="modalReadOnly" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2" />
          </label>
          <label class="block">
            <span class="text-xs font-medium text-slate-600">Extended status label</span>
            <input v-model="form.statusDesc" :disabled="modalReadOnly" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2" />
          </label>
          <label class="block">
            <span class="text-xs font-medium text-slate-600">Row status (lookup)</span>
            <input v-model="form.ldeStatus" :disabled="modalReadOnly" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2" />
          </label>
        </div>
        <div class="mt-6 flex justify-end gap-2">
          <button type="button" class="rounded-lg border border-slate-200 px-4 py-2 text-sm" @click="showModal = false">Close</button>
          <button v-if="!modalReadOnly" type="button" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700" @click="saveModal">
            Save
          </button>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
