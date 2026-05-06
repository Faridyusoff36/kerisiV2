<script setup lang="ts">
/** PAGEID 1676 / 2182 — Release queue listing (NAD_API_CC_EF_RELEASE dt_emergencyFundRelease). */
import { computed, onMounted, onUnmounted, ref, watch } from "vue";
import { Download, FileDown, FileSpreadsheet, MoreVertical, Search, X } from "lucide-vue-next";
import { listEmergencyFundReleaseQueueListing } from "@/api/cms";
import { useDatatableFeatures } from "@/composables/useDatatableFeatures";
import type { DatatableRefApi } from "@/composables/useDatatableFeatures";
import { useToast } from "@/composables/useToast";
import type { EmergencyFundReleaseQueueRow } from "@/types";

const toast = useToast();
const datatableRef = ref<DatatableRefApi | null>(null);

const rows = ref<EmergencyFundReleaseQueueRow[]>([]);
const total = ref(0);
const page = ref(1);
const limit = ref(10);
const q = ref("");

type SortKey =
  | "emf_emergency_fund_no"
  | "emf_paid_date"
  | "emf_taken_amt"
  | "emf_status"
  | "emf_clearance_date"
  | "pmt_posting_no";
const sortBy = ref<SortKey>("emf_emergency_fund_no");
const sortDir = ref<"asc" | "desc">("desc");
const loading = ref(false);

const props = withDefaults(
  defineProps<{
    /** Optional card subtitle when embedded (e.g. tab). */
    titleOverride?: string;
  }>(),
  { titleOverride: undefined },
);

const titleCard = computed(() => props.titleOverride ?? "Release queue");

const totalPages = computed(() => (total.value ? Math.max(1, Math.ceil(total.value / limit.value)) : 1));
const startIdx = computed(() => (total.value === 0 ? 0 : (page.value - 1) * limit.value + 1));
const endIdx = computed(() => Math.min(page.value * limit.value, total.value));

