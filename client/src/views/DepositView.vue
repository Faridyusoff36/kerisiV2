<script setup lang="ts">
/**
 * Credit Control / Deposit (PAGEID 1445, MENUID 1809)
 *
 * Source: FIMS BL `ZR_CREDITCONTROL_DEPOSIT_API` (DT_LIST_OF_DEPOSIT). Joined
 * `deposit_master` + `deposit_details` + `deposit_category` listing with:
 *   - Top filters (category, pay-to type, transaction type, currency, PTJ,
 *     date range)
 *   - Smart filters (deposit no, vendor code, ref no, acct code, amount,
 *     fund type) backed by `/credit-control/deposit/autosuggest`
 *   - Signed footer sum: CR rows negated (legacy report behaviour).
 *   - Kerisi 2.0 UI: Search Parameter card (violet), listing toolbar, purple table header.
 */
import { computed, onMounted, onUnmounted, ref, watch } from "vue";
import {
  ChevronRight,
  Copy,
  Download,
  FileDown,
  FileSpreadsheet,
  Filter,
  Hash,
  MoreVertical,
  Pencil,
  Search,
  X,
} from "lucide-vue-next";
import AdminLayout from "@/layouts/AdminLayout.vue";
import { useDatatableFeatures } from "@/composables/useDatatableFeatures";
import type { DatatableRefApi } from "@/composables/useDatatableFeatures";
import {
  autosuggestDeposit,
  fetchDepositOptions,
  listDeposits,
} from "@/api/cms";
import { useToast } from "@/composables/useToast";
import type { DepositOptions, DepositRow } from "@/types";

const toast = useToast();
const datatableRef = ref<DatatableRefApi | null>(null);

const rows = ref<DepositRow[]>([]);
const loading = ref(false);
const total = ref(0);
const footer = ref({ ddtAmt: 0 });
const page = ref(1);
const limit = ref(10);
const q = ref("");

// Top filters
const tfCategory = ref("");
const tfPayToType = ref("");
const tfTransactionType = ref("");
const tfCurrency = ref("");
const tfPtj = ref("");
const tfDateFrom = ref("");
const tfDateTo = ref("");

// Smart filter
const showSmartFilter = ref(false);
const smartFilter = ref<Record<string, string>>({
  smart_deposit_no: "",
  smart_vendor_code: "",
  smart_ref_no: "",
  smart_acct_code: "",
  smart_amount: "",
  smart_fund_type: "",
});

const options = ref<DepositOptions>({
  category: [],
  payToType: [],
  currency: [],
  ptj: [],
});

const totalPages = computed(() =>
  total.value ? Math.max(1, Math.ceil(total.value / limit.value)) : 1,
);
const startIdx = computed(() =>
  total.value === 0 ? 0 : (page.value - 1) * limit.value + 1,
);
const endIdx = computed(() =>
  Math.min(page.value * limit.value, total.value),
);

