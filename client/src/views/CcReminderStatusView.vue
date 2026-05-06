<script setup lang="ts">
/**
 * Credit Control / Successful Generated Reminder — MENUID 2669.
 */
import { computed, onMounted, onUnmounted, ref, watch } from "vue";
import { Download, FileDown, FileSpreadsheet, Filter, MoreVertical, Search, X } from "lucide-vue-next";
import AdminLayout from "@/layouts/AdminLayout.vue";
import {
  getCreditControlReminderBusinessTypes,
  getCreditControlReminderDebtorCreditorTypes,
  listCreditControlReminderStatus,
} from "@/api/cms";
import { useDatatableFeatures } from "@/composables/useDatatableFeatures";
import type { DatatableRefApi } from "@/composables/useDatatableFeatures";
import { useToast } from "@/composables/useToast";
import type { CreditControlReminderLookupOption, CreditControlReminderStatusRow } from "@/types";

const toast = useToast();
const datatableRef = ref<DatatableRefApi | null>(null);
const rows = ref<CreditControlReminderStatusRow[]>([]);
const page = ref(1);
const limit = ref(10);
const q = ref("");
const total = ref(0);
const loading = ref(false);
const showSmartFilter = ref(false);
const hasSearched = ref(false);

const typeOptions = ref<CreditControlReminderLookupOption[]>([]);
const businessOptions = ref<CreditControlReminderLookupOption[]>([]);

const smartFilter = ref({
  type: "",
  businessType: "",
});

type SortKey = "crm_reminder_date" | "crm_debtor_id" | "crm_debtor_name" | "crm_amt_outstanding";
const sortBy = ref<SortKey>("crm_reminder_date");
const sortDir = ref<"asc" | "desc">("desc");

const totalPages = computed(() =>
  total.value ? Math.max(1, Math.ceil(total.value / limit.value)) : 1,
);

function fmtMoney(n: number | null | undefined): string {
  if (n == null || Number.isNaN(n)) return "";
  return new Intl.NumberFormat("en-MY", { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(n);
}

async function refreshBusinessTypes() {
  businessOptions.value = [];
  const t = smartFilter.value.type.trim();
  if (!t) return;
  try {
    const res = await getCreditControlReminderBusinessTypes(t);
    businessOptions.value = res.data;
  } catch {
    businessOptions.value = [];
  }
}

async function loadRows() {
  const t = smartFilter.value.type.trim();
  const b = smartFilter.value.businessType.trim();
  if (!t || !b) {
    toast.error("Validation", "Debtor/creditor type and business type are required.");
    return;
  }
  loading.value = true;
  const params = new URLSearchParams({
    type: t,
    business_type: b,
    page: String(page.value),
    limit: String(limit.value),
    sort_by: sortBy.value,
    sort_dir: sortDir.value,
    ...(q.value.trim() ? { q: q.value.trim() } : {}),
  });
  try {
    const res = await listCreditControlReminderStatus(`?${params.toString()}`);
    rows.value = res.data;
    total.value = Number(res.meta?.total ?? 0);
  } catch (e) {
    toast.error("Load failed", e instanceof Error ? e.message : "Unable to load reminders.");
  } finally {
    loading.value = false;
  }
}

function applySmartFilter() {
  if (!smartFilter.value.type.trim() || !smartFilter.value.businessType.trim()) {
    toast.error("Validation", "Select type and business type.");
    return;
  }
  page.value = 1;
  showSmartFilter.value = false;
  hasSearched.value = true;
  void loadRows();
}

function resetSmartFilter() {
  smartFilter.value = { type: "", businessType: "" };
  businessOptions.value = [];
}

function toggleSort(col: SortKey) {
  if (sortBy.value === col) sortDir.value = sortDir.value === "asc" ? "desc" : "asc";
  else {
    sortBy.value = col;
    sortDir.value = col === "crm_reminder_date" ? "desc" : "asc";
  }
  if (!hasSearched.value) return;
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
  "Debtor ID",
  "Name",
  "Business",
  "Category",
  "Loan No",
  "Invoice No",
  "Outstanding",
  "Reminder Bill",
  "Reference",
  "Reminder Date",
];

function rowExport(r: CreditControlReminderStatusRow) {
  return {
    No: r.index,
    "Debtor ID": r.debtorid ?? "",
    Name: r.debtorname ?? "",
    Business: r.type2 ?? "",
    Category: r.category ?? "",
    "Loan No": r.loano ?? "",
    "Invoice No": r.noinv ?? "",
    Outstanding: r.outstandingAmt != null ? fmtMoney(r.outstandingAmt) : "",
    "Reminder Bill": r.reminderBill ?? "",
    Reference: r.referenceNo ?? "",
    "Reminder Date": r.reminderDate ? String(r.reminderDate) : "",
  };
}

const overflowOpen = ref(false);
const overflowRoot = ref<HTMLElement | null>(null);

function onClickOutside(event: MouseEvent) {
  if (!overflowOpen.value) return;
  if (overflowRoot.value?.contains(event.target as Node)) return;
  overflowOpen.value = false;
}

const smartFilterRef = ref<Record<string, unknown>>({});
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
  pageName: "Successful Generated Reminder",
  apiDataPath: "/credit-control/reminder-status",
  defaultExportColumns: exportColumns,
  getFilteredList: () => rows.value.map((r) => rowExport(r) as Record<string, unknown>),
  datatableRef,
  searchKeyword: q,
  smartFilter: smartFilterRef,
  applyFilters: () => {
    if (hasSearched.value) void loadRows();
  },
});

