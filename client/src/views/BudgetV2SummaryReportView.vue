<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref, watch } from "vue";
import { Download, FileDown, FileSpreadsheet, MoreVertical, Search, X } from "lucide-vue-next";
import AdminLayout from "@/layouts/AdminLayout.vue";
import { getUmumAllocationPtjOptions, postBudgetV2BudgetSummaryListing } from "@/api/cms";
import { useDatatableFeatures } from "@/composables/useDatatableFeatures";
import type { DatatableRefApi } from "@/composables/useDatatableFeatures";
import { useToast } from "@/composables/useToast";
import type { BudgetV2BudgetSummaryRow, UmumAllocationPtjOptions } from "@/types";

/** MENUID 3382 | 3389 | 3393 — legacy `V2_BUDGET_SUMMARY_API` dt_listing. */

const props = defineProps<{ menuId: 3382 | 3389 | 3393 }>();

const PAGE_HEADINGS: Record<3382 | 3389 | 3393, string> = {
  3382: "Budget / Report / Budget Report by Date / Budget Summary By Date (WBR068)",
  3389: "Budget / Report / Budget Report by Date / Budget Variation By Date (WBR069)",
  3393: "Budget / Report / Budget Summary By Date / Budget Summary By PTJ (WBR071) (OLD)",
};

const EXPORT_PAGE_LABEL: Record<3382 | 3389 | 3393, string> = {
  3382: "Budget Summary By Date WBR068",
  3389: "Budget Variation By Date WBR069",
  3393: "Budget Summary By PTJ WBR071 OLD",
};

const pageHeading = computed(() => PAGE_HEADINGS[props.menuId]);

const toast = useToast();

const rows = ref<BudgetV2BudgetSummaryRow[]>([]);
/** Full result set before client search/page. */
const allRows = ref<BudgetV2BudgetSummaryRow[]>([]);
const aggregatePct = ref<string | null>(null);
const page = ref(1);
const limit = ref(10);
const q = ref("");

const options = ref<UmumAllocationPtjOptions>({
  topFilter: { year: [], ptj: [], activity: [] },
});

const topFilter = ref({
  bdgYear: "",
  ounCode: "",
  tfActivityGroup: "",
  tfActivitySubgroup: "",
  ftyFundType: "",
  ccrCostcentre: "",
  cpaProjectNo: "",
  dateFrom: "",
  dateTo: "",
});

async function loadOptions() {
  try {
    const res = await getUmumAllocationPtjOptions();
    options.value = res.data;
    if (!topFilter.value.bdgYear && options.value.topFilter.year.length > 0) {
      topFilter.value.bdgYear = options.value.topFilter.year[0]!.id;
    }
  } catch (e) {
    toast.error("Load failed", e instanceof Error ? e.message : "Could not load filter options.");
  }
}

function buildPayload(): Record<string, string> {
  const p: Record<string, string> = {};
  if (topFilter.value.bdgYear) p.bdg_year = topFilter.value.bdgYear;
  if (topFilter.value.ounCode) p.oun_code = topFilter.value.ounCode;
  if (topFilter.value.ftyFundType.trim()) p.fty_fund_type = topFilter.value.ftyFundType.trim();
  if (topFilter.value.ccrCostcentre.trim()) p.ccr_costcentre = topFilter.value.ccrCostcentre.trim();
  if (topFilter.value.cpaProjectNo.trim()) p.cpa_project_no = topFilter.value.cpaProjectNo.trim();
  if (topFilter.value.tfActivityGroup.trim()) p.tf_activity_group = topFilter.value.tfActivityGroup.trim();
  if (topFilter.value.tfActivitySubgroup.trim()) p.tf_activity_subgroup = topFilter.value.tfActivitySubgroup.trim();
  if (topFilter.value.dateFrom.trim()) p.bgt_trans_date_from = topFilter.value.dateFrom.trim();
  if (topFilter.value.dateTo.trim()) p.bgt_trans_date_to = topFilter.value.dateTo.trim();
  return p;
}

