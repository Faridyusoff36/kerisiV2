<script setup lang="ts">
import { ref, onMounted } from "vue";
import AdminLayout from "@/layouts/AdminLayout.vue";
import { useToast } from "@/composables/useToast";
import {
  type ApDvSuggestion,
  suggestApDvPayee,
  suggestApDvFundType,
  suggestApDvActivityCode,
  suggestApDvPtj,
  suggestApDvCostCenter,
  suggestApDvAccountCode,
} from "@/api/cms";

const toast = useToast();

// ─── Button state: initial → manual → upload ─────────────────────────────────
// "initial"  : [ + Manual ]  [ ↑ Upload File ]
// "manual"   : [ + Manual Voucher for Staff ] [ + Manual Voucher for Others ] [ ↑ Change To Upload ]
// "upload"   : [ ↑ Upload Voucher for Staff ] [ ↑ Upload Voucher for Others ] [ ↑ Change To Manual ]
type ActionMode = "initial" | "manual" | "upload";
const actionMode = ref<ActionMode>("initial");

// ─── Active modal ─────────────────────────────────────────────────────────────
type ActiveModal = "" | "manualStaff" | "manualOthers" | "uploadStaff" | "uploadOthers";
const activeModal = ref<ActiveModal>("");

// ─── Autosuggest helper types ─────────────────────────────────────────────────
type DimKey = "payee" | "fund" | "activity" | "ptj" | "costCenter" | "accountCode" | "creditAccountCode";

// ─── Main form values ─────────────────────────────────────────────────────────
const form = ref({
  payeeId: "", payeeText: "",
  description: "PEMBAYARAN KE ATAS",
  fundType: "", fundDesc: "",
  activityCode: "", activityDesc: "",
  ptjCode: "", ptjDesc: "",
  costCenter: "", costCenterDesc: "",
  accountCode: "", accountCodeDesc: "",
  codeSo: "", budgetCode: "",
  creditAccountCode: "", creditAccountCodeDesc: "",
  amount: "0.00", status: "DRAFT",
});

// ─── Line items ───────────────────────────────────────────────────────────────
interface LineItem {
  id: number; type: "staff" | "others";
  billNo: string; payeeId: string; payeeText: string;
  bankAccount: string; accountNo: string;
  fund: string; activity: string; ptj: string;
  costCenter: string; accountCode: string;
  amount: string; referenceNo: string;
}
const debitLines  = ref<LineItem[]>([]);
const creditLines = ref<LineItem[]>([]);
let nextId = 1;

