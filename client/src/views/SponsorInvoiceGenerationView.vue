<script setup lang="ts">
/**
 * Student Finance / Sponsor / Invoice Generation (PAGEID 1218 / MENUID 1491).
 *
 * Source: FIMS BL `V2_SFSI_API` (?listing=2 path). Read-only listing
 * driven by a top form (Sponsor / Program Level / Semester) — required
 * — plus a smart filter (matric, name, status, outstanding range,
 * claim range).
 *
 * The legacy Generate action calls `CALL DB2.create_invoice_sponsor`
 * SP which is NOT migrated — the Generate button renders disabled with
 * a "not migrated" tooltip until the SP is ported. We still surface the
 * checkbox column visually so the legacy UX is recognisable, but the
 * checkboxes themselves are disabled.
 *
 * Per project policy (legacy COMPONENT_JS has no `printout` field) we
 * surface PDF / CSV / Excel exports for the rendered page set.
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
import {
  getSponsorInvoiceGenerationOptions,
  listSponsorInvoiceGeneration,
} from "@/api/cms";
import { useToast } from "@/composables/useToast";
import type {
  SponsorInvoiceGenerationFooter,
  SponsorInvoiceGenerationOptions,
  SponsorInvoiceGenerationRow,
  SponsorInvoiceGenerationSmartFilter,
  SponsorInvoiceGenerationTopFilter,
} from "@/types";

const toast = useToast();
const datatableRef = ref<DatatableRefApi | null>(null);

const rows = ref<SponsorInvoiceGenerationRow[]>([]);
const loading = ref(false);
const total = ref(0);
const totalStudent = ref(0);
const footer = ref<SponsorInvoiceGenerationFooter>({ outstandingAmt: 0, cimNettAmt: 0 });
const page = ref(1);
const limit = ref(10);
const q = ref("");

type InvSortKey =
  | "std_student_id"
  | "std_student_name"
  | "std_status_desc"
  | "spn_sponsor_name"
  | "spc_date_from"
  | "spc_date_to"
  | "cim_invoice_no"
  | "outstanding_amt"
  | "ssp_limit_bal"
  | "cim_nett_amt";

const sortBy = ref<InvSortKey>("std_student_id");
const sortDir = ref<"asc" | "desc">("asc");

const showSmartFilter = ref(false);
const topFilter = ref<SponsorInvoiceGenerationTopFilter>({
  spnSponsorCode: "",
  stdProgram: "",
  cimSemesterId: "",
});

const smartFilter = ref<SponsorInvoiceGenerationSmartFilter>({
  stdStudentId: "",
  stdStudentName: "",
  studStatus: "",
  outstandingAmtFrom: "",
  outstandingAmtTo: "",
  cimNettAmtFrom: "",
  cimNettAmtTo: "",
});

const options = ref<SponsorInvoiceGenerationOptions>({
  sponsors: [],
  programLevels: [],
  semesters: [],
});

// Local checkbox state — only used to mirror the legacy UI, since
// Generate is disabled until the SP is migrated.
const checkedRows = ref<Record<number, boolean>>({});

const totalPages = computed(() =>
  total.value ? Math.max(1, Math.ceil(total.value / limit.value)) : 1,
);
const startIdx = computed(() =>
  total.value === 0 ? 0 : (page.value - 1) * limit.value + 1,
);
const endIdx = computed(() => Math.min(page.value * limit.value, total.value));

const topFilterReady = computed(
  () =>
    !!topFilter.value.spnSponsorCode
    && !!topFilter.value.stdProgram
    && !!topFilter.value.cimSemesterId,
);

function fmt(n: number | null | undefined): string {
  return new Intl.NumberFormat("en-MY", {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(Number.isFinite(n ?? NaN) ? (n as number) : 0);
}

async function loadOptions() {
  try {
    const res = await getSponsorInvoiceGenerationOptions();
    options.value = res.data;
  } catch {
    // silent — dropdowns are best-effort
  }
}

async function loadRows() {
  if (!topFilterReady.value) {
    rows.value = [];
    total.value = 0;
    totalStudent.value = 0;
    footer.value = { outstandingAmt: 0, cimNettAmt: 0 };
    return;
  }
  loading.value = true;
  const params = new URLSearchParams({
    page: String(page.value),
    limit: String(limit.value),
    sort_by: sortBy.value,
    sort_dir: sortDir.value,
    spn_sponsor_code: topFilter.value.spnSponsorCode,
    std_program: topFilter.value.stdProgram,
    cim_semester_id: topFilter.value.cimSemesterId,
  });
  if (q.value.trim()) params.set("q", q.value.trim());
  if (smartFilter.value.stdStudentId)
    params.set("std_student_id", smartFilter.value.stdStudentId);
  if (smartFilter.value.stdStudentName)
    params.set("std_student_name", smartFilter.value.stdStudentName);
  if (smartFilter.value.studStatus)
    params.set("std_status_desc", smartFilter.value.studStatus);
  if (smartFilter.value.outstandingAmtFrom)
    params.set("outstanding_amt_from", smartFilter.value.outstandingAmtFrom);
  if (smartFilter.value.outstandingAmtTo)
    params.set("outstanding_amt_to", smartFilter.value.outstandingAmtTo);
  if (smartFilter.value.cimNettAmtFrom)
    params.set("cim_nett_amt_from", smartFilter.value.cimNettAmtFrom);
  if (smartFilter.value.cimNettAmtTo)
    params.set("cim_nett_amt_to", smartFilter.value.cimNettAmtTo);

  try {
    const res = await listSponsorInvoiceGeneration(`?${params.toString()}`);
    rows.value = res.data;
    total.value = Number(res.meta?.total ?? 0);
    totalStudent.value = Number(res.meta?.totalStudent ?? 0);
    footer.value = res.meta?.footer ?? { outstandingAmt: 0, cimNettAmt: 0 };
  } catch (e) {
    toast.error(
      "Load failed",
      e instanceof Error
        ? e.message
        : "Unable to load Sponsor Invoice Generation list.",
    );
  } finally {
    loading.value = false;
  }
}

function applyTopFilter() {
  page.value = 1;
  void loadRows();
}

function toggleSort(col: InvSortKey) {
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

function applySmartFilter() {
  page.value = 1;
  showSmartFilter.value = false;
  void loadRows();
}

function resetSmartFilter() {
  smartFilter.value = {
    stdStudentId: "",
    stdStudentName: "",
    studStatus: "",
    outstandingAmtFrom: "",
    outstandingAmtTo: "",
    cimNettAmtFrom: "",
    cimNettAmtTo: "",
  };
}

const exportColumns = [
  "Matric",
  "Name",
  "Status",
  "Sponsor",
  "Start Date",
  "End Date",
  "Invoice No",
  "Invoice Amt",
  "Limit Balance",
  "Claim Amt",
];

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
  pageName: "Sponsor Invoice Generation",
  apiDataPath: "/student-finance/sponsor-invoice-generation",
  defaultExportColumns: exportColumns,
  getFilteredList: () =>
    rows.value.map((r) => ({
      Matric: r.stdStudentId,
      Name: r.stdStudentName ?? "",
      Status: r.stdStatusDesc ?? "",
      Sponsor: r.spnSponsorName ?? "",
      "Start Date": r.spcDateFrom ?? "",
      "End Date": r.spcDateTo ?? "",
      "Invoice No": r.cimInvoiceNo ?? "",
      "Invoice Amt": r.outstandingAmt.toFixed(2),
      "Limit Balance": (r.sspLimitBal ?? 0).toFixed(2),
      "Claim Amt": (r.cimNettAmt ?? 0).toFixed(2),
    })),
  datatableRef,
  searchKeyword: q,
  smartFilter,
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
    const ws = wb.addWorksheet("Sponsor Invoice Generation");
    ws.addRow(["No", ...exportColumns]);
    rows.value.forEach((r, idx) => {
      ws.addRow([
        idx + 1,
        r.stdStudentId,
        r.stdStudentName ?? "",
        r.stdStatusDesc ?? "",
        r.spnSponsorName ?? "",
        r.spcDateFrom ?? "",
        r.spcDateTo ?? "",
        r.cimInvoiceNo ?? "",
        r.outstandingAmt,
        r.sspLimitBal ?? 0,
        r.cimNettAmt ?? 0,
      ]);
    });
    const buf = await wb.xlsx.writeBuffer();
    const blob = new Blob([buf], {
      type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
    });
    const url = URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.href = url;
    a.download = `SponsorInvoiceGeneration_${new Date().toISOString().slice(0, 10)}.xlsx`;
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

onMounted(async () => {
  document.addEventListener("click", onClickOutside);
  await loadOptions();
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

      <h1 class="page-title">Student Finance / Sponsor / Invoice Generation</h1>

      <article class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
          <h1 class="text-base font-semibold text-slate-900">Search Sponsored Student</h1>
        </div>
        <div class="grid grid-cols-1 gap-3 p-4 md:grid-cols-3">
          <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Sponsor *</label>
            <select
              v-model="topFilter.spnSponsorCode"
              class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
            >
              <option value="">Select sponsor</option>
              <option v-for="opt in options.sponsors" :key="opt.id" :value="opt.id">
                {{ opt.label }}
              </option>
            </select>
          </div>
          <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Program Level *</label>
            <select
              v-model="topFilter.stdProgram"
              class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
            >
              <option value="">Select program level</option>
              <option v-for="opt in options.programLevels" :key="opt.id" :value="opt.id">
                {{ opt.label }}
              </option>
            </select>
          </div>
          <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Semester *</label>
            <select
              v-model="topFilter.cimSemesterId"
              class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
            >
              <option value="">Select semester</option>
              <option v-for="opt in options.semesters" :key="opt.id" :value="opt.id">
                {{ opt.label }}
              </option>
            </select>
          </div>
          <div class="md:col-span-3">
            <button
              type="button"
              :disabled="!topFilterReady"
              class="inline-flex items-center gap-1 rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white disabled:opacity-50"
              @click="applyTopFilter"
            >
              Search
            </button>
          </div>
        </div>
      </article>

      <article class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
          <h1 class="text-base font-semibold text-slate-900">
            List of Sponsored Students with Outstanding Amount to Sponsor
          </h1>
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
          <div
            v-if="!topFilterReady"
            class="rounded-md border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-800"
          >
            Pick Sponsor, Program Level and Semester above, then click <strong>Search</strong>.
          </div>

          <div class="flex flex-wrap items-end justify-between gap-4">
            <div class="flex items-center gap-2">
              <label class="text-xs font-medium text-slate-600">Display</label>
              <select
                v-model.number="limit"
                :disabled="!topFilterReady"
                class="rounded-lg border border-slate-300 px-2 py-1.5 text-sm disabled:opacity-50"
                @change="
                  page = 1;
                  loadRows();
                "
              >
                <option v-for="n in [5, 10, 25, 50, 100]" :key="n" :value="n">{{ n }}</option>
              </select>
              <span v-if="topFilterReady" class="text-xs text-slate-500">
                {{ total }} invoice(s) · {{ totalStudent }} student(s)
              </span>
            </div>
            <div class="flex items-center gap-2">
              <label class="text-xs font-medium text-slate-600">Search</label>
              <div class="relative">
                <Search
                  class="pointer-events-none absolute left-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400"
                />
                <input
                  v-model="q"
                  type="search"
                  :disabled="!topFilterReady"
                  placeholder="Filter rows..."
                  class="w-56 rounded-lg border border-slate-300 py-1.5 pl-8 pr-8 text-sm disabled:bg-slate-50"
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
                  @click="q = ''"
                >
                  <X class="h-3.5 w-3.5" />
                </button>
              </div>
              <button
                type="button"
                :disabled="!topFilterReady"
                class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-medium shadow-sm hover:bg-slate-50 disabled:opacity-50"
                @click="showSmartFilter = true"
              >
                <Filter class="h-4 w-4" />
                Filter
              </button>
            </div>
          </div>

          <div class="overflow-x-auto rounded-lg border border-slate-200">
            <div :class="rows.length > 10 ? 'max-h-[480px] overflow-y-auto' : ''">
              <table class="admin-table-kitchen w-full min-w-[1300px] text-sm">
                <thead class="admin-table-thead-sticky">
                  <tr class="border-b border-slate-200 text-left">
                    <th class="px-3 py-2 text-xs font-semibold uppercase">No.</th>
                    <th
                      class="cursor-pointer px-3 py-2 text-xs font-semibold uppercase"
                      @click="toggleSort('std_student_id')"
                    >
                      Matric
                      <span v-if="sortBy === 'std_student_id'">{{
                        sortDir === "asc" ? "↑" : "↓"
                      }}</span>
                    </th>
                    <th
                      class="cursor-pointer px-3 py-2 text-xs font-semibold uppercase"
                      @click="toggleSort('std_student_name')"
                    >
                      Name
                      <span v-if="sortBy === 'std_student_name'">{{
                        sortDir === "asc" ? "↑" : "↓"
                      }}</span>
                    </th>
                    <th
                      class="cursor-pointer px-3 py-2 text-xs font-semibold uppercase"
                      @click="toggleSort('std_status_desc')"
                    >
                      Status
                      <span v-if="sortBy === 'std_status_desc'">{{
                        sortDir === "asc" ? "↑" : "↓"
                      }}</span>
                    </th>
                    <th
                      class="cursor-pointer px-3 py-2 text-xs font-semibold uppercase"
                      @click="toggleSort('spn_sponsor_name')"
                    >
                      Sponsor
                      <span v-if="sortBy === 'spn_sponsor_name'">{{
                        sortDir === "asc" ? "↑" : "↓"
                      }}</span>
                    </th>
                    <th
                      class="cursor-pointer px-3 py-2 text-xs font-semibold uppercase"
                      @click="toggleSort('spc_date_from')"
                    >
                      Start Date
                      <span v-if="sortBy === 'spc_date_from'">{{
                        sortDir === "asc" ? "↑" : "↓"
                      }}</span>
                    </th>
                    <th
                      class="cursor-pointer px-3 py-2 text-xs font-semibold uppercase"
                      @click="toggleSort('spc_date_to')"
                    >
                      End Date
                      <span v-if="sortBy === 'spc_date_to'">{{
                        sortDir === "asc" ? "↑" : "↓"
                      }}</span>
                    </th>
                    <th
                      class="cursor-pointer px-3 py-2 text-xs font-semibold uppercase"
                      @click="toggleSort('cim_invoice_no')"
                    >
                      Invoice No
                      <span v-if="sortBy === 'cim_invoice_no'">{{
                        sortDir === "asc" ? "↑" : "↓"
                      }}</span>
                    </th>
                    <th
                      class="cursor-pointer px-3 py-2 text-right text-xs font-semibold uppercase"
                      @click="toggleSort('outstanding_amt')"
                    >
                      Invoice Amt
                      <span v-if="sortBy === 'outstanding_amt'">{{
                        sortDir === "asc" ? "↑" : "↓"
                      }}</span>
                    </th>
                    <th
                      class="cursor-pointer px-3 py-2 text-right text-xs font-semibold uppercase"
                      @click="toggleSort('ssp_limit_bal')"
                    >
                      Limit Balance
                      <span v-if="sortBy === 'ssp_limit_bal'">{{
                        sortDir === "asc" ? "↑" : "↓"
                      }}</span>
                    </th>
                    <th
                      class="cursor-pointer px-3 py-2 text-right text-xs font-semibold uppercase"
                      @click="toggleSort('cim_nett_amt')"
                    >
                      Claim Amt
                      <span v-if="sortBy === 'cim_nett_amt'">{{
                        sortDir === "asc" ? "↑" : "↓"
                      }}</span>
                    </th>
                    <th class="px-3 py-2 text-xs font-semibold uppercase">Action</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-if="loading">
                    <td colspan="12" class="px-3 py-6 text-center text-sm text-slate-500">
                      Loading...
                    </td>
                  </tr>
                  <tr v-else-if="rows.length === 0">
                    <td colspan="12" class="px-3 py-6 text-center text-sm text-slate-500">
                      No records found.
                    </td>
                  </tr>
                  <tr
                    v-for="row in rows"
                    :key="row.cimCustInvoiceId"
                    class="border-b border-slate-100 hover:bg-slate-50"
                  >
                    <td class="px-3 py-2">{{ row.index }}</td>
                    <td class="whitespace-nowrap px-3 py-2 font-medium text-slate-900">
                      {{ row.stdStudentId }}
                    </td>
                    <td class="px-3 py-2">{{ row.stdStudentName ?? "-" }}</td>
                    <td class="px-3 py-2">{{ row.stdStatusDesc ?? "-" }}</td>
                    <td class="px-3 py-2">{{ row.spnSponsorName ?? "-" }}</td>
                    <td class="whitespace-nowrap px-3 py-2">{{ row.spcDateFrom ?? "-" }}</td>
                    <td class="whitespace-nowrap px-3 py-2">{{ row.spcDateTo ?? "-" }}</td>
                    <td class="whitespace-nowrap px-3 py-2">{{ row.cimInvoiceNo ?? "-" }}</td>
                    <td class="px-3 py-2 text-right tabular-nums">
                      {{ fmt(row.outstandingAmt) }}
                    </td>
                    <td class="px-3 py-2 text-right tabular-nums">
                      {{ row.sspLimitBal !== null ? fmt(row.sspLimitBal) : "-" }}
                    </td>
                    <td class="px-3 py-2 text-right tabular-nums">
                      <span v-if="row.cimNettAmt">{{ fmt(row.cimNettAmt) }}</span>
                      <span v-else
                        ><span class="text-slate-700">0.00</span><br /><span
                          class="text-xs text-rose-600"
                          >{{ row.tellMeWhy ?? "Please check Fee Cover" }}</span
                        ></span
                      >
                    </td>
                    <td class="whitespace-nowrap px-3 py-2 text-slate-400">
                      <span title="View invoice (legacy MENUID 1062) is not migrated yet">
                        <button
                          type="button"
                          disabled
                          class="mr-1 inline-flex cursor-not-allowed items-center gap-1 rounded border border-slate-200 px-2 py-0.5 text-xs opacity-60"
                        >
                          View
                        </button>
                      </span>
                      <input
                        type="checkbox"
                        v-model="checkedRows[row.cimCustInvoiceId]"
                        disabled
                        title="Generate Invoice (legacy create_invoice_sponsor SP) is not migrated yet"
                        class="cursor-not-allowed opacity-60"
                      />
                    </td>
                  </tr>
                </tbody>
                <tfoot v-if="rows.length > 0">
                  <tr class="border-t-2 border-slate-300 bg-slate-50">
                    <td colspan="8" class="px-3 py-2 text-right text-xs font-semibold uppercase">
                      Total
                    </td>
                    <td class="px-3 py-2 text-right text-sm font-semibold tabular-nums">
                      {{ fmt(footer.outstandingAmt) }}
                    </td>
                    <td></td>
                    <td class="px-3 py-2 text-right text-sm font-semibold tabular-nums">
                      {{ fmt(footer.cimNettAmt) }}
                    </td>
                    <td></td>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>

          <div
            class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-3"
          >
            <div class="text-xs text-slate-500">
              Showing {{ startIdx }}-{{ endIdx }} of {{ total }}
            </div>
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
              <span title="Generate Invoice flow (legacy create_invoice_sponsor SP) is not migrated yet">
                <button
                  type="button"
                  disabled
                  class="inline-flex cursor-not-allowed items-center gap-1.5 rounded-lg border border-slate-300 bg-slate-100 px-3 py-1.5 text-xs font-medium opacity-60"
                >
                  Generate
                </button>
              </span>
              <div class="mx-2 h-5 w-px bg-slate-200" />
              <button
                type="button"
                :disabled="!topFilterReady || rows.length === 0"
                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium disabled:opacity-50"
                @click="handleDownloadPDF"
              >
                <Download class="h-3.5 w-3.5" />PDF
              </button>
              <button
                type="button"
                :disabled="!topFilterReady || rows.length === 0"
                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium disabled:opacity-50"
                @click="handleDownloadCSV"
              >
                <FileDown class="h-3.5 w-3.5" />CSV
              </button>
              <button
                type="button"
                :disabled="!topFilterReady || rows.length === 0"
                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium disabled:opacity-50"
                @click="exportExcel"
              >
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
        <div class="w-full max-w-lg rounded-lg border border-slate-200 bg-white shadow-2xl">
          <div class="border-b border-slate-100 px-4 py-3">
            <h3 class="text-base font-semibold text-slate-900">Smart filter</h3>
          </div>
          <div class="space-y-3 p-4">
            <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
              <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Matric</label>
                <input
                  v-model="smartFilter.stdStudentId"
                  type="text"
                  class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
                />
              </div>
              <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Name</label>
                <input
                  v-model="smartFilter.stdStudentName"
                  type="text"
                  class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
                />
              </div>
              <div class="md:col-span-2">
                <label class="mb-1 block text-sm font-medium text-slate-700">Status</label>
                <select
                  v-model="smartFilter.studStatus"
                  class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
                >
                  <option value="">Any</option>
                  <option value="1">ACTIVE</option>
                  <option value="0">INACTIVE</option>
                </select>
              </div>
            </div>
            <div>
              <label class="mb-1 block text-sm font-medium text-slate-700">Invoice Amt (MYR)</label>
              <div class="flex items-center gap-2">
                <input
                  v-model="smartFilter.outstandingAmtFrom"
                  type="text"
                  inputmode="decimal"
                  placeholder="From"
                  class="w-full rounded-lg border border-slate-300 px-3 py-2 text-right text-sm"
                />
                <span class="text-xs text-slate-500">to</span>
                <input
                  v-model="smartFilter.outstandingAmtTo"
                  type="text"
                  inputmode="decimal"
                  placeholder="To"
                  class="w-full rounded-lg border border-slate-300 px-3 py-2 text-right text-sm"
                />
              </div>
            </div>
            <div>
              <label class="mb-1 block text-sm font-medium text-slate-700">Claim Amt (MYR)</label>
              <div class="flex items-center gap-2">
                <input
                  v-model="smartFilter.cimNettAmtFrom"
                  type="text"
                  inputmode="decimal"
                  placeholder="From"
                  class="w-full rounded-lg border border-slate-300 px-3 py-2 text-right text-sm"
                />
                <span class="text-xs text-slate-500">to</span>
                <input
                  v-model="smartFilter.cimNettAmtTo"
                  type="text"
                  inputmode="decimal"
                  placeholder="To"
                  class="w-full rounded-lg border border-slate-300 px-3 py-2 text-right text-sm"
                />
              </div>
            </div>
          </div>
          <div class="flex justify-end gap-2 border-t border-slate-100 px-4 py-3">
            <button
              type="button"
              class="rounded-lg border border-slate-300 px-4 py-2 text-sm"
              @click="resetSmartFilter"
            >
              Reset
            </button>
            <button
              type="button"
              class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white"
              @click="applySmartFilter"
            >
              OK
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </AdminLayout>
</template>
