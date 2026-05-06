<script setup lang="ts">
/**
 * Credit Control — Creditor/Debtor/Advance ageing-style bucket reports
 * (MENUIDs 3370, 3445, 3371, 3443, 3447, 3446, 3448, 3409, 3375).
 *
 * Backend: `CreditControlAgeingReportController` on mysql_secondary (ORM/query builder).
 * Filter-first: legacy reports require an end date before loading row sets.
 */
import { computed, onMounted, onUnmounted, ref, watch } from "vue";
import {
  Download,
  FileDown,
  FileSpreadsheet,
  Filter,
  MoreVertical,
  Search,
  X,
} from "lucide-vue-next";
import AdminLayout from "@/layouts/AdminLayout.vue";
import { useDatatableFeatures } from "@/composables/useDatatableFeatures";
import type { DatatableRefApi } from "@/composables/useDatatableFeatures";
import { listCreditControlAgeingReports } from "@/api/cms";
import { useToast } from "@/composables/useToast";
import type { CreditControlAgeingRow } from "@/types";

const props = defineProps<{
  pageBreadcrumb: string;
  cardTitle: string;
  ageingKind: string;
}>();

const toast = useToast();
const datatableRef = ref<DatatableRefApi | null>(null);
const rows = ref<CreditControlAgeingRow[]>([]);
const page = ref(1);
const limit = ref(10);
const q = ref("");
const total = ref(0);
const loading = ref(false);
const showSmartFilter = ref(false);
const hasSearched = ref(false);

const smartFilter = ref({
  endDate: "",
  tfCustomerType: "",
  ounCode: "",
  ftyFundType: "",
  atActivityCode: "",
  ccrCostcentre: "",
  tfCustomerId: "",
  acmAcctCode: "",
  tfRegion: "",
});

function toLegacyDate(iso: string): string {
  if (!iso) return "";
  const m = /^(\d{4})-(\d{2})-(\d{2})$/.exec(iso);
  return m ? `${m[3]}/${m[2]}/${m[1]}` : iso;
}

const split6y = computed(() =>
  [
    "creditor_summary_ext",
    "creditor_details_ext",
    "debtor_summary_ext",
    "debtor_details_ext",
    "creditor_ap_listing",
    "advance_listing",
  ].includes(props.ageingKind),
);

const detailLayout = computed(() =>
  [
    "creditor_details",
    "creditor_details_ext",
    "debtor_details_ext",
    "creditor_ap_listing",
    "advance_listing",
  ].includes(props.ageingKind),
);

const showFundTypeGroup = computed(
  () =>
    props.ageingKind !== "creditor_summary" &&
    props.ageingKind !== "debtor_summary_ext",
);

type Col = { key: string; label: string };

const dimensionCols = computed((): Col[] => {
  const r: Col[] = [{ key: "region", label: "Region" }];
  if (showFundTypeGroup.value) {
    r.push({ key: "fund_type", label: "Fund Type" });
  }
  if (detailLayout.value) {
    r.push(
      { key: "activity", label: "Activity" },
      { key: "ptj", label: "PTJ" },
      { key: "cost_centre", label: "Cost Centre" },
    );
  } else {
    r.push({ key: "ptj", label: "PTJ" });
  }
  r.push(
    { key: "cust_type", label: "Cust Type" },
    { key: "id_no", label: props.ageingKind === "debtor_summary_ext" ? "ID No" : "Cust Id" },
    { key: "cust_name", label: "Cust Name" },
  );
  if (props.ageingKind === "debtor_summary_ext") {
    r.push({ key: "account_code", label: "Account Code" });
  }
  if (detailLayout.value) {
    r.push(
      { key: "document_no", label: props.ageingKind.includes("debtor") ? "Invoice No" : "Document No" },
      { key: "account_code", label: "Account Code" },
      { key: "account_desc", label: "Account Desc" },
    );
  }
  return r;
});

const bucketCols = computed((): Col[] => {
  const b: Col[] = [{ key: "balance_as_date", label: "Balance as date" }];
  const seven = [
    { key: "days_0_30", label: "< 30 Days" },
    { key: "days_31_60", label: "31-60 Days" },
    { key: "days_61_90", label: "61-90 Days" },
    { key: "days_91_180", label: "91-180 Days" },
    { key: "days_6_12_mo", label: "6-12 Months" },
    { key: "days_12_24_mo", label: "12-24 Months" },
  ];
  if (split6y.value) {
    b.push(...seven, { key: "days_2_6_yr", label: "2-6 Years" }, { key: "days_over_6_yr", label: "> 6 Years" });
  } else {
    b.push(...seven, { key: "days_over_24_mo", label: "> 24 Months" });
  }
  return b;
});

