<script setup lang="ts">
/**
 * Portal / Advance Staff / Recoupment / Generate Bill Recoupment old (PAGEID 1999 / MENUID 2442).
 */
import { onMounted, onUnmounted, ref, watch } from "vue";
import { Search, X } from "lucide-vue-next";
import AdminLayout from "@/layouts/AdminLayout.vue";
import { listPortalAdvanceGenerateBillBatches } from "@/api/cms";
import { useToast } from "@/composables/useToast";
import type { PortalAdvanceGenerateBillBatchRow } from "@/types";

const props = withDefaults(
  defineProps<{
    pageBreadcrumb?: string;
    cardTitle?: string;
  }>(),
  {
    pageBreadcrumb:
      "Portal / Advance Staff / Declaration / Manage / Recoupment / Generate Bill Recoupment old",
    cardTitle: "Cash advance batches (endorsed, ready to generate draft)",
  },
);

const toast = useToast();
const rows = ref<PortalAdvanceGenerateBillBatchRow[]>([]);
const total = ref(0);
const page = ref(1);
const limit = ref(10);
const q = ref("");
const loading = ref(false);

const mf = new Intl.NumberFormat("en-MY", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
function amt(v: unknown): string {
  if (v === null || v === undefined || v === "") return "-";
  const n = typeof v === "number" ? v : Number(String(v).replace(/,/g, ""));
  if (!Number.isFinite(n)) return String(v);
  return mf.format(n);
}

function formatDt(v: unknown): string {
  if (v === null || v === undefined || v === "") return "-";
  const s = String(v);
  const d = new Date(s.includes("T") ? s : s.replace(" ", "T"));
  if (!Number.isNaN(d.getTime())) {
    const dd = String(d.getDate()).padStart(2, "0");
    const mm = String(d.getMonth() + 1).padStart(2, "0");
    return `${dd}/${mm}/${d.getFullYear()}`;
  }
  return s;
}

async function loadRows() {
  loading.value = true;
  try {
    const params = new URLSearchParams({
      page: String(page.value),
      limit: String(limit.value),
      ...(q.value ? { q: q.value } : {}),
    });
    const res = await listPortalAdvanceGenerateBillBatches(`?${params.toString()}`);
    rows.value = res.data ?? [];
    total.value = Number(res.meta?.total ?? 0);
  } catch (e) {
    toast.error("Load failed", e instanceof Error ? e.message : "Could not load batches.");
    rows.value = [];
    total.value = 0;
  } finally {
    loading.value = false;
  }
}

const totalPages = () => Math.max(1, Math.ceil(total.value / Math.max(1, limit.value)));

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

let searchDeb: ReturnType<typeof setTimeout> | null = null;
watch(q, () => {
  if (searchDeb) clearTimeout(searchDeb);
  searchDeb = setTimeout(() => {
    searchDeb = null;
    page.value = 1;
    void loadRows();
  }, 350);
});

watch(limit, () => {
  page.value = 1;
  void loadRows();
});

onMounted(() => void loadRows());
onUnmounted(() => {
  if (searchDeb) clearTimeout(searchDeb);
});
</script>

<template>
  <AdminLayout>
    <div class="space-y-4">
      <h1 class="page-title">{{ pageBreadcrumb }}</h1>

      <article class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <p class="border-b border-amber-100 bg-amber-50/90 px-4 py-3 text-sm text-amber-950">
          Legacy bulk action “Disburse · generate draft bill” (<code class="rounded bg-white/80 px-1">disburse_submit</code>)
          is not ported in Kerisi20; use Kerisi Classic to create draft recoup bills from selected batches when required.
          This screen lists endorsed batches eligible for drafting (same filter as Classic).
        </p>
        <div class="border-b border-slate-100 px-4 py-3">
          <h2 class="text-base font-semibold text-slate-900">{{ cardTitle }}</h2>
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
            <div class="relative">
              <Search
                class="pointer-events-none absolute left-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400"
              />
              <input
                v-model="q"
                type="search"
                placeholder="Filter batch ID / status…"
                class="w-72 rounded-lg border border-slate-300 py-1.5 pl-8 pr-8 text-sm"
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

          <div class="overflow-x-auto rounded-lg border border-slate-200">
            <table class="admin-table-kitchen w-full min-w-[960px] text-sm">
              <thead class="admin-table-thead-sticky">
                <tr class="border-b border-slate-200 text-left">
                  <th class="px-3 py-2 text-xs font-semibold uppercase">No</th>
                  <th class="px-3 py-2 text-xs font-semibold uppercase">Batch ID</th>
                  <th class="px-3 py-2 text-xs font-semibold uppercase">Transaction no</th>
                  <th class="px-3 py-2 text-xs font-semibold uppercase">Batch amount</th>
                  <th class="px-3 py-2 text-xs font-semibold uppercase">Linked draft bill no</th>
                  <th class="px-3 py-2 text-xs font-semibold uppercase">Recoup status</th>
                  <th class="px-3 py-2 text-xs font-semibold uppercase">Date</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="loading">
                  <td colspan="7" class="px-3 py-8 text-center text-slate-500">Loading…</td>
                </tr>
                <tr v-else-if="rows.length === 0">
                  <td colspan="7" class="px-3 py-8 text-center text-slate-500">No records.</td>
                </tr>
                <tr
                  v-for="row in rows"
                  :key="String(row.cabId ?? '-') + '-' + String(row.index)"
                  class="border-b border-slate-100 hover:bg-slate-50"
                >
                  <td class="px-3 py-2">{{ row.index }}</td>
                  <td class="px-3 py-2 font-medium text-slate-900">{{ row.cabBatchId ?? "-" }}</td>
                  <td class="px-3 py-2">{{ row.cabTransNo ?? "-" }}</td>
                  <td class="px-3 py-2 text-right tabular-nums">{{ amt(row.cabBatchAmt) }}</td>
                  <td class="px-3 py-2">{{ row.cabLinkedBillsNo ?? "-" }}</td>
                  <td class="px-3 py-2">{{ row.bimStatusLabel ?? row.recoupMasterStatusRaw ?? "-" }}</td>
                  <td class="px-3 py-2">{{ formatDt(row.cabRecoupDate) }}</td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-3 text-xs">
            <div class="text-slate-500">Total rows: {{ total }}</div>
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
        </div>
      </article>
    </div>
  </AdminLayout>
</template>
