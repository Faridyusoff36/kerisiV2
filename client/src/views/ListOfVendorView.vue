<script setup lang="ts">
/**
 * Purchasing / List of Vendor (PAGEID 1376 / MENUID 1685).
 * Legacy BL: ZR_PURCHASING_VENDORLIST_BL — `vend_customer_supplier` (DB2).
 */
import { computed, onMounted, onUnmounted, ref, watch } from "vue";
import {
  Download,
  Eye,
  FileDown,
  FileSpreadsheet,
  MoreVertical,
  Pencil,
  Search,
  X,
} from "lucide-vue-next";
import AdminLayout from "@/layouts/AdminLayout.vue";
import { listPurchasingVendors } from "@/api/cms";
import { useDatatableFeatures } from "@/composables/useDatatableFeatures";
import type { DatatableRefApi } from "@/composables/useDatatableFeatures";
import { useToast } from "@/composables/useToast";
import type { PurchasingVendorRow } from "@/types";

const props = withDefaults(
  defineProps<{
    pageBreadcrumb?: string;
    cardTitle?: string;
    /** Optional notice (e.g. hidden menu “New Vendor” MENUID 1824). */
    listBanner?: string;
    exportPageName?: string;
  }>(),
  {
    pageBreadcrumb: "Purchasing / List of Vendor",
    cardTitle: "List of Vendor",
    listBanner: "",
    exportPageName: "Purchasing - List of Vendor",
  },
);

const toast = useToast();
const datatableRef = ref<DatatableRefApi | null>(null);

const rows = ref<PurchasingVendorRow[]>([]);
const total = ref(0);
const page = ref(1);
const limit = ref(10);
const q = ref("");
const sortBy = ref<
  "vcs_vendor_code" | "vcs_vendor_name" | "vcs_registration_no" | "vcs_reg_exp_date" | "vcs_vendor_status"
>("vcs_vendor_code");
const sortDir = ref<"asc" | "desc">("asc");
const loading = ref(false);

const totalPages = computed(() => (total.value ? Math.max(1, Math.ceil(total.value / limit.value)) : 1));
const startIdx = computed(() => (total.value === 0 ? 0 : (page.value - 1) * limit.value + 1));
const endIdx = computed(() => Math.min(page.value * limit.value, total.value));

function formatExp(v: string | null): string {
  if (!v) return "-";
  const d = new Date(v);
  if (Number.isNaN(d.getTime())) return v;
  const dd = String(d.getDate()).padStart(2, "0");
  const mm = String(d.getMonth() + 1).padStart(2, "0");
  return `${dd}/${mm}/${d.getFullYear()}`;
}

async function loadRows() {
  loading.value = true;
  const params = new URLSearchParams({
    page: String(page.value),
    limit: String(limit.value),
    sort_by: sortBy.value,
    sort_dir: sortDir.value,
    ...(q.value ? { q: q.value } : {}),
  });
  try {
    const res = await listPurchasingVendors(`?${params.toString()}`);
    rows.value = res.data;
    total.value = Number(res.meta?.total ?? 0);
  } catch (e) {
    toast.error("Load failed", e instanceof Error ? e.message : "Unable to load vendors.");
  } finally {
    loading.value = false;
  }
}

