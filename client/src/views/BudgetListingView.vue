<script setup lang="ts">
/**
 * Budget / Monitoring / Budget Listing (PAGEID 1510 / MENUID 1831).
 * Seven legacy datatables (API_BDG_MONITORING_LISTING). Opened from Monitoring via
 * ?bgdId=&year=.
 */
import { computed, onMounted, ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import { ChevronLeft, Download, FileDown, FileSpreadsheet, MoreVertical, Search, X } from "lucide-vue-next";
import AdminLayout from "@/layouts/AdminLayout.vue";
import { listBudgetMonitoringListing } from "@/api/cms";
import { useToast } from "@/composables/useToast";

type ListingSection =
  | "initial"
  | "increment_decrement"
  | "virement"
  | "prerequisition_v2"
  | "requisition_v2"
  | "commitment_v2"
  | "expenses_v2";

const SECTION_META: { section: ListingSection; title: string }[] = [
  { section: "initial", title: "Budget Initial" },
  { section: "increment_decrement", title: "Increment / Decrement" },
  { section: "virement", title: "Virement" },
  { section: "prerequisition_v2", title: "Pre Requisition" },
  { section: "requisition_v2", title: "Requisition" },
  { section: "commitment_v2", title: "Commitment" },
  { section: "expenses_v2", title: "Expenses" },
];

const props = withDefaults(
  defineProps<{
    pageHeading?: string;
    /** When `router.back()` is not available, go here (e.g. hidden Budget View). */
    monitoringFallbackPath?: string;
    /** Optional replacement for the “open from monitoring” hint when `!canLoad`. */
    emptyStateHint?: string;
  }>(),
  {
    pageHeading: "Budget / Monitoring / Budget Listing",
    monitoringFallbackPath: "/admin/kerisi/m/1471",
    emptyStateHint: "",
  },
);

const toast = useToast();
const route = useRoute();
const router = useRouter();

const bgdId = computed(() => String(route.query.bgdId ?? "").trim());
const year = computed(() => String(route.query.year ?? "").trim());
const canLoad = computed(() => bgdId.value !== "" && year.value !== "");

type TabState = {
  rows: Record<string, unknown>[];
  total: number;
  page: number;
  limit: number;
  q: string;
  loading: boolean;
  footer: Record<string, unknown>;
};

function emptyTab(): TabState {
  return { rows: [], total: 0, page: 1, limit: 5, q: "", loading: false, footer: {} };
}

const tabs = ref<Record<ListingSection, TabState>>({
  initial: emptyTab(),
  increment_decrement: emptyTab(),
  virement: emptyTab(),
  prerequisition_v2: emptyTab(),
  requisition_v2: emptyTab(),
  commitment_v2: emptyTab(),
  expenses_v2: emptyTab(),
});

const currency = new Intl.NumberFormat("en-MY", { minimumFractionDigits: 2, maximumFractionDigits: 2 });

function formatAmt(v: unknown): string {
  if (v === null || v === undefined || v === "") return "-";
  const n = typeof v === "number" ? v : Number(String(v).replace(/,/g, ""));
  if (!Number.isFinite(n)) return String(v);
  return currency.format(n);
}

function formatDateVal(v: unknown): string {
  if (v === null || v === undefined || v === "") return "-";
  if (typeof v === "string" && /^\d{2}\/\d{2}\/\d{4}/.test(v)) return v;
  const d = new Date(String(v));
  if (Number.isNaN(d.getTime())) return String(v);
  const dd = String(d.getDate()).padStart(2, "0");
  const mm = String(d.getMonth() + 1).padStart(2, "0");
  return `${dd}/${mm}/${d.getFullYear()}`;
}

async function loadSection(s: ListingSection) {
  if (!canLoad.value) return;
  const t = tabs.value[s];
  t.loading = true;
  const params = new URLSearchParams({
    section: s,
    bgd_id: bgdId.value,
    year: year.value,
    page: String(t.page),
    limit: String(t.limit),
    ...(t.q ? { q: t.q } : {}),
  });
  try {
    const res = await listBudgetMonitoringListing(`?${params.toString()}`);
    t.rows = (res.data ?? []) as Record<string, unknown>[];
    t.total = Number(res.meta?.total ?? 0);
    t.footer = (res.meta?.footer as Record<string, unknown>) ?? {};
  } catch (e) {
    toast.error("Load failed", e instanceof Error ? e.message : `Could not load ${s}.`);
  } finally {
    t.loading = false;
  }
}

function reloadAll() {
  SECTION_META.forEach(({ section }) => {
    tabs.value[section].page = 1;
    void loadSection(section);
  });
}

function goBack() {
  if (window.history.length > 1) router.back();
  else router.push({ path: props.monitoringFallbackPath });
}

watch([bgdId, year], () => {
  reloadAll();
});

onMounted(() => {
  if (canLoad.value) reloadAll();
});

const debouncers: Partial<Record<ListingSection, ReturnType<typeof setTimeout>>> = {};
function scheduleSearch(s: ListingSection) {
  if (debouncers[s]) clearTimeout(debouncers[s]!);
  debouncers[s] = setTimeout(() => {
    debouncers[s] = undefined;
    tabs.value[s].page = 1;
    void loadSection(s);
  }, 350);
}

function footerAmount(s: ListingSection): string {
  const f = tabs.value[s].footer;
  const v = f.bdg_initial_amt ?? f.bgt_trans_amt ?? f.ppr_amount;
  return formatAmt(v);
}

function rowExport(s: ListingSection, r: Record<string, unknown>): string[] {
  const idx = String(r.index ?? "");
  if (s === "initial") {
    return [
      idx,
      String(r.bdgYear ?? r.bdg_year ?? ""),
      String(r.bdgBudgetId ?? r.bdg_budget_id ?? ""),
      String(r.allocation ?? ""),
      formatDateVal(r.transDate ?? r.trans_date),
      formatAmt(r.bdgInitialAmt ?? r.bdg_initial_amt),
      String(r.bdgRefId ?? r.bdg_ref_id ?? ""),
      String(r.bdgStatus ?? r.bdg_status ?? ""),
    ];
  }
  if (s === "increment_decrement" || s === "virement") {
    return [
      idx,
      String(r.bdgYear ?? r.bdg_year ?? ""),
      String(r.bdgBudgetId ?? r.bdg_budget_id ?? ""),
      formatDateVal(r.bgtTransDate ?? r.bgt_trans_date),
      String(r.bgtRef ?? r.bgt_ref ?? ""),
      formatAmt(r.bgtTransAmt ?? r.bgt_trans_amt),
    ];
  }
  if (s === "prerequisition_v2") {
    return [
      idx,
      String(r.sbgBudgetId ?? r.sbg_budget_id ?? ""),
      String(r.ftyFundType ?? r.fty_fund_type ?? ""),
      String(r.atActivityCode ?? r.at_activity_code ?? ""),
      String(r.ounCode ?? r.oun_code ?? ""),
      String(r.ccrCostcentre ?? r.ccr_costcentre ?? ""),
      String(r.acmAcctCode ?? r.acm_acct_code ?? ""),
      String(r.lbcBudgetCode ?? r.lbc_budget_code ?? ""),
      formatDateVal(r.bgtTransDate ?? r.bgt_trans_date),
      String(r.bgtRef ?? r.bgt_ref ?? ""),
      formatAmt(r.bgtTransAmt ?? r.bgt_trans_amt),
    ];
  }
  if (s === "requisition_v2") {
    return [
      idx,
      String(r.bdgBudgetId ?? r.bdg_budget_id ?? ""),
      String(r.sbgBudgetId ?? r.sbg_budget_id ?? ""),
      String(r.ftyFundType ?? r.fty_fund_type ?? ""),
      String(r.atActivityCode ?? r.at_activity_code ?? ""),
      String(r.ounCode ?? r.oun_code ?? ""),
      String(r.ccrCostcentre ?? r.ccr_costcentre ?? ""),
      String(r.acmAcctCode ?? r.acm_acct_code ?? ""),
      formatDateVal(r.bgtTransDate ?? r.bgt_trans_date),
      String(r.bgtRef ?? r.bgt_ref ?? ""),
      String(r.rqmRequisitionNo ?? r.rqm_requisition_no ?? ""),
      formatAmt(r.bgtTransAmt ?? r.bgt_trans_amt),
    ];
  }
  if (s === "commitment_v2") {
    return [
      idx,
      String(r.bdgBudgetId ?? r.bdg_budget_id ?? ""),
      String(r.sbgBudgetId ?? r.sbg_budget_id ?? ""),
      String(r.ftyFundType ?? r.fty_fund_type ?? ""),
      String(r.atActivityCode ?? r.at_activity_code ?? ""),
      String(r.ounCode ?? r.oun_code ?? ""),
      String(r.ccrCostcentre ?? r.ccr_costcentre ?? ""),
      String(r.lbcBudgetCode ?? r.lbc_budget_code ?? ""),
      formatDateVal(r.bgtTransDate ?? r.bgt_trans_date),
      String(r.bgtRef ?? r.bgt_ref ?? ""),
      formatAmt(r.bgtTransAmt ?? r.bgt_trans_amt),
    ];
  }
  return [
    idx,
    String(r.bdgBudgetId ?? r.bdg_budget_id ?? ""),
    String(r.sbgBudgetId ?? r.sbg_budget_id ?? ""),
    String(r.ftyFundType ?? r.fty_fund_type ?? ""),
    String(r.atActivityCode ?? r.at_activity_code ?? ""),
    String(r.ounCode ?? r.oun_code ?? ""),
    String(r.ccrCostcentre ?? r.ccr_costcentre ?? ""),
    String(r.acmAcctCode ?? r.acm_acct_code ?? ""),
    String(r.bdgBudgetCode ?? r.bdg_budget_code ?? ""),
    formatDateVal(r.bgtTransDate ?? r.bgt_trans_date),
    String(r.bgtRef ?? r.bgt_ref ?? ""),
    formatAmt(r.bgtTransAmt ?? r.bgt_trans_amt),
  ];
}

function exportHeadings(s: ListingSection): string[] {
  if (s === "initial") {
    return ["No", "Year", "Structure Budget", "Allocation", "Transaction Date", "Total (RM)", "Reference", "Status"];
  }
  if (s === "increment_decrement" || s === "virement") {
    return ["No", "Year", "Structure Budget", "Transaction Date", "Reference", "Amount (RM)"];
  }
  if (s === "prerequisition_v2") {
    return ["No", "SBG", "Fund", "Act", "PTJ", "CC", "Acct", "Bud code", "Date", "Ref", "Amt"];
  }
  if (s === "requisition_v2") {
    return ["No", "Bud", "SBG", "Fund", "Act", "PTJ", "CC", "Acct", "Date", "Ref", "Req", "Amt"];
  }
  if (s === "commitment_v2") {
    return ["No", "Bud", "SBG", "Fund", "Act", "PTJ", "CC", "Code", "Date", "Ref", "Amt"];
  }
  return ["No", "Bud", "SBG", "Fund", "Act", "PTJ", "CC", "Acct", "Bud code", "Date", "Ref", "Amt"];
}

async function downloadPdf(s: ListingSection) {
  const t = tabs.value[s];
  if (t.rows.length === 0) {
    toast.info("No data", "Nothing to export.");
    return;
  }
  try {
    const { jsPDF } = await import("jspdf");
    const autoTable = (await import("jspdf-autotable")).default;
    const doc = new jsPDF({ orientation: "landscape", unit: "mm", format: "a4" });
    const title = `Budget Listing / ${SECTION_META.find((x) => x.section === s)?.title ?? s}`;
    doc.setFontSize(11);
    doc.text(title, 14, 12);
    doc.setFontSize(8);
    doc.text(`GL ${bgdId.value} · Year ${year.value}`, 14, 16);
    const head = [exportHeadings(s)];
    const body = t.rows.map((r) => rowExport(s, r));
    autoTable(doc, {
      startY: 18,
      head,
      body,
      styles: { fontSize: 7 },
      headStyles: { fillColor: [30, 41, 59] },
    });
    doc.save(`Budget_Listing_${s}_${new Date().toISOString().slice(0, 10)}.pdf`);
    toast.success("PDF downloaded");
  } catch (e) {
    toast.error("Export failed", e instanceof Error ? e.message : "PDF failed.");
  }
}

function downloadCsv(s: ListingSection) {
  const t = tabs.value[s];
  if (t.rows.length === 0) {
    toast.info("No data", "Nothing to export.");
    return;
  }
  const head = exportHeadings(s);
  const lines = [head.join(",")];
  t.rows.forEach((r) => {
    lines.push(
      rowExport(s, r)
        .map((c) => `"${String(c).replace(/"/g, '""')}"`)
        .join(","),
    );
  });
  const blob = new Blob([lines.join("\r\n")], { type: "text/csv;charset=utf-8" });
  const url = URL.createObjectURL(blob);
  const a = document.createElement("a");
  a.href = url;
  a.download = `Budget_Listing_${s}_${new Date().toISOString().slice(0, 10)}.csv`;
  a.click();
  URL.revokeObjectURL(url);
  toast.success("CSV downloaded");
}

async function downloadExcel(s: ListingSection) {
  const t = tabs.value[s];
  if (t.rows.length === 0) {
    toast.info("No data", "Nothing to export.");
    return;
  }
  try {
    const ExcelJS = await import("exceljs");
    const wb = new ExcelJS.Workbook();
    const ws = wb.addWorksheet(s);
    ws.addRow(exportHeadings(s));
    t.rows.forEach((r) => ws.addRow(rowExport(s, r)));
    const buf = await wb.xlsx.writeBuffer();
    const blob = new Blob([buf], { type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" });
    const url = URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.href = url;
    a.download = `Budget_Listing_${s}_${new Date().toISOString().slice(0, 10)}.xlsx`;
    a.click();
    URL.revokeObjectURL(url);
    toast.success("Excel downloaded");
  } catch (e) {
    toast.error("Export failed", e instanceof Error ? e.message : "Excel failed.");
  }
}

function totalPages(s: ListingSection): number {
  const t = tabs.value[s];
  return t.total ? Math.max(1, Math.ceil(t.total / t.limit)) : 1;
}
function startIdx(s: ListingSection): number {
  const t = tabs.value[s];
  return t.total === 0 ? 0 : (t.page - 1) * t.limit + 1;
}
function endIdx(s: ListingSection): number {
  const t = tabs.value[s];
  return Math.min(t.page * t.limit, t.total);
}

function footCols(s: ListingSection): number {
  if (s === "initial") return 5;
  if (s === "increment_decrement" || s === "virement") return 4;
  if (s === "prerequisition_v2") return 9;
  if (s === "requisition_v2") return 10;
  if (s === "commitment_v2") return 9;
  return 10;
}
</script>

<template>
  <AdminLayout>
    <div class="space-y-4">
      <div class="flex items-center gap-2">
        <button
          type="button"
          class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-2.5 py-1 text-xs text-slate-600 hover:bg-slate-50"
          @click="goBack"
        >
          <ChevronLeft class="h-3.5 w-3.5" />
          Back
        </button>
        <h1 class="page-title">{{ pageHeading }}</h1>
      </div>

      <p v-if="!canLoad" class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
        <template v-if="emptyStateHint">{{ emptyStateHint }}</template>
        <template v-else>
          Open this page from <strong>Budget Monitoring</strong> using <strong>View Budget</strong>, or append
          <code class="rounded bg-white px-1">?bgdId=…&amp;year=…</code>
          to the URL.
        </template>
      </p>

      <template v-else>
        <section
          v-for="{ section, title } in SECTION_META"
          :key="section"
          class="rounded-lg border border-slate-200 bg-white shadow-sm"
        >
          <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 px-4 py-3">
            <div class="flex items-center gap-2">
              <h2 class="text-base font-semibold text-slate-900">{{ title }}</h2>
              <span class="text-xs text-slate-500">GL: {{ bgdId }} · Year {{ year }}</span>
            </div>
            <button type="button" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100" aria-label="More">
              <MoreVertical class="h-4 w-4" />
            </button>
          </div>

          <div class="space-y-4 p-4">
            <div class="flex flex-wrap items-end justify-between gap-4">
              <div class="flex items-center gap-2">
                <label class="text-xs font-medium text-slate-600">Display</label>
                <select
                  v-model.number="tabs[section].limit"
                  class="rounded-lg border border-slate-300 px-2 py-1.5 text-sm"
                  @change="tabs[section].page = 1; void loadSection(section)"
                >
                  <option v-for="n in [5, 10, 25, 50]" :key="n" :value="n">{{ n }}</option>
                </select>
              </div>
              <div class="relative">
                <Search class="pointer-events-none absolute left-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400" />
                <input
                  v-model="tabs[section].q"
                  type="search"
                  placeholder="Filter rows…"
                  class="w-56 rounded-lg border border-slate-300 py-1.5 pl-8 pr-8 text-sm"
                  @keyup.enter="tabs[section].page = 1; void loadSection(section)"
                  @input="scheduleSearch(section)"
                />
                <button
                  v-if="tabs[section].q"
                  type="button"
                  class="absolute right-1 top-1/2 -translate-y-1/2 rounded p-0.5 text-slate-400 hover:bg-slate-100"
                  aria-label="Clear search"
                  @click="tabs[section].q = ''; tabs[section].page = 1; void loadSection(section)"
                >
                  <X class="h-3.5 w-3.5" />
                </button>
              </div>
            </div>

            <div class="overflow-x-auto rounded-lg border border-slate-200">
              <div :class="tabs[section].rows.length > 10 ? 'max-h-[360px] overflow-y-auto' : ''">
                <table class="min-w-[960px] w-full text-sm">
                  <thead class="sticky top-0 bg-slate-50">
                    <tr class="border-b border-slate-200 text-left text-xs font-semibold uppercase text-slate-600">
                      <th class="px-2 py-2">No</th>
                      <template v-if="section === 'initial'">
                        <th class="px-2 py-2">Year</th>
                        <th class="px-2 py-2">Structure Budget</th>
                        <th class="px-2 py-2">Allocation</th>
                        <th class="px-2 py-2">Transaction Date</th>
                        <th class="px-2 py-2 text-right">Total (RM)</th>
                        <th class="px-2 py-2">Reference</th>
                        <th class="px-2 py-2">Status</th>
                      </template>
                      <template v-else-if="section === 'increment_decrement' || section === 'virement'">
                        <th class="px-2 py-2">Year</th>
                        <th class="px-2 py-2">Structure Budget</th>
                        <th class="px-2 py-2">Transaction Date</th>
                        <th class="px-2 py-2">Reference</th>
                        <th class="px-2 py-2 text-right">Amount (RM)</th>
                      </template>
                      <template v-else-if="section === 'prerequisition_v2'">
                        <th class="px-2 py-2">Structure Budget</th>
                        <th class="px-2 py-2">Fund</th>
                        <th class="px-2 py-2">Activity</th>
                        <th class="px-2 py-2">PTJ</th>
                        <th class="px-2 py-2">CC</th>
                        <th class="px-2 py-2">Acct</th>
                        <th class="px-2 py-2">Budget Code</th>
                        <th class="px-2 py-2">Date</th>
                        <th class="px-2 py-2">Ref</th>
                        <th class="px-2 py-2 text-right">Amt</th>
                      </template>
                      <template v-else-if="section === 'requisition_v2'">
                        <th class="px-2 py-2">Budget No</th>
                        <th class="px-2 py-2">SBG</th>
                        <th class="px-2 py-2">Fund</th>
                        <th class="px-2 py-2">Act</th>
                        <th class="px-2 py-2">PTJ</th>
                        <th class="px-2 py-2">CC</th>
                        <th class="px-2 py-2">Acct</th>
                        <th class="px-2 py-2">Date</th>
                        <th class="px-2 py-2">Ref</th>
                        <th class="px-2 py-2">Req No</th>
                        <th class="px-2 py-2 text-right">Amt</th>
                      </template>
                      <template v-else-if="section === 'commitment_v2'">
                        <th class="px-2 py-2">Budget No</th>
                        <th class="px-2 py-2">SBG</th>
                        <th class="px-2 py-2">Fund</th>
                        <th class="px-2 py-2">Act</th>
                        <th class="px-2 py-2">PTJ</th>
                        <th class="px-2 py-2">CC</th>
                        <th class="px-2 py-2">Code</th>
                        <th class="px-2 py-2">Date</th>
                        <th class="px-2 py-2">Ref</th>
                        <th class="px-2 py-2 text-right">Amt</th>
                      </template>
                      <template v-else>
                        <th class="px-2 py-2">Budget No</th>
                        <th class="px-2 py-2">SBG</th>
                        <th class="px-2 py-2">Fund</th>
                        <th class="px-2 py-2">Act</th>
                        <th class="px-2 py-2">PTJ</th>
                        <th class="px-2 py-2">CC</th>
                        <th class="px-2 py-2">Acct</th>
                        <th class="px-2 py-2">Bud Code</th>
                        <th class="px-2 py-2">Date</th>
                        <th class="px-2 py-2">Ref</th>
                        <th class="px-2 py-2 text-right">Amt</th>
                      </template>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-if="tabs[section].loading">
                      <td colspan="20" class="px-3 py-6 text-center text-slate-500">Loading…</td>
                    </tr>
                    <tr v-else-if="tabs[section].rows.length === 0">
                      <td colspan="20" class="px-3 py-6 text-center text-slate-500">No records.</td>
                    </tr>
                    <template v-else>
                      <tr
                        v-for="row in tabs[section].rows"
                        :key="`${section}-${String(row.index)}`"
                        class="border-b border-slate-100 hover:bg-slate-50"
                      >
                        <td class="px-2 py-1.5">{{ row.index }}</td>
                        <template v-if="section === 'initial'">
                          <td class="px-2 py-1.5">{{ row.bdgYear ?? row.bdg_year }}</td>
                          <td class="px-2 py-1.5 whitespace-nowrap">{{ row.bdgBudgetId ?? row.bdg_budget_id }}</td>
                          <td class="px-2 py-1.5">{{ row.allocation }}</td>
                          <td class="px-2 py-1.5">{{ formatDateVal(row.transDate ?? row.trans_date) }}</td>
                          <td class="px-2 py-1.5 text-right tabular-nums">
                            {{ formatAmt(row.bdgInitialAmt ?? row.bdg_initial_amt) }}
                          </td>
                          <td class="px-2 py-1.5">{{ row.bdgRefId ?? row.bdg_ref_id }}</td>
                          <td class="px-2 py-1.5">{{ row.bdgStatus ?? row.bdg_status }}</td>
                        </template>
                        <template v-else-if="section === 'increment_decrement' || section === 'virement'">
                          <td class="px-2 py-1.5">{{ row.bdgYear ?? row.bdg_year }}</td>
                          <td class="px-2 py-1.5 whitespace-nowrap">{{ row.bdgBudgetId ?? row.bdg_budget_id }}</td>
                          <td class="px-2 py-1.5">{{ formatDateVal(row.bgtTransDate ?? row.bgt_trans_date) }}</td>
                          <td class="px-2 py-1.5">{{ row.bgtRef ?? row.bgt_ref }}</td>
                          <td class="px-2 py-1.5 text-right tabular-nums">{{ formatAmt(row.bgtTransAmt ?? row.bgt_trans_amt) }}</td>
                        </template>
                        <template v-else-if="section === 'prerequisition_v2'">
                          <td class="px-2 py-1.5 whitespace-nowrap">{{ row.sbgBudgetId ?? row.sbg_budget_id }}</td>
                          <td class="px-2 py-1.5">{{ row.ftyFundType ?? row.fty_fund_type }}</td>
                          <td class="px-2 py-1.5">{{ row.atActivityCode ?? row.at_activity_code }}</td>
                          <td class="px-2 py-1.5">{{ row.ounCode ?? row.oun_code }}</td>
                          <td class="px-2 py-1.5">{{ row.ccrCostcentre ?? row.ccr_costcentre }}</td>
                          <td class="px-2 py-1.5">{{ row.acmAcctCode ?? row.acm_acct_code }}</td>
                          <td class="px-2 py-1.5">{{ row.lbcBudgetCode ?? row.lbc_budget_code }}</td>
                          <td class="px-2 py-1.5">{{ formatDateVal(row.bgtTransDate ?? row.bgt_trans_date) }}</td>
                          <td class="px-2 py-1.5">{{ row.bgtRef ?? row.bgt_ref }}</td>
                          <td class="px-2 py-1.5 text-right tabular-nums">{{ formatAmt(row.bgtTransAmt ?? row.bgt_trans_amt) }}</td>
                        </template>
                        <template v-else-if="section === 'requisition_v2'">
                          <td class="px-2 py-1.5">{{ row.bdgBudgetId ?? row.bdg_budget_id }}</td>
                          <td class="px-2 py-1.5">{{ row.sbgBudgetId ?? row.sbg_budget_id }}</td>
                          <td class="px-2 py-1.5">{{ row.ftyFundType ?? row.fty_fund_type }}</td>
                          <td class="px-2 py-1.5">{{ row.atActivityCode ?? row.at_activity_code }}</td>
                          <td class="px-2 py-1.5">{{ row.ounCode ?? row.oun_code }}</td>
                          <td class="px-2 py-1.5">{{ row.ccrCostcentre ?? row.ccr_costcentre }}</td>
                          <td class="px-2 py-1.5">{{ row.acmAcctCode ?? row.acm_acct_code }}</td>
                          <td class="px-2 py-1.5">{{ formatDateVal(row.bgtTransDate ?? row.bgt_trans_date) }}</td>
                          <td class="px-2 py-1.5">{{ row.bgtRef ?? row.bgt_ref }}</td>
                          <td class="px-2 py-1.5">{{ row.rqmRequisitionNo ?? row.rqm_requisition_no }}</td>
                          <td class="px-2 py-1.5 text-right tabular-nums">{{ formatAmt(row.bgtTransAmt ?? row.bgt_trans_amt) }}</td>
                        </template>
                        <template v-else-if="section === 'commitment_v2'">
                          <td class="px-2 py-1.5">{{ row.bdgBudgetId ?? row.bdg_budget_id }}</td>
                          <td class="px-2 py-1.5">{{ row.sbgBudgetId ?? row.sbg_budget_id }}</td>
                          <td class="px-2 py-1.5">{{ row.ftyFundType ?? row.fty_fund_type }}</td>
                          <td class="px-2 py-1.5">{{ row.atActivityCode ?? row.at_activity_code }}</td>
                          <td class="px-2 py-1.5">{{ row.ounCode ?? row.oun_code }}</td>
                          <td class="px-2 py-1.5">{{ row.ccrCostcentre ?? row.ccr_costcentre }}</td>
                          <td class="px-2 py-1.5">{{ row.lbcBudgetCode ?? row.lbc_budget_code }}</td>
                          <td class="px-2 py-1.5">{{ formatDateVal(row.bgtTransDate ?? row.bgt_trans_date) }}</td>
                          <td class="px-2 py-1.5">{{ row.bgtRef ?? row.bgt_ref }}</td>
                          <td class="px-2 py-1.5 text-right tabular-nums">{{ formatAmt(row.bgtTransAmt ?? row.bgt_trans_amt) }}</td>
                        </template>
                        <template v-else>
                          <td class="px-2 py-1.5">{{ row.bdgBudgetId ?? row.bdg_budget_id }}</td>
                          <td class="px-2 py-1.5">{{ row.sbgBudgetId ?? row.sbg_budget_id }}</td>
                          <td class="px-2 py-1.5">{{ row.ftyFundType ?? row.fty_fund_type }}</td>
                          <td class="px-2 py-1.5">{{ row.atActivityCode ?? row.at_activity_code }}</td>
                          <td class="px-2 py-1.5">{{ row.ounCode ?? row.oun_code }}</td>
                          <td class="px-2 py-1.5">{{ row.ccrCostcentre ?? row.ccr_costcentre }}</td>
                          <td class="px-2 py-1.5">{{ row.acmAcctCode ?? row.acm_acct_code }}</td>
                          <td class="px-2 py-1.5">{{ row.bdgBudgetCode ?? row.bdg_budget_code }}</td>
                          <td class="px-2 py-1.5">{{ formatDateVal(row.bgtTransDate ?? row.bgt_trans_date) }}</td>
                          <td class="px-2 py-1.5">{{ row.bgtRef ?? row.bgt_ref }}</td>
                          <td class="px-2 py-1.5 text-right tabular-nums">{{ formatAmt(row.bgtTransAmt ?? row.bgt_trans_amt) }}</td>
                        </template>
                      </tr>
                    </template>
                  </tbody>
                  <tfoot v-if="!tabs[section].loading && tabs[section].rows.length > 0" class="bg-slate-50">
                    <tr class="border-t border-slate-200">
                      <td class="px-2 py-2 text-right text-xs font-semibold text-slate-600" :colspan="footCols(section)">
                        Total (filtered)
                      </td>
                      <td class="px-2 py-2 text-right text-sm font-semibold tabular-nums">{{ footerAmount(section) }}</td>
                    </tr>
                  </tfoot>
                </table>
              </div>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-3">
              <div class="text-xs text-slate-500">
                Showing {{ startIdx(section) }}-{{ endIdx(section) }} of {{ tabs[section].total }}
              </div>
              <div class="flex flex-wrap items-center gap-2">
                <button
                  type="button"
                  :disabled="tabs[section].page <= 1"
                  class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium disabled:opacity-50"
                  @click="tabs[section].page--; void loadSection(section)"
                >
                  Prev
                </button>
                <span class="text-xs text-slate-600">Page {{ tabs[section].page }} / {{ totalPages(section) }}</span>
                <button
                  type="button"
                  :disabled="tabs[section].page >= totalPages(section)"
                  class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium disabled:opacity-50"
                  @click="tabs[section].page++; void loadSection(section)"
                >
                  Next
                </button>
                <div class="mx-2 h-5 w-px bg-slate-200" />
                <button
                  type="button"
                  class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium"
                  @click="void downloadPdf(section)"
                >
                  <Download class="h-3.5 w-3.5" />
                  PDF
                </button>
                <button
                  type="button"
                  class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium"
                  @click="downloadCsv(section)"
                >
                  <FileDown class="h-3.5 w-3.5" />
                  CSV
                </button>
                <button
                  type="button"
                  class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium"
                  @click="void downloadExcel(section)"
                >
                  <FileSpreadsheet class="h-3.5 w-3.5" />
                  Excel
                </button>
              </div>
            </div>
          </div>
        </section>
      </template>
    </div>
  </AdminLayout>
</template>
