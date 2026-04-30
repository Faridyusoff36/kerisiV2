<script setup lang="ts">
/**
 * Account Receivable Kerisi shell view.
 * Uses the AR registry (PAGE_MENUID1024_LEVEL3.json → kerisi-ar-registry.generated.ts)
 * and the shell list API at GET /api/account-receivable/kerisi-ar/{menuId}.
 *
 * Covers all 32 AR menus: Invoice, Receipt, Cheque, Offline Receipt, and Setup sub-modules.
 * Renders datatable(s) + smart filter (when present) + form sections (above or below grid
 * depending on componentId order). Popup-modal pages show fields as read-only placeholders
 * pending full CRUD wiring.
 */
import { computed, onMounted, onUnmounted, ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import {
  ChevronLeft,
  Download,
  Eye,
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
import { listKerisiArData } from "@/api/cms";
import {
  getKerisiMenuTrailByMenuId,
  parseKerisiNumericMenuIdFromPath,
} from "@/config/kerisi-menu-resolve";
import type { KerisiArDatatable, KerisiArPageSpec } from "@/config/kerisi-ar-registry.generated";
import { getKerisiArSpec } from "@/config/kerisi-ar-registry.generated";
import { useToast } from "@/composables/useToast";

const toast   = useToast();
const route   = useRoute();
const router  = useRouter();

const menuId = computed(() => parseKerisiNumericMenuIdFromPath(route.path));

const spec = computed<KerisiArPageSpec | null>(() => {
  const id = menuId.value;
  if (id === null) return null;
  return getKerisiArSpec(id);
});

const pageHeading = computed(() => {
  const trail = menuId.value !== null ? getKerisiMenuTrailByMenuId(menuId.value) : null;
  if (trail?.length) return trail.join(" / ");
  return spec.value?.pageTitle ?? "Account Receivable";
});

const hasBackButton = computed(() => spec.value?.hasBackButton ?? false);

function goBack() {
  router.back();
}

// ── datatable helpers ──────────────────────────────────────────────────────
function toStr(v: string | Record<string, unknown> | undefined): string {
  if (v === null || v === undefined) return "";
  if (typeof v === "string") return v;
  return "";
}

function cellKey(dt: KerisiArDatatable, colIdx: number): string {
  const raw = toStr(dt.dtKey[colIdx]);
  if (raw.trim()) return raw.trim();
  const lab = toStr(dt.dtBi[colIdx]) || `col_${colIdx}`;
  return lab.replace(/\s+/g, "_").toLowerCase();
}

function displayCell(row: Record<string, unknown>, dt: KerisiArDatatable, colIdx: number): string {
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

function tableColspan(dt: KerisiArDatatable): number {
  return dt.dtBi.length;
}

// ── layout: form-before-datatable ─────────────────────────────────────────
/**
 * If the first form section's componentId is lower than the first datatable's
 * componentId, the form panel renders ABOVE the grid (e.g. MENUID 1339 pattern).
 */
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
  if (!s) return [] as { title: string; fields: KerisiArPageSpec["formSections"] }[];
  const seen = new Map<string, typeof s.formSections[number][]>();
  for (const f of s.formSections) {
    const key = f.componentTitle || "Details";
    if (!seen.has(key)) seen.set(key, []);
    seen.get(key)!.push(f);
  }
  return [...seen.entries()].map(([title, fields]) => ({ title, fields }));
});

// ── smart filter ─────────────────────────────────────────────────────────
const primaryDt = computed<KerisiArDatatable | null>(() => spec.value?.datatables[0] ?? null);

const showSmartFilterUi = computed(() => (spec.value?.smartFilterFields?.length ?? 0) > 0);
const showSmartFilter = ref(false);
const smartFilterValues = ref<Record<string, string>>({});

function initFilters() {
  const fields = spec.value?.smartFilterFields ?? [];
  const vals: Record<string, string> = {};
  fields.forEach((_, i) => (vals[`sf_${i}`] = ""));
  smartFilterValues.value = vals;
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
  // Pass any relevant route query params (e.g. cr_cheque_no for MENUID 2528)
  for (const [k, v] of Object.entries(route.query)) {
    if (v && !params.has(k)) params.set(k, String(Array.isArray(v) ? v[0] : v));
  }

  try {
    const res = await listKerisiArData(id, `?${params.toString()}`);
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

const totalPages = computed(() => (total.value > 0 ? Math.ceil(total.value / limit.value) : 1));

function prevPage() {
  if (page.value > 1) {
    page.value--;
    void loadRows();
  }
}
function nextPage() {
  if (page.value < totalPages.value) {
    page.value++;
    void loadRows();
  }
}
function onLimitChange() {
  page.value = 1;
  void loadRows();
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
        Invalid Account Receivable menu route.
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

        <!-- Legacy BL hint -->
        <p
          v-if="spec?.legacyBlName || spec?.legacyApiUrl"
          class="text-xs text-slate-400"
        >
          <span v-if="spec.legacyBlName">Legacy BL: {{ spec.legacyBlName }}</span>
          <span v-if="spec.legacyBlName && spec.legacyApiUrl"> · </span>
          <span v-if="spec.legacyApiUrl">Legacy API: {{ spec.legacyApiUrl }}</span>
        </p>

        <!-- Form sections BEFORE datatable (when componentId order says so) -->
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
              <button
                type="button"
                class="inline-flex items-center gap-1.5 rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-medium text-white hover:bg-slate-700"
              >
                <Plus class="h-3.5 w-3.5" />
                Add
              </button>
              <button
                type="button"
                class="inline-flex items-center gap-1 rounded-lg border border-slate-300 px-3 py-1.5 text-xs text-slate-600 hover:bg-slate-50"
              >
                <FileDown class="h-3.5 w-3.5" />
                PDF
              </button>
              <button
                type="button"
                class="inline-flex items-center gap-1 rounded-lg border border-slate-300 px-3 py-1.5 text-xs text-slate-600 hover:bg-slate-50"
              >
                <Download class="h-3.5 w-3.5" />
                CSV
              </button>
              <button
                type="button"
                class="inline-flex items-center gap-1 rounded-lg border border-slate-300 px-3 py-1.5 text-xs text-slate-600 hover:bg-slate-50"
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
              <div class="flex flex-wrap items-end justify-between gap-4">
                <div class="flex items-center gap-2">
                  <span class="text-xs font-medium text-slate-600">Display</span>
                  <select
                    v-model="limit"
                    class="rounded-lg border border-slate-300 px-2 py-1.5 text-sm"
                    @change="onLimitChange"
                  >
                    <option v-for="n in [10, 25, 50, 100]" :key="n" :value="n">{{ n }}</option>
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
                      @input="onSearch"
                      @keydown.enter.prevent="onSearch"
                    />
                    <button
                      v-if="q"
                      type="button"
                      class="absolute right-1 top-1/2 -translate-y-1/2 rounded p-0.5 text-slate-400 hover:bg-slate-100"
                      @click="clearSearch"
                    >
                      <X class="h-3.5 w-3.5" />
                    </button>
                  </div>
                  <button
                    v-if="showSmartFilterUi"
                    type="button"
                    class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm text-slate-600 hover:bg-slate-50"
                    @click="showSmartFilter = true"
                  >
                    <Filter class="h-4 w-4" />
                    Filter
                  </button>
                </div>
              </div>
            </template>

            <!-- Table -->
            <div class="overflow-x-auto">
              <table class="admin-table-kitchen w-full text-sm">
                <thead class="admin-table-thead-sticky">
                  <tr class="border-b border-slate-200 text-left">
                    <th
                      v-for="(h, hi) in dt.dtBi"
                      :key="hi"
                      class="whitespace-nowrap px-3 py-2 text-xs font-semibold uppercase text-slate-700"
                    >
                      <span v-if="!isActionCol(h) || di > 0">{{ h }}</span>
                    </th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-if="loading && di === 0">
                    <td :colspan="tableColspan(dt)" class="admin-table-kitchen-caption">
                      Loading…
                    </td>
                  </tr>
                  <tr v-else-if="(di === 0 ? rows : []).length === 0 && !loading">
                    <td :colspan="tableColspan(dt)" class="admin-table-kitchen-caption">
                      No records found.
                    </td>
                  </tr>
                  <template v-else>
                    <tr
                      v-for="(row, ri) in (di === 0 ? rows : [])"
                      :key="ri"
                      class="border-b border-slate-100 hover:bg-slate-50"
                    >
                      <td
                        v-for="(h, hi) in dt.dtBi"
                        :key="hi"
                        class="px-3 py-2"
                      >
                        <!-- Row number -->
                        <span v-if="isNoCol(h)">
                          {{ (page - 1) * limit + ri + 1 }}
                        </span>
                        <!-- Action column -->
                        <template v-else-if="isActionCol(h)">
                          <div class="flex items-center gap-1">
                            <button
                              type="button"
                              class="rounded p-1 text-slate-500 hover:text-blue-600"
                              title="View"
                            >
                              <Eye class="h-3.5 w-3.5" />
                            </button>
                            <button
                              type="button"
                              class="rounded p-1 text-slate-500 hover:text-amber-600"
                              title="Edit"
                            >
                              <Pencil class="h-3.5 w-3.5" />
                            </button>
                            <button
                              type="button"
                              class="rounded p-1 text-slate-500 hover:text-red-600"
                              title="Delete"
                            >
                              <Trash2 class="h-3.5 w-3.5" />
                            </button>
                          </div>
                        </template>
                        <!-- Data cell -->
                        <span v-else class="whitespace-nowrap">
                          {{ displayCell(row, dt, hi) }}
                        </span>
                      </td>
                    </tr>
                  </template>
                </tbody>
              </table>
            </div>

            <!-- Pagination (primary datatable only) -->
            <template v-if="di === 0">
              <div class="flex items-center justify-between text-sm text-slate-500">
                <span>Showing {{ total === 0 ? 0 : (page - 1) * limit + 1 }}–{{ Math.min(page * limit, total) }} of {{ total }}</span>
                <div class="flex items-center gap-2">
                  <button
                    type="button"
                    :disabled="page <= 1"
                    class="rounded-lg border border-slate-300 px-3 py-1 text-xs disabled:opacity-40"
                    @click="prevPage"
                  >
                    Prev
                  </button>
                  <span class="text-xs">Page {{ page }} / {{ totalPages }}</span>
                  <button
                    type="button"
                    :disabled="page >= totalPages"
                    class="rounded-lg border border-slate-300 px-3 py-1 text-xs disabled:opacity-40"
                    @click="nextPage"
                  >
                    Next
                  </button>
                </div>
              </div>
            </template>
          </div>
        </section>

        <!-- Form sections AFTER datatable (normal case) -->
        <template v-if="!formBeforeDataTable && formSectionGroups.length > 0">
          <article
            v-for="grp in formSectionGroups"
            :key="grp.title"
            class="rounded-lg border border-slate-200 bg-white shadow-sm"
          >
            <div class="border-b border-slate-100 px-4 py-3">
              <h2 class="text-base font-semibold text-slate-900">{{ grp.title }}</h2>
            </div>
            <div class="grid gap-3 p-4 sm:grid-cols-2">
              <div v-for="(f, fi) in grp.fields" :key="'fsa-' + fi">
                <label class="mb-1 block text-xs font-medium text-slate-600">{{ f.title }}</label>
                <input
                  disabled
                  :class="[
                    'w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-500',
                    f.cssClass?.includes('force-one-column') ? 'sm:col-span-2' : '',
                  ]"
                  :placeholder="f.fieldType"
                  value=""
                />
              </div>
            </div>
          </article>
        </template>

        <!-- Popup modal form placeholder (read-only until CRUD is wired) -->
        <Teleport to="body">
          <div
            v-if="false"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm"
          >
            <!-- Popup modal content goes here when CRUD is wired per menuId -->
          </div>
        </Teleport>

        <!-- Smart filter dialog (kitchen-sink pattern) -->
        <Teleport to="body">
          <div
            v-if="showSmartFilter && showSmartFilterUi"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm"
            @click.self="showSmartFilter = false"
          >
            <div class="w-full max-w-lg rounded-lg border border-slate-200 bg-white shadow-2xl">
              <div class="border-b border-slate-100 px-4 py-3">
                <h3 class="text-base font-semibold text-slate-900">Smart Filter</h3>
              </div>
              <div class="max-h-[60vh] overflow-y-auto space-y-3 p-4">
                <div
                  v-for="(f, i) in spec?.smartFilterFields ?? []"
                  :key="'sf-' + i"
                  class="space-y-1"
                >
                  <label class="text-sm font-medium text-slate-700">{{ f.title }}</label>
                  <input
                    v-if="f.fieldType === 'text' || f.fieldType === 'date' || !f.fieldType"
                    v-model="smartFilterValues[`sf_${i}`]"
                    :type="f.fieldType === 'date' ? 'date' : 'text'"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
                  />
                  <select
                    v-else-if="f.fieldType === 'dropdown'"
                    v-model="smartFilterValues[`sf_${i}`]"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
                  >
                    <option value="">Any</option>
                  </select>
                  <input
                    v-else
                    v-model="smartFilterValues[`sf_${i}`]"
                    type="text"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
                  />
                </div>
              </div>
              <div class="flex justify-end gap-2 border-t border-slate-100 px-4 py-3">
                <button
                  type="button"
                  class="rounded-lg border border-slate-300 px-4 py-2 text-sm"
                  @click="resetSmartFilter"
                >
                  Reset
                </button>
                <button
                  type="button"
                  class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white"
                  @click="applySmartFilter"
                >
                  OK
                </button>
              </div>
            </div>
          </div>
        </Teleport>

        <!-- Fallback: no spec in registry -->
        <div
          v-if="!spec"
          class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900"
        >
          This AR menu ({{ menuId }}) is not in the registry. Regenerate using
          <code class="font-mono text-xs">node scripts/gen-kerisi-ar-registry.mjs</code>.
        </div>
      </template>
    </div>
  </AdminLayout>
</template>
