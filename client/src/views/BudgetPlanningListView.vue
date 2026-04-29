<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref, watch, watchEffect } from "vue";
import {
  ChevronsUpDown,
  ClipboardCheck,
  Copy,
  Download,
  FileDown,
  FileSpreadsheet,
  ListFilter,
  MoreVertical,
  Search,
  Trash2,
  X,
} from "lucide-vue-next";
import { useRoute, useRouter } from "vue-router";
import AdminLayout from "@/layouts/AdminLayout.vue";
import {
  deleteBudgetPlanning,
  duplicateBudgetPlanning,
  getBudgetPlanningOptions,
  listBudgetPlanning,
} from "@/api/cms";
import { useDatatableFeatures } from "@/composables/useDatatableFeatures";
import type { DatatableRefApi } from "@/composables/useDatatableFeatures";
import { useConfirmDialog } from "@/composables/useConfirmDialog";
import { useToast } from "@/composables/useToast";
import type { BudgetPlanningOptions, BudgetPlanningRow, BudgetPlanningScope } from "@/types";

type ScopeMeta = {
  title: string;
  breadcrumb: string;
};

const SCOPE_META: Record<BudgetPlanningScope, ScopeMeta> = {
  yearly: {
    title: "Dasar Sedia Ada (Yearly)",
    breadcrumb: "Budget / Planning / Dasar Sedia Ada",
  },
  allocation_2: {
    title: "Allocation 2 List",
    breadcrumb: "Budget / Planning / Allocation 2 List",
  },
  allocation_3: {
    title: "Allocation 3 List",
    breadcrumb: "Budget / Planning / Allocation 3 List",
  },
  one_off: {
    title: "Dasar Baru / One Off",
    breadcrumb: "Budget / Planning / Dasar Baru / One Off",
  },
  to_initial: {
    title: "Planning to Initial",
    breadcrumb: "Budget / Planning / Planning to Initial",
  },
};

const props = defineProps<{
  scope?: BudgetPlanningScope;
  /** Override SCOPE_META breadcrumb (hidden menu paths). */
  breadcrumbOverride?: string;
  /** Override SCOPE_META panel title. */
  panelTitleOverride?: string;
}>();

const toast = useToast();
const { confirm } = useConfirmDialog();
const route = useRoute();
const router = useRouter();

const scope = computed<BudgetPlanningScope>(() => {
  const candidate = (props.scope ?? (route.meta?.scope as BudgetPlanningScope | undefined)) as
    | BudgetPlanningScope
    | undefined;
  return candidate && candidate in SCOPE_META ? candidate : "yearly";
});
const meta = computed<ScopeMeta>(() => {
  const base = SCOPE_META[scope.value] ?? SCOPE_META.yearly;
  return {
    title: props.panelTitleOverride ?? base.title,
    breadcrumb: props.breadcrumbOverride ?? base.breadcrumb,
  };
});

const isOneOffScope = computed(() => scope.value === "one_off");
const isToInitialScope = computed(() => scope.value === "to_initial");
/** Legacy LIST_OF_BUDGETPLANNING shell (Dasar Baru / One Off + Planning to Initial). */
const isLegacyListShell = computed(() => isOneOffScope.value || isToInitialScope.value);

const rows = ref<BudgetPlanningRow[]>([]);
const total = ref(0);
const page = ref(1);
const limit = ref(10);
const q = ref("");
const showSmartFilter = ref(false);
const smartFilter = ref<{
  planningNo: string;
  year: string;
  oun: string;
  ccr: string;
  status: string;
  type: string;
  title: string;
  amount: string;
}>({ planningNo: "", year: "", oun: "", ccr: "", status: "", type: "", title: "", amount: "" });

const options = ref<BudgetPlanningOptions>({
  smartFilter: { year: [], status: [], oun: [], ccr: [], type: [] },
});

/** Planning to Initial — row selection (bulk post not migrated on API yet). */
const selectedBpmIds = ref<Set<number>>(new Set());
const masterCheckboxRef = ref<HTMLInputElement | null>(null);

watch(scope, () => {
  selectedBpmIds.value = new Set();
});

