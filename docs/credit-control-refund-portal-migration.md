# Credit Control — List of Refund Application (Portal)

Migration notes: Kerisi 1.0 → Kerisi 2.0 (MENUID **2604**).

## Legacy mapping

| Kerisi 1.0 | Kerisi 2.0 |
|------------|------------|
| `SNA_API_LIST_OF_REFUND_CC_STAFF` (`ListOfRefund`) | `GET /api/credit-control/list-of-refund-portal` |
| `JS_ONLOAD_LIST_OF_REFUND_CC` | Vue `CcListOfRefundPortalView.vue` — `onMounted` → `loadRows()` |
| `MM_API_LIST_OF_REFUND_CC_STUDENT` (`checkReject`) | `POST /api/credit-control/list-of-refund-portal/submit-check` (optional pre-check) |
| Checkbox submit / process | `POST /api/credit-control/list-of-refund-portal/submit` |

> **Note:** `API_LIST_OF_REFUND_CC` in some dumps refers to **deposit / refund_process** insert flows, not this portal listing. The portal listing BL is **`SNA_API_LIST_OF_REFUND_CC_STAFF`**.

## Business rules (preserved)

1. **Join** `temp_refund_application` (`tra`) with `refund_prefix_setup` (`rps`) on `acm_acct_code`.
2. **Scope:** `tra_application_no IS NOT NULL`, `tra_status = 'APPLY'`, `tra_payto_type = 'B'`, `tra_status_process IS NULL`, and `tra_process` not completed (`NULL` or `<> 'Y'`).
3. **Prefix:** When column exists, `rps.rps_payto_type = 'B'` (Kerisi 1.0 parity).
4. **Group filter:** Legacy `rps_group_desc IN (user FUG groups)`. Kerisi 2.0 can narrow rows via env `REFUND_PORTAL_RPS_GROUP_CODES` (comma-separated). Map to real RBAC when FIMS groups are synced into users.
5. **Batch submit:** Legacy `checkReject` — selected `tra_id` rows must belong to **exactly one** `tra_application_no` before workflow continues. Submit updates: `tra_status = '1'`, `tra_process = 'Y'`, `tra_status_process = 'DRAFT'` (see legacy `process_details` in `SNA_API_CREDITCONTROL_REQUESTREFUNDSTAFF`). Amount edits in modal are **not** part of this batch endpoint; extend if required.

## API examples

### List

`GET /api/credit-control/list-of-refund-portal?page=1&limit=10&q=vendor&sort_by=application_no&sort_dir=asc`

**200**

```json
{
  "data": [
    {
      "index": 1,
      "traId": 1001,
      "applicationNo": "APP-2026-001",
      "id": "VEND001",
      "name": "ACME SDN BHD",
      "accountCode": "30101001",
      "accountLabel": "30101001 - Creditor Control",
      "referenceNo": "INV-1 - note",
      "applicationDate": "05/05/2026",
      "amountEligibleRefund": 1200.5,
      "status": "APPLY",
      "remark": null,
      "reportUrl": "/admin/kerisi/m/2604?traId=1001"
    }
  ],
  "meta": {
    "page": 1,
    "limit": 10,
    "total": 1,
    "totalPages": 1
  }
}
```

### Submit (staff)

`POST /api/credit-control/list-of-refund-portal/submit`  
`Content-Type: application/json`

```json
{ "tra_ids": [1001, 1002] }
```

**200** — all IDs allowed and single application number:

```json
{ "data": { "updated": 2, "tra_application_no": "APP-2026-001" } }
```

**400** — multiple application numbers among selection:

```json
{
  "error": {
    "code": "BAD_REQUEST",
    "message": "Selected rows must belong to exactly one application number."
  }
}
```

### Pre-check (optional, legacy `checkReject`)

`POST /api/credit-control/list-of-refund-portal/submit-check`

```json
{ "tra_ids": [1001, 1002] }
```

**200**

```json
{ "data": { "ok": true, "distinct_application_count": 1 } }
```

## JS onload process (Kerisi 2.0)

1. Router loads `CcListOfRefundPortalView`.
2. `onMounted`: register click-outside, wire `datatableRef.getExportConfig`, call `loadRows()`.
3. `loadRows()` sets `loading`, builds `URLSearchParams` (`page`, `limit`, `q`, `sort_by`, `sort_dir`), calls `listCreditControlListOfRefundPortal`.
4. Response rows bound to table; pagination + search debounce refresh the same call.

## Role permission flow

| Layer | Current Kerisi 2.0 |
|-------|---------------------|
| API | Authenticated via `auth:sanctum` (same group as other CC Level 4 GET routes). |
| Row scope | Optional `REFUND_PORTAL_RPS_GROUP_CODES` to mirror `rps_group_desc IN (...)`. |
| Future | Add `Permission::CREDIT_CONTROL_REFUND_PORTAL_*` and `permission:` middleware on `POST submit` when roles are seeded. |

## Migration checklist

- [ ] Confirm `refund_prefix_setup` columns: `rps_payto_type`, `rps_group_desc` (add or skip via `Schema::hasColumn` in code).
- [ ] Set `REFUND_PORTAL_RPS_GROUP_CODES` if non-admin staff must see a subset of prefix rows.
- [ ] Validate `temp_refund_application` columns used in update: `tra_status`, `tra_process`, `tra_status_process`, `updateddate`, `updatedby`.
- [ ] UAT: search, sort, pagination, empty list, submit one application (multi-row), submit multi-application (expect 400).
- [ ] Performance: index `(tra_status, tra_payto_type, tra_process, tra_status_process, tra_application_no)` if lists are large.
- [ ] Train staff: selections must be one application per submit (legacy rule).

## Datatable binding (Vue)

- Column fields map from API snake/camel as in `CcListOfRefundPortalRow`.
- Checkboxes: `selectedIds` + “Select all on page”.
- Submit button: disabled if `selectedIds.length === 0` or `submitting`; calls `submitCreditControlRefundPortalBatch` then `loadRows()` and clears selection.
