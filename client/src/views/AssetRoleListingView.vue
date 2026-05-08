<script setup lang="ts">
/** Kerisi menu 3148 — distinct organization cascade role codes (reference). */
import { onMounted, ref, watch } from "vue";
import { Search, X } from "lucide-vue-next";

import AdminLayout from "@/layouts/AdminLayout.vue";
import FimsListTable, { type FimsColumn } from "@/components/fims/FimsListTable.vue";
import type { DatatableRefApi } from "@/composables/useDatatableFeatures";
import { listAssetOrganizationCascadeRoles } from "@/api/cms";
import { useToast } from "@/composables/useToast";
import type { AssetOrganizationCascadeRoleRow } from "@/types";

const PAGE_BREADCRUMB = "Asset / Setup / General / Role Listing";

const toast = useToast();
const rows = ref<AssetOrganizationCascadeRoleRow[]>([]);
const loading = ref(false);
const total = ref(0);
const page = ref(1);
const limit = ref(25);
const q = ref("");
const datatableRef = ref<DatatableRefApi | null>(null);

const columns: FimsColumn<AssetOrganizationCascadeRoleRow>[] = [
  { key: "no", label: "No", value: (r) => r.index },
  { key: "roleCode", label: "Cascade role code", value: (r) => r.roleCode },
];

async function loadRows() {
  loading.value = true;
  try {
    const params = new URLSearchParams({
      page: String(page.value),
      limit: String(limit.value),
      ...(q.value.trim() ? { q: q.value.trim() } : {}),
    });
    const res = await listAssetOrganizationCascadeRoles(`?${params.toString()}`);
    rows.value = res.data;
    total.value = Number(res.meta?.total ?? 0);
  } catch (e) {
    toast.error("Load failed", e instanceof Error ? e.message : "");
  } finally {
    loading.value = false;
  }
}

const totalPages = () => (total.value ? Math.max(1, Math.ceil(total.value / limit.value)) : 1);

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
      <h1 class="page-title">{{ PAGE_BREADCRUMB }}</h1>
      <p class="max-w-2xl text-sm text-slate-600">
        Distinct values from <code class="rounded bg-slate-100 px-1">organization_authorization.ora_cascade_role</code> (filtered to asset-related prefixes when browsing
        unfiltered).
      </p>

      <article class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-4 py-3">
          <h2 class="text-base font-semibold text-slate-900">Role codes</h2>
        </div>
        <div class="space-y-4 p-4">
          <div class="flex flex-wrap items-end justify-between gap-4">
            <div class="flex flex-wrap items-center gap-2">
              <label class="text-xs font-medium text-slate-600">Display</label>
              <select
                v-model.number="limit"
                class="rounded-lg border border-slate-300 px-2 py-1.5 text-sm shadow-sm"
                @change="
                  page = 1;
                  loadRows();
                "
              >
                <option v-for="n in [10, 25, 50, 100]" :key="n" :value="n">{{ n }}</option>
              </select>
            </div>
            <div class="relative">
              <Search class="pointer-events-none absolute left-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400" />
              <input v-model="q" type="search" placeholder="Filter code…" class="w-52 rounded-lg border border-slate-300 py-1.5 pl-8 pr-8 text-sm shadow-sm" />
              <button v-if="q" type="button" class="absolute right-1 top-1/2 -translate-y-1/2 rounded p-0.5 text-slate-400 hover:bg-slate-100" @click="q = ''">
                <X class="h-3.5 w-3.5" />
              </button>
            </div>
          </div>

          <div v-if="loading" class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-6 text-center text-sm text-slate-600">Loading…</div>
          <FimsListTable
            v-else
            ref="datatableRef"
            :rows="rows"
            :columns="columns"
            :grouped="false"
            sort-by="role"
            sort-dir="asc"
            :row-key="(r) => r.roleCode"
            min-width="480px"
            @sort="
              () => {
                //
              }
            "
          />
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
