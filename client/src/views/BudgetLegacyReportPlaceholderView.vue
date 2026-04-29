<script setup lang="ts">
/** Placeholder for legacy Malaysian budget report forms with no migrated BL yet. */
import { FileWarning } from "lucide-vue-next";
import AdminLayout from "@/layouts/AdminLayout.vue";

const props = defineProps<{
  title: string;
  /** Short paragraph for screen readers / clarity. */
  description?: string;
}>();
</script>

<template>
  <AdminLayout>
    <div class="space-y-4">
      <h1 class="page-title">Budget / {{ props.title }}</h1>
      <article class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col items-center justify-center px-4 py-14 text-center">
          <FileWarning class="h-11 w-11 text-amber-500/90" />
          <h2 class="mt-4 text-lg font-semibold text-slate-800">{{ props.title }}</h2>
          <p class="mt-2 max-w-lg text-sm text-slate-600">
            {{
              props.description ??
              "This legacy report screen depended on Kerisi Classic report builders and is not reproduced here. Use the classic app for full parameters and output."
            }}
          </p>
        </div>
      </article>
    </div>
  </AdminLayout>
</template>
