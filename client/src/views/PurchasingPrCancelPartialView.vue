<script setup lang="ts">
/**
 * Purchasing / Purchase Requisition / Purchase Requisition Cancel Partial (MENUID 3042).
 * Legacy-aligned: Information + Details + List of Existing PR in PO/WPN/GRN/BILL + Reason + Submit.
 *
 * Opens with `?rqm_requisition_id=<id>` (deep link from lists).
 */
import { computed, nextTick, ref, watch } from "vue";
import { useRoute } from "vue-router";
import AdminLayout from "@/layouts/AdminLayout.vue";
import {
  getPurchasingPurchaseRequisition,
  listPurchasingPrPartialExistingDocs,
  purchasingPurchaseRequisitionCostCentres,
  purchasingPurchaseRequisitionOptions,
  updatePurchasingPrCancel,
  type PurchasingPrDropdownRow,
  type PurchasingPrOptionsPayload,
} from "@/api/cms";
import { getKerisiMenuTrailByMenuId } from "@/config/kerisi-menu-resolve";
import { useToast } from "@/composables/useToast";

const MENU_ID = 3042;
const toast = useToast();
const route = useRoute();

const pageHeading = computed(() => {
  const trail = getKerisiMenuTrailByMenuId(MENU_ID);
  return trail?.length ? trail.join(" / ") : "Purchasing / Purchase Requisition / Purchase Requisition Cancel Partial";
});

const rqmId = computed(() => {
  const raw = route.query.rqm_requisition_id ?? route.query.rqmRequisitionId ?? route.query.id;
  const n = typeof raw === "string" ? Number(raw) : Array.isArray(raw) ? Number(raw[0]) : NaN;
  return Number.isFinite(n) && n > 0 ? n : 0;
});

const opts = ref<PurchasingPrOptionsPayload | null>(null);
const loading = ref(true);
const saving = ref(false);
const partialLoading = ref(false);
const partialRows = ref<Record<string, unknown>[]>([]);
const detailsOpen = ref(false);
const existingOpen = ref(false);

const ccOptionsOverride = ref<PurchasingPrDropdownRow[] | null>(null);
const skipOuWatcher = ref(true);

type FormShape = {
  rqmRequisitionNo: string;
  rqmStatus: string;
  rqmRequestBy: string;
  rqmRequestDate: string;
  rqmRequisitionTitle: string;
  rqmTenderScope: string;
  ounCode: string;
  ccrCostcentre: string;
  ftyFundType: string;
  atActivityCode: string;
  soCode: string;
  rqmContactPerson: string;
  rqmEntAmt: string;
  rqmAmount: string;
  rqmRateDate: string;
  rqmCurrencyCode: string;
  rqmRateType: string;
  rqmCurrencyUnit: string;
  rqmConversionRate: string;
  rqmRefNo: string;
  rqmJenisTender: string;
  rqmDocReceiveDate: string;
  rqmTenderType: string;
  rqmQuotationReceive: string;
  rqmCancelRemark: string;
  rqmPayeeCode: string;
  pprRequisitionId: number | null;
  /** Resolved `id — name`; prefer for read-only Request By display. */
  rqmRequestByDisplay: string;
};

const blankForm = (): FormShape => ({
  rqmRequisitionNo: "",
  rqmStatus: "DRAFT",
  rqmRequestBy: "",
  rqmRequestDate: new Date().toISOString().slice(0, 10),
  rqmRequisitionTitle: "",
  rqmTenderScope: "",
  ounCode: "",
  ccrCostcentre: "",
  ftyFundType: "",
  atActivityCode: "",
  soCode: "",
  rqmContactPerson: "",
  rqmEntAmt: "",
  rqmAmount: "",
  rqmRateDate: "",
  rqmCurrencyCode: "",
  rqmRateType: "",
  rqmCurrencyUnit: "",
  rqmConversionRate: "",
  rqmRefNo: "",
  rqmJenisTender: "",
  rqmDocReceiveDate: "",
  rqmTenderType: "",
  rqmQuotationReceive: "",
  rqmCancelRemark: "",
  rqmPayeeCode: "",
  pprRequisitionId: null,
  rqmRequestByDisplay: "",
});

const form = ref<FormShape>(blankForm());

