import { getKerisiMenuTrailByMenuId } from "@/config/kerisi-menu-resolve";
import type { KerisiSfLevel3Datatable, KerisiSfLevel3PageSpec } from "@/config/kerisi-sf-level3-registry.generated";
import { getKerisiSfLevel3Spec } from "@/config/kerisi-sf-level3-registry.generated";

const SYNTHETIC_COMPONENT_ID = -1;

/** Placeholder grid when JSON export omitted datatable metadata (common for setup screens). */
export function syntheticPlaceholderDatatable(
  componentTitle: string,
  dtFilter: string | null = "default",
): KerisiSfLevel3Datatable {
  const t = componentTitle.trim() || "List";
  return {
    componentId: SYNTHETIC_COMPONENT_ID,
    componentTitle: t,
    dtBi: ["No", "Details", "Action"],
    dtKey: ["", "details", ""],
    dtAjax: null,
    dtFilter: dtFilter,
    dtPageLength: "10",
  };
}

export function buildShellFallbackSpec(menuId: number): KerisiSfLevel3PageSpec {
  const trail = getKerisiMenuTrailByMenuId(menuId);
  const pageTitle = trail?.length ? trail[trail.length - 1]! : `Menu ${menuId}`;
  return {
    menuId,
    pageId: 0,
    pageName: "shell_fallback",
    pageTitle,
    menuPath: trail?.join(">") ?? "",
    breadcrumbRaw: "",
    legacyApiUrl: null,
    legacyBlName: null,
    datatables: [syntheticPlaceholderDatatable(pageTitle)],
    smartFilterFields: [],
    topFilterFields: [],
    popupFormFields: [],
    formSections: [],
  };
}

export type ResolvedKerisiSfShell = {
  spec: KerisiSfLevel3PageSpec;
  hint: string | null;
};

/**
 * Registry row + synthetic grid when export omitted columns, or full fallback from menu tree.
 */
export function resolveKerisiSfShellSpec(menuId: number): ResolvedKerisiSfShell {
  const reg = getKerisiSfLevel3Spec(menuId);
  if (! reg) {
    return {
      spec: buildShellFallbackSpec(menuId),
      hint: "This menu is not in the PAGE_MENUID1019_LEVEL3 export — showing a placeholder grid. Regenerate the registry when the export includes this page, or add a dedicated Vue view.",
    };
  }
  if (reg.datatables.length === 0) {
    const filterMode =
      reg.smartFilterFields.length > 0
        ? "smart"
        : reg.topFilterFields.length > 0
          ? "top"
          : "default";
    return {
      spec: {
        ...reg,
        datatables: [syntheticPlaceholderDatatable(reg.pageTitle || "List", filterMode)],
      },
      hint: "The Level-3 export has no datatable column metadata for this screen (legacy UI may be form-only or not captured). Placeholder columns are shown until the export or backend list is wired.",
    };
  }
  return { spec: reg, hint: null };
}
