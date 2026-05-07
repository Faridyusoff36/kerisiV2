<script setup lang="ts">
/** Kerisi menu 1562 — Asset building / location (`building_location`). */
import { onMounted, ref, watch } from "vue";
import { Eye, Pencil, Plus, Search, Trash2, X } from "lucide-vue-next";

import AdminLayout from "@/layouts/AdminLayout.vue";
import FimsListTable, { type FimsColumn } from "@/components/fims/FimsListTable.vue";
import {
  createAssetBuildingLocation,
  deleteAssetBuildingLocation,
  getAssetBuildingLocation,
  listAssetBuildingLocations,
  updateAssetBuildingLocation,
} from "@/api/cms";
import { useToast } from "@/composables/useToast";
import type { AssetBuildingLocationInput, AssetBuildingLocationRow } from "@/types";

const PAGE_BREADCRUMB = "Asset / Inventory / Asset Setup By Location";

const toast = useToast();
const rows = ref<AssetBuildingLocationRow[]>([]);
const loading = ref(false);
const total = ref(0);
const page = ref(1);
const limit = ref(15);
const q = ref("");
const sortBy = ref<"bdl_code" | "bdl_desc" | "bdl_id">("bdl_code");
const sortDir = ref<"asc" | "desc">("asc");

const showModal = ref(false);
const modalReadOnly = ref(false);
const editId = ref<number | null>(null);
const form = ref<AssetBuildingLocationInput>({ bdlCode: "", bdlDesc: "", bdlStatus: 1 });

const columns: FimsColumn<AssetBuildingLocationRow>[] = [
  { key: "no", label: "No", value: (r) => r.index },
  { key: "bdlCode", label: "Location code", value: (r) => r.bdlCode },
  { key: "bdlDesc", label: "Description", value: (r) => r.bdlDesc },
  { key: "bdlStatus", label: "Status", value: (r) => r.bdlStatus },
  { key: "createdby", label: "Created by", value: (r) => r.createdby ?? "—" },
  { key: "action", label: "Action" },
];

function totalPages() {
  return total.value ? Math.max(1, Math.ceil(total.value / limit.value)) : 1;
}

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
    const res = await listAssetBuildingLocations(`?${params.toString()}`);
    rows.value = res.data;
    total.value = Number(res.meta?.total ?? 0);
  } catch (e) {
    toast.error("Load failed", e instanceof Error ? e.message : "");
  } finally {
    loading.value = false;
  }
}

async function openView(id: number) {
  modalReadOnly.value = true;
  editId.value = id;
  try {
    const res = await getAssetBuildingLocation(id);
    form.value = {
      bdlCode: res.data.bdlCode ?? "",
      bdlDesc: res.data.bdlDesc ?? "",
      bdlStatus: Number(res.data.bdlStatus ?? 1) === 0 ? 0 : 1,
    };
    showModal.value = true;
  } catch (e) {
    toast.error("Load failed", e instanceof Error ? e.message : "");
  }
}

function openCreate() {
  modalReadOnly.value = false;
  editId.value = null;
  form.value = { bdlCode: "", bdlDesc: "", bdlStatus: 1 };
  showModal.value = true;
}

async function openEdit(id: number) {
  modalReadOnly.value = false;
  editId.value = id;
  try {
    const res = await getAssetBuildingLocation(id);
    form.value = {
      bdlCode: res.data.bdlCode ?? "",
      bdlDesc: res.data.bdlDesc ?? "",
      bdlStatus: Number(res.data.bdlStatus ?? 1) === 0 ? 0 : 1,
    };
    showModal.value = true;
  } catch (e) {
    toast.error("Load failed", e instanceof Error ? e.message : "");
  }
}

async function saveModal() {
  if (modalReadOnly.value) return;
  const code = form.value.bdlCode.trim();
  const desc = form.value.bdlDesc.trim();
  if (!code || !desc) {
    toast.error("Validation", "Code and description required (max 10 / 20 chars per legacy schema).");
    return;
  }
  if (code.length > 10 || desc.length > 20) {
    toast.error("Validation", "Code must be ≤ 10 characters and description ≤ 20.");
    return;
  }
  try {
    if (editId.value == null) {
      await createAssetBuildingLocation({
        ...form.value,
        bdlCode: code,
        bdlDesc: desc,
        bdlStatus: Number(form.value.bdlStatus) === 0 ? 0 : 1,
      });
      toast.success("Saved");
    } else {
      await updateAssetBuildingLocation(editId.value, {
        ...form.value,
        bdlCode: code,
        bdlDesc: desc,
        bdlStatus: Number(form.value.bdlStatus) === 0 ? 0 : 1,
      });
      toast.success("Updated");
    }
    showModal.value = false;
    await loadRows();
  } catch (e) {
    const msg = e instanceof Error ? e.message : "Unable to save.";
    if (msg.toLowerCase().includes("duplicate") || msg.includes("409")) toast.error("Duplicate", "Location code already exists.");
    else toast.error("Save failed", msg);
  }
}

