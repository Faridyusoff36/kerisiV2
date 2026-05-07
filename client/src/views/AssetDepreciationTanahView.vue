<script setup lang="ts">
/** Kerisi menu 2483 — Depreciation Setup (Tanah). */
import { onMounted, ref, watch } from "vue";
import { Eye, Pencil, Plus, Search, Trash2, X } from "lucide-vue-next";

import AdminLayout from "@/layouts/AdminLayout.vue";
import FimsListTable, { type FimsColumn } from "@/components/fims/FimsListTable.vue";
import type { DatatableRefApi } from "@/composables/useDatatableFeatures";
import {
  createAssetDepreciationTanah,
  deleteAssetDepreciationTanah,
  getAssetDepreciationTanah,
  listAssetDepreciationTanah,
  updateAssetDepreciationTanah,
} from "@/api/cms";
import { useToast } from "@/composables/useToast";
import type { AssetDepreciationTanahInput, AssetDepreciationTanahRow } from "@/types";

const PAGE_BREADCRUMB = "Asset / Setup / General / Depreciation Setup (Tanah)";

const toast = useToast();
const rows = ref<AssetDepreciationTanahRow[]>([]);
const loading = ref(false);
const total = ref(0);
const page = ref(1);
const limit = ref(15);
const q = ref("");
const sortDir = ref<"asc" | "desc">("asc");
const datatableRef = ref<DatatableRefApi | null>(null);

const showModal = ref(false);
const modalReadOnly = ref(false);
const editId = ref<number | null>(null);
const form = ref<AssetDepreciationTanahInput>({
  itmItemCode: "",
  adtEstimatedLife: "",
  adtStatus: "1",
  statusDesc: "",
  aimAssetDesc: "",
});

const columns: FimsColumn<AssetDepreciationTanahRow>[] = [
  { key: "no", label: "No", value: (r) => r.index },
  { key: "itmItemLabel", label: "Item", value: (r) => r.itmItemLabel },
  { key: "adtEstimatedLife", label: "Est. life", value: (r) => r.adtEstimatedLife },
  { key: "statusLabel", label: "Status", value: (r) => r.statusLabel },
  { key: "createdby", label: "Created by", value: (r) => String(r.createdby ?? "—") },
  { key: "action", label: "Action" },
];

async function loadRows() {
  loading.value = true;
  try {
    const params = new URLSearchParams({
      page: String(page.value),
      limit: String(limit.value),
      sort_dir: sortDir.value,
      ...(q.value.trim() ? { q: q.value.trim() } : {}),
    });
    const res = await listAssetDepreciationTanah(`?${params.toString()}`);
    rows.value = res.data;
    total.value = Number(res.meta?.total ?? 0);
  } catch (e) {
    toast.error("Load failed", e instanceof Error ? e.message : "");
  } finally {
    loading.value = false;
  }
}

function toggleSortDir() {
  sortDir.value = sortDir.value === "asc" ? "desc" : "asc";
  page.value = 1;
  void loadRows();
}

const totalPages = () => (total.value ? Math.max(1, Math.ceil(total.value / limit.value)) : 1);

function openCreate() {
  modalReadOnly.value = false;
  editId.value = null;
  form.value = { itmItemCode: "", adtEstimatedLife: "", adtStatus: "1", statusDesc: "", aimAssetDesc: "" };
  showModal.value = true;
}

async function openView(id: number) {
  modalReadOnly.value = true;
  editId.value = id;
  try {
    const d = (await getAssetDepreciationTanah(id)).data;
    form.value = {
      itmItemCode: d.itmItemCode,
      adtEstimatedLife: d.adtEstimatedLife ?? "",
      adtStatus: d.adtStatus,
      statusDesc: d.statusDesc ?? "",
      aimAssetDesc: d.aimAssetDesc ?? "",
    };
    showModal.value = true;
  } catch (e) {
    toast.error("Load failed", e instanceof Error ? e.message : "");
  }
}

async function openEdit(id: number) {
  modalReadOnly.value = false;
  editId.value = id;
  try {
    const d = (await getAssetDepreciationTanah(id)).data;
    form.value = {
      itmItemCode: d.itmItemCode,
      adtEstimatedLife: d.adtEstimatedLife ?? "",
      adtStatus: d.adtStatus,
      statusDesc: d.statusDesc ?? "",
      aimAssetDesc: d.aimAssetDesc ?? "",
    };
    showModal.value = true;
  } catch (e) {
    toast.error("Load failed", e instanceof Error ? e.message : "");
  }
}