const tableColumns = computed(() => [...dimensionCols.value, ...bucketCols.value]);

const totalPages = computed(() =>
  total.value ? Math.max(1, Math.ceil(total.value / limit.value)) : 1,
);

function fmtCell(v: unknown): string {
  if (v === null || v === undefined) return "";
  if (typeof v === "number") {
    return new Intl.NumberFormat("en-MY", { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(v);
  }
  return String(v);
}

async function loadRows() {
  const end = toLegacyDate(smartFilter.value.endDate);
  if (!end) {
    toast.error("Validation", "End date is required.");
    return;
  }
  loading.value = true;
  const sf = smartFilter.value;
  const params = new URLSearchParams({
    kind: props.ageingKind,
    tf_end_date: end,
    page: String(page.value),
    limit: String(limit.value),
    ...(q.value.trim() ? { q: q.value.trim() } : {}),
    ...(sf.tfCustomerType ? { tf_customer_type: sf.tfCustomerType } : {}),
    ...(sf.ounCode ? { oun_code: sf.ounCode } : {}),
    ...(sf.ftyFundType ? { fty_fund_type: sf.ftyFundType } : {}),
    ...(sf.atActivityCode ? { at_activity_code: sf.atActivityCode } : {}),
    ...(sf.ccrCostcentre ? { ccr_costcentre: sf.ccrCostcentre } : {}),
    ...(sf.tfCustomerId ? { tf_customer_id: sf.tfCustomerId } : {}),
    ...(sf.acmAcctCode ? { acm_acct_code: sf.acmAcctCode } : {}),
    ...(sf.tfRegion ? { tf_region: sf.tfRegion } : {}),
  });
  try {
    const res = await listCreditControlAgeingReports(`?${params.toString()}`);
    rows.value = res.data;
    total.value = Number(res.meta?.total ?? 0);
  } catch (e) {
    toast.error("Load failed", e instanceof Error ? e.message : "Unable to load report.");
  } finally {
    loading.value = false;
  }
}

function applySmartFilter() {
  page.value = 1;
  showSmartFilter.value = false;
  hasSearched.value = true;
  void loadRows();
}

function resetSmartFilter() {
  smartFilter.value = {
    endDate: "",
    tfCustomerType: "",
    ounCode: "",
    ftyFundType: "",
    atActivityCode: "",
    ccrCostcentre: "",
    tfCustomerId: "",
    acmAcctCode: "",
    tfRegion: "",
  };
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

const exportColumns = computed(() => tableColumns.value.map((c) => c.label));

function rowExportRecord(row: CreditControlAgeingRow): Record<string, string | number> {
  const o: Record<string, string | number> = {};
  for (const c of tableColumns.value) {
    const v = row[c.key];
    o[c.label] = v === null || v === undefined ? "" : typeof v === "number" ? v : String(v);
  }
  return o;
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
  pageName: props.cardTitle,
  apiDataPath: `/credit-control/ageing-reports:${props.ageingKind}`,
  defaultExportColumns: [],
  getFilteredList: () => rows.value.map((r) => rowExportRecord(r) as Record<string, unknown>),
  datatableRef,
  searchKeyword: q,
  smartFilter,
  applyFilters: () => {
    if (hasSearched.value) void loadRows();
  },
});

let qDebounce: ReturnType<typeof setTimeout> | null = null;
watch(q, () => {
  if (!hasSearched.value) return;
  if (qDebounce) clearTimeout(qDebounce);
  qDebounce = setTimeout(() => {
    qDebounce = null;
    page.value = 1;
    void loadRows();
  }, 320);
});

async function exportExcel() {
  try {
    if (rows.value.length === 0) {
      toast.info("No data", "There is nothing to export.");
      return;
    }
    const ExcelJS = await import("exceljs");
    const wb = new ExcelJS.Workbook();
    const ws = wb.addWorksheet("Report");
    const headers = ["No", ...exportColumns.value];
    ws.addRow(headers);
    rows.value.forEach((r, i) => {
      const vals = tableColumns.value.map((c) => r[c.key] ?? "");
      ws.addRow([i + 1, ...vals]);
    });
    const buf = await wb.xlsx.writeBuffer();
    const blob = new Blob([buf], { type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" });
    const url = URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.href = url;
    a.download = `CC_Ageing_${props.ageingKind}_${new Date().toISOString().slice(0, 10)}.xlsx`;
    a.click();
    URL.revokeObjectURL(url);
    toast.success("Excel downloaded");
  } catch (e) {
    toast.error("Export failed", e instanceof Error ? e.message : "Excel export failed.");
  }
}

watch(q, () => {
  if (!hasSearched.value) return;
  page.value = 1;
  const t = setTimeout(() => void loadRows(), 320);
  return () => clearTimeout(t);
});

onMounted(() => {
  document.addEventListener("click", onClickOutside);
  datatableRef.value = {
    getExportConfig: () => ({
      columns: exportColumns.value,
      data: rows.value.map((r) => rowExportRecord(r) as Record<string, unknown>),
    }),
  };
});
onUnmounted(() => {
  document.removeEventListener("click", onClickOutside);
  if (qDebounce) clearTimeout(qDebounce);
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

      <h1 class="page-title">{{ pageBreadcrumb }}</h1>

      <article class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between gap-2 border-b border-slate-100 px-4 py-3">
          <div>
            <h2 class="text-base font-semibold text-slate-900">{{ cardTitle }}</h2>
            <p v-if="ageingKind === 'advance_listing'" class="mt-1 text-xs text-amber-700">
              Advance listing uses an interim bucket query until the legacy advance join is fully ported.
            </p>
          </div>
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
              <select
                v-model.number="limit"
                class="rounded-lg border border-slate-300 px-2 py-1.5 text-sm"
                @change="page = 1; hasSearched && void loadRows()"
              >
                <option v-for="n in [5, 10, 25, 50, 100]" :key="n" :value="n">{{ n }}</option>
              </select>
            </div>
            <div class="flex flex-wrap items-center gap-2">
              <button
                type="button"
                class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm text-slate-700 hover:bg-slate-50"
                @click="showSmartFilter = true"
              >
                <Filter class="h-3.5 w-3.5" />
                Smart filter
              </button>
              <label class="text-xs font-medium text-slate-600">Search</label>
              <div class="relative">
                <Search
                  class="pointer-events-none absolute left-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400"
                />
                <input
                  v-model="q"
                  type="search"
                  placeholder="Filter loaded rows…"
                  class="w-56 rounded-lg border border-slate-300 py-1.5 pl-8 pr-8 text-sm"
                  :disabled="!hasSearched"
                  @keyup.enter="page = 1; hasSearched && void loadRows()"
                />
                <button
                  v-if="q"
                  type="button"
                  class="absolute right-1 top-1/2 -translate-y-1/2 rounded p-0.5 text-slate-400 hover:bg-slate-100"
                  @click="q = ''; page = 1; hasSearched && void loadRows()"
                >
                  <X class="h-3.5 w-3.5" />
                </button>
              </div>
            </div>
          </div>

          <div v-if="!hasSearched" class="rounded-lg border border-dashed border-slate-200 bg-slate-50 px-4 py-6 text-center text-sm text-slate-600">
            Set/report filters (end date required) via
            <button type="button" class="font-medium text-violet-600 hover:underline" @click="showSmartFilter = true">
              Smart filter
            </button>
            , then apply to load data.
          </div>

          <div v-else class="flex flex-wrap items-center justify-end gap-2">
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

          <div
            v-if="hasSearched"
            class="max-h-[28rem] overflow-y-auto overflow-x-auto rounded-lg border border-slate-200"
          >
            <table class="min-w-full divide-y divide-slate-200 text-left text-xs">
              <thead class="sticky top-0 z-10 bg-slate-50 text-slate-600">
                <tr>
                  <th class="whitespace-nowrap px-2 py-2 font-medium">No</th>
                  <th
                    v-for="c in tableColumns"
                    :key="c.key"
                    class="whitespace-nowrap px-2 py-2 font-medium"
                  >
                    {{ c.label }}
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 bg-white text-slate-800">
                <tr v-if="loading">
                  <td :colspan="tableColumns.length + 1" class="px-3 py-8 text-center text-slate-500">
                    Loading…
                  </td>
                </tr>
                <tr v-else-if="rows.length === 0">
                  <td :colspan="tableColumns.length + 1" class="px-3 py-8 text-center text-slate-500">
                    No rows
                  </td>
                </tr>
                <tr v-for="row in rows" v-else :key="String(row.index)">
                  <td class="px-2 py-1.5">{{ row.index }}</td>
                  <td v-for="c in tableColumns" :key="c.key" class="px-2 py-1.5 text-right tabular-nums">
                    <span v-if="String(c.key).startsWith('days_') || c.key === 'balance_as_date'">{{
                      fmtCell(row[c.key])
                    }}</span>
                    <span v-else>{{ row[c.key] ?? "" }}</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div v-if="hasSearched && total > 0" class="flex flex-wrap items-center justify-between gap-3 text-xs text-slate-600">
            <span>Records: {{ total }}</span>
            <div class="flex items-center gap-2">
              <button
                type="button"
                class="rounded border border-slate-300 px-2 py-1 hover:bg-slate-50 disabled:opacity-40"
                :disabled="page <= 1"
                @click="prevPage"
              >
                Prev
              </button>
              <span>Page {{ page }} / {{ totalPages }}</span>
              <button
                type="button"
                class="rounded border border-slate-300 px-2 py-1 hover:bg-slate-50 disabled:opacity-40"
                :disabled="page >= totalPages"
                @click="nextPage"
              >
                Next
              </button>
            </div>
          </div>
        </div>
      </article>

      <Teleport to="body">
        <div
          v-if="showSmartFilter"
          class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
          @click.self="showSmartFilter = false"
        >
          <div class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-xl bg-white shadow-xl">
            <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
              <h3 class="text-sm font-semibold text-slate-900">Smart filter</h3>
              <button type="button" class="rounded p-1 text-slate-500 hover:bg-slate-100" @click="showSmartFilter = false">
                <X class="h-4 w-4" />
              </button>
            </div>
            <div class="space-y-3 px-4 py-4 text-sm">
              <label class="block">
                <span class="text-xs font-medium text-slate-600">End date *</span>
                <input v-model="smartFilter.endDate" type="date" class="mt-1 w-full rounded-lg border border-slate-300 px-2 py-1.5" />
              </label>
              <label class="block">
                <span class="text-xs font-medium text-slate-600">Region (pejabat)</span>
                <input v-model="smartFilter.tfRegion" class="mt-1 w-full rounded-lg border border-slate-300 px-2 py-1.5" />
              </label>
              <label class="block">
                <span class="text-xs font-medium text-slate-600">Customer type</span>
                <input v-model="smartFilter.tfCustomerType" class="mt-1 w-full rounded-lg border border-slate-300 px-2 py-1.5" />
              </label>
              <label class="block">
                <span class="text-xs font-medium text-slate-600">PTJ (oun code)</span>
                <input v-model="smartFilter.ounCode" class="mt-1 w-full rounded-lg border border-slate-300 px-2 py-1.5" />
              </label>
              <label class="block">
                <span class="text-xs font-medium text-slate-600">Fund type</span>
                <input v-model="smartFilter.ftyFundType" class="mt-1 w-full rounded-lg border border-slate-300 px-2 py-1.5" />
              </label>
              <label class="block">
                <span class="text-xs font-medium text-slate-600">Activity code</span>
                <input v-model="smartFilter.atActivityCode" class="mt-1 w-full rounded-lg border border-slate-300 px-2 py-1.5" />
              </label>
              <label class="block">
                <span class="text-xs font-medium text-slate-600">Cost centre</span>
                <input v-model="smartFilter.ccrCostcentre" class="mt-1 w-full rounded-lg border border-slate-300 px-2 py-1.5" />
              </label>
              <label class="block">
                <span class="text-xs font-medium text-slate-600">Customer ID</span>
                <input v-model="smartFilter.tfCustomerId" class="mt-1 w-full rounded-lg border border-slate-300 px-2 py-1.5" />
              </label>
              <label class="block">
                <span class="text-xs font-medium text-slate-600">Account code</span>
                <input v-model="smartFilter.acmAcctCode" class="mt-1 w-full rounded-lg border border-slate-300 px-2 py-1.5" />
              </label>
            </div>
            <div class="flex justify-end gap-2 border-t border-slate-100 px-4 py-3">
              <button
                type="button"
                class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm text-slate-700 hover:bg-slate-50"
                @click="resetSmartFilter"
              >
                Reset
              </button>
              <button
                type="button"
                class="rounded-lg bg-violet-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-violet-700"
                @click="applySmartFilter"
              >
                Apply
              </button>
            </div>
          </div>
        </div>
      </Teleport>
    </div>
  </AdminLayout>
</template>
