import fs from "fs";

const indexPath = "client/src/router/index.ts";
const snippetPath = "scripts/gen-asset-level3-routes-snippet.txt";

let index = fs.readFileSync(indexPath, "utf8");
const snippet = fs.readFileSync(snippetPath, "utf8").trimEnd();

if (!index.includes('import AssetLegacyPlaceholderView')) {
  index = index.replace(
    'import AssetInventoryListView from "@/views/AssetInventoryListView.vue";',
    'import AssetInventoryListView from "@/views/AssetInventoryListView.vue";\nimport AssetLegacyPlaceholderView from "@/views/AssetLegacyPlaceholderView.vue";',
  );
}

const old = `    // Hidden Asset — HIDDEN_PAGE_LEVEL2–5 (\`Menu\` paths starting \`Asset>\`).
    ...kerisiAssetHiddenRoutes,`;
const next =
  `${snippet}\n    // Hidden Asset — remaining LEVEL2–5 stubs (LEVEL3 explicit routes above).\n    ...kerisiAssetHiddenRoutes,`;

if (!index.includes(old)) {
  console.error("Expected Asset hidden block not found.");
  process.exit(1);
}

index = index.replace(old, next);
fs.writeFileSync(indexPath, index);
console.log("patched", indexPath);
