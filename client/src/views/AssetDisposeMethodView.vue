<script setup lang="ts">
/**
 * Kerisi MENUID 1564 / PAGEID 1279 — Asset Dispose Method (kitchen-sink datatable + modal CRUD).
 */
import { computed, onMounted, onUnmounted, ref, watch } from "vue";
import { Download, Eye, FileDown, FileSpreadsheet, MoreVertical, Pencil, Plus, Search, X } from "lucide-vue-next";

import AdminLayout from "@/layouts/AdminLayout.vue";
import FimsListTable, { type FimsColumn } from "@/components/fims/FimsListTable.vue";
import { useDatatableFeatures } from "@/composables/useDatatableFeatures";
import type { DatatableRefApi } from "@/composables/useDatatableFeatures";
import {
  createAssetDisposeMethod,
  getAssetDisposeMethod,
  listAssetDisposeMethods,
  updateAssetDisposeMethod,
} from "@/api/cms";
import { useToast } from "@/composables/useToast";
import type { AssetDisposeMethodInput, AssetDisposeMethodRow } from "@/types";

const PAGE_NAME = "Dispose method";
const PAGE_BREADCRUMB = "Asset / Setup / General / Asset Dispose Method";

const toast = useToast();

const rows = ref<AssetDisposeMethodRow[]>([]);
const loading = ref(false);
const total = ref(0);
const page = ref(1);
const limit = ref(15);
const q = ref("");
const sortBy = ref("adt_code");
const sortDir = ref<"asc" | "desc">("asc");

const datatableRef = ref<DatatableRefApi | null>(null);
const overflowOpen = ref(false);
const overflowRoot = ref<HTMLElement | null>(null);

const showModal = ref(false);
const modalReadOnly = ref(false);
const editId = ref<number | null>(null);
const form = ref<AssetDisposeMethodInput>({
  adtCode: "",
  adtName: "",
  adtStatus: 1,
});

const totalPages = computed(() => (total.value ? Math.max(1, Math.ceil(total.value / limit.value)) : 1));
const startIdx = computed(() => (total.value === 0 ? 0 : (page.value - 1) * limit.value + 1));
const endIdx = computed(() => Math.min(page.value * limit.value, total.value));

