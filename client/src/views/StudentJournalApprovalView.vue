<script setup lang="ts">
/**
 * Student Finance / Sponsor / Student Journal Approval (PAGEID 1954 / MENUID 2390).
 *
 * Source: FIMS BL `MZ_BL_SF_APPROVAL`. The page composes:
 *   - Read-only master form (manual_journal_master).
 *   - Read-only Credit datatable (mjd_trans_type = 'CR').
 *   - Read-only Debit datatable (mjd_trans_type = 'DT').
 *   - Approve / Reject form — calls workflowUpdate SP and is NOT
 *     migrated yet, so the Process panel renders disabled with an
 *     explanatory note.
 *
 * Legacy URL uses `wtk_application_id=<mjm_journal_id>` (workflow
 * payload) plus optional `taskId=<wtk_task_id>`. POST `details=1`
 * sends `mjm_journal_id` = `$_GET['wtk_application_id']` (see
 * `TRIGGER_PAGE_1954` onload BL). Vue accepts the journal id via any of:
 *   `wtk_application_id`, `mjm_journal_id`, or `id` (alias for tooling).
 * When opened from the sidebar with no query (no task), legacy still
 * shows empty Details + “No records” grids and hides the Process panel
 * (`if (!taskId) $('#cm_flowApprove').hide()`).
 *
 * Per project policy (legacy COMPONENT_JS has no `printout` field) we
 * surface PDF / CSV / Excel exports for both Credit and Debit grids.
 */
