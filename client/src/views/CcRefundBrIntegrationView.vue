<script setup lang="ts">
/**
 * Bill Registration Integration — MENUID **2289**, Page **1871** (REFUND_STAFF).
 *
 * UI pattern matches Kerisi 2.0 listings (see `PostingToTbView`): card toolbar,
 * `admin-table-kitchen`, footer with Showing + Prev/Next.
 *
 * Legacy: `SNA_JS_CC_REFUNDSTAFF_BRINTEGRATION` / `SNA_API_CC_REFUNDSTAFF_BRINTEGRATION`.
 */
import { onMounted, onUnmounted, reactive, ref, toRef, watch } from "vue";
import { ExternalLink, Filter, MoreVertical, Search, X } from "lucide-vue-next";
import AdminLayout from "@/layouts/AdminLayout.vue";
import { listCreditControlRefundBrIntegration } from "@/api/cms";
import { useDatatableFeatures } from "@/composables/useDatatableFeatures";
import type { DatatableRefApi } from "@/composables/useDatatableFeatures";
import { useToast } from "@/composables/useToast";
import type { CcRefundBrIntegrationRow } from "@/types";

const toast = useToast();

type SortKey =
  | "bim_bills_no"
  | "createddate"
  | "bim_bill_amt"
  | "bim_status"
  | "bim_cust_invoice_date";

interface BriGridState {
  key: "non" | "app";
  approved: boolean;
  /** Short card heading (Posting-style). */
  cardTitle: string;
  /** Accessible / long label */
  title: string;
  page: number;
  limit: number;
  q: string;
  total: number;
  loading: boolean;
  rows: CcRefundBrIntegrationRow[];
  sortBy: SortKey;
  sortDir: "asc" | "desc";
}

function emptySmartFilter() {
  return {
    bim_bills_no: "",
    bim_bills_type: "",
    bim_payto_id: "",
    bim_payto_name: "",
    bim_bills_desc: "",
    bim_status: "",
    bim_bill_amt: "",
    bim_cust_invoice_no: "",
    bim_cust_invoice_date: "",
    createddate: "",
  };
}

const nonApproved = reactive<BriGridState>({
  key: "non",
  approved: false,
  cardTitle: "Non-approved list",
  title: "Bill Registration Integration Non-Approved List",
  page: 1,
  limit: 5,
  q: "",
  total: 0,
  loading: false,
  rows: [],
  sortBy: "createddate",
  sortDir: "desc",
});

const approved = reactive<BriGridState>({
  key: "app",
  approved: true,
  cardTitle: "Approved list",
  title: "Bill Registration Integration Approved List",
  page: 1,
  limit: 5,
  q: "",
  total: 0,
  loading: false,
  rows: [],
  sortBy: "bim_bills_no",
  sortDir: "asc",
});

const smartFilterNon = ref(emptySmartFilter());
const smartFilterApp = ref(emptySmartFilter());

const smartModalFor = ref<null | "non" | "app">(null);

const overflowOpen = reactive({ non: false, app: false });

function toggleOverflow(which: "non" | "app") {
  const next = !overflowOpen[which];
  overflowOpen.non = which === "non" ? next : false;
  overflowOpen.app = which === "app" ? next : false;
}

function closeOverflow(which: "non" | "app") {
  overflowOpen[which] = false;
}

function panels(): BriGridState[] {
  return [nonApproved, approved];
}

function openSmartModal(which: "non" | "app") {
  smartModalFor.value = which;
}

function closeSmartModal() {
  smartModalFor.value = null;
}

const datatableRefNon = ref<DatatableRefApi | null>(null);
const datatableRefApp = ref<DatatableRefApi | null>(null);

const {
  isGrouped: isGroupedNon,
  handleSaveTemplate: saveTplNon,
  handleLoadTemplate: loadTplNon,
  handleUngroupList: ungrpNon,
  handleGroupList: grpNon,
  templateFileInputRef: templateInputNon,
  onTemplateFileChange: onTplChangeNon,
} = useDatatableFeatures({
  pageName: "List Of Refund Bill (Non-approved)",
  apiDataPath: "/credit-control/refund-br-integration",
  defaultExportColumns: [],
  getFilteredList: () => [],
  datatableRef: datatableRefNon,
  searchKeyword: toRef(nonApproved, "q"),
  smartFilter: smartFilterNon,
  applyFilters: () => void load(nonApproved),
});

