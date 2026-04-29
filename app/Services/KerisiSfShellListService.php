<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Best-effort list payloads for Kerisi SF shell screens so grids match legacy
 * data sources where the PAGE JSON export omitted BL/datatable wiring.
 *
 * Rows use snake_case keys aligned with legacy dt_key where possible; outbound
 * JSON is camelCased by CamelCaseMiddleware like other APIs.
 */
class KerisiSfShellListService
{
    public function fetch(int $menuId, Request $request): array
    {
        $page = max(1, (int) $request->input('page', 1));
        $limit = max(1, min(100, (int) $request->input('limit', 10)));
        $q = trim((string) $request->input('q', ''));

        try {
            return match ($menuId) {
                1058 => $this->lookupListing('FCATEGORY', $page, $limit, $q),
                1067 => $this->sponsorTypeListing($page, $limit, $q),
                1071 => $this->discountNotePolicyListing($page, $limit, $q),
                1072 => $this->insuranceVendorListing($page, $limit, $q),
                1073 => $this->lookupListing('BARRING_TYPE', $page, $limit, $q),
                1074 => $this->barringPolicyListing($page, $limit, $q),
                1082, 2093 => $this->feeItemListing($page, $limit, $q),
                1083 => $this->feeStructureListing($page, $limit, $q),
                1084, 2750 => $this->academicCalendarListing($page, $limit, $q),
                1025 => $this->sponsorProfileListing($page, $limit, $q),
                1029 => $this->outboundDataListing($page, $limit, $q),
                1030 => $this->receiptBatchListing($page, $limit, $q),
                1038 => $this->insuranceOfferedStudentListing($page, $limit, $q),
                1039 => $this->insuranceNewReturningListing($page, $limit, $q),
                1354 => $this->discountTypeListing($page, $limit, $q),
                1339 => $this->importDataInsuranceStudentListing($request, $page, $limit, $q),
                1507 => $this->ptptnStudentListing($page, $limit, $q),
                1911 => $this->nonFeeStructureListing($page, $limit, $q),
                2020 => $this->sponsorAdvancePaymentListing($page, $limit, $q),
                2556 => $this->barringStudentListing($page, $limit, $q),
                2601 => $this->barringOfferedStudentListing($page, $limit, $q),
                2797 => $this->insuranceReturningIFASListing($page, $limit, $q),
                2799 => $this->insuranceDuplicateListing($page, $limit, $q),
                2802 => $this->bankAccountManualUpdateListing($page, $limit, $q),
                2834 => $this->cnPolicyByEventListing($page, $limit, $q),
                2937 => $this->bankAccountOfferStudentListing($page, $limit, $q),
                // Form-only / detail pages — no list query
                1069, 1070, 1150, 1192, 1193, 1252, 1253, 1255, 1257,
                1278, 1279, 1280, 1298, 1311, 1313, 1323, 1326, 1327, 1328,
                1335, 1491, 1530, 1571, 1576, 1822, 2096, 2390, 2840 => ['rows' => [], 'total' => 0, 'connector' => 'sf_form_only'],
                default => $this->registryShellDefault($menuId, $request, $page, $limit, $q),
            };
        } catch (\Throwable $e) {
            report($e);

            return [
                'rows' => [],
                'total' => 0,
                'connector' => 'registry_shell_error',
                'shellError' => config('app.debug') ? $e->getMessage() : 'secondary_db_query_failed',
            ];
        }
    }

    /**
     * @return array{rows: array<int, array<string, mixed>>, total: int, connector: string, shellError?: string}
     */
    private function registryShellDefault(int $menuId, Request $request, int $page, int $limit, string $q): array
    {
        $report = app(KerisiSfStudentFinanceReportShellService::class);
        if ($report->handles($menuId)) {
            return $report->fetch($menuId, $request, $page, $limit, $q);
        }

        return ['rows' => [], 'total' => 0, 'connector' => 'registry_shell'];
    }

    /**
     * @return array{rows: array<int, array<string, mixed>>, total: int, connector: string}
     */
    private function lookupListing(string $lmaCode, int $page, int $limit, string $q): array
    {
        $conn = DB::connection('mysql_secondary');
        $base = $conn->table('lookup_details')->where('lma_code_name', $lmaCode);

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->where(function ($w) use ($like) {
                $w->whereRaw('LOWER(IFNULL(lde_value, \'\')) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(IFNULL(lde_description, \'\')) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(IFNULL(lde_description2, \'\')) LIKE ?', [$like]);
            });
        }

        $total = (clone $base)->count();
        $rows = (clone $base)
            ->orderBy('lde_sorting')
            ->orderBy('lde_value')
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get([
                'lde_id',
                'lde_value',
                'lde_description',
                'lde_description2',
                'lde_status',
            ]);

        $data = $rows->map(function ($r) {
            $status = (string) ($r->lde_status ?? '');
            $active = in_array($status, ['1', 'Y', 'y', 'A'], true);

            return [
                'lde_id' => $r->lde_id,
                'lde_value' => $r->lde_value,
                'lde_description' => $r->lde_description,
                'lde_description2' => $r->lde_description2,
                'lde_status' => $r->lde_status,
                'dcp_status_desc' => $active ? 'ACTIVE' : 'INACTIVE',
                'details' => trim((string) ($r->lde_value ?? '').' — '.(string) ($r->lde_description ?? '')),
            ];
        })->all();

