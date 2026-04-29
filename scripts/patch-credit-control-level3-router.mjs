import fs from "fs";

const indexPath = "client/src/router/index.ts";
const snippetPath = "scripts/gen-credit-control-level3-routes-snippet.txt";

let index = fs.readFileSync(indexPath, "utf8");
const snippet = fs.readFileSync(snippetPath, "utf8").trimEnd();

if (!index.includes("import CreditControlLegacyPlaceholderView")) {
  index = index.replace(
    'import ListOfDepositView from "@/views/ListOfDepositView.vue";',
    'import ListOfDepositView from "@/views/ListOfDepositView.vue";\nimport CreditControlLegacyPlaceholderView from "@/views/CreditControlLegacyPlaceholderView.vue";',
  );
}

const old = `    // Hidden Credit Control — HIDDEN_PAGE_LEVEL2–5 (\`Menu\` starts \`Credit Control>\`).
    ...kerisiCreditControlHiddenRoutes,`;
const next =
  `${snippet}\n    // Hidden Credit Control — remaining LEVEL2–5 stubs (LEVEL3 explicit routes above).\n    ...kerisiCreditControlHiddenRoutes,`;

if (!index.includes(old)) {
  console.error("Expected Credit Control hidden block not found.");
  process.exit(1);
}

index = index.replace(old, next);
fs.writeFileSync(indexPath, index);
console.log("patched", indexPath);