watchEffect(() => {
  const el = masterCheckboxRef.value;
  if (!el || !isToInitialScope.value) return;
  const ids = rows.value.map((r) => r.bpmId);
  const count = ids.filter((id) => selectedBpmIds.value.has(id)).length;
  el.indeterminate = count > 0 && count < ids.length;
  el.checked = ids.length > 0 && count === ids.length;
});

function onMasterCheckboxChange(e: Event) {
  const checked = (e.target as HTMLInputElement).checked;
  const next = new Set(selectedBpmIds.value);
  for (const r of rows.value) {
    if (checked) next.add(r.bpmId);
    else next.delete(r.bpmId);
  }
  selectedBpmIds.value = next;
}

function toggleRowSelected(bpmId: number, checked: boolean) {
  const next = new Set(selectedBpmIds.value);
  if (checked) next.add(bpmId);
  else next.delete(bpmId);
  selectedBpmIds.value = next;
}

function postToInitial() {
  const n = selectedBpmIds.value.size;
  if (n === 0) {
    toast.info("No rows selected", "Select one or more planning rows, then try again.");
    return;
  }
  toast.info(
    "Not available yet",
    "Post to Initial depends on legacy stored procedures (budget allocation) that are not wired in this app. Selection is for UI parity only.",
  );
}

function goPlanningNew() {
  void router.push({ name: "kerisi-planning-new-application" });
}

async function loadOptions() {
  const res = await getBudgetPlanningOptions(scope.value);
  options.value = res.data;
}

async function loadRows() {
  const params = new URLSearchParams({
    page: String(page.value),
    limit: String(limit.value),
    ...(q.value ? { q: q.value } : {}),
    ...(smartFilter.value.planningNo ? { sm_planning_no: smartFilter.value.planningNo } : {}),
    ...(smartFilter.value.year ? { sm_year: smartFilter.value.year } : {}),
    ...(smartFilter.value.oun ? { sm_oun: smartFilter.value.oun } : {}),
    ...(smartFilter.value.ccr ? { sm_ccr: smartFilter.value.ccr } : {}),
    ...(smartFilter.value.status ? { sm_status: smartFilter.value.status } : {}),
    ...(smartFilter.value.type ? { sm_type: smartFilter.value.type } : {}),
    ...(smartFilter.value.title ? { sm_title: smartFilter.value.title } : {}),
    ...(smartFilter.value.amount ? { sm_amount: smartFilter.value.amount } : {}),
  });
  const res = await listBudgetPlanning(scope.value, `?${params.toString()}`);
  rows.value = res.data;
  total.value = Number(res.meta?.total ?? 0);
}

function applySmartFilter() {
  showSmartFilter.value = false;
  page.value = 1;
  void loadRows();
}

function resetSmartFilter() {
  smartFilter.value = { planningNo: "", year: "", oun: "", ccr: "", status: "", type: "", title: "", amount: "" };
}

async function removeItem(row: BudgetPlanningRow) {
  if (!row.canDelete) {
    toast.error("Not allowed", "Endorsed / posted records cannot be deleted.");
    return;
  }
  const accepted = await confirm({
    title: "Delete planning record",
    message: `Delete planning ${row.bpmPlanningNo ?? row.bpmId}?`,
    confirmText: "Delete",
    destructive: true,
  });
  if (!accepted) return;
  try {
    await deleteBudgetPlanning(row.bpmId);
    toast.success("Planning record deleted.");
    await loadRows();
  } catch (e) {
    toast.error("Delete failed", e instanceof Error ? e.message : "Delete failed");
  }
}

async function duplicateRow(row: BudgetPlanningRow) {
  try {
    const res = await duplicateBudgetPlanning(row.bpmId);
    toast.success(res.data.successMessage ?? "Budget planning duplicated.");
    await loadRows();
  } catch (e) {
    toast.error("Duplicate failed", e instanceof Error ? e.message : "Duplicate failed");
  }
}

