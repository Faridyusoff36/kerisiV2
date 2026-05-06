<script setup lang="ts">
/**
 * Credit Control — staff refund listing (portal queue).
 *
 * - Default (no props): MENUID **2604** — List Of Refund Application (Portal).
 * - With `adminList` layout props: MENUID **2287** — Admin / List of Refund (same legacy
 *   `SNA_API_LIST_OF_REFUND_CC_STAFF` / `ListOfRefund` datatable + submit).
 * - Legacy onload hook: `SNA_JS_LIST_OF_REFUND_CC_STAFF` (`refresh`).
 */
import { computed, onMounted, onUnmounted, ref, watch } from "vue";
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
  checkCreditControlRefundPortalSubmit,
  listCreditControlListOfRefundPortal,
  rejectCreditControlRefundPortalBatch,
  submitCreditControlRefundPortalBatch,
} from "@/api/cms";
import { useDatatableFeatures } from "@/composables/useDatatableFeatures";
import type { DatatableRefApi } from "@/composables/useDatatableFeatures";
import { useToast } from "@/composables/useToast";
import type { CcListOfRefundPortalRow } from "@/types";

const props = withDefaults(
  defineProps<{
    /** Full breadcrumb line in `.page-title` (e.g. Admin / List of Refund). */
    pageHeading?: string;
    cardTitle?: string;
    /** PDF/CSV template + export labels in `useDatatableFeatures`. */
    datatablePageName?: string;
    excelFileLabel?: string;
    excelSheetName?: string;
    emptyStateTitle?: string;
    emptyStateHint?: string;
  }>(),
  {
    pageHeading: "Credit Control / Refund / Refund (Staff) / List Of Refund Application (Portal)",
    cardTitle: "List Of Refund Application (Portal)",
    datatablePageName: "List Of Refund Application (Portal)",
    excelFileLabel: "CC_Refund_Portal",
    excelSheetName: "Refund portal",
    emptyStateTitle: "No portal refund applications",
    emptyStateHint:
      "Try changing search criteria or filters. New submissions appear when status is APPLY.",
  },
);

const toast = useToast();
const datatableRef = ref<DatatableRefApi | null>(null);
const rows = ref<CcListOfRefundPortalRow[]>([]);
const page = ref(1);
const limit = ref(10);
const q = ref("");
const total = ref(0);
const loading = ref(false);
const submitting = ref(false);
const rejecting = ref(false);

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
  const params = new URLSearchParams({
    page: String(page.value),
    limit: String(limit.value),
    sort_by: sortBy.value,
    sort_dir: sortDir.value,
    ...(q.value.trim() ? { q: q.value.trim() } : {}),
  });
  try {
    const res = await listCreditControlListOfRefundPortal(`?${params.toString()}`);
    rows.value = res.data;
    total.value = Number(res.meta?.total ?? 0);
  } catch (e) {
    toast.error("Load failed", e instanceof Error ? e.message : "Unable to load list.");
  } finally {
    loading.value = false;
  }
}

async function submitSelected() {
  const ids = [...selectedIds.value];
  if (ids.length === 0) {
    toast.info("Nothing selected", "Choose at least one row.");
    return;
  }
  try {
    const pre = await checkCreditControlRefundPortalSubmit({ tra_ids: ids });
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
    const res = await submitCreditControlRefundPortalBatch({ tra_ids: ids });
    toast.success("Submitted", `${res.data.updated} row(s) updated.`);
    selectedIds.value = new Set();
    await loadRows();
  } catch (e) {
    toast.error("Submit failed", e instanceof Error ? e.message : String(e));
  } finally {
    submitting.value = false;
  }
}

