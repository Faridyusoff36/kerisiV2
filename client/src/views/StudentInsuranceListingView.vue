<script setup lang="ts">
/**
 * Student Finance / Insurance — shared list UI for:
 *   - MENUID 1039 — Insurance : Returning Student (PAGEID 859, `api/DT_RETURNSTUDENT_LIST`).
 *   - MENUID 2797 — List … insurance invoice at iFAS (BL `MZ_BL_SF_REPORT_IFAS`).
 *   - MENUID 2799 — Duplicate / multiple policies (BL `MZ_BL_SF_INS_MULTIPLE_REPORT`).
 *
 * Smart filter & columns match PAGE_MENUID1019_LEVEL3.json. Legacy COMPONENT_JS is
 * unrelated boilerplate; exports default to PDF / CSV / Excel per project policy.
 * Edit icon (1039 only): insurance maintenance form + POST flows are not migrated yet.
 */
import { computed, onMounted, onUnmounted, ref, watch } from "vue";
import { useRoute } from "vue-router";
import {
  Download,
  FileDown,
  FileSpreadsheet,
  Filter,
  MoreVertical,
  Pencil,
  Search,
  X,
} from "lucide-vue-next";
import AdminLayout from "@/layouts/AdminLayout.vue";
import {
  getStudentInsuranceListingOptions,
  listStudentInsuranceListing,
} from "@/api/cms";
import { getKerisiMenuTrailByMenuId, parseKerisiNumericMenuIdFromPath } from "@/config/kerisi-menu-resolve";
import { useDatatableFeatures } from "@/composables/useDatatableFeatures";
import type { DatatableRefApi } from "@/composables/useDatatableFeatures";
import { useToast } from "@/composables/useToast";
import type {
  StudentInsuranceListingOptions,
  StudentInsuranceListingRow,
  StudentInsuranceListingSmartFilter,
} from "@/types";

const toast = useToast();
const route = useRoute();
const datatableRef = ref<DatatableRefApi | null>(null);

const menuId = computed(() => parseKerisiNumericMenuIdFromPath(route.path));

const listingVariant = computed(() => {
  switch (menuId.value) {
    case 2797:
      return "ifas";
    case 2799:
      return "duplicate";
    default:
      return "returning";
  }
});

const showActionColumn = computed(() => menuId.value === 1039);

const pageTitle = computed(() => {
  const id = menuId.value;
  if (id === null) return "Student Finance / Insurance";
  const trail = getKerisiMenuTrailByMenuId(id);
  return trail?.length ? trail.join(" / ") : `Student Finance / Menu ${id}`;
});

const cardHeading = computed(() => {
  switch (menuId.value) {
    case 2797:
      return "List of returning student whose insurance invoice at iFAS";
    case 2799:
      return "List of new/returning student whose insurance is duplicate/multiple";
    default:
      return "List of New/Returning Student";
  }
});

const rows = ref<StudentInsuranceListingRow[]>([]);
const loading = ref(false);
const total = ref(0);
const page = ref(1);
const limit = ref(10);
const q = ref("");

type InsuranceSortKey =
  | "matric"
  | "name"
  | "status"
  | "program_level"
  | "semester"
  | "ins_inst"
  | "policy";

const sortBy = ref<InsuranceSortKey>("matric");
const sortDir = ref<"asc" | "desc">("asc");

const showSmartFilter = ref(false);
const smartFilter = ref<StudentInsuranceListingSmartFilter>({
  programLevel: "",
  semesterStart: "",
});

const options = ref<StudentInsuranceListingOptions>({
  programLevel: [],
  semesterStart: [],
});

const totalPages = computed(() =>
  total.value ? Math.max(1, Math.ceil(total.value / limit.value)) : 1,
);
const startIdx = computed(() =>
  total.value === 0 ? 0 : (page.value - 1) * limit.value + 1,
);
const endIdx = computed(() => Math.min(page.value * limit.value, total.value));

async function loadOptions() {
  try {
    const res = await getStudentInsuranceListingOptions();
    options.value = res.data;
  } catch (e) {
    toast.error(
      "Load failed",
      e instanceof Error ? e.message : "Unable to load filter options.",
    );
  }
}

