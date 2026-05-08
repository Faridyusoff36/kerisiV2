<script setup lang="ts">
/**
 * Student Finance / Sponsor / Profile (PAGEID 845 / MENUID 1025).
 *
 * Source: FIMS BL `V2_SFSP_SPONSOR_API`. Read-only datatable + smart
 * filter on the `sponsor` master.
 *
 * Smart filter mirrors the legacy contract:
 *   - sponsor    — LIKE %...% on CONCAT(code,' - ',name)
 *   - country    — LIKE %...% case-insensitive on
 *                  spn_extended_field->>'$.spn_country_desc'
 *   - email      — LIKE %...% on spn_email
 *   - spn_status — LIKE %...% on spn_status_cd
 *
 * The legacy COMPONENT_JS exposes Edit / View / Delete / Assign-Student
 * actions deep-linking to legacy menuID=1068 (Sponsor form) and 1478
 * (Sponsor → Assign Student); neither destination is migrated yet, so
 * all four Action buttons render as disabled with "not migrated"
 * tooltips. (Edit/Delete are also legacy-disabled when row.hasChild is
 * truthy — we keep that constraint visible.)
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
import { getSponsorProfileOptions, listSponsorProfile } from "@/api/cms";
import { useToast } from "@/composables/useToast";
import type {
  SponsorProfileOptions,
  SponsorProfileRow,
  SponsorProfileSmartFilter,
} from "@/types";

const toast = useToast();
const datatableRef = ref<DatatableRefApi | null>(null);

const rows = ref<SponsorProfileRow[]>([]);
const loading = ref(false);
const total = ref(0);
const page = ref(1);
const limit = ref(10);
const q = ref("");

type ProfileSortKey = "sponsor" | "email" | "spn_status" | "status_of_invoice";
const sortBy = ref<ProfileSortKey>("sponsor");
const sortDir = ref<"asc" | "desc">("asc");

const showSmartFilter = ref(false);
const smartFilter = ref<SponsorProfileSmartFilter>({
  sponsor: "",
  country: "",
  email: "",
  sponStatus: "",
});

const options = ref<SponsorProfileOptions>({ status: [] });

const totalPages = computed(() =>
  total.value ? Math.max(1, Math.ceil(total.value / limit.value)) : 1,
);
const startIdx = computed(() =>
  total.value === 0 ? 0 : (page.value - 1) * limit.value + 1,
);
const endIdx = computed(() => Math.min(page.value * limit.value, total.value));

async function loadOptions() {
  try {
    const res = await getSponsorProfileOptions();
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
  if (smartFilter.value.sponsor) params.set("sponsor", smartFilter.value.sponsor);
  if (smartFilter.value.country) params.set("country", smartFilter.value.country);
  if (smartFilter.value.email) params.set("email", smartFilter.value.email);
  if (smartFilter.value.sponStatus) params.set("spn_status", smartFilter.value.sponStatus);

  try {
    const res = await listSponsorProfile(`?${params.toString()}`);
    rows.value = res.data;
    total.value = Number(res.meta?.total ?? 0);
  } catch (e) {
    toast.error(
      "Load failed",
      e instanceof Error ? e.message : "Unable to load sponsor profile list.",
    );
  } finally {
    loading.value = false;
  }
}

function toggleSort(col: ProfileSortKey) {
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
  smartFilter.value = { sponsor: "", country: "", email: "", sponStatus: "" };
}

const exportColumns = [
  "Sponsor",
  "Contact Name 1",
  "Contact Number 1",
  "Contact Name 2",
  "Contact Number 2",
  "Contact Name 3",
  "Contact Number 3",
  "Email",
  "Status",
  "Status of Invoice",
];

const {
  templateFileInputRef,
  onTemplateFileChange,
  handleDownloadPDF,
  handleDownloadCSV,
} = useDatatableFeatures({
  pageName: "List of Sponsor",
  apiDataPath: "/student-finance/sponsor-profile",
  defaultExportColumns: exportColumns,
  getFilteredList: () =>
    rows.value.map((r) => ({
      Sponsor: r.sponsor ?? "",
      "Contact Name 1": r.spnContactPerson ?? "",
      "Contact Number 1": r.spnContactNo ?? "",
      "Contact Name 2": r.spnContactPerson2 ?? "",
      "Contact Number 2": r.spnContactNo2 ?? "",
      "Contact Name 3": r.spnContactPerson3 ?? "",
      "Contact Number 3": r.spnContactNo3 ?? "",
      Email: r.email ?? "",
      Status: r.sponStatus ?? "",
      "Status of Invoice": r.statusOfInvoice ?? "",
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
    const ws = wb.addWorksheet("Sponsor Profile");
    ws.addRow(["No", ...exportColumns]);
    rows.value.forEach((r, idx) => {
      ws.addRow([
        idx + 1,
        r.sponsor ?? "",
        r.spnContactPerson ?? "",
        r.spnContactNo ?? "",
        r.spnContactPerson2 ?? "",
        r.spnContactNo2 ?? "",
        r.spnContactPerson3 ?? "",
        r.spnContactNo3 ?? "",
        r.email ?? "",
        r.sponStatus ?? "",
        r.statusOfInvoice ?? "",
      ]);
    });
    const buf = await wb.xlsx.writeBuffer();
    const blob = new Blob([buf], {
      type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
    });
    const url = URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.href = url;
    a.download = `SponsorProfile_${new Date().toISOString().slice(0, 10)}.xlsx`;
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

      <h1 class="page-title">Student Finance / Sponsor / Profile</h1>

      <article class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
          <h1 class="text-base font-semibold text-slate-900">List of Sponsor</h1>
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
              <table class="w-full min-w-[1000px] text-sm">
                <thead class="sticky top-0 bg-slate-50">
                  <tr class="border-b border-slate-200 text-left">
                    <th class="px-3 py-2 text-xs font-semibold uppercase">No.</th>
                    <th
                      class="cursor-pointer px-3 py-2 text-xs font-semibold uppercase"
                      @click="toggleSort('sponsor')"
                    >
                      Sponsor
                      <span v-if="sortBy === 'sponsor'">{{
                        sortDir === "asc" ? "↑" : "↓"
                      }}</span>
                    </th>
                    <th class="px-3 py-2 text-xs font-semibold uppercase">Contact Person</th>
                    <th
                      class="cursor-pointer px-3 py-2 text-xs font-semibold uppercase"
                      @click="toggleSort('email')"
                    >
                      Email
                      <span v-if="sortBy === 'email'">{{
                        sortDir === "asc" ? "↑" : "↓"
                      }}</span>
                    </th>
                    <th
                      class="cursor-pointer px-3 py-2 text-xs font-semibold uppercase"
                      @click="toggleSort('spn_status')"
                    >
                      Status
                      <span v-if="sortBy === 'spn_status'">{{
                        sortDir === "asc" ? "↑" : "↓"
                      }}</span>
                    </th>
                    <th
                      class="cursor-pointer px-3 py-2 text-xs font-semibold uppercase"
                      @click="toggleSort('status_of_invoice')"
                    >
                      Status of Invoice
                      <span v-if="sortBy === 'status_of_invoice'">{{
                        sortDir === "asc" ? "↑" : "↓"
                      }}</span>
                    </th>
                    <th class="px-3 py-2 text-xs font-semibold uppercase">Action</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-if="loading">
                    <td colspan="7" class="px-3 py-6 text-center text-sm text-slate-500">
                      Loading...
                    </td>
                  </tr>
                  <tr v-else-if="rows.length === 0">
                    <td colspan="7" class="px-3 py-6 text-center text-sm text-slate-500">
                      No records found.
                    </td>
                  </tr>
                  <tr
                    v-for="row in rows"
                    :key="`sp-${row.spnSponsorId}-${row.index}`"
                    class="border-b border-slate-100 hover:bg-slate-50"
                  >
                    <td class="px-3 py-2">{{ row.index }}</td>
                    <td class="px-3 py-2 font-medium text-slate-900">{{ row.sponsor ?? "-" }}</td>
                    <td class="px-3 py-2">
                      <div v-if="row.spnContactPerson" class="text-xs text-slate-700">
                        {{ row.spnContactPerson
                        }}<span v-if="row.spnContactNo" class="ml-1 text-slate-500"
                          >· {{ row.spnContactNo }}</span
                        >
                      </div>
                      <div v-if="row.spnContactPerson2" class="text-xs text-slate-700">
                        {{ row.spnContactPerson2
                        }}<span v-if="row.spnContactNo2" class="ml-1 text-slate-500"
                          >· {{ row.spnContactNo2 }}</span
                        >
                      </div>
                      <div v-if="row.spnContactPerson3" class="text-xs text-slate-700">
                        {{ row.spnContactPerson3
                        }}<span v-if="row.spnContactNo3" class="ml-1 text-slate-500"
                          >· {{ row.spnContactNo3 }}</span
                        >
                      </div>
                      <span
                        v-if="
                          !row.spnContactPerson && !row.spnContactPerson2 && !row.spnContactPerson3
                        "
                        class="text-slate-400"
                        >-</span
                      >
                    </td>
                    <td class="px-3 py-2">{{ row.email ?? "-" }}</td>
                    <td class="px-3 py-2">{{ row.sponStatus ?? "-" }}</td>
                    <td class="px-3 py-2">{{ row.statusOfInvoice ?? "-" }}</td>
                    <td class="whitespace-nowrap px-3 py-2 text-slate-400">
                      <span
                        :title="
                          row.hasChild
                            ? 'Sponsor has assigned students; legacy disables Edit'
                            : 'Sponsor form (legacy MENUID 1068) is not migrated yet'
                        "
                      >
                        <button
                          type="button"
                          disabled
                          class="mr-1 inline-flex cursor-not-allowed items-center gap-1 rounded border border-slate-200 px-2 py-0.5 text-xs opacity-60"
                        >
                          Edit
                        </button>
                      </span>
                      <span
                        title="Sponsor form (legacy MENUID 1068) is not migrated yet"
                      >
                        <button
                          type="button"
                          disabled
                          class="mr-1 inline-flex cursor-not-allowed items-center gap-1 rounded border border-slate-200 px-2 py-0.5 text-xs opacity-60"
                        >
                          View
                        </button>
                      </span>
                      <span
                        :title="
                          row.hasChild
                            ? 'Sponsor has assigned students; legacy disables Delete'
                            : 'Sponsor delete API is not migrated yet'
                        "
                      >
                        <button
                          type="button"
                          disabled
                          class="mr-1 inline-flex cursor-not-allowed items-center gap-1 rounded border border-slate-200 px-2 py-0.5 text-xs opacity-60"
                        >
                          Delete
                        </button>
                      </span>
                      <span
                        title="Assign Student page (legacy MENUID 1478) is not migrated yet"
                      >
                        <button
                          type="button"
                          disabled
                          class="inline-flex cursor-not-allowed items-center gap-1 rounded border border-slate-200 px-2 py-0.5 text-xs opacity-60"
                        >
                          Assign
                        </button>
                      </span>
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
            <div>
              <label class="mb-1 block text-sm font-medium text-slate-700">Sponsor</label>
              <input
                v-model="smartFilter.sponsor"
                type="text"
                placeholder="Code or name"
                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
              />
            </div>
            <div>
              <label class="mb-1 block text-sm font-medium text-slate-700">Country</label>
              <input
                v-model="smartFilter.country"
                type="text"
                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
              />
            </div>
            <div>
              <label class="mb-1 block text-sm font-medium text-slate-700">Email</label>
              <input
                v-model="smartFilter.email"
                type="text"
                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
              />
            </div>
            <div>
              <label class="mb-1 block text-sm font-medium text-slate-700">Status</label>
              <select
                v-model="smartFilter.sponStatus"
                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
              >
                <option value="">Any</option>
                <option v-for="opt in options.status" :key="opt.id" :value="opt.id">
                  {{ opt.label }}
                </option>
              </select>
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
