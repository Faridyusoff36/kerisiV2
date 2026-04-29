#!/usr/bin/env node
/**
 * Reads `/Users/nurinaamira/PAGE_LEVEL3/PAGE_MENUID1122_LEVEL3.json` (Payroll)
 * and emits:
 *   - client/src/config/kerisi-payroll-registry.generated.ts
 *   - config/kerisi_payroll.php (whitelist of menu IDs)
 *
 * Run from repo root: `node scripts/gen-kerisi-payroll-registry.mjs`
 */
import fs from "fs";
import path from "path";
import { fileURLToPath } from "url";

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const root = path.resolve(__dirname, "..");

const SRC_JSON = "/Users/nurinaamira/PAGE_LEVEL3/PAGE_MENUID1122_LEVEL3.json";
const OUT_TS = path.join(root, "client/src/config/kerisi-payroll-registry.generated.ts");
const OUT_PHP = path.join(root, "config/kerisi_payroll.php");

function stripHtml(s) {
  if (s == null || typeof s !== "string") return "";
  return s.replace(/<[^>]*>/g, "").replace(/\s+/g, " ").trim();
}

function parseDt(raw) {
  if (!raw) return null;
  try {
    const dt = JSON.parse(raw);
    return {
      dtBi: Array.isArray(dt.dt_bi) ? dt.dt_bi : [],
      dtKey: Array.isArray(dt.dt_key) ? dt.dt_key : [],
      dtJs: Array.isArray(dt.dt_js) ? dt.dt_js : [],
      dtAjax: typeof dt.dt_ajax === "string" ? dt.dt_ajax : null,
      dtFilter: dt.dt_filter ?? null,
      dtPageLength: dt.dt_pageLength ?? null,
      dtFreezeLeft: Number(dt.dt_freeze_left ?? 0),
      dtFreezeRight: Number(dt.dt_freeze_right ?? 0),
      dtSort: Array.isArray(dt.dt_sort) ? dt.dt_sort : [],
      dtClass: Array.isArray(dt.dt_class) ? dt.dt_class : [],
    };
  } catch {
    return null;
  }
}

function aggregateMenu(menuId, rows) {
  const first = rows[0];
  const datatableByComp = new Map();
  const smartFilterFields = [];
  const topFilterFields = [];
  const popupFormFields = [];
  const formSections = [];

  for (const r of rows) {
    const ct = String(r.COMPONENTTYPE || "");
    const ctNorm = ct.toLowerCase();

    if (ct === "datatable" && r["Datatable column details"]) {
      const dt = parseDt(r["Datatable column details"]);
      if (dt && !datatableByComp.has(r.COMPONENTID)) {
        datatableByComp.set(r.COMPONENTID, {
          componentId: r.COMPONENTID,
          componentTitle: stripHtml(r.COMPONENTTITLE || ""),
          apiBlName: r.API_BL_NAME || null,
          apiUrl: r.API_URL ? String(r.API_URL).replace(/^"+|"+$/g, "").trim() : null,
          ...dt,
        });
      }
    }

    const rawTitle = r.Form_Item_Title || "";
    if (!rawTitle && ct !== "datatable") continue;

    const field = {
      title: stripHtml(rawTitle),
      fieldType: r.Form_Item_Type || "",
      cssClass: r.Form_Item_css_class || "",
      additionalAttribute: r.Form_Item_additional_attribute || "",
      lookupQuery: r.Form_Item_lookup_query || null,
      isDisabled: r.Form_Item_isDisable === "true" || r.Form_Item_isDisable === true,
      defaultValue: r.Form_Item_default || null,
    };

    if (!field.title) continue;

    if (ctNorm.includes("smart filter")) {
      smartFilterFields.push(field);
    } else if (ctNorm.includes("top filter")) {
      topFilterFields.push(field);
    } else if (ctNorm.includes("popup")) {
      popupFormFields.push(field);
    } else if (ct === "form") {
      formSections.push({
        componentId: r.COMPONENTID,
        componentTitle: stripHtml(r.COMPONENTTITLE || ""),
        ...field,
      });
    }
  }

  // Primary API BL name and URL
  let blName = null;
  let apiUrl = null;
  for (const r of rows) {
    if (r.API_BL_NAME && String(r.API_BL_NAME).trim()) {
      blName = String(r.API_BL_NAME).trim();
      break;
    }
  }
  for (const r of rows) {
    if (r.API_URL) {
      const u = String(r.API_URL).replace(/^"+|"+$/g, "").trim();
      if (u) { apiUrl = u; break; }
    }
  }

  // Breadcrumb: strip <back...> tags
  const bc = (first.PAGEBREADCRUMBS || "")
    .replace(/<back[^>]*><\/back>/gi, "")
    .replace(/<[^>]*>/g, "")
    .trim();

  return {
    menuId,
    pageId: first.PAGEID,
    pageName: first.PAGENAME || "",
    pageTitle: first.PAGETITLE || "",
    menuPath: first.Menu || "",
    breadcrumbRaw: bc,
    legacyApiUrl: apiUrl,
    legacyBlName: blName,
    hasBackButton: /(<back)/i.test(first.PAGEBREADCRUMBS || ""),
    blPreprocessTotal: Number(first.BL_preprocess_total ?? 0),
    blOnloadTotal: Number(first.BL_onload_total ?? 0),
    datatables: [...datatableByComp.values()],
    smartFilterFields,
    topFilterFields,
    popupFormFields,
    formSections,
  };
}

