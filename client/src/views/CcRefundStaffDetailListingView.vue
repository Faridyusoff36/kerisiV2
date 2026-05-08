<script setup lang="ts">
/**
 * Credit Control / Detail listing refund process lines — MENUID **2290** (sidebar label may still say “Report of Refund Bill”).
 * Legacy: `SNA_API_CC_REFUNDSTAFF_DETAILISTINGPROCESS`.
 *
 * Onload bridge: `SNA_JS_CC_REFUNDSTAFF_DETAILISTINGPROCESS` (`.refresh()`).
 *
 * Open `/admin/kerisi/m/2290`. Layout: Search Parameter card, then Detail Listing Of Refund Process + table.
 */
import { computed, onMounted, onUnmounted, ref, watch } from "vue";
import { ChevronRight, Copy, Filter, Hash, MoreVertical, Pencil, Search, X } from "lucide-vue-next";
import AdminLayout from "@/layouts/AdminLayout.vue";
import { listCreditControlRefundStaffDetail } from "@/api/cms";
import { useDatatableFeatures } from "@/composables/useDatatableFeatures";
import type { DatatableRefApi } from "@/composables/useDatatableFeatures";
import { useToast } from "@/composables/useToast";
import type { CreditControlRefundStaffDetailRow } from "@/types";

const toast = useToast();

type SortKey = "bim_bills_no" | "createddate" | "bid_amt";

interface Col {
  key: keyof CreditControlRefundStaffDetailRow;
  label: string;
  /** Right-align formatted money */
  num?: boolean;
  sort?: SortKey;
}

/** Kerisi 2.0 default listing (screenshot): first 17 columns only; full backend row in “extended” mode. */
const tableColumnsCore: Col[] = [
  { key: "bim_bills_no", label: "Bill No", sort: "bim_bills_no" },
  { key: "bim_bills_type_label", label: "Type" },
  { key: "bim_cust_invoice_no", label: "Invoice No" },
  { key: "bim_cust_invoice_date", label: "Cust Inv Date" },
  { key: "bim_bills_desc", label: "Bill Desc" },
  { key: "bim_bill_amt", label: "Bill Amt", num: true },
  { key: "bim_payto_id", label: "Payee Code" },
  { key: "bim_payto_name", label: "Payee Name" },
  { key: "bim_status", label: "Status" },
  { key: "createddate", label: "Created", sort: "createddate" },
  { key: "bid_payto_type", label: "Line Pay Type" },
  { key: "bid_payto_id", label: "Line Pay To ID" },
  { key: "bid_payto_name", label: "Line Pay To Name" },
  { key: "vsa_vendor_bank", label: "Vendor Bank" },
  { key: "vsa_bank_accno", label: "Bank A/C" },
  { key: "third_party_info", label: "Third Party" },
];

const tableColumnsExtended: Col[] = [
  ...tableColumnsCore,
  { key: "third_party_bank_name", label: "3rd Party Bank" },
  { key: "tra_3rd_bank_acc_no", label: "3rd Party Bank A/C" },
  { key: "fty_fund_type", label: "Fund" },
  { key: "at_activity_code", label: "Activity" },
  { key: "oun_code", label: "PTJ" },
  { key: "ccr_costcentre", label: "CC" },
  { key: "acm_acct_code", label: "Acct" },
  { key: "bid_amt", label: "Line Amt", num: true, sort: "bid_amt" },
  { key: "novoucher", label: "Voucher" },
  { key: "voucherdate", label: "Voucher Date" },
  { key: "paymode", label: "Pay Mode" },
  { key: "eftno", label: "EFT No" },
  { key: "eftdate", label: "EFT Date" },
  { key: "tra_application_no", label: "Application No" },
];

/** Matches legacy dt_list column set; UI defaults to Kerisi 2.0 compact grid. */
const showExtendedColumns = ref(false);
const tableColumns = computed(() => (showExtendedColumns.value ? tableColumnsExtended : tableColumnsCore));

const tableMinWidthClass = computed(() => (showExtendedColumns.value ? "min-w-[2200px]" : "min-w-[1280px]"));

function emptySmartFilter() {
  return {
    bim_bills_no: "",
    bim_payto_id: "",
    bim_payto_name: "",
    bim_cust_invoice_no: "",
    bim_cust_invoice_date: "",
    bim_status: "",
    bid_amt: "",
    createddate: "",
    novoucher: "",
    tra_application_no: "",
  };
}