async function rejectSelected() {
  const ids = [...selectedIds.value];
  if (ids.length === 0) {
    toast.info("Nothing selected", "Choose at least one row.");
    return;
  }
  try {
    const pre = await checkCreditControlRefundPortalSubmit({ tra_ids: ids });
    if (!pre.data.ok) {
      toast.error(
        "Cannot reject",
        `Selections must belong to one application only (found ${pre.data.distinct_application_count} distinct application numbers).`,
      );
      return;
    }
  } catch (e) {
    toast.error("Check failed", e instanceof Error ? e.message : String(e));
    return;
  }
  const remark = window.prompt(
    "Rejection remark (required). This is stored on the application line(s):",
    "",
  );
  if (remark === null) return;
  const trimmed = remark.trim();
  if (trimmed === "") {
    toast.error("Remark required", "Enter a rejection reason.");
    return;
  }
  if (
    !confirm(
      `Reject ${ids.length} line(s) for application ${rows.value.find((r) => ids.includes(r.traId))?.applicationNo ?? ""}? This cannot be undone from this screen.`,
    )
  ) {
    return;
  }
  rejecting.value = true;
  try {
    const res = await rejectCreditControlRefundPortalBatch({ tra_ids: ids, remark: trimmed });
    toast.success("Rejected", `${res.data.updated} row(s) updated.`);
    selectedIds.value = new Set();
    await loadRows();
  } catch (e) {
    toast.error("Reject failed", e instanceof Error ? e.message : String(e));
  } finally {
    rejecting.value = false;
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
  "Amount Portal (RM)",
  "Amount Refund (RM)",
  "Status",
  "Remark",
  "Supporting Document",
];

function rowExport(r: CcListOfRefundPortalRow) {
  return {
    No: r.index,
    "Application No": r.applicationNo ?? "",
    ID: r.id ?? "",
    Name: r.name ?? "",
    "Account Code": r.accountLabel ?? r.accountCode ?? "",
    "Reference No": r.referenceNo ?? "",
    "Application Date": r.applicationDate ?? "",
    "Amount Portal (RM)": r.traAmt != null ? fmtMoney(r.traAmt) : "",
    "Amount Refund (RM)": r.amountEligibleRefund != null ? fmtMoney(r.amountEligibleRefund) : "",
    Status: r.status ?? "",
    Remark: r.remark ?? "",
    "Supporting Document": r.supportingDocumentUrl ?? r.dzPath ?? "",
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
  pageName: props.datatablePageName,
  apiDataPath: "/credit-control/list-of-refund-portal",
  defaultExportColumns: exportColumns,
  getFilteredList: () => rows.value.map((r) => rowExport(r) as Record<string, unknown>),
  datatableRef,
  searchKeyword: q,
  smartFilter: ref({}),
  applyFilters: () => void loadRows(),
});

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

async function exportExcel() {
  try {
    if (rows.value.length === 0) {
      toast.info("No data", "There is nothing to export.");
      return;
    }
    const ExcelJS = await import("exceljs");
    const wb = new ExcelJS.Workbook();
    const ws = wb.addWorksheet(props.excelSheetName);
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
    a.download = `${props.excelFileLabel}_${new Date().toISOString().slice(0, 10)}.xlsx`;
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
  (window as unknown as { SNA_JS_LIST_OF_REFUND_CC_STAFF?: { refresh: () => void } }).SNA_JS_LIST_OF_REFUND_CC_STAFF =
    {
      refresh: () => void loadRows(),
    };
  void loadRows();
});
onUnmounted(() => {
  document.removeEventListener("click", onClickOutside);
  if (searchDeb) clearTimeout(searchDeb);
  delete (window as unknown as { SNA_JS_LIST_OF_REFUND_CC_STAFF?: unknown }).SNA_JS_LIST_OF_REFUND_CC_STAFF;
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

      <h1 class="page-title">{{ props.pageHeading }}</h1>

      <article class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between gap-2 border-b border-slate-100 px-4 py-3">
          <h2 class="text-base font-semibold text-slate-900">{{ props.cardTitle }}</h2>
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
          <div class="flex flex-wrap items-end justify-between gap-4">
            <div class="flex flex-wrap items-center gap-2">
              <label class="text-xs font-medium text-slate-600">Display</label>
              <select v-model.number="limit" class="rounded-lg border border-slate-300 px-2 py-1.5 text-sm">
                <option v-for="n in [5, 10, 25, 50, 100]" :key="n" :value="n">{{ n }}</option>
              </select>
              <button
                type="button"
                class="rounded-lg bg-violet-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-violet-700 disabled:opacity-50"
                :disabled="selectedIds.size === 0 || submitting || rejecting"
                @click="submitSelected"
              >
                {{ submitting ? "Submitting…" : "Submit selected" }}
              </button>
              <button
                type="button"
                class="rounded-lg border border-rose-300 bg-white px-3 py-1.5 text-sm font-medium text-rose-700 hover:bg-rose-50 disabled:opacity-50"
                :disabled="selectedIds.size === 0 || submitting || rejecting"
                @click="rejectSelected"
              >
                {{ rejecting ? "Rejecting…" : "Reject selected" }}
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
            application number only (legacy rule). Requires signed-in Credit Control staff.
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
            <table class="min-w-[900px] divide-y divide-slate-200 text-left text-xs sm:min-w-full">
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
                    <button type="button" class="hover:text-slate-900" @click="toggleSort('amount')">
                      Amount Portal (RM)
                    </button>
                  </th>
                  <th class="whitespace-nowrap px-2 py-2 text-right font-medium">
                    <button type="button" class="hover:text-slate-900" @click="toggleSort('amount_eligible_refund')">
                      Amount Refund (RM)
                    </button>
                  </th>
                  <th class="whitespace-nowrap px-2 py-2 font-medium">
                    <button type="button" class="hover:text-slate-900" @click="toggleSort('status')">Status</button>
                  </th>
                  <th class="whitespace-nowrap px-2 py-2 font-medium">Remark</th>
                  <th class="whitespace-nowrap px-2 py-2 font-medium">Supporting Document</th>
                  <th class="whitespace-nowrap px-2 py-2 font-medium">Action</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 bg-white text-slate-800">
                <tr v-if="loading">
                  <td colspan="14" class="px-3 py-10 text-center text-slate-500">
                    <span class="inline-block animate-pulse">Loading applications…</span>
                  </td>
                </tr>
                <tr v-else-if="rows.length === 0">
                  <td colspan="14" class="px-3 py-10 text-center text-slate-500">
                    <p class="font-medium text-slate-700">{{ props.emptyStateTitle }}</p>
                    <p class="mt-1 text-xs">{{ props.emptyStateHint }}</p>
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
                  <td class="px-2 py-1.5 text-right tabular-nums">{{ fmtMoney(row.traAmt) }}</td>
                  <td class="px-2 py-1.5 text-right tabular-nums">{{ fmtMoney(row.amountEligibleRefund) }}</td>
                  <td class="px-2 py-1.5">{{ row.status }}</td>
                  <td class="max-w-[8rem] truncate px-2 py-1.5 text-slate-600" :title="row.remark ?? ''">
                    {{ row.remark }}
                  </td>
                  <td class="max-w-[9rem] px-2 py-1.5">
                    <a
                      v-if="row.supportingDocumentUrl"
                      :href="row.supportingDocumentUrl"
                      class="inline-flex items-center gap-0.5 truncate text-violet-600 hover:underline"
                      target="_blank"
                      rel="noopener noreferrer"
                      :title="row.dzPath ?? row.supportingDocumentUrl"
                    >
                      <ExternalLink class="h-3 w-3 shrink-0" />
                      <span class="truncate">View</span>
                    </a>
                    <span v-else class="text-slate-400">—</span>
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
