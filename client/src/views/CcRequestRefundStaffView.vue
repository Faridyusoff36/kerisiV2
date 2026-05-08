<script setup lang="ts">
/**
 * Credit Control / Refund (Staff) / Request Refund — MENUID **2291** (2.0).
 *
 * Kerisi 1.0 mapping: **MENUID 2465**, **PAGEID 2018** — `SNA_JS_CREDITCONTROL_REQUESTREFUNDSTAFF` /
 * `SNA_API_CREDITCONTROL_REQUESTREFUNDSTAFF`, query `temp_refund_application` with
 * `tra_status = 'APPLY'` and `tra_payto_type = 'B'`, global filter on the legacy CONCAT_WS haystack,
 * `reference` = CONCAT_WS(' - ', tra_ref_no, tra_ref_no_note), ORDER BY tra_application_no DESC.
 *
 * KerisiAI `CREDIT CONTROL_LEVEL 4.json`: the **dt_listapply** datatable is filed under **MENUID 2465**
 * in that export (not present in kerisi-menu-migrated); behaviour is implemented here per menu **2291**.
 * Rows labelled **MENUID 2291 / PAGE 1873** in the same JSON (`API_LIST_OF_REFUND_CC`) correspond to **MENUID 2604**
 * in this SPA (`CcListOfRefundPortalView`).
 *
 * Legacy onload: `SNA_JS_CREDITCONTROL_REQUESTREFUNDSTAFF`.
 * Legacy API: `SNA_API_CREDITCONTROL_REQUESTREFUNDSTAFF` — `dt_listapply`.
 */
import { computed, onMounted, onUnmounted, ref, watch } from "vue";
import { ChevronDown, ChevronUp, ExternalLink, MoreVertical, Search, X } from "lucide-vue-next";
import AdminLayout from "@/layouts/AdminLayout.vue";
import { listCreditControlRequestRefundStaff } from "@/api/cms";
import { useDatatableFeatures } from "@/composables/useDatatableFeatures";
import type { DatatableRefApi } from "@/composables/useDatatableFeatures";
import { useToast } from "@/composables/useToast";
import type { CcRequestRefundStaffRow } from "@/types";

const toast = useToast();
const datatableRef = ref<DatatableRefApi | null>(null);
const rows = ref<CcRequestRefundStaffRow[]>([]);
const page = ref(1);
const limit = ref(10);
const q = ref("");
const total = ref(0);
const loading = ref(false);

type SortKey =
  | "application_no"
  | "amount"
  | "staff_id"
  | "staff_name"
  | "reference"
  | "fund_type"
  | "activity_code"
  | "ptj"
  | "cost_center"
  | "account_code"
  | "status"
  | "request_by"
  | "request_date";
/** Default aligns with legacy: ORDER BY tra_application_no DESC. */
const sortBy = ref<SortKey>("application_no");
const sortDir = ref<"asc" | "desc">("desc");

const totalPages = computed(() =>
  total.value ? Math.max(1, Math.ceil(total.value / limit.value)) : 1,
);

const showingFrom = computed(() =>
  total.value === 0 ? 0 : (page.value - 1) * limit.value + 1,
);
const showingTo = computed(() => Math.min(page.value * limit.value, total.value));

const exportColumns = [
  "No",
  "Application No",
  "Amount (RM)",
  "Staff ID",
  "Staff Name",
  "Reference",
  "Fund Type",
  "Activity Code",
  "PTJ",
  "Cost Center",
  "Account Code",
  "Status",
  "Request By",
  "Request Date",
];