const {
  isGrouped: isGroupedApp,
  handleSaveTemplate: saveTplApp,
  handleLoadTemplate: loadTplApp,
  handleUngroupList: ungrpApp,
  handleGroupList: grpApp,
  templateFileInputRef: templateInputApp,
  onTemplateFileChange: onTplChangeApp,
} = useDatatableFeatures({
  pageName: "List Of Refund Bill (Approved)",
  apiDataPath: "/credit-control/refund-br-integration",
  defaultExportColumns: [],
  getFilteredList: () => [],
  datatableRef: datatableRefApp,
  searchKeyword: toRef(approved, "q"),
  smartFilter: smartFilterApp,
  applyFilters: () => void load(approved),
});

function totalPages(panel: BriGridState) {
  return panel.total ? Math.max(1, Math.ceil(panel.total / panel.limit)) : 1;
}

function showingFrom(panel: BriGridState) {
  return panel.total === 0 ? 0 : (panel.page - 1) * panel.limit + 1;
}

function showingTo(panel: BriGridState) {
  return Math.min(panel.page * panel.limit, panel.total);
}

function fmtMoney(n: number | null | undefined): string {
  if (n == null || Number.isNaN(n)) return "";
  return new Intl.NumberFormat("en-MY", { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(n);
}

function fmtRefundDate(row: CcRefundBrIntegrationRow): string {
  const iso = row.bimCustInvoiceDate;
  if (iso) {
    const [y, m, d] = iso.split("-");
    if (d && m && y) return `${d}/${m}/${y}`;
  }
  if (row.createddate) return String(row.createddate).slice(0, 19).replace("T", " ");
  return "—";
}

function appendSmartFilterParams(params: URLSearchParams, sf: Record<string, string>) {
  const keys = [
    "bim_bills_no",
    "bim_bills_type",
    "bim_payto_id",
    "bim_payto_name",
    "bim_bills_desc",
    "bim_status",
    "bim_bill_amt",
    "bim_cust_invoice_no",
    "bim_cust_invoice_date",
    "createddate",
  ] as const;
  for (const k of keys) {
    const v = String(sf[k] ?? "").trim();
    if (v) params.append(`smart_filter[${k}]`, v);
  }
}

async function load(panel: BriGridState) {
  panel.loading = true;
  const sf = panel.key === "non" ? smartFilterNon.value : smartFilterApp.value;
  const params = new URLSearchParams({
    page: String(panel.page),
    limit: String(panel.limit),
    sort_by: panel.sortBy,
    sort_dir: panel.sortDir,
    approved: panel.approved ? "true" : "false",
    ...(panel.q.trim() ? { q: panel.q.trim() } : {}),
  });
  appendSmartFilterParams(params, sf);

  try {
    const res = await listCreditControlRefundBrIntegration(`?${params.toString()}`);
    panel.rows = res.data;
    panel.total = Number(res.meta?.total ?? 0);
  } catch (e) {
    toast.error("Load failed", e instanceof Error ? e.message : "Unable to load list.");
  } finally {
    panel.loading = false;
  }
}

function reloadAll() {
  void load(nonApproved);
  void load(approved);
}

function toggleSort(panel: BriGridState, col: SortKey) {
  if (panel.sortBy === col) {
    panel.sortDir = panel.sortDir === "asc" ? "desc" : "asc";
  } else {
    panel.sortBy = col;
    panel.sortDir = col === "createddate" ? "desc" : "asc";
  }
  panel.page = 1;
  void load(panel);
}

function prevPage(panel: BriGridState) {
  if (panel.page > 1) {
    panel.page -= 1;
    void load(panel);
  }
}

function nextPage(panel: BriGridState) {
  if (panel.page < totalPages(panel)) {
    panel.page += 1;
    void load(panel);
  }
}

function resetSmart(which: "non" | "app") {
  const empty = emptySmartFilter();
  if (which === "non") smartFilterNon.value = empty;
  else smartFilterApp.value = empty;
}

function resetFromModal() {
  const w = smartModalFor.value;
  if (!w) return;
  resetSmart(w);
  closeSmartModal();
  const panel = w === "non" ? nonApproved : approved;
  panel.page = 1;
  void load(panel);
}

function applyFromModal() {
  const w = smartModalFor.value;
  if (!w) return;
  closeSmartModal();
  const panel = w === "non" ? nonApproved : approved;
  panel.page = 1;
  void load(panel);
}

watch(
  () => nonApproved.limit,
  () => {
    nonApproved.page = 1;
    void load(nonApproved);
  },
);

watch(
  () => approved.limit,
  () => {
    approved.page = 1;
    void load(approved);
  },
);

let debNon: ReturnType<typeof setTimeout> | null = null;
watch(
  () => nonApproved.q,
  () => {
    if (debNon) clearTimeout(debNon);
    debNon = setTimeout(() => {
      debNon = null;
      nonApproved.page = 1;
      void load(nonApproved);
    }, 320);
  },
);

let debApp: ReturnType<typeof setTimeout> | null = null;
watch(
  () => approved.q,
  () => {
    if (debApp) clearTimeout(debApp);
    debApp = setTimeout(() => {
      debApp = null;
      approved.page = 1;
      void load(approved);
    }, 320);
  },
);

function onDocClickOverflow() {
  overflowOpen.non = false;
  overflowOpen.app = false;
}

onMounted(() => {
  document.addEventListener("click", onDocClickOverflow);
  (
    window as unknown as {
      SNA_JS_CC_REFUNDSTAFF_BRINTEGRATION?: { refresh: () => void };
    }
  ).SNA_JS_CC_REFUNDSTAFF_BRINTEGRATION = {
    refresh: () => reloadAll(),
  };
  reloadAll();
});

onUnmounted(() => {
  document.removeEventListener("click", onDocClickOverflow);
  if (debNon) clearTimeout(debNon);
  if (debApp) clearTimeout(debApp);
  delete (window as unknown as { SNA_JS_CC_REFUNDSTAFF_BRINTEGRATION?: unknown }).SNA_JS_CC_REFUNDSTAFF_BRINTEGRATION;
});
</script>

<template>
  <AdminLayout>
    <div class="space-y-4">
      <input
        ref="templateInputNon"
        type="file"
        accept=".json,application/json"
        class="hidden"
        @change="onTplChangeNon"
      />
      <input
        ref="templateInputApp"
        type="file"
        accept=".json,application/json"
        class="hidden"
        @change="onTplChangeApp"
      />

      <h1 class="page-title">Credit Control / Refund / Refund (Staff) / List Of Refund Bill</h1>

      <article
        v-for="panel in panels()"
        :key="panel.key"
        class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm"
      >
        <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
          <h2 class="text-base font-semibold text-slate-900">{{ panel.cardTitle }}</h2>
          <div class="relative shrink-0">
            <button
              type="button"
              class="rounded-lg p-2 text-slate-500 hover:bg-slate-100"
              aria-label="More actions"
              @click.stop="toggleOverflow(panel.key)"
            >
              <MoreVertical class="h-4 w-4" />
            </button>
            <div
              v-if="overflowOpen[panel.key]"
              class="absolute right-0 z-30 mt-1 w-44 rounded-lg border border-slate-200 bg-white py-1 shadow-lg"
              @click.stop
            >
              <template v-if="panel.key === 'non'">
                <button
                  type="button"
                  class="block w-full px-3 py-2 text-left text-xs text-slate-700 hover:bg-slate-50"
                  @click="
                    closeOverflow('non');
                    saveTplNon();
                  "
                >
                  Save template
                </button>
                <button
                  type="button"
                  class="block w-full px-3 py-2 text-left text-xs text-slate-700 hover:bg-slate-50"
                  @click="
                    closeOverflow('non');
                    loadTplNon();
                  "
                >
                  Load template
                </button>
                <button
                  v-if="isGroupedNon"
                  type="button"
                  class="block w-full px-3 py-2 text-left text-xs text-slate-700 hover:bg-slate-50"
                  @click="
                    closeOverflow('non');
                    ungrpNon();
                  "
                >
                  Ungroup list
                </button>
                <button
                  v-else
                  type="button"
                  class="block w-full px-3 py-2 text-left text-xs text-slate-700 hover:bg-slate-50"
                  @click="
                    closeOverflow('non');
                    grpNon();
                  "
                >
                  Group list
                </button>
              </template>
              <template v-else>
                <button
                  type="button"
                  class="block w-full px-3 py-2 text-left text-xs text-slate-700 hover:bg-slate-50"
                  @click="
                    closeOverflow('app');
                    saveTplApp();
                  "
                >
                  Save template
                </button>
                <button
                  type="button"
                  class="block w-full px-3 py-2 text-left text-xs text-slate-700 hover:bg-slate-50"
                  @click="
                    closeOverflow('app');
                    loadTplApp();
                  "
                >
                  Load template
                </button>
                <button
                  v-if="isGroupedApp"
                  type="button"
                  class="block w-full px-3 py-2 text-left text-xs text-slate-700 hover:bg-slate-50"
                  @click="
                    closeOverflow('app');
                    ungrpApp();
                  "
                >
                  Ungroup list
                </button>
                <button
                  v-else
                  type="button"
                  class="block w-full px-3 py-2 text-left text-xs text-slate-700 hover:bg-slate-50"
                  @click="
                    closeOverflow('app');
                    grpApp();
                  "
                >
                  Group list
                </button>
              </template>
            </div>
          </div>
        </div>

        <div class="space-y-4 p-4">
          <div class="flex flex-wrap items-end justify-between gap-4">
            <div class="flex items-center gap-2">
              <label class="text-xs font-medium text-slate-600">Display</label>
              <select v-model.number="panel.limit" class="rounded-lg border border-slate-300 px-2 py-1.5 text-sm">
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
                  v-model="panel.q"
                  type="search"
                  placeholder="Filter rows..."
                  class="w-56 rounded-lg border border-slate-300 py-1.5 pl-8 pr-8 text-sm"
                  autocomplete="off"
                  :aria-label="`Search ${panel.title}`"
                  @keyup.enter="panel.page = 1; void load(panel)"
                />
                <button
                  v-if="panel.q"
                  type="button"
                  class="absolute right-1 top-1/2 -translate-y-1/2 rounded p-0.5 text-slate-400 hover:bg-slate-100"
                  aria-label="Clear search"
                  @click="panel.q = ''; panel.page = 1; void load(panel)"
                >
                  <X class="h-3.5 w-3.5" />
                </button>
              </div>
              <button
                type="button"
                class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm"
                @click="openSmartModal(panel.key)"
              >
                <Filter class="h-4 w-4" />Filter
              </button>
            </div>
          </div>

          <div class="overflow-x-auto rounded-lg border border-slate-200">
            <div :class="panel.rows.length > 10 ? 'max-h-[420px] overflow-y-auto' : ''">
              <table class="admin-table-kitchen w-full min-w-[1100px] text-sm">
                <thead class="admin-table-thead-sticky">
                  <tr class="border-b border-slate-200 text-left">
                    <th class="px-3 py-2 text-xs font-semibold uppercase">No</th>
                    <th
                      class="cursor-pointer px-3 py-2 text-xs font-semibold uppercase"
                      @click="toggleSort(panel, 'bim_bills_no')"
                    >
                      BRF No
                    </th>
                    <th class="px-3 py-2 text-xs font-semibold uppercase">Type BRF</th>
                    <th class="px-3 py-2 text-xs font-semibold uppercase">Payee Code</th>
                    <th class="px-3 py-2 text-xs font-semibold uppercase">Payee Name</th>
                    <th class="px-3 py-2 text-xs font-semibold uppercase">Description</th>
                    <th
                      class="cursor-pointer px-3 py-2 text-right text-xs font-semibold uppercase"
                      @click="toggleSort(panel, 'bim_bill_amt')"
                    >
                      Amount (RM)
                    </th>
                    <th
                      class="cursor-pointer px-3 py-2 text-xs font-semibold uppercase"
                      @click="toggleSort(panel, 'bim_status')"
                    >
                      Status
                    </th>
                    <th
                      class="cursor-pointer px-3 py-2 text-xs font-semibold uppercase"
                      @click="toggleSort(panel, 'bim_cust_invoice_date')"
                    >
                      Refund Date
                    </th>
                    <th class="px-3 py-2 text-center text-xs font-semibold uppercase">Action</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-if="panel.loading">
                    <td colspan="10" class="px-3 py-6 text-center text-sm text-slate-500">Loading...</td>
                  </tr>
                  <tr v-else-if="panel.rows.length === 0">
                    <td colspan="10" class="px-3 py-6 text-center text-sm text-slate-500">No records found.</td>
                  </tr>
                  <tr
                    v-for="row in panel.rows"
                    v-else
                    :key="`${row.bimBillsId}-${row.index}`"
                    class="border-b border-slate-100 hover:bg-slate-50"
                  >
                    <td class="px-3 py-2">{{ row.index }}</td>
                    <td class="px-3 py-2 font-medium text-slate-900">{{ row.bimBillsNo }}</td>
                    <td class="px-3 py-2">{{ row.bimBillsType }}</td>
                    <td class="px-3 py-2">{{ row.bimPaytoId }}</td>
                    <td class="max-w-[12rem] truncate px-3 py-2 sm:max-w-none" :title="row.bimPaytoName ?? ''">
                      {{ row.bimPaytoName }}
                    </td>
                    <td class="max-w-[14rem] truncate px-3 py-2" :title="row.bimBillsDesc ?? ''">{{ row.bimBillsDesc }}</td>
                    <td class="px-3 py-2 text-right tabular-nums">{{ fmtMoney(row.bimBillAmt) }}</td>
                    <td class="px-3 py-2">
                      <span
                        class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-700"
                      >
                        {{ row.bimStatus ?? "—" }}
                      </span>
                    </td>
                    <td class="whitespace-nowrap px-3 py-2">{{ fmtRefundDate(row) }}</td>
                    <td class="px-3 py-2">
                      <div class="flex flex-wrap items-center justify-center gap-1">
                        <a
                          :href="row.viewUrl"
                          class="text-sky-600 hover:underline"
                          target="_blank"
                          rel="noopener noreferrer"
                        >
                          <ExternalLink class="mr-0.5 inline h-3.5 w-3.5" />View
                        </a>
                        <span class="text-slate-300">|</span>
                        <a :href="row.editUrl" class="text-sky-600 hover:underline" target="_blank" rel="noopener noreferrer">
                          Edit
                        </a>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-3">
            <div class="text-xs text-slate-500">
              Showing {{ showingFrom(panel) }}-{{ showingTo(panel) }} of {{ panel.total }}
            </div>
            <div class="flex items-center gap-2">
              <button
                type="button"
                :disabled="panel.page <= 1 || panel.loading"
                class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium disabled:opacity-50"
                @click="prevPage(panel)"
              >
                Prev
              </button>
              <span class="text-xs text-slate-600">Page {{ panel.page }} / {{ totalPages(panel) }}</span>
              <button
                type="button"
                :disabled="panel.page >= totalPages(panel) || panel.loading"
                class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium disabled:opacity-50"
                @click="nextPage(panel)"
              >
                Next
              </button>
            </div>
          </div>
        </div>
      </article>

      <Teleport to="body">
        <div
          v-if="smartModalFor"
          class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
          @click.self="closeSmartModal()"
        >
          <div class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-xl bg-white shadow-xl">
            <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
              <h3 class="text-sm font-semibold text-slate-900">Smart filter</h3>
              <button type="button" class="rounded p-1 text-slate-500 hover:bg-slate-100" @click="closeSmartModal()">
                <X class="h-4 w-4" />
              </button>
            </div>
            <div v-if="smartModalFor === 'non'" class="space-y-3 px-4 py-4 text-sm">
              <label class="block">
                <span class="text-xs font-medium text-slate-600">BRF no</span>
                <input v-model="smartFilterNon.bim_bills_no" class="mt-1 w-full rounded-lg border border-slate-300 px-2 py-1.5" />
              </label>
              <label class="block">
                <span class="text-xs font-medium text-slate-600">Type (I/G)</span>
                <input v-model="smartFilterNon.bim_bills_type" class="mt-1 w-full rounded-lg border border-slate-300 px-2 py-1.5" />
              </label>
              <label class="block">
                <span class="text-xs font-medium text-slate-600">Payee code</span>
                <input v-model="smartFilterNon.bim_payto_id" class="mt-1 w-full rounded-lg border border-slate-300 px-2 py-1.5" />
              </label>
              <label class="block">
                <span class="text-xs font-medium text-slate-600">Payee name (contains)</span>
                <input v-model="smartFilterNon.bim_payto_name" class="mt-1 w-full rounded-lg border border-slate-300 px-2 py-1.5" />
              </label>
              <label class="block">
                <span class="text-xs font-medium text-slate-600">Description</span>
                <input v-model="smartFilterNon.bim_bills_desc" class="mt-1 w-full rounded-lg border border-slate-300 px-2 py-1.5" />
              </label>
              <label class="block">
                <span class="text-xs font-medium text-slate-600">Status</span>
                <input v-model="smartFilterNon.bim_status" class="mt-1 w-full rounded-lg border border-slate-300 px-2 py-1.5" />
              </label>
              <label class="block">
                <span class="text-xs font-medium text-slate-600">Bill amount</span>
                <input v-model="smartFilterNon.bim_bill_amt" class="mt-1 w-full rounded-lg border border-slate-300 px-2 py-1.5" />
              </label>
              <label class="block">
                <span class="text-xs font-medium text-slate-600">Customer invoice no</span>
                <input v-model="smartFilterNon.bim_cust_invoice_no" class="mt-1 w-full rounded-lg border border-slate-300 px-2 py-1.5" />
              </label>
              <label class="block">
                <span class="text-xs font-medium text-slate-600">Refund date (dd/mm/yyyy)</span>
                <input v-model="smartFilterNon.bim_cust_invoice_date" class="mt-1 w-full rounded-lg border border-slate-300 px-2 py-1.5" />
              </label>
              <label class="block">
                <span class="text-xs font-medium text-slate-600">Created date (dd/mm/yyyy)</span>
                <input
                  v-model="smartFilterNon.createddate"
                  placeholder="31/12/2025"
                  class="mt-1 w-full rounded-lg border border-slate-300 px-2 py-1.5"
                />
              </label>
            </div>
            <div v-else-if="smartModalFor === 'app'" class="space-y-3 px-4 py-4 text-sm">
              <label class="block">
                <span class="text-xs font-medium text-slate-600">BRF no</span>
                <input v-model="smartFilterApp.bim_bills_no" class="mt-1 w-full rounded-lg border border-slate-300 px-2 py-1.5" />
              </label>
              <label class="block">
                <span class="text-xs font-medium text-slate-600">Type (I/G)</span>
                <input v-model="smartFilterApp.bim_bills_type" class="mt-1 w-full rounded-lg border border-slate-300 px-2 py-1.5" />
              </label>
              <label class="block">
                <span class="text-xs font-medium text-slate-600">Payee code</span>
                <input v-model="smartFilterApp.bim_payto_id" class="mt-1 w-full rounded-lg border border-slate-300 px-2 py-1.5" />
              </label>
              <label class="block">
                <span class="text-xs font-medium text-slate-600">Payee name (contains)</span>
                <input v-model="smartFilterApp.bim_payto_name" class="mt-1 w-full rounded-lg border border-slate-300 px-2 py-1.5" />
              </label>
              <label class="block">
                <span class="text-xs font-medium text-slate-600">Description</span>
                <input v-model="smartFilterApp.bim_bills_desc" class="mt-1 w-full rounded-lg border border-slate-300 px-2 py-1.5" />
              </label>
              <label class="block">
                <span class="text-xs font-medium text-slate-600">Status</span>
                <input v-model="smartFilterApp.bim_status" class="mt-1 w-full rounded-lg border border-slate-300 px-2 py-1.5" />
              </label>
              <label class="block">
                <span class="text-xs font-medium text-slate-600">Bill amount</span>
                <input v-model="smartFilterApp.bim_bill_amt" class="mt-1 w-full rounded-lg border border-slate-300 px-2 py-1.5" />
              </label>
              <label class="block">
                <span class="text-xs font-medium text-slate-600">Customer invoice no</span>
                <input v-model="smartFilterApp.bim_cust_invoice_no" class="mt-1 w-full rounded-lg border border-slate-300 px-2 py-1.5" />
              </label>
              <label class="block">
                <span class="text-xs font-medium text-slate-600">Refund date (dd/mm/yyyy)</span>
                <input v-model="smartFilterApp.bim_cust_invoice_date" class="mt-1 w-full rounded-lg border border-slate-300 px-2 py-1.5" />
              </label>
              <label class="block">
                <span class="text-xs font-medium text-slate-600">Created date (dd/mm/yyyy)</span>
                <input
                  v-model="smartFilterApp.createddate"
                  placeholder="31/12/2025"
                  class="mt-1 w-full rounded-lg border border-slate-300 px-2 py-1.5"
                />
              </label>
            </div>
            <div class="flex justify-end gap-2 border-t border-slate-100 px-4 py-3">
              <button
                type="button"
                class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm text-slate-700 hover:bg-slate-50"
                @click="resetFromModal()"
              >
                Reset
              </button>
              <button
                type="button"
                class="rounded-lg bg-violet-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-violet-700"
                @click="applyFromModal()"
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
