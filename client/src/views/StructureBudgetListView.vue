<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref, watch } from "vue";
import { Download, FileDown, FileSpreadsheet, MoreVertical, Pencil, Search, Trash2, X } from "lucide-vue-next";
import AdminLayout from "@/layouts/AdminLayout.vue";
import { getStructureBudgetListOptions, listStructureBudgetList } from "@/api/cms";
import { useDatatableFeatures } from "@/composables/useDatatableFeatures";
import type { DatatableRefApi } from "@/composables/useDatatableFeatures";
import { useToast } from "@/composables/useToast";
import type { StructureBudgetListOptions, StructureBudgetListRow } from "@/types";

const toast = useToast();

const rows = ref<StructureBudgetListRow[]>([]);
const total = ref(0);
const page = ref(1);
const limit = ref(10);
const q = ref("");

const topFilter = ref<{ year: string; fund: string; activity: string; oun: string; ccr: string }>({
  year: "",
  fund: "",
  activity: "",
  oun: "",
  ccr: "",
});

const showSmartFilter = ref(false);
const smartFilter = ref<{
  year: string;
  fund: string;
  activity: string;
  oun: string;
  ccr: string;
  budgetCode: string;
  status: string;
  deficit: string;
}>({ year: "", fund: "", activity: "", oun: "", ccr: "", budgetCode: "", status: "", deficit: "" });

const options = ref<StructureBudgetListOptions>({
  topFilter: { year: [], fund: [], activity: [], oun: [], ccr: [] },
  smartFilter: { year: [], fund: [], activity: [], oun: [], ccr: [], budgetCode: [], status: [], deficit: [] },
});

async function loadOptions() {
  const res = await getStructureBudgetListOptions();
  options.value = res.data;
}

async function loadRows() {
  const params = new URLSearchParams({
    page: String(page.value),
    limit: String(limit.value),
    ...(q.value ? { q: q.value } : {}),
    ...(topFilter.value.year ? { tf_year: topFilter.value.year } : {}),
    ...(topFilter.value.fund ? { tf_fund: topFilter.value.fund } : {}),
    ...(topFilter.value.activity ? { tf_activity: topFilter.value.activity } : {}),
    ...(topFilter.value.oun ? { tf_oun: topFilter.value.oun } : {}),
    ...(topFilter.value.ccr ? { tf_ccr: topFilter.value.ccr } : {}),
    ...(smartFilter.value.year ? { sm_year: smartFilter.value.year } : {}),
    ...(smartFilter.value.fund ? { sm_fund: smartFilter.value.fund } : {}),
    ...(smartFilter.value.activity ? { sm_activity: smartFilter.value.activity } : {}),
    ...(smartFilter.value.oun ? { sm_oun: smartFilter.value.oun } : {}),
    ...(smartFilter.value.ccr ? { sm_ccr: smartFilter.value.ccr } : {}),
    ...(smartFilter.value.budgetCode ? { sm_budget_code: smartFilter.value.budgetCode } : {}),
    ...(smartFilter.value.status ? { sm_status: smartFilter.value.status } : {}),
    ...(smartFilter.value.deficit ? { sm_deficit: smartFilter.value.deficit } : {}),
  });
  const res = await listStructureBudgetList(`?${params.toString()}`);
  rows.value = res.data;
  total.value = Number(res.meta?.total ?? 0);
}

function applyTopFilter() {
  page.value = 1;
  void loadRows();
}

function resetTopFilter() {
  topFilter.value = { year: "", fund: "", activity: "", oun: "", ccr: "" };
  page.value = 1;
  void loadRows();
}

function applySmartFilter() {
  showSmartFilter.value = false;
  page.value = 1;
  void loadRows();
}

function resetSmartFilter() {
  smartFilter.value = { year: "", fund: "", activity: "", oun: "", ccr: "", budgetCode: "", status: "", deficit: "" };
}

const exportColumns = [
  "Fund",
  "PTJ",
  "Cost Centre",
  "Activity",
  "Activity Description",
  "Budget Code",
  "Budget Code Description",
  "Deficit Budget",
  "Status",
];

function toExportRow(r: StructureBudgetListRow): Record<string, string | number> {
  return {
    Fund: r.sbFund ?? "",
    PTJ: r.sbOun ?? "",
    "Cost Centre": r.sbCcr ?? "",
    Activity: r.sbActivity ?? "",
    "Activity Description": r.sbActivityDesc ?? "",
    "Budget Code": r.sbBudgetCode ?? "",
    "Budget Code Description": r.sbBudgetCodeDesc ?? "",
    "Deficit Budget": r.sbDeficitBudget ?? "",
    Status: r.sbStatus ?? "",
  };
}

