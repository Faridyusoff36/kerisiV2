<script setup lang="ts">
/**
 * Payroll Kerisi shell view.
 * Uses the Payroll registry (PAGE_MENUID1122_LEVEL3.json → kerisi-remaining-registry.generated.ts)
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
  Eye,
  FileDown,
  FileSpreadsheet,
  Filter,
  MoreVertical,
  Pencil,
  Info,
  Plus,
  Search,
  Trash2,
  X,
} from "lucide-vue-next";
import AdminLayout from "@/layouts/AdminLayout.vue";
import { getKerisiPrToCancelDetails, kerisiWpnCancel, listKerisiRemainingData } from "@/api/cms";
import {
  getKerisiMenuTrailByMenuId,
  parseKerisiNumericMenuIdFromPath,
} from "@/config/kerisi-menu-resolve";
import type {
  KerisiRemainingDatatable,
  KerisiRemainingFormField,
  KerisiRemainingPageSpec,
} from "@/config/kerisi-remaining-registry.generated";
import { getKerisiRemainingSpec } from "@/config/kerisi-remaining-registry.generated";
import { useToast } from "@/composables/useToast";

const toast  = useToast();
const route  = useRoute();
const router = useRouter();

const menuId = computed(() => parseKerisiNumericMenuIdFromPath(route.path));

/** Menu 2085 GRN Cancel — checkbox value (stub). Declared early for handlers below. */
const grn2085SelectedCbox = ref<string>("");

