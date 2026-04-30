<script setup lang="ts">
/**
 * Account Payable / Debit Note / Debit Note Form (MENUID 3548 / PAGEID 2676).
 *
 * Source: FIMS registry `kerisi-remaining-registry.generated.ts` entry 3548,
 * BL `QLA_API_AP_DN_FORM`, formSections "Debit Note Head".
 *
 * Layout mirrors the legacy two-column form:
 *   Col 1: Debit Note No · Bill No* · Payee Code/Name · Factoring Code/Name ·
 *           Rate Type · Currency Unit · Currency · Bill Currency Rate ·
 *           Total Amount (Currency) · Total Amount (MYR) · Current Currency Rate ·
 *           Debit Note Description*
 *   Col 2: Debit Note Approve Date · (blank) · Pay To Type · Factoring Type ·
 *           (blank for force-right-empty rows)
 *
 * Below the head: Debit datatable → Credit datatable → Total Debit / Total Credit
 * footer → Save / Submit action bar.
 *
 * Route: /admin/kerisi/m/3548
 *   ?dna_id=X           → edit existing DN
 *   ?bim_bills_no=X     → pre-select bill
 *   ?mode=view          → read-only
 */
import { computed, onMounted, onUnmounted, ref, watch } from "vue";
import {
  ChevronLeft,
  Loader2,
  Pencil,
  Save,
  Search,
  Send,
  X,
} from "lucide-vue-next";
import { useRoute, useRouter } from "vue-router";
import AdminLayout from "@/layouts/AdminLayout.vue";
import { useToast } from "@/composables/useToast";
import { useConfirmDialog } from "@/composables/useConfirmDialog";
import {
  getApDebitNoteForm,
  getApDebitNoteFormBillDetails,
  listApDebitNoteFormCurrencies,
  listApDebitNoteFormCustomerTypes,
  saveApDebitNoteForm,
  searchApDebitNoteFormBills,
  submitApDebitNoteForm,
  cancelApDebitNoteForm,
} from "@/api/cms";
import type {
  ApDnFormHead,
  ApDnFormLine,
  LookupOption,
} from "@/types";

const route = useRoute();
const router = useRouter();
const toast = useToast();
const { confirm } = useConfirmDialog();

const isCancellationForm = computed(() => route.path.includes("/3550"));
const isReadonly = computed(() => route.query.mode === "view" || isCancellationForm.value);
const dnaId = computed(() => {
  const raw = route.query.dna_id ?? route.query.id;
  return typeof raw === "string" && raw !== "" ? raw : null;
});
const preselectedBillNo = computed(() => {
  const raw = route.query.bim_bills_no;
  return typeof raw === "string" && raw !== "" ? raw : null;
});

// ── state ─────────────────────────────────────────────────────────────────
const loading = ref(false);
const saving = ref(false);
const submitting = ref(false);

const head = ref<ApDnFormHead>({
  dna_id: null,
  dna_dnnote_no: null,
  dna_approve_date: null,
  bim_bills_id: null,
  bim_bills_no: null,
  bim_payto_id: null,
  bim_payto_name: null,
  bim_payto_type: null,
  bim_factoring_code: null,
  bim_factoring_type: null,
  bim_rate_type: null,
  bim_currency_unit: null,
  bim_currency_code: null,
  bim_currency_rate: null,
  bim_ent_amt: null,
  bim_bill_amt: null,
  bim_current_rate: null,
  dna_description: null,
  dna_status_dn: "DRAFT",
  dna_cancel_by: null,
  dna_cancel_date: null,
  dna_cancel_reason: null,
});

const debitLines = ref<ApDnFormLine[]>([]);
const creditLines = ref<ApDnFormLine[]>([]);

// ── dropdown options ───────────────────────────────────────────────────────
const currencyOptions = ref<LookupOption[]>([]);
const customerTypeOptions = ref<LookupOption[]>([]);

// ── bill autosuggest combobox ──────────────────────────────────────────────
const billQuery = ref("");
const billResults = ref<{ id: string; billNo: string; paytoName: string; status: string }[]>([]);
const billOpen = ref(false);
const billLoading = ref(false);
let billTimer: ReturnType<typeof setTimeout> | null = null;

async function runBillSearch(term: string) {
  billLoading.value = true;
  try {
    const res = await searchApDebitNoteFormBills(term);
    billResults.value = res.data;
    billOpen.value = true;
  } catch {
    billResults.value = [];
  } finally {
    billLoading.value = false;
  }
}

function onBillInput(v: string) {
  billQuery.value = v;
  if (!v) clearBill();
  if (billTimer) clearTimeout(billTimer);
  billTimer = setTimeout(() => {
    void runBillSearch(v.trim());
  }, 350);
}

async function pickBill(opt: { id: string; billNo: string; paytoName: string; status: string }) {
  billQuery.value = opt.billNo;
  billOpen.value = false;
  await loadBillDetails(opt.billNo);
}

function clearBill() {
  billQuery.value = "";
  billResults.value = [];
  billOpen.value = false;
  head.value = {
    dna_id: null,
    dna_dnnote_no: null,
    dna_approve_date: null,
    bim_bills_id: null,
    bim_bills_no: null,
    bim_payto_id: null,
    bim_payto_name: null,
    bim_payto_type: null,
    bim_factoring_code: null,
    bim_factoring_type: null,
    bim_rate_type: null,
    bim_currency_unit: null,
    bim_currency_code: null,
    bim_currency_rate: null,
    bim_ent_amt: null,
    bim_bill_amt: null,
    bim_current_rate: null,
    dna_description: null,
    dna_status_dn: "DRAFT",
  dna_cancel_by: null,
  dna_cancel_date: null,
  dna_cancel_reason: null,
  };
  debitLines.value = [];
  creditLines.value = [];
}

function closeBillSoon() {
  window.setTimeout(() => (billOpen.value = false), 150);
}

