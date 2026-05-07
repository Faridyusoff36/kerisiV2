<script setup lang="ts">
/**
 * Preventive / corrective / schedule listings — Kerisi 3492, 3498, 3502, 3493, 3499 (draft-filtered).
 */
import { computed, onMounted, ref, watch } from "vue";
import { useRoute } from "vue-router";
import { Search, X } from "lucide-vue-next";

import AdminLayout from "@/layouts/AdminLayout.vue";
import FimsListTable, { type FimsColumn } from "@/components/fims/FimsListTable.vue";
import { listAssetMaintenanceRegister } from "@/api/cms";
import { useToast } from "@/composables/useToast";
import type { AssetMaintenanceListRow } from "@/types";

type ListingMode = "preventive" | "corrective" | "schedule";

const route = useRoute();

const pageTitle = computed(() => (typeof route.meta.title === "string" ? route.meta.title : "Maintenance"));

const listing = computed<ListingMode>(() => {
  const m = route.meta.maintenanceListing;
  if (m === "preventive" || m === "corrective" || m === "schedule") return m;
  return "preventive";
});

const draftOnly = computed(() => Boolean(route.meta.maintenanceDraftOnly));

const toast = useToast();
const rows = ref<AssetMaintenanceListRow[]>([]);
const loading = ref(false);
const total = ref(0);
const page = ref(1);
const limit = ref(15);
const q = ref("");
const sortDir = ref<"asc" | "desc">("desc");

const columns = computed<FimsColumn<AssetMaintenanceListRow>[]>(() => {
  if (listing.value === "schedule") {
    return [
      { key: "no", label: "No", value: (r) => r.index },
      { key: "assetCode", label: "Asset", value: (r) => r.assetCode },
      { key: "scheduleMethod", label: "Schedule method", value: (r) => r.scheduleMethod || "—" },
      { key: "periodType", label: "Period", value: (r) => r.periodType || "—" },
      { key: "startDate", label: "Start", value: (r) => r.startDate || "—" },
      { key: "status", label: "Status", value: (r) => r.status },
      { key: "notes", label: "Notes", value: (r) => r.notes || "—" },
    ];
  }

  return [
    { key: "no", label: "No", value: (r) => r.index },
    { key: "referenceNo", label: "Reference", value: (r) => r.referenceNo || "—" },
    { key: "assetCode", label: "Asset", value: (r) => r.assetCode },
    { key: "description", label: "Description", value: (r) => r.description || "—" },
    { key: "status", label: "Status", value: (r) => r.status },
    { key: "vendorCode", label: "Vendor", value: (r) => r.vendorCode || "—" },
    { key: "totalCost", label: "Cost", value: (r) => r.totalCost || "—" },
    { key: "completeDate", label: "Complete", value: (r) => r.completeDate || "—" },
  ];
});

function totalPages() {
  return total.value ? Math.max(1, Math.ceil(total.value / limit.value)) : 1;
}

async function loadRows() {
  loading.value = true;
  try {
    const params = new URLSearchParams({
      listing: listing.value,
      page: String(page.value),
      limit: String(limit.value),
      sort_dir: sortDir.value,
      ...(q.value.trim() ? { q: q.value.trim() } : {}),
    });
    if (draftOnly.value && listing.value !== "schedule") params.set("amt_status", "DRAFT");

    const res = await listAssetMaintenanceRegister(`?${params.toString()}`);
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

watch([listing, draftOnly], () => {
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

      <p v-if="draftOnly" class="rounded-lg border border-sky-200 bg-sky-50 px-4 py-2 text-sm text-sky-950">
        Draft-oriented view filtered by maintenance status (<code class="rounded bg-white px-1">DRAFT</code> prefix match). Composer actions remain on legacy Kerisi.
      </p>

      <article class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 px-4 py-3">
          <h2 class="text-base font-semibold text-slate-900">Listing ({{ listing }})</h2>
          <button type="button" class="rounded border border-slate-200 px-2 py-1 text-xs" @click="toggleSort">Sort ID {{ sortDir }}</button>
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
          <FimsListTable v-else :rows="rows" :columns="columns" :grouped="false" sort-by="id" sort-dir="asc" :row-key="(r) => r.id" min-width="960px" />

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