        return [
            'rows' => $data,
            'total' => $total,
            'connector' => 'lookup_details:'.$lmaCode,
        ];
    }

    /**
     * Sponsor types: prefer lookup_details; fallback to distinct sponsor.spn_sponsor_type.
     *
     * @return array{rows: array<int, array<string, mixed>>, total: int, connector: string}
     */
    private function sponsorTypeListing(int $page, int $limit, string $q): array
    {
        foreach (['SPONSOR_TYPE', 'SPONSORTYPE', 'SPONSOR_CAT'] as $code) {
            $conn = DB::connection('mysql_secondary');
            $probe = $conn->table('lookup_details')->where('lma_code_name', $code)->limit(1)->exists();
            if ($probe) {
                return $this->lookupListing($code, $page, $limit, $q);
            }
        }

        $conn = DB::connection('mysql_secondary');
        $base = $conn->table('sponsor')->selectRaw('DISTINCT spn_sponsor_type AS code');

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw('LOWER(IFNULL(spn_sponsor_type, \'\')) LIKE ?', [$like]);
        }

        $total = $conn->query()->fromSub($base, 't')->count();

        $rows = $conn->query()
            ->fromSub((clone $base), 't')
            ->orderBy('code')
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        $data = collect($rows)->map(fn ($r) => [
            'code' => (string) ($r->code ?? ''),
            'details' => (string) ($r->code ?? ''),
        ])->all();

        return [
            'rows' => $data,
            'total' => $total,
            'connector' => 'sponsor.spn_sponsor_type_distinct',
        ];
    }

    /**
     * Discount structure (1071) / Discount type setup (1354): discount_note_policy.
     *
     * @return array{rows: array<int, array<string, mixed>>, total: int, connector: string}
     */
    private function discountNotePolicyListing(int $page, int $limit, string $q): array
    {
        $conn = DB::connection('mysql_secondary');
        $base = $conn->table('discount_note_policy');

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->where(function ($w) use ($like) {
                $w->whereRaw('LOWER(IFNULL(CAST(dcp_dc_policy_id AS CHAR), \'\')) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(IFNULL(dcp_dc_description, \'\')) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(IFNULL(dcp_dc_type, \'\')) LIKE ?', [$like]);
            });
        }

        $total = (clone $base)->count();

        $rows = (clone $base)
            ->orderBy('dcp_dc_policy_id')
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        $data = $rows->map(function ($r) {
            $o = (array) $r;
            $statusRaw = $o['dcp_status'] ?? null;
            $desc = '';
            if ($statusRaw !== null && $statusRaw !== '') {
                $desc = ((string) $statusRaw === '1' || strtoupper((string) $statusRaw) === 'ACTIVE')
                    ? 'ACTIVE' : 'INACTIVE';
            }

            return array_merge($o, [
                'dcp_status_desc' => $desc !== '' ? $desc : null,
                'details' => trim(implode(' — ', array_filter([
                    (string) ($o['dcp_dc_type'] ?? ''),
                    (string) ($o['dcp_dc_description'] ?? ''),
                ]))),
            ]);
        })->all();

        return [
            'rows' => $data,
            'total' => $total,
            'connector' => 'discount_note_policy',
        ];
    }

    /**
     * Insurance institution — vendors used as insurers (subset of vend_customer_supplier).
     *
     * @return array{rows: array<int, array<string, mixed>>, total: int, connector: string}
     */
    private function insuranceVendorListing(int $page, int $limit, string $q): array
    {
        $conn = DB::connection('mysql_secondary');
        $base = $conn->table('vend_customer_supplier as v');

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->where(function ($w) use ($like) {
                $w->whereRaw('LOWER(IFNULL(v.vcs_vendor_code, \'\')) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(IFNULL(v.vcs_vendor_name, \'\')) LIKE ?', [$like]);
            });
        }

        $total = (clone $base)->count();

        $rows = (clone $base)
            ->orderBy('v.vcs_vendor_code')
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get([
                'v.vcs_vendor_code',
                'v.vcs_vendor_name',
                'v.vcs_vendor_status',
            ]);

        $data = $rows->map(function ($r) {
            $st = (string) ($r->vcs_vendor_status ?? '');
            $label = in_array($st, ['1', 'Y'], true) ? 'ACTIVE' : 'INACTIVE';

            return [
                'vcs_vendor_code' => $r->vcs_vendor_code,
                'vcs_vendor_name' => $r->vcs_vendor_name,
                'status_desc' => $label,
                'details' => trim(($r->vcs_vendor_code ?? '').' — '.($r->vcs_vendor_name ?? '')),
            ];
        })->all();

        return [
            'rows' => $data,
            'total' => $total,
            'connector' => 'vend_customer_supplier_insurance_shell',
        ];
    }

    /**
     * Discount Type setup (MENUID 1354): lookup row if present; otherwise discount_note_policy rows.
     *
     * @return array{rows: array<int, array<string, mixed>>, total: int, connector: string}
     */
    private function discountTypeListing(int $page, int $limit, string $q): array
    {
        foreach (['DISCOUNT_TYPE', 'DC_TYPE', 'DISCOUNT_NOTE_TYPE'] as $code) {
            $conn = DB::connection('mysql_secondary');
            if ($conn->table('lookup_details')->where('lma_code_name', $code)->exists()) {
                return $this->lookupListing($code, $page, $limit, $q);
            }
        }

        return $this->discountNotePolicyListing($page, $limit, $q);
    }

    /**
     * @return array{rows: array<int, array<string, mixed>>, total: int, connector: string}
     */
    private function barringPolicyListing(int $page, int $limit, string $q): array
    {
        $conn = DB::connection('mysql_secondary');
        if (! $this->hasTable($conn->getDatabaseName(), 'barring_policy')) {
            return ['rows' => [], 'total' => 0, 'connector' => 'barring_policy_missing'];
        }

        $base = $conn->table('barring_policy');

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                'LOWER(CONCAT_WS(\'|\', '
                    .'IFNULL(bp_barring_type_desc, \'\'), '
                    .'IFNULL(bp_citizenship_status_desc, \'\'), '
                    .'IFNULL(bp_student_type_desc, \'\'), '
                    .'IFNULL(CAST(bp_minimum_amt AS CHAR), \'\')'
                    .')) LIKE ?',
                [$like]
            );
        }

        $total = (clone $base)->count();

        $rows = (clone $base)
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        $data = collect($rows)->map(function ($r) {
            $arr = (array) $r;

            return array_merge($arr, [
                'details' => trim(implode(' ', array_filter([
                    $arr['bp_barring_type_desc'] ?? null,
                    $arr['bp_minimum_amt'] ?? null,
                ]))),
            ]);
        })->all();

        return [
            'rows' => $data,
            'total' => $total,
            'connector' => 'barring_policy',
        ];
    }

    /**
     * @return array{rows: array<int, array<string, mixed>>, total: int, connector: string}
     */
    private function feeItemListing(int $page, int $limit, string $q): array
    {
        $conn = DB::connection('mysql_secondary');
        if (! $this->hasTable($conn->getDatabaseName(), 'cust_invoice_item')) {
            return ['rows' => [], 'total' => 0, 'connector' => 'cust_invoice_item_missing'];
        }

        $base = $conn->table('cust_invoice_item');

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->where(function ($w) use ($like) {
                $w->whereRaw('LOWER(IFNULL(cii_item_code, \'\')) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(IFNULL(cii_item_description_bm, \'\')) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(IFNULL(cii_item_description_en, \'\')) LIKE ?', [$like]);
            });
        }

        $total = (clone $base)->count();

        $rows = (clone $base)
            ->orderBy('cii_item_code')
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        $data = collect($rows)->map(function ($r) {
            $o = (array) $r;

            return array_merge($o, [
                'details' => trim(($o['cii_item_code'] ?? '').' — '.($o['cii_item_description_bm'] ?? '')),
            ]);
        })->all();

        return [
            'rows' => $data,
            'total' => $total,
            'connector' => 'cust_invoice_item',
        ];
    }

    private function academicCalendarListing(int $page, int $limit, string $q): array
    {
        $conn = DB::connection('mysql_secondary');
        $base = $conn->table('academic_calendar');

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->where(function ($w) use ($like) {
                $w->whereRaw('LOWER(IFNULL(acl_semester_code, \'\')) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(IFNULL(acl_semester_name, \'\')) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(IFNULL(acl_status, \'\')) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(IFNULL(acl_program_level, \'\')) LIKE ?', [$like]);
            });
        }

        $total = (clone $base)->count();

        $rows = (clone $base)
            ->orderBy('acl_semester_code')
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        $data = collect($rows)->map(function ($r) {
            $o = (array) $r;

            return array_merge($o, [
                'details' => trim(($o['acl_semester_code'] ?? '').' — '.($o['acl_semester_name'] ?? '')),
            ]);
        })->all();

        return [
            'rows' => $data,
            'total' => $total,
            'connector' => 'academic_calendar',
        ];
    }

    /**
     * Student Finance / Insurance / Import Data Insurance (MENUID 1339).
     * Legacy BL `DT_NEWSTUDENT_LIST` loaded batch detail by `trxId`. We mirror
     * {@see StudentInsuranceListingController} (we do not apply the same
     * std_status / std_mode_study filter so "new student" and import rows are
     * not hidden if their status codes differ). Optional `trx_id` filters by
     * the first matching column present on `stud_insurance` (e.g. batch/trx).
     *
     * @return array{rows: array<int, array<string, mixed>>, total: int, connector: string}
     */
    private function importDataInsuranceStudentListing(Request $request, int $page, int $limit, string $q): array
    {
        $conn = DB::connection('mysql_secondary');
        $dbName = $conn->getDatabaseName();

        $trxId = trim((string) $request->input('trx_id', ''));

        $base = $conn->table('student as std')
            ->leftJoin('stud_insurance as sin', 'sin.std_student_id', '=', 'std.std_student_id');

        if ($trxId !== '') {
            foreach (['trx_id', 'sin_trx_id', 'batch_trx_id', 'sin_batch_id'] as $trxCol) {
                if ($this->secondaryColumnExists($dbName, 'stud_insurance', $trxCol)) {
                    $base->where('sin.'.$trxCol, $trxId);
                    break;
                }
            }
        }

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                'LOWER(CONCAT_WS(\'|\', '
                    .'IFNULL(std.std_student_id, \'\'), '
                    .'IFNULL(std.std_student_name, \'\'), '
                    .'IFNULL(sin.sin_ins_policy_no, \'\'), '
                    .'IFNULL(sin.vcs_vendor_code_insuran, \'\')'
                    .')) LIKE ?',
                [$like]
            );
        }

        $total = (clone $base)->count();

        $select = [
            'std.std_student_id',
            'std.std_student_name',
            'sin.sin_ins_policy_no',
            'sin.vcs_vendor_code_insuran',
        ];
        if ($this->secondaryColumnExists($dbName, 'student', 'std_intake_semester')) {
            $select[] = 'std.std_intake_semester';
        }
        if ($this->secondaryColumnExists($dbName, 'student', 'std_current_sem')) {
            $select[] = 'std.std_current_sem';
        }

        $startExpr = null;
        $endExpr = null;
        if ($this->secondaryColumnExists($dbName, 'stud_insurance', 'sin_coverage_from')) {
            $startExpr = DB::raw(
                "CASE WHEN sin.sin_coverage_from IS NOT NULL AND sin.sin_coverage_from <> '0000-00-00' "
                    ."THEN DATE_FORMAT(sin.sin_coverage_from, '%d/%m/%Y') ELSE '' END AS start_date"
            );
        }
        if ($this->secondaryColumnExists($dbName, 'stud_insurance', 'sin_coverage_to')) {
            $endExpr = DB::raw(
                "CASE WHEN sin.sin_coverage_to IS NOT NULL AND sin.sin_coverage_to <> '0000-00-00' "
                    ."THEN DATE_FORMAT(sin.sin_coverage_to, '%d/%m/%Y') ELSE '' END AS end_date"
            );
        }

        $selectList = array_merge($select, array_filter([$startExpr, $endExpr]));

        $rows = (clone $base)
            ->select($selectList)
            ->orderBy('std.std_student_id')
            ->orderByRaw('sin.sin_ins_policy_no IS NULL')
            ->orderBy('sin.sin_ins_policy_no')
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        $data = $rows->map(function ($r) {
            $start = isset($r->start_date) ? (string) $r->start_date : '';
            $end = isset($r->end_date) ? (string) $r->end_date : '';
            $semRaw = isset($r->std_intake_semester) ? $r->std_intake_semester : null;
            $sem = is_string($semRaw) && trim($semRaw) !== ''
                ? trim($semRaw)
                : (string) (($r->std_current_sem ?? '') ?: '');

            return [
                'stud_id' => $r->std_student_id,
                'stud_name' => $r->std_student_name,
                'policy_no' => $r->sin_ins_policy_no ?? null,
                'icc' => $r->vcs_vendor_code_insuran ?? null,
                'start_date' => $start,
                'end_date' => $end,
                'sem_start' => $sem,
            ];
        })->all();

        return [
            'rows' => $data,
            'total' => $total,
            'connector' => 'student:stud_insurance_import_shell',
        ];
    }

    private function likeEscape(string $needle): string
    {
        return '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $needle).'%';
    }

    private function hasTable(string $database, string $table): bool
    {
        try {
            $row = DB::connection('mysql_secondary')->selectOne(
                'SELECT 1 FROM information_schema.tables WHERE table_schema = ? AND table_name = ? LIMIT 1',
                [$database, $table]
            );

            return $row !== null;
        } catch (\Throwable) {
            return false;
        }
    }

    private function secondaryColumnExists(string $database, string $table, string $column): bool
    {
        try {
            $row = DB::connection('mysql_secondary')->selectOne(
                'SELECT 1 FROM information_schema.columns WHERE table_schema = ? AND table_name = ? AND column_name = ? LIMIT 1',
                [$database, $table, $column]
            );

            return $row !== null;
        } catch (\Throwable) {
            return false;
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MENUID 1029 — Bill Presentment > Outbound Data list
    // ─────────────────────────────────────────────────────────────────────────
    private function outboundDataListing(int $page, int $limit, string $q): array
    {
        $conn = DB::connection('mysql_secondary');
        $base = $conn->table('outbound_data as od')
            ->join('bill_presentment_master as bpm', 'bpm.bpm_bill_present_master_id', '=', 'od.bpm_bill_present_master_id');

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->where(function ($w) use ($like) {
                $w->whereRaw("LOWER(IFNULL(od.obd_student_id,'')) LIKE ?", [$like])
                    ->orWhereRaw("LOWER(IFNULL(od.obd_student_name,'')) LIKE ?", [$like]);
            });
        }

        $total = (clone $base)->count();
        $rows = (clone $base)
            ->orderByDesc('od.createddate')
            ->skip(($page - 1) * $limit)->take($limit)
            ->get(['od.obd_student_id', 'od.obd_student_name', 'od.bpm_bill_present_master_id', 'od.createddate']);

        return ['rows' => $rows->toArray(), 'total' => $total, 'connector' => 'sf_outbound_data'];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MENUID 1030 — Bill Presentment > Receipt by Batch
    // ─────────────────────────────────────────────────────────────────────────
    private function receiptBatchListing(int $page, int $limit, string $q): array
    {
        $conn = DB::connection('mysql_secondary');
        $base = $conn->table('receipt_batch_master');

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->where(function ($w) use ($like) {
                $w->whereRaw("LOWER(IFNULL(rbm_reference_no,'')) LIKE ?", [$like])
                    ->orWhereRaw("LOWER(IFNULL(rbm_file_name,'')) LIKE ?", [$like]);
            });
        }

        $total = (clone $base)->count();
        $rows = (clone $base)
            ->orderByDesc('rbm_receipt_batch_master_id')
            ->skip(($page - 1) * $limit)->take($limit)
            ->get(['rbm_receipt_batch_master_id', 'rbm_reference_no', 'rbm_bank_date',
                'rbm_file_name', 'rbm_source_cd', 'rbm_status_cd', 'rbm_total_data', 'rbm_total_amt']);

        return ['rows' => $rows->toArray(), 'total' => $total, 'connector' => 'sf_receipt_batch'];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MENUID 1025 — Sponsor > Profile
    // ─────────────────────────────────────────────────────────────────────────
    private function sponsorProfileListing(int $page, int $limit, string $q): array
    {
        $conn = DB::connection('mysql_secondary');
        $base = $conn->table('sponsor');

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->where(function ($w) use ($like) {
                $w->whereRaw("LOWER(IFNULL(spn_sponsor_name,'')) LIKE ?", [$like])
                    ->orWhereRaw("LOWER(IFNULL(spn_contact_person,'')) LIKE ?", [$like])
                    ->orWhereRaw("LOWER(IFNULL(spn_email,'')) LIKE ?", [$like]);
            });
        }

        $total = (clone $base)->count();
        $rows = (clone $base)
            ->orderBy('spn_sponsor_name')
            ->skip(($page - 1) * $limit)->take($limit)
            ->get(['spn_sponsor_id', 'spn_sponsor_code', 'spn_sponsor_name',
                'spn_contact_person', 'spn_email', 'spn_status_cd', 'spn_status_invoice_cd']);

        return ['rows' => $rows->toArray(), 'total' => $total, 'connector' => 'sf_sponsor_profile'];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MENUID 1038 — Insurance > List of Offered Students
    // ─────────────────────────────────────────────────────────────────────────
    private function insuranceOfferedStudentListing(int $page, int $limit, string $q): array
    {
        $conn = DB::connection('mysql_secondary');
        $base = $conn->table('offered_student')
            ->where('ost_citizenship_status', '1')
            ->where('ost_mode_study', '1');

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->where(function ($w) use ($like) {
                $w->whereRaw("LOWER(IFNULL(ost_student_id,'')) LIKE ?", [$like])
                    ->orWhereRaw("LOWER(IFNULL(ost_student_name,'')) LIKE ?", [$like])
                    ->orWhereRaw("LOWER(IFNULL(ost_ic_no,'')) LIKE ?", [$like])
                    ->orWhereRaw("LOWER(IFNULL(ost_offered_semester,'')) LIKE ?", [$like]);
            });
        }

        $total = (clone $base)->count();
        $rows = (clone $base)
            ->orderBy('ost_student_id')
            ->skip(($page - 1) * $limit)->take($limit)
            ->get(['ost_student_id', 'ost_student_name', 'ost_ic_no', 'ost_passport',
                'ost_program_level', 'ost_offered_semester']);

        return ['rows' => $rows->toArray(), 'total' => $total, 'connector' => 'sf_insurance_offered'];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MENUID 1039 — Insurance > New/Returning Student
    // ─────────────────────────────────────────────────────────────────────────
    private function insuranceNewReturningListing(int $page, int $limit, string $q): array
    {
        $conn = DB::connection('mysql_secondary');
        $base = $conn->table('student as s')
            ->join('stud_insurance as si', 'si.std_student_id', '=', 's.std_student_id');

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->where(function ($w) use ($like) {
                $w->whereRaw("LOWER(IFNULL(s.std_student_id,'')) LIKE ?", [$like])
                    ->orWhereRaw("LOWER(IFNULL(s.std_student_name,'')) LIKE ?", [$like]);
            });
        }

        $total = (clone $base)->count();
        $rows = (clone $base)
            ->orderBy('s.std_student_id')
            ->skip(($page - 1) * $limit)->take($limit)
            ->get(['s.std_student_id', 's.std_student_name', 's.std_status',
                's.std_program_level', 's.std_intake_semester',
                'si.vcs_vendor_code_insuran', 'si.sin_ins_policy_no']);

        return ['rows' => $rows->toArray(), 'total' => $total, 'connector' => 'sf_insurance_new_returning'];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MENUID 1083 — Setup > Fee Structure
    // ─────────────────────────────────────────────────────────────────────────
    private function feeStructureListing(int $page, int $limit, string $q): array
    {
        $conn = DB::connection('mysql_secondary');
        $base = $conn->table('fee_structure_master');

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->where(function ($w) use ($like) {
                $w->whereRaw("LOWER(IFNULL(fsm_fee_str_code,'')) LIKE ?", [$like])
                    ->orWhereRaw("LOWER(IFNULL(fsm_fee_str_desc,'')) LIKE ?", [$like])
                    ->orWhereRaw("LOWER(IFNULL(fsm_fee_str_code_cloned,'')) LIKE ?", [$like]);
            });
        }

        $total = (clone $base)->count();
        $rows = (clone $base)
            ->orderBy('fsm_fee_str_code')
            ->skip(($page - 1) * $limit)->take($limit)
            ->get(['fsm_fee_struc_master_id', 'fsm_fee_str_code', 'fsm_fee_str_desc',
                'fsm_program_level', 'fsm_citizenship_status', 'fsm_fee_str_code_cloned',
                'fsm_total_amt', 'fsm_status']);

        return ['rows' => $rows->toArray(), 'total' => $total, 'connector' => 'sf_fee_structure'];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MENUID 1507 — Sponsor > PTPTN (sponsor_type = '05')
    // ─────────────────────────────────────────────────────────────────────────
    private function ptptnStudentListing(int $page, int $limit, string $q): array
    {
        $conn = DB::connection('mysql_secondary');
        $base = $conn->table('student as a')
            ->join('stud_sponsor as b', 'b.std_student_id', '=', 'a.std_student_id')
            ->join('sponsor as c', 'c.spn_sponsor_code', '=', 'b.spn_sponsor_code')
            ->where('c.spn_sponsor_type', '05');

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->where(function ($w) use ($like) {
                $w->whereRaw("LOWER(IFNULL(a.std_student_id,'')) LIKE ?", [$like])
                    ->orWhereRaw("LOWER(IFNULL(a.std_student_name,'')) LIKE ?", [$like])
                    ->orWhereRaw("LOWER(IFNULL(a.std_ic_no,'')) LIKE ?", [$like]);
            });
        }

        $total = (clone $base)->count();
        $rows = (clone $base)
            ->orderBy('a.std_student_id')
            ->skip(($page - 1) * $limit)->take($limit)
            ->get(['a.std_student_id', 'a.std_student_name', 'a.std_ic_no', 'a.std_passport',
                'a.std_status', 'a.std_program_level',
                'b.ssp_warrant_no', 'b.ssp_warrant_amt', 'b.ssp_ptptn_deduction_amt', 'b.ssp_ptptn_balance_amt']);

        return ['rows' => $rows->toArray(), 'total' => $total, 'connector' => 'sf_ptptn_students'];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MENUID 1911 — Setup > Non-Fee Structure
    // ─────────────────────────────────────────────────────────────────────────
    private function nonFeeStructureListing(int $page, int $limit, string $q): array
    {
        $conn = DB::connection('mysql_secondary');
        $base = $conn->table('noninv_struct_master as nsm')
            ->leftJoin('lookup_details as ld', function ($j) {
                $j->on('ld.lde_value', '=', 'nsm.nsm_program_level')
                    ->where('ld.lma_code_name', '=', 'PROGRAM_LEVEL');
            });

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->where(function ($w) use ($like) {
                $w->whereRaw("LOWER(IFNULL(nsm.nsm_fee_str_code,'')) LIKE ?", [$like])
                    ->orWhereRaw("LOWER(IFNULL(nsm.nsm_fee_str_desc,'')) LIKE ?", [$like]);
            });
        }

        $total = (clone $base)->count();
        $rows = (clone $base)
            ->orderBy('nsm.nsm_fee_str_code')
            ->skip(($page - 1) * $limit)->take($limit)
            ->get(['nsm.nsm_id', 'nsm.nsm_fee_str_code', 'nsm.nsm_fee_str_desc',
                'nsm.nsm_program_level', 'ld.lde_description as program_level_desc']);

        return ['rows' => $rows->toArray(), 'total' => $total, 'connector' => 'sf_non_fee_structure'];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MENUID 2020 — Sponsor > Advance Payment
    // ─────────────────────────────────────────────────────────────────────────
    private function sponsorAdvancePaymentListing(int $page, int $limit, string $q): array
    {
        $conn = DB::connection('mysql_secondary');
        $base = $conn->table('deposit_master as dpm')
            ->where('dpm.dpm_payto_type', 'S');

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->where(function ($w) use ($like) {
                $w->whereRaw("LOWER(IFNULL(dpm.vcs_vendor_code,'')) LIKE ?", [$like])
                    ->orWhereRaw("LOWER(IFNULL(dpm.dpm_vendor_name,'')) LIKE ?", [$like])
                    ->orWhereRaw("LOWER(IFNULL(dpm.dpm_deposit_no,'')) LIKE ?", [$like]);
            });
        }

        $total = (clone $base)->count();
        $rows = (clone $base)
            ->orderBy('dpm.dpm_deposit_no')
            ->skip(($page - 1) * $limit)->take($limit)
            ->get(['dpm.dpm_deposit_master_id', 'dpm.vcs_vendor_code', 'dpm.dpm_vendor_name',
                'dpm.dpm_deposit_no', 'dpm.dpm_status']);

        return ['rows' => $rows->toArray(), 'total' => $total, 'connector' => 'sf_sponsor_advance_payment'];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MENUID 2556 — Barring > List of Students
    // ─────────────────────────────────────────────────────────────────────────
    private function barringStudentListing(int $page, int $limit, string $q): array
    {
        $conn = DB::connection('mysql_secondary');
        $base = $conn->table('stud_barring as sb')
            ->join('student as s', 's.std_student_id', '=', 'sb.std_student_id');

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->where(function ($w) use ($like) {
                $w->whereRaw("LOWER(IFNULL(sb.std_student_id,'')) LIKE ?", [$like])
                    ->orWhereRaw("LOWER(IFNULL(s.std_student_name,'')) LIKE ?", [$like]);
            });
        }

        $total = (clone $base)->count();
        $rows = (clone $base)
            ->orderBy('sb.std_student_id')
            ->skip(($page - 1) * $limit)->take($limit)
            ->get(['sb.sbr_id', 'sb.std_student_id', 's.std_student_name',
                's.std_citizenship_status', 's.std_current_sem', 's.std_program_level']);

        return ['rows' => $rows->toArray(), 'total' => $total, 'connector' => 'sf_barring_students'];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MENUID 2601 — Barring > Release Registration Fee (Offered)
    // ─────────────────────────────────────────────────────────────────────────
    private function barringOfferedStudentListing(int $page, int $limit, string $q): array
    {
        $conn = DB::connection('mysql_secondary');
        $base = $conn->table('stud_offered_barring as sob')
            ->join('offered_student as os', 'os.ost_student_id', '=', 'sob.sob_student_id');

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->where(function ($w) use ($like) {
                $w->whereRaw("LOWER(IFNULL(sob.sob_student_id,'')) LIKE ?", [$like])
                    ->orWhereRaw("LOWER(IFNULL(os.ost_student_name,'')) LIKE ?", [$like]);
            });
        }

        $total = (clone $base)->count();
        $rows = (clone $base)
            ->orderBy('sob.sob_student_id')
            ->skip(($page - 1) * $limit)->take($limit)
            ->get(['sob.sob_id', 'sob.sob_student_id', 'os.ost_student_name',
                'os.ost_citizenship_status', 'os.ost_offered_semester', 'os.ost_program_level']);

        return ['rows' => $rows->toArray(), 'total' => $total, 'connector' => 'sf_barring_offered_students'];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MENUID 2797 — Insurance > Returning Students at iFAS
    // ─────────────────────────────────────────────────────────────────────────
    private function insuranceReturningIFASListing(int $page, int $limit, string $q): array
    {
        $conn = DB::connection('mysql_secondary');
        $base = $conn->table('student as a')
            ->join('stud_insurance as b', 'b.std_student_id', '=', 'a.std_student_id')
            ->where('a.std_status', '!=', '01');

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->where(function ($w) use ($like) {
                $w->whereRaw("LOWER(IFNULL(a.std_student_id,'')) LIKE ?", [$like])
                    ->orWhereRaw("LOWER(IFNULL(a.std_student_name,'')) LIKE ?", [$like])
                    ->orWhereRaw("LOWER(IFNULL(b.sin_ins_policy_no,'')) LIKE ?", [$like]);
            });
        }

        $total = (clone $base)->count();
        $rows = (clone $base)
            ->orderBy('a.std_student_id')
            ->skip(($page - 1) * $limit)->take($limit)
            ->get(['a.std_student_id', 'a.std_student_name', 'a.std_status',
                'a.std_program_level', 'a.std_intake_semester',
                'b.vcs_vendor_code_insuran', 'b.sin_ins_policy_no']);

        return ['rows' => $rows->toArray(), 'total' => $total, 'connector' => 'sf_insurance_returning_ifas'];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MENUID 2799 — Insurance > Duplicate/Multiple
    // ─────────────────────────────────────────────────────────────────────────
    private function insuranceDuplicateListing(int $page, int $limit, string $q): array
    {
        $conn = DB::connection('mysql_secondary');
        $base = $conn->table('student as a')
            ->join('stud_insurance as b', 'b.std_student_id', '=', 'a.std_student_id')
            ->whereIn('a.std_student_id', function ($sub) {
                $sub->select('std_student_id')
                    ->from('stud_insurance')
                    ->groupBy('std_student_id')
                    ->havingRaw('COUNT(*) > 1');
            });

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->where(function ($w) use ($like) {
                $w->whereRaw("LOWER(IFNULL(a.std_student_id,'')) LIKE ?", [$like])
                    ->orWhereRaw("LOWER(IFNULL(a.std_student_name,'')) LIKE ?", [$like]);
            });
        }

        $total = (clone $base)->count();
        $rows = (clone $base)
            ->orderBy('a.std_student_id')
            ->skip(($page - 1) * $limit)->take($limit)
            ->get(['a.std_student_id', 'a.std_student_name', 'a.std_status',
                'a.std_program_level', 'a.std_intake_semester',
                'b.vcs_vendor_code_insuran', 'b.sin_ins_policy_no']);

        return ['rows' => $rows->toArray(), 'total' => $total, 'connector' => 'sf_insurance_duplicate'];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MENUID 2802 — Bank Account Update > Manual Update
    // ─────────────────────────────────────────────────────────────────────────
    private function bankAccountManualUpdateListing(int $page, int $limit, string $q): array
    {
        $conn = DB::connection('mysql_secondary');
        $base = $conn->table('student as std')
            ->leftJoin('stud_account as sa', 'sa.std_student_id', '=', 'std.std_student_id');

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->where(function ($w) use ($like) {
                $w->whereRaw("LOWER(IFNULL(std.std_student_id,'')) LIKE ?", [$like])
                    ->orWhereRaw("LOWER(IFNULL(std.std_student_name,'')) LIKE ?", [$like])
                    ->orWhereRaw("LOWER(IFNULL(std.std_ic_no,'')) LIKE ?", [$like])
                    ->orWhereRaw("LOWER(IFNULL(sa.sac_bank_acc_no,'')) LIKE ?", [$like]);
            });
        }

        $total = (clone $base)->count();
        $rows = (clone $base)
            ->orderBy('std.std_student_id')
            ->skip(($page - 1) * $limit)->take($limit)
            ->get(['std.std_student_id', 'std.std_student_name', 'std.std_ic_no', 'std.std_passport',
                'std.std_status', 'sa.sac_bank_acc_no', 'sa.sac_bank_code', 'sa.sac_status']);

        return ['rows' => $rows->toArray(), 'total' => $total, 'connector' => 'sf_bank_account_manual'];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MENUID 2834 — Setup > CN Policy by Event
    // ─────────────────────────────────────────────────────────────────────────
    private function cnPolicyByEventListing(int $page, int $limit, string $q): array
    {
        $conn = DB::connection('mysql_secondary');
        $base = $conn->table('credit_note_policy as cnp')
            ->leftJoin('lookup_cn_policy_type as lct', 'lct.lcn_code', '=', 'cnp.cnp_event_code')
            ->leftJoin('lookup_details as ld', function ($j) {
                $j->on('ld.lde_value', '=', 'cnp.cnp_fee_involved')
                    ->where('ld.lma_code_name', '=', 'FCATEGORY');
            });

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->where(function ($w) use ($like) {
                $w->whereRaw("LOWER(IFNULL(lct.lcn_description,'')) LIKE ?", [$like])
                    ->orWhereRaw("LOWER(IFNULL(cnp.cnp_fee_item,'')) LIKE ?", [$like])
                    ->orWhereRaw("LOWER(IFNULL(ld.lde_description,'')) LIKE ?", [$like]);
            });
        }

        $total = (clone $base)->count();
        $rows = (clone $base)
            ->orderBy('cnp.cnp_cn_policy_id')
            ->skip(($page - 1) * $limit)->take($limit)
            ->get(['cnp.cnp_cn_policy_id', 'cnp.cnp_event_code', 'lct.lcn_description as event_name',
                'cnp.cnp_fee_involved', 'ld.lde_description as fee_category_desc',
                'cnp.cnp_fee_item', 'cnp.cnp_cn_rate', 'cnp.cnp_within_day', 'cnp.cnp_status']);

        return ['rows' => $rows->toArray(), 'total' => $total, 'connector' => 'sf_cn_policy_by_event'];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MENUID 2937 — Bank Account Update > Offer Student
    // ─────────────────────────────────────────────────────────────────────────
    private function bankAccountOfferStudentListing(int $page, int $limit, string $q): array
    {
        $conn = DB::connection('mysql_secondary');
        $base = $conn->table('offered_student as os')
            ->join('deposit_master as dm', function ($j) {
                $j->on('dm.vcs_vendor_code', '=', 'os.ost_student_id')
                    ->where('dm.dpm_payto_type', '=', 'A')
                    ->whereNull('dm.ismigration');
            })
            ->leftJoin('lookup_details as lde', function ($j) {
                $j->on('lde.lde_value', '=', 'os.ost_citizenship_status')
                    ->where('lde.lma_code_name', '=', 'NATIONALITY');
            })
            ->leftJoin('lookup_details as lde2', function ($j) {
                $j->on('lde2.lde_value', '=', 'os.ost_program_level')
                    ->where('lde2.lma_code_name', '=', 'PROGRAM_LEVEL');
            });

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->where(function ($w) use ($like) {
                $w->whereRaw("LOWER(IFNULL(os.ost_student_id,'')) LIKE ?", [$like])
                    ->orWhereRaw("LOWER(IFNULL(os.ost_student_name,'')) LIKE ?", [$like])
                    ->orWhereRaw("LOWER(IFNULL(os.ost_ic_no,'')) LIKE ?", [$like]);
            });
        }

        $total = (clone $base)->count();
        $rows = (clone $base)
            ->orderBy('os.ost_student_id')
            ->skip(($page - 1) * $limit)->take($limit)
            ->get(['os.ost_student_id', 'os.ost_student_name', 'os.ost_program', 'os.ost_program_level',
                'os.ost_faculty_code', 'os.ost_method_study', 'os.ost_mode_study',
                'os.ost_offered_semester', 'os.ost_ic_no', 'os.ost_citizenship_status',
                'lde.lde_description as citizenship_desc',
                'dm.dpm_deposit_no', 'dm.dpm_status']);

        return ['rows' => $rows->toArray(), 'total' => $total, 'connector' => 'sf_bank_account_offer_student'];
    }
}