async function saveModal() {
  if (modalReadOnly.value) return;
  if (!form.value.itmItemCode.trim()) {
    toast.error("Validation", "Item code required.");
    return;
  }
  try {
    if (editId.value == null) await createAssetDepreciationTanah({ ...form.value });
    else await updateAssetDepreciationTanah(editId.value, { ...form.value });
    toast.success("Saved");
    showModal.value = false;
    await loadRows();
  } catch (e) {
    toast.error("Save failed", e instanceof Error ? e.message : "");
  }
}

async function onDelete(id: number) {
  if (!confirm("Delete this row?")) return;
  try {
    await deleteAssetDepreciationTanah(id);
    toast.success("Deleted");
    await loadRows();
  } catch (e) {
    toast.error("Delete failed", e instanceof Error ? e.message : "");
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
        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 px-4 py-3">
          <h2 class="text-base font-semibold text-slate-900">Tanah depreciation</h2>
          <div class="flex gap-2">
            <button type="button" class="rounded border border-slate-200 px-2 py-1 text-xs" @click="toggleSortDir">Sort ID {{ sortDir }}</button>
            <button type="button" class="inline-flex items-center gap-1 rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-blue-700" @click="openCreate">
              <Plus class="h-3.5 w-3.5" />
              Add
            </button>
          </div>
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
                <option v-for="n in [5, 10, 15, 25, 50]" :key="n" :value="n">{{ n }}</option>
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
            :sort-by="'id'"
            sort-dir="asc"
            :row-key="(r) => r.adtDeprId"
            min-width="800px"
            @sort="
              () => {
                /* non-sortable table */
              }
            "
          >
            <template #action="{ row }">
              <span class="inline-flex gap-1">
                <button type="button" class="rounded p-1 text-slate-500 hover:bg-slate-100" title="View" @click="openView((row as AssetDepreciationTanahRow).adtDeprId)">
                  <Eye class="h-3.5 w-3.5" />
                </button>
                <button type="button" class="rounded p-1 text-slate-500 hover:bg-slate-100" title="Edit" @click="openEdit((row as AssetDepreciationTanahRow).adtDeprId)">
                  <Pencil class="h-3.5 w-3.5" />
                </button>
                <button type="button" class="rounded p-1 text-red-500 hover:bg-red-50" title="Delete" @click="onDelete((row as AssetDepreciationTanahRow).adtDeprId)">
                  <Trash2 class="h-3.5 w-3.5" />
                </button>
              </span>
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

    <div
      v-if="showModal"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
      role="dialog"
      aria-modal="true"
      @click.self="showModal = false"
    >
      <div class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-xl bg-white p-5 shadow-xl">
        <h3 class="mb-4 text-lg font-semibold text-slate-900">{{ modalReadOnly ? "View" : editId ? "Edit" : "New" }} tanah setup</h3>
        <div class="space-y-3 text-sm">
          <label class="block">
            <span class="text-xs font-medium text-slate-600">Item code</span>
            <input v-model="form.itmItemCode" :disabled="modalReadOnly" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2" />
          </label>
          <label class="block">
            <span class="text-xs font-medium text-slate-600">Asset description (stored in JSON)</span>
            <input v-model="form.aimAssetDesc" :disabled="modalReadOnly" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2" />
          </label>
          <label class="block">
            <span class="text-xs font-medium text-slate-600">Estimated life</span>
            <input v-model="form.adtEstimatedLife" :disabled="modalReadOnly" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2" />
          </label>
          <label class="block">
            <span class="text-xs font-medium text-slate-600">Status (1 = active)</span>
            <input v-model="form.adtStatus" :disabled="modalReadOnly" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2" />
          </label>
          <label class="block">
            <span class="text-xs font-medium text-slate-600">Status description</span>
            <input v-model="form.statusDesc" :disabled="modalReadOnly" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2" />
          </label>
        </div>
        <div class="mt-6 flex justify-end gap-2">
          <button type="button" class="rounded-lg border border-slate-200 px-4 py-2 text-sm" @click="showModal = false">Close</button>
          <button v-if="!modalReadOnly" type="button" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700" @click="saveModal">
            Save
          </button>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
