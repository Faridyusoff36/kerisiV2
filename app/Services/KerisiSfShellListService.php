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
                1084, 2750 => $this->academicCalendarListing($page, $limit, $q),
                1354 => $this->discountTypeListing($page, $limit, $q),
                1339 => $this->importDataInsuranceStudentListing($request, $page, $limit, $q),
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
}
