<script setup lang="ts">
import { computed, watchEffect } from "vue";
import { Construction } from "lucide-vue-next";
import { useRoute } from "vue-router";

import {
  getKerisiMenuTrailByMenuId,
  parseKerisiNumericMenuIdFromPath,
} from "@/config/kerisi-menu-resolve";
import AdminLayout from "@/layouts/AdminLayout.vue";
import { useSiteStore } from "@/stores/site";

const route = useRoute();
const site = useSiteStore();

const menuIdParsed = computed(() => {
  const raw =
    typeof route.params.menuId === "string"
      ? route.params.menuId
      : Array.isArray(route.params.menuId)
        ? route.params.menuId[0]
        : "";
  if (raw) {
    const n = Number.parseInt(raw, 10);
    if (Number.isFinite(n)) return n;
  }
  return parseKerisiNumericMenuIdFromPath(route.path);
});

const breadcrumbTrail = computed(() =>
  menuIdParsed.value !== null ? getKerisiMenuTrailByMenuId(menuIdParsed.value) : null,
);

/** Single line for `.page-title` (plain text trail). */
const pageHeading = computed(() => {
  const trail = breadcrumbTrail.value;
  if (trail?.length) {
    return trail.join(" / ");
  }
  const id = menuIdParsed.value;
  return id !== null ? `KERISI / Menu ${id}` : "KERISI";
});

watchEffect(() => {
  /** Only sync tab title for the generic `:menuId` fallback; explicit routes use `meta.title` + router.afterEach. */
  if (route.name !== "kerisi-menu") return;
  const head = pageHeading.value;
  site.setDocumentTitle(head.replace(/\s+\/\s+/g, " · "));
});
</script>

<template>
  <AdminLayout>
    <div class="space-y-4">
      <h1 class="page-title">{{ pageHeading }}</h1>

      <article class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col items-center justify-center px-4 py-16 text-center">
          <Construction class="h-12 w-12 text-slate-300" />
          <h2 class="mt-4 text-lg font-semibold text-slate-700">Under Development</h2>
          <p class="mt-1 max-w-md text-sm text-slate-400">
            This Kerisi screen has not been migrated yet. Labels above come from the menu map (
            <code class="rounded bg-slate-100 px-1 text-xs text-slate-600">kerisi-menu-migrated</code>
            ).
          </p>
          <p v-if="menuIdParsed !== null" class="mt-2 text-xs text-slate-500">MENUID {{ menuIdParsed }}</p>
        </div>
      </article>
    </div>
  </AdminLayout>
</template>
