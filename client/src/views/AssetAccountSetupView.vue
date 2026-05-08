<script setup lang="ts">
/** Kerisi menu 1645 — Asset account setup listing (asset_depr_setup). */
import { computed, onMounted, ref, watch } from "vue";
import { Download, Eye, FileDown, FileSpreadsheet, Plus, Search, X } from "lucide-vue-next";

import AdminLayout from "@/layouts/AdminLayout.vue";
import FimsListTable, { type FimsColumn } from "@/components/fims/FimsListTable.vue";
import { useDatatableFeatures } from "@/composables/useDatatableFeatures";
import type { DatatableRefApi } from "@/composables/useDatatableFeatures";
import { getAssetAccountSetup, listAssetAccountSetup } from "@/api/cms";
import { useToast } from "@/composables/useToast";
import type { AssetAccountSetupDetail, AssetAccountSetupRow } from "@/types";

const PAGE_NAME = "Asset account setup";
const PAGE_BREADCRUMB = "Asset / Setup / General / Asset Account Setup";

const exportColumnLabels = [
  "Type",
  "Category",
  "Subcategory",
  "Asset acct",
  "Depr code",
  "Depr group",
  "Depr %",
  "Status",
] as const;

const toast = useToast();
const rows = ref<AssetAccountSetupRow[]>([]);
const loading = ref(false);
const total = ref(0);
const page = ref(1);
const limit = ref(10);
const q = ref("");
const sortDir = ref<"asc" | "desc">("asc");
const datatableRef = ref<DatatableRefApi | null>(null);

const totalPages = computed(() => (total.value ? Math.max(1, Math.ceil(total.value / limit.value)) : 1));
const startIdx = computed(() => (total.value === 0 ? 0 : (page.value - 1) * limit.value + 1));
const endIdx = computed(() => Math.min(page.value * limit.value, total.value));

const showModal = ref(false);
const detail = ref<AssetAccountSetupDetail | null>(null);

const columns: FimsColumn<AssetAccountSetupRow>[] = [
  { key: "no", label: "No", value: (r) => r.index },
  { key: "adsType", label: "Type", value: (r) => r.adsType },
  { key: "itmCategoryDisplay", label: "Category", value: (r) => r.itmCategoryDisplay },
  { key: "itmSubcategoryDisplay", label: "Subcategory", value: (r) => r.itmSubcategoryDisplay },
  { key: "acmAcctCodeDisplay", label: "Asset acct", value: (r) => r.acmAcctCodeDisplay, hiddenByDefault: false },
  { key: "adsDeprCodeDisplay", label: "Depr code", value: (r) => r.adsDeprCodeDisplay },
  { key: "deprGroupDisplay", label: "Depr group", value: (r) => r.deprGroupDisplay },
  { key: "adsDepreciationPercent", label: "Depr %", align: "right", value: (r) => r.adsDepreciationPercent },
  { key: "adsStatusDisplay", label: "Status", value: (r) => r.adsStatusDisplay },
  { key: "action", label: "Action" },
];

async function loadRows() {
  loading.value = true;
  try {
    const params = new URLSearchParams({
      page: String(page.value),
      limit: String(limit.value),
      sort_dir: sortDir.value,
      ...(q.value.trim() ? { q: q.value.trim() } : {}),
    });
    const res = await listAssetAccountSetup(`?${params.toString()}`);
    rows.value = res.data;
    total.value = Number(res.meta?.total ?? 0);
  } catch (e) {
    toast.error("Load failed", e instanceof Error ? e.message : "");
  } finally {
    loading.value = false;
  }
}