function formatAmt(v: string): string {
  const n = parseFloat(v);
  return isNaN(n) ? "0.00" : n.toLocaleString("en-MY", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
function removeLine(tbl: "debit" | "credit", id: number) {
  if (tbl === "debit") debitLines.value = debitLines.value.filter((l) => l.id !== id);
  else creditLines.value = creditLines.value.filter((l) => l.id !== id);
}

// ─── Manual Voucher for Staff form ───────────────────────────────────────────
const staffForm = ref({
  lineType: "debit" as "debit" | "credit",
  staffId: "", staffName: "",
  payeeName: "", accountBank: "", accountNo: "",
  amount: "", referenceNo: "",
  factoringType: "", factoringId: "", factoringName: "", factoringAccountNo: "",
});
const staffStaffQuery   = ref("");
const staffStaffOpen    = ref(false);
const staffStaffLoading = ref(false);
const staffStaffResults = ref<ApDvSuggestion[]>([]);
let staffStaffTimer: ReturnType<typeof setTimeout> | null = null;

async function searchStaffStaff(q: string) {
  staffStaffLoading.value = true;
  try {
    const res = await suggestApDvPayee(q);
    staffStaffResults.value = res.data;
    staffStaffOpen.value = true;
  } catch { staffStaffResults.value = []; }
  finally { staffStaffLoading.value = false; }
}
function pickStaffStaff(opt: ApDvSuggestion) {
  staffForm.value.staffId = opt.id;
  staffForm.value.staffName = opt.desc;
  staffForm.value.payeeName = opt.desc;
  staffStaffQuery.value = opt.text;
  staffStaffOpen.value = false;
}
function openManualStaff() {
  staffForm.value = { lineType: "debit", staffId: "", staffName: "", payeeName: "", accountBank: "", accountNo: "", amount: "", referenceNo: "", factoringType: "", factoringId: "", factoringName: "", factoringAccountNo: "" };
  staffStaffQuery.value = "";
  staffStaffResults.value = [];
  staffStaffOpen.value = false;
  activeModal.value = "manualStaff";
}
function saveStaffLine() {
  if (!staffForm.value.amount || isNaN(Number(staffForm.value.amount))) { toast.error("Validation", "Amount is required."); return; }
  const line: LineItem = {
    id: nextId++, type: "staff",
    billNo: staffForm.value.referenceNo,
    payeeId: staffForm.value.staffId, payeeText: staffForm.value.payeeName,
    bankAccount: staffForm.value.accountBank, accountNo: staffForm.value.accountNo,
    fund: form.value.fundType, activity: form.value.activityCode,
    ptj: form.value.ptjCode, costCenter: form.value.costCenter,
    accountCode: form.value.accountCode,
    amount: Number(staffForm.value.amount).toFixed(2),
    referenceNo: staffForm.value.referenceNo,
  };
  if (staffForm.value.lineType === "debit") debitLines.value.push(line);
  else creditLines.value.push(line);
  activeModal.value = "";
  toast.success("Added", `Staff voucher line added to ${staffForm.value.lineType}.`);
}

// ─── Manual Voucher for Others form ──────────────────────────────────────────
const othersForm = ref({
  lineType: "debit" as "debit" | "credit",
  payeeCode: "", payeeName: "", accountBank: "", accountNo: "",
  amount: "", factoringType: "", factoringId: "", factoringName: "", factoringAccountNo: "",
});
function openManualOthers() {
  othersForm.value = { lineType: "debit", payeeCode: "", payeeName: "", accountBank: "", accountNo: "", amount: "", factoringType: "", factoringId: "", factoringName: "", factoringAccountNo: "" };
  activeModal.value = "manualOthers";
}
function saveOthersLine() {
  if (!othersForm.value.amount || isNaN(Number(othersForm.value.amount))) { toast.error("Validation", "Amount is required."); return; }
  const line: LineItem = {
    id: nextId++, type: "others",
    billNo: "",
    payeeId: othersForm.value.payeeCode, payeeText: othersForm.value.payeeName,
    bankAccount: othersForm.value.accountBank, accountNo: othersForm.value.accountNo,
    fund: form.value.fundType, activity: form.value.activityCode,
    ptj: form.value.ptjCode, costCenter: form.value.costCenter,
    accountCode: form.value.accountCode,
    amount: Number(othersForm.value.amount).toFixed(2),
    referenceNo: "",
  };
  if (othersForm.value.lineType === "debit") debitLines.value.push(line);
  else creditLines.value.push(line);
  activeModal.value = "";
  toast.success("Added", `Others voucher line added to ${othersForm.value.lineType}.`);
}

// ─── Upload forms ─────────────────────────────────────────────────────────────
const uploadStaffFile  = ref<File | null>(null);
const uploadOthersFile = ref<File | null>(null);

function handleUploadFile(target: "staff" | "others", evt: Event) {
  const file = (evt.target as HTMLInputElement).files?.[0] ?? null;
  if (target === "staff") uploadStaffFile.value = file;
  else uploadOthersFile.value = file;
}
function submitUpload(target: "staff" | "others") {
  const file = target === "staff" ? uploadStaffFile.value : uploadOthersFile.value;
  if (!file) { toast.error("Validation", "Please select a CSV file."); return; }
  toast.info("Upload", `${file.name} — upload functionality coming soon.`);
  activeModal.value = "";
}

// ─── Header form autosuggest ──────────────────────────────────────────────────
const dimQuery   = ref<Record<DimKey, string>>({ payee: "", fund: "", activity: "", ptj: "", costCenter: "", accountCode: "", creditAccountCode: "" });
const dimOpen    = ref<Record<DimKey, boolean>>({ payee: false, fund: false, activity: false, ptj: false, costCenter: false, accountCode: false, creditAccountCode: false });
const dimLoading = ref<Record<DimKey, boolean>>({ payee: false, fund: false, activity: false, ptj: false, costCenter: false, accountCode: false, creditAccountCode: false });
const dimResults = ref<Record<DimKey, ApDvSuggestion[]>>({ payee: [], fund: [], activity: [], ptj: [], costCenter: [], accountCode: [], creditAccountCode: [] });
const dimTimers: Record<DimKey, ReturnType<typeof setTimeout> | null> = { payee: null, fund: null, activity: null, ptj: null, costCenter: null, accountCode: null, creditAccountCode: null };

async function runDimSearch(key: DimKey, term: string, openDropdown = true) {
  dimLoading.value[key] = true;
  try {
    let res: { data: ApDvSuggestion[] };
    if (key === "payee")             res = await suggestApDvPayee(term);
    else if (key === "fund")         res = await suggestApDvFundType(term);
    else if (key === "activity")     res = await suggestApDvActivityCode(term);
    else if (key === "ptj")          res = await suggestApDvPtj(term);
    else if (key === "costCenter")   res = await suggestApDvCostCenter(term);
    else                             res = await suggestApDvAccountCode(term, form.value.fundType);
    dimResults.value[key] = res.data;
    if (openDropdown) dimOpen.value[key] = true;
  } catch { dimResults.value[key] = []; }
  finally { dimLoading.value[key] = false; }
}

function onDimInput(key: DimKey, v: string) {
  dimQuery.value[key] = v;
  const timer = dimTimers[key];
  if (timer) clearTimeout(timer);
  dimTimers[key] = setTimeout(() => void runDimSearch(key, v.trim()), 300);
}
function onDimFocus(key: DimKey, evt?: FocusEvent) {
  void runDimSearch(key, "");
  (evt?.target as HTMLInputElement | undefined)?.select?.();
}
function closeDimSoon(key: DimKey) { window.setTimeout(() => (dimOpen.value[key] = false), 150); }
function pickDim(key: DimKey, opt: ApDvSuggestion) {
  dimQuery.value[key] = opt.text;
  dimOpen.value[key] = false;
  if (key === "payee") { form.value.payeeId = opt.id; form.value.payeeText = opt.text; }
  else if (key === "fund") {
    form.value.fundType = opt.id; form.value.fundDesc = opt.desc;
    form.value.activityCode = ""; form.value.activityDesc = "";
    dimQuery.value.activity = "";
  } else if (key === "activity") { form.value.activityCode = opt.id; form.value.activityDesc = opt.desc; }
  else if (key === "ptj") { form.value.ptjCode = opt.id; form.value.ptjDesc = opt.desc; }
  else if (key === "costCenter") { form.value.costCenter = opt.id; form.value.costCenterDesc = opt.desc; }
  else if (key === "accountCode") { form.value.accountCode = opt.id; form.value.accountCodeDesc = opt.desc; }
  else if (key === "creditAccountCode") { form.value.creditAccountCode = opt.id; form.value.creditAccountCodeDesc = opt.desc; }
}
function clearDim(key: DimKey) {
  dimQuery.value[key] = "";
  dimOpen.value[key] = false;
  if (key === "payee") { form.value.payeeId = ""; form.value.payeeText = ""; }
  else if (key === "fund") { form.value.fundType = ""; form.value.fundDesc = ""; form.value.activityCode = ""; form.value.activityDesc = ""; dimQuery.value.activity = ""; }
  else if (key === "activity") { form.value.activityCode = ""; form.value.activityDesc = ""; }
  else if (key === "ptj") { form.value.ptjCode = ""; form.value.ptjDesc = ""; }
  else if (key === "costCenter") { form.value.costCenter = ""; form.value.costCenterDesc = ""; }
  else if (key === "accountCode") { form.value.accountCode = ""; form.value.accountCodeDesc = ""; }
  else if (key === "creditAccountCode") { form.value.creditAccountCode = ""; form.value.creditAccountCodeDesc = ""; }
}

onMounted(() => {
  void runDimSearch("payee",      "", false);
  void runDimSearch("fund",       "", false);
  void runDimSearch("activity",   "", false);
  void runDimSearch("ptj",        "", false);
  void runDimSearch("costCenter", "", false);
});
</script>

<template>
  <AdminLayout>
    <div class="space-y-4">
      <h1 class="page-title">Account Payable / Voucher / Direct Voucher</h1>

      <!-- ── Voucher Details ────────────────────────────────────────────── -->
      <article class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-4 py-3">
          <h2 class="text-base font-semibold text-slate-900">Voucher Details</h2>
        </div>

        <div class="grid gap-y-3 p-4">

          <!-- Payee -->
          <div class="grid items-center gap-2 md:grid-cols-[13rem_0.5rem_1fr]">
            <label class="text-xs font-semibold text-slate-700">Payee</label>
            <span class="text-slate-400">:</span>
            <div class="relative">
              <div class="flex items-center gap-1">
                <input type="search" class="h-8 flex-1 rounded border border-slate-300 px-2 text-xs focus:outline-none focus:ring-2 focus:ring-slate-400" placeholder="Search payee…" :value="dimQuery.payee" autocomplete="off" @input="onDimInput('payee', ($event.target as HTMLInputElement).value)" @focus="onDimFocus('payee', $event)" @blur="closeDimSoon('payee')" />
                <button v-if="dimQuery.payee" type="button" class="text-slate-400 hover:text-slate-600" @mousedown.prevent="clearDim('payee')">✕</button>
              </div>
              <ul v-if="dimOpen.payee && dimResults.payee.length" class="absolute z-50 mt-0.5 max-h-48 w-full overflow-auto rounded border border-slate-200 bg-white shadow-lg">
                <li v-for="opt in dimResults.payee" :key="opt.id" class="cursor-pointer px-3 py-1.5 text-xs hover:bg-slate-100" @mousedown.prevent="pickDim('payee', opt)">{{ opt.text }}</li>
              </ul>
            </div>
          </div>

          <!-- Description -->
          <div class="grid items-start gap-2 md:grid-cols-[13rem_0.5rem_1fr]">
            <label class="pt-1.5 text-xs font-semibold text-slate-700">Description <span class="text-red-600">*</span></label>
            <span class="pt-1.5 text-slate-400">:</span>
            <textarea v-model="form.description" rows="3" class="rounded border border-slate-300 px-2 py-1.5 text-xs uppercase focus:outline-none focus:ring-2 focus:ring-slate-400" />
          </div>

          <!-- Fund -->
          <div class="grid items-center gap-2 md:grid-cols-[13rem_0.5rem_1fr]">
            <label class="text-xs font-semibold text-slate-700">Fund <span class="text-red-600">*</span></label>
            <span class="text-slate-400">:</span>
            <div class="relative">
              <div class="flex items-center gap-1">
                <input type="search" class="h-8 flex-1 rounded border border-slate-300 px-2 text-xs focus:outline-none focus:ring-2 focus:ring-slate-400" placeholder="Search fund type…" :value="dimQuery.fund" autocomplete="off" @input="onDimInput('fund', ($event.target as HTMLInputElement).value)" @focus="onDimFocus('fund', $event)" @blur="closeDimSoon('fund')" />
                <button v-if="dimQuery.fund" type="button" class="text-slate-400 hover:text-slate-600" @mousedown.prevent="clearDim('fund')">✕</button>
              </div>
              <ul v-if="dimOpen.fund && dimResults.fund.length" class="absolute z-50 mt-0.5 max-h-48 w-full overflow-auto rounded border border-slate-200 bg-white shadow-lg">
                <li v-for="opt in dimResults.fund" :key="opt.id" class="cursor-pointer px-3 py-1.5 text-xs hover:bg-slate-100" @mousedown.prevent="pickDim('fund', opt)">{{ opt.text }}</li>
              </ul>
            </div>
          </div>

          <!-- Activity -->
          <div class="grid items-center gap-2 md:grid-cols-[13rem_0.5rem_1fr]">
            <label class="text-xs font-semibold text-slate-700">Activity <span class="text-red-600">*</span></label>
            <span class="text-slate-400">:</span>
            <div class="relative">
              <div class="flex items-center gap-1">
                <input type="search" class="h-8 flex-1 rounded border border-slate-300 px-2 text-xs focus:outline-none focus:ring-2 focus:ring-slate-400" placeholder="Search activity…" :value="dimQuery.activity" autocomplete="off" @input="onDimInput('activity', ($event.target as HTMLInputElement).value)" @focus="onDimFocus('activity', $event)" @blur="closeDimSoon('activity')" />
                <button v-if="dimQuery.activity" type="button" class="text-slate-400 hover:text-slate-600" @mousedown.prevent="clearDim('activity')">✕</button>
              </div>
              <ul v-if="dimOpen.activity && dimResults.activity.length" class="absolute z-50 mt-0.5 max-h-48 w-full overflow-auto rounded border border-slate-200 bg-white shadow-lg">
                <li v-for="opt in dimResults.activity" :key="opt.id" class="cursor-pointer px-3 py-1.5 text-xs hover:bg-slate-100" @mousedown.prevent="pickDim('activity', opt)">{{ opt.text }}</li>
              </ul>
            </div>
          </div>

          <!-- PTJ -->
          <div class="grid items-center gap-2 md:grid-cols-[13rem_0.5rem_1fr]">
            <label class="text-xs font-semibold text-slate-700">PTJ <span class="text-red-600">*</span></label>
            <span class="text-slate-400">:</span>
            <div class="relative">
              <div class="flex items-center gap-1">
                <input type="search" class="h-8 flex-1 rounded border border-slate-300 px-2 text-xs focus:outline-none focus:ring-2 focus:ring-slate-400" placeholder="Search PTJ…" :value="dimQuery.ptj" autocomplete="off" @input="onDimInput('ptj', ($event.target as HTMLInputElement).value)" @focus="onDimFocus('ptj', $event)" @blur="closeDimSoon('ptj')" />
                <button v-if="dimQuery.ptj" type="button" class="text-slate-400 hover:text-slate-600" @mousedown.prevent="clearDim('ptj')">✕</button>
              </div>
              <ul v-if="dimOpen.ptj && dimResults.ptj.length" class="absolute z-50 mt-0.5 max-h-48 w-full overflow-auto rounded border border-slate-200 bg-white shadow-lg">
                <li v-for="opt in dimResults.ptj" :key="opt.id" class="cursor-pointer px-3 py-1.5 text-xs hover:bg-slate-100" @mousedown.prevent="pickDim('ptj', opt)">{{ opt.text }}</li>
              </ul>
            </div>
          </div>

          <!-- Cost Center -->
          <div class="grid items-center gap-2 md:grid-cols-[13rem_0.5rem_1fr]">
            <label class="text-xs font-semibold text-slate-700">Cost Center <span class="text-red-600">*</span></label>
            <span class="text-slate-400">:</span>
            <div class="relative">
              <div class="flex items-center gap-1">
                <input type="search" class="h-8 flex-1 rounded border border-slate-300 px-2 text-xs focus:outline-none focus:ring-2 focus:ring-slate-400" placeholder="Search cost center…" :value="dimQuery.costCenter" autocomplete="off" @input="onDimInput('costCenter', ($event.target as HTMLInputElement).value)" @focus="onDimFocus('costCenter', $event)" @blur="closeDimSoon('costCenter')" />
                <button v-if="dimQuery.costCenter" type="button" class="text-slate-400 hover:text-slate-600" @mousedown.prevent="clearDim('costCenter')">✕</button>
              </div>
              <ul v-if="dimOpen.costCenter && dimResults.costCenter.length" class="absolute z-50 mt-0.5 max-h-48 w-full overflow-auto rounded border border-slate-200 bg-white shadow-lg">
                <li v-for="opt in dimResults.costCenter" :key="opt.id" class="cursor-pointer px-3 py-1.5 text-xs hover:bg-slate-100" @mousedown.prevent="pickDim('costCenter', opt)">{{ opt.text }}</li>
              </ul>
            </div>
          </div>

          <!-- Account Code -->
          <div class="grid items-center gap-2 md:grid-cols-[13rem_0.5rem_1fr]">
            <label class="text-xs font-semibold text-slate-700">Account Code <span class="text-red-600">*</span></label>
            <span class="text-slate-400">:</span>
            <div class="relative">
              <div class="flex items-center gap-1">
                <input type="search" class="h-8 flex-1 rounded border border-slate-300 px-2 text-xs focus:outline-none focus:ring-2 focus:ring-slate-400" placeholder="Search account code…" :value="dimQuery.accountCode" autocomplete="off" @input="onDimInput('accountCode', ($event.target as HTMLInputElement).value)" @focus="onDimFocus('accountCode', $event)" @blur="closeDimSoon('accountCode')" />
                <button v-if="dimQuery.accountCode" type="button" class="text-slate-400 hover:text-slate-600" @mousedown.prevent="clearDim('accountCode')">✕</button>
              </div>
              <ul v-if="dimOpen.accountCode && dimResults.accountCode.length" class="absolute z-50 mt-0.5 max-h-48 w-full overflow-auto rounded border border-slate-200 bg-white shadow-lg">
                <li v-for="opt in dimResults.accountCode" :key="opt.id" class="cursor-pointer px-3 py-1.5 text-xs hover:bg-slate-100" @mousedown.prevent="pickDim('accountCode', opt)">{{ opt.text }}</li>
              </ul>
            </div>
          </div>

          <!-- Code SO -->
          <div class="grid items-center gap-2 md:grid-cols-[13rem_0.5rem_1fr]">
            <label class="text-xs font-semibold text-slate-700">Code SO</label>
            <span class="text-slate-400">:</span>
            <input v-model="form.codeSo" type="text" autocomplete="off" class="h-8 rounded border border-slate-300 px-2 text-xs focus:outline-none focus:ring-2 focus:ring-slate-400" />
          </div>

          <!-- Budget Code (disabled) -->
          <div class="grid items-center gap-2 md:grid-cols-[13rem_0.5rem_1fr]">
            <label class="text-xs font-semibold text-slate-700">Budget Code</label>
            <span class="text-slate-400">:</span>
            <input :value="form.budgetCode" disabled class="h-8 rounded border border-slate-200 bg-slate-100 px-2 text-xs text-slate-500" />
          </div>

          <!-- Credit Account Code -->
          <div class="grid items-center gap-2 md:grid-cols-[13rem_0.5rem_1fr]">
            <label class="text-xs font-semibold text-slate-700">Credit Account Code <span class="text-red-600">*</span></label>
            <span class="text-slate-400">:</span>
            <div class="relative">
              <div class="flex items-center gap-1">
                <input type="search" class="h-8 flex-1 rounded border border-slate-300 px-2 text-xs focus:outline-none focus:ring-2 focus:ring-slate-400" placeholder="Search credit account code…" :value="dimQuery.creditAccountCode" autocomplete="off" @input="onDimInput('creditAccountCode', ($event.target as HTMLInputElement).value)" @focus="onDimFocus('creditAccountCode', $event)" @blur="closeDimSoon('creditAccountCode')" />
                <button v-if="dimQuery.creditAccountCode" type="button" class="text-slate-400 hover:text-slate-600" @mousedown.prevent="clearDim('creditAccountCode')">✕</button>
              </div>
              <ul v-if="dimOpen.creditAccountCode && dimResults.creditAccountCode.length" class="absolute z-50 mt-0.5 max-h-48 w-full overflow-auto rounded border border-slate-200 bg-white shadow-lg">
                <li v-for="opt in dimResults.creditAccountCode" :key="opt.id" class="cursor-pointer px-3 py-1.5 text-xs hover:bg-slate-100" @mousedown.prevent="pickDim('creditAccountCode', opt)">{{ opt.text }}</li>
              </ul>
            </div>
          </div>

          <!-- Amount + Status -->
          <div class="grid grid-cols-2 gap-6">
            <div class="grid items-center gap-2 md:grid-cols-[9rem_0.5rem_1fr]">
              <label class="text-xs font-semibold text-slate-700">Amount</label>
              <span class="text-slate-400">:</span>
              <div class="flex">
                <span class="inline-flex h-8 items-center rounded-l border border-r-0 border-slate-300 bg-slate-100 px-2 text-xs text-slate-600">MYR</span>
                <input :value="form.amount" disabled class="h-8 flex-1 rounded-r border border-slate-200 bg-slate-50 px-2 text-right text-xs text-slate-500" />
              </div>
            </div>
            <div class="grid items-center gap-2 md:grid-cols-[6rem_0.5rem_1fr]">
              <label class="text-xs font-semibold text-slate-700">Status</label>
              <span class="text-slate-400">:</span>
              <input :value="form.status" disabled class="h-8 rounded border border-slate-200 bg-slate-100 px-2 text-xs text-slate-500" />
            </div>
          </div>
        </div><!-- /form grid -->

        <!-- ── Action buttons (3 states) ──────────────────────────────── -->
        <div class="flex flex-wrap items-center justify-center gap-3 border-t border-slate-100 bg-slate-50 px-4 py-3">

          <!-- Initial state -->
          <template v-if="actionMode === 'initial'">
            <button type="button"
              class="inline-flex items-center gap-1.5 rounded-lg bg-sky-600 px-4 py-2 text-xs font-medium text-white hover:bg-sky-700"
              @click="actionMode = 'manual'">
              <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
              Manual
            </button>
            <button type="button"
              class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-4 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50"
              @click="actionMode = 'upload'">
              <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
              Upload File
            </button>
          </template>

          <!-- Manual mode -->
          <template v-else-if="actionMode === 'manual'">
            <button type="button"
              class="inline-flex items-center gap-1.5 rounded-lg bg-sky-600 px-4 py-2 text-xs font-medium text-white hover:bg-sky-700"
              @click="openManualStaff">
              <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
              Manual Voucher for Staff
            </button>
            <button type="button"
              class="inline-flex items-center gap-1.5 rounded-lg bg-sky-600 px-4 py-2 text-xs font-medium text-white hover:bg-sky-700"
              @click="openManualOthers">
              <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
              Manual Voucher for Others
            </button>
            <button type="button"
              class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-4 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50"
              @click="actionMode = 'upload'">
              <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
              Change To Upload
            </button>
          </template>

          <!-- Upload mode -->
          <template v-else>
            <button type="button"
              class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-4 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50"
              @click="uploadStaffFile = null; activeModal = 'uploadStaff'">
              <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
              Upload Voucher for Staff
            </button>
            <button type="button"
              class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-4 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50"
              @click="uploadOthersFile = null; activeModal = 'uploadOthers'">
              <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
              Upload Voucher for Others
            </button>
            <button type="button"
              class="inline-flex items-center gap-1.5 rounded-lg bg-sky-600 px-4 py-2 text-xs font-medium text-white hover:bg-sky-700"
              @click="actionMode = 'manual'">
              <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
              Change To Manual
            </button>
          </template>

        </div>
      </article>

      <!-- ── Debit table ────────────────────────────────────────────────── -->
      <article class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 bg-slate-50 px-4 py-2.5">
          <h2 class="text-sm font-semibold text-slate-800">Debit</h2>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-xs">
            <thead>
              <tr class="border-b border-slate-200 bg-slate-50 text-left">
                <th class="px-3 py-2 font-medium text-slate-600">No</th>
                <th class="px-3 py-2 font-medium text-slate-600">Bill No.</th>
                <th class="px-3 py-2 font-medium text-slate-600">Payee</th>
                <th class="px-3 py-2 font-medium text-slate-600">Payee Bank Account</th>
                <th class="px-3 py-2 font-medium text-slate-600">Fund</th>
                <th class="px-3 py-2 font-medium text-slate-600">Activity</th>
                <th class="px-3 py-2 font-medium text-slate-600">PTJ</th>
                <th class="px-3 py-2 font-medium text-slate-600">Cost Centre</th>
                <th class="px-3 py-2 font-medium text-slate-600">Account Code</th>
                <th class="px-3 py-2 text-right font-medium text-slate-600">Amount</th>
                <th class="px-3 py-2 font-medium text-slate-600">Action</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="!debitLines.length"><td colspan="11" class="px-3 py-6 text-center text-slate-400">No records</td></tr>
              <tr v-for="(l, i) in debitLines" :key="l.id" class="border-b border-slate-100 hover:bg-slate-50">
                <td class="px-3 py-2">{{ i + 1 }}</td>
                <td class="px-3 py-2">{{ l.billNo || '—' }}</td>
                <td class="px-3 py-2">{{ l.payeeText || '—' }}</td>
                <td class="px-3 py-2">{{ l.bankAccount || '—' }}</td>
                <td class="px-3 py-2">{{ l.fund || '—' }}</td>
                <td class="px-3 py-2">{{ l.activity || '—' }}</td>
                <td class="px-3 py-2">{{ l.ptj || '—' }}</td>
                <td class="px-3 py-2">{{ l.costCenter || '—' }}</td>
                <td class="px-3 py-2">{{ l.accountCode || '—' }}</td>
                <td class="px-3 py-2 text-right">{{ formatAmt(l.amount) }}</td>
                <td class="px-3 py-2"><button type="button" class="rounded bg-red-100 px-2 py-0.5 text-xs text-red-600 hover:bg-red-200" @click="removeLine('debit', l.id)">Delete</button></td>
              </tr>
            </tbody>
          </table>
        </div>
      </article>

      <!-- ── Credit table ───────────────────────────────────────────────── -->
      <article class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 bg-slate-50 px-4 py-2.5">
          <h2 class="text-sm font-semibold text-slate-800">Credit</h2>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-xs">
            <thead>
              <tr class="border-b border-slate-200 bg-slate-50 text-left">
                <th class="px-3 py-2 font-medium text-slate-600">No</th>
                <th class="px-3 py-2 font-medium text-slate-600">Bill No.</th>
                <th class="px-3 py-2 font-medium text-slate-600">Payee</th>
                <th class="px-3 py-2 font-medium text-slate-600">Payee Bank Account</th>
                <th class="px-3 py-2 font-medium text-slate-600">Fund</th>
                <th class="px-3 py-2 font-medium text-slate-600">Activity</th>
                <th class="px-3 py-2 font-medium text-slate-600">PTJ</th>
                <th class="px-3 py-2 font-medium text-slate-600">Cost Centre</th>
                <th class="px-3 py-2 font-medium text-slate-600">Account Code</th>
                <th class="px-3 py-2 text-right font-medium text-slate-600">Amount</th>
                <th class="px-3 py-2 font-medium text-slate-600">Action</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="!creditLines.length"><td colspan="11" class="px-3 py-6 text-center text-slate-400">No records</td></tr>
              <tr v-for="(l, i) in creditLines" :key="l.id" class="border-b border-slate-100 hover:bg-slate-50">
                <td class="px-3 py-2">{{ i + 1 }}</td>
                <td class="px-3 py-2">{{ l.billNo || '—' }}</td>
                <td class="px-3 py-2">{{ l.payeeText || '—' }}</td>
                <td class="px-3 py-2">{{ l.bankAccount || '—' }}</td>
                <td class="px-3 py-2">{{ l.fund || '—' }}</td>
                <td class="px-3 py-2">{{ l.activity || '—' }}</td>
                <td class="px-3 py-2">{{ l.ptj || '—' }}</td>
                <td class="px-3 py-2">{{ l.costCenter || '—' }}</td>
                <td class="px-3 py-2">{{ l.accountCode || '—' }}</td>
                <td class="px-3 py-2 text-right">{{ formatAmt(l.amount) }}</td>
                <td class="px-3 py-2"><button type="button" class="rounded bg-red-100 px-2 py-0.5 text-xs text-red-600 hover:bg-red-200" @click="removeLine('credit', l.id)">Delete</button></td>
              </tr>
            </tbody>
          </table>
        </div>
      </article>

    </div>
  </AdminLayout>

  <!-- ════════════════════════════════════════════════════════════════════
       MODAL: Manual Voucher for Staff
       ════════════════════════════════════════════════════════════════════ -->
  <Teleport to="body">
    <div v-if="activeModal === 'manualStaff'" class="fixed inset-0 z-[200] flex items-center justify-center bg-black/40 p-4">
      <div class="w-full max-w-xl rounded-lg bg-white shadow-xl">
        <div class="flex items-center justify-between rounded-t-lg bg-indigo-400 px-4 py-3">
          <h3 class="text-sm font-semibold text-white">Manual Voucher for Staff</h3>
          <button type="button" class="text-white/80 hover:text-white" @click="activeModal = ''">✕</button>
        </div>
        <div class="grid gap-y-2.5 p-4">

          <!-- Line Type -->
          <div class="grid items-center gap-2 md:grid-cols-[10rem_0.5rem_1fr]">
            <label class="text-xs font-semibold text-slate-700">Type <span class="text-red-600">*</span></label>
            <span class="text-slate-400">:</span>
            <div class="flex gap-4 text-xs">
              <label class="flex items-center gap-1.5 cursor-pointer"><input v-model="staffForm.lineType" type="radio" value="debit" class="accent-indigo-500" /> Debit</label>
              <label class="flex items-center gap-1.5 cursor-pointer"><input v-model="staffForm.lineType" type="radio" value="credit" class="accent-indigo-500" /> Credit</label>
            </div>
          </div>

          <!-- Staff ID -->
          <div class="grid items-center gap-2 md:grid-cols-[10rem_0.5rem_1fr]">
            <label class="text-xs font-semibold text-slate-700">Staff ID <span class="text-red-600">*</span></label>
            <span class="text-slate-400">:</span>
            <div class="relative">
              <div class="flex items-center gap-1">
                <input type="search" class="h-8 flex-1 rounded border border-slate-300 px-2 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-400" placeholder="Search staff…" :value="staffStaffQuery" autocomplete="off"
                  @input="() => { staffStaffQuery = ($event.target as HTMLInputElement).value; if (staffStaffTimer) clearTimeout(staffStaffTimer); staffStaffTimer = setTimeout(() => searchStaffStaff(staffStaffQuery.trim()), 300); }"
                  @focus="searchStaffStaff('')"
                  @blur="() => window.setTimeout(() => (staffStaffOpen = false), 150)" />
                <button v-if="staffStaffQuery" type="button" class="text-slate-400 hover:text-slate-600" @mousedown.prevent="() => { staffStaffQuery = ''; staffForm.staffId = ''; staffForm.staffName = ''; staffForm.payeeName = ''; }">✕</button>
              </div>
              <ul v-if="staffStaffOpen && staffStaffResults.length" class="absolute z-[210] mt-0.5 max-h-40 w-full overflow-auto rounded border border-slate-200 bg-white shadow-lg">
                <li v-for="opt in staffStaffResults" :key="opt.id" class="cursor-pointer px-3 py-1.5 text-xs hover:bg-slate-100" @mousedown.prevent="pickStaffStaff(opt)">{{ opt.text }}</li>
              </ul>
            </div>
          </div>

          <!-- Payee Name -->
          <div class="grid items-center gap-2 md:grid-cols-[10rem_0.5rem_1fr]">
            <label class="text-xs font-semibold text-slate-700">Payee Name <span class="text-red-600">*</span></label>
            <span class="text-slate-400">:</span>
            <input v-model="staffForm.payeeName" type="text" class="h-8 rounded border border-slate-300 px-2 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-400" autocomplete="off" />
          </div>

          <!-- Account Bank -->
          <div class="grid items-center gap-2 md:grid-cols-[10rem_0.5rem_1fr]">
            <label class="text-xs font-semibold text-slate-700">Account Bank</label>
            <span class="text-slate-400">:</span>
            <input v-model="staffForm.accountBank" type="text" class="h-8 rounded border border-slate-300 px-2 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-400" placeholder="Bank name" autocomplete="off" />
          </div>

          <!-- Account No -->
          <div class="grid items-center gap-2 md:grid-cols-[10rem_0.5rem_1fr]">
            <label class="text-xs font-semibold text-slate-700">Account No <span class="text-red-600">*</span></label>
            <span class="text-slate-400">:</span>
            <input v-model="staffForm.accountNo" type="text" class="h-8 rounded border border-slate-300 px-2 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-400" autocomplete="off" />
          </div>

          <!-- Amount -->
          <div class="grid items-center gap-2 md:grid-cols-[10rem_0.5rem_1fr]">
            <label class="text-xs font-semibold text-slate-700">Amount <span class="text-red-600">*</span></label>
            <span class="text-slate-400">:</span>
            <div class="flex">
              <span class="inline-flex h-8 items-center rounded-l border border-r-0 border-slate-300 bg-slate-100 px-2 text-xs text-slate-600">MYR</span>
              <input v-model="staffForm.amount" type="number" min="0" step="0.01" class="h-8 flex-1 rounded-r border border-slate-300 px-2 text-right text-xs focus:outline-none focus:ring-2 focus:ring-indigo-400" placeholder="0.00" />
            </div>
          </div>

          <!-- Reference No -->
          <div class="grid items-center gap-2 md:grid-cols-[10rem_0.5rem_1fr]">
            <label class="text-xs font-semibold text-slate-700">Reference No <span class="text-red-600">*</span></label>
            <span class="text-slate-400">:</span>
            <input v-model="staffForm.referenceNo" type="text" class="h-8 rounded border border-slate-300 px-2 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-400" autocomplete="off" />
          </div>

          <!-- Factoring Type -->
          <div class="grid items-center gap-2 md:grid-cols-[10rem_0.5rem_1fr]">
            <label class="text-xs font-semibold text-slate-700">Factoring Type</label>
            <span class="text-slate-400">:</span>
            <input v-model="staffForm.factoringType" type="text" class="h-8 rounded border border-slate-300 px-2 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-400" placeholder="Factoring type" autocomplete="off" />
          </div>

          <!-- Factoring ID -->
          <div class="grid items-center gap-2 md:grid-cols-[10rem_0.5rem_1fr]">
            <label class="text-xs font-semibold text-slate-700">Factoring ID</label>
            <span class="text-slate-400">:</span>
            <input v-model="staffForm.factoringId" type="text" class="h-8 rounded border border-slate-300 px-2 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-400" placeholder="Factoring ID" autocomplete="off" />
          </div>

          <!-- Factoring Name -->
          <div class="grid items-center gap-2 md:grid-cols-[10rem_0.5rem_1fr]">
            <label class="text-xs font-semibold text-slate-700">Factoring Name</label>
            <span class="text-slate-400">:</span>
            <input v-model="staffForm.factoringName" type="text" class="h-8 rounded border border-slate-300 px-2 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-400" autocomplete="off" />
          </div>

          <!-- Factoring Account No -->
          <div class="grid items-center gap-2 md:grid-cols-[10rem_0.5rem_1fr]">
            <label class="text-xs font-semibold text-slate-700">Factoring Account No</label>
            <span class="text-slate-400">:</span>
            <input v-model="staffForm.factoringAccountNo" type="text" class="h-8 rounded border border-slate-300 px-2 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-400" autocomplete="off" />
          </div>

        </div>
        <div class="flex items-center justify-end gap-2 border-t border-slate-200 px-4 py-3">
          <button type="button" class="rounded-lg bg-red-500 px-4 py-1.5 text-xs font-medium text-white hover:bg-red-600" @click="activeModal = ''">Cancel</button>
          <button type="button" class="rounded-lg bg-sky-600 px-4 py-1.5 text-xs font-medium text-white hover:bg-sky-700" @click="saveStaffLine">Save</button>
        </div>
      </div>
    </div>
  </Teleport>

  <!-- ════════════════════════════════════════════════════════════════════
       MODAL: Manual Voucher for Others
       ════════════════════════════════════════════════════════════════════ -->
  <Teleport to="body">
    <div v-if="activeModal === 'manualOthers'" class="fixed inset-0 z-[200] flex items-center justify-center bg-black/40 p-4">
      <div class="w-full max-w-xl rounded-lg bg-white shadow-xl">
        <div class="flex items-center justify-between rounded-t-lg bg-indigo-400 px-4 py-3">
          <h3 class="text-sm font-semibold text-white">Manual Voucher for Others</h3>
          <button type="button" class="text-white/80 hover:text-white" @click="activeModal = ''">✕</button>
        </div>
        <div class="grid gap-y-2.5 p-4">

          <!-- Line Type -->
          <div class="grid items-center gap-2 md:grid-cols-[10rem_0.5rem_1fr]">
            <label class="text-xs font-semibold text-slate-700">Type <span class="text-red-600">*</span></label>
            <span class="text-slate-400">:</span>
            <div class="flex gap-4 text-xs">
              <label class="flex items-center gap-1.5 cursor-pointer"><input v-model="othersForm.lineType" type="radio" value="debit" class="accent-indigo-500" /> Debit</label>
              <label class="flex items-center gap-1.5 cursor-pointer"><input v-model="othersForm.lineType" type="radio" value="credit" class="accent-indigo-500" /> Credit</label>
            </div>
          </div>

          <!-- Payee Code -->
          <div class="grid items-center gap-2 md:grid-cols-[10rem_0.5rem_1fr]">
            <label class="text-xs font-semibold text-slate-700">Payee Code <span class="text-red-600">*</span></label>
            <span class="text-slate-400">:</span>
            <input v-model="othersForm.payeeCode" type="text" class="h-8 rounded border border-slate-300 px-2 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-400" autocomplete="off" />
          </div>

          <!-- Payee Name -->
          <div class="grid items-center gap-2 md:grid-cols-[10rem_0.5rem_1fr]">
            <label class="text-xs font-semibold text-slate-700">Payee Name <span class="text-red-600">*</span></label>
            <span class="text-slate-400">:</span>
            <input v-model="othersForm.payeeName" type="text" class="h-8 rounded border border-slate-300 px-2 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-400" autocomplete="off" />
          </div>

          <!-- Account Bank -->
          <div class="grid items-center gap-2 md:grid-cols-[10rem_0.5rem_1fr]">
            <label class="text-xs font-semibold text-slate-700">Account Bank</label>
            <span class="text-slate-400">:</span>
            <input v-model="othersForm.accountBank" type="text" class="h-8 rounded border border-slate-300 px-2 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-400" placeholder="Bank name" autocomplete="off" />
          </div>

          <!-- Account No -->
          <div class="grid items-center gap-2 md:grid-cols-[10rem_0.5rem_1fr]">
            <label class="text-xs font-semibold text-slate-700">Account No <span class="text-red-600">*</span></label>
            <span class="text-slate-400">:</span>
            <input v-model="othersForm.accountNo" type="text" class="h-8 rounded border border-slate-300 px-2 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-400" autocomplete="off" />
          </div>

          <!-- Amount -->
          <div class="grid items-center gap-2 md:grid-cols-[10rem_0.5rem_1fr]">
            <label class="text-xs font-semibold text-slate-700">Amount <span class="text-red-600">*</span></label>
            <span class="text-slate-400">:</span>
            <div class="flex">
              <span class="inline-flex h-8 items-center rounded-l border border-r-0 border-slate-300 bg-slate-100 px-2 text-xs text-slate-600">MYR</span>
              <input v-model="othersForm.amount" type="number" min="0" step="0.01" class="h-8 flex-1 rounded-r border border-slate-300 px-2 text-right text-xs focus:outline-none focus:ring-2 focus:ring-indigo-400" placeholder="0.00" />
            </div>
          </div>

          <!-- Factoring Type -->
          <div class="grid items-center gap-2 md:grid-cols-[10rem_0.5rem_1fr]">
            <label class="text-xs font-semibold text-slate-700">Factoring Type</label>
            <span class="text-slate-400">:</span>
            <input v-model="othersForm.factoringType" type="text" class="h-8 rounded border border-slate-300 px-2 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-400" placeholder="Factoring type" autocomplete="off" />
          </div>

          <!-- Factoring ID -->
          <div class="grid items-center gap-2 md:grid-cols-[10rem_0.5rem_1fr]">
            <label class="text-xs font-semibold text-slate-700">Factoring ID</label>
            <span class="text-slate-400">:</span>
            <input v-model="othersForm.factoringId" type="text" class="h-8 rounded border border-slate-300 px-2 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-400" placeholder="Factoring ID" autocomplete="off" />
          </div>

          <!-- Factoring Name -->
          <div class="grid items-center gap-2 md:grid-cols-[10rem_0.5rem_1fr]">
            <label class="text-xs font-semibold text-slate-700">Factoring Name</label>
            <span class="text-slate-400">:</span>
            <input v-model="othersForm.factoringName" type="text" class="h-8 rounded border border-slate-300 px-2 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-400" autocomplete="off" />
          </div>

          <!-- Factoring Account No -->
          <div class="grid items-center gap-2 md:grid-cols-[10rem_0.5rem_1fr]">
            <label class="text-xs font-semibold text-slate-700">Factoring Account No</label>
            <span class="text-slate-400">:</span>
            <input v-model="othersForm.factoringAccountNo" type="text" class="h-8 rounded border border-slate-300 px-2 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-400" autocomplete="off" />
          </div>

        </div>
        <div class="flex items-center justify-end gap-2 border-t border-slate-200 px-4 py-3">
          <button type="button" class="rounded-lg bg-red-500 px-4 py-1.5 text-xs font-medium text-white hover:bg-red-600" @click="activeModal = ''">Cancel</button>
          <button type="button" class="rounded-lg bg-sky-600 px-4 py-1.5 text-xs font-medium text-white hover:bg-sky-700" @click="saveOthersLine">Save</button>
        </div>
      </div>
    </div>
  </Teleport>

  <!-- ════════════════════════════════════════════════════════════════════
       MODAL: Upload Voucher for Staff
       ════════════════════════════════════════════════════════════════════ -->
  <Teleport to="body">
    <div v-if="activeModal === 'uploadStaff'" class="fixed inset-0 z-[200] flex items-center justify-center bg-black/40 p-4">
      <div class="w-full max-w-md rounded-lg bg-white shadow-xl">
        <div class="flex items-center justify-between rounded-t-lg bg-indigo-400 px-4 py-3">
          <h3 class="text-sm font-semibold text-white">Upload Voucher for Staff</h3>
          <button type="button" class="text-white/80 hover:text-white" @click="activeModal = ''">✕</button>
        </div>
        <div class="p-4">
          <div class="grid items-center gap-2 md:grid-cols-[7rem_0.5rem_1fr]">
            <label class="text-xs font-semibold text-slate-700">File (.csv) <span class="text-red-600">*</span></label>
            <span class="text-slate-400">:</span>
            <div class="flex items-center gap-2">
              <input id="upload-staff-file" type="file" accept=".csv" class="hidden" @change="handleUploadFile('staff', $event)" />
              <label for="upload-staff-file" class="flex h-8 flex-1 cursor-pointer items-center rounded border border-slate-300 bg-white px-2 text-xs text-slate-500 hover:bg-slate-50">
                {{ uploadStaffFile ? uploadStaffFile.name : 'Choose file…' }}
              </label>
              <label for="upload-staff-file" class="inline-flex h-8 cursor-pointer items-center rounded border border-slate-300 bg-slate-100 px-2 text-slate-600 hover:bg-slate-200">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
              </label>
            </div>
          </div>
        </div>
        <div class="flex items-center justify-end gap-2 border-t border-slate-200 px-4 py-3">
          <button type="button" class="rounded-lg bg-red-500 px-4 py-1.5 text-xs font-medium text-white hover:bg-red-600" @click="activeModal = ''">Cancel</button>
          <button type="button" class="rounded-lg bg-sky-600 px-4 py-1.5 text-xs font-medium text-white hover:bg-sky-700" @click="submitUpload('staff')">Upload</button>
          <button type="button" class="rounded-lg border border-slate-300 bg-white px-4 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50" @click="toast.info('Template', 'Template download coming soon.')">Template</button>
        </div>
      </div>
    </div>
  </Teleport>

  <!-- ════════════════════════════════════════════════════════════════════
       MODAL: Upload Voucher for Others
       ════════════════════════════════════════════════════════════════════ -->
  <Teleport to="body">
    <div v-if="activeModal === 'uploadOthers'" class="fixed inset-0 z-[200] flex items-center justify-center bg-black/40 p-4">
      <div class="w-full max-w-md rounded-lg bg-white shadow-xl">
        <div class="flex items-center justify-between rounded-t-lg bg-indigo-400 px-4 py-3">
          <h3 class="text-sm font-semibold text-white">Upload Voucher for Others</h3>
          <button type="button" class="text-white/80 hover:text-white" @click="activeModal = ''">✕</button>
        </div>
        <div class="p-4">
          <div class="grid items-center gap-2 md:grid-cols-[7rem_0.5rem_1fr]">
            <label class="text-xs font-semibold text-slate-700">File (.csv) <span class="text-red-600">*</span></label>
            <span class="text-slate-400">:</span>
            <div class="flex items-center gap-2">
              <input id="upload-others-file" type="file" accept=".csv" class="hidden" @change="handleUploadFile('others', $event)" />
              <label for="upload-others-file" class="flex h-8 flex-1 cursor-pointer items-center rounded border border-slate-300 bg-white px-2 text-xs text-slate-500 hover:bg-slate-50">
                {{ uploadOthersFile ? uploadOthersFile.name : 'Choose file…' }}
              </label>
              <label for="upload-others-file" class="inline-flex h-8 cursor-pointer items-center rounded border border-slate-300 bg-slate-100 px-2 text-slate-600 hover:bg-slate-200">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
              </label>
            </div>
          </div>
        </div>
        <div class="flex items-center justify-end gap-2 border-t border-slate-200 px-4 py-3">
          <button type="button" class="rounded-lg bg-red-500 px-4 py-1.5 text-xs font-medium text-white hover:bg-red-600" @click="activeModal = ''">Cancel</button>
          <button type="button" class="rounded-lg bg-sky-600 px-4 py-1.5 text-xs font-medium text-white hover:bg-sky-700" @click="submitUpload('others')">Upload</button>
          <button type="button" class="rounded-lg border border-slate-300 bg-white px-4 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50" @click="toast.info('Template', 'Template download coming soon.')">Template</button>
        </div>
      </div>
    </div>
  </Teleport>
</template>
