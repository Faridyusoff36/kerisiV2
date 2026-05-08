<script setup lang="ts">
/**
 * Purchasing / Setup / Item Main (menu 1820).
 * Cascading grids per legacy PAGE 1499. Detail modal mirrors Activity Code (read-only).
 */
import { computed, onMounted, onUnmounted, ref } from "vue";
import { useRoute } from "vue-router";
import { Download, Eye, FileDown, FileSpreadsheet, Pencil, Plus, Search, X } from "lucide-vue-next";
import AdminLayout from "@/layouts/AdminLayout.vue";
import {
  purchasingItemMainGroups,
  purchasingItemMainMainCategories,
  purchasingItemMainSubcategories,
  purchasingItemMainSubsiri,
  purchasingItemMainItemLines,
  type PurchasingItemMainGroupOpt,
} from "@/api/cms";
import { getKerisiMenuTrailByMenuId } from "@/config/kerisi-menu-resolve";
import { useToast } from "@/composables/useToast";

const toast = useToast();
const route = useRoute();

const kerisiMenuId = computed(() => {
  const m = (route.meta as { kerisiMenuId?: number }).kerisiMenuId;
  return typeof m === "number" && !Number.isNaN(m) ? m : 1820;
});

const pageHeading = computed(() => {
  const trail = getKerisiMenuTrailByMenuId(kerisiMenuId.value);
  if (trail?.length) return trail.join(" / ");
  if (kerisiMenuId.value === 2293) return "Asset / Setup / General / Item Main";
  if (kerisiMenuId.value === 1563) return "Asset / Setup / General / Item Listing";
  return "Purchasing / Setup / Item Main";
});

const groupOptions = ref<PurchasingItemMainGroupOpt[]>([]);
const grouplookup = ref("");

const mainRows = ref<Record<string, unknown>[]>([]);
const mainTotal = ref(0);
const mainPage = ref(1);
const mainLimit = ref(5);
const mainQ = ref("");
const mainLoading = ref(false);

const subRows = ref<Record<string, unknown>[]>([]);
const subTotal = ref(0);
const subPage = ref(1);
const subLimit = ref(5);
const subQ = ref("");
const subLoading = ref(false);

const ssiRows = ref<Record<string, unknown>[]>([]);
const ssiTotal = ref(0);
const ssiPage = ref(1);
const ssiLimit = ref(5);
const ssiQ = ref("");
const ssiLoading = ref(false);

const lineRows = ref<Record<string, unknown>[]>([]);
const lineTotal = ref(0);
const linePage = ref(1);
const lineLimit = ref(5);
const lineQ = ref("");
const lineLoading = ref(false);

const selectedMain = ref<Record<string, unknown> | null>(null);
const selectedSub = ref<Record<string, unknown> | null>(null);
const selectedSsi = ref<Record<string, unknown> | null>(null);

let debMain: ReturnType<typeof setTimeout> | null = null;
let debSub: ReturnType<typeof setTimeout> | null = null;
let debSsi: ReturnType<typeof setTimeout> | null = null;
let debLine: ReturnType<typeof setTimeout> | null = null;

function mainCode(r: Record<string, unknown>): string {
  return String(r.ldeValue ?? r.lde_value ?? "");
}
function subCode(r: Record<string, unknown>): string {
  return String(r.iscSubcategoryCode ?? r.isc_subcategory_code ?? "");
}
function ssiCode(r: Record<string, unknown>): string {
  return String(r.issSubsiriCode ?? r.iss_subsiri_code ?? "");
}

function applyQueryGroupFromRoute(): void {
  const gRaw = route.query.grouplookup ?? route.query.group;
  const g = typeof gRaw === "string" && gRaw.trim() ? gRaw.trim() : "";
  if (!g || !groupOptions.value.some((o) => o.value === g)) return;
  grouplookup.value = g;
}

async function loadGroups(): Promise<void> {
  try {
    const res = await purchasingItemMainGroups();
    groupOptions.value = Array.isArray(res.data) ? res.data : [];
  } catch (e) {
    toast.error("Load failed", e instanceof Error ? e.message : "Groups unavailable.");
    groupOptions.value = [];
  }
}

function buildParams(page: number, limit: number, q: string, extra: Record<string, string>): string {
  const p = new URLSearchParams({
    page: String(page),
    limit: String(limit),
  });
  if (q.trim()) p.set("q", q.trim());
  Object.entries(extra).forEach(([k, v]) => {
    if (v !== "") p.set(k, v);
  });
  return `?${p.toString()}`;
}

async function fetchMain(reset = false) {
  if (reset) mainPage.value = 1;
  if (!grouplookup.value.trim()) {
    mainRows.value = [];
    mainTotal.value = 0;
    return;
  }
  mainLoading.value = true;
  try {
    const qp = buildParams(mainPage.value, mainLimit.value, mainQ.value, {
      grouplookup: grouplookup.value.trim(),
    });
    const res = await purchasingItemMainMainCategories(qp);
    mainRows.value = Array.isArray(res.data) ? (res.data as Record<string, unknown>[]) : [];
    mainTotal.value = Number(res.meta?.total ?? 0);
  } catch (e) {
    toast.error("Main category", e instanceof Error ? e.message : "Unable to load.");
    mainRows.value = [];
    mainTotal.value = 0;
  } finally {
    mainLoading.value = false;
  }
}

async function fetchSub(reset = false) {
  if (reset) subPage.value = 1;
  const cat = selectedMain.value ? mainCode(selectedMain.value) : "";
  if (!cat) {
    subRows.value = [];
    subTotal.value = 0;
    return;
  }
  subLoading.value = true;
  try {
    const qp = buildParams(subPage.value, subLimit.value, subQ.value, { category_code: cat });
    const res = await purchasingItemMainSubcategories(qp);
    subRows.value = Array.isArray(res.data) ? (res.data as Record<string, unknown>[]) : [];
    subTotal.value = Number(res.meta?.total ?? 0);
  } catch (e) {
    toast.error("Subcategory", e instanceof Error ? e.message : "Unable to load.");
    subRows.value = [];
    subTotal.value = 0;
  } finally {
    subLoading.value = false;
  }
}