async function loadRows() {
  loading.value = true;
  const params = new URLSearchParams({
    page: String(page.value),
    limit: String(limit.value),
    sort_by: sortBy.value,
    sort_dir: sortDir.value,
    variant: listingVariant.value,
  });
  if (q.value.trim()) params.set("q", q.value.trim());
  if (smartFilter.value.programLevel)
    params.set("std_program_level", smartFilter.value.programLevel);
  if (smartFilter.value.semesterStart)
    params.set("std_intake_semester", smartFilter.value.semesterStart);

  try {
    const res = await listStudentInsuranceListing(`?${params.toString()}`);
    rows.value = res.data;
    total.value = Number(res.meta?.total ?? 0);
  } catch (e) {
    toast.error(
      "Load failed",
      e instanceof Error ? e.message : "Unable to load list.",
    );
  } finally {
    loading.value = false;
  }
}

function toggleSort(col: InsuranceSortKey) {
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
    programLevel: "",
    semesterStart: "",
  };
}

function onEdit(matric: string) {
  toast.info(
    "Edit",
    `Policy maintenance for ${matric} is not migrated yet (legacy modal + POST).`,
  );
}

const exportColumns = [
  "Matric No",
  "Name",
  "Status",
  "Prog Level",
  "Semester Start",
  "Insurance Institution",
  "Policy No",
];