async function loadBillDetails(billNo: string) {
  loading.value = true;
  try {
    const res = await getApDebitNoteFormBillDetails(billNo);
    const d = res.data;
    head.value = {
      ...head.value,
      bim_bills_id: d.bimBillsId ?? null,
      bim_bills_no: d.bimBillsNo ?? billNo,
      bim_payto_id: d.bimPaytoId ?? null,
      bim_payto_name: d.bimPaytoName ?? null,
      bim_payto_type: d.bimPaytoType ?? null,
      bim_factoring_code: d.bimFactoringCode ?? null,
      bim_factoring_type: d.bimFactoringType ?? null,
      bim_rate_type: d.bimRateType ?? null,
      bim_currency_unit: d.bimCurrencyUnit ?? null,
      bim_currency_code: d.bimCurrencyCode ?? null,
      bim_currency_rate: d.bimCurrencyRate ?? null,
      bim_ent_amt: d.bimEntAmt ?? null,
      bim_bill_amt: d.bimBillAmt ?? null,
      bim_current_rate: d.bimCurrentRate ?? null,
    };
    debitLines.value = (d.debitLines ?? []) as ApDnFormLine[];
    creditLines.value = (d.creditLines ?? []) as ApDnFormLine[];
  } catch (e) {
    toast.error("Load failed", e instanceof Error ? e.message : "Unable to load bill details.");
  } finally {
    loading.value = false;
  }
}

async function loadExistingDn() {
  if (!dnaId.value) return;
  loading.value = true;
  try {
    const res = await getApDebitNoteForm(dnaId.value);
    const d = res.data;
    head.value = {
      dna_id: d.dnaId ?? null,
      dna_dnnote_no: d.dnaDnnoteNo ?? null,
      dna_approve_date: d.dnaApproveDate ?? null,
      bim_bills_id: d.bimBillsId ?? null,
      bim_bills_no: d.bimBillsNo ?? null,
      bim_payto_id: d.bimPaytoId ?? null,
      bim_payto_name: d.bimPaytoName ?? null,
      bim_payto_type: d.bimPaytoType ?? null,
      bim_factoring_code: d.bimFactoringCode ?? null,
      bim_factoring_type: d.bimFactoringType ?? null,
      bim_rate_type: d.bimRateType ?? null,
      bim_currency_unit: d.bimCurrencyUnit ?? null,
      bim_currency_code: d.bimCurrencyCode ?? null,
      bim_currency_rate: d.bimCurrencyRate ?? null,
      bim_ent_amt: d.bimEntAmt ?? null,
      bim_bill_amt: d.bimBillAmt ?? null,
      bim_current_rate: d.bimCurrentRate ?? null,
      dna_description: d.dnaDescription ?? null,
      dna_status_dn: d.dnaStatusDn ?? "DRAFT",
      dna_cancel_by: d.dnaCancelBy ?? null,
      dna_cancel_date: d.dnaCancelDate ?? null,
      dna_cancel_reason: d.dnaCancelReason ?? null,
    };
    billQuery.value = head.value.bim_bills_no ?? "";
    debitLines.value = (d.debitLines ?? []) as ApDnFormLine[];
    creditLines.value = (d.creditLines ?? []) as ApDnFormLine[];
  } catch (e) {
    toast.error("Load failed", e instanceof Error ? e.message : "Unable to load debit note.");
  } finally {
    loading.value = false;
  }
}

// ── debit/credit search ────────────────────────────────────────────────────
const debitSearch = ref("");
const creditSearch = ref("");

const filteredDebit = computed(() => {
  const q = debitSearch.value.trim().toLowerCase();
  if (!q) return debitLines.value;
  return debitLines.value.filter((r) =>
    [r.ftyFundType, r.atActivityCode, r.ounCode, r.ccrCostcentre, r.acmAcctCode, r.itItemCode]
      .some((v) => String(v ?? "").toLowerCase().includes(q)),
  );
});

const filteredCredit = computed(() => {
  const q = creditSearch.value.trim().toLowerCase();
  if (!q) return creditLines.value;
  return creditLines.value.filter((r) =>
    [r.ftyFundType, r.atActivityCode, r.ounCode, r.ccrCostcentre, r.acmAcctCode, r.itItemCode]
      .some((v) => String(v ?? "").toLowerCase().includes(q)),
  );
});

// ── edit modal for Debit Note Amount ─────────────────────────────────────
const showModal = ref(false);
const editingLine = ref<ApDnFormLine | null>(null);
const editingDtType = ref<"dt" | "cr">("dt");
const modalDnEntAmt = ref<number>(0);
const modalDnAmt = ref<number>(0);

function openEditModal(line: ApDnFormLine, dtType: "dt" | "cr") {
  editingLine.value = line;
  editingDtType.value = dtType;
  modalDnEntAmt.value = Number(line.dedDnEntAmt ?? 0);
  modalDnAmt.value = Number(line.dedDnAmt ?? 0);
  showModal.value = true;
}

function saveModal() {
  if (!editingLine.value) return;
  editingLine.value.dedDnEntAmt = modalDnEntAmt.value;
  editingLine.value.dedDnAmt = modalDnAmt.value;
  // Recalculate balance
  editingLine.value.dedBalEntAmt = (Number(editingLine.value.bidEntAmt ?? 0) - modalDnEntAmt.value);
  editingLine.value.dedBalAmt = (Number(editingLine.value.bidAmt ?? 0) - modalDnAmt.value);
  showModal.value = false;
  editingLine.value = null;
}

function closeModal() {
  showModal.value = false;
  editingLine.value = null;
}

// ── totals ─────────────────────────────────────────────────────────────────
const totalDebit = computed(() =>
  debitLines.value.reduce((s, l) => s + Number(l.dedDnAmt ?? 0), 0),
);
const totalCredit = computed(() =>
  creditLines.value.reduce((s, l) => s + Number(l.dedDnAmt ?? 0), 0),
);

// ── canSubmit / canSave ────────────────────────────────────────────────────
const canSubmit = computed(() => {
  if (isReadonly.value) return false;
  const s = (head.value.dna_status_dn ?? "DRAFT").toUpperCase();
  return s === "DRAFT" || s === "ENTRY" || !dnaId.value;
});