function fmtMoney(n: number | null | undefined): string {
  if (n == null || Number.isNaN(n)) return "";
  return new Intl.NumberFormat("en-MY", { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(n);
}

function toggleSort(col: SortKey) {
  if (sortBy.value === col) sortDir.value = sortDir.value === "asc" ? "desc" : "asc";
  else {
    sortBy.value = col;
    sortDir.value = col === "request_date" || col === "application_no" ? "desc" : "asc";
  }
  page.value = 1;
  void loadRows();
}

function sortChevron(col: SortKey) {
  if (sortBy.value !== col) return null;
  return sortDir.value === "asc" ? "up" : "down";
}

async function loadRows() {
  loading.value = true;
  const params = new URLSearchParams({
    page: String(page.value),
    limit: String(limit.value),
    sort_by: sortBy.value,
    sort_dir: sortDir.value,
    ...(q.value.trim() ? { q: q.value.trim() } : {}),
  });
  try {
    const res = await listCreditControlRequestRefundStaff(`?${params.toString()}`);
    rows.value = res.data;
    total.value = Number(res.meta?.total ?? 0);
  } catch (e) {
    toast.error("Load failed", e instanceof Error ? e.message : "Unable to load refund requests.");
  } finally {
    loading.value = false;
  }
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

function rowExport(r: CcRequestRefundStaffRow) {
  return {
    No: r.index,
    "Application No": r.applicationNo ?? "",
    "Amount (RM)": r.amountRm != null ? fmtMoney(r.amountRm) : "",
    "Staff ID": r.staffId ?? "",
    "Staff Name": r.staffName ?? "",
    Reference: r.reference ?? "",
    "Fund Type": r.fundType ?? "",
    "Activity Code": r.activityCode ?? "",
    PTJ: r.ptj ?? "",
    "Cost Center": r.costCenter ?? "",
    "Account Code": r.accountCode ?? "",
    Status: r.status ?? "",
    "Request By": r.requestBy ?? "",
    "Request Date": r.requestDate ?? "",
  };
}

const overflowOpen = ref(false);
const overflowRoot = ref<HTMLElement | null>(null);

function onClickOutside(event: MouseEvent) {
  if (!overflowOpen.value) return;
  if (overflowRoot.value?.contains(event.target as Node)) return;
  overflowOpen.value = false;
}

const {
  isGrouped,
  handleSaveTemplate,
  handleLoadTemplate,
  handleUngroupList,
  handleGroupList,
  templateFileInputRef,
  onTemplateFileChange,
} = useDatatableFeatures({
  pageName: "Request Refund (Staff)",
  apiDataPath: "/credit-control/request-refund-staff",
  defaultExportColumns: exportColumns,
  getFilteredList: () => rows.value.map((r) => rowExport(r) as Record<string, unknown>),
  datatableRef,
  searchKeyword: q,
  smartFilter: ref({}),
  applyFilters: () => void loadRows(),
});

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

onMounted(() => {
  document.addEventListener("click", onClickOutside);
  datatableRef.value = {
    getExportConfig: () => ({
      columns: exportColumns,
      data: rows.value.map((r) => rowExport(r) as Record<string, unknown>),
    }),
  };
  (window as unknown as { SNA_JS_CREDITCONTROL_REQUESTREFUNDSTAFF?: { refresh: () => void } })
    .SNA_JS_CREDITCONTROL_REQUESTREFUNDSTAFF = {
    refresh: () => void loadRows(),
  };
  void loadRows();
});

onUnmounted(() => {
  document.removeEventListener("click", onClickOutside);
  if (searchDeb) clearTimeout(searchDeb);
  delete (window as unknown as { SNA_JS_CREDITCONTROL_REQUESTREFUNDSTAFF?: unknown })
    .SNA_JS_CREDITCONTROL_REQUESTREFUNDSTAFF;
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

      <h1 class="page-title">Credit Control / Refund (Staff) / Request Refund</h1>

      <article class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <!-- Kerisi 1.0-style panel: (1) title + overflow (2) Display | Search (3) exports (4) grid -->
        <div class="flex items-center justify-between gap-2 border-b border-slate-100 px-4 py-3">
          <h2 class="text-base font-semibold text-slate-900">Request Refund</h2>
          <div ref="overflowRoot" class="relative shrink-0">
            <button
              type="button"
              class="rounded-lg p-2 text-slate-500 hover:bg-slate-100"
              @click.stop="overflowOpen = !overflowOpen"
              aria-label="More actions"
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

        <div class="flex flex-col gap-3 border-b border-slate-100 px-4 py-2.5 sm:flex-row sm:items-center sm:justify-between">
          <div class="flex flex-wrap items-center gap-2">
            <label class="text-xs font-medium text-slate-600">Display</label>
            <select v-model.number="limit" class="rounded-lg border border-slate-300 px-2 py-1.5 text-sm">
              <option v-for="n in [5, 10, 25, 50, 100]" :key="n" :value="n">{{ n }}</option>
            </select>
          </div>
          <div class="flex min-w-0 flex-1 flex-wrap items-center justify-end gap-2 sm:max-w-xl lg:max-w-2xl">
            <label class="shrink-0 text-xs font-medium text-slate-600">Search</label>
            <div class="relative min-w-0 flex-1 sm:min-w-[12rem]">
              <Search class="pointer-events-none absolute left-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400" />
              <input
                v-model="q"
                type="search"
                placeholder="Application no., staff, reference, fund, PTJ, account…"
                class="w-full rounded-lg border border-slate-300 py-1.5 pl-8 pr-8 text-sm"
                autocomplete="off"
                aria-label="Search refund requests"
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

        <div class="space-y-3 px-4 pb-4 pt-3">
          <details class="text-xs text-slate-500">
            <summary class="cursor-pointer select-none font-medium text-slate-600">Screen notes (legacy parity)</summary>
            <p class="mt-2 leading-relaxed">
              This grid matches legacy
              <code class="rounded bg-slate-100 px-1">dt_listapply</code>
              —
              <code class="rounded bg-slate-100 px-1">temp_refund_application</code>
              rows with
              <strong>tra_status = APPLY</strong>
              and
              <strong>tra_payto_type = B</strong>
              .
              Search uses the same
              <code class="rounded bg-slate-100 px-1">CONCAT_WS</code>
              haystack as Kerisi Classic; it does
              <strong>not</strong>
              filter by
              <code class="rounded bg-slate-100 px-1">tra_id</code>
              .
              <strong>Open</strong>
              goes to Refund Application (2286) with search prefilled (
              <code class="rounded bg-slate-100 px-1">?q=</code>
              application no. or numeric
              <code class="rounded bg-slate-100 px-1">tra_id</code>
              ).
            </p>
          </details>

          <div class="max-h-[min(28rem,70vh)] overflow-y-auto overflow-x-auto rounded-lg border border-slate-200">
            <table class="min-w-[1040px] divide-y divide-slate-200 text-left text-xs sm:min-w-full">
              <thead class="sticky top-0 z-10 bg-slate-50 text-slate-600">
                <tr>
                  <th class="whitespace-nowrap px-2 py-2 font-medium">No</th>
                  <th class="whitespace-nowrap px-2 py-2 font-medium">
                    <button
                      type="button"
                      class="inline-flex items-center gap-0.5 hover:text-slate-900"
                      @click="toggleSort('application_no')"
                    >
                      Application No
                      <ChevronDown
                        v-if="sortChevron('application_no') === 'down'"
                        class="h-3 w-3 shrink-0 text-slate-500"
                      />
                      <ChevronUp
                        v-else-if="sortChevron('application_no') === 'up'"
                        class="h-3 w-3 shrink-0 text-slate-500"
                      />
                    </button>
                  </th>
                  <th class="whitespace-nowrap px-2 py-2 text-right font-medium">
                    <button
                      type="button"
                      class="inline-flex w-full items-center justify-end gap-0.5 hover:text-slate-900"
                      @click="toggleSort('amount')"
                    >
                      Amount (RM)
                      <ChevronDown v-if="sortChevron('amount') === 'down'" class="h-3 w-3 shrink-0 text-slate-500" />
                      <ChevronUp v-else-if="sortChevron('amount') === 'up'" class="h-3 w-3 shrink-0 text-slate-500" />
                    </button>
                  </th>
                  <th class="whitespace-nowrap px-2 py-2 font-medium">
                    <button type="button" class="inline-flex items-center gap-0.5 hover:text-slate-900" @click="toggleSort('staff_id')">
                      Staff ID
                      <ChevronDown v-if="sortChevron('staff_id') === 'down'" class="h-3 w-3 shrink-0 text-slate-500" />
                      <ChevronUp v-else-if="sortChevron('staff_id') === 'up'" class="h-3 w-3 shrink-0 text-slate-500" />
                    </button>
                  </th>
                  <th class="whitespace-nowrap px-2 py-2 font-medium">
                    <button type="button" class="inline-flex items-center gap-0.5 hover:text-slate-900" @click="toggleSort('staff_name')">
                      Staff Name
                      <ChevronDown v-if="sortChevron('staff_name') === 'down'" class="h-3 w-3 shrink-0 text-slate-500" />
                      <ChevronUp v-else-if="sortChevron('staff_name') === 'up'" class="h-3 w-3 shrink-0 text-slate-500" />
                    </button>
                  </th>
                  <th class="whitespace-nowrap px-2 py-2 font-medium">
                    <button type="button" class="inline-flex items-center gap-0.5 hover:text-slate-900" @click="toggleSort('reference')">
                      Reference
                      <ChevronDown v-if="sortChevron('reference') === 'down'" class="h-3 w-3 shrink-0 text-slate-500" />
                      <ChevronUp v-else-if="sortChevron('reference') === 'up'" class="h-3 w-3 shrink-0 text-slate-500" />
                    </button>
                  </th>
                  <th class="whitespace-nowrap px-2 py-2 font-medium">
                    <button type="button" class="inline-flex items-center gap-0.5 hover:text-slate-900" @click="toggleSort('fund_type')">
                      Fund Type
                      <ChevronDown v-if="sortChevron('fund_type') === 'down'" class="h-3 w-3 shrink-0 text-slate-500" />
                      <ChevronUp v-else-if="sortChevron('fund_type') === 'up'" class="h-3 w-3 shrink-0 text-slate-500" />
                    </button>
                  </th>
                  <th class="whitespace-nowrap px-2 py-2 font-medium">
                    <button type="button" class="inline-flex items-center gap-0.5 hover:text-slate-900" @click="toggleSort('activity_code')">
                      Activity Code
                      <ChevronDown v-if="sortChevron('activity_code') === 'down'" class="h-3 w-3 shrink-0 text-slate-500" />
                      <ChevronUp v-else-if="sortChevron('activity_code') === 'up'" class="h-3 w-3 shrink-0 text-slate-500" />
                    </button>
                  </th>
                  <th class="whitespace-nowrap px-2 py-2 font-medium">
                    <button type="button" class="inline-flex items-center gap-0.5 hover:text-slate-900" @click="toggleSort('ptj')">
                      PTJ
                      <ChevronDown v-if="sortChevron('ptj') === 'down'" class="h-3 w-3 shrink-0 text-slate-500" />
                      <ChevronUp v-else-if="sortChevron('ptj') === 'up'" class="h-3 w-3 shrink-0 text-slate-500" />
                    </button>
                  </th>
                  <th class="whitespace-nowrap px-2 py-2 font-medium">
                    <button type="button" class="inline-flex items-center gap-0.5 hover:text-slate-900" @click="toggleSort('cost_center')">
                      Cost Center
                      <ChevronDown v-if="sortChevron('cost_center') === 'down'" class="h-3 w-3 shrink-0 text-slate-500" />
                      <ChevronUp v-else-if="sortChevron('cost_center') === 'up'" class="h-3 w-3 shrink-0 text-slate-500" />
                    </button>
                  </th>
                  <th class="whitespace-nowrap px-2 py-2 font-medium">
                    <button type="button" class="inline-flex items-center gap-0.5 hover:text-slate-900" @click="toggleSort('account_code')">
                      Account Code
                      <ChevronDown v-if="sortChevron('account_code') === 'down'" class="h-3 w-3 shrink-0 text-slate-500" />
                      <ChevronUp v-else-if="sortChevron('account_code') === 'up'" class="h-3 w-3 shrink-0 text-slate-500" />
                    </button>
                  </th>
                  <th class="whitespace-nowrap px-2 py-2 font-medium">
                    <button type="button" class="inline-flex items-center gap-0.5 hover:text-slate-900" @click="toggleSort('status')">
                      Status
                      <ChevronDown v-if="sortChevron('status') === 'down'" class="h-3 w-3 shrink-0 text-slate-500" />
                      <ChevronUp v-else-if="sortChevron('status') === 'up'" class="h-3 w-3 shrink-0 text-slate-500" />
                    </button>
                  </th>
                  <th class="whitespace-nowrap px-2 py-2 font-medium">
                    <button type="button" class="inline-flex items-center gap-0.5 hover:text-slate-900" @click="toggleSort('request_by')">
                      Request By
                      <ChevronDown v-if="sortChevron('request_by') === 'down'" class="h-3 w-3 shrink-0 text-slate-500" />
                      <ChevronUp v-else-if="sortChevron('request_by') === 'up'" class="h-3 w-3 shrink-0 text-slate-500" />
                    </button>
                  </th>
                  <th class="whitespace-nowrap px-2 py-2 font-medium">
                    <button type="button" class="inline-flex items-center gap-0.5 hover:text-slate-900" @click="toggleSort('request_date')">
                      Request Date
                      <ChevronDown v-if="sortChevron('request_date') === 'down'" class="h-3 w-3 shrink-0 text-slate-500" />
                      <ChevronUp v-else-if="sortChevron('request_date') === 'up'" class="h-3 w-3 shrink-0 text-slate-500" />
                    </button>
                  </th>
                  <th class="whitespace-nowrap px-2 py-2 font-medium">Action</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 bg-white text-slate-800">
                <tr v-if="loading">
                  <td colspan="15" class="px-3 py-10 text-center text-slate-500">
                    <span class="inline-block animate-pulse">Loading refund requests…</span>
                  </td>
                </tr>
                <tr v-else-if="rows.length === 0">
                  <td colspan="15" class="px-3 py-10 text-center text-slate-500">
                    <p class="font-medium text-slate-700">No records</p>
                    <p class="mt-1 text-xs">Try another search, or confirm APPLY + pay-to B rows exist in the database.</p>
                  </td>
                </tr>
                <tr v-for="row in rows" v-else :key="`${row.traId}-${row.index}`" class="hover:bg-slate-50/80">
                  <td class="px-2 py-1.5">{{ row.index }}</td>
                  <td class="px-2 py-1.5 font-medium">{{ row.applicationNo }}</td>
                  <td class="px-2 py-1.5 text-right tabular-nums">{{ fmtMoney(row.amountRm) }}</td>
                  <td class="px-2 py-1.5">{{ row.staffId }}</td>
                  <td class="max-w-[8rem] truncate px-2 py-1.5 sm:max-w-none" :title="row.staffName ?? ''">
                    {{ row.staffName }}
                  </td>
                  <td class="max-w-[8rem] truncate px-2 py-1.5" :title="row.reference ?? ''">{{ row.reference }}</td>
                  <td class="px-2 py-1.5">{{ row.fundType }}</td>
                  <td class="px-2 py-1.5">{{ row.activityCode }}</td>
                  <td class="px-2 py-1.5">{{ row.ptj }}</td>
                  <td class="px-2 py-1.5">{{ row.costCenter }}</td>
                  <td class="max-w-[7rem] truncate px-2 py-1.5" :title="row.accountCode ?? ''">{{ row.accountCode }}</td>
                  <td class="px-2 py-1.5">{{ row.status }}</td>
                  <td class="px-2 py-1.5">{{ row.requestBy }}</td>
                  <td class="whitespace-nowrap px-2 py-1.5">{{ row.requestDate }}</td>
                  <td class="px-2 py-1.5">
                    <a
                      :href="row.actionUrl"
                      class="inline-flex items-center gap-0.5 text-violet-600 hover:underline"
                      target="_blank"
                      rel="noopener noreferrer"
                    >
                      <ExternalLink class="h-3 w-3" />
                      Open
                    </a>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div v-if="total > 0" class="flex flex-wrap items-center justify-between gap-3 text-xs text-slate-600">
            <span>Showing {{ showingFrom }}–{{ showingTo }} of {{ total }}</span>
            <div class="flex items-center gap-2">
              <button
                type="button"
                class="rounded border border-slate-300 px-2 py-1 hover:bg-slate-50 disabled:opacity-40"
                :disabled="page <= 1 || loading"
                @click="prevPage"
              >
                Prev
              </button>
              <span>Page {{ page }} / {{ totalPages }}</span>
              <button
                type="button"
                class="rounded border border-slate-300 px-2 py-1 hover:bg-slate-50 disabled:opacity-40"
                :disabled="page >= totalPages || loading"
                @click="nextPage"
              >
                Next
              </button>
            </div>
          </div>
        </div>
      </article>
    </div>
  </AdminLayout>
</template>