const costCentreOpts = computed(() => ccOptionsOverride.value ?? opts.value?.costCentres ?? []);

function formatMyr(raw: unknown): string {
  const n = typeof raw === "number" ? raw : parseFloat(String(raw ?? ""));
  if (!Number.isFinite(n)) return "MYR 0.00";
  return `MYR ${n.toFixed(2)}`;
}

const totalAmtDisplay = computed(() => formatMyr(form.value.rqmAmount));

const requestByDisplay = computed(() => {
  const preset = String(form.value.rqmRequestByDisplay ?? "").trim();
  if (preset) return preset;
  const id = String(form.value.rqmRequestBy ?? "").trim();
  if (!id) return "";
  const hit = opts.value?.requestBy?.find((o) => o.value === id);
  if (hit) return hit.label;
  return id;
});

function settleAmounts(): void {
  const ent = Number(form.value.rqmEntAmt);
  if (!Number.isFinite(ent)) return;
  form.value.rqmAmount = String(ent);
}

function applyHeaderFromApi(row: Record<string, unknown>): void {
  const next = blankForm();
  const keys = Object.keys(next) as (keyof FormShape)[];
  for (const k of keys) {
    const r = row[k as string];
    if (r !== undefined && r !== null) {
      (next as Record<string, unknown>)[k] = r as unknown;
    }
  }
  if (!next.rqmRequestDate) next.rqmRequestDate = new Date().toISOString().slice(0, 10);
  if (!next.rqmStatus) next.rqmStatus = "DRAFT";
  for (const dk of ["rqmRequestDate", "rqmRateDate", "rqmDocReceiveDate"] as const) {
    const v = next[dk];
    if (typeof v === "string" && v.length >= 10) (next as Record<string, unknown>)[dk] = v.slice(0, 10);
  }
  for (const nk of ["rqmEntAmt", "rqmAmount", "rqmQuotationReceive", "rqmCurrencyUnit", "rqmConversionRate"] as const) {
    const v = next[nk];
    if (typeof v === "number") (next as Record<string, unknown>)[nk] = String(v);
  }
  next.rqmCancelRemark = row.rqmCancelRemark !== undefined ? String(row.rqmCancelRemark ?? "") : "";
  form.value = next;
}

async function loadOptions(): Promise<void> {
  const res = await purchasingPurchaseRequisitionOptions();
  opts.value = res.data;
}

async function loadCostCentresForPtj(oun: string): Promise<void> {
  const o = oun.trim();
  if (!o) {
    ccOptionsOverride.value = null;
    return;
  }
  try {
    const res = await purchasingPurchaseRequisitionCostCentres(o);
    ccOptionsOverride.value = res.data.costCentres;
  } catch {
    ccOptionsOverride.value = [];
  }
}

async function loadPartialExisting(): Promise<void> {
  if (rqmId.value < 1) {
    partialRows.value = [];
    return;
  }
  partialLoading.value = true;
  try {
    const res = await listPurchasingPrPartialExistingDocs(rqmId.value);
    partialRows.value = Array.isArray(res.data) ? res.data : [];
  } catch {
    partialRows.value = [];
  } finally {
    partialLoading.value = false;
  }
}

async function loadExisting(): Promise<void> {
  if (rqmId.value < 1) return;
  try {
    const res = await getPurchasingPurchaseRequisition(rqmId.value);
    applyHeaderFromApi(res.data as Record<string, unknown>);
    if (String(form.value.ounCode ?? "").trim()) {
      await loadCostCentresForPtj(String(form.value.ounCode));
    }
  } catch (e) {
    toast.error("Load failed", e instanceof Error ? e.message : "Could not load Purchase Requisition.");
  }
}

async function loadBootstrap(): Promise<void> {
  loading.value = true;
  skipOuWatcher.value = true;
  detailsOpen.value = false;
  existingOpen.value = false;
  try {
    await loadOptions();
    if (rqmId.value > 0) {
      await loadExisting();
      await loadPartialExisting();
    } else {
      form.value = blankForm();
      ccOptionsOverride.value = null;
      partialRows.value = [];
    }
  } catch (e) {
    toast.error("Load failed", e instanceof Error ? e.message : "Unable to load form.");
  } finally {
    await nextTick();
    skipOuWatcher.value = false;
    loading.value = false;
  }
}

