<script setup lang="ts">
/**
 * HIDDEN_PAGE_LEVEL5 MENU 1983 — PAGEID 1637 (accrual) + PAGEID 2182 (release queue tab).
 * Single menu route exposes both legacy screens as tabs.
 */
import { ref } from "vue";
import AdminLayout from "@/layouts/AdminLayout.vue";
import CcEmergencyFundAccrualListPanel from "@/views/CcEmergencyFundAccrualListPanel.vue";
import CcEmergencyFundReleaseQueueListPanel from "@/views/CcEmergencyFundReleaseQueueListPanel.vue";

type TabId = "accrual" | "release";
const tab = ref<TabId>("accrual");
</script>

<template>
  <AdminLayout>
    <div class="space-y-4">
      <h1 class="page-title">Credit Control / Advance / Emergency Fund / Accrual / Accrual</h1>
      <div class="flex flex-wrap gap-2 border-b border-slate-200 pb-2" role="tablist" aria-label="Emergency fund accrual sections">
        <button
          type="button"
          role="tab"
          :aria-selected="tab === 'accrual'"
          class="rounded-lg px-3 py-1.5 text-sm font-medium"
          :class="
            tab === 'accrual' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'
          "
          @click="tab = 'accrual'"
        >
          Accrual list
        </button>
        <button
          type="button"
          role="tab"
          :aria-selected="tab === 'release'"
          class="rounded-lg px-3 py-1.5 text-sm font-medium"
          :class="
            tab === 'release' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'
          "
          @click="tab = 'release'"
        >
          Release queue
        </button>
      </div>
      <CcEmergencyFundAccrualListPanel v-if="tab === 'accrual'" />
      <CcEmergencyFundReleaseQueueListPanel
        v-if="tab === 'release'"
        title-override="Release queue (PAGEID 2182)"
      />
    </div>
  </AdminLayout>
</template>