async function onDelete(id: number) {
  if (!confirm("Delete this location row? Rows in use cannot be deleted.")) return;
  try {
    await deleteAssetBuildingLocation(id);
    toast.success("Deleted");
    await loadRows();
  } catch (e) {
    const msg = e instanceof Error ? e.message : "Delete failed.";
    if (msg.includes("423") || msg.toUpperCase().includes("IN_USE")) {
      toast.error("In use", "This location is referenced; set status inactive instead.");
    } else toast.error("Delete failed", msg);
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
          <h2 class="text-base font-semibold text-slate-900">Location setup</h2>
          <div class="flex flex-wrap gap-2">
            <select
              v-model="sortBy"
              class="rounded border border-slate-200 px-2 py-1 text-xs"
              @change="
                page = 1;
                loadRows();
              "
            >
              <option value="bdl_code">Sort: code</option>
              <option value="bdl_desc">Sort: description</option>
              <option value="bdl_id">Sort: ID</option>
            </select>
            <button
              type="button"
              class="rounded border border-slate-200 px-2 py-1 text-xs"
              @click="
                sortDir = sortDir === 'asc' ? 'desc' : 'asc';
                page = 1;
                loadRows();
              "
            >
              Dir {{ sortDir }}
            </button>
            <button type="button" class="inline-flex items-center gap-1 rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-blue-700" @click="openCreate">
              <Plus class="h-3.5 w-3.5" />
              Add
            </button>
          </div>
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
              <input v-model="q" type="search" placeholder="Search…" class="w-52 rounded-lg border border-slate-300 py-1.5 pl-8 pr-8 text-sm shadow-sm" />
              <button v-if="q" type="button" class="absolute right-1 top-1/2 -translate-y-1/2 rounded p-0.5 text-slate-400 hover:bg-slate-100" @click="q = ''">
                <X class="h-3.5 w-3.5" />
              </button>
            </div>
          </div>

          <div v-if="loading" class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-6 text-center text-sm text-slate-600">Loading…</div>
          <FimsListTable
            v-else
            :rows="rows"
            :columns="columns"
            :grouped="false"
            sort-by="id"
            sort-dir="asc"
            :row-key="(r) => r.bdlId"
            min-width="800px"
            @sort="
              () => {
                /* sorting via API params */
              }
            "
          >
            <template #action="{ row }">
              <span class="inline-flex gap-1">
                <button type="button" class="rounded p-1 text-slate-500 hover:bg-slate-100" title="View" @click="openView((row as AssetBuildingLocationRow).bdlId)">
                  <Eye class="h-3.5 w-3.5" />
                </button>
                <button type="button" class="rounded p-1 text-slate-500 hover:bg-slate-100" title="Edit" @click="openEdit((row as AssetBuildingLocationRow).bdlId)">
                  <Pencil class="h-3.5 w-3.5" />
                </button>
                <button type="button" class="rounded p-1 text-red-500 hover:bg-red-50" title="Delete" @click="onDelete((row as AssetBuildingLocationRow).bdlId)">
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
        <h3 class="mb-4 text-lg font-semibold text-slate-900">{{ modalReadOnly ? "View" : editId ? "Edit" : "New" }} location</h3>
        <div class="space-y-3 text-sm">
          <label class="block">
            <span class="text-xs font-medium text-slate-600">Location code (max 10)</span>
            <input v-model="form.bdlCode" :disabled="modalReadOnly" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 uppercase" maxlength="10" />
          </label>
          <label class="block">
            <span class="text-xs font-medium text-slate-600">Description (max 20)</span>
            <input v-model="form.bdlDesc" :disabled="modalReadOnly" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2" maxlength="20" />
          </label>
          <label class="block">
            <span class="text-xs font-medium text-slate-600">Status</span>
            <select v-model.number="form.bdlStatus" :disabled="modalReadOnly" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
              <option :value="1">Active</option>
              <option :value="0">Inactive</option>
            </select>
          </label>
        </div>
        <div class="mt-6 flex justify-end gap-2">
          <button type="button" class="rounded-lg border border-slate-200 px-4 py-2 text-sm" @click="showModal = false">Close</button>
          <button v-if="!modalReadOnly" type="button" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700" @click="saveModal">Save</button>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
