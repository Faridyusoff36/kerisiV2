<script setup lang="ts">
/**
 * Credit Control / Refund / Refund (Staff) / Admin / Refund Application — MENUID 2286.
 *
 * Legacy onload symbol: `SNA_JS_CC_REFUNDSTAFF`. Legacy datatable/API:
 * `SNA_API_CC_REFUNDSTAFF` (implementation aligns with
 * `SNA_API_CREDITCONTROL_REQUESTREFUNDSTAFF` / `dt_listapply`).
 */
import { computed, onMounted, onUnmounted, ref, watch } from "vue";
import { useRoute } from "vue-router";
import {
  CheckSquare,
  Download,
  ExternalLink,
  FileDown,
  FileSpreadsheet,
  MoreVertical,
  Search,
  Square,
  X,
} from "lucide-vue-next";
import AdminLayout from "@/layouts/AdminLayout.vue";
import {
  checkCreditControlRefundApplicationSubmit,
  listCreditControlRefundApplication,
  submitCreditControlRefundApplicationBatch,
} from "@/api/cms";
import type { CcRefundApplicationSubmitBody } from "@/api/cms";
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
const limit = ref(10);
const q = ref("");
const total = ref(0);
const loading = ref(false);
const submitting = ref(false);

/** Top filters — applied on Search Refund / Refresh / list load (not auto on every keystroke). */
const ftyFundType = ref("");
const acmAcctCode = ref("");
const billRegIntegration = ref<"" | "I" | "G">("");

type SortKey =
  | "application_no"
  | "id"
  | "name"
  | "account_code"
  | "reference_no"
  | "application_date"
  | "amount_eligible_refund"
  | "amount"
  | "status";
const sortBy = ref<SortKey>("application_no");
const sortDir = ref<"asc" | "desc">("asc");

const selectedIds = ref<Set<number>>(new Set());

const totalPages = computed(() =>
  total.value ? Math.max(1, Math.ceil(total.value / limit.value)) : 1,
);

const pageIds = computed(() => rows.value.map((r) => r.traId));
const allOnPageSelected = computed(
  () =>
    pageIds.value.length > 0 && pageIds.value.every((id) => selectedIds.value.has(id)),
);

function filterPayload(): Omit<CcRefundApplicationSubmitBody, "tra_ids"> {
  const body: Omit<CcRefundApplicationSubmitBody, "tra_ids"> = {};
  const f = ftyFundType.value.trim();
  if (f) body.fty_fund_type = f;
  const a = acmAcctCode.value.trim();
  if (a) body.acm_acct_code = a;
  if (billRegIntegration.value) body.bill_reg_integration_type = billRegIntegration.value;
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
  const f = ftyFundType.value.trim();
  if (f) params.set("fty_fund_type", f);
  const a = acmAcctCode.value.trim();
  if (a) params.set("acm_acct_code", a);
  if (billRegIntegration.value) params.set("bill_reg_integration_type", billRegIntegration.value);
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
  } catch (e) {
    toast.error("Load failed", e instanceof Error ? e.message : "Unable to load list.");
  } finally {
    loading.value = false;
  }
}

function refreshList() {
  void loadRows();
}

function searchRefund() {
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
  "Application No",
  "ID",
  "Name",
  "Account Code",
  "Reference No",
  "Application Date",
  "Amount Eligible (RM)",
  "Status",
  "Remark",
];

function rowExport(r: CcRefundApplicationAdminRow) {
  return {
    No: r.index,
    "Application No": r.applicationNo ?? "",
    ID: r.id ?? "",
    Name: r.name ?? "",
    "Account Code": r.accountLabel ?? r.accountCode ?? "",
    "Reference No": r.referenceNo ?? "",
    "Application Date": r.applicationDate ?? "",
    "Amount Eligible (RM)": r.amountEligibleRefund != null ? fmtMoney(r.amountEligibleRefund) : "",
    Status: r.status ?? "",
    Remark: r.remark ?? "",
  };
}

const overflowOpen = ref(false);
const overflowRoot = ref<HTMLElement | null>(null);