function formatAmt(n: number | string | null | undefined): string {
  if (n == null || n === "") return "-";
  const num = typeof n === "string" ? Number(n) : n;
  if (Number.isNaN(num)) return String(n);
  return num.toLocaleString("en-MY", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

async function loadRows() {
  loading.value = true;
  const params = new URLSearchParams({
    page: String(page.value),
    limit: String(limit.value),
    sort_by: sortBy.value,
    sort_dir: sortDir.value,
    ...(q.value ? { q: q.value } : {}),
  });
  try {
    const res = await listEmergencyFundReleaseQueueListing(`?${params.toString()}`);
    rows.value = res.data;
    total.value = Number(res.meta?.total ?? 0);
  } catch (e) {
    toast.error("Load failed", e instanceof Error ? e.message : "Unable to load release queue.");
  } finally {
    loading.value = false;
  }
}

function toggleSort(col: SortKey) {
  if (sortBy.value === col) sortDir.value = sortDir.value === "asc" ? "desc" : "asc";
  else {
    sortBy.value = col;
    sortDir.value = "asc";
  }
  void loadRows();
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
  "emf_id",
  "Emergency Fund No",
  "Paid Date",
  "Apply By",
  "Category",
  "Remark Query",
  "Amount (RM)",
  "Status",
  "Clearance Date",
  "Posting Accrual",
];

function asExportRow(r: EmergencyFundReleaseQueueRow) {
  return {
    No: r.listIndex,
    emf_id: r.emfId ?? "",
    "Emergency Fund No": r.emfEmergencyFundNo ?? "",
    "Paid Date": r.paidDateDisp ?? "",
    "Apply By": r.applyByDisp ?? "",
    Category: r.categoryDisp ?? "",
    "Remark Query": r.emfRemarkByQuery ?? "",
    "Amount (RM)": r.emfTakenAmt != null ? formatAmt(r.emfTakenAmt) : "",
    Status: r.emfStatus ?? "",
    "Clearance Date": r.clearanceDateDisp ?? "",
    "Posting Accrual": r.pmtPostingNo ?? "",
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
  handleGroupList, templateFileInputRef, onTemplateFileChange, handleDownloadPDF, handleDownloadCSV } = useDatatableFeatures({
  pageName: "Credit Control — Emergency Fund Release Queue",
  apiDataPath: "/credit-control/emergency-fund-release-queue-listing",
  defaultExportColumns: exportColumns,
  getFilteredList: () => rows.value.map(asExportRow),
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
    const ws = wb.addWorksheet("Release");
    ws.addRow(exportColumns);
    rows.value.forEach((r) => {
      const e = asExportRow(r);
      ws.addRow(exportColumns.map((h) => e[h as keyof typeof e]));
    });
    const buf = await wb.xlsx.writeBuffer();
    const blob = new Blob([buf], { type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" });
    const url = URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.href = url;
    a.download = `EF_Release_Queue_${new Date().toISOString().slice(0, 10)}.xlsx`;
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
watch(limit, () => {
  page.value = 1;
  void loadRows();
});

onMounted(() => {
  document.addEventListener("click", onClickOutside);
  void loadRows();
});
onUnmounted(() => {
  document.removeEventListener("click", onClickOutside);
  if (searchDebounce) clearTimeout(searchDebounce);
});
</script>

<template>
  <div class="space-y-4">
    <input
      ref="templateFileInputRef"
      type="file"
      accept=".json,application/json"
      class="hidden"
      @change="onTemplateFileChange"
    />
    <article ref="datatableRef" class="rounded-lg border border-slate-200 bg-white shadow-sm">
      <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
        <h2 class="text-base font-semibold text-slate-900">{{ titleCard }}</h2>
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
      </div>
      <div class="space-y-4 p-4">
        <div class="flex flex-wrap items-end justify-between gap-4">
          <div class="flex items-center gap-2">
            <label class="text-xs font-medium text-slate-600">Display</label>
            <select v-model.number="limit" class="rounded-lg border border-slate-200 px-2 py-1.5 text-sm">
              <option :value="5">5</option>
              <option :value="10">10</option>
              <option :value="25">25</option>
              <option :value="50">50</option>
            </select>
          </div>
          <div class="relative min-w-[200px] flex-1 sm:max-w-xs">
            <Search class="pointer-events-none absolute left-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
            <input
              v-model="q"
              type="search"
              placeholder="Filter rows…"
              class="w-full rounded-lg border border-slate-200 py-1.5 pl-8 pr-8 text-sm"
              autocomplete="off"
            />
            <button
              v-if="q"
              type="button"
              class="absolute right-1 top-1/2 -translate-y-1/2 rounded p-1 text-slate-400 hover:bg-slate-100"
              aria-label="Clear search"
              @click="q = ''"
            >
              <X class="h-3.5 w-3.5" />
            </button>
          </div>
        </div>
        <div class="overflow-x-auto rounded-lg border border-slate-200">
          <div :class="rows.length > 10 ? 'max-h-[420px] overflow-y-auto' : ''">
            <table class="admin-table-kitchen w-full min-w-[1200px] text-sm">
              <thead class="admin-table-thead-sticky">
                <tr class="border-b border-slate-200 text-left">
                  <th class="px-3 py-2 text-xs font-semibold uppercase shadow-sm">No</th>
                  <th class="px-3 py-2 text-xs font-semibold uppercase shadow-sm">emf_id</th>
                  <th
                    class="cursor-pointer px-3 py-2 text-xs font-semibold uppercase shadow-sm"
                    @click="toggleSort('emf_emergency_fund_no')"
                  >
                    Emergency Fund No
                  </th>
                  <th
                    class="cursor-pointer px-3 py-2 text-xs font-semibold uppercase shadow-sm"
                    @click="toggleSort('emf_paid_date')"
                  >
                    Paid Date
                  </th>
                  <th class="px-3 py-2 text-xs font-semibold uppercase shadow-sm">Apply By</th>
                  <th class="px-3 py-2 text-xs font-semibold uppercase shadow-sm">Category</th>
                  <th class="px-3 py-2 text-xs font-semibold uppercase shadow-sm">Remark Query</th>
                  <th
                    class="cursor-pointer px-3 py-2 text-right text-xs font-semibold uppercase shadow-sm"
                    @click="toggleSort('emf_taken_amt')"
                  >
                    Amount (RM)
                  </th>
                  <th
                    class="cursor-pointer px-3 py-2 text-xs font-semibold uppercase shadow-sm"
                    @click="toggleSort('emf_status')"
                  >
                    Status
                  </th>
                  <th
                    class="cursor-pointer px-3 py-2 text-xs font-semibold uppercase shadow-sm"
                    @click="toggleSort('emf_clearance_date')"
                  >
                    Clearance Date
                  </th>
                  <th
                    class="cursor-pointer px-3 py-2 text-xs font-semibold uppercase shadow-sm"
                    @click="toggleSort('pmt_posting_no')"
                  >
                    Posting Accrual
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="loading">
                  <td colspan="11" class="px-3 py-6 text-center text-sm text-slate-500">Loading…</td>
                </tr>
                <tr v-else-if="rows.length === 0">
                  <td colspan="11" class="px-3 py-6 text-center text-sm text-slate-500">No records.</td>
                </tr>
                <tr
                  v-for="row in rows"
                  :key="`${row.emfId}-${row.listIndex}`"
                  class="border-b border-slate-100 hover:bg-slate-50"
                >
                  <td class="px-3 py-2">{{ row.listIndex }}</td>
                  <td class="px-3 py-2 font-medium">{{ row.emfId ?? "-" }}</td>
                  <td class="px-3 py-2">{{ row.emfEmergencyFundNo ?? "-" }}</td>
                  <td class="px-3 py-2 whitespace-nowrap">{{ row.paidDateDisp ?? "-" }}</td>
                  <td class="px-3 py-2">{{ row.applyByDisp ?? "-" }}</td>
                  <td class="px-3 py-2">{{ row.categoryDisp ?? "-" }}</td>
                  <td class="max-w-[220px] px-3 py-2">{{ row.emfRemarkByQuery ?? "-" }}</td>
                  <td class="px-3 py-2 text-right tabular-nums">{{ formatAmt(row.emfTakenAmt) }}</td>
                  <td class="px-3 py-2">{{ row.emfStatus ?? "-" }}</td>
                  <td class="px-3 py-2 whitespace-nowrap">{{ row.clearanceDateDisp ?? "-" }}</td>
                  <td class="px-3 py-2">{{ row.pmtPostingNo ?? "-" }}</td>
                </tr>
              </tbody>
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
              class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium"
              @click="handleDownloadPDF"
            >
              <Download class="h-3.5 w-3.5" />
              PDF
            </button>
            <button
              type="button"
              class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium"
              @click="handleDownloadCSV"
            >
              <FileDown class="h-3.5 w-3.5" />
              CSV
            </button>
            <button
              type="button"
              class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium"
              @click="exportExcel"
            >
              <FileSpreadsheet class="h-3.5 w-3.5" />
              Excel
            </button>
          </div>
        </div>
      </div>
    </article>
  </div>
</template>
