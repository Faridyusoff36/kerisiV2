<script setup lang="ts">
/**
 * Asset / Asset Verification (PAGEID 2123 / MENUID 2574).
 * Legacy: AFQ_ASSETVERIFICATION_API. Datatable — default (top) filter pattern.
 */
import { onMounted, onUnmounted, ref, watch } from "vue";
import { Download, FileDown, FileSpreadsheet, MoreVertical, Pencil, Search, X } from "lucide-vue-next";
import AdminLayout from "@/layouts/AdminLayout.vue";
import { getAssetVerification, listAssetVerification, updateAssetVerification } from "@/api/cms";
import { useToast } from "@/composables/useToast";
import type { AssetVerificationRow } from "@/types";
import { useDatatableFeatures } from "@/composables/useDatatableFeatures";
import type { DatatableRefApi } from "@/composables/useDatatableFeatures";

const toast = useToast();
const rows = ref<AssetVerificationRow[]>([]);
const total = ref(0);
const page = ref(1);
const limit = ref(10);
const q = ref("");
const sortBy = ref<string>("aim_asset_code");
const sortDir = ref<"asc" | "desc">("asc");
const loading = ref(false);
const showModal = ref(false);
const editAssetId = ref<number | null>(null);
const form = ref({
  realCurBuilding: "",
  realCurRoom: "",
  realCurBuildingDesc: "",
  realCurRoomDesc: "",
  assetStatus: "",
});

function formatDay(s: string | null | undefined): string {
  if (!s) return "—";
  const d = new Date(s);
  if (Number.isNaN(d.getTime())) return s;
  const dd = String(d.getDate()).padStart(2, "0");
  const mm = String(d.getMonth() + 1).padStart(2, "0");
  return `${dd}/${mm}/${d.getFullYear()}`;
}

async function loadRows() {
  loading.value = true;
  const params = new URLSearchParams({
    page: String(page.value),
    limit: String(limit.value),
    sort_by: sortBy.value,
    sort_dir: sortDir.value,
    ...(q.value ? { q: q.value } : {}),
  });
  try {
    const res = await listAssetVerification(`?${params.toString()}`);
    rows.value = res.data;
    total.value = Number(res.meta?.total ?? 0);
  } catch (e) {
    toast.error("Load failed", e instanceof Error ? e.message : "Unable to load verification list.");
  } finally {
    loading.value = false;
  }
}

function toggleSort(col: string) {
  if (sortBy.value === col) sortDir.value = sortDir.value === "asc" ? "desc" : "asc";
  else {
    sortBy.value = col;
    sortDir.value = "asc";
  }
  void loadRows();
}

function totalPages() {
  return total.value ? Math.max(1, Math.ceil(total.value / limit.value)) : 1;
}
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
const startIdx = () => (total.value === 0 ? 0 : (page.value - 1) * limit.value + 1);
const endIdx = () => Math.min(page.value * limit.value, total.value);

async function openEdit(row: AssetVerificationRow) {
  if (row.verificationLocked) {
    toast.info("Locked", "This row is verified (CHECKED) and cannot be edited.");
    return;
  }
  editAssetId.value = row.assetId;
  try {
    const res = await getAssetVerification(row.assetId);
    const d = res.data;
    form.value = {
      realCurBuilding: d.realCurBuilding ?? "",
      realCurRoom: d.realCurRoom ?? "",
      realCurBuildingDesc: d.realCurBuildingDesc ?? "",
      realCurRoomDesc: d.realCurRoomDesc ?? "",
      assetStatus: d.assetStatus ?? "",
    };
    showModal.value = true;
  } catch (e) {
    toast.error("Load failed", e instanceof Error ? e.message : "");
  }
}

async function saveModal() {
  if (editAssetId.value == null) return;
  try {
    await updateAssetVerification(editAssetId.value, {
      realCurBuilding: form.value.realCurBuilding || null,
      realCurRoom: form.value.realCurRoom || null,
      realCurBuildingDesc: form.value.realCurBuildingDesc || null,
      realCurRoomDesc: form.value.realCurRoomDesc || null,
      assetStatus: form.value.assetStatus || null,
    });
    toast.success("Saved");
    showModal.value = false;
    await loadRows();
  } catch (e) {
    toast.error("Save failed", e instanceof Error ? e.message : "");
  }
}