async function fetchSsi(reset = false) {
  if (reset) ssiPage.value = 1;
  const cat = selectedMain.value ? mainCode(selectedMain.value) : "";
  const sc = selectedSub.value ? subCode(selectedSub.value) : "";
  if (!cat || !sc) {
    ssiRows.value = [];
    ssiTotal.value = 0;
    return;
  }
  ssiLoading.value = true;
  try {
    const qp = buildParams(ssiPage.value, ssiLimit.value, ssiQ.value, {
      category_code: cat,
      subcategory_code: sc,
    });
    const res = await purchasingItemMainSubsiri(qp);
    ssiRows.value = Array.isArray(res.data) ? (res.data as Record<string, unknown>[]) : [];
    ssiTotal.value = Number(res.meta?.total ?? 0);
  } catch (e) {
    toast.error("Subsiri", e instanceof Error ? e.message : "Unable to load.");
    ssiRows.value = [];
    ssiTotal.value = 0;
  } finally {
    ssiLoading.value = false;
  }
}

async function fetchLines(reset = false) {
  if (reset) linePage.value = 1;
  const cat = selectedMain.value ? mainCode(selectedMain.value) : "";
  const sc = selectedSub.value ? subCode(selectedSub.value) : "";
  const ss = selectedSsi.value ? ssiCode(selectedSsi.value) : "";
  if (!cat || !sc || !ss) {
    lineRows.value = [];
    lineTotal.value = 0;
    return;
  }
  lineLoading.value = true;
  try {
    const qp = buildParams(linePage.value, lineLimit.value, lineQ.value, {
      category_code: cat,
      subcategory_code: sc,
      subsiri_code: ss,
    });
    const res = await purchasingItemMainItemLines(qp);
    lineRows.value = Array.isArray(res.data) ? (res.data as Record<string, unknown>[]) : [];
    lineTotal.value = Number(res.meta?.total ?? 0);
  } catch (e) {
    toast.error("Item main", e instanceof Error ? e.message : "Unable to load.");
    lineRows.value = [];
    lineTotal.value = 0;
  } finally {
    lineLoading.value = false;
  }
}

async function applyGroupSearch() {
  selectedMain.value = null;
  selectedSub.value = null;
  selectedSsi.value = null;
  await fetchMain(true);
  if (mainRows.value.length > 0) {
    selectedMain.value = mainRows.value[0] as Record<string, unknown>;
  }
  await fetchSub(true);
  if (subRows.value.length > 0) {
    selectedSub.value = subRows.value[0] as Record<string, unknown>;
  }
  await fetchSsi(true);
  if (ssiRows.value.length > 0) {
    selectedSsi.value = ssiRows.value[0] as Record<string, unknown>;
  }
  await fetchLines(true);
}

function toggleMain(row: Record<string, unknown>) {
  if (selectedMain.value && mainCode(selectedMain.value) === mainCode(row)) {
    selectedMain.value = null;
  } else {
    selectedMain.value = row;
  }
  selectedSub.value = null;
  selectedSsi.value = null;
  void fetchSub(true);
  void fetchSsi(true);
  void fetchLines(true);
}

function toggleSub(row: Record<string, unknown>) {
  if (selectedSub.value && subCode(selectedSub.value) === subCode(row)) {
    selectedSub.value = null;
  } else {
    selectedSub.value = row;
  }
  selectedSsi.value = null;
  void fetchSsi(true);
  void fetchLines(true);
}

function toggleSsi(row: Record<string, unknown>) {
  if (selectedSsi.value && ssiCode(selectedSsi.value) === ssiCode(row)) {
    selectedSsi.value = null;
  } else {
    selectedSsi.value = row;
  }
  void fetchLines(true);
}

function debounce(
  slot: "main" | "sub" | "ssi" | "line",
  fn: () => void,
) {
  const run = () => {
    if (slot === "main") {
      debMain = null;
    } else if (slot === "sub") {
      debSub = null;
    } else if (slot === "ssi") {
      debSsi = null;
    } else {
      debLine = null;
    }
    fn();
  };
  if (slot === "main") {
    if (debMain) clearTimeout(debMain);
    debMain = setTimeout(run, 350);
    return;
  }
  if (slot === "sub") {
    if (debSub) clearTimeout(debSub);
    debSub = setTimeout(run, 350);
    return;
  }
  if (slot === "ssi") {
    if (debSsi) clearTimeout(debSsi);
    debSsi = setTimeout(run, 350);
    return;
  }
  if (debLine) clearTimeout(debLine);
  debLine = setTimeout(run, 350);
}

function flushMain() {
  mainPage.value = 1;
  void fetchMain();
}
function flushSub() {
  subPage.value = 1;
  void fetchSub();
}
function flushSsi() {
  ssiPage.value = 1;
  void fetchSsi();
}
function flushLine() {
  linePage.value = 1;
  void fetchLines();
}

function onMainKey(e: KeyboardEvent) {
  if (e.key === "Enter") flushMain();
}
function onSubKey(e: KeyboardEvent) {
  if (e.key === "Enter") flushSub();
}
function onSsiKey(e: KeyboardEvent) {
  if (e.key === "Enter") flushSsi();
}
function onLineKey(e: KeyboardEvent) {
  if (e.key === "Enter") flushLine();
}

function clearMainQ() {
  mainQ.value = "";
  flushMain();
}
function clearSubQ() {
  subQ.value = "";
  flushSub();
}
function clearSsiQ() {
  ssiQ.value = "";
  flushSsi();
}
function clearLineQ() {
  lineQ.value = "";
  flushLine();
}