function onClickOutside(event: MouseEvent) {
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
  pageName: "Refund Application (Admin)",
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

watch([ftyFundType, acmAcctCode, billRegIntegration], () => {
  selectedIds.value = new Set();
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
    a.download = `CC_Refund_Application_Admin_${new Date().toISOString().slice(0, 10)}.xlsx`;
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

      <h1 class="page-title">Credit Control / Refund / Refund (Staff) / Admin / Refund Application</h1>

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

        <div class="space-y-4 p-4">
          <section class="rounded-lg border border-slate-100 bg-slate-50/90 p-4">
            <h3 class="mb-3 text-sm font-semibold text-slate-800">Filters</h3>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
              <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">Type of Refund</label>
                <input
                  v-model="ftyFundType"
                  type="text"
                  autocomplete="off"
                  class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-sm"
                  placeholder="Fund type code / description"
                />
              </div>
              <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">Account Code</label>
                <input
                  v-model="acmAcctCode"
                  type="text"
                  autocomplete="off"
                  class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-sm"
                  placeholder="Account code"
                />
              </div>
              <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">
                  Type of Bill Registration Integration Refund
                </label>
                <select
                  v-model="billRegIntegration"
                  class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-sm"
                >
                  <option value="">All</option>
                  <option value="I">I</option>
                  <option value="G">G</option>
                </select>
              </div>
            </div>
            <div class="mt-4 flex flex-wrap gap-2">
              <button
                type="button"
                class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-50"
                @click="refreshList"
              >
                Refresh
              </button>
              <button
                type="button"
                class="inline-flex items-center gap-1 rounded-lg bg-violet-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-violet-700"
                @click="searchRefund"
              >
                Search Refund
              </button>
            </div>
          </section>

          <div class="flex flex-wrap items-end justify-between gap-4">
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
            <div class="relative min-w-[200px] flex-1 sm:max-w-sm">
              <label class="mb-1 block text-xs font-medium text-slate-600">Search</label>
              <div class="relative">
                <Search class="pointer-events-none absolute left-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400" />
                <input
                  v-model="q"
                  type="search"
                  placeholder="Application no., ID, name, account, reference…"
                  class="w-full rounded-lg border border-slate-300 py-1.5 pl-8 pr-8 text-sm"
                  autocomplete="off"
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

          <p class="text-xs text-slate-500">
            Submissions must include rows from
            <strong>one</strong>
            application number only (legacy rule). Bill registration integration filter applies when
            <code class="rounded bg-slate-100 px-1">tra_extended_field</code>
            is present in the database. Requires signed-in Credit Control staff.
          </p>

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

          <div class="max-h-[min(28rem,70vh)] overflow-y-auto overflow-x-auto rounded-lg border border-slate-200">
            <table class="min-w-[720px] divide-y divide-slate-200 text-left text-xs sm:min-w-full">
              <thead class="sticky top-0 z-10 bg-slate-50 text-slate-600">
                <tr>
                  <th class="w-10 whitespace-nowrap px-2 py-2 font-medium">
                    <button
                      type="button"
                      class="rounded p-0.5 text-slate-600 hover:bg-slate-200"
                      title="Select all on page"
                      @click="toggleSelectAllOnPage"
                    >
                      <CheckSquare v-if="allOnPageSelected" class="h-4 w-4" />
                      <Square v-else class="h-4 w-4" />
                    </button>
                  </th>
                  <th class="whitespace-nowrap px-2 py-2 font-medium">No</th>
                  <th class="whitespace-nowrap px-2 py-2 font-medium">
                    <button type="button" class="hover:text-slate-900" @click="toggleSort('application_no')">
                      Application No
                    </button>
                  </th>
                  <th class="whitespace-nowrap px-2 py-2 font-medium">
                    <button type="button" class="hover:text-slate-900" @click="toggleSort('id')">ID</button>
                  </th>
                  <th class="whitespace-nowrap px-2 py-2 font-medium">
                    <button type="button" class="hover:text-slate-900" @click="toggleSort('name')">Name</button>
                  </th>
                  <th class="whitespace-nowrap px-2 py-2 font-medium">
                    <button type="button" class="hover:text-slate-900" @click="toggleSort('account_code')">
                      Account Code
                    </button>
                  </th>
                  <th class="whitespace-nowrap px-2 py-2 font-medium">
                    <button type="button" class="hover:text-slate-900" @click="toggleSort('reference_no')">
                      Reference No
                    </button>
                  </th>
                  <th class="whitespace-nowrap px-2 py-2 font-medium">
                    <button type="button" class="hover:text-slate-900" @click="toggleSort('application_date')">
                      Application Date
                    </button>
                  </th>
                  <th class="whitespace-nowrap px-2 py-2 text-right font-medium">
                    <button type="button" class="hover:text-slate-900" @click="toggleSort('amount_eligible_refund')">
                      Amt eligible (RM)
                    </button>
                  </th>
                  <th class="whitespace-nowrap px-2 py-2 font-medium">
                    <button type="button" class="hover:text-slate-900" @click="toggleSort('status')">Status</button>
                  </th>
                  <th class="whitespace-nowrap px-2 py-2 font-medium">Remark</th>
                  <th class="whitespace-nowrap px-2 py-2 font-medium">Action</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 bg-white text-slate-800">
                <tr v-if="loading">
                  <td colspan="12" class="px-3 py-10 text-center text-slate-500">
                    <span class="inline-block animate-pulse">Loading applications…</span>
                  </td>
                </tr>
                <tr v-else-if="rows.length === 0">
                  <td colspan="12" class="px-3 py-10 text-center text-slate-500">
                    <p class="font-medium text-slate-700">No refund applications</p>
                    <p class="mt-1 text-xs">
                      Adjust filters and click Search Refund, or refine the search box. Rows must be APPLY with staff
                      pay-to (B).
                    </p>
                  </td>
                </tr>
                <tr v-for="row in rows" v-else :key="`${row.traId}-${row.index}`" class="hover:bg-slate-50/80">
                  <td class="px-2 py-1.5">
                    <button
                      type="button"
                      class="rounded p-0.5 text-slate-600 hover:bg-slate-200"
                      @click="toggleRow(row.traId)"
                    >
                      <CheckSquare v-if="selectedIds.has(row.traId)" class="h-4 w-4" />
                      <Square v-else class="h-4 w-4" />
                    </button>
                  </td>
                  <td class="px-2 py-1.5">{{ row.index }}</td>
                  <td class="px-2 py-1.5 font-medium">{{ row.applicationNo }}</td>
                  <td class="px-2 py-1.5">{{ row.id }}</td>
                  <td class="max-w-[10rem] truncate px-2 py-1.5 sm:max-w-none" :title="row.name ?? ''">{{ row.name }}</td>
                  <td class="max-w-[8rem] truncate px-2 py-1.5 xl:max-w-none" :title="row.accountLabel ?? ''">
                    {{ row.accountLabel ?? row.accountCode }}
                  </td>
                  <td class="max-w-[8rem] truncate px-2 py-1.5" :title="row.referenceNo ?? ''">{{ row.referenceNo }}</td>
                  <td class="whitespace-nowrap px-2 py-1.5">{{ row.applicationDate }}</td>
                  <td class="px-2 py-1.5 text-right tabular-nums">{{ fmtMoney(row.amountEligibleRefund) }}</td>
                  <td class="px-2 py-1.5">{{ row.status }}</td>
                  <td class="max-w-[8rem] truncate px-2 py-1.5 text-slate-600" :title="row.remark ?? ''">
                    {{ row.remark }}
                  </td>
                  <td class="px-2 py-1.5">
                    <a
                      :href="row.reportUrl"
                      class="inline-flex items-center gap-0.5 text-violet-600 hover:underline"
                      target="_blank"
                      rel="noopener noreferrer"
                    >
                      <ExternalLink class="h-3 w-3" />
                      Open
                    </a>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div v-if="total > 0" class="flex flex-wrap items-center justify-between gap-3 text-xs text-slate-600">
            <span>Records: {{ total }}</span>
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
