<script setup lang="ts">
/**
 * Asset / Asset Cancellation Listing (PAGEID 2267 / MENUID 2756).
 * Datatable — smart filter pattern (kitchen sink).
 */
import { onMounted, onUnmounted, ref, watch } from "vue";
import { useRouter } from "vue-router";
import { Download, FileDown, FileSpreadsheet, Filter, MoreVertical, Pencil, Printer, Search, X } from "lucide-vue-next";
import AdminLayout from "@/layouts/AdminLayout.vue";
import { listAssetCancellationJournals } from "@/api/cms";
import { useToast } from "@/composables/useToast";
import type { AssetCancellationJournalRow } from "@/types";
import { useDatatableFeatures } from "@/composables/useDatatableFeatures";
import type { DatatableRefApi } from "@/composables/useDatatableFeatures";

const router = useRouter();
const toast = useToast();
const currency = new Intl.NumberFormat("en-MY", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
const rows = ref<AssetCancellationJournalRow[]>([]);
const total = ref(0);
const page = ref(1);
const limit = ref(10);
const q = ref("");
const sortBy = ref<string>("createddate");
const sortDir = ref<"asc" | "desc">("desc");
const loading = ref(false);
const showSmartFilter = ref(false);
const smartFilter = ref({
  createddate: "",
  createddateEnd: "",
  mjmJournalDesc: "",
  mjmTotalAmt: "",
  mjmStatus: "",
  createdby: "",
});

function formatTs(s: string | null | undefined): string {
  if (!s) return "—";
  const d = new Date(s);
  if (Number.isNaN(d.getTime())) return s;
  const dd = String(d.getDate()).padStart(2, "0");
  const mm = String(d.getMonth() + 1).padStart(2, "0");
  return `${dd}/${mm}/${d.getFullYear()}`;
}

function totalPages() {
  return total.value ? Math.max(1, Math.ceil(total.value / limit.value)) : 1;
}
function startIdx() {
  return total.value === 0 ? 0 : (page.value - 1) * limit.value + 1;
}
function endIdx() {
  return Math.min(page.value * limit.value, total.value);
}

async function loadRows() {
  loading.value = true;
  const sf = smartFilter.value;
  const params = new URLSearchParams({
    page: String(page.value),
    limit: String(limit.value),
    sort_by: sortBy.value,
    sort_dir: sortDir.value,
    ...(q.value ? { q: q.value } : {}),
    ...(sf.createddate ? { createddate: sf.createddate } : {}),
    ...(sf.createddateEnd ? { createddate_end: sf.createddateEnd } : {}),
    ...(sf.mjmJournalDesc ? { mjm_journal_desc: sf.mjmJournalDesc } : {}),
    ...(sf.mjmTotalAmt ? { mjm_total_amt: sf.mjmTotalAmt } : {}),
    ...(sf.mjmStatus ? { mjm_status: sf.mjmStatus } : {}),
    ...(sf.createdby ? { createdby: sf.createdby } : {}),
  });
  try {
    const res = await listAssetCancellationJournals(`?${params.toString()}`);
    rows.value = res.data;
    total.value = Number(res.meta?.total ?? 0);
  } catch (e) {
    toast.error("Load failed", e instanceof Error ? e.message : "Unable to load journals.");
  } finally {
    loading.value = false;
  }
}

function toggleSort(col: string) {
  if (sortBy.value === col) sortDir.value = sortDir.value === "asc" ? "desc" : "asc";
  else {
    sortBy.value = col;
    sortDir.value = col === "createddate" ? "desc" : "asc";
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
  if (page.value < totalPages()) {
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
  smartFilter.value = { createddate: "", createddateEnd: "", mjmJournalDesc: "", mjmTotalAmt: "", mjmStatus: "", createdby: "" };
}

function goEdit(row: AssetCancellationJournalRow) {
  if (!row.isDraft) {
    toast.info("Read only", "Edit is only available for draft / in-progress journals in the legacy app.");
    return;
  }
  void router.push({ name: "kerisi-asset-cancellation", query: { mjm_journal_id: String(row.journalId), asset_id: String(row.assetId) } });
}

async function printRow(row: AssetCancellationJournalRow) {
  const { default: jsPDF } = await import("jspdf");
  const d = new jsPDF();
  d.text(`Asset cancellation journal — ${row.journalNo ?? row.journalId}`, 14, 16);
  d.setFontSize(10);
  d.text(`Date: ${formatTs(row.createdDate)}`, 14, 26);
  d.text(`Description: ${row.description ?? ""}`, 14, 34);
  d.text(`Item: ${row.itemDescription ?? ""}`, 14, 42);
  d.text(`Amount: ${row.amount != null ? currency.format(row.amount) : "—"}`, 14, 50);
  d.text(`Status: ${row.status ?? ""}`, 14, 58);
  d.save(`cancellation_journal_${row.journalId}.pdf`);
}

const exportCols = ["Date", "Journal No.", "Description", "Item", "Amount", "Status", "Created By", "Approve By"];

function asExport(r: AssetCancellationJournalRow): (string | number)[] {
  return [
    formatTs(r.createdDate),
    r.journalNo ?? "",
    r.description ?? "",
    r.itemDescription ?? "",
    r.amount != null ? currency.format(r.amount) : "",
    r.status ?? "",
    r.createdBy ?? "",
    r.approveBy ?? "",
  ];
}

async function exportRows(kind: "pdf" | "csv" | "excel") {
  if (!rows.value.length) {
    toast.info("No data", "Nothing to export.");
    return;
  }
  const name = `Asset_Cancellation_Journals_${new Date().toISOString().slice(0, 10)}`;
  const body = rows.value.map(asExport);
  if (kind === "csv") {
    const esc = (v: string | number) => (/,|\n|"/.test(String(v)) ? `"${String(v).replace(/"/g, '""')}"` : v);
    const c = [["No", ...exportCols], ...body.map((r, i) => [i + 1, ...r])].map((a) => a.map(esc).join(",")).join("\n");
    const a = document.createElement("a");
    a.href = URL.createObjectURL(new Blob([c], { type: "text/csv;charset=utf-8" }));
    a.download = `${name}.csv`;
    a.click();
    toast.success("CSV downloaded");
  } else if (kind === "excel") {
    const ExcelJS = await import("exceljs");
    const wb = new ExcelJS.Workbook();
    const w = wb.addWorksheet("Journals");
    w.addRow(["No", ...exportCols]);
    body.forEach((r, i) => w.addRow([i + 1, ...r]));
    const buf = await wb.xlsx.writeBuffer();
    const a = document.createElement("a");
    a.href = URL.createObjectURL(new Blob([buf], { type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" }));
    a.download = `${name}.xlsx`;
    a.click();
    URL.revokeObjectURL(a.href);
    toast.success("Excel downloaded");
  } else {
    const { default: jsPDF } = await import("jspdf");
    const t = (await import("jspdf-autotable")).default;
    const d = new jsPDF({ orientation: "landscape" });
    d.text(name, 14, 14);
    t(d, { head: [["No", ...exportCols]], body: body.map((r, i) => [i + 1, ...r]), startY: 20, styles: { fontSize: 7 } });
    d.save(`${name}.pdf`);
    toast.success("PDF downloaded");
  }
}

let searchTimer: ReturnType<typeof setTimeout> | null = null;
watch(q, () => {
  if (searchTimer) clearTimeout(searchTimer);
  searchTimer = setTimeout(() => {
    searchTimer = null;
    page.value = 1;
    void loadRows();
  }, 350);
});

const overflowOpen = ref(false);
const overflowRoot = ref<HTMLElement | null>(null);
function onClickOutside(ev: MouseEvent) {
  if (!overflowOpen.value) return;
  if (overflowRoot.value?.contains(ev.target as Node)) return;
  overflowOpen.value = false;
}

const { templateFileInputRef, isGrouped, handleSaveTemplate, handleLoadTemplate, onTemplateFileChange, handleGroupList, handleUngroupList } =
  useDatatableFeatures({
    pageName: "Asset Cancellation Journals",
    apiDataPath: "",
    defaultExportColumns: [],
    getFilteredList: () => [],
    datatableRef: ref<DatatableRefApi | null>(null),
    searchKeyword: q,
  });

onMounted(() => {
  document.addEventListener("click", onClickOutside);
  void loadRows();
});
onUnmounted(() => {
  document.removeEventListener("click", onClickOutside);
  if (searchTimer) clearTimeout(searchTimer);
});
</script>

<template>
  <AdminLayout>
    <div class="space-y-4">
      <h1 class="page-title">Asset / Asset Cancellation Listing</h1>
      <article class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
          <h2 class="text-base font-semibold text-slate-900">ASSET CANCELLATION LISTING</h2>
          <div ref="overflowRoot" class="relative">
            <button type="button" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100" @click.stop="overflowOpen = !overflowOpen">
              <MoreVertical class="h-4 w-4" />
            </button>
            <div v-if="overflowOpen" class="absolute right-0 z-30 mt-1 w-44 rounded-lg border border-slate-200 bg-white py-1 shadow-lg" @click.stop>
              <button type="button" class="block w-full px-3 py-2 text-left text-xs text-slate-700 hover:bg-slate-50" @click="overflowOpen = false; handleSaveTemplate()">Save template</button>
              <button type="button" class="block w-full px-3 py-2 text-left text-xs text-slate-700 hover:bg-slate-50" @click="overflowOpen = false; handleLoadTemplate()">Load template</button>
              <input ref="templateFileInputRef" type="file" accept="application/json" class="hidden" @change="onTemplateFileChange" />
              <button v-if="isGrouped" type="button" class="block w-full px-3 py-2 text-left text-xs text-slate-700 hover:bg-slate-50" @click="overflowOpen = false; handleUngroupList()">Ungroup list</button>
              <button v-else type="button" class="block w-full px-3 py-2 text-left text-xs text-slate-700 hover:bg-slate-50" @click="overflowOpen = false; handleGroupList()">Group list</button>
            </div>
          </div>
        </div>
        <div class="space-y-4 p-4">
          <div class="flex flex-wrap items-end justify-between gap-4">
            <div class="flex items-center gap-2">
              <label class="text-xs text-slate-600">Display</label>
              <select v-model.number="limit" class="rounded-lg border border-slate-300 px-2 py-1.5 text-sm" @change="page = 1; loadRows()">
                <option v-for="n in [10, 25, 50, 100]" :key="n" :value="n">{{ n }}</option>
              </select>
            </div>
            <div class="flex flex-wrap items-center gap-2">
              <div class="relative">
                <Search class="pointer-events-none absolute left-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400" />
                <input v-model="q" type="search" placeholder="Filter rows..." class="w-56 rounded-lg border border-slate-300 py-1.5 pl-8 pr-8 text-sm" @keyup.enter="page = 1; loadRows()" />
                <button v-if="q" type="button" class="absolute right-1 top-1/2 -translate-y-1/2 p-0.5 text-slate-400" @click="q = ''"><X class="h-3.5 w-3.5" /></button>
              </div>
              <button type="button" class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm hover:bg-slate-50" @click="showSmartFilter = true">
                <Filter class="h-4 w-4" />Filter
              </button>
            </div>
          </div>
          <div class="overflow-x-auto rounded-lg border border-slate-200">
            <div :class="rows.length > 10 ? 'max-h-[480px] overflow-y-auto' : ''">
              <table class="admin-table-kitchen w-full min-w-[1200px] text-sm">
                <thead class="admin-table-thead-sticky">
                  <tr class="border-b text-left text-xs font-semibold uppercase">
                    <th class="px-2 py-2">No</th>
                    <th class="cursor-pointer px-2 py-2" @click="toggleSort('createddate')">Date</th>
                    <th class="cursor-pointer px-2 py-2" @click="toggleSort('mjm_journal_no')">Journal No.</th>
                    <th class="cursor-pointer px-2 py-2" @click="toggleSort('mjm_journal_desc')">Description</th>
                    <th class="px-2 py-2">Item Description</th>
                    <th class="cursor-pointer px-2 py-2 text-right" @click="toggleSort('mjm_total_amt')">Amount</th>
                    <th class="cursor-pointer px-2 py-2" @click="toggleSort('mjm_status')">Status</th>
                    <th class="cursor-pointer px-2 py-2" @click="toggleSort('createdby')">Created By</th>
                    <th class="cursor-pointer px-2 py-2" @click="toggleSort('mjm_approveby')">Approve By</th>
                    <th class="px-2 py-2 text-right">Action</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-if="loading"><td colspan="10" class="p-6 text-center text-slate-500">Loading…</td></tr>
                  <tr v-else-if="!rows.length"><td colspan="10" class="p-6 text-center text-slate-500">No journals</td></tr>
                  <tr v-for="row in rows" :key="row.journalId" class="border-b border-slate-100 hover:bg-slate-50">
                    <td class="px-2 py-1.5">{{ row.index }}</td>
                    <td class="px-2 py-1.5">{{ formatTs(row.createdDate) }}</td>
                    <td class="px-2 py-1.5 font-medium">{{ row.journalNo ?? "—" }}</td>
                    <td class="px-2 py-1.5 max-w-[180px] truncate" :title="row.description ?? ''">{{ row.description ?? "—" }}</td>
                    <td class="px-2 py-1.5 max-w-[160px] truncate" :title="row.itemDescription ?? ''">{{ row.itemDescription ?? "—" }}</td>
                    <td class="px-2 py-1.5 text-right tabular-nums">{{ row.amount != null ? currency.format(row.amount) : "—" }}</td>
                    <td class="px-2 py-1.5">{{ row.status ?? "—" }}</td>
                    <td class="px-2 py-1.5">{{ row.createdBy ?? "—" }}</td>
                    <td class="px-2 py-1.5">{{ row.approveBy ?? "—" }}</td>
                    <td class="px-2 py-1.5 text-right">
                      <button type="button" class="mr-1 inline-flex rounded p-1 text-slate-600 hover:bg-slate-100" title="PDF" @click="printRow(row)"><Printer class="h-4 w-4" /></button>
                      <button type="button" class="inline-flex rounded p-1 text-slate-600 hover:bg-slate-100 disabled:opacity-40" :disabled="!row.isDraft" title="Edit" @click="goEdit(row)"><Pencil class="h-4 w-4" /></button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-3 text-xs text-slate-500">
            <p>Showing {{ startIdx() }}–{{ endIdx() }} of {{ total }}</p>
            <div class="flex items-center gap-2">
              <button type="button" class="rounded border bg-white px-2 py-1" :disabled="page <= 1" @click="prevPage">Prev</button>
              <span class="text-slate-600">Page {{ page }} / {{ totalPages() }}</span>
              <button type="button" class="rounded border bg-white px-2 py-1" :disabled="page >= totalPages()" @click="nextPage">Next</button>
              <div class="mx-2 h-4 w-px bg-slate-200" />
              <button type="button" class="inline-flex items-center gap-1 rounded border bg-white px-2 py-1" @click="exportRows('pdf')"><Download class="h-3.5 w-3.5" />PDF</button>
              <button type="button" class="inline-flex items-center gap-1 rounded border bg-white px-2 py-1" @click="exportRows('csv')"><FileDown class="h-3.5 w-3.5" />CSV</button>
              <button type="button" class="inline-flex items-center gap-1 rounded border bg-white px-2 py-1" @click="exportRows('excel')"><FileSpreadsheet class="h-3.5 w-3.5" />Excel</button>
            </div>
          </div>
        </div>
      </article>
      <Teleport to="body">
        <div v-if="showSmartFilter" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm" @click.self="showSmartFilter = false">
          <div class="max-h-[80vh] w-full max-w-lg overflow-y-auto rounded-lg border border-slate-200 bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b px-4 py-3">
              <h2 class="text-base font-semibold">Smart filter</h2>
              <button type="button" class="p-1 text-slate-500" @click="showSmartFilter = false"><X class="h-4 w-4" /></button>
            </div>
            <div class="grid gap-2 px-4 py-3">
              <div><label class="text-sm">Date from (DD/MM/YYYY)</label><input v-model="smartFilter.createddate" class="mt-1 w-full rounded border px-2 py-1.5 text-sm" /></div>
              <div><label class="text-sm">Date to (DD/MM/YYYY)</label><input v-model="smartFilter.createddateEnd" class="mt-1 w-full rounded border px-2 py-1.5 text-sm" /></div>
              <div><label class="text-sm">Description</label><input v-model="smartFilter.mjmJournalDesc" class="mt-1 w-full rounded border px-2 py-1.5 text-sm" /></div>
              <div><label class="text-sm">Amount</label><input v-model="smartFilter.mjmTotalAmt" class="mt-1 w-full rounded border px-2 py-1.5 text-sm" /></div>
              <div><label class="text-sm">Status</label><input v-model="smartFilter.mjmStatus" class="mt-1 w-full rounded border px-2 py-1.5 text-sm" /></div>
              <div><label class="text-sm">Created by</label><input v-model="smartFilter.createdby" class="mt-1 w-full rounded border px-2 py-1.5 text-sm" /></div>
            </div>
            <div class="flex justify-end gap-2 border-t px-4 py-3">
              <button type="button" class="rounded border px-3 py-1.5" @click="resetSmartFilter">Reset</button>
              <button type="button" class="rounded bg-slate-900 px-3 py-1.5 text-sm text-white" @click="applySmartFilter">OK</button>
            </div>
          </div>
        </div>
      </Teleport>
    </div>
  </AdminLayout>
</template>
