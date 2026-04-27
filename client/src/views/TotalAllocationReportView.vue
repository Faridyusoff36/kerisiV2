<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref, watch } from "vue";
import { Download, FileDown, FileSpreadsheet, MoreVertical, Search, X } from "lucide-vue-next";
import AdminLayout from "@/layouts/AdminLayout.vue";
import { getTotalAllocationReportOptions, listTotalAllocationReport } from "@/api/cms";
import { useDatatableFeatures } from "@/composables/useDatatableFeatures";
import type { DatatableRefApi } from "@/composables/useDatatableFeatures";
import { useToast } from "@/composables/useToast";
import type {
  TotalAllocationOptions,
  TotalAllocationRow,
  TotalAllocationTotals,
} from "@/types";

const toast = useToast();

const rows = ref<TotalAllocationRow[]>([]);
const total = ref(0);
const totals = ref<TotalAllocationTotals>({ initial: 0, topup: 0, virement: 0, grand: 0 });
const page = ref(1);
const limit = ref(10);
const q = ref("");
const topFilter = ref<{ year: string }>({ year: "" });
const showSmartFilter = ref(false);
const smartFilter = ref<{
  fund: string;
  activity: string;
  oun: string;
  ccr: string;
  budgetCode: string;
}>({ fund: "", activity: "", oun: "", ccr: "", budgetCode: "" });

const options = ref<TotalAllocationOptions>({
  topFilter: { year: [] },
  smartFilter: { fund: [], activity: [], oun: [], ccr: [], budgetCode: [] },
});

async function loadOptions() {
  const res = await getTotalAllocationReportOptions();
  options.value = res.data;
  if (!topFilter.value.year && options.value.topFilter.year.length > 0) {
    topFilter.value.year = options.value.topFilter.year[0]!.id;
  }
}

async function loadRows() {
  if (!topFilter.value.year) {
    rows.value = [];
    total.value = 0;
    totals.value = { initial: 0, topup: 0, virement: 0, grand: 0 };
    return;
  }
  const params = new URLSearchParams({
    page: String(page.value),
    limit: String(limit.value),
    tf_year: topFilter.value.year,
    ...(q.value ? { q: q.value } : {}),
    ...(smartFilter.value.fund ? { sm_fund: smartFilter.value.fund } : {}),
    ...(smartFilter.value.activity ? { sm_activity: smartFilter.value.activity } : {}),
    ...(smartFilter.value.oun ? { sm_oun: smartFilter.value.oun } : {}),
    ...(smartFilter.value.ccr ? { sm_ccr: smartFilter.value.ccr } : {}),
    ...(smartFilter.value.budgetCode ? { sm_budget_code: smartFilter.value.budgetCode } : {}),
  });
  const res = await listTotalAllocationReport(`?${params.toString()}`);
  rows.value = res.data;
  total.value = Number(res.meta?.total ?? 0);
  if (res.meta?.totals) {
    totals.value = res.meta.totals as TotalAllocationTotals;
  }
}

function applyTopFilter() {
  page.value = 1;
  void loadRows();
}

function resetTopFilter() {
  topFilter.value = { year: options.value.topFilter.year[0]?.id ?? "" };
  page.value = 1;
  void loadRows();
}

function applySmartFilter() {
  showSmartFilter.value = false;
  page.value = 1;
  void loadRows();
}

function resetSmartFilter() {
  smartFilter.value = { fund: "", activity: "", oun: "", ccr: "", budgetCode: "" };
}

const exportColumns = ["Year", "Fund", "Activity", "OUN", "Cost Centre", "Budget Code", "Initial", "Top Up", "Virement", "Total"];