const datatableRef = ref<DatatableRefApi | null>(null);
const { templateFileInputRef, onTemplateFileChange, handleDownloadPDF, handleDownloadCSV } = useDatatableFeatures({
  pageName: "Budget Structure List",
  apiDataPath: "/budget/structure-list",
  defaultExportColumns: exportColumns,
  getFilteredList: () => rows.value.map(toExportRow),
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
    const ws = wb.addWorksheet("Budget Structure List");
    ws.addRow(["No", ...exportColumns]);
    rows.value.forEach((r, idx) => {
      const row = toExportRow(r);
      ws.addRow([idx + 1, ...exportColumns.map((c) => row[c] ?? "")]);
    });
    const buf = await wb.xlsx.writeBuffer();
    const blob = new Blob([buf], { type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" });
    const url = URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.href = url;
    a.download = `Budget_Structure_List_${new Date().toISOString().slice(0, 10)}.xlsx`;
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
  await loadOptions();
  await loadRows();
});
onUnmounted(() => {
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
      <h1 class="page-title">Budget / Setup / Budget Structure List</h1>

      <article class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-4 py-3">
          <h2 class="text-base font-semibold text-slate-900">Top Filter</h2>
        </div>
        <div class="grid gap-4 p-4 sm:grid-cols-2 lg:grid-cols-3">
          <div>
            <label class="mb-1 block text-xs font-medium text-slate-600">Year</label>
            <select v-model="topFilter.year" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
              <option value="">Any</option>
              <option v-for="opt in options.topFilter.year" :key="opt.id" :value="opt.id">{{ opt.label }}</option>
            </select>
          </div>
          <div class="relative">
            <label class="mb-1 block text-xs font-medium text-slate-600">Fund</label>
            <select v-model="topFilter.fund" class="w-full rounded-lg border border-slate-300 px-3 py-2 pr-9 text-sm">
              <option value="">Any</option>
              <option v-for="opt in options.topFilter.fund" :key="opt.id" :value="opt.id">{{ opt.label }}</option>
            </select>
            <button
              v-if="topFilter.fund"
              type="button"
              class="absolute right-2 top-[1.85rem] rounded p-0.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600"
              aria-label="Clear fund"
              @click="topFilter.fund = ''"
            >
              <X class="h-3.5 w-3.5" />
            </button>
          </div>
          <div>
            <label class="mb-1 block text-xs font-medium text-slate-600">PTJ</label>
            <select v-model="topFilter.oun" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
              <option value="">Any</option>
              <option v-for="opt in options.topFilter.oun" :key="opt.id" :value="opt.id">{{ opt.label }}</option>
            </select>
          </div>
          <div>
            <label class="mb-1 block text-xs font-medium text-slate-600">Cost Centre</label>
            <select v-model="topFilter.ccr" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
              <option value="">Any</option>
              <option v-for="opt in options.topFilter.ccr" :key="opt.id" :value="opt.id">{{ opt.label }}</option>
            </select>
          </div>
          <div>
            <label class="mb-1 block text-xs font-medium text-slate-600">Activity</label>
            <select v-model="topFilter.activity" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
              <option value="">Any</option>
              <option v-for="opt in options.topFilter.activity" :key="opt.id" :value="opt.id">{{ opt.label }}</option>
            </select>
          </div>
          <div class="flex items-end justify-end gap-2 lg:col-span-1">
            <button
              type="button"
              class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-violet-600 px-4 py-2 text-sm font-medium text-white hover:bg-violet-700 sm:w-auto"
              @click="applyTopFilter"
            >
              <Search class="h-4 w-4 shrink-0" />
              Search
            </button>
          </div>
        </div>
        <div class="border-t border-slate-100 px-4 pb-3">
          <button type="button" class="text-xs font-medium text-slate-500 hover:text-slate-800" @click="resetTopFilter">Clear all filters</button>
        </div>
      </article>

      <article class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
          <h2 class="text-base font-semibold text-slate-900">Budget Structure List</h2>
          <button class="rounded-lg p-2 text-slate-500 hover:bg-slate-100" aria-label="More">
            <MoreVertical class="h-4 w-4" />
          </button>
        </div>
        <div class="space-y-4 p-4">
          <div class="flex flex-wrap items-end justify-between gap-4">
            <div class="flex items-center gap-2">
              <label class="text-xs font-medium text-slate-600">Display</label>
              <select
                v-model.number="limit"
                class="rounded-lg border border-slate-300 px-2 py-1.5 text-sm"
                @change="page = 1; void loadRows()"
              >
                <option v-for="n in [5, 10, 25, 50, 100]" :key="n" :value="n">{{ n }}</option>
              </select>
            </div>
            <div class="flex items-center gap-2">
              <button type="button" class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium hover:bg-slate-50" @click="showSmartFilter = true">Filter</button>
              <label class="text-xs font-medium text-slate-600">Search</label>
              <div class="relative">
                <Search class="pointer-events-none absolute left-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400" />
                <input
                  v-model="q"
                  type="search"
                  placeholder="Filter rows..."
                  class="w-56 rounded-lg border border-slate-300 py-1.5 pl-8 pr-8 text-sm"
                  @keyup.enter="page = 1; void loadRows()"
                />
                <button v-if="q" type="button" class="absolute right-1 top-1/2 -translate-y-1/2 rounded p-0.5 text-slate-400 hover:bg-slate-100" aria-label="Clear search" @click="q = ''">
                  <X class="h-3.5 w-3.5" />
                </button>
              </div>
            </div>
          </div>
          <div class="overflow-x-auto rounded-lg border border-slate-200">
            <div :class="rows.length > 10 ? 'max-h-[480px] overflow-y-auto' : ''">
              <table class="w-full min-w-[1100px] text-sm">
                <thead class="sticky top-0 z-[1] bg-violet-600 text-white">
                  <tr class="border-b border-violet-500 text-left">
                    <th class="px-3 py-2.5 text-xs font-semibold uppercase tracking-wide">No</th>
                    <th class="px-3 py-2.5 text-xs font-semibold uppercase tracking-wide">Fund</th>
                    <th class="px-3 py-2.5 text-xs font-semibold uppercase tracking-wide">PTJ</th>
                    <th class="px-3 py-2.5 text-xs font-semibold uppercase tracking-wide">Cost Centre</th>
                    <th class="px-3 py-2.5 text-xs font-semibold uppercase tracking-wide">Activity</th>
                    <th class="px-3 py-2.5 text-xs font-semibold uppercase tracking-wide">Activity Description</th>
                    <th class="px-3 py-2.5 text-xs font-semibold uppercase tracking-wide">Budget Code</th>
                    <th class="px-3 py-2.5 text-xs font-semibold uppercase tracking-wide">Budget Code Description</th>
                    <th class="px-3 py-2.5 text-xs font-semibold uppercase tracking-wide">Deficit Budget</th>
                    <th class="px-3 py-2.5 text-xs font-semibold uppercase tracking-wide">Status</th>
                    <th class="px-3 py-2.5 text-xs font-semibold uppercase tracking-wide">Action</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="row in rows" :key="row.sbBudgetId" class="border-b border-slate-100 hover:bg-slate-50">
                    <td class="px-3 py-2">{{ row.index }}</td>
                    <td class="px-3 py-2">{{ row.sbFund ?? "—" }}</td>
                    <td class="px-3 py-2">{{ row.sbOun ?? "—" }}</td>
                    <td class="px-3 py-2">{{ row.sbCcr ?? "—" }}</td>
                    <td class="px-3 py-2">{{ row.sbActivity ?? "—" }}</td>
                    <td class="max-w-[14rem] whitespace-normal break-words px-3 py-2">{{ row.sbActivityDesc ?? "—" }}</td>
                    <td class="px-3 py-2">{{ row.sbBudgetCode ?? "—" }}</td>
                    <td class="max-w-[16rem] whitespace-normal break-words px-3 py-2">{{ row.sbBudgetCodeDesc ?? "—" }}</td>
                    <td class="px-3 py-2">{{ row.sbDeficitBudget ?? "—" }}</td>
                    <td class="px-3 py-2">{{ row.sbStatus ?? "—" }}</td>
                    <td class="px-3 py-2">
                      <div class="flex items-center gap-1">
                        <button
                          type="button"
                          disabled
                          class="cursor-not-allowed rounded p-1 text-slate-300"
                          title="Edit is not available in this migration (read-only list)"
                          aria-label="Edit (unavailable)"
                        >
                          <Pencil class="h-4 w-4" />
                        </button>
                        <button
                          type="button"
                          disabled
                          class="cursor-not-allowed rounded p-1 text-slate-300"
                          title="Delete is not available in this migration (read-only list)"
                          aria-label="Delete (unavailable)"
                        >
                          <Trash2 class="h-4 w-4" />
                        </button>
                      </div>
                    </td>
                  </tr>
                  <tr v-if="rows.length === 0">
                    <td colspan="11" class="px-3 py-6 text-center text-xs text-slate-500">No data</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          <div class="flex flex-wrap items-center justify-between gap-2 border-t border-slate-100 pt-3">
            <div class="text-xs text-slate-500">Page {{ page }} of {{ totalPages }} · {{ total }} record{{ total === 1 ? '' : 's' }}</div>
            <div class="flex items-center gap-2">
              <button
                type="button"
                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium hover:bg-slate-50 disabled:opacity-50"
                :disabled="page <= 1"
                @click="page = Math.max(1, page - 1); void loadRows()"
              >
                Prev
              </button>
              <button
                type="button"
                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium hover:bg-slate-50 disabled:opacity-50"
                :disabled="page >= totalPages"
                @click="page = Math.min(totalPages, page + 1); void loadRows()"
              >
                Next
              </button>
              <button type="button" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium hover:bg-slate-50" @click="handleDownloadPDF">
                <Download class="h-3.5 w-3.5" />PDF
              </button>
              <button type="button" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium hover:bg-slate-50" @click="handleDownloadCSV">
                <FileDown class="h-3.5 w-3.5" />CSV
              </button>
              <button type="button" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium hover:bg-slate-50" @click="exportExcel">
                <FileSpreadsheet class="h-3.5 w-3.5" />Excel
              </button>
            </div>
          </div>
        </div>
      </article>
    </div>

    <Teleport to="body">
      <div
        v-if="showSmartFilter"
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm"
        @click.self="showSmartFilter = false"
      >
        <div class="w-full max-w-2xl rounded-lg border border-slate-200 bg-white shadow-2xl">
          <div class="border-b border-slate-100 px-4 py-3">
            <h3 class="text-base font-semibold text-slate-900">Filter</h3>
          </div>
          <div class="grid gap-3 p-4 md:grid-cols-2">
            <div>
              <label class="mb-1 block text-xs font-medium text-slate-600">Year</label>
              <select v-model="smartFilter.year" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">Any</option>
                <option v-for="opt in options.smartFilter.year" :key="opt.id" :value="opt.id">{{ opt.label }}</option>
              </select>
            </div>
            <div>
              <label class="mb-1 block text-xs font-medium text-slate-600">Fund</label>
              <select v-model="smartFilter.fund" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">Any</option>
                <option v-for="opt in options.smartFilter.fund" :key="opt.id" :value="opt.id">{{ opt.label }}</option>
              </select>
            </div>
            <div>
              <label class="mb-1 block text-xs font-medium text-slate-600">PTJ</label>
              <select v-model="smartFilter.oun" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">Any</option>
                <option v-for="opt in options.smartFilter.oun" :key="opt.id" :value="opt.id">{{ opt.label }}</option>
              </select>
            </div>
            <div>
              <label class="mb-1 block text-xs font-medium text-slate-600">Cost Centre</label>
              <select v-model="smartFilter.ccr" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">Any</option>
                <option v-for="opt in options.smartFilter.ccr" :key="opt.id" :value="opt.id">{{ opt.label }}</option>
              </select>
            </div>
            <div>
              <label class="mb-1 block text-xs font-medium text-slate-600">Activity</label>
              <select v-model="smartFilter.activity" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">Any</option>
                <option v-for="opt in options.smartFilter.activity" :key="opt.id" :value="opt.id">{{ opt.label }}</option>
              </select>
            </div>
            <div>
              <label class="mb-1 block text-xs font-medium text-slate-600">Budget Code</label>
              <select v-model="smartFilter.budgetCode" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">Any</option>
                <option v-for="opt in options.smartFilter.budgetCode" :key="opt.id" :value="opt.id">{{ opt.label }}</option>
              </select>
            </div>
            <div>
              <label class="mb-1 block text-xs font-medium text-slate-600">Status</label>
              <select v-model="smartFilter.status" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">Any</option>
                <option v-for="opt in options.smartFilter.status" :key="opt.id" :value="opt.id">{{ opt.label }}</option>
              </select>
            </div>
            <div>
              <label class="mb-1 block text-xs font-medium text-slate-600">Deficit Budget</label>
              <select v-model="smartFilter.deficit" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">Any</option>
                <option v-for="opt in options.smartFilter.deficit" :key="opt.id" :value="opt.id">{{ opt.label }}</option>
              </select>
            </div>
          </div>
          <div class="flex justify-end gap-2 border-t border-slate-100 px-4 py-3">
            <button type="button" class="rounded-lg border border-slate-300 px-4 py-2 text-sm" @click="resetSmartFilter">Reset</button>
            <button type="button" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white" @click="applySmartFilter">OK</button>
          </div>
        </div>
      </div>
    </Teleport>
  </AdminLayout>
</template>
