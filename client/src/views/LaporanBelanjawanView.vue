<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref, watch } from "vue";
import { Download, FileDown, FileSpreadsheet, MoreVertical, Search, X } from "lucide-vue-next";
import AdminLayout from "@/layouts/AdminLayout.vue";
import { getLaporanBelanjawanOptions, listLaporanBelanjawan } from "@/api/cms";
import { useDatatableFeatures } from "@/composables/useDatatableFeatures";
import type { DatatableRefApi } from "@/composables/useDatatableFeatures";
import { useToast } from "@/composables/useToast";
import type {
  LaporanBelanjawanOptions,
  LaporanBelanjawanRow,
  LaporanBelanjawanTotals,
} from "@/types";

const toast = useToast();

const rows = ref<LaporanBelanjawanRow[]>([]);
const total = ref(0);
const totals = ref<LaporanBelanjawanTotals>({
  initial: 0,
  additional: 0,
  virement: 0,
  topup: 0,
  allocated: 0,
  expenses: 0,
  balance: 0,
});

const page = ref(1);
const limit = ref(10);
const q = ref("");
const topFilter = ref<{
  year: string;
  fund: string;
  accountSeries: string;
  dateFrom: string;
  dateTo: string;
}>({ year: "", fund: "", accountSeries: "", dateFrom: "", dateTo: "" });

const showSmartFilter = ref(false);
const smartFilter = ref<{ fund: string; accountSeries: string }>({
  fund: "",
  accountSeries: "",
});

const options = ref<LaporanBelanjawanOptions>({
  topFilter: { year: [], fund: [], accountSeries: [] },
  smartFilter: { fund: [], accountSeries: [] },
});

async function loadOptions() {
  const res = await getLaporanBelanjawanOptions();
  options.value = res.data;
  if (!topFilter.value.year && options.value.topFilter.year.length > 0) {
    topFilter.value.year = options.value.topFilter.year[0]!.id;
  }
}

async function loadRows() {
  if (!topFilter.value.year) {
    rows.value = [];
    total.value = 0;
    return;
  }
  const params = new URLSearchParams({
    page: String(page.value),
    limit: String(limit.value),
    tf_year: topFilter.value.year,
    ...(topFilter.value.fund ? { tf_fund: topFilter.value.fund } : {}),
    ...(topFilter.value.accountSeries ? { tf_account_series: topFilter.value.accountSeries } : {}),
    ...(topFilter.value.dateFrom ? { tf_date_from: topFilter.value.dateFrom } : {}),
    ...(topFilter.value.dateTo ? { tf_date_to: topFilter.value.dateTo } : {}),
    ...(q.value ? { q: q.value } : {}),
    ...(smartFilter.value.fund ? { sm_fund: smartFilter.value.fund } : {}),
    ...(smartFilter.value.accountSeries ? { sm_account_series: smartFilter.value.accountSeries } : {}),
  });
  const res = await listLaporanBelanjawan(`?${params.toString()}`);
  rows.value = res.data;
  total.value = Number(res.meta?.total ?? 0);
  if (res.meta?.totals) {
    totals.value = res.meta.totals as LaporanBelanjawanTotals;
  }
}

function applyTopFilter() {
  page.value = 1;
  void loadRows();
}

function resetTopFilter() {
  topFilter.value = {
    year: options.value.topFilter.year[0]?.id ?? "",
    fund: "",
    accountSeries: "",
    dateFrom: "",
    dateTo: "",
  };
  page.value = 1;
  void loadRows();
}

function applySmartFilter() {
  showSmartFilter.value = false;
  page.value = 1;
  void loadRows();
}

function resetSmartFilter() {
  smartFilter.value = { fund: "", accountSeries: "" };
}

