<script setup lang="ts">
import { onMounted, onUnmounted, ref, watch } from "vue";
import { Download, FileDown, FileSpreadsheet, MoreVertical, Plus, Search, Trash2, X } from "lucide-vue-next";
import AdminLayout from "@/layouts/AdminLayout.vue";
import {
  createBudgetPlanningSchedule,
  deleteBudgetPlanningSchedule,
  getBudgetPlanningSchedule,
  getBudgetPlanningScheduleOptions,
  listBudgetPlanningSchedules,
  updateBudgetPlanningSchedule,
} from "@/api/cms";
import { useDatatableFeatures } from "@/composables/useDatatableFeatures";
import type { DatatableRefApi } from "@/composables/useDatatableFeatures";
import { useConfirmDialog } from "@/composables/useConfirmDialog";
import { useToast } from "@/composables/useToast";
import type {
  BudgetPlanningScheduleInput,
  BudgetPlanningScheduleOptions,
  BudgetPlanningScheduleRow,
} from "@/types";

const toast = useToast();
const { confirm } = useConfirmDialog();

const rows = ref<BudgetPlanningScheduleRow[]>([]);
const page = ref(1);
const limit = ref(10);
const q = ref("");
const total = ref(0);
const yearFilter = ref<string>("");
const showModal = ref(false);
const editId = ref<number | null>(null);
const options = ref<BudgetPlanningScheduleOptions>({ topFilter: { years: [] }, popupModal: { status: [] } });
const form = ref<BudgetPlanningScheduleInput>({
  bpsYearBudget: new Date().getFullYear() + 1,
  bpsPlanStartDate: "",
  bpsPlanEndDate: "",
  bpsStatus: "ACTIVE",
});

async function loadOptions() {
  const res = await getBudgetPlanningScheduleOptions();
  options.value = res.data;
}

async function loadRows() {
  const yearTrimmed = yearFilter.value.trim();
  const params = new URLSearchParams({
    page: String(page.value),
    limit: String(limit.value),
    ...(q.value ? { q: q.value } : {}),
    ...(yearTrimmed ? { year_filter: yearTrimmed } : {}),
  });
  const res = await listBudgetPlanningSchedules(`?${params.toString()}`);
  rows.value = res.data;
  total.value = Number(res.meta?.total ?? 0);
}

async function openEdit(row: BudgetPlanningScheduleRow) {
  if (row.isCurrentYear) {
    toast.error("Not allowed", "Schedule for the current year cannot be edited.");
    return;
  }
  const res = await getBudgetPlanningSchedule(row.bpsId);
  editId.value = row.bpsId;
  form.value = {
    bpsYearBudget: res.data.bpsYearBudget,
    bpsPlanStartDate: res.data.bpsPlanStartDate ?? "",
    bpsPlanEndDate: res.data.bpsPlanEndDate ?? "",
    bpsStatus: res.data.bpsStatus,
  };
  showModal.value = true;
}

function openCreate() {
  editId.value = null;
  form.value = {
    bpsYearBudget: new Date().getFullYear() + 1,
    bpsPlanStartDate: "",
    bpsPlanEndDate: "",
    bpsStatus: "ACTIVE",
  };
  showModal.value = true;
}

async function saveItem() {
  if (!form.value.bpsPlanStartDate || !form.value.bpsPlanEndDate) {
    toast.error("Validation", "Start and end dates are required.");
    return;
  }
  try {
    if (editId.value == null) {
      const res = await createBudgetPlanningSchedule(form.value);
      toast.success(res.data.successMessage ?? "New Schedule for Budget Planning successfully saved.");
    } else {
      const res = await updateBudgetPlanningSchedule(editId.value, form.value);
      toast.success(res.data.successMessage ?? "Schedule successfully updated.");
    }
    showModal.value = false;
    await loadRows();
  } catch (e) {
    toast.error("Save failed", e instanceof Error ? e.message : "Save failed");
  }
}

async function removeItem(row: BudgetPlanningScheduleRow) {
  if (row.isCurrentYear) {
    toast.error("Not allowed", "Schedule for the current year cannot be deleted.");
    return;
  }
  const accepted = await confirm({
    title: "Delete schedule",
    message: `Delete planning schedule for year ${row.bpsYearBudget}?`,
    confirmText: "Delete",
    destructive: true,
  });
  if (!accepted) return;
  try {
    await deleteBudgetPlanningSchedule(row.bpsId);
    toast.success("Schedule deleted");
    await loadRows();
  } catch (e) {
    toast.error("Delete failed", e instanceof Error ? e.message : "Delete failed");
  }
}

const exportColumns = ["Budget Year", "Planning Date", "Status"];

function toExportRow(r: BudgetPlanningScheduleRow): Record<string, string | number> {
  return {
    "Budget Year": r.bpsYearBudget ?? "",
    "Planning Date": r.planningDate ?? "",
    Status: r.bpsStatus ?? "",
  };
}

