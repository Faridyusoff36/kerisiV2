#!/usr/bin/env node
/**
 * gen-kerisi-remaining-registry.mjs
 * Processes all 14 remaining FIMS JSON files and outputs:
 *   - client/src/config/kerisi-remaining-registry.generated.ts
 *   - config/kerisi_remaining.php
 */
import { readFileSync, writeFileSync } from "fs";
import { join, dirname } from "path";
import { fileURLToPath } from "url";

const __dir = dirname(fileURLToPath(import.meta.url));
const ROOT  = join(__dir, "..");
const PAGE_DIR = "/Users/nurinaamira/PAGE_LEVEL3";

const SOURCE_FILES = [
  "PAGE_MENUID1228_LEVEL3.json",
  "PAGE_MENUID1229_LEVEL3.json",
  "PAGE_MENUID1404_LEVEL3.json",
  "PAGE_MENUID1489_LEVEL3.json",
  "PAGE_MENUID1533_LEVEL3.json",
  "PAGE_MENUID1543_LEVEL3.json",
  "PAGE_MENUID1547_LEVEL3.json",
  "PAGE_MENUID1651_LEVEL3.json",
  "PAGE_MENUID1684_LEVEL3.json",
  "PAGE_MENUID1701_LEVEL3.json",
  "PAGE_MENUID1709_LEVEL3.json",
  "PAGE_MENUID3401_LEVEL3.json",
  "PAGE_MENUID3454_LEVEL3.json",
  "PAGE_MENUID1052_LEVEL3.json",
];

/* ── helpers ────────────────────────────────────────────────────────────── */
function stripHtml(s) {
  return String(s || "").replace(/<[^>]*>/g, "").replace(/\s+/g, " ").trim();
}

