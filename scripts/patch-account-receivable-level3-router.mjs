import fs from "fs";

const indexPath = "client/src/router/index.ts";
const snippetPath = "scripts/gen-account-receivable-level3-routes-snippet.txt";

let index = fs.readFileSync(indexPath, "utf8");
const snippet = fs.readFileSync(snippetPath, "utf8").trimEnd();

if (!index.includes('import AccountReceivableLegacyPlaceholderView')) {
  index = index.replace(
    'import AccountPayableLegacyPlaceholderView from "@/views/AccountPayableLegacyPlaceholderView.vue";',
    'import AccountPayableLegacyPlaceholderView from "@/views/AccountPayableLegacyPlaceholderView.vue";\nimport AccountReceivableLegacyPlaceholderView from "@/views/AccountReceivableLegacyPlaceholderView.vue";',
  );
}

const ap = "    ...kerisiAccountPayableHiddenRoutes,";
const ar = "    ...kerisiAccountReceivableHiddenRoutes,";
const combined = `${ap}\n${ar}`;
if (!index.includes(combined)) {
  console.error("Expected adjacent AP and AR spreads not found.");
  process.exit(1);
}

index = index.replace(combined, `${ap}\n${snippet}\n${ar}`);
fs.writeFileSync(indexPath, index);
console.log("patched", indexPath);
