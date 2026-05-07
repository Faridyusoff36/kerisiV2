<script setup lang="ts">
import { ref, computed, onMounted } from "vue";
import { useRoute } from "vue-router";
import AdminLayout from "@/layouts/AdminLayout.vue";
import { useToast } from "@/composables/useToast";
import {
  Pencil, Copy, Trash2, Users, FileText, Receipt, ListChecks,
  X, Loader2, Hash, ChevronDown,
} from "lucide-vue-next";
import { apiRequest } from "@/api/client";

const route = useRoute();
const toast = useToast();

// ── Types ─────────────────────────────────────────────────────────────────────
interface VoucherMaster {
  vmaVoucherId: number;
  vmaVoucherNo: string;
  vmaVchStatus: string;
  vmaCurrencyCode: string | null;
  vmaTotalAmt: string;
  vmaEntAmt: string | null;
  vmaPaytoType: string | null;
  vmaPaytoId: string | null;
  vmaPaytoName: string | null;
  vmaExchangeTypeCode: string | null;
  vmaConversionRate: string | null;
  vmaVchDescription: string | null;
  vmaSubsystemCode: string | null;
  creditAccountCode: string | null;
}

interface VoucherDetail {
  vdeVoucherDetlId: number;
  bimBillsNo: string | null;
  vdePaytoType: string | null;
  vdePaytoId: string | null;
  vdePaytoName: string | null;
  vdeBankName: string | null;
  vdeBankAcctno: string | null;
  ftyFundType: string | null;
  ftyFundDesc: string | null;
  atActivityCode: string | null;
  atActivityDesc: string | null;
  ounCode: string | null;
  ounDesc: string | null;
  ccrCostcentre: string | null;
  ccrCostcentreDesc: string | null;
  acmAcctCode: string | null;
  acmAcctDesc: string | null;
  vdeAmount: string | null;
  vdeFactoringType: string | null;
  vdeFactoringId: string | null;
  vdeFactoringName: string | null;
  vdeFactBankName: string | null;
  vdeFactBankAcctno: string | null;
  vdeStatus: string | null;
  vdePaymentNo: string | null;
}

// ── Load voucher data ─────────────────────────────────────────────────────────
const loading     = ref(false);
const master      = ref<VoucherMaster | null>(null);
const debitRows   = ref<VoucherDetail[]>([]);
const creditRows  = ref<VoucherDetail[]>([]);

const debitTotal  = computed(() => debitRows.value.reduce((s, r) => s + parseFloat(r.vdeAmount ?? "0"), 0));
const creditTotal = computed(() => creditRows.value.reduce((s, r) => s + parseFloat(r.vdeAmount ?? "0"), 0));