const mainPages = computed(() =>
  Math.max(1, Math.ceil(mainTotal.value / Math.max(1, mainLimit.value))),
);
const subPages = computed(() =>
  Math.max(1, Math.ceil(subTotal.value / Math.max(1, subLimit.value))),
);
const ssiPages = computed(() =>
  Math.max(1, Math.ceil(ssiTotal.value / Math.max(1, ssiLimit.value))),
);
const linePages = computed(() =>
  Math.max(1, Math.ceil(lineTotal.value / Math.max(1, lineLimit.value))),
);

const mainStartIdx = computed(() => (mainTotal.value === 0 ? 0 : (mainPage.value - 1) * mainLimit.value + 1));
const mainEndIdx = computed(() => Math.min(mainPage.value * mainLimit.value, mainTotal.value));
const subStartIdx = computed(() => (subTotal.value === 0 ? 0 : (subPage.value - 1) * subLimit.value + 1));
const subEndIdx = computed(() => Math.min(subPage.value * subLimit.value, subTotal.value));
const ssiStartIdx = computed(() => (ssiTotal.value === 0 ? 0 : (ssiPage.value - 1) * ssiLimit.value + 1));
const ssiEndIdx = computed(() => Math.min(ssiPage.value * ssiLimit.value, ssiTotal.value));
const lineStartIdx = computed(() => (lineTotal.value === 0 ? 0 : (linePage.value - 1) * lineLimit.value + 1));
const lineEndIdx = computed(() => Math.min(linePage.value * lineLimit.value, lineTotal.value));

function mainPrev() {
  if (mainPage.value > 1) {
    mainPage.value -= 1;
    void fetchMain();
  }
}
function mainNext() {
  if (mainPage.value < mainPages.value) {
    mainPage.value += 1;
    void fetchMain();
  }
}
function subPrev() {
  if (subPage.value > 1) {
    subPage.value -= 1;
    void fetchSub();
  }
}
function subNext() {
  if (subPage.value < subPages.value) {
    subPage.value += 1;
    void fetchSub();
  }
}
function ssiPrev() {
  if (ssiPage.value > 1) {
    ssiPage.value -= 1;
    void fetchSsi();
  }
}
function ssiNext() {
  if (ssiPage.value < ssiPages.value) {
    ssiPage.value += 1;
    void fetchSsi();
  }
}
function linePrev() {
  if (linePage.value > 1) {
    linePage.value -= 1;
    void fetchLines();
  }
}
function lineNext() {
  if (linePage.value < linePages.value) {
    linePage.value += 1;
    void fetchLines();
  }
}

function toastAdd(section: string) {
  toast.info(
    "Not available here",
    `Adding "${section}" from this screen is not implemented in Kerisi 2.0 yet. Use the legacy process if your site supports it.`,
  );
}

function csvEscapeField(field: unknown): string {
  if (field === null || field === undefined) return "";
  const str = String(field);
  return str.includes(",") || str.includes('"') || str.includes("\n") ? `"${str.replace(/"/g, '""')}"` : str;
}

function exportDateSlug(): string {
  return new Date().toISOString().slice(0, 10);
}

function downloadCsvBlob(filename: string, lines: string[]): void {
  const blob = new Blob([lines.join("\n")], { type: "text/csv;charset=utf-8;" });
  const link = document.createElement("a");
  link.href = URL.createObjectURL(blob);
  link.download = filename;
  link.style.visibility = "hidden";
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  URL.revokeObjectURL(link.href);
}

async function exportTablePdf(
  title: string,
  fileSlug: string,
  headers: string[],
  dataRows: string[][],
): Promise<void> {
  const { default: jsPDF } = await import("jspdf");
  const autoTable = (await import("jspdf-autotable")).default;
  const orientation = headers.length >= 4 ? "landscape" : "portrait";
  const doc = new jsPDF({ orientation, unit: "mm", format: "a4" });
  const pageWidth = doc.internal.pageSize.getWidth();
  const margin = 10;
  const now = new Date();
  const formattedDateTime = `Date : ${String(now.getDate()).padStart(2, "0")}/${String(now.getMonth() + 1).padStart(2, "0")}/${now.getFullYear()} ${String(now.getHours() % 12 || 12).padStart(2, "0")}:${String(now.getMinutes()).padStart(2, "0")}:${String(now.getSeconds()).padStart(2, "0")} ${now.getHours() >= 12 ? "PM" : "AM"}`;
  doc.setFontSize(10);
  doc.text(formattedDateTime, pageWidth - margin - doc.getTextWidth(formattedDateTime), margin + 8);
  doc.setFontSize(14);
  doc.setFont("helvetica", "bold");
  doc.text(title, (pageWidth - doc.getTextWidth(title)) / 2, margin + 10);
  const body = dataRows.map((row, index) => [String(index + 1), ...row]);
  autoTable(doc, {
    head: [["No.", ...headers]],
    body: body,
    startY: margin + 18,
    margin: { left: margin, right: margin },
    styles: { fontSize: 8, cellPadding: 1.5 },
    headStyles: { fillColor: [59, 130, 246], textColor: [255, 255, 255], fontStyle: "bold", halign: "center" },
    columnStyles: { 0: { halign: "center", cellWidth: 12 } },
  });
  doc.save(`${fileSlug}_${exportDateSlug()}.pdf`);
}