watch(
  smartFilter,
  (sf) => {
    smartFilterRef.value = { ...sf };
  },
  { deep: true, immediate: true },
);

watch(
  () => smartFilter.value.type,
  () => {
    smartFilter.value.businessType = "";
    void refreshBusinessTypes();
  },
);

let searchDeb: ReturnType<typeof setTimeout> | null = null;
watch(q, () => {
  if (!hasSearched.value) return;
  if (searchDeb) clearTimeout(searchDeb);
  searchDeb = setTimeout(() => {
    searchDeb = null;
    page.value = 1;
    void loadRows();
  }, 320);
});

watch(limit, () => {
  if (!hasSearched.value) return;
  page.value = 1;
  void loadRows();
});

async function exportExcel() {
  try {
    if (rows.value.length === 0) {
      toast.info("No data", "There is nothing to export.");
      return;
    }
    const ExcelJS = await import("exceljs");
    const wb = new ExcelJS.Workbook();
    const ws = wb.addWorksheet("Reminders");
    ws.addRow(exportColumns);
    rows.value.forEach((r) => {
      const e = rowExport(r);
      ws.addRow(exportColumns.map((h) => e[h as keyof typeof e]));
    });
    const buf = await wb.xlsx.writeBuffer();
    const blob = new Blob([buf], { type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" });
    const url = URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.href = url;
    a.download = `CC_Reminder_Status_${new Date().toISOString().slice(0, 10)}.xlsx`;
    a.click();
    URL.revokeObjectURL(url);
    toast.success("Excel downloaded");
  } catch (e) {
    toast.error("Export failed", e instanceof Error ? e.message : "Excel export failed.");
  }
}

onMounted(async () => {
  document.addEventListener("click", onClickOutside);
  try {
    const res = await getCreditControlReminderDebtorCreditorTypes();
    typeOptions.value = res.data;
  } catch {
    typeOptions.value = [];
  }
  datatableRef.value = {
    getExportConfig: () => ({
      columns: exportColumns,
      data: rows.value.map((r) => rowExport(r) as Record<string, unknown>),
    }),
  };
});
onUnmounted(() => {
  document.removeEventListener("click", onClickOutside);
  if (searchDeb) clearTimeout(searchDeb);
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

      <h1 class="page-title">Credit Control / Reminder / Successful Generated Reminder</h1>

      <article class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between gap-2 border-b border-slate-100 px-4 py-3">
          <h2 class="text-base font-semibold text-slate-900">Successful Generated Reminder</h2>
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
                :disabled="!hasSearched"
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
            Choose
            <button type="button" class="font-medium text-violet-600 hover:underline" @click="showSmartFilter = true">
              Smart filter
            </button>
            (type + business type), then Apply to load data.
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
                  <th class="whitespace-nowrap px-2 py-2 font-medium">
                    <button type="button" class="hover:text-slate-900" @click="toggleSort('crm_debtor_id')">Debtor ID</button>
                  </th>
                  <th class="whitespace-nowrap px-2 py-2 font-medium">
                    <button type="button" class="hover:text-slate-900" @click="toggleSort('crm_debtor_name')">Name</button>
                  </th>
                  <th class="whitespace-nowrap px-2 py-2 font-medium">Business</th>
                  <th class="whitespace-nowrap px-2 py-2 font-medium">Category</th>
                  <th class="whitespace-nowrap px-2 py-2 font-medium">Loan No</th>
                  <th class="whitespace-nowrap px-2 py-2 font-medium">Invoice No</th>
                  <th class="whitespace-nowrap px-2 py-2 text-right font-medium">
                    <button type="button" class="hover:text-slate-900" @click="toggleSort('crm_amt_outstanding')">Outstanding</button>
                  </th>
                  <th class="whitespace-nowrap px-2 py-2 font-medium">Reminder Bill</th>
                  <th class="whitespace-nowrap px-2 py-2 font-medium">Reference</th>
                  <th class="whitespace-nowrap px-2 py-2 font-medium">
                    <button type="button" class="hover:text-slate-900" @click="toggleSort('crm_reminder_date')">Reminder Date</button>
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 bg-white text-slate-800">
                <tr v-if="loading">
                  <td colspan="11" class="px-3 py-8 text-center text-slate-500">Loading…</td>
                </tr>
                <tr v-else-if="rows.length === 0">
                  <td colspan="11" class="px-3 py-8 text-center text-slate-500">No rows</td>
                </tr>
                <tr v-for="row in rows" v-else :key="`${row.seqid}-${row.index}`">
                  <td class="px-2 py-1.5">{{ row.index }}</td>
                  <td class="px-2 py-1.5">{{ row.debtorid }}</td>
                  <td class="px-2 py-1.5">{{ row.debtorname }}</td>
                  <td class="px-2 py-1.5">{{ row.type2 }}</td>
                  <td class="px-2 py-1.5">{{ row.category }}</td>
                  <td class="px-2 py-1.5">{{ row.loano }}</td>
                  <td class="px-2 py-1.5">{{ row.noinv }}</td>
                  <td class="px-2 py-1.5 text-right tabular-nums">{{ fmtMoney(row.outstandingAmt) }}</td>
                  <td class="px-2 py-1.5">{{ row.reminderBill }}</td>
                  <td class="px-2 py-1.5">{{ row.referenceNo }}</td>
                  <td class="px-2 py-1.5">{{ row.reminderDate }}</td>
                </tr>
              </tbody>
            </table>
          </div>

          <div
            v-if="hasSearched && total > 0"
            class="flex flex-wrap items-center justify-between gap-3 text-xs text-slate-600"
          >
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
                <span class="text-xs font-medium text-slate-600">Debtor / creditor type *</span>
                <select v-model="smartFilter.type" class="mt-1 w-full rounded-lg border border-slate-300 px-2 py-1.5">
                  <option value="">— Select —</option>
                  <option v-for="o in typeOptions" :key="o.id" :value="o.id">{{ o.label }}</option>
                </select>
              </label>
              <label class="block">
                <span class="text-xs font-medium text-slate-600">Business type *</span>
                <select
                  v-model="smartFilter.businessType"
                  class="mt-1 w-full rounded-lg border border-slate-300 px-2 py-1.5"
                  :disabled="!smartFilter.type"
                >
                  <option value="">— Select —</option>
                  <option v-for="o in businessOptions" :key="o.id" :value="o.id">{{ o.label }}</option>
                </select>
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
