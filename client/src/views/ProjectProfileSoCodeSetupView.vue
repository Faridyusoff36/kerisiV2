<script setup lang="ts">
/**
 * Setup & Maintenance → GL Structure → Project Profile So Code
 * (PAGEID 1327 / MENUID 1615).
 *
 * Edits existing `capital_project` rows via GET/PATCH `/api/project-monitoring/projects/{cpaProjectNo}`.
 */
import { computed, onMounted, ref, watch } from "vue";
import { useRoute } from "vue-router";
import AdminLayout from "@/layouts/AdminLayout.vue";
import {
  getProjectMonitoringProject,
  patchProjectMonitoringProject,
} from "@/api/cms";
import { useToast } from "@/composables/useToast";
import type { CapitalProjectProfilePatch, ProjectListRow } from "@/types";

const route = useRoute();
const toast = useToast();

const projectNoLookup = ref("");
const loadingRow = ref(false);
const saving = ref(false);

const cpaProjectNo = ref("");
const desc = ref("");
const ftyFundType = ref("");
const latActivityCode = ref("");
const ounCode = ref("");
const ccrCostcentre = ref("");
const soCode = ref("");
const cpaProjectType = ref("");
const cpaSource = ref("");
const cpaProjectStatus = ref("");
const cpaStartDate = ref("");
const cpaEndDate = ref("");

const hasLoaded = computed(() => cpaProjectNo.value !== "");

function isoToDdMmYy(iso: string | null | undefined): string {
  if (!iso) return "";
  const d = new Date(iso);
  if (Number.isNaN(d.getTime())) return "";
  const dd = String(d.getDate()).padStart(2, "0");
  const mm = String(d.getMonth() + 1).padStart(2, "0");
  return `${dd}/${mm}/${d.getFullYear()}`;
}

function applyRow(row: ProjectListRow): void {
  cpaProjectNo.value = row.cpaProjectNo ?? "";
  desc.value = row.cpaProjectDesc ?? "";
  ftyFundType.value = row.ftyFundType ?? "";
  latActivityCode.value = row.latActivityCode ?? "";
  ounCode.value = row.ounCode ?? "";
  ccrCostcentre.value = row.ccrCostcentre ?? "";
  soCode.value = row.soCode ?? "";
  cpaProjectType.value = row.cpaProjectType ?? "";
  cpaSource.value = row.cpaSource ?? "";
  cpaProjectStatus.value = row.cpaProjectStatus ?? "";
  cpaStartDate.value = isoToDdMmYy(row.cpaStartDate);
  cpaEndDate.value = isoToDdMmYy(row.cpaEndDate);
}

async function loadByNo(no: string) {
  const n = no.trim();
  if (!n) {
    toast.info("Enter a project number", "Type or paste the project ID first.");
    return;
  }
  loadingRow.value = true;
  try {
    const res = await getProjectMonitoringProject(n);
    applyRow(res.data);
    toast.success("Loaded");
  } catch (e) {
    toast.error("Load failed", e instanceof Error ? e.message : "Unable to load project.");
  } finally {
    loadingRow.value = false;
  }
}

async function save() {
  if (!cpaProjectNo.value) {
    toast.info("Nothing to save", "Load a project first.");
    return;
  }
  saving.value = true;
  try {
    const body: CapitalProjectProfilePatch = {
      cpaProjectDesc: desc.value || undefined,
      ftyFundType: ftyFundType.value || undefined,
      latActivityCode: latActivityCode.value || undefined,
      ounCode: ounCode.value || undefined,
      ccrCostcentre: ccrCostcentre.value || undefined,
      soCode: soCode.value || undefined,
      cpaProjectType: cpaProjectType.value || undefined,
      cpaSource: cpaSource.value || undefined,
      cpaProjectStatus: cpaProjectStatus.value || undefined,
      cpaStartDate: cpaStartDate.value.trim() || undefined,
      cpaEndDate: cpaEndDate.value.trim() || undefined,
    };
    const res = await patchProjectMonitoringProject(cpaProjectNo.value, body);
    applyRow(res.data);
    toast.success("Saved");
  } catch (e) {
    toast.error("Save failed", e instanceof Error ? e.message : "Unable to save.");
  } finally {
    saving.value = false;
  }
}

onMounted(async () => {
  const q = route.query.cpa_project_no ?? route.query.cpaProjectNo;
  const qs = typeof q === "string" ? q.trim() : "";
  if (qs) {
    projectNoLookup.value = qs;
    await loadByNo(qs);
  }
});

