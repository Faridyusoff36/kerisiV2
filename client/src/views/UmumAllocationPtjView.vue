<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref, watch } from "vue";
import {
  Download,
  FileDown,
  FileSpreadsheet,
  MoreVertical,
  Search,
  X,
} from "lucide-vue-next";
import AdminLayout from "@/layouts/AdminLayout.vue";
import { getUmumAllocationPtjOptions, listUmumAllocationPtj } from "@/api/cms";
import { useDatatableFeatures } from "@/composables/useDatatableFeatures";
import type { DatatableRefApi } from "@/composables/useDatatableFeatures";
import { useToast } from "@/composables/useToast";
import type { UmumAllocationPtjFooter, UmumAllocationPtjOptions, UmumAllocationPtjRow } from "@/types";

const toast = useToast();

const rows = ref<UmumAllocationPtjRow[]>([]);
const total = ref(0);
const footer = ref<UmumAllocationPtjFooter | null>(null);
const page = ref(1);
const limit = ref(10);
const q = ref("");

const options = ref<UmumAllocationPtjOptions>({
  topFilter: { year: [], ptj: [], activity: [] },
});

/** Top filter mirrors legacy "Filter By": Year, PTJ, Activity, Date From, Date To (dd/mm/yyyy). */
const topFilter = ref({
  bdgYear: "",
  ounCode: "",
  atActivityCode: "",
  dateFrom: "",
  dateTo: "",
});

async function loadOptions() {
  try {
    const res = await getUmumAllocationPtjOptions();
    options.value = res.data;
    if (!topFilter.value.bdgYear && options.value.topFilter.year.length > 0) {
      topFilter.value.bdgYear = options.value.topFilter.year[0]!.id;
    }
  } catch (e) {
    toast.error("Load failed", e instanceof Error ? e.message : "Could not load filter options.");
  }
}

async function loadRows() {
  try {
    const params = new URLSearchParams({
      page: String(page.value),
      limit: String(limit.value),
    });
    if (q.value) params.set("q", q.value);
    if (topFilter.value.bdgYear) params.set("bdg_year", topFilter.value.bdgYear);
    if (topFilter.value.ounCode) params.set("oun_code", topFilter.value.ounCode);
    if (topFilter.value.atActivityCode) params.set("at_activity_code", topFilter.value.atActivityCode);
    if (topFilter.value.dateFrom) params.set("date_from", topFilter.value.dateFrom);
    if (topFilter.value.dateTo) params.set("date_to", topFilter.value.dateTo);

    const res = await listUmumAllocationPtj(`?${params.toString()}`);
    rows.value = res.data;
    total.value = Number(res.meta?.total ?? 0);
    footer.value = (res.meta?.footer as UmumAllocationPtjFooter | undefined) ?? null;
  } catch (e) {
    toast.error("Load failed", e instanceof Error ? e.message : "Could not load rows.");
  }
}

function applyTopFilter() {
  page.value = 1;
  void loadRows();
}

function resetTopFilter() {
  topFilter.value = {
    bdgYear: options.value.topFilter.year[0]?.id ?? "",
    ounCode: "",
    atActivityCode: "",
    dateFrom: "",
    dateTo: "",
  };
  page.value = 1;
  void loadRows();
}

const currency = new Intl.NumberFormat("en-MY", {
  minimumFractionDigits: 2,
  maximumFractionDigits: 2,
});

function fmtMoney(v: unknown): string {
  if (v === null || v === undefined || v === "") return "";
  const n = typeof v === "number" ? v : Number(v);
  if (!Number.isFinite(n)) return "";
  return currency.format(n);
}

const exportColumns = [
  "Activity Code",
  "Activity Description",
  "PTJ",
  "PTJ Desc",
  "Cost Centre",
  "Cost Centre Desc",
  "Allocation",
  "Lock",
  "Request",
  "Commit",
  "Expenses",
  "Total Expenses",
  "Balance",
];

