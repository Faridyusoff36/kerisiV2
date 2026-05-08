<script setup lang="ts">
/**
 * Kew PA 1–2 style registers from `goods_receive_master` — menus 2589 / 2597.
 */
import { computed, onMounted, ref, watch } from "vue";
import { useRoute } from "vue-router";
import { Search, X } from "lucide-vue-next";

import AdminLayout from "@/layouts/AdminLayout.vue";
import FimsListTable, { type FimsColumn } from "@/components/fims/FimsListTable.vue";
import { listAssetGoodsReceiveKewpa } from "@/api/cms";
import { useToast } from "@/composables/useToast";
import type { AssetKewPaGrnRow } from "@/types";

const route = useRoute();

const pageTitle = computed(() => (typeof route.meta.title === "string" ? route.meta.title : "Kew PA"));

const variant = computed(() => (route.meta.kewPaVariant === "reject" ? "reject" : "receive"));

const toast = useToast();
const rows = ref<AssetKewPaGrnRow[]>([]);
const loading = ref(false);
const total = ref(0);
const page = ref(1);
const limit = ref(15);
const q = ref("");
const sortDir = ref<"asc" | "desc">("desc");

const columns: FimsColumn<AssetKewPaGrnRow>[] = [
  { key: "no", label: "No", value: (r) => r.index },
  { key: "grmReceiveNo", label: "GRN no.", value: (r) => r.grmReceiveNo },
  { key: "grmReceiveDate", label: "Date", value: (r) => r.grmReceiveDate || "—" },
  { key: "grmStatus", label: "Status", value: (r) => r.grmStatus },
  { key: "vendorCode", label: "Vendor", value: (r) => r.vendorCode || "—" },
  { key: "poNo", label: "PO", value: (r) => r.poNo || "—" },
  { key: "totalRm", label: "Total (RM)", value: (r) => r.totalRm || "—" },
  { key: "bimBillsNo", label: "Bill no.", value: (r) => r.bimBillsNo || "—" },
];

function totalPages() {
  return total.value ? Math.max(1, Math.ceil(total.value / limit.value)) : 1;
}

async function loadRows() {
  loading.value = true;
  try {
    const params = new URLSearchParams({
      variant: variant.value,
      page: String(page.value),
      limit: String(limit.value),
      sort_dir: sortDir.value,
      ...(q.value.trim() ? { q: q.value.trim() } : {}),
    });
    const res = await listAssetGoodsReceiveKewpa(`?${params.toString()}`);
    rows.value = res.data;
    total.value = Number(res.meta?.total ?? 0);
  } catch (e) {
    toast.error("Load failed", e instanceof Error ? e.message : "");
  } finally {
    loading.value = false;
  }
}

function toggleSort() {
  sortDir.value = sortDir.value === "asc" ? "desc" : "asc";
  page.value = 1;
  void loadRows();
}

watch(variant, () => {
  page.value = 1;
  void loadRows();
});

let deb: ReturnType<typeof setTimeout> | null = null;
watch(q, () => {
  if (deb) clearTimeout(deb);
  deb = setTimeout(() => {
    deb = null;
    page.value = 1;
    void loadRows();
  }, 350);
});

onMounted(() => void loadRows());
</script>

<template>
  <AdminLayout>
    <div class="space-y-4">
      <h1 class="page-title">{{ pageTitle }}</h1>

      <p class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-2 text-sm text-slate-700">
        <template v-if="variant === 'receive'"> GRN rows with legacy status <strong>ENDORSE</strong> (posted receipt pathway). Adjust filters if your site uses additional receipt states.
        </template>
        <template v-else> GRN rows with legacy status <strong>CANCEL</strong> as an approximation for penolakan; extend the API if rejection uses a distinct code. </template>
      </p>

      <article class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 px-4 py-3">
          <h2 class="text-base font-semibold text-slate-900">{{ variant === "receive" ? "Receipt register" : "Rejection-oriented register" }}</h2>
          <button type="button" class="rounded border border-slate-200 px-2 py-1 text-xs" @click="toggleSort">Sort GRN ID {{ sortDir }}</button>
        </div>

        <div class="space-y-4 p-4">
          <div class="flex flex-wrap items-end justify-between gap-4">
            <div class="flex flex-wrap items-center gap-2">
              <label class="text-xs font-medium text-slate-600">Rows</label>
              <select
                v-model.number="limit"
                class="rounded-lg border border-slate-300 px-2 py-1.5 text-sm shadow-sm"
                @change="
                  page = 1;
                  loadRows();
                "
              >
                <option v-for="n in [5, 10, 15, 25, 50]" :key="n" :value="n">{{ n }}</option>
              </select>
            </div>
            <div class="relative">
              <Search class="pointer-events-none absolute left-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400" />
              <input v-model="q" type="search" placeholder="Search…" class="w-60 rounded-lg border border-slate-300 py-1.5 pl-8 pr-8 text-sm shadow-sm" />
              <button v-if="q" type="button" class="absolute right-1 top-1/2 -translate-y-1/2 rounded p-0.5 text-slate-400 hover:bg-slate-100" @click="q = ''">
                <X class="h-3.5 w-3.5" />
              </button>
            </div>
          </div>

          <div v-if="loading" class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-6 text-center text-sm text-slate-600">Loading…</div>
          <FimsListTable v-else :rows="rows" :columns="columns" :grouped="false" sort-by="id" sort-dir="asc" :row-key="(r) => r.grmReceiveId" min-width="900px" />

          <div class="flex flex-wrap items-center justify-between gap-2 text-sm text-slate-600">
            <span>Page {{ page }} / {{ totalPages() }}</span>
            <div class="flex gap-2">
              <button type="button" class="rounded border border-slate-200 px-3 py-1 hover:bg-slate-50" :disabled="page <= 1" @click="page--; loadRows()">Prev</button>
              <button type="button" class="rounded border border-slate-200 px-3 py-1 hover:bg-slate-50" :disabled="page >= totalPages()" @click="page++; loadRows()">
                Next
              </button>
            </div>
          </div>
        </div>
      </article>
    </div>
  </AdminLayout>
</template>