import { computed, onUnmounted, ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import {
  ChevronLeft,
  Download,
  FileDown,
  FileSpreadsheet,
  Search,
  X,
} from "lucide-vue-next";
import AdminLayout from "@/layouts/AdminLayout.vue";
import {
  getStudentJournalApprovalDetail,
  listStudentJournalApprovalCredit,
  listStudentJournalApprovalDebit,
} from "@/api/cms";
import { useToast } from "@/composables/useToast";
import type {
  StudentJournalApprovalDetail,
  StudentJournalApprovalFooter,
  StudentJournalApprovalRow,
} from "@/types";

const toast = useToast();
const route = useRoute();
const router = useRouter();

/** First positive integer from query (legacy `wtk_application_id` is the primary key). */
function parseJournalIdFromQuery(): number | null {
  const keys = [
    "wtk_application_id",
    "mjm_journal_id",
    "id",
    "wtkApplicationId",
    "mjmJournalId",
  ] as const;
  for (const key of keys) {
    const raw = route.query[key];
    const s = String(Array.isArray(raw) ? raw[0] : raw ?? "").trim();
    if (!s) continue;
    const n = Number(s);
    if (Number.isFinite(n) && n > 0) {
      return Math.floor(n);
    }
  }
  return null;
}

const journalId = computed(() => parseJournalIdFromQuery());

/** Present when user arrived from workflow inbox (legacy `taskId` GET). */
const workflowTaskId = computed(() => {
  const raw = route.query.taskId ?? route.query.task_id;
  const s = String(Array.isArray(raw) ? raw[0] : raw ?? "").trim();
  return s || null;
});

const detail = ref<StudentJournalApprovalDetail | null>(null);
const detailLoading = ref(false);

type SortDir = "asc" | "desc";

type CreditSortKey =
  | "cim_cust"
  | "fty_fund_type"
  | "at_activity_code"
  | "oun_code"
  | "ccr_costcentre"
  | "acm_acct_code"
  | "mjd_trans_amt"
  | "mjd_document_no";

type DebitSortKey =
  | "fty_fund_type"
  | "at_activity_code"
  | "oun_code"
  | "ccr_costcentre"
  | "acm_acct_code"
  | "mjd_document_no";

interface Grid<TKey extends string> {
  rows: StudentJournalApprovalRow[];
  loading: boolean;
  total: number;
  page: number;
  limit: number;
  q: string;
  sortBy: TKey;
  sortDir: SortDir;
  footer: StudentJournalApprovalFooter;
}

const credit = ref<Grid<CreditSortKey>>({
  rows: [],
  loading: false,
  total: 0,
  page: 1,
  limit: 10,
  q: "",
  sortBy: "cim_cust",
  sortDir: "asc",
  footer: { mjdTransAmt: 0, mjmTotalAmt: 0 },
});

const debit = ref<Grid<DebitSortKey>>({
  rows: [],
  loading: false,
  total: 0,
  page: 1,
  limit: 10,
  q: "",
  sortBy: "fty_fund_type",
  sortDir: "asc",
  footer: { mjdTransAmt: 0, mjmTotalAmt: 0 },
});

function fmt(n: number | null | undefined): string {
  return new Intl.NumberFormat("en-MY", {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(Number.isFinite(n ?? NaN) ? (n as number) : 0);
}

function goBack() {
  router.back();
}

async function loadDetail() {
  if (!journalId.value) return;
  detailLoading.value = true;
  try {
    const res = await getStudentJournalApprovalDetail(journalId.value);
    detail.value = res.data;
  } catch (e) {
    toast.error(
      "Load failed",
      e instanceof Error ? e.message : "Unable to load journal master.",
    );
  } finally {
    detailLoading.value = false;
  }
}

async function loadCredit() {
  if (!journalId.value) return;
  credit.value.loading = true;
  const params = new URLSearchParams({
    page: String(credit.value.page),
    limit: String(credit.value.limit),
    sort_by: credit.value.sortBy,
    sort_dir: credit.value.sortDir,
  });
  if (credit.value.q.trim()) params.set("q", credit.value.q.trim());
  try {
    const res = await listStudentJournalApprovalCredit(
      journalId.value,
      `?${params.toString()}`,
    );
    credit.value.rows = res.data;
    credit.value.total = Number(res.meta?.total ?? 0);
    credit.value.footer = res.meta?.footer ?? { mjdTransAmt: 0, mjmTotalAmt: 0 };
  } catch (e) {
    toast.error("Load failed", e instanceof Error ? e.message : "Unable to load Credit rows.");
  } finally {
    credit.value.loading = false;
  }
}

async function loadDebit() {
  if (!journalId.value) return;
  debit.value.loading = true;
  const params = new URLSearchParams({
    page: String(debit.value.page),
    limit: String(debit.value.limit),
    sort_by: debit.value.sortBy,
    sort_dir: debit.value.sortDir,
  });
  if (debit.value.q.trim()) params.set("q", debit.value.q.trim());
  try {
    const res = await listStudentJournalApprovalDebit(
      journalId.value,
      `?${params.toString()}`,
    );
    debit.value.rows = res.data;
    debit.value.total = Number(res.meta?.total ?? 0);
    debit.value.footer = res.meta?.footer ?? { mjdTransAmt: 0, mjmTotalAmt: 0 };
  } catch (e) {
    toast.error("Load failed", e instanceof Error ? e.message : "Unable to load Debit rows.");
  } finally {
    debit.value.loading = false;
  }
}

function toggleCreditSort(col: CreditSortKey) {
  if (credit.value.sortBy === col)
    credit.value.sortDir = credit.value.sortDir === "asc" ? "desc" : "asc";
  else {
    credit.value.sortBy = col;
    credit.value.sortDir = "asc";
  }
  void loadCredit();
}

function toggleDebitSort(col: DebitSortKey) {
  if (debit.value.sortBy === col)
    debit.value.sortDir = debit.value.sortDir === "asc" ? "desc" : "asc";
  else {
    debit.value.sortBy = col;
    debit.value.sortDir = "asc";
  }
  void loadDebit();
}

const creditTotalPages = computed(() =>
  credit.value.total ? Math.max(1, Math.ceil(credit.value.total / credit.value.limit)) : 1,
);
const debitTotalPages = computed(() =>
  debit.value.total ? Math.max(1, Math.ceil(debit.value.total / debit.value.limit)) : 1,
);

let creditDebounce: ReturnType<typeof setTimeout> | null = null;
let debitDebounce: ReturnType<typeof setTimeout> | null = null;

watch(
  () => credit.value.q,
  () => {
    if (creditDebounce) clearTimeout(creditDebounce);
    creditDebounce = setTimeout(() => {
      creditDebounce = null;
      credit.value.page = 1;
      void loadCredit();
    }, 350);
  },
);

watch(
  () => debit.value.q,
  () => {
    if (debitDebounce) clearTimeout(debitDebounce);
    debitDebounce = setTimeout(() => {
      debitDebounce = null;
      debit.value.page = 1;
      void loadDebit();
    }, 350);
  },
);

async function exportExcel(which: "credit" | "debit") {
  const grid = which === "credit" ? credit.value : debit.value;
  if (grid.rows.length === 0) {
    toast.info("No data", "There is nothing to export.");
    return;
  }
  try {
    const ExcelJS = await import("exceljs");
    const wb = new ExcelJS.Workbook();
    const ws = wb.addWorksheet(which === "credit" ? "Credit" : "Debit");
    if (which === "credit") {
      ws.addRow([
        "Student / Sponsor",
        "Fund",
        "Activity",
        "PTJ",
        "Cost Center",
        "Account Code",
        "Amount (RM)",
        "Document No",
      ]);
      grid.rows.forEach((r) => {
        ws.addRow([
          r.cimCust ?? "",
          r.ftyFundType ?? "",
          r.atActivityCode ?? "",
          r.ounCode ?? "",
          r.ccrCostcentre ?? "",
          r.acmAcctCode ?? "",
          r.mjdTransAmt,
          r.mjdDocumentNo ?? "",
        ]);
      });
    } else {
      ws.addRow([
        "Fund",
        "Activity",
        "PTJ",
        "Cost Center",
        "Account Code",
        "Amount",
        "Document No",
      ]);
      grid.rows.forEach((r) => {
        ws.addRow([
          r.ftyFundType ?? "",
          r.atActivityCode ?? "",
          r.ounCode ?? "",
          r.ccrCostcentre ?? "",
          r.acmAcctCode ?? "",
          r.mjmTotalAmt ?? 0,
          r.mjdDocumentNo ?? "",
        ]);
      });
    }
    const buf = await wb.xlsx.writeBuffer();
    const blob = new Blob([buf], {
      type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
    });
    const url = URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.href = url;
    a.download = `JournalApproval_${which}_${journalId.value ?? "x"}_${new Date()
      .toISOString()
      .slice(0, 10)}.xlsx`;
    a.click();
    URL.revokeObjectURL(url);
    toast.success("Excel downloaded");
  } catch (e) {
    toast.error("Export failed", e instanceof Error ? e.message : "Excel export failed.");
  }
}

function exportCsv(which: "credit" | "debit") {
  const grid = which === "credit" ? credit.value : debit.value;
  if (grid.rows.length === 0) {
    toast.info("No data", "There is nothing to export.");
    return;
  }
  const headers =
    which === "credit"
      ? [
          "Student / Sponsor",
          "Fund",
          "Activity",
          "PTJ",
          "Cost Center",
          "Account Code",
          "Amount (RM)",
          "Document No",
        ]
      : [
          "Fund",
          "Activity",
          "PTJ",
          "Cost Center",
          "Account Code",
          "Amount",
          "Document No",
        ];
  const body =
    which === "credit"
      ? grid.rows.map((r) => [
          r.cimCust ?? "",
          r.ftyFundType ?? "",
          r.atActivityCode ?? "",
          r.ounCode ?? "",
          r.ccrCostcentre ?? "",
          r.acmAcctCode ?? "",
          (r.mjdTransAmt ?? 0).toFixed(2),
          r.mjdDocumentNo ?? "",
        ])
      : grid.rows.map((r) => [
          r.ftyFundType ?? "",
          r.atActivityCode ?? "",
          r.ounCode ?? "",
          r.ccrCostcentre ?? "",
          r.acmAcctCode ?? "",
          (r.mjmTotalAmt ?? 0).toFixed(2),
          r.mjdDocumentNo ?? "",
        ]);
  const escape = (s: string) =>
    /[",\n]/.test(s) ? '"' + s.replace(/"/g, '""') + '"' : s;
  const csv = [headers, ...body]
    .map((row) => row.map((c) => escape(String(c ?? ""))).join(","))
    .join("\n");
  const blob = new Blob([csv], { type: "text/csv;charset=utf-8" });
  const url = URL.createObjectURL(blob);
  const a = document.createElement("a");
  a.href = url;
  a.download = `JournalApproval_${which}_${journalId.value ?? "x"}_${new Date()
    .toISOString()
    .slice(0, 10)}.csv`;
  a.click();
  URL.revokeObjectURL(url);
}

function exportPdf(which: "credit" | "debit") {
  toast.info(
    "PDF export",
    `Use the ${which === "credit" ? "Credit" : "Debit"} CSV/Excel export and print to PDF.`,
  );
}

/** Reset lists when no journal; load when id present (parity with legacy empty shell). */
function clearJournalLists() {
  detail.value = null;
  detailLoading.value = false;
  credit.value.rows = [];
  credit.value.loading = false;
  credit.value.total = 0;
  credit.value.page = 1;
  credit.value.footer = { mjdTransAmt: 0, mjmTotalAmt: 0 };
  debit.value.rows = [];
  debit.value.loading = false;
  debit.value.total = 0;
  debit.value.page = 1;
  debit.value.footer = { mjdTransAmt: 0, mjmTotalAmt: 0 };
}

async function loadAllJournalData() {
  if (!journalId.value) {
    clearJournalLists();
    return;
  }
  await Promise.all([loadDetail(), loadCredit(), loadDebit()]);
}

watch(journalId, () => void loadAllJournalData(), { immediate: true });

onUnmounted(() => {
  if (creditDebounce) clearTimeout(creditDebounce);
  if (debitDebounce) clearTimeout(debitDebounce);
});
</script>

<template>
  <AdminLayout>
    <div class="space-y-4">
      <div class="flex items-center gap-2">
        <button
          type="button"
          class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-2.5 py-1 text-xs text-slate-600 hover:bg-slate-50"
          @click="goBack"
          aria-label="Back"
        >
          <ChevronLeft class="h-3.5 w-3.5" />
          Back
        </button>
        <h1 class="page-title">
          Student Finance / Sponsor / Student Journal Approval by Sponsor
        </h1>
      </div>

      <article class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
          <h1 class="text-base font-semibold text-slate-900">Details</h1>
          <span v-if="detail" class="text-xs text-slate-500">
            Journal #{{ detail.mjmJournalNo ?? detail.mjmJournalId }}
          </span>
        </div>
        <div v-if="detailLoading && journalId" class="p-4 text-sm text-slate-500">
          Loading...
        </div>
        <div v-else class="flex flex-col gap-4 p-4 md:flex-row md:gap-8">
          <div class="min-w-0 flex-1 space-y-3">
            <div>
              <label class="mb-1 block text-xs font-medium text-slate-500">Journal No</label>
              <input
                :value="detail?.mjmJournalNo ?? ''"
                disabled
                class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm"
              />
            </div>
            <div>
              <label class="mb-1 block text-xs font-medium text-slate-500">Description</label>
              <input
                :value="detail?.mjmJournalDesc ?? ''"
                disabled
                class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm"
              />
            </div>
            <div>
              <label class="mb-1 block text-xs font-medium text-slate-500">Type of Journal</label>
              <input
                :value="detail?.mjmTypeofjournal ?? ''"
                disabled
                class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm"
              />
            </div>
            <div>
              <label class="mb-1 block text-xs font-medium text-slate-500">Date Journal</label>
              <input
                :value="detail?.mjmEnterdate ?? ''"
                disabled
                class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm"
              />
            </div>
            <div>
              <label class="mb-1 block text-xs font-medium text-slate-500">Advance Category</label>
              <input
                :value="detail?.advanceCategory ?? ''"
                disabled
                class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm"
              />
            </div>
          </div>
          <div class="min-w-0 flex-1 space-y-3">
            <div>
              <label class="mb-1 block text-xs font-medium text-slate-500">Advance Amount</label>
              <input
                :value="
                  detail != null && detail.advanceAmount != null
                    ? `MYR ${fmt(detail.advanceAmount)}`
                    : ''
                "
                disabled
                class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-right text-sm"
              />
            </div>
            <div>
              <label class="mb-1 block text-xs font-medium text-slate-500">Total Amount</label>
              <input
                :value="detail != null ? `MYR ${fmt(detail.mjmTotalAmt)}` : ''"
                disabled
                class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-right text-sm"
              />
            </div>
            <div>
              <label class="mb-1 block text-xs font-medium text-slate-500">Status</label>
              <input
                :value="detail?.mjmStatus ?? ''"
                disabled
                class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm"
              />
            </div>
            <div>
              <label class="mb-1 block text-xs font-medium text-slate-500">Reference No.</label>
              <input
                :value="detail?.dpmDepositNo ?? ''"
                disabled
                class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm"
              />
            </div>
            <div>
              <label class="mb-1 block text-xs font-medium text-slate-500">Semester</label>
              <input
                :value="detail?.semester ?? ''"
                disabled
                class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm"
              />
            </div>
          </div>
        </div>
      </article>

      <article class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
          <h1 class="text-base font-semibold text-slate-900">Credit</h1>
          <div class="flex items-center gap-2">
            <div class="relative">
              <Search
                class="pointer-events-none absolute left-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400"
              />
              <input
                v-model="credit.q"
                type="search"
                placeholder="Filter rows..."
                class="w-56 rounded-lg border border-slate-300 py-1.5 pl-8 pr-8 text-sm"
              />
              <button
                v-if="credit.q"
                type="button"
                class="absolute right-1 top-1/2 -translate-y-1/2 rounded p-0.5 text-slate-400 hover:bg-slate-100"
                aria-label="Clear search"
                @click="credit.q = ''"
              >
                <X class="h-3.5 w-3.5" />
              </button>
            </div>
          </div>
        </div>
        <div class="space-y-3 p-4">
          <div class="overflow-x-auto rounded-lg border border-slate-200">
            <div :class="credit.rows.length > 10 ? 'max-h-[480px] overflow-y-auto' : ''">
              <table class="admin-table-kitchen w-full min-w-[960px] text-sm">
                <thead class="admin-table-thead-sticky">
                  <tr class="border-b border-slate-200 text-left">
                    <th
                      class="cursor-pointer px-3 py-2 text-xs font-semibold uppercase"
                      @click="toggleCreditSort('cim_cust')"
                    >
                      Student / Sponsor
                      <span v-if="credit.sortBy === 'cim_cust'">{{
                        credit.sortDir === "asc" ? "↑" : "↓"
                      }}</span>
                    </th>
                    <th
                      class="cursor-pointer px-3 py-2 text-xs font-semibold uppercase"
                      @click="toggleCreditSort('fty_fund_type')"
                    >
                      Fund
                    </th>
                    <th
                      class="cursor-pointer px-3 py-2 text-xs font-semibold uppercase"
                      @click="toggleCreditSort('at_activity_code')"
                    >
                      Activity
                    </th>
                    <th
                      class="cursor-pointer px-3 py-2 text-xs font-semibold uppercase"
                      @click="toggleCreditSort('oun_code')"
                    >
                      PTJ
                    </th>
                    <th
                      class="cursor-pointer px-3 py-2 text-xs font-semibold uppercase"
                      @click="toggleCreditSort('ccr_costcentre')"
                    >
                      Cost Center
                    </th>
                    <th
                      class="cursor-pointer px-3 py-2 text-xs font-semibold uppercase"
                      @click="toggleCreditSort('acm_acct_code')"
                    >
                      Account Code
                    </th>
                    <th
                      class="cursor-pointer px-3 py-2 text-right text-xs font-semibold uppercase"
                      @click="toggleCreditSort('mjd_trans_amt')"
                    >
                      Amount (RM)
                    </th>
                    <th
                      class="cursor-pointer px-3 py-2 text-xs font-semibold uppercase"
                      @click="toggleCreditSort('mjd_document_no')"
                    >
                      Document No
                    </th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-if="credit.loading">
                    <td colspan="8" class="px-3 py-6 text-center text-sm text-slate-500">
                      Loading...
                    </td>
                  </tr>
                  <tr v-else-if="credit.rows.length === 0">
                    <td colspan="8" class="px-3 py-6 text-center text-sm text-slate-500">
                      No records
                    </td>
                  </tr>
                  <tr
                    v-for="row in credit.rows"
                    :key="`cr-${row.mjdJournalDetlId}`"
                    class="border-b border-slate-100 hover:bg-slate-50"
                  >
                    <td class="whitespace-nowrap px-3 py-2">{{ row.cimCust ?? "-" }}</td>
                    <td class="whitespace-nowrap px-3 py-2">{{ row.ftyFundType ?? "-" }}</td>
                    <td class="whitespace-nowrap px-3 py-2">{{ row.atActivityCode ?? "-" }}</td>
                    <td class="whitespace-nowrap px-3 py-2">{{ row.ounCode ?? "-" }}</td>
                    <td class="whitespace-nowrap px-3 py-2">{{ row.ccrCostcentre ?? "-" }}</td>
                    <td class="whitespace-nowrap px-3 py-2">{{ row.acmAcctCode ?? "-" }}</td>
                    <td class="px-3 py-2 text-right tabular-nums">
                      {{ fmt(row.mjdTransAmt) }}
                    </td>
                    <td class="whitespace-nowrap px-3 py-2">{{ row.mjdDocumentNo ?? "-" }}</td>
                  </tr>
                </tbody>
                <tfoot v-if="credit.rows.length > 0">
                  <tr class="border-t-2 border-slate-300 bg-slate-50">
                    <td colspan="6" class="px-3 py-2 text-right text-xs font-semibold uppercase">
                      Total
                    </td>
                    <td class="px-3 py-2 text-right text-sm font-semibold tabular-nums">
                      {{ fmt(credit.footer.mjdTransAmt) }}
                    </td>
                    <td></td>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>
          <div class="flex flex-wrap items-center justify-between gap-3 pt-1">
            <div class="text-xs text-slate-500">
              Showing
              {{ credit.total === 0 ? 0 : (credit.page - 1) * credit.limit + 1 }}-{{
                Math.min(credit.page * credit.limit, credit.total)
              }}
              of {{ credit.total }}
            </div>
            <div class="flex items-center gap-2">
              <button
                type="button"
                :disabled="credit.page <= 1"
                class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium disabled:opacity-50"
                @click="
                  credit.page -= 1;
                  loadCredit();
                "
              >
                Prev
              </button>
              <span class="text-xs text-slate-600">Page {{ credit.page }} / {{ creditTotalPages }}</span>
              <button
                type="button"
                :disabled="credit.page >= creditTotalPages"
                class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium disabled:opacity-50"
                @click="
                  credit.page += 1;
                  loadCredit();
                "
              >
                Next
              </button>
              <div class="mx-2 h-5 w-px bg-slate-200" />
              <button
                type="button"
                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium"
                @click="exportPdf('credit')"
              >
                <Download class="h-3.5 w-3.5" />PDF
              </button>
              <button
                type="button"
                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium"
                @click="exportCsv('credit')"
              >
                <FileDown class="h-3.5 w-3.5" />CSV
              </button>
              <button
                type="button"
                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium"
                @click="exportExcel('credit')"
              >
                <FileSpreadsheet class="h-3.5 w-3.5" />Excel
              </button>
            </div>
          </div>
        </div>
      </article>

      <article class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
          <h1 class="text-base font-semibold text-slate-900">Debit</h1>
          <div class="flex items-center gap-2">
            <div class="relative">
              <Search
                class="pointer-events-none absolute left-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400"
              />
              <input
                v-model="debit.q"
                type="search"
                placeholder="Filter rows..."
                class="w-56 rounded-lg border border-slate-300 py-1.5 pl-8 pr-8 text-sm"
              />
              <button
                v-if="debit.q"
                type="button"
                class="absolute right-1 top-1/2 -translate-y-1/2 rounded p-0.5 text-slate-400 hover:bg-slate-100"
                aria-label="Clear search"
                @click="debit.q = ''"
              >
                <X class="h-3.5 w-3.5" />
              </button>
            </div>
          </div>
        </div>
        <div class="space-y-3 p-4">
          <div class="overflow-x-auto rounded-lg border border-slate-200">
            <div :class="debit.rows.length > 10 ? 'max-h-[480px] overflow-y-auto' : ''">
              <table class="admin-table-kitchen w-full min-w-[900px] text-sm">
                <thead class="admin-table-thead-sticky">
                  <tr class="border-b border-slate-200 text-left">
                    <th
                      class="cursor-pointer px-3 py-2 text-xs font-semibold uppercase"
                      @click="toggleDebitSort('fty_fund_type')"
                    >
                      Fund
                    </th>
                    <th
                      class="cursor-pointer px-3 py-2 text-xs font-semibold uppercase"
                      @click="toggleDebitSort('at_activity_code')"
                    >
                      Activity
                    </th>
                    <th
                      class="cursor-pointer px-3 py-2 text-xs font-semibold uppercase"
                      @click="toggleDebitSort('oun_code')"
                    >
                      PTJ
                    </th>
                    <th
                      class="cursor-pointer px-3 py-2 text-xs font-semibold uppercase"
                      @click="toggleDebitSort('ccr_costcentre')"
                    >
                      Cost Center
                    </th>
                    <th
                      class="cursor-pointer px-3 py-2 text-xs font-semibold uppercase"
                      @click="toggleDebitSort('acm_acct_code')"
                    >
                      Account Code
                    </th>
                    <th class="px-3 py-2 text-right text-xs font-semibold uppercase">Amount</th>
                    <th
                      class="cursor-pointer px-3 py-2 text-xs font-semibold uppercase"
                      @click="toggleDebitSort('mjd_document_no')"
                    >
                      Document No
                    </th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-if="debit.loading">
                    <td colspan="7" class="px-3 py-6 text-center text-sm text-slate-500">
                      Loading...
                    </td>
                  </tr>
                  <tr v-else-if="debit.rows.length === 0">
                    <td colspan="7" class="px-3 py-6 text-center text-sm text-slate-500">
                      No records
                    </td>
                  </tr>
                  <tr
                    v-for="row in debit.rows"
                    :key="`dt-${row.mjdJournalDetlId}`"
                    class="border-b border-slate-100 hover:bg-slate-50"
                  >
                    <td class="whitespace-nowrap px-3 py-2">{{ row.ftyFundType ?? "-" }}</td>
                    <td class="whitespace-nowrap px-3 py-2">{{ row.atActivityCode ?? "-" }}</td>
                    <td class="whitespace-nowrap px-3 py-2">{{ row.ounCode ?? "-" }}</td>
                    <td class="whitespace-nowrap px-3 py-2">{{ row.ccrCostcentre ?? "-" }}</td>
                    <td class="whitespace-nowrap px-3 py-2">{{ row.acmAcctCode ?? "-" }}</td>
                    <td class="px-3 py-2 text-right tabular-nums">
                      {{ fmt(row.mjmTotalAmt) }}
                    </td>
                    <td class="whitespace-nowrap px-3 py-2">{{ row.mjdDocumentNo ?? "-" }}</td>
                  </tr>
                </tbody>
                <tfoot v-if="debit.rows.length > 0">
                  <tr class="border-t-2 border-slate-300 bg-slate-50">
                    <td colspan="5" class="px-3 py-2 text-right text-xs font-semibold uppercase">
                      Total
                    </td>
                    <td class="px-3 py-2 text-right text-sm font-semibold tabular-nums">
                      {{ fmt(debit.footer.mjdTransAmt) }}
                    </td>
                    <td></td>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>
          <div class="flex flex-wrap items-center justify-between gap-3 pt-1">
            <div class="text-xs text-slate-500">
              Showing
              {{ debit.total === 0 ? 0 : (debit.page - 1) * debit.limit + 1 }}-{{
                Math.min(debit.page * debit.limit, debit.total)
              }}
              of {{ debit.total }}
            </div>
            <div class="flex items-center gap-2">
              <button
                type="button"
                :disabled="debit.page <= 1"
                class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium disabled:opacity-50"
                @click="
                  debit.page -= 1;
                  loadDebit();
                "
              >
                Prev
              </button>
              <span class="text-xs text-slate-600">Page {{ debit.page }} / {{ debitTotalPages }}</span>
              <button
                type="button"
                :disabled="debit.page >= debitTotalPages"
                class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium disabled:opacity-50"
                @click="
                  debit.page += 1;
                  loadDebit();
                "
              >
                Next
              </button>
              <div class="mx-2 h-5 w-px bg-slate-200" />
              <button
                type="button"
                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium"
                @click="exportPdf('debit')"
              >
                <Download class="h-3.5 w-3.5" />PDF
              </button>
              <button
                type="button"
                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium"
                @click="exportCsv('debit')"
              >
                <FileDown class="h-3.5 w-3.5" />CSV
              </button>
              <button
                type="button"
                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium"
                @click="exportExcel('debit')"
              >
                <FileSpreadsheet class="h-3.5 w-3.5" />Excel
              </button>
            </div>
          </div>
        </div>
      </article>

      <article v-if="workflowTaskId" class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
          <h1 class="text-base font-semibold text-slate-900">Process</h1>
        </div>
        <div class="space-y-3 p-4">
          <div class="rounded-md border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-800">
            The legacy approval flow calls <code>workflowUpdate</code> stored procedure and
            depends on the workflow task table (<code>wf_task</code>). Both have not been
            migrated yet — Approve / Reject is disabled.
          </div>
          <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
            <div>
              <label class="mb-1 block text-xs font-medium text-slate-500">Status</label>
              <select disabled class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm">
                <option>—</option>
              </select>
            </div>
            <div class="md:col-span-2">
              <label class="mb-1 block text-xs font-medium text-slate-500">Remarks</label>
              <textarea
                disabled
                rows="3"
                class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm"
              ></textarea>
            </div>
          </div>
          <div class="flex justify-end">
            <span title="Workflow update SP is not migrated yet">
              <button
                type="button"
                disabled
                class="inline-flex cursor-not-allowed items-center gap-1.5 rounded-lg bg-slate-300 px-4 py-2 text-sm font-medium text-white opacity-60"
              >
                Save &amp; Submit
              </button>
            </span>
          </div>
        </div>
      </article>
    </div>
  </AdminLayout>
</template>