async function loadRows() {
  if (!topFilter.value.bdgYear) {
    toast.info("Year required", "Select a budget year before searching.");
    return;
  }
  try {
    const res = await postBudgetV2BudgetSummaryListing(buildPayload());
    allRows.value = res.data;
    aggregatePct.value = (res.meta?.aggregateExpensesPercent as string | undefined | null) ?? null;
    page.value = 1;
    applyClientFilter();
  } catch (e) {
    toast.error("Load failed", e instanceof Error ? e.message : "Could not load report.");
  }
}

function applyClientFilter() {
  const needle = q.value.trim().toLowerCase();
  if (!needle) {
    rows.value = allRows.value;
    return;
  }
  rows.value = allRows.value.filter((r) => {
    const blob = [
      r.acctCode,
      r.acmAcctDesc,
      r.pTJ,
      r.costcentre,
      r.description,
      r.fundTypeDisplay,
      r.projectNo,
    ]
      .filter(Boolean)
      .join(" ")
      .toLowerCase();
    return blob.includes(needle);
  });
}

function applyTopFilter() {
  void loadRows();
}

function resetTopFilter() {
  topFilter.value = {
    bdgYear: options.value.topFilter.year[0]?.id ?? "",
    ounCode: "",
    tfActivityGroup: "",
    tfActivitySubgroup: "",
    ftyFundType: "",
    ccrCostcentre: "",
    cpaProjectNo: "",
    dateFrom: "",
    dateTo: "",
  };
  q.value = "";
  void loadRows();
}

const currency = new Intl.NumberFormat("en-MY", {
  minimumFractionDigits: 2,
  maximumFractionDigits: 2,
});

function fmtMoney(v: unknown): string {
  if (v === null || v === undefined || v === "") return "";
  const n = typeof v === "number" ? v : Number(v);
  if (!Number.isFinite(n)) return "";
  return currency.format(n);
}

function fmtPct(v: unknown): string {
  if (v === null || v === undefined || v === "") return "";
  const s = String(v);
  return s;
}

const exportColumns = [
  "Account",
  "Account Desc",
  "PTJ",
  "Cost Centre",
  "Activity",
  "Fund",
  "Project",
  "Opening",
  "Initial",
  "Virement",
  "Additional",
  "Topup",
  "Pre Req",
  "Request",
  "Commit",
  "Locked",
  "Expenses",
  "Allocated",
  "Balance",
  "Total",
  "% Exp",
];

function toExportRow(r: BudgetV2BudgetSummaryRow): Record<string, string> {
  return {
    Account: r.acctCode ?? "",
    "Account Desc": r.acmAcctDesc ?? "",
    PTJ: r.pTJ ?? "",
    "Cost Centre": r.costcentre ?? "",
    Activity: r.description ?? "",
    Fund: r.fundTypeDisplay ?? "",
    Project: r.projectNo ?? "",
    Opening: fmtMoney(r.opening),
    Initial: fmtMoney(r.initial),
    Virement: fmtMoney(r.virement),
    Additional: fmtMoney(r.additional),
    Topup: fmtMoney(r.topup),
    "Pre Req": fmtMoney(r.preRequest),
    Request: fmtMoney(r.request),
    Commit: fmtMoney(r.commit),
    Locked: fmtMoney(r.locked),
    Expenses: fmtMoney(r.expenses),
    Allocated: fmtMoney(r.allocated),
    Balance: fmtMoney(r.balance),
    Total: fmtMoney(r.total),
    "% Exp": fmtPct(r.expensesPercent),
  };
}

const displayRows = computed(() => {
  const list = rows.value;
  const start = (page.value - 1) * limit.value;
  return list.slice(start, start + limit.value);
});

const filteredTotal = computed(() => rows.value.length);
const totalPages = computed(() => Math.max(1, Math.ceil(filteredTotal.value / Math.max(1, limit.value))));

watch(q, () => {
  applyClientFilter();
  page.value = 1;
});

