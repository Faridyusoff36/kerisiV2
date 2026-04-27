<script setup lang="ts">
import { computed, onMounted, ref } from "vue";
import { ChevronLeft, Download, FileUp, Plus, Trash2, Upload, X } from "lucide-vue-next";
import { useRouter } from "vue-router";
import AdminLayout from "@/layouts/AdminLayout.vue";
import {
  createBudgetPlanningNew,
  getBudgetPlanningNewOptions,
  listBudgetPlanningNewAccounts,
} from "@/api/cms";
import { useToast } from "@/composables/useToast";
import { useConfirmDialog } from "@/composables/useConfirmDialog";
import type {
  BudgetPlanningNewAccount,
  BudgetPlanningNewOptions,
} from "@/types";

const router = useRouter();
const toast = useToast();
const { confirm } = useConfirmDialog();

const loadingOptions = ref(false);
const loadingAccounts = ref(false);
const submitting = ref(false);
const showRemarkModal = ref(false);
const showCsvUpload = ref(false);

const options = ref<BudgetPlanningNewOptions>({
  defaults: { year: new Date().getFullYear() },
  ptjs: [],
  costCentres: [],
  funds: [],
  activities: [],
  types: [],
});

const form = ref({
  bpmYear: new Date().getFullYear(),
  bpmOunCode: "",
  bpmCcrCostcentre: "",
  ftyFundType: "",
  atActivityCode: "",
  bpmType: "",
  bpmRemark: "",
});

const lines = ref<BudgetPlanningNewAccount[]>([]);
const csvFileName = ref<string>("");

const filteredCostCentres = computed(() => {
  if (!form.value.bpmOunCode) return options.value.costCentres;
  return options.value.costCentres.filter((cc) => cc.ounCode === form.value.bpmOunCode);
});

const planningInfoComplete = computed(() => {
  const f = form.value;
  return Boolean(
    f.bpmYear &&
      f.bpmOunCode &&
      f.bpmCcrCostcentre &&
      f.ftyFundType &&
      f.atActivityCode &&
      f.bpmType,
  );
});

const totalAmount = computed(() =>
  lines.value.reduce((acc, l) => acc + Number(l.bpdAmt || 0), 0),
);

const totalLineCount = computed(() => lines.value.length);

const validLineCount = computed(
  () => lines.value.filter((l) => Number(l.bpdAmt) > 0).length,
);

function fmtMoney(v: number | null | undefined): string {
  if (v === null || v === undefined) return "0.00";
  return Number(v).toLocaleString("en-US", {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });
}

async function loadOptions() {
  loadingOptions.value = true;
  try {
    const res = await getBudgetPlanningNewOptions();
    options.value = res.data;
    if (!form.value.bpmYear || form.value.bpmYear < 2000) {
      form.value.bpmYear = res.data.defaults.year;
    }
    if (!form.value.bpmType && res.data.types[0]) {
      form.value.bpmType = res.data.types[0].id;
    }
  } catch (e) {
    toast.error("Load failed", e instanceof Error ? e.message : "Failed to load form options.");
  } finally {
    loadingOptions.value = false;
  }
}

async function loadAccounts() {
  if (!form.value.ftyFundType) {
    toast.error("Fund required", "Pick a Fund before loading accounts.");
    return;
  }
  loadingAccounts.value = true;
  try {
    const res = await listBudgetPlanningNewAccounts(
      form.value.ftyFundType,
      form.value.atActivityCode,
    );
    const previous = new Map(lines.value.map((l) => [l.acmAcctCode, Number(l.bpdAmt) || 0]));
    lines.value = res.data.map((acct) => ({
      ...acct,
      bpdAmt: previous.get(acct.acmAcctCode) ?? 0,
    }));
    if (lines.value.length === 0) {
      toast.info("No accounts", "No accounts are mapped to this Fund / Activity combination.");
    } else {
      toast.success(`${lines.value.length} account candidate(s) loaded.`);
    }
  } catch (e) {
    toast.error("Load failed", e instanceof Error ? e.message : "Failed to load account list.");
  } finally {
    loadingAccounts.value = false;
  }
}

function clearLines() {
  lines.value = [];
}

function removeLine(idx: number) {
  lines.value.splice(idx, 1);
}