function tsEscape(str) {
  return String(str)
    .replace(/\\/g, "\\\\")
    .replace(/'/g, "\\'")
    .replace(/\r/g, "\\r")
    .replace(/\n/g, "\\n");
}

function main() {
  if (!fs.existsSync(SRC_JSON)) {
    console.error("Missing source JSON:", SRC_JSON);
    process.exit(1);
  }

  const raw = JSON.parse(fs.readFileSync(SRC_JSON, "utf8"));
  const byMenu = new Map();
  for (const r of raw) {
    const id = r.MENUID;
    if (!byMenu.has(id)) byMenu.set(id, []);
    byMenu.get(id).push(r);
  }

  const menuIds = [...byMenu.keys()].sort((a, b) => a - b);
  const specs = {};
  for (const id of menuIds) {
    specs[id] = aggregateMenu(id, byMenu.get(id));
  }

  const lines = [];
  lines.push("/** Auto-generated by scripts/gen-kerisi-payroll-registry.mjs — do not edit by hand. */");
  lines.push("");
  lines.push("export type KerisiPayrollFormField = {");
  lines.push("  title: string;");
  lines.push("  fieldType: string;");
  lines.push("  cssClass: string;");
  lines.push("  additionalAttribute: string;");
  lines.push("  lookupQuery: string | null;");
  lines.push("  isDisabled: boolean;");
  lines.push("  defaultValue: string | null;");
  lines.push("};");
  lines.push("");
  lines.push("export type KerisiPayrollDatatable = {");
  lines.push("  componentId: number;");
  lines.push("  componentTitle: string;");
  lines.push("  apiBlName: string | null;");
  lines.push("  apiUrl: string | null;");
  lines.push("  dtBi: string[];");
  lines.push("  dtKey: string[];");
  lines.push("  dtJs: string[];");
  lines.push("  dtAjax: string | null;");
  lines.push("  dtFilter: string | null;");
  lines.push("  dtPageLength: string | null;");
  lines.push("  dtFreezeLeft: number;");
  lines.push("  dtFreezeRight: number;");
  lines.push("  dtSort: string[];");
  lines.push("  dtClass: string[];");
  lines.push("};");
  lines.push("");
  lines.push("export type KerisiPayrollPageSpec = {");
  lines.push("  menuId: number;");
  lines.push("  pageId: number;");
  lines.push("  pageName: string;");
  lines.push("  pageTitle: string;");
  lines.push("  menuPath: string;");
  lines.push("  breadcrumbRaw: string;");
  lines.push("  legacyApiUrl: string | null;");
  lines.push("  legacyBlName: string | null;");
  lines.push("  hasBackButton: boolean;");
  lines.push("  blPreprocessTotal: number;");
  lines.push("  blOnloadTotal: number;");
  lines.push("  datatables: KerisiPayrollDatatable[];");
  lines.push("  smartFilterFields: KerisiPayrollFormField[];");
  lines.push("  topFilterFields: KerisiPayrollFormField[];");
  lines.push("  popupFormFields: KerisiPayrollFormField[];");
  lines.push("  formSections: Array<KerisiPayrollFormField & { componentId: number; componentTitle: string }>;");
  lines.push("};");
  lines.push("");
  lines.push("export const KERISI_PAYROLL_REGISTRY: Record<number, KerisiPayrollPageSpec> = {");

  for (const id of menuIds) {
    const s = specs[id];
    lines.push(`  ${id}: {`);
    lines.push(`    menuId: ${s.menuId},`);
    lines.push(`    pageId: ${s.pageId},`);
    lines.push(`    pageName: '${tsEscape(s.pageName)}',`);
    lines.push(`    pageTitle: '${tsEscape(s.pageTitle)}',`);
    lines.push(`    menuPath: '${tsEscape(s.menuPath)}',`);
    lines.push(`    breadcrumbRaw: '${tsEscape(s.breadcrumbRaw)}',`);
    lines.push(`    legacyApiUrl: ${s.legacyApiUrl ? `'${tsEscape(s.legacyApiUrl)}'` : "null"},`);
    lines.push(`    legacyBlName: ${s.legacyBlName ? `'${tsEscape(s.legacyBlName)}'` : "null"},`);
    lines.push(`    hasBackButton: ${s.hasBackButton},`);
    lines.push(`    blPreprocessTotal: ${s.blPreprocessTotal},`);
    lines.push(`    blOnloadTotal: ${s.blOnloadTotal},`);
    lines.push(`    datatables: ${JSON.stringify(s.datatables)},`);
    lines.push(`    smartFilterFields: ${JSON.stringify(s.smartFilterFields)},`);
    lines.push(`    topFilterFields: ${JSON.stringify(s.topFilterFields)},`);
    lines.push(`    popupFormFields: ${JSON.stringify(s.popupFormFields)},`);
    lines.push(`    formSections: ${JSON.stringify(s.formSections)},`);
    lines.push(`  },`);
  }

  lines.push("};");
  lines.push("");
  lines.push("export function isKerisiPayrollRegistered(menuId: number): boolean {");
  lines.push("  return Object.prototype.hasOwnProperty.call(KERISI_PAYROLL_REGISTRY, menuId);");
  lines.push("}");
  lines.push("");
  lines.push("export function getKerisiPayrollSpec(menuId: number): KerisiPayrollPageSpec | null {");
  lines.push("  return KERISI_PAYROLL_REGISTRY[menuId] ?? null;");
  lines.push("}");

  fs.writeFileSync(OUT_TS, lines.join("\n") + "\n", "utf8");

  const phpBody = `<?php

declare(strict_types=1);

/**
 * Menu IDs for the Payroll module (MENUID 1122 parent).
 * Auto-generated by scripts/gen-kerisi-payroll-registry.mjs
 */
return [
    'menu_ids' => [
${menuIds.map((id) => `        ${id},`).join("\n")}
    ],
];
`;
  fs.writeFileSync(OUT_PHP, phpBody, "utf8");

  console.log("Wrote", OUT_TS);
  console.log("Wrote", OUT_PHP);
  console.log("Payroll menus:", menuIds.length, "→", menuIds.join(", "));
}

main();