const datatableRef = ref<DatatableRefApi | null>(null);
const { templateFileInputRef, onTemplateFileChange, handleDownloadPDF, handleDownloadCSV } = useDatatableFeatures({
  pageName: EXPORT_PAGE_LABEL[props.menuId],
  apiDataPath: "/budget/report/v2-budget-summary/listing",
  defaultExportColumns: exportColumns,
  getFilteredList: () =>
    rows.value.map((r, i) => {
      const er = toExportRow(r);
      return { ...er, No: String(i + 1) };
    }),
  datatableRef,
  searchKeyword: q,
  applyFilters: () => {
    applyClientFilter();
    page.value = 1;
  },
});

async function exportExcel() {
  try {
    if (rows.value.length === 0) {
      toast.info("No data", "There is nothing to export.");
      return;
    }
    const ExcelJS = await import("exceljs");
    const wb = new ExcelJS.Workbook();
    const ws = wb.addWorksheet(EXPORT_PAGE_LABEL[props.menuId]);
    ws.addRow(["No", ...exportColumns]);
    rows.value.forEach((r, idx) => {
      const er = toExportRow(r);
      ws.addRow([idx + 1, ...exportColumns.map((c) => er[c] ?? "")]);
    });
    if (aggregatePct.value) {
      ws.addRow([]);
      ws.addRow(["Aggregate expenses %", aggregatePct.value]);
    }
    const buf = await wb.xlsx.writeBuffer();
    const blob = new Blob([buf], { type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" });
    const url = URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.href = url;
    a.download = `${EXPORT_PAGE_LABEL[props.menuId].replace(/\s+/g, "_")}_${new Date().toISOString().slice(0, 10)}.xlsx`;
    a.click();
    URL.revokeObjectURL(url);
    toast.success("Excel downloaded");
  } catch (e) {
    toast.error("Export failed", e instanceof Error ? e.message : "Excel export failed.");
  }
}

onMounted(async () => {
  await loadOptions();
  if (topFilter.value.bdgYear) {
    await loadRows();
  }
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
        <div class="border-b border-slate-100 px-4 py-3">
          <h2 class="text-base font-semibold text-slate-900">Filter By</h2>
        </div>
        <div class="grid gap-3 p-4 md:grid-cols-2 lg:grid-cols-3">
          <div>
            <label class="mb-1 block text-xs font-medium text-slate-600">Year</label>
            <select v-model="topFilter.bdgYear" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
              <option value="">—</option>
              <option v-for="opt in options.topFilter.year" :key="opt.id" :value="opt.id">{{ opt.label }}</option>
            </select>
          </div>
          <div>
            <label class="mb-1 block text-xs font-medium text-slate-600">PTJ (subtree)</label>
            <select v-model="topFilter.ounCode" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
              <option value="">—</option>
              <option v-for="opt in options.topFilter.ptj" :key="opt.id" :value="opt.id">{{ opt.label }}</option>
            </select>
          </div>
          <div>
            <label class="mb-1 block text-xs font-medium text-slate-600">Fund type</label>
            <input
              v-model="topFilter.ftyFundType"
              type="text"
              maxlength="32"
              class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
              placeholder="Optional"
              autocomplete="off"
            />
          </div>
          <div>
            <label class="mb-1 block text-xs font-medium text-slate-600">Cost centre</label>
            <input
              v-model="topFilter.ccrCostcentre"
              type="text"
              maxlength="32"
              class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
              placeholder="Optional"
              autocomplete="off"
            />
          </div>
          <div>
            <label class="mb-1 block text-xs font-medium text-slate-600">Project no.</label>
            <input
              v-model="topFilter.cpaProjectNo"
              type="text"
              maxlength="64"
              class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
              placeholder="Optional"
              autocomplete="off"
            />
          </div>
          <div>
            <label class="mb-1 block text-xs font-medium text-slate-600">Activity group</label>
            <input
              v-model="topFilter.tfActivityGroup"
              type="text"
              maxlength="32"
              class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
              placeholder="Optional"
              autocomplete="off"
            />
          </div>
          <div>
            <label class="mb-1 block text-xs font-medium text-slate-600">Activity subgroup</label>
            <input
              v-model="topFilter.tfActivitySubgroup"
              type="text"
              maxlength="32"
              class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
              placeholder="Optional"
              autocomplete="off"
            />
          </div>
          <div>
            <label class="mb-1 block text-xs font-medium text-slate-600">Date from (DD/MM/YYYY)</label>
            <input
              v-model="topFilter.dateFrom"
              type="text"
              maxlength="10"
              placeholder="DD/MM/YYYY"
              class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
              autocomplete="off"
            />
          </div>
          <div>
            <label class="mb-1 block text-xs font-medium text-slate-600">Date to (DD/MM/YYYY)</label>
            <input
              v-model="topFilter.dateTo"
              type="text"
              maxlength="10"
              placeholder="DD/MM/YYYY"
              class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
              autocomplete="off"
            />
          </div>
          <div class="flex flex-col justify-end md:col-span-2 lg:col-span-3">
            <div class="flex flex-wrap justify-end gap-2">
              <button type="button" class="rounded-lg border border-slate-300 px-4 py-2 text-sm" @click="resetTopFilter">
                Reset
              </button>
              <button type="button" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white" @click="applyTopFilter">
                Search
              </button>
            </div>
          </div>
        </div>
      </article>

      <article class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
          <h2 class="text-base font-semibold text-slate-900">Listing</h2>
          <button class="rounded-lg p-2 text-slate-500 hover:bg-slate-100" aria-label="More" type="button">
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
                @change="page = 1"
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
                  placeholder="Filter loaded rows..."
                  class="w-56 rounded-lg border border-slate-300 py-1.5 pl-8 pr-8 text-sm"
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
            </div>
          </div>
          <p v-if="aggregatePct !== null" class="text-xs text-slate-600">
            Aggregate expenses % (legacy roll-up): <span class="font-mono">{{ aggregatePct }}</span>
          </p>
          <div class="overflow-x-auto rounded-lg border border-slate-200">
            <div :class="displayRows.length > 10 ? 'max-h-[480px] overflow-y-auto' : ''">
              <table class="w-full min-w-[1600px] text-sm">
                <thead class="sticky top-0 bg-slate-50">
                  <tr class="border-b border-slate-200 text-left">
                    <th class="px-2 py-2 text-xs font-semibold uppercase">No</th>
                    <th class="px-2 py-2 text-xs font-semibold uppercase">Acct</th>
                    <th class="px-2 py-2 text-xs font-semibold uppercase">Desc</th>
                    <th class="px-2 py-2 text-xs font-semibold uppercase">PTJ</th>
                    <th class="px-2 py-2 text-xs font-semibold uppercase">CC</th>
                    <th class="px-2 py-2 text-xs font-semibold uppercase">Activity</th>
                    <th class="px-2 py-2 text-xs font-semibold uppercase">Fund</th>
                    <th class="px-2 py-2 text-xs font-semibold uppercase">Proj</th>
                    <th class="px-2 py-2 text-right text-xs font-semibold uppercase">Open</th>
                    <th class="px-2 py-2 text-right text-xs font-semibold uppercase">Init</th>
                    <th class="px-2 py-2 text-right text-xs font-semibold uppercase">Vire</th>
                    <th class="px-2 py-2 text-right text-xs font-semibold uppercase">Add</th>
                    <th class="px-2 py-2 text-right text-xs font-semibold uppercase">Top</th>
                    <th class="px-2 py-2 text-right text-xs font-semibold uppercase">Pre</th>
                    <th class="px-2 py-2 text-right text-xs font-semibold uppercase">Req</th>
                    <th class="px-2 py-2 text-right text-xs font-semibold uppercase">Com</th>
                    <th class="px-2 py-2 text-right text-xs font-semibold uppercase">Lock</th>
                    <th class="px-2 py-2 text-right text-xs font-semibold uppercase">Exp</th>
                    <th class="px-2 py-2 text-right text-xs font-semibold uppercase">Alloc</th>
                    <th class="px-2 py-2 text-right text-xs font-semibold uppercase">Bal</th>
                    <th class="px-2 py-2 text-right text-xs font-semibold uppercase">Tot</th>
                    <th class="px-2 py-2 text-right text-xs font-semibold uppercase">%Exp</th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="(row, i) in displayRows"
                    :key="`${row.acctCode}-${i}-${page}`"
                    class="border-b border-slate-100 hover:bg-slate-50"
                  >
                    <td class="px-2 py-2">{{ (page - 1) * limit + i + 1 }}</td>
                    <td class="px-2 py-2 font-mono text-xs">{{ row.acctCode ?? "—" }}</td>
                    <td class="max-w-[140px] truncate px-2 py-2" :title="row.acmAcctDesc ?? ''">
                      {{ row.acmAcctDesc ?? "—" }}
                    </td>
                    <td class="max-w-[120px] truncate px-2 py-2" :title="row.pTJ ?? ''">{{ row.pTJ ?? "—" }}</td>
                    <td class="max-w-[120px] truncate px-2 py-2" :title="row.costcentre ?? ''">{{ row.costcentre ?? "—" }}</td>
                    <td class="max-w-[100px] truncate px-2 py-2" :title="row.description ?? ''">{{ row.description ?? "—" }}</td>
                    <td class="max-w-[100px] truncate px-2 py-2" :title="row.fundTypeDisplay ?? ''">
                      {{ row.fundTypeDisplay ?? "—" }}
                    </td>
                    <td class="px-2 py-2">{{ row.projectNo ?? "—" }}</td>
                    <td class="px-2 py-2 text-right">{{ fmtMoney(row.opening) }}</td>
                    <td class="px-2 py-2 text-right">{{ fmtMoney(row.initial) }}</td>
                    <td class="px-2 py-2 text-right">{{ fmtMoney(row.virement) }}</td>
                    <td class="px-2 py-2 text-right">{{ fmtMoney(row.additional) }}</td>
                    <td class="px-2 py-2 text-right">{{ fmtMoney(row.topup) }}</td>
                    <td class="px-2 py-2 text-right">{{ fmtMoney(row.preRequest) }}</td>
                    <td class="px-2 py-2 text-right">{{ fmtMoney(row.request) }}</td>
                    <td class="px-2 py-2 text-right">{{ fmtMoney(row.commit) }}</td>
                    <td class="px-2 py-2 text-right">{{ fmtMoney(row.locked) }}</td>
                    <td class="px-2 py-2 text-right">{{ fmtMoney(row.expenses) }}</td>
                    <td class="px-2 py-2 text-right">{{ fmtMoney(row.allocated) }}</td>
                    <td class="px-2 py-2 text-right">{{ fmtMoney(row.balance) }}</td>
                    <td class="px-2 py-2 text-right">{{ fmtMoney(row.total) }}</td>
                    <td class="px-2 py-2 text-right font-mono text-xs">{{ fmtPct(row.expensesPercent) }}</td>
                  </tr>
                  <tr v-if="displayRows.length === 0">
                    <td colspan="22" class="px-3 py-6 text-center text-xs text-slate-500">No data</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          <div class="flex flex-wrap items-center justify-between gap-2 border-t border-slate-100 pt-3">
            <div class="text-xs text-slate-500">
              Page {{ page }} of {{ totalPages }} · {{ filteredTotal }} row{{ filteredTotal === 1 ? "" : "s" }} (filtered)
            </div>
            <div class="flex flex-wrap items-center gap-2">
              <button
                type="button"
                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium hover:bg-slate-50 disabled:opacity-50"
                :disabled="page <= 1"
                @click="page = Math.max(1, page - 1)"
              >
                Prev
              </button>
              <button
                type="button"
                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium hover:bg-slate-50 disabled:opacity-50"
                :disabled="page >= totalPages"
                @click="page = Math.min(totalPages, page + 1)"
              >
                Next
              </button>
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
            </div>
          </div>
        </div>
      </article>
    </div>
  </AdminLayout>
</template>