function csvEscape(value: string): string {
  if (value === null || value === undefined) return "";
  const s = String(value);
  return /[",\r\n]/.test(s) ? `"${s.replace(/"/g, '""')}"` : s;
}

function downloadCsv() {
  if (lines.value.length === 0) {
    toast.info("No data", "Load the account list first.");
    return;
  }
  const header = ["Account Code", "Account", "Account Activity", "Account Level", "Amount (RM)"];
  const csvLines = [header.map(csvEscape).join(",")];
  lines.value.forEach((l) => {
    csvLines.push(
      [
        csvEscape(l.acmAcctCode),
        csvEscape(l.acmAcctDesc),
        csvEscape(l.acmAcctActivity),
        csvEscape(l.acmAcctLevel?.toString() ?? ""),
        Number(l.bpdAmt || 0).toFixed(2),
      ].join(","),
    );
  });
  const csv = "\uFEFF" + csvLines.join("\r\n");
  const blob = new Blob([csv], { type: "text/csv;charset=utf-8" });
  const url = URL.createObjectURL(blob);
  const a = document.createElement("a");
  a.href = url;
  a.download = `planning_template_${form.value.bpmYear}_${form.value.ftyFundType || "fund"}_${
    form.value.atActivityCode || "activity"
  }.csv`;
  a.click();
  URL.revokeObjectURL(url);
  toast.success("CSV downloaded.");
}

function parseCsvText(text: string): { code: string; amount: number }[] {
  const cleaned = text.replace(/^\uFEFF/, "");
  const rows: string[][] = [];
  let cur: string[] = [];
  let cell = "";
  let inQuotes = false;
  for (let i = 0; i < cleaned.length; i++) {
    const ch = cleaned[i];
    if (inQuotes) {
      if (ch === '"' && cleaned[i + 1] === '"') {
        cell += '"';
        i++;
      } else if (ch === '"') {
        inQuotes = false;
      } else {
        cell += ch;
      }
    } else if (ch === '"') {
      inQuotes = true;
    } else if (ch === ",") {
      cur.push(cell);
      cell = "";
    } else if (ch === "\n") {
      cur.push(cell);
      rows.push(cur);
      cur = [];
      cell = "";
    } else if (ch === "\r") {
      // ignore
    } else {
      cell += ch;
    }
  }
  if (cell.length > 0 || cur.length > 0) {
    cur.push(cell);
    rows.push(cur);
  }

  const out: { code: string; amount: number }[] = [];
  rows.forEach((r, idx) => {
    if (r.length < 2) return;
    if (idx === 0) {
      const headerCell = (r[0] ?? "").toLowerCase().trim();
      if (headerCell.includes("account") && headerCell.includes("code")) return;
    }
    const code = (r[0] ?? "").trim();
    if (!code) return;
    const amountRaw = (r[r.length - 1] ?? "").toString().replace(/[^0-9.\-]/g, "");
    const amount = Number(amountRaw);
    if (!Number.isFinite(amount)) return;
    out.push({ code, amount });
  });
  return out;
}

async function onCsvFileChange(e: Event) {
  const input = e.target as HTMLInputElement;
  const file = input.files?.[0];
  if (!file) return;
  csvFileName.value = file.name;
  try {
    const text = await file.text();
    const parsed = parseCsvText(text);
    if (parsed.length === 0) {
      toast.error("CSV empty", "No usable rows were found.");
      return;
    }
    const amountByCode = new Map(parsed.map((p) => [p.code, p.amount]));
    let merged = 0;
    let appended = 0;
    lines.value = lines.value.map((l) => {
      if (amountByCode.has(l.acmAcctCode)) {
        merged++;
        return { ...l, bpdAmt: amountByCode.get(l.acmAcctCode) ?? 0 };
      }
      return l;
    });
    const existingCodes = new Set(lines.value.map((l) => l.acmAcctCode));
    parsed.forEach((p) => {
      if (!existingCodes.has(p.code)) {
        appended++;
        lines.value.push({
          index: lines.value.length + 1,
          acmAcctCode: p.code,
          acmAcctDesc: "",
          acmAcctActivity: "",
          acmAcctLevel: null,
          bpdAmt: p.amount,
        });
      }
    });
    lines.value = lines.value.map((l, idx) => ({ ...l, index: idx + 1 }));
    toast.success(
      `CSV applied: ${merged} matched, ${appended} added.`,
    );
    showCsvUpload.value = false;
  } catch (err) {
    toast.error("Parse failed", err instanceof Error ? err.message : "Failed to parse CSV.");
  } finally {
    input.value = "";
  }
}

function openRemarkModal() {
  if (!planningInfoComplete.value) {
    toast.error("Incomplete", "Please complete the Planning Info section first.");
    return;
  }
  if (validLineCount.value === 0) {
    toast.error("No amounts", "Enter at least one positive Amount before submitting.");
    return;
  }
  showRemarkModal.value = true;
}

async function confirmAndSubmit() {
  if (!form.value.bpmRemark.trim()) {
    toast.error("Remark required", "Please leave a remark before submitting.");
    return;
  }
  const accepted = await confirm({
    title: "Submit planning record?",
    message: `This creates a DRAFT planning record with ${validLineCount.value} line(s) totaling RM ${fmtMoney(
      totalAmount.value,
    )}.`,
    confirmText: "Yes, submit",
  });
  if (!accepted) return;

  submitting.value = true;
  try {
    const payload = {
      bpmYear: Number(form.value.bpmYear),
      bpmOunCode: form.value.bpmOunCode,
      bpmCcrCostcentre: form.value.bpmCcrCostcentre,
      ftyFundType: form.value.ftyFundType,
      atActivityCode: form.value.atActivityCode,
      bpmType: form.value.bpmType,
      bpmRemark: form.value.bpmRemark.trim(),
      lines: lines.value
        .filter((l) => Number(l.bpdAmt) > 0)
        .map((l) => ({
          acmAcctCode: l.acmAcctCode,
          bpdAmt: Number(l.bpdAmt),
        })),
    };
    const res = await createBudgetPlanningNew(payload);
    toast.success(res.data.successMessage ?? "Planning record created.");
    showRemarkModal.value = false;
    router.push({ name: "kerisi-planning-dasar-sedia-ada" });
  } catch (e) {
    const err = e as { details?: Record<string, string[]>; message?: string };
    if (err?.details) {
      const first = Object.values(err.details)[0];
      const msg = Array.isArray(first) ? first[0] : "Validation failed.";
      toast.error("Validation failed", msg ?? "Please review the form.");
    } else {
      toast.error("Submit failed", err.message ?? "Failed to create planning record.");
    }
  } finally {
    submitting.value = false;
  }
}

function goBack() {
  if (window.history.length > 1) router.back();
  else router.push("/admin");
}

onMounted(loadOptions);
</script>

<template>
  <AdminLayout>
    <div class="space-y-4">
      <div class="flex items-center gap-2">
        <button
          type="button"
          class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-2.5 py-1 text-xs text-slate-600 hover:bg-slate-50"
          @click="goBack"
          aria-label="Back"
        >
          <ChevronLeft class="h-3.5 w-3.5" />
          Back
        </button>
        <h1 class="page-title">Budget / Planning / Planning New Application</h1>
      </div>

      <!-- Planning Info card -->
      <article class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-4 py-3">
          <h2 class="text-base font-semibold text-slate-900">Planning Info</h2>
          <p class="mt-1 text-xs text-slate-500">Fill in the planning scope. All starred fields are required.</p>
        </div>
        <div class="grid grid-cols-1 gap-3 p-4 sm:grid-cols-2 lg:grid-cols-3">
          <div>
            <label class="text-xs font-medium text-slate-600">Reference No</label>
            <input
              type="text"
              disabled
              placeholder="(generated on save)"
              class="mt-1 w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-1.5 text-sm text-slate-500"
            />
          </div>
          <div>
            <label class="text-xs font-medium text-slate-600">Year <span class="text-rose-500">*</span></label>
            <input
              v-model.number="form.bpmYear"
              type="number"
              min="2000"
              max="2100"
              class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm"
            />
          </div>
          <div>
            <label class="text-xs font-medium text-slate-600">Type <span class="text-rose-500">*</span></label>
            <select
              v-model="form.bpmType"
              class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm"
            >
              <option value="" disabled>— Select Type —</option>
              <option v-for="t in options.types" :key="t.id" :value="t.id">{{ t.label }}</option>
            </select>
          </div>
          <div>
            <label class="text-xs font-medium text-slate-600">PTJ <span class="text-rose-500">*</span></label>
            <select
              v-model="form.bpmOunCode"
              class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm"
              @change="form.bpmCcrCostcentre = ''"
            >
              <option value="" disabled>— Select PTJ —</option>
              <option v-for="p in options.ptjs" :key="p.id" :value="p.id">{{ p.label }}</option>
            </select>
          </div>
          <div>
            <label class="text-xs font-medium text-slate-600">Cost Centre <span class="text-rose-500">*</span></label>
            <select
              v-model="form.bpmCcrCostcentre"
              class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm"
              :disabled="!form.bpmOunCode"
            >
              <option value="" disabled>— Select Cost Centre —</option>
              <option v-for="c in filteredCostCentres" :key="c.id" :value="c.id">{{ c.label }}</option>
            </select>
          </div>
          <div>
            <label class="text-xs font-medium text-slate-600">Fund <span class="text-rose-500">*</span></label>
            <select
              v-model="form.ftyFundType"
              class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm"
            >
              <option value="" disabled>— Select Fund —</option>
              <option v-for="f in options.funds" :key="f.id" :value="f.id">{{ f.label }}</option>
            </select>
          </div>
          <div class="sm:col-span-2 lg:col-span-3">
            <label class="text-xs font-medium text-slate-600">Activity <span class="text-rose-500">*</span></label>
            <select
              v-model="form.atActivityCode"
              class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm"
            >
              <option value="" disabled>— Select Activity —</option>
              <option v-for="a in options.activities" :key="a.id" :value="a.id">{{ a.label }}</option>
            </select>
          </div>
        </div>
      </article>

      <!-- Account Activity card -->
      <article class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
          <div>
            <h2 class="text-base font-semibold text-slate-900">Account Activity</h2>
            <p class="mt-1 text-xs text-slate-500">
              Load accounts mapped to the chosen Fund, fill in amounts, or download / upload a CSV template.
            </p>
          </div>
          <div class="flex flex-wrap items-center gap-2">
            <button
              type="button"
              class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50 disabled:opacity-50"
              :disabled="!form.ftyFundType || loadingAccounts"
              @click="loadAccounts"
            >
              <Plus class="h-3.5 w-3.5" />
              {{ loadingAccounts ? "Loading…" : "Load Accounts" }}
            </button>
            <button
              type="button"
              class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50 disabled:opacity-50"
              :disabled="lines.length === 0"
              @click="downloadCsv"
            >
              <Download class="h-3.5 w-3.5" />
              Download CSV Template
            </button>
            <button
              type="button"
              class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50"
              @click="showCsvUpload = true"
            >
              <FileUp class="h-3.5 w-3.5" />
              Upload Template
            </button>
            <button
              v-if="lines.length > 0"
              type="button"
              class="inline-flex items-center gap-1 rounded-lg border border-rose-300 bg-white px-3 py-1.5 text-xs font-medium text-rose-600 hover:bg-rose-50"
              @click="clearLines"
            >
              <X class="h-3.5 w-3.5" />
              Clear Lines
            </button>
          </div>
        </div>
        <div class="space-y-3 p-4">
          <div class="flex flex-wrap items-center justify-between gap-3 text-xs text-slate-600">
            <div class="flex flex-wrap items-center gap-3">
              <span>Total lines: <strong>{{ totalLineCount }}</strong></span>
              <span>With amount: <strong>{{ validLineCount }}</strong></span>
            </div>
            <div class="text-sm font-semibold text-slate-800">
              Total: RM {{ fmtMoney(totalAmount) }}
            </div>
          </div>
          <div class="overflow-x-auto rounded-lg border border-slate-200">
            <div :class="lines.length > 10 ? 'max-h-[480px] overflow-y-auto' : ''">
              <table class="w-full min-w-[900px] text-sm">
                <thead class="sticky top-0 bg-slate-50">
                  <tr class="border-b border-slate-200 text-left">
                    <th class="px-3 py-2 text-xs font-semibold uppercase">No</th>
                    <th class="px-3 py-2 text-xs font-semibold uppercase">Account Code</th>
                    <th class="px-3 py-2 text-xs font-semibold uppercase">Account</th>
                    <th class="px-3 py-2 text-xs font-semibold uppercase">Account Activity</th>
                    <th class="px-3 py-2 text-xs font-semibold uppercase">Account Level</th>
                    <th class="px-3 py-2 text-right text-xs font-semibold uppercase">Amount (RM)</th>
                    <th class="px-3 py-2 text-xs font-semibold uppercase">Action</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-if="lines.length === 0">
                    <td colspan="7" class="px-3 py-6 text-center text-xs text-slate-500">
                      No accounts loaded yet. Pick a Fund above and click <strong>Load Accounts</strong>.
                    </td>
                  </tr>
                  <tr
                    v-for="(line, idx) in lines"
                    :key="line.acmAcctCode"
                    class="border-b border-slate-100 hover:bg-slate-50"
                  >
                    <td class="px-3 py-2 text-slate-500">{{ idx + 1 }}</td>
                    <td class="px-3 py-2 font-medium">{{ line.acmAcctCode }}</td>
                    <td class="px-3 py-2 max-w-[280px] truncate" :title="line.acmAcctDesc">
                      {{ line.acmAcctDesc || "—" }}
                    </td>
                    <td class="px-3 py-2">{{ line.acmAcctActivity || "—" }}</td>
                    <td class="px-3 py-2">{{ line.acmAcctLevel ?? "—" }}</td>
                    <td class="px-3 py-2 text-right">
                      <input
                        v-model.number="line.bpdAmt"
                        type="number"
                        step="0.01"
                        min="0"
                        class="w-32 rounded border border-slate-300 px-2 py-1 text-right text-sm"
                      />
                    </td>
                    <td class="px-3 py-2">
                      <button
                        class="rounded p-1 text-rose-500 hover:bg-rose-50"
                        title="Remove line"
                        @click="removeLine(idx)"
                      >
                        <Trash2 class="h-3.5 w-3.5" />
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </article>

      <!-- Submit row -->
      <div class="flex items-center justify-end gap-2">
        <button
          type="button"
          class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
          @click="goBack"
        >
          Cancel
        </button>
        <button
          type="button"
          class="inline-flex items-center gap-1 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 disabled:opacity-50"
          :disabled="!planningInfoComplete || validLineCount === 0 || submitting"
          @click="openRemarkModal"
        >
          <Upload class="h-3.5 w-3.5" />
          Submit
        </button>
      </div>
    </div>

    <!-- CSV Upload Modal -->
    <Teleport to="body">
      <div
        v-if="showCsvUpload"
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 px-4"
        @click.self="showCsvUpload = false"
      >
        <div class="w-full max-w-lg rounded-lg bg-white shadow-xl">
          <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
            <h3 class="text-base font-semibold text-slate-900">Upload CSV Template</h3>
            <button class="rounded p-1 text-slate-500 hover:bg-slate-100" @click="showCsvUpload = false">
              <X class="h-4 w-4" />
            </button>
          </div>
          <div class="space-y-3 p-4 text-sm text-slate-600">
            <p>
              The CSV must have <strong>Account Code</strong> as the first column and
              <strong>Amount (RM)</strong> as the last column (matching the downloaded template).
              Existing loaded accounts will have their amounts updated; unknown codes are appended.
            </p>
            <input
              type="file"
              accept=".csv,text/csv"
              class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm file:mr-3 file:rounded-md file:border-0 file:bg-slate-100 file:px-3 file:py-1.5 file:text-xs file:font-medium file:text-slate-700 hover:file:bg-slate-200"
              @change="onCsvFileChange"
            />
            <p v-if="csvFileName" class="text-xs text-slate-500">Last upload: {{ csvFileName }}</p>
          </div>
          <div class="flex items-center justify-end gap-2 border-t border-slate-100 px-4 py-3">
            <button
              class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50"
              @click="showCsvUpload = false"
            >
              Close
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Remark Modal -->
    <Teleport to="body">
      <div
        v-if="showRemarkModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 px-4"
        @click.self="showRemarkModal = false"
      >
        <div class="w-full max-w-lg rounded-lg bg-white shadow-xl">
          <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
            <h3 class="text-base font-semibold text-slate-900">Leave some remark</h3>
            <button class="rounded p-1 text-slate-500 hover:bg-slate-100" @click="showRemarkModal = false">
              <X class="h-4 w-4" />
            </button>
          </div>
          <div class="space-y-3 p-4">
            <div>
              <label class="text-xs font-medium text-slate-600">Remark <span class="text-rose-500">*</span></label>
              <textarea
                v-model="form.bpmRemark"
                rows="4"
                class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
                placeholder="Why are we creating this planning record?"
              ></textarea>
            </div>
            <div class="rounded-lg bg-slate-50 px-3 py-2 text-xs text-slate-600">
              <div>Lines with amount: <strong>{{ validLineCount }}</strong></div>
              <div>Total: <strong>RM {{ fmtMoney(totalAmount) }}</strong></div>
            </div>
          </div>
          <div class="flex items-center justify-end gap-2 border-t border-slate-100 px-4 py-3">
            <button
              type="button"
              class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50"
              :disabled="submitting"
              @click="showRemarkModal = false"
            >
              Cancel
            </button>
            <button
              type="button"
              class="rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-blue-700 disabled:opacity-50"
              :disabled="submitting"
              @click="confirmAndSubmit"
            >
              {{ submitting ? "Submitting…" : "Submit" }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </AdminLayout>
</template>