const smartFilter = ref(emptySmartFilter());
const rows = ref<CreditControlRefundStaffDetailRow[]>([]);
const page = ref(1);
const limit = ref(10);
const q = ref("");
const total = ref(0);
const loading = ref(false);
const showSmartModal = ref(false);
const overflowOpen = ref(false);
const sortBy = ref<SortKey>("bim_bills_no");
const sortDir = ref<"asc" | "desc">("asc");

/** Legacy Shell “Search By” (single mode aligned with Classic BRF detail dt_list). */
const searchByKind = ref<"brf_detail">("brf_detail");

const datatableRef = ref<DatatableRefApi | null>(null);

const {
  isGrouped,
  handleSaveTemplate,
  handleLoadTemplate,
  handleUngroupList,
  handleGroupList,
  templateFileInputRef: templateInputRef,
  onTemplateFileChange: onTplChange,
} = useDatatableFeatures({
  pageName: "Detail Listing Of Refund Process",
  apiDataPath: "/credit-control/refund-staff-detail-listing",
  defaultExportColumns: [],
  getFilteredList: () => [],
  datatableRef,
  searchKeyword: q,
  smartFilter,
  applyFilters: () => void loadRows(),
});

function totalPages() {
  return total.value ? Math.max(1, Math.ceil(total.value / limit.value)) : 1;
}

function showingFrom() {
  return total.value === 0 ? 0 : (page.value - 1) * limit.value + 1;
}

function showingTo() {
  return Math.min(page.value * limit.value, total.value);
}