const columns: FimsColumn<AssetDisposeMethodRow>[] = [
  { key: "no", label: "No", value: (r) => r.index },
  {
    key: "adtCode",
    label: "Dispose Code",
    sortable: true,
    sortKey: "adt_code",
    hideable: true,
    value: (r) => r.adtCode ?? "",
  },
  {
    key: "adtName",
    label: "Dispose Description",
    sortable: true,
    sortKey: "adt_name",
    hideable: true,
    value: (r) => r.adtName ?? "",
  },
  {
    key: "adtStatus",
    label: "Dispose Status",
    sortable: true,
    sortKey: "adt_status",
    hideable: true,
    value: (r) => r.adtStatus ?? "",
  },
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
    const res = await listAssetDisposeMethods(`?${params.toString()}`);
    rows.value = res.data;
    total.value = Number(res.meta?.total ?? 0);
  } catch (e) {
    toast.error("Load failed", e instanceof Error ? e.message : "Unable to load dispose methods.");
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

async function openView(id: number) {
  modalReadOnly.value = true;
  editId.value = id;
  try {
    const res = await getAssetDisposeMethod(id);
    form.value = {
      adtCode: res.data.adtCode ?? "",
      adtName: res.data.adtName ?? "",
      adtStatus: Number(res.data.adtStatus ?? 1),
    };
    showModal.value = true;
  } catch (e) {
    toast.error("Load failed", e instanceof Error ? e.message : "Unable to load record.");
  }
}

function openCreate() {
  modalReadOnly.value = false;
  editId.value = null;
  form.value = { adtCode: "", adtName: "", adtStatus: 1 };
  showModal.value = true;
}

async function openEdit(id: number) {
  modalReadOnly.value = false;
  editId.value = id;
  try {
    const res = await getAssetDisposeMethod(id);
    form.value = {
      adtCode: res.data.adtCode ?? "",
      adtName: res.data.adtName ?? "",
      adtStatus: Number(res.data.adtStatus ?? 1),
    };
    showModal.value = true;
  } catch (e) {
    toast.error("Load failed", e instanceof Error ? e.message : "Unable to load record.");
  }
}

async function saveModal() {
  if (modalReadOnly.value) return;

  const code = form.value.adtCode?.trim() ?? "";
  const name = form.value.adtName?.trim() ?? "";
  if (!code || !name) {
    toast.error("Validation", "Dispose code and description are required.");
    return;
  }

  try {
    if (editId.value == null) {
      await createAssetDisposeMethod({
        adtCode: code,
        adtName: name,
        adtStatus: Number(form.value.adtStatus) === 0 ? 0 : 1,
      });
      toast.success("Saved");
    } else {
      await updateAssetDisposeMethod(editId.value, {
        adtCode: code,
        adtName: name,
        adtStatus: Number(form.value.adtStatus) === 0 ? 0 : 1,
      });
      toast.success("Updated");
    }
    showModal.value = false;
    await loadRows();
  } catch (e) {
    const msg = e instanceof Error ? e.message : "Unable to save.";
    if (msg.includes("DUPLICATE") || msg.toLowerCase().includes("already exists")) {
      toast.error("Duplicate", "Dispose code already exists.");
      return;
    }
    toast.error("Process error", msg);
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
  pageName: PAGE_NAME,
  apiDataPath: "/asset/dispose-methods",
  defaultExportColumns: ["Dispose Code", "Dispose Description", "Dispose Status"],
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
    a.download = `Asset_Dispose_Method_${new Date().toISOString().slice(0, 10)}.xlsx`;
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

onMounted(() => {
  void loadRows();
  document.addEventListener("click", onClickOutside);
});

onUnmounted(() => {
  if (qSearchDebounce) clearTimeout(qSearchDebounce);
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
          <div>
            <h1 class="text-base font-semibold text-slate-900">{{ PAGE_NAME }}</h1>
          </div>
          <div ref="overflowRoot" class="relative">
            <button type="button" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100" @click.stop="overflowOpen = !overflowOpen">
              <MoreVertical class="h-4 w-4" />
            </button>
            <div v-if="overflowOpen" class="absolute right-0 z-30 mt-1 w-44 rounded-lg border border-slate-200 bg-white py-1 shadow-lg" @click.stop>
              <button
                type="button"
                class="block w-full px-3 py-2 text-left text-xs text-slate-700 hover:bg-slate-50"
                @click="
                  overflowOpen = false;
                  handleSaveTemplate();
                "
              >
                Save template
              </button>
              <button
                type="button"
                class="block w-full px-3 py-2 text-left text-xs text-slate-700 hover:bg-slate-50"
                @click="
                  overflowOpen = false;
                  handleLoadTemplate();
                "
              >
                Load template
              </button>
              <button
                v-if="isGrouped"
                type="button"
                class="block w-full px-3 py-2 text-left text-xs text-slate-700 hover:bg-slate-50"
                @click="
                  overflowOpen = false;
                  handleUngroupList();
                "
              >
                Ungroup list
              </button>
              <button
                v-else
                type="button"
                class="block w-full px-3 py-2 text-left text-xs text-slate-700 hover:bg-slate-50"
                @click="
                  overflowOpen = false;
                  handleGroupList();
                "
              >
                Group list
              </button>
            </div>
          </div>
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
                  @keyup.enter="
                    page = 1;
                    void loadRows();
                  "
                />
                <button
                  v-if="q"
                  type="button"
                  class="absolute right-1 top-1/2 -translate-y-1/2 rounded p-0.5 text-slate-400 hover:bg-slate-100"
                  @click="
                    q = '';
                    page = 1;
                    loadRows();
                  "
                >
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
            :row-key="(r) => r.adtId"
            :group-by="(r) => `Status ${r.adtStatus ?? ''}`"
            min-width="720px"
            @sort="onSort"
          >
            <template #action="{ row }">
              <span class="inline-flex gap-1">
                <button
                  type="button"
                  class="rounded p-1 text-slate-500 hover:bg-slate-100"
                  title="View"
                  @click="openView((row as AssetDisposeMethodRow).adtId)"
                >
                  <Eye class="h-3.5 w-3.5" />
                </button>
                <button
                  type="button"
                  class="rounded p-1 text-slate-500 hover:bg-slate-100"
                  title="Edit"
                  @click="openEdit((row as AssetDisposeMethodRow).adtId)"
                >
                  <Pencil class="h-3.5 w-3.5" />
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

      <Teleport to="body">
        <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm" @click.self="showModal = false">
          <div class="w-full max-w-lg rounded-lg border border-slate-200 bg-white shadow-2xl">
            <div class="border-b border-slate-100 px-4 py-3">
              <h3 class="text-base font-semibold text-slate-900">
                {{ modalReadOnly ? "Dispose method — view" : editId == null ? "Dispose method — add" : "Dispose method — edit" }}
              </h3>
            </div>
            <div class="space-y-4 p-4">
              <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Dispose Code *</label>
                <input
                  v-model="form.adtCode"
                  type="text"
                  class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm uppercase"
                  :disabled="modalReadOnly"
                  required
                />
              </div>
              <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Dispose Description *</label>
                <textarea v-model="form.adtName" rows="3" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" :disabled="modalReadOnly" required />
              </div>
              <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Dispose Status *</label>
                <select v-model.number="form.adtStatus" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" :disabled="modalReadOnly">
                  <option :value="1">ACTIVE</option>
                  <option :value="0">INACTIVE</option>
                </select>
              </div>
            </div>
            <div class="flex justify-end gap-2 border-t border-slate-100 px-4 py-3">
              <button type="button" class="rounded-lg border border-red-300 px-4 py-2 text-sm text-red-600" @click="showModal = false">Cancel</button>
              <button
                v-if="!modalReadOnly"
                type="button"
                class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white"
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