function toggleSort() {
  sortDir.value = sortDir.value === "asc" ? "desc" : "asc";
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

function onAddClick() {
  toast.info(
    "Read-only listing",
    "New account code setups are not created from this screen. Use Kerisi 1.0 or the upstream maintenance process if your site allows it.",
  );
}

const { handleDownloadPDF, handleDownloadCSV } = useDatatableFeatures({
  pageName: PAGE_NAME,
  apiDataPath: "/asset/account-setup",
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
    a.download = `Asset_Account_Setup_${new Date().toISOString().slice(0, 10)}.xlsx`;
    a.click();
    URL.revokeObjectURL(url);
    toast.success("Excel downloaded");
  } catch (e) {
    toast.error("Export failed", e instanceof Error ? e.message : "Excel export failed.");
  }
}

async function openView(id: number) {
  try {
    detail.value = (await getAssetAccountSetup(id)).data;
    showModal.value = true;
  } catch (e) {
    toast.error("Load failed", e instanceof Error ? e.message : "");
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
          <h2 class="text-base font-semibold text-slate-900">Account code setup</h2>
          <button type="button" class="rounded border border-slate-200 px-2 py-1 text-xs" @click="toggleSort">Sort ID {{ sortDir }}</button>
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
              <input v-model="q" type="search" placeholder="Search…" class="w-52 rounded-lg border border-slate-300 py-1.5 pl-8 pr-8 text-sm shadow-sm" />
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
            sort-by="id"
            sort-dir="asc"
            :row-key="(r) => r.adsDeprId"
            min-width="1200px"
            @sort="
              () => {
                /* sorting via header uses sortDir toggle instead */
              }
            "
          >
            <template #action="{ row }">
              <button type="button" class="rounded p-1 text-slate-500 hover:bg-slate-100" title="View" @click="openView((row as AssetAccountSetupRow).adsDeprId)">
                <Eye class="h-3.5 w-3.5" />
              </button>
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
                @click="onAddClick"
              >
                <Plus class="h-3.5 w-3.5" />
                Add
              </button>
            </div>
          </div>
        </div>
      </article>
    </div>

    <div v-if="showModal && detail" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="showModal = false">
      <div class="max-h-[90vh] w-full max-w-3xl overflow-y-auto rounded-xl bg-white p-5 shadow-xl text-sm">
        <h3 class="mb-4 text-lg font-semibold text-slate-900">Account setup detail</h3>
        <dl class="grid grid-cols-1 gap-x-8 gap-y-2 sm:grid-cols-2">
          <div><dt class="text-xs font-medium text-slate-500">Type</dt><dd class="font-medium text-slate-900">{{ detail.adsType }}</dd></div>
          <div><dt class="text-xs font-medium text-slate-500">Status</dt><dd>{{ detail.adsStatusDisplay }}</dd></div>
          <div class="sm:col-span-2"><dt class="text-xs font-medium text-slate-500">Category</dt><dd>{{ detail.itmCategoryDisplay }}</dd></div>
          <div class="sm:col-span-2"><dt class="text-xs font-medium text-slate-500">Subcategory</dt><dd>{{ detail.itmSubcategoryDisplay }}</dd></div>
          <div class="sm:col-span-2"><dt class="text-xs font-medium text-slate-500">Asset account</dt><dd>{{ detail.acmAcctCodeDisplay }}</dd></div>
          <div class="sm:col-span-2"><dt class="text-xs font-medium text-slate-500">Depreciation code</dt><dd>{{ detail.adsDeprCodeDisplay }}</dd></div>
          <div class="sm:col-span-2"><dt class="text-xs font-medium text-slate-500">Accumulated</dt><dd>{{ detail.accumDisplay }}</dd></div>
          <div class="sm:col-span-2"><dt class="text-xs font-medium text-slate-500">Disposal</dt><dd>{{ detail.disposalDisplay }}</dd></div>
          <div class="sm:col-span-2"><dt class="text-xs font-medium text-slate-500">Writeoff</dt><dd>{{ detail.writeoffDisplay }}</dd></div>
          <div class="sm:col-span-2"><dt class="text-xs font-medium text-slate-500">Depreciation group</dt><dd>{{ detail.deprGroupDisplay }}</dd></div>
          <div><dt class="text-xs font-medium text-slate-500">Estimated life</dt><dd>{{ detail.adsEstimatedLife }}</dd></div>
          <div><dt class="text-xs font-medium text-slate-500">Depr %</dt><dd>{{ detail.adsDepreciationPercent }}</dd></div>
          <div><dt class="text-xs font-medium text-slate-500">Residual</dt><dd>{{ detail.adsResidualValue }}</dd></div>
          <div><dt class="text-xs font-medium text-slate-500">Min / Max</dt><dd>{{ detail.minAmt }} / {{ detail.maxAmt }}</dd></div>
          <div class="sm:col-span-2"><dt class="text-xs font-medium text-slate-500">Notes</dt><dd class="whitespace-pre-wrap">{{ detail.notes || "—" }}</dd></div>
        </dl>
        <div class="mt-6 flex justify-end">
          <button type="button" class="rounded-lg border border-slate-200 px-4 py-2 text-sm" @click="showModal = false">Close</button>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