watch(
  () => form.value.ounCode,
  async (ou) => {
    if (skipOuWatcher.value) return;
    const s = typeof ou === "string" ? ou.trim() : "";
    form.value.ccrCostcentre = "";
    await loadCostCentresForPtj(s);
  },
);

watch(rqmId, () => loadBootstrap(), { immediate: true });

function formatCell(v: unknown): string {
  if (v === null || v === undefined) return "";
  if (typeof v === "number") return Number.isFinite(v) ? v.toFixed(2) : "";
  return String(v);
}

function buildCancelPayload(): Record<string, unknown> {
  settleAmounts();
  const f = form.value;
  return {
    rqmRequestBy: f.rqmRequestBy,
    rqmRequestDate: f.rqmRequestDate,
    rqmRequisitionTitle: f.rqmRequisitionTitle,
    rqmTenderScope: f.rqmTenderScope,
    rqmIsagreementExist: "N",
    rqmAggNo: "",
    ounCode: f.ounCode,
    ccrCostcentre: f.ccrCostcentre,
    ftyFundType: f.ftyFundType,
    atActivityCode: f.atActivityCode,
    rqmContactPerson: f.rqmContactPerson,
    rqmPayeeCode: f.rqmPayeeCode.trim() === "" ? null : f.rqmPayeeCode.trim(),
    soCode: f.soCode || null,
    rqmCancelRemark: f.rqmCancelRemark.trim(),
    rqmEntAmt: f.rqmEntAmt === "" ? null : f.rqmEntAmt,
    rqmAmount: f.rqmAmount === "" ? null : f.rqmAmount,
    rqmRateDate: f.rqmRateDate || null,
    rqmCurrencyCode: f.rqmCurrencyCode || null,
    rqmRateType: f.rqmRateType || null,
    rqmCurrencyUnit: f.rqmCurrencyUnit || null,
    rqmConversionRate: f.rqmConversionRate || null,
    rqmRefNo: f.rqmRefNo || null,
    rqmJenisTender: f.rqmJenisTender || null,
    rqmDocReceiveDate: f.rqmDocReceiveDate || null,
    rqmTenderType: f.rqmTenderType || null,
    rqmQuotationReceive: f.rqmQuotationReceive === "" ? null : f.rqmQuotationReceive,
    rqmStatus: f.rqmStatus || "DRAFT",
  };
}

async function submit(): Promise<void> {
  if (rqmId.value < 1) {
    toast.error("Submit", "Open this form with ?rqm_requisition_id=");
    return;
  }
  if (!String(form.value.rqmCancelRemark ?? "").trim()) {
    toast.error("Submit", "Reason is required.");
    return;
  }
  saving.value = true;
  try {
    const res = await updatePurchasingPrCancel(rqmId.value, buildCancelPayload());
    applyHeaderFromApi(res.data as Record<string, unknown>);
    toast.success("Submitted");
    await loadPartialExisting();
  } catch (e) {
    toast.error("Submit failed", e instanceof Error ? e.message : "Unable to submit.");
  } finally {
    saving.value = false;
  }
}

function labelClass(): string {
  return "text-right text-sm font-medium text-slate-700";
}
</script>

