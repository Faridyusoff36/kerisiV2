<script setup lang="ts">
/**
 * Credit Control / Refund / Refund (Staff) / Admin / Refund Application — MENUID 2286.
 *
 * Legacy onload symbol: `SNA_JS_CC_REFUNDSTAFF`. Legacy datatable/API:
 * `SNA_API_CC_REFUNDSTAFF` — TopFilter + {@code dt_listpayinadvstaff} (payment in advance listing).
 */
import { computed, onMounted, onUnmounted, ref, watch } from "vue";
import { useRoute } from "vue-router";
import {
  Building2,
  CheckSquare,
  Copy,
  CreditCard,
  Download,
  Eye,
  FileDown,
  FileSpreadsheet,
  Hash,
  MoreVertical,
  Pencil,
  RefreshCw,
  Search,
  Square,
  Trash2,
  ChevronDown,
  X,
} from "lucide-vue-next";
import AdminLayout from "@/layouts/AdminLayout.vue";
import {
  checkCreditControlRefundApplicationSubmit,
  listCreditControlRefundApplication,
  listRefundApplicationDepositAccounts,
  listRefundApplicationPayToVendors,
  submitCreditControlRefundApplicationBatch,
} from "@/api/cms";
import type { CcRefundApplicationSubmitBody, CcRefundDepositAccountOption } from "@/api/cms";
import { useDatatableFeatures } from "@/composables/useDatatableFeatures";
import type { DatatableRefApi } from "@/composables/useDatatableFeatures";
import { useToast } from "@/composables/useToast";
import type { CcRefundApplicationAdminRow } from "@/types";

const route = useRoute();
/** When `q` is set from the URL, skip debounced duplicate `loadRows` from the `watch(q)` path. */
const qEditSource = ref<"user" | "route">("user");

function routeQueryQ(): string {
  const raw = route.query.q;
  if (typeof raw === "string") return raw.trim();
  if (Array.isArray(raw) && raw[0] != null) return String(raw[0]).trim();
  return "";
}

const toast = useToast();
const datatableRef = ref<DatatableRefApi | null>(null);
const rows = ref<CcRefundApplicationAdminRow[]>([]);
const page = ref(1);
const limit = ref(5);
const q = ref("");
const total = ref(0);
/** Grand totals for all rows matching current filters (Kerisi 1.0 purple footer). */
const footerPaymentAdvance = ref(0);
const footerRequestRefund = ref(0);
const loading = ref(false);
const submitting = ref(false);

const typeOfRefund = ref<"STAFF">("STAFF");
const billRegIntegration = ref<"" | "I" | "G">("");

/** BERKELOMPOK (G) deposit lines use payto B; INDIVIDU (I) uses I — matches legacy SQL. */
const depositPaytoTypeForApi = computed(() => (billRegIntegration.value === "I" ? "I" : "B"));

/** Account combobox — display text shown in the yellow-focus field */
const accountComboText = ref("");
const accountPickId = ref("");
const accountFromPicker = ref(false);
const accountOptions = ref<CcRefundDepositAccountOption[]>([]);
const accountMenuOpen = ref(false);

/** Staff ID — {@code tra.vcs_vendor_code} (+ staff name) per Classic TopFilter. */
const payToComboText = ref("");
const payToPickId = ref("");
const payToFromPicker = ref(false);
const payToOptions = ref<CcRefundDepositAccountOption[]>([]);
const payToMenuOpen = ref(false);
/** After “Search Refund”, show inline compulsory styling on empty mandatory fields. */
const filterSubmitAttempted = ref(false);

const kerisiFilterInputClass =
  "w-full rounded-lg border px-3 py-2 text-sm placeholder:text-slate-400 focus:border-violet-500 focus:bg-amber-50 focus:outline-none focus:ring-1 focus:ring-violet-400";

const accountTopInvalid = computed(
  () => filterSubmitAttempted.value && !(accountFromPicker.value && accountPickId.value.trim()),
);
const briTopInvalid = computed(() => filterSubmitAttempted.value && !billRegIntegration.value);
const staffIdTopInvalid = computed(
  () => filterSubmitAttempted.value && !(payToFromPicker.value && payToPickId.value.trim()),
);

type SortKey =
  | "application_no"
  | "id"
  | "name"
  | "deposit_no"
  | "account_code"
  | "reference_no"
  | "application_date"
  | "amount_eligible_refund"
  | "amount"
  | "status";
const sortBy = ref<SortKey>("id");
const sortDir = ref<"asc" | "desc">("asc");

const selectedIds = ref<Set<number>>(new Set());

const totalPages = computed(() =>
  total.value ? Math.max(1, Math.ceil(total.value / limit.value)) : 1,
);

const showingFrom = computed(() =>
  total.value === 0 ? 0 : (page.value - 1) * limit.value + 1,
);
const showingTo = computed(() => Math.min(page.value * limit.value, total.value));

