<script setup lang="ts">
/**
 * Kerisi menus 3470 (reports) vs 3481 (draft applications scope).
 */
import { computed, onMounted, ref, watch } from "vue";
import { useRoute } from "vue-router";
import { Search, X } from "lucide-vue-next";

import AdminLayout from "@/layouts/AdminLayout.vue";
import FimsListTable, { type FimsColumn } from "@/components/fims/FimsListTable.vue";
import { listAssetDamageRegister } from "@/api/cms";
import { useToast } from "@/composables/useToast";
import type { AssetDamageListingRow } from "@/types";

const route = useRoute();

const pageTitle = computed(() => (typeof route.meta.title === "string" ? route.meta.title : "Damage listing"));

const scope = computed(() => (route.meta.damageListingScope === "applications" ? "applications" : "reports"));

const toast = useToast();
const rows = ref<AssetDamageListingRow[]>([]);
const loading = ref(false);
const total = ref(0);
const page = ref(1);
const limit = ref(15);
const q = ref("");
const sortDir = ref<"asc" | "desc">("desc");

const columns: FimsColumn<AssetDamageListingRow>[] = [
  { key: "no", label: "No", value: (r) => r.index },
  { key: "drmReportNo", label: "Report no.", value: (r) => r.drmReportNo },
  { key: "drmStatus", label: "Status", value: (r) => r.drmStatus },
  { key: "drmQtyAsset", label: "Qty", value: (r) => String(r.drmQtyAsset) },
  { key: "drmTotalAmt", label: "Amount", value: (r) => r.drmTotalAmt || "—" },
  { key: "drmDescription", label: "Description", value: (r) => r.drmDescription || "—" },
  { key: "createddate", label: "Created", value: (r) => r.createddate || "—" },
];

function totalPages() {
  return total.value ? Math.max(1, Math.ceil(total.value / limit.value)) : 1;
}

async function loadRows() {
  loading.value = true;
  try {
    const params = new URLSearchParams({
      scope: scope.value,
      page: String(page.value),
      limit: String(limit.value),
      sort_dir: sortDir.value,
      ...(q.value.trim() ? { q: q.value.trim() } : {}),
    });
    const res = await listAssetDamageRegister(`?${params.toString()}`);
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

watch(scope, () => {
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

      <p v-if="scope === 'applications'" class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-2 text-sm text-amber-900">
        Showing open / draft-oriented damage registrations. Full workflow forms are still on the legacy Kerisi module.
      </p>

      <article class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 px-4 py-3">
          <h2 class="text-base font-semibold text-slate-900">{{ scope === 'applications' ? 'Applications (draft-oriented)' : 'Damage reports' }}</h2>
          <button type="button" class="rounded border border-slate-200 px-2 py-1 text-xs" @click="toggleSort">Sort DRM ID {{ sortDir }}</button>
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
          <FimsListTable v-else :rows="rows" :columns="columns" :grouped="false" sort-by="id" sort-dir="asc" :row-key="(r) => r.drmId" min-width="900px" />

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
