<script setup lang="ts">
/** Kerisi menu 2746 — composite room × building × PTJ listing (read-only). */
import { computed, onMounted, ref, watch } from "vue";
import { Download, FileDown, FileSpreadsheet, Plus, Search, X } from "lucide-vue-next";

import AdminLayout from "@/layouts/AdminLayout.vue";
import FimsListTable, { type FimsColumn } from "@/components/fims/FimsListTable.vue";
import { useDatatableFeatures } from "@/composables/useDatatableFeatures";
import type { DatatableRefApi } from "@/composables/useDatatableFeatures";
import { listAssetRoomLocations } from "@/api/cms";
import { useToast } from "@/composables/useToast";
import type { AssetRoomLocationRow } from "@/types";

const PAGE_NAME = "Room & location";
const PAGE_TITLE = "Asset / Inventory / Location Listing";
const exportColumnLabels = ["Room code", "Room name", "Location", "Building", "PTJ", "Level", "Status"] as const;

const toast = useToast();
const rows = ref<AssetRoomLocationRow[]>([]);
const loading = ref(false);
const total = ref(0);
const page = ref(1);
const limit = ref(15);
const q = ref("");
const sortDir = ref<"asc" | "desc">("asc");
const datatableRef = ref<DatatableRefApi | null>(null);

const totalPages = computed(() => (total.value ? Math.max(1, Math.ceil(total.value / limit.value)) : 1));
const startIdx = computed(() => (total.value === 0 ? 0 : (page.value - 1) * limit.value + 1));
const endIdx = computed(() => Math.min(page.value * limit.value, total.value));

const columns: FimsColumn<AssetRoomLocationRow>[] = [
  { key: "no", label: "No", value: (r) => r.index },
  { key: "rmmCode", label: "Room code", value: (r) => r.rmmCode },
  { key: "rmmName", label: "Room name", value: (r) => r.rmmName },
  { key: "locationLabel", label: "Location", value: (r) => r.locationLabel },
  { key: "buildingLabel", label: "Building", value: (r) => r.buildingLabel },
  { key: "ptjLabel", label: "PTJ", value: (r) => r.ptjLabel },
  { key: "rmmLevel", label: "Level", value: (r) => r.rmmLevel },
  { key: "rmmStatus", label: "Status", value: (r) => r.rmmStatus || "—" },
];

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

function onAddClick() {
  toast.info(
    "Read-only listing",
    "Rooms and locations are maintained elsewhere in Kerisi / FIMS. This screen is for viewing and export only.",
  );
}

const { handleDownloadPDF, handleDownloadCSV } = useDatatableFeatures({
  pageName: PAGE_NAME,
  apiDataPath: "/asset/room-location-listing",
  defaultExportColumns: [...exportColumnLabels],
  getFilteredList: () => (datatableRef.value?.getExportConfig?.()?.data as Record<string, unknown>[]) ?? [],
  datatableRef,
  searchKeyword: q,
  applyFilters: () => void loadRows(),
});

async function exportExcel() {
  try {
    const cfg = datatableRef.value?.getExportConfig?.();
    const columnsOut = cfg?.columns ?? [...exportColumnLabels];
    const data = (cfg?.data as Record<string, unknown>[]) ?? [];
    if (data.length === 0) {
      toast.info("No data", "There is nothing to export.");
      return;
    }
    const ExcelJS = await import("exceljs");
    const wb = new ExcelJS.Workbook();
    const ws = wb.addWorksheet(PAGE_NAME);
    ws.addRow(["No", ...columnsOut]);
    data.forEach((row, idx) => {
      const values = columnsOut.map((c) => (row[c] ?? "") as string | number);
      ws.addRow([idx + 1, ...values]);
    });
    const buf = await wb.xlsx.writeBuffer();
    const blob = new Blob([buf], { type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" });
    const url = URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.href = url;
    a.download = `Location_Listing_${new Date().toISOString().slice(0, 10)}.xlsx`;
    a.click();
    URL.revokeObjectURL(url);
    toast.success("Excel downloaded");
  } catch (e) {
    toast.error("Export failed", e instanceof Error ? e.message : "Excel export failed.");
  }
}

async function loadRows() {
  loading.value = true;
  try {
    const params = new URLSearchParams({
      page: String(page.value),
      limit: String(limit.value),
      sort_dir: sortDir.value,
      ...(q.value.trim() ? { q: q.value.trim() } : {}),
    });
    const res = await listAssetRoomLocations(`?${params.toString()}`);
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
      <h1 class="page-title">{{ PAGE_TITLE }}</h1>

      <article class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 px-4 py-3">
          <h2 class="text-base font-semibold text-slate-900">{{ PAGE_NAME }}</h2>
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
          <FimsListTable
            v-else
            ref="datatableRef"
            :rows="rows"
            :columns="columns"
            :grouped="false"
            sort-by="id"
            sort-dir="asc"
            :row-key="(r) => r.rmmId"
            min-width="960px"
          />

          <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-3">
            <div class="text-xs text-slate-500">Showing {{ startIdx }}-{{ endIdx }} of {{ total }}</div>
            <div class="flex flex-wrap items-center gap-2">
              <button
                type="button"
                :disabled="page <= 1"
                class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium disabled:opacity-50"
                @click="prevPage"
              >
                Prev
              </button>
              <span class="text-xs text-slate-600">Page {{ page }} / {{ totalPages }}</span>
              <button
                type="button"
                :disabled="page >= totalPages"
                class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium disabled:opacity-50"
                @click="nextPage"
              >
                Next
              </button>
              <div class="mx-2 h-5 w-px bg-slate-200" />
              <button
                type="button"
                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium shadow-sm hover:bg-slate-50"
                @click="handleDownloadPDF"
              >
                <Download class="h-3.5 w-3.5" />
                PDF
              </button>
              <button
                type="button"
                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium shadow-sm hover:bg-slate-50"
                @click="handleDownloadCSV"
              >
                <FileDown class="h-3.5 w-3.5" />
                CSV
              </button>
              <button
                type="button"
                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium shadow-sm hover:bg-slate-50"
                @click="exportExcel"
              >
                <FileSpreadsheet class="h-3.5 w-3.5" />
                Excel
              </button>
              <button
                type="button"
                class="inline-flex items-center gap-1.5 rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-medium text-white shadow-sm hover:bg-slate-800"
                @click="onAddClick"
              >
                <Plus class="h-3.5 w-3.5" />
                Add
              </button>
            </div>
          </div>
        </div>
      </article>
    </div>
  </AdminLayout>
</template>
