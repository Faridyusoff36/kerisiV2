<script setup lang="ts">
/**
 * Standard admin page header. Use at the top of every admin view so the
 * breadcrumb + title + action cluster looks identical everywhere (matches
 * the kitchen-sink reference).
 *
 * Usage:
 *   <PageHeader breadcrumb="Cashbook / Bank Master" title="Bank Master">
 *     <template #subtitle>Short description (optional)</template>
 *     <template #actions>
 *       <button>Export</button>
 *     </template>
 *   </PageHeader>
 *
 * The `breadcrumb` prop accepts either a slash-separated string (legacy
 * "Module / Page" form) or an explicit array of crumbs.
 */
import { computed, useSlots } from "vue";
import { ChevronRight } from "lucide-vue-next";

const props = defineProps<{
  title: string;
  breadcrumb?: string | readonly string[];
}>();

const slots = useSlots();
const hasSubtitle = computed(() => Boolean(slots.subtitle));
const hasActions = computed(() => Boolean(slots.actions));

const crumbs = computed<string[]>(() => {
  if (!props.breadcrumb) return [];
  if (Array.isArray(props.breadcrumb)) {
    return (props.breadcrumb as readonly string[]).map((c) => c.trim()).filter(Boolean);
  }
  return String(props.breadcrumb)
    .split("/")
    .map((c) => c.trim())
    .filter(Boolean);
});
</script>

<template>
  <header class="space-y-1">
    <nav
      v-if="crumbs.length"
      aria-label="Breadcrumb"
      class="flex flex-wrap items-center gap-x-1.5 gap-y-1 text-xs font-medium"
    >
      <template v-for="(crumb, idx) in crumbs" :key="idx">
        <ChevronRight
          v-if="idx > 0"
          class="h-3 w-3 shrink-0 text-slate-300"
          aria-hidden="true"
        />
        <span
          :class="idx === crumbs.length - 1 ? 'text-slate-700' : 'text-slate-400'"
        >
          {{ crumb }}
        </span>
      </template>
    </nav>
    <div class="flex flex-wrap items-start justify-between gap-3">
      <div class="min-w-0 flex-1">
        <h1 class="page-title">{{ title }}</h1>
        <p v-if="hasSubtitle" class="mt-1 text-sm text-slate-500">
          <slot name="subtitle" />
        </p>
      </div>
      <div v-if="hasActions" class="flex shrink-0 flex-wrap items-center gap-2">
        <slot name="actions" />
      </div>
    </div>
  </header>
</template>