function currencyMyr(amount: number): string {
  return new Intl.NumberFormat("en-MY", {
    style: "currency",
    currency: "MYR",
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(Number.isFinite(amount) ? amount : 0);
}

function buildQuery(): string {
  const params = new URLSearchParams({
    page: String(page.value),
    limit: String(limit.value),
  });
  if (q.value.trim()) params.set("q", q.value.trim());
  if (tfCategory.value) params.set("category", tfCategory.value);
  if (tfPayToType.value) params.set("pay_to_type", tfPayToType.value);
  if (tfTransactionType.value)
    params.set("transaction_type", tfTransactionType.value);
  if (tfCurrency.value) params.set("currency", tfCurrency.value);
  if (tfPtj.value) params.set("ptj", tfPtj.value);
  if (tfDateFrom.value) params.set("date_from", tfDateFrom.value);
  if (tfDateTo.value) params.set("date_to", tfDateTo.value);
  for (const [k, v] of Object.entries(smartFilter.value)) {
    if (v && v.trim()) params.set(k, v.trim());
  }
  return `?${params.toString()}`;
}

async function loadRows() {
  loading.value = true;
  try {
    const res = await listDeposits(buildQuery());
    rows.value = res.data;
    total.value = Number(res.meta?.total ?? 0);
    const f = (res.meta?.footer as { ddtAmt?: number } | undefined) ?? {};
    footer.value = { ddtAmt: Number(f.ddtAmt ?? 0) };
  } catch (e) {
    toast.error(
      "Load failed",
      e instanceof Error ? e.message : "Unable to load deposits.",
    );
  } finally {
    loading.value = false;
  }
}

async function loadOptions() {
  try {
    const res = await fetchDepositOptions();
    options.value = res.data;
  } catch {
    // Silent — dropdowns remain empty; the legacy page behaves the same way.
  }
}

function applyTopFilters() {
  page.value = 1;
  void loadRows();
}

function copyPageSummary() {
  const lines = [
    "Credit Control / Deposit",
    typeof window !== "undefined" ? window.location.href : "",
  ].filter(Boolean);
  void navigator.clipboard.writeText(lines.join("\n")).then(
    () => toast.success("Copied", "Breadcrumb and link copied."),
    () => toast.error("Copy failed", "Clipboard not available."),
  );
}

function resetTopFilters() {
  tfCategory.value = "";
  tfPayToType.value = "";
  tfTransactionType.value = "";
  tfCurrency.value = "";
  tfPtj.value = "";
  tfDateFrom.value = "";
  tfDateTo.value = "";
  applyTopFilters();
}

function applySmartFilter() {
  showSmartFilter.value = false;
  page.value = 1;
  void loadRows();
}

function resetSmartFilter() {
  Object.keys(smartFilter.value).forEach((k) => {
    smartFilter.value[k] = "";
  });
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

const exportColumns = [
  "Trans Date",
  "Deposit No",
  "Pay to Type",
  "Vendor Code",
  "Vendor Name",
  "Ref No",
  "Doc No",
  "Fund",
  "Activity",
  "PTJ",
  "Cost Centre",
  "Account Code",
  "Description",
  "Currency",
  "Entry Amount",
  "Amount",
  "Type",
];

const {
  templateFileInputRef,
  onTemplateFileChange,
  handleDownloadPDF,
  handleDownloadCSV,
} = useDatatableFeatures({
  pageName: "Credit Control - Deposit",
  apiDataPath: "/credit-control/deposit",
  defaultExportColumns: exportColumns,
  getFilteredList: () =>
    rows.value.map((r) => ({
      "Trans Date": r.transactionDate ?? "",
      "Deposit No": r.dpmDepositNo ?? "",
      "Pay to Type": r.dpmPaytoType ?? "",
      "Vendor Code": r.vcsVendorCode ?? "",
      "Vendor Name": r.dpmVendorName ?? "",
      "Ref No": r.dpmRefNo ?? "",
      "Doc No": r.ddtDocNo ?? "",
      Fund: r.ftyFundType ?? "",
      Activity: r.atActivityCode ?? "",
      PTJ: r.ounCode ?? "",
      "Cost Centre": r.ccrCostcentre ?? "",
      "Account Code": r.acmAcctCode ?? "",
      Description: r.acmAcctDesc ?? "",
      Currency: r.ddtCurrencyCode ?? "",
      "Entry Amount": currencyMyr(r.ddtEntAmt),
      Amount: currencyMyr(r.ddtAmt),
      Type: r.ddtType ?? "",
    })),
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
    const ws = wb.addWorksheet("Deposit");
    ws.addRow(["No", ...exportColumns]);
    rows.value.forEach((r, i) => {
      ws.addRow([
        i + 1,
        r.transactionDate ?? "",
        r.dpmDepositNo ?? "",
        r.dpmPaytoType ?? "",
        r.vcsVendorCode ?? "",
        r.dpmVendorName ?? "",
        r.dpmRefNo ?? "",
        r.ddtDocNo ?? "",
        r.ftyFundType ?? "",
        r.atActivityCode ?? "",
        r.ounCode ?? "",
        r.ccrCostcentre ?? "",
        r.acmAcctCode ?? "",
        r.acmAcctDesc ?? "",
        r.ddtCurrencyCode ?? "",
        r.ddtEntAmt,
        r.ddtAmt,
        r.ddtType ?? "",
      ]);
    });
    const buf = await wb.xlsx.writeBuffer();
    const blob = new Blob([buf], {
      type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
    });
    const url = URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.href = url;
    a.download = `Deposit_${new Date().toISOString().slice(0, 10)}.xlsx`;
    a.click();
    URL.revokeObjectURL(url);
    toast.success("Excel downloaded");
  } catch (e) {
    toast.error(
      "Export failed",
      e instanceof Error ? e.message : "Excel export failed.",
    );
  }
}

// Debounce smart-filter autosuggest panels (separate from the combobox fields
// themselves so the panel doesn't reload on every keystroke).
const suggestions = ref<Record<string, { id: string; label: string }[]>>({
  smart_deposit_no: [],
  smart_vendor_code: [],
  smart_ref_no: [],
  smart_acct_code: [],
  smart_amount: [],
  smart_fund_type: [],
});
const suggestMap: Record<string, string> = {
  smart_deposit_no: "deposit_no",
  smart_vendor_code: "vendor_code",
  smart_ref_no: "ref_no",
  smart_acct_code: "acct_code",
  smart_amount: "amount",
  smart_fund_type: "fund_type",
};

const suggestTimers: Record<string, number | null> = {};
function onSmartInput(key: string) {
  const fieldKey = suggestMap[key];
  if (!fieldKey) return;
  if (suggestTimers[key]) window.clearTimeout(suggestTimers[key] ?? 0);
  suggestTimers[key] = window.setTimeout(() => {
    suggestTimers[key] = null;
    void (async () => {
      try {
        const res = await autosuggestDeposit(fieldKey, smartFilter.value[key], 10);
        suggestions.value[key] = res.data;
      } catch {
        suggestions.value[key] = [];
      }
    })();
  }, 300);
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

onMounted(async () => {
  await Promise.all([loadOptions(), loadRows()]);
});

onUnmounted(() => {
  if (searchDebounce) clearTimeout(searchDebounce);
  Object.values(suggestTimers).forEach((t) => {
    if (t) window.clearTimeout(t);
  });
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

      <h1 class="page-title">Credit Control / Deposit</h1>

      <!-- Kerisi 2.0 shell: Search Parameter -->
      <article
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
              title="Kerisi menu 1809"
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
            <span class="inline-flex rounded-lg p-1.5 opacity-50" title="Classic edit (N/A)"
              ><Pencil class="h-4 w-4"
            /></span>
          </div>
        </div>
        <div class="space-y-4 p-4">
          <div class="grid gap-3 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            <div>
              <label class="mb-1 block text-xs font-medium text-slate-600">Category</label>
              <select v-model="tfCategory" class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-sm">
                <option value="">All</option>
                <option v-for="o in options.category" :key="o.id" :value="o.id">{{ o.label }}</option>
              </select>
            </div>
            <div>
              <label class="mb-1 block text-xs font-medium text-slate-600">Pay-to Type</label>
              <select v-model="tfPayToType" class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-sm">
                <option value="">All</option>
                <option v-for="o in options.payToType" :key="o.id" :value="o.id">{{ o.label }}</option>
              </select>
            </div>
            <div>
              <label class="mb-1 block text-xs font-medium text-slate-600">Transaction Type</label>
              <select v-model="tfTransactionType" class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-sm">
                <option value="">All</option>
                <option value="DT">Debit</option>
                <option value="CR">Credit</option>
              </select>
            </div>
            <div>
              <label class="mb-1 block text-xs font-medium text-slate-600">Currency</label>
              <select v-model="tfCurrency" class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-sm">
                <option value="">All</option>
                <option v-for="o in options.currency" :key="o.id" :value="o.id">{{ o.label }}</option>
              </select>
            </div>
            <div>
              <label class="mb-1 block text-xs font-medium text-slate-600">PTJ</label>
              <select v-model="tfPtj" class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-sm">
                <option value="">All</option>
                <option v-for="o in options.ptj" :key="o.id" :value="o.id">{{ o.label }}</option>
              </select>
            </div>
            <div>
              <label class="mb-1 block text-xs font-medium text-slate-600">Date From</label>
              <input v-model="tfDateFrom" type="date" class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-sm" />
            </div>
            <div>
              <label class="mb-1 block text-xs font-medium text-slate-600">Date To</label>
              <input v-model="tfDateTo" type="date" class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-sm" />
            </div>
          </div>
          <p class="text-xs leading-relaxed text-slate-600">
            Use
            <button
              type="button"
              class="font-semibold text-violet-700 underline decoration-violet-300 underline-offset-2 hover:text-violet-800"
              @click="showSmartFilter = true"
            >
              More filters
            </button>
            on the listing card for deposit no / vendor / ref / account / amount / fund, or click
            <strong class="font-medium text-slate-800">Search</strong>
            to apply these parameters.
          </p>
          <div class="flex flex-wrap items-center justify-end gap-2">
            <button
              type="button"
              class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
              @click="resetTopFilters"
            >
              Reset
            </button>
            <button
              type="button"
              class="inline-flex items-center gap-2 rounded-lg bg-violet-600 px-5 py-2 text-sm font-medium text-white shadow-sm shadow-violet-500/25 hover:bg-violet-700"
              @click="applyTopFilters"
            >
              <Search class="h-4 w-4" />
              Search
            </button>
          </div>
        </div>
      </article>

      <!-- Listing card -->
      <article class="overflow-hidden rounded-xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-100">
        <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
<<<<<<< HEAD
          <h1 class="text-base font-semibold text-slate-900">List of Deposit</h1>
          <button type="button" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100" aria-label="More">
            <MoreVertical class="h-4 w-4" />
          </button>
=======
          <h2 class="text-base font-semibold text-slate-900">List of Deposit</h2>
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
>>>>>>> pr/12
        </div>
        <div class="space-y-4 p-4">
          <div class="flex flex-wrap items-end gap-x-4 gap-y-3">
            <div class="flex items-center gap-2">
              <label class="text-xs font-medium text-slate-600">Display</label>
              <select
                v-model.number="limit"
                class="rounded-lg border border-slate-300 px-2 py-1.5 text-sm"
                @change="page = 1; void loadRows()"
              >
                <option v-for="n in [5, 10, 25, 50, 100]" :key="n" :value="n">{{ n }}</option>
              </select>
              <button
                type="button"
                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium"
                @click="showSmartFilter = true"
              >
                <Filter class="h-3.5 w-3.5" />
                Smart Filter
              </button>
            </div>
<<<<<<< HEAD
            <div class="flex items-center gap-2">
              <label class="text-xs font-medium text-slate-600">Search</label>
              <div class="relative">
                <Search class="pointer-events-none absolute left-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400" />
                <input
                  v-model="q"
                  type="search"
                  placeholder="Filter rows..."
                  class="w-60 rounded-lg border border-slate-300 py-1.5 pl-8 pr-8 text-sm"
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
=======
            <button
              type="button"
              class="inline-flex items-center gap-0.5 pb-1 text-xs font-medium text-violet-600 hover:text-violet-800 hover:underline"
              @click="showSmartFilter = true"
            >
              <ChevronRight class="h-3.5 w-3.5 shrink-0" aria-hidden="true" />
              More filters (modal)
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
              </label>
              <button
                type="button"
                class="inline-flex items-center gap-1 rounded-lg border border-violet-200 bg-violet-50/80 px-3 py-1.5 text-sm font-medium text-violet-800 shadow-sm hover:bg-violet-100"
                @click="showSmartFilter = true"
              >
                <Filter class="h-4 w-4" />
                Filter
              </button>
>>>>>>> pr/12
            </div>
          </div>

          <div class="overflow-x-auto rounded-lg border border-slate-200">
<<<<<<< HEAD
            <div :class="rows.length > 10 ? 'max-h-[480px] overflow-y-auto' : ''">
              <table class="w-full min-w-[1400px] text-sm">
                <thead class="sticky top-0 bg-slate-50">
                  <tr class="border-b border-slate-200 text-left">
                    <th class="px-3 py-2 text-xs font-semibold uppercase">No</th>
                    <th class="px-3 py-2 text-xs font-semibold uppercase">Trans Date</th>
                    <th class="px-3 py-2 text-xs font-semibold uppercase">Deposit No</th>
                    <th class="px-3 py-2 text-xs font-semibold uppercase">Pay-to</th>
                    <th class="px-3 py-2 text-xs font-semibold uppercase">Vendor Code</th>
                    <th class="px-3 py-2 text-xs font-semibold uppercase">Vendor Name</th>
                    <th class="px-3 py-2 text-xs font-semibold uppercase">Ref No</th>
                    <th class="px-3 py-2 text-xs font-semibold uppercase">Doc No</th>
                    <th class="px-3 py-2 text-xs font-semibold uppercase">Fund</th>
                    <th class="px-3 py-2 text-xs font-semibold uppercase">Activity</th>
                    <th class="px-3 py-2 text-xs font-semibold uppercase">PTJ</th>
                    <th class="px-3 py-2 text-xs font-semibold uppercase">Cost Centre</th>
                    <th class="px-3 py-2 text-xs font-semibold uppercase">Acct Code</th>
                    <th class="px-3 py-2 text-xs font-semibold uppercase">Ccy</th>
                    <th class="px-3 py-2 text-right text-xs font-semibold uppercase">Amount</th>
                    <th class="px-3 py-2 text-xs font-semibold uppercase">Type</th>
=======
            <div :class="rows.length > 10 ? 'max-h-[min(28rem,70vh)] overflow-y-auto' : ''">
              <table class="w-full min-w-[1400px] text-xs">
                <thead class="sticky top-0 z-10 bg-violet-600 text-white shadow-sm">
                  <tr>
                    <th class="whitespace-nowrap px-3 py-2.5 text-left font-semibold uppercase">No</th>
                    <th class="whitespace-nowrap px-3 py-2.5 text-left font-semibold uppercase">Trans Date</th>
                    <th class="whitespace-nowrap px-3 py-2.5 text-left font-semibold uppercase">Deposit No</th>
                    <th class="whitespace-nowrap px-3 py-2.5 text-left font-semibold uppercase">Pay-to</th>
                    <th class="whitespace-nowrap px-3 py-2.5 text-left font-semibold uppercase">Vendor Code</th>
                    <th class="whitespace-nowrap px-3 py-2.5 text-left font-semibold uppercase">Vendor Name</th>
                    <th class="whitespace-nowrap px-3 py-2.5 text-left font-semibold uppercase">Ref No</th>
                    <th class="whitespace-nowrap px-3 py-2.5 text-left font-semibold uppercase">Doc No</th>
                    <th class="whitespace-nowrap px-3 py-2.5 text-left font-semibold uppercase">Fund</th>
                    <th class="whitespace-nowrap px-3 py-2.5 text-left font-semibold uppercase">Activity</th>
                    <th class="whitespace-nowrap px-3 py-2.5 text-left font-semibold uppercase">PTJ</th>
                    <th class="whitespace-nowrap px-3 py-2.5 text-left font-semibold uppercase">Cost Centre</th>
                    <th class="whitespace-nowrap px-3 py-2.5 text-left font-semibold uppercase">Acct Code</th>
                    <th class="whitespace-nowrap px-3 py-2.5 text-left font-semibold uppercase">Ccy</th>
                    <th class="whitespace-nowrap px-3 py-2.5 text-right font-semibold uppercase">Amount</th>
                    <th class="whitespace-nowrap px-3 py-2.5 text-left font-semibold uppercase">Type</th>
>>>>>>> pr/12
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                  <tr v-if="loading">
                    <td colspan="16" class="px-3 py-6 text-center text-sm text-slate-500">Loading...</td>
                  </tr>
                  <tr v-else-if="rows.length === 0">
                    <td colspan="16" class="px-3 py-6 text-center text-sm text-slate-500">No records found.</td>
                  </tr>
                  <template v-else>
                    <tr
                      v-for="row in rows"
                      :key="`${row.dpmDepositMasterId}-${row.ddtDocNo ?? ''}-${row.index}`"
                      class="hover:bg-slate-50"
                    >
                      <td class="px-3 py-2">{{ row.index }}</td>
                      <td class="px-3 py-2">{{ row.transactionDate ?? "-" }}</td>
                      <td class="px-3 py-2 font-medium text-slate-900">{{ row.dpmDepositNo ?? "-" }}</td>
                      <td class="px-3 py-2">{{ row.dpmPaytoType ?? "-" }}</td>
                      <td class="px-3 py-2">{{ row.vcsVendorCode ?? "-" }}</td>
                      <td class="px-3 py-2">{{ row.dpmVendorName ?? "-" }}</td>
                      <td class="px-3 py-2">{{ row.dpmRefNo ?? "-" }}</td>
                      <td class="px-3 py-2">{{ row.ddtDocNo ?? "-" }}</td>
                      <td class="px-3 py-2">{{ row.ftyFundType ?? "-" }}</td>
                      <td class="px-3 py-2">{{ row.atActivityCode ?? "-" }}</td>
                      <td class="px-3 py-2">{{ row.ounCode ?? "-" }}</td>
                      <td class="px-3 py-2">{{ row.ccrCostcentre ?? "-" }}</td>
                      <td class="px-3 py-2">{{ row.acmAcctCode ?? "-" }}</td>
                      <td class="px-3 py-2">{{ row.ddtCurrencyCode ?? "-" }}</td>
                      <td class="px-3 py-2 text-right tabular-nums">{{ currencyMyr(row.ddtAmt) }}</td>
                      <td class="px-3 py-2">
                        <span
                          class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium"
                          :class="row.ddtType === 'DT' ? 'bg-sky-100 text-sky-700' : 'bg-amber-100 text-amber-700'"
                        >
                          {{ row.ddtType ?? "-" }}
                        </span>
                      </td>
                    </tr>
                  </template>
                </tbody>
                <tfoot v-if="rows.length > 0" class="sticky bottom-0 border-t border-violet-200 bg-violet-50/90 backdrop-blur-sm">
                  <tr>
                    <td colspan="14" class="px-3 py-2 text-right text-xs font-semibold uppercase text-violet-900">Total (signed)</td>
                    <td class="px-3 py-2 text-right text-sm font-semibold tabular-nums text-violet-950">
                      {{ currencyMyr(footer.ddtAmt) }}
                    </td>
                    <td></td>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>

          <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-3">
            <div class="text-xs text-slate-500">Showing {{ startIdx }}-{{ endIdx }} of {{ total }}</div>
            <div class="flex items-center gap-2">
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
                class="inline-flex items-center gap-1.5 rounded-lg bg-violet-600 px-3 py-1.5 text-xs font-medium text-white shadow-sm hover:bg-violet-700"
                @click="handleDownloadCSV"
              >
                <FileDown class="h-3.5 w-3.5" />CSV
              </button>
              <button
                type="button"
                class="inline-flex items-center gap-1.5 rounded-lg border border-violet-200 bg-violet-50 px-3 py-1.5 text-xs font-medium text-violet-900 hover:bg-violet-100"
                @click="exportExcel"
              >
                <FileSpreadsheet class="h-3.5 w-3.5" />Excel
              </button>
              <button
                type="button"
                class="inline-flex items-center gap-1.5 rounded-lg bg-violet-600 px-3 py-1.5 text-xs font-medium text-white shadow-sm hover:bg-violet-700"
                @click="handleDownloadPDF"
              >
                <Download class="h-3.5 w-3.5" />PDF
              </button>
            </div>
          </div>
        </div>
      </article>
    </div>

    <!-- Smart filter modal -->
    <Teleport to="body">
      <div
        v-if="showSmartFilter"
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm"
        role="dialog"
        aria-modal="true"
        @click.self="showSmartFilter = false"
      >
        <div class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-xl border border-slate-200 bg-white shadow-2xl">
          <div class="flex items-center justify-between border-b border-slate-100 px-5 py-3">
            <h2 class="text-base font-semibold text-slate-900">Smart filter</h2>
            <button type="button" class="rounded-lg p-1 text-slate-500 hover:bg-slate-100" @click="showSmartFilter = false">
              <X class="h-4 w-4" />
            </button>
          </div>
          <div class="grid gap-3 p-5 md:grid-cols-2">
            <div v-for="(_, key) in smartFilter" :key="key">
              <label class="mb-1 block text-xs font-medium capitalize text-slate-600">
                {{ key.replace('smart_', '').replace('_', ' ') }}
              </label>
              <input
                v-model="smartFilter[key]"
                type="search"
                :list="`suggest-${key}`"
                class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-sm"
                :placeholder="`Filter by ${key.replace('smart_', '').replace('_', ' ')}`"
                @input="onSmartInput(String(key))"
              />
              <datalist :id="`suggest-${key}`">
                <option v-for="s in suggestions[key] ?? []" :key="s.id" :value="s.id">{{ s.label }}</option>
              </datalist>
            </div>
          </div>
          <div class="flex items-center justify-end gap-2 border-t border-slate-100 px-5 py-3">
            <button
              type="button"
              class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
              @click="resetSmartFilter"
            >
              Reset
            </button>
            <button
              type="button"
              class="rounded-lg bg-violet-600 px-4 py-2 text-sm font-medium text-white hover:bg-violet-700"
              @click="applySmartFilter"
            >
              Apply
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </AdminLayout>
</template>
