<script setup lang="ts">
/** General Ledger legacy screens not yet ported (upload, error queues, PTJ reports, etc.). */
import { computed } from "vue";
import { useRouter } from "vue-router";
import { FileWarning } from "lucide-vue-next";
import AdminLayout from "@/layouts/AdminLayout.vue";

const props = defineProps<{
  title: string;
  breadcrumb?: string;
  description?: string;
  relatedPath?: string;
  relatedLabel?: string;
}>();

const router = useRouter();
const crumb = computed(() => props.breadcrumb ?? `General Ledger / ${props.title}`);

function goRelated() {
  if (props.relatedPath) void router.push(props.relatedPath);
}
</script>

<template>
  <AdminLayout>
    <div class="space-y-4">
      <h1 class="page-title">{{ crumb }}</h1>
      <article class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col items-center justify-center px-4 py-14 text-center">
          <FileWarning class="h-11 w-11 text-amber-500/90" />
          <h2 class="mt-4 text-lg font-semibold text-slate-800">{{ title }}</h2>
          <p class="mt-2 max-w-lg text-sm text-slate-600">
            {{
              description ??
              "This legacy General Ledger screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow."
            }}
          </p>
          <button
            v-if="relatedPath && relatedLabel"
            type="button"
            class="mt-6 rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800"
            @click="goRelated"
          >
            {{ relatedLabel }}
          </button>
        </div>
      </article>
    </div>
  </AdminLayout>
</template>
