<script setup lang="ts">
/**
 * Asset Cancellation — asset pick list (PAGEID 2139 / MENUID 2591) or
 * read-only verification strip (PAGEID 2221 / MENUID 2697).
 */
import { computed, onMounted, onUnmounted, ref, watch } from "vue";
import { useRoute } from "vue-router";
import { Download, FileDown, FileSpreadsheet, Filter, MoreVertical, Search, X } from "lucide-vue-next";
import AdminLayout from "@/layouts/AdminLayout.vue";
import { listAssetCancellationAssets } from "@/api/cms";
import { useToast } from "@/composables/useToast";
import type { AssetCancellationAssetRow } from "@/types";
import { useDatatableFeatures } from "@/composables/useDatatableFeatures";
import type { DatatableRefApi } from "@/composables/useDatatableFeatures";

const props = withDefaults(
  defineProps<{
    /** true = MENUID 2591 (selection / listing); false = MENUID 2697 (verification context). */
    selectionMode?: boolean;
  }>(),
  { selectionMode: true },
);

const route = useRoute();
const toast = useToast();
const currency = new Intl.NumberFormat("en-MY", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
const rows = ref<AssetCancellationAssetRow[]>([]);
const total = ref(0);
const page = ref(1);
const limit = ref(10);
const q = ref("");
const sortBy = ref<string>("aim_asset_code");
const sortDir = ref<"asc" | "desc">("asc");
const loading = ref(false);
const showSmartFilter = ref(false);
const smartFilter = ref({
  aimAssetCode: "",
  aimAssetType: "",
  aimAssetDesc: "",
  ftyFundType: "",
  atActivityCode: "",
  ounCodePayment: "",
  cpaProjectNo: "",
  acmAcctCode: "",
  aimInstallCost: "",
  aimRegisteredDate: "",
});

const mjmJournalId = computed(() => (route.query.mjm_journal_id as string) || (route.query.mjmJournalId as string) || "");

const pageTitle = computed(() =>
  props.selectionMode ? "Asset / Asset Cancellation" : "Asset / Asset Cancellation For Verification",
);

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
    ...(!props.selectionMode ? { for_verification: "1", ...(mjmJournalId.value ? { mjm_journal_id: mjmJournalId.value } : {}) } : {}),
    ...(sf.aimAssetCode ? { aim_asset_code: sf.aimAssetCode } : {}),
    ...(sf.aimAssetType ? { aim_asset_type: sf.aimAssetType } : {}),
    ...(sf.aimAssetDesc ? { aim_asset_desc: sf.aimAssetDesc } : {}),
    ...(sf.ftyFundType ? { fty_fund_type: sf.ftyFundType } : {}),
    ...(sf.atActivityCode ? { at_activity_code: sf.atActivityCode } : {}),
    ...(sf.ounCodePayment ? { oun_code_payment: sf.ounCodePayment } : {}),
    ...(sf.cpaProjectNo ? { cpa_project_no: sf.cpaProjectNo } : {}),
    ...(sf.acmAcctCode ? { acm_acct_code: sf.acmAcctCode } : {}),
    ...(sf.aimInstallCost ? { aim_install_cost: sf.aimInstallCost } : {}),
    ...(sf.aimRegisteredDate ? { aim_registered_date: sf.aimRegisteredDate } : {}),
  });
  try {
    const res = await listAssetCancellationAssets(`?${params.toString()}`);
    rows.value = res.data;
    total.value = Number(res.meta?.total ?? 0);
  } catch (e) {
    toast.error("Load failed", e instanceof Error ? e.message : "Unable to load assets.");
  } finally {
    loading.value = false;
  }
}

function toggleSort(col: string) {
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
  smartFilter.value = {
    aimAssetCode: "",
    aimAssetType: "",
    aimAssetDesc: "",
    ftyFundType: "",
    atActivityCode: "",
    ounCodePayment: "",
    cpaProjectNo: "",
    acmAcctCode: "",
    aimInstallCost: "",
    aimRegisteredDate: "",
  };
}