function toggleSort(col: typeof sortBy.value) {
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

const exportColumns = [
  "No",
  "Vendor Code",
  "Vendor Name",
  "Address",
  "Registration No",
  "Registration Expiry Date",
  "Telephone No",
  "Fax No",
  "Contact Person",
  "Creditor",
  "Debtor",
  "Vendor Status",
];

function asExportRow(r: PurchasingVendorRow) {
  return {
    No: r.index,
    "Vendor Code": r.vcsVendorCode ?? "",
    "Vendor Name": r.vcsVendorName ?? "",
    Address: r.vcsAddress ?? "",
    "Registration No": r.vcsRegistrationNo ?? "",
    "Registration Expiry Date": formatExp(r.vcsRegExpDate),
    "Telephone No": r.vcsTelNo ?? "",
    "Fax No": r.vcsFaxNo ?? "",
    "Contact Person": r.vcsContactPerson ?? "",
    Creditor: r.vcsIscreditor,
    Debtor: r.vcsIsdebtor,
    "Vendor Status": r.vcsVendorStatus,
  };
}

const { templateFileInputRef, onTemplateFileChange, handleDownloadPDF, handleDownloadCSV } = useDatatableFeatures({
  pageName: props.exportPageName,
  apiDataPath: "/purchasing/vendors",
  defaultExportColumns: exportColumns,
  getFilteredList: () => rows.value.map(asExportRow),
  datatableRef,
  searchKeyword: q,
  smartFilter: ref({}),
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
    const ws = wb.addWorksheet("Vendors");
    ws.addRow(exportColumns);
    rows.value.forEach((r) => {
      const e = asExportRow(r);
      ws.addRow(exportColumns.map((h) => e[h as keyof typeof e]));
    });
    const buf = await wb.xlsx.writeBuffer();
    const blob = new Blob([buf], { type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" });
    const url = URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.href = url;
    a.download = `List_of_Vendor_${new Date().toISOString().slice(0, 10)}.xlsx`;
    a.click();
    URL.revokeObjectURL(url);
    toast.success("Excel downloaded");
  } catch (e) {
    toast.error("Export failed", e instanceof Error ? e.message : "Excel export failed.");
  }
}

function onView() {
  toast.info("View vendor", "Vendor detail/edit screens are not migrated in this batch.");
}
function onEdit() {
  toast.info("Edit vendor", "Vendor detail/edit screens are not migrated in this batch.");
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
      <h1 class="page-title">{{ pageBreadcrumb }}</h1>

      <article ref="datatableRef" class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <p
          v-if="listBanner"
          class="border-b border-amber-100 bg-amber-50/90 px-4 py-3 text-sm text-amber-950"
        >
          {{ listBanner }}
        </p>
        <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
          <h1 class="text-base font-semibold text-slate-900">{{ cardTitle }}</h1>
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
                @change="page = 1"
              >
                <option v-for="n in [5, 10, 25, 50, 100]" :key="n" :value="n">{{ n }}</option>
              </select>
            </div>
            <div class="flex flex-wrap items-center gap-2">
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
                  @click="q = ''"
                >
                  <X class="h-3.5 w-3.5" />
                </button>
              </div>
            </div>
          </div>

          <div class="overflow-x-auto rounded-lg border border-slate-200">
            <div :class="rows.length > 10 ? 'max-h-[420px] overflow-y-auto' : ''">
              <table class="w-full min-w-[1200px] text-sm">
                <thead class="sticky top-0 bg-slate-50">
                  <tr class="border-b border-slate-200 text-left">
                    <th class="px-3 py-2 text-xs font-semibold uppercase shadow-sm">No</th>
                    <th
                      class="cursor-pointer px-3 py-2 text-xs font-semibold uppercase shadow-sm"
                      @click="toggleSort('vcs_vendor_code')"
                    >
                      Vendor Code
                    </th>
                    <th
                      class="cursor-pointer px-3 py-2 text-xs font-semibold uppercase shadow-sm"
                      @click="toggleSort('vcs_vendor_name')"
                    >
                      Vendor Name
                    </th>
                    <th class="px-3 py-2 text-xs font-semibold uppercase shadow-sm">Address</th>
                    <th
                      class="cursor-pointer px-3 py-2 text-xs font-semibold uppercase shadow-sm"
                      @click="toggleSort('vcs_registration_no')"
                    >
                      Registration No
                    </th>
                    <th
                      class="cursor-pointer px-3 py-2 text-xs font-semibold uppercase shadow-sm"
                      @click="toggleSort('vcs_reg_exp_date')"
                    >
                      Reg. Expiry
                    </th>
                    <th class="px-3 py-2 text-xs font-semibold uppercase shadow-sm">Telephone</th>
                    <th class="px-3 py-2 text-xs font-semibold uppercase shadow-sm">Fax</th>
                    <th class="px-3 py-2 text-xs font-semibold uppercase shadow-sm">Contact</th>
                    <th class="px-3 py-2 text-xs font-semibold uppercase shadow-sm">Creditor</th>
                    <th class="px-3 py-2 text-xs font-semibold uppercase shadow-sm">Debtor</th>
                    <th
                      class="cursor-pointer px-3 py-2 text-xs font-semibold uppercase shadow-sm"
                      @click="toggleSort('vcs_vendor_status')"
                    >
                      Status
                    </th>
                    <th class="px-3 py-2 text-xs font-semibold uppercase shadow-sm">Action</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-if="loading">
                    <td colspan="13" class="px-3 py-6 text-center text-sm text-slate-500">Loading…</td>
                  </tr>
                  <tr v-else-if="rows.length === 0">
                    <td colspan="13" class="px-3 py-6 text-center text-sm text-slate-500">No records.</td>
                  </tr>
                  <tr v-for="row in rows" :key="`${row.vcsVendorCode}-${row.index}`" class="border-b border-slate-100 hover:bg-slate-50">
                    <td class="px-3 py-2">{{ row.index }}</td>
                    <td class="px-3 py-2 font-medium text-slate-900">{{ row.vcsVendorCode ?? "-" }}</td>
                    <td class="px-3 py-2">{{ row.vcsVendorName ?? "-" }}</td>
                    <td class="px-3 py-2 text-slate-600">{{ row.vcsAddress ?? "-" }}</td>
                    <td class="px-3 py-2">{{ row.vcsRegistrationNo ?? "-" }}</td>
                    <td class="px-3 py-2">{{ formatExp(row.vcsRegExpDate) }}</td>
                    <td class="px-3 py-2">{{ row.vcsTelNo ?? "-" }}</td>
                    <td class="px-3 py-2">{{ row.vcsFaxNo ?? "-" }}</td>
                    <td class="px-3 py-2">{{ row.vcsContactPerson ?? "-" }}</td>
                    <td class="px-3 py-2">{{ row.vcsIscreditor }}</td>
                    <td class="px-3 py-2">{{ row.vcsIsdebtor }}</td>
                    <td class="px-3 py-2">{{ row.vcsVendorStatus }}</td>
                    <td class="px-3 py-2">
                      <div class="flex gap-1">
                        <button
                          type="button"
                          class="rounded p-1 text-slate-500 hover:bg-slate-100"
                          title="View"
                          @click="onView"
                        >
                          <Eye class="h-3.5 w-3.5" />
                        </button>
                        <button
                          type="button"
                          class="rounded p-1 text-slate-500 hover:bg-slate-100"
                          title="Edit"
                          @click="onEdit"
                        >
                          <Pencil class="h-3.5 w-3.5" />
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-3">
            <div class="text-xs text-slate-500">Showing {{ startIdx }}-{{ endIdx }} of {{ total }}</div>
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
                <Download class="h-3.5 w-3.5" />
                PDF
              </button>
              <button
                type="button"
                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium"
                @click="handleDownloadCSV"
              >
                <FileDown class="h-3.5 w-3.5" />
                CSV
              </button>
              <button
                type="button"
                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium"
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