// ── format helpers ─────────────────────────────────────────────────────────
function fmtMoney(v: number | null | undefined): string {
  const n = Number(v ?? 0);
  if (!Number.isFinite(n)) return "0.00";
  return new Intl.NumberFormat("en-MY", { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(n);
}

// ── save / submit ──────────────────────────────────────────────────────────
function validateHead(): boolean {
  if (!head.value.bim_bills_no) {
    toast.error("Missing bill", "Please select a Bill No first.");
    return false;
  }
  if (!head.value.dna_description?.trim()) {
    toast.error("Missing description", "Debit Note Description is required.");
    return false;
  }
  return true;
}

async function handleSave() {
  if (!validateHead()) return;
  saving.value = true;
  try {
    const res = await saveApDebitNoteForm({
      dnaId: head.value.dna_id,
      bimBillsNo: head.value.bim_bills_no,
      dnaDescription: head.value.dna_description,
      debitLines: debitLines.value,
      creditLines: creditLines.value,
    });
    head.value.dna_id = res.data.dnaId ?? null;
    head.value.dna_dnnote_no = res.data.dnaDnnoteNo ?? null;
    head.value.dna_status_dn = res.data.dnaStatusDn ?? "DRAFT";
    if (!dnaId.value && res.data.dnaId) {
      await router.replace({ path: "/admin/kerisi/m/3548", query: { dna_id: res.data.dnaId } });
    }
    toast.success("Saved", `Debit note ${head.value.dna_dnnote_no ?? res.data.dnaId} saved.`);
  } catch (e) {
    toast.error("Save failed", e instanceof Error ? e.message : "Unable to save.");
  } finally {
    saving.value = false;
  }
}

async function handleSubmit() {
  const ok = await confirm({
    title: "Submit debit note?",
    message: "Submit will save and mark this debit note as submitted.",
    confirmText: "Submit",
  });
  if (!ok) return;

  if (!dnaId.value) {
    if (!validateHead()) return;
    saving.value = true;
    try {
      const res = await saveApDebitNoteForm({
        dnaId: null,
        bimBillsNo: head.value.bim_bills_no,
        dnaDescription: head.value.dna_description,
        debitLines: debitLines.value,
        creditLines: creditLines.value,
      });
      head.value.dna_id = res.data.dnaId ?? null;
      head.value.dna_dnnote_no = res.data.dnaDnnoteNo ?? null;
      if (res.data.dnaId) {
        await router.replace({ path: "/admin/kerisi/m/3548", query: { dna_id: res.data.dnaId } });
      }
    } catch (e) {
      toast.error("Save failed", e instanceof Error ? e.message : "Unable to save before submit.");
      saving.value = false;
      return;
    } finally {
      saving.value = false;
    }
  }

  if (!dnaId.value && !head.value.dna_id) return;
  const id = (dnaId.value ?? head.value.dna_id) as string;

  submitting.value = true;
  try {
    const res = await submitApDebitNoteForm(id);
    head.value.dna_status_dn = (res.data as { dnaStatusDn?: string }).dnaStatusDn ?? "ENTRY";
    toast.success("Submitted", "Debit note submitted successfully.");
  } catch (e) {
    toast.error("Submit failed", e instanceof Error ? e.message : "Unable to submit.");
  } finally {
    submitting.value = false;
  }
}

async function handleCancelDebitNote() {
  if (!dnaId.value) return;
  if (!head.value.dna_cancel_reason?.trim()) {
    toast.error("Missing remark", "Cancel Remark is required.");
    return;
  }
  const ok = await confirm({
    title: "Cancel debit note?",
    message: "This will mark the debit note as cancelled.",
    confirmText: "Save",
    destructive: true,
  });
  if (!ok) return;
  saving.value = true;
  try {
    const res = await cancelApDebitNoteForm(dnaId.value, head.value.dna_cancel_reason.trim());
    head.value.dna_status_dn = (res.data as { dnaStatusDn?: string }).dnaStatusDn ?? "CANCEL";
    toast.success("Cancelled", "Debit note cancellation saved.");
    await router.replace({ path: "/admin/kerisi/m/3550", query: { dna_id: dnaId.value, mode: "view" } });
  } catch (e) {
    toast.error("Cancel failed", e instanceof Error ? e.message : "Unable to cancel debit note.");
  } finally {
    saving.value = false;
  }
}

function goBack() {
  void router.push(isCancellationForm.value ? "/admin/kerisi/m/3549" : "/admin/kerisi/m/3543");
}

// ── lifecycle ─────────────────────────────────────────────────────────────
onMounted(async () => {
  const [curRes, ctRes] = await Promise.allSettled([
    listApDebitNoteFormCurrencies(),
    listApDebitNoteFormCustomerTypes(),
  ]);
  if (curRes.status === "fulfilled") currencyOptions.value = curRes.value.data;
  if (ctRes.status === "fulfilled") customerTypeOptions.value = ctRes.value.data;

  if (dnaId.value) {
    await loadExistingDn();
  } else if (preselectedBillNo.value) {
    billQuery.value = preselectedBillNo.value;
    await loadBillDetails(preselectedBillNo.value);
  }
});

onUnmounted(() => {
  if (billTimer) clearTimeout(billTimer);
});

watch(() => route.query.dna_id, () => {
  if (dnaId.value) void loadExistingDn();
});
</script>

<template>
  <AdminLayout>
    <div class="space-y-4">
      <!-- Page header -->
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
        <h1 class="page-title">{{ isCancellationForm ? "Account Payable / Debit Note / Debit Note Cancellation Form" : "Account Payable / Debit Note / Debit Note Form" }}</h1>
      </div>

      <!-- Loading overlay -->
      <div v-if="loading" class="flex items-center justify-center py-8 text-slate-500">
        <Loader2 class="mr-2 h-5 w-5 animate-spin" />
        Loading…
      </div>

      <!-- Debit Note Head -->
      <article class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <header class="border-b border-slate-100 px-4 py-2.5">
          <h2 class="text-sm font-semibold text-slate-800">Debit Note Head</h2>
        </header>

        <section class="grid gap-x-8 gap-y-3 p-4 md:grid-cols-2">
          <!-- Row 1 left: Debit Note No -->
          <div class="flex items-center gap-3">
            <label class="w-44 shrink-0 text-xs font-medium text-slate-700">Debit Note No</label>
            <span class="text-slate-400">:</span>
            <input
              :value="head.dna_dnnote_no ?? ''"
              type="text"
              readonly
              placeholder="Auto Assigned"
              class="flex-1 rounded-md border border-slate-200 bg-slate-50 px-3 py-1.5 text-sm text-slate-500"
            />
          </div>
          <!-- Row 1 right: Debit Note Approve Date -->
          <div class="flex items-center gap-3">
            <label class="w-44 shrink-0 text-xs font-medium text-slate-700">Debit Note Approve Date</label>
            <span class="text-slate-400">:</span>
            <input
              v-model="head.dna_approve_date"
              type="date"
              :disabled="isReadonly || true"
              class="flex-1 rounded-md border border-slate-200 bg-slate-50 px-3 py-1.5 text-sm disabled:bg-slate-50"
            />
          </div>

          <!-- Row 2 left: Bill No (autosuggest) -->
          <div class="flex items-start gap-3">
            <label class="w-44 shrink-0 pt-1.5 text-xs font-medium text-slate-700">
              Bill No <span class="text-rose-500">*</span>
            </label>
            <span class="pt-1.5 text-slate-400">:</span>
            <div class="relative flex-1">
              <Search class="pointer-events-none absolute left-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400" />
              <input
                :value="billQuery"
                type="search"
                :disabled="isReadonly || !!dnaId"
                placeholder="Search Bill No…"
                autocomplete="off"
                class="w-full rounded-md border border-slate-300 py-1.5 pl-8 pr-8 text-sm disabled:bg-slate-50"
                @input="onBillInput(($event.target as HTMLInputElement).value)"
                @focus="billOpen = true; void runBillSearch(billQuery.trim())"
                @blur="closeBillSoon"
              />
              <button
                v-if="billQuery && !isReadonly && !dnaId"
                type="button"
                class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-700"
                @click="clearBill"
              >
                <X class="h-3.5 w-3.5" />
              </button>
              <div
                v-if="billOpen && !isReadonly && !dnaId"
                class="absolute left-0 right-0 top-full z-20 mt-1 max-h-60 overflow-y-auto rounded-md border border-slate-200 bg-white text-sm shadow-lg"
              >
                <div v-if="billLoading" class="flex items-center gap-2 px-3 py-2 text-xs text-slate-500">
                  <Loader2 class="h-3.5 w-3.5 animate-spin" />
                  Searching…
                </div>
                <div v-else-if="billResults.length === 0" class="px-3 py-2 text-xs text-slate-500">
                  {{ billQuery ? "No matches." : "Type to search bill no." }}
                </div>
                <button
                  v-for="opt in billResults"
                  :key="opt.id"
                  type="button"
                  class="block w-full cursor-pointer px-3 py-1.5 text-left text-xs hover:bg-slate-50"
                  @mousedown.prevent="pickBill(opt)"
                >
                  <div class="font-medium text-slate-800">{{ opt.billNo }}</div>
                  <div class="text-slate-500">{{ opt.paytoName }}</div>
                </button>
              </div>
            </div>
          </div>
          <!-- Row 2 right: (blank) -->
          <div aria-hidden="true"></div>

          <!-- Row 3 left: Payee Code/Name -->
          <div class="flex items-start gap-3">
            <label class="w-44 shrink-0 pt-1.5 text-xs font-medium text-slate-700">Payee Code/ Name</label>
            <span class="pt-1.5 text-slate-400">:</span>
            <textarea
              :value="head.bim_payto_name ?? ''"
              readonly
              rows="2"
              class="flex-1 rounded-md border border-slate-200 bg-slate-50 px-3 py-1.5 text-sm text-slate-600"
            />
          </div>
          <!-- Row 3 right: Pay To Type -->
          <div class="flex items-center gap-3">
            <label class="w-44 shrink-0 text-xs font-medium text-slate-700">Pay To Type</label>
            <span class="text-slate-400">:</span>
            <select
              :value="head.bim_payto_type ?? ''"
              disabled
              class="flex-1 rounded-md border border-slate-200 bg-slate-50 px-3 py-1.5 text-sm disabled:bg-slate-50"
            >
              <option value=""></option>
              <option v-for="opt in customerTypeOptions" :key="opt.value" :value="opt.value">
                {{ opt.label }}
              </option>
            </select>
          </div>

          <!-- Row 4 left: Factoring Code/Name -->
          <div class="flex items-start gap-3">
            <label class="w-44 shrink-0 pt-1.5 text-xs font-medium text-slate-700">Factoring Code/Name</label>
            <span class="pt-1.5 text-slate-400">:</span>
            <textarea
              :value="head.bim_factoring_code ?? ''"
              readonly
              rows="2"
              class="flex-1 rounded-md border border-slate-200 bg-slate-50 px-3 py-1.5 text-sm text-slate-600"
            />
          </div>
          <!-- Row 4 right: Factoring Type -->
          <div class="flex items-center gap-3">
            <label class="w-44 shrink-0 text-xs font-medium text-slate-700">Factoring Type</label>
            <span class="text-slate-400">:</span>
            <select
              :value="head.bim_factoring_type ?? ''"
              disabled
              class="flex-1 rounded-md border border-slate-200 bg-slate-50 px-3 py-1.5 text-sm disabled:bg-slate-50"
            >
              <option value=""></option>
              <option v-for="opt in customerTypeOptions" :key="opt.value" :value="opt.value">
                {{ opt.label }}
              </option>
            </select>
          </div>

          <!-- Row 5 left: Rate Type (force-right-empty) -->
          <div class="flex items-center gap-3">
            <label class="w-44 shrink-0 text-xs font-medium text-slate-700">Rate Type</label>
            <span class="text-slate-400">:</span>
            <input
              :value="head.bim_rate_type ?? ''"
              type="text"
              readonly
              class="flex-1 rounded-md border border-slate-200 bg-slate-50 px-3 py-1.5 text-sm text-slate-600"
            />
          </div>
          <div aria-hidden="true"></div>

          <!-- Row 6 left: Currency Unit -->
          <div class="flex items-center gap-3">
            <label class="w-44 shrink-0 text-xs font-medium text-slate-700">Currency Unit</label>
            <span class="text-slate-400">:</span>
            <input
              :value="head.bim_currency_unit ?? ''"
              type="text"
              readonly
              class="flex-1 rounded-md border border-slate-200 bg-slate-50 px-3 py-1.5 text-sm text-slate-600"
            />
          </div>
          <div aria-hidden="true"></div>

          <!-- Row 7 left: Currency -->
          <div class="flex items-center gap-3">
            <label class="w-44 shrink-0 text-xs font-medium text-slate-700">Currency</label>
            <span class="text-slate-400">:</span>
            <select
              :value="head.bim_currency_code ?? ''"
              disabled
              class="flex-1 rounded-md border border-slate-200 bg-slate-50 px-3 py-1.5 text-sm disabled:bg-slate-50"
            >
              <option value=""></option>
              <option v-for="opt in currencyOptions" :key="opt.value" :value="opt.value">
                {{ opt.label }}
              </option>
            </select>
          </div>
          <div aria-hidden="true"></div>

          <!-- Row 8 left: Bill Currency Rate -->
          <div class="flex items-center gap-3">
            <label class="w-44 shrink-0 text-xs font-medium text-slate-700">Bill Currency Rate</label>
            <span class="text-slate-400">:</span>
            <input
              :value="head.bim_currency_rate ?? ''"
              type="text"
              readonly
              class="flex-1 rounded-md border border-slate-200 bg-slate-50 px-3 py-1.5 text-sm text-slate-600"
            />
          </div>
          <div aria-hidden="true"></div>

          <!-- Row 9 left: Total Amount (Currency) -->
          <div class="flex items-center gap-3">
            <label class="w-44 shrink-0 text-xs font-medium text-slate-700">Total Amount (Currency)</label>
            <span class="text-slate-400">:</span>
            <input
              :value="head.bim_ent_amt != null ? fmtMoney(head.bim_ent_amt) : ''"
              type="text"
              readonly
              class="flex-1 rounded-md border border-slate-200 bg-slate-50 px-3 py-1.5 text-right text-sm tabular-nums text-slate-600"
            />
          </div>
          <div aria-hidden="true"></div>

          <!-- Row 10 left: Total Amount (MYR) -->
          <div class="flex items-center gap-3">
            <label class="w-44 shrink-0 text-xs font-medium text-slate-700">Total Amount</label>
            <span class="text-slate-400">:</span>
            <div class="flex flex-1 items-stretch">
              <span class="inline-flex items-center rounded-l-md border border-r-0 border-slate-200 bg-slate-100 px-2.5 text-xs font-medium text-slate-500">MYR</span>
              <input
                :value="head.bim_bill_amt != null ? fmtMoney(head.bim_bill_amt) : ''"
                type="text"
                readonly
                class="flex-1 rounded-r-md border border-slate-200 bg-slate-50 px-3 py-1.5 text-right text-sm tabular-nums text-slate-600"
              />
            </div>
          </div>
          <div aria-hidden="true"></div>

          <!-- Row 11 left: Current Currency Rate -->
          <div class="flex items-center gap-3">
            <label class="w-44 shrink-0 text-xs font-medium text-slate-700">Current Currency Rate</label>
            <span class="text-slate-400">:</span>
            <input
              :value="head.bim_current_rate ?? ''"
              type="text"
              readonly
              class="flex-1 rounded-md border border-slate-200 bg-slate-50 px-3 py-1.5 text-sm text-slate-600"
            />
          </div>
          <div aria-hidden="true"></div>

          <!-- Row 12: Debit Note Description (spans full width) -->
          <div class="flex items-start gap-3 md:col-span-2">
            <label class="w-44 shrink-0 pt-1.5 text-xs font-medium text-slate-700">
              Debit Note Description <span class="text-rose-500">*</span>
            </label>
            <span class="pt-1.5 text-slate-400">:</span>
            <textarea
              v-model="head.dna_description"
              :disabled="isReadonly"
              rows="3"
              class="flex-1 rounded-md border border-slate-300 px-3 py-1.5 text-sm uppercase disabled:bg-slate-50"
              placeholder="Enter debit note description…"
            />
          </div>
        </section>
      </article>

      <!-- Debit datatable -->
      <article class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <header class="border-b border-slate-100 px-4 py-2.5">
          <h2 class="text-sm font-semibold text-slate-800">Debit</h2>
        </header>
        <!-- Search bar (kitchen-sink style) -->
        <div class="flex items-center justify-end border-b border-slate-100 bg-slate-50/60 px-4 py-2">
          <div class="relative w-52">
            <Search class="pointer-events-none absolute left-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400" />
            <input
              v-model="debitSearch"
              type="search"
              placeholder="Search…"
              class="h-8 w-full rounded-md border border-slate-300 bg-white py-1 pl-8 pr-8 text-xs focus:outline-none focus:ring-2 focus:ring-slate-200"
            />
            <button
              v-if="debitSearch"
              type="button"
              class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-700"
              @click="debitSearch = ''"
            >
              <X class="h-3.5 w-3.5" />
            </button>
          </div>
        </div>
        <div class="overflow-x-auto">
          <table class="admin-table-kitchen min-w-[1400px]">
            <thead class="admin-table-thead-sticky">
              <tr>
                <th class="px-2 py-2 text-left text-xs font-semibold">No</th>
                <th class="px-2 py-2 text-left text-xs font-semibold">Fund</th>
                <th class="px-2 py-2 text-left text-xs font-semibold">Activity</th>
                <th class="px-2 py-2 text-left text-xs font-semibold">PTJ</th>
                <th class="px-2 py-2 text-left text-xs font-semibold">Cost Center</th>
                <th class="px-2 py-2 text-left text-xs font-semibold">Code SO</th>
                <th class="px-2 py-2 text-left text-xs font-semibold">Item Code</th>
                <th class="px-2 py-2 text-left text-xs font-semibold">Account Code</th>
                <th class="px-2 py-2 text-left text-xs font-semibold">Budget Code</th>
                <th class="px-2 py-2 text-right text-xs font-semibold">Balance Bill<br />(Currency)</th>
                <th class="px-2 py-2 text-right text-xs font-semibold">Balance Bill<br />(MYR)</th>
                <th class="px-2 py-2 text-right text-xs font-semibold">Debit Note Amount<br />(Currency)</th>
                <th class="px-2 py-2 text-right text-xs font-semibold">Debit Note Amount<br />(MYR)</th>
                <th class="px-2 py-2 text-right text-xs font-semibold">Balance<br />(Currency)</th>
                <th class="px-2 py-2 text-right text-xs font-semibold">Balance<br />(MYR)</th>
                <th class="px-2 py-2 text-left text-xs font-semibold">Action</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="filteredDebit.length === 0">
                <td colspan="16" class="px-3 py-8 text-center text-xs text-slate-500">No records</td>
              </tr>
              <tr
                v-for="(line, i) in filteredDebit"
                :key="`dt-${line.dedId ?? i}`"
                class="border-b border-slate-100 hover:bg-slate-50/60"
              >
                <td class="px-2 py-1.5 text-xs">{{ i + 1 }}</td>
                <td class="px-2 py-1.5 text-xs">{{ line.ftyFundType ?? "-" }}</td>
                <td class="px-2 py-1.5 text-xs">{{ line.atActivityCode ?? "-" }}</td>
                <td class="px-2 py-1.5 text-xs">{{ line.ounCode ?? "-" }}</td>
                <td class="px-2 py-1.5 text-xs">{{ line.ccrCostcentre ?? "-" }}</td>
                <td class="px-2 py-1.5 text-xs">{{ line.soCode ?? "-" }}</td>
                <td class="px-2 py-1.5 text-xs">{{ line.itItemCode ?? "-" }}</td>
                <td class="px-2 py-1.5 text-xs">{{ line.acmAcctCode ?? "-" }}</td>
                <td class="px-2 py-1.5 text-xs">{{ line.bdgBudgetCode ?? "-" }}</td>
                <td class="px-2 py-1.5 text-right text-xs tabular-nums">{{ fmtMoney(line.bidEntAmt) }}</td>
                <td class="px-2 py-1.5 text-right text-xs tabular-nums">{{ fmtMoney(line.bidAmt) }}</td>
                <td class="px-2 py-1.5 text-right text-xs tabular-nums">{{ fmtMoney(line.dedDnEntAmt) }}</td>
                <td class="px-2 py-1.5 text-right text-xs tabular-nums">{{ fmtMoney(line.dedDnAmt) }}</td>
                <td class="px-2 py-1.5 text-right text-xs tabular-nums">{{ fmtMoney(line.dedBalEntAmt) }}</td>
                <td class="px-2 py-1.5 text-right text-xs tabular-nums">{{ fmtMoney(line.dedBalAmt) }}</td>
                <td class="px-2 py-1.5 text-xs">
                  <button
                    v-if="!isReadonly"
                    type="button"
                    title="Edit Debit Note Amount"
                    class="rounded p-1 text-slate-500 hover:bg-slate-100 hover:text-slate-700"
                    @click="openEditModal(line, 'dt')"
                  >
                    <Pencil class="h-3.5 w-3.5" />
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </article>

      <!-- Credit datatable -->
      <article class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <header class="border-b border-slate-100 px-4 py-2.5">
          <h2 class="text-sm font-semibold text-slate-800">Credit</h2>
        </header>
        <!-- Search bar -->
        <div class="flex items-center justify-end border-b border-slate-100 bg-slate-50/60 px-4 py-2">
          <div class="relative w-52">
            <Search class="pointer-events-none absolute left-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400" />
            <input
              v-model="creditSearch"
              type="search"
              placeholder="Search…"
              class="h-8 w-full rounded-md border border-slate-300 bg-white py-1 pl-8 pr-8 text-xs focus:outline-none focus:ring-2 focus:ring-slate-200"
            />
            <button
              v-if="creditSearch"
              type="button"
              class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-700"
              @click="creditSearch = ''"
            >
              <X class="h-3.5 w-3.5" />
            </button>
          </div>
        </div>
        <div class="overflow-x-auto">
          <table class="admin-table-kitchen min-w-[1400px]">
            <thead class="admin-table-thead-sticky">
              <tr>
                <th class="px-2 py-2 text-left text-xs font-semibold">No</th>
                <th class="px-2 py-2 text-left text-xs font-semibold">Fund</th>
                <th class="px-2 py-2 text-left text-xs font-semibold">Activity</th>
                <th class="px-2 py-2 text-left text-xs font-semibold">PTJ</th>
                <th class="px-2 py-2 text-left text-xs font-semibold">Cost Center</th>
                <th class="px-2 py-2 text-left text-xs font-semibold">Code SO</th>
                <th class="px-2 py-2 text-left text-xs font-semibold">Item Code</th>
                <th class="px-2 py-2 text-left text-xs font-semibold">Account Code</th>
                <th class="px-2 py-2 text-left text-xs font-semibold">Budget Code</th>
                <th class="px-2 py-2 text-right text-xs font-semibold">Balance Bill<br />(Currency)</th>
                <th class="px-2 py-2 text-right text-xs font-semibold">Balance Bill<br />(MYR)</th>
                <th class="px-2 py-2 text-right text-xs font-semibold">Debit Note Amount<br />(Currency)</th>
                <th class="px-2 py-2 text-right text-xs font-semibold">Debit Note Amount<br />(MYR)</th>
                <th class="px-2 py-2 text-right text-xs font-semibold">Balance<br />(Currency)</th>
                <th class="px-2 py-2 text-right text-xs font-semibold">Balance<br />(MYR)</th>
                <th class="px-2 py-2 text-left text-xs font-semibold">Action</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="filteredCredit.length === 0">
                <td colspan="16" class="px-3 py-8 text-center text-xs text-slate-500">No records</td>
              </tr>
              <tr
                v-for="(line, i) in filteredCredit"
                :key="`cr-${line.dedId ?? i}`"
                class="border-b border-slate-100 hover:bg-slate-50/60"
              >
                <td class="px-2 py-1.5 text-xs">{{ i + 1 }}</td>
                <td class="px-2 py-1.5 text-xs">{{ line.ftyFundType ?? "-" }}</td>
                <td class="px-2 py-1.5 text-xs">{{ line.atActivityCode ?? "-" }}</td>
                <td class="px-2 py-1.5 text-xs">{{ line.ounCode ?? "-" }}</td>
                <td class="px-2 py-1.5 text-xs">{{ line.ccrCostcentre ?? "-" }}</td>
                <td class="px-2 py-1.5 text-xs">{{ line.soCode ?? "-" }}</td>
                <td class="px-2 py-1.5 text-xs">{{ line.itItemCode ?? "-" }}</td>
                <td class="px-2 py-1.5 text-xs">{{ line.acmAcctCode ?? "-" }}</td>
                <td class="px-2 py-1.5 text-xs">{{ line.bdgBudgetCode ?? "-" }}</td>
                <td class="px-2 py-1.5 text-right text-xs tabular-nums">{{ fmtMoney(line.bidEntAmt) }}</td>
                <td class="px-2 py-1.5 text-right text-xs tabular-nums">{{ fmtMoney(line.bidAmt) }}</td>
                <td class="px-2 py-1.5 text-right text-xs tabular-nums">{{ fmtMoney(line.dedDnEntAmt) }}</td>
                <td class="px-2 py-1.5 text-right text-xs tabular-nums">{{ fmtMoney(line.dedDnAmt) }}</td>
                <td class="px-2 py-1.5 text-right text-xs tabular-nums">{{ fmtMoney(line.dedBalEntAmt) }}</td>
                <td class="px-2 py-1.5 text-right text-xs tabular-nums">{{ fmtMoney(line.dedBalAmt) }}</td>
                <td class="px-2 py-1.5 text-xs">
                  <button
                    v-if="!isReadonly"
                    type="button"
                    title="Edit Debit Note Amount"
                    class="rounded p-1 text-slate-500 hover:bg-slate-100 hover:text-slate-700"
                    @click="openEditModal(line, 'cr')"
                  >
                    <Pencil class="h-3.5 w-3.5" />
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </article>

      <!-- Total Debit / Total Credit row -->
      <div class="grid grid-cols-2 gap-x-8">
        <div class="flex items-center gap-3 rounded-lg border border-slate-200 bg-white px-4 py-3">
          <label class="w-28 shrink-0 text-xs font-medium text-slate-700">Total Debit</label>
          <span class="text-slate-400">:</span>
          <div class="flex flex-1 items-stretch">
            <span class="inline-flex items-center rounded-l-md border border-r-0 border-slate-300 bg-slate-100 px-2.5 text-xs font-medium text-slate-600">MYR</span>
            <input
              :value="fmtMoney(totalDebit)"
              type="text"
              readonly
              class="flex-1 rounded-r-md border border-slate-300 bg-slate-50 px-3 py-1.5 text-right text-sm tabular-nums text-slate-700"
            />
          </div>
        </div>
        <div class="flex items-center gap-3 rounded-lg border border-slate-200 bg-white px-4 py-3">
          <label class="w-28 shrink-0 text-xs font-medium text-slate-700">Total Credit</label>
          <span class="text-slate-400">:</span>
          <div class="flex flex-1 items-stretch">
            <span class="inline-flex items-center rounded-l-md border border-r-0 border-slate-300 bg-slate-100 px-2.5 text-xs font-medium text-slate-600">MYR</span>
            <input
              :value="fmtMoney(totalCredit)"
              type="text"
              readonly
              class="flex-1 rounded-r-md border border-slate-300 bg-slate-50 px-3 py-1.5 text-right text-sm tabular-nums text-slate-700"
            />
          </div>
        </div>
      </div>

      <!-- Cancel Information -->
      <article v-if="isCancellationForm" class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <header class="border-b border-slate-100 px-4 py-2.5">
          <h2 class="text-sm font-semibold text-slate-800">Cancel Information</h2>
        </header>
        <section class="grid gap-x-8 gap-y-3 p-4 md:grid-cols-2">
          <div class="flex items-center gap-3">
            <label class="w-44 shrink-0 text-xs font-medium text-slate-700">Cancel By</label>
            <span class="text-slate-400">:</span>
            <input :value="head.dna_cancel_by ?? ''" type="text" readonly class="flex-1 rounded-md border border-slate-200 bg-slate-50 px-3 py-1.5 text-sm text-slate-600" />
          </div>
          <div class="flex items-center gap-3">
            <label class="w-44 shrink-0 text-xs font-medium text-slate-700">Cancel Date</label>
            <span class="text-slate-400">:</span>
            <input v-model="head.dna_cancel_date" type="date" disabled class="flex-1 rounded-md border border-slate-200 bg-slate-50 px-3 py-1.5 text-sm" />
          </div>
          <div class="flex items-start gap-3 md:col-span-2">
            <label class="w-44 shrink-0 pt-1.5 text-xs font-medium text-slate-700">Cancel Remark <span class="text-rose-500">*</span></label>
            <span class="pt-1.5 text-slate-400">:</span>
            <textarea v-model="head.dna_cancel_reason" :disabled="route.query.mode === 'view'" rows="3" class="flex-1 rounded-md border border-slate-300 px-3 py-1.5 text-sm disabled:bg-slate-50" />
          </div>
        </section>
      </article>

      <!-- Action bar -->
      <div v-if="!route.query.mode || route.query.mode !== 'view'" class="flex items-center justify-center gap-2 py-2">
        <button
          type="button"
          class="inline-flex items-center gap-1.5 rounded-md bg-slate-800 px-5 py-2 text-sm font-medium text-white hover:bg-slate-700 disabled:opacity-60"
          :disabled="saving"
          @click="isCancellationForm ? handleCancelDebitNote() : handleSave"
        >
          <Loader2 v-if="saving" class="h-4 w-4 animate-spin" />
          <Save v-else class="h-4 w-4" />
          {{ isCancellationForm ? "Save" : "Save" }}
        </button>
        <button
          v-if="canSubmit && !isCancellationForm"
          type="button"
          class="inline-flex items-center gap-1.5 rounded-md bg-emerald-600 px-5 py-2 text-sm font-medium text-white hover:bg-emerald-700 disabled:opacity-60"
          :disabled="submitting"
          @click="handleSubmit"
        >
          <Loader2 v-if="submitting" class="h-4 w-4 animate-spin" />
          <Send v-else class="h-4 w-4" />
          Submit
        </button>
      </div>

      <!-- Debit Note Amount edit modal -->
      <Teleport to="body">
        <div
          v-if="showModal"
          class="fixed inset-0 z-50 flex items-center justify-center bg-black/40"
          @click.self="closeModal"
        >
          <div class="w-full max-w-md rounded-lg bg-white shadow-xl">
            <div class="flex items-center justify-between rounded-t-lg bg-indigo-500 px-4 py-3">
              <h3 class="text-sm font-semibold text-white">Debit Note Amount</h3>
              <button type="button" class="text-white hover:text-indigo-200" @click="closeModal">
                <X class="h-4 w-4" />
              </button>
            </div>
            <div class="grid gap-3 p-4">
              <!-- Currency (disabled) -->
              <div class="grid grid-cols-[140px_10px_1fr] items-center gap-1">
                <label class="text-xs font-medium text-slate-700">Currency</label>
                <span class="text-slate-400">:</span>
                <select disabled class="rounded-md border border-slate-200 bg-slate-50 px-3 py-1.5 text-sm disabled:bg-slate-50">
                  <option :value="head.bim_currency_code ?? ''">
                    {{ head.bim_currency_code || "—" }}
                  </option>
                </select>
              </div>
              <!-- Balance Bill (Currency) -->
              <div class="grid grid-cols-[140px_10px_1fr] items-center gap-1">
                <label class="text-xs font-medium text-slate-700">Balance Bill (Currency)</label>
                <span class="text-slate-400">:</span>
                <input
                  :value="editingLine ? fmtMoney(editingLine.bidEntAmt) : ''"
                  type="text"
                  readonly
                  class="rounded-md border border-slate-200 bg-slate-50 px-3 py-1.5 text-right text-sm tabular-nums"
                />
              </div>
              <!-- Balance Bill (MYR) -->
              <div class="grid grid-cols-[140px_10px_1fr] items-center gap-1">
                <label class="text-xs font-medium text-slate-700">Balance Bill</label>
                <span class="text-slate-400">:</span>
                <div class="flex items-stretch">
                  <span class="inline-flex items-center rounded-l-md border border-r-0 border-slate-200 bg-slate-100 px-2 text-xs font-medium text-slate-600">MYR</span>
                  <input
                    :value="editingLine ? fmtMoney(editingLine.bidAmt) : ''"
                    type="text"
                    readonly
                    class="flex-1 rounded-r-md border border-slate-200 bg-slate-50 px-3 py-1.5 text-right text-sm tabular-nums"
                  />
                </div>
              </div>
              <!-- DN Amount (Currency) -->
              <div class="grid grid-cols-[140px_10px_1fr] items-center gap-1">
                <label class="text-xs font-medium text-slate-700">
                  Debit Note Amount (Currency) <span class="text-rose-500">*</span>
                </label>
                <span class="text-slate-400">:</span>
                <input
                  v-model.number="modalDnEntAmt"
                  type="number"
                  step="0.01"
                  min="0"
                  class="rounded-md border border-slate-300 px-3 py-1.5 text-right text-sm tabular-nums focus:outline-none focus:ring-2 focus:ring-indigo-300"
                />
              </div>
              <!-- DN Amount (MYR) -->
              <div class="grid grid-cols-[140px_10px_1fr] items-center gap-1">
                <label class="text-xs font-medium text-slate-700">
                  Debit Note Amount <span class="text-rose-500">*</span>
                </label>
                <span class="text-slate-400">:</span>
                <div class="flex items-stretch">
                  <span class="inline-flex items-center rounded-l-md border border-r-0 border-slate-300 bg-slate-100 px-2 text-xs font-medium text-slate-600">MYR</span>
                  <input
                    v-model.number="modalDnAmt"
                    type="number"
                    step="0.01"
                    min="0"
                    class="flex-1 rounded-r-md border border-slate-300 px-3 py-1.5 text-right text-sm tabular-nums focus:outline-none focus:ring-2 focus:ring-indigo-300"
                  />
                </div>
              </div>
            </div>
            <div class="flex items-center justify-end gap-2 rounded-b-lg border-t border-slate-200 px-4 py-3">
              <button
                type="button"
                class="inline-flex items-center rounded-md border border-rose-300 bg-white px-4 py-1.5 text-sm font-medium text-rose-700 hover:bg-rose-50"
                @click="closeModal"
              >
                Cancel
              </button>
              <button
                type="button"
                class="inline-flex items-center rounded-md bg-blue-600 px-4 py-1.5 text-sm font-medium text-white hover:bg-blue-700"
                @click="saveModal"
              >
                Ok
              </button>
            </div>
          </div>
        </div>
      </Teleport>
    </div>
  </AdminLayout>
</template>