const spec = computed<KerisiRemainingPageSpec | null>(() => {
  const id = menuId.value;
  if (id === null) return null;
  return getKerisiRemainingSpec(id);
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

function cellKey(dt: KerisiRemainingDatatable, colIdx: number): string {
  const raw = toStr(dt.dtKey[colIdx]);
  if (raw.trim()) return raw.trim();
  const lab = toStr(dt.dtBi[colIdx]) || `col_${colIdx}`;
  return lab.replace(/\s+/g, "_").toLowerCase();
}

/** Purchase Order shell — Amount shown as legacy grouped decimals. */
function formatPurchasingPoAmountCell(mid: number | null, dt: KerisiRemainingDatatable, colIdx: number, raw: string): string {
  if (mid !== 1833 && mid !== 2030 && mid !== 2039) return raw;
  const dk = String(dt.dtKey[colIdx] ?? "").toLowerCase();
  const label = String(dt.dtBi[colIdx] ?? "").toLowerCase();
  if (!(dk.includes("pom_order_amt") || label.includes("amount"))) {
    return raw;
  }
  const clean = raw.replace(/,/g, "").trim();
  if (clean === "") return "";
  const n = parseFloat(clean);
  if (!Number.isFinite(n)) return raw;
  return new Intl.NumberFormat("en-MY", { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(n);
}

/** Work Progress Note grids — Tax/Amount/Unit Price columns use legacy grouping. */
function formatKerisiWpnMoney(mid: number | null, dt: KerisiRemainingDatatable, colIdx: number, raw: string): string {
  if (mid !== 1840 && mid !== 2082 && mid !== 1838 && mid !== 1839 && mid !== 2085 && mid !== 2624 && mid !== 2626 && mid !== 1828 && mid !== 2663) return raw;
  const dk = String(dt.dtKey[colIdx] ?? "").toLowerCase();
  const lab = String(dt.dtBi[colIdx] ?? "")
    .toLowerCase()
    .replace(/<br\s*\/?>/gi, " ");
  const isAmt =
    dk.includes("amt") ||
    dk.includes("tax") ||
    dk.includes("price") ||
    lab.includes("(rm)") ||
    (lab.includes("amount") && !lab.includes("checkbox"));
  if (!isAmt) return raw;
  const clean = raw.replace(/,/g, "").trim();
  if (clean === "") return "";
  const n2 = parseFloat(clean);
  if (!Number.isFinite(n2)) return raw;
  return new Intl.NumberFormat("en-MY", { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(n2);
}

function formatKerisiWpnDateIfNeeded(mid: number | null, dt: KerisiRemainingDatatable, colIdx: number, raw: string): string {
  if (mid !== 2082 && mid !== 1839 && mid !== 2085 && mid !== 1828 && mid !== 2663) return raw;
  const dk = String(dt.dtKey[colIdx] ?? "").toLowerCase();
  if (
    mid === 2082 &&
    !dk.includes("wpm_receive") &&
    !dk.includes("date")
  ) {
    return raw;
  }
  if (mid === 1839 && !dk.includes("created") && !dk.includes("date")) return raw;
  if (mid === 2085 && !dk.includes("tarikh") && !dk.includes("date")) return raw;
  if ((mid === 1828 || mid === 2663) && !dk.includes("date")) return raw;
  const t = raw.trim();
  if (!t) return "";
  // SQL date-only strings → DD/MM/YYYY without timezone shift
  const iso = /^(\d{4})-(\d{2})-(\d{2})/.exec(t);
  if (iso) {
    return `${iso[3]}/${iso[2]}/${iso[1]}`;
  }
  const d = new Date(t);
  if (Number.isNaN(d.getTime())) return raw;
  const dd = String(d.getDate()).padStart(2, "0");
  const mm = String(d.getMonth() + 1).padStart(2, "0");
  const yyyy = d.getFullYear();
  return `${dd}/${mm}/${yyyy}`;
}

function displayCell(row: Record<string, unknown>, dt: KerisiRemainingDatatable, colIdx: number): string {
  const key = cellKey(dt, colIdx);
  const tryKey = (k: string) => {
    const v = row[k];
    return v !== undefined && v !== null ? String(v) : null;
  };

  let resolved: string | null = tryKey(key);

  if (resolved === null && /^[A-Z]/.test(key)) {
    const cc = key.charAt(0).toLowerCase() + key.slice(1);
    resolved = tryKey(cc);
  }

  if (resolved === null) {
    const camel = key.replace(/_([a-z])/g, (_, c: string) => c.toUpperCase());
    resolved = tryKey(camel);
  }

  if (resolved === null) {
    const compact = key.replace(/[^a-zA-Z0-9]/g, "").toLowerCase();
    if (compact.length) {
      for (const [rk, rv] of Object.entries(row)) {
        if (rk.replace(/[^a-zA-Z0-9]/g, "").toLowerCase() === compact) {
          if (rv !== undefined && rv !== null) {
            resolved = String(rv);
            break;
          }
        }
      }
    }
  }

  if (menuId.value === 2846) {
    const lowerKey = key.toLowerCase();
    if (lowerKey === "agg_amtnew") {
      const raw = row.agg_amt_new ?? row.aggAmtNew ?? row.agg_amtNew ?? row.aggAmtnew;
      return raw !== undefined && raw !== null ? formatMoneyInput(String(raw)) : "";
    }
    if (lowerKey.includes("amt")) return formatMoneyInput(resolved ?? "");
    if (lowerKey.includes("date")) return formatKerisiWpnDateIfNeeded(2082, dt, colIdx, resolved ?? "");
  }

  if (menuId.value === 3320) {
    const lowerKey = key.toLowerCase();
    if (["createddate", "startdate", "enddate"].includes(lowerKey)) {
      return formatKerisiWpnDateIfNeeded(2082, dt, colIdx, resolved ?? "");
    }
    if (["amount", "amountbalance", "amountmonthly"].includes(lowerKey)) {
      return formatMoneyInput(resolved ?? "");
    }
  }

  if (menuId.value === 2618) {
    const rowText = (k: string) => {
      const v = row[k] ?? row[k.replace(/_([a-z])/g, (_, c: string) => c.toUpperCase())];
      return v !== undefined && v !== null ? String(v) : "";
    };
    if (key === "tjs_jobscope_code") {
      const code = rowText("tjs_jobscope_code");
      const desc = rowText("tjs_jobscope_desc");
      return code && desc ? `${code} - ${desc}` : code;
    }
    if (key === "tjs_jobscope_category") {
      const code = rowText("tjs_jobscope_category");
      const desc = rowText("tjs_jobscope_category_desc");
      return code && desc ? `${code} - ${desc}` : code;
    }
    if (key === "tjs_logic_code") {
      const code = rowText("tjs_logic_code");
      const desc = rowText("tjs_logic_desc");
      return desc || code;
    }
    if (key === "trf_bumi_status") {
      const code = rowText("trf_bumi_status");
      const desc = rowText("trf_bumi_status_desc");
      return code && desc ? `${code} - ${desc}` : code;
    }
  }

  if (menuId.value === 3306 && key === "tas_submit_date_formatted") {
    const v = resolved ?? "";
    const m = /^(\d{4})(\d{2})(\d{2})/.exec(v);
    return m ? `${m[3]}/${m[2]}/${m[1]}` : v;
  }

  const po = formatPurchasingPoAmountCell(menuId.value, dt, colIdx, resolved ?? "");
  const withWpn = formatKerisiWpnMoney(menuId.value, dt, colIdx, po);

  return formatKerisiWpnDateIfNeeded(menuId.value, dt, colIdx, withWpn);
}

function isActionCol(h: string | Record<string, unknown>): boolean {
  const t = toStr(h).trim().toLowerCase();
  return t === "action" || t.startsWith("action") || t.includes("checkbox");
}

function isNoCol(h: string | Record<string, unknown>): boolean {
  const t = toStr(h).trim().toLowerCase();
  return t === "no" || t === "no.";
}

/** Registry column titles may include `<br>` markup — show a single space for thead. */
function stripHtmlBrLabel(h: string | Record<string, unknown> | undefined): string {
  const s = typeof h === "string" ? h : "";
  return s.replace(/<br\s*\/?>/gi, " ").trim();
}

/** Registry `dtClass` may include `d-none` (legacy Bootstrap) to hide Id / internal columns. */
function isHiddenDtCol(dt: KerisiRemainingDatatable, colIdx: number): boolean {
  const raw = dt.dtClass?.[colIdx];
  if (typeof raw !== "string" || !raw.trim()) return false;
  return /\bd-none\b/i.test(raw);
}

function visibleColIndices(dt: KerisiRemainingDatatable): number[] {
  return dt.dtBi.map((_, i) => i).filter((i) => !isHiddenDtCol(dt, i));
}

function tableColspan(dt: KerisiRemainingDatatable): number {
  return visibleColIndices(dt).length;
}

/** Menu 1833 — grand total under Amount (Purchase Order List legacy). */
const grandTotalPoAmtRm = ref<number | null>(null);

function poGrandTotalColSpans(dt: KerisiRemainingDatatable): { label: number; amount: number; tail: number } {
  const vis = visibleColIndices(dt);
  const amountIx = vis.findIndex((hi) => {
    const dk = String(dt.dtKey[hi] ?? "").toLowerCase();
    const lab = String(dt.dtBi[hi] ?? "").toLowerCase();
    return dk.includes("pom_order_amt") || lab.includes("amount");
  });
  const idx = amountIx >= 0 ? amountIx : Math.max(0, vis.length - 2);
  const label = Math.max(idx, 1);
  const tail = Math.max(0, vis.length - label - 1);
  return { label, amount: 1, tail };
}

function formatGrandPoTotal(n: number): string {
  return new Intl.NumberFormat("en-MY", { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(n);
}

function poNumericColClass(dt: KerisiRemainingDatatable, hi: number): string {
  const mid = menuId.value;
  if (mid !== 1833 && mid !== 2030 && mid !== 2039) return "";
  const dk = String(dt.dtKey[hi] ?? "").toLowerCase();
  const lab = String(dt.dtBi[hi] ?? "").toLowerCase();
  if (dk.includes("pom_order_amt") || lab.includes("amount")) return "text-right tabular-nums";
  return "";
}

/** WPN list / cancel / detail grids — right-align amount & tax columns (registry headers may use &lt;br&gt;). */
function wpnNumericColClass(dt: KerisiRemainingDatatable, hi: number): string {
  const mid = menuId.value;
  if (mid !== 1840 && mid !== 2082 && mid !== 1838 && mid !== 2626) return "";
  const dk = String(dt.dtKey[hi] ?? "").toLowerCase();
  const lab = String(dt.dtBi[hi] ?? "")
    .toLowerCase()
    .replace(/<br\s*\/?>/gi, " ");
  if (dk.includes("amt") || dk.includes("tax") || dk.includes("price") || lab.includes("(rm)")) {
    return "text-right tabular-nums";
  }
  return "";
}

/** Purchasing GRN / GRN Cancel / Vendor Assessment GRN+WPN — amount columns. */
function purchasingGrnNumericColClass(dt: KerisiRemainingDatatable, hi: number): string {
  const mid = menuId.value;
  if (mid !== 1839 && mid !== 2085 && mid !== 2624 && mid !== 2846 && mid !== 3320 && mid !== 1828 && mid !== 2663) return "";
  const dk = String(dt.dtKey[hi] ?? "").toLowerCase();
  const lab = String(dt.dtBi[hi] ?? "")
    .toLowerCase()
    .replace(/<br\s*\/?>/gi, " ");
  if (dk.includes("amt") || dk.includes("tax") || dk.includes("amaun") || lab.includes("(rm)") || lab === "amount") {
    return "text-right tabular-nums";
  }
  return "";
}

function tableNumericColClass(dt: KerisiRemainingDatatable, hi: number): string {
  return poNumericColClass(dt, hi) || wpnNumericColClass(dt, hi) || purchasingGrnNumericColClass(dt, hi);
}

/** Purchasing shell pages that use legacy purple thead (match screenshots). */
const kerisiPurchasingPurpleShell = computed(() => {
  const m = menuId.value;
  return m === 1839 || m === 2085 || m === 2624 || m === 2626;
});

const tenderQuotationMenuIds = new Set([2333, 3272, 2724, 2762, 2845, 2827]);
const isTenderQuotationPage = computed(() => {
  const m = menuId.value;
  return m !== null && tenderQuotationMenuIds.has(m);
});
const newVariationOrderMenuIds = new Set([2848, 3323]);
const isNewVariationOrderPage = computed(() => {
  const m = menuId.value;
  return m !== null && newVariationOrderMenuIds.has(m);
});

function hasFreezeLeft(dt: KerisiRemainingDatatable): boolean {
  return (dt.dtFreezeLeft ?? 0) > 0;
}

function kerisiFormFieldHidden(f: KerisiRemainingPageSpec["formSections"][number]): boolean {
  return (f.additionalAttribute ?? "").toLowerCase().includes("d-none");
}

function wpnDropdownOptions(which: "wpn_type" | "po_pr_no" | "vendor" | "currency"): { value: string; label: string }[] {
  const o = kerisiFormOptions.value as Record<string, { value: string; label: string }[] | undefined>;
  const alt: Record<string, string> = {
    wpn_type: "wpnType",
    po_pr_no: "poPrNo",
    vendor: "vendor",
    currency: "currency",
  };
  const a = o[which];
  const b = o[alt[which] ?? ""];
  return Array.isArray(a) && a.length ? a : Array.isArray(b) && b.length ? b : [];
}

type KerisiOption = {
  value: string;
  label: string;
  category?: string;
  ounCode?: string;
  agreementNo?: string;
  agreementRef?: string;
  agreementAmount?: string;
};

function advertisementOptions(key: string): KerisiOption[] {
  const raw = kerisiFormOptions.value[key];
  return Array.isArray(raw) ? (raw as KerisiOption[]) : [];
}

function popupFieldKey(f: KerisiRemainingFormField): string {
  return (f.title ?? "")
    .replace(/\*/g, "")
    .trim()
    .toLowerCase();
}

function advertisementPopupFields(): KerisiRemainingFormField[] {
  const fields = spec.value?.popupFormFields ?? [];
  if (menuId.value !== 2618) return fields;
  if (popupContext.value === "taraf") {
    return fields.filter((f) => popupFieldKey(f) === "taraf");
  }
  return fields.filter((f) => ["category", "jobscope code", "logic"].includes(popupFieldKey(f)));
}

function optionsForAdvertisementPopupField(f: KerisiRemainingFormField): KerisiOption[] {
  const key = popupFieldKey(f);
  if (key === "category") return advertisementOptions("jobscopeCategories");
  if (key === "logic") return advertisementOptions("jobscopeLogic");
  if (key === "taraf") return advertisementOptions("taraf");
  if (key === "jobscope code") {
    const category = popupFormValues.value.category ?? "";
    const opts = advertisementOptions("jobscopes");
    return category ? opts.filter((o) => !o.category || o.category === category) : opts;
  }
  return [];
}

function advertisementPopupModelKey(f: KerisiRemainingFormField): string {
  const key = popupFieldKey(f);
  if (key === "category") return "category";
  if (key === "jobscope code") return "jobscopeCode";
  if (key === "logic") return "logic";
  if (key === "taraf") return "taraf";
  return key.replace(/\s+/g, "_");
}

function selectedOptionLabel(options: KerisiOption[], value: string): string {
  return options.find((o) => o.value === value)?.label ?? value;
}

function variationOrderOptions(key: string): KerisiOption[] {
  const raw = kerisiFormOptions.value[key];
  return Array.isArray(raw) ? (raw as KerisiOption[]) : [];
}

function onVariationAgreementChange(): void {
  const selected = variationOrderOptions("agreements").find((o) => o.value === kerisiFormValues.value.aggId);
  if (!selected) return;
  kerisiFormValues.value.aggNo = selected.agreementNo ?? "";
  kerisiFormValues.value.aggRefDoc = selected.agreementRef ?? "";
  kerisiFormValues.value.aggAmt = selected.agreementAmount ?? "";
}

function formatMoneyInput(value: string | undefined): string {
  const n = Number(String(value ?? "").replace(/,/g, ""));
  if (!Number.isFinite(n)) return value ?? "";
  return new Intl.NumberFormat("en-MY", { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(n);
}

function bankUpdatedFieldKey(title: string): string {
  const map: Record<string, string> = {
    "Vendor Code": "vendorCode",
    "Vendor Name": "vendorName",
    "IC No": "icNo",
    "Telephone No": "telNo",
    "Creditor": "creditor",
    "Fax No": "faxNo",
    "Contact Person": "contactPerson",
    "Vendor Status": "vendorStatus",
    "Debtor": "debtor",
    "Taraf": "taraf",
    "No. GST": "gstNo",
    "Email": "email",
    "Registration Date": "registrationDate",
    "Expiry Date": "expiryDate",
    "KWSP No": "kwspNo",
    "SOCSO No": "socsoNo",
    "Company Category": "companyCategory",
    "Registration No(SSM)": "registrationNoSsm",
    "Registration No (MOF)": "registrationNoMof",
    "Registration Date (SSM)": "registrationDateSsm",
    "Registration Expiry Date (MOF)": "registrationExpiryDateMof",
    "Registration Expiry Date (SSM)": "registrationExpiryDateSsm",
    "Registration No (MOTAC)": "registrationNoMotac",
    "Registration Expired Date (MOTAC)": "registrationExpiredDateMotac",
    "Registration Date (MOTAC)": "registrationDateMotac",
    "ROS No": "rosNo",
    "Name": "approvalName",
    "Position": "approvalPosition",
    "Date": "approvalDate",
    "Approval Status": "approvalStatus",
    "Remark *": "approvalRemark",
  };
  return map[title] ?? title.replace(/[^a-zA-Z0-9]+(.)/g, (_, c: string) => c.toUpperCase()).replace(/^[A-Z]/, (c) => c.toLowerCase());
}

function bankUpdatedFieldDisabled(field: KerisiRemainingPageSpec["formSections"][number]): boolean {
  return String(field.additionalAttribute ?? "").toLowerCase().includes("disabled");
}

function bankUpdatedOptions(title: string): { value: string; label: string }[] {
  const options = kerisiFormOptions.value as Record<string, { value: string; label: string }[] | undefined>;
  if (title === "Creditor" || title === "Debtor") return options.yesNo ?? [];
  if (title === "Taraf") return options.taraf ?? [];
  if (title === "Approval Status") return options.approvalStatus ?? [];
  return [];
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
  if (!s) return [] as { title: string; fields: KerisiRemainingPageSpec["formSections"] }[];
  const seen = new Map<string, KerisiRemainingPageSpec["formSections"][number][]>();
  for (const f of s.formSections) {
    const key = f.componentTitle || "Details";
    if (!seen.has(key)) seen.set(key, []);
    seen.get(key)!.push(f);
  }
  return [...seen.entries()].map(([title, fields]) => ({ title, fields }));
});

// ── smart filter ─────────────────────────────────────────────────────────
const primaryDt = computed<KerisiRemainingDatatable | null>(() => spec.value?.datatables[0] ?? null);
const showSmartFilterUi = computed(() => (spec.value?.smartFilterFields?.length ?? 0) > 0);
const showTopFilterUi   = computed(() => (spec.value?.topFilterFields?.length ?? 0) > 0);
const hasPopupForm      = computed(() => (spec.value?.popupFormFields?.length ?? 0) > 0);

const showSmartFilter = ref(false);
const smartFilterValues = ref<Record<string, string>>({});
const topFilterValues   = ref<Record<string, string>>({});
/** Option lists for top-filter dropdowns (server meta.topFilterOptions), e.g. menu 1829. */
const topFilterOptions  = ref<Record<string, { value: string; label: string }[]>>({});
/** Menu 1840 — Status smart filter options from server (meta.smartFilterOptions). */
const smartFilterOptionLists = ref<Record<string, { value: string; label: string }[]>>({});
/** Menu 1838 — second grid rows (WPN Detail). */
const extraDatatableRowsStore = ref<Record<string, unknown>[][]>([]);
/** Rows count for Vendor Assessment secondary grid (menu 2624 / 2626). */
const secondaryGridTotal = ref(0);
const kerisiFormOptions = ref<Record<string, unknown>>({});
const kerisiFormValues = ref<Record<string, string>>({});

function optionsForTopFilter(index: number): { value: string; label: string }[] {
  const legacyKey = `tf_${index}`;
  const camelKey = `tf${index}`;
  return topFilterOptions.value[legacyKey] ?? topFilterOptions.value[camelKey] ?? [];
}

function optionsForSmartFilter(index: number): { value: string; label: string }[] {
  const k = `sf_${index}`;
  return smartFilterOptionLists.value[k] ?? [];
}

function initFilters() {
  const sfFields = spec.value?.smartFilterFields ?? [];
  const sfVals: Record<string, string> = {};
  sfFields.forEach((_, i) => (sfVals[`sf_${i}`] = ""));
  smartFilterValues.value = sfVals;

  const tfFields = spec.value?.topFilterFields ?? [];
  const tfVals: Record<string, string> = {};
  tfFields.forEach((_, i) => (tfVals[`tf_${i}`] = ""));
  topFilterValues.value = tfVals;
  topFilterOptions.value = {};
  smartFilterOptionLists.value = {};
  extraDatatableRowsStore.value = [];
  secondaryGridTotal.value = 0;
  kerisiFormOptions.value = {};
  kerisiFormValues.value = {};

  q.value = "";
  page.value = 1;
  grn2085SelectedCbox.value = "";
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
const popupContext     = ref<"default" | "jobscope" | "taraf">("default");

function openAddModal(context: "default" | "jobscope" | "taraf" = "default") {
  modalMode.value = "add";
  popupContext.value = context;
  popupFormValues.value = {};
  showPopupModal.value = true;
}

/** Menu 1773 (Purchase Requisition List) navigates to New Purchase Requisition (1771); others keep modal UX. */
function onAddPrimaryClick(di = 0) {
  if (menuId.value === 1773) {
    void router.push({ path: "/admin/kerisi/m/1771" });

    return;
  }
  if (menuId.value === 2618) {
    if (di === 0) openAddModal("jobscope");
    else if (di === 1) openAddModal("taraf");
    return;
  }
  openAddModal("default");
}

function openEditModal(row: Record<string, unknown>, context: "default" | "jobscope" | "taraf" = "default") {
  modalMode.value = "edit";
  popupContext.value = context;
  if (menuId.value === 2618 && context === "jobscope") {
    popupFormValues.value = {
      category: String(row.tjs_jobscope_category ?? row.tjsJobscopeCategory ?? ""),
      jobscopeCode: String(row.tjs_jobscope_code ?? row.tjsJobscopeCode ?? ""),
      logic: String(row.tjs_logic_code ?? row.tjsLogicCode ?? ""),
    };
  } else if (menuId.value === 2618 && context === "taraf") {
    popupFormValues.value = {
      taraf: String(row.trf_bumi_status ?? row.trfBumiStatus ?? ""),
    };
  } else {
    popupFormValues.value = Object.fromEntries(
      Object.entries(row).map(([k, v]) => [k, v !== null && v !== undefined ? String(v) : ""])
    );
  }
  showPopupModal.value = true;
}

function saveAdvertisementPopup(): void {
  if (popupContext.value === "jobscope") {
    const category = popupFormValues.value.category ?? "";
    const jobscopeCode = popupFormValues.value.jobscopeCode ?? "";
    const logic = popupFormValues.value.logic ?? "";
    if (!category || !jobscopeCode || !logic) {
      toast.error("Jobscope", "Category, Jobscope Code and Logic are required.");
      return;
    }
    const jobscopeLabel = selectedOptionLabel(advertisementOptions("jobscopes"), jobscopeCode);
    const categoryLabel = selectedOptionLabel(advertisementOptions("jobscopeCategories"), category);
    const logicLabel = selectedOptionLabel(advertisementOptions("jobscopeLogic"), logic);
    rows.value = [
      ...rows.value,
      {
        tjs_jobscope_code: jobscopeCode,
        tjs_jobscope_desc: jobscopeLabel.includes(" - ") ? jobscopeLabel.split(" - ").slice(1).join(" - ") : "",
        tjs_jobscope_category: category,
        tjs_jobscope_category_desc: categoryLabel,
        tjs_logic_code: logic,
        tjs_logic_desc: logicLabel,
      },
    ];
  } else if (popupContext.value === "taraf") {
    const taraf = popupFormValues.value.taraf ?? "";
    if (!taraf) {
      toast.error("Taraf", "Taraf is required.");
      return;
    }
    const label = selectedOptionLabel(advertisementOptions("taraf"), taraf);
    extraDatatableRowsStore.value = [
      [
        ...(extraDatatableRowsStore.value[0] ?? []),
        {
          trf_bumi_status: taraf,
          trf_bumi_status_desc: label.includes(" - ") ? label.split(" - ").slice(1).join(" - ") : "",
        },
      ],
      extraDatatableRowsStore.value[1] ?? [],
    ];
  }
  showPopupModal.value = false;
}

// ── list state ────────────────────────────────────────────────────────────
const rows    = ref<Record<string, unknown>[]>([]);
const loading = ref(false);
/** Menu 3038 — secondary "Details PR" grid (loaded on demand). */
const showDetailsPr = ref(false);
const detailRows = ref<Record<string, unknown>[]>([]);
const detailLoading = ref(false);
const total   = ref(0);
const page    = ref(1);
const limit   = ref(10);
function resetPr3038Details() {
  showDetailsPr.value = false;
  detailRows.value = [];
  detailLoading.value = false;
}

/** Primary key for navigating from List of PR To Be Cancel (3038) → Purchase Requisition Cancel (3039). */
function extractRqmRequisitionId(row: Record<string, unknown>): number | null {
  const raw = row.rqm_requisition_id ?? row.rqmRequisitionId;
  if (typeof raw === "number") return Number.isFinite(raw) && raw > 0 ? raw : null;
  if (typeof raw === "string" && raw.trim() !== "") {
    const n = Number(raw);
    return Number.isFinite(n) && n > 0 ? n : null;
  }
  return null;
}

/** Click a master row on menu 3038 to open cancel form with PR id (legacy PR Cancel / row navigation). */
function navigateToPrCancelFrom3038(row: Record<string, unknown>) {
  const id = extractRqmRequisitionId(row);
  if (id === null) {
    toast.error("Purchase Requisition Cancel", "Could not read requisition id from this row.");
    return;
  }
  void router.push({ path: "/admin/kerisi/m/3039", query: { rqm_requisition_id: String(id) } });
}

/** Work Progress Note Cancel (2082) — row id for shell detail / legacy checkbox value. */
function extractWpmProgressId(row: Record<string, unknown>): number | null {
  const raw = row.wpm_progress_id ?? row.wpmProgressId;
  if (typeof raw === "number") return Number.isFinite(raw) && raw > 0 ? raw : null;
  if (typeof raw === "string" && raw.trim() !== "") {
    const n = Number(raw);
    return Number.isFinite(n) && n > 0 ? n : null;
  }
  return null;
}

/** Open Work Progress Note Detail with this WPN (legacy eye → url_view). */
function openWpnDetail2082(row: Record<string, unknown>) {
  const id = extractWpmProgressId(row);
  if (id === null) {
    toast.error("Work Progress Note", "Could not read WPN id from this row.");
    return;
  }
  void router.push({ path: "/admin/kerisi/m/1838", query: { wpm_progress_id: String(id) } });
}

/** Purchasing / GRN Vendor Assessment grids — navigate to GRN screen. */
function openKerisiGrn1858(row: Record<string, unknown>): void {
  const raw = row.grm_receive_id ?? row.grmReceiveId;
  const id =
    typeof raw === "number" ? raw : typeof raw === "string" ? parseInt(raw, 10) : NaN;
  if (!Number.isFinite(id) || id < 1) {
    toast.error("Good Receive Note", "Missing receive id.");
    return;
  }
  void router.push({ path: "/admin/kerisi/m/1858", query: { grm_receive_id: String(id) } });
}

/** Purchasing / Vendor Assessment WPN grids — navigate to WPN Detail. */
function openKerisiWpn1838(row: Record<string, unknown>): void {
  openWpnDetail2082(row);
}

function openGrnCancel2085Eye(row: Record<string, unknown>): void {
  const u = row.url_view ?? row.urlView;
  if (typeof u === "string" && u.startsWith("/")) {
    void router.push(u);

    return;
  }
  openKerisiGrn1858(row);
}

function onGrn2085CheckboxChange(row: Record<string, unknown>, checked: boolean): void {
  const cbox = String(row.cbox ?? "");
  if (!cbox) return;
  if (checked) {
    grn2085SelectedCbox.value = cbox;
  } else if (grn2085SelectedCbox.value === cbox) {
    grn2085SelectedCbox.value = "";
  }
}

function submitGrnCancel2085Placeholder(): void {
  toast.info("Good Receive Note Cancel", "Cancellation submit API is not connected yet.");
  grn2085SelectedCbox.value = "";
}

function onWpn2082CheckboxChange(row: Record<string, unknown>, checked: boolean): void {
  const cbox = String(row.cbox ?? "");
  if (!cbox) return;
  if (checked) {
    wpn2082SelectedCbox.value = cbox;
  } else if (wpn2082SelectedCbox.value === cbox) {
    wpn2082SelectedCbox.value = "";
  }
}

async function submitWpnCancel2082(): Promise<void> {
  const id = wpn2082SelectedCbox.value.trim();
  if (!id) {
    toast.error("WPN Cancel", "Please select one row.");
    return;
  }
  try {
    const res = await kerisiWpnCancel({ selectedId: id });
    toast.success("WPN Cancel", res.data?.successMessage ?? "Submitted.");
    wpn2082SelectedCbox.value = "";
    await loadRows();
  } catch (e) {
    const err = e as Error & { message?: string };
    toast.error("WPN Cancel", err.message ?? "Cancel failed.");
  }
}

function onShellMasterRowClick(di: number, row: Record<string, unknown>) {
  if (menuId.value !== 3038 || di !== 0) return;
  navigateToPrCancelFrom3038(row);
}

async function openPr3038Details(row: Record<string, unknown>) {
  const noRaw = row.rqm_requisition_no ?? row.rqmRequisitionNo;
  const idRaw = row.rqm_requisition_id ?? row.rqmRequisitionId;
  const params = new URLSearchParams();
  if (typeof noRaw === "string" && noRaw.trim() !== "") params.set("rqm_requisition_no", noRaw.trim());
  else if (idRaw !== undefined && idRaw !== null && String(idRaw) !== "") params.set("rqm_requisition_id", String(idRaw));
  else {
    toast.error("Details", "Missing requisition reference.");
    return;
  }
  detailLoading.value = true;
  showDetailsPr.value = true;
  try {
    const res = await getKerisiPrToCancelDetails(params.toString());
    detailRows.value = Array.isArray(res.data) ? res.data : [];
  } catch (e) {
    detailRows.value = [];
    toast.error("Details", e instanceof Error ? e.message : "Unable to load Details PR.");
  } finally {
    detailLoading.value = false;
  }
}

function shellRows(di: number): Record<string, unknown>[] {
  /** Menu 3041 DT1 (Details PO/Bill) — not wired yet; dedicated API would populate this. Empty = "No records". */
  if (menuId.value === 3041 && di > 0) return [];
  if (menuId.value === 3038 && di > 0) return detailRows.value;
  if (menuId.value === 1838 && di === 1) return extraDatatableRowsStore.value[0] ?? [];
  if ((menuId.value === 2624 || menuId.value === 2626) && di === 1) {
    return extraDatatableRowsStore.value[0] ?? [];
  }
  if (menuId.value === 2618 && di === 1) return extraDatatableRowsStore.value[0] ?? [];
  if (menuId.value === 2618 && di === 2) return extraDatatableRowsStore.value[1] ?? [];
  if (menuId.value === 1955 && di > 0) return extraDatatableRowsStore.value[di - 1] ?? [];
  return rows.value;
}

function shellTableLoading(di: number): boolean {
  if (menuId.value === 3041 && di > 0) return false;
  if (menuId.value === 3038 && di > 0) return detailLoading.value;
  return loading.value;
}

function showDetailSection(di: number): boolean {
  if (menuId.value === 2618 && di === 2) return false;
  if (menuId.value !== 3038 || di === 0) return true;
  return showDetailsPr.value;
}

const q       = ref("");
/** Legacy cbox = wpm_progress_id + '_' + wpm_progress_no for WPN Cancel POST */
const wpn2082SelectedCbox = ref<string>("");
let searchDebounce: ReturnType<typeof setTimeout> | null = null;

async function loadRows() {
  const id = menuId.value;
  if (id === null) return;
  if (id === 3038) resetPr3038Details();
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
  if (id === 3306 && (kerisiFormValues.value.tdmTenderId || kerisiFormValues.value.tdm_tender_id)) {
    params.set("tdm_tender_id", kerisiFormValues.value.tdmTenderId || kerisiFormValues.value.tdm_tender_id);
  }

  // Pass route query params
  for (const [k, v] of Object.entries(route.query)) {
    if (v && !params.has(k)) params.set(k, String(Array.isArray(v) ? v[0] : v));
  }

  try {
    const res = await listKerisiRemainingData(id, `?${params.toString()}`);
    rows.value = Array.isArray(res.data) ? res.data : [];
    total.value = Number(res.meta?.total ?? 0);
    grandTotalPoAmtRm.value = null;
    const m = res.meta as Record<string, unknown> | undefined;
    if (id === 1833 && m) {
      const raw = m.grandTotalPomOrderAmtRm ?? m.grand_total_pom_order_amt_rm;
      if (typeof raw === "number" && Number.isFinite(raw)) {
        grandTotalPoAmtRm.value = raw;
      } else if (typeof raw === "string" && raw.trim() !== "") {
        const num = parseFloat(raw);
        if (Number.isFinite(num)) grandTotalPoAmtRm.value = num;
      }
    }
    const opts = m?.topFilterOptions as Record<string, { value: string; label: string }[]> | undefined;
    if (opts && typeof opts === "object") {
      topFilterOptions.value = opts;
    } else {
      topFilterOptions.value = {};
    }
    const sfOpts = m?.smartFilterOptions ?? m?.smart_filter_options;
    if (sfOpts && typeof sfOpts === "object") {
      smartFilterOptionLists.value = sfOpts as Record<string, { value: string; label: string }[]>;
    } else {
      smartFilterOptionLists.value = {};
    }
    const edt = m?.extraDatatableRows ?? m?.extra_datatable_rows;
    extraDatatableRowsStore.value = Array.isArray(edt) ? (edt as Record<string, unknown>[][]) : [];
    const secTot = m?.secondary_total ?? m?.secondaryTotal;
    let nSec =
      typeof secTot === "number" && Number.isFinite(secTot)
        ? secTot
        : typeof secTot === "string" && secTot.trim() !== ""
          ? Number(secTot)
          : 0;
    if (!Number.isFinite(nSec)) nSec = 0;
    secondaryGridTotal.value = nSec;
    const fo = m?.formOptions ?? m?.form_options;
    kerisiFormOptions.value = fo && typeof fo === "object" ? (fo as Record<string, unknown>) : {};
    const fv = m?.formValues ?? m?.form_values;
    if (fv && typeof fv === "object") {
      kerisiFormValues.value = Object.fromEntries(
        Object.entries(fv as Record<string, unknown>).map(([k, v]) => [
          k,
          v !== null && v !== undefined ? String(v) : "",
        ]),
      );
      const recv = kerisiFormValues.value.wpmReceiveDate;
      if (recv && recv.includes(" ") && recv.length >= 10) {
        kerisiFormValues.value.wpmReceiveDate = recv.slice(0, 10);
      }
    } else {
      kerisiFormValues.value = {};
    }
    if (m?.shellError && typeof m.shellError === "string") {
      toast.error("List source", m.shellError);
    }
  } catch (e) {
    toast.error("Load failed", e instanceof Error ? e.message : "Unable to load list.");
    rows.value  = [];
    total.value = 0;
    grandTotalPoAmtRm.value = null;
    topFilterOptions.value = {};
    smartFilterOptionLists.value = {};
    extraDatatableRowsStore.value = [];
    secondaryGridTotal.value = 0;
    kerisiFormOptions.value = {};
    kerisiFormValues.value = {};
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
/** Item Main Listing (1829): legacy cascaded autosuggest — refetch options when parent dropdown changes. */
function onTopFilterFieldChange(fieldIndex: number) {
  if (menuId.value !== 1829) return;
  if (fieldIndex === 0) {
    topFilterValues.value.tf_1 = "";
    topFilterValues.value.tf_2 = "";
  } else if (fieldIndex === 1) {
    topFilterValues.value.tf_2 = "";
  }
  page.value = 1;
  void loadRows();
}

function applyTopFilter() {
  page.value = 1;
  void loadRows();
}

function applyCommitteeReportFilter() {
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
  const headers = dt.dtBi.filter((h, i) => !isActionCol(h) && !isNoCol(h) && !isHiddenDtCol(dt, i));
  const keyIdxs = dt.dtBi
    .map((_, i) => i)
    .filter((i) => !isActionCol(dt.dtBi[i] ?? "") && !isNoCol(dt.dtBi[i] ?? "") && !isHiddenDtCol(dt, i));
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
  resetPr3038Details();
  wpn2082SelectedCbox.value = "";
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
                @change="onTopFilterFieldChange(fi)"
              >
                <option value="">— All —</option>
                <option
                  v-for="opt in optionsForTopFilter(fi)"
                  :key="'tf-' + fi + '-' + opt.value"
                  :value="opt.value"
                >
                  {{ opt.label }}
                </option>
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
        <template v-if="formBeforeDataTable && menuId !== 1838 && menuId !== 1955 && menuId !== 2618 && menuId !== 3306 && !isNewVariationOrderPage">
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
        <template v-else-if="formBeforeDataTable && menuId === 1955">
          <article
            v-for="grp in formSectionGroups"
            :key="'bank-update-' + grp.title"
            class="rounded-lg border border-slate-200 bg-white shadow-sm"
          >
            <div class="border-b border-slate-100 px-4 py-3">
              <h2 class="text-base font-semibold text-slate-900">{{ grp.title }}</h2>
            </div>
            <div class="grid gap-x-6 gap-y-3 p-4 lg:grid-cols-2">
              <div
                v-for="(f, fi) in grp.fields"
                :key="'bank-update-field-' + fi"
                :class="[
                  f.fieldType === 'dropzone' || f.fieldType === 'textarea' ? 'lg:col-span-2' : '',
                  'grid items-start gap-2 md:grid-cols-[13rem_0.5rem_1fr]',
                ]"
              >
                <label class="pt-2 text-xs font-semibold text-slate-700">{{ f.title }}</label>
                <span class="pt-2">:</span>
                <div v-if="f.fieldType === 'dropzone'" class="rounded-lg border border-dashed border-slate-300 bg-slate-50 p-4 text-xs text-slate-500">
                  <input type="file" class="block w-full text-xs text-slate-500 file:mr-3 file:rounded file:border-0 file:bg-slate-900 file:px-3 file:py-1.5 file:text-xs file:text-white" />
                </div>
                <textarea
                  v-else-if="f.fieldType === 'textarea'"
                  v-model="kerisiFormValues[bankUpdatedFieldKey(f.title)]"
                  rows="3"
                  :disabled="bankUpdatedFieldDisabled(f)"
                  class="rounded border border-slate-300 px-2 py-1 text-xs disabled:bg-slate-100 disabled:text-slate-500"
                />
                <select
                  v-else-if="f.fieldType === 'dropdown'"
                  v-model="kerisiFormValues[bankUpdatedFieldKey(f.title)]"
                  :disabled="bankUpdatedFieldDisabled(f)"
                  class="h-8 rounded border border-slate-300 bg-white px-2 text-xs disabled:bg-slate-100 disabled:text-slate-500"
                >
                  <option value="">— Select —</option>
                  <option
                    v-for="opt in bankUpdatedOptions(f.title)"
                    :key="'bank-update-option-' + f.title + '-' + opt.value"
                    :value="opt.value"
                  >
                    {{ opt.label }}
                  </option>
                </select>
                <input
                  v-else
                  v-model="kerisiFormValues[bankUpdatedFieldKey(f.title)]"
                  :type="f.fieldType === 'date' ? 'date' : 'text'"
                  :disabled="bankUpdatedFieldDisabled(f)"
                  class="h-8 rounded border border-slate-300 px-2 text-xs disabled:bg-slate-100 disabled:text-slate-500"
                />
              </div>
            </div>
          </article>
        </template>
        <template v-else-if="formBeforeDataTable && isNewVariationOrderPage">
          <article class="rounded-lg border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-4 py-3">
              <h2 class="text-base font-semibold text-slate-900">Details</h2>
            </div>
            <div class="grid gap-x-6 gap-y-3 p-4 lg:grid-cols-2">
              <div class="grid items-center gap-2 md:grid-cols-[13rem_0.5rem_1fr]">
                <label class="text-xs font-semibold text-slate-700">VO No <span class="text-red-600">*</span></label>
                <span>:</span>
                <input v-model="kerisiFormValues.agvNo" disabled placeholder="Auto Assigned" class="h-8 rounded border border-slate-200 bg-slate-100 px-2 text-xs text-slate-500" />
              </div>
              <div class="grid items-center gap-2 md:grid-cols-[13rem_0.5rem_1fr]">
                <label class="text-xs font-semibold text-slate-700">Date of Letter <span class="text-red-600">*</span></label>
                <span>:</span>
                <input v-model="kerisiFormValues.agvLetterDate" type="date" class="h-8 rounded border border-slate-300 px-2 text-xs" />
              </div>
              <div class="grid items-center gap-2 md:grid-cols-[13rem_0.5rem_1fr]">
                <label class="text-xs font-semibold text-slate-700">Reference No <span class="text-red-600">*</span></label>
                <span>:</span>
                <input v-model="kerisiFormValues.agvReferenceNo" class="h-8 rounded border border-slate-300 px-2 text-xs" />
              </div>
              <div class="grid items-center gap-2 md:grid-cols-[13rem_0.5rem_1fr]">
                <label class="text-xs font-semibold text-slate-700">Agreement No <span class="text-red-600">*</span></label>
                <span>:</span>
                <select v-model="kerisiFormValues.aggId" class="h-8 rounded border border-slate-300 bg-white px-2 text-xs" @change="onVariationAgreementChange">
                  <option value="">— Select —</option>
                  <option v-for="opt in variationOrderOptions('agreements')" :key="'ag-' + opt.value" :value="opt.value">
                    {{ opt.label }}
                  </option>
                </select>
              </div>
              <div class="hidden">
                <input v-model="kerisiFormValues.aggId" />
                <input v-model="kerisiFormValues.agvDocument" />
                <input v-model="kerisiFormValues.agvId" />
              </div>
              <div class="grid items-center gap-2 md:grid-cols-[13rem_0.5rem_1fr]">
                <label class="text-xs font-semibold text-slate-700">Agreement Amount (RM) <span class="text-red-600">*</span></label>
                <span>:</span>
                <div class="flex">
                  <span class="inline-flex h-8 items-center rounded-l border border-r-0 border-slate-300 bg-slate-100 px-2 text-xs">MYR</span>
                  <input :value="formatMoneyInput(kerisiFormValues.aggAmt)" disabled class="h-8 flex-1 rounded-r border border-slate-200 bg-slate-100 px-2 text-right text-xs text-slate-500" />
                </div>
              </div>
              <div class="grid items-center gap-2 md:grid-cols-[13rem_0.5rem_1fr]">
                <label class="text-xs font-semibold text-slate-700">Agreement Ref</label>
                <span>:</span>
                <input v-model="kerisiFormValues.aggRefDoc" disabled class="h-8 rounded border border-slate-200 bg-slate-100 px-2 text-xs text-slate-500" />
              </div>
              <div class="grid items-center gap-2 md:grid-cols-[13rem_0.5rem_1fr]">
                <label class="text-xs font-semibold text-slate-700">Amount VO (RM) <span class="text-red-600">*</span></label>
                <span>:</span>
                <div class="flex">
                  <span class="inline-flex h-8 items-center rounded-l border border-r-0 border-slate-300 bg-slate-100 px-2 text-xs">MYR</span>
                  <input v-model="kerisiFormValues.agvAmt" class="h-8 flex-1 rounded-r border border-slate-300 px-2 text-right text-xs" />
                </div>
              </div>
              <div class="grid items-start gap-2 lg:col-span-2 md:grid-cols-[13rem_0.5rem_1fr]">
                <label class="pt-2 text-xs font-semibold text-slate-700">Reason <span class="text-red-600">*</span></label>
                <span class="pt-2">:</span>
                <textarea v-model="kerisiFormValues.reason" rows="4" class="rounded border border-slate-300 px-2 py-1 text-xs" />
              </div>
              <div class="grid items-start gap-2 lg:col-span-2 md:grid-cols-[13rem_0.5rem_1fr]">
                <label class="pt-2 text-xs font-semibold text-slate-700">Upload Document</label>
                <span class="pt-2">:</span>
                <div class="flex h-24 items-center justify-center rounded border border-slate-200 bg-slate-50 text-slate-400">
                  <Download class="h-5 w-5" />
                </div>
              </div>
              <div class="grid items-center gap-2 md:grid-cols-[13rem_0.5rem_1fr]">
                <label class="text-xs font-semibold text-slate-700">Status</label>
                <span>:</span>
                <input v-model="kerisiFormValues.agvStatus" disabled placeholder="DRAFT" class="h-8 rounded border border-slate-200 bg-slate-100 px-2 text-xs text-slate-500" />
              </div>
            </div>
          </article>
        </template>
        <template v-else-if="formBeforeDataTable && menuId === 3306">
          <article class="rounded-lg border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-4 py-3">
              <h2 class="text-base font-semibold text-slate-900">Search Tender</h2>
            </div>
            <div class="grid items-center gap-2 p-4 md:grid-cols-[13rem_0.5rem_1fr_auto]">
              <label class="text-xs font-semibold text-slate-700">Tender Number</label>
              <span>:</span>
              <select
                v-model="kerisiFormValues.tdmTenderId"
                class="h-8 rounded border border-slate-300 bg-white px-2 text-xs focus:outline-none focus:ring-2 focus:ring-slate-400"
              >
                <option value="">— Select —</option>
                <option v-for="opt in advertisementOptions('tenderNumbers')" :key="'tn-' + opt.value" :value="opt.value">
                  {{ opt.label }}
                </option>
              </select>
              <button
                type="button"
                class="h-8 rounded bg-violet-500 px-4 text-xs font-medium text-white hover:bg-violet-600"
                @click="applyCommitteeReportFilter"
              >
                Search
              </button>
            </div>
          </article>
        </template>
        <template v-else-if="formBeforeDataTable && menuId === 2618">
          <article class="rounded-lg border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-4 py-3">
              <h2 class="text-base font-semibold text-slate-900">Information</h2>
            </div>
            <div class="grid gap-x-6 gap-y-3 p-4 lg:grid-cols-2">
              <div class="grid items-center gap-2 md:grid-cols-[13rem_0.5rem_1fr]">
                <label class="text-xs font-semibold text-slate-700">Purchase Requisition No <span class="text-red-600">*</span></label><span>:</span>
                <select v-model="kerisiFormValues.rqmRequisitionNo" class="h-8 rounded border border-slate-300 px-2 text-xs">
                  <option value="">— Select —</option>
                  <option v-for="opt in advertisementOptions('purchaseRequisitions')" :key="'pr-' + opt.value" :value="opt.value">{{ opt.label }}</option>
                </select>
              </div>
              <div class="grid items-center gap-2 md:grid-cols-[13rem_0.5rem_1fr]">
                <label class="text-xs font-semibold text-slate-700">Request By <span class="text-red-600">*</span></label><span>:</span>
                <select v-model="kerisiFormValues.tdmRequestby" class="h-8 rounded border border-slate-300 px-2 text-xs">
                  <option value="">— Select —</option>
                  <option v-for="opt in advertisementOptions('requestBy')" :key="'rb-' + opt.value" :value="opt.value">{{ opt.label }}</option>
                </select>
              </div>
              <div class="grid items-center gap-2 md:grid-cols-[13rem_0.5rem_1fr]">
                <label class="text-xs font-semibold text-slate-700">Tender / Quotation No</label><span>:</span>
                <input v-model="kerisiFormValues.tdmTenderNo" disabled placeholder="Auto Assigned" class="h-8 rounded border border-slate-200 bg-slate-100 px-2 text-xs text-slate-500" />
              </div>
              <div class="grid items-center gap-2 md:grid-cols-[13rem_0.5rem_1fr]">
                <label class="text-xs font-semibold text-slate-700">Status</label><span>:</span>
                <input v-model="kerisiFormValues.tdmStatus" disabled placeholder="DRAFT" class="h-8 rounded border border-slate-200 bg-slate-100 px-2 text-xs text-slate-500" />
              </div>
              <div class="grid items-center gap-2 md:grid-cols-[13rem_0.5rem_1fr]">
                <label class="text-xs font-semibold text-slate-700">Request Date <span class="text-red-600">*</span></label><span>:</span>
                <input v-model="kerisiFormValues.tdmRequestdate" type="date" class="h-8 rounded border border-slate-300 px-2 text-xs" />
              </div>
              <div class="grid items-center gap-2 md:grid-cols-[13rem_0.5rem_1fr]">
                <label class="text-xs font-semibold text-slate-700">Tender type <span class="text-red-600">*</span></label><span>:</span>
                <select v-model="kerisiFormValues.tdmTenderType" class="h-8 rounded border border-slate-300 px-2 text-xs">
                  <option value="">— Select —</option>
                  <option v-for="opt in advertisementOptions('tenderTypes')" :key="'tt-' + opt.value" :value="opt.value">{{ opt.label }}</option>
                </select>
              </div>
              <div class="grid items-center gap-2 md:grid-cols-[13rem_0.5rem_1fr]">
                <label class="text-xs font-semibold text-slate-700">Requisition Method</label><span>:</span>
                <select v-model="kerisiFormValues.tdmTenderMethod" class="h-8 rounded border border-slate-300 px-2 text-xs">
                  <option value="">— Select —</option>
                  <option v-for="opt in advertisementOptions('tenderMethods')" :key="'tm-' + opt.value" :value="opt.value">{{ opt.label }}</option>
                </select>
              </div>
              <div class="hidden lg:block" />
              <div class="grid items-center gap-2 lg:col-span-2 md:grid-cols-[13rem_0.5rem_1fr]">
                <label class="text-xs font-semibold text-slate-700">Title <span class="text-red-600">*</span> <span class="text-red-600">(max length: 4000)</span></label><span>:</span>
                <input v-model="kerisiFormValues.tdmTitle" maxlength="4000" class="h-8 rounded border border-slate-300 px-2 text-xs uppercase" />
              </div>
              <div class="grid items-center gap-2 lg:col-span-2 md:grid-cols-[13rem_0.5rem_1fr]">
                <label class="text-xs font-semibold text-slate-700">Justification <span class="text-red-600">*</span> <span class="text-red-600">(max length: 4000)</span></label><span>:</span>
                <input v-model="kerisiFormValues.tdmJustification" maxlength="4000" class="h-8 rounded border border-slate-300 px-2 text-xs" />
              </div>
              <div class="grid items-center gap-2 lg:col-span-2 md:grid-cols-[13rem_0.5rem_1fr]">
                <label class="text-xs font-semibold text-slate-700">PTJ <span class="text-red-600">*</span></label><span>:</span>
                <select v-model="kerisiFormValues.ounCode" class="h-8 rounded border border-slate-300 px-2 text-xs">
                  <option value="">— Select —</option>
                  <option v-for="opt in advertisementOptions('ptj')" :key="'ptj-' + opt.value" :value="opt.value">{{ opt.label }}</option>
                </select>
              </div>
              <div class="grid items-center gap-2 lg:col-span-2 md:grid-cols-[13rem_0.5rem_1fr]">
                <label class="text-xs font-semibold text-slate-700">Costcentre <span class="text-red-600">*</span></label><span>:</span>
                <select v-model="kerisiFormValues.ccrCostcentre" class="h-8 rounded border border-slate-300 px-2 text-xs">
                  <option value="">— Select —</option>
                  <option v-for="opt in advertisementOptions('costCentres').filter((o) => !kerisiFormValues.ounCode || !o.ounCode || o.ounCode === kerisiFormValues.ounCode)" :key="'cc-' + opt.value" :value="opt.value">{{ opt.label }}</option>
                </select>
              </div>
              <div class="grid items-center gap-2 lg:col-span-2 md:grid-cols-[13rem_0.5rem_1fr]">
                <label class="text-xs font-semibold text-slate-700">Fund <span class="text-red-600">*</span></label><span>:</span>
                <select v-model="kerisiFormValues.ftyFundType" class="h-8 rounded border border-slate-300 px-2 text-xs">
                  <option value="">— Select —</option>
                  <option v-for="opt in advertisementOptions('fundTypes')" :key="'fund-' + opt.value" :value="opt.value">{{ opt.label }}</option>
                </select>
              </div>
              <div class="grid items-center gap-2 lg:col-span-2 md:grid-cols-[13rem_0.5rem_1fr]">
                <label class="text-xs font-semibold text-slate-700">Activity <span class="text-red-600">*</span></label><span>:</span>
                <select v-model="kerisiFormValues.atActivityCode" class="h-8 rounded border border-slate-300 px-2 text-xs">
                  <option value="">— Select —</option>
                  <option v-for="opt in advertisementOptions('activities')" :key="'act-' + opt.value" :value="opt.value">{{ opt.label }}</option>
                </select>
              </div>
              <div class="grid items-center gap-2 lg:col-span-2 md:grid-cols-[13rem_0.5rem_1fr]">
                <label class="text-xs font-semibold text-slate-700">Code SO</label><span>:</span>
                <select v-model="kerisiFormValues.soCode" class="h-8 rounded border border-slate-300 px-2 text-xs">
                  <option value="">— Select —</option>
                  <option v-for="opt in advertisementOptions('soCodes')" :key="'so-' + opt.value" :value="opt.value">{{ opt.label }}</option>
                </select>
              </div>
              <div class="grid items-start gap-2 lg:col-span-2 md:grid-cols-[13rem_0.5rem_1fr]">
                <label class="pt-2 text-xs font-semibold text-slate-700">Quotation/Tender Delivery Address</label><span class="pt-2">:</span>
                <textarea v-model="kerisiFormValues.tdmAddress" rows="3" class="rounded border border-slate-300 px-2 py-1 text-xs" />
              </div>
              <div class="grid items-center gap-2 lg:col-span-2 md:grid-cols-[13rem_0.5rem_1fr]">
                <label class="text-xs font-semibold text-slate-700">Estimate Siap</label><span>:</span>
                <input v-model="kerisiFormValues.tbrEstimateDuration" class="h-8 rounded border border-slate-300 px-2 text-xs" />
              </div>
              <div class="grid items-center gap-2 lg:col-span-2 md:grid-cols-[13rem_0.5rem_1fr]">
                <label class="text-xs font-semibold text-slate-700">Contact Person</label><span>:</span>
                <input v-model="kerisiFormValues.tdmContactPerson" disabled class="h-8 rounded border border-slate-200 bg-slate-100 px-2 text-xs text-slate-500" />
              </div>
              <div class="grid items-center gap-2 lg:col-span-2 md:grid-cols-[13rem_0.5rem_1fr]">
                <label class="text-xs font-semibold text-slate-700">Reference No</label><span>:</span>
                <input v-model="kerisiFormValues.tdmBriefingRefNo" placeholder="Auto Assigned" class="h-8 rounded border border-slate-300 px-2 text-xs" />
              </div>
              <div class="grid items-center gap-2 lg:col-span-2 md:grid-cols-[13rem_0.5rem_1fr]">
                <label class="text-xs font-semibold text-slate-700">Estimate Amount (RM) <span class="text-red-600">*</span></label><span>:</span>
                <div class="flex"><span class="inline-flex h-8 items-center rounded-l border border-r-0 border-slate-300 bg-slate-100 px-2 text-xs">MYR</span><input v-model="kerisiFormValues.tdmEstimatedAmount" class="h-8 flex-1 rounded-r border border-slate-300 px-2 text-right text-xs" /></div>
              </div>
              <div class="grid items-start gap-2 md:grid-cols-[13rem_0.5rem_1fr]">
                <label class="pt-2 text-xs font-semibold text-slate-700">Advertisement Document</label><span class="pt-2">:</span>
                <div class="flex h-20 items-center justify-center rounded border border-slate-200 bg-slate-50 text-slate-400"><Download class="h-5 w-5" /></div>
              </div>
              <div class="grid items-start gap-2 md:grid-cols-[13rem_0.5rem_1fr]">
                <label class="pt-2 text-xs font-semibold text-slate-700">Tender/Quotation Document Only <span class="text-red-600">*</span></label><span class="pt-2">:</span>
                <div class="flex h-20 items-center justify-center rounded border border-slate-200 bg-slate-50 text-slate-400"><Download class="h-5 w-5" /></div>
              </div>
              <div class="grid items-center gap-2 md:grid-cols-[13rem_0.5rem_1fr]">
                <label class="text-xs font-semibold text-slate-700">Tarikh Taklimat Tender</label><span>:</span>
                <input v-model="kerisiFormValues.tdmTenderOpenStart" type="date" class="h-8 rounded border border-slate-300 px-2 text-xs" />
              </div>
              <div class="grid items-center gap-2 md:grid-cols-[13rem_0.5rem_1fr]">
                <label class="text-xs font-semibold text-slate-700">Tarikh Tutup Peti</label><span>:</span>
                <input v-model="kerisiFormValues.tdmTenderOpenClose" type="date" class="h-8 rounded border border-slate-300 px-2 text-xs" />
              </div>
              <div class="grid items-center gap-2 md:grid-cols-[13rem_0.5rem_1fr]">
                <label class="text-xs font-semibold text-slate-700">Amount Document Price(RM) <span class="text-red-600">*</span> <span class="text-red-600">(min: 0)</span></label><span>:</span>
                <div class="flex"><span class="inline-flex h-8 items-center rounded-l border border-r-0 border-slate-300 bg-slate-100 px-2 text-xs">MYR</span><input v-model="kerisiFormValues.tdmAmountDoc" class="h-8 flex-1 rounded-r border border-slate-300 px-2 text-right text-xs" /></div>
              </div>
            </div>
          </article>
        </template>
        <template v-else-if="formBeforeDataTable && menuId === 1838">
          <article class="rounded-lg border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-4 py-3">
              <h2 class="text-base font-semibold text-slate-900">WPN Information</h2>
            </div>
            <div class="grid gap-3 p-4 sm:grid-cols-2">
              <div
                v-for="(f, fi) in spec?.formSections ?? []"
                v-show="(!f.componentTitle || f.componentTitle === 'WPN Information') && !kerisiFormFieldHidden(f)"
                :key="'wpn-fs-' + fi"
                :class="f.fieldType === 'textarea' ? 'sm:col-span-2' : ''"
              >
                <label class="mb-1 block text-xs font-medium text-slate-600">{{ f.title }}</label>
                <select
                  v-if="(f.title ?? '').toLowerCase().includes('wpn type')"
                  v-model="kerisiFormValues.wpmType"
                  disabled
                  class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700"
                >
                  <option value="">— Select —</option>
                  <option v-for="opt in wpnDropdownOptions('wpn_type')" :key="'wt-' + fi + '-' + opt.value" :value="opt.value">
                    {{ opt.label }}
                  </option>
                </select>
                <select
                  v-else-if="(f.title ?? '').toLowerCase().includes('po no') || (f.title ?? '').toLowerCase().includes('pr no')"
                  v-model="kerisiFormValues.pomOrderNo"
                  disabled
                  class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700"
                >
                  <option value="">— Select —</option>
                  <option v-for="opt in wpnDropdownOptions('po_pr_no')" :key="'po-' + fi + '-' + opt.value" :value="opt.value">
                    {{ opt.label }}
                  </option>
                </select>
                <select
                  v-else-if="(f.title ?? '').toLowerCase().includes('vendor code')"
                  v-model="kerisiFormValues.vcsVendorCode"
                  disabled
                  class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700"
                >
                  <option value="">— Select —</option>
                  <option v-for="opt in wpnDropdownOptions('vendor')" :key="'v-' + fi + '-' + opt.value" :value="opt.value">
                    {{ opt.label }}
                  </option>
                </select>
                <select
                  v-else-if="(f.title ?? '').toLowerCase().includes('currency')"
                  v-model="kerisiFormValues.wpmCurrencyCode"
                  disabled
                  class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700"
                >
                  <option value="">— Select —</option>
                  <option v-for="opt in wpnDropdownOptions('currency')" :key="'c-' + fi + '-' + opt.value" :value="opt.value">
                    {{ opt.label }}
                  </option>
                </select>
                <textarea
                  v-else-if="(f.title ?? '').toLowerCase().includes('po description')"
                  v-model="kerisiFormValues.pomDescription"
                  rows="3"
                  disabled
                  class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700"
                />
                <input
                  v-else-if="f.fieldType === 'date'"
                  v-model="kerisiFormValues.wpmReceiveDate"
                  disabled
                  type="date"
                  class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700"
                />
                <input
                  v-else-if="(f.title ?? '').toLowerCase().includes('wpn no')"
                  v-model="kerisiFormValues.wpmProgressNo"
                  disabled
                  type="text"
                  placeholder="Auto Assigned"
                  class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700"
                />
                <input
                  v-else-if="(f.title ?? '').toLowerCase().includes('vendor name')"
                  v-model="kerisiFormValues.vcsVendorName"
                  disabled
                  type="text"
                  class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700"
                />
                <input
                  v-else-if="(f.title ?? '').toLowerCase().includes('do no')"
                  v-model="kerisiFormValues.wpmReferenceDoc"
                  disabled
                  type="text"
                  class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700"
                />
                <input
                  v-else-if="(f.title ?? '').trim().toLowerCase() === 'status'"
                  v-model="kerisiFormValues.wpmStatus"
                  disabled
                  type="text"
                  placeholder="DRAFT"
                  class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700"
                />
                <input v-else disabled type="text" class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-500" />
              </div>
            </div>
          </article>
          <article class="mt-4 rounded-lg border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-4 py-3">
              <h2 class="text-base font-semibold text-slate-900">Remarks</h2>
            </div>
            <div class="p-4">
              <label class="mb-1 block text-xs font-medium text-slate-600">Remarks</label>
              <textarea
                v-model="kerisiFormValues.wpmCancelRemark"
                rows="3"
                disabled
                class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700"
              />
            </div>
          </article>
        </template>

        <!-- Datatable sections -->
        <section
          v-for="(dt, di) in spec?.datatables ?? []"
          v-show="showDetailSection(di)"
          :key="dt.componentId + '-' + di"
          class="rounded-lg border border-slate-200 bg-white shadow-sm"
        >
          <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
            <h2 class="text-base font-semibold text-slate-900">
              {{ dt.componentTitle || "Data" }}
            </h2>
            <div v-if="di === 0 || menuId !== 3038" class="flex items-center gap-2">
              <!-- Add button (only on popup-modal pages or default) -->
              <button
                v-if="menuId === 1955 || menuId === 3306 || menuId === 2846 || menuId === 3320 || isTenderQuotationPage ? false : menuId === 2618 ? di < 2 : hasPopupForm || di === 0"
                type="button"
                class="inline-flex items-center gap-1.5 rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-medium text-white hover:bg-slate-700"
                @click="onAddPrimaryClick(di)"
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
          <template v-if="di === 0 || menuId === 2624 || menuId === 2626">
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
                  v-if="showSmartFilterUi && di === 0"
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
              <table class="w-full text-sm">
                <thead>
                  <tr
                    class="border-b border-slate-200"
                    :class="
                      kerisiPurchasingPurpleShell
                        ? 'border-violet-700 bg-violet-600'
                        : menuId === 2082
                          ? 'bg-violet-100'
                          : 'bg-slate-50'
                    "
                  >
                    <th
                      v-for="hi in visibleColIndices(dt)"
                      :key="'h-' + dt.componentId + '-' + hi"
                      :class="[
                        'px-3 py-2 text-left text-xs font-semibold tracking-wide',
                        kerisiPurchasingPurpleShell
                          ? 'normal-case text-white'
                          : menuId === 2082
                            ? 'normal-case text-violet-900'
                            : 'uppercase text-slate-500',
                      ]"
                    >
                      <input
                        v-if="menuId === 3306 && isActionCol(dt.dtBi[hi] ?? '')"
                        type="checkbox"
                        class="h-4 w-4 rounded border-slate-300 text-violet-600 focus:ring-violet-500"
                        aria-label="Select all submissions"
                      />
                      <template v-else>
                        {{ isNoCol(dt.dtBi[hi] ?? "") ? "No" : stripHtmlBrLabel(dt.dtBi[hi]) }}
                      </template>
                    </th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-if="shellTableLoading(di)">
                    <td :colspan="tableColspan(dt)" class="px-3 py-6 text-center text-sm text-slate-400">
                      Loading…
                    </td>
                  </tr>
                  <tr v-else-if="!shellTableLoading(di) && shellRows(di).length === 0">
                    <td :colspan="tableColspan(dt)" class="px-3 py-6 text-center text-sm text-slate-400">
                      No records.
                    </td>
                  </tr>
                  <tr
                    v-else
                    v-for="(row, ri) in shellRows(di)"
                    :key="ri"
                    class="border-b border-slate-100 hover:bg-slate-50"
                    :class="[
                      menuId === 3038 && di === 0 ? 'cursor-pointer' : '',
                      kerisiPurchasingPurpleShell && ri % 2 === 1 ? 'bg-slate-50/90' : '',
                    ]"
                    @click="onShellMasterRowClick(di, row)"
                  >
                    <td
                      v-for="hi in visibleColIndices(dt)"
                      :key="'c-' + ri + '-' + hi"
                      class="px-3 py-2 text-slate-700"
                      :class="tableNumericColClass(dt, hi)"
                    >
                      <template v-if="isNoCol(dt.dtBi[hi] ?? '')">
                        {{
                          (menuId === 3038 && di > 0) ||
                          (menuId === 1838 && di > 0) ||
                          ((menuId === 1955 || menuId === 2624 || menuId === 2626 || menuId === 2618) && di > 0)
                            ? ri + 1
                            : (page - 1) * limit + ri + 1
                        }}
                      </template>
                      <template v-else-if="isActionCol(dt.dtBi[hi] ?? '')">
                        <!-- Row click navigates to 3039; stop bubble so Details only loads the lower grid -->
                        <div v-if="menuId === 3038 && di === 0" class="flex items-center gap-1" @click.stop>
                          <button
                            type="button"
                            class="rounded p-1 text-slate-500 hover:bg-slate-100 hover:text-slate-800"
                            title="Open Details"
                            @click="openPr3038Details(row)"
                          >
                            <Info class="h-3.5 w-3.5" />
                          </button>
                        </div>
                        <div
                          v-else-if="menuId === 2082 && di === 0"
                          class="flex items-center gap-2"
                          @click.stop
                        >
                          <button
                            type="button"
                            class="rounded p-1 text-slate-500 hover:bg-slate-100 hover:text-slate-800"
                            title="View"
                            @click="openWpnDetail2082(row)"
                          >
                            <Eye class="h-3.5 w-3.5" />
                          </button>
                          <input
                            type="checkbox"
                            class="h-4 w-4 rounded border-slate-300 text-violet-600 focus:ring-violet-500"
                            aria-label="Select for WPN Cancel"
                            :checked="wpn2082SelectedCbox === String(row.cbox ?? '')"
                            @change="onWpn2082CheckboxChange(row, ($event.target as HTMLInputElement).checked)"
                            @click.stop
                          />
                        </div>
                        <div
                          v-else-if="menuId === 2085 && di === 0"
                          class="flex items-center gap-2"
                          @click.stop
                        >
                          <button
                            type="button"
                            class="rounded p-1 text-slate-500 hover:bg-slate-100 hover:text-slate-800"
                            title="View"
                            @click="openGrnCancel2085Eye(row)"
                          >
                            <Eye class="h-3.5 w-3.5" />
                          </button>
                          <input
                            type="checkbox"
                            class="h-4 w-4 rounded border-slate-300 text-violet-600 focus:ring-violet-500"
                            aria-label="Select for GRN Cancel"
                            :checked="grn2085SelectedCbox === String(row.cbox ?? '')"
                            @change="onGrn2085CheckboxChange(row, ($event.target as HTMLInputElement).checked)"
                            @click.stop
                          />
                        </div>
                        <div v-else-if="menuId === 1839 && di === 0" class="flex items-center gap-0.5" @click.stop>
                          <button
                            type="button"
                            class="rounded p-1 text-slate-400"
                            disabled
                            title="Print (not wired)"
                          >
                            <FileDown class="h-3.5 w-3.5 opacity-50" />
                          </button>
                          <button
                            type="button"
                            class="rounded p-1 text-slate-500 hover:bg-slate-100 hover:text-slate-800 disabled:opacity-40"
                            title="Edit"
                            :disabled="String(row.grm_status ?? row.grmStatus ?? '').toUpperCase() !== 'DRAFT'"
                            @click="openKerisiGrn1858(row)"
                          >
                            <Pencil class="h-3.5 w-3.5" />
                          </button>
                          <button
                            type="button"
                            class="rounded p-1 text-slate-500 hover:bg-slate-100 hover:text-slate-800"
                            title="View"
                            @click="openKerisiGrn1858(row)"
                          >
                            <Eye class="h-3.5 w-3.5" />
                          </button>
                          <button
                            type="button"
                            class="rounded p-1 text-red-400 hover:bg-red-50 hover:text-red-600 disabled:opacity-40"
                            title="Delete"
                            disabled
                          >
                            <Trash2 class="h-3.5 w-3.5" />
                          </button>
                        </div>
                        <div
                          v-else-if="(menuId === 2624 || menuId === 2626) && di >= 0"
                          class="flex items-center gap-1"
                          @click.stop
                        >
                          <button
                            type="button"
                            class="rounded p-1 text-slate-600 hover:bg-slate-100"
                            title="View"
                            @click="menuId === 2624 ? openKerisiGrn1858(row) : openKerisiWpn1838(row)"
                          >
                            <Eye class="h-3.5 w-3.5" />
                          </button>
                          <button
                            type="button"
                            class="rounded p-1 text-slate-600 hover:bg-slate-100"
                            title="New vendor assessment"
                            @click="
                              toast.info(
                                'Vendor Assessment',
                                'Assessment workflow is opened from legacy after GRN/WPN linking.',
                              )
                            "
                          >
                            <Plus class="h-3.5 w-3.5" />
                          </button>
                        </div>
                        <div v-else-if="menuId === 2618 && di < 2" class="flex items-center gap-1" @click.stop>
                          <button
                            type="button"
                            class="rounded p-1 text-slate-500 hover:bg-slate-100 hover:text-slate-800"
                            title="Edit"
                            @click="openEditModal(row, di === 0 ? 'jobscope' : 'taraf')"
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
                        <div v-else-if="menuId === 3306" class="flex items-center justify-center" @click.stop>
                          <input
                            type="checkbox"
                            class="h-4 w-4 rounded border-slate-300 text-violet-600 focus:ring-violet-500"
                            :value="String(row.tas_cust_id ?? row.tasCustId ?? '')"
                            aria-label="Select submission"
                          />
                        </div>
                        <div v-else class="flex items-center gap-1" @click.stop>
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
                <tfoot v-if="di === 0 && menuId === 1833 && grandTotalPoAmtRm != null">
                  <tr class="border-t-2 border-violet-600 bg-violet-100 font-semibold text-slate-900">
                    <td class="px-3 py-2 text-left uppercase tracking-wide text-violet-950" :colspan="poGrandTotalColSpans(dt).label">
                      Grand Total
                    </td>
                    <td
                      class="px-3 py-2 text-right tabular-nums text-violet-950"
                      :colspan="poGrandTotalColSpans(dt).amount"
                    >
                      {{ formatGrandPoTotal(grandTotalPoAmtRm) }}
                    </td>
                    <td v-if="poGrandTotalColSpans(dt).tail > 0" :colspan="poGrandTotalColSpans(dt).tail" class="px-3 py-2" />
                  </tr>
                </tfoot>
              </table>
            </div>

            <div
              v-if="di === 1 && (menuId === 2624 || menuId === 2626)"
              class="flex justify-between border-t border-slate-100 pt-3 text-xs text-slate-500"
            >
              <span>{{ secondaryGridTotal === 0 ? "No records" : `${secondaryGridTotal} records` }}</span>
            </div>

            <!-- Pagination (primary datatable only) -->
            <template v-if="di === 0">
              <div class="flex items-center justify-between pt-1">
                <div class="flex items-center gap-2 text-xs text-slate-500">
                  <span>{{ menuId === 2082 || menuId === 2085 ? "Display" : "Show" }}</span>
                  <select
                    v-model="limit"
                    class="rounded border border-slate-300 px-2 py-1 text-xs"
                    @change="onLimitChange"
                  >
                    <option v-for="n in [5, 10, 25, 50, 100]" :key="n" :value="n">{{ n }}</option>
                  </select>
                  <span>{{ menuId === 2082 || menuId === 2085 ? "records" : "entries" }}</span>
                  <span v-if="menuId === 2082 || menuId === 2085" class="ml-4">
                    {{ total === 0 ? "No records" : `${total} record${total === 1 ? "" : "s"}` }}
                  </span>
                  <span v-else class="ml-4">
                    {{
                      total === 0 ? "No records" : `${(page - 1) * limit + 1}–${Math.min(page * limit, total)} of ${total}`
                    }}
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
              <div v-if="menuId === 2082" class="flex justify-end pt-3">
                <button
                  type="button"
                  class="rounded-lg bg-violet-600 px-5 py-2 text-sm font-medium text-white shadow-sm hover:bg-violet-700 disabled:opacity-50"
                  :disabled="!wpn2082SelectedCbox"
                  @click="submitWpnCancel2082"
                >
                  WPN Cancel
                </button>
              </div>
              <div v-if="menuId === 2085" class="flex justify-end pt-3">
                <button
                  type="button"
                  class="inline-flex items-center gap-2 rounded-lg bg-violet-600 px-5 py-2 text-sm font-medium text-white shadow-sm hover:bg-violet-700 disabled:opacity-50"
                  :disabled="!grn2085SelectedCbox"
                  @click="submitGrnCancel2085Placeholder"
                >
                  <X class="h-4 w-4" />
                  GRN Cancel
                </button>
              </div>
            </template>
          </div>
        </section>

        <template v-if="isNewVariationOrderPage">
          <article class="rounded-lg border border-slate-200 bg-white shadow-sm">
            <div class="grid gap-3 p-4 lg:grid-cols-2">
              <div class="grid items-center gap-2 md:grid-cols-[13rem_0.5rem_1fr]">
                <label class="text-xs font-semibold text-slate-700">Status</label>
                <span>:</span>
                <select v-model="kerisiFormValues.workflowStatus" class="h-8 rounded border border-slate-300 bg-white px-2 text-xs">
                  <option value="">— Select —</option>
                  <option v-for="opt in variationOrderOptions('statusOptions')" :key="'vos-' + opt.value" :value="opt.value">
                    {{ opt.label }}
                  </option>
                </select>
              </div>
              <div class="grid items-start gap-2 md:grid-cols-[13rem_0.5rem_1fr]">
                <label class="pt-2 text-xs font-semibold text-slate-700">Remarks</label>
                <span class="pt-2">:</span>
                <textarea v-model="kerisiFormValues.workflowRemarks" rows="1" class="rounded border border-slate-300 px-2 py-1 text-xs" />
              </div>
            </div>
          </article>

          <article class="rounded-lg border border-slate-200 bg-white shadow-sm">
            <div class="grid items-center gap-2 p-4 md:grid-cols-[13rem_0.5rem_1fr]">
              <label class="text-xs font-semibold text-slate-700">Next Receiver <span class="text-red-600">*</span></label>
              <span>:</span>
              <select v-model="kerisiFormValues.nextReceiver" class="h-8 rounded border border-slate-300 bg-white px-2 text-xs">
                <option value="">— Select —</option>
                <option v-for="opt in variationOrderOptions('nextReceivers')" :key="'vonr-' + opt.value" :value="opt.value">
                  {{ opt.label }}
                </option>
              </select>
            </div>
          </article>

          <div class="flex justify-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-3 shadow-sm">
            <button type="button" class="inline-flex items-center gap-1 rounded bg-violet-500 px-3 py-1.5 text-xs font-medium text-white hover:bg-violet-600">
              <Download class="h-3 w-3" />
              Save
            </button>
            <button type="button" class="inline-flex items-center gap-1 rounded bg-violet-500 px-3 py-1.5 text-xs font-medium text-white hover:bg-violet-600">
              <Download class="h-3 w-3" />
              Save &amp; Submit
            </button>
          </div>
        </template>

        <template v-if="menuId === 2618">
          <article class="rounded-lg border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
              <h2 class="text-base font-semibold text-slate-900">Attachment A: Technical Info</h2>
              <div class="flex items-center gap-2 text-slate-600">
                <span class="text-sm font-semibold">#</span>
                <button type="button" class="rounded p-1 hover:bg-slate-100" title="Copy">
                  <Download class="h-3.5 w-3.5" />
                </button>
                <button type="button" class="rounded p-1 hover:bg-slate-100" title="Edit">
                  <Pencil class="h-3.5 w-3.5" />
                </button>
                <button type="button" class="rounded p-1 hover:bg-slate-100" title="Collapse">
                  <ChevronLeft class="h-3.5 w-3.5 -rotate-90" />
                </button>
              </div>
            </div>
            <div class="p-4">
              <table class="w-full text-xs">
                <thead>
                  <tr class="bg-violet-500 text-white">
                    <th class="px-3 py-2 text-left font-semibold">Description</th>
                    <th class="px-3 py-2 text-left font-semibold">Sample Answer</th>
                  </tr>
                </thead>
                <tbody>
                  <tr class="border-b border-slate-100">
                    <td colspan="2" class="px-3 py-3 text-slate-700">
                      To start, click "Sample Data" or "Copy From"
                    </td>
                  </tr>
                </tbody>
              </table>
              <div class="mt-3 flex justify-end gap-2">
                <button type="button" class="inline-flex items-center gap-1 rounded bg-violet-500 px-3 py-1.5 text-xs font-medium text-white hover:bg-violet-600">
                  <Pencil class="h-3 w-3" />
                  Sample Data
                </button>
                <button type="button" class="inline-flex items-center gap-1 rounded bg-violet-500 px-3 py-1.5 text-xs font-medium text-white hover:bg-violet-600">
                  <Download class="h-3 w-3" />
                  Copy From
                </button>
              </div>
            </div>
          </article>

          <article class="rounded-lg border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
              <h2 class="text-base font-semibold text-slate-900">Attachment B: Financial Info</h2>
              <div class="flex items-center gap-2 text-slate-600">
                <span class="text-sm font-semibold">#</span>
                <button type="button" class="rounded p-1 hover:bg-slate-100" title="Copy">
                  <Download class="h-3.5 w-3.5" />
                </button>
                <button type="button" class="rounded p-1 hover:bg-slate-100" title="Edit">
                  <Pencil class="h-3.5 w-3.5" />
                </button>
                <button type="button" class="rounded p-1 hover:bg-slate-100" title="Collapse">
                  <ChevronLeft class="h-3.5 w-3.5 -rotate-90" />
                </button>
              </div>
            </div>
            <div class="p-4">
              <table class="w-full text-xs">
                <thead>
                  <tr class="bg-violet-500 text-white">
                    <th class="px-3 py-2 text-left font-semibold">Description</th>
                    <th class="px-3 py-2 text-left font-semibold">Sample Answer</th>
                  </tr>
                </thead>
                <tbody>
                  <tr class="border-b border-slate-100">
                    <td colspan="2" class="px-3 py-3 text-slate-700">
                      To start, click "Sample Data" or "Copy From"
                    </td>
                  </tr>
                </tbody>
              </table>
              <div class="mt-3 flex justify-end gap-2">
                <button type="button" class="inline-flex items-center gap-1 rounded bg-violet-500 px-3 py-1.5 text-xs font-medium text-white hover:bg-violet-600">
                  <Pencil class="h-3 w-3" />
                  Sample Data
                </button>
                <button type="button" class="inline-flex items-center gap-1 rounded bg-violet-500 px-3 py-1.5 text-xs font-medium text-white hover:bg-violet-600">
                  <Download class="h-3 w-3" />
                  Copy From
                </button>
              </div>
            </div>
          </article>

          <article class="rounded-lg border border-slate-200 bg-white shadow-sm">
            <div class="grid items-center gap-2 p-4 md:grid-cols-[13rem_0.5rem_1fr]">
              <label class="text-xs font-semibold text-slate-700">Next Receiver <span class="text-red-600">*</span></label>
              <span>:</span>
              <select v-model="kerisiFormValues.nextReceiver" class="h-8 rounded border border-slate-300 px-2 text-xs">
                <option value="">— Select —</option>
                <option v-for="opt in advertisementOptions('nextReceivers')" :key="'nr-' + opt.value" :value="opt.value">
                  {{ opt.label }}
                </option>
              </select>
            </div>
          </article>

          <div class="flex justify-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-3 shadow-sm">
            <button type="button" class="inline-flex items-center gap-1 rounded bg-violet-500 px-3 py-1.5 text-xs font-medium text-white hover:bg-violet-600">
              <Download class="h-3 w-3" />
              Save
            </button>
            <button type="button" class="inline-flex items-center gap-1 rounded bg-violet-500 px-3 py-1.5 text-xs font-medium text-white hover:bg-violet-600">
              <Download class="h-3 w-3" />
              Save &amp; Submit
            </button>
          </div>
        </template>

        <!-- Form sections AFTER datatable -->
        <template v-if="!formBeforeDataTable && menuId !== 2845">
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
                <option
                  v-for="opt in optionsForSmartFilter(fi)"
                  :key="'sf-' + fi + '-' + opt.value"
                  :value="opt.value"
                >
                  {{ opt.label }}
                </option>
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
              {{
                menuId === 2618
                  ? popupContext === "taraf"
                    ? "Taraf"
                    : "Jobscope"
                  : `${modalMode === "add" ? "Add" : "Edit"} ${spec?.pageTitle ?? "Record"}`
              }}
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
              v-for="(f, fi) in advertisementPopupFields()"
              :key="'pf-' + fi"
              :class="f.cssClass?.includes('d-none') ? 'hidden' : ''"
            >
              <label class="mb-1 block text-xs font-medium text-slate-600">{{ f.title }}</label>
              <select
                v-if="f.fieldType === 'dropdown' || f.lookupQuery"
                v-model="popupFormValues[menuId === 2618 ? advertisementPopupModelKey(f) : `pf_${fi}`]"
                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400"
                :disabled="f.isDisabled"
              >
                <option value="">— Select —</option>
                <option
                  v-for="opt in optionsForAdvertisementPopupField(f)"
                  :key="'pfo-' + advertisementPopupModelKey(f) + '-' + opt.value"
                  :value="opt.value"
                >
                  {{ opt.label }}
                </option>
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
              @click="menuId === 2618 ? saveAdvertisementPopup() : (showPopupModal = false)"
            >
              Save
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </AdminLayout>
</template>