function fmtMoney(v: number | null | undefined): string {
  if (v === null || v === undefined) return "";
  return Number(v).toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

/** Full export field map — `exportColumnsList` picks subset per scope. */
function baseExportRow(r: BudgetPlanningRow): Record<string, string | number> {
  return {
    "Planning No": r.bpmPlanningNo ?? "",
    "Planning Year": r.bpmYear ?? "",
    Year: r.bpmYear ?? "",
    Title: r.bpmRemark ?? "",
    Type: r.bpmType ?? "",
    Amount: fmtMoney(r.bpmTotalAmt),
    "Total Amount": fmtMoney(r.bpmTotalAmt),
    PTJ: r.bpmOunCode ?? "",
    OUN: r.bpmOunCode ?? "",
    "Cost Centre": r.bpmCcrCostcentre ?? "",
    Status: r.bpmStatus ?? "",
  };
}

const exportColumnsList = computed(() => {
  if (isToInitialScope.value) {
    return ["Planning No", "Planning Year", "Title", "Type", "Amount", "Status"];
  }
  if (isOneOffScope.value) {
    return ["Planning No", "Planning Year", "Title", "Type", "Amount", "PTJ", "Status"];
  }
  return ["Planning No", "Year", "OUN", "Cost Centre", "Title", "Total Amount", "Status", "Type"];
});

function getFilteredExportRows(): Record<string, unknown>[] {
  return rows.value.map((r) => {
    const b = baseExportRow(r);
    const row: Record<string, unknown> = {};
    for (const c of exportColumnsList.value) {
      row[c] = b[c] ?? "";
    }
    return row;
  });
}

function toExportRow(r: BudgetPlanningRow): Record<string, string | number> {
  const b = baseExportRow(r);
  const row: Record<string, string | number> = {};
  for (const c of exportColumnsList.value) {
    row[c] = b[c] ?? "";
  }
  return row;
}

const datatableRef = ref<DatatableRefApi | null>({
  getExportConfig: () => ({
    columns: [...exportColumnsList.value],
    data: getFilteredExportRows(),
  }),
});

const { templateFileInputRef, onTemplateFileChange, handleDownloadPDF, handleDownloadCSV } = useDatatableFeatures({
  pageName: "Budget Planning",
  apiDataPath: "/budget/planning-list",
  defaultExportColumns: [
    "Planning No",
    "Year",
    "OUN",
    "Cost Centre",
    "Title",
    "Total Amount",
    "Status",
    "Type",
  ],
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
    const cols = exportColumnsList.value;
    const ExcelJS = await import("exceljs");
    const wb = new ExcelJS.Workbook();
    const ws = wb.addWorksheet("Planning");
    ws.addRow(["No", ...cols]);
    rows.value.forEach((r, idx) => {
      const row = toExportRow(r);
      ws.addRow([idx + 1, ...cols.map((c) => row[c] ?? "")]);
    });
    const buf = await wb.xlsx.writeBuffer();
    const blob = new Blob([buf], { type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" });
    const url = URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.href = url;
    a.download = `${meta.value.title.replace(/\s+/g, "_")}_${new Date().toISOString().slice(0, 10)}.xlsx`;
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

watch(scope, async () => {
  page.value = 1;
  await loadOptions();
  await loadRows();
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
      <h1 class="page-title">{{ meta.breadcrumb }}</h1>

      <!-- Legacy: Dasar Baru / One Off + Planning to Initial -->
      <article
        v-if="isLegacyListShell"
        class="rounded-lg border border-slate-200 bg-white shadow-sm"
      >
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-4 py-3">
          <h2 class="text-base font-semibold text-slate-900">List of Budget Planning</h2>
          <div class="flex flex-wrap items-center gap-2">
            <button
              type="button"
              class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-300 bg-white text-slate-600 hover:bg-slate-50"
              aria-label="Filter"
              @click="showSmartFilter = true"
            >
              <ListFilter class="h-4 w-4" />
            </button>
            <label class="text-xs font-medium text-slate-600">Search</label>
            <div class="relative">
              <Search class="pointer-events-none absolute left-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400" />
              <input
                v-model="q"
                type="search"
                placeholder=""
                class="w-56 rounded-lg border border-slate-300 py-1.5 pl-8 pr-8 text-sm"
                @keyup.enter="page = 1; void loadRows()"
              />
              <button
                v-if="q"
                type="button"
                class="absolute right-1 top-1/2 -translate-y-1/2 rounded p-0.5 text-slate-400 hover:bg-slate-100"
                aria-label="Clear search"
                @click="q = ''"
              >
                <X class="h-3.5 w-3.5" />
              </button>
            </div>
          </div>
        </div>
        <div class="space-y-4 p-4">
          <div class="flex flex-wrap items-center gap-3">
            <label class="text-xs font-medium text-slate-600">Display</label>
            <select
              v-model.number="limit"
              class="rounded-lg border border-slate-300 px-2 py-1.5 text-sm"
              @change="page = 1; void loadRows()"
            >
              <option v-for="n in [5, 10, 25, 50, 100]" :key="n" :value="n">{{ n }}</option>
            </select>
          </div>
          <div class="overflow-x-auto rounded-lg border border-slate-200">
            <div :class="rows.length > 10 ? 'max-h-[480px] overflow-y-auto' : ''">
              <table class="w-full min-w-[900px] text-sm">
                <thead class="sticky top-0 z-[1] bg-violet-600 text-white">
                  <tr class="border-b border-violet-500 text-left">
                    <th class="px-3 py-2.5 text-xs font-semibold uppercase tracking-wide">No</th>
                    <th class="px-3 py-2.5 text-xs font-semibold uppercase tracking-wide">
                      <span class="inline-flex items-center gap-1">Planning No <ChevronsUpDown class="h-3.5 w-3.5 opacity-80" /></span>
                    </th>
                    <th class="px-3 py-2.5 text-xs font-semibold uppercase tracking-wide">
                      <span class="inline-flex items-center gap-1">Planning Year <ChevronsUpDown class="h-3.5 w-3.5 opacity-80" /></span>
                    </th>
                    <th class="px-3 py-2.5 text-xs font-semibold uppercase tracking-wide">
                      <span class="inline-flex items-center gap-1">Title <ChevronsUpDown class="h-3.5 w-3.5 opacity-80" /></span>
                    </th>
                    <th class="px-3 py-2.5 text-xs font-semibold uppercase tracking-wide">
                      <span class="inline-flex items-center gap-1">Type <ChevronsUpDown class="h-3.5 w-3.5 opacity-80" /></span>
                    </th>
                    <th class="px-3 py-2.5 text-right text-xs font-semibold uppercase tracking-wide">
                      <span class="inline-flex items-center justify-end gap-1">Amount <ChevronsUpDown class="h-3.5 w-3.5 shrink-0 opacity-80" /></span>
                    </th>
                    <th
                      v-if="isOneOffScope"
                      class="px-3 py-2.5 text-xs font-semibold uppercase tracking-wide"
                    >
                      <span class="inline-flex items-center gap-1">PTJ <ChevronsUpDown class="h-3.5 w-3.5 opacity-80" /></span>
                    </th>
                    <th class="px-3 py-2.5 text-xs font-semibold uppercase tracking-wide">
                      <span class="inline-flex items-center gap-1">Status <ChevronsUpDown class="h-3.5 w-3.5 opacity-80" /></span>
                    </th>
                    <th class="px-3 py-2.5 text-xs font-semibold uppercase tracking-wide">Action</th>
                    <th
                      v-if="isToInitialScope"
                      class="w-12 px-2 py-2.5 text-center text-xs font-semibold uppercase tracking-wide"
                    >
                      <input
                        ref="masterCheckboxRef"
                        type="checkbox"
                        class="h-4 w-4 rounded border-white/40 bg-white/10 accent-violet-200"
                        aria-label="Select all on this page"
                        @change="onMasterCheckboxChange"
                      />
                    </th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="row in rows" :key="row.bpmId" class="border-b border-slate-100 hover:bg-slate-50">
                    <td class="px-3 py-2">{{ row.index }}</td>
                    <td class="px-3 py-2 font-medium">{{ row.bpmPlanningNo ?? row.bpmId }}</td>
                    <td class="px-3 py-2">{{ row.bpmYear ?? "—" }}</td>
                    <td class="max-w-[280px] truncate px-3 py-2" :title="row.bpmRemark ?? ''">{{ row.bpmRemark ?? "—" }}</td>
                    <td class="px-3 py-2">{{ row.bpmType ?? "—" }}</td>
                    <td class="px-3 py-2 text-right">{{ fmtMoney(row.bpmTotalAmt) }}</td>
                    <td v-if="isOneOffScope" class="px-3 py-2">{{ row.bpmOunCode ?? "—" }}</td>
                    <td class="px-3 py-2">{{ row.bpmStatus ?? "—" }}</td>
                    <td class="px-3 py-2">
                      <div class="flex items-center gap-1">
                        <button class="rounded p-1 text-slate-500 hover:bg-slate-100" title="Duplicate" @click="duplicateRow(row)">
                          <Copy class="h-3.5 w-3.5" />
                        </button>
                        <button
                          class="rounded p-1 text-rose-500 hover:bg-rose-50 disabled:opacity-40 disabled:hover:bg-transparent"
                          :disabled="!row.canDelete"
                          title="Delete"
                          @click="removeItem(row)"
                        >
                          <Trash2 class="h-3.5 w-3.5" />
                        </button>
                      </div>
                    </td>
                    <td v-if="isToInitialScope" class="px-2 py-2 text-center">
                      <input
                        type="checkbox"
                        class="h-4 w-4 rounded border-slate-300 text-violet-600"
                        :checked="selectedBpmIds.has(row.bpmId)"
                        aria-label="Select row"
                        @change="toggleRowSelected(row.bpmId, ($event.target as HTMLInputElement).checked)"
                      />
                    </td>
                  </tr>
                  <tr v-if="rows.length === 0">
                    <td colspan="9" class="px-3 py-8 text-center text-sm text-slate-500">No records</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-3">
            <div class="flex flex-wrap items-center gap-3 text-xs text-slate-500">
              <span>{{ total }} record{{ total === 1 ? "" : "s" }}</span>
              <template v-if="totalPages > 1">
                <span class="text-slate-400">Page {{ page }} / {{ totalPages }}</span>
                <div class="flex items-center gap-1">
                  <button
                    type="button"
                    class="rounded border border-slate-300 bg-white px-2 py-1 hover:bg-slate-50 disabled:opacity-40"
                    :disabled="page <= 1"
                    @click="page = Math.max(1, page - 1); void loadRows()"
                  >
                    Prev
                  </button>
                  <button
                    type="button"
                    class="rounded border border-slate-300 bg-white px-2 py-1 hover:bg-slate-50 disabled:opacity-40"
                    :disabled="page >= totalPages"
                    @click="page = Math.min(totalPages, page + 1); void loadRows()"
                  >
                    Next
                  </button>
                </div>
              </template>
            </div>
            <div class="flex flex-wrap items-center gap-2">
              <button
                v-if="isOneOffScope"
                type="button"
                class="inline-flex items-center gap-2 rounded-lg bg-violet-600 px-4 py-2 text-sm font-medium text-white hover:bg-violet-700"
                @click="goPlanningNew"
              >
                <span class="text-base font-medium leading-none">+</span>
                New
              </button>
              <button
                v-else-if="isToInitialScope"
                type="button"
                class="inline-flex items-center gap-2 rounded-lg bg-violet-600 px-4 py-2 text-sm font-medium text-white hover:bg-violet-700"
                @click="postToInitial"
              >
                <ClipboardCheck class="h-4 w-4" />
                Post to Initial
              </button>
            </div>
          </div>
        </div>
      </article>

      <!-- Standard planning lists (yearly, allocation 2/3) -->
      <article
        v-else
        class="rounded-lg border border-slate-200 bg-white shadow-sm"
      >
        <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
          <h2 class="text-base font-semibold text-slate-900">{{ meta.title }}</h2>
          <button class="rounded-lg p-2 text-slate-500 hover:bg-slate-100" aria-label="More">
            <MoreVertical class="h-4 w-4" />
          </button>
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
                <thead class="sticky top-0 bg-slate-50">
                  <tr class="border-b border-slate-200 text-left">
                    <th class="px-3 py-2 text-xs font-semibold uppercase">No</th>
                    <th class="px-3 py-2 text-xs font-semibold uppercase">Planning No</th>
                    <th class="px-3 py-2 text-xs font-semibold uppercase">Year</th>
                    <th class="px-3 py-2 text-xs font-semibold uppercase">OUN</th>
                    <th class="px-3 py-2 text-xs font-semibold uppercase">Cost Centre</th>
                    <th class="px-3 py-2 text-xs font-semibold uppercase">Title</th>
                    <th class="px-3 py-2 text-right text-xs font-semibold uppercase">Total Amount</th>
                    <th class="px-3 py-2 text-xs font-semibold uppercase">Status</th>
                    <th class="px-3 py-2 text-xs font-semibold uppercase">Type</th>
                    <th class="px-3 py-2 text-xs font-semibold uppercase">Action</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="row in rows" :key="row.bpmId" class="border-b border-slate-100 hover:bg-slate-50">
                    <td class="px-3 py-2">{{ row.index }}</td>
                    <td class="px-3 py-2 font-medium">{{ row.bpmPlanningNo ?? row.bpmId }}</td>
                    <td class="px-3 py-2">{{ row.bpmYear ?? "—" }}</td>
                    <td class="px-3 py-2">{{ row.bpmOunCode ?? "—" }}</td>
                    <td class="px-3 py-2">{{ row.bpmCcrCostcentre ?? "—" }}</td>
                    <td class="max-w-[280px] truncate px-3 py-2" :title="row.bpmRemark ?? ''">{{ row.bpmRemark ?? "—" }}</td>
                    <td class="px-3 py-2 text-right">{{ fmtMoney(row.bpmTotalAmt) }}</td>
                    <td class="px-3 py-2">{{ row.bpmStatus ?? "—" }}</td>
                    <td class="px-3 py-2">{{ row.bpmType ?? "—" }}</td>
                    <td class="px-3 py-2">
                      <div class="flex items-center gap-1">
                        <button class="rounded p-1 text-slate-500 hover:bg-slate-100" title="Duplicate" @click="duplicateRow(row)">
                          <Copy class="h-3.5 w-3.5" />
                        </button>
                        <button
                          class="rounded p-1 text-rose-500 hover:bg-rose-50 disabled:opacity-40 disabled:hover:bg-transparent"
                          :disabled="!row.canDelete"
                          title="Delete"
                          @click="removeItem(row)"
                        >
                          <Trash2 class="h-3.5 w-3.5" />
                        </button>
                      </div>
                    </td>
                  </tr>
                  <tr v-if="rows.length === 0">
                    <td colspan="10" class="px-3 py-6 text-center text-xs text-slate-500">No data</td>
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
              >Prev</button>
              <button
                type="button"
                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium hover:bg-slate-50 disabled:opacity-50"
                :disabled="page >= totalPages"
                @click="page = Math.min(totalPages, page + 1); void loadRows()"
              >Next</button>
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
              <label class="mb-1 block text-xs font-medium text-slate-600">Planning No</label>
              <input v-model="smartFilter.planningNo" type="text" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
            </div>
            <div>
              <label class="mb-1 block text-xs font-medium text-slate-600">Year</label>
              <select v-model="smartFilter.year" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">Any</option>
                <option v-for="opt in options.smartFilter.year" :key="opt.id" :value="opt.id">{{ opt.label }}</option>
              </select>
            </div>
            <div>
              <label class="mb-1 block text-xs font-medium text-slate-600">OUN</label>
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
              <label class="mb-1 block text-xs font-medium text-slate-600">Status</label>
              <select v-model="smartFilter.status" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">Any</option>
                <option v-for="opt in options.smartFilter.status" :key="opt.id" :value="opt.id">{{ opt.label }}</option>
              </select>
            </div>
            <div>
              <label class="mb-1 block text-xs font-medium text-slate-600">Type</label>
              <select v-model="smartFilter.type" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">Any</option>
                <option v-for="opt in options.smartFilter.type" :key="opt.id" :value="opt.id">{{ opt.label }}</option>
              </select>
            </div>
            <div>
              <label class="mb-1 block text-xs font-medium text-slate-600">Title (contains)</label>
              <input v-model="smartFilter.title" type="text" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
            </div>
            <div>
              <label class="mb-1 block text-xs font-medium text-slate-600">Total Amount (=)</label>
              <input v-model="smartFilter.amount" type="number" step="0.01" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
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
