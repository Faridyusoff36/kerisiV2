<script setup lang="ts">
/**
 * Student Finance / Sponsor / PTPTN (PAGEID 1231 / MENUID 1507).
 *
 * Source: FIMS BL `V2_PTPTN_API` (?listing=1). Read-only datatable
 * joining student × stud_sponsor × sponsor scoped to PTPTN
 * (spn_sponsor_type='05'). Smart filter keys mirror the legacy
 * contract (matric, name, ic_passport, program_level, student_status,
 * reference_no, warrant_no).
 *
 * The legacy COMPONENT_JS marks the Action column as `d-none` (hidden)
 * because the View link points to legacy menuID=1479 (Sponsor form)
 * which is NOT migrated yet — the Action column is omitted entirely.
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
import { getSponsorPtptnOptions, listSponsorPtptn } from "@/api/cms";
import { useToast } from "@/composables/useToast";
import type {
  SponsorPtptnOptions,
  SponsorPtptnRow,
  SponsorPtptnSmartFilter,
} from "@/types";

const toast = useToast();
const datatableRef = ref<DatatableRefApi | null>(null);

const rows = ref<SponsorPtptnRow[]>([]);
const loading = ref(false);
const total = ref(0);
const page = ref(1);
const limit = ref(10);
const q = ref("");

type PtptnSortKey =
  | "std_student_id"
  | "std_student_name"
  | "ic_passport"
  | "std_program_level"
  | "ssp_reference_no"
  | "ssp_warrant_no"
  | "ssp_warrant_amt"
  | "deduction"
  | "balance";

const sortBy = ref<PtptnSortKey>("std_student_id");
const sortDir = ref<"asc" | "desc">("asc");

const showSmartFilter = ref(false);
const smartFilter = ref<SponsorPtptnSmartFilter>({
  matric: "",
  name: "",
  icPassport: "",
  programLevel: "",
  studentStatus: "",
  referenceNo: "",
  warrantNo: "",
});

const options = ref<SponsorPtptnOptions>({ programLevel: [], studentStatus: [] });

const totalPages = computed(() =>
  total.value ? Math.max(1, Math.ceil(total.value / limit.value)) : 1,
);
const startIdx = computed(() =>
  total.value === 0 ? 0 : (page.value - 1) * limit.value + 1,
);
const endIdx = computed(() => Math.min(page.value * limit.value, total.value));

function fmt(amount: number | null | undefined): string {
  const n = amount ?? 0;
  return new Intl.NumberFormat("en-MY", {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(Number.isFinite(n) ? n : 0);
}

async function loadOptions() {
  try {
    const res = await getSponsorPtptnOptions();
    options.value = res.data;
  } catch {
    // silent — dropdowns are best-effort
  }
}

async function loadRows() {
  loading.value = true;
  const params = new URLSearchParams({
    page: String(page.value),
    limit: String(limit.value),
    sort_by: sortBy.value,
    sort_dir: sortDir.value,
  });
  if (q.value.trim()) params.set("q", q.value.trim());
  if (smartFilter.value.matric) params.set("std_student_id", smartFilter.value.matric);
  if (smartFilter.value.name) params.set("std_student_name", smartFilter.value.name);
  if (smartFilter.value.icPassport)
    params.set("ic_passport", smartFilter.value.icPassport);
  if (smartFilter.value.programLevel)
    params.set("std_program_level", smartFilter.value.programLevel);
  if (smartFilter.value.studentStatus)
    params.set("std_status", smartFilter.value.studentStatus);
  if (smartFilter.value.referenceNo)
    params.set("ssp_reference_no", smartFilter.value.referenceNo);
  if (smartFilter.value.warrantNo)
    params.set("ssp_warrant_no", smartFilter.value.warrantNo);

  try {
    const res = await listSponsorPtptn(`?${params.toString()}`);
    rows.value = res.data;
    total.value = Number(res.meta?.total ?? 0);
  } catch (e) {
    toast.error(
      "Load failed",
      e instanceof Error ? e.message : "Unable to load PTPTN students.",
    );
  } finally {
    loading.value = false;
  }
}

function toggleSort(col: PtptnSortKey) {
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
    matric: "",
    name: "",
    icPassport: "",
    programLevel: "",
    studentStatus: "",
    referenceNo: "",
    warrantNo: "",
  };
}

const exportColumns = [
  "Matric",
  "Name",
  "NRIC/Passport",
  "Status",
  "Program Level",
  "No. Fail Pinjaman",
  "Warrant No.",
  "Warrant Amt",
  "Deduction",
  "Balance",
];

const {
  templateFileInputRef,
  onTemplateFileChange,
  handleDownloadPDF,
  handleDownloadCSV,
} = useDatatableFeatures({
  pageName: "List of PTPTN Students",
  apiDataPath: "/student-finance/sponsor-ptptn",
  defaultExportColumns: exportColumns,
  getFilteredList: () =>
    rows.value.map((r) => ({
      Matric: r.stdStudentId,
      Name: r.stdStudentName ?? "",
      "NRIC/Passport": r.icPassport ?? "",
      Status: r.studStatus ?? "",
      "Program Level": r.stdProgramLevel ?? "",
      "No. Fail Pinjaman": r.sspReferenceNo ?? "",
      "Warrant No.": r.sspWarrantNo ?? "",
      "Warrant Amt": (r.sspWarrantAmt ?? 0).toFixed(2),
      Deduction: (r.deduction ?? 0).toFixed(2),
      Balance: (r.balance ?? 0).toFixed(2),
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
    const ws = wb.addWorksheet("PTPTN Students");
    ws.addRow(["No", ...exportColumns]);
    rows.value.forEach((r, idx) => {
      ws.addRow([
        idx + 1,
        r.stdStudentId,
        r.stdStudentName ?? "",
        r.icPassport ?? "",
        r.studStatus ?? "",
        r.stdProgramLevel ?? "",
        r.sspReferenceNo ?? "",
        r.sspWarrantNo ?? "",
        r.sspWarrantAmt ?? 0,
        r.deduction ?? 0,
        r.balance ?? 0,
      ]);
    });
    const buf = await wb.xlsx.writeBuffer();
    const blob = new Blob([buf], {
      type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
    });
    const url = URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.href = url;
    a.download = `PTPTN_${new Date().toISOString().slice(0, 10)}.xlsx`;
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

      <h1 class="page-title">Student Finance / Sponsor / PTPTN</h1>

      <article class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
          <h1 class="text-base font-semibold text-slate-900">List of PTPTN Students</h1>
          <button
            type="button"
            class="rounded-lg p-2 text-slate-500 hover:bg-slate-100"
            aria-label="More"
          >
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
                @change="
                  page = 1;
                  loadRows();
                "
              >
                <option v-for="n in [5, 10, 25, 50, 100]" :key="n" :value="n">{{ n }}</option>
              </select>
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
                  @click="q = ''"
                >
                  <X class="h-3.5 w-3.5" />
                </button>
              </div>
              <button
                type="button"
                class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-medium shadow-sm hover:bg-slate-50"
                @click="showSmartFilter = true"
              >
                <Filter class="h-4 w-4" />
                Filter
              </button>
            </div>
          </div>

          <div class="overflow-x-auto rounded-lg border border-slate-200">
            <div :class="rows.length > 10 ? 'max-h-[480px] overflow-y-auto' : ''">
              <table class="w-full min-w-[1100px] text-sm">
                <thead class="sticky top-0 bg-slate-50">
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
                      @click="toggleSort('ic_passport')"
                    >
                      NRIC/Passport
                      <span v-if="sortBy === 'ic_passport'">{{
                        sortDir === "asc" ? "↑" : "↓"
                      }}</span>
                    </th>
                    <th class="px-3 py-2 text-xs font-semibold uppercase">Status</th>
                    <th
                      class="cursor-pointer px-3 py-2 text-xs font-semibold uppercase"
                      @click="toggleSort('std_program_level')"
                    >
                      Program Level
                      <span v-if="sortBy === 'std_program_level'">{{
                        sortDir === "asc" ? "↑" : "↓"
                      }}</span>
                    </th>
                    <th
                      class="cursor-pointer px-3 py-2 text-xs font-semibold uppercase"
                      @click="toggleSort('ssp_reference_no')"
                    >
                      No. Fail Pinjaman
                      <span v-if="sortBy === 'ssp_reference_no'">{{
                        sortDir === "asc" ? "↑" : "↓"
                      }}</span>
                    </th>
                    <th
                      class="cursor-pointer px-3 py-2 text-xs font-semibold uppercase"
                      @click="toggleSort('ssp_warrant_no')"
                    >
                      Warrant No.
                      <span v-if="sortBy === 'ssp_warrant_no'">{{
                        sortDir === "asc" ? "↑" : "↓"
                      }}</span>
                    </th>
                    <th
                      class="cursor-pointer px-3 py-2 text-right text-xs font-semibold uppercase"
                      @click="toggleSort('ssp_warrant_amt')"
                    >
                      Warrant Amt
                      <span v-if="sortBy === 'ssp_warrant_amt'">{{
                        sortDir === "asc" ? "↑" : "↓"
                      }}</span>
                    </th>
                    <th
                      class="cursor-pointer px-3 py-2 text-right text-xs font-semibold uppercase"
                      @click="toggleSort('deduction')"
                    >
                      Deduction
                      <span v-if="sortBy === 'deduction'">{{
                        sortDir === "asc" ? "↑" : "↓"
                      }}</span>
                    </th>
                    <th
                      class="cursor-pointer px-3 py-2 text-right text-xs font-semibold uppercase"
                      @click="toggleSort('balance')"
                    >
                      Balance
                      <span v-if="sortBy === 'balance'">{{
                        sortDir === "asc" ? "↑" : "↓"
                      }}</span>
                    </th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-if="loading">
                    <td colspan="11" class="px-3 py-6 text-center text-sm text-slate-500">
                      Loading...
                    </td>
                  </tr>
                  <tr v-else-if="rows.length === 0">
                    <td colspan="11" class="px-3 py-6 text-center text-sm text-slate-500">
                      No records found.
                    </td>
                  </tr>
                  <tr
                    v-for="row in rows"
                    :key="`${row.stdStudentId}-${row.sspReferenceNo ?? ''}-${row.index}`"
                    class="border-b border-slate-100 hover:bg-slate-50"
                  >
                    <td class="px-3 py-2">{{ row.index }}</td>
                    <td class="whitespace-nowrap px-3 py-2 font-medium text-slate-900">
                      {{ row.stdStudentId }}
                    </td>
                    <td class="px-3 py-2">{{ row.stdStudentName ?? "-" }}</td>
                    <td class="whitespace-nowrap px-3 py-2">{{ row.icPassport ?? "-" }}</td>
                    <td class="px-3 py-2">{{ row.studStatus ?? "-" }}</td>
                    <td class="px-3 py-2">{{ row.stdProgramLevel ?? "-" }}</td>
                    <td class="whitespace-nowrap px-3 py-2">
                      {{ row.sspReferenceNo ?? "-" }}
                    </td>
                    <td class="whitespace-nowrap px-3 py-2">
                      {{ row.sspWarrantNo ?? "-" }}
                    </td>
                    <td class="px-3 py-2 text-right tabular-nums">
                      {{ fmt(row.sspWarrantAmt) }}
                    </td>
                    <td class="px-3 py-2 text-right tabular-nums">
                      {{ fmt(row.deduction) }}
                    </td>
                    <td class="px-3 py-2 text-right tabular-nums">
                      {{ fmt(row.balance) }}
                    </td>
                  </tr>
                </tbody>
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
              <button
                type="button"
                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium"
                @click="handleDownloadPDF"
              >
                <Download class="h-3.5 w-3.5" />PDF
              </button>
              <button
                type="button"
                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium"
                @click="handleDownloadCSV"
              >
                <FileDown class="h-3.5 w-3.5" />CSV
              </button>
              <button
                type="button"
                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium"
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
                  v-model="smartFilter.matric"
                  type="text"
                  class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
                />
              </div>
              <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Name</label>
                <input
                  v-model="smartFilter.name"
                  type="text"
                  class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
                />
              </div>
              <div>
                <label class="mb-1 block text-sm font-medium text-slate-700"
                  >NRIC/Passport</label
                >
                <input
                  v-model="smartFilter.icPassport"
                  type="text"
                  class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
                />
              </div>
              <div>
                <label class="mb-1 block text-sm font-medium text-slate-700"
                  >Program Level</label
                >
                <select
                  v-model="smartFilter.programLevel"
                  class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
                >
                  <option value="">Any</option>
                  <option v-for="opt in options.programLevel" :key="opt.id" :value="opt.id">
                    {{ opt.label }}
                  </option>
                </select>
              </div>
              <div>
                <label class="mb-1 block text-sm font-medium text-slate-700"
                  >No. Fail Pinjaman</label
                >
                <input
                  v-model="smartFilter.referenceNo"
                  type="text"
                  class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
                />
              </div>
              <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Warrant No.</label>
                <input
                  v-model="smartFilter.warrantNo"
                  type="text"
                  class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
                />
              </div>
              <div class="md:col-span-2">
                <label class="mb-1 block text-sm font-medium text-slate-700">Status</label>
                <select
                  v-model="smartFilter.studentStatus"
                  class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
                >
                  <option value="">Any</option>
                  <option
                    v-for="opt in options.studentStatus"
                    :key="opt.id"
                    :value="opt.id"
                  >
                    {{ opt.label }}
                  </option>
                </select>
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
