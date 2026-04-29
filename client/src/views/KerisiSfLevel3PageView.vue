<script setup lang="ts">
/**
 * Student Finance Kerisi shell: uses Level-3 registry when available, otherwise
 * `resolveKerisiSfShellSpec` (synthetic grid from menu title or placeholder columns).
 * Rows: GET /api/student-finance/kerisi-level3/{menuId} (empty until per-menu BL is wired).
 */
import { computed, onMounted, onUnmounted, ref, watch } from "vue";
import { useRoute } from "vue-router";
import {
  Download,
  Eye,
  FileDown,
  FileSpreadsheet,
  Filter,
  MoreVertical,
  Pencil,
  Search,
  Trash2,
  X,
} from "lucide-vue-next";
import AdminLayout from "@/layouts/AdminLayout.vue";
import { listKerisiSfLevel3Data } from "@/api/cms";
import {
  getKerisiMenuTrailByMenuId,
  parseKerisiNumericMenuIdFromPath,
} from "@/config/kerisi-menu-resolve";
import type { KerisiSfLevel3Datatable, KerisiSfLevel3PageSpec } from "@/config/kerisi-sf-level3-registry.generated";
import { resolveKerisiSfShellSpec } from "@/config/kerisi-sf-level3-shell";
import { useToast } from "@/composables/useToast";

const toast = useToast();
const route = useRoute();

const menuId = computed(() => parseKerisiNumericMenuIdFromPath(route.path));

const shellResolved = computed(() => {
  const id = menuId.value;
  if (id === null) return null;
  return resolveKerisiSfShellSpec(id);
});

const spec = computed<KerisiSfLevel3PageSpec | null>(() => shellResolved.value?.spec ?? null);

const shellHint = computed(() => shellResolved.value?.hint ?? null);

const pageHeading = computed(() => {
  const trail =
    menuId.value !== null ? getKerisiMenuTrailByMenuId(menuId.value) : null;
  if (trail?.length) return trail.join(" / ");
  return spec.value?.pageTitle ?? "Student Finance";
});

function cellKey(dt: KerisiSfLevel3Datatable, colIdx: number): string {
  const raw = dt.dtKey[colIdx] ?? "";
  if (raw.trim()) return raw.trim();
  const lab = dt.dtBi[colIdx] ?? `col_${colIdx}`;
  return lab.replace(/\s+/g, "_").toLowerCase();
}

function labelWithoutNoAction(dt: KerisiSfLevel3Datatable): string[] {
  return dt.dtBi.filter((h) => {
    const t = String(h).trim().toLowerCase();
    return t !== "no" && t !== "no." && t !== "action";
  });
}