function fmtMoney(v: number | null | undefined): string {
  if (v === null || v === undefined) return "";
  return Number(v).toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

const exportColumns = [
  "Fund",
  "Activity",
  "Cost Centre",
  "Account Series",
  "Account Code",
  "Glacct",
  "Opening",
  "Initial",
  "Additional",
  "Virement",
  "Top Up",
  "Allocated",
  "Locked",
  "Pre Request",
  "Request",
  "Commit",
  "Expenses",
  "Balance",
  "Expenses %",
];

function toExportRow(r: LaporanBelanjawanRow): Record<string, string | number> {
  return {
    Fund: r.fund ?? "",
    Activity: r.activity ?? "",
    "Cost Centre": r.costcentre ?? "",
    "Account Series": r.accountSeries ?? "",
    "Account Code": r.account ?? "",
    Glacct: r.glacctCode ?? "",
    Opening: fmtMoney(r.opening),
    Initial: fmtMoney(r.initial),
    Additional: fmtMoney(r.additional),
    Virement: fmtMoney(r.virement),
    "Top Up": fmtMoney(r.topup),
    Allocated: fmtMoney(r.allocated),
    Locked: fmtMoney(r.locked),
    "Pre Request": fmtMoney(r.preRequest),
    Request: fmtMoney(r.request),
    Commit: fmtMoney(r.commit),
    Expenses: fmtMoney(r.expenses),
    Balance: fmtMoney(r.balance),
    "Expenses %": r.expensesPercentage,
  };
}

const datatableRef = ref<DatatableRefApi | null>(null);
const { templateFileInputRef, onTemplateFileChange, handleDownloadPDF, handleDownloadCSV } = useDatatableFeatures({
  pageName: "Laporan Belanjawan",
  apiDataPath: "/budget/report/laporan-belanjawan",
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
    const ws = wb.addWorksheet("Laporan Belanjawan");
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
    a.download = `Laporan_Belanjawan_${new Date().toISOString().slice(0, 10)}.xlsx`;
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
      <h1 class="page-title">Budget / Report / Laporan Belanjawan</h1>

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
          <div>
            <label class="mb-1 block text-xs font-medium text-slate-600">Statement Date</label>
            <input
              :value="new Date().toISOString().slice(0, 10)"
              type="text"
              disabled
              class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-500"
            />
          </div>
          <div>
            <label class="mb-1 block text-xs font-medium text-slate-600">Fund</label>
            <select v-model="topFilter.fund" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
              <option value="">Any</option>
              <option v-for="opt in options.topFilter.fund" :key="opt.id" :value="opt.id">{{ opt.label }}</option>
            </select>
          </div>
          <div>
            <label class="mb-1 block text-xs font-medium text-slate-600">Date From</label>
            <input v-model="topFilter.dateFrom" type="date" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
          </div>
          <div>
            <label class="mb-1 block text-xs font-medium text-slate-600">Date To</label>
            <input v-model="topFilter.dateTo" type="date" :min="topFilter.dateFrom || undefined" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
          </div>
          <div>
            <label class="mb-1 block text-xs font-medium text-slate-600">Account Code Series</label>
            <select v-model="topFilter.accountSeries" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
              <option value="">Any</option>
              <option v-for="opt in options.topFilter.accountSeries" :key="opt.id" :value="opt.id">{{ opt.label }}</option>
            </select>
          </div>
          <div class="md:col-span-3 flex items-end justify-end gap-2">
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
              <table class="admin-table-kitchen w-full min-w-[1800px] text-sm">
                <thead class="admin-table-thead-sticky">
                  <tr class="border-b border-slate-200 text-left">
                    <th class="px-3 py-2 text-xs font-semibold uppercase">No</th>
                    <th class="px-3 py-2 text-xs font-semibold uppercase">Fund</th>
                    <th class="px-3 py-2 text-xs font-semibold uppercase">Activity</th>
                    <th class="px-3 py-2 text-xs font-semibold uppercase">Cost Centre</th>
                    <th class="px-3 py-2 text-xs font-semibold uppercase">A. Series</th>
                    <th class="px-3 py-2 text-xs font-semibold uppercase">A. Code</th>
                    <th class="px-3 py-2 text-right text-xs font-semibold uppercase">Opening</th>
                    <th class="px-3 py-2 text-right text-xs font-semibold uppercase">Initial</th>
                    <th class="px-3 py-2 text-right text-xs font-semibold uppercase">Additional</th>
                    <th class="px-3 py-2 text-right text-xs font-semibold uppercase">Virement</th>
                    <th class="px-3 py-2 text-right text-xs font-semibold uppercase">Top Up</th>
                    <th class="px-3 py-2 text-right text-xs font-semibold uppercase">Allocated</th>
                    <th class="px-3 py-2 text-right text-xs font-semibold uppercase">Locked</th>
                    <th class="px-3 py-2 text-right text-xs font-semibold uppercase">Pre Req.</th>
                    <th class="px-3 py-2 text-right text-xs font-semibold uppercase">Request</th>
                    <th class="px-3 py-2 text-right text-xs font-semibold uppercase">Commit</th>
                    <th class="px-3 py-2 text-right text-xs font-semibold uppercase">Expenses</th>
                    <th class="px-3 py-2 text-right text-xs font-semibold uppercase">Balance</th>
                    <th class="px-3 py-2 text-right text-xs font-semibold uppercase">Exp. %</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(row, i) in rows" :key="`${row.fund}-${row.activity}-${row.costcentre}-${row.account}-${i}`" class="border-b border-slate-100 hover:bg-slate-50">
                    <td class="px-3 py-2">{{ row.index }}</td>
                    <td class="px-3 py-2">{{ row.fund ?? "—" }}</td>
                    <td class="px-3 py-2">{{ row.activity ?? "—" }}</td>
                    <td class="px-3 py-2">{{ row.costcentre ?? "—" }}</td>
                    <td class="px-3 py-2">{{ row.accountSeries ?? "—" }}</td>
                    <td class="px-3 py-2">{{ row.account ?? "—" }}</td>
                    <td class="px-3 py-2 text-right">{{ fmtMoney(row.opening) }}</td>
                    <td class="px-3 py-2 text-right">{{ fmtMoney(row.initial) }}</td>
                    <td class="px-3 py-2 text-right">{{ fmtMoney(row.additional) }}</td>
                    <td class="px-3 py-2 text-right">{{ fmtMoney(row.virement) }}</td>
                    <td class="px-3 py-2 text-right">{{ fmtMoney(row.topup) }}</td>
                    <td class="px-3 py-2 text-right font-medium">{{ fmtMoney(row.allocated) }}</td>
                    <td class="px-3 py-2 text-right">{{ fmtMoney(row.locked) }}</td>
                    <td class="px-3 py-2 text-right">{{ fmtMoney(row.preRequest) }}</td>
                    <td class="px-3 py-2 text-right">{{ fmtMoney(row.request) }}</td>
                    <td class="px-3 py-2 text-right">{{ fmtMoney(row.commit) }}</td>
                    <td class="px-3 py-2 text-right">{{ fmtMoney(row.expenses) }}</td>
                    <td class="px-3 py-2 text-right">{{ fmtMoney(row.balance) }}</td>
                    <td class="px-3 py-2 text-right">{{ row.expensesPercentage.toFixed(2) }}</td>
                  </tr>
                  <tr v-if="rows.length === 0">
                    <td colspan="19" class="px-3 py-6 text-center text-xs text-slate-500">No data</td>
                  </tr>
                </tbody>
                <tfoot v-if="rows.length > 0" class="bg-slate-50">
                  <tr class="border-t-2 border-slate-200 font-semibold">
                    <td class="px-3 py-2" colspan="7">Total</td>
                    <td class="px-3 py-2 text-right">{{ fmtMoney(totals.initial) }}</td>
                    <td class="px-3 py-2 text-right">{{ fmtMoney(totals.additional) }}</td>
                    <td class="px-3 py-2 text-right">{{ fmtMoney(totals.virement) }}</td>
                    <td class="px-3 py-2 text-right">{{ fmtMoney(totals.topup) }}</td>
                    <td class="px-3 py-2 text-right">{{ fmtMoney(totals.allocated) }}</td>
                    <td colspan="4"></td>
                    <td class="px-3 py-2 text-right">{{ fmtMoney(totals.expenses) }}</td>
                    <td class="px-3 py-2 text-right">{{ fmtMoney(totals.balance) }}</td>
                    <td></td>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>
          <div class="flex flex-wrap items-center justify-between gap-2 border-t border-slate-100 pt-3">
            <div class="text-xs text-slate-500">Showing {{ total === 0 ? 0 : (page - 1) * limit + 1 }}-{{ Math.min(page * limit, total) }} of {{ total }}</div>
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
        <div class="w-full max-w-lg rounded-lg border border-slate-200 bg-white shadow-2xl">
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
              <label class="mb-1 block text-xs font-medium text-slate-600">Account Series</label>
              <select v-model="smartFilter.accountSeries" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">Any</option>
                <option v-for="opt in options.smartFilter.accountSeries" :key="opt.id" :value="opt.id">{{ opt.label }}</option>
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