function fmtMoney(v: number | null | undefined): string {
  if (v === null || v === undefined) return "";
  return Number(v).toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function toExportRow(r: TotalAllocationRow): Record<string, string | number> {
  return {
    Year: r.rptYear ?? "",
    Fund: r.rptFund ?? "",
    Activity: r.rptActivity ?? "",
    OUN: r.rptOun ?? "",
    "Cost Centre": r.rptCcr ?? "",
    "Budget Code": r.rptBudgetCode ?? "",
    Initial: fmtMoney(r.rptInitial),
    "Top Up": fmtMoney(r.rptTopup),
    Virement: fmtMoney(r.rptVirement),
    Total: fmtMoney(r.rptTotal),
  };
}

const datatableRef = ref<DatatableRefApi | null>(null);
const { templateFileInputRef, onTemplateFileChange, handleDownloadPDF, handleDownloadCSV } = useDatatableFeatures({
  pageName: "Total Allocation Report",
  apiDataPath: "/budget/report/total-allocation",
  defaultExportColumns: exportColumns,
  getFilteredList: () => rows.value.map(toExportRow),
  datatableRef,
  searchKeyword: q,
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
    const ws = wb.addWorksheet("Total Allocation");
    ws.addRow(["No", ...exportColumns]);
    rows.value.forEach((r, idx) => {
      const row = toExportRow(r);
      ws.addRow([idx + 1, ...exportColumns.map((c) => row[c] ?? "")]);
    });
    ws.addRow([]);
    ws.addRow([
      "",
      "TOTAL",
      "",
      "",
      "",
      "",
      "",
      fmtMoney(totals.value.initial),
      fmtMoney(totals.value.topup),
      fmtMoney(totals.value.virement),
      fmtMoney(totals.value.grand),
    ]);
    const buf = await wb.xlsx.writeBuffer();
    const blob = new Blob([buf], { type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" });
    const url = URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.href = url;
    a.download = `Total_Allocation_${new Date().toISOString().slice(0, 10)}.xlsx`;
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

const totalPages = computed(() => Math.max(1, Math.ceil(total.value / Math.max(1, limit.value))));

onMounted(async () => {
  await loadOptions();
  await loadRows();
});
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
      <h1 class="page-title">Budget / Report / Total Allocation Report</h1>

      <article class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-4 py-3">
          <h1 class="text-base font-semibold text-slate-900">Top Filter</h1>
        </div>
        <div class="grid gap-3 p-4 md:grid-cols-3">
          <div>
            <label class="mb-1 block text-xs font-medium text-slate-600">Year <span class="text-rose-500">*</span></label>
            <select v-model="topFilter.year" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
              <option v-for="opt in options.topFilter.year" :key="opt.id" :value="opt.id">{{ opt.label }}</option>
            </select>
          </div>
          <div class="md:col-span-2 flex items-end justify-end gap-2">
            <button type="button" class="rounded-lg border border-slate-300 px-4 py-2 text-sm" @click="resetTopFilter">Reset</button>
            <button type="button" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white" @click="applyTopFilter">Apply</button>
          </div>
        </div>
      </article>

      <article class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
          <h1 class="text-base font-semibold text-slate-900">Listing</h1>
          <button class="rounded-lg p-2 text-slate-500 hover:bg-slate-100" aria-label="More">
            <MoreVertical class="h-4 w-4" />
          </button>
        </div>
        <div class="space-y-4 p-4">
          <div class="flex flex-wrap items-end justify-between gap-4">
            <div class="flex items-center gap-2">
              <label class="text-xs font-medium text-slate-600">Display</label>
              <select v-model.number="limit" class="rounded-lg border border-slate-300 px-2 py-1.5 text-sm" @change="page = 1; void loadRows()">
                <option v-for="n in [5, 10, 25, 50, 100]" :key="n" :value="n">{{ n }}</option>
              </select>
            </div>
            <div class="flex items-center gap-2">
              <button type="button" class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium hover:bg-slate-50" @click="showSmartFilter = true">Filter</button>
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
                <button v-if="q" type="button" class="absolute right-1 top-1/2 -translate-y-1/2 rounded p-0.5 text-slate-400 hover:bg-slate-100" aria-label="Clear search" @click="q = ''">
                  <X class="h-3.5 w-3.5" />
                </button>
              </div>
            </div>
          </div>
          <div class="overflow-x-auto rounded-lg border border-slate-200">
            <div :class="rows.length > 10 ? 'max-h-[480px] overflow-y-auto' : ''">
              <table class="w-full min-w-[1100px] text-sm">
                <thead class="sticky top-0 bg-slate-50">
                  <tr class="border-b border-slate-200 text-left">
                    <th class="px-3 py-2 text-xs font-semibold uppercase">No</th>
                    <th class="px-3 py-2 text-xs font-semibold uppercase">Year</th>
                    <th class="px-3 py-2 text-xs font-semibold uppercase">Fund</th>
                    <th class="px-3 py-2 text-xs font-semibold uppercase">Activity</th>
                    <th class="px-3 py-2 text-xs font-semibold uppercase">OUN</th>
                    <th class="px-3 py-2 text-xs font-semibold uppercase">Cost Centre</th>
                    <th class="px-3 py-2 text-xs font-semibold uppercase">Budget Code</th>
                    <th class="px-3 py-2 text-right text-xs font-semibold uppercase">Initial</th>
                    <th class="px-3 py-2 text-right text-xs font-semibold uppercase">Top Up</th>
                    <th class="px-3 py-2 text-right text-xs font-semibold uppercase">Virement</th>
                    <th class="px-3 py-2 text-right text-xs font-semibold uppercase">Total</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="row in rows" :key="`${row.rptYear}-${row.rptFund}-${row.rptActivity}-${row.rptOun}-${row.rptCcr}-${row.rptBudgetCode}-${row.index}`" class="border-b border-slate-100 hover:bg-slate-50">
                    <td class="px-3 py-2">{{ row.index }}</td>
                    <td class="px-3 py-2">{{ row.rptYear ?? "—" }}</td>
                    <td class="px-3 py-2">{{ row.rptFund ?? "—" }}</td>
                    <td class="px-3 py-2">{{ row.rptActivity ?? "—" }}</td>
                    <td class="px-3 py-2">{{ row.rptOun ?? "—" }}</td>
                    <td class="px-3 py-2">{{ row.rptCcr ?? "—" }}</td>
                    <td class="px-3 py-2">{{ row.rptBudgetCode ?? "—" }}</td>
                    <td class="px-3 py-2 text-right">{{ fmtMoney(row.rptInitial) }}</td>
                    <td class="px-3 py-2 text-right">{{ fmtMoney(row.rptTopup) }}</td>
                    <td class="px-3 py-2 text-right">{{ fmtMoney(row.rptVirement) }}</td>
                    <td class="px-3 py-2 text-right font-medium">{{ fmtMoney(row.rptTotal) }}</td>
                  </tr>
                  <tr v-if="rows.length === 0">
                    <td colspan="11" class="px-3 py-6 text-center text-xs text-slate-500">No data</td>
                  </tr>
                </tbody>
                <tfoot v-if="rows.length > 0" class="bg-slate-50">
                  <tr class="border-t-2 border-slate-200 font-semibold">
                    <td class="px-3 py-2" colspan="7">Total</td>
                    <td class="px-3 py-2 text-right">{{ fmtMoney(totals.initial) }}</td>
                    <td class="px-3 py-2 text-right">{{ fmtMoney(totals.topup) }}</td>
                    <td class="px-3 py-2 text-right">{{ fmtMoney(totals.virement) }}</td>
                    <td class="px-3 py-2 text-right">{{ fmtMoney(totals.grand) }}</td>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>
          <div class="flex flex-wrap items-center justify-between gap-2 border-t border-slate-100 pt-3">
            <div class="text-xs text-slate-500">Page {{ page }} of {{ totalPages }} · {{ total }} record{{ total === 1 ? '' : 's' }}</div>
            <div class="flex items-center gap-2">
              <button
                type="button"
                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium hover:bg-slate-50 disabled:opacity-50"
                :disabled="page <= 1"
                @click="page = Math.max(1, page - 1); void loadRows()"
              >Prev</button>
              <button
                type="button"
                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium hover:bg-slate-50 disabled:opacity-50"
                :disabled="page >= totalPages"
                @click="page = Math.min(totalPages, page + 1); void loadRows()"
              >Next</button>
              <button type="button" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium hover:bg-slate-50" @click="handleDownloadPDF">
                <Download class="h-3.5 w-3.5" />PDF
              </button>
              <button type="button" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium hover:bg-slate-50" @click="handleDownloadCSV">
                <FileDown class="h-3.5 w-3.5" />CSV
              </button>
              <button type="button" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium hover:bg-slate-50" @click="exportExcel">
                <FileSpreadsheet class="h-3.5 w-3.5" />Excel
              </button>
            </div>
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
        <div class="w-full max-w-2xl rounded-lg border border-slate-200 bg-white shadow-2xl">
          <div class="border-b border-slate-100 px-4 py-3">
            <h3 class="text-base font-semibold text-slate-900">Filter</h3>
          </div>
          <div class="grid gap-3 p-4 md:grid-cols-2">
            <div>
              <label class="mb-1 block text-xs font-medium text-slate-600">Fund</label>
              <select v-model="smartFilter.fund" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">Any</option>
                <option v-for="opt in options.smartFilter.fund" :key="opt.id" :value="opt.id">{{ opt.label }}</option>
              </select>
            </div>
            <div>
              <label class="mb-1 block text-xs font-medium text-slate-600">Activity</label>
              <select v-model="smartFilter.activity" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">Any</option>
                <option v-for="opt in options.smartFilter.activity" :key="opt.id" :value="opt.id">{{ opt.label }}</option>
              </select>
            </div>
            <div>
              <label class="mb-1 block text-xs font-medium text-slate-600">OUN</label>
              <select v-model="smartFilter.oun" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">Any</option>
                <option v-for="opt in options.smartFilter.oun" :key="opt.id" :value="opt.id">{{ opt.label }}</option>
              </select>
            </div>
            <div>
              <label class="mb-1 block text-xs font-medium text-slate-600">Cost Centre</label>
              <select v-model="smartFilter.ccr" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">Any</option>
                <option v-for="opt in options.smartFilter.ccr" :key="opt.id" :value="opt.id">{{ opt.label }}</option>
              </select>
            </div>
            <div>
              <label class="mb-1 block text-xs font-medium text-slate-600">Budget Code</label>
              <select v-model="smartFilter.budgetCode" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">Any</option>
                <option v-for="opt in options.smartFilter.budgetCode" :key="opt.id" :value="opt.id">{{ opt.label }}</option>
              </select>
            </div>
          </div>
          <div class="flex justify-end gap-2 border-t border-slate-100 px-4 py-3">
            <button type="button" class="rounded-lg border border-slate-300 px-4 py-2 text-sm" @click="resetSmartFilter">Reset</button>
            <button type="button" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white" @click="applySmartFilter">OK</button>
          </div>
        </div>
      </div>
    </Teleport>
  </AdminLayout>
</template>