function displayCell(row: Record<string, unknown>, dt: KerisiSfLevel3Datatable, colIdx: number): string {
  const key = cellKey(dt, colIdx);
  const v = row[key];
  if (v !== undefined && v !== null) return String(v);
  // Legacy dt_key uses PascalCase; Laravel APIs emit snake_case → CamelCaseMiddleware → camelCase (e.g. invoice_no → invoiceNo).
  if (/^[A-Z][a-zA-Z0-9]*$/.test(key)) {
    const camelFromPascal = key.charAt(0).toLowerCase() + key.slice(1);
    const vp = row[camelFromPascal];
    if (vp !== undefined && vp !== null) return String(vp);
  }
  const camel = key.replace(/_([a-z])/g, (_, c: string) => c.toUpperCase());
  const v2 = row[camel];
  if (v2 !== undefined && v2 !== null) return String(v2);
  // Lowercase / compact dt_key (e.g. "icpassport") vs API camelCase ("icPassport")
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

const rows = ref<Record<string, unknown>[]>([]);
const loading = ref(false);
const total = ref(0);
const page = ref(1);
const limit = ref(10);
const q = ref("");

const smartFilterValues = ref<Record<string, string>>({});
const topFilterValues = ref<Record<string, string>>({});

const showSmartFilter = ref(false);

const primaryDt = computed(() => spec.value?.datatables?.[0] ?? null);

const totalPages = computed(() =>
  total.value ? Math.max(1, Math.ceil(total.value / limit.value)) : 1,
);
const startIdx = computed(() =>
  total.value === 0 ? 0 : (page.value - 1) * limit.value + 1,
);
const endIdx = computed(() => Math.min(page.value * limit.value, total.value));

function initFilters() {
  const s = spec.value;
  if (!s) return;
  const sf: Record<string, string> = {};
  s.smartFilterFields.forEach((_, i) => {
    sf[`sf_${i}`] = "";
  });
  smartFilterValues.value = sf;
  const tf: Record<string, string> = {};
  s.topFilterFields.forEach((_, i) => {
    tf[`tf_${i}`] = "";
  });
  topFilterValues.value = tf;
}

function queryParamSingle(v: unknown): string {
  if (v === undefined || v === null) return "";
  if (Array.isArray(v)) return String(v[0] ?? "").trim();
  return String(v).trim();
}

async function loadRows() {
  const id = menuId.value;
  if (id === null) return;
  loading.value = true;
  const params = new URLSearchParams({
    page: String(page.value),
    limit: String(limit.value),
  });
  const trxFromRoute =
    queryParamSingle(route.query.trx_id) || queryParamSingle(route.query.trxId);
  if (trxFromRoute) params.set("trx_id", trxFromRoute);
  if (q.value.trim()) params.set("q", q.value.trim());
  Object.entries(smartFilterValues.value).forEach(([k, v]) => {
    if (v.trim()) params.set(k, v.trim());
  });
  Object.entries(topFilterValues.value).forEach(([k, v]) => {
    if (v.trim()) params.set(k, v.trim());
  });
  try {
    const res = await listKerisiSfLevel3Data(id, `?${params.toString()}`);
    rows.value = Array.isArray(res.data) ? res.data : [];
    total.value = Number(res.meta?.total ?? 0);
    const m = res.meta as Record<string, unknown> | undefined;
    if (m && m.shellError && typeof m.shellError === "string") {
      toast.error("List source", m.shellError);
    }
  } catch (e) {
    toast.error(
      "Load failed",
      e instanceof Error ? e.message : "Unable to load list.",
    );
    rows.value = [];
    total.value = 0;
  } finally {
    loading.value = false;
  }
}

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

function applySmartFilter() {
  page.value = 1;
  showSmartFilter.value = false;
  void loadRows();
}

function resetSmartFilter() {
  initFilters();
}

const exportColumns = computed(() => {
  const dt = primaryDt.value;
  if (!dt) return ["Record"];
  return labelWithoutNoAction(dt);
});

const pageExportTitle = computed(() => pageHeading.value);

function buildExportRows(): Record<string, unknown>[] {
  const dt = primaryDt.value;
  if (!dt) return [];
  return rows.value.map((row) => {
    const out: Record<string, unknown> = {};
    labelWithoutNoAction(dt).forEach((lab) => {
      const idx = dt.dtBi.indexOf(lab);
      out[lab] = idx >= 0 ? displayCell(row, dt, idx) : "";
    });
    return out;
  });
}

function escapeCsvField(field: unknown): string {
  if (field === null || field === undefined) return "";
  const str = String(field);
  return str.includes(",") || str.includes('"') || str.includes("\n")
    ? `"${str.replace(/"/g, '""')}"`
    : str;
}

async function handleDownloadPDF() {
  try {
    const dataToExport = buildExportRows();
    if (dataToExport.length === 0) {
      toast.info("No data", "There is nothing to export.");
      return;
    }
    const cols = exportColumns.value;
    const { default: jsPDF } = await import("jspdf");
    const autoTable = (await import("jspdf-autotable")).default;
    const doc = new jsPDF({ orientation: "portrait", unit: "mm", format: "a4" });
    const pageWidth = doc.internal.pageSize.getWidth();
    const margin = 10;
    const now = new Date();
    const formattedDateTime = `Date : ${String(now.getDate()).padStart(2, "0")}/${String(now.getMonth() + 1).padStart(2, "0")}/${now.getFullYear()} ${String(now.getHours() % 12 || 12).padStart(2, "0")}:${String(now.getMinutes()).padStart(2, "0")}:${String(now.getSeconds()).padStart(2, "0")} ${now.getHours() >= 12 ? "PM" : "AM"}`;
    doc.setFontSize(10);
    doc.text(formattedDateTime, pageWidth - margin - doc.getTextWidth(formattedDateTime), margin + 8);
    doc.setFontSize(16);
    doc.setFont("helvetica", "bold");
    const title = pageExportTitle.value;
    doc.text(title, (pageWidth - doc.getTextWidth(title)) / 2, margin + 10);
    const tableData = dataToExport.map((item, index) => {
      const row: unknown[] = [(index + 1).toString()];
      cols.forEach((col) => {
        row.push(String(item[col] ?? ""));
      });
      return row;
    });
    autoTable(doc, {
      head: [["No.", ...cols]],
      body: tableData as import("jspdf-autotable").RowInput[],
      startY: margin + 18,
      margin: { left: margin, right: margin },
      styles: { fontSize: 9, cellPadding: 2 },
      headStyles: {
        fillColor: [59, 130, 246],
        textColor: [255, 255, 255],
        fontStyle: "bold",
        halign: "center",
      },
      columnStyles: { 0: { halign: "center", cellWidth: 15 } },
    });
    doc.save(`${title.replace(/\s+/g, "_")}_${new Date().toISOString().split("T")[0]}.pdf`);
    toast.success("PDF downloaded");
  } catch (error) {
    console.error("PDF export error:", error);
    toast.error("Export failed", "Could not generate PDF.");
  }
}

function handleDownloadCSV() {
  try {
    const dataToExport = buildExportRows();
    if (dataToExport.length === 0) {
      toast.info("No data", "There is nothing to export.");
      return;
    }
    const cols = exportColumns.value;
    const now = new Date();
    const formattedDateTime = `Date : ${String(now.getDate()).padStart(2, "0")}/${String(now.getMonth() + 1).padStart(2, "0")}/${now.getFullYear()} ${String(now.getHours() % 12 || 12).padStart(2, "0")}:${String(now.getMinutes()).padStart(2, "0")}:${String(now.getSeconds()).padStart(2, "0")} ${now.getHours() >= 12 ? "PM" : "AM"}`;
    let csvContent =
      escapeCsvField(formattedDateTime) + "\n" + escapeCsvField(pageExportTitle.value) + "\n";
    if (q.value.trim()) csvContent += escapeCsvField(`Search: ${q.value.trim()}`) + "\n";
    const sf = { ...smartFilterValues.value, ...topFilterValues.value };
    Object.keys(sf).forEach((key) => {
      const v = sf[key];
      if (v !== undefined && v !== null && String(v).trim()) {
        csvContent += escapeCsvField(`${key}: ${v}`) + "\n";
      }
    });
    if (
      Object.keys(sf).some((k) => {
        const x = sf[k];
        return x !== undefined && x !== null && String(x).trim();
      }) ||
      q.value.trim()
    ) {
      csvContent += "\n";
    }
    csvContent += ["No.", ...cols].map(escapeCsvField).join(",") + "\n";
    dataToExport.forEach((item, index) => {
      const row: string[] = [(index + 1).toString()];
      cols.forEach((col) => {
        row.push(String(item[col] ?? ""));
      });
      csvContent += row.map(escapeCsvField).join(",") + "\n";
    });
    const blob = new Blob([csvContent], { type: "text/csv;charset=utf-8;" });
    const link = document.createElement("a");
    link.href = URL.createObjectURL(blob);
    link.download = `${pageExportTitle.value.replace(/\s+/g, "_")}_${new Date().toISOString().split("T")[0]}.csv`;
    link.style.visibility = "hidden";
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(link.href);
    toast.success("CSV downloaded");
  } catch (error) {
    console.error("CSV export error:", error);
    toast.error("Export failed", "Could not generate CSV.");
  }
}

async function exportExcel() {
  try {
    if (rows.value.length === 0) {
      toast.info("No data", "There is nothing to export.");
      return;
    }
    const ExcelJS = await import("exceljs");
    const wb = new ExcelJS.Workbook();
    const ws = wb.addWorksheet("Data");
    const cols = exportColumns.value;
    ws.addRow(["No", ...cols]);
    const dt = primaryDt.value;
    rows.value.forEach((row, idx) => {
      const cells = cols.map((lab) => {
        if (!dt) return "";
        const cIdx = dt.dtBi.indexOf(lab);
        return cIdx >= 0 ? displayCell(row, dt, cIdx) : "";
      });
      ws.addRow([idx + 1, ...cells]);
    });
    const buf = await wb.xlsx.writeBuffer();
    const blob = new Blob([buf], {
      type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
    });
    const url = URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.href = url;
    a.download = `KerisiSF_${menuId.value ?? "export"}_${new Date().toISOString().slice(0, 10)}.xlsx`;
    a.click();
    URL.revokeObjectURL(url);
    toast.success("Excel downloaded");
  } catch (e) {
    toast.error(
      "Export failed",
      e instanceof Error ? e.message : "Excel export failed.",
    );
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

watch(menuId, () => {
  initFilters();
  page.value = 1;
  void loadRows();
});

watch(
  () => route.query.trx_id ?? route.query.trxId,
  () => {
    page.value = 1;
    void loadRows();
  },
);

onMounted(() => {
  initFilters();
  void loadRows();
});

onUnmounted(() => {
  if (searchDebounce) clearTimeout(searchDebounce);
});

function showTopFilterRow(): boolean {
  const dt = primaryDt.value;
  return (
    (spec.value?.topFilterFields?.length ?? 0) > 0 ||
    dt?.dtFilter === "top"
  );
}

function showSmartFilterUi(): boolean {
  const dt = primaryDt.value;
  return (
    (spec.value?.smartFilterFields?.length ?? 0) > 0 ||
    dt?.dtFilter === "smart"
  );
}

function tableColspan(dt: KerisiSfLevel3Datatable): number {
  return dt.dtBi.length;
}

/**
 * True when the form section(s) should render BEFORE the datatable — detected by
 * comparing the first componentId of formSections vs datatables. MENUID 1339
 * (Import Data Insurance) has form componentId 3444 < datatable componentId 3445,
 * so the "Import Data Insurance" card must appear above the "List of Student" grid,
 * matching the legacy page layout.
 */
const formBeforeDataTable = computed(() => {
  const s = spec.value;
  if (!s || s.formSections.length === 0) return false;
  const firstFormId = s.formSections[0]?.componentId ?? Infinity;
  const firstDtId = s.datatables[0]?.componentId ?? Infinity;
  return firstFormId < firstDtId;
});

/** Deduplicated list of componentTitle values used to build form-section cards. */
const formSectionGroups = computed(() => {
  const s = spec.value;
  if (!s) return [] as { title: string; fields: KerisiSfLevel3PageSpec["formSections"] }[];
  const seen = new Map<string, typeof s.formSections[number][]>();
  for (const f of s.formSections) {
    const key = f.componentTitle ?? "Details";
    if (!seen.has(key)) seen.set(key, []);
    seen.get(key)!.push(f);
  }
  return [...seen.entries()].map(([title, fields]) => ({ title, fields }));
});
</script>

<template>
  <AdminLayout>
    <div class="space-y-4">
      <div
        v-if="menuId === null"
        class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900"
      >
        Invalid Kerisi menu route.
      </div>

      <template v-else-if="spec">
        <h1 class="page-title">{{ pageHeading }}</h1>

        <p
          v-if="shellHint"
          class="rounded-lg border border-sky-200 bg-sky-50 px-3 py-2 text-xs text-sky-950"
        >
          {{ shellHint }}
        </p>

        <p
          v-if="spec.legacyBlName || spec.legacyApiUrl"
          class="text-xs text-slate-500"
        >
          <span v-if="spec.legacyBlName">Legacy BL: {{ spec.legacyBlName }}</span>
          <span v-if="spec.legacyBlName && spec.legacyApiUrl"> · </span>
          <span v-if="spec.legacyApiUrl">Legacy API: {{ spec.legacyApiUrl }}</span>
        </p>

        <!-- Form section cards rendered BEFORE the datatable when the registry
             shows a lower componentId for the form (e.g. MENUID 1339 Import Data
             Insurance: form 3444 precedes grid 3445 in the legacy UI). -->
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
                  class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-600"
                  :placeholder="f.fieldType"
                  value=""
                />
              </div>
            </div>
          </article>
        </template>

        <section
          v-for="(dt, di) in spec.datatables"
          :key="dt.componentId + '-' + dt.componentTitle + '-' + di"
          class="rounded-lg border border-slate-200 bg-white shadow-sm"
        >
          <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
            <h2 class="text-base font-semibold text-slate-900">
              {{ dt.componentTitle || "Data" }}
            </h2>
            <button
              type="button"
              class="rounded-lg p-2 text-slate-500 hover:bg-slate-100"
              aria-label="More"
            >
              <MoreVertical class="h-4 w-4" />
            </button>
          </div>

          <div class="space-y-4 p-4">
            <template v-if="di === 0">
              <!-- Top filter (kitchen-sink pattern: filters above grid) -->
              <div
                v-if="showTopFilterRow()"
                class="flex flex-wrap items-end gap-3 rounded-lg border border-slate-100 bg-slate-50/80 p-3"
              >
                <template v-for="(f, i) in spec.topFilterFields" :key="'tf-' + i">
                  <div class="min-w-[140px]">
                    <label class="mb-1 block text-xs font-medium text-slate-600">{{ f.title }}</label>
                    <input
                      v-if="f.fieldType === 'text' || f.fieldType === 'date' || !f.fieldType"
                      v-model="topFilterValues[`tf_${i}`]"
                      :type="f.fieldType === 'date' ? 'date' : 'text'"
                      class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-sm"
                    />
                    <select
                      v-else-if="f.fieldType === 'dropdown'"
                      v-model="topFilterValues[`tf_${i}`]"
                      class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-sm"
                    >
                      <option value="">Any</option>
                    </select>
                  </div>
                </template>
                <button
                  type="button"
                  class="rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-medium text-white"
                  @click="
                    page = 1;
                    loadRows();
                  "
                >
                  Apply
                </button>
              </div>

              <div class="flex flex-wrap items-end justify-between gap-4">
                <div class="flex items-center gap-2">
                  <label class="text-xs font-medium text-slate-600">Display</label>
                  <select
                    v-model.number="limit"
                    class="rounded-lg border border-slate-300 px-2 py-1.5 text-sm"
                    @change="
                      page = 1;
                      loadRows();
                    "
                  >
                    <option v-for="n in [5, 10, 25, 50, 100]" :key="n" :value="n">{{ n }}</option>
                  </select>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                  <label class="text-xs font-medium text-slate-600">Search</label>
                  <div class="relative">
                    <Search
                      class="pointer-events-none absolute left-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400"
                    />
                    <input
                      v-model="q"
                      type="search"
                      placeholder="Filter rows..."
                      class="w-56 rounded-lg border border-slate-300 py-1.5 pl-8 pr-8 text-sm"
                      @keyup.enter="
                        page = 1;
                        loadRows();
                      "
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
                  <button
                    v-if="showSmartFilterUi()"
                    type="button"
                    class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-medium shadow-sm hover:bg-slate-50"
                    @click="showSmartFilter = true"
                  >
                    <Filter class="h-4 w-4" />
                    Filter
                  </button>
                </div>
              </div>

              <div class="overflow-x-auto rounded-lg border border-slate-200">
                <div :class="rows.length > 10 ? 'max-h-[420px] overflow-y-auto' : ''">
                  <table class="w-full min-w-[720px] text-sm">
                    <thead class="sticky top-0 bg-slate-50">
                      <tr class="border-b border-slate-200 text-left">
                        <th
                          v-for="(h, hi) in dt.dtBi"
                          :key="hi"
                          class="whitespace-nowrap px-3 py-2 text-xs font-semibold uppercase text-slate-700"
                        >
                          {{ h }}
                        </th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-if="loading">
                        <td :colspan="tableColspan(dt)" class="px-3 py-6 text-center text-slate-500">
                          Loading...
                        </td>
                      </tr>
                      <tr v-else-if="rows.length === 0">
                        <td :colspan="tableColspan(dt)" class="px-3 py-6 text-center text-slate-500">
                          No records found.
                        </td>
                      </tr>
                      <template v-else>
                        <tr
                          v-for="(row, ri) in rows"
                          :key="ri"
                          class="border-b border-slate-100 hover:bg-slate-50"
                        >
                          <td
                            v-for="(_h, ci) in dt.dtBi"
                            :key="ci"
                            class="px-3 py-2 text-slate-800"
                          >
                            <template v-if="String(dt.dtBi[ci]).trim().toLowerCase() === 'no'">
                              {{ startIdx + ri }}
                            </template>
                            <template
                              v-else-if="String(dt.dtBi[ci]).trim().toLowerCase() === 'action'"
                            >
                              <span class="inline-flex gap-1 text-slate-400" title="Workflow actions pending">
                                <Eye class="h-4 w-4 shrink-0" aria-hidden="true" />
                                <Pencil class="h-4 w-4 shrink-0" aria-hidden="true" />
                                <Trash2 class="h-4 w-4 shrink-0" aria-hidden="true" />
                              </span>
                            </template>
                            <template v-else>
                              {{ displayCell(row, dt, ci) }}
                            </template>
                          </td>
                        </tr>
                      </template>
                    </tbody>
                  </table>
                </div>
              </div>

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
                    class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium"
                    @click="handleDownloadPDF"
                  >
                    <Download class="h-3.5 w-3.5" />PDF
                  </button>
                  <button
                    type="button"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium"
                    @click="handleDownloadCSV"
                  >
                    <FileDown class="h-3.5 w-3.5" />CSV
                  </button>
                  <button
                    type="button"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium"
                    @click="exportExcel"
                  >
                    <FileSpreadsheet class="h-3.5 w-3.5" />Excel
                  </button>
                </div>
              </div>
            </template>

            <template v-else>
              <p class="text-xs text-slate-500">
                Additional datagrid from legacy JSON — list data for this grid is not wired in the registry shell yet.
              </p>
              <div class="overflow-x-auto rounded-lg border border-dashed border-slate-200 bg-slate-50/50">
                <table class="w-full min-w-[480px] text-sm">
                  <thead class="bg-slate-100">
                    <tr class="border-b border-slate-200 text-left">
                      <th
                        v-for="(h, hi) in dt.dtBi"
                        :key="hi"
                        class="whitespace-nowrap px-3 py-2 text-xs font-semibold uppercase text-slate-700"
                      >
                        {{ h }}
                      </th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td :colspan="tableColspan(dt)" class="px-3 py-6 text-center text-slate-500">
                        No connector for this grid (shell preview).
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </template>
          </div>
        </section>

        <!-- Form section cards rendered AFTER the datatable when componentId order
             places them after (the normal case for supplemental detail panels). -->
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
                  class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-600"
                  :placeholder="f.fieldType"
                  value=""
                />
              </div>
            </div>
          </article>
        </template>

        <Teleport to="body">
          <div
            v-if="showSmartFilter && showSmartFilterUi()"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm"
            @click.self="showSmartFilter = false"
          >
            <div class="w-full max-w-lg rounded-lg border border-slate-200 bg-white shadow-2xl">
              <div class="border-b border-slate-100 px-4 py-3">
                <h3 class="text-base font-semibold text-slate-900">Smart filter</h3>
              </div>
              <div class="space-y-3 p-4">
                <div v-for="(f, i) in spec.smartFilterFields" :key="'sf-' + i" class="space-y-1">
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
      </template>
    </div>
  </AdminLayout>
</template>