function toExportRow(r: UmumAllocationPtjRow): Record<string, string> {
  return {
    "Activity Code": r.atActivityCode ?? "",
    "Activity Description": r.atActivityDescriptionBm ?? "",
    PTJ: r.ounCode ?? "",
    "PTJ Desc": r.ounDesc ?? "",
    "Cost Centre": r.ccrCostcentre ?? "",
    "Cost Centre Desc": r.ccrCostcentreDesc ?? "",
    Allocation: fmtMoney(r.allocation),
    Lock: fmtMoney(r.lock),
    Request: fmtMoney(r.request),
    Commit: fmtMoney(r.commitment),
    Expenses: fmtMoney(r.expenses),
    "Total Expenses": fmtMoney(r.totalExpenses),
    Balance: fmtMoney(r.balance),
  };
}

const datatableRef = ref<DatatableRefApi | null>(null);
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
  pageName: "Umum Allocation by PTJ",
  apiDataPath: "/budget/report/umum-allocation-ptj",
  defaultExportColumns: exportColumns,
  getFilteredList: () =>
    rows.value.map((r, i) => {
      const er = toExportRow(r);
      return { ...er, No: String(i + 1) };
    }),
  datatableRef,
  searchKeyword: q,
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
    const ws = wb.addWorksheet("Umum Allocation PTJ");
    ws.addRow(["No", ...exportColumns]);
    rows.value.forEach((r, idx) => {
      const er = toExportRow(r);
      ws.addRow([idx + 1, ...exportColumns.map((c) => er[c] ?? "")]);
    });
    ws.addRow([]);
    if (footer.value) {
      ws.addRow([
        "",
        "TOTAL",
        "",
        "",
        "",
        "",
        fmtMoney(footer.value.allocation),
        fmtMoney(footer.value.lock),
        fmtMoney(footer.value.request),
        fmtMoney(footer.value.commitment),
        fmtMoney(footer.value.expenses),
        fmtMoney(footer.value.totalExpenses),
        fmtMoney(footer.value.balance),
      ]);
    }
    const buf = await wb.xlsx.writeBuffer();
    const blob = new Blob([buf], { type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" });
    const url = URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.href = url;
    a.download = `Umum_Allocation_PTJ_${new Date().toISOString().slice(0, 10)}.xlsx`;
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

const totalPages = computed(() => Math.max(1, Math.ceil(total.value / Math.max(1, limit.value))));

onMounted(async () => {
  document.addEventListener("click", onClickOutside);
  await loadOptions();
  await loadRows();
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
      <h1 class="page-title">
        Budget / Report / Total Allocation / Umum Allocation, Expenditure &amp; Balance of Allocation by PTJ
      </h1>

      <article class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-4 py-3">
          <h1 class="text-base font-semibold text-slate-900">Filter By</h1>
        </div>
        <div class="grid gap-3 p-4 md:grid-cols-2 lg:grid-cols-3">
          <div>
            <label class="mb-1 block text-xs font-medium text-slate-600">Year</label>
            <select v-model="topFilter.bdgYear" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
              <option value="">—</option>
              <option v-for="opt in options.topFilter.year" :key="opt.id" :value="opt.id">{{ opt.label }}</option>
            </select>
          </div>
          <div>
            <label class="mb-1 block text-xs font-medium text-slate-600">PTJ</label>
            <select v-model="topFilter.ounCode" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
              <option value="">—</option>
              <option v-for="opt in options.topFilter.ptj" :key="opt.id" :value="opt.id">{{ opt.label }}</option>
            </select>
          </div>
          <div>
            <label class="mb-1 block text-xs font-medium text-slate-600">Activity</label>
            <select v-model="topFilter.atActivityCode" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
              <option value="">—</option>
              <option v-for="opt in options.topFilter.activity" :key="opt.id" :value="opt.id">{{ opt.label }}</option>
            </select>
          </div>
          <div>
            <label class="mb-1 block text-xs font-medium text-slate-600">Date From (DD/MM/YYYY)</label>
            <input
              v-model="topFilter.dateFrom"
              type="text"
              maxlength="10"
              placeholder="DD/MM/YYYY"
              class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
              autocomplete="off"
            />
          </div>
          <div>
            <label class="mb-1 block text-xs font-medium text-slate-600">Date To (DD/MM/YYYY)</label>
            <input
              v-model="topFilter.dateTo"
              type="text"
              maxlength="10"
              placeholder="DD/MM/YYYY"
              class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
              autocomplete="off"
            />
          </div>
          <div class="flex flex-col justify-end md:col-span-2 lg:col-span-3">
            <div class="flex flex-wrap justify-end gap-2">
              <button type="button" class="rounded-lg border border-slate-300 px-4 py-2 text-sm" @click="resetTopFilter">
                Reset
              </button>
              <button type="button" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white" @click="applyTopFilter">
                Search
              </button>
            </div>
          </div>
        </div>
      </article>

      <article class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
          <h1 class="text-base font-semibold text-slate-900">Listing</h1>
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
              <select v-model.number="limit" class="rounded-lg border border-slate-300 px-2 py-1.5 text-sm" @change="page = 1; void loadRows()">
                <option v-for="n in [5, 10, 25, 50, 100]" :key="n" :value="n">{{ n }}</option>
              </select>
            </div>
            <div class="flex items-center gap-2">
              <label class="text-xs font-medium text-slate-600">Search</label>
              <div class="relative">
                <Search class="pointer-events-none absolute left-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400" />
                <input
                  v-model="q"
                  type="search"
                  placeholder="Filter rows..."
                  class="w-56 rounded-lg border border-slate-300 py-1.5 pl-8 pr-8 text-sm"
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
            </div>
          </div>
          <div class="overflow-x-auto rounded-lg border border-slate-200">
            <div :class="rows.length > 10 ? 'max-h-[480px] overflow-y-auto' : ''">
              <table class="admin-table-kitchen w-full min-w-[1200px] text-sm">
                <thead class="admin-table-thead-sticky">
                  <tr class="border-b border-slate-200 text-left">
                    <th class="px-3 py-2 text-xs font-semibold uppercase">No</th>
                    <th class="px-3 py-2 text-xs font-semibold uppercase">Activity Code</th>
                    <th class="px-3 py-2 text-xs font-semibold uppercase">Description of Activity</th>
                    <th class="px-3 py-2 text-xs font-semibold uppercase">PTJ</th>
                    <th class="px-3 py-2 text-xs font-semibold uppercase">Description of PTJ</th>
                    <th class="px-3 py-2 text-xs font-semibold uppercase">Cost Centre</th>
                    <th class="px-3 py-2 text-xs font-semibold uppercase">Description of Cost Centre</th>
                    <th class="px-3 py-2 text-right text-xs font-semibold uppercase">Allocation (RM)</th>
                    <th class="px-3 py-2 text-right text-xs font-semibold uppercase">Lock (RM)</th>
                    <th class="px-3 py-2 text-right text-xs font-semibold uppercase">Request (RM)</th>
                    <th class="px-3 py-2 text-right text-xs font-semibold uppercase">Commit (RM)</th>
                    <th class="px-3 py-2 text-right text-xs font-semibold uppercase">Expenses (RM)</th>
                    <th class="px-3 py-2 text-right text-xs font-semibold uppercase">Total Expenses (RM)</th>
                    <th class="px-3 py-2 text-right text-xs font-semibold uppercase">Allocation Balance (RM)</th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="row in rows"
                    :key="`${row.index}-${row.atActivityCode}-${row.ounCode}-${row.ccrCostcentre}`"
                    class="border-b border-slate-100 hover:bg-slate-50"
                  >
                    <td class="px-3 py-2">{{ row.index }}</td>
                    <td class="px-3 py-2">{{ row.atActivityCode ?? "—" }}</td>
                    <td class="px-3 py-2">{{ row.atActivityDescriptionBm ?? "—" }}</td>
                    <td class="px-3 py-2">{{ row.ounCode ?? "—" }}</td>
                    <td class="px-3 py-2">{{ row.ounDesc ?? "—" }}</td>
                    <td class="px-3 py-2">{{ row.ccrCostcentre ?? "—" }}</td>
                    <td class="px-3 py-2">{{ row.ccrCostcentreDesc ?? "—" }}</td>
                    <td class="px-3 py-2 text-right">{{ fmtMoney(row.allocation) }}</td>
                    <td class="px-3 py-2 text-right">{{ fmtMoney(row.lock) }}</td>
                    <td class="px-3 py-2 text-right">{{ fmtMoney(row.request) }}</td>
                    <td class="px-3 py-2 text-right">{{ fmtMoney(row.commitment) }}</td>
                    <td class="px-3 py-2 text-right">{{ fmtMoney(row.expenses) }}</td>
                    <td class="px-3 py-2 text-right">{{ fmtMoney(row.totalExpenses) }}</td>
                    <td class="px-3 py-2 text-right">{{ fmtMoney(row.balance) }}</td>
                  </tr>
                  <tr v-if="rows.length === 0">
                    <td colspan="14" class="px-3 py-6 text-center text-xs text-slate-500">No data</td>
                  </tr>
                </tbody>
                <tfoot v-if="rows.length > 0 && footer" class="bg-slate-50">
                  <tr class="border-t-2 border-slate-200 font-semibold">
                    <td class="px-3 py-2" colspan="7">Total</td>
                    <td class="px-3 py-2 text-right">{{ fmtMoney(footer.allocation) }}</td>
                    <td class="px-3 py-2 text-right">{{ fmtMoney(footer.lock) }}</td>
                    <td class="px-3 py-2 text-right">{{ fmtMoney(footer.request) }}</td>
                    <td class="px-3 py-2 text-right">{{ fmtMoney(footer.commitment) }}</td>
                    <td class="px-3 py-2 text-right">{{ fmtMoney(footer.expenses) }}</td>
                    <td class="px-3 py-2 text-right">{{ fmtMoney(footer.totalExpenses) }}</td>
                    <td class="px-3 py-2 text-right">{{ fmtMoney(footer.balance) }}</td>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>
          <div class="flex flex-wrap items-center justify-between gap-2 border-t border-slate-100 pt-3">
            <div class="text-xs text-slate-500">
              Showing {{ total === 0 ? 0 : (page - 1) * limit + 1 }}-{{ Math.min(page * limit, total) }} of {{ total }}
            </div>
            <div class="flex flex-wrap items-center gap-2">
              <button
                type="button"
                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium hover:bg-slate-50 disabled:opacity-50"
                :disabled="page <= 1"
                @click="
                  page = Math.max(1, page - 1);
                  void loadRows();
                "
              >
                Prev
              </button>
              <button
                type="button"
                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium hover:bg-slate-50 disabled:opacity-50"
                :disabled="page >= totalPages"
                @click="
                  page = Math.min(totalPages, page + 1);
                  void loadRows();
                "
              >
                Next
              </button>
              <button
                type="button"
                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium hover:bg-slate-50"
                @click="handleDownloadPDF"
              >
                <Download class="h-3.5 w-3.5" />PDF
              </button>
              <button
                type="button"
                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium hover:bg-slate-50"
                @click="handleDownloadCSV"
              >
                <FileDown class="h-3.5 w-3.5" />CSV
              </button>
              <button
                type="button"
                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium hover:bg-slate-50"
                @click="exportExcel"
              >
                <FileSpreadsheet class="h-3.5 w-3.5" />Excel
              </button>
            </div>
          </div>
        </div>
      </article>
    </div>
  </AdminLayout>
</template>
