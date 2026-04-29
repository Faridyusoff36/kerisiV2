import fs from "fs";

const indexPath = "client/src/router/index.ts";
const snippetPath = "scripts/gen-portal-level3-routes-snippet.txt";

let index = fs.readFileSync(indexPath, "utf8");
const snippet = fs.readFileSync(snippetPath, "utf8").trimEnd();

if (!index.includes('import PortalLegacyPlaceholderView')) {
  index = index.replace(
    'import SponsorLetterView from "@/views/SponsorLetterView.vue";',
    'import SponsorLetterView from "@/views/SponsorLetterView.vue";\nimport PortalLegacyPlaceholderView from "@/views/PortalLegacyPlaceholderView.vue";',
  );
}

const old = `    // Hidden Portal — HIDDEN_PAGE_LEVEL2–5 (\`Menu\` starts \`Portal>\` excluding \`Portal>Loan\` covered by loan hidden routes).
    ...kerisiPortalHiddenRoutes,`;
const next =
  `${snippet}\n    // Hidden Portal — remaining LEVEL2–5 stubs (LEVEL3 explicit routes above).\n    ...kerisiPortalHiddenRoutes,`;

if (!index.includes(old)) {
  console.error("Expected Portal hidden block not found.");
  process.exit(1);
}

index = index.replace(old, next);
fs.writeFileSync(indexPath, index);
console.log("patched", indexPath);
