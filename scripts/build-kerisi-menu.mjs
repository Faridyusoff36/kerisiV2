/**
 * Reads:
 *   client/src/config/kerisi-menu-source.csv         (visible Kerisi menu)
 *   client/src/config/kerisi-hidden-menu-source.csv  (Kerisi menus hidden by default)
 *
 * Writes:
 *   client/src/config/kerisi-menu-migrated.ts (auto-generated tree + hidden-id lists)
 *
 * Usage: node scripts/build-kerisi-menu.mjs [path-to-source.csv] [path-to-hidden.csv]
 *
 * Roots: MENUPARENT === 0 (or self-parent). Missing parent rows get a synthetic
 * root; title from kerisi-missing-parent-titles.json (optional) or inferred
 * from first child titles.
 *
 * Hidden rows (sourced from the hidden CSV) are tagged with `hiddenByDefault: true`
 * on each emitted node so the sidebar + admin menu UI can keep them collapsed
 * until an admin opts in. Synthetic parents that exist solely to host hidden
 * children inherit the flag.
 */
import fs from "fs";
import path from "path";
import { fileURLToPath } from "url";

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const root = path.join(__dirname, "..");
const defaultSourceCsv = path.join(root, "client/src/config/kerisi-menu-source.csv");
const defaultHiddenCsv = path.join(root, "client/src/config/kerisi-hidden-menu-source.csv");
const outFile = path.join(root, "client/src/config/kerisi-menu-migrated.ts");
const missingParentTitlesFile = path.join(root, "client/src/config/kerisi-missing-parent-titles.json");

const sourceCsvPath = process.argv[2] || defaultSourceCsv;
const hiddenCsvPath = process.argv[3] || defaultHiddenCsv;

function loadMissingParentTitles() {
  try {
    return JSON.parse(fs.readFileSync(missingParentTitlesFile, "utf8"));
  } catch {
    return {};
  }
}

function parseCsvLine(line) {
  const out = [];
  let cur = "";
  let inQ = false;
  for (let i = 0; i < line.length; i++) {
    const c = line[i];
    if (c === '"') {
      if (inQ && line[i + 1] === '"') {
        cur += '"';
        i++;
      } else inQ = !inQ;
      continue;
    }
    if (!inQ && c === ",") {
      out.push(cur);
      cur = "";
      continue;
    }
    cur += c;
  }
  out.push(cur);
  return out;
}

function readCsvRows(csvPath, hiddenByDefault, rowOrder) {
  if (!fs.existsSync(csvPath)) return [];
  const raw = fs.readFileSync(csvPath, "utf8").replace(/^\uFEFF/, "");
  const lines = raw.split(/\r?\n/).filter((l) => l.trim().length > 0);
  if (lines.length < 2) return [];

  const out = [];
  for (let li = 1; li < lines.length; li++) {
    const cols = parseCsvLine(lines[li]);
    if (cols.length < 5) continue;
    const menuId = Number.parseInt(cols[0].trim(), 10);
    if (Number.isNaN(menuId)) continue;
    let title = cols[1].trim();
    if (title.startsWith('"') && title.endsWith('"')) title = title.slice(1, -1);
    const parentId = Number.parseInt(cols[2].trim(), 10);
    if (Number.isNaN(parentId)) continue;
    const menuOrder = Number.parseInt(cols[4].trim(), 10);
    rowOrder.set(menuId, Number.isNaN(menuOrder) ? 0 : menuOrder);
    out.push({ menuId, title, parentId, virtual: false, hiddenByDefault });
  }
  return out;
}

function inferSyntheticTitle(pid, childRows, rowOrder) {
  if (!childRows.length) return "Menu";
  const sorted = [...childRows].sort(
    (a, b) => (rowOrder.get(a.menuId) ?? 0) - (rowOrder.get(b.menuId) ?? 0) || a.menuId - b.menuId,
  );
  const labels = sorted.map((r) => (r.title || "").trim()).filter(Boolean);
  if (labels.length === 0) return "Menu";
  if (labels.length === 1) return labels[0];
  if (labels.length === 2) return `${labels[0]} · ${labels[1]}`;
  return `${labels[0]} · ${labels[1]} …`;
}

