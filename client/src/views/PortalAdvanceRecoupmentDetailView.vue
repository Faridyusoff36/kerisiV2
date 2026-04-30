<script setup lang="ts">
/**
 * Portal / Advance Staff / Recoupment — detail + debit lines (PAGEID 2235 / 2234 → MENUID 2712 / 2716).
 */
import { onMounted, onUnmounted, ref, watch } from "vue";
import { useRoute } from "vue-router";
import { Search, X } from "lucide-vue-next";
import AdminLayout from "@/layouts/AdminLayout.vue";
import { getPortalAdvanceRecoupHeader, listPortalAdvanceRecoupDebitLines } from "@/api/cms";
import { useToast } from "@/composables/useToast";
import type { PortalAdvanceRecoupDebitLineRow, PortalAdvanceRecoupHeader } from "@/types";

const props = withDefaults(
  defineProps<{
    pageBreadcrumb?: string;
    cardTitle?: string;
  }>(),
  {
    pageBreadcrumb: "Portal / Advance Staff / Declaration / Manage / Recoupment / Recoup Details",
    cardTitle: "Recoupment details",
  },
);

const route = useRoute();
const toast = useToast();
const header = ref<PortalAdvanceRecoupHeader | null>(null);
const rows = ref<PortalAdvanceRecoupDebitLineRow[]>([]);
const total = ref(0);
const page = ref(1);
const limit = ref(10);
const q = ref("");
const loading = ref(false);
const loadingLines = ref(false);
const footerAmt = ref<string | number | null>(null);

