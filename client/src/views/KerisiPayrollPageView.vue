<script setup lang="ts">
/**
 * Payroll Kerisi shell view.
 * Uses the Payroll registry (PAGE_MENUID1122_LEVEL3.json → kerisi-payroll-registry.generated.ts)
 * and the shell list API at GET /api/payroll/kerisi/{menuId}.
 *
 * Covers all 67 Payroll menus: Lookup, Setup, Staff Profile, Salary Processing,
 * Salary Crediting, Allowance & Deduction, Employee Benefit, Income Tax, Report,
 * Integration, and Kew 8 sub-modules.
 * Renders datatable(s) + smart filter (when present) + top filter + form sections.
 */
import { computed, onMounted, onUnmounted, ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import {
  ChevronLeft,
  Download,
  FileDown,
  FileSpreadsheet,
  Filter,
  MoreVertical,
  Pencil,
  Plus,
  Search,
  Trash2,
  X,
} from "lucide-vue-next";
import AdminLayout from "@/layouts/AdminLayout.vue";
import { listKerisiPayrollData } from "@/api/cms";
import {
  getKerisiMenuTrailByMenuId,
  parseKerisiNumericMenuIdFromPath,
} from "@/config/kerisi-menu-resolve";
import type { KerisiPayrollDatatable, KerisiPayrollPageSpec } from "@/config/kerisi-payroll-registry.generated";
import { getKerisiPayrollSpec } from "@/config/kerisi-payroll-registry.generated";
import { useToast } from "@/composables/useToast";

const toast  = useToast();
const route  = useRoute();
const router = useRouter();

const menuId = computed(() => parseKerisiNumericMenuIdFromPath(route.path));

const spec = computed<KerisiPayrollPageSpec | null>(() => {
  const id = menuId.value;
  if (id === null) return null;
  return getKerisiPayrollSpec(id);
});

const pageHeading = computed(() => {
  const trail = menuId.value !== null ? getKerisiMenuTrailByMenuId(menuId.value) : null;
  if (trail?.length) return trail.join(" / ");
  return spec.value?.pageTitle ?? "Payroll";
});

const hasBackButton = computed(() => spec.value?.hasBackButton ?? false);

function goBack() {
  router.back();
}

// ── datatable helpers ──────────────────────────────────────────────────────
function toStr(v: string | Record<string, unknown> | undefined): string {
  if (v === undefined || v === null) return "";
  if (typeof v === "string") return v;
  return "";
}

function cellKey(dt: KerisiPayrollDatatable, colIdx: number): string {
  const raw = toStr(dt.dtKey[colIdx]);
  if (raw.trim()) return raw.trim();
  const lab = toStr(dt.dtBi[colIdx]) || `col_${colIdx}`;
  return lab.replace(/\s+/g, "_").toLowerCase();
}

function displayCell(row: Record<string, unknown>, dt: KerisiPayrollDatatable, colIdx: number): string {
  const key = cellKey(dt, colIdx);
  const tryKey = (k: string) => {
    const v = row[k];
    return v !== undefined && v !== null ? String(v) : null;
  };

  const direct = tryKey(key);
  if (direct !== null) return direct;

  // PascalCase → camelCase
  if (/^[A-Z]/.test(key)) {
    const cc = key.charAt(0).toLowerCase() + key.slice(1);
    const v2 = tryKey(cc);
    if (v2 !== null) return v2;
  }

  // snake_case → camelCase
  const camel = key.replace(/_([a-z])/g, (_, c: string) => c.toUpperCase());
  const v3 = tryKey(camel);
  if (v3 !== null) return v3;

  // compact match (ignore non-alphanumeric)
  const compact = key.replace(/[^a-zA-Z0-9]/g, "").toLowerCase();
  if (compact.length) {
    for (const [rk, rv] of Object.entries(row)) {
      if (rk.replace(/[^a-zA-Z0-9]/g, "").toLowerCase() === compact) {
        if (rv !== undefined && rv !== null) return String(rv);
      }
    }
  }
  return "";
}

function isActionCol(h: string | Record<string, unknown>): boolean {
  const t = toStr(h).trim().toLowerCase();
  return t === "action" || t.startsWith("action") || t.includes("checkbox");
}

function isNoCol(h: string | Record<string, unknown>): boolean {
  const t = toStr(h).trim().toLowerCase();
  return t === "no" || t === "no.";
}

function tableColspan(dt: KerisiPayrollDatatable): number {
  return dt.dtBi.length;
}

function hasFreezeLeft(dt: KerisiPayrollDatatable): boolean {
  return (dt.dtFreezeLeft ?? 0) > 0;
}

// ── layout: form-before-datatable ─────────────────────────────────────────
const formBeforeDataTable = computed(() => {
  const s = spec.value;
  if (!s || s.formSections.length === 0) return false;
  const firstFormId = s.formSections[0]?.componentId ?? Infinity;
  const firstDtId   = s.datatables[0]?.componentId   ?? Infinity;
  return firstFormId < firstDtId;
});

/** Deduplicated form section groups keyed by componentTitle. */
const formSectionGroups = computed(() => {
  const s = spec.value;
  if (!s) return [] as { title: string; fields: KerisiPayrollPageSpec["formSections"] }[];
  const seen = new Map<string, KerisiPayrollPageSpec["formSections"][number][]>();
  for (const f of s.formSections) {
    const key = f.componentTitle || "Details";
    if (!seen.has(key)) seen.set(key, []);
    seen.get(key)!.push(f);
  }
  return [...seen.entries()].map(([title, fields]) => ({ title, fields }));
});

// ── smart filter ─────────────────────────────────────────────────────────
const primaryDt = computed<KerisiPayrollDatatable | null>(() => spec.value?.datatables[0] ?? null);
const showSmartFilterUi = computed(() => (spec.value?.smartFilterFields?.length ?? 0) > 0);
const showTopFilterUi   = computed(() => (spec.value?.topFilterFields?.length ?? 0) > 0);
const hasPopupForm      = computed(() => (spec.value?.popupFormFields?.length ?? 0) > 0);

const showSmartFilter = ref(false);
const smartFilterValues = ref<Record<string, string>>({});
const topFilterValues   = ref<Record<string, string>>({});

function initFilters() {
  const sfFields = spec.value?.smartFilterFields ?? [];
  const sfVals: Record<string, string> = {};
  sfFields.forEach((_, i) => (sfVals[`sf_${i}`] = ""));
  smartFilterValues.value = sfVals;

  const tfFields = spec.value?.topFilterFields ?? [];
  const tfVals: Record<string, string> = {};
  tfFields.forEach((_, i) => (tfVals[`tf_${i}`] = ""));
  topFilterValues.value = tfVals;

  q.value = "";
  page.value = 1;
}

function resetSmartFilter() {
  Object.keys(smartFilterValues.value).forEach((k) => (smartFilterValues.value[k] = ""));
}

function applySmartFilter() {
  showSmartFilter.value = false;
  page.value = 1;
  void loadRows();
}

// ── popup modal ──────────────────────────────────────────────────────────
const showPopupModal   = ref(false);
const modalMode        = ref<"add" | "edit">("add");
const popupFormValues  = ref<Record<string, string>>({});

function openAddModal() {
  modalMode.value = "add";
  popupFormValues.value = {};
  showPopupModal.value = true;
}

function openEditModal(row: Record<string, unknown>) {
  modalMode.value = "edit";
  popupFormValues.value = Object.fromEntries(
    Object.entries(row).map(([k, v]) => [k, v !== null && v !== undefined ? String(v) : ""])
  );
  showPopupModal.value = true;
}

// ── list state ────────────────────────────────────────────────────────────
const rows    = ref<Record<string, unknown>[]>([]);
const loading = ref(false);
const total   = ref(0);
const page    = ref(1);
const limit   = ref(10);
const q       = ref("");
let searchDebounce: ReturnType<typeof setTimeout> | null = null;

async function loadRows() {
  const id = menuId.value;
  if (id === null) return;
  loading.value = true;

  const params = new URLSearchParams({
    page: String(page.value),
    limit: String(limit.value),
  });
  if (q.value.trim()) params.set("q", q.value.trim());

  Object.entries(smartFilterValues.value).forEach(([k, v]) => {
    if (v.trim()) params.set(k, v.trim());
  });
  Object.entries(topFilterValues.value).forEach(([k, v]) => {
    if (v.trim()) params.set(k, v.trim());
  });

  // Pass route query params
  for (const [k, v] of Object.entries(route.query)) {
    if (v && !params.has(k)) params.set(k, String(Array.isArray(v) ? v[0] : v));
  }

  try {
    const res = await listKerisiPayrollData(id, `?${params.toString()}`);
    rows.value  = Array.isArray(res.data) ? res.data : [];
    total.value = Number(res.meta?.total ?? 0);
    const m = res.meta as Record<string, unknown> | undefined;
    if (m?.shellError && typeof m.shellError === "string") {
      toast.error("List source", m.shellError);
    }
  } catch (e) {
    toast.error("Load failed", e instanceof Error ? e.message : "Unable to load list.");
    rows.value  = [];
    total.value = 0;
  } finally {
    loading.value = false;
  }
}

function onSearch() {
  if (searchDebounce) clearTimeout(searchDebounce);
  searchDebounce = setTimeout(() => {
    page.value = 1;
    void loadRows();
  }, 350);
}

function clearSearch() {
  q.value = "";
  page.value = 1;
  void loadRows();
}

function onSearchKeydown(e: KeyboardEvent) {
  if (e.key === "Enter") {
    if (searchDebounce) clearTimeout(searchDebounce);
    page.value = 1;
    void loadRows();
  }
}

const totalPages = computed(() => (total.value > 0 ? Math.ceil(total.value / limit.value) : 1));

function prevPage() {
  if (page.value > 1) { page.value--; void loadRows(); }
}
function nextPage() {
  if (page.value < totalPages.value) { page.value++; void loadRows(); }
}
function onLimitChange() {
  page.value = 1;
  void loadRows();
}

// ── top filter apply ──────────────────────────────────────────────────────
function applyTopFilter() {
  page.value = 1;
  void loadRows();
}

// ── export stubs ──────────────────────────────────────────────────────────
function handleDownloadPDF() {
  toast.success("Export", "PDF export — connect backend when ready.");
}
function handleDownloadCSV() {
  const dt = primaryDt.value;
  if (!dt || rows.value.length === 0) { toast.error("Export", "No data to export."); return; }
  const headers = dt.dtBi.filter((h, i) => !isActionCol(h) && !isNoCol(h));
  const keyIdxs = dt.dtBi.map((_, i) => i).filter((i) => !isActionCol(dt.dtBi[i] ?? "") && !isNoCol(dt.dtBi[i] ?? ""));
  const csvRows = [
    headers.join(","),
    ...rows.value.map((row) =>
      keyIdxs.map((i) => `"${String(displayCell(row, dt, i)).replace(/"/g, '""')}"`).join(",")
    ),
  ];
  const blob = new Blob([csvRows.join("\n")], { type: "text/csv" });
  const url  = URL.createObjectURL(blob);
  const a    = document.createElement("a");
  a.href     = url;
  a.download = `${spec.value?.pageTitle ?? "export"}.csv`;
  a.click();
  URL.revokeObjectURL(url);
}
function handleDownloadExcel() {
  toast.success("Export", "Excel export — connect backend when ready.");
}

onMounted(() => {
  initFilters();
  void loadRows();
});

watch(menuId, () => {
  initFilters();
  void loadRows();
});

onUnmounted(() => {
  if (searchDebounce) clearTimeout(searchDebounce);
});
</script>

<template>
  <AdminLayout>
    <div class="space-y-4">
      <!-- Invalid route guard -->
      <div
        v-if="menuId === null"
        class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900"
      >
        Invalid Payroll menu route.
      </div>

      <template v-else>
        <!-- Page title -->
        <div v-if="hasBackButton" class="flex items-center gap-2">
          <button
            type="button"
            class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-2.5 py-1 text-xs text-slate-600 hover:bg-slate-50"
            aria-label="Back"
            @click="goBack"
          >
            <ChevronLeft class="h-3.5 w-3.5" />
            Back
          </button>
          <h1 class="page-title">{{ pageHeading }}</h1>
        </div>
        <h1 v-else class="page-title">{{ pageHeading }}</h1>

        <!-- Top Filter panel (rendered above datatables when present) -->
        <article v-if="showTopFilterUi" class="rounded-lg border border-slate-200 bg-white shadow-sm">
          <div class="border-b border-slate-100 px-4 py-3">
            <h2 class="text-base font-semibold text-slate-900">Filter</h2>
          </div>
          <div class="grid gap-3 p-4 sm:grid-cols-2 lg:grid-cols-3">
            <div v-for="(f, fi) in spec?.topFilterFields ?? []" :key="'tf-' + fi">
              <label class="mb-1 block text-xs font-medium text-slate-600">{{ f.title }}</label>
              <select
                v-if="f.fieldType === 'dropdown' || f.lookupQuery"
                v-model="topFilterValues[`tf_${fi}`]"
                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400"
              >
                <option value="">— All —</option>
              </select>
              <input
                v-else
                v-model="topFilterValues[`tf_${fi}`]"
                :type="f.fieldType === 'date' ? 'date' : 'text'"
                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400"
                :placeholder="f.title"
              />
            </div>
          </div>
          <div class="flex justify-end gap-2 px-4 pb-4">
            <button
              type="button"
              class="rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50"
              @click="() => { initFilters(); void loadRows(); }"
            >
              Reset
            </button>
            <button
              type="button"
              class="rounded-lg bg-slate-900 px-4 py-2 text-sm text-white hover:bg-slate-700"
              @click="applyTopFilter"
            >
              Search
            </button>
          </div>
        </article>

        <!-- Form sections BEFORE datatable -->
        <template v-if="formBeforeDataTable">
          <article
            v-for="grp in formSectionGroups"
            :key="grp.title"
            class="rounded-lg border border-slate-200 bg-white shadow-sm"
          >
            <div class="border-b border-slate-100 px-4 py-3">
              <h2 class="text-base font-semibold text-slate-900">{{ grp.title }}</h2>
            </div>
            <div class="grid gap-3 p-4 sm:grid-cols-2">
              <div v-for="(f, fi) in grp.fields" :key="'fsbf-' + fi">
                <label class="mb-1 block text-xs font-medium text-slate-600">{{ f.title }}</label>
                <input
                  disabled
                  class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-500"
                  :placeholder="f.fieldType"
                  value=""
                />
              </div>
            </div>
          </article>
        </template>

        <!-- Datatable sections -->
        <section
          v-for="(dt, di) in spec?.datatables ?? []"
          :key="dt.componentId + '-' + di"
          class="rounded-lg border border-slate-200 bg-white shadow-sm"
        >
          <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
            <h2 class="text-base font-semibold text-slate-900">
              {{ dt.componentTitle || "Data" }}
            </h2>
            <div class="flex items-center gap-2">
              <!-- Add button (only on popup-modal pages or default) -->
              <button
                v-if="hasPopupForm || di === 0"
                type="button"
                class="inline-flex items-center gap-1.5 rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-medium text-white hover:bg-slate-700"
                @click="openAddModal"
              >
                <Plus class="h-3.5 w-3.5" />
                Add
              </button>
              <button
                type="button"
                class="inline-flex items-center gap-1 rounded-lg border border-slate-300 px-3 py-1.5 text-xs text-slate-600 hover:bg-slate-50"
                @click="handleDownloadPDF"
              >
                <FileDown class="h-3.5 w-3.5" />
                PDF
              </button>
              <button
                type="button"
                class="inline-flex items-center gap-1 rounded-lg border border-slate-300 px-3 py-1.5 text-xs text-slate-600 hover:bg-slate-50"
                @click="handleDownloadCSV"
              >
                <Download class="h-3.5 w-3.5" />
                CSV
              </button>
              <button
                type="button"
                class="inline-flex items-center gap-1 rounded-lg border border-slate-300 px-3 py-1.5 text-xs text-slate-600 hover:bg-slate-50"
                @click="handleDownloadExcel"
              >
                <FileSpreadsheet class="h-3.5 w-3.5" />
                Excel
              </button>
              <button
                type="button"
                class="rounded-lg p-1.5 text-slate-500 hover:bg-slate-100"
                aria-label="More options"
              >
                <MoreVertical class="h-4 w-4" />
              </button>
            </div>
          </div>

          <div class="space-y-3 p-4">
            <!-- Search + smart-filter bar (primary datatable only) -->
            <template v-if="di === 0">
              <div class="flex items-center gap-2">
                <div class="relative flex-1">
                  <Search class="pointer-events-none absolute left-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400" />
                  <input
                    v-model="q"
                    type="search"
                    placeholder="Filter rows…"
                    class="h-8 w-full rounded-lg border border-slate-300 pl-8 pr-8 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400"
                    @input="onSearch"
                    @keydown="onSearchKeydown"
                  />
                  <button
                    v-if="q"
                    type="button"
                    class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-700"
                    @click="clearSearch"
                  >
                    <X class="h-3.5 w-3.5" />
                  </button>
                </div>
                <button
                  v-if="showSmartFilterUi"
                  type="button"
                  class="inline-flex h-8 items-center gap-1.5 rounded-lg border border-slate-300 px-3 text-sm text-slate-600 hover:bg-slate-50"
                  @click="showSmartFilter = true"
                >
                  <Filter class="h-3.5 w-3.5" />
                  Filter
                </button>
              </div>
            </template>

            <!-- Table -->
            <div :class="hasFreezeLeft(dt) ? 'overflow-x-auto' : ''">
              <table class="admin-table-kitchen">
                <thead class="admin-table-thead-sticky">
                  <tr>
                    <th v-for="(h, hi) in dt.dtBi" :key="hi">
                      {{ isNoCol(h) ? "No" : h }}
                    </th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-if="loading">
                    <td :colspan="tableColspan(dt)" class="px-3 py-6 text-center text-sm text-slate-400">
                      Loading…
                    </td>
                  </tr>
                  <tr v-else-if="rows.length === 0 && di === 0">
                    <td :colspan="tableColspan(dt)" class="admin-table-kitchen-caption">No records found.</td>
                  </tr>
                  <tr v-else v-for="(row, ri) in rows" :key="ri">
                    <td v-for="(h, hi) in dt.dtBi" :key="hi">
                      <template v-if="isNoCol(h)">{{ (page - 1) * limit + ri + 1 }}</template>
                      <template v-else-if="isActionCol(h)">
                        <div class="flex items-center gap-1">
                          <button
                            type="button"
                            class="rounded p-1 text-slate-500 hover:bg-slate-100 hover:text-slate-800"
                            title="Edit"
                            @click="openEditModal(row)"
                          >
                            <Pencil class="h-3.5 w-3.5" />
                          </button>
                          <button
                            type="button"
                            class="rounded p-1 text-red-400 hover:bg-red-50 hover:text-red-600"
                            title="Delete"
                          >
                            <Trash2 class="h-3.5 w-3.5" />
                          </button>
                        </div>
                      </template>
                      <template v-else>{{ displayCell(row, dt, hi) }}</template>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Pagination (primary datatable only) -->
            <template v-if="di === 0">
              <div class="flex items-center justify-between pt-1">
                <div class="flex items-center gap-2 text-xs text-slate-500">
                  <span>Display</span>
                  <select
                    v-model="limit"
                    class="rounded border border-slate-300 px-2 py-1 text-xs"
                    @change="onLimitChange"
                  >
                    <option v-for="n in [5, 10, 25, 50, 100]" :key="n" :value="n">{{ n }}</option>
                  </select>
                  <span>entries</span>
                  <span class="ml-4">
                    {{ total === 0 ? "No records" : `Showing ${(page - 1) * limit + 1}-${Math.min(page * limit, total)} of ${total}` }}
                  </span>
                </div>
                <div class="flex items-center gap-1">
                  <button
                    type="button"
                    :disabled="page <= 1"
                    class="rounded border border-slate-300 px-2.5 py-1 text-xs disabled:opacity-40 hover:bg-slate-50"
                    @click="prevPage"
                  >
                    ‹
                  </button>
                  <span class="px-2 text-xs text-slate-600">{{ page }} / {{ totalPages }}</span>
                  <button
                    type="button"
                    :disabled="page >= totalPages"
                    class="rounded border border-slate-300 px-2.5 py-1 text-xs disabled:opacity-40 hover:bg-slate-50"
                    @click="nextPage"
                  >
                    ›
                  </button>
                </div>
              </div>
            </template>
          </div>
        </section>

        <!-- Form sections AFTER datatable -->
        <template v-if="!formBeforeDataTable">
          <article
            v-for="grp in formSectionGroups"
            :key="grp.title"
            class="rounded-lg border border-slate-200 bg-white shadow-sm"
          >
            <div class="border-b border-slate-100 px-4 py-3">
              <h2 class="text-base font-semibold text-slate-900">{{ grp.title }}</h2>
            </div>
            <div class="grid gap-3 p-4 sm:grid-cols-2">
              <div v-for="(f, fi) in grp.fields" :key="'fsaf-' + fi">
                <label class="mb-1 block text-xs font-medium text-slate-600">{{ f.title }}</label>
                <input
                  disabled
                  class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-500"
                  :placeholder="f.fieldType"
                  value=""
                />
              </div>
            </div>
          </article>
        </template>

        <!-- Placeholder when no spec found -->
        <article
          v-if="!spec"
          class="rounded-lg border border-slate-200 bg-white shadow-sm"
        >
          <div class="flex flex-col items-center justify-center px-4 py-16 text-center">
            <h2 class="text-lg font-semibold text-slate-700">Under Development</h2>
            <p class="mt-1 max-w-md text-sm text-slate-400">
              This Payroll screen is not in the registry yet.
            </p>
            <p v-if="menuId !== null" class="mt-2 text-xs text-slate-500">MENUID {{ menuId }}</p>
          </div>
        </article>
      </template>
    </div>

    <!-- Smart Filter Modal -->
    <Teleport to="body">
      <div
        v-if="showSmartFilter"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40"
        @click.self="showSmartFilter = false"
      >
        <div class="w-full max-w-lg rounded-xl border border-slate-200 bg-white shadow-xl">
          <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
            <h3 class="text-base font-semibold text-slate-900">Smart Filter</h3>
            <button
              type="button"
              class="text-slate-400 hover:text-slate-700"
              @click="showSmartFilter = false"
            >
              <X class="h-4 w-4" />
            </button>
          </div>
          <div class="grid gap-3 p-5 sm:grid-cols-2">
            <div
              v-for="(f, fi) in spec?.smartFilterFields ?? []"
              :key="'sf-' + fi"
            >
              <label class="mb-1 block text-xs font-medium text-slate-600">{{ f.title }}</label>
              <select
                v-if="f.fieldType === 'dropdown' || f.lookupQuery"
                v-model="smartFilterValues[`sf_${fi}`]"
                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400"
              >
                <option value="">— All —</option>
              </select>
              <input
                v-else
                v-model="smartFilterValues[`sf_${fi}`]"
                :type="f.fieldType === 'date' ? 'date' : 'text'"
                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400"
                :placeholder="f.title"
              />
            </div>
          </div>
          <div class="flex justify-end gap-2 border-t border-slate-100 px-5 py-4">
            <button
              type="button"
              class="rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50"
              @click="resetSmartFilter"
            >
              Reset
            </button>
            <button
              type="button"
              class="rounded-lg bg-slate-900 px-4 py-2 text-sm text-white hover:bg-slate-700"
              @click="applySmartFilter"
            >
              OK
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Popup Modal (Add/Edit) -->
    <Teleport to="body">
      <div
        v-if="showPopupModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40"
        @click.self="showPopupModal = false"
      >
        <div class="w-full max-w-lg rounded-xl border border-slate-200 bg-white shadow-xl">
          <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
            <h3 class="text-base font-semibold text-slate-900">
              {{ modalMode === "add" ? "Add" : "Edit" }} {{ spec?.pageTitle ?? "Record" }}
            </h3>
            <button
              type="button"
              class="text-slate-400 hover:text-slate-700"
              @click="showPopupModal = false"
            >
              <X class="h-4 w-4" />
            </button>
          </div>
          <div class="grid gap-3 p-5 sm:grid-cols-2">
            <div
              v-for="(f, fi) in spec?.popupFormFields ?? []"
              :key="'pf-' + fi"
              :class="f.cssClass?.includes('d-none') ? 'hidden' : ''"
            >
              <label class="mb-1 block text-xs font-medium text-slate-600">{{ f.title }}</label>
              <select
                v-if="f.fieldType === 'dropdown' || f.lookupQuery"
                v-model="popupFormValues[`pf_${fi}`]"
                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400"
                :disabled="f.isDisabled"
              >
                <option value="">— Select —</option>
              </select>
              <textarea
                v-else-if="f.fieldType === 'textarea'"
                v-model="popupFormValues[`pf_${fi}`]"
                rows="3"
                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400"
                :placeholder="f.title"
                :disabled="f.isDisabled"
              />
              <input
                v-else
                v-model="popupFormValues[`pf_${fi}`]"
                :type="f.fieldType === 'date' ? 'date' : f.fieldType === 'number' ? 'number' : 'text'"
                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400 disabled:bg-slate-50 disabled:text-slate-500"
                :placeholder="f.title"
                :disabled="f.isDisabled"
              />
            </div>
          </div>
          <div class="flex justify-end gap-2 border-t border-slate-100 px-5 py-4">
            <button
              type="button"
              class="rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50"
              @click="showPopupModal = false"
            >
              Cancel
            </button>
            <button
              type="button"
              class="rounded-lg bg-slate-900 px-4 py-2 text-sm text-white hover:bg-slate-700"
              @click="showPopupModal = false"
            >
              Save
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </AdminLayout>
</template>
