<script setup lang="ts">
/**
 * Credit Control / Subsidiary Ledger — Subsidiary Statement (MENUID 2667) or
 * All Subsidiary Ledger (3381). Legacy SNA_API_CC_SUBSLEDGER_ALL over
 * rep_aging_debtor with running balance (MySQL 8 window).
 * Onload bridge: `SNA_JS_CC_SUBSLEDGER_ALL.refresh()`.
 * Kerisi 2.0 shell matches Deposit / other CC listings: Search Parameter card,
 * overflow menu (template / group), toolbar row, violet table header, exports.
 */
import { computed, onMounted, onUnmounted, ref, watch } from "vue";
import { Copy, ChevronRight, Download, FileDown, FileSpreadsheet, Filter, Hash, MoreVertical, Pencil, Search, X } from "lucide-vue-next";
import AdminLayout from "@/layouts/AdminLayout.vue";
import {
  getSubsidiaryLedgerAllOptions,
  listSubsidiaryLedgerAll,
  searchInvoiceBalanceCustomer,
} from "@/api/cms";
import { useDatatableFeatures } from "@/composables/useDatatableFeatures";
import type { DatatableRefApi } from "@/composables/useDatatableFeatures";
import { useToast } from "@/composables/useToast";
import type { CcCustomerOption, SubsidiaryLedgerAllOptions, SubsidiaryLedgerAllRow } from "@/types";

const props = withDefaults(
  defineProps<{
    pageBreadcrumb?: string;
    cardTitle?: string;
    legacyMenuId?: string;
  }>(),
  {
    pageBreadcrumb: "Credit Control / Subsidiary Ledger / All Subsidiary Ledger",
    cardTitle: "All Subsidiary Ledger",
    legacyMenuId: "3381",
  },
);

const toast = useToast();
const datatableRef = ref<DatatableRefApi | null>(null);

const displayBreadcrumb = computed(() => props.pageBreadcrumb);
const displayCardTitle = computed(() => props.cardTitle);

const options = ref<SubsidiaryLedgerAllOptions>({
  customerTypes: [],
  fundTypes: [],
  costCentres: [],
  accountCodes: [],
});

const rows = ref<SubsidiaryLedgerAllRow[]>([]);
const loading = ref(false);
const total = ref(0);
const page = ref(1);
const limit = ref(10);
const q = ref("");
const hasSearched = ref(false);
const sortBy = ref<
  | "pde_trans_date"
  | "pde_document_no"
  | "pde_payto_id"
  | "acm_acct_code"
  | "debit"
  | "credit"
  | "balance"
>("pde_trans_date");
const sortDir = ref<"asc" | "desc">("asc");

const customerType = ref("");
const customerId = ref("");
const accountCode = ref("");
const costCentre = ref("");
const statementDate = ref(new Date().toISOString().slice(0, 10));
const fundType = ref("");
const dateStart = ref("");
const dateEnd = ref("");

const custQuery = ref("");
const custOptions = ref<CcCustomerOption[]>([]);
const showCustDropdown = ref(false);
let custTimer: number | null = null;

const totalPages = computed(() =>
  total.value ? Math.max(1, Math.ceil(total.value / limit.value)) : 1,
);
const startIdx = computed(() =>
  total.value === 0 ? 0 : (page.value - 1) * limit.value + 1,
);
const endIdx = computed(() => Math.min(page.value * limit.value, total.value));