const mf = new Intl.NumberFormat("en-MY", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
function amt(v: unknown): string {
  if (v === null || v === undefined || v === "") return "-";
  const n = typeof v === "number" ? v : Number(String(v).replace(/,/g, ""));
  if (!Number.isFinite(n)) return String(v);
  return mf.format(n);
}

function billId(): string | null {
  const raw = route.query.bim_bills_id;
  if (raw === undefined || raw === null) return null;
  const s = Array.isArray(raw) ? raw[0] : raw;
  return s ? String(s) : null;
}

async function loadHeader() {
  const id = billId();
  if (!id) {
    header.value = null;
    return;
  }
  try {
    const res = await getPortalAdvanceRecoupHeader(id);
    header.value = res.data;
  } catch (e) {
    header.value = null;
    toast.error("Not found", e instanceof Error ? e.message : "Could not load header.");
  }
}

async function loadLines() {
  const id = billId();
  if (!id) {
    rows.value = [];
    total.value = 0;
    footerAmt.value = null;
    return;
  }
  loadingLines.value = true;
  try {
    const params = new URLSearchParams({
      page: String(page.value),
      limit: String(limit.value),
      ...(q.value ? { q: q.value } : {}),
    });
    const res = await listPortalAdvanceRecoupDebitLines(id, `?${params.toString()}`);
    rows.value = res.data ?? [];
    total.value = Number(res.meta?.total ?? 0);
    const f = res.meta?.footer as { bidAmt?: unknown } | undefined;
    const raw = f?.bidAmt;
    footerAmt.value =
      raw == null ? null : typeof raw === "string" || typeof raw === "number" ? raw : null;
  } catch (e) {
    rows.value = [];
    total.value = 0;
    footerAmt.value = null;
    toast.error("Debit lines failed", e instanceof Error ? e.message : "Could not load lines.");
  } finally {
    loadingLines.value = false;
  }
}

async function loadAll() {
  loading.value = true;
  try {
    await Promise.all([loadHeader(), loadLines()]);
  } finally {
    loading.value = false;
  }
}

function totalPages() {
  return Math.max(1, Math.ceil(total.value / Math.max(1, limit.value)));
}

function prevPage() {
  if (page.value > 1) {
    page.value -= 1;
    void loadLines();
  }
}
function nextPage() {
  if (page.value < totalPages()) {
    page.value += 1;
    void loadLines();
  }
}

let searchDeb: ReturnType<typeof setTimeout> | null = null;

watch(
  () => route.query.bim_bills_id,
  () => {
    page.value = 1;
    void loadAll();
  },
);

watch(q, () => {
  if (searchDeb) clearTimeout(searchDeb);
  searchDeb = setTimeout(() => {
    searchDeb = null;
    page.value = 1;
    void loadLines();
  }, 350);
});

watch(limit, () => {
  page.value = 1;
  void loadLines();
});

onMounted(() => void loadAll());
onUnmounted(() => {
  if (searchDeb) clearTimeout(searchDeb);
});
</script>

<template>
  <AdminLayout>
    <div class="space-y-4">
      <h1 class="page-title">{{ pageBreadcrumb }}</h1>

      <article class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-4 py-3">
          <h2 class="text-base font-semibold text-slate-900">{{ cardTitle }}</h2>
        </div>

        <div class="border-b border-amber-50 bg-amber-50/50 px-4 py-3 text-sm text-amber-950">
          Workflow submission and approvals (<code class="rounded bg-white/70 px-1">workflowSubmit</code> /
          <code class="rounded bg-white/70 px-1">workflowUpdate</code>) remain in Kerisi Classic.
        </div>

        <div v-if="!billId()" class="p-6 text-sm text-slate-600">
          Open this screen from
          <span class="font-medium">Recoupment Status</span> (action links) or add
          <code class="rounded bg-slate-100 px-1">bim_bills_id</code> to the query string.
        </div>

        <div v-else class="space-y-6 p-4">
          <section class="grid gap-3 rounded-lg border border-slate-100 bg-slate-50/60 p-4 text-sm md:grid-cols-2">
            <template v-if="header">
              <div><span class="text-xs text-slate-500">Bill no</span><div>{{ header.noBrc ?? "-" }}</div></div>
              <div><span class="text-xs text-slate-500">Status</span><div>{{ header.brcStatus ?? "-" }}</div></div>
              <div class="md:col-span-2">
                <span class="text-xs text-slate-500">Description</span>
                <div>{{ header.descBrc ?? "-" }}</div>
              </div>
              <div><span class="text-xs text-slate-500">Amount</span><div class="tabular-nums">{{ amt(header.brcAmt) }}</div></div>
              <div><span class="text-xs text-slate-500">Batch no</span><div>{{ header.batchNo ?? "-" }}</div></div>
              <div><span class="text-xs text-slate-500">Invoice no</span><div>{{ header.invNo ?? "-" }}</div></div>
              <div><span class="text-xs text-slate-500">Invoice date</span><div>{{ header.invDate ?? "-" }}</div></div>
              <div><span class="text-xs text-slate-500">Payee</span><div>{{ header.payeeCode ?? "-" }} · {{ header.payeeName ?? "" }}</div></div>
            </template>
            <template v-else-if="loading">
              <div class="md:col-span-2 text-slate-500">Loading header…</div>
            </template>
            <template v-else>
              <div class="md:col-span-2 text-slate-500">No header data.</div>
            </template>
          </section>

          <section>
            <h3 class="mb-3 text-sm font-semibold text-slate-900">Debit lines (activity)</h3>

            <div class="flex flex-wrap items-end justify-between gap-4 pb-3">
              <div class="flex items-center gap-2">
                <label class="text-xs font-medium text-slate-600">Display</label>
                <select
                  v-model.number="limit"
                  class="rounded-lg border border-slate-300 px-2 py-1.5 text-sm"
                >
                  <option v-for="n in [5, 10, 25, 50, 100]" :key="n" :value="n">{{ n }}</option>
                </select>
              </div>
              <div class="relative">
                <Search
                  class="pointer-events-none absolute left-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400"
                />
                <input
                  v-model="q"
                  type="search"
                  placeholder="Filter grouped debit rows…"
                  class="w-72 rounded-lg border border-slate-300 py-1.5 pl-8 pr-8 text-sm"
                />
                <button
                  v-if="q"
                  type="button"
                  class="absolute right-1 top-1/2 -translate-y-1/2 rounded p-0.5 text-slate-400 hover:bg-slate-100"
                  @click="q = ''"
                >
                  <X class="h-3.5 w-3.5" />
                </button>
              </div>
            </div>

            <div class="overflow-x-auto rounded-lg border border-slate-200">
              <table class="admin-table-kitchen w-full min-w-[980px] text-sm">
                <thead class="admin-table-thead-sticky">
                  <tr class="border-b border-slate-200 text-left">
                    <th class="px-3 py-2 text-xs font-semibold uppercase">No</th>
                    <th class="px-3 py-2 text-xs font-semibold uppercase">Fund / SO</th>
                    <th class="px-3 py-2 text-xs font-semibold uppercase">OU / CC / Activity</th>
                    <th class="px-3 py-2 text-xs font-semibold uppercase">Account</th>
                    <th class="px-3 py-2 text-xs font-semibold uppercase text-right">Amount</th>
                    <th class="px-3 py-2 text-xs font-semibold uppercase">Bank</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-if="loadingLines">
                    <td colspan="6" class="px-3 py-8 text-center text-slate-500">Loading…</td>
                  </tr>
                  <tr v-else-if="rows.length === 0">
                    <td colspan="6" class="px-3 py-8 text-center text-slate-500">No debit lines.</td>
                  </tr>
                  <tr
                    v-for="row in rows"
                    :key="row.index + '-' + String(row.xx ?? '') + String(row.soCode ?? '')"
                    class="border-b border-slate-100 hover:bg-slate-50"
                  >
                    <td class="px-3 py-2">{{ row.index }}</td>
                    <td class="px-3 py-2">
                      <div>{{ row.ftyFundType ?? "-" }}</div>
                      <div class="text-xs text-slate-500">SO {{ row.soCode ?? "-" }}</div>
                    </td>
                    <td class="px-3 py-2">
                      <div>{{ row.ounCode ?? "-" }}</div>
                      <div class="text-xs text-slate-500">{{ row.ccrCostcentre ?? "-" }} · {{ row.atActivityCode ?? "-" }}</div>
                    </td>
                    <td class="px-3 py-2 font-mono text-xs">{{ row.acmAcctCode ?? "-" }}</td>
                    <td class="px-3 py-2 text-right tabular-nums">{{ amt(row.bidAmt) }}</td>
                    <td class="px-3 py-2 text-xs">{{ row.bank ?? "-" }}</td>
                  </tr>
                </tbody>
                <tfoot v-if="rows.length">
                  <tr class="border-t border-slate-200 bg-slate-50 font-medium">
                    <td colspan="4" class="px-3 py-2 text-right">Total debit (filtered)</td>
                    <td class="px-3 py-2 text-right tabular-nums">{{ amt(footerAmt) }}</td>
                    <td />
                  </tr>
                </tfoot>
              </table>
            </div>

            <div class="mt-3 flex flex-wrap items-center justify-between gap-3 text-xs">
              <div class="text-slate-500">Grouped rows: {{ total }}</div>
              <div class="flex items-center gap-2">
                <button
                  type="button"
                  class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 disabled:opacity-40"
                  :disabled="page <= 1"
                  @click="prevPage"
                >
                  Prev
                </button>
                <span class="text-slate-600">Page {{ page }} / {{ totalPages() }}</span>
                <button
                  type="button"
                  class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 disabled:opacity-40"
                  :disabled="page >= totalPages()"
                  @click="nextPage"
                >
                  Next
                </button>
              </div>
            </div>
          </section>
        </div>
      </article>
    </div>
  </AdminLayout>
</template>