const pageIds = computed(() => rows.value.map((r) => r.traId));
const allOnPageSelected = computed(
  () =>
    pageIds.value.length > 0 && pageIds.value.every((id) => selectedIds.value.has(id)),
);

function resolvedAccountCode(): string {
  const id = accountPickId.value.trim();
  if (accountFromPicker.value && id) return id;
  const t = accountComboText.value.trim();
  if (!t) return "";
  return t.split(/\s*-\s*/)[0]?.trim() ?? t;
}

function filterPayload(): Omit<CcRefundApplicationSubmitBody, "tra_ids"> {
  const body: Omit<CcRefundApplicationSubmitBody, "tra_ids"> = {};
  const code = resolvedAccountCode();
  if (code) {
    body.acm_acct_code = code;
    if (accountFromPicker.value) body.acm_acct_exact = true;
  }
  if (billRegIntegration.value) body.bill_reg_integration_type = billRegIntegration.value;
  if (payToFromPicker.value && payToPickId.value.trim()) {
    body.vcs_vendor_code = payToPickId.value.trim();
    body.vcs_vendor_exact = true;
  }
  return body;
}

function buildListQuery(): string {
  const params = new URLSearchParams({
    page: String(page.value),
    limit: String(limit.value),
    sort_by: sortBy.value,
    sort_dir: sortDir.value,
    ...(q.value.trim() ? { q: q.value.trim() } : {}),
  });
  const code = resolvedAccountCode();
  if (code) {
    params.set("acm_acct_code", code);
    if (accountFromPicker.value) params.set("acm_acct_exact", "1");
  }
  if (billRegIntegration.value) params.set("bill_reg_integration_type", billRegIntegration.value);
  if (payToFromPicker.value && payToPickId.value.trim()) {
    params.set("vcs_vendor_code", payToPickId.value.trim());
    params.set("vcs_vendor_exact", "1");
  }
  return `?${params.toString()}`;
}

