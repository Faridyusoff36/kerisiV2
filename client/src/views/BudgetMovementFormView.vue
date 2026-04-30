<script setup lang="ts">
/**
 * Budget movement form (legacy Increment / Decrement / Virement Form).
 * Menus MENUID 1557 / 1558 / 1559 — read-only header + detail lines.
 */
import { computed, onMounted, ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import { ArrowLeft } from "lucide-vue-next";
import AdminLayout from "@/layouts/AdminLayout.vue";
import { getBudgetMovementForm } from "@/api/cms";
import { useToast } from "@/composables/useToast";
import type {
  BudgetMovementFormData,
  BudgetMovementFormDetailRow,
  BudgetMovementStructureSlot,
  BudgetMovementType,
} from "@/types";

const props = defineProps<{
  type: BudgetMovementType;
}>();

const route = useRoute();
const router = useRouter();
const toast = useToast();

const meta = computed(() => {
  switch (props.type) {
    case "increment":
      return {
        formTitle: "Increment Form",
        breadcrumb: "Budget / Increment / Increment Form",
        listPath: "/admin/kerisi/m/1554",
      };
    case "decrement":
      return {
        formTitle: "Decrement Form",
        breadcrumb: "Budget / Decrement / Decrement Form",
        listPath: "/admin/kerisi/m/1555",
      };
    case "virement":
      return {
        formTitle: "Virement Form",
        breadcrumb: "Budget / Virement / Virement Form",
        listPath: "/admin/kerisi/m/1556",
      };
  }
});

const movementId = computed(() => {
  const raw = route.query.id;
  const s = Array.isArray(raw) ? raw[0] : raw;
  if (s === undefined || s === null || s === "") return null;
  const n = Number(s);
  return Number.isFinite(n) && n > 0 ? n : null;
});

const payload = ref<BudgetMovementFormData | null>(null);
const loading = ref(false);

const currency = new Intl.NumberFormat("en-MY", { minimumFractionDigits: 2, maximumFractionDigits: 2 });

function formatAmount(v: unknown): string {
  if (v === null || v === undefined || v === "") return "—";
  const n = typeof v === "number" ? v : Number(String(v).replace(/,/g, ""));
  if (!Number.isFinite(n)) return String(v);
  return currency.format(n);
}

function formatDate(v: unknown): string {
  if (!v) return "—";
  const d = new Date(String(v));
  if (Number.isNaN(d.getTime())) return "—";
  return `${String(d.getDate()).padStart(2, "0")}/${String(d.getMonth() + 1).padStart(2, "0")}/${d.getFullYear()}`;
}

function slotId(b: BudgetMovementStructureSlot | null): string {
  if (!b) return "";
  return b.sbgBudgetId != null && b.sbgBudgetId !== "" ? String(b.sbgBudgetId) : "";
}

function structureCells(b: BudgetMovementStructureSlot | null) {
  if (!b) {
    return { id: "", ptj: "", cc: "", fund: "", act: "", code: "" };
  }
  if (!slotId(b)) {
    return { id: "", ptj: "", cc: "", fund: "", act: "", code: "" };
  }
  return {
    id: String(b.sbgBudgetId ?? ""),
    ptj: b.ounCode ?? "",
    cc: b.ccrCostcentre ?? "",
    fund: b.ftyFundType ?? "",
    act: b.atActivityCode ?? "",
    code: b.lbcBudgetCode ?? "",
  };
}

/** Raw FK when structure_budget row is missing. */
function fallbackFromId(d: BudgetMovementFormDetailRow): string {
  return d.sbgBudgetIdFrom != null && String(d.sbgBudgetIdFrom) !== "" ? String(d.sbgBudgetIdFrom) : "";
}

function fallbackToId(d: BudgetMovementFormDetailRow): string {
  return d.sbgBudgetIdTo != null && String(d.sbgBudgetIdTo) !== "" ? String(d.sbgBudgetIdTo) : "";
}

async function load() {
  if (movementId.value == null) {
    payload.value = null;
    return;
  }
  loading.value = true;
  try {
    const res = await getBudgetMovementForm(props.type, movementId.value);
    payload.value = res.data;
  } catch (e) {
    payload.value = null;
    toast.error("Load failed", e instanceof Error ? e.message : "Could not load movement.");
  } finally {
    loading.value = false;
  }
}

function gotoList() {
  void router.push({ path: meta.value.listPath });
}

const isEditMode = computed(() => route.query.mode === "edit");

onMounted(() => {
  void load();
});

watch(
  () => [props.type, movementId.value] as const,
  () => {
    void load();
  },
);
</script>

<template>
  <AdminLayout>
    <div class="space-y-4">
      <div class="flex flex-wrap items-center gap-3">
        <button
          type="button"
          class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm hover:bg-slate-50"
          @click="gotoList"
        >
          <ArrowLeft class="h-4 w-4" />
          Back to list
        </button>
      </div>

      <h1 class="page-title">{{ meta.breadcrumb }}</h1>

      <article class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-4 py-3">
          <h2 class="text-base font-semibold text-slate-900">{{ meta.formTitle }}</h2>
          <p v-if="isEditMode" class="mt-1 text-xs text-amber-800">
            Editing is not available in this screen; data is shown read-only from the legacy movement.
          </p>
        </div>

        <div v-if="movementId == null" class="p-6 text-sm text-slate-500">
          Open this form from the budget list using <strong>View</strong> or <strong>Edit</strong>, or append
          <code class="rounded bg-slate-100 px-1">?id=</code> with a movement id.
        </div>

        <div v-else-if="loading" class="p-6 text-sm text-slate-500">Loading…</div>

        <div v-else-if="!payload" class="p-6 text-sm text-slate-500">No data.</div>

        <div v-else class="space-y-4 p-4">
          <div class="grid gap-3 rounded-lg border border-slate-100 bg-slate-50/80 p-4 text-sm sm:grid-cols-2 lg:grid-cols-3">
            <div>
              <div class="text-xs font-medium uppercase text-slate-500">Reference no</div>
              <div class="font-medium text-slate-900">{{ payload.master.bmmBudgetMovementNo ?? "—" }}</div>
            </div>
            <div>
              <div class="text-xs font-medium uppercase text-slate-500">Year</div>
              <div class="font-medium text-slate-900">{{ payload.master.bmmYear ?? "—" }}</div>
            </div>
            <div v-if="type === 'virement'">
              <div class="text-xs font-medium uppercase text-slate-500">Movement type</div>
              <div class="font-medium text-slate-900">{{ payload.master.bmmMovementType ?? "—" }}</div>
            </div>
            <div>
              <div class="text-xs font-medium uppercase text-slate-500">Status</div>
              <div class="font-medium text-slate-900">{{ payload.master.bmmStatus ?? "—" }}</div>
            </div>
            <div>
              <div class="text-xs font-medium uppercase text-slate-500">Total amount</div>
              <div class="font-medium text-slate-900">{{ formatAmount(payload.master.bmmTotalAmt) }}</div>
            </div>
            <div>
              <div class="text-xs font-medium uppercase text-slate-500">Date</div>
              <div class="font-medium text-slate-900">{{ formatDate(payload.master.date) }}</div>
            </div>
            <div class="sm:col-span-2 lg:col-span-3">
              <div class="text-xs font-medium uppercase text-slate-500">Reason / remark</div>
              <div class="text-slate-900">{{ payload.master.bmmDescription ?? payload.master.bmmReason ?? "—" }}</div>
            </div>
            <div v-if="payload.master.bmmEndorseDoc" class="sm:col-span-2 lg:col-span-3">
              <div class="text-xs font-medium uppercase text-slate-500">Authority / endorsement</div>
              <div class="text-slate-900">{{ payload.master.bmmEndorseDoc }}</div>
            </div>
          </div>

          <div class="overflow-x-auto rounded-lg border border-slate-200">
            <table class="admin-table-kitchen" :class="type === 'virement' ? 'w-full min-w-[1200px] text-sm' : 'w-full min-w-[960px] text-sm'">
              <thead class="admin-table-thead-sticky">
                <template v-if="type === 'virement'">
                  <tr class="border-b border-slate-200 text-left text-xs font-semibold uppercase">
                    <th class="px-3 py-2" rowspan="2">No</th>
                    <th class="border-l border-slate-200 px-3 py-2 text-center" colspan="6">From</th>
                    <th class="border-l border-slate-200 px-3 py-2 text-center" colspan="6">To</th>
                    <th class="border-l border-slate-200 px-3 py-2" rowspan="2">Quarter</th>
                    <th class="border-l border-slate-200 px-3 py-2 text-right" rowspan="2">Amount</th>
                  </tr>
                  <tr class="border-b border-slate-200 text-left text-xs font-semibold uppercase">
                    <th class="border-l border-slate-200 px-3 py-1.5">SBG</th>
                    <th class="px-3 py-1.5">PTJ</th>
                    <th class="px-3 py-1.5">CC</th>
                    <th class="px-3 py-1.5">Fund</th>
                    <th class="px-3 py-1.5">Activity</th>
                    <th class="px-3 py-1.5">Code</th>
                    <th class="border-l border-slate-200 px-3 py-1.5">SBG</th>
                    <th class="px-3 py-1.5">PTJ</th>
                    <th class="px-3 py-1.5">CC</th>
                    <th class="px-3 py-1.5">Fund</th>
                    <th class="px-3 py-1.5">Activity</th>
                    <th class="px-3 py-1.5">Code</th>
                  </tr>
                </template>
                <template v-else>
                  <tr class="border-b border-slate-200 text-left text-xs font-semibold uppercase">
                    <th class="px-3 py-2">No</th>
                    <th class="px-3 py-2">SBG</th>
                    <th class="px-3 py-2">PTJ</th>
                    <th class="px-3 py-2">CC</th>
                    <th class="px-3 py-2">Fund</th>
                    <th class="px-3 py-2">Activity</th>
                    <th class="px-3 py-2">Code</th>
                    <th class="px-3 py-2">Quarter</th>
                    <th class="px-3 py-2 text-right">Amount</th>
                  </tr>
                </template>
              </thead>
              <tbody>
                <tr
                  v-for="(line, idx) in payload.details"
                  :key="line.bmdBgtMovementDetlId"
                  class="border-b border-slate-100 hover:bg-slate-50"
                >
                  <template v-if="type === 'virement'">
                    <td class="px-3 py-2">{{ idx + 1 }}</td>
                    <td class="border-l border-slate-100 px-3 py-2 font-mono text-xs">
                      {{ structureCells(line.sourceBudget).id || fallbackFromId(line) || "—" }}
                    </td>
                    <td class="px-3 py-2">{{ structureCells(line.sourceBudget).ptj || "—" }}</td>
                    <td class="px-3 py-2">{{ structureCells(line.sourceBudget).cc || "—" }}</td>
                    <td class="px-3 py-2">{{ structureCells(line.sourceBudget).fund || "—" }}</td>
                    <td class="px-3 py-2">{{ structureCells(line.sourceBudget).act || "—" }}</td>
                    <td class="px-3 py-2">{{ structureCells(line.sourceBudget).code || "—" }}</td>
                    <td class="border-l border-slate-100 px-3 py-2 font-mono text-xs">
                      {{ structureCells(line.destinationBudget).id || fallbackToId(line) || "—" }}
                    </td>
                    <td class="px-3 py-2">{{ structureCells(line.destinationBudget).ptj || "—" }}</td>
                    <td class="px-3 py-2">{{ structureCells(line.destinationBudget).cc || "—" }}</td>
                    <td class="px-3 py-2">{{ structureCells(line.destinationBudget).fund || "—" }}</td>
                    <td class="px-3 py-2">{{ structureCells(line.destinationBudget).act || "—" }}</td>
                    <td class="px-3 py-2">{{ structureCells(line.destinationBudget).code || "—" }}</td>
                    <td class="border-l border-slate-100 px-3 py-2">{{ line.qbuQuarterId ?? "—" }}</td>
                    <td class="border-l border-slate-100 px-3 py-2 text-right">{{ formatAmount(line.bmdMvtAmt) }}</td>
                  </template>
                  <template v-else>
                    <td class="px-3 py-2">{{ idx + 1 }}</td>
                    <td class="px-3 py-2 font-mono text-xs">
                      {{ structureCells(line.destinationBudget).id || fallbackToId(line) || "—" }}
                    </td>
                    <td class="px-3 py-2">{{ structureCells(line.destinationBudget).ptj || "—" }}</td>
                    <td class="px-3 py-2">{{ structureCells(line.destinationBudget).cc || "—" }}</td>
                    <td class="px-3 py-2">{{ structureCells(line.destinationBudget).fund || "—" }}</td>
                    <td class="px-3 py-2">{{ structureCells(line.destinationBudget).act || "—" }}</td>
                    <td class="px-3 py-2">{{ structureCells(line.destinationBudget).code || "—" }}</td>
                    <td class="px-3 py-2">{{ line.qbuQuarterId ?? "—" }}</td>
                    <td class="px-3 py-2 text-right">{{ formatAmount(line.bmdMvtAmt) }}</td>
                  </template>
                </tr>
                <tr v-if="payload.details.length === 0">
                  <td
                    class="px-3 py-6 text-center text-sm text-slate-400"
                    :colspan="type === 'virement' ? 15 : 9"
                  >
                    No detail lines.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </article>
    </div>
  </AdminLayout>
</template>
