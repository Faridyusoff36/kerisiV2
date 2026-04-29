<script setup lang="ts">
/**
 * Portal / Advance Staff / Recoupment / Recoupment Status (PAGEID 2233 / MENUID 2714).
 */
import { computed, onMounted, onUnmounted, ref, watch } from "vue";
import { Eye, Search, X } from "lucide-vue-next";
import AdminLayout from "@/layouts/AdminLayout.vue";
import { listPortalAdvanceRecoupBills } from "@/api/cms";
import { useToast } from "@/composables/useToast";
import { useRouter } from "vue-router";
import type { PortalAdvanceRecoupBillRow } from "@/types";

const props = withDefaults(
  defineProps<{
    pageBreadcrumb?: string;
    cardTitle?: string;
  }>(),
  {
    pageBreadcrumb:
      "Portal / Advance Staff / Declaration / Manage / Recoupment / Recoupment Status",
    cardTitle: "Recoupment bills",
  },
);

const toast = useToast();
const router = useRouter();
const section = ref<"pending" | "approved">("pending");
const rows = ref<PortalAdvanceRecoupBillRow[]>([]);
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
      section: section.value,
      ...(q.value ? { q: q.value } : {}),
    });
    const res = await listPortalAdvanceRecoupBills(`?${params.toString()}`);
    rows.value = res.data ?? [];
    total.value = Number(res.meta?.total ?? 0);
  } catch (e) {
    toast.error("Load failed", e instanceof Error ? e.message : "Could not load recoupment bills.");
    rows.value = [];
    total.value = 0;
  } finally {
    loading.value = false;
  }
}

const totalPages = computed(() => Math.max(1, Math.ceil(total.value / Math.max(1, limit.value))));

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

function openDetail(row: PortalAdvanceRecoupBillRow, mode: "detail" | "draft") {
  const id = row.bimBillsId;
  if (id === null || id === undefined || String(id) === "") {
    toast.info("Missing key", "No bill id for this row.");
    return;
  }
  const path = mode === "draft" ? "/admin/kerisi/m/2716" : "/admin/kerisi/m/2712";
  void router.push({ path, query: { bim_bills_id: String(id) } });
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

watch(section, () => {
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
        <div class="border-b border-slate-100 px-4 py-3">
          <h2 class="text-base font-semibold text-slate-900">{{ cardTitle }}</h2>
          <p class="mt-1 text-xs text-slate-500">
            Pending = not approved; Approved = status APPROVE (legacy <code class="rounded bg-slate-100 px-1">dt_RecoupList</code> /
            <code class="rounded bg-slate-100 px-1">dt_RecoupListApproved</code>).
          </p>
        </div>

        <div class="space-y-4 p-4">
          <div class="flex flex-wrap items-center gap-2">
            <button
              type="button"
              class="rounded-lg border px-3 py-1.5 text-sm font-medium"
              :class="
                section === 'pending'
                  ? 'border-slate-900 bg-slate-900 text-white'
                  : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50'
              "
              @click="section = 'pending'"
            >
              Pending
            </button>
            <button
              type="button"
              class="rounded-lg border px-3 py-1.5 text-sm font-medium"
              :class="
                section === 'approved'
                  ? 'border-slate-900 bg-slate-900 text-white'
                  : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50'
              "
              @click="section = 'approved'"
            >
              Approved
            </button>
          </div>

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
                placeholder="Search…"
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
            <table class="w-full min-w-[1100px] text-sm">
              <thead class="bg-slate-50">
                <tr class="border-b border-slate-200 text-left">
                  <th class="px-3 py-2 text-xs font-semibold uppercase">No</th>
                  <th class="px-3 py-2 text-xs font-semibold uppercase">Recoup no</th>
                  <th class="px-3 py-2 text-xs font-semibold uppercase">Payee</th>
                  <th class="px-3 py-2 text-xs font-semibold uppercase">Description</th>
                  <th class="px-3 py-2 text-xs font-semibold uppercase">Amount</th>
                  <th class="px-3 py-2 text-xs font-semibold uppercase">Status</th>
                  <th class="px-3 py-2 text-xs font-semibold uppercase">Created</th>
                  <th class="px-3 py-2 text-xs font-semibold uppercase">Action</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="loading">
                  <td colspan="8" class="px-3 py-8 text-center text-slate-500">Loading…</td>
                </tr>
                <tr v-else-if="rows.length === 0">
                  <td colspan="8" class="px-3 py-8 text-center text-slate-500">No records.</td>
                </tr>
                <tr
                  v-for="row in rows"
                  :key="String(row.bimBillsId) + '-' + row.index"
                  class="border-b border-slate-100 hover:bg-slate-50"
                >
                  <td class="px-3 py-2">{{ row.index }}</td>
                  <td class="px-3 py-2 font-medium text-slate-900">{{ row.bimBillsNo ?? "-" }}</td>
                  <td class="px-3 py-2">
                    <div>{{ row.bimPaytoId ?? "-" }}</div>
                    <div class="text-xs text-slate-500">{{ row.bimPaytoName ?? "" }}</div>
                  </td>
                  <td class="px-3 py-2 text-slate-700">{{ row.bimBillsDesc ?? "-" }}</td>
                  <td class="px-3 py-2 text-right tabular-nums">{{ amt(row.bimBillAmt) }}</td>
                  <td class="px-3 py-2">{{ row.bimStatus ?? "-" }}</td>
                  <td class="px-3 py-2">{{ formatDt(row.createddate) }}</td>
                  <td class="px-3 py-2">
                    <div class="flex flex-wrap gap-1">
                      <button
                        type="button"
                        class="inline-flex items-center gap-1 rounded border border-slate-200 px-2 py-1 text-xs hover:bg-slate-50"
                        title="Recoup details"
                        @click="openDetail(row, 'detail')"
                      >
                        <Eye class="h-3.5 w-3.5" /> Detail
                      </button>
                      <button
                        type="button"
                        class="inline-flex items-center gap-1 rounded border border-slate-200 px-2 py-1 text-xs hover:bg-slate-50"
                        title="Draft screen (menu 2716)"
                        @click="openDetail(row, 'draft')"
                      >
                        Draft
                      </button>
                    </div>
                  </td>
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
              <span class="text-slate-600">Page {{ page }} / {{ totalPages }}</span>
              <button
                type="button"
                class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 disabled:opacity-40"
                :disabled="page >= totalPages"
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