const datatableRef = ref<DatatableRefApi | null>(null);
const { templateFileInputRef, onTemplateFileChange, handleDownloadPDF, handleDownloadCSV } = useDatatableFeatures({
  pageName: "Budget Planning Schedule",
  apiDataPath: "/budget/planning-schedule",
  defaultExportColumns: exportColumns,
  getFilteredList: () => rows.value.map(toExportRow),
  datatableRef,
  searchKeyword: q,
  applyFilters: () => void loadRows(),
});

async function exportExcel() {
  try {
    if (rows.value.length === 0) {
      toast.info("No data", "There is nothing to export.");
      return;
    }
    const ExcelJS = await import("exceljs");
    const wb = new ExcelJS.Workbook();
    const ws = wb.addWorksheet("Budget Planning Schedule");
    ws.addRow(["No", ...exportColumns]);
    rows.value.forEach((r, idx) => {
      const row = toExportRow(r);
      ws.addRow([idx + 1, ...exportColumns.map((c) => row[c] ?? "")]);
    });
    const buf = await wb.xlsx.writeBuffer();
    const blob = new Blob([buf], { type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" });
    const url = URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.href = url;
    a.download = `Budget_Planning_Schedule_${new Date().toISOString().slice(0, 10)}.xlsx`;
    a.click();
    URL.revokeObjectURL(url);
    toast.success("Excel downloaded");
  } catch (e) {
    toast.error("Export failed", e instanceof Error ? e.message : "Excel export failed.");
  }
}

let searchDebounce: ReturnType<typeof setTimeout> | null = null;
watch(q, () => {
  if (searchDebounce) clearTimeout(searchDebounce);
  searchDebounce = setTimeout(() => {
    searchDebounce = null;
    page.value = 1;
    void loadRows();
  }, 350);
});

function applyTopFilter() {
  page.value = 1;
  void loadRows();
}

function resetTopFilter() {
  yearFilter.value = "";
  page.value = 1;
  void loadRows();
}

onMounted(async () => {
  await loadOptions();
  await loadRows();
});
onUnmounted(() => {
  if (searchDebounce) clearTimeout(searchDebounce);
});
</script>

<template>
  <AdminLayout>
    <div class="space-y-4">
      <input
        ref="templateFileInputRef"
        type="file"
        accept=".json,application/json"
        class="hidden"
        @change="onTemplateFileChange"
      />
      <h1 class="page-title">Budget / Setup / Budget Planning Schedule</h1>

      <article class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-4 py-3">
          <h1 class="text-base font-semibold text-slate-900">Top Filter</h1>
        </div>
        <div class="grid gap-3 p-4 md:grid-cols-3">
          <div>
            <label class="mb-1 block text-xs font-medium text-slate-600">Year</label>
            <input
              id="bps-top-filter-year"
              v-model="yearFilter"
              type="text"
              inputmode="numeric"
              autocomplete="off"
              maxlength="9"
              placeholder="e.g. 2026"
              list="budget-planning-schedule-year-hints"
              class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
              @keyup.enter="applyTopFilter"
            />
            <datalist id="budget-planning-schedule-year-hints">
              <option v-for="opt in options.topFilter.years" :key="opt.id" :value="opt.label" />
            </datalist>
          </div>
          <div class="md:col-span-2 flex items-end justify-end gap-2">
            <button
              type="button"
              class="rounded-lg border border-slate-300 px-4 py-2 text-sm"
              @click="resetTopFilter"
            >
              Reset
            </button>
            <button
              type="button"
              class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white"
              @click="applyTopFilter"
            >
              Apply
            </button>
          </div>
        </div>
      </article>

      <article class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
          <h1 class="text-base font-semibold text-slate-900">Listing Schedule</h1>
          <button class="rounded-lg p-2 text-slate-500 hover:bg-slate-100" aria-label="More">
            <MoreVertical class="h-4 w-4" />
          </button>
        </div>
        <div class="space-y-4 p-4">
          <div class="flex flex-wrap items-end justify-between gap-4">
            <div class="flex items-center gap-2">
              <label class="text-xs font-medium text-slate-600">Display</label>
              <select
                v-model.number="limit"
                class="rounded-lg border border-slate-300 px-2 py-1.5 text-sm"
                @change="page = 1; void loadRows()"
              >
                <option v-for="n in [5, 10, 25, 50, 100]" :key="n" :value="n">{{ n }}</option>
              </select>
            </div>
            <div class="flex items-center gap-2">
              <label class="text-xs font-medium text-slate-600">Search</label>
              <div class="relative">
                <Search class="pointer-events-none absolute left-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400" />
                <input
                  v-model="q"
                  type="search"
                  placeholder="Filter rows..."
                  class="w-56 rounded-lg border border-slate-300 py-1.5 pl-8 pr-8 text-sm"
                  @keyup.enter="page = 1; void loadRows()"
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
          </div>
          <div class="overflow-x-auto rounded-lg border border-slate-200">
            <div :class="rows.length > 10 ? 'max-h-[420px] overflow-y-auto' : ''">
              <table class="w-full min-w-[800px] text-sm">
                <thead class="sticky top-0 bg-slate-50">
                  <tr class="border-b border-slate-200 text-left">
                    <th class="px-3 py-2 text-xs font-semibold uppercase">No</th>
                    <th class="px-3 py-2 text-xs font-semibold uppercase">Budget Year</th>
                    <th class="px-3 py-2 text-xs font-semibold uppercase">Planning Date</th>
                    <th class="px-3 py-2 text-xs font-semibold uppercase">Status</th>
                    <th class="px-3 py-2 text-xs font-semibold uppercase">Action</th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="row in rows"
                    :key="row.bpsId"
                    class="border-b border-slate-100 hover:bg-slate-50"
                  >
                    <td class="px-3 py-2">{{ row.index }}</td>
                    <td class="px-3 py-2">{{ row.bpsYearBudget }}</td>
                    <td class="px-3 py-2">{{ row.planningDate ?? "—" }}</td>
                    <td class="px-3 py-2">{{ row.bpsStatus }}</td>
                    <td class="px-3 py-2">
                      <div class="flex items-center gap-1">
                        <button
                          class="rounded p-1 text-slate-500 hover:bg-slate-100 disabled:opacity-40 disabled:hover:bg-transparent"
                          :disabled="row.isCurrentYear"
                          title="Edit"
                          @click="openEdit(row)"
                        >
                          ✎
                        </button>
                        <button
                          class="rounded p-1 text-rose-500 hover:bg-rose-50 disabled:opacity-40 disabled:hover:bg-transparent"
                          :disabled="row.isCurrentYear"
                          title="Delete"
                          @click="removeItem(row)"
                        >
                          <Trash2 class="h-3.5 w-3.5" />
                        </button>
                      </div>
                    </td>
                  </tr>
                  <tr v-if="rows.length === 0">
                    <td colspan="5" class="px-3 py-6 text-center text-xs text-slate-500">No data</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          <div class="flex flex-wrap items-center justify-end gap-2 border-t border-slate-100 pt-3">
            <button
              type="button"
              class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium hover:bg-slate-50"
              @click="handleDownloadPDF"
            >
              <Download class="h-3.5 w-3.5" />PDF
            </button>
            <button
              type="button"
              class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium hover:bg-slate-50"
              @click="handleDownloadCSV"
            >
              <FileDown class="h-3.5 w-3.5" />CSV
            </button>
            <button
              type="button"
              class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium hover:bg-slate-50"
              @click="exportExcel"
            >
              <FileSpreadsheet class="h-3.5 w-3.5" />Excel
            </button>
            <button
              type="button"
              class="inline-flex items-center gap-1.5 rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-medium text-white hover:bg-slate-800"
              @click="openCreate"
            >
              <Plus class="h-3.5 w-3.5" />Add
            </button>
          </div>
        </div>
      </article>
    </div>

    <Teleport to="body">
      <div
        v-if="showModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm"
        @click.self="showModal = false"
      >
        <div class="w-full max-w-lg rounded-lg border border-slate-200 bg-white shadow-2xl">
          <div class="border-b border-slate-100 px-4 py-3">
            <h3 class="text-base font-semibold text-slate-900">
              {{ editId == null ? "New" : "Edit" }} Planning Details
            </h3>
          </div>
          <div class="space-y-4 p-4">
            <div v-if="editId != null">
              <label class="mb-1 block text-sm font-medium text-slate-700">Planning ID</label>
              <input
                :value="editId"
                type="text"
                disabled
                class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-500"
              />
            </div>
            <div>
              <label class="mb-1 block text-sm font-medium text-slate-700">Year <span class="text-rose-500">*</span></label>
              <input
                v-model.number="form.bpsYearBudget"
                :disabled="editId != null"
                type="number"
                min="2000"
                max="9999"
                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm disabled:bg-slate-50 disabled:text-slate-500"
              />
            </div>
            <div>
              <label class="mb-1 block text-sm font-medium text-slate-700">Planning Start Date <span class="text-rose-500">*</span></label>
              <input
                v-model="form.bpsPlanStartDate"
                type="date"
                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
              />
            </div>
            <div>
              <label class="mb-1 block text-sm font-medium text-slate-700">Planning End Date <span class="text-rose-500">*</span></label>
              <input
                v-model="form.bpsPlanEndDate"
                type="date"
                :min="form.bpsPlanStartDate || undefined"
                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
              />
            </div>
            <div>
              <label class="mb-1 block text-sm font-medium text-slate-700">Status <span class="text-rose-500">*</span></label>
              <select
                v-model="form.bpsStatus"
                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
              >
                <option v-for="opt in options.popupModal.status" :key="opt.id" :value="opt.id">{{ opt.label }}</option>
              </select>
            </div>
          </div>
          <div class="flex justify-end gap-2 border-t border-slate-100 px-4 py-3">
            <button
              type="button"
              class="rounded-lg border border-slate-300 px-4 py-2 text-sm"
              @click="showModal = false"
            >
              Cancel
            </button>
            <button
              type="button"
              class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white"
              @click="saveItem"
            >
              Save
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </AdminLayout>
</template>
