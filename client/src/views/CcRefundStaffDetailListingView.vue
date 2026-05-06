<script setup lang="ts">
/**
 * Credit Control / Report of Refund Bill — MENUID 2290.
 * Line-level refund process listing (`temp_refund_bills_master` × `temp_refund_bills_details`).
 */
import { computed, onMounted, onUnmounted, ref, watch } from "vue";
import { Download, FileDown, FileSpreadsheet, Filter, MoreVertical, Search, X } from "lucide-vue-next";
import AdminLayout from "@/layouts/AdminLayout.vue";
import { listCreditControlRefundStaffDetail } from "@/api/cms";
import { useDatatableFeatures } from "@/composables/useDatatableFeatures";
import type { DatatableRefApi } from "@/composables/useDatatableFeatures";
import { useToast } from "@/composables/useToast";
import type { CreditControlRefundStaffDetailRow } from "@/types";

const toast = useToast();
const datatableRef = ref<DatatableRefApi | null>(null);
const rows = ref<CreditControlRefundStaffDetailRow[]>([]);
const page = ref(1);
const limit = ref(10);
const q = ref("");
const total = ref(0);
const loading = ref(false);
const showSmartFilter = ref(false);

const smartFilter = ref({
  bim_bills_no: "",
  bim_payto_id: "",
  bim_payto_name: "",
  bim_status: "",
  bid_amt: "",
  createddate: "",
});

const totalPages = computed(() =>
  total.value ? Math.max(1, Math.ceil(total.value / limit.value)) : 1,
);

type DetailCol = { key: keyof CreditControlRefundStaffDetailRow | string; label: string; num?: boolean };

const tableColumns: DetailCol[] = [
  { key: "bim_bills_no", label: "Bill No" },
  { key: "bim_bills_type_label", label: "Type" },
  { key: "bim_bills_desc", label: "Bill Desc" },
  { key: "bim_bill_amt", label: "Bill Amt", num: true },
  { key: "bim_status", label: "Status" },
  { key: "createddate", label: "Created" },
  { key: "bid_payto_id", label: "Line Pay To ID" },
  { key: "bid_payto_name", label: "Line Pay To Name" },
  { key: "vsa_bank_accno", label: "Bank A/C" },
  { key: "fty_fund_type", label: "Fund" },
  { key: "at_activity_code", label: "Activity" },
  { key: "oun_code", label: "PTJ" },
  { key: "ccr_costcentre", label: "CC" },
  { key: "acm_acct_code", label: "Acct" },
  { key: "bid_amt", label: "Line Amt", num: true },
  { key: "third_party_info", label: "3rd Party" },
  { key: "novoucher", label: "Voucher" },
  { key: "voucherdate", label: "Voucher Date" },
  { key: "paymode", label: "Pay Mode" },
  { key: "eftdate", label: "EFT Date" },
  { key: "eftno", label: "EFT No" },
];