const exportCols = ["Code", "Desc", "Type", "Item", "Fund", "Activity", "PTJ Pay", "CC Pay", "Project", "Account", "Install", "Status"];

function asExport(r: AssetCancellationAssetRow): (string | number)[] {
  return [
    r.assetCode ?? "",
    r.assetDesc ?? "",
    r.assetType ?? "",
    r.itemCode ?? "",
    r.fund ?? "",
    r.activity ?? "",
    r.ptjPayment ?? "",
    r.costcentrePayment ?? "",
    r.projectNo ?? "",
    r.accountCode ?? "",
    r.installCost != null ? currency.format(r.installCost) : "",
    r.status ?? "",
  ];
}

async function exportRows(kind: "pdf" | "csv" | "excel") {
  if (!rows.value.length) {
    toast.info("No data", "Nothing to export.");
    return;
  }
  const name = `Asset_Cancellation_Assets_${new Date().toISOString().slice(0, 10)}`;
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
    const w = wb.addWorksheet("Assets");
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

watch([() => props.selectionMode, mjmJournalId], () => {
  page.value = 1;
  void loadRows();
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
    pageName: "Asset Cancellation Assets",
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
      <h1 class="page-title">{{ pageTitle }}</h1>
      <article class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
          <h2 class="text-base font-semibold text-slate-900">{{ selectionMode ? "List of Assets" : "Asset Info" }}</h2>
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
          <div v-if="!selectionMode && !mjmJournalId" class="rounded border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-900">
            Optional: add <code class="rounded bg-white px-1">?mjm_journal_id=</code> to the URL to scope rows to one cancellation journal (legacy verify context).
          </div>
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
              <button v-if="selectionMode" type="button" class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm hover:bg-slate-50" @click="showSmartFilter = true">
                <Filter class="h-4 w-4" />Filter
              </button>
            </div>
          </div>
          <div class="overflow-x-auto rounded-lg border border-slate-200">
            <div :class="rows.length > 10 ? 'max-h-[480px] overflow-y-auto' : ''">
              <table class="admin-table-kitchen w-full min-w-[1600px] text-sm">
                <thead class="admin-table-thead-sticky">
                  <tr class="border-b text-left text-xs font-semibold uppercase">
                    <th v-if="selectionMode" class="px-2 py-2">No</th>
                    <th class="cursor-pointer px-2 py-2" @click="toggleSort('aim_asset_code')">Asset Code</th>
                    <th class="cursor-pointer px-2 py-2" @click="toggleSort('aim_asset_desc')">Desc</th>
                    <th class="cursor-pointer px-2 py-2" @click="toggleSort('aim_asset_type')">Type</th>
                    <th class="cursor-pointer px-2 py-2" @click="toggleSort('itm_item_code')">Item</th>
                    <th class="px-2 py-2">Fund</th>
                    <th class="px-2 py-2">Activity</th>
                    <th class="px-2 py-2">PTJ Pay</th>
                    <th class="px-2 py-2">CC Pay</th>
                    <th class="cursor-pointer px-2 py-2" @click="toggleSort('cpa_project_no')">Project</th>
                    <th class="cursor-pointer px-2 py-2" @click="toggleSort('acm_acct_code')">Account</th>
                    <th class="cursor-pointer px-2 py-2 text-right" @click="toggleSort('aim_install_cost')">Install</th>
                    <th class="cursor-pointer px-2 py-2" @click="toggleSort('aim_status')">Status</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-if="loading"><td :colspan="selectionMode ? 13 : 12" class="p-6 text-center text-slate-500">Loading…</td></tr>
                  <tr v-else-if="!rows.length"><td :colspan="selectionMode ? 13 : 12" class="p-6 text-center text-slate-500">No assets</td></tr>
                  <tr v-for="row in rows" :key="row.assetId" class="border-b border-slate-100 hover:bg-slate-50">
                    <td v-if="selectionMode" class="px-2 py-1.5">{{ row.index }}</td>
                    <td class="px-2 py-1.5 font-medium">{{ row.assetCode ?? "—" }}</td>
                    <td class="px-2 py-1.5 max-w-[160px] truncate" :title="row.assetDesc ?? ''">{{ row.assetDesc ?? "—" }}</td>
                    <td class="px-2 py-1.5">{{ row.assetType ?? "—" }}</td>
                    <td class="px-2 py-1.5">{{ row.itemCode ?? "—" }}</td>
                    <td class="px-2 py-1.5 max-w-[120px] truncate">{{ row.fund ?? "—" }}</td>
                    <td class="px-2 py-1.5 max-w-[120px] truncate">{{ row.activity ?? "—" }}</td>
                    <td class="px-2 py-1.5 max-w-[120px] truncate">{{ row.ptjPayment ?? "—" }}</td>
                    <td class="px-2 py-1.5 max-w-[120px] truncate">{{ row.costcentrePayment ?? "—" }}</td>
                    <td class="px-2 py-1.5">{{ row.projectNo ?? "—" }}</td>
                    <td class="px-2 py-1.5 max-w-[140px] truncate">{{ row.accountCode ?? "—" }}</td>
                    <td class="px-2 py-1.5 text-right tabular-nums">{{ row.installCost != null ? currency.format(row.installCost) : "—" }}</td>
                    <td class="px-2 py-1.5">{{ row.status ?? "—" }}</td>
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
          <div class="max-h-[80vh] w-full max-w-3xl overflow-y-auto rounded-lg border border-slate-200 bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b px-4 py-3">
              <h2 class="text-base font-semibold">Smart filter</h2>
              <button type="button" class="p-1 text-slate-500" @click="showSmartFilter = false"><X class="h-4 w-4" /></button>
            </div>
            <div class="grid gap-2 px-4 py-3 sm:grid-cols-2">
              <div><label class="text-sm">Asset code</label><input v-model="smartFilter.aimAssetCode" class="mt-1 w-full rounded border border-slate-300 px-2 py-1.5 text-sm" /></div>
              <div><label class="text-sm">Asset description</label><input v-model="smartFilter.aimAssetDesc" class="mt-1 w-full rounded border px-2 py-1.5 text-sm" /></div>
              <div><label class="text-sm">Type</label><input v-model="smartFilter.aimAssetType" class="mt-1 w-full rounded border px-2 py-1.5 text-sm" /></div>
              <div><label class="text-sm">Fund type</label><input v-model="smartFilter.ftyFundType" class="mt-1 w-full rounded border px-2 py-1.5 text-sm" /></div>
              <div><label class="text-sm">Activity</label><input v-model="smartFilter.atActivityCode" class="mt-1 w-full rounded border px-2 py-1.5 text-sm" /></div>
              <div><label class="text-sm">PTJ payment</label><input v-model="smartFilter.ounCodePayment" class="mt-1 w-full rounded border px-2 py-1.5 text-sm" /></div>
              <div><label class="text-sm">Project no</label><input v-model="smartFilter.cpaProjectNo" class="mt-1 w-full rounded border px-2 py-1.5 text-sm" /></div>
              <div><label class="text-sm">Account code</label><input v-model="smartFilter.acmAcctCode" class="mt-1 w-full rounded border px-2 py-1.5 text-sm" /></div>
              <div><label class="text-sm">Install cost</label><input v-model="smartFilter.aimInstallCost" class="mt-1 w-full rounded border px-2 py-1.5 text-sm" /></div>
              <div><label class="text-sm">Registered year</label><input v-model="smartFilter.aimRegisteredDate" class="mt-1 w-full rounded border px-2 py-1.5 text-sm" placeholder="YYYY" /></div>
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
