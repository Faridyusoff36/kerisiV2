<script setup lang="ts">
/**
 * Credit Control / Advance / Emergency Fund / Report / Reminder
 * PAGEID 1686 / MENUID 2037 — NAD_API_CC_EF_REPORT_REMINDER.
 */
import { computed, onMounted, onUnmounted, ref, watch } from "vue";
import { Download, FileDown, FileSpreadsheet, MoreVertical, Search, X } from "lucide-vue-next";
import AdminLayout from "@/layouts/AdminLayout.vue";
import { listEmergencyFundReminderReport } from "@/api/cms";
import { useDatatableFeatures } from "@/composables/useDatatableFeatures";
import type { DatatableRefApi } from "@/composables/useDatatableFeatures";
import { useToast } from "@/composables/useToast";
import type { EmergencyFundReminderReportRow } from "@/types";

const toast = useToast();
const datatableRef = ref<DatatableRefApi | null>(null);

const rows = ref<EmergencyFundReminderReportRow[]>([]);
const total = ref(0);
const page = ref(1);
const limit = ref(10);
const q = ref("");

type SortKey =
  | "crm_debtor_id"
  | "crm_debtor_name"
  | "crm_invoice_no"
  | "crm_amount_inv"
  | "crm_reminder_bil"
  | "crm_reminder_date_disp"
  | "full_address";
const sortBy = ref<SortKey>("crm_debtor_id");
const sortDir = ref<"asc" | "desc">("asc");
const loading = ref(false);

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
    const res = await listEmergencyFundReminderReport(`?${params.toString()}`);
    rows.value = res.data;
    total.value = Number(res.meta?.total ?? 0);
  } catch (e) {
    toast.error("Load failed", e instanceof Error ? e.message : "Unable to load reminder report.");
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
  "Staff No",
  "Name",
  "Description",
  "Amount",
  "Reminder Type",
  "Reminder Date",
  "Address",
];

function asExportRow(r: EmergencyFundReminderReportRow) {
  return {
    No: r.listIndex,
    "Staff No": r.crmDebtorId ?? "",
    Name: r.crmDebtorName ?? "",
    Description: r.crmInvoiceNo ?? "",
    Amount: r.crmAmountInv != null ? formatAmt(r.crmAmountInv) : "",
    "Reminder Type": r.crmReminderBil ?? "",
    "Reminder Date": r.crmReminderDateDisp ?? "",
    Address: r.fullAddress ?? "",
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
  pageName: "Credit Control — Emergency Fund Reminder Report",
  apiDataPath: "/credit-control/emergency-fund-reminder-report",
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
    const ws = wb.addWorksheet("Reminder");
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
    a.download = `EF_Reminder_Report_${new Date().toISOString().slice(0, 10)}.xlsx`;
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
  <AdminLayout>
    <div class="space-y-4">
      <input
        ref="templateFileInputRef"
        type="file"
        accept=".json,application/json"
        class="hidden"
        @change="onTemplateFileChange"
      />
      <h1 class="page-title">Credit Control / Advance / Emergency Fund / Report / Reminder</h1>

      <article ref="datatableRef" class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
          <h1 class="text-base font-semibold text-slate-900">Reminder</h1>
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
              <table class="admin-table-kitchen w-full min-w-[900px] text-sm">
                <thead class="admin-table-thead-sticky">
                  <tr class="border-b border-slate-200 text-left">
                    <th class="px-3 py-2 text-xs font-semibold uppercase shadow-sm">No</th>
                    <th
                      class="cursor-pointer px-3 py-2 text-xs font-semibold uppercase shadow-sm"
                      @click="toggleSort('crm_debtor_id')"
                    >
                      Staff No
                    </th>
                    <th
                      class="cursor-pointer px-3 py-2 text-xs font-semibold uppercase shadow-sm"
                      @click="toggleSort('crm_debtor_name')"
                    >
                      Name
                    </th>
                    <th
                      class="cursor-pointer px-3 py-2 text-xs font-semibold uppercase shadow-sm"
                      @click="toggleSort('crm_invoice_no')"
                    >
                      Description
                    </th>
                    <th
                      class="cursor-pointer px-3 py-2 text-right text-xs font-semibold uppercase shadow-sm"
                      @click="toggleSort('crm_amount_inv')"
                    >
                      Amount
                    </th>
                    <th
                      class="cursor-pointer px-3 py-2 text-xs font-semibold uppercase shadow-sm"
                      @click="toggleSort('crm_reminder_bil')"
                    >
                      Reminder Type
                    </th>
                    <th
                      class="cursor-pointer px-3 py-2 text-xs font-semibold uppercase shadow-sm"
                      @click="toggleSort('crm_reminder_date_disp')"
                    >
                      Reminder Date
                    </th>
                    <th
                      class="cursor-pointer px-3 py-2 text-xs font-semibold uppercase shadow-sm"
                      @click="toggleSort('full_address')"
                    >
                      Address
                    </th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-if="loading">
                    <td colspan="8" class="px-3 py-6 text-center text-sm text-slate-500">Loading…</td>
                  </tr>
                  <tr v-else-if="rows.length === 0">
                    <td colspan="8" class="px-3 py-6 text-center text-sm text-slate-500">No records.</td>
                  </tr>
                  <tr
                    v-for="row in rows"
                    :key="`${row.crmDebtorId}-${row.crmInvoiceNo ?? ''}-${row.listIndex}`"
                    class="border-b border-slate-100 hover:bg-slate-50"
                  >
                    <td class="px-3 py-2">{{ row.listIndex }}</td>
                    <td class="px-3 py-2 font-medium">{{ row.crmDebtorId ?? "-" }}</td>
                    <td class="px-3 py-2">{{ row.crmDebtorName ?? "-" }}</td>
                    <td class="px-3 py-2">{{ row.crmInvoiceNo ?? "-" }}</td>
                    <td class="px-3 py-2 text-right tabular-nums">{{ formatAmt(row.crmAmountInv) }}</td>
                    <td class="px-3 py-2">{{ row.crmReminderBil ?? "-" }}</td>
                    <td class="px-3 py-2 whitespace-nowrap">{{ row.crmReminderDateDisp ?? "-" }}</td>
                    <td class="max-w-[280px] px-3 py-2 text-slate-700">{{ row.fullAddress ?? "-" }}</td>
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
  </AdminLayout>
</template>