function fmtMoney(v: unknown): string {
  if (v === null || v === undefined || v === "") return "";
  const n = typeof v === "number" ? v : Number(v);
  if (Number.isNaN(n)) return String(v);
  return new Intl.NumberFormat("en-MY", { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(n);
}

function cell(r: CreditControlRefundStaffDetailRow, key: string): string {
  const v = r[key as keyof CreditControlRefundStaffDetailRow];
  if (v === null || v === undefined) return "";
  return String(v).replace(/\n/g, " / ");
}

async function loadRows() {
  loading.value = true;
  const sf = smartFilter.value;
  const params = new URLSearchParams({
    page: String(page.value),
    limit: String(limit.value),
    ...(q.value.trim() ? { q: q.value.trim() } : {}),
    ...(sf.bim_bills_no.trim() ? { bim_bills_no: sf.bim_bills_no.trim() } : {}),
    ...(sf.bim_payto_id.trim() ? { bim_payto_id: sf.bim_payto_id.trim() } : {}),
    ...(sf.bim_payto_name.trim() ? { bim_payto_name: sf.bim_payto_name.trim() } : {}),
    ...(sf.bim_status.trim() ? { bim_status: sf.bim_status.trim() } : {}),
    ...(sf.bid_amt.trim() ? { bid_amt: sf.bid_amt.trim() } : {}),
    ...(sf.createddate.trim() ? { createddate: sf.createddate.trim() } : {}),
  });
  try {
    const res = await listCreditControlRefundStaffDetail(`?${params.toString()}`);
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
  void loadRows();
}

function resetSmartFilter() {
  smartFilter.value = {
    bim_bills_no: "",
    bim_payto_id: "",
    bim_payto_name: "",
    bim_status: "",
    bid_amt: "",
    createddate: "",
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

const exportColumns = ["No", ...tableColumns.map((c) => c.label)];

function rowExport(r: CreditControlRefundStaffDetailRow) {
  const o: Record<string, string | number> = { No: r.index };
  for (const c of tableColumns) {
    const raw = r[c.key as keyof CreditControlRefundStaffDetailRow];
    o[c.label] =
      c.num && raw !== null && raw !== undefined && raw !== ""
        ? fmtMoney(raw)
        : cell(r, c.key);
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
  pageName: "Report of Refund Bill",
  apiDataPath: "/credit-control/refund-staff-detail-listing",
  defaultExportColumns: exportColumns,
  getFilteredList: () => rows.value.map((r) => rowExport(r) as Record<string, unknown>),
  datatableRef,
  searchKeyword: q,
  smartFilter: smartFilterRef,
  applyFilters: () => void loadRows(),
});

watch(
  smartFilter,
  (sf) => {
    smartFilterRef.value = { ...sf };
  },
  { deep: true, immediate: true },
);

let searchDeb: ReturnType<typeof setTimeout> | null = null;
watch(q, () => {
  if (searchDeb) clearTimeout(searchDeb);
  searchDeb = setTimeout(() => {
    searchDeb = null;
    page.value = 1;
    void loadRows();
  }, 320);
});

watch(limit, () => {
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
    const ws = wb.addWorksheet("Refund detail");
    ws.addRow(exportColumns);
    rows.value.forEach((r) => {
      const e = rowExport(r);
      ws.addRow(exportColumns.map((h) => e[h]));
    });
    const buf = await wb.xlsx.writeBuffer();
    const blob = new Blob([buf], { type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" });
    const url = URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.href = url;
    a.download = `CC_Refund_Staff_Detail_${new Date().toISOString().slice(0, 10)}.xlsx`;
    a.click();
    URL.revokeObjectURL(url);
    toast.success("Excel downloaded");
  } catch (e) {
    toast.error("Export failed", e instanceof Error ? e.message : "Excel export failed.");
  }
}

onMounted(() => {
  document.addEventListener("click", onClickOutside);
  datatableRef.value = {
    getExportConfig: () => ({
      columns: exportColumns,
      data: rows.value.map((r) => rowExport(r) as Record<string, unknown>),
    }),
  };
  void loadRows();
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

      <h1 class="page-title">Credit Control / Refund / Refund (Student) / Report of Refund Bill</h1>

      <article class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between gap-2 border-b border-slate-100 px-4 py-3">
          <h2 class="text-base font-semibold text-slate-900">Report of Refund Bill</h2>
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
              <select v-model.number="limit" class="rounded-lg border border-slate-300 px-2 py-1.5 text-sm">
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
                  placeholder="Filter rows…"
                  class="w-56 rounded-lg border border-slate-300 py-1.5 pl-8 pr-8 text-sm"
                  @keyup.enter="page = 1; void loadRows()"
                />
                <button
                  v-if="q"
                  type="button"
                  class="absolute right-1 top-1/2 -translate-y-1/2 rounded p-0.5 text-slate-400 hover:bg-slate-100"
                  @click="q = ''; page = 1; void loadRows()"
                >
                  <X class="h-3.5 w-3.5" />
                </button>
              </div>
            </div>
          </div>

          <div class="flex flex-wrap items-center justify-end gap-2">
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

          <div class="max-h-[28rem] overflow-y-auto overflow-x-auto rounded-lg border border-slate-200">
            <table class="min-w-[1400px] divide-y divide-slate-200 text-left text-xs">
              <thead class="sticky top-0 z-10 bg-slate-50 text-slate-600">
                <tr>
                  <th class="whitespace-nowrap px-2 py-2 font-medium">No</th>
                  <th v-for="c in tableColumns" :key="c.key" class="whitespace-nowrap px-2 py-2 font-medium">
                    {{ c.label }}
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 bg-white text-slate-800">
                <tr v-if="loading">
                  <td :colspan="tableColumns.length + 1" class="px-3 py-8 text-center text-slate-500">Loading…</td>
                </tr>
                <tr v-else-if="rows.length === 0">
                  <td :colspan="tableColumns.length + 1" class="px-3 py-8 text-center text-slate-500">No rows</td>
                </tr>
                <tr v-for="row in rows" v-else :key="`${row.index}-${row.bim_bills_id}-${row.bid_payto_id}`">
                  <td class="px-2 py-1.5">{{ row.index }}</td>
                  <td v-for="c in tableColumns" :key="c.key" class="px-2 py-1.5" :class="c.num ? 'text-right tabular-nums' : 'text-left'">
                    <span v-if="c.num">{{ fmtMoney(row[c.key as keyof CreditControlRefundStaffDetailRow]) }}</span>
                    <span v-else>{{ cell(row, c.key) }}</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div v-if="total > 0" class="flex flex-wrap items-center justify-between gap-3 text-xs text-slate-600">
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
                <span class="text-xs font-medium text-slate-600">Bill no (contains)</span>
                <input v-model="smartFilter.bim_bills_no" class="mt-1 w-full rounded-lg border border-slate-300 px-2 py-1.5" />
              </label>
              <label class="block">
                <span class="text-xs font-medium text-slate-600">Line pay to ID</span>
                <input v-model="smartFilter.bim_payto_id" class="mt-1 w-full rounded-lg border border-slate-300 px-2 py-1.5" />
              </label>
              <label class="block">
                <span class="text-xs font-medium text-slate-600">Master pay to name (contains)</span>
                <input v-model="smartFilter.bim_payto_name" class="mt-1 w-full rounded-lg border border-slate-300 px-2 py-1.5" />
              </label>
              <label class="block">
                <span class="text-xs font-medium text-slate-600">Status</span>
                <input v-model="smartFilter.bim_status" class="mt-1 w-full rounded-lg border border-slate-300 px-2 py-1.5" />
              </label>
              <label class="block">
                <span class="text-xs font-medium text-slate-600">Line amount (contains)</span>
                <input v-model="smartFilter.bid_amt" class="mt-1 w-full rounded-lg border border-slate-300 px-2 py-1.5" />
              </label>
              <label class="block">
                <span class="text-xs font-medium text-slate-600">Created date (dd/mm/yyyy)</span>
                <input v-model="smartFilter.createddate" placeholder="31/12/2025" class="mt-1 w-full rounded-lg border border-slate-300 px-2 py-1.5" />
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