function fmtMoney(n: number | null | undefined): string {
  if (n == null || Number.isNaN(n)) return "";
  return new Intl.NumberFormat("en-MY", { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(n);
}

function toggleSort(col: SortKey) {
  if (sortBy.value === col) sortDir.value = sortDir.value === "asc" ? "desc" : "asc";
  else {
    sortBy.value = col;
    sortDir.value = "asc";
  }
  page.value = 1;
  void loadRows();
}

function toggleRow(id: number) {
  const next = new Set(selectedIds.value);
  if (next.has(id)) next.delete(id);
  else next.add(id);
  selectedIds.value = next;
}

function toggleSelectAllOnPage() {
  if (allOnPageSelected.value) {
    const next = new Set(selectedIds.value);
    for (const id of pageIds.value) next.delete(id);
    selectedIds.value = next;
    return;
  }
  const next = new Set(selectedIds.value);
  for (const id of pageIds.value) next.add(id);
  selectedIds.value = next;
}

async function loadRows() {
  loading.value = true;
  try {
    const res = await listCreditControlRefundApplication(buildListQuery());
    rows.value = res.data;
    total.value = Number(res.meta?.total ?? 0);
    const foot = res.meta?.footer as {
      paymentInAdvanceTotal?: number;
      requestRefundTotal?: number;
    } | undefined;
    footerPaymentAdvance.value = Number(foot?.paymentInAdvanceTotal ?? 0);
    footerRequestRefund.value = Number(foot?.requestRefundTotal ?? 0);
  } catch (e) {
    toast.error("Load failed", e instanceof Error ? e.message : "Unable to load list.");
  } finally {
    loading.value = false;
  }
}

function refreshList() {
  void loadRows();
}

let accountLoadDeb: ReturnType<typeof setTimeout> | null = null;

async function loadAccountOptions() {
  try {
    const p = new URLSearchParams({ payto_type: depositPaytoTypeForApi.value, limit: "15" });
    const t = accountComboText.value.trim();
    if (t) p.set("q", t);
    const res = await listRefundApplicationDepositAccounts(`?${p.toString()}`);
    accountOptions.value = res.data ?? [];
  } catch {
    accountOptions.value = [];
  }
}

function scheduleAccountLoad() {
  if (accountLoadDeb) clearTimeout(accountLoadDeb);
  accountLoadDeb = setTimeout(() => {
    accountLoadDeb = null;
    void loadAccountOptions();
  }, 280);
}

function onAccountFocus() {
  accountMenuOpen.value = true;
  void loadAccountOptions();
}

function onAccountInput() {
  resetPayToPicker();
  accountFromPicker.value = false;
  accountPickId.value = "";
  accountMenuOpen.value = true;
  scheduleAccountLoad();
}

function pickAccountOption(o: CcRefundDepositAccountOption) {
  if (accountPickId.value !== o.id || !accountFromPicker.value) {
    resetPayToPicker();
  }
  accountFromPicker.value = true;
  accountPickId.value = o.id;
  accountComboText.value = o.text;
  accountMenuOpen.value = false;
}

function clearAccountPicker() {
  accountFromPicker.value = false;
  accountPickId.value = "";
  accountComboText.value = "";
  accountOptions.value = [];
  accountMenuOpen.value = false;
  resetPayToPicker();
}

function resetPayToPicker() {
  payToFromPicker.value = false;
  payToPickId.value = "";
  payToComboText.value = "";
  payToOptions.value = [];
  payToMenuOpen.value = false;
}

let payToLoadDeb: ReturnType<typeof setTimeout> | null = null;

async function loadPayToOptions() {
  const code = resolvedAccountCode();
  if (!code || !billRegIntegration.value) {
    payToOptions.value = [];
    return;
  }
  try {
    const p = new URLSearchParams({ acm_acct_code: code, limit: "15" });
    if (accountFromPicker.value) p.set("acm_acct_exact", "1");
    p.set("bill_reg_integration_type", billRegIntegration.value);
    const t = payToComboText.value.trim();
    if (t) p.set("q", t);
    const res = await listRefundApplicationPayToVendors(`?${p.toString()}`);
    payToOptions.value = res.data ?? [];
  } catch {
    payToOptions.value = [];
  }
}

function schedulePayToLoad() {
  if (payToLoadDeb) clearTimeout(payToLoadDeb);
  payToLoadDeb = setTimeout(() => {
    payToLoadDeb = null;
    void loadPayToOptions();
  }, 280);
}

function onPayToFocus() {
  payToMenuOpen.value = true;
  void loadPayToOptions();
}

function onPayToInput() {
  payToFromPicker.value = false;
  payToPickId.value = "";
  payToMenuOpen.value = true;
  schedulePayToLoad();
}

function pickPayToOption(o: CcRefundDepositAccountOption) {
  payToFromPicker.value = true;
  payToPickId.value = o.id;
  payToComboText.value = o.text;
  payToMenuOpen.value = false;
}

function searchRefund() {
  filterSubmitAttempted.value = true;
  if (!billRegIntegration.value) {
    toast.error(
      "Top filter",
      "Pilih jenis Bill Registration Integration Refund (INDIVIDU atau BERKELOMPOK).",
    );
    return;
  }
  if (!accountFromPicker.value || !accountPickId.value.trim()) {
    toast.error("Top filter", "Pilih kod akaun daripada senarai deposit.");
    return;
  }
  if (!payToFromPicker.value || !payToPickId.value.trim()) {
    toast.error("Top filter", "Pilih Staff ID daripada senarai.");
    return;
  }
  page.value = 1;
  selectedIds.value = new Set();
  void loadRows();
}

async function submitSelected() {
  const ids = [...selectedIds.value];
  if (ids.length === 0) {
    toast.info("Nothing selected", "Choose at least one row.");
    return;
  }
  const base = filterPayload();
  try {
    const pre = await checkCreditControlRefundApplicationSubmit({ ...base, tra_ids: ids });
    if (!pre.data.ok) {
      toast.error(
        "Cannot submit",
        `Selections must belong to one application only (found ${pre.data.distinct_application_count} distinct application numbers).`,
      );
      return;
    }
  } catch (e) {
    toast.error("Check failed", e instanceof Error ? e.message : String(e));
    return;
  }
  if (
    !confirm(
      `Submit ${ids.length} line(s) for application ${rows.value.find((r) => ids.includes(r.traId))?.applicationNo ?? ""}? This moves them to staff draft processing.`,
    )
  ) {
    return;
  }
  submitting.value = true;
  try {
    const res = await submitCreditControlRefundApplicationBatch({ ...base, tra_ids: ids });
    toast.success("Submitted", `${res.data.updated} row(s) updated.`);
    selectedIds.value = new Set();
    await loadRows();
  } catch (e) {
    toast.error("Submit failed", e instanceof Error ? e.message : String(e));
  } finally {
    submitting.value = false;
  }
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
  "No",
  "Refunder ID",
  "Refunder Name",
  "Deposit No",
  "Payment In Advance (RM)",
  "Request Refund (RM)",
  "Staff Bank",
  "Staff Account No",
];

function rowExport(r: CcRefundApplicationAdminRow) {
  return {
    No: r.index,
    "Refunder ID": r.id ?? "",
    "Refunder Name": r.name ?? "",
    "Deposit No": r.dpmDepositNo ?? "",
    "Payment In Advance (RM)": r.traAmt != null ? fmtMoney(r.traAmt) : "",
    "Request Refund (RM)": r.amountEligibleRefund != null ? fmtMoney(r.amountEligibleRefund) : "",
    "Staff Bank": r.staffBankName ?? "",
    "Staff Account No": r.staffAccountNo ?? "",
  };
}

function kerisiClassicOnly(action: string) {
  toast.info(action, "This action existed in Kerisi 1.0; it is not wired in Kerisi 2.0 yet.");
}

const overflowOpen = ref(false);
const overflowRoot = ref<HTMLElement | null>(null);
const accountComboRoot = ref<HTMLElement | null>(null);
const payToComboRoot = ref<HTMLElement | null>(null);

function onClickOutside(event: MouseEvent) {
  if (!accountComboRoot.value?.contains(event.target as Node)) {
    accountMenuOpen.value = false;
  }
  if (!payToComboRoot.value?.contains(event.target as Node)) {
    payToMenuOpen.value = false;
  }
  if (!overflowOpen.value) return;
  if (overflowRoot.value?.contains(event.target as Node)) return;
  overflowOpen.value = false;
}

const {
  isGrouped,
  handleSaveTemplate,
  handleLoadTemplate,
  handleUngroupList,
  handleGroupList,
  templateFileInputRef,
  onTemplateFileChange,
  handleDownloadPDF,
  handleDownloadCSV,
} = useDatatableFeatures({
  pageName: "List Of Payment In Advance (Refund Staff)",
  apiDataPath: "/credit-control/refund-application",
  defaultExportColumns: exportColumns,
  getFilteredList: () => rows.value.map((r) => rowExport(r) as Record<string, unknown>),
  datatableRef,
  searchKeyword: q,
  smartFilter: ref({}),
  applyFilters: () => void loadRows(),
});

let searchDeb: ReturnType<typeof setTimeout> | null = null;
watch(q, () => {
  if (qEditSource.value === "route") {
    qEditSource.value = "user";
    return;
  }
  if (searchDeb) clearTimeout(searchDeb);
  searchDeb = setTimeout(() => {
    searchDeb = null;
    page.value = 1;
    void loadRows();
  }, 320);
});

watch(
  () => [route.name, route.query.q] as const,
  () => {
    if (route.name !== "kerisi-cc-refund-application-admin") return;
    const fromRoute = routeQueryQ();
    if (fromRoute !== q.value) {
      qEditSource.value = "route";
      q.value = fromRoute;
    }
    page.value = 1;
    void loadRows();
  },
  { immediate: true },
);

watch(limit, () => {
  page.value = 1;
  void loadRows();
});

watch(billRegIntegration, (v, prev) => {
  resetPayToPicker();
  selectedIds.value = new Set();
  if (prev && v && prev !== v) {
    accountFromPicker.value = false;
    accountPickId.value = "";
    accountComboText.value = "";
    accountOptions.value = [];
    accountMenuOpen.value = false;
  }
});

async function exportExcel() {
  try {
    if (rows.value.length === 0) {
      toast.info("No data", "There is nothing to export.");
      return;
    }
    const ExcelJS = await import("exceljs");
    const wb = new ExcelJS.Workbook();
    const ws = wb.addWorksheet("Refund application");
    ws.addRow(exportColumns);
    rows.value.forEach((r) => {
      const e = rowExport(r);
      ws.addRow(exportColumns.map((h) => e[h as keyof typeof e]));
    });
    const buf = await wb.xlsx.writeBuffer();
    const blob = new Blob([buf], { type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" });
    const url = URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.href = url;
    a.download = `CC_Payment_In_Advance_Staff_${new Date().toISOString().slice(0, 10)}.xlsx`;
    a.click();
    URL.revokeObjectURL(url);
    toast.success("Excel downloaded");
  } catch (e) {
    toast.error("Export failed", e instanceof Error ? e.message : "Excel export failed.");
  }
}

onMounted(() => {
  document.addEventListener("click", onClickOutside);
  datatableRef.value = {
    getExportConfig: () => ({
      columns: exportColumns,
      data: rows.value.map((r) => rowExport(r) as Record<string, unknown>),
    }),
  };
  (window as unknown as { SNA_JS_CC_REFUNDSTAFF?: { refresh: () => void; search: () => void } }).SNA_JS_CC_REFUNDSTAFF = {
    refresh: () => void loadRows(),
    search: () => {
      page.value = 1;
      selectedIds.value = new Set();
      void loadRows();
    },
  };
});

onUnmounted(() => {
  document.removeEventListener("click", onClickOutside);
  if (searchDeb) clearTimeout(searchDeb);
  if (accountLoadDeb) clearTimeout(accountLoadDeb);
  if (payToLoadDeb) clearTimeout(payToLoadDeb);
  delete (window as unknown as { SNA_JS_CC_REFUNDSTAFF?: unknown }).SNA_JS_CC_REFUNDSTAFF;
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

      <h1 class="page-title">Credit Control / Refund (Staff) / Refund Type</h1>

      <article class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between gap-2 border-b border-slate-100 px-4 py-3">
          <h2 class="text-base font-semibold text-slate-900">Refund Application</h2>
          <div ref="overflowRoot" class="relative">
            <button
              type="button"
              class="rounded-lg p-2 text-slate-500 hover:bg-slate-100"
              @click.stop="overflowOpen = !overflowOpen"
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
                @click="overflowOpen = false; handleSaveTemplate()"
              >
                Save template
              </button>
              <button
                type="button"
                class="block w-full px-3 py-2 text-left text-xs text-slate-700 hover:bg-slate-50"
                @click="overflowOpen = false; handleLoadTemplate()"
              >
                Load template
              </button>
              <button
                v-if="isGrouped"
                type="button"
                class="block w-full px-3 py-2 text-left text-xs text-slate-700 hover:bg-slate-50"
                @click="overflowOpen = false; handleUngroupList()"
              >
                Ungroup list
              </button>
              <button
                v-else
                type="button"
                class="block w-full px-3 py-2 text-left text-xs text-slate-700 hover:bg-slate-50"
                @click="overflowOpen = false; handleGroupList()"
              >
                Group list
              </button>
            </div>
          </div>
        </div>

        <div class="border-b border-slate-100">
          <section class="rounded-t-lg border-x border-t border-slate-200 bg-gradient-to-b from-slate-50/90 to-white">
            <div
              class="flex items-center justify-between border-b border-slate-200/80 bg-slate-100/60 px-4 py-2.5"
            >
              <h3 class="text-sm font-semibold tracking-tight text-slate-900">Top Filter</h3>
              <div class="flex items-center gap-0.5 text-slate-500">
                <span class="inline-flex rounded p-1.5 hover:bg-white" title="Menu 2286"><Hash class="h-4 w-4" /></span>
                <span class="inline-flex rounded p-1.5 hover:bg-white"><Copy class="h-4 w-4" /></span>
                <span class="inline-flex rounded p-1.5 hover:bg-white"><Pencil class="h-4 w-4" /></span>
              </div>
            </div>
            <div class="space-y-3 p-4">
              <div class="grid gap-3 sm:[grid-template-columns:minmax(11rem,auto)_1fr] sm:gap-x-4 sm:gap-y-3">
                <label class="pt-2 text-xs font-medium text-slate-600 sm:pt-2.5">Type of Refund:</label>
                <div>
                  <select
                    :value="typeOfRefund"
                    disabled
                    class="w-full cursor-not-allowed rounded-lg border border-slate-200 bg-slate-100/80 px-3 py-2 text-sm font-medium text-slate-700"
                  >
                    <option value="STAFF">STAFF</option>
                  </select>
                </div>

                <label class="pt-2 text-xs font-medium text-slate-700 sm:pt-2.5">
                  Account Code <span class="text-red-600">*</span> :
                </label>
                <div ref="accountComboRoot" class="relative min-w-0">
                  <input
                    v-model="accountComboText"
                    type="text"
                    autocomplete="off"
                    :class="[
                      kerisiFilterInputClass,
                      'pr-9',
                      accountTopInvalid ? 'border-red-500 ring-1 ring-red-400/50' : 'border-slate-300',
                    ]"
                    placeholder="Taip untuk cari — pilih kod akaun deposit"
                    @focus="onAccountFocus"
                    @input="onAccountInput"
                  />
                  <button
                    v-if="accountComboText"
                    type="button"
                    class="absolute right-2 top-1/2 -translate-y-1/2 rounded p-0.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600"
                    aria-label="Clear account"
                    @mousedown.prevent="clearAccountPicker()"
                  >
                    <X class="h-4 w-4" />
                  </button>
                  <div
                    v-if="accountMenuOpen && accountOptions.length > 0"
                    class="absolute left-0 right-0 z-40 mt-1 max-h-52 overflow-auto rounded-lg border border-slate-200 bg-white py-1 shadow-lg"
                  >
                    <button
                      v-for="opt in accountOptions"
                      :key="opt.id"
                      type="button"
                      class="block w-full px-3 py-2 text-left text-sm hover:bg-violet-50"
                      @mousedown.prevent="pickAccountOption(opt)"
                    >
                      {{ opt.text }}
                    </button>
                  </div>
                  <p v-if="accountTopInvalid" class="mt-1 text-xs text-red-600">Compulsory</p>
                </div>

                <label class="pt-2 text-xs font-medium text-slate-700 sm:pt-2.5">
                  Type of Bill Registration Integration Refund <span class="text-red-600">*</span> :
                </label>
                <div class="relative min-w-0">
                  <select
                    v-model="billRegIntegration"
                    required
                    :class="[
                      kerisiFilterInputClass,
                      'appearance-none pr-9',
                      briTopInvalid ? 'border-red-500 ring-1 ring-red-400/50' : 'border-slate-300 bg-white',
                    ]"
                  >
                    <option value="" disabled>Pilih...</option>
                    <option value="I">INDIVIDU</option>
                    <option value="G">BERKELOMPOK</option>
                  </select>
                  <button
                    v-if="billRegIntegration"
                    type="button"
                    class="absolute right-7 top-1/2 -translate-y-1/2 rounded p-0.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600"
                    aria-label="Clear bill registration type"
                    @click="billRegIntegration = ''"
                  >
                    <X class="h-4 w-4" />
                  </button>
                  <span
                    class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400"
                    aria-hidden="true"
                  >
                    <ChevronDown class="h-4 w-4" />
                  </span>
                  <p v-if="briTopInvalid" class="mt-1 text-xs text-red-600">Compulsory</p>
                </div>

                <label class="pt-2 text-xs font-medium text-slate-700 sm:pt-2.5">
                  Staff ID <span class="text-red-600">*</span> :
                </label>
                <div ref="payToComboRoot" class="relative min-w-0">
                  <input
                    v-model="payToComboText"
                    type="text"
                    autocomplete="off"
                    :disabled="!billRegIntegration || !resolvedAccountCode()"
                    :class="[
                      kerisiFilterInputClass,
                      'pr-9',
                      staffIdTopInvalid ? 'border-red-500 ring-1 ring-red-400/50' : 'border-slate-300',
                      !billRegIntegration || !resolvedAccountCode()
                        ? 'cursor-not-allowed bg-slate-50 text-slate-500'
                        : 'bg-white',
                    ]"
                    placeholder="Taip untuk cari Staff ID (kod — nama)"
                    @focus="onPayToFocus"
                    @input="onPayToInput"
                  />
                  <button
                    v-if="payToComboText"
                    type="button"
                    class="absolute right-2 top-1/2 -translate-y-1/2 rounded p-0.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600"
                    aria-label="Clear staff ID"
                    @mousedown.prevent="resetPayToPicker()"
                  >
                    <X class="h-4 w-4" />
                  </button>
                  <div
                    v-if="payToMenuOpen && payToOptions.length > 0"
                    class="absolute left-0 right-0 z-40 mt-1 max-h-52 overflow-auto rounded-lg border border-slate-200 bg-white py-1 shadow-lg"
                  >
                    <button
                      v-for="opt in payToOptions"
                      :key="opt.id"
                      type="button"
                      class="block w-full px-3 py-2 text-left text-sm hover:bg-violet-50"
                      @mousedown.prevent="pickPayToOption(opt)"
                    >
                      {{ opt.text }}
                    </button>
                  </div>
                  <p v-if="staffIdTopInvalid" class="mt-1 text-xs text-red-600">Compulsory</p>
                </div>
              </div>
              <div class="flex flex-wrap items-center justify-end gap-2 border-t border-slate-100 pt-3">
                <button
                  type="button"
                  class="inline-flex items-center gap-1.5 rounded-lg border border-violet-600 bg-white px-3 py-2 text-sm font-medium text-violet-700 shadow-sm hover:bg-violet-50"
                  @click="refreshList"
                >
                  <RefreshCw class="h-4 w-4 shrink-0" />
                  Refresh
                </button>
                <button
                  type="button"
                  class="inline-flex items-center gap-1.5 rounded-lg bg-violet-600 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-violet-700"
                  @click="searchRefund"
                >
                  <Search class="h-4 w-4 shrink-0" />
                  Search Refund
                </button>
              </div>
            </div>
          </section>
        </div>

        <div
          class="flex flex-col gap-3 border-b border-slate-100 px-4 py-2.5 sm:flex-row sm:items-center sm:justify-between"
        >
          <div class="flex flex-wrap items-center gap-2">
            <label class="text-xs font-medium text-slate-600">Display</label>
            <select v-model.number="limit" class="rounded-lg border border-slate-300 px-2 py-1.5 text-sm">
              <option v-for="n in [5, 10, 25, 50, 100]" :key="n" :value="n">{{ n }}</option>
            </select>
            <button
              type="button"
              class="rounded-lg bg-violet-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-violet-700 disabled:opacity-50"
              :disabled="selectedIds.size === 0 || submitting"
              @click="submitSelected"
            >
              {{ submitting ? "Submitting…" : "Submit selected" }}
            </button>
            <span v-if="selectedIds.size > 0" class="text-xs text-slate-600">{{ selectedIds.size }} selected</span>
          </div>
          <div class="flex min-w-0 flex-1 flex-wrap items-center justify-end gap-2 sm:max-w-xl lg:max-w-2xl">
            <label class="shrink-0 text-xs font-medium text-slate-600">Search</label>
            <div class="relative min-w-0 flex-1 sm:min-w-[12rem]">
              <Search class="pointer-events-none absolute left-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400" />
              <input
                v-model="q"
                type="search"
                placeholder="Deposit no., staff ID / name, bank, account…"
                class="w-full rounded-lg border border-slate-300 py-1.5 pl-8 pr-8 text-sm"
                autocomplete="off"
                aria-label="Search refund applications"
                @keyup.enter="page = 1; void loadRows()"
              />
              <button
                v-if="q"
                type="button"
                class="absolute right-1 top-1/2 -translate-y-1/2 rounded p-0.5 text-slate-400 hover:bg-slate-100"
                @click="q = ''; page = 1; void loadRows()"
              >
                <X class="h-3.5 w-3.5" />
              </button>
            </div>
          </div>
        </div>

        <div class="space-y-3 px-4 pb-4 pt-3">
          <div class="border-b border-slate-100 pb-3">
            <h3 class="text-sm font-semibold tracking-tight text-slate-900">List Of Payment In Advance</h3>
            <p class="mt-0.5 text-xs text-slate-500">
              {{
                loading
                  ? "Loading…"
                  : total > 0
                    ? `${total} record${total === 1 ? "" : "s"} (Classic dt_listpayinadvstaff)`
                    : "Adjust Top Filter and search to load payment-in-advance lines."
              }}
            </p>
          </div>
          <div class="flex flex-wrap items-center justify-end gap-2">
            <button
              type="button"
              class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm text-slate-700 hover:bg-slate-50"
              @click="handleDownloadPDF"
            >
              <FileDown class="h-3.5 w-3.5" />
              PDF
            </button>
            <button
              type="button"
              class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm text-slate-700 hover:bg-slate-50"
              @click="handleDownloadCSV"
            >
              <Download class="h-3.5 w-3.5" />
              CSV
            </button>
            <button
              type="button"
              class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm text-slate-700 hover:bg-slate-50"
              @click="exportExcel"
            >
              <FileSpreadsheet class="h-3.5 w-3.5" />
              Excel
            </button>
          </div>

          <details class="text-xs text-slate-500">
            <summary class="cursor-pointer select-none font-medium text-slate-600">Submission rules</summary>
            <p class="mt-2 leading-relaxed">
              Submissions must include rows from
              <strong>one</strong>
              application number only (legacy rule). Bill registration integration filter applies when
              <code class="rounded bg-slate-100 px-1">tra_extended_field</code>
              is present in the database. Requires signed-in Credit Control staff.
            </p>
          </details>

          <div class="max-h-[min(28rem,70vh)] overflow-y-auto overflow-x-auto rounded-lg border border-slate-200">
            <table class="min-w-[56rem] divide-y divide-slate-200 text-left text-xs sm:min-w-full">
              <thead class="sticky top-0 z-10 bg-slate-50 text-[11px] font-medium uppercase tracking-wide text-slate-600">
                <tr>
                  <th class="whitespace-nowrap px-2 py-2.5 font-medium normal-case tracking-normal">No</th>
                  <th class="whitespace-nowrap px-2 py-2 font-medium normal-case tracking-normal">
                    <button type="button" class="hover:text-slate-900" @click="toggleSort('id')">Refunder ID</button>
                  </th>
                  <th class="min-w-[7rem] whitespace-nowrap px-2 py-2 font-medium normal-case tracking-normal">
                    <button type="button" class="hover:text-slate-900" @click="toggleSort('name')">
                      Refunder Name
                    </button>
                  </th>
                  <th class="whitespace-nowrap px-2 py-2 font-medium normal-case tracking-normal">
                    <button type="button" class="hover:text-slate-900" @click="toggleSort('deposit_no')">
                      Deposit No
                    </button>
                  </th>
                  <th class="whitespace-nowrap px-2 py-2 text-right font-medium normal-case tracking-normal">
                    <button type="button" class="hover:text-slate-900" @click="toggleSort('amount')">
                      Payment In Advance (RM)
                    </button>
                  </th>
                  <th class="whitespace-nowrap px-2 py-2 text-right font-medium normal-case tracking-normal">
                    <button type="button" class="hover:text-slate-900" @click="toggleSort('amount_eligible_refund')">
                      Request Refund (RM)
                    </button>
                  </th>
                  <th class="min-w-[9rem] px-2 py-2 font-medium normal-case tracking-normal">Staff Account Info</th>
                  <th class="w-14 whitespace-nowrap px-2 py-2 text-center font-medium normal-case tracking-normal">
                    <button
                      type="button"
                      class="mx-auto rounded p-0.5 text-slate-600 hover:bg-slate-200"
                      title="Select all on page"
                      @click="toggleSelectAllOnPage"
                    >
                      <CheckSquare v-if="allOnPageSelected" class="h-4 w-4" />
                      <Square v-else class="h-4 w-4" />
                    </button>
                    <span class="sr-only">Select</span>
                  </th>
                  <th class="whitespace-nowrap px-2 py-2 font-medium normal-case tracking-normal">Action</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 bg-white text-slate-800">
                <tr v-if="loading">
                  <td colspan="9" class="px-3 py-10 text-center text-slate-500">
                    <span class="inline-block animate-pulse">Loading payment-in-advance rows…</span>
                  </td>
                </tr>
                <tr v-else-if="rows.length === 0">
                  <td colspan="9" class="px-3 py-10 text-center text-slate-500">
                    <p class="font-medium text-slate-700">No records in this scope</p>
                    <p class="mt-1 max-w-lg text-xs mx-auto leading-relaxed">
                      Use Top Filter (Account Code, Bill registration type, Staff ID), then Search Refund. Lines are APPLY
                      staff refunds (tra_payto_type B), matching Kerisi Classic dt_listpayinadvstaff.
                    </p>
                  </td>
                </tr>
                <tr v-for="row in rows" v-else :key="`${row.traId}-${row.index}`" class="hover:bg-slate-50/80">
                  <td class="px-2 py-1.5 tabular-nums text-slate-600">{{ row.index }}</td>
                  <td class="whitespace-nowrap px-2 py-1.5 font-medium">{{ row.id }}</td>
                  <td class="max-w-[12rem] truncate px-2 py-1.5 sm:max-w-[16rem]" :title="row.name ?? ''">{{ row.name }}</td>
                  <td class="whitespace-nowrap px-2 py-1.5 font-mono text-[11px]">{{ row.dpmDepositNo }}</td>
                  <td class="px-2 py-1.5 text-right tabular-nums">{{ fmtMoney(row.traAmt) }}</td>
                  <td class="px-2 py-1.5 text-right tabular-nums">{{ fmtMoney(row.amountEligibleRefund) }}</td>
                  <td class="px-2 py-1.5">
                    <div class="flex flex-col gap-1 text-[11px] leading-snug">
                      <div class="flex items-start gap-1 text-slate-600">
                        <Building2 class="mt-0.5 h-3.5 w-3.5 shrink-0 text-slate-400" aria-hidden="true" />
                        <span class="break-words">{{ row.staffBankName || "—" }}</span>
                      </div>
                      <div class="flex items-start gap-1 tabular-nums text-slate-700">
                        <CreditCard class="mt-0.5 h-3.5 w-3.5 shrink-0 text-slate-400" aria-hidden="true" />
                        <span>{{ row.staffAccountNo || "—" }}</span>
                      </div>
                    </div>
                  </td>
                  <td class="px-2 py-1.5 text-center">
                    <button
                      type="button"
                      class="rounded p-1 text-slate-600 hover:bg-slate-200"
                      title="Toggle select"
                      @click="toggleRow(row.traId)"
                    >
                      <CheckSquare v-if="selectedIds.has(row.traId)" class="h-4 w-4 text-violet-600" />
                      <Square v-else class="h-4 w-4" />
                    </button>
                  </td>
                  <td class="px-2 py-1.5">
                    <div class="flex flex-nowrap items-center gap-1.5 text-violet-600">
                      <a
                        :href="row.reportUrl"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="rounded p-1 hover:bg-violet-50"
                        title="View"
                        @click.stop
                      >
                        <Eye class="h-4 w-4" />
                      </a>
                      <a
                        :href="row.reportUrl"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="rounded p-1 hover:bg-violet-50"
                        title="Edit (portal stub)"
                        @click.stop
                      >
                        <Pencil class="h-4 w-4" />
                      </a>
                      <button
                        type="button"
                        class="rounded p-1 text-slate-500 hover:bg-slate-100 hover:text-slate-700"
                        title="Delete"
                        @click="kerisiClassicOnly('Delete line')"
                      >
                        <Trash2 class="h-4 w-4" />
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
              <tfoot v-if="!loading && total > 0">
                <tr class="border-t-2 border-slate-300 bg-violet-50 font-semibold text-slate-900">
                  <td colspan="4" class="px-2 py-2.5 text-right">Grand Total</td>
                  <td class="px-2 py-2.5 text-right tabular-nums">{{ fmtMoney(footerPaymentAdvance) }}</td>
                  <td class="px-2 py-2.5 text-right tabular-nums">{{ fmtMoney(footerRequestRefund) }}</td>
                  <td colspan="3" class="px-2 py-2.5"></td>
                </tr>
              </tfoot>
            </table>
          </div>

          <div v-if="total > 0" class="flex flex-wrap items-center justify-between gap-3 text-xs text-slate-600">
            <span>Showing {{ showingFrom }}–{{ showingTo }} of {{ total }}</span>
            <div class="flex items-center gap-2">
              <button
                type="button"
                class="rounded border border-slate-300 px-2 py-1 hover:bg-slate-50 disabled:opacity-40"
                :disabled="page <= 1 || loading"
                @click="prevPage"
              >
                Prev
              </button>
              <span>Page {{ page }} / {{ totalPages }}</span>
              <button
                type="button"
                class="rounded border border-slate-300 px-2 py-1 hover:bg-slate-50 disabled:opacity-40"
                :disabled="page >= totalPages || loading"
                @click="nextPage"
              >
                Next
              </button>
            </div>
          </div>
        </div>
      </article>
    </div>
  </AdminLayout>
</template>