const {
  templateFileInputRef,
  onTemplateFileChange,
  handleDownloadPDF,
  handleDownloadCSV,
} = useDatatableFeatures({
  pageName: "Insurance student list",
  apiDataPath: "/student-finance/insurance-student-list",
  defaultExportColumns: exportColumns,
  getFilteredList: () =>
    rows.value.map((r) => ({
      "Matric No": r.matric,
      Name: r.name ?? "",
      Status: r.statusLabel ?? "",
      "Prog Level": r.programLevelLabel ?? "",
      "Semester Start": r.semesterStart ?? "",
      "Insurance Institution": r.insuranceInstitution ?? "",
      "Policy No": r.policyNo ?? "",
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
    const ws = wb.addWorksheet("Insurance");
    ws.addRow(["No", ...exportColumns]);
    rows.value.forEach((r, idx) => {
      ws.addRow([
        idx + 1,
        r.matric,
        r.name ?? "",
        r.statusLabel ?? "",
        r.programLevelLabel ?? "",
        r.semesterStart ?? "",
        r.insuranceInstitution ?? "",
        r.policyNo ?? "",
      ]);
    });
    const buf = await wb.xlsx.writeBuffer();
    const blob = new Blob([buf], {
      type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
    });
    const url = URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.href = url;
    a.download = `InsuranceList_${new Date().toISOString().slice(0, 10)}.xlsx`;
    a.click();
    URL.revokeObjectURL(url);
    toast.success("Excel downloaded");
  } catch (e) {
    toast.error(
      "Export failed",
      e instanceof Error ? e.message : "Excel export failed.",
    );
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

watch(listingVariant, () => {
  page.value = 1;
  void loadRows();
});

onMounted(async () => {
  await loadOptions();
  await loadRows();
});

onUnmounted(() => {
  if (searchDebounce) clearTimeout(searchDebounce);
});

const colspanBody = computed(() => (showActionColumn.value ? 9 : 8));
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

      <h1 class="page-title">{{ pageTitle }}</h1>

      <article class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
          <h2 class="text-base font-semibold text-slate-900">{{ cardHeading }}</h2>
          <button type="button" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100" aria-label="More">
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
            <div class="flex flex-wrap items-center gap-2">
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
            <div :class="rows.length > 10 ? 'max-h-[420px] overflow-y-auto' : ''">
              <table class="w-full min-w-[960px] text-sm">
                <thead class="sticky top-0 bg-slate-50">
                  <tr class="border-b border-slate-200 text-left">
                    <th class="px-3 py-2 text-xs font-semibold uppercase">No.</th>
                    <th
                      class="cursor-pointer px-3 py-2 text-xs font-semibold uppercase"
                      @click="toggleSort('matric')"
                    >
                      Matric No
                      <span v-if="sortBy === 'matric'">{{ sortDir === "asc" ? "↑" : "↓" }}</span>
                    </th>
                    <th
                      class="cursor-pointer px-3 py-2 text-xs font-semibold uppercase"
                      @click="toggleSort('name')"
                    >
                      Name
                      <span v-if="sortBy === 'name'">{{ sortDir === "asc" ? "↑" : "↓" }}</span>
                    </th>
                    <th
                      class="cursor-pointer px-3 py-2 text-xs font-semibold uppercase"
                      @click="toggleSort('status')"
                    >
                      Status
                      <span v-if="sortBy === 'status'">{{ sortDir === "asc" ? "↑" : "↓" }}</span>
                    </th>
                    <th
                      class="cursor-pointer whitespace-nowrap px-3 py-2 text-xs font-semibold uppercase"
                      @click="toggleSort('program_level')"
                    >
                      Prog Level
                      <span v-if="sortBy === 'program_level'">{{ sortDir === "asc" ? "↑" : "↓" }}</span>
                    </th>
                    <th
                      class="cursor-pointer whitespace-nowrap px-3 py-2 text-xs font-semibold uppercase"
                      @click="toggleSort('semester')"
                    >
                      Semester Start
                      <span v-if="sortBy === 'semester'">{{ sortDir === "asc" ? "↑" : "↓" }}</span>
                    </th>
                    <th
                      class="cursor-pointer px-3 py-2 text-xs font-semibold uppercase"
                      @click="toggleSort('ins_inst')"
                    >
                      Insurance Institution
                      <span v-if="sortBy === 'ins_inst'">{{ sortDir === "asc" ? "↑" : "↓" }}</span>
                    </th>
                    <th
                      class="cursor-pointer px-3 py-2 text-xs font-semibold uppercase"
                      @click="toggleSort('policy')"
                    >
                      Policy No
                      <span v-if="sortBy === 'policy'">{{ sortDir === "asc" ? "↑" : "↓" }}</span>
                    </th>
                    <th v-if="showActionColumn" class="px-3 py-2 text-right text-xs font-semibold uppercase">
                      Action
                    </th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-if="loading">
                    <td :colspan="colspanBody" class="px-3 py-6 text-center text-sm text-slate-500">
                      Loading...
                    </td>
                  </tr>
                  <tr v-else-if="rows.length === 0">
                    <td :colspan="colspanBody" class="px-3 py-6 text-center text-sm text-slate-500">
                      No records found.
                    </td>
                  </tr>
                  <template v-else>
                    <tr
                      v-for="(row, idx) in rows"
                      :key="`${row.matric}-${idx}-${row.policyNo}`"
                      class="border-b border-slate-100 hover:bg-slate-50"
                    >
                    <td class="px-3 py-2">{{ startIdx + idx }}</td>
                    <td class="px-3 py-2 font-medium text-slate-900">{{ row.matric }}</td>
                    <td class="px-3 py-2">{{ row.name || "—" }}</td>
                    <td class="px-3 py-2">{{ row.statusLabel || "—" }}</td>
                    <td class="whitespace-nowrap px-3 py-2">{{ row.programLevelLabel || "—" }}</td>
                    <td class="whitespace-nowrap px-3 py-2">{{ row.semesterStart || "—" }}</td>
                    <td class="px-3 py-2">{{ row.insuranceInstitution || "—" }}</td>
                    <td class="px-3 py-2">{{ row.policyNo || "—" }}</td>
                    <td v-if="showActionColumn" class="px-3 py-2 text-right">
                      <button
                        type="button"
                        class="inline-flex rounded p-1 text-slate-500 hover:bg-slate-100 hover:text-slate-800"
                        title="Edit"
                        aria-label="Edit"
                        @click="onEdit(row.matric)"
                      >
                        <Pencil class="h-4 w-4" />
                      </button>
                    </td>
                  </tr>
                  </template>
                </tbody>
              </table>
            </div>
          </div>

          <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-3">
            <div class="text-xs text-slate-500">Showing {{ startIdx }}-{{ endIdx }} of {{ total }}</div>
            <div class="flex flex-wrap items-center gap-2">
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
          <div class="space-y-4 p-4">
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
              <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Program level</label>
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
                <label class="mb-1 block text-sm font-medium text-slate-700">Semester Start</label>
                <select
                  v-model="smartFilter.semesterStart"
                  class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
                >
                  <option value="">Any</option>
                  <option v-for="opt in options.semesterStart" :key="opt.id" :value="opt.id">
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
