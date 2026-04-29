import { KERISI_MENU_TREE, type KerisiMigratedMenuNode } from "@/config/kerisi-menu-migrated";

/** Resolves `/admin/kerisi/m/123` even when matched by a literal static route (no `:menuId` param). */
export function parseKerisiNumericMenuIdFromPath(pathname: string): number | null {
  const m = pathname.match(/^\/admin\/kerisi\/m\/(\d+)\/?$/);
  if (!m?.[1]) return null;
  const n = Number.parseInt(m[1], 10);
  return Number.isFinite(n) ? n : null;
}

/** DFS the migrated Kerisi tree by menuId; returns breadcrumb labels from root to leaf. */
function findKerisiMenuTrail(
  nodes: KerisiMigratedMenuNode[],
  menuId: number,
  trail: string[],
): string[] | null {
  for (const n of nodes) {
    const next = [...trail, n.label];
    if (n.menuId === menuId) {
      return next;
    }
    if (n.children?.length) {
      const inner = findKerisiMenuTrail(n.children, menuId, next);
      if (inner !== null) {
        return inner;
      }
    }
  }
  return null;
}

export function getKerisiMenuTrailByMenuId(menuId: number): string[] | null {
  return findKerisiMenuTrail(KERISI_MENU_TREE, menuId, []);
}