function main() {
  if (!fs.existsSync(sourceCsvPath)) {
    console.error("Source CSV not found:", sourceCsvPath);
    process.exit(1);
  }
  const missingParentTitles = loadMissingParentTitles();
  const rowOrder = new Map();

  const visibleRows = readCsvRows(sourceCsvPath, false, rowOrder);
  if (visibleRows.length === 0) {
    console.error("Source CSV has no data rows");
    process.exit(1);
  }
  const hiddenRows = readCsvRows(hiddenCsvPath, true, rowOrder);

  const seen = new Set();
  const rows = [];
  for (const r of [...visibleRows, ...hiddenRows]) {
    if (seen.has(r.menuId)) continue;
    seen.add(r.menuId);
    rows.push(r);
  }

  const idSet = new Set(rows.map((r) => r.menuId));
  const missingParents = new Set();
  for (const r of rows) {
    if (r.parentId !== 0 && r.parentId !== r.menuId && !idSet.has(r.parentId)) {
      missingParents.add(r.parentId);
    }
  }

  const byParentId = new Map();
  for (const r of rows) {
    if (!byParentId.has(r.parentId)) byParentId.set(r.parentId, []);
    byParentId.get(r.parentId).push(r);
  }

  for (const pid of missingParents) {
    const key = String(pid);
    const mapped = missingParentTitles[key];
    const kids = byParentId.get(pid) || [];
    const title =
      typeof mapped === "string" && mapped.trim()
        ? mapped.trim()
        : inferSyntheticTitle(pid, kids, rowOrder);
    const allKidsHidden = kids.length > 0 && kids.every((k) => k.hiddenByDefault === true);

    rows.push({
      menuId: pid,
      title,
      parentId: 0,
      virtual: true,
      hiddenByDefault: allKidsHidden,
    });
    idSet.add(pid);
  }

  const byId = new Map(rows.map((r) => [r.menuId, r]));
  const childrenMap = new Map();
  const roots = [];

  for (const r of rows) {
    if (r.parentId === 0 || r.parentId === r.menuId) {
      roots.push(r);
      continue;
    }
    if (!byId.has(r.parentId)) {
      roots.push(r);
      continue;
    }
    const list = childrenMap.get(r.parentId) || [];
    list.push(r);
    childrenMap.set(r.parentId, list);
  }

  function sortRows(arr) {
    arr.sort((a, b) => (rowOrder.get(a.menuId) ?? 0) - (rowOrder.get(b.menuId) ?? 0) || a.menuId - b.menuId);
  }

  sortRows(roots);

  function buildNode(r) {
    const kids = childrenMap.get(r.menuId) || [];
    sortRows(kids);
    const label = r.title || `(Menu ${r.menuId})`;
    const to = `/admin/kerisi/m/${r.menuId}`;
    const node = { menuId: r.menuId, label, to };
    if (r.hiddenByDefault === true) node.hiddenByDefault = true;
    if (kids.length > 0) {
      node.children = kids.map(buildNode);
    }
    return node;
  }

  const tree = roots.map(buildNode);

  const hiddenItemIds = [];
  const hiddenChildIds = [];
  const hiddenGrandchildIds = [];

  function collectHiddenIds(nodes, depth) {
    for (const n of nodes) {
      if (n.hiddenByDefault === true) {
        const id = `kerisi-${n.menuId}`;
        if (depth === 0) hiddenItemIds.push(id);
        else if (depth === 1) hiddenChildIds.push(id);
        else if (depth === 2) hiddenGrandchildIds.push(id);
      }
      if (n.children && n.children.length > 0) collectHiddenIds(n.children, depth + 1);
    }
  }
  collectHiddenIds(tree, 0);

  const header = `/** Auto-generated by scripts/build-kerisi-menu.mjs from kerisi-menu-source.csv + kerisi-hidden-menu-source.csv — do not edit. */\n\nexport type KerisiMigratedMenuNode = {\n  menuId: number;\n  label: string;\n  to: string;\n  /** When true, the node is hidden in the sidebar by default but kept in the data model so it can be enabled in the admin Menus UI. */\n  hiddenByDefault?: boolean;\n  children?: KerisiMigratedMenuNode[];\n};\n\nexport const KERISI_MENU_TREE: KerisiMigratedMenuNode[] = `;

  const treeBody = JSON.stringify(tree, null, 2)
    .replace(/"menuId":/g, "menuId:")
    .replace(/"label":/g, "label:")
    .replace(/"to":/g, "to:")
    .replace(/"hiddenByDefault":/g, "hiddenByDefault:")
    .replace(/"children":/g, "children:");

  const idsBlock = `\n\n/** Top-level Kerisi item IDs (kerisi-{menuId}) hidden by default. */\nexport const KERISI_DEFAULT_HIDDEN_ITEM_IDS: readonly string[] = ${JSON.stringify(hiddenItemIds, null, 2)};\n\n/** Level-2 (child) Kerisi IDs hidden by default. */\nexport const KERISI_DEFAULT_HIDDEN_CHILD_IDS: readonly string[] = ${JSON.stringify(hiddenChildIds, null, 2)};\n\n/** Level-3 (grandchild) Kerisi IDs hidden by default. */\nexport const KERISI_DEFAULT_HIDDEN_GRANDCHILD_IDS: readonly string[] = ${JSON.stringify(hiddenGrandchildIds, null, 2)};\n`;

  fs.writeFileSync(outFile, `${header}${treeBody};${idsBlock}`, "utf8");
  console.log(
    "Wrote",
    outFile,
    "roots:",
    tree.length,
    "data rows:",
    rows.filter((r) => !r.virtual).length,
    "synthetic parents:",
    missingParents.size,
    "hidden-by-default:",
    hiddenItemIds.length + hiddenChildIds.length + hiddenGrandchildIds.length,
  );
}

main();