function fmtMoney(v: unknown): string {
  if (v === null || v === undefined || v === "") return "";
  const n = typeof v === "number" ? v : Number(v);
  if (Number.isNaN(n)) return String(v);
  return new Intl.NumberFormat("en-MY", { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(n);
}

function fmtDateish(v: unknown): string {
  if (v === null || v === undefined || v === "") return "";
  const s = String(v);
  if (/^\d{4}-\d{2}-\d{2}/u.test(s)) {
    const [y, mo, da] = s.slice(0, 10).split("-");
    if (y && mo && da) return `${da}/${mo}/${y}`;
  }
  return s.replace("T", " ").slice(0, 19);
}

function cell(r: CreditControlRefundStaffDetailRow, col: Col): string {
  const v = r[col.key];
  if (v === null || v === undefined) return "";
  if (col.num) return fmtMoney(v);
  if (col.key === "bim_cust_invoice_date" || col.key === "createddate" || col.key === "voucherdate" || col.key === "eftdate") {
    return fmtDateish(v);
  }
  return String(v).replace(/\n/g, " / ");
}

function appendSmartFilterParams(params: URLSearchParams) {
  const sf = smartFilter.value;
  const keys = [
    "bim_bills_no",
    "bim_payto_id",
    "bim_payto_name",
    "bim_cust_invoice_no",
    "bim_cust_invoice_date",
    "bim_status",
    "bid_amt",
    "createddate",
    "novoucher",
    "tra_application_no",
  ] as const;
  for (const k of keys) {
    const v = String(sf[k] ?? "").trim();
    if (v) params.append(`smart_filter[${k}]`, v);
  }
}

async function loadRows() {
  loading.value = true;
  const params = new URLSearchParams({
    page: String(page.value),
    limit: String(limit.value),
    sort_by: sortBy.value,
    sort_dir: sortDir.value,
    ...(q.value.trim() ? { q: q.value.trim() } : {}),
  });
  appendSmartFilterParams(params);
  try {
    const res = await listCreditControlRefundStaffDetail(`?${params.toString()}`);
    rows.value = res.data;
    total.value = Number(res.meta?.total ?? 0);
  } catch (e) {
    toast.error("Load failed", e instanceof Error ? e.message : "Unable to load report.");
  } finally {
    loading.value = false;
  }
}

function toggleSort(col: SortKey) {
  if (sortBy.value === col) {
    sortDir.value = sortDir.value === "asc" ? "desc" : "asc";
  } else {
    sortBy.value = col;
    sortDir.value = col === "createddate" ? "desc" : "asc";
  }
  page.value = 1;
  void loadRows();
}

function openSmartModal() {
  showSmartModal.value = true;
}

function closeSmartModal() {
  showSmartModal.value = false;
}

function resetFromModal() {
  smartFilter.value = emptySmartFilter();
  closeSmartModal();
  page.value = 1;
  void loadRows();
}

function applyFromModal() {
  closeSmartModal();
  page.value = 1;
  void loadRows();
}

/** Primary “Search” on the Classic Search Parameter card. */
function runParameterSearch() {
  page.value = 1;
  void loadRows();
}

function copyPageSummary() {
  const lines = [
    "Credit Control / Refund / Refund (Staff) / Detail Listing Of Refund Process",
    typeof window !== "undefined" ? window.location.href : "",
  ].filter(Boolean);
  void navigator.clipboard.writeText(lines.join("\n")).then(
    () => toast.success("Copied", "Breadcrumb and link copied."),
    () => toast.error("Copy failed", "Clipboard not available."),
  );
}

function toggleExtendedColumns() {
  showExtendedColumns.value = !showExtendedColumns.value;
  closeOverflow();
}

function prevPage() {
  if (page.value > 1) {
    page.value -= 1;
    void loadRows();
  }
}

function nextPage() {
  if (page.value < totalPages()) {
    page.value += 1;
    void loadRows();
  }
}

function toggleOverflow(e?: Event) {
  e?.stopPropagation();
  overflowOpen.value = !overflowOpen.value;
}

function closeOverflow() {
  overflowOpen.value = false;
}

function onDocClick() {
  closeOverflow();
}

let searchDeb: ReturnType<typeof setTimeout> | null = null;
watch(q, () => {
  if (searchDeb) clearTimeout(searchDeb);
  searchDeb = setTimeout(() => {
    searchDeb = null;
    page.value = 1;
    void loadRows();
  }, 320);
});

watch(limit, () => {
  page.value = 1;
  void loadRows();
});

onMounted(() => {
  document.addEventListener("click", onDocClick);
  (window as unknown as { SNA_JS_CC_REFUNDSTAFF_DETAILISTINGPROCESS?: { refresh: () => void } }).SNA_JS_CC_REFUNDSTAFF_DETAILISTINGPROCESS = {
    refresh: () => {
      page.value = 1;
      void loadRows();
    },
  };
  void loadRows();
});

onUnmounted(() => {
  document.removeEventListener("click", onDocClick);
  if (searchDeb) clearTimeout(searchDeb);
  delete (window as unknown as { SNA_JS_CC_REFUNDSTAFF_DETAILISTINGPROCESS?: unknown }).SNA_JS_CC_REFUNDSTAFF_DETAILISTINGPROCESS;
});
</script>

<template>
  <AdminLayout>
    <div class="space-y-4">
      <input
        ref="templateInputRef"
        type="file"
        accept=".json,application/json"
        class="hidden"
        @change="onTplChange"
      />

      <h1 class="page-title">Credit Control / Refund / Refund (Staff) / Detail Listing Of Refund Process</h1>

      <!-- Kerisi 2.0 shell: Search Parameter + listing card (violet CTAs, compact grid default). -->
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
              title="Menu 2290"
              aria-label="Menu id"
            >
              <Hash class="h-4 w-4" />
            </button>
            <button
              type="button"
              class="inline-flex rounded-lg p-1.5 hover:bg-white hover:text-violet-700"
              title="Copy page title and URL"
              aria-label="Copy"
              @click.stop="copyPageSummary()"
            >
              <Copy class="h-4 w-4" />
            </button>
            <span class="inline-flex rounded-lg p-1.5 opacity-50" title="Edit (Classic)"
              ><Pencil class="h-4 w-4"
            /></span>
          </div>
        </div>
        <div class="space-y-4 p-4">
          <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <label class="block min-w-0 flex-1">
              <span class="text-xs font-medium text-slate-700">Search By <span class="text-red-600">*</span></span>
              <select
                v-model="searchByKind"
                class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm sm:max-w-md"
                aria-required="true"
              >
                <option value="brf_detail">BRF DETAIL</option>
              </select>
            </label>
            <button
              type="button"
              class="inline-flex shrink-0 items-center gap-1.5 rounded-lg bg-violet-600 px-4 py-2 text-sm font-medium text-white shadow-sm shadow-violet-500/25 hover:bg-violet-700 sm:self-center"
              @click="runParameterSearch()"
            >
              <Search class="h-4 w-4" />Search
            </button>
          </div>
          <p class="text-xs leading-relaxed text-slate-600">
            Use
            <button
              type="button"
              class="font-semibold text-violet-700 underline decoration-violet-300 underline-offset-2 hover:text-violet-800"
              @click="openSmartModal()"
            >
              More filters
            </button>
            on the listing card below, or click
            <strong class="font-medium text-slate-800">Search</strong>
            to load/refine rows (same API as Kerisi Classic dt_list).
          </p>
        </div>
      </article>

      <article
        class="overflow-hidden rounded-xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-100"
      >
        <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
          <h2 class="text-base font-semibold text-slate-900">Detail Listing Of Refund Process</h2>
          <div class="relative shrink-0">
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
                type="button"
                class="block w-full px-3 py-2 text-left text-xs text-slate-700 hover:bg-slate-50"
                @click="toggleExtendedColumns()"
              >
                {{ showExtendedColumns ? "Compact columns (2.0 default)" : "Show all columns" }}
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
              <select v-model.number="limit" class="rounded-lg border border-slate-300 px-2 py-1.5 text-sm">
                <option v-for="n in [5, 10, 25, 50, 100]" :key="n" :value="n">{{ n }}</option>
              </select>
            </div>
            <button
              type="button"
              class="inline-flex items-center gap-0.5 pb-1 text-xs font-medium text-violet-600 hover:text-violet-800 hover:underline"
              @click="openSmartModal()"
            >
              <ChevronRight class="h-3.5 w-3.5 shrink-0" aria-hidden="true" />
              More filters (modal)
            </button>
            <div class="flex min-w-0 flex-1 flex-wrap items-end justify-end gap-2 sm:min-w-[20rem]">
              <label class="flex min-w-0 flex-col gap-1">
                <span class="text-xs font-medium text-slate-600">Search</span>
                <div class="relative w-full min-w-[10rem] sm:w-56">
                  <Search
                    class="pointer-events-none absolute left-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400"
                  />
                  <input
                    v-model="q"
                    type="search"
                    placeholder="Filter rows..."
                    class="w-full rounded-lg border border-slate-300 py-1.5 pl-8 pr-8 text-sm"
                    autocomplete="off"
                    aria-label="Search refund detail rows"
                    @keyup.enter="
                      page = 1;
                      void loadRows();
                    "
                  />
                  <button
                    v-if="q"
                    type="button"
                    class="absolute right-1 top-1/2 -translate-y-1/2 rounded p-0.5 text-slate-400 hover:bg-slate-100"
                    aria-label="Clear search"
                    @click="
                      q = '';
                      page = 1;
                      void loadRows();
                    "
                  >
                    <X class="h-3.5 w-3.5" />
                  </button>
                </div>
              </label>
              <button
                type="button"
                class="inline-flex items-center gap-1 rounded-lg border border-violet-200 bg-violet-50/80 px-3 py-1.5 text-sm font-medium text-violet-800 shadow-sm hover:bg-violet-100"
                @click="openSmartModal()"
              >
                <Filter class="h-4 w-4" />Filter
              </button>
            </div>
          </div>

          <div class="overflow-x-auto rounded-lg border border-slate-200">
            <div
              :class="[
                'min-h-[220px]',
                rows.length > 10 ? 'max-h-[min(28rem,70vh)] overflow-y-auto' : '',
              ]"
            >
              <table class="admin-table-kitchen w-full text-xs" :class="tableMinWidthClass">
                <thead class="admin-table-thead-sticky">
                  <tr class="border-b border-slate-200 text-left">
                    <th class="whitespace-nowrap px-2 py-2 text-[11px] font-semibold uppercase">No</th>
                    <th
                      v-for="c in tableColumns"
                      :key="c.key"
                      class="whitespace-nowrap px-2 py-2 text-[11px] font-semibold uppercase"
                      :class="[
                        c.num ? 'text-right' : 'text-left',
                        c.sort ? 'cursor-pointer hover:bg-slate-100' : '',
                      ]"
                      @click="c.sort ? toggleSort(c.sort) : undefined"
                    >
                      {{ c.label }}
                    </th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-if="loading">
                    <td :colspan="tableColumns.length + 1" class="px-3 py-8 text-center text-sm text-slate-500">
                      Loading...
                    </td>
                  </tr>
                  <tr v-else-if="rows.length === 0">
                    <td :colspan="tableColumns.length + 1" class="px-3 py-8 text-center text-sm text-slate-500">
                      No rows
                    </td>
                  </tr>
                  <tr
                    v-for="row in rows"
                    v-else
                    :key="`${row.index}-${row.bim_bills_id}-${row.bid_payto_id}-${row.novoucher}`"
                    class="border-b border-slate-100 hover:bg-slate-50"
                  >
                    <td class="px-2 py-2">{{ row.index }}</td>
                    <td
                      v-for="c in tableColumns"
                      :key="c.key"
                      class="max-w-[10rem] truncate px-2 py-2 xl:max-w-none"
                      :class="c.num ? 'text-right tabular-nums' : 'text-left'"
                      :title="cell(row, c)"
                    >
                      {{ cell(row, c) }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-3">
            <div class="text-xs text-slate-500">
              Showing {{ showingFrom() }}-{{ showingTo() }} of {{ total }}
            </div>
            <div class="flex items-center gap-2">
              <button
                type="button"
                :disabled="page <= 1 || loading"
                class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium disabled:opacity-50"
                @click="prevPage()"
              >
                Prev
              </button>
              <span class="text-xs text-slate-600">Page {{ page }} / {{ totalPages() }}</span>
              <button
                type="button"
                :disabled="page >= totalPages() || loading"
                class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium disabled:opacity-50"
                @click="nextPage()"
              >
                Next
              </button>
            </div>
          </div>
        </div>
      </article>

      <Teleport to="body">
        <div
          v-if="showSmartModal"
          class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
          @click.self="closeSmartModal()"
        >
          <div class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-xl bg-white shadow-xl">
            <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
              <h3 class="text-sm font-semibold text-slate-900">Smart filter</h3>
              <button type="button" class="rounded p-1 text-slate-500 hover:bg-slate-100" @click="closeSmartModal()">
                <X class="h-4 w-4" />
              </button>
            </div>
            <div class="space-y-3 px-4 py-4 text-sm">
              <label class="block">
                <span class="text-xs font-medium text-slate-600">Bill no (contains)</span>
                <input v-model="smartFilter.bim_bills_no" class="mt-1 w-full rounded-lg border border-slate-300 px-2 py-1.5" />
              </label>
              <label class="block">
                <span class="text-xs font-medium text-slate-600">Line pay to ID</span>
                <input v-model="smartFilter.bim_payto_id" class="mt-1 w-full rounded-lg border border-slate-300 px-2 py-1.5" />
              </label>
              <label class="block">
                <span class="text-xs font-medium text-slate-600">Master payee name (contains)</span>
                <input v-model="smartFilter.bim_payto_name" class="mt-1 w-full rounded-lg border border-slate-300 px-2 py-1.5" />
              </label>
              <label class="block">
                <span class="text-xs font-medium text-slate-600">Customer invoice no</span>
                <input
                  v-model="smartFilter.bim_cust_invoice_no"
                  class="mt-1 w-full rounded-lg border border-slate-300 px-2 py-1.5"
                />
              </label>
              <label class="block">
                <span class="text-xs font-medium text-slate-600">Refund date (dd/mm/yyyy)</span>
                <input v-model="smartFilter.bim_cust_invoice_date" class="mt-1 w-full rounded-lg border border-slate-300 px-2 py-1.5" />
              </label>
              <label class="block">
                <span class="text-xs font-medium text-slate-600">Status</span>
                <input v-model="smartFilter.bim_status" class="mt-1 w-full rounded-lg border border-slate-300 px-2 py-1.5" />
              </label>
              <label class="block">
                <span class="text-xs font-medium text-slate-600">Line amount (contains)</span>
                <input v-model="smartFilter.bid_amt" class="mt-1 w-full rounded-lg border border-slate-300 px-2 py-1.5" />
              </label>
              <label class="block">
                <span class="text-xs font-medium text-slate-600">Created date (dd/mm/yyyy)</span>
                <input v-model="smartFilter.createddate" placeholder="07/05/2026" class="mt-1 w-full rounded-lg border border-slate-300 px-2 py-1.5" />
              </label>
              <label class="block">
                <span class="text-xs font-medium text-slate-600">Voucher no (contains)</span>
                <input v-model="smartFilter.novoucher" class="mt-1 w-full rounded-lg border border-slate-300 px-2 py-1.5" />
              </label>
              <label class="block">
                <span class="text-xs font-medium text-slate-600">Application no (contains)</span>
                <input v-model="smartFilter.tra_application_no" class="mt-1 w-full rounded-lg border border-slate-300 px-2 py-1.5" />
              </label>
            </div>
            <div class="flex justify-end gap-2 border-t border-slate-100 px-4 py-3">
              <button
                type="button"
                class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm text-slate-700 hover:bg-slate-50"
                @click="resetFromModal()"
              >
                Reset
              </button>
              <button
                type="button"
                class="rounded-lg bg-violet-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-violet-700"
                @click="applyFromModal()"
              >
                Apply
              </button>
            </div>
          </div>
        </div>
      </Teleport>
    </div>
  </AdminLayout>
</template>