async function exportTableExcel(
  worksheetName: string,
  fileSlug: string,
  headers: string[],
  dataRows: string[][],
): Promise<void> {
  const ExcelJS = await import("exceljs");
  const wb = new ExcelJS.Workbook();
  const safeName =
    worksheetName
      .replace(/[:\\/?*[\]]/g, "-")
      .replace(/\s+/g, " ")
      .trim()
      .slice(0, 31) || "Export";
  const ws = wb.addWorksheet(safeName);
  ws.addRow(["No.", ...headers]);
  dataRows.forEach((row, index) => {
    ws.addRow([index + 1, ...row]);
  });
  const buf = await wb.xlsx.writeBuffer();
  const blob = new Blob([buf], { type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" });
  const url = URL.createObjectURL(blob);
  const a = document.createElement("a");
  a.href = url;
  a.download = `${fileSlug}_${exportDateSlug()}.xlsx`;
  a.click();
  URL.revokeObjectURL(url);
}

function runGridExport(
  label: string,
  fileSlug: string,
  headers: string[],
  dataRows: string[][],
  kind: "pdf" | "csv" | "excel",
): void {
  if (dataRows.length === 0) {
    toast.info("No data", "There is nothing to export on this page.");
    return;
  }
  void (async () => {
    try {
      if (kind === "csv") {
        const lines = [
          ["No.", ...headers].map(csvEscapeField).join(","),
          ...dataRows.map((row, i) => [String(i + 1), ...row].map(csvEscapeField).join(",")),
        ];
        downloadCsvBlob(`${fileSlug}_${exportDateSlug()}.csv`, lines);
        toast.success("CSV downloaded");
        return;
      }
      if (kind === "excel") {
        await exportTableExcel(label, fileSlug, headers, dataRows);
        toast.success("Excel downloaded");
        return;
      }
      await exportTablePdf(label, fileSlug, headers, dataRows);
      toast.success("PDF downloaded");
    } catch (e) {
      toast.error("Export failed", e instanceof Error ? e.message : String(e));
    }
  })();
}

function mainExportPayload(): { headers: string[]; rows: string[][] } {
  return {
    headers: ["Code", "Description", "Status"],
    rows: mainRows.value.map((row) => [cellMainCode(row), cellMainDesc(row), cellMainStatus(row)]),
  };
}

function exportMain(kind: "pdf" | "csv" | "excel") {
  const { headers, rows } = mainExportPayload();
  runGridExport("Item listing — Main category", "ItemListing_MainCategory", headers, rows, kind);
}

function subExportPayload(): { headers: string[]; rows: string[][] } {
  return {
    headers: ["Code", "Description", "Status"],
    rows: subRows.value.map((row) => [cellSubCode(row), cellSubDesc(row), cellSubStatus(row)]),
  };
}

function exportSub(kind: "pdf" | "csv" | "excel") {
  const { headers, rows } = subExportPayload();
  runGridExport("Item listing — Subcategory", "ItemListing_Subcategory", headers, rows, kind);
}

function ssiExportPayload(): { headers: string[]; rows: string[][] } {
  return {
    headers: ["Code", "Description", "Status"],
    rows: ssiRows.value.map((row) => [cellSsiCode(row), cellSsiDesc(row), cellSsiStatus(row)]),
  };
}

function exportSsi(kind: "pdf" | "csv" | "excel") {
  const { headers, rows } = ssiExportPayload();
  runGridExport("Item listing — Subsiri", "ItemListing_Subsiri", headers, rows, kind);
}

function lineExportPayload(): { headers: string[]; rows: string[][] } {
  return {
    headers: ["Code", "Description", "Account Code", "MyFisLite", "Status"],
    rows: lineRows.value.map((row) => [
      cellLineCode(row),
      cellLineDesc(row),
      cellLineAcct(row),
      cellLineMyFi(row),
      cellLineStatus(row),
    ]),
  };
}

function exportLine(kind: "pdf" | "csv" | "excel") {
  const { headers, rows } = lineExportPayload();
  runGridExport("Item listing — Item main", "ItemListing_ItemMain", headers, rows, kind);
}

/** Detail modal */
const detailVisible = ref(false);
const detailHeading = ref("Details");
const detailRow = ref<Record<string, unknown> | null>(null);

function humanKey(k: string): string {
  return k
    .replace(/_/g, " ")
    .replace(/([a-z])([A-Z])/g, "$1 $2")
    .trim();
}

function openDetail(row: Record<string, unknown>, title: string) {
  detailHeading.value = title;
  detailRow.value = row;
  detailVisible.value = true;
}

function closeDetail() {
  detailVisible.value = false;
  detailRow.value = null;
}

const detailPairs = computed(() => {
  if (!detailRow.value) return [];
  return Object.entries(detailRow.value).map(([key, val]) => ({
    k: humanKey(key),
    v: val === null || val === undefined ? "—" : String(val),
  }));
});

/** Row highlight */
function mainSelected(r: Record<string, unknown>): boolean {
  return selectedMain.value !== null && mainCode(selectedMain.value) === mainCode(r);
}
function subSelected(r: Record<string, unknown>): boolean {
  return selectedSub.value !== null && subCode(selectedSub.value) === subCode(r);
}
function ssiSelected(r: Record<string, unknown>): boolean {
  return selectedSsi.value !== null && ssiCode(selectedSsi.value) === ssiCode(r);
}

/** Display helpers — API returns camelCase from middleware */
function cellMainCode(r: Record<string, unknown>) {
  return String(r.ldeValue ?? r.lde_value ?? "—");
}
function cellMainDesc(r: Record<string, unknown>) {
  return String(r.ldeDescription ?? r.lde_description ?? "—");
}
function cellMainStatus(r: Record<string, unknown>) {
  return String(r.ldeStatus ?? r.lde_status ?? "—");
}

function cellSubCode(r: Record<string, unknown>) {
  return String(r.iscSubcategoryCode ?? r.isc_subcategory_code ?? "—");
}
function cellSubDesc(r: Record<string, unknown>) {
  return String(r.iscSubcategoryDesc ?? r.isc_subcategory_desc ?? "—");
}
function cellSubStatus(r: Record<string, unknown>) {
  return String(r.iscStatus ?? r.isc_status ?? "—");
}

function cellSsiCode(r: Record<string, unknown>) {
  return String(r.issSubsiriCode ?? r.iss_subsiri_code ?? "—");
}
function cellSsiDesc(r: Record<string, unknown>) {
  return String(r.issSubsiriDesc ?? r.iss_subsiri_desc ?? "—");
}
function cellSsiStatus(r: Record<string, unknown>) {
  return String(r.issStatus ?? r.iss_status ?? "—");
}

function cellLineCode(r: Record<string, unknown>) {
  return String(r.itmItemCode ?? r.itm_item_code ?? "—");
}
function cellLineDesc(r: Record<string, unknown>) {
  return String(r.itmItemDesc ?? r.itm_item_desc ?? "—");
}
function cellLineAcct(r: Record<string, unknown>) {
  return String(r.acmAcctCode ?? r.acm_acct_code ?? "—");
}
function cellLineMyFi(r: Record<string, unknown>) {
  return String(r.itmMyfisliteFlag ?? r.itm_myfislite_flag ?? "—");
}
function cellLineStatus(r: Record<string, unknown>) {
  return String(r.itmStatus ?? r.itm_status ?? "—");
}

onMounted(async () => {
  await loadGroups();
  applyQueryGroupFromRoute();
  if (!grouplookup.value.trim() && (kerisiMenuId.value === 2293 || kerisiMenuId.value === 1563)) {
    const prefs = ["ASSET/INV", "WIP"];
    const pick = prefs.find((p) => groupOptions.value.some((o) => o.value === p));
    if (pick) grouplookup.value = pick;
    else if (groupOptions.value[0]?.value) grouplookup.value = groupOptions.value[0].value;
  }
  if (grouplookup.value.trim()) await applyGroupSearch();
});

onUnmounted(() => {
  [debMain, debSub, debSsi, debLine].forEach((t) => {
    if (t) clearTimeout(t);
  });
});
</script>

<template>
  <AdminLayout>
    <div class="space-y-4">
      <h1 class="page-title">{{ pageHeading }}</h1>

      <!-- Search Group -->
      <article class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-4 py-3">
          <h2 class="text-base font-semibold text-slate-900">Search Group</h2>
        </div>
        <div class="flex flex-wrap items-end justify-between gap-3 px-4 py-4">
          <div class="min-w-[12rem] flex-1">
            <label class="mb-1 block text-xs font-medium text-slate-600">
              Group <span class="text-red-600">*</span>
            </label>
            <select
              v-model="grouplookup"
              class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400"
            >
              <option value="">— Select —</option>
              <option v-for="o in groupOptions" :key="o.value" :value="o.value">
                {{ o.label }}
              </option>
            </select>
          </div>
          <button
            type="button"
            class="inline-flex shrink-0 items-center gap-2 rounded-lg bg-blue-600 px-5 py-2 text-sm font-medium text-white hover:bg-blue-700"
            :disabled="!grouplookup.trim()"
            @click="applyGroupSearch"
          >
            <Search class="h-4 w-4" />
            Search
          </button>
        </div>
      </article>

      <!-- Main Category -->
      <section class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-4 py-3">
          <h2 class="text-base font-semibold text-slate-900">Main Category</h2>
        </div>
        <div class="space-y-3 p-4">
          <div class="flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-2 text-xs text-slate-600">
              <span>Show</span>
              <select
                v-model.number="mainLimit"
                class="rounded border border-slate-300 px-2 py-1 text-xs"
                @change="mainPage = 1; fetchMain()"
              >
                <option v-for="n in [5, 10, 25, 50]" :key="n" :value="n">{{ n }}</option>
              </select>
              <span>entries</span>
            </div>
            <div class="relative flex min-w-[12rem] flex-1 items-center gap-2">
              <Search class="pointer-events-none absolute left-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400" />
              <input
                v-model="mainQ"
                type="search"
                placeholder="Search…"
                class="h-8 w-full min-w-0 rounded-lg border border-slate-300 py-1.5 pl-8 pr-8 text-sm"
                @input="debounce('main', flushMain)"
                @keydown="onMainKey"
              />
              <button
                v-if="mainQ"
                type="button"
                class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-700"
                @click="clearMainQ"
              >
                <X class="h-3.5 w-3.5" />
              </button>
            </div>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead>
                <tr class="border-b border-slate-200 bg-slate-50">
                  <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">No</th>
                  <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Code</th>
                  <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Description</th>
                  <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Status</th>
                  <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Action</th>
                </tr>
              </thead>
              <tbody>
                <template v-if="mainLoading">
                  <tr>
                    <td colspan="5" class="px-3 py-6 text-center text-slate-400">Loading…</td>
                  </tr>
                </template>
                <template v-else-if="!mainRows.length">
                  <tr>
                    <td colspan="5" class="px-3 py-6 text-center text-slate-400">No records found.</td>
                  </tr>
                </template>
                <template v-else>
                  <tr
                    v-for="(row, ri) in mainRows"
                    :key="'m-' + ri + '-' + cellMainCode(row)"
                    class="cursor-pointer border-b border-slate-100 hover:bg-slate-50"
                    :class="{ 'bg-amber-100 hover:bg-amber-100': mainSelected(row) }"
                    @click="toggleMain(row)"
                  >
                    <td class="whitespace-nowrap px-3 py-2">{{ (mainPage - 1) * mainLimit + ri + 1 }}</td>
                    <td class="px-3 py-2 font-medium">{{ cellMainCode(row) }}</td>
                    <td class="px-3 py-2">{{ cellMainDesc(row) }}</td>
                    <td class="px-3 py-2">{{ cellMainStatus(row) }}</td>
                    <td class="px-3 py-2">
                      <div class="flex items-center gap-1">
                        <button
                          type="button"
                          title="View"
                          class="rounded p-1 text-slate-500 hover:bg-white hover:text-slate-800"
                          @click.stop="openDetail(row, 'Main Category — Details')"
                        >
                          <Eye class="h-3.5 w-3.5" />
                        </button>
                        <button type="button" title="Edit" class="rounded p-1 text-slate-400 hover:bg-white hover:text-slate-700">
                          <Pencil class="h-3.5 w-3.5" />
                        </button>
                      </div>
                    </td>
                  </tr>
                </template>
              </tbody>
            </table>
          </div>
          <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-3">
            <div class="text-xs text-slate-500">Showing {{ mainStartIdx }}-{{ mainEndIdx }} of {{ mainTotal }}</div>
            <div class="flex flex-wrap items-center gap-2">
              <button
                type="button"
                :disabled="mainPage <= 1"
                class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium disabled:opacity-50"
                @click="mainPrev"
              >
                Prev
              </button>
              <span class="text-xs text-slate-600">Page {{ mainPage }} / {{ mainPages }}</span>
              <button
                type="button"
                :disabled="mainPage >= mainPages"
                class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium disabled:opacity-50"
                @click="mainNext"
              >
                Next
              </button>
              <div class="mx-2 h-5 w-px bg-slate-200" />
              <button
                type="button"
                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium shadow-sm hover:bg-slate-50"
                @click="exportMain('pdf')"
              >
                <Download class="h-3.5 w-3.5" />
                PDF
              </button>
              <button
                type="button"
                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium shadow-sm hover:bg-slate-50"
                @click="exportMain('csv')"
              >
                <FileDown class="h-3.5 w-3.5" />
                CSV
              </button>
              <button
                type="button"
                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium shadow-sm hover:bg-slate-50"
                @click="exportMain('excel')"
              >
                <FileSpreadsheet class="h-3.5 w-3.5" />
                Excel
              </button>
              <button
                type="button"
                class="inline-flex items-center gap-1.5 rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-medium text-white shadow-sm hover:bg-slate-800"
                @click="toastAdd('Main Category')"
              >
                <Plus class="h-3.5 w-3.5" />
                Add
              </button>
            </div>
          </div>
        </div>
      </section>

      <!-- Item Subcategory -->
      <section class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-4 py-3">
          <h2 class="text-base font-semibold text-slate-900">Item Subcategory</h2>
        </div>
        <div class="space-y-3 p-4">
          <div class="flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-2 text-xs text-slate-600">
              <span>Show</span>
              <select v-model.number="subLimit" class="rounded border border-slate-300 px-2 py-1 text-xs" @change="subPage = 1; fetchSub()">
                <option v-for="n in [5, 10, 25, 50]" :key="n" :value="n">{{ n }}</option>
              </select>
              <span>entries</span>
            </div>
            <div class="relative flex min-w-[12rem] flex-1">
              <Search class="pointer-events-none absolute left-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400" />
              <input
                v-model="subQ"
                type="search"
                placeholder="Search…"
                class="h-8 w-full rounded-lg border border-slate-300 py-1.5 pl-8 pr-8 text-sm"
                @input="debounce('sub', flushSub)"
                @keydown="onSubKey"
              />
              <button
                v-if="subQ"
                type="button"
                class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-700"
                @click="clearSubQ"
              >
                <X class="h-3.5 w-3.5" />
              </button>
            </div>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead>
                <tr class="border-b border-slate-200 bg-slate-50">
                  <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">No</th>
                  <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Code</th>
                  <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Description</th>
                  <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Status</th>
                  <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Action</th>
                </tr>
              </thead>
              <tbody>
                <template v-if="subLoading">
                  <tr>
                    <td colspan="5" class="px-3 py-6 text-center text-slate-400">Loading…</td>
                  </tr>
                </template>
                <template v-else-if="!subRows.length">
                  <tr>
                    <td colspan="5" class="px-3 py-6 text-center text-slate-400">
                      {{
                        selectedMain ? 'No records found.' : 'Select a Main Category row to load subcategories.'
                      }}
                    </td>
                  </tr>
                </template>
                <template v-else>
                  <tr
                    v-for="(row, ri) in subRows"
                    :key="'s-' + ri + '-' + cellSubCode(row)"
                    class="cursor-pointer border-b border-slate-100 hover:bg-slate-50"
                    :class="{ 'bg-amber-100 hover:bg-amber-100': subSelected(row) }"
                    @click="toggleSub(row)"
                  >
                    <td class="whitespace-nowrap px-3 py-2">{{ (subPage - 1) * subLimit + ri + 1 }}</td>
                    <td class="px-3 py-2 font-medium">{{ cellSubCode(row) }}</td>
                    <td class="px-3 py-2">{{ cellSubDesc(row) }}</td>
                    <td class="px-3 py-2">{{ cellSubStatus(row) }}</td>
                    <td class="px-3 py-2">
                      <div class="flex items-center gap-1">
                        <button
                          type="button"
                          title="View"
                          class="rounded p-1 text-slate-500 hover:bg-white"
                          @click.stop="openDetail(row, 'Item Subcategory — Details')"
                        >
                          <Eye class="h-3.5 w-3.5" />
                        </button>
                        <button type="button" title="Edit" class="rounded p-1 text-slate-400 hover:bg-white">
                          <Pencil class="h-3.5 w-3.5" />
                        </button>
                      </div>
                    </td>
                  </tr>
                </template>
              </tbody>
            </table>
          </div>
          <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-3">
            <div class="text-xs text-slate-500">Showing {{ subStartIdx }}-{{ subEndIdx }} of {{ subTotal }}</div>
            <div class="flex flex-wrap items-center gap-2">
              <button
                type="button"
                :disabled="subPage <= 1"
                class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium disabled:opacity-50"
                @click="subPrev"
              >
                Prev
              </button>
              <span class="text-xs text-slate-600">Page {{ subPage }} / {{ subPages }}</span>
              <button
                type="button"
                :disabled="subPage >= subPages"
                class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium disabled:opacity-50"
                @click="subNext"
              >
                Next
              </button>
              <div class="mx-2 h-5 w-px bg-slate-200" />
              <button
                type="button"
                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium shadow-sm hover:bg-slate-50"
                @click="exportSub('pdf')"
              >
                <Download class="h-3.5 w-3.5" />
                PDF
              </button>
              <button
                type="button"
                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium shadow-sm hover:bg-slate-50"
                @click="exportSub('csv')"
              >
                <FileDown class="h-3.5 w-3.5" />
                CSV
              </button>
              <button
                type="button"
                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium shadow-sm hover:bg-slate-50"
                @click="exportSub('excel')"
              >
                <FileSpreadsheet class="h-3.5 w-3.5" />
                Excel
              </button>
              <button
                type="button"
                class="inline-flex items-center gap-1.5 rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-medium text-white shadow-sm hover:bg-slate-800"
                @click="toastAdd('Item Subcategory')"
              >
                <Plus class="h-3.5 w-3.5" />
                Add
              </button>
            </div>
          </div>
        </div>
      </section>

      <!-- Item Subsiri -->
      <section class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-4 py-3">
          <h2 class="text-base font-semibold text-slate-900">Item Subsiri</h2>
        </div>
        <div class="space-y-3 p-4">
          <div class="flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-2 text-xs text-slate-600">
              <span>Show</span>
              <select v-model.number="ssiLimit" class="rounded border border-slate-300 px-2 py-1 text-xs" @change="ssiPage = 1; fetchSsi()">
                <option v-for="n in [5, 10, 25, 50]" :key="n" :value="n">{{ n }}</option>
              </select>
              <span>entries</span>
            </div>
            <div class="relative flex min-w-[12rem] flex-1">
              <Search class="pointer-events-none absolute left-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400" />
              <input
                v-model="ssiQ"
                type="search"
                placeholder="Search…"
                class="h-8 w-full rounded-lg border border-slate-300 py-1.5 pl-8 pr-8 text-sm"
                @input="debounce('ssi', flushSsi)"
                @keydown="onSsiKey"
              />
              <button v-if="ssiQ" type="button" class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400" @click="clearSsiQ">
                <X class="h-3.5 w-3.5" />
              </button>
            </div>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead>
                <tr class="border-b border-slate-200 bg-slate-50">
                  <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">No</th>
                  <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Code</th>
                  <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Description</th>
                  <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Status</th>
                  <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Action</th>
                </tr>
              </thead>
              <tbody>
                <template v-if="ssiLoading">
                  <tr>
                    <td colspan="5" class="px-3 py-6 text-center text-slate-400">Loading…</td>
                  </tr>
                </template>
                <template v-else-if="!ssiRows.length">
                  <tr>
                    <td colspan="5" class="px-3 py-6 text-center text-slate-400">
                      {{
                        selectedMain && selectedSub ? 'No records found.' : 'Select Main Category and Subcategory to load.'
                      }}
                    </td>
                  </tr>
                </template>
                <template v-else>
                  <tr
                    v-for="(row, ri) in ssiRows"
                    :key="'i-' + ri + '-' + cellSsiCode(row)"
                    class="cursor-pointer border-b border-slate-100 hover:bg-slate-50"
                    :class="{ 'bg-amber-100 hover:bg-amber-100': ssiSelected(row) }"
                    @click="toggleSsi(row)"
                  >
                    <td class="whitespace-nowrap px-3 py-2">{{ (ssiPage - 1) * ssiLimit + ri + 1 }}</td>
                    <td class="px-3 py-2 font-medium">{{ cellSsiCode(row) }}</td>
                    <td class="px-3 py-2">{{ cellSsiDesc(row) }}</td>
                    <td class="px-3 py-2">{{ cellSsiStatus(row) }}</td>
                    <td class="px-3 py-2">
                      <div class="flex items-center gap-1">
                        <button
                          type="button"
                          title="View"
                          class="rounded p-1 text-slate-500 hover:bg-white"
                          @click.stop="openDetail(row, 'Item Subsiri — Details')"
                        >
                          <Eye class="h-3.5 w-3.5" />
                        </button>
                        <button type="button" title="Edit" class="rounded p-1 text-slate-400 hover:bg-white">
                          <Pencil class="h-3.5 w-3.5" />
                        </button>
                      </div>
                    </td>
                  </tr>
                </template>
              </tbody>
            </table>
          </div>
          <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-3">
            <div class="text-xs text-slate-500">Showing {{ ssiStartIdx }}-{{ ssiEndIdx }} of {{ ssiTotal }}</div>
            <div class="flex flex-wrap items-center gap-2">
              <button
                type="button"
                :disabled="ssiPage <= 1"
                class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium disabled:opacity-50"
                @click="ssiPrev"
              >
                Prev
              </button>
              <span class="text-xs text-slate-600">Page {{ ssiPage }} / {{ ssiPages }}</span>
              <button
                type="button"
                :disabled="ssiPage >= ssiPages"
                class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium disabled:opacity-50"
                @click="ssiNext"
              >
                Next
              </button>
              <div class="mx-2 h-5 w-px bg-slate-200" />
              <button
                type="button"
                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium shadow-sm hover:bg-slate-50"
                @click="exportSsi('pdf')"
              >
                <Download class="h-3.5 w-3.5" />
                PDF
              </button>
              <button
                type="button"
                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium shadow-sm hover:bg-slate-50"
                @click="exportSsi('csv')"
              >
                <FileDown class="h-3.5 w-3.5" />
                CSV
              </button>
              <button
                type="button"
                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium shadow-sm hover:bg-slate-50"
                @click="exportSsi('excel')"
              >
                <FileSpreadsheet class="h-3.5 w-3.5" />
                Excel
              </button>
              <button
                type="button"
                class="inline-flex items-center gap-1.5 rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-medium text-white shadow-sm hover:bg-slate-800"
                @click="toastAdd('Item Subsiri')"
              >
                <Plus class="h-3.5 w-3.5" />
                Add
              </button>
            </div>
          </div>
        </div>
      </section>

      <!-- Item Main (codes) -->
      <section class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-4 py-3">
          <h2 class="text-base font-semibold text-slate-900">Item Main</h2>
        </div>
        <div class="space-y-3 p-4">
          <div class="flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-2 text-xs text-slate-600">
              <span>Show</span>
              <select v-model.number="lineLimit" class="rounded border border-slate-300 px-2 py-1 text-xs" @change="linePage = 1; fetchLines()">
                <option v-for="n in [5, 10, 25, 50]" :key="n" :value="n">{{ n }}</option>
              </select>
              <span>entries</span>
            </div>
            <div class="relative flex min-w-[12rem] flex-1">
              <Search class="pointer-events-none absolute left-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400" />
              <input
                v-model="lineQ"
                type="search"
                placeholder="Search…"
                class="h-8 w-full rounded-lg border border-slate-300 py-1.5 pl-8 pr-8 text-sm"
                @input="debounce('line', flushLine)"
                @keydown="onLineKey"
              />
              <button v-if="lineQ" type="button" class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400" @click="clearLineQ">
                <X class="h-3.5 w-3.5" />
              </button>
            </div>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead>
                <tr class="border-b border-slate-200 bg-slate-50">
                  <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">No</th>
                  <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Code</th>
                  <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Description</th>
                  <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Account Code</th>
                  <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">MyFisLite</th>
                  <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Status</th>
                  <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Action</th>
                </tr>
              </thead>
              <tbody>
                <template v-if="lineLoading">
                  <tr>
                    <td colspan="7" class="px-3 py-6 text-center text-slate-400">Loading…</td>
                  </tr>
                </template>
                <template v-else-if="!lineRows.length">
                  <tr>
                    <td colspan="7" class="px-3 py-6 text-center text-slate-400">
                      {{
                        selectedMain && selectedSub && selectedSsi
                          ? 'No records found.'
                          : 'Select hierarchy through Item Subsiri to load Item Main.'
                      }}
                    </td>
                  </tr>
                </template>
                <template v-else>
                  <tr
                    v-for="(row, ri) in lineRows"
                    :key="'l-' + ri + '-' + cellLineCode(row)"
                    class="border-b border-slate-100 hover:bg-slate-50"
                  >
                    <td class="whitespace-nowrap px-3 py-2">{{ (linePage - 1) * lineLimit + ri + 1 }}</td>
                    <td class="px-3 py-2 font-medium">{{ cellLineCode(row) }}</td>
                    <td class="px-3 py-2">{{ cellLineDesc(row) }}</td>
                    <td class="px-3 py-2">{{ cellLineAcct(row) }}</td>
                    <td class="px-3 py-2">{{ cellLineMyFi(row) }}</td>
                    <td class="px-3 py-2">{{ cellLineStatus(row) }}</td>
                    <td class="px-3 py-2">
                      <button
                        type="button"
                        title="View"
                        class="rounded p-1 text-slate-500 hover:bg-slate-100"
                        @click="openDetail(row, 'Item Main — Details')"
                      >
                        <Eye class="h-3.5 w-3.5" />
                      </button>
                    </td>
                  </tr>
                </template>
              </tbody>
            </table>
          </div>
          <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-3">
            <div class="text-xs text-slate-500">Showing {{ lineStartIdx }}-{{ lineEndIdx }} of {{ lineTotal }}</div>
            <div class="flex flex-wrap items-center gap-2">
              <button
                type="button"
                :disabled="linePage <= 1"
                class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium disabled:opacity-50"
                @click="linePrev"
              >
                Prev
              </button>
              <span class="text-xs text-slate-600">Page {{ linePage }} / {{ linePages }}</span>
              <button
                type="button"
                :disabled="linePage >= linePages"
                class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium disabled:opacity-50"
                @click="lineNext"
              >
                Next
              </button>
              <div class="mx-2 h-5 w-px bg-slate-200" />
              <button
                type="button"
                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium shadow-sm hover:bg-slate-50"
                @click="exportLine('pdf')"
              >
                <Download class="h-3.5 w-3.5" />
                PDF
              </button>
              <button
                type="button"
                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium shadow-sm hover:bg-slate-50"
                @click="exportLine('csv')"
              >
                <FileDown class="h-3.5 w-3.5" />
                CSV
              </button>
              <button
                type="button"
                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium shadow-sm hover:bg-slate-50"
                @click="exportLine('excel')"
              >
                <FileSpreadsheet class="h-3.5 w-3.5" />
                Excel
              </button>
              <button
                type="button"
                class="inline-flex items-center gap-1.5 rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-medium text-white shadow-sm hover:bg-slate-800"
                @click="toastAdd('Item Main')"
              >
                <Plus class="h-3.5 w-3.5" />
                Add
              </button>
            </div>
          </div>
        </div>
      </section>
    </div>

    <Teleport to="body">
      <div v-if="detailVisible" class="fixed inset-0 z-50 flex items-center justify-center bg-black/45 p-4" @click.self="closeDetail">
        <div class="max-h-[90vh] w-full max-w-xl overflow-auto rounded-xl border border-slate-200 bg-white shadow-xl">
          <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
            <h3 class="text-base font-semibold text-slate-900">{{ detailHeading }}</h3>
            <button type="button" class="rounded p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-700" @click="closeDetail">
              <X class="h-4 w-4" />
            </button>
          </div>
          <dl class="grid gap-x-6 gap-y-3 p-5 sm:grid-cols-2">
            <div v-for="(pair, pi) in detailPairs" :key="pi">
              <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">{{ pair.k }}</dt>
              <dd class="text-sm text-slate-900">{{ pair.v }}</dd>
            </div>
          </dl>
          <div class="flex justify-end border-t border-slate-100 px-5 py-4">
            <button type="button" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700" @click="closeDetail">
              Close
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </AdminLayout>
</template>
