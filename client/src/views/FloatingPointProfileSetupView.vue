<script setup lang="ts">
/**
 * Setup & Maintenance → General Ledger Structure → Floating Point for Profile Setup
 * (PAGEID 1943 / MENUID 2375).
 *
 * Source: `HIDDEN_PAGE_LEVEL3.json` — datatable + smart filter (`dt_filter: default`);
 * legacy BL `NAD_API_PTJCOSTCENTER_LISTING` (`dt_listingProfile=1`).
 * On-load JS `SNA_JS_PTJCOSTCENTER_LISTING`: hide duplicate activity column group for
 * non-tourism installs (render a single activity block here).
 *
 * Kitchensink: "Datatable — smart filter pattern"; read-only rows with View → Project
 * Profile So Code (menu 1615 placeholder). Default PDF / CSV / Excel exports.
 */
import { computed, onMounted, onUnmounted, ref, watch } from "vue";
import { useRouter } from "vue-router";
import {
  Download,
  Eye,
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
import { listProfileFloatingPointListing } from "@/api/cms";
import { useToast } from "@/composables/useToast";
import type { ProfileFloatingPointRow, ProfileFloatingPointSmartFilter } from "@/types";

const router = useRouter();
const toast = useToast();
const datatableRef = ref<DatatableRefApi | null>(null);

type SortKey =
  | "ouc_ounit_costcentre_id"
  | "fty_fund_type"
  | "fty_fund_desc"
  | "at_activity_code"
  | "at_activity_description_bm"
  | "oun_code"
  | "oun_desc"
  | "ccr_costcentre"
  | "ccr_costcentre_desc"
  | "ouc_status_display"
  | "datecreate";

const rows = ref<ProfileFloatingPointRow[]>([]);
const page = ref(1);
const limit = ref(10);
const q = ref("");
const total = ref(0);
const loading = ref(false);
const sortBy = ref<SortKey>("ouc_ounit_costcentre_id");
const sortDir = ref<"asc" | "desc">("desc");

const showSmartFilter = ref(false);
const smartFilter = ref<ProfileFloatingPointSmartFilter>({
  ftyFundType: "",
  atActivityCode: "",
  ounCode: "",
  ccrCostcentre: "",
  oucStatus: "",
});

const totalPages = computed(() =>
  total.value ? Math.max(1, Math.ceil(total.value / limit.value)) : 1,
);
const startIdx = computed(() =>
  total.value === 0 ? 0 : (page.value - 1) * limit.value + 1,
);
const endIdx = computed(() => Math.min(page.value * limit.value, total.value));

function goView(_row: ProfileFloatingPointRow) {
  /** Profile edits are keyed by capital project number on MENUID 1615; org-unit-costcentre listing does not imply a CPA project row. Open the editor for manual lookup / save. */
  void router.push({
    name: "kerisi-setup-gl-project-profile-so-code",
  });
}

async function loadRows() {
  loading.value = true;
  const params = new URLSearchParams({
    page: String(page.value),
    limit: String(limit.value),
    sort_by: sortBy.value,
    sort_dir: sortDir.value,
    ...(q.value ? { q: q.value } : {}),
    ...(smartFilter.value.ftyFundType ? { fty_fund_type_filter: smartFilter.value.ftyFundType } : {}),
    ...(smartFilter.value.atActivityCode ? { at_activity_code_filter: smartFilter.value.atActivityCode } : {}),
    ...(smartFilter.value.ounCode ? { oun_code_filter: smartFilter.value.ounCode } : {}),
    ...(smartFilter.value.ccrCostcentre ? { ccr_costcentre_filter: smartFilter.value.ccrCostcentre } : {}),
    ...(smartFilter.value.oucStatus ? { ouc_status_filter: smartFilter.value.oucStatus } : {}),
  });
  try {
    const res = await listProfileFloatingPointListing(`?${params.toString()}`);
    rows.value = res.data ?? [];
    total.value = Number(res.meta?.total ?? 0);
  } catch (e) {
    toast.error("Load failed", e instanceof Error ? e.message : "Unable to load listing.");
    rows.value = [];
    total.value = 0;
  } finally {
    loading.value = false;
  }
}

function toggleSort(col: SortKey) {
  if (sortBy.value === col) sortDir.value = sortDir.value === "asc" ? "desc" : "asc";
  else {
    sortBy.value = col;
    sortDir.value = col === "datecreate" ? "desc" : "asc";
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
    ftyFundType: "",
    atActivityCode: "",
    ounCode: "",
    ccrCostcentre: "",
    oucStatus: "",
  };
}

function asExportRow(r: ProfileFloatingPointRow) {
  return {
    Fund: r.ftyFundType ?? "",
    "Fund Desc": r.ftyFundDesc ?? "",
    Activity: r.atActivityCode ?? "",
    "Activity Desc": r.atActivityDescriptionBm ?? "",
    PTJ: r.ounCode ?? "",
    "PTJ Desc": r.ounDesc ?? "",
    "Cost Centre": r.ccrCostcentre ?? "",
    "Cost Centre Desc": r.ccrCostcentreDesc ?? "",
    Status: r.oucStatus ?? "",
    "Date Created": r.datecreate ?? "",
  };
}

const DEFAULT_EXPORT_COLUMNS = [
  "Fund",
  "Fund Desc",
  "Activity",
  "Activity Desc",
  "PTJ",
  "PTJ Desc",
  "Cost Centre",
  "Cost Centre Desc",
  "Status",
  "Date Created",
];

const { templateFileInputRef, onTemplateFileChange, handleDownloadPDF, handleDownloadCSV } =
  useDatatableFeatures({
    pageName: "Profile Setup Listing — Floating Point",
    apiDataPath: "/general-ledger/profile-floating-point-listing",
    defaultExportColumns: DEFAULT_EXPORT_COLUMNS,
    getFilteredList: () => rows.value.map(asExportRow),
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
    const ws = wb.addWorksheet("Profile Setup");
    const cols = DEFAULT_EXPORT_COLUMNS;
    ws.addRow(["No", ...cols]);
    rows.value.forEach((r, idx) => {
      const e = asExportRow(r);
      ws.addRow([
        idx + 1,
        e.Fund,
        e["Fund Desc"],
        e.Activity,
        e["Activity Desc"],
        e.PTJ,
        e["PTJ Desc"],
        e["Cost Centre"],
        e["Cost Centre Desc"],
        e.Status,
        e["Date Created"],
      ]);
    });
    const buf = await wb.xlsx.writeBuffer();
    const blob = new Blob([buf], {
      type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
    });
    const url = URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.href = url;
    a.download = `Floating_Point_Profile_Setup_${new Date().toISOString().slice(0, 10)}.xlsx`;
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
  void loadRows();
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
      <h1 class="page-title">
        Setup and Maintenance / General Ledger Structure / Floating Point for Profile Setup
      </h1>

      <article class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
          <h1 class="text-base font-semibold text-slate-900">
            Floating Point for Profile Setup
          </h1>
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
                  placeholder="Filter rows…"
                  class="w-56 rounded-lg border border-slate-300 py-1.5 pl-8 pr-8 text-sm"
                  @keyup.enter="page = 1; void loadRows()"
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
              <button
                type="button"
                class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm"
                @click="showSmartFilter = true"
              >
                <Filter class="h-4 w-4" />Filter
              </button>
            </div>
          </div>

          <div class="overflow-x-auto rounded-lg border border-slate-200">
            <div ref="datatableRef" :class="rows.length > 10 ? 'max-h-[420px] overflow-y-auto' : ''">
              <table class="admin-table-kitchen w-full min-w-[1100px] text-sm">
                <thead class="admin-table-thead-sticky">
                  <tr class="border-b border-slate-200 text-left">
                    <th
                      class="cursor-pointer px-3 py-2 text-xs font-semibold uppercase"
                      @click="toggleSort('fty_fund_type')"
                    >
                      Fund
                    </th>
                    <th
                      class="cursor-pointer px-3 py-2 text-xs font-semibold uppercase"
                      @click="toggleSort('fty_fund_desc')"
                    >
                      Fund Desc
                    </th>
                    <th
                      class="cursor-pointer px-3 py-2 text-xs font-semibold uppercase"
                      @click="toggleSort('at_activity_code')"
                    >
                      Activity
                    </th>
                    <th
                      class="cursor-pointer px-3 py-2 text-xs font-semibold uppercase"
                      @click="toggleSort('at_activity_description_bm')"
                    >
                      Activity Desc
                    </th>
                    <th class="cursor-pointer px-3 py-2 text-xs font-semibold uppercase" @click="toggleSort('oun_code')">
                      PTJ
                    </th>
                    <th class="cursor-pointer px-3 py-2 text-xs font-semibold uppercase" @click="toggleSort('oun_desc')">
                      PTJ Desc
                    </th>
                    <th
                      class="cursor-pointer px-3 py-2 text-xs font-semibold uppercase"
                      @click="toggleSort('ccr_costcentre')"
                    >
                      Cost Center
                    </th>
                    <th
                      class="cursor-pointer px-3 py-2 text-xs font-semibold uppercase"
                      @click="toggleSort('ccr_costcentre_desc')"
                    >
                      Cost Center Desc
                    </th>
                    <th
                      class="cursor-pointer px-3 py-2 text-xs font-semibold uppercase"
                      @click="toggleSort('ouc_status_display')"
                    >
                      Status
                    </th>
                    <th
                      class="cursor-pointer px-3 py-2 text-xs font-semibold uppercase"
                      @click="toggleSort('datecreate')"
                    >
                      Date Created
                    </th>
                    <th class="px-3 py-2 text-center text-xs font-semibold uppercase">Action</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-if="loading">
                    <td colspan="11" class="px-3 py-6 text-center text-sm text-slate-500">
                      Loading…
                    </td>
                  </tr>
                  <tr v-else-if="rows.length === 0">
                    <td colspan="11" class="px-3 py-6 text-center text-sm text-slate-500">
                      No records found.
                    </td>
                  </tr>
                  <template v-else>
                    <tr
                      v-for="row in rows"
                      :key="row.oucOunitCostcentreId"
                      class="border-b border-slate-100 hover:bg-slate-50"
                    >
                      <td class="px-3 py-2">{{ row.ftyFundType }}</td>
                      <td class="px-3 py-2">{{ row.ftyFundDesc }}</td>
                      <td class="px-3 py-2">{{ row.atActivityCode }}</td>
                      <td class="px-3 py-2">{{ row.atActivityDescriptionBm }}</td>
                      <td class="px-3 py-2">{{ row.ounCode }}</td>
                      <td class="px-3 py-2">{{ row.ounDesc }}</td>
                      <td class="px-3 py-2">{{ row.ccrCostcentre }}</td>
                      <td class="px-3 py-2">{{ row.ccrCostcentreDesc }}</td>
                      <td class="px-3 py-2">{{ row.oucStatus }}</td>
                      <td class="px-3 py-2">{{ row.datecreate }}</td>
                      <td class="px-3 py-2 text-center">
                        <button
                          type="button"
                          class="inline-flex rounded p-1 text-sky-700 hover:bg-sky-50"
                          title="View"
                          @click="goView(row)"
                        >
                          <Eye class="h-4 w-4" />
                        </button>
                      </td>
                    </tr>
                  </template>
                </tbody>
              </table>
            </div>
          </div>

          <div class="flex items-center justify-between text-sm text-slate-500">
            <div>Showing {{ startIdx }}-{{ endIdx }} of {{ total }}</div>
            <div class="flex items-center gap-2">
              <button
                type="button"
                class="rounded border border-slate-300 px-2 py-1 hover:bg-slate-50 disabled:opacity-40"
                :disabled="page <= 1"
                @click="prevPage"
              >
                Prev
              </button>
              <span>
                Page {{ page }} /
                {{ totalPages }}
              </span>
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
          <div class="flex flex-wrap items-center justify-end gap-2 border-t border-slate-100 pt-3">
            <button
              type="button"
              class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-2 py-1 text-xs hover:bg-slate-50"
              @click="handleDownloadPDF()"
            >
              <FileDown class="h-3.5 w-3.5" />
              PDF
            </button>
            <button
              type="button"
              class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-2 py-1 text-xs hover:bg-slate-50"
              @click="handleDownloadCSV()"
            >
              <Download class="h-3.5 w-3.5" />
              CSV
            </button>
            <button
              type="button"
              class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-2 py-1 text-xs hover:bg-slate-50"
              @click="exportExcel"
            >
              <FileSpreadsheet class="h-3.5 w-3.5" />
              Excel
            </button>
          </div>
        </div>
      </article>
    </div>

    <Teleport to="body">
      <div
        v-if="showSmartFilter"
        class="fixed inset-0 z-[100] flex items-center justify-center bg-black/40 p-4"
        @keydown.esc="
          showSmartFilter = false;
          resetSmartFilter();
        "
      >
        <div
          class="w-full max-w-lg rounded-lg border border-slate-200 bg-white p-6 shadow-xl"
          @click.stop
        >
          <h2 class="text-base font-semibold text-slate-900">Smart filter</h2>
          <p class="mt-1 text-xs text-slate-500">
            Match cascade rows (fund, activity, PTJ, cost centre, status).
          </p>
          <div class="mt-4 grid gap-3 text-sm">
            <label class="block space-y-1">
              <span class="text-xs font-medium text-slate-700">Fund type</span>
              <input
                v-model="smartFilter.ftyFundType"
                type="text"
                class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-sm"
                placeholder="Fund code…"
              />
            </label>
            <label class="block space-y-1">
              <span class="text-xs font-medium text-slate-700">Activity code</span>
              <input
                v-model="smartFilter.atActivityCode"
                type="text"
                class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-sm"
                placeholder="Activity code…"
              />
            </label>
            <label class="block space-y-1">
              <span class="text-xs font-medium text-slate-700">PTJ code</span>
              <input
                v-model="smartFilter.ounCode"
                type="text"
                class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-sm"
                placeholder="PTJ…"
              />
            </label>
            <label class="block space-y-1">
              <span class="text-xs font-medium text-slate-700">Cost centre</span>
              <input
                v-model="smartFilter.ccrCostcentre"
                type="text"
                class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-sm"
                placeholder="Cost centre…"
              />
            </label>
            <label class="block space-y-1">
              <span class="text-xs font-medium text-slate-700">Status</span>
              <select
                v-model="smartFilter.oucStatus"
                class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-sm"
              >
                <option value="">Any</option>
                <option value="ACTIVE">ACTIVE</option>
                <option value="INACTIVE">INACTIVE</option>
              </select>
            </label>
          </div>
          <div class="mt-6 flex justify-end gap-2">
            <button
              type="button"
              class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm"
              @click="
                resetSmartFilter();
                showSmartFilter = false;
              "
            >
              Reset
            </button>
            <button
              type="button"
              class="rounded-lg bg-sky-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-sky-700"
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