<template>
  <AdminLayout>
    <div class="space-y-4">
      <div class="flex flex-wrap items-start justify-between gap-2">
        <h1 class="page-title">{{ pageHeading }}</h1>
      </div>

      <p v-if="loading" class="text-sm text-slate-500">Loading…</p>

      <template v-else>
        <!-- Information -->
        <article class="rounded-lg border border-slate-200 bg-white shadow-sm">
          <div class="border-b border-slate-100 px-4 py-3">
            <h2 class="text-base font-semibold text-slate-900">Information</h2>
          </div>
          <div class="grid gap-3 p-4 md:grid-cols-2">
            <div class="grid items-center gap-2 md:grid-cols-[12rem_1fr] md:gap-4">
              <label :class="labelClass()">Requisition No :</label>
              <input
                type="text"
                readonly
                class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-600"
                :value="form.rqmRequisitionNo ? String(form.rqmRequisitionNo) : 'Auto Assigned'"
              />
            </div>

            <div class="grid items-center gap-2 md:grid-cols-[12rem_1fr] md:gap-4">
              <label :class="labelClass()"><span class="text-red-600">*</span> Request By :</label>
              <input
                type="text"
                readonly
                class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700"
                :value="requestByDisplay"
                placeholder="—"
              />
            </div>

            <div class="grid items-center gap-2 md:grid-cols-[12rem_1fr] md:gap-4">
              <label :class="labelClass()"><span class="text-red-600">*</span> Request Date :</label>
              <input v-model="form.rqmRequestDate" type="date" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
            </div>
            <div class="grid items-center gap-2 md:grid-cols-[12rem_1fr] md:gap-4">
              <label :class="labelClass()">Status PR Cancel Partial :</label>
              <input
                type="text"
                readonly
                class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm uppercase text-slate-600"
                :value="String(form.rqmStatus ?? 'DRAFT')"
              />
            </div>

            <div class="md:col-span-2">
              <div class="grid items-start gap-2 md:grid-cols-[12rem_1fr] md:gap-4">
                <label :class="labelClass() + ' pt-2'"><span class="text-red-600">*</span> Title :</label>
                <input
                  v-model="form.rqmRequisitionTitle"
                  type="text"
                  class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
                  maxlength="4000"
                />
              </div>
            </div>
            <div class="md:col-span-2">
              <div class="grid items-start gap-2 md:grid-cols-[12rem_1fr] md:gap-4">
                <label :class="labelClass() + ' pt-2'"><span class="text-red-600">*</span> Justification :</label>
                <textarea
                  v-model="form.rqmTenderScope"
                  rows="3"
                  class="min-h-[4.5rem] w-full resize-y rounded-lg border border-slate-300 px-3 py-2 text-sm"
                  maxlength="1000"
                />
              </div>
            </div>

            <div class="grid items-center gap-2 md:grid-cols-[12rem_1fr] md:gap-4 md:col-span-2">
              <label :class="labelClass()"><span class="text-red-600">*</span> PTJ :</label>
              <select v-model="form.ounCode" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
                <option value="">Select…</option>
                <option v-for="o in opts?.ptj ?? []" :key="'ptj-' + o.value" :value="o.value">{{ o.label }}</option>
              </select>
            </div>
            <div class="grid items-center gap-2 md:grid-cols-[12rem_1fr] md:gap-4 md:col-span-2">
              <label :class="labelClass()"><span class="text-red-600">*</span> Costcentre :</label>
              <select v-model="form.ccrCostcentre" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
                <option value="">Select…</option>
                <option v-for="o in costCentreOpts" :key="'cc-' + o.value" :value="o.value">{{ o.label }}</option>
              </select>
            </div>
            <div class="grid items-center gap-2 md:grid-cols-[12rem_1fr] md:gap-4 md:col-span-2">
              <label :class="labelClass()"><span class="text-red-600">*</span> Fund :</label>
              <select v-model="form.ftyFundType" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
                <option value="">Select…</option>
                <option v-for="o in opts?.fund ?? []" :key="'ft-' + o.value" :value="o.value">{{ o.label }}</option>
              </select>
            </div>
            <div class="grid items-center gap-2 md:grid-cols-[12rem_1fr] md:gap-4 md:col-span-2">
              <label :class="labelClass()"><span class="text-red-600">*</span> Activity :</label>
              <select v-model="form.atActivityCode" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
                <option value="">Select…</option>
                <option v-for="o in opts?.activity ?? []" :key="'ac-' + o.value" :value="o.value">{{ o.label }}</option>
              </select>
            </div>
            <div class="grid items-center gap-2 md:grid-cols-[12rem_1fr] md:gap-4 md:col-span-2">
              <label :class="labelClass()">Code SO :</label>
              <select v-model="form.soCode" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
                <option value="">—</option>
                <option v-for="o in opts?.soCode ?? []" :key="'so-' + o.value" :value="o.value">{{ o.label }}</option>
              </select>
            </div>
          </div>
        </article>

        <!-- Details (financial / compliance) -->
        <article class="rounded-lg border border-slate-200 bg-white shadow-sm">
          <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
            <h2 class="text-base font-semibold text-slate-900">Details</h2>
            <button
              type="button"
              class="rounded px-2 py-1 text-sm text-slate-500 hover:bg-slate-100"
              :aria-expanded="detailsOpen"
              @click="detailsOpen = !detailsOpen"
            >
              {{ detailsOpen ? "⌄" : "›" }}
            </button>
          </div>
          <div v-if="detailsOpen" class="grid gap-3 p-4 md:grid-cols-2">
            <div class="grid items-center gap-2 md:grid-cols-[12rem_1fr] md:gap-4 md:col-span-2">
              <label :class="labelClass()"><span class="text-red-600">*</span> Contact Person :</label>
              <select v-model="form.rqmContactPerson" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
                <option value="">Select…</option>
                <option v-for="o in opts?.contactPerson ?? []" :key="'cp-' + o.value" :value="o.value">{{ o.label }}</option>
              </select>
            </div>
            <div class="grid items-center gap-2 md:grid-cols-[12rem_1fr] md:gap-4">
              <label :class="labelClass()"><span class="text-red-600">*</span> Enter Amount :</label>
              <input
                v-model="form.rqmEntAmt"
                type="text"
                readonly
                class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm"
              />
            </div>
            <div class="grid items-center gap-2 md:grid-cols-[12rem_1fr] md:gap-4">
              <label :class="labelClass()">Total Amount :</label>
              <input
                type="text"
                readonly
                class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-medium text-slate-800"
                :value="totalAmtDisplay"
              />
            </div>
            <div class="grid items-center gap-2 md:grid-cols-[12rem_1fr] md:gap-4 md:col-span-2">
              <label class="text-right text-sm font-medium text-red-600">Rate Date (FOR INTERNATIONAL RATE ONLY) :</label>
              <input v-model="form.rqmRateDate" type="date" class="w-full max-w-xs rounded-lg border border-slate-300 px-3 py-2 text-sm" />
            </div>
            <div class="grid items-center gap-2 md:grid-cols-[12rem_1fr] md:gap-4">
              <label :class="labelClass()">Foreign Currency Code :</label>
              <select v-model="form.rqmCurrencyCode" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
                <option value="">Select…</option>
                <option v-for="o in opts?.foreignCurrencyCode ?? []" :key="'cur-' + o.value" :value="o.value">{{ o.label }}</option>
              </select>
            </div>
            <div class="grid items-center gap-2 md:grid-cols-[12rem_1fr] md:gap-4">
              <label :class="labelClass()">Rate Type :</label>
              <select v-model="form.rqmRateType" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
                <option value="">Select…</option>
                <option v-for="o in opts?.rateType ?? []" :key="'rt-' + o.value" :value="o.value">{{ o.label }}</option>
              </select>
            </div>
            <div class="grid items-center gap-2 md:grid-cols-[12rem_1fr] md:gap-4">
              <label :class="labelClass()">Conversion Rate :</label>
              <input v-model="form.rqmConversionRate" type="text" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
            </div>
            <div class="grid items-center gap-2 md:grid-cols-[12rem_1fr] md:gap-4 md:col-span-2">
              <label :class="labelClass()"><span class="text-red-600">*</span> Document No :</label>
              <input v-model="form.rqmRefNo" type="text" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm uppercase" />
            </div>
            <div class="grid items-center gap-2 md:grid-cols-[12rem_1fr] md:gap-4">
              <label :class="labelClass()"><span class="text-red-600">*</span> Requisition Type :</label>
              <select v-model="form.rqmJenisTender" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
                <option value="">Select…</option>
                <option v-for="o in opts?.requisitionType ?? []" :key="'jt-' + o.value" :value="o.value">{{ o.label }}</option>
              </select>
            </div>
            <div class="grid items-center gap-2 md:grid-cols-[12rem_1fr] md:gap-4">
              <label :class="labelClass()"><span class="text-red-600">*</span> Document Received Date :</label>
              <input v-model="form.rqmDocReceiveDate" type="date" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
            </div>
            <div class="grid items-center gap-2 md:grid-cols-[12rem_1fr] md:gap-4">
              <label :class="labelClass()"><span class="text-red-600">*</span> Purchase Method :</label>
              <select v-model="form.rqmTenderType" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
                <option value="">Select…</option>
                <option v-for="o in opts?.purchaseMethod ?? []" :key="'pm-' + o.value" :value="o.value">{{ o.label }}</option>
              </select>
            </div>
            <div class="grid items-center gap-2 md:grid-cols-[12rem_1fr] md:gap-4">
              <label :class="labelClass()"><span class="text-red-600">*</span> No of Quotation :</label>
              <input v-model="form.rqmQuotationReceive" type="text" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
            </div>
          </div>
        </article>

        <!-- List of Existing PR in PO / WPN / GRN / BILL -->
        <article class="rounded-lg border border-slate-200 bg-white shadow-sm">
          <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
            <h2 class="text-base font-semibold text-slate-900">
              List of Existing PR in PO / WPN / GRN / BILL
            </h2>
            <button
              type="button"
              class="rounded px-2 py-1 text-sm text-slate-500 hover:bg-slate-100"
              :aria-expanded="existingOpen"
              @click="existingOpen = !existingOpen"
            >
              {{ existingOpen ? "⌄" : "›" }}
            </button>
          </div>
          <div v-if="existingOpen" class="overflow-x-auto p-4">
            <p v-if="partialLoading" class="text-sm text-slate-500">Loading…</p>
            <table v-else class="admin-table-kitchen min-w-[64rem] w-full text-xs">
              <thead class="admin-table-thead-sticky">
                <tr>
                  <th class="px-2 py-2 text-left font-semibold text-slate-600">Application No</th>
                  <th class="px-2 py-2 text-left font-semibold text-slate-600">PO No</th>
                  <th class="px-2 py-2 text-left font-semibold text-slate-600">Vendor</th>
                  <th class="px-2 py-2 text-left font-semibold text-slate-600">Description</th>
                  <th class="px-2 py-2 text-right font-semibold text-slate-600">Total Amount</th>
                  <th class="px-2 py-2 text-right font-semibold text-slate-600">Received Amount</th>
                  <th class="px-2 py-2 text-left font-semibold text-slate-600">Status</th>
                  <th class="px-2 py-2 text-left font-semibold text-slate-600">Source</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="partialRows.length === 0">
                  <td colspan="8" class="px-2 py-4 text-center text-slate-500">No records</td>
                </tr>
                <tr v-for="(row, ix) in partialRows" :key="ix" class="border-b border-slate-100">
                  <td class="px-2 py-2">{{ row.applicationNo ?? "" }}</td>
                  <td class="px-2 py-2">{{ row.pomOrderNo ?? "" }}</td>
                  <td class="px-2 py-2">{{ row.vcsVendorCode ?? "" }}</td>
                  <td class="px-2 py-2">{{ row.pomDescription ?? "" }}</td>
                  <td class="px-2 py-2 text-right">{{ formatCell(row.grmTotalAmt) }}</td>
                  <td class="px-2 py-2 text-right">{{ formatCell(row.grdReceiveAmt) }}</td>
                  <td class="px-2 py-2">{{ row.applicationStatus ?? "" }}</td>
                  <td class="px-2 py-2">{{ row.fromTable ?? "" }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </article>

        <!-- Reason Cancel Partial PR -->
        <article class="rounded-lg border border-slate-200 bg-white shadow-sm">
          <div class="border-b border-slate-100 px-4 py-3">
            <h2 class="text-base font-semibold text-slate-900">Reason Cancel Partial PR</h2>
          </div>
          <div class="grid gap-3 p-4">
            <div class="grid items-start gap-2 md:grid-cols-[12rem_1fr] md:gap-4">
              <label :class="labelClass() + ' pt-2'"><span class="text-red-600">*</span> Reason :</label>
              <input
                v-model="form.rqmCancelRemark"
                type="text"
                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
                maxlength="8000"
                placeholder="State the reason for partial cancellation…"
              />
            </div>
          </div>
        </article>

        <div class="flex justify-center pb-8">
          <button
            type="button"
            class="rounded-lg bg-violet-600 px-10 py-2.5 text-sm font-medium text-white shadow hover:bg-violet-700 disabled:opacity-50"
            :disabled="saving || rqmId < 1"
            @click="submit"
          >
            Submit
          </button>
        </div>
      </template>
    </div>
  </AdminLayout>
</template>
