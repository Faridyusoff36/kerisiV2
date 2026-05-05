<script setup lang="ts">
import { ChevronDown } from "lucide-vue-next";

import type { MenuNode } from "@/config/admin-menu";

defineProps<{
  /** Children to render at this level (already pref-resolved). */
  nodes: MenuNode[];
  /**
   * Render depth, 0 = direct children of a top-level item (the original
   * "child" row), 1 = grandchild, 2+ = great-grandchild and beyond.
   */
  depth: number;
  /** Reactive open/closed map keyed by `node.id`. */
  openMenus: Record<string, boolean>;
  /** Active-route checker (matches AdminLayout's `isNodeActive`). */
  isNodeActive: (node: { to: string; children?: MenuNode[] }) => boolean;
  /** Per-row class helpers from AdminLayout. */
  childRowClass: string;
  childClass: (path: string) => string;
  /** Toggle handler from AdminLayout (mutates `openMenus[id]`). */
  toggle: (id: string) => void;
  /** Active route path, used to highlight the active leaf via `childClass`. */
  routePath: string;
}>();
</script>

<template>
  <div
    class="mt-1 space-y-0.5"
    :class="
      depth === 0
        ? 'ml-5 border-l-2 border-slate-200 pl-4'
        : 'ml-4 border-l border-slate-200 pl-3'
    "
  >
    <template v-for="node in nodes" :key="node.id">
      <button
        v-if="node.children && node.children.length > 0"
        type="button"
        class="flex w-full items-center rounded-md text-left transition-all hover:bg-[var(--accent-50)]"
        :class="[childRowClass, childClass(isNodeActive(node) ? routePath : node.to)]"
        @click="toggle(node.id)"
      >
        <span class="flex-1">{{ node.label }}</span>
        <ChevronDown
          class="h-3.5 w-3.5 text-slate-400 transition-transform duration-200"
          :class="{ '-rotate-90': !openMenus[node.id] }"
        />
      </button>

      <router-link
        v-else
        :to="node.to"
        :class="[childRowClass, childClass(node.to)]"
      >
        {{ node.label }}
      </router-link>

      <AdminSidebarSubmenu
        v-if="node.children && node.children.length > 0 && openMenus[node.id]"
        :nodes="node.children"
        :depth="depth + 1"
        :open-menus="openMenus"
        :is-node-active="isNodeActive"
        :child-row-class="childRowClass"
        :child-class="childClass"
        :toggle="toggle"
        :route-path="routePath"
      />
    </template>
  </div>
</template>