function tsEscape(s) {
  return String(s || "").replace(/\\/g, "\\\\").replace(/'/g, "\\'");
}

function parseDt(raw) {
  if (!raw) return null;
  try {
    const obj = typeof raw === "string" ? JSON.parse(raw) : raw;
    const dtBi  = (obj.dt_bi  || []).map(String);
    const dtKey = (obj.dt_key || []).map(v => (v === null || v === undefined) ? "" : String(v));
    if (!dtBi.length) return null;
    return {
      dtBi,
      dtKey,
      dtJs:         (obj.dt_js  || []).map(v => (v === null || v === undefined) ? "" : String(v)),
      dtAjax:       obj.dt_ajax   ? String(obj.dt_ajax)   : null,
      dtFilter:     obj.dt_filter ? String(obj.dt_filter) : null,
      dtPageLength: obj.dt_page_length ? String(obj.dt_page_length) : null,
      dtFreezeLeft:  Number(obj.dt_freeze_left  || 0),
      dtFreezeRight: Number(obj.dt_freeze_right || 0),
      dtSort:  (obj.dt_sort  || []).map(String),
      dtClass: (obj.dt_class || []).map(String),
    };
  } catch { return null; }
}

function parseFormFields(rows, typeFilter) {
  const fields = [];
  for (const r of rows) {
    const ct = String(r.COMPONENTTYPE || "").toLowerCase();
    if (!ct.includes(typeFilter)) continue;
    const items = r.Form_Item_Title || r.form_item_title;
    if (!items) continue;
    const titles  = Array.isArray(items) ? items : [items];
    const types   = Array.isArray(r.Form_Item_Type)                ? r.Form_Item_Type                : [r.Form_Item_Type || "text"];
    const css     = Array.isArray(r.Form_Item_css_class)            ? r.Form_Item_css_class            : [r.Form_Item_css_class || ""];
    const attrs   = Array.isArray(r.Form_Item_additional_attribute) ? r.Form_Item_additional_attribute : [r.Form_Item_additional_attribute || ""];
    const defs    = Array.isArray(r.Form_Item_default)              ? r.Form_Item_default              : [r.Form_Item_default || null];
    const lookups = Array.isArray(r.Form_Item_lookup_query)         ? r.Form_Item_lookup_query         : [r.Form_Item_lookup_query || null];
    const disab   = Array.isArray(r.Form_Item_isDisable)            ? r.Form_Item_isDisable            : [r.Form_Item_isDisable || false];
    for (let i = 0; i < titles.length; i++) {
      fields.push({
        title:               stripHtml(titles[i] || ""),
        fieldType:           String(types[i] || "text").toLowerCase().replace(/[^a-z0-9_]/g,"").trim() || "text",
        cssClass:            String(css[i] || ""),
        additionalAttribute: String(attrs[i] || ""),
        lookupQuery:         lookups[i] ? String(lookups[i]) : null,
        isDisabled:          Boolean(disab[i]),
        defaultValue:        defs[i] !== null && defs[i] !== undefined ? String(defs[i]) : null,
      });
    }
  }
  return fields;
}

/* ── aggregate one menuId ───────────────────────────────────────────────── */
function aggregateMenu(menuId, rows) {
  const first = rows[0];
  const datatableByComp = new Map();
  const smartFilterFields = [];
  const topFilterFields   = [];
  const popupFormFields   = [];
  const formSections      = [];

  for (const r of rows) {
    const ct     = String(r.COMPONENTTYPE || "");
    const ctLow  = ct.toLowerCase();

    if (ct === "datatable" || ctLow === "datatable") {
      const raw = r["Datatable column details"] || r.datatable_column_details;
      const dt  = parseDt(raw);
      if (dt && !datatableByComp.has(r.COMPONENTID)) {
        datatableByComp.set(r.COMPONENTID, {
          componentId:    r.COMPONENTID,
          componentTitle: stripHtml(r.COMPONENTTITLE || ""),
          apiBlName:      r.API_BL_NAME ? String(r.API_BL_NAME).trim() : null,
          apiUrl:         r.API_URL     ? String(r.API_URL).replace(/^"+|"+$/g,"").trim() : null,
          ...dt,
        });
      }
    }

    if (ctLow.includes("smart filter") || ctLow.includes("smart_filter")) {
      const sf = parseFormFields([r], "smart");
      smartFilterFields.push(...sf);
    }
    if (ctLow.includes("top filter") || ctLow.includes("top_filter")) {
      const tf = parseFormFields([r], "top");
      topFilterFields.push(...tf);
    }
    if (ctLow.includes("popup") || ctLow.includes("modal")) {
      const pf = parseFormFields([r], "popup");
      popupFormFields.push(...pf);
    }
    if (ctLow === "form") {
      const items = r.Form_Item_Title || r.form_item_title;
      if (items) {
        const arr = Array.isArray(items) ? items : [items];
        const types   = Array.isArray(r.Form_Item_Type) ? r.Form_Item_Type : [r.Form_Item_Type || "text"];
        const css     = Array.isArray(r.Form_Item_css_class) ? r.Form_Item_css_class : [r.Form_Item_css_class || ""];
        const attrs   = Array.isArray(r.Form_Item_additional_attribute) ? r.Form_Item_additional_attribute : [r.Form_Item_additional_attribute || ""];
        const defs    = Array.isArray(r.Form_Item_default) ? r.Form_Item_default : [r.Form_Item_default || null];
        const lookups = Array.isArray(r.Form_Item_lookup_query) ? r.Form_Item_lookup_query : [r.Form_Item_lookup_query || null];
        const disab   = Array.isArray(r.Form_Item_isDisable) ? r.Form_Item_isDisable : [r.Form_Item_isDisable || false];
        for (let i = 0; i < arr.length; i++) {
          formSections.push({
            componentId:    r.COMPONENTID,
            componentTitle: stripHtml(r.COMPONENTTITLE || ""),
            title:          stripHtml(arr[i] || ""),
            fieldType:      String(types[i] || "text").toLowerCase().replace(/[^a-z0-9_]/g,"").trim() || "text",
            cssClass:       String(css[i] || ""),
            additionalAttribute: String(attrs[i] || ""),
            lookupQuery:    lookups[i] ? String(lookups[i]) : null,
            isDisabled:     Boolean(disab[i]),
            defaultValue:   defs[i] !== null && defs[i] !== undefined ? String(defs[i]) : null,
          });
        }
      }
    }
  }

  const menuPath = String(first.MENUPATH || first.PAGEBREADCRUMBS || "");
  const crumb    = menuPath.replace(/>/g, " / ").replace(/\s+/g," ").trim();

  return {
    menuId:            Number(menuId),
    pageId:            Number(first.PAGEID || 0),
    pageName:          String(first.PAGENAME || ""),
    pageTitle:         stripHtml(first.PAGETITLE || ""),
    menuPath,
    breadcrumbRaw:     crumb,
    legacyApiUrl:      first.API_URL  ? String(first.API_URL).replace(/^"+|"+$/g,"").trim() : null,
    legacyBlName:      first.API_BL_NAME ? String(first.API_BL_NAME).trim() : null,
    hasBackButton:     Boolean(first.HASBACKBUTTON || false),
    blPreprocessTotal: Number(first.BL_preprocess_total || 0),
    blOnloadTotal:     Number(first.BL_onload_total || 0),
    datatables:        [...datatableByComp.values()],
    smartFilterFields,
    topFilterFields,
    popupFormFields,
    formSections,
  };
}

/* ── main ───────────────────────────────────────────────────────────────── */
function main() {
  // Aggregate all rows grouped by menuId across all source files
  const byMenu = new Map();

  for (const fname of SOURCE_FILES) {
    const fpath = join(PAGE_DIR, fname);
    let raw;
    try { raw = readFileSync(fpath, "utf8"); } catch { console.warn(`SKIP: ${fname}`); continue; }
    const data = JSON.parse(raw);
    const rows = Array.isArray(data) ? data : (data.data || []);
    for (const r of rows) {
      const mid = r.MENUID;
      if (!mid) continue;
      const key = Number(mid);
      if (!byMenu.has(key)) byMenu.set(key, []);
      byMenu.get(key).push(r);
    }
  }

  const menuIds = [...byMenu.keys()].sort((a,b) => a-b);
  console.log(`Processing ${menuIds.length} unique menu IDs…`);

  const specs = {};
  for (const id of menuIds) {
    specs[id] = aggregateMenu(id, byMenu.get(id));
  }

  /* ── TypeScript output ──────────────────────────────────────────────── */
  const lines = [
    `/** Auto-generated by scripts/gen-kerisi-remaining-registry.mjs — do not edit. */`,
    ``,
    `export type KerisiRemainingFormField = {`,
    `  title: string;`,
    `  fieldType: string;`,
    `  cssClass: string;`,
    `  additionalAttribute: string;`,
    `  lookupQuery: string | null;`,
    `  isDisabled: boolean;`,
    `  defaultValue: string | null;`,
    `};`,
    ``,
    `export type KerisiRemainingDatatable = {`,
    `  componentId: number;`,
    `  componentTitle: string;`,
    `  apiBlName: string | null;`,
    `  apiUrl: string | null;`,
    `  dtBi: (string | Record<string, unknown>)[];`,
    `  dtKey: (string | Record<string, unknown>)[];`,
    `  dtJs: (string | Record<string, unknown>)[];`,
    `  dtAjax: string | null;`,
    `  dtFilter: string | null;`,
    `  dtPageLength: string | null;`,
    `  dtFreezeLeft: number;`,
    `  dtFreezeRight: number;`,
    `  dtSort: string[];`,
    `  dtClass: string[];`,
    `};`,
    ``,
    `export type KerisiRemainingPageSpec = {`,
    `  menuId: number;`,
    `  pageId: number;`,
    `  pageName: string;`,
    `  pageTitle: string;`,
    `  menuPath: string;`,
    `  breadcrumbRaw: string;`,
    `  legacyApiUrl: string | null;`,
    `  legacyBlName: string | null;`,
    `  hasBackButton: boolean;`,
    `  blPreprocessTotal: number;`,
    `  blOnloadTotal: number;`,
    `  datatables: KerisiRemainingDatatable[];`,
    `  smartFilterFields: KerisiRemainingFormField[];`,
    `  topFilterFields: KerisiRemainingFormField[];`,
    `  popupFormFields: KerisiRemainingFormField[];`,
    `  formSections: Array<KerisiRemainingFormField & { componentId: number; componentTitle: string }>;`,
    `};`,
    ``,
    `export const KERISI_REMAINING_REGISTRY: Record<number, KerisiRemainingPageSpec> = {`,
  ];

  for (const id of menuIds) {
    const s = specs[id];
    lines.push(`  ${id}: ${JSON.stringify(s)},`);
  }
  lines.push(`};`);
  lines.push(``);
  lines.push(`export function isKerisiRemainingRegistered(menuId: number): boolean {`);
  lines.push(`  return Object.prototype.hasOwnProperty.call(KERISI_REMAINING_REGISTRY, menuId);`);
  lines.push(`}`);
  lines.push(``);
  lines.push(`export function getKerisiRemainingSpec(menuId: number): KerisiRemainingPageSpec | null {`);
  lines.push(`  return KERISI_REMAINING_REGISTRY[menuId] ?? null;`);
  lines.push(`}`);
  lines.push(``);

  const tsOut = join(ROOT, "client/src/config/kerisi-remaining-registry.generated.ts");
  writeFileSync(tsOut, lines.join("\n"), "utf8");
  console.log(`Wrote ${tsOut}`);

  /* ── PHP whitelist ──────────────────────────────────────────────────── */
  const phpLines = [
    `<?php`,
    `/** Auto-generated by scripts/gen-kerisi-remaining-registry.mjs — do not edit. */`,
    `return [`,
    `    'menu_ids' => [`,
    ...menuIds.map(id => `        ${id},`),
    `    ],`,
    `];`,
    ``,
  ];
  const phpOut = join(ROOT, "config/kerisi_remaining.php");
  writeFileSync(phpOut, phpLines.join("\n"), "utf8");
  console.log(`Wrote ${phpOut}`);

  console.log(`Done — ${menuIds.length} menus registered.`);
}

main();
