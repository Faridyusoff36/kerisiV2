const fs = require("fs");
const j = JSON.parse(fs.readFileSync("C:/Users/farid/OneDrive/Desktop/KerisiAI/Jason Level 4/ASSET_LEVEL 4.json", "utf8"));
const pid = Number(process.argv[2] || 2891);
const titleNeedle = process.argv[3] || "";
const rows = j.filter((r) => r.PAGEID === pid && r.COMPONENTTYPE === "datatable");
for (const dt of rows) {
  const t = dt.COMPONENTTITLE || "";
  if (titleNeedle && !t.includes(titleNeedle)) continue;
  console.log("=", t, "=", dt.API_BL_NAME || "null");
  const bl = dt["Business Logic (BL) Details"] || "";
  console.log(bl.slice(0, 15000));
  console.log("\n---NEXT---\n");
}
