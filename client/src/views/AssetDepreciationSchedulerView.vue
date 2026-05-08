<script setup lang="ts">
/** Kerisi menu 3338 — Asset depreciation scheduler (per org). */
import { onMounted, ref, watch } from "vue";
import { Pencil, Search, X } from "lucide-vue-next";

import AdminLayout from "@/layouts/AdminLayout.vue";
import FimsListTable, { type FimsColumn } from "@/components/fims/FimsListTable.vue";
import type { DatatableRefApi } from "@/composables/useDatatableFeatures";
import { listAssetDepreciationScheduler, updateAssetDepreciationScheduler } from "@/api/cms";
import { useToast } from "@/composables/useToast";
import type { AssetDepreciationSchedulerRow } from "@/types";

const PAGE_BREADCRUMB = "Asset / Setup / General / Asset Depreciation Scheduler Setup";

const toast = useToast();
const rows = ref<AssetDepreciationSchedulerRow[]>([]);
const loading = ref(false);
const total = ref(0);
const page = ref(1);
const limit = ref(15);
const q = ref("");
const sortBy = ref("id");
const sortDir = ref<"asc" | "desc">("asc");
const datatableRef = ref<DatatableRefApi | null>(null);

const showModal = ref(false);
const editRow = ref<AssetDepreciationSchedulerRow | null>(null);
const editDay = ref("");
const editOpen = ref("Y");

const columns: FimsColumn<AssetDepreciationSchedulerRow>[] = [
  { key: "no", label: "No", value: (r) => r.index },
  { key: "org", label: "Organization", sortable: true, sortKey: "org", value: (r) => r.org },
  { key: "day", label: "Day", sortable: true, sortKey: "day", value: (r) => r.day },
  { key: "isOpen", label: "Open", sortable: true, sortKey: "is_open", value: (r) => r.isOpen },
  { key: "updateBy", label: "Updated by", value: (r) => String(r.updateBy ?? "—") },
  { key: "action", label: "Action" },
];

async function loadRows() {
  loading.value = true;
  try {
    const params = new URLSearchParams({
      page: String(page.value),
      limit: String(limit.value),
      sort_by: sortBy.value,
      sort_dir: sortDir.value,
      ...(q.value.trim() ? { q: q.value.trim() } : {}),
    });
    const res = await listAssetDepreciationScheduler(`?${params.toString()}`);
    rows.value = res.data;
    total.value = Number(res.meta?.total ?? 0);
  } catch (e) {
    toast.error("Load failed", e instanceof Error ? e.message : "");
  } finally {
    loading.value = false;
  }
}

function onSort(sortKey: string) {
  if (sortBy.value === sortKey) sortDir.value = sortDir.value === "asc" ? "desc" : "asc";
  else {
    sortBy.value = sortKey;
    sortDir.value = "asc";
  }
  page.value = 1;
  void loadRows();
}

const totalPages = () => (total.value ? Math.max(1, Math.ceil(total.value / limit.value)) : 1);

function openEdit(r: AssetDepreciationSchedulerRow) {
  editRow.value = r;
  editDay.value = r.day;
  editOpen.value = r.adscIsopen === "N" ? "N" : "Y";
  showModal.value = true;
}

async function saveModal() {
  if (!editRow.value) return;
  try {
    await updateAssetDepreciationScheduler(editRow.value.id, {
      adscDay: editDay.value.trim(),
      adscIsopen: editOpen.value,
    });
    toast.success("Updated");
    showModal.value = false;
    await loadRows();
  } catch (e) {
    toast.error("Save failed", e instanceof Error ? e.message : "");
  }
}

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

      <article class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-4 py-3">
          <h2 class="text-base font-semibold text-slate-900">Scheduler</h2>
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
                <option v-for="n in [5, 10, 15, 25]" :key="n" :value="n">{{ n }}</option>
              </select>
            </div>
            <div class="relative">
              <Search class="pointer-events-none absolute left-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400" />
              <input v-model="q" type="search" placeholder="Search…" class="w-52 rounded-lg border border-slate-300 py-1.5 pl-8 pr-8 text-sm shadow-sm" />
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
            :sort-by="sortBy"
            :sort-dir="sortDir"
            :row-key="(r) => r.id"
            min-width="720px"
            @sort="onSort"
          >
            <template #action="{ row }">
              <button type="button" class="rounded p-1 text-slate-500 hover:bg-slate-100" title="Edit" @click="openEdit(row as AssetDepreciationSchedulerRow)">
                <Pencil class="h-3.5 w-3.5" />
              </button>
            </template>
          </FimsListTable>

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

    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="showModal = false">
      <div class="w-full max-w-md rounded-xl bg-white p-5 shadow-xl">
        <h3 class="mb-4 text-lg font-semibold text-slate-900">Edit scheduler</h3>
        <div class="space-y-3 text-sm">
          <p class="text-slate-600">{{ editRow?.org }}</p>
          <label class="block">
            <span class="text-xs font-medium text-slate-600">Run day</span>
            <input v-model="editDay" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2" />
          </label>
          <label class="block">
            <span class="text-xs font-medium text-slate-600">Open flag</span>
            <select v-model="editOpen" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
              <option value="Y">Y</option>
              <option value="N">N</option>
            </select>
          </label>
        </div>
        <div class="mt-6 flex justify-end gap-2">
          <button type="button" class="rounded-lg border border-slate-200 px-4 py-2 text-sm" @click="showModal = false">Cancel</button>
          <button type="button" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700" @click="saveModal">Save</button>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
