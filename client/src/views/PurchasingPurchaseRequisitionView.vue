<script setup lang="ts">
/**
 * Purchasing / Purchase Requisition / New Purchase Requisition (MENUID 1771).
 * Legacy-aligned header form + mysql_secondary dropdown payloads.
 *
 * Deep link: `/admin/kerisi/m/1771?rqm_requisition_id=123`
 */
import { computed, nextTick, ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import { Printer, Save } from "lucide-vue-next";
import AdminLayout from "@/layouts/AdminLayout.vue";
import {
  getPurchasingPurchaseRequisition,
  purchasingPurchaseRequisitionCostCentres,
  purchasingPurchaseRequisitionCreate,
  purchasingPurchaseRequisitionOptions,
  updatePurchasingPurchaseRequisition,
  type PurchasingPrDropdownRow,
  type PurchasingPrOptionsPayload,
} from "@/api/cms";
import { getKerisiMenuTrailByMenuId } from "@/config/kerisi-menu-resolve";
import { useToast } from "@/composables/useToast";

const MENU_ID = 1771;
const toast = useToast();
const route = useRoute();
const router = useRouter();

const pageHeading = computed(() => {
  const trail = getKerisiMenuTrailByMenuId(MENU_ID);
  return trail?.length ? trail.join(" / ") : "Purchasing / Purchase Requisition / New Purchase Requisition";
});

const rqmId = computed(() => {
  const raw = route.query.rqm_requisition_id ?? route.query.rqmRequisitionId;
  const n = typeof raw === "string" ? Number(raw) : Array.isArray(raw) ? Number(raw[0]) : NaN;
  return Number.isFinite(n) && n > 0 ? n : 0;
});

const opts = ref<PurchasingPrOptionsPayload | null>(null);
const loading = ref(true);
const saving = ref(false);

const ccOptionsOverride = ref<PurchasingPrDropdownRow[] | null>(null);
/** Prevents clearing cost centre right after hydrating an existing PR. */
const skipOuWatcher = ref(true);

type FormShape = {
  rqmRequisitionNo: string;
  rqmStatus: string;
  rqmRequestBy: string;
  rqmRequestDate: string;
  rqmRequisitionTitle: string;
  rqmTenderScope: string;
  rqmIsagreementExist: string;
  rqmAggNo: string;
  ounCode: string;
  ccrCostcentre: string;
  ftyFundType: string;
  atActivityCode: string;
  rqmContactPerson: string;
  rqmPayeeCode: string;
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
  pprRequisitionId: number | null;
};

const blankForm = (): FormShape => ({
  rqmRequisitionNo: "",
  rqmStatus: "DRAFT",
  rqmRequestBy: "",
  rqmRequestDate: new Date().toISOString().slice(0, 10),
  rqmRequisitionTitle: "",
  rqmTenderScope: "",
  rqmIsagreementExist: "N",
  rqmAggNo: "",
  ounCode: "",
  ccrCostcentre: "",
  ftyFundType: "",
  atActivityCode: "",
  rqmContactPerson: "",
  rqmPayeeCode: "",
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
  pprRequisitionId: null as number | null,
});

const form = ref<FormShape>(blankForm());

const costCentreOpts = computed(() => ccOptionsOverride.value ?? opts.value?.costCentres ?? []);

function settleAmounts(): void {
  const ent = Number(form.value.rqmEntAmt);
  if (!Number.isFinite(ent)) return;
  form.value.rqmAmount = String(ent);
}

function formatMyr(raw: unknown): string {
  const n = typeof raw === "number" ? raw : parseFloat(String(raw ?? ""));
  if (!Number.isFinite(n)) return "MYR 0.00";
  return `MYR ${n.toFixed(2)}`;
}

const totalAmtDisplay = computed(() => formatMyr(form.value.rqmAmount));

function applyHeaderFromApi(row: Record<string, unknown>): void {
  const next = blankForm();
  const keys = Object.keys(next);
  for (const k of keys) {
    if (row[k] !== undefined && row[k] !== null) {
      (next as Record<string, unknown>)[k] = row[k] as unknown;
    }
  }
  if (!next.rqmRequestDate) next.rqmRequestDate = new Date().toISOString().slice(0, 10);
  if (!next.rqmStatus) next.rqmStatus = "DRAFT";
  if (!next.rqmIsagreementExist) next.rqmIsagreementExist = "N";
  for (const dk of ["rqmRequestDate", "rqmRateDate", "rqmDocReceiveDate"] as const) {
    const v = next[dk];
    if (typeof v === "string" && v.length >= 10) (next as Record<string, unknown>)[dk] = v.slice(0, 10);
  }
  for (const nk of ["rqmEntAmt", "rqmAmount", "rqmQuotationReceive", "rqmCurrencyUnit", "rqmConversionRate"] as const) {
    const v = next[nk];
    if (typeof v === "number") (next as Record<string, unknown>)[nk] = String(v);
  }
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
  try {
    await loadOptions();
    if (rqmId.value > 0) {
      await loadExisting();
    } else {
      form.value = blankForm();
      ccOptionsOverride.value = null;
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

function buildSaveBody(): Record<string, unknown> {
  settleAmounts();
  const f = form.value;
  const body: Record<string, unknown> = {
    rqmRequestBy: f.rqmRequestBy,
    rqmRequestDate: f.rqmRequestDate,
    rqmRequisitionTitle: f.rqmRequisitionTitle,
    rqmTenderScope: f.rqmTenderScope,
    rqmIsagreementExist: f.rqmIsagreementExist,
    rqmAggNo: f.rqmIsagreementExist === "Y" ? f.rqmAggNo : "",
    ounCode: f.ounCode,
    ccrCostcentre: f.ccrCostcentre,
    ftyFundType: f.ftyFundType,
    atActivityCode: f.atActivityCode,
    rqmContactPerson: f.rqmContactPerson,
    rqmPayeeCode: f.rqmPayeeCode || null,
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
  return body;
}

async function save(): Promise<void> {
  saving.value = true;
  try {
    const body = buildSaveBody();
    if (rqmId.value > 0) {
      const res = await updatePurchasingPurchaseRequisition(rqmId.value, body);
      applyHeaderFromApi(res.data as Record<string, unknown>);
      toast.success("Saved");
    } else {
      const res = await purchasingPurchaseRequisitionCreate(body);
      const row = res.data as Record<string, unknown>;
      applyHeaderFromApi(row);
      const newId = Number(row.rqmRequisitionId ?? row.rqm_requisition_id ?? 0);
      if (Number.isFinite(newId) && newId > 0) {
        await router.replace({ path: route.path, query: { ...route.query, rqm_requisition_id: String(newId) } });
      }
      toast.success("Created");
    }
  } catch (e) {
    toast.error("Save failed", e instanceof Error ? e.message : "Unable to save.");
  } finally {
    saving.value = false;
  }
}

function doPrint(): void {
  window.print();
}

function labelClass(): string {
  return "text-right text-sm font-medium text-slate-700";
}
</script>

<template>
  <AdminLayout>
    <div class="space-y-4 print:space-y-3">
      <h1 class="page-title">{{ pageHeading }}</h1>

      <p v-if="loading" class="text-sm text-slate-500">Loading…</p>

      <template v-else>
        <article class="rounded-lg border border-slate-200 bg-white shadow-sm">
          <div class="border-b border-slate-100 px-4 py-3">
            <h2 class="text-base font-semibold text-slate-900">Information</h2>
          </div>
          <div class="grid gap-3 p-4 md:grid-cols-2">
            <div class="contents md:col-span-2">
              <div class="grid items-center gap-2 md:grid-cols-[12rem_1fr] md:gap-4">
                <label :class="labelClass()">Requisition No :</label>
                <input
                  type="text"
                  readonly
                  class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-600"
                  :value="form.rqmRequisitionNo ? String(form.rqmRequisitionNo) : 'Auto Assigned'"
                />
              </div>
            </div>
            <div class="grid items-center gap-2 md:grid-cols-[12rem_1fr] md:gap-4">
              <label :class="labelClass()"><span class="text-red-600">*</span> Request By :</label>
              <select v-model="form.rqmRequestBy" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
                <option value="">Select…</option>
                <option v-for="o in opts?.requestBy ?? []" :key="'rb-' + o.value" :value="o.value">{{ o.label }}</option>
              </select>
            </div>
            <div class="grid items-center gap-2 md:grid-cols-[12rem_1fr] md:gap-4">
              <label :class="labelClass()"><span class="text-red-600">*</span> Request Date :</label>
              <input v-model="form.rqmRequestDate" type="date" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
            </div>
            <div class="grid items-center gap-2 md:grid-cols-[12rem_1fr] md:gap-4">
              <label :class="labelClass()">Status :</label>
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
                  class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
                  maxlength="1000"
                />
              </div>
            </div>
            <div class="grid items-center gap-2 md:grid-cols-[12rem_1fr] md:gap-4 md:col-span-2">
              <label :class="labelClass()"><span class="text-red-600">*</span> Agreement :</label>
              <select v-model="form.rqmIsagreementExist" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
                <option v-for="o in opts?.agreementYesNo ?? []" :key="'ag-' + o.value" :value="o.value">{{ o.label }}</option>
              </select>
            </div>
            <div
              v-if="form.rqmIsagreementExist === 'Y'"
              class="grid items-center gap-2 md:grid-cols-[12rem_1fr] md:gap-4 md:col-span-2"
            >
              <label :class="labelClass()"><span class="text-red-600">*</span> Agreement No :</label>
              <select v-model="form.rqmAggNo" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
                <option value="">Select…</option>
                <option v-for="o in opts?.agreementNo ?? []" :key="'agn-' + o.value" :value="o.value">{{ o.label }}</option>
              </select>
            </div>
            <div class="grid items-center gap-2 md:grid-cols-[12rem_1fr] md:gap-4 md:col-span-2">
              <label :class="labelClass()"><span class="text-red-600">*</span> PTJ :</label>
              <select v-model="form.ounCode" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
                <option value="">Select…</option>
                <option v-for="o in opts?.ptj ?? []" :key="'ptj-' + o.value" :value="o.value">{{ o.label }}</option>
              </select>
            </div>
            <div class="grid items-center gap-2 md:grid-cols-[12rem_1fr] md:gap-4 md:col-span-2">
              <label :class="labelClass()"><span class="text-red-600">*</span> Cost centre :</label>
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
              <label :class="labelClass()"><span class="text-red-600">*</span> Contact Person :</label>
              <select v-model="form.rqmContactPerson" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
                <option value="">Select…</option>
                <option v-for="o in opts?.contactPerson ?? []" :key="'cp-' + o.value" :value="o.value">{{ o.label }}</option>
              </select>
            </div>
            <div class="grid items-center gap-2 md:grid-cols-[12rem_1fr] md:gap-4 md:col-span-2">
              <label :class="labelClass()">Vendor :</label>
              <select v-model="form.rqmPayeeCode" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
                <option value="">Select…</option>
                <option v-for="o in opts?.vendor ?? []" :key="'v-' + o.value" :value="o.value">{{ o.label }}</option>
              </select>
            </div>
          </div>
        </article>

        <article class="rounded-lg border border-slate-200 bg-white shadow-sm">
          <div class="border-b border-slate-100 px-4 py-3">
            <h2 class="text-base font-semibold text-slate-900">Details</h2>
          </div>
          <div class="grid gap-3 p-4 md:grid-cols-2">
            <div class="grid items-center gap-2 md:grid-cols-[12rem_1fr] md:gap-4">
              <label :class="labelClass()">Enter Amount :</label>
              <input
                v-model="form.rqmEntAmt"
                type="number"
                step="0.0001"
                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
                @change="settleAmounts"
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
              <label :class="labelClass()">Conversion Unit :</label>
              <input v-model="form.rqmCurrencyUnit" type="text" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
            </div>
            <div class="grid items-center gap-2 md:grid-cols-[12rem_1fr] md:gap-4">
              <label :class="labelClass()">Conversion Rate :</label>
              <input v-model="form.rqmConversionRate" type="text" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
            </div>
            <div class="grid items-center gap-2 md:grid-cols-[12rem_1fr] md:gap-4 md:col-span-2">
              <label :class="labelClass()">Document No :</label>
              <input v-model="form.rqmRefNo" type="text" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
            </div>
            <div class="grid items-center gap-2 md:grid-cols-[12rem_1fr] md:gap-4">
              <label :class="labelClass()">Requisition Type :</label>
              <select v-model="form.rqmJenisTender" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
                <option value="">Select…</option>
                <option v-for="o in opts?.requisitionType ?? []" :key="'jt-' + o.value" :value="o.value">{{ o.label }}</option>
              </select>
            </div>
            <div class="grid items-center gap-2 md:grid-cols-[12rem_1fr] md:gap-4">
              <label :class="labelClass()">Document Received Date :</label>
              <input v-model="form.rqmDocReceiveDate" type="date" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
            </div>
            <div class="grid items-center gap-2 md:grid-cols-[12rem_1fr] md:gap-4">
              <label :class="labelClass()">Purchase Method :</label>
              <select v-model="form.rqmTenderType" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
                <option value="">Select…</option>
                <option v-for="o in opts?.purchaseMethod ?? []" :key="'pm-' + o.value" :value="o.value">{{ o.label }}</option>
              </select>
            </div>
            <div class="grid items-center gap-2 md:grid-cols-[12rem_1fr] md:gap-4">
              <label :class="labelClass()">No of Quotation :</label>
              <input v-model="form.rqmQuotationReceive" type="number" step="1" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
            </div>
            <div class="grid items-center gap-2 md:grid-cols-[12rem_1fr] md:gap-4 md:col-span-2">
              <label :class="labelClass()">PRE - PR No :</label>
              <input
                type="text"
                readonly
                class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-600"
                :value="form.pprRequisitionId != null ? String(form.pprRequisitionId) : ''"
                placeholder="—"
              />
            </div>
          </div>
          <div class="flex justify-end gap-2 border-t border-slate-100 px-4 py-3 print:hidden">
            <button
              type="button"
              class="inline-flex items-center gap-2 rounded-lg bg-violet-600 px-4 py-2 text-sm font-medium text-white shadow hover:bg-violet-700 disabled:opacity-50"
              :disabled="saving"
              @click="save"
            >
              <Save class="h-4 w-4" />
              Save
            </button>
            <button
              type="button"
              class="inline-flex items-center gap-2 rounded-lg bg-violet-600 px-4 py-2 text-sm font-medium text-white shadow hover:bg-violet-700"
              @click="doPrint"
            >
              <Printer class="h-4 w-4" />
              Print
            </button>
          </div>
        </article>
      </template>
    </div>
  </AdminLayout>
</template>
