import fs from "fs";

const pageId = Number(process.argv[2] || 0);
const j = JSON.parse(
  fs.readFileSync("C:/KerisiAI/02MigrateFromOldKerisi/JSON_DATA/HIDDEN_PAGE_LEVEL2.json", "utf8"),
);
const rows = j.filter((r) => Number(r.PAGEID) === pageId);
for (const r of rows) {
  const o = { ...r };
  const bl = o["Business Logic (BL) Details"];
  o["Business Logic (BL) Details"] = typeof bl === "string" ? `${bl.slice(0, 400)}…` : bl;
  o.COMPONENT_JS = typeof o.COMPONENT_JS === "string" ? `${o.COMPONENT_JS.slice(0, 400)}…` : o.COMPONENT_JS;
  const dt = o["Datatable column details"];
  o["Datatable column details"] = typeof dt === "string" ? `${dt.slice(0, 500)}…` : dt;
  console.log(JSON.stringify(o, null, 2));
  console.log("---");
}
