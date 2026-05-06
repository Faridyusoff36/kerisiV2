<script setup lang="ts">
/**
 * Budget / New Initial V2 (PAGEID 1277 / MENUID 1560).
 * Legacy: header form + `SWS_DT_BUDGET_INITIAL_NEW_V2` detail grid (`?id=bam_id`).
 * Read-only: open from Budget Initial list (View) or `?bamId=` / `?id=`.
 */
import { computed, onMounted, onUnmounted, ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import {
  Download,
  FileDown,
  FileSpreadsheet,
  MoreVertical,
  Pencil,
  Search,
  Trash2,
  X,
} from "lucide-vue-next";
import AdminLayout from "@/layouts/AdminLayout.vue";
import { getBudgetInitialNewV2Master, listBudgetInitialNewV2Details } from "@/api/cms";
import { useDatatableFeatures } from "@/composables/useDatatableFeatures";
import type { DatatableRefApi } from "@/composables/useDatatableFeatures";
import { useToast } from "@/composables/useToast";
import type { BudgetInitialNewV2DetailRow, BudgetInitialNewV2Master } from "@/types";

const props = withDefaults(
  defineProps<{
    /** Hidden menu MENUID 1337 (PAGEID 1075) uses legacy breadcrumb "Budget / Initial / Initial". */
    listHeading?: string;
  }>(),
  { listHeading: "Budget / New Initial V2" },
);
const route = useRoute();
const router = useRouter();
const toast = useToast();

const bamId = computed(() => {
  const raw = route.query.bamId ?? route.query.id;
  const s = Array.isArray(raw) ? raw[0] : raw;
  const n = Number(s);
  return Number.isFinite(n) && n > 0 ? n : null;
});

const master = ref<BudgetInitialNewV2Master | null>(null);
const masterLoading = ref(false);
const rows = ref<BudgetInitialNewV2DetailRow[]>([]);
const page = ref(1);
const limit = ref(10);
const q = ref("");
const total = ref(0);
const grandTotal = ref(0);
const pageTotal = ref(0);
const detailLoading = ref(false);

type SortKey = "budgetId" | "fund" | "activity" | "ptj" | "ccr" | "budgetCode" | "amount";
const sortBy = ref<SortKey>("budgetId");
const sortDir = ref<"asc" | "desc">("asc");

const totalPages = computed(() => (total.value ? Math.max(1, Math.ceil(total.value / limit.value)) : 1));
const startIdx = computed(() => (total.value === 0 ? 0 : (page.value - 1) * limit.value + 1));
const endIdx = computed(() => Math.min(page.value * limit.value, total.value));

const currency = new Intl.NumberFormat("en-MY", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
function formatAmt(v: number | null | undefined): string {
  if (v == null || Number.isNaN(v)) return "-";
  return currency.format(v);
}

async function loadMaster() {
  if (bamId.value == null) {
    master.value = null;
    return;
  }
  masterLoading.value = true;
  try {
    const res = await getBudgetInitialNewV2Master(bamId.value);
    master.value = res.data;
  } catch (e) {
    master.value = null;
    toast.error("Load failed", e instanceof Error ? e.message : "Could not load allocation header.");
  } finally {
    masterLoading.value = false;
  }
}

async function loadDetails() {
  if (bamId.value == null) {
    rows.value = [];
    total.value = 0;
    grandTotal.value = 0;
    pageTotal.value = 0;
    return;
  }
  detailLoading.value = true;
  try {
    const qp = new URLSearchParams({
      bam_id: String(bamId.value),
      page: String(page.value),
      limit: String(limit.value),
      sort_by: sortBy.value,
      sort_dir: sortDir.value,
      ...(q.value ? { q: q.value } : {}),
    });
    const res = await listBudgetInitialNewV2Details(`?${qp.toString()}`);
    rows.value = res.data;
    total.value = Number(res.meta?.total ?? 0);
    grandTotal.value = Number(res.meta?.grandTotal ?? 0);
    pageTotal.value = Number(res.meta?.pageTotal ?? 0);
  } catch (e) {
    rows.value = [];
    toast.error("Load failed", e instanceof Error ? e.message : "Could not load detail lines.");
  } finally {
    detailLoading.value = false;
  }
}

function toggleSort(col: SortKey) {
  if (sortBy.value === col) sortDir.value = sortDir.value === "asc" ? "desc" : "asc";
  else {
    sortBy.value = col;
    sortDir.value = "asc";
  }
  void loadDetails();
}

function prevPage() {
  if (page.value > 1) {
    page.value -= 1;
    void loadDetails();
  }
}
function nextPage() {
  if (page.value < totalPages.value) {
    page.value += 1;
    void loadDetails();
  }
}

function gotoList() {
  void router.push({ path: "/admin/kerisi/m/1541" });
}

function notMigrated(kind: string) {
  toast.info("Not migrated yet", `The New Initial V2 ${kind} is not part of this batch.`);
}

const exportColumns = [
  "Budget index no",
  "Fund",
  "Activity",
  "PTJ",
  "Cost centre",
  "Budget code",
  "Amount",
  "Status",
];

function toExportRow(r: BudgetInitialNewV2DetailRow) {
  return {
    "Budget index no": r.budgetId ?? "",
    Fund: r.fund ?? "",
    Activity: r.activity ?? "",
    PTJ: r.ptj ?? "",
    "Cost centre": r.ccr ?? "",
    "Budget code": r.budgetCode ?? "",
    Amount: r.initialAmt != null ? formatAmt(r.initialAmt) : "",
    Status: r.stat ?? "",
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
  pageName: "Budget - New Initial V2 (lines)",
  apiDataPath: "/budget/initial-new-v2/details",
  defaultExportColumns: exportColumns,
  getFilteredList: () => rows.value.map(toExportRow),
  datatableRef,
  searchKeyword: q,
  smartFilter: ref({}),
  applyFilters: () => void loadDetails(),
});

async function exportExcel() {
  try {
    if (rows.value.length === 0) {
      toast.info("No data", "There is nothing to export.");
      return;
    }
    const ExcelJS = await import("exceljs");
    const wb = new ExcelJS.Workbook();
    const ws = wb.addWorksheet("Initial detail");
    ws.addRow(["No", ...exportColumns]);
    rows.value.forEach((r) => {
      const e = toExportRow(r);
      ws.addRow([r.index, ...exportColumns.map((h) => e[h as keyof typeof e])]);
    });
    const buf = await wb.xlsx.writeBuffer();
    const blob = new Blob([buf], { type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" });
    const url = URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.href = url;
    a.download = `Budget_Initial_New_V2_${bamId.value ?? "lines"}.xlsx`;
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
    void loadDetails();
  }, 350);
});
watch(limit, () => {
  page.value = 1;
  void loadDetails();
});
watch(
  bamId,
  async () => {
    page.value = 1;
    await loadMaster();
    await loadDetails();
  },
  { immediate: true },
);

onMounted(() => {
  document.addEventListener('click', onClickOutside);
});
onUnmounted(() => {
  document.removeEventListener('click', onClickOutside);
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
      <div class="flex flex-wrap items-center justify-between gap-2">
        <h1 class="page-title">{{ listHeading }}</h1>
        <button
          type="button"
          class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50"
          @click="gotoList"
        >
          ← Budget Initial list
        </button>
      </div>

      <div v-if="bamId == null" class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
        No allocation selected. Open this page from
        <button type="button" class="font-semibold underline" @click="gotoList">Budget Initial</button>
        and choose <strong>View</strong> on a row, or append
        <code class="rounded bg-amber-100 px-1">?bamId=</code> to the URL.
      </div>

      <article v-else class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-4 py-3">
          <h2 class="text-base font-semibold text-slate-900">Initial information</h2>
          <p v-if="masterLoading" class="mt-1 text-xs text-slate-500">Loading header…</p>
        </div>
        <div v-if="master" class="grid gap-3 p-4 text-sm sm:grid-cols-2 lg:grid-cols-3">
          <div>
            <div class="text-xs font-medium text-slate-500">Year</div>
            <div class="font-medium text-slate-900">{{ master.year ?? "—" }}</div>
          </div>
          <div>
            <div class="text-xs font-medium text-slate-500">Quarter</div>
            <div class="font-medium text-slate-900">{{ master.quarterLabel ?? master.quarterId ?? "—" }}</div>
          </div>
          <div>
            <div class="text-xs font-medium text-slate-500">Reference no</div>
            <div class="font-medium text-slate-900">{{ master.reference ?? "—" }}</div>
          </div>
          <div class="sm:col-span-2">
            <div class="text-xs font-medium text-slate-500">Authority / endorse document</div>
            <div class="text-slate-900">{{ master.endorseDoc ?? "—" }}</div>
          </div>
          <div>
            <div class="text-xs font-medium text-slate-500">Status</div>
            <div class="font-medium text-slate-900">{{ master.stat ?? "—" }}</div>
          </div>
          <div>
            <div class="text-xs font-medium text-slate-500">Total amount</div>
            <div class="font-medium text-slate-900">{{ formatAmt(master.total) }}</div>
          </div>
          <div>
            <div class="text-xs font-medium text-slate-500">Unregistered structure rows</div>
            <div class="text-slate-900">{{ master.unregisteredCount }}</div>
          </div>
          <div>
            <div class="text-xs font-medium text-slate-500">Upload error rows</div>
            <div class="text-slate-900">{{ master.errorDataFileCount }}</div>
          </div>
          <div class="sm:col-span-2 lg:col-span-3">
            <div class="text-xs font-medium text-slate-500">File name</div>
            <div class="text-slate-700">{{ master.fileName ?? "—" }}</div>
          </div>
        </div>
      </article>

      <article v-if="bamId != null" ref="datatableRef" class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
          <h2 class="text-base font-semibold text-slate-900">Initial detail</h2>
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
                placeholder="Search lines…"
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
            <table class="admin-table-kitchen w-full min-w-[1024px] text-sm">
              <thead class="admin-table-thead-sticky">
                <tr class="border-b border-slate-200 text-left">
                  <th class="px-3 py-2 text-xs font-semibold uppercase">No</th>
                  <th
                    class="cursor-pointer px-3 py-2 text-xs font-semibold uppercase"
                    @click="toggleSort('budgetId')"
                  >
                    Budget index no
                  </th>
                  <th class="cursor-pointer px-3 py-2 text-xs font-semibold uppercase" @click="toggleSort('fund')">
                    Fund
                  </th>
                  <th
                    class="cursor-pointer px-3 py-2 text-xs font-semibold uppercase"
                    @click="toggleSort('activity')"
                  >
                    Activity
                  </th>
                  <th class="cursor-pointer px-3 py-2 text-xs font-semibold uppercase" @click="toggleSort('ptj')">
                    PTJ
                  </th>
                  <th class="cursor-pointer px-3 py-2 text-xs font-semibold uppercase" @click="toggleSort('ccr')">
                    Cost centre
                  </th>
                  <th
                    class="cursor-pointer px-3 py-2 text-xs font-semibold uppercase"
                    @click="toggleSort('budgetCode')"
                  >
                    Budget code
                  </th>
                  <th
                    class="cursor-pointer px-3 py-2 text-right text-xs font-semibold uppercase"
                    @click="toggleSort('amount')"
                  >
                    Amount
                  </th>
                  <th class="px-3 py-2 text-xs font-semibold uppercase">Status</th>
                  <th class="px-3 py-2 text-xs font-semibold uppercase">Action</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="detailLoading">
                  <td colspan="10" class="px-3 py-6 text-center text-slate-500">Loading…</td>
                </tr>
                <tr v-else-if="rows.length === 0">
                  <td colspan="10" class="px-3 py-6 text-center text-slate-500">No detail lines.</td>
                </tr>
                <tr v-for="row in rows" :key="row.id" class="border-b border-slate-100 hover:bg-slate-50">
                  <td class="px-3 py-2">{{ row.index }}</td>
                  <td class="px-3 py-2 font-mono text-xs">{{ row.budgetId ?? "—" }}</td>
                  <td class="px-3 py-2">{{ row.fund ?? "—" }}</td>
                  <td class="px-3 py-2">{{ row.activity ?? "—" }}</td>
                  <td class="px-3 py-2">{{ row.ptj ?? "—" }}</td>
                  <td class="px-3 py-2">{{ row.ccr ?? "—" }}</td>
                  <td class="px-3 py-2">{{ row.budgetCode ?? "—" }}</td>
                  <td class="px-3 py-2 text-right tabular-nums">{{ formatAmt(row.initialAmt) }}</td>
                  <td class="px-3 py-2">{{ row.stat ?? "—" }}</td>
                  <td class="px-3 py-2">
                    <div class="flex gap-1">
                      <button
                        type="button"
                        class="rounded p-1 text-slate-400 hover:bg-slate-100"
                        title="Edit line"
                        @click="notMigrated('line editor')"
                      >
                        <Pencil class="h-3.5 w-3.5" />
                      </button>
                      <button
                        type="button"
                        class="rounded p-1 text-slate-400 hover:bg-slate-100"
                        title="Delete line"
                        @click="notMigrated('delete line')"
                      >
                        <Trash2 class="h-3.5 w-3.5" />
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="flex flex-wrap items-center justify-between gap-2 border-t border-slate-100 pt-3 text-xs text-slate-600">
            <div>
              Showing {{ startIdx }}-{{ endIdx }} of {{ total }}
              <span class="mx-2 text-slate-300">|</span>
              Page total {{ formatAmt(pageTotal) }}
              <span class="mx-2 text-slate-300">|</span>
              Grand total {{ formatAmt(grandTotal) }}
            </div>
            <div class="flex flex-wrap items-center gap-2">
              <button
                type="button"
                :disabled="page <= 1"
                class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 font-medium disabled:opacity-50"
                @click="prevPage"
              >
                Prev
              </button>
              <span>Page {{ page }} / {{ totalPages }}</span>
              <button
                type="button"
                :disabled="page >= totalPages"
                class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 font-medium disabled:opacity-50"
                @click="nextPage"
              >
                Next
              </button>
              <div class="mx-1 h-5 w-px bg-slate-200" />
              <button
                type="button"
                class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-2 py-1.5 font-medium"
                @click="handleDownloadPDF"
              >
                <Download class="h-3.5 w-3.5" />
                PDF
              </button>
              <button
                type="button"
                class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-2 py-1.5 font-medium"
                @click="handleDownloadCSV"
              >
                <FileDown class="h-3.5 w-3.5" />
                CSV
              </button>
              <button
                type="button"
                class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-2 py-1.5 font-medium"
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