function fmtMoney(n: number): string {
  return new Intl.NumberFormat("en-MY", {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(Number.isFinite(n) ? n : 0);
}

function toLegacyDate(iso: string): string {
  if (!iso) return "";
  const m = /^(\d{4})-(\d{2})-(\d{2})$/.exec(iso);
  return m ? `${m[3]}/${m[2]}/${m[1]}` : iso;
}

function copyPageSummary() {
  const lines = [displayBreadcrumb.value, typeof window !== "undefined" ? window.location.href : ""].filter(
    Boolean,
  );
  void navigator.clipboard.writeText(lines.join("\n")).then(
    () => toast.success("Copied", "Breadcrumb and link copied."),
    () => toast.error("Copy failed", "Clipboard not available."),
  );
}

function buildQuery(): string {
  const params = new URLSearchParams({
    page: String(page.value),
    limit: String(limit.value),
    sort_by: sortBy.value,
    sort_dir: sortDir.value,
    customer_type: customerType.value,
  });
  if (q.value.trim()) params.set("q", q.value.trim());
  if (customerId.value.trim()) params.set("customer_id", customerId.value.trim());
  if (accountCode.value) params.set("acm_acct_code", accountCode.value);
  if (costCentre.value) params.set("ccr_costcentre", costCentre.value);
  if (fundType.value) params.set("fty_fund_type", fundType.value);
  const sd = toLegacyDate(statementDate.value);
  if (sd) params.set("statement_date", sd);
  const ds = toLegacyDate(dateStart.value);
  const de = toLegacyDate(dateEnd.value);
  if (ds) params.set("date_start", ds);
  if (de) params.set("date_end", de);
  return `?${params.toString()}`;
}

async function loadRows() {
  if (!customerType.value) {
    toast.error("Customer type required", "Select Customer Type before searching.");
    return;
  }
  loading.value = true;
  try {
    const res = await listSubsidiaryLedgerAll(buildQuery());
    rows.value = res.data;
    total.value = Number(res.meta?.total ?? 0);
  } catch (e) {
    toast.error("Load failed", e instanceof Error ? e.message : "Unable to load subsidiary ledger.");
  } finally {
    loading.value = false;
  }
}

function runSearch() {
  hasSearched.value = true;
  page.value = 1;
  void loadRows();
}

function toggleSort(col: typeof sortBy.value) {
  if (sortBy.value === col) sortDir.value = sortDir.value === "asc" ? "desc" : "asc";
  else {
    sortBy.value = col;
    sortDir.value = col === "pde_trans_date" ? "asc" : "asc";
  }
  if (!hasSearched.value) return;
  void loadRows();
}

function onCustInput() {
  if (custTimer) window.clearTimeout(custTimer);
  custTimer = window.setTimeout(() => {
    custTimer = null;
    void (async () => {
      try {
        const res = await searchInvoiceBalanceCustomer(custQuery.value, customerType.value, 20);
        custOptions.value = res.data;
      } catch {
        custOptions.value = [];
      }
    })();
  }, 300);
}

function closeCustSoon() {
  window.setTimeout(() => {
    showCustDropdown.value = false;
  }, 180);
}

function pickCustomer(opt: CcCustomerOption) {
  customerId.value = String(opt.id);
  custQuery.value = opt.label;
  showCustDropdown.value = false;
}

function clearCustomer() {
  customerId.value = "";
  custQuery.value = "";
  custOptions.value = [];
}

function resetSearchParameters() {
  accountCode.value = "";
  costCentre.value = "";
  fundType.value = "";
  dateStart.value = "";
  dateEnd.value = "";
  statementDate.value = new Date().toISOString().slice(0, 10);
  clearCustomer();
  const types = options.value.customerTypes;
  if (types.length) {
    const a = types.find((c) => c.code === "A");
    customerType.value = a?.code ?? types[0]!.code;
  }
  hasSearched.value = false;
  rows.value = [];
  total.value = 0;
  page.value = 1;
}

function scrollToSearchParameters() {
  document.getElementById("subsidiary-search-params")?.scrollIntoView({ behavior: "smooth", block: "start" });
}

const overflowOpen = ref(false);
const overflowRoot = ref<HTMLElement | null>(null);

function onClickOutside(event: MouseEvent) {
  if (!overflowOpen.value) return;
  if (overflowRoot.value?.contains(event.target as Node)) return;
  overflowOpen.value = false;
}

function closeOverflow() {
  overflowOpen.value = false;
}

function toggleOverflow(e?: Event) {
  e?.stopPropagation();
  overflowOpen.value = !overflowOpen.value;
}

async function loadOptions() {
  try {
    const res = await getSubsidiaryLedgerAllOptions();
    options.value = res.data;
    if (!customerType.value && res.data.customerTypes.length) {
      const a = res.data.customerTypes.find((c) => c.code === "A");
      customerType.value = a?.code ?? res.data.customerTypes[0]!.code;
    }
  } catch {
    // keep defaults
  }
}

const exportColumns = [
  "Customer Type",
  "Customer ID",
  "Customer Name",
  "Document No",
  "Document Description",
  "Fund",
  "Activity Code",
  "PTJ",
  "Cost Center",
  "S/O Code",
  "Account Code",
  "Account Description",
  "Reference 1",
  "Reference 2",
  "Reference 3",
  "Transaction Date",
  "Amount Debit (RM)",
  "Amount Credit (RM)",
  "Amount Balance (RM)",
];

const { handleDownloadPDF, handleDownloadCSV, handleSaveTemplate, handleLoadTemplate, handleGroupList, handleUngroupList, templateFileInputRef, onTemplateFileChange, isGrouped } =
  useDatatableFeatures({
    pageName: displayCardTitle.value,
    apiDataPath: "/credit-control/subsidiary-ledger-all",
    defaultExportColumns: exportColumns,
    getFilteredList: () =>
      rows.value.map((r) => ({
        "Customer Type": r.customerTypeLabel ?? "",
        "Customer ID": r.pdePaytoId ?? "",
        "Customer Name": r.pdePaytoName ?? "",
        "Document No": r.pdeDocumentNo ?? "",
        "Document Description": r.docDescription ?? "",
        Fund: r.fundType ?? "",
        "Activity Code": r.activityCode ?? "",
        PTJ: r.ounCode ?? "",
        "Cost Center": r.costCentre ?? "",
        "S/O Code": r.soCode ?? "",
        "Account Code": r.acctCode ?? "",
        "Account Description": r.acctDesc ?? "",
        "Reference 1": r.reference1 ?? "",
        "Reference 2": r.reference2 ?? "",
        "Reference 3": r.reference3 ?? "",
        "Transaction Date": r.transDate ?? "",
        "Amount Debit (RM)": r.debit,
        "Amount Credit (RM)": r.credit,
        "Amount Balance (RM)": r.balance,
      })),
    datatableRef,
    searchKeyword: q,
    smartFilter: ref({}),
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
    const ws = wb.addWorksheet(displayCardTitle.value.slice(0, 31));
    ws.addRow(["No", ...exportColumns]);
    rows.value.forEach((r, i) => {
      ws.addRow([
        i + 1,
        r.customerTypeLabel ?? "",
        r.pdePaytoId ?? "",
        r.pdePaytoName ?? "",
        r.pdeDocumentNo ?? "",
        r.docDescription ?? "",
        r.fundType ?? "",
        r.activityCode ?? "",
        r.ounCode ?? "",
        r.costCentre ?? "",
        r.soCode ?? "",
        r.acctCode ?? "",
        r.acctDesc ?? "",
        r.reference1 ?? "",
        r.reference2 ?? "",
        r.reference3 ?? "",
        r.transDate ?? "",
        r.debit,
        r.credit,
        r.balance,
      ]);
    });
    const buf = await wb.xlsx.writeBuffer();
    const blob = new Blob([buf], { type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" });
    const url = URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.href = url;
    a.download = `${displayCardTitle.value.replace(/\s+/g, "_")}_${new Date().toISOString().slice(0, 10)}.xlsx`;
    a.click();
    URL.revokeObjectURL(url);
    toast.success("Excel downloaded");
  } catch (e) {
    toast.error("Export failed", e instanceof Error ? e.message : "Excel export failed.");
  }
}

let searchDeb: ReturnType<typeof setTimeout> | null = null;
watch(q, () => {
  if (!hasSearched.value) return;
  if (searchDeb) clearTimeout(searchDeb);
  searchDeb = setTimeout(() => {
    searchDeb = null;
    page.value = 1;
    void loadRows();
  }, 350);
});

watch(limit, () => {
  if (!hasSearched.value) return;
  page.value = 1;
  void loadRows();
});

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

onMounted(async () => {
  document.addEventListener("click", onClickOutside);
  await loadOptions();
  (window as unknown as { SNA_JS_CC_SUBSLEDGER_ALL?: { refresh: () => void } }).SNA_JS_CC_SUBSLEDGER_ALL = {
    refresh: () => {
      page.value = 1;
      void loadRows();
    },
  };
});

onUnmounted(() => {
  document.removeEventListener("click", onClickOutside);
  delete (window as unknown as { SNA_JS_CC_SUBSLEDGER_ALL?: unknown }).SNA_JS_CC_SUBSLEDGER_ALL;
  if (custTimer) window.clearTimeout(custTimer);
  if (searchDeb) clearTimeout(searchDeb);
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

      <h1 class="page-title">{{ displayBreadcrumb }}</h1>

      <article
        id="subsidiary-search-params"
        class="overflow-hidden rounded-xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-violet-100/40"
      >
        <div
          class="flex items-center justify-between border-b border-slate-100 bg-gradient-to-r from-violet-50/60 to-white px-4 py-2.5"
        >
          <h2 class="text-base font-semibold text-slate-900">Search Parameter</h2>
          <div class="flex items-center gap-0.5 text-slate-500">
            <button
              type="button"
              class="inline-flex rounded-lg p-1.5 hover:bg-white hover:text-violet-700"
              :title="`Kerisi menu ${legacyMenuId}`"
              aria-label="Menu id"
            >
              <Hash class="h-4 w-4" />
            </button>
            <button
              type="button"
              class="inline-flex rounded-lg p-1.5 hover:bg-white hover:text-violet-700"
              title="Copy breadcrumb and URL"
              aria-label="Copy"
              @click.stop="copyPageSummary()"
            >
              <Copy class="h-4 w-4" />
            </button>
            <span class="inline-flex rounded-lg p-1.5 opacity-50" title="Classic edit N/A"
              ><Pencil class="h-4 w-4"
            /></span>
          </div>
        </div>
        <div class="space-y-4 p-4">
          <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <label class="block">
              <span class="text-xs font-medium text-slate-700"
                >Customer Type <span class="text-red-600">*</span></span
              >
              <select
                v-model="customerType"
                class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
                required
              >
                <option value="" disabled>Select…</option>
                <option v-for="o in options.customerTypes" :key="o.code" :value="o.code">{{ o.label }}</option>
              </select>
            </label>
            <div class="relative block">
              <span class="text-xs font-medium text-slate-700">Customer ID</span>
              <div class="relative mt-1">
                <input
                  v-model="custQuery"
                  type="text"
                  autocomplete="off"
                  placeholder="Search customer…"
                  class="w-full rounded-lg border border-slate-300 px-3 py-2 pr-8 text-sm"
                  @focus="showCustDropdown = true"
                  @input="onCustInput()"
                  @blur="closeCustSoon()"
                />
                <button
                  v-if="customerId"
                  type="button"
                  class="absolute right-1 top-1/2 -translate-y-1/2 rounded p-1 text-slate-400 hover:bg-slate-100"
                  aria-label="Clear customer"
                  @mousedown.prevent="clearCustomer()"
                >
                  <X class="h-4 w-4" />
                </button>
                <div
                  v-if="showCustDropdown && custOptions.length > 0"
                  class="absolute z-40 mt-1 max-h-48 w-full overflow-auto rounded-lg border border-slate-200 bg-white py-1 shadow-lg"
                >
                  <button
                    v-for="c in custOptions"
                    :key="c.id"
                    type="button"
                    class="block w-full px-3 py-2 text-left text-xs hover:bg-violet-50"
                    @mousedown.prevent="pickCustomer(c)"
                  >
                    {{ c.label }}
                  </button>
                </div>
              </div>
            </div>
            <label class="block">
              <span class="text-xs font-medium text-slate-700">Account Code</span>
              <select v-model="accountCode" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">Any</option>
                <option v-for="o in options.accountCodes" :key="o.code" :value="o.code">{{ o.label }}</option>
              </select>
            </label>
            <label class="block">
              <span class="text-xs font-medium text-slate-700">Fund Type</span>
              <select v-model="fundType" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">Any</option>
                <option v-for="o in options.fundTypes" :key="o.code" :value="o.code">{{ o.label }}</option>
              </select>
            </label>
            <label class="block">
              <span class="text-xs font-medium text-slate-700">Cost Center</span>
              <select v-model="costCentre" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">Any</option>
                <option v-for="o in options.costCentres" :key="o.code" :value="o.code">{{ o.label }}</option>
              </select>
            </label>
            <div class="grid grid-cols-1 gap-2 sm:grid-cols-[1fr_auto_1fr] sm:items-end">
              <label class="block sm:col-span-3">
                <span class="text-xs font-medium text-slate-700">Date Range</span>
              </label>
              <input
                v-model="dateStart"
                type="date"
                class="rounded-lg border border-slate-300 px-3 py-2 text-sm"
                aria-label="Date from"
              />
              <span class="hidden pb-2 text-center text-xs text-slate-500 sm:block">to</span>
              <input
                v-model="dateEnd"
                type="date"
                class="rounded-lg border border-slate-300 px-3 py-2 text-sm"
                aria-label="Date to"
              />
            </div>
            <label class="block">
              <span class="text-xs font-medium text-slate-700">Statement Date</span>
              <input
                v-model="statementDate"
                type="date"
                class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
              />
            </label>
          </div>
          <p class="text-xs leading-relaxed text-slate-600">
            Use
            <button
              type="button"
              class="font-semibold text-violet-700 underline decoration-violet-300 underline-offset-2 hover:text-violet-800"
              @click="scrollToSearchParameters()"
            >
              Search Parameter
            </button>
            above for account / fund / dates, or the listing toolbar to filter loaded rows. Primary data:
            <span class="font-medium text-slate-800">rep_aging_debtor</span>
            (MySQL 8 window balance).
          </p>
          <div class="flex flex-wrap items-center justify-end gap-2">
            <button
              type="button"
              class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
              @click="resetSearchParameters()"
            >
              Reset
            </button>
            <button
              type="button"
              class="inline-flex items-center gap-2 rounded-lg bg-violet-600 px-5 py-2 text-sm font-medium text-white shadow-sm shadow-violet-500/25 hover:bg-violet-700"
              @click="runSearch()"
            >
              <Search class="h-4 w-4" />
              Search
            </button>
          </div>
        </div>
      </article>

      <article
        class="overflow-hidden rounded-xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-100"
      >
        <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
          <h2 class="text-base font-semibold text-slate-900">{{ displayCardTitle }}</h2>
          <div ref="overflowRoot" class="relative">
            <button
              type="button"
              class="rounded-lg p-2 text-slate-500 hover:bg-slate-100"
              aria-label="More actions"
              @click.stop="toggleOverflow"
            >
              <MoreVertical class="h-4 w-4" />
            </button>
            <div
              v-if="overflowOpen"
              class="absolute right-0 z-30 mt-1 w-44 rounded-lg border border-slate-200 bg-white py-1 shadow-lg"
              @click.stop
            >
              <button
                type="button"
                class="block w-full px-3 py-2 text-left text-xs text-slate-700 hover:bg-slate-50"
                @click="
                  closeOverflow();
                  handleSaveTemplate();
                "
              >
                Save template
              </button>
              <button
                type="button"
                class="block w-full px-3 py-2 text-left text-xs text-slate-700 hover:bg-slate-50"
                @click="
                  closeOverflow();
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
                  closeOverflow();
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
                  closeOverflow();
                  handleGroupList();
                "
              >
                Group list
              </button>
            </div>
          </div>
        </div>
        <div class="space-y-4 p-4">
          <div class="flex flex-wrap items-end gap-x-4 gap-y-3">
            <div class="flex items-center gap-2">
              <label class="text-xs font-medium text-slate-600">Display</label>
              <select
                v-model.number="limit"
                class="rounded-lg border border-slate-300 px-2 py-1.5 text-sm"
                :disabled="!hasSearched"
                @change="page = 1; hasSearched && void loadRows()"
              >
                <option v-for="n in [5, 10, 25, 50, 100]" :key="n" :value="n">{{ n }}</option>
              </select>
            </div>
            <button
              type="button"
              class="inline-flex items-center gap-0.5 pb-1 text-xs font-medium text-violet-600 hover:text-violet-800 hover:underline"
              @click="scrollToSearchParameters()"
            >
              <ChevronRight class="h-3.5 w-3.5 shrink-0" aria-hidden="true" />
              Refine parameters (scroll)
            </button>
            <div class="flex min-w-0 flex-1 flex-wrap items-end justify-end gap-2 sm:min-w-[20rem]">
              <label class="flex min-w-0 flex-col gap-1">
                <span class="text-xs font-medium text-slate-600">Search</span>
                <div class="relative w-full min-w-[10rem] sm:w-60">
                  <Search
                    class="pointer-events-none absolute left-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400"
                  />
                  <input
                    v-model="q"
                    type="search"
                    placeholder="Filter rows…"
                    class="w-full rounded-lg border border-slate-300 py-1.5 pl-8 pr-8 text-sm"
                    autocomplete="off"
                    :disabled="!hasSearched"
                    @keyup.enter="page = 1; hasSearched && void loadRows()"
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
              </label>
              <button
                type="button"
                class="inline-flex items-center gap-1 rounded-lg border border-violet-200 bg-violet-50/80 px-3 py-1.5 text-sm font-medium text-violet-800 shadow-sm hover:bg-violet-100"
                @click="scrollToSearchParameters()"
              >
                <Filter class="h-4 w-4" />
                Parameters
              </button>
            </div>
          </div>

          <div class="overflow-x-auto rounded-lg border border-slate-200">
            <div :class="rows.length > 10 ? 'max-h-[min(28rem,70vh)] overflow-y-auto' : ''">
              <table class="w-full min-w-[1800px] text-xs">
              <thead class="sticky top-0 z-10 bg-violet-600 text-white shadow-sm">
                <tr>
                  <th class="whitespace-nowrap px-2 py-2.5 text-left font-semibold uppercase">No</th>
                  <th class="whitespace-nowrap px-2 py-2.5 text-left font-semibold uppercase">Customer Type</th>
                  <th
                    class="cursor-pointer whitespace-nowrap px-2 py-2.5 text-left font-semibold uppercase"
                    @click="toggleSort('pde_payto_id')"
                  >
                    Customer ID
                  </th>
                  <th class="whitespace-nowrap px-2 py-2.5 text-left font-semibold uppercase">Customer Name</th>
                  <th class="cursor-pointer whitespace-nowrap px-2 py-2.5 text-left font-semibold uppercase" @click="toggleSort('pde_document_no')">Document No</th>
                  <th class="whitespace-nowrap px-2 py-2.5 text-left font-semibold uppercase">Document Description</th>
                  <th class="whitespace-nowrap px-2 py-2.5 text-left font-semibold uppercase">Fund</th>
                  <th class="whitespace-nowrap px-2 py-2.5 text-left font-semibold uppercase">Activity Code</th>
                  <th class="whitespace-nowrap px-2 py-2.5 text-left font-semibold uppercase">PTJ</th>
                  <th class="whitespace-nowrap px-2 py-2.5 text-left font-semibold uppercase">Cost Center</th>
                  <th class="whitespace-nowrap px-2 py-2.5 text-left font-semibold uppercase">S/O Code</th>
                  <th class="cursor-pointer whitespace-nowrap px-2 py-2.5 text-left font-semibold uppercase" @click="toggleSort('acm_acct_code')">Account Code</th>
                  <th class="whitespace-nowrap px-2 py-2.5 text-left font-semibold uppercase">Account Description</th>
                  <th class="whitespace-nowrap px-2 py-2.5 text-left font-semibold uppercase">Reference 1</th>
                  <th class="whitespace-nowrap px-2 py-2.5 text-left font-semibold uppercase">Reference 2</th>
                  <th class="whitespace-nowrap px-2 py-2.5 text-left font-semibold uppercase">Reference 3</th>
                  <th class="cursor-pointer whitespace-nowrap px-2 py-2.5 text-left font-semibold uppercase" @click="toggleSort('pde_trans_date')">Transaction Date</th>
                  <th class="cursor-pointer whitespace-nowrap px-2 py-2.5 text-right font-semibold uppercase" @click="toggleSort('debit')">Amount Debit (RM)</th>
                  <th class="cursor-pointer whitespace-nowrap px-2 py-2.5 text-right font-semibold uppercase" @click="toggleSort('credit')">Amount Credit (RM)</th>
                  <th class="cursor-pointer whitespace-nowrap px-2 py-2.5 text-right font-semibold uppercase" @click="toggleSort('balance')">Amount Balance (RM)</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 bg-white">
                <tr v-if="loading">
                  <td colspan="20" class="px-3 py-8 text-center text-sm text-slate-500">Loading…</td>
                </tr>
                <tr v-else-if="!hasSearched">
                  <td colspan="20" class="px-3 py-10 text-center text-sm text-slate-500">
                    Set filters and click <span class="font-medium text-slate-800">Search</span> to load lines from
                    <span class="font-medium text-slate-800">rep_aging_debtor</span>.
                  </td>
                </tr>
                <tr v-else-if="rows.length === 0">
                  <td colspan="20" class="px-3 py-10 text-center text-sm text-slate-500">No records</td>
                </tr>
                <template v-else>
                  <tr
                    v-for="r in rows"
                    :key="r.pdePostingDetlId"
                    class="hover:bg-slate-50"
                  >
                    <td class="whitespace-nowrap px-2 py-2">{{ r.index }}</td>
                    <td class="px-2 py-2">{{ r.customerTypeLabel ?? "—" }}</td>
                    <td class="px-2 py-2">{{ r.pdePaytoId ?? "—" }}</td>
                    <td class="max-w-[10rem] truncate px-2 py-2" :title="r.pdePaytoName ?? ''">{{ r.pdePaytoName ?? "—" }}</td>
                    <td class="px-2 py-2 font-medium text-slate-900">{{ r.pdeDocumentNo ?? "—" }}</td>
                    <td class="max-w-[12rem] truncate px-2 py-2" :title="r.docDescription ?? ''">{{ r.docDescription ?? "—" }}</td>
                    <td class="px-2 py-2">{{ r.fundType ?? "—" }}</td>
                    <td class="px-2 py-2">{{ r.activityCode ?? "—" }}</td>
                    <td class="px-2 py-2">{{ r.ounCode ?? "—" }}</td>
                    <td class="px-2 py-2">{{ r.costCentre ?? "—" }}</td>
                    <td class="px-2 py-2">{{ r.soCode ?? "—" }}</td>
                    <td class="px-2 py-2">{{ r.acctCode ?? "—" }}</td>
                    <td class="max-w-[12rem] truncate px-2 py-2" :title="r.acctDesc ?? ''">{{ r.acctDesc ?? "—" }}</td>
                    <td class="px-2 py-2">{{ r.reference1 ?? "—" }}</td>
                    <td class="px-2 py-2">{{ r.reference2 ?? "—" }}</td>
                    <td class="px-2 py-2">{{ r.reference3 ?? "—" }}</td>
                    <td class="whitespace-nowrap px-2 py-2">{{ r.transDate ?? "—" }}</td>
                    <td class="whitespace-nowrap px-2 py-2 text-right tabular-nums">{{ r.debit > 0 ? fmtMoney(r.debit) : "—" }}</td>
                    <td class="whitespace-nowrap px-2 py-2 text-right tabular-nums">{{ r.credit > 0 ? fmtMoney(r.credit) : "—" }}</td>
                    <td class="whitespace-nowrap px-2 py-2 text-right tabular-nums font-medium">{{ fmtMoney(r.balance) }}</td>
                  </tr>
                </template>
              </tbody>
            </table>
            </div>
          </div>

          <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-3">
            <div class="text-xs text-slate-500">Showing {{ startIdx }}-{{ endIdx }} of {{ total }}</div>
            <div class="flex flex-wrap items-center gap-2">
              <button
                type="button"
                :disabled="page <= 1 || loading || !hasSearched"
                class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium disabled:opacity-50"
                @click="prevPage()"
              >
                Prev
              </button>
              <span class="text-xs text-slate-600">Page {{ page }} / {{ totalPages }}</span>
              <button
                type="button"
                :disabled="page >= totalPages || loading || !hasSearched"
                class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium disabled:opacity-50"
                @click="nextPage()"
              >
                Next
              </button>
              <div class="mx-1 hidden h-5 w-px bg-slate-200 sm:block" />
              <button
                type="button"
                class="inline-flex items-center gap-1.5 rounded-lg bg-violet-600 px-3 py-1.5 text-xs font-medium text-white shadow-sm hover:bg-violet-700 disabled:opacity-50"
                :disabled="!hasSearched || rows.length === 0"
                @click="handleDownloadCSV()"
              >
                <FileDown class="h-3.5 w-3.5" />
                Download CSV
              </button>
              <button
                type="button"
                class="inline-flex items-center gap-1.5 rounded-lg border border-violet-200 bg-violet-50 px-3 py-1.5 text-xs font-medium text-violet-900 hover:bg-violet-100 disabled:opacity-50"
                :disabled="!hasSearched || rows.length === 0"
                @click="exportExcel()"
              >
                <FileSpreadsheet class="h-3.5 w-3.5" />
                Excel
              </button>
              <button
                type="button"
                class="inline-flex items-center gap-1.5 rounded-lg bg-violet-600 px-3 py-1.5 text-xs font-medium text-white shadow-sm hover:bg-violet-700 disabled:opacity-50"
                :disabled="!hasSearched || rows.length === 0"
                @click="handleDownloadPDF()"
              >
                <Download class="h-3.5 w-3.5" />
                Print PDF
              </button>
            </div>
          </div>
        </div>
      </article>
    </div>
  </AdminLayout>
</template>
