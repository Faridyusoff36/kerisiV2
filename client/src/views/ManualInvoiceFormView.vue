<script setup lang="ts">
/**
 * Student Finance > Manual Invoice Form (PAGEID 2347 / MENUID 2898).
 *
 * Layout mirrors legacy FIMS: Invoice Head (two-column label fields), Debit /
 * Credit datatables (+ Add), Process flow panel, Save / Submit. Data loads via
 * `GET /student-finance/manual-invoice/{id}` when `?id=` is present; screens
 * without `id` show the same empty scaffold (new-record flow deferred).
 *
 * Debit/Credit **+ Add** persists `cust_invoice_details` rows (`POST …/lines`) when
 * status is DRAFT. Save / Submit workflows remain stubs.
 */
import { computed, ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import {
  ChevronLeft,
  Copy,
  Hash,
  Loader2,
  PencilLine,
  Plus,
  RefreshCcw,
  Save,
  Send,
  Trash2,
  X,
} from "lucide-vue-next";
import AdminLayout from "@/layouts/AdminLayout.vue";
import { addManualInvoiceLine, getManualInvoice, removeManualInvoiceLine } from "@/api/cms";
import { useToast } from "@/composables/useToast";
import { useConfirmDialog } from "@/composables/useConfirmDialog";
import type { ManualInvoiceDetail, ManualInvoiceDetailLine } from "@/types";

const toast = useToast();
const { confirm } = useConfirmDialog();
const route = useRoute();
const router = useRouter();

function parseInvoiceId(): number | null {
  const raw = route.query.id ?? route.query.cimCustInvoiceId;
  const s = String(Array.isArray(raw) ? raw[0] : raw ?? "").trim();
  const n = Number(s);
  if (!Number.isFinite(n) || n <= 0) return null;
  return Math.floor(n);
}

const invoiceId = computed(() => parseInvoiceId());

const editQuery = computed(() => {
  const raw = route.query.edit ?? route.query.mode;
  const s = String(Array.isArray(raw) ? raw[0] : raw ?? "").trim().toLowerCase();
  return s === "1" || s === "true" || s === "edit";
});

const detail = ref<ManualInvoiceDetail | null>(null);
const loading = ref(false);
const loadFailed = ref(false);
const savingLine = ref(false);

/** Add-line modal (legacy 11 costing columns + amount/tax). */
const showLineModal = ref(false);
/** When false, invoice shows debit+credit in one grid — user picks DT vs CR in the modal. */
const lineTxLocked = ref(true);

type LineDraft = {
  itemCode: string;
  itemCategory: string;
  fundType: string;
  activityCode: string;
  ounCode: string;
  costCentre: string;
  projectNo: string;
  acctCode: string;
  inclusive: string;
  quantity: string;
  unitPrice: string;
  taxCode: string;
  taxAmt: string;
  totalAmt: string;
};

function emptyLineDraft(): LineDraft {
  return {
    itemCode: "",
    itemCategory: "",
    fundType: "",
    activityCode: "",
    ounCode: "",
    costCentre: "",
    projectNo: "",
    acctCode: "",
    inclusive: "",
    quantity: "",
    unitPrice: "",
    taxCode: "",
    taxAmt: "",
    totalAmt: "",
  };
}

const lineDraft = ref<LineDraft>(emptyLineDraft());
const modalFld =
  "mt-1.5 block w-full rounded border border-violet-200/95 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 shadow-inner focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-400/50";
const modalFldReadonly =
  `${modalFld} cursor-not-allowed bg-slate-100 text-slate-600`;

const modalTransactionType = ref<"DT" | "CR">("DT");
/** Which grid opened the modal (Debit vs Credit toolbar). */
const lineModalWhich = ref<"debit" | "credit">("debit");

const isNewShell = computed(() => invoiceId.value === null);

function normalizeInvoiceStatus(raw: string | null | undefined): string {
  return String(raw ?? "").trim().toUpperCase();
}

/** DB may vary casing/spacing — match backend draft checks loosely. */
const isDraftInvoice = computed(
  () => normalizeInvoiceStatus(detail.value?.status) === "DRAFT",
);

const modalHeaderTitle = computed(() => {
  if (!lineTxLocked.value) {
    return modalTransactionType.value === "DT" ? "Debit" : "Credit";
  }
  return lineModalWhich.value === "debit" ? "Debit" : "Credit";
});

/** Sidebar opens /2898 without ?id — keep + Add clickable; validate in handler. */
const addLineBusy = computed(
  () => savingLine.value || (!!invoiceId.value && loading.value),
);

async function load() {
  loadFailed.value = false;
  if (!invoiceId.value) {
    detail.value = null;
    return;
  }
  loading.value = true;
  try {
    const res = await getManualInvoice(invoiceId.value);
    detail.value = res.data;
    loadFailed.value = false;
  } catch {
    detail.value = null;
    loadFailed.value = true;
    toast.error("Load failed", "Unable to load this manual invoice.");
  } finally {
    loading.value = false;
  }
}

watch(
  () => route.fullPath,
  () => void load(),
  { immediate: true },
);

function goBack() {
  router.push({ path: "/admin/kerisi/m/2897" });
}

async function reload() {
  if (!invoiceId.value) return;
  await load();
}

function fmt(n: number): string {
  return new Intl.NumberFormat("en-MY", {
    style: "currency",
    currency: "MYR",
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(Number.isFinite(n) ? n : 0);
}

const canAttemptEditUi = computed(
  () =>
    !!detail.value &&
    isDraftInvoice.value &&
    editQuery.value,
);

const canEditLines = computed(
  () =>
    invoiceId.value != null &&
    !loading.value &&
    isDraftInvoice.value,
);

function parseMoneyField(raw: string): number {
  return Number.parseFloat(String(raw).replace(/,/g, ""));
}

watch(
  () => [lineDraft.value.quantity, lineDraft.value.unitPrice] as const,
  () => {
    const qs = lineDraft.value.quantity.trim();
    const ps = lineDraft.value.unitPrice.trim();
    if (qs === "" || ps === "") return;
    const qn = parseMoneyField(qs);
    const pn = parseMoneyField(ps);
    if (!Number.isFinite(qn) || !Number.isFinite(pn) || qn < 0 || pn < 0) return;
    lineDraft.value.totalAmt = (Math.round(qn * pn * 100) / 100).toFixed(2);
  },
);

function rowsForDebit(d: ManualInvoiceDetail): ManualInvoiceDetailLine[] {
  if (d.splitDebitCredit) return d.debitLines;
  return [...d.debitLines, ...d.creditLines];
}

function rowsForCredit(d: ManualInvoiceDetail): ManualInvoiceDetailLine[] {
  if (d.splitDebitCredit) return d.creditLines;
  return [];
}

/** Display values for Invoice Head inputs (readonly / empty shell). */
const headVals = computed(() => {
  const d = detail.value;
  return {
    invoiceNo: d?.invoiceNo ?? "",
    ourRef: d?.ourRef ?? "",
    yourRef: d?.yourRef ?? "",
    debtorName: d?.debtorName ?? "",
    debtorId: d?.debtorId ?? "",
    debtorType: d?.debtorTypeLabel ?? "",
    semester: d?.semesterId ?? "",
    invoiceDate:
      d?.invoiceDateTime ?? d?.invoiceDate ?? "",
    status: d?.status ?? "",
    description: d?.description ?? "",
    total: d != null ? fmt(d.totalAmt) : "",
    balance: d != null ? fmt(d.balAmt) : "",
    address: [d?.address1, d?.address2].filter(Boolean).join(", ") || "",
    postcodeCity:
      [d?.postcode, d?.city].filter(Boolean).join(" · ") || "",
    stateCountry:
      [d?.state, d?.country].filter(Boolean).join(", ") || "",
    tel: d?.telNo ?? "",
    email: d?.email ?? "",
  };
});

function openAddLine(which: "debit" | "credit") {
  if (addLineBusy.value) return;
  lineModalWhich.value = which;
  // Determine transaction-type lock from loaded detail; default locked per section.
  const d = detail.value;
  if (d && !d.splitDebitCredit) {
    lineTxLocked.value = false;
    modalTransactionType.value = which === "credit" ? "CR" : "DT";
  } else {
    lineTxLocked.value = true;
    modalTransactionType.value = which === "debit" ? "DT" : "CR";
  }
  lineDraft.value = emptyLineDraft();
  showLineModal.value = true;
}

function closeLineModal() {
  showLineModal.value = false;
}

async function submitLineModal() {
  if (savingLine.value) return;
  if (!invoiceId.value) {
    toast.error(
      "No invoice selected",
      "Open an invoice from Manual Invoice Listing first (View or Edit on a row).",
    );
    return;
  }
  if (!detail.value || !isDraftInvoice.value) {
    toast.error(
      "Invoice not editable",
      `Lines can only be saved while status is DRAFT (current: ${detail.value?.status ?? "no invoice"}).`,
    );
    return;
  }
  const id = invoiceId.value;
  const qs = lineDraft.value.quantity.trim();
  const ps = lineDraft.value.unitPrice.trim();
  let total: number;
  if (qs !== "" && ps !== "") {
    const qn = parseMoneyField(qs);
    const pn = parseMoneyField(ps);
    if (!Number.isFinite(qn) || !Number.isFinite(pn) || qn < 0 || pn < 0) {
      toast.error("Validation", "Enter valid quantity and unit price (≥ 0).");
      return;
    }
    total = Math.round(qn * pn * 100) / 100;
  } else {
    total = parseMoneyField(lineDraft.value.totalAmt);
    if (!Number.isFinite(total) || total < 0) {
      toast.error(
        "Validation",
        "Enter Quantity and Unit Price, or fill Total Amount (MYR).",
      );
      return;
    }
  }
  let taxOpt: number | undefined;
  if (lineDraft.value.taxAmt.trim() !== "") {
    const tax = parseMoneyField(lineDraft.value.taxAmt);
    if (!Number.isFinite(tax) || tax < 0) {
      toast.error("Validation", "Enter a valid tax amount or leave it blank.");
      return;
    }
    taxOpt = tax;
  }
  savingLine.value = true;
  try {
    const res = await addManualInvoiceLine(id, {
      transactionType: modalTransactionType.value,
      totalAmt: total,
      ...(taxOpt !== undefined ? { taxAmt: taxOpt } : {}),
      itemCategory: lineDraft.value.itemCategory.trim() || null,
      itemCode: lineDraft.value.itemCode.trim() || null,
      fundType: lineDraft.value.fundType.trim() || null,
      activityCode: lineDraft.value.activityCode.trim() || null,
      acctCode: lineDraft.value.acctCode.trim() || null,
      ounCode: lineDraft.value.ounCode.trim() || null,
      costCentre: lineDraft.value.costCentre.trim() || null,
      projectNo: lineDraft.value.projectNo.trim() || null,
      taxCode: lineDraft.value.taxCode.trim() || null,
    });
    detail.value = res.data;
    toast.success("Line added");
    closeLineModal();
  } catch {
    toast.error("Add failed", "Could not save this line.");
  } finally {
    savingLine.value = false;
  }
}

async function onDeleteLine(row: ManualInvoiceDetailLine) {
  const id = invoiceId.value;
  if (!id || !canEditLines.value) return;
  const ok = await confirm({
    title: "Remove line?",
    message: "Delete this invoice line? Header totals will be recalculated.",
    destructive: true,
    confirmText: "Remove",
  });
  if (!ok) return;
  savingLine.value = true;
  try {
    const res = await removeManualInvoiceLine(id, row.id);
    detail.value = res.data;
    toast.success("Line removed");
  } catch {
    toast.error("Delete failed", "Could not remove this line.");
  } finally {
    savingLine.value = false;
  }
}

function stubSave() {
  toast.info("Save", "Persisting invoice head and lines requires the ported FIMS BL.");
}

function stubSubmit() {
  toast.info("Submit", "Workflow submit is not migrated yet.");
}
</script>

<template>
  <AdminLayout>
    <div class="space-y-4">
      <div class="flex items-center gap-2">
        <button
          type="button"
          class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-2.5 py-1 text-xs text-slate-600 hover:bg-slate-50"
          aria-label="Back"
          @click="goBack"
        >
          <ChevronLeft class="h-3.5 w-3.5" />
          Back
        </button>
        <h1 class="page-title">Student Finance / Manual Invoice Form</h1>
      </div>

      <p
        class="rounded-md border border-slate-200 bg-slate-50 px-4 py-2 text-xs text-slate-600"
      >
        <template v-if="isNewShell">
          No invoice selected — use
          <strong class="font-medium text-slate-800">New</strong> or open
          <strong class="font-medium text-slate-800">View / Edit</strong>
          from Manual Invoice Listing. The layout below mirrors the legacy form shell.
        </template>
        <template v-else-if="invoiceId && loading"> Loading invoice details… </template>
        <template v-else-if="loadFailed && invoiceId">
          Could not load invoice <strong>#{{ invoiceId }}</strong>.
        </template>
      </p>

      <!-- Invoice Head -->
      <article class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <header
          class="flex items-center justify-between border-b border-slate-100 px-4 py-2.5"
        >
          <h2 class="text-base font-semibold text-slate-900">Invoice Head</h2>
          <button
            v-if="invoiceId && !loading"
            type="button"
            class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-2.5 py-1 text-xs font-medium text-slate-700 hover:bg-slate-50 disabled:opacity-50"
            :disabled="loading"
            @click="reload"
          >
            <RefreshCcw class="h-3.5 w-3.5" />
            Reload
          </button>
        </header>

        <div class="relative p-4">
          <div
            v-if="invoiceId && loading"
            class="absolute inset-0 z-10 flex flex-col items-center justify-center gap-2 rounded-md bg-white/85"
          >
            <Loader2 class="h-8 w-8 animate-spin text-violet-600" />
            <span class="text-sm text-slate-600">Loading…</span>
          </div>

          <section class="grid gap-x-8 gap-y-3 md:grid-cols-2">
            <!-- Field rows: label ~ legacy width -->
            <div class="flex items-start gap-3 md:col-span-1">
              <label class="w-44 shrink-0 pt-2 text-xs font-medium text-slate-700">
                Invoice No
              </label>
              <span class="pt-2 text-slate-400">:</span>
              <input
                type="text"
                readonly
                class="flex-1 rounded-md border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-800"
                :value="headVals.invoiceNo"
                placeholder="—"
              />
            </div>
            <div class="flex items-start gap-3 md:col-span-1">
              <label class="w-44 shrink-0 pt-2 text-xs font-medium text-slate-700">
                Status
              </label>
              <span class="pt-2 text-slate-400">:</span>
              <input
                type="text"
                readonly
                class="flex-1 rounded-md border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-800"
                :value="headVals.status"
                placeholder="—"
              />
            </div>
            <div class="flex items-start gap-3 md:col-span-1">
              <label class="w-44 shrink-0 pt-2 text-xs font-medium text-slate-700">
                Invoice Date / Time
              </label>
              <span class="pt-2 text-slate-400">:</span>
              <input
                type="text"
                readonly
                class="flex-1 rounded-md border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-800"
                :value="headVals.invoiceDate"
                placeholder="dd/mm/yyyy hh:mm"
              />
            </div>
            <div class="flex items-start gap-3 md:col-span-1">
              <label class="w-44 shrink-0 pt-2 text-xs font-medium text-slate-700">
                Semester
              </label>
              <span class="pt-2 text-slate-400">:</span>
              <input
                type="text"
                readonly
                class="flex-1 rounded-md border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-800"
                :value="headVals.semester"
                placeholder="—"
              />
            </div>

            <div class="flex items-start gap-3 md:col-span-1">
              <label class="w-44 shrink-0 pt-2 text-xs font-medium text-slate-700">
                Debtor Type
              </label>
              <span class="pt-2 text-slate-400">:</span>
              <input
                type="text"
                readonly
                class="flex-1 rounded-md border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-800"
                :value="headVals.debtorType"
                placeholder="—"
              />
            </div>
            <div class="flex items-start gap-3 md:col-span-1">
              <label class="w-44 shrink-0 pt-2 text-xs font-medium text-slate-700">
                Debtor ID
              </label>
              <span class="pt-2 text-slate-400">:</span>
              <input
                type="text"
                readonly
                class="flex-1 rounded-md border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-800"
                :value="headVals.debtorId"
                placeholder="—"
              />
            </div>
            <div class="flex items-start gap-3 md:col-span-2">
              <label class="w-44 shrink-0 pt-2 text-xs font-medium text-slate-700">
                Debtor Name
              </label>
              <span class="pt-2 text-slate-400">:</span>
              <input
                type="text"
                readonly
                class="flex-1 rounded-md border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-800"
                :value="headVals.debtorName"
                placeholder="—"
              />
            </div>

            <div class="flex items-start gap-3 md:col-span-1">
              <label class="w-44 shrink-0 pt-2 text-xs font-medium text-slate-700">
                Our Ref
              </label>
              <span class="pt-2 text-slate-400">:</span>
              <input
                type="text"
                readonly
                class="flex-1 rounded-md border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-800"
                :value="headVals.ourRef"
                placeholder="—"
              />
            </div>
            <div class="flex items-start gap-3 md:col-span-1">
              <label class="w-44 shrink-0 pt-2 text-xs font-medium text-slate-700">
                Your Ref
              </label>
              <span class="pt-2 text-slate-400">:</span>
              <input
                type="text"
                readonly
                class="flex-1 rounded-md border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-800"
                :value="headVals.yourRef"
                placeholder="—"
              />
            </div>

            <div class="flex items-start gap-3 md:col-span-1">
              <label class="w-44 shrink-0 pt-2 text-xs font-medium text-slate-700">
                Total Amount
              </label>
              <span class="pt-2 text-slate-400">:</span>
              <input
                type="text"
                readonly
                class="flex-1 rounded-md border border-slate-200 bg-slate-50 px-3 py-2 text-sm tabular-nums text-slate-800"
                :value="headVals.total"
                placeholder="MYR 0.00"
              />
            </div>
            <div class="flex items-start gap-3 md:col-span-1">
              <label class="w-44 shrink-0 pt-2 text-xs font-medium text-slate-700">
                Balance
              </label>
              <span class="pt-2 text-slate-400">:</span>
              <input
                type="text"
                readonly
                class="flex-1 rounded-md border border-slate-200 bg-slate-50 px-3 py-2 text-sm tabular-nums text-slate-800"
                :value="headVals.balance"
                placeholder="MYR 0.00"
              />
            </div>

            <div class="flex items-start gap-3 md:col-span-2">
              <label class="w-44 shrink-0 pt-2 text-xs font-medium text-slate-700">
                Description / Narrative
              </label>
              <span class="pt-2 text-slate-400">:</span>
              <textarea
                readonly
                rows="2"
                class="flex-1 resize-none rounded-md border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-800"
                :value="headVals.description"
                placeholder="—"
              />
            </div>

            <div
              class="mt-2 grid gap-3 rounded-lg border border-slate-100 bg-slate-50/90 p-3 md:col-span-2 md:grid-cols-3"
            >
              <div>
                <span class="text-xs font-medium text-slate-600">Address</span>
                <p class="mt-1 text-sm text-slate-800">{{ headVals.address || "—" }}</p>
              </div>
              <div>
                <span class="text-xs font-medium text-slate-600">Postcode / City</span>
                <p class="mt-1 text-sm text-slate-800">{{ headVals.postcodeCity || "—" }}</p>
              </div>
              <div>
                <span class="text-xs font-medium text-slate-600">State / Country</span>
                <p class="mt-1 text-sm text-slate-800">{{ headVals.stateCountry || "—" }}</p>
              </div>
              <div>
                <span class="text-xs font-medium text-slate-600">Tel</span>
                <p class="mt-1 text-sm text-slate-800">{{ headVals.tel || "—" }}</p>
              </div>
              <div class="md:col-span-2">
                <span class="text-xs font-medium text-slate-600">Email</span>
                <p class="mt-1 break-all text-sm text-slate-800">{{ headVals.email || "—" }}</p>
              </div>
            </div>
          </section>
        </div>
      </article>

      <!-- Debit -->
      <article class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <header class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
          <h2 class="text-base font-semibold text-slate-900">Debit</h2>
          <button
            type="button"
            class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 shadow-sm hover:bg-slate-50 disabled:pointer-events-none disabled:opacity-40"
            :disabled="addLineBusy"
            @click="openAddLine('debit')"
          >
            <Plus class="h-3.5 w-3.5" />
            Add
          </button>
        </header>
        <div class="overflow-x-auto p-2">
          <table class="min-w-[1100px] w-full text-xs">
            <thead class="bg-violet-600 text-white">
              <tr class="text-left">
                <th class="whitespace-nowrap px-2 py-2">Cat.</th>
                <th class="whitespace-nowrap px-2 py-2">Item</th>
                <th class="whitespace-nowrap px-2 py-2">Fund</th>
                <th class="whitespace-nowrap px-2 py-2">Activity</th>
                <th class="whitespace-nowrap px-2 py-2">Account</th>
                <th class="whitespace-nowrap px-2 py-2">OUM</th>
                <th class="whitespace-nowrap px-2 py-2">CC</th>
                <th class="whitespace-nowrap px-2 py-2">Project</th>
                <th class="whitespace-nowrap px-2 py-2">Tax</th>
                <th class="whitespace-nowrap px-2 py-2 text-right">Tax Amt</th>
                <th class="whitespace-nowrap px-2 py-2 text-right">Amount</th>
                <th class="whitespace-nowrap px-2 py-2 text-right">Actions</th>
              </tr>
            </thead>
            <tbody v-if="detail">
              <tr v-if="rowsForDebit(detail).length === 0">
                <td colspan="12" class="px-2 py-6 text-center text-slate-500">
                  No debit lines.
                </td>
              </tr>
              <tr
                v-for="ln in rowsForDebit(detail)"
                :key="'d-' + ln.id"
                class="border-b border-slate-100 hover:bg-slate-50/80"
              >
                <td class="px-2 py-1.5">{{ ln.itemCategory ?? "—" }}</td>
                <td class="px-2 py-1.5">{{ ln.itemCode ?? "—" }}</td>
                <td class="px-2 py-1.5">{{ ln.fundType ?? "—" }}</td>
                <td class="px-2 py-1.5">{{ ln.activityCode ?? "—" }}</td>
                <td class="px-2 py-1.5">{{ ln.acctCode ?? "—" }}</td>
                <td class="px-2 py-1.5">{{ ln.ounCode ?? "—" }}</td>
                <td class="px-2 py-1.5">{{ ln.costCentre ?? "—" }}</td>
                <td class="px-2 py-1.5">{{ ln.projectNo ?? "—" }}</td>
                <td class="px-2 py-1.5">{{ ln.taxCode ?? "—" }}</td>
                <td class="px-2 py-1.5 text-right tabular-nums">{{ fmt(ln.taxAmt) }}</td>
                <td class="px-2 py-1.5 text-right tabular-nums font-medium">{{ fmt(ln.totalAmt) }}</td>
                <td class="px-2 py-1.5 text-right">
                  <button
                    v-if="canEditLines"
                    type="button"
                    class="inline-flex rounded p-1 text-slate-400 hover:bg-slate-100 hover:text-red-600 disabled:opacity-40"
                    :disabled="savingLine"
                    title="Remove line"
                    @click="onDeleteLine(ln)"
                  >
                    <Trash2 class="h-4 w-4" />
                  </button>
                </td>
              </tr>
            </tbody>
            <tbody v-else>
              <tr>
                <td colspan="12" class="px-2 py-6 text-center text-slate-500">
                  Select an invoice to load lines — or prepare a new invoice once migration is ready.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </article>

      <!-- Credit -->
      <article
        v-if="!detail || detail.splitDebitCredit"
        class="rounded-lg border border-slate-200 bg-white shadow-sm"
      >
        <header class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
          <h2 class="text-base font-semibold text-slate-900">Credit</h2>
          <button
            type="button"
            class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 shadow-sm hover:bg-slate-50 disabled:pointer-events-none disabled:opacity-40"
            :disabled="addLineBusy"
            @click="openAddLine('credit')"
          >
            <Plus class="h-3.5 w-3.5" />
            Add
          </button>
        </header>
        <div class="overflow-x-auto p-2">
          <table class="min-w-[1100px] w-full text-xs">
            <thead class="bg-violet-600 text-white">
              <tr class="text-left">
                <th class="whitespace-nowrap px-2 py-2">Cat.</th>
                <th class="whitespace-nowrap px-2 py-2">Item</th>
                <th class="whitespace-nowrap px-2 py-2">Fund</th>
                <th class="whitespace-nowrap px-2 py-2">Activity</th>
                <th class="whitespace-nowrap px-2 py-2">Account</th>
                <th class="whitespace-nowrap px-2 py-2">OUM</th>
                <th class="whitespace-nowrap px-2 py-2">CC</th>
                <th class="whitespace-nowrap px-2 py-2">Project</th>
                <th class="whitespace-nowrap px-2 py-2">Tax</th>
                <th class="whitespace-nowrap px-2 py-2 text-right">Tax Amt</th>
                <th class="whitespace-nowrap px-2 py-2 text-right">Amount</th>
                <th class="whitespace-nowrap px-2 py-2 text-right">Actions</th>
              </tr>
            </thead>
            <tbody v-if="detail">
              <tr v-if="rowsForCredit(detail).length === 0">
                <td colspan="12" class="px-2 py-6 text-center text-slate-500">
                  No credit lines.
                </td>
              </tr>
              <tr
                v-for="ln in rowsForCredit(detail)"
                :key="'c-' + ln.id"
                class="border-b border-slate-100 hover:bg-slate-50/80"
              >
                <td class="px-2 py-1.5">{{ ln.itemCategory ?? "—" }}</td>
                <td class="px-2 py-1.5">{{ ln.itemCode ?? "—" }}</td>
                <td class="px-2 py-1.5">{{ ln.fundType ?? "—" }}</td>
                <td class="px-2 py-1.5">{{ ln.activityCode ?? "—" }}</td>
                <td class="px-2 py-1.5">{{ ln.acctCode ?? "—" }}</td>
                <td class="px-2 py-1.5">{{ ln.ounCode ?? "—" }}</td>
                <td class="px-2 py-1.5">{{ ln.costCentre ?? "—" }}</td>
                <td class="px-2 py-1.5">{{ ln.projectNo ?? "—" }}</td>
                <td class="px-2 py-1.5">{{ ln.taxCode ?? "—" }}</td>
                <td class="px-2 py-1.5 text-right tabular-nums">{{ fmt(ln.taxAmt) }}</td>
                <td class="px-2 py-1.5 text-right tabular-nums font-medium">{{ fmt(ln.totalAmt) }}</td>
                <td class="px-2 py-1.5 text-right">
                  <button
                    v-if="canEditLines"
                    type="button"
                    class="inline-flex rounded p-1 text-slate-400 hover:bg-slate-100 hover:text-red-600 disabled:opacity-40"
                    :disabled="savingLine"
                    title="Remove line"
                    @click="onDeleteLine(ln)"
                  >
                    <Trash2 class="h-4 w-4" />
                  </button>
                </td>
              </tr>
            </tbody>
            <tbody v-else>
              <tr>
                <td colspan="12" class="px-2 py-6 text-center text-slate-500">
                  Select an invoice with credit splits to load rows.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </article>

      <!-- Process flow -->
      <article class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <header class="border-b border-slate-100 px-4 py-3">
          <h2 class="text-base font-semibold text-slate-900">Process flow</h2>
        </header>
        <div class="overflow-x-auto p-2">
          <table class="min-w-[640px] w-full text-xs">
            <thead class="bg-slate-100 text-slate-800">
              <tr class="text-left">
                <th class="px-2 py-2">Stage</th>
                <th class="px-2 py-2">Action</th>
                <th class="px-2 py-2">By</th>
                <th class="px-2 py-2">Date / Time</th>
                <th class="px-2 py-2">Remarks</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="!detail?.processFlow?.length">
                <td colspan="5" class="px-2 py-5 text-center text-slate-500">
                  Workflow history unavailable until the legacy engine is connected.
                  <template v-if="detail?.status">
                    Current status:
                    <strong>{{ detail.status }}</strong>
                  </template>
                </td>
              </tr>
              <template v-else>
                <tr v-for="(row, i) in detail!.processFlow" :key="'pf-' + i">
                  <td class="border-b border-slate-100 px-2 py-2" colspan="5">
                    {{
                      typeof row === "object" && row !== null
                        ? JSON.stringify(row)
                        : String(row)
                    }}
                  </td>
                </tr>
              </template>
            </tbody>
          </table>
        </div>
      </article>

      <!-- Actions -->
      <div
        class="flex flex-wrap items-center gap-3 border-t border-transparent pt-1"
      >
        <button
          type="button"
          class="inline-flex items-center gap-2 rounded-lg bg-violet-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-violet-700"
          @click="stubSave"
        >
          <Save class="h-4 w-4" />
          Save
        </button>
        <button
          type="button"
          class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-800 shadow-sm hover:bg-slate-50"
          @click="stubSubmit"
        >
          <Send class="h-4 w-4" />
          Submit
        </button>
        <p class="max-w-xl text-xs text-slate-500">
          <strong>Save</strong> and <strong>Submit</strong> remain stubs until workflow APIs exist.
          <template v-if="detail && !isDraftInvoice">
            Debit/Credit lines cannot be edited while status is {{ detail.status }}.
          </template>
          <template v-else-if="detail && isDraftInvoice && !editQuery">
            Listing <strong>Edit</strong> may open <code>?edit=1</code>; lines can still be added on DRAFT.
          </template>
          <template v-if="canAttemptEditUi">Draft edit flagged from listing.</template>
        </p>
      </div>

      <Teleport to="body">
        <div
          v-if="showLineModal"
          class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/45 p-4"
          role="dialog"
          aria-modal="true"
          aria-labelledby="mi-line-modal-title"
          @click.self="closeLineModal"
        >
          <div
            class="max-h-[min(92vh,760px)] w-full max-w-md overflow-hidden rounded-lg border border-violet-900/10 shadow-2xl"
            @click.stop
          >
            <div
              class="flex items-center justify-between bg-violet-600 px-4 py-3 text-white shadow-inner"
            >
              <h2 id="mi-line-modal-title" class="text-sm font-semibold tracking-tight">
                {{ modalHeaderTitle }}
              </h2>
              <div class="flex shrink-0 items-center gap-1.5">
                <span
                  class="rounded p-1.5 text-white/80"
                  aria-hidden="true"
                  title=""
                >
                  <Hash class="h-4 w-4" />
                </span>
                <span class="rounded p-1.5 text-white/80" aria-hidden="true">
                  <Copy class="h-4 w-4" />
                </span>
                <span class="rounded p-1.5 text-white/80" aria-hidden="true">
                  <PencilLine class="h-4 w-4" />
                </span>
                <button
                  type="button"
                  class="rounded p-1.5 hover:bg-white/15"
                  aria-label="Close"
                  @click="closeLineModal"
                >
                  <X class="h-4 w-4" />
                </button>
              </div>
            </div>

            <div class="max-h-[min(calc(92vh-7rem),640px)] overflow-y-auto bg-white px-4 py-4">
              <div v-if="!lineTxLocked" class="mb-4 flex flex-wrap gap-4 rounded-md bg-white/90 px-3 py-2.5 text-sm shadow-sm ring-1 ring-violet-200/60">
                <label class="inline-flex cursor-pointer items-center gap-2 font-medium text-slate-700">
                  <input v-model="modalTransactionType" type="radio" value="DT" class="text-violet-600" />
                  Debit (DT)
                </label>
                <label class="inline-flex cursor-pointer items-center gap-2 font-medium text-slate-700">
                  <input v-model="modalTransactionType" type="radio" value="CR" class="text-violet-600" />
                  Credit (CR)
                </label>
              </div>

              <div class="space-y-4">
                <div>
                  <label class="block text-xs font-semibold text-slate-700">Item</label>
                  <input v-model="lineDraft.itemCode" type="text" autocomplete="off" :class="modalFld" />
                </div>
                <div>
                  <label class="block text-xs font-semibold text-slate-700">Sub-Item</label>
                  <input v-model="lineDraft.itemCategory" type="text" autocomplete="off" :class="modalFld" />
                </div>
                <div>
                  <label class="block text-xs font-semibold text-slate-700">Fund Type</label>
                  <input v-model="lineDraft.fundType" type="text" autocomplete="off" :class="modalFld" />
                </div>
                <div>
                  <label class="block text-xs font-semibold text-slate-700">Activity Code</label>
                  <input v-model="lineDraft.activityCode" type="text" autocomplete="off" :class="modalFld" />
                </div>
                <div>
                  <label class="block text-xs font-semibold text-slate-700">PTJ</label>
                  <input v-model="lineDraft.ounCode" type="text" autocomplete="off" :class="modalFld" />
                </div>
                <div>
                  <label class="block text-xs font-semibold text-slate-700">Costcentre</label>
                  <input v-model="lineDraft.costCentre" type="text" autocomplete="off" :class="modalFld" />
                </div>
                <div>
                  <label class="block text-xs font-semibold text-slate-700">Project</label>
                  <input
                    v-model="lineDraft.projectNo"
                    type="text"
                    readonly
                    tabindex="-1"
                    placeholder="—"
                    title="Controlled by costing / project linkage in legacy."
                    autocomplete="off"
                    :class="modalFldReadonly"
                  />
                </div>
                <div>
                  <label class="block text-xs font-semibold text-slate-700">Account Code</label>
                  <input v-model="lineDraft.acctCode" type="text" autocomplete="off" :class="modalFld" />
                </div>
                <div>
                  <label class="block text-xs font-semibold text-slate-700">Inclusive?</label>
                  <select v-model="lineDraft.inclusive" :class="modalFld">
                    <option value="">Please select...</option>
                    <option value="Y">Yes</option>
                    <option value="N">No</option>
                  </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                  <div>
                    <label class="block text-xs font-semibold text-slate-700"><span class="text-red-600">*</span> Quantity</label>
                    <input
                      v-model="lineDraft.quantity"
                      type="text"
                      inputmode="decimal"
                      autocomplete="off"
                      :class="modalFld"
                    />
                  </div>
                  <div>
                    <label class="block text-xs font-semibold text-slate-700"><span class="text-red-600">*</span> Unit Price</label>
                    <div class="relative mt-1.5">
                      <span class="pointer-events-none absolute left-2.5 top-1/2 -translate-y-1/2 text-[11px] font-semibold uppercase text-slate-400">
                        MYR
                      </span>
                      <input
                        v-model="lineDraft.unitPrice"
                        type="text"
                        inputmode="decimal"
                        autocomplete="off"
                        :class="[modalFld, 'pl-[3.35rem]']"
                      />
                    </div>
                  </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                  <div>
                    <label class="block text-xs font-semibold text-slate-700">Tax Code</label>
                    <input v-model="lineDraft.taxCode" type="text" autocomplete="off" :class="modalFld" />
                  </div>
                  <div>
                    <label class="block text-xs font-semibold text-slate-700">Tax Amount</label>
                    <div class="relative mt-1.5">
                      <span class="pointer-events-none absolute left-2.5 top-1/2 -translate-y-1/2 text-[11px] font-semibold uppercase text-slate-400">
                        MYR
                      </span>
                      <input
                        v-model="lineDraft.taxAmt"
                        type="text"
                        inputmode="decimal"
                        autocomplete="off"
                        :class="[modalFld, 'pl-[3.35rem]']"
                      />
                    </div>
                  </div>
                </div>

                <div>
                  <label class="block text-xs font-semibold text-slate-700">Total Amount</label>
                  <div class="relative mt-1.5">
                    <span class="pointer-events-none absolute left-2.5 top-1/2 -translate-y-1/2 text-[11px] font-semibold uppercase text-slate-400">
                      MYR
                    </span>
                    <input
                      v-model="lineDraft.totalAmt"
                      type="text"
                      inputmode="decimal"
                      autocomplete="off"
                      :class="[modalFld, 'pl-[3.35rem] font-semibold text-slate-950']"
                    />
                  </div>
                  <p class="mt-2 text-[11px] leading-snug text-slate-600">
                    When Quantity and Unit Price are filled, Total updates automatically; otherwise enter Total Amount
                    alone.
                  </p>
                </div>
              </div>
            </div>

            <div class="flex justify-end gap-2 border-t border-violet-200/80 bg-white px-4 py-3">
              <button
                type="button"
                class="min-w-[5.5rem] rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-red-700"
                @click="closeLineModal"
              >
                Cancel
              </button>
              <button
                type="button"
                class="min-w-[5.5rem] rounded-md bg-violet-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-violet-700 disabled:opacity-50"
                :disabled="savingLine"
                @click="submitLineModal"
              >
                <span v-if="savingLine" class="inline-flex items-center gap-2">
                  <Loader2 class="h-4 w-4 animate-spin" />
                  OK
                </span>
                <span v-else>Ok</span>
              </button>
            </div>
          </div>
        </div>
      </Teleport>
    </div>
  </AdminLayout>
</template>