function fmt(v: string | number | null | undefined): string {
  const n = typeof v === "number" ? v : parseFloat(String(v ?? "0"));
  return isNaN(n) ? "0.00" : n.toLocaleString("en-MY", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function concat(...parts: (string | null | undefined)[]): string {
  return parts.filter(Boolean).join(" - ");
}

async function loadVoucher(vno: string) {
  loading.value = true;
  master.value = null;
  debitRows.value = [];
  creditRows.value = [];
  try {
    const res = await apiRequest<{
      data: { master: VoucherMaster; debit: VoucherDetail[]; credit: VoucherDetail[] };
    }>(`/api/kerisi/ap/voucher-detail?voucher_no=${encodeURIComponent(vno)}`);
    master.value   = res.data.master;
    debitRows.value  = res.data.debit ?? [];
    creditRows.value = res.data.credit ?? [];
  } catch {
    toast.error("Failed to load voucher data.");
  } finally {
    loading.value = false;
  }
}

onMounted(() => {
  const vno = route.query.voucher_no as string | undefined;
  if (vno) {
    loadVoucher(vno);
  }
});

// ── Edit modal (Voucher Information / Creditor Info) ─────────────────────────
type ManualMode = "auto" | "manual";
const editModal     = ref(false);
const editRow       = ref<VoucherDetail | null>(null);
const editSaving    = ref(false);
const editPayeeMode = ref<ManualMode>("auto");
const editBankMode  = ref<ManualMode>("auto");
const editFactorMode = ref<ManualMode>("manual");

const editForm = ref({
  payeeType: "", payeeCode: "", payeeName: "",
  bankName: "", bankAcctno: "",
  factoringType: "", factoringId: "", factoringName: "",
  factoringBankName: "", factoringBankAcctno: "",
});

function openEditModal(row: VoucherDetail) {
  editRow.value = row;
  editPayeeMode.value  = row.vdePaytoId ? "auto" : "manual";
  editBankMode.value   = row.vdeBankAcctno ? "auto" : "manual";
  editFactorMode.value = row.vdeFactoringName ? "manual" : "auto";
  editForm.value = {
    payeeType:          row.vdePaytoType ?? "",
    payeeCode:          row.vdePaytoId ?? "",
    payeeName:          row.vdePaytoName ?? "",
    bankName:           row.vdeBankName ?? "",
    bankAcctno:         row.vdeBankAcctno ?? "",
    factoringType:      row.vdeFactoringType ?? "",
    factoringId:        row.vdeFactoringId ?? "",
    factoringName:      row.vdeFactoringName ?? "",
    factoringBankName:  row.vdeFactBankName ?? "",
    factoringBankAcctno: row.vdeFactBankAcctno ?? "",
  };
  editModal.value = true;
}

async function saveEditModal() {
  if (!editRow.value) return;
  editSaving.value = true;
  try {
    await apiRequest(`/api/kerisi/ap/voucher-detail-item/${editRow.value.vdeVoucherDetlId}`, {
      method: "PUT",
      body: JSON.stringify({
        vde_payto_type:       editForm.value.payeeType,
        vde_payto_id:         editPayeeMode.value === "auto" ? editForm.value.payeeCode : null,
        vde_payto_name:       editForm.value.payeeName,
        vde_bank_name:        editBankMode.value === "auto" ? editForm.value.bankName : null,
        vde_bank_acctno:      editForm.value.bankAcctno,
        vde_factoring_type:   editForm.value.factoringType,
        vde_factoring_id:     editFactorMode.value === "auto" ? editForm.value.factoringId : null,
        vde_factoring_name:   editForm.value.factoringName,
        vde_fact_bank_name:   editForm.value.factoringBankName,
        vde_fact_bank_acctno: editForm.value.factoringBankAcctno,
      }),
    });
    toast.success("Saved successfully.");
    editModal.value = false;
    if (master.value) loadVoucher(master.value.vmaVoucherNo);
  } catch {
    toast.error("Failed to save.");
  } finally {
    editSaving.value = false;
  }
}
</script>

<template>
  <AdminLayout>
    <div class="space-y-4">
      <h1 class="page-title">Account Payable / Voucher / Voucher Information Creditor</h1>

      <!-- ── Voucher content (when loaded) ─────────────────────────────────── -->
      <template v-if="!loading && master">

        <!-- ── Voucher Details master form (kitchen sink card) ──────────────── -->
        <article class="rounded-lg border border-slate-200 bg-white shadow-sm">
          <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
            <div class="flex items-center gap-2">
              <FileText class="h-4 w-4 text-slate-500" />
              <h2 class="text-base font-semibold text-slate-900">Voucher Details</h2>
            </div>
            <div class="flex items-center gap-1">
              <button type="button" class="rounded-lg p-1.5 text-slate-500 transition-colors hover:bg-slate-100" title="Reference">
                <Hash class="h-4 w-4" />
              </button>
              <button type="button" class="rounded-lg p-1.5 text-slate-500 transition-colors hover:bg-slate-100" title="Copy">
                <Copy class="h-4 w-4" />
              </button>
              <button type="button" class="rounded-lg p-1.5 text-slate-500 transition-colors hover:bg-slate-100" title="Edit">
                <Pencil class="h-4 w-4" />
              </button>
            </div>
          </div>

          <div class="p-5">
            <div class="grid grid-cols-1 gap-x-8 gap-y-3 text-sm md:grid-cols-2">
              <!-- Row 1 — Voucher Number | Status -->
              <div class="flex items-center gap-3">
                <label class="w-36 shrink-0 text-sm font-semibold text-slate-700">Voucher Number</label>
                <span class="text-slate-400">:</span>
                <input :value="master.vmaVoucherNo" disabled class="flex-1 rounded-md border border-slate-200 bg-slate-50 px-3 py-1.5 text-sm text-slate-700" />
              </div>
              <div class="flex items-center gap-3">
                <label class="w-32 shrink-0 text-sm font-semibold text-slate-700">Status</label>
                <span class="text-slate-400">:</span>
                <input :value="master.vmaVchStatus || ''" disabled class="flex-1 rounded-md border border-slate-200 bg-slate-50 px-3 py-1.5 text-sm font-medium text-slate-700" />
              </div>

              <!-- Row 2 — Currency | Amount -->
              <div class="flex items-center gap-3">
                <label class="w-36 shrink-0 text-sm font-semibold text-slate-700">Currency</label>
                <span class="text-slate-400">:</span>
                <input :value="master.vmaCurrencyCode || ''" disabled class="flex-1 rounded-md border border-slate-200 bg-slate-50 px-3 py-1.5 text-sm text-slate-700" />
              </div>
              <div class="flex items-center gap-3">
                <label class="w-32 shrink-0 text-sm font-semibold text-slate-700">Amount</label>
                <span class="text-slate-400">:</span>
                <div class="flex flex-1 overflow-hidden rounded-md border border-slate-200">
                  <span class="bg-slate-100 px-3 py-1.5 text-sm font-medium text-slate-600">{{ master.vmaCurrencyCode || 'MYR' }}</span>
                  <input :value="fmt(master.vmaTotalAmt)" disabled class="w-full bg-slate-50 px-3 py-1.5 text-right text-sm tabular-nums text-slate-800" />
                </div>
              </div>

              <!-- Row 3 — Payee (full width) -->
              <div class="flex items-center gap-3 md:col-span-2">
                <label class="w-36 shrink-0 text-sm font-semibold text-slate-700">Payee</label>
                <span class="text-slate-400">:</span>
                <input :value="concat(master.vmaPaytoId, master.vmaPaytoName)" disabled class="flex-1 rounded-md border border-slate-200 bg-slate-50 px-3 py-1.5 text-sm text-slate-700" />
              </div>

              <!-- Row 4 — Exchange Type | Exchange Rate -->
              <div class="flex items-center gap-3">
                <label class="w-36 shrink-0 text-sm font-semibold text-slate-700">Exchange Type</label>
                <span class="text-slate-400">:</span>
                <div class="relative flex-1">
                  <input :value="master.vmaExchangeTypeCode || ''" disabled class="w-full rounded-md border border-slate-200 bg-slate-50 px-3 py-1.5 pr-8 text-sm text-slate-700" />
                  <ChevronDown class="pointer-events-none absolute right-2 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400" />
                </div>
              </div>
              <div class="flex items-center gap-3">
                <label class="w-32 shrink-0 text-sm font-semibold text-slate-700">Exchange Rate</label>
                <span class="text-slate-400">:</span>
                <input :value="master.vmaConversionRate || ''" disabled class="flex-1 rounded-md border border-slate-200 bg-slate-50 px-3 py-1.5 text-sm text-slate-700" />
              </div>

              <!-- Row 5 — Contact Number (full width) -->
              <div class="flex items-center gap-3 md:col-span-2">
                <label class="w-36 shrink-0 text-sm font-semibold text-slate-700">Contact Number</label>
                <span class="text-slate-400">:</span>
                <input value="" disabled class="flex-1 rounded-md border border-slate-200 bg-slate-50 px-3 py-1.5 text-sm text-slate-700" />
              </div>

              <!-- Row 6 — Description (full width, textarea) -->
              <div class="flex items-start gap-3 md:col-span-2">
                <label class="w-36 shrink-0 pt-1.5 text-sm font-semibold text-slate-700">Description</label>
                <span class="pt-1.5 text-slate-400">:</span>
                <textarea :value="master.vmaVchDescription || ''" disabled rows="2" class="flex-1 resize-none rounded-md border border-slate-200 bg-slate-50 px-3 py-1.5 text-sm text-slate-700"></textarea>
              </div>

              <!-- Row 7 — Subsystem No (full width) -->
              <div class="flex items-center gap-3 md:col-span-2">
                <label class="w-36 shrink-0 text-sm font-semibold text-slate-700">Subsystem No.</label>
                <span class="text-slate-400">:</span>
                <input :value="master.vmaSubsystemCode || ''" disabled class="flex-1 rounded-md border border-slate-200 bg-slate-50 px-3 py-1.5 text-sm text-slate-700" />
              </div>

              <!-- Row 8 — Credit Account Code (full width with x button) -->
              <div class="flex items-center gap-3 md:col-span-2">
                <label class="w-36 shrink-0 text-sm font-semibold text-slate-700">Credit Account Code</label>
                <span class="text-slate-400">:</span>
                <div class="relative flex-1">
                  <input :value="master.creditAccountCode || ''" disabled class="w-full rounded-md border border-slate-200 bg-slate-50 px-3 py-1.5 pr-14 text-sm text-slate-700" />
                  <div class="pointer-events-none absolute inset-y-0 right-2 flex items-center gap-1 text-slate-400">
                    <X class="h-3.5 w-3.5" />
                    <span class="text-slate-300">|</span>
                    <ChevronDown class="h-3.5 w-3.5" />
                  </div>
                </div>
              </div>
            </div>
          </div>
        </article>

        <!-- ── Debit table (kitchen sink card) ──────────────────────────────── -->
        <article class="rounded-lg border border-slate-200 bg-white shadow-sm">
          <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
            <div class="flex items-center gap-2">
              <Receipt class="h-4 w-4 text-rose-600" />
              <h2 class="text-base font-semibold text-slate-900">Debit</h2>
              <span class="ml-1 inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">
                {{ debitRows.length }} {{ debitRows.length === 1 ? 'record' : 'records' }}
              </span>
            </div>
          </div>
          <div class="space-y-3 p-4">
            <div class="overflow-x-auto rounded-lg border border-slate-200">
              <table class="admin-table-kitchen w-full min-w-[1600px] text-sm">
                <thead class="admin-table-thead-sticky">
                  <tr class="border-b border-slate-200 text-left">
                    <th class="sticky left-0 z-20 bg-slate-50 px-3 py-2 text-xs font-semibold uppercase tracking-wider text-slate-500">No</th>
                    <th class="px-3 py-2 text-xs font-semibold uppercase tracking-wider text-slate-500">Bill No.</th>
                    <th class="px-3 py-2 text-xs font-semibold uppercase tracking-wider text-slate-500">Payee</th>
                    <th class="px-3 py-2 text-xs font-semibold uppercase tracking-wider text-slate-500">Payee Bank Account</th>
                    <th class="px-3 py-2 text-xs font-semibold uppercase tracking-wider text-slate-500">Fund</th>
                    <th class="px-3 py-2 text-xs font-semibold uppercase tracking-wider text-slate-500">Activity</th>
                    <th class="px-3 py-2 text-xs font-semibold uppercase tracking-wider text-slate-500">PTJ</th>
                    <th class="px-3 py-2 text-xs font-semibold uppercase tracking-wider text-slate-500">Cost Centre</th>
                    <th class="px-3 py-2 text-xs font-semibold uppercase tracking-wider text-slate-500">Account Code</th>
                    <th class="px-3 py-2 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Amount</th>
                    <th class="px-3 py-2 text-xs font-semibold uppercase tracking-wider text-slate-500">Factoring</th>
                    <th class="px-3 py-2 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Action</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-if="debitRows.length === 0">
                    <td colspan="12" class="px-3 py-8 text-center text-sm text-slate-500">No debit records found.</td>
                  </tr>
                  <tr
                    v-for="(row, idx) in debitRows"
                    :key="row.vdeVoucherDetlId"
                    class="border-b border-slate-100 hover:bg-slate-50"
                  >
                    <td class="sticky left-0 z-10 bg-white px-3 py-2 tabular-nums text-slate-500">{{ idx + 1 }}</td>
                    <td class="px-3 py-2">{{ row.bimBillsNo || '-' }}</td>
                    <td class="px-3 py-2">{{ concat(row.vdePaytoId, row.vdePaytoName) || '-' }}</td>
                    <td class="px-3 py-2">{{ concat(row.vdeBankName, row.vdeBankAcctno) || '-' }}</td>
                    <td class="px-3 py-2">{{ concat(row.ftyFundType, row.ftyFundDesc) || '-' }}</td>
                    <td class="px-3 py-2">{{ concat(row.atActivityCode, row.atActivityDesc) || '-' }}</td>
                    <td class="px-3 py-2">{{ concat(row.ounCode, row.ounDesc) || '-' }}</td>
                    <td class="px-3 py-2">{{ concat(row.ccrCostcentre, row.ccrCostcentreDesc) || '-' }}</td>
                    <td class="px-3 py-2 font-medium text-slate-900">{{ concat(row.acmAcctCode, row.acmAcctDesc) || '-' }}</td>
                    <td class="px-3 py-2 text-right font-medium tabular-nums text-slate-900">{{ fmt(row.vdeAmount) }}</td>
                    <td class="px-3 py-2 text-xs leading-relaxed text-slate-600">
                      <div><span class="text-slate-400">Type :</span> {{ row.vdeFactoringType || 'null' }}</div>
                      <div><span class="text-slate-400">Name :</span> {{ row.vdeFactoringName || 'null' }}</div>
                      <div><span class="text-slate-400">Bank :</span> {{ row.vdeFactBankName || 'null' }}</div>
                      <div><span class="text-slate-400">Acc No :</span> {{ row.vdeFactBankAcctno || 'null' }}</div>
                    </td>
                    <td class="px-3 py-2">
                      <div class="flex justify-end gap-1">
                        <button type="button" class="rounded-lg p-1.5 text-slate-500 transition-colors hover:bg-slate-200" title="Edit Creditor Info" @click="openEditModal(row)">
                          <Pencil class="h-3.5 w-3.5" />
                        </button>
                        <button type="button" class="rounded-lg p-1.5 text-slate-500 transition-colors hover:bg-slate-200" title="Copy" @click="toast.info('Copy not yet implemented.')">
                          <Copy class="h-3.5 w-3.5" />
                        </button>
                        <button type="button" class="rounded-lg p-1.5 text-red-500 transition-colors hover:bg-red-100" title="Delete" @click="toast.info('Delete detail line not yet implemented.')">
                          <Trash2 class="h-3.5 w-3.5" />
                        </button>
                      </div>
                    </td>
                  </tr>
                  <tr v-if="debitRows.length > 0" class="border-t-2 border-slate-200 bg-slate-50 font-semibold">
                    <td colspan="9" class="px-3 py-2 text-right text-xs uppercase tracking-wider text-slate-500">Grand Total</td>
                    <td class="px-3 py-2 text-right tabular-nums text-slate-900">{{ fmt(debitTotal) }}</td>
                    <td colspan="2"></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </article>

        <!-- ── Credit table (kitchen sink card) ─────────────────────────────── -->
        <article class="rounded-lg border border-slate-200 bg-white shadow-sm">
          <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
            <div class="flex items-center gap-2">
              <ListChecks class="h-4 w-4 text-emerald-600" />
              <h2 class="text-base font-semibold text-slate-900">Credit</h2>
              <span class="ml-1 inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">
                {{ creditRows.length }} {{ creditRows.length === 1 ? 'record' : 'records' }}
              </span>
            </div>
          </div>
          <div class="space-y-3 p-4">
            <div class="overflow-x-auto rounded-lg border border-slate-200">
              <table class="admin-table-kitchen w-full min-w-[1600px] text-sm">
                <thead class="admin-table-thead-sticky">
                  <tr class="border-b border-slate-200 text-left">
                    <th class="sticky left-0 z-20 bg-slate-50 px-3 py-2 text-xs font-semibold uppercase tracking-wider text-slate-500">No</th>
                    <th class="px-3 py-2 text-xs font-semibold uppercase tracking-wider text-slate-500">Bill No.</th>
                    <th class="px-3 py-2 text-xs font-semibold uppercase tracking-wider text-slate-500">Payee</th>
                    <th class="px-3 py-2 text-xs font-semibold uppercase tracking-wider text-slate-500">Payee Bank Account</th>
                    <th class="px-3 py-2 text-xs font-semibold uppercase tracking-wider text-slate-500">Fund</th>
                    <th class="px-3 py-2 text-xs font-semibold uppercase tracking-wider text-slate-500">Activity</th>
                    <th class="px-3 py-2 text-xs font-semibold uppercase tracking-wider text-slate-500">PTJ</th>
                    <th class="px-3 py-2 text-xs font-semibold uppercase tracking-wider text-slate-500">Cost Centre</th>
                    <th class="px-3 py-2 text-xs font-semibold uppercase tracking-wider text-slate-500">Account Code</th>
                    <th class="px-3 py-2 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Amount</th>
                    <th class="px-3 py-2 text-xs font-semibold uppercase tracking-wider text-slate-500">Factoring</th>
                    <th class="px-3 py-2 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Action</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-if="creditRows.length === 0">
                    <td colspan="12" class="px-3 py-8 text-center text-sm text-slate-500">No credit records found.</td>
                  </tr>
                  <tr
                    v-for="(row, idx) in creditRows"
                    :key="row.vdeVoucherDetlId"
                    class="border-b border-slate-100 hover:bg-slate-50"
                  >
                    <td class="sticky left-0 z-10 bg-white px-3 py-2 tabular-nums text-slate-500">{{ idx + 1 }}</td>
                    <td class="px-3 py-2">{{ row.bimBillsNo || '-' }}</td>
                    <td class="px-3 py-2">{{ concat(row.vdePaytoId, row.vdePaytoName) || '-' }}</td>
                    <td class="px-3 py-2">{{ concat(row.vdeBankName, row.vdeBankAcctno) || '-' }}</td>
                    <td class="px-3 py-2">{{ concat(row.ftyFundType, row.ftyFundDesc) || '-' }}</td>
                    <td class="px-3 py-2">{{ concat(row.atActivityCode, row.atActivityDesc) || '-' }}</td>
                    <td class="px-3 py-2">{{ concat(row.ounCode, row.ounDesc) || '-' }}</td>
                    <td class="px-3 py-2">{{ concat(row.ccrCostcentre, row.ccrCostcentreDesc) || '-' }}</td>
                    <td class="px-3 py-2 font-medium text-slate-900">{{ concat(row.acmAcctCode, row.acmAcctDesc) || '-' }}</td>
                    <td class="px-3 py-2 text-right font-medium tabular-nums text-slate-900">{{ fmt(row.vdeAmount) }}</td>
                    <td class="px-3 py-2 text-xs leading-relaxed text-slate-600">
                      <div><span class="text-slate-400">Type :</span> {{ row.vdeFactoringType || 'null' }}</div>
                      <div><span class="text-slate-400">Name :</span> {{ row.vdeFactoringName || 'null' }}</div>
                      <div><span class="text-slate-400">Bank :</span> {{ row.vdeFactBankName || 'null' }}</div>
                      <div><span class="text-slate-400">Acc No :</span> {{ row.vdeFactBankAcctno || 'null' }}</div>
                    </td>
                    <td class="px-3 py-2">
                      <div class="flex justify-end gap-1">
                        <button type="button" class="rounded-lg p-1.5 text-slate-500 transition-colors hover:bg-slate-200" title="Edit Creditor Info" @click="openEditModal(row)">
                          <Users class="h-3.5 w-3.5" />
                        </button>
                      </div>
                    </td>
                  </tr>
                  <tr v-if="creditRows.length > 0" class="border-t-2 border-slate-200 bg-slate-50 font-semibold">
                    <td colspan="9" class="px-3 py-2 text-right text-xs uppercase tracking-wider text-slate-500">Grand Total</td>
                    <td class="px-3 py-2 text-right tabular-nums text-slate-900">{{ fmt(creditTotal) }}</td>
                    <td colspan="2"></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </article>

      </template>

      <!-- ── Loading skeleton ───────────────────────────────────────────────── -->
      <article v-if="loading" class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center gap-2 px-4 py-12 text-sm text-slate-500">
          <Loader2 class="h-4 w-4 animate-spin" />
          Loading voucher data…
        </div>
      </article>

      <!-- ── Empty state ────────────────────────────────────────────────────── -->
      <article v-if="!loading && !master" class="rounded-lg border border-dashed border-slate-300 bg-slate-50 shadow-sm">
        <div class="flex flex-col items-center justify-center gap-2 px-4 py-16 text-sm text-slate-500">
          <FileText class="h-8 w-8 text-slate-300" />
          <p>No voucher selected. Open this page from <strong class="text-slate-700">Voucher Listing</strong> to view voucher details.</p>
        </div>
      </article>
    </div>
  </AdminLayout>

  <!-- ── Edit Creditor Info Modal (kitchen sink dialog) ────────────────────── -->
  <Teleport to="body">
    <div
      v-if="editModal"
      class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4"
      @click.self="editModal = false"
    >
      <div class="w-full max-w-xl overflow-hidden rounded-lg border border-slate-200 bg-white shadow-xl">
        <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
          <div class="flex items-center gap-2">
            <Pencil class="h-4 w-4 text-slate-500" />
            <h2 class="text-base font-semibold text-slate-900">Voucher Information</h2>
          </div>
          <button type="button" class="rounded-lg p-1.5 text-slate-500 transition-colors hover:bg-slate-100" @click="editModal = false" aria-label="Close">
            <X class="h-4 w-4" />
          </button>
        </div>

        <div class="space-y-5 p-4 text-sm">
          <!-- Voucher No (read-only) -->
          <div class="flex items-center gap-3">
            <label class="w-32 shrink-0 text-xs font-medium uppercase tracking-wider text-slate-400">Voucher No</label>
            <input :value="master?.vmaVoucherNo" disabled class="flex-1 rounded-lg border border-slate-200 bg-slate-50 px-3 py-1.5 text-sm text-slate-500" />
          </div>

          <!-- Payee section -->
          <div class="space-y-3 rounded-lg border border-slate-200 p-3">
            <div class="flex items-center gap-2">
              <Users class="h-3.5 w-3.5 text-slate-500" />
              <h3 class="text-sm font-semibold text-slate-900">Payee Information</h3>
            </div>

            <div class="flex items-center gap-3">
              <label class="w-32 shrink-0 text-xs font-medium uppercase tracking-wider text-slate-400">Key In Mode</label>
              <div class="flex gap-4">
                <label class="flex cursor-pointer items-center gap-1.5 text-slate-700">
                  <input type="radio" v-model="editPayeeMode" value="auto" class="accent-slate-900" /> Auto
                </label>
                <label class="flex cursor-pointer items-center gap-1.5 text-slate-700">
                  <input type="radio" v-model="editPayeeMode" value="manual" class="accent-slate-900" /> Manual
                </label>
              </div>
            </div>

            <div class="flex items-center gap-3">
              <label class="w-32 shrink-0 text-xs font-medium uppercase tracking-wider text-slate-400">Payee Type</label>
              <input v-model="editForm.payeeType" placeholder="e.g. S / O" class="flex-1 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm shadow-sm focus:border-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-200" />
            </div>

            <div v-if="editPayeeMode === 'auto'" class="flex items-center gap-3">
              <label class="w-32 shrink-0 text-xs font-medium uppercase tracking-wider text-slate-400">Payee Code</label>
              <input v-model="editForm.payeeCode" placeholder="IC / Staff ID" class="flex-1 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm shadow-sm focus:border-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-200" />
            </div>

            <div class="flex items-center gap-3">
              <label class="w-32 shrink-0 text-xs font-medium uppercase tracking-wider text-slate-400">Payee Name</label>
              <input v-model="editForm.payeeName" placeholder="Payee name" class="flex-1 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm shadow-sm focus:border-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-200" />
            </div>
          </div>

          <!-- Bank section -->
          <div class="space-y-3 rounded-lg border border-slate-200 p-3">
            <div class="flex items-center gap-2">
              <Receipt class="h-3.5 w-3.5 text-slate-500" />
              <h3 class="text-sm font-semibold text-slate-900">Bank Information</h3>
            </div>

            <div class="flex items-center gap-3">
              <label class="w-32 shrink-0 text-xs font-medium uppercase tracking-wider text-slate-400">Key In Mode</label>
              <div class="flex gap-4">
                <label class="flex cursor-pointer items-center gap-1.5 text-slate-700">
                  <input type="radio" v-model="editBankMode" value="auto" class="accent-slate-900" /> Auto
                </label>
                <label class="flex cursor-pointer items-center gap-1.5 text-slate-700">
                  <input type="radio" v-model="editBankMode" value="manual" class="accent-slate-900" /> Manual
                </label>
              </div>
            </div>

            <div v-if="editBankMode === 'auto'" class="flex items-center gap-3">
              <label class="w-32 shrink-0 text-xs font-medium uppercase tracking-wider text-slate-400">Bank Name</label>
              <input v-model="editForm.bankName" placeholder="Bank name" class="flex-1 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm shadow-sm focus:border-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-200" />
            </div>

            <div class="flex items-center gap-3">
              <label class="w-32 shrink-0 text-xs font-medium uppercase tracking-wider text-slate-400">Bank Acct No</label>
              <input v-model="editForm.bankAcctno" placeholder="Account number" class="flex-1 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm shadow-sm focus:border-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-200" />
            </div>
          </div>

          <!-- Factoring section -->
          <div class="space-y-3 rounded-lg border border-slate-200 p-3">
            <div class="flex items-center gap-2">
              <ListChecks class="h-3.5 w-3.5 text-slate-500" />
              <h3 class="text-sm font-semibold text-slate-900">Factoring Information</h3>
            </div>

            <div class="flex items-center gap-3">
              <label class="w-32 shrink-0 text-xs font-medium uppercase tracking-wider text-slate-400">Key In Mode</label>
              <div class="flex gap-4">
                <label class="flex cursor-pointer items-center gap-1.5 text-slate-700">
                  <input type="radio" v-model="editFactorMode" value="auto" class="accent-slate-900" /> Auto
                </label>
                <label class="flex cursor-pointer items-center gap-1.5 text-slate-700">
                  <input type="radio" v-model="editFactorMode" value="manual" class="accent-slate-900" /> Manual
                </label>
              </div>
            </div>

            <div class="flex items-center gap-3">
              <label class="w-32 shrink-0 text-xs font-medium uppercase tracking-wider text-slate-400">Type</label>
              <input v-model="editForm.factoringType" placeholder="e.g. C" class="flex-1 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm shadow-sm focus:border-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-200" />
            </div>

            <div v-if="editFactorMode === 'auto'" class="flex items-center gap-3">
              <label class="w-32 shrink-0 text-xs font-medium uppercase tracking-wider text-slate-400">Code</label>
              <input v-model="editForm.factoringId" placeholder="Factoring ID" class="flex-1 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm shadow-sm focus:border-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-200" />
            </div>

            <div class="flex items-center gap-3">
              <label class="w-32 shrink-0 text-xs font-medium uppercase tracking-wider text-slate-400">Name</label>
              <input v-model="editForm.factoringName" placeholder="Name" class="flex-1 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm shadow-sm focus:border-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-200" />
            </div>

            <div class="flex items-center gap-3">
              <label class="w-32 shrink-0 text-xs font-medium uppercase tracking-wider text-slate-400">Bank Name</label>
              <input v-model="editForm.factoringBankName" placeholder="Bank name" class="flex-1 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm shadow-sm focus:border-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-200" />
            </div>

            <div class="flex items-center gap-3">
              <label class="w-32 shrink-0 text-xs font-medium uppercase tracking-wider text-slate-400">Bank Acct No</label>
              <input v-model="editForm.factoringBankAcctno" placeholder="Account number" class="flex-1 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm shadow-sm focus:border-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-200" />
            </div>
          </div>
        </div>

        <div class="flex justify-end gap-2 border-t border-slate-100 bg-slate-50 px-4 py-3">
          <button
            type="button"
            class="rounded-lg border border-slate-300 bg-white px-4 py-1.5 text-sm font-medium text-slate-700 shadow-sm transition-colors hover:bg-slate-50"
            @click="editModal = false"
          >Cancel</button>
          <button
            type="button"
            class="inline-flex items-center gap-1.5 rounded-lg bg-slate-900 px-4 py-1.5 text-sm font-medium text-white shadow-sm transition-colors hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-50"
            :disabled="editSaving"
            @click="saveEditModal"
          >
            <Loader2 v-if="editSaving" class="h-3.5 w-3.5 animate-spin" />
            {{ editSaving ? 'Saving…' : 'Save' }}
          </button>
        </div>
      </div>
    </div>
  </Teleport>
</template>
