<script setup lang="ts">
/**
 * Budget Advance Controlled / In Advance detail (PAGEID 1785 / MENUID 2161).
 * Read-only header from `budget_in_advance_master`. Line-level legacy grids are deferred.
 */
import { computed, onMounted, ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import { ArrowLeft } from "lucide-vue-next";
import AdminLayout from "@/layouts/AdminLayout.vue";
import { getBudgetInAdvanceMaster } from "@/api/cms";
import { useToast } from "@/composables/useToast";
import type { BudgetInAdvanceMasterShow } from "@/types";

const route = useRoute();
const router = useRouter();
const toast = useToast();

const bamId = computed(() => {
  const raw = route.query.id;
  const s = Array.isArray(raw) ? raw[0] : raw;
  const n = Number(s);
  return Number.isFinite(n) && n > 0 ? n : null;
});

const master = ref<BudgetInAdvanceMasterShow | null>(null);
const loading = ref(false);

const currency = new Intl.NumberFormat("en-MY", { minimumFractionDigits: 2, maximumFractionDigits: 2 });

function formatDateVal(v: unknown): string {
  if (v === null || v === undefined || v === "") return "—";
  const s = String(v);
  if (/^\d{4}-\d{2}-\d{2}/.test(s)) {
    const d = new Date(s.slice(0, 10));
    if (!Number.isNaN(d.getTime())) {
      const dd = String(d.getDate()).padStart(2, "0");
      const mm = String(d.getMonth() + 1).padStart(2, "0");
      return `${dd}/${mm}/${d.getFullYear()}`;
    }
  }
  return s;
}

function formatAmt(v: number | null | undefined): string {
  if (v == null) return "—";
  return currency.format(v);
}

async function load() {
  if (bamId.value == null) {
    master.value = null;
    return;
  }
  loading.value = true;
  try {
    const res = await getBudgetInAdvanceMaster(bamId.value);
    master.value = res.data;
  } catch (e) {
    master.value = null;
    toast.error("Load failed", e instanceof Error ? e.message : "Could not load record.");
  } finally {
    loading.value = false;
  }
}

function goBack() {
  if (window.history.length > 1) router.back();
  else void router.push({ path: "/admin/kerisi/m/2160" });
}

onMounted(() => {
  void load();
});

watch(bamId, () => {
  void load();
});
</script>

<template>
  <AdminLayout>
    <div class="space-y-4">
      <div class="flex flex-wrap items-center gap-3">
        <button
          type="button"
          class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm hover:bg-slate-50"
          @click="goBack"
        >
          <ArrowLeft class="h-4 w-4" />
          Back
        </button>
      </div>

      <h1 class="page-title">Budget / Budget Advance Controlled Details</h1>

      <article class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-4 py-3">
          <h2 class="text-base font-semibold text-slate-900">Budget Advance Controlled Details</h2>
        </div>

        <div v-if="bamId == null" class="p-6 text-sm text-slate-500">
          Open this screen from the advance lists with <code class="rounded bg-slate-100 px-1">?id=</code> (budget advance id).
        </div>
        <div v-else-if="loading" class="p-6 text-sm text-slate-500">Loading…</div>
        <div v-else-if="!master" class="p-6 text-sm text-slate-500">Record not found.</div>
        <div v-else class="space-y-4 p-4">
          <div class="grid gap-3 rounded-lg border border-slate-100 bg-slate-50/80 p-4 text-sm sm:grid-cols-2 lg:grid-cols-3">
            <div>
              <div class="text-xs font-medium uppercase text-slate-500">Reference no</div>
              <div class="font-medium text-slate-900">{{ master.bamNo ?? "—" }}</div>
            </div>
            <div>
              <div class="text-xs font-medium uppercase text-slate-500">Year</div>
              <div class="font-medium text-slate-900">{{ master.bamYear ?? "—" }}</div>
            </div>
            <div>
              <div class="text-xs font-medium uppercase text-slate-500">Date</div>
              <div class="font-medium text-slate-900">{{ formatDateVal(master.tarikh) }}</div>
            </div>
            <div>
              <div class="text-xs font-medium uppercase text-slate-500">Authority approval</div>
              <div class="font-medium text-slate-900">{{ master.bamEndorseDoc ?? "—" }}</div>
            </div>
            <div>
              <div class="text-xs font-medium uppercase text-slate-500">Amount (RM)</div>
              <div class="font-medium text-slate-900">{{ formatAmt(master.bamTotal) }}</div>
            </div>
            <div>
              <div class="text-xs font-medium uppercase text-slate-500">Status</div>
              <div class="font-medium text-slate-900">{{ master.bamStatus ?? "—" }}</div>
            </div>
            <div v-if="master.createdby || master.updatedby" class="sm:col-span-2 lg:col-span-3">
              <div class="text-xs font-medium uppercase text-slate-500">Audit</div>
              <div class="text-slate-700">
                <span v-if="master.createdby">Created by {{ master.createdby }}</span>
                <span v-if="master.updatedby">
                  <span v-if="master.createdby"> · </span>
                  Updated by {{ master.updatedby }}
                </span>
              </div>
            </div>
          </div>
          <p class="text-xs text-slate-500">
            Detail line grids and workflow actions from the legacy page are not ported in this batch.
          </p>
        </div>
      </article>
    </div>
  </AdminLayout>
</template>
