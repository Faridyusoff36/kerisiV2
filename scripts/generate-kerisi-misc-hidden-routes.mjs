import fs from "fs";
import path from "path";

const JSON_DIR = "C:/KerisiAI/02MigrateFromOldKerisi/JSON_DATA";
const LEVEL_FILES = [
  "HIDDEN_PAGE_LEVEL2.json",
  "HIDDEN_PAGE_LEVEL3.json",
  "HIDDEN_PAGE_LEVEL4.json",
  "HIDDEN_PAGE_LEVEL5.json",
];

const ROUTER_DIR = "client/src/router";

const router = fs.readFileSync(path.join(ROUTER_DIR, "index.ts"), "utf8");
const routeIds = new Set();
const re = /path:\s*["']\/admin\/kerisi\/m\/(\d+)["']/g;
let m;
while ((m = re.exec(router))) routeIds.add(m[1]);

for (const f of fs.readdirSync(ROUTER_DIR)) {
  if (!f.endsWith("-hidden-routes.ts")) continue;
  const src = fs.readFileSync(path.join(ROUTER_DIR, f), "utf8");
  const fnRe = /\b[a-zA-Z]+Hidden\(\s*(\d+)/g;
  while ((m = fnRe.exec(src))) routeIds.add(m[1]);
}

/**
 * Per-module config: kebab slug, helper function name, related-link target.
 * `prefix` is matched against `Menu.toLowerCase()` startsWith.
 */
const MODULES = [
  {
    prefix: "account payable>",
    slug: "account-payable",
    fn: "apHidden",
    exportName: "kerisiAccountPayableHiddenRoutes",
    related: { path: "/admin/kerisi/m/2078", label: "Account bank updated" },
    namePrefix: "kerisi-ap",
    description:
      "This legacy Account Payable bill, voucher, payment, integration, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
  },
  {
    prefix: "account receivable>",
    slug: "account-receivable",
    fn: "arHidden",
    exportName: "kerisiAccountReceivableHiddenRoutes",
    related: { path: "/admin/kerisi/m/1023", label: "Invoice listing" },
    namePrefix: "kerisi-ar",
    description:
      "This legacy Account Receivable invoice, receipt, cheque, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
  },
  {
    prefix: "budget>",
    slug: "budget",
    fn: "budgetHidden",
    exportName: "kerisiBudgetHiddenRoutes",
    related: { path: "/admin/kerisi/m/1471", label: "Budget monitoring" },
    namePrefix: "kerisi-budget",
    description:
      "This legacy Budget setup, planning, closing, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
  },
  {
    prefix: "purchasing>",
    slug: "purchasing",
    fn: "purchHidden",
    exportName: "kerisiPurchasingHiddenRoutes",
    related: { path: "/admin/kerisi/m/1841", label: "Status PO & PR" },
    namePrefix: "kerisi-purch",
    description:
      "This legacy Purchasing screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
  },
  {
    prefix: "general ledger>",
    slug: "general-ledger-extra",
    fn: "glExtraHidden",
    exportName: "kerisiGeneralLedgerExtraHiddenRoutes",
    related: { path: "/admin/kerisi/m/2519", label: "General Ledger listing" },
    namePrefix: "kerisi-gl-extra",
    description:
      "This legacy General Ledger reversal, trial balance, or financial statement screen is not reproduced in Kerisi20. Use Kerisi Classic for the full report.",
  },
  {
    prefix: "vendor portal>",
    slug: "vendor-portal",
    fn: "vendorPortalHidden",
    exportName: "kerisiVendorPortalHiddenRoutes",
    related: { path: "/admin/kerisi/m/1961", label: "Vendor portal" },
    namePrefix: "kerisi-vendor-portal",
    description:
      "This legacy Vendor Portal screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
  },
  {
    prefix: "petty cash >",
    slug: "petty-cash",
    fn: "pcHidden",
    exportName: "kerisiPettyCashHiddenRoutes",
    related: { path: "/admin/kerisi/m/1490", label: "List of petty cash application" },
    namePrefix: "kerisi-petty-cash",
    description:
      "This legacy Petty Cash claim, recoupment, setup, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
  },
  {
    prefix: "debtor portal>",
    slug: "debtor-portal",
    fn: "debtorPortalHidden",
    exportName: "kerisiDebtorPortalHiddenRoutes",
    related: { path: "/admin/kerisi/m/2267", label: "Debtors statement" },
    namePrefix: "kerisi-debtor-portal",
    description:
      "This legacy Debtor Portal screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
  },
  {
    prefix: "eis>",
    slug: "eis",
    fn: "eisHidden",
    exportName: "kerisiEisHiddenRoutes",
    related: { path: "/admin", label: "Main dashboard" },
    namePrefix: "kerisi-eis",
    description: "This legacy EIS screen is not reproduced in Kerisi20.",
  },
];

function fallbackTitle(menu) {
  const parts = String(menu).split(">");
  const leaf = parts[parts.length - 1]?.trim();
  return leaf || "Page";
}

function slugifyPath(menuPath) {
  return menuPath
    .toLowerCase()
    .replace(/[()/&]/g, " ")
    .replace(/[^a-z0-9]+/g, "-")
    .replace(/^-+|-+$/g, "")
    .slice(0, 80);
}

const rows = [];
for (const f of LEVEL_FILES) {
  const fp = path.join(JSON_DIR, f);
  if (!fs.existsSync(fp)) continue;
  const j = JSON.parse(fs.readFileSync(fp, "utf8"));
  for (const r of j) rows.push(r);
}

const byMenu = new Map();
for (const r of rows) {
  const mid = String(r.MENUID);
  if (!byMenu.has(mid)) byMenu.set(mid, { PAGETITLE: r.PAGETITLE, Menu: r.Menu });
}

for (const mod of MODULES) {
  const todo = [...byMenu.entries()]
    .filter(([mid, d]) => {
      if (routeIds.has(mid)) return false;
      const s = String(d.Menu || "").toLowerCase();
      return s.startsWith(mod.prefix);
    })
    .sort((a, b) => Number(a[0]) - Number(b[0]));

  const outFile = path.join(ROUTER_DIR, `kerisi-${mod.slug}-hidden-routes.ts`);

  if (todo.length === 0) {
    if (fs.existsSync(outFile)) fs.rmSync(outFile);
    continue;
  }

  const usedNames = new Set();
  const lines = todo.map(([mid, d]) => {
    const rawTitle = String(d.PAGETITLE ?? "").replace(/\s+/g, " ").trim();
    const title = rawTitle || fallbackTitle(d.Menu);
    const menuPath = String(d.Menu).replace(/\s+/g, " ").trim();
    const slug = slugifyPath(
      menuPath.split(">").slice(1).join("-") || mid,
    );
    let name = `${mod.namePrefix}-${slug || mid}`;
    if (usedNames.has(name)) name = `${name}-${mid}`;
    usedNames.add(name);
    return `  ${mod.fn}(${mid}, ${JSON.stringify(name)}, ${JSON.stringify(title)}, ${JSON.stringify(menuPath)}),`;
  });

  const body = `import type { RouteRecordRaw } from "vue-router";
import GenericLegacyPlaceholderView from "@/views/GenericLegacyPlaceholderView.vue";

const DESC = ${JSON.stringify(mod.description)};
const RELATED = { path: ${JSON.stringify(mod.related.path)}, label: ${JSON.stringify(mod.related.label)} };

function ${mod.fn}(menuId: number, name: string, title: string, legacyMenuPath: string): RouteRecordRaw {
  const breadcrumb = legacyMenuPath.replace(/>/g, " / ").replace(/\\s+/g, " ").trim();
  return {
    path: \`/admin/kerisi/m/\${menuId}\`,
    name,
    component: GenericLegacyPlaceholderView,
    props: {
      title,
      breadcrumb,
      description: DESC,
      relatedPath: RELATED.path,
      relatedLabel: RELATED.label,
    },
    meta: { requiresAuth: true, title },
  };
}

/** HIDDEN_PAGE_LEVEL2–5 rows whose \`Menu\` starts with \`${mod.prefix.replace(/>$/, "")}\`. Generated. */
export const ${mod.exportName}: RouteRecordRaw[] = [
${lines.join("\n")}
];
`;

  fs.writeFileSync(outFile, body, "utf8");
  console.log(`Wrote ${outFile} (${todo.length} routes)`);
}
