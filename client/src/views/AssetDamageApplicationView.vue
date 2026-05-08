<script setup lang="ts">
/**
 * Kerisi menu 3481 / PAGEID 2891 — Damage Asset Application (legacy YUS_DAMAGE_APPLICATION_JS workspace).
 * Kerisi 2.0 shell: violet parameter card, listing card, toolbar, table chrome (cf. Deposit / Dispose Method).
 */
import { computed, onMounted, onUnmounted, ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import {
  Copy,
  Hash,
  Link2,
  Loader2,
  MoreVertical,
  Pencil,
  Plus,
  RefreshCw,
  Save,
  Search,
  X,
} from "lucide-vue-next";

import AdminLayout from "@/layouts/AdminLayout.vue";
import { getAssetDamageApplication, listAssetDamageRegister } from "@/api/cms";
import { useToast } from "@/composables/useToast";
import type {
  AssetDamageApplicationHeader,
  AssetDamageApplicationLine,
  AssetDamageListingRow,
  AssetDamageProcessFlowStep,
} from "@/types";

const MENU_ID = 3481;
const PAGE_NAME = "Damage asset application";
const PAGE_BREADCRUMB = "Asset / Asset Maintenance / Damage Report / Damage Asset Application";

const toast = useToast();
const route = useRoute();
const router = useRouter();

const overflowOpen = ref(false);
const overflowRoot = ref<HTMLElement | null>(null);
const overflowListOpen = ref(false);
const overflowListRoot = ref<HTMLElement | null>(null);

const drmIdParam = computed(() => {
  const q = route.query.drm;
  const raw = Array.isArray(q) ? q[0] : q;
  const n = raw ? Number(raw) : NaN;
  return Number.isFinite(n) && n > 0 ? n : null;
});

const loading = ref(false);
const draftOptions = ref<AssetDamageListingRow[]>([]);
const loadingDrafts = ref(false);

const header = ref<AssetDamageApplicationHeader | null>(null);
const lines = ref<AssetDamageApplicationLine[]>([]);
const processFlow = ref<AssetDamageProcessFlowStep[]>([]);

const lineSearch = ref("");

const filteredLines = computed(() => {
  const q = lineSearch.value.trim().toLowerCase();
  if (!q) return lines.value;
  return lines.value.filter((r) =>
    [
      r.assetNo,
      r.assetDesc,
      r.category,
      r.subcategory,
      r.damageDetails,
      r.lastUser,
      r.ptj,
      r.accountCode,
      r.room,
      r.building,
    ]
      .join(" ")
      .toLowerCase()
      .includes(q),
  );
});

const totalLineCount = computed(() => lines.value.length);
const filteredCount = computed(() => filteredLines.value.length);

function onClickOutside(event: MouseEvent) {
  if (overflowOpen.value && !overflowRoot.value?.contains(event.target as Node)) {
    overflowOpen.value = false;
  }
  if (overflowListOpen.value && !overflowListRoot.value?.contains(event.target as Node)) {
    overflowListOpen.value = false;
  }
}

async function loadDraftOptions() {
  loadingDrafts.value = true;
  try {
    const res = await listAssetDamageRegister("?scope=applications&page=1&limit=100&sort_dir=desc");
    draftOptions.value = res.data;
    if (drmIdParam.value == null && res.data[0]) {
      await router.replace({ query: { ...route.query, drm: String(res.data[0].drmId) } });
    }
  } catch (e) {
    toast.error("Load failed", e instanceof Error ? e.message : "Unable to load draft list.");
    draftOptions.value = [];
  } finally {
    loadingDrafts.value = false;
  }
}

async function loadApplication() {
  const id = drmIdParam.value;
  if (id == null) {
    header.value = null;
    lines.value = [];
    processFlow.value = [];
    return;
  }
  loading.value = true;
  try {
    const res = await getAssetDamageApplication(id);
    header.value = res.data.header;
    lines.value = res.data.lines;
    processFlow.value = res.data.processFlow;
  } catch (e) {
    toast.error("Load failed", e instanceof Error ? e.message : "Unable to load application.");
    header.value = null;
    lines.value = [];
    processFlow.value = [];
  } finally {
    loading.value = false;
  }
}

async function refreshAll() {
  await loadDraftOptions();
  await loadApplication();
}

function copyPageSummary() {
  const linesOut = [PAGE_BREADCRUMB, typeof window !== "undefined" ? window.location.href : ""].filter(Boolean);
  void navigator.clipboard.writeText(linesOut.join("\n")).then(
    () => toast.success("Copied", "Breadcrumb and link copied."),
    () => toast.error("Copy failed", "Clipboard not available."),
  );
}

function onPickDraft(e: Event) {
  const v = (e.target as HTMLSelectElement).value;
  if (!v) return;
  void router.replace({ query: { ...route.query, drm: v } });
}

function copyReportNo() {
  const t = header.value?.drmReportNo ?? "";
  if (!t) {
    toast.info("Nothing to copy", "Report number is empty.");
    return;
  }
  void navigator.clipboard.writeText(t).then(
    () => toast.success("Copied"),
    () => toast.error("Copy failed", "Clipboard not available."),
  );
}

function onSave() {
  toast.info("Not implemented", "Save will call the legacy BL once the POST contract is wired.");
}

function onSaveSubmit() {
  toast.info("Not implemented", "Save & submit remains on Kerisi 1.0 until workflow actions are ported.");
}

function onNewLine() {
  toast.info("Not implemented", "Add asset row uses Kerisi 1.0 or a future POST /detail endpoint.");
}

watch(drmIdParam, () => void loadApplication());

onMounted(async () => {
  document.addEventListener("click", onClickOutside);
  await loadDraftOptions();
  await loadApplication();
});

onUnmounted(() => {
  document.removeEventListener("click", onClickOutside);
});
</script>

<template>
  <AdminLayout>
    <div class="space-y-4">
      <h1 class="page-title">{{ PAGE_BREADCRUMB }}</h1>

      <!-- Kerisi 2.0 — Search / open application -->
      <article
        class="overflow-hidden rounded-xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-violet-100/40"
      >
        <div
          class="flex items-center justify-between border-b border-slate-100 bg-gradient-to-r from-violet-50/60 to-white px-4 py-2.5"
        >
          <h2 class="text-base font-semibold text-slate-900">Search Parameter</h2>
          <div class="flex items-center gap-0.5 text-slate-500">
            <button
              type="button"
              class="inline-flex rounded-lg p-1.5 hover:bg-white hover:text-violet-700"
              :title="`Kerisi menu ${MENU_ID}`"
              @click="toast.info('Menu', `Kerisi menu ${MENU_ID} · ${PAGE_NAME}`)"
            >
              <Hash class="h-4 w-4" />
            </button>
            <button
              type="button"
              class="inline-flex rounded-lg p-1.5 hover:bg-white hover:text-violet-700"
              title="Copy breadcrumb and URL"
              @click.stop="copyPageSummary"
            >
              <Copy class="h-4 w-4" />
            </button>
            <span class="inline-flex rounded-lg p-1.5 opacity-50" title="Classic edit (Kerisi 1.0)"
              ><Pencil class="h-4 w-4"
            /></span>
          </div>
        </div>
        <div class="space-y-4 p-4">
          <div class="grid gap-3 md:grid-cols-1 lg:grid-cols-2">
            <div>
              <label class="mb-1 block text-xs font-medium text-slate-600">Open application (DRM)</label>
              <select
                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm"
                :value="drmIdParam ?? ''"
                :disabled="loadingDrafts"
                @change="onPickDraft"
              >
                <option value="">— Select draft / application —</option>
                <option v-for="d in draftOptions" :key="d.drmId" :value="String(d.drmId)">
                  #{{ d.drmId }}
                  {{ d.drmReportNo ? ` — ${d.drmReportNo}` : "" }}
                  {{ d.drmDescription ? ` — ${d.drmDescription.slice(0, 48)}` : "" }}
                </option>
              </select>
            </div>
          </div>
          <p class="text-xs leading-relaxed text-slate-600">
            Select a draft application, then use
            <strong class="font-medium text-slate-800">Reload</strong>
            to refresh the draft list and
            <strong class="font-medium text-slate-800">Load</strong>
            to re-fetch the current DRM from the server.
          </p>
          <div class="flex flex-wrap items-center justify-end gap-2">
            <button
              type="button"
              class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 disabled:opacity-50"
              :disabled="loadingDrafts || loading"
              @click="refreshAll"
            >
              <RefreshCw class="h-4 w-4" :class="{ 'animate-spin': loadingDrafts || loading }" />
              Reload
            </button>
            <button
              type="button"
              class="inline-flex items-center gap-2 rounded-lg bg-violet-600 px-5 py-2 text-sm font-medium text-white shadow-sm shadow-violet-500/25 hover:bg-violet-700 disabled:opacity-50"
              :disabled="drmIdParam == null || loading"
              @click="loadApplication"
            >
              <Search class="h-4 w-4" />
              Load
            </button>
          </div>
          <div v-if="loadingDrafts" class="flex items-center gap-2 text-xs text-slate-500">
            <Loader2 class="h-3.5 w-3.5 animate-spin" />
            Loading draft list…
          </div>
        </div>
      </article>

      <template v-if="drmIdParam && !loading && header">
        <!-- Damage Application Information -->
        <article
          class="overflow-hidden rounded-xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-violet-100/30"
        >
          <div
            class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 bg-gradient-to-r from-violet-50/60 to-white px-4 py-2.5"
          >
            <h2 class="text-base font-semibold text-slate-900">Damage Application Information</h2>
            <div class="flex items-center gap-0.5 text-slate-500">
              <button
                type="button"
                class="inline-flex rounded-lg p-1.5 hover:bg-white hover:text-violet-700"
                title="Internal id"
                @click="toast.info('DRM id', String(header.drmId))"
              >
                <Hash class="h-4 w-4" />
              </button>
              <button
                type="button"
                class="inline-flex rounded-lg p-1.5 hover:bg-white hover:text-violet-700"
                title="Copy report no."
                @click="copyReportNo"
              >
                <Copy class="h-4 w-4" />
              </button>
              <button
                type="button"
                class="inline-flex rounded-lg p-1.5 hover:bg-white hover:text-violet-700"
                title="Edit in Kerisi 1.0"
                @click="toast.info('Edit', 'Full editing remains on Kerisi 1.0 for now.')"
              >
                <Pencil class="h-4 w-4" />
              </button>
            </div>
          </div>
          <div class="grid gap-4 p-4 sm:grid-cols-2 lg:grid-cols-3">
            <label class="block text-sm">
              <span class="text-xs font-medium text-slate-600">Report No</span>
              <input
                :value="header.drmReportNo"
                type="text"
                readonly
                class="mt-1 w-full rounded-lg border border-slate-200 bg-slate-50/80 px-3 py-2 text-slate-800 shadow-inner"
              />
            </label>
            <label class="block text-sm sm:col-span-2">
              <span class="text-xs font-medium text-slate-600">Description</span>
              <input
                :value="header.drmDescription"
                type="text"
                readonly
                class="mt-1 w-full rounded-lg border border-slate-200 bg-slate-50/80 px-3 py-2 text-slate-800 shadow-inner"
              />
            </label>
            <div class="block text-sm">
              <span class="text-xs font-medium text-slate-600">Status</span>
              <div class="mt-1 flex items-center gap-2">
                <span
                  class="inline-flex rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-wide"
                  :class="
                    String(header.drmStatus).toUpperCase().includes('DRAFT')
                      ? 'bg-violet-100 text-violet-800 ring-1 ring-violet-200/80'
                      : 'bg-slate-100 text-slate-800 ring-1 ring-slate-200'
                  "
                >
                  {{ header.drmStatus }}
                </span>
              </div>
            </div>
          </div>
        </article>

        <!-- View List -->
        <article class="overflow-hidden rounded-xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-100">
          <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 px-4 py-3">
            <h2 class="text-base font-semibold text-slate-900">View List</h2>
            <div ref="overflowListRoot" class="relative flex items-center gap-1">
              <Link2 class="h-4 w-4 text-slate-400" aria-hidden="true" />
              <button
                type="button"
                class="rounded-lg p-2 text-slate-500 hover:bg-slate-100"
                aria-label="More"
                @click.stop="overflowListOpen = !overflowListOpen"
              >
                <MoreVertical class="h-4 w-4" />
              </button>
              <div
                v-if="overflowListOpen"
                class="absolute right-0 top-full z-30 mt-1 w-48 rounded-lg border border-slate-200 bg-white py-1 shadow-lg"
                @click.stop
              >
                <button
                  type="button"
                  class="block w-full px-3 py-2 text-left text-xs text-slate-700 hover:bg-slate-50"
                  @click="
                    overflowListOpen = false;
                    loadApplication();
                  "
                >
                  Refresh grid
                </button>
                <button
                  type="button"
                  class="block w-full px-3 py-2 text-left text-xs text-slate-700 hover:bg-slate-50"
                  @click="
                    overflowListOpen = false;
                    lineSearch = '';
                  "
                >
                  Clear search
                </button>
              </div>
            </div>
          </div>
          <div class="space-y-4 p-4">
            <div class="flex flex-wrap items-end justify-between gap-3">
              <p class="text-xs text-slate-600">
                <span class="font-medium text-slate-800">{{ filteredCount }}</span>
                of
                <span class="font-medium text-slate-800">{{ totalLineCount }}</span>
                rows
                <span v-if="lineSearch.trim()" class="text-slate-500"> (filtered)</span>
              </p>
            <div class="w-full min-w-[12rem] max-w-md sm:ml-auto">
              <label class="mb-1 block text-xs font-medium text-slate-600">Search</label>
              <div class="relative">
                <Search class="pointer-events-none absolute left-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400" />
                <input
                  v-model="lineSearch"
                  type="search"
                  placeholder="Filter rows…"
                  class="h-9 w-full rounded-lg border border-slate-300 py-1.5 pl-8 pr-8 text-sm shadow-sm"
                />
                <button
                  v-if="lineSearch"
                  type="button"
                  class="absolute right-1 top-1/2 -translate-y-1/2 rounded p-0.5 text-slate-400 hover:bg-slate-100"
                  aria-label="Clear"
                  @click="lineSearch = ''"
                >
                  <X class="h-3.5 w-3.5" />
                </button>
              </div>
            </div>
            </div>

            <div class="overflow-x-auto rounded-lg border border-slate-200">
              <div :class="filteredLines.length > 10 ? 'max-h-[min(28rem,70vh)] overflow-y-auto' : ''">
                <table class="w-full min-w-[2200px] text-xs">
                  <thead class="sticky top-0 z-10 bg-violet-600 text-white shadow-sm">
                    <tr>
                      <th class="whitespace-nowrap px-3 py-2.5 text-left font-semibold uppercase tracking-wide">No</th>
                      <th class="whitespace-nowrap px-3 py-2.5 text-left font-semibold uppercase tracking-wide">Asset No</th>
                      <th class="whitespace-nowrap px-3 py-2.5 text-left font-semibold uppercase tracking-wide">Asset Description</th>
                      <th class="whitespace-nowrap px-3 py-2.5 text-left font-semibold uppercase tracking-wide">Asset Category</th>
                      <th class="whitespace-nowrap px-3 py-2.5 text-left font-semibold uppercase tracking-wide">Asset SubCategory</th>
                      <th class="whitespace-nowrap px-3 py-2.5 text-left font-semibold uppercase tracking-wide">Brand / Model</th>
                      <th class="whitespace-nowrap px-3 py-2.5 text-left font-semibold uppercase tracking-wide">Serial / Chasis No</th>
                      <th class="whitespace-nowrap px-3 py-2.5 text-left font-semibold uppercase tracking-wide">Plate No</th>
                      <th class="whitespace-nowrap px-3 py-2.5 text-left font-semibold uppercase tracking-wide">PTJ</th>
                      <th class="whitespace-nowrap px-3 py-2.5 text-left font-semibold uppercase tracking-wide">Fund Type</th>
                      <th class="whitespace-nowrap px-3 py-2.5 text-left font-semibold uppercase tracking-wide">Account Code</th>
                      <th class="whitespace-nowrap px-3 py-2.5 text-left font-semibold uppercase tracking-wide">Building</th>
                      <th class="whitespace-nowrap px-3 py-2.5 text-left font-semibold uppercase tracking-wide">Room</th>
                      <th class="whitespace-nowrap px-3 py-2.5 text-left font-semibold uppercase tracking-wide">Last User</th>
                      <th class="whitespace-nowrap px-3 py-2.5 text-left font-semibold uppercase tracking-wide">Damage Details</th>
                      <th class="whitespace-nowrap px-3 py-2.5 text-left font-semibold uppercase tracking-wide">Damage Date</th>
                      <th class="whitespace-nowrap px-3 py-2.5 text-left font-semibold uppercase tracking-wide">Recommendation</th>
                      <th class="whitespace-nowrap px-3 py-2.5 text-left font-semibold uppercase tracking-wide">Estimated Cost</th>
                      <th class="whitespace-nowrap px-3 py-2.5 text-left font-semibold uppercase tracking-wide">Install Cost</th>
                      <th class="whitespace-nowrap px-3 py-2.5 text-left font-semibold uppercase tracking-wide">Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-if="filteredLines.length === 0">
                      <td colspan="20" class="px-3 py-8 text-center text-sm text-slate-500">No records</td>
                    </tr>
                    <tr
                      v-for="row in filteredLines"
                      :key="row.drdReportDetailsId"
                      class="border-b border-slate-100 hover:bg-violet-50/40"
                    >
                      <td class="whitespace-nowrap px-3 py-2 tabular-nums text-slate-700">{{ row.index }}</td>
                      <td class="whitespace-nowrap px-3 py-2 font-medium text-slate-900">{{ row.assetNo || "—" }}</td>
                      <td class="max-w-[14rem] px-3 py-2 text-slate-700">{{ row.assetDesc || "—" }}</td>
                      <td class="px-3 py-2 text-slate-700">{{ row.category || "—" }}</td>
                      <td class="px-3 py-2 text-slate-700">{{ row.subcategory || "—" }}</td>
                      <td class="px-3 py-2 text-slate-700">{{ row.brandModel || "—" }}</td>
                      <td class="px-3 py-2 text-slate-700">{{ row.serialChasis || "—" }}</td>
                      <td class="px-3 py-2 text-slate-700">{{ row.plateNo || "—" }}</td>
                      <td class="px-3 py-2 text-slate-700">{{ row.ptj || "—" }}</td>
                      <td class="px-3 py-2 text-slate-700">{{ row.fundType || "—" }}</td>
                      <td class="px-3 py-2 text-slate-700">{{ row.accountCode || "—" }}</td>
                      <td class="px-3 py-2 text-slate-700">{{ row.building || "—" }}</td>
                      <td class="px-3 py-2 text-slate-700">{{ row.room || "—" }}</td>
                      <td class="px-3 py-2 text-slate-700">{{ row.lastUser || "—" }}</td>
                      <td class="max-w-[12rem] px-3 py-2 text-slate-700">{{ row.damageDetails || "—" }}</td>
                      <td class="whitespace-nowrap px-3 py-2 text-slate-700">{{ row.damageDate || "—" }}</td>
                      <td class="max-w-[12rem] px-3 py-2 text-slate-700">{{ row.recommendation || "—" }}</td>
                      <td class="whitespace-nowrap px-3 py-2 text-right tabular-nums text-slate-700">{{ row.estimatedCost || "—" }}</td>
                      <td class="whitespace-nowrap px-3 py-2 text-right tabular-nums text-slate-700">{{ row.installCost || "—" }}</td>
                      <td class="px-3 py-2 text-slate-400">—</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
            <div class="flex justify-end border-t border-slate-100 pt-3">
              <button
                type="button"
                class="inline-flex items-center gap-1.5 rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-slate-800"
                @click="onNewLine"
              >
                <Plus class="h-4 w-4" />
                New
              </button>
            </div>
          </div>
        </article>

        <!-- Process Flow -->
        <article
          class="overflow-hidden rounded-xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-violet-100/30"
        >
          <div
            class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 bg-gradient-to-r from-violet-50/60 to-white px-4 py-2.5"
          >
            <h2 class="text-base font-semibold text-slate-900">Process Flow</h2>
            <div ref="overflowRoot" class="relative flex items-center gap-1">
              <Link2 class="h-4 w-4 text-slate-400" aria-hidden="true" />
              <button
                type="button"
                class="rounded-lg p-2 text-slate-500 hover:bg-slate-100"
                @click.stop="overflowOpen = !overflowOpen"
              >
                <MoreVertical class="h-4 w-4" />
              </button>
              <div
                v-if="overflowOpen"
                class="absolute right-0 top-full z-30 mt-1 w-44 rounded-lg border border-slate-200 bg-white py-1 shadow-lg"
                @click.stop
              >
                <button
                  type="button"
                  class="block w-full px-3 py-2 text-left text-xs text-slate-700 hover:bg-slate-50"
                  @click="
                    overflowOpen = false;
                    loadApplication();
                  "
                >
                  Refresh workflow
                </button>
              </div>
            </div>
          </div>
          <div class="p-4">
            <div
              v-if="processFlow.length === 0"
              class="rounded-lg border border-dashed border-slate-200 bg-slate-50/80 px-4 py-10 text-center text-sm text-slate-500"
            >
              No workflow steps returned for
              <span class="rounded bg-slate-200/80 px-1.5 py-0.5 font-mono text-xs text-slate-800">ASSET_DAMAGE_REPORT</span>.
            </div>
            <ol v-else class="space-y-3">
              <li
                v-for="(step, si) in processFlow"
                :key="si + '-' + step.processName"
                class="rounded-lg border border-slate-200/90 bg-gradient-to-r from-white to-slate-50/90 pl-4 pr-4 py-3 shadow-sm ring-1 ring-slate-100/80"
              >
                <div class="border-l-4 border-violet-500 pl-3">
                  <div class="flex flex-wrap items-start justify-between gap-2">
                    <div>
                      <p class="font-semibold text-slate-900">{{ step.processName }}</p>
                      <p v-if="step.statusDesc" class="mt-0.5 text-xs font-semibold uppercase tracking-wide text-violet-700">
                        {{ step.statusDesc }}
                      </p>
                    </div>
                    <div v-if="step.createdDate" class="text-right text-xs text-slate-500">
                      {{ step.createdDate }}
                      <span v-if="step.createdTime" class="block text-slate-400">{{ step.createdTime }}</span>
                    </div>
                  </div>
                  <div class="mt-2 grid gap-x-4 gap-y-1 text-xs text-slate-600 sm:grid-cols-2">
                    <p v-if="step.createdByName"><span class="font-medium text-slate-700">By:</span> {{ step.createdByName }}</p>
                    <p v-if="step.ounDesc"><span class="font-medium text-slate-700">PTJ / unit:</span> {{ step.ounDesc }}</p>
                    <p v-if="step.emailAddr"><span class="font-medium text-slate-700">Email:</span> {{ step.emailAddr }}</p>
                    <p v-if="step.telNoWork"><span class="font-medium text-slate-700">Tel:</span> {{ step.telNoWork }}</p>
                    <p v-if="step.remark" class="sm:col-span-2">
                      <span class="font-medium text-slate-700">Remark:</span>
                      {{ step.remark }}
                    </p>
                  </div>
                </div>
              </li>
            </ol>
          </div>
        </article>

        <div
          class="flex flex-wrap items-center justify-center gap-3 rounded-xl border border-slate-200/90 bg-white px-4 py-5 shadow-sm ring-1 ring-slate-100"
        >
          <button
            type="button"
            class="inline-flex items-center gap-2 rounded-lg border border-violet-200 bg-violet-50/80 px-6 py-2.5 text-sm font-medium text-violet-900 shadow-sm hover:bg-violet-100"
            @click="onSave"
          >
            <Save class="h-4 w-4" />
            Save
          </button>
          <button
            type="button"
            class="inline-flex items-center gap-2 rounded-lg bg-violet-600 px-6 py-2.5 text-sm font-medium text-white shadow-sm shadow-violet-500/25 hover:bg-violet-700"
            @click="onSaveSubmit"
          >
            <Save class="h-4 w-4" />
            Save &amp; Submit
          </button>
        </div>
      </template>

      <div
        v-else-if="loading"
        class="flex items-center justify-center gap-2 rounded-xl border border-slate-200/90 bg-white py-16 text-slate-600 shadow-sm ring-1 ring-slate-100"
      >
        <Loader2 class="h-5 w-5 animate-spin text-violet-600" />
        Loading…
      </div>

      <div
        v-else
        class="rounded-xl border border-dashed border-slate-300/90 bg-slate-50/50 px-4 py-10 text-center text-sm text-slate-600 ring-1 ring-slate-100/80"
      >
        <p class="font-medium text-slate-800">No application selected</p>
        <p class="mt-1">
          Choose a record above or open this page with
          <code class="rounded bg-white px-1.5 py-0.5 font-mono text-xs text-violet-800 ring-1 ring-violet-100">?drm=…</code>
          in the URL.
        </p>
      </div>
    </div>
  </AdminLayout>
</template>