watch(
  () => route.query.cpa_project_no ?? route.query.cpaProjectNo,
  async (nv) => {
    const qs = typeof nv === "string" ? nv.trim() : "";
    if (qs && qs !== projectNoLookup.value) {
      projectNoLookup.value = qs;
      await loadByNo(qs);
    }
  },
);
</script>

<template>
  <AdminLayout>
    <div class="space-y-4">
      <h1 class="page-title">Setup and Maintenance / General Ledger Structure / Project Profile So Code</h1>

      <article class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
          <h2 class="text-base font-semibold text-slate-900">Capital project profile</h2>
        </div>

        <div class="space-y-4 p-4">
          <p class="text-sm text-slate-600">
            Load an existing capital project record, edit its classification (fund, activity, PTJ, cost centre, SO code, etc.), date range,
            description, source, status, then save back to `capital_project`.
          </p>

          <div class="flex flex-wrap items-end gap-3">
            <div class="min-w-[240px] flex-1">
              <label class="mb-1 block text-xs font-medium text-slate-600">Project No</label>
              <input
                v-model="projectNoLookup"
                type="text"
                autocomplete="off"
                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
                placeholder="e.g. PCA/2025/001"
                @keyup.enter="() => loadByNo(projectNoLookup)"
              />
            </div>
            <button
              type="button"
              class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white disabled:opacity-50"
              :disabled="loadingRow"
              @click="loadByNo(projectNoLookup)"
            >
              {{ loadingRow ? "Loading…" : "Load" }}
            </button>
          </div>

          <template v-if="hasLoaded">
            <fieldset class="space-y-3 rounded-lg border border-slate-100 bg-slate-50 p-4" :disabled="saving">
              <legend class="px-1 text-xs font-semibold uppercase text-slate-500">Editable fields</legend>
              <div class="grid gap-3 md:grid-cols-2">
                <div>
                  <label class="mb-1 block text-xs text-slate-600">Project No (readonly)</label>
                  <input v-model="cpaProjectNo" type="text" readonly class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-600" />
                </div>
                <div>
                  <label class="mb-1 block text-xs text-slate-600">Description</label>
                  <input v-model="desc" type="text" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                </div>
                <div>
                  <label class="mb-1 block text-xs text-slate-600">Fund Type</label>
                  <input v-model="ftyFundType" type="text" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                </div>
                <div>
                  <label class="mb-1 block text-xs text-slate-600">Activity Code</label>
                  <input v-model="latActivityCode" type="text" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                </div>
                <div>
                  <label class="mb-1 block text-xs text-slate-600">PTJ</label>
                  <input v-model="ounCode" type="text" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                </div>
                <div>
                  <label class="mb-1 block text-xs text-slate-600">Cost Centre</label>
                  <input v-model="ccrCostcentre" type="text" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                </div>
                <div>
                  <label class="mb-1 block text-xs text-slate-600">SO Code</label>
                  <input v-model="soCode" type="text" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                </div>
                <div>
                  <label class="mb-1 block text-xs text-slate-600">Project Type</label>
                  <input v-model="cpaProjectType" type="text" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                </div>
                <div>
                  <label class="mb-1 block text-xs text-slate-600">Start Date</label>
                  <input v-model="cpaStartDate" type="text" placeholder="DD/MM/YYYY" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                </div>
                <div>
                  <label class="mb-1 block text-xs text-slate-600">End Date</label>
                  <input v-model="cpaEndDate" type="text" placeholder="DD/MM/YYYY" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                </div>
                <div>
                  <label class="mb-1 block text-xs text-slate-600">Source</label>
                  <input v-model="cpaSource" type="text" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                </div>
                <div>
                  <label class="mb-1 block text-xs text-slate-600">Project Status</label>
                  <input v-model="cpaProjectStatus" type="text" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                </div>
              </div>
            </fieldset>

            <div class="flex justify-end gap-2">
              <button
                type="button"
                class="rounded-lg bg-slate-900 px-5 py-2 text-sm font-medium text-white disabled:opacity-50"
                :disabled="saving"
                @click="save"
              >
                {{ saving ? "Saving…" : "Save" }}
              </button>
            </div>
          </template>

          <p v-else class="text-sm text-slate-500">Enter a project number and choose Load.</p>
        </div>
      </article>
    </div>
  </AdminLayout>
</template>