const exportColumns = [
  "Asset Code",
  "Description",
  "Model",
  "Brand",
  "Serial No",
  "Current Bldg",
  "Current Room",
  "Actual Bldg",
  "Actual Room",
  "Asset St.",
  "Verification",
  "Verify date",
];

function asExport(r: AssetVerificationRow): (string | number)[] {
  return [
    r.assetCode ?? "",
    r.assetDescription ?? "",
    r.model ?? "",
    r.brand ?? "",
    r.serialNo ?? "",
    r.currentBuilding ?? "",
    r.currentRoom ?? "",
    r.actualBuilding ?? "",
    r.actualRoom ?? "",
    r.assetStatus ?? "",
    r.verificationStatus ?? "",
    formatDay(r.verifyDate ?? null),
  ];
}

async function exportRows(kind: "pdf" | "csv" | "excel") {
  if (!rows.value.length) {
    toast.info("No data", "Nothing to export.");
    return;
  }
  const name = `Asset_Verification_${new Date().toISOString().slice(0, 10)}`;
  const body = rows.value.map(asExport);
  if (kind === "csv") {
    const esc = (v: string | number) => (/,|\n|"/.test(String(v)) ? `"${String(v).replace(/"/g, '""')}"` : v);
    const c = [["No", ...exportColumns], ...body.map((r, i) => [i + 1, ...r])].map((a) => a.map(esc).join(",")).join("\n");
    const a = document.createElement("a");
    a.href = URL.createObjectURL(new Blob([c], { type: "text/csv;charset=utf-8" }));
    a.download = `${name}.csv`;
    a.click();
    toast.success("CSV downloaded");
  } else if (kind === "excel") {
    const ExcelJS = await import("exceljs");
    const wb = new ExcelJS.Workbook();
    const w = wb.addWorksheet("Verification");
    w.addRow(["No", ...exportColumns]);
    body.forEach((r, i) => w.addRow([i + 1, ...r]));
    const buf = await wb.xlsx.writeBuffer();
    const a = document.createElement("a");
    a.href = URL.createObjectURL(new Blob([buf], { type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" }));
    a.download = `${name}.xlsx`;
    a.click();
    URL.revokeObjectURL(a.href);
    toast.success("Excel downloaded");
  } else {
    const { default: jsPDF } = await import("jspdf");
    const t = (await import("jspdf-autotable")).default;
    const d = new jsPDF({ orientation: "landscape" });
    d.text(name, 14, 14);
    t(d, { head: [["No", ...exportColumns]], body: body.map((r, i) => [i + 1, ...r]), startY: 20, styles: { fontSize: 6 } });
    d.save(`${name}.pdf`);
    toast.success("PDF downloaded");
  }
}

let t: ReturnType<typeof setTimeout> | null = null;
watch(q, () => {
  if (t) clearTimeout(t);
  t = setTimeout(() => {
    t = null;
    page.value = 1;
    void loadRows();
  }, 350);
});

const overflowOpen = ref(false);
const overflowRoot = ref<HTMLElement | null>(null);
function onClickOutside(ev: MouseEvent) {
  if (!overflowOpen.value) return;
  if (overflowRoot.value?.contains(ev.target as Node)) return;
  overflowOpen.value = false;
}

const { templateFileInputRef, isGrouped, handleSaveTemplate, handleLoadTemplate, onTemplateFileChange, handleGroupList, handleUngroupList } =
  useDatatableFeatures({
    pageName: "Asset Verification",
    apiDataPath: "",
    defaultExportColumns: [],
    getFilteredList: () => [],
    datatableRef: ref<DatatableRefApi | null>(null),
    searchKeyword: q,
  });

onMounted(() => {
  document.addEventListener("click", onClickOutside);
  void loadRows();
});
onUnmounted(() => {
  document.removeEventListener("click", onClickOutside);
  if (t) clearTimeout(t);
});
</script>

<template>
  <AdminLayout>
    <div class="space-y-4">
      <h1 class="page-title">Asset / Asset Verification</h1>
      <article class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
          <h2 class="text-base font-semibold text-slate-900">ASSET VERIFICATION</h2>
          <div ref="overflowRoot" class="relative">
            <button type="button" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100" @click.stop="overflowOpen = !overflowOpen">
              <MoreVertical class="h-4 w-4" />
            </button>
            <div v-if="overflowOpen" class="absolute right-0 z-30 mt-1 w-44 rounded-lg border border-slate-200 bg-white py-1 shadow-lg" @click.stop>
              <button type="button" class="block w-full px-3 py-2 text-left text-xs text-slate-700 hover:bg-slate-50" @click="overflowOpen = false; handleSaveTemplate()">Save template</button>
              <button type="button" class="block w-full px-3 py-2 text-left text-xs text-slate-700 hover:bg-slate-50" @click="overflowOpen = false; handleLoadTemplate()">Load template</button>
              <input ref="templateFileInputRef" type="file" accept="application/json" class="hidden" @change="onTemplateFileChange" />
              <button v-if="isGrouped" type="button" class="block w-full px-3 py-2 text-left text-xs text-slate-700 hover:bg-slate-50" @click="overflowOpen = false; handleUngroupList()">Ungroup list</button>
              <button v-else type="button" class="block w-full px-3 py-2 text-left text-xs text-slate-700 hover:bg-slate-50" @click="overflowOpen = false; handleGroupList()">Group list</button>
            </div>
          </div>
        </div>
        <div class="space-y-4 p-4">
          <div class="flex flex-wrap items-end justify-between gap-4">
            <div class="flex items-center gap-2">
              <label class="text-xs text-slate-600">Display</label>
              <select v-model.number="limit" class="rounded-lg border border-slate-300 px-2 py-1.5 text-sm" @change="page = 1; loadRows()">
                <option v-for="n in [10, 25, 50, 100]" :key="n" :value="n">{{ n }}</option>
              </select>
            </div>
            <div class="relative">
              <Search class="pointer-events-none absolute left-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400" />
              <input v-model="q" type="search" placeholder="Filter rows..." class="w-56 rounded-lg border border-slate-300 py-1.5 pl-8 pr-8 text-sm" @keyup.enter="page = 1; loadRows()" />
              <button v-if="q" type="button" class="absolute right-1 top-1/2 -translate-y-1/2 p-0.5 text-slate-400" @click="q = ''"><X class="h-3.5 w-3.5" /></button>
            </div>
          </div>
          <div class="overflow-x-auto rounded-lg border border-slate-200">
            <div :class="rows.length > 10 ? 'max-h-[480px] overflow-y-auto' : ''">
              <table class="admin-table-kitchen w-full min-w-[1400px] text-sm">
                <thead class="admin-table-thead-sticky">
                  <tr class="border-b text-left text-xs font-semibold uppercase">
                    <th class="px-2 py-2">No</th>
                    <th class="cursor-pointer px-2 py-2" @click="toggleSort('aim_asset_code')">Asset Code</th>
                    <th class="cursor-pointer px-2 py-2" @click="toggleSort('aim_asset_desc')">Description</th>
                    <th class="cursor-pointer px-2 py-2" @click="toggleSort('aim_model')">Model</th>
                    <th class="cursor-pointer px-2 py-2" @click="toggleSort('aim_brand_name')">Brand</th>
                    <th class="cursor-pointer px-2 py-2" @click="toggleSort('aim_serial_no')">Serial No</th>
                    <th class="px-2 py-2">Current Building</th>
                    <th class="px-2 py-2">Current Room</th>
                    <th class="px-2 py-2">Actual Building</th>
                    <th class="px-2 py-2">Actual Room</th>
                    <th class="cursor-pointer px-2 py-2" @click="toggleSort('aim_asset_status')">Asset Status</th>
                    <th class="cursor-pointer px-2 py-2" @click="toggleSort('aim_verification_sts')">Verification</th>
                    <th class="cursor-pointer px-2 py-2" @click="toggleSort('aim_verify_date')">Verify Date</th>
                    <th class="px-2 py-2 text-right"> </th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-if="loading"><td colspan="14" class="p-6 text-center text-slate-500">Loading…</td></tr>
                  <tr v-else-if="!rows.length"><td colspan="14" class="p-6 text-center text-slate-500">No rows</td></tr>
                  <tr v-for="row in rows" :key="row.assetId" class="border-b border-slate-100 hover:bg-slate-50">
                    <td class="px-2 py-1.5">{{ row.index }}</td>
                    <td class="px-2 py-1.5 font-medium">{{ row.assetCode ?? "—" }}</td>
                    <td class="px-2 py-1.5 max-w-[180px] truncate" :title="row.assetDescription ?? ''">{{ row.assetDescription ?? "—" }}</td>
                    <td class="px-2 py-1.5">{{ row.model ?? "—" }}</td>
                    <td class="px-2 py-1.5">{{ row.brand ?? "—" }}</td>
                    <td class="px-2 py-1.5">{{ row.serialNo ?? "—" }}</td>
                    <td class="px-2 py-1.5 max-w-[120px] truncate">{{ row.currentBuilding ?? "—" }}</td>
                    <td class="px-2 py-1.5 max-w-[100px] truncate">{{ row.currentRoom ?? "—" }}</td>
                    <td class="px-2 py-1.5 max-w-[120px] truncate">{{ row.actualBuilding ?? "—" }}</td>
                    <td class="px-2 py-1.5 max-w-[100px] truncate">{{ row.actualRoom ?? "—" }}</td>
                    <td class="px-2 py-1.5">{{ row.assetStatus ?? "—" }}</td>
                    <td class="px-2 py-1.5">{{ row.verificationStatus ?? "—" }}</td>
                    <td class="px-2 py-1.5">{{ formatDay(row.verifyDate) }}</td>
                    <td class="px-2 py-1.5 text-right">
                      <button type="button" class="inline-flex rounded p-1 text-slate-600 hover:bg-slate-100 disabled:opacity-40" :disabled="row.verificationLocked" title="Edit" @click="openEdit(row)">
                        <Pencil class="h-4 w-4" />
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-3 text-xs text-slate-500">
            <p>Showing {{ startIdx() }}–{{ endIdx() }} of {{ total }}</p>
            <div class="flex items-center gap-2">
              <button type="button" class="rounded border bg-white px-2 py-1" :disabled="page <= 1" @click="prevPage">Prev</button>
              <span class="text-slate-600">Page {{ page }} / {{ totalPages() }}</span>
              <button type="button" class="rounded border bg-white px-2 py-1" :disabled="page >= totalPages()" @click="nextPage">Next</button>
              <div class="mx-2 h-4 w-px bg-slate-200" />
              <button type="button" class="inline-flex items-center gap-1 rounded border bg-white px-2 py-1" @click="exportRows('pdf')"><Download class="h-3.5 w-3.5" />PDF</button>
              <button type="button" class="inline-flex items-center gap-1 rounded border bg-white px-2 py-1" @click="exportRows('csv')"><FileDown class="h-3.5 w-3.5" />CSV</button>
              <button type="button" class="inline-flex items-center gap-1 rounded border bg-white px-2 py-1" @click="exportRows('excel')"><FileSpreadsheet class="h-3.5 w-3.5" />Excel</button>
            </div>
          </div>
        </div>
      </article>
      <Teleport to="body">
        <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm" @click.self="showModal = false">
          <div class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-lg border border-slate-200 bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b px-4 py-3">
              <h2 class="text-base font-semibold">Edit verification</h2>
              <button type="button" class="p-1 text-slate-500" @click="showModal = false"><X class="h-4 w-4" /></button>
            </div>
            <div class="grid gap-3 px-4 py-3">
              <div><label class="text-xs text-slate-600">Actual building (code)</label><input v-model="form.realCurBuilding" class="mt-1 w-full rounded border border-slate-300 px-2 py-1.5 text-sm" /></div>
              <div><label class="text-xs text-slate-600">Actual building description</label><input v-model="form.realCurBuildingDesc" class="mt-1 w-full rounded border px-2 py-1.5 text-sm" /></div>
              <div><label class="text-xs text-slate-600">Actual room (code)</label><input v-model="form.realCurRoom" class="mt-1 w-full rounded border px-2 py-1.5 text-sm" /></div>
              <div><label class="text-xs text-slate-600">Actual room description</label><input v-model="form.realCurRoomDesc" class="mt-1 w-full rounded border px-2 py-1.5 text-sm" /></div>
              <div><label class="text-xs text-slate-600">Asset status</label><input v-model="form.assetStatus" class="mt-1 w-full rounded border px-2 py-1.5 text-sm" /></div>
            </div>
            <div class="flex justify-end gap-2 border-t px-4 py-3">
              <button type="button" class="rounded border px-3 py-1.5 text-sm" @click="showModal = false">Cancel</button>
              <button type="button" class="rounded bg-slate-900 px-3 py-1.5 text-sm text-white" @click="saveModal">Save</button>
            </div>
          </div>
        </div>
      </Teleport>
    </div>
  </AdminLayout>
</template>
