import fs from "fs";

const indexPath = "client/src/router/index.ts";
const snippetPath = "scripts/gen-investment-level3-routes-snippet.txt";

let index = fs.readFileSync(indexPath, "utf8");
const snippet = fs.readFileSync(snippetPath, "utf8").trimEnd();

if (!index.includes('import InvestmentLegacyPlaceholderView')) {
  index = index.replace(
    'import ListOfInvestmentsView from "@/views/ListOfInvestmentsView.vue";',
    'import ListOfInvestmentsView from "@/views/ListOfInvestmentsView.vue";\nimport InvestmentLegacyPlaceholderView from "@/views/InvestmentLegacyPlaceholderView.vue";',
  );
}

const old = `    // Hidden Investment — HIDDEN_PAGE_LEVEL2–5 (\`Menu\` paths starting \`Investment>\`).
    ...kerisiInvestmentHiddenRoutes,`;
const next =
  `${snippet}\n    // Hidden Investment — remaining LEVEL2–5 stubs (LEVEL3 explicit routes above).\n    ...kerisiInvestmentHiddenRoutes,`;

if (!index.includes(old)) {
  console.error("Expected Investment hidden block not found.");
  process.exit(1);
}

index = index.replace(old, next);
fs.writeFileSync(indexPath, index);
console.log("patched", indexPath);
