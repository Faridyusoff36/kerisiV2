<script setup lang="ts">
/**
 * Account Payable / Payee Registration Details (PAGEID 1404 / MENUID 1713).
 * Read-only header from the same scope as Payee Registration (Others).
 */
import { computed, onMounted, ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import { ArrowLeft } from "lucide-vue-next";
import AdminLayout from "@/layouts/AdminLayout.vue";
import { getPayeeRegistrationDetail } from "@/api/cms";
import { useToast } from "@/composables/useToast";
import type { PayeeRegistrationDetail } from "@/types";

const route = useRoute();
const router = useRouter();
const toast = useToast();

const vcsId = computed(() => {
  const raw = route.query.id ?? route.query.vcsId;
  const s = Array.isArray(raw) ? raw[0] : raw;
  return s != null && String(s).trim() !== "" ? String(s).trim() : null;
});

const detail = ref<PayeeRegistrationDetail | null>(null);
const loading = ref(false);

async function load() {
  if (vcsId.value == null) {
    detail.value = null;
    return;
  }
  loading.value = true;
  try {
    const res = await getPayeeRegistrationDetail(vcsId.value);
    detail.value = res.data;
  } catch (e) {
    detail.value = null;
    toast.error("Load failed", e instanceof Error ? e.message : "Could not load payee.");
  } finally {
    loading.value = false;
  }
}

function goBack() {
  if (window.history.length > 1) router.back();
  else void router.push({ path: "/admin/kerisi/m/1711" });
}

onMounted(() => {
  void load();
});
watch(vcsId, () => {
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

      <h1 class="page-title">Account Payable / Payee Registration Details</h1>

      <article class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-4 py-3">
          <h2 class="text-base font-semibold text-slate-900">Payee Registration Details (Others)</h2>
          <p class="mt-1 text-xs text-slate-500">Read-only. Editing remains in Kerisi Classic.</p>
        </div>

        <div v-if="vcsId == null" class="p-6 text-sm text-slate-500">
          Open from <strong>Payee Registration</strong> using <strong>View</strong>, or append
          <code class="rounded bg-slate-100 px-1">?id=</code> (payee id).
        </div>
        <div v-else-if="loading" class="p-6 text-sm text-slate-500">Loading…</div>
        <div v-else-if="!detail" class="p-6 text-sm text-slate-500">Payee not found.</div>
        <div v-else class="grid gap-4 p-4 text-sm sm:grid-cols-2">
          <div class="sm:col-span-2">
            <div class="text-xs font-medium uppercase text-slate-500">Payee code</div>
            <div class="font-mono font-medium text-slate-900">{{ detail.vcsVendorCode ?? "—" }}</div>
          </div>
          <div class="sm:col-span-2">
            <div class="text-xs font-medium uppercase text-slate-500">Payee name</div>
            <div class="text-slate-900">{{ detail.vcsVendorName ?? "—" }}</div>
          </div>
          <div>
            <div class="text-xs font-medium uppercase text-slate-500">Address line 1</div>
            <div>{{ detail.vcsAddr1 ?? "—" }}</div>
          </div>
          <div>
            <div class="text-xs font-medium uppercase text-slate-500">Address line 2</div>
            <div>{{ detail.vcsAddr2 ?? "—" }}</div>
          </div>
          <div>
            <div class="text-xs font-medium uppercase text-slate-500">Address line 3</div>
            <div>{{ detail.vcsAddr3 ?? "—" }}</div>
          </div>
          <div>
            <div class="text-xs font-medium uppercase text-slate-500">Town</div>
            <div>{{ detail.vcsTown ?? "—" }}</div>
          </div>
          <div>
            <div class="text-xs font-medium uppercase text-slate-500">State</div>
            <div>{{ detail.state ?? "—" }}</div>
          </div>
          <div>
            <div class="text-xs font-medium uppercase text-slate-500">Bank</div>
            <div>{{ detail.vendorBank ?? "—" }}</div>
          </div>
          <div>
            <div class="text-xs font-medium uppercase text-slate-500">Bank account no</div>
            <div class="font-mono">{{ detail.vcsBankAccno ?? "—" }}</div>
          </div>
          <div>
            <div class="text-xs font-medium uppercase text-slate-500">JomPAY biller code</div>
            <div>{{ detail.vcsBillerCode ?? "—" }}</div>
          </div>
          <div>
            <div class="text-xs font-medium uppercase text-slate-500">Phone</div>
            <div>{{ detail.vcsTelNo ?? "—" }}</div>
          </div>
          <div>
            <div class="text-xs font-medium uppercase text-slate-500">Email</div>
            <div>{{ detail.vcsEmailAddress ?? "—" }}</div>
          </div>
          <div>
            <div class="text-xs font-medium uppercase text-slate-500">Contact person</div>
            <div>{{ detail.vcsContactPerson ?? "—" }}</div>
          </div>
          <div>
            <div class="text-xs font-medium uppercase text-slate-500">IC no</div>
            <div>{{ detail.vcsIcNo ?? "—" }}</div>
          </div>
          <div>
            <div class="text-xs font-medium uppercase text-slate-500">SSM no</div>
            <div>{{ detail.vcsRegistrationNo ?? "—" }}</div>
          </div>
          <div>
            <div class="text-xs font-medium uppercase text-slate-500">Status</div>
            <span
              class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium"
              :class="detail.vcsVendorStatus === 'ACTIVE' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-600'"
            >
              {{ detail.vcsVendorStatus }}
            </span>
          </div>
        </div>
      </article>
    </div>
  </AdminLayout>
</template>
