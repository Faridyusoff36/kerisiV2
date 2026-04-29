<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * List payloads for Kerisi Student Finance → Report shell routes (see
 * config/kerisi_student_finance_report_shell.php). Best-effort parity with legacy
 * BL/datatables; unknown menus fall back to a wide invoice+student row shape.
 */
class KerisiSfStudentFinanceReportShellService
{
    public function handles(int $menuId): bool
    {
        return in_array($menuId, config('kerisi_student_finance_report_shell.menu_ids', []), true);
    }

    /**
     * @return array{rows: array<int, array<string, mixed>>, total: int, connector: string}
     */
    public function fetch(int $menuId, Request $request, int $page, int $limit, string $q): array
    {
        return match ($menuId) {
            1262 => $this->shellReportInvoiceListingGeneral($request, $page, $limit, $q),
            1284, 1285, 1286 => $this->shellEmptyRegistryNoGrid($menuId),
            1538 => $this->shellInsuranceReportListing($request, $page, $limit, $q),
            1539 => $this->shellStatisticByCategory($request, $page, $limit, $q),
            1550 => $this->shellSponsorInvoiceDataListing($request, $page, $limit, $q),
            1790 => $this->shellSummaryFeesBySemester($request, $page, $limit, $q),
            1794 => $this->shellAccommodationFeesReport($request, $page, $limit, $q),
            1795 => $this->shellAccommodationInasisReport($request, $page, $limit, $q),
            1797 => $this->mymohesFeesDataListing($request, $page, $limit, $q),
            1799 => $this->shellActiveStudentsNotInvoiced($request, $page, $limit, $q),
            1916 => $this->shellListOfSponsorReport($page, $limit, $q),
            1918, 1923, 1924 => $this->shellStudentSponsorListing($menuId, $request, $page, $limit, $q),
            default => $this->shellInvoiceStudentReportWideDefault($menuId, $request, $page, $limit, $q),
        };
    }

    /**
     * @return array{rows: array<int, array<string, mixed>>, total: int, connector: string}
     */
    private function shellEmptyRegistryNoGrid(int $menuId): array
    {
        return [
            'rows' => [],
            'total' => 0,
            'connector' => 'report_shell_no_datatable:'.$menuId,
        ];
    }

    private function invoiceShellCoreBase(): Builder
    {
        $coll = 'COLLATE utf8mb4_unicode_ci';

        return DB::connection('mysql_secondary')
            ->table('cust_invoice_master as cim')
            ->leftJoin('student as std', function ($join) use ($coll) {
                $join->on(
                    DB::raw("cim.cim_cust_id {$coll}"),
                    '=',
                    DB::raw("std.std_student_id {$coll}"),
                );
            })
            ->whereIn('cim.cim_cust_type', ['A', 'E'])
            ->where(function ($w) {
                $w->whereNull('cim.cim_system_id')
                    ->orWhereIn('cim.cim_system_id', ['STUD_INV', 'SF_SPON_INV']);
            })
            ->whereExists(function ($sub) {
                $sub->select(DB::raw(1))
                    ->from('cust_invoice_details as cid0')
                    ->whereColumn('cid0.cim_cust_invoice_id', 'cim.cim_cust_invoice_id')
                    ->where('cid0.cid_transaction_type', 'DT');
            });
    }

    /**
     * MYMOHES-style smart filters: sf_0 program level, sf_1 semester, sf_2 fee category code.
     */
    private function applyProgramSemesterCategorySmartFilters(Builder $base, Request $request): void
    {
        $sf0 = trim((string) $request->input('sf_0', ''));
        $sf1 = trim((string) $request->input('sf_1', ''));
        $sf2 = trim((string) $request->input('sf_2', ''));

        if ($sf0 !== '') {
            $base->where('std.std_program_level', $sf0);
        }
        if ($sf1 !== '') {
            $base->where('cim.cim_semester_id', $sf1);
        }
        if ($sf2 !== '') {
            $base->whereExists(function ($sub) use ($sf2) {
                $sub->select(DB::raw(1))
                    ->from('cust_invoice_details as cidf')
                    ->whereColumn('cidf.cim_cust_invoice_id', 'cim.cim_cust_invoice_id')
                    ->where('cidf.cid_transaction_type', 'DT')
                    ->where('cidf.cii_item_category', $sf2);
            });
        }
    }

    private function applyInvoiceShellGlobalSearch(Builder $base, string $q): void
    {
        if ($q === '') {
            return;
        }
        $needle = mb_strtolower($q, 'UTF-8');
        $like = $this->likeEscape($needle);
        $base->whereRaw(
            'LOWER(CONCAT_WS(\'|\', '
                .'IFNULL(cim.cim_invoice_no, \'\'), '
                .'IFNULL(cim.cim_cust_id, \'\'), '
                .'IFNULL(cim.cim_cust_name, \'\'), '
                .'IFNULL(cim.cim_semester_id, \'\')'
                .')) LIKE ?',
            [$like]
        );
    }

    private function categoryDescriptionSubquery(): string
    {
        return '(SELECT ld.lde_description FROM cust_invoice_details cid2 '
            .'INNER JOIN lookup_details ld ON ld.lma_code_name = \'FCATEGORY\' AND ld.lde_value = cid2.cii_item_category '
            .'WHERE cid2.cim_cust_invoice_id = cim.cim_cust_invoice_id AND cid2.cid_transaction_type = \'DT\' '
            .'ORDER BY cid2.cid_cust_invoice_detl_id ASC LIMIT 1)';
    }

    private function firstFeeItemCodeSubquery(): string
    {
        return '(SELECT cid3.cii_item_code FROM cust_invoice_details cid3 '
            .'WHERE cid3.cim_cust_invoice_id = cim.cim_cust_invoice_id AND cid3.cid_transaction_type = \'DT\' '
            .'ORDER BY cid3.cid_cust_invoice_detl_id ASC LIMIT 1)';
    }

    /**
     * @return array{rows: array<int, array<string, mixed>>, total: int, connector: string}
     */
    private function mymohesFeesDataListing(Request $request, int $page, int $limit, string $q): array
    {
        $base = $this->invoiceShellCoreBase();
        $this->applyProgramSemesterCategorySmartFilters($base, $request);
        $this->applyInvoiceShellGlobalSearch($base, $q);

        $total = (clone $base)->count();

        $categoryExpr = $this->categoryDescriptionSubquery();

        $rows = (clone $base)
            ->select([
                'cim.cim_invoice_no',
                'cim.cim_cust_id',
                'cim.cim_cust_name',
                'cim.cim_invoice_date',
                'cim.cim_nett_amt',
                DB::raw("({$categoryExpr}) as category_desc"),
            ])
            ->orderByDesc('cim.cim_invoice_date')
            ->orderBy('cim.cim_invoice_no')
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        $data = $rows->map(function ($r) {
            $rawDate = $r->cim_invoice_date ?? null;
            $invoiceDate = '';
            if ($rawDate !== null && $rawDate !== '') {
                $ts = strtotime((string) $rawDate);
                $invoiceDate = $ts ? date('d/m/Y', $ts) : (string) $rawDate;
            }

            return [
                'invoice_no' => $r->cim_invoice_no,
                'matric_no' => $r->cim_cust_id,
                'student_name' => $r->cim_cust_name,
                'invoice_date' => $invoiceDate,
                'fees' => $r->cim_nett_amt !== null
                    ? number_format((float) $r->cim_nett_amt, 2, '.', '')
                    : '0.00',
                'category_desc' => (string) ($r->category_desc ?? ''),
            ];
        })->all();

        return [
            'rows' => $data,
            'total' => $total,
            'connector' => 'cust_invoice_master:mymohes_report',
        ];
    }

    /**
     * Invoice — Report Listing General (1262). Smart: status, fee category, fee item.
     *
     * @return array{rows: array<int, array<string, mixed>>, total: int, connector: string}
     */
    private function shellReportInvoiceListingGeneral(Request $request, int $page, int $limit, string $q): array
    {
        $sf0 = trim((string) $request->input('sf_0', ''));
        $sf1 = trim((string) $request->input('sf_1', ''));
        $sf2 = trim((string) $request->input('sf_2', ''));

        $base = $this->invoiceShellCoreBase();

        if ($sf0 !== '') {
            $base->where('cim.cim_status', $sf0);
        }
        if ($sf1 !== '') {
            $base->whereExists(function ($sub) use ($sf1) {
                $sub->select(DB::raw(1))
                    ->from('cust_invoice_details as cidf')
                    ->whereColumn('cidf.cim_cust_invoice_id', 'cim.cim_cust_invoice_id')
                    ->where('cidf.cid_transaction_type', 'DT')
                    ->where('cidf.cii_item_category', $sf1);
            });
        }
        if ($sf2 !== '') {
            $likeItem = $this->likeEscape(mb_strtolower($sf2, 'UTF-8'));
            $base->whereExists(function ($sub) use ($likeItem) {
                $sub->select(DB::raw(1))
                    ->from('cust_invoice_details as cidf')
                    ->whereColumn('cidf.cim_cust_invoice_id', 'cim.cim_cust_invoice_id')
                    ->where('cidf.cid_transaction_type', 'DT')
                    ->whereRaw('LOWER(IFNULL(cidf.cii_item_code, \'\')) LIKE ?', [$likeItem]);
            });
        }

        $this->applyInvoiceShellGlobalSearch($base, $q);

        $total = (clone $base)->distinct()->count(DB::raw('cim.cim_cust_invoice_id'));

        $catSq = $this->categoryDescriptionSubquery();
        $itemSq = $this->firstFeeItemCodeSubquery();

        $rows = (clone $base)
            ->select([
                'cim.cim_invoice_no',
                'cim.cim_invoice_date',
                'cim.cim_status',
                'cim.cim_cust_id',
                'cim.cim_cust_name',
                'cim.cim_cust_type',
                'cim.cim_semester_id',
                'cim.cim_batch_no',
                'cim.cim_nett_amt',
                'cim.cim_bal_amt',
                DB::raw("cim.cim_extended_field->>'\$.fee_code' as fi_kod"),
                DB::raw("({$catSq}) as fee_category"),
                DB::raw("({$itemSq}) as fee_item_code"),
                DB::raw('(CASE WHEN cim.cim_nett_amt IS NOT NULL AND cim.cim_bal_amt IS NOT NULL '
                    .'THEN (cim.cim_nett_amt - cim.cim_bal_amt) ELSE NULL END) as paid_like_amt'),
            ])
            ->distinct()
            ->orderByDesc('cim.cim_invoice_date')
            ->orderBy('cim.cim_invoice_no')
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        $data = $rows->map(function ($r) {
            $rawDate = $r->cim_invoice_date ?? null;
            $invoiceDate = '';
            if ($rawDate !== null && $rawDate !== '') {
                $ts = strtotime((string) $rawDate);
                $invoiceDate = $ts ? date('d/m/Y', $ts) : (string) $rawDate;
            }
            $custTypeLabel = $r->cim_cust_type === 'E' ? 'PENAJA' : 'PELAJAR';

            return [
                'invoice_no' => $r->cim_invoice_no,
                'invoice_date' => $invoiceDate,
                'invoice_status' => $r->cim_status,
                'customer_id' => $r->cim_cust_id,
                'customer_name' => $r->cim_cust_name,
                'customer_type' => $custTypeLabel,
                'fee_category' => (string) ($r->fee_category ?? ''),
                'fee_code' => (string) ($r->fee_item_code ?? ''),
                'cim_semester_id' => $r->cim_semester_id,
                'cim_batch_no' => $r->cim_batch_no,
                'amount' => $r->cim_nett_amt !== null ? number_format((float) $r->cim_nett_amt, 2, '.', '') : '',
                'cid_paid_amt' => $r->paid_like_amt !== null
                    ? number_format((float) $r->paid_like_amt, 2, '.', '')
                    : '',
                'cid_bal_amt' => $r->cim_bal_amt !== null
                    ? number_format((float) $r->cim_bal_amt, 2, '.', '')
                    : '',
                'fi_kod' => (string) ($r->fi_kod ?? ''),
            ];
        })->all();

        return [
            'rows' => $data,
            'total' => $total,
            'connector' => 'cust_invoice_master:invoice_report_general',
        ];
    }

    /**
     * @return array{rows: array<int, array<string, mixed>>, total: int, connector: string}
     */
    private function shellListOfSponsorReport(int $page, int $limit, string $q): array
    {
        $base = DB::connection('mysql_secondary')->table('sponsor as s');

        if ($q !== '') {
            $needle = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                'LOWER(CONCAT_WS(\'__\', '
                    .'IFNULL(s.spn_sponsor_code, \'\'), '
                    .'IFNULL(s.spn_sponsor_name, \'\'), '
                    .'IF(s.spn_status_cd = \'1\', \'active\', \'inactive\'), '
                    .'IF(s.spn_status_invoice_cd = \'1\', \'yes\', \'no\')'
                    .')) LIKE ?',
                [$needle]
            );
        }

        $total = (clone $base)->count();

        $rows = (clone $base)
            ->orderBy('s.spn_sponsor_code')
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get([
                's.spn_sponsor_code',
                's.spn_sponsor_name',
                DB::raw('IF(s.spn_status_cd = \'1\', \'Active\', \'Inactive\') AS status_label'),
                DB::raw('IF(s.spn_status_invoice_cd = \'1\', \'Yes\', \'No\') AS claim_label'),
            ]);

        $data = collect($rows)->map(fn ($r) => [
            'spn_sponsor_code' => $r->spn_sponsor_code,
            'spn_sponsor_name' => $r->spn_sponsor_name,
            'status' => $r->status_label ?? '',
            'claim' => $r->claim_label ?? '',
        ])->all();

        return [
            'rows' => $data,
            'total' => $total,
            'connector' => 'sponsor:list_of_sponsor_report',
        ];
    }

    /**
     * @return array{rows: array<int, array<string, mixed>>, total: int, connector: string}
     */
    private function shellInsuranceReportListing(Request $request, int $page, int $limit, string $q): array
    {
        $base = DB::connection('mysql_secondary')
            ->table('student as A')
            ->leftJoin('stud_insurance as B', 'A.std_student_id', '=', 'B.std_student_id')
            ->leftJoin('vend_customer_supplier as V', 'V.vcs_vendor_code', '=', 'B.vcs_vendor_code_insuran')
            ->whereIn('A.std_status', ['01', '24'])
            ->where('A.std_mode_study', '1');

        $sf0 = trim((string) $request->input('sf_0', ''));
        $sf1 = trim((string) $request->input('sf_1', ''));
        if ($sf0 !== '') {
            $base->whereRaw(
                "LOWER(IFNULL(A.std_extended_field->>'$.std_status_desc','')) LIKE ?",
                [$this->likeEscape(mb_strtolower($sf0, 'UTF-8'))]
            );
        }
        if ($sf1 !== '') {
            $base->whereRaw(
                'LOWER(CONCAT(IFNULL(A.std_program_level,\'\'), IFNULL(A.std_extended_field->>\'$.std_program_level_desc\' ,\'\'))) LIKE ?',
                [$this->likeEscape(mb_strtolower($sf1, 'UTF-8'))]
            );
        }

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                'LOWER(CONCAT_WS(\'|\', '
                    ."IFNULL(A.std_student_id, ''), IFNULL(A.std_student_name, ''), "
                    ."IFNULL(A.std_extended_field->>'$.std_status_desc', ''), "
                    ."IFNULL(B.sin_ins_policy_no, ''), IFNULL(B.vcs_vendor_code_insuran, '')"
                    .')) LIKE ?',
                [$like]
            );
        }

        $total = (clone $base)->count();

        $rows = (clone $base)
            ->select([
                'A.std_student_id',
                'A.std_student_name',
                DB::raw("IFNULL(A.std_extended_field->>'$.std_status_desc','') AS stud_status_desc"),
                DB::raw('A.std_method_study AS student_category_code'),
                DB::raw(
                    "CONCAT_WS(' - ', NULLIF(A.std_program_level,''), NULLIF(A.std_extended_field->>'$.std_program_level_desc','')) AS prog_level_label"
                ),
                'B.vcs_vendor_code_insuran',
                'V.vcs_vendor_name',
                'B.sin_ins_policy_no',
            ])
            ->orderBy('A.std_student_id')
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        $data = $rows->map(fn ($r) => [
            'matric_no' => (string) $r->std_student_id,
            'name' => (string) $r->std_student_name,
            'status' => (string) ($r->stud_status_desc ?? ''),
            'student_category' => (string) ($r->student_category_code ?? ''),
            'proglevel' => (string) ($r->prog_level_label ?? ''),
            'ins_code' => (string) ($r->vcs_vendor_code_insuran ?? ''),
            'ins_name' => (string) ($r->vcs_vendor_name ?? ''),
            'policy_no' => (string) ($r->sin_ins_policy_no ?? ''),
            'coverage_from' => '',
            'coverage_to' => '',
        ])->all();

        return [
            'rows' => $data,
            'total' => $total,
            'connector' => 'student:insurance_report_listing',
        ];
    }

    /**
     * Statistic Record By Category (1539). Inner query projects a stable status string;
     * outer query aggregates and optional text search runs on aliases (no HAVING on std.*).
     *
     * @return array{rows: array<int, array<string, mixed>>, total: int, connector: string}
     */
    private function shellStatisticByCategory(Request $request, int $page, int $limit, string $q): array
    {
        $sf0 = trim((string) $request->input('sf_0', ''));

        $inner = DB::connection('mysql_secondary')
            ->table('student as std')
            ->select([
                'std.std_current_sem',
                'std.std_method_study',
                DB::raw(
                    "TRIM(COALESCE(std.std_extended_field->>'$.std_status_desc', '')) AS status_desc"
                ),
            ]);

        if ($sf0 !== '') {
            $inner->where('std.std_method_study', $sf0);
        }

        $aggregated = DB::connection('mysql_secondary')
            ->query()
            ->fromSub($inner, 's')
            ->select([
                's.std_current_sem',
                's.std_method_study',
                's.status_desc',
                DB::raw('COUNT(*) AS cnt'),
            ])
            ->groupBy([
                's.std_current_sem',
                's.std_method_study',
                's.status_desc',
            ]);

        $conn = DB::connection('mysql_secondary');
        $outer = $conn->query()->fromSub($aggregated, 'g');

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $outer->whereRaw(
                'LOWER(CONCAT_WS(\'|\', '
                    .'IFNULL(g.std_current_sem, \'\'), '
                    .'IFNULL(g.std_method_study, \'\'), '
                    .'IFNULL(g.status_desc, \'\')'
                    .')) LIKE ?',
                [$like]
            );
        }

        $total = (clone $outer)->count();

        $rows = (clone $outer)
            ->orderBy('std_current_sem')
            ->orderBy('std_method_study')
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        $data = collect($rows)->map(fn ($r) => [
            'std_current_sem' => $r->std_current_sem,
            'std_study_category' => $r->std_method_study,
            'status_desc' => $r->status_desc ?? '',
            'count_stud' => (string) ($r->cnt ?? '0'),
        ])->all();

        return [
            'rows' => $data,
            'total' => $total,
            'connector' => 'student:statistic_by_category',
        ];
    }

    /**
     * Sponsor-type invoices (customer type E).
     *
     * @return array{rows: array<int, array<string, mixed>>, total: int, connector: string}
     */
    private function shellSponsorInvoiceDataListing(Request $request, int $page, int $limit, string $q): array
    {
        $base = $this->invoiceShellCoreBase()->where('cim.cim_cust_type', 'E');
        $this->applyProgramSemesterCategorySmartFilters($base, $request);
        $this->applyInvoiceShellGlobalSearch($base, $q);

        $total = (clone $base)->distinct()->count(DB::raw('cim.cim_cust_invoice_id'));

        $catSq = $this->categoryDescriptionSubquery();

        $rows = (clone $base)
            ->select([
                'cim.cim_cust_id',
                'cim.cim_cust_name',
                DB::raw('IFNULL(NULLIF(std.std_ic_no, \'\'), std.std_passport) AS ic_passport'),
                'cim.cim_our_ref',
                'cim.cim_invoice_no',
                'cim.cim_invoice_date',
                'cim.cim_status',
                'cim.cim_semester_id',
                'cim.cim_nett_amt',
                'cim.cim_bal_amt',
                DB::raw("({$catSq}) as category_desc"),
            ])
            ->distinct()
            ->orderByDesc('cim.cim_invoice_date')
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        $data = $rows->map(function ($r) {
            $d = $r->cim_invoice_date ? date('d/m/Y', strtotime((string) $r->cim_invoice_date)) : '';
            $paid = ($r->cim_nett_amt !== null && $r->cim_bal_amt !== null)
                ? (float) $r->cim_nett_amt - (float) $r->cim_bal_amt
                : null;

            return [
                'matric' => $r->cim_cust_id,
                'name' => $r->cim_cust_name,
                'ic_passport' => (string) ($r->ic_passport ?? ''),
                'cim_our_ref' => $r->cim_our_ref,
                'invoice_no' => $r->cim_invoice_no,
                'invoice_date' => $d,
                'invoice_status' => $r->cim_status,
                'customer_id' => $r->cim_cust_id,
                'customer_name' => $r->cim_cust_name,
                'cim_semester_id' => $r->cim_semester_id,
                'amount' => $r->cim_nett_amt !== null ? number_format((float) $r->cim_nett_amt, 2, '.', '') : '',
                'cid_paid_amt' => $paid !== null ? number_format($paid, 2, '.', '') : '',
                'cid_bal_amt' => $r->cim_bal_amt !== null ? number_format((float) $r->cim_bal_amt, 2, '.', '') : '',
                'category' => (string) ($r->category_desc ?? ''),
            ];
        })->all();

        return [
            'rows' => $data,
            'total' => $total,
            'connector' => 'cust_invoice_master:sponsor_invoice_listing',
        ];
    }

    /**
     * Aggregated summary (best-effort; not identical to legacy NF_BL_SF_SUMMARY_REPORT).
     *
     * @return array{rows: array<int, array<string, mixed>>, total: int, connector: string}
     */
    private function shellSummaryFeesBySemester(Request $request, int $page, int $limit, string $q): array
    {
        $tf0 = trim((string) $request->input('tf_0', ''));
        $tf1 = trim((string) $request->input('tf_1', ''));

        $base = DB::connection('mysql_secondary')
            ->table('cust_invoice_master as cim')
            ->join('student as std', 'std.std_student_id', '=', 'cim.cim_cust_id')
            ->join('cust_invoice_details as cid', function ($j) {
                $j->on('cid.cim_cust_invoice_id', '=', 'cim.cim_cust_invoice_id')
                    ->where('cid.cid_transaction_type', '=', 'DT');
            })
            ->whereIn('cim.cim_cust_type', ['A', 'E'])
            ->where(function ($w) {
                $w->whereNull('cim.cim_system_id')
                    ->orWhereIn('cim.cim_system_id', ['STUD_INV', 'SF_SPON_INV']);
            });

        if ($tf0 !== '') {
            $base->where('std.std_program_level', $tf0);
        }
        if ($tf1 !== '') {
            $base->where('cim.cim_semester_id', $tf1);
        }

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                'LOWER(CONCAT_WS(\'|\', '
                    .'IFNULL(std.std_program_level, \'\'), '
                    .'IFNULL(cim.cim_semester_id, \'\'), '
                    .'IFNULL(cid.cii_item_category, \'\')'
                    .')) LIKE ?',
                [$like]
            );
        }

        $aggregated = $base->clone()
            ->select([
                'std.std_program_level AS proglevel',
                'std.std_faculty_code AS center_study',
                DB::raw(
                    "(CASE WHEN std.std_citizenship_country IN ('MY','MYS','MALAYSIA') THEN 'CITIZEN' ELSE 'NON CITIZEN' END) AS citizen_bucket"
                ),
                'std.std_college_code AS ptj_college',
                'cim.cim_semester_id AS semester',
                'cid.cii_item_category AS cat_code',
                DB::raw('SUM(cim.cim_nett_amt) AS total_amt'),
                DB::raw('COUNT(DISTINCT cim.cim_cust_invoice_id) AS bill_cnt'),
            ])
            ->groupBy(
                'std.std_program_level',
                'std.std_faculty_code',
                DB::raw("(CASE WHEN std.std_citizenship_country IN ('MY','MYS','MALAYSIA') THEN 'CITIZEN' ELSE 'NON CITIZEN' END)"),
                'std.std_college_code',
                'cim.cim_semester_id',
                'cid.cii_item_category'
            );

        $conn = DB::connection('mysql_secondary');
        $total = $conn->query()->fromSub($aggregated, 'agg')->count();

        $rows = $conn->query()
            ->fromSub($aggregated, 'agg')
            ->leftJoin('lookup_details as ld', function ($j) {
                $j->where('ld.lma_code_name', '=', 'FCATEGORY')
                    ->whereColumn('ld.lde_value', '=', 'agg.cat_code');
            })
            ->select([
                'agg.proglevel',
                'agg.center_study',
                'agg.citizen_bucket AS status',
                'agg.ptj_college AS PTJ',
                'agg.semester',
                DB::raw('COALESCE(ld.lde_description, agg.cat_code) AS categorydesc'),
                'agg.bill_cnt AS student_bill',
                'agg.total_amt AS amount',
            ])
            ->orderBy('agg.semester')
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        $data = collect($rows)->map(fn ($r) => [
            'proglevel' => $r->proglevel,
            'center_study' => $r->center_study,
            'status' => $r->status,
            'PTJ' => $r->PTJ,
            'semester' => $r->semester,
            'categorydesc' => (string) ($r->categorydesc ?? ''),
            'student_bill' => (string) ($r->student_bill ?? '0'),
            'amount' => $r->amount !== null ? number_format((float) $r->amount, 2, '.', '') : '',
        ])->all();

        return [
            'rows' => $data,
            'total' => $total,
            'connector' => 'aggregate:summary_fees_by_semester',
        ];
    }

    /**
     * @return array{rows: array<int, array<string, mixed>>, total: int, connector: string}
     */
    private function shellAccommodationFeesReport(Request $request, int $page, int $limit, string $q): array
    {
        $base = $this->invoiceShellCoreBase();
        $this->applyProgramSemesterCategorySmartFilters($base, $request);
        $this->applyInvoiceShellGlobalSearch($base, $q);

        $ccSq = '(SELECT cidz.cid_extended_field->>\'$.ccr_costcentre_charged_desc\' FROM cust_invoice_details cidz '
            .'WHERE cidz.cim_cust_invoice_id = cim.cim_cust_invoice_id AND cidz.cid_transaction_type = \'DT\' '
            .'ORDER BY cidz.cid_cust_invoice_detl_id ASC LIMIT 1)';

        $total = (clone $base)->count();

        $catSq = $this->categoryDescriptionSubquery();

        $rows = (clone $base)
            ->select([
                'cim.cim_invoice_no',
                'cim.cim_cust_id',
                'cim.cim_cust_name',
                DB::raw("({$ccSq}) AS costcentre_desc"),
                'cim.cim_status',
                DB::raw("({$catSq}) AS category_desc"),
                'cim.cim_nett_amt',
            ])
            ->orderByDesc('cim.cim_invoice_date')
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        $data = collect($rows)->map(fn ($r) => [
            'invoice_no' => $r->cim_invoice_no,
            'student_id' => $r->cim_cust_id,
            'student_name' => $r->cim_cust_name,
            'costcentre_desc' => (string) ($r->costcentre_desc ?? ''),
            'status' => $r->cim_status,
            'category' => (string) ($r->category_desc ?? ''),
            'amount' => $r->cim_nett_amt !== null ? number_format((float) $r->cim_nett_amt, 2, '.', '') : '',
        ])->all();

        return [
            'rows' => $data,
            'total' => $total,
            'connector' => 'cust_invoice_master:accommodation_fees',
        ];
    }

    /**
     * @return array{rows: array<int, array<string, mixed>>, total: int, connector: string}
     */
    private function shellAccommodationInasisReport(Request $request, int $page, int $limit, string $q): array
    {
        $base = DB::connection('mysql_secondary')
            ->table('cust_invoice_master as cim')
            ->join('cust_invoice_details as cid', function ($j) {
                $j->on('cid.cim_cust_invoice_id', '=', 'cim.cim_cust_invoice_id')
                    ->where('cid.cid_transaction_type', '=', 'DT');
            })
            ->whereIn('cim.cim_cust_type', ['A', 'E'])
            ->where(function ($w) {
                $w->whereNull('cim.cim_system_id')
                    ->orWhereIn('cim.cim_system_id', ['STUD_INV', 'SF_SPON_INV']);
            })
            ->select([
                DB::raw("cid.cid_extended_field->>'$.ccr_costcentre_charged_desc' AS costcentre_desc"),
                DB::raw("cid.cid_extended_field->>'$.at_activity_code_desc' AS activity_code"),
                'cim.cim_cust_id',
                DB::raw('SUM(cim.cim_nett_amt) AS total_amt'),
            ])
            ->groupBy([
                DB::raw("cid.cid_extended_field->>'$.ccr_costcentre_charged_desc'"),
                DB::raw("cid.cid_extended_field->>'$.at_activity_code_desc'"),
                'cim.cim_cust_id',
            ]);

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->where(function ($w) use ($like) {
                $w->whereRaw(
                    "LOWER(IFNULL(cid.cid_extended_field->>'$.ccr_costcentre_charged_desc','')) LIKE ?",
                    [$like]
                )->orWhereRaw(
                    "LOWER(IFNULL(cid.cid_extended_field->>'$.at_activity_code_desc','')) LIKE ?",
                    [$like]
                )->orWhereRaw('LOWER(IFNULL(cim.cim_cust_id, \'\')) LIKE ?', [$like]);
            });
        }

        $conn = DB::connection('mysql_secondary');
        $total = $conn->query()->fromSub(clone $base, 't')->count();

        $rows = $conn->query()
            ->fromSub($base, 't')
            ->orderBy('t.costcentre_desc')
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        $data = collect($rows)->map(fn ($r) => [
            'costcentre_desc' => (string) ($r->costcentre_desc ?? ''),
            'activity_code' => (string) ($r->activity_code ?? ''),
            'cust_id' => (string) ($r->cim_cust_id ?? ''),
            'total_amount' => isset($r->total_amt) ? number_format((float) $r->total_amt, 2, '.', '') : '',
        ])->all();

        return [
            'rows' => $data,
            'total' => $total,
            'connector' => 'aggregate:accommodation_inasis',
        ];
    }

    /**
     * @return array{rows: array<int, array<string, mixed>>, total: int, connector: string}
     */
    private function shellActiveStudentsNotInvoiced(Request $request, int $page, int $limit, string $q): array
    {
        $sf0 = trim((string) $request->input('sf_0', ''));
        $sf1 = trim((string) $request->input('sf_1', ''));

        $base = DB::connection('mysql_secondary')->table('student as std')
            ->where('std.std_status', '01')
            ->whereNotExists(function ($sub) use ($sf1) {
                $sub->select(DB::raw(1))
                    ->from('cust_invoice_master as cim')
                    ->whereColumn('cim.cim_cust_id', 'std.std_student_id')
                    ->whereIn('cim.cim_cust_type', ['A', 'E'])
                    ->where(function ($w) {
                        $w->whereNull('cim.cim_system_id')
                            ->orWhereIn('cim.cim_system_id', ['STUD_INV', 'SF_SPON_INV']);
                    });
                if ($sf1 !== '') {
                    $sub->where('cim.cim_semester_id', $sf1);
                }
            });

        if ($sf0 !== '') {
            $like = $this->likeEscape(mb_strtolower($sf0, 'UTF-8'));
            $base->whereRaw('LOWER(IFNULL(std.std_faculty_code, \'\')) LIKE ?', [$like]);
        }

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                'LOWER(CONCAT_WS(\'|\', '
                    .'IFNULL(std.std_student_id, \'\'), '
                    .'IFNULL(std.std_student_name, \'\'), '
                    .'IFNULL(std.std_ic_no, \'\'), '
                    .'IFNULL(std.std_program_level, \'\')'
                    .')) LIKE ?',
                [$like]
            );
        }

        $total = (clone $base)->count();

        $rows = (clone $base)
            ->select([
                'std.std_student_id',
                'std.std_student_name',
                'std.std_citizenship_status',
                'std.std_program_level',
                'std.std_faculty_code',
                'std.std_college_code',
                'std.std_current_sem',
                DB::raw("std.std_extended_field->>'$.std_status_desc' AS status_desc"),
                'std.std_outstanding_amt',
            ])
            ->orderBy('std.std_student_id')
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        $data = collect($rows)->map(function ($r) {
            return [
                'matric_no' => $r->std_student_id,
                'student_name' => $r->std_student_name,
                'citizenship' => $r->std_citizenship_status,
                'program' => $r->std_program_level,
                'faculty' => $r->std_faculty_code,
                'college_ptj' => $r->std_college_code,
                'bill_sem' => $r->std_current_sem,
                'status' => (string) ($r->status_desc ?? ''),
                'category' => '',
                'fees' => $r->std_outstanding_amt !== null
                    ? number_format((float) $r->std_outstanding_amt, 2, '.', '')
                    : '',
            ];
        })->all();

        return [
            'rows' => $data,
            'total' => $total,
            'connector' => 'student:active_not_invoiced',
        ];
    }

    /**
     * Student + stud_sponsor + sponsor (+ optional period cover).
     *
     * @return array{rows: array<int, array<string, mixed>>, total: int, connector: string}
     */
    private function shellStudentSponsorListing(int $menuId, Request $request, int $page, int $limit, string $q): array
    {
        $base = DB::connection('mysql_secondary')
            ->table('student as std')
            ->join('stud_sponsor as ss', 'ss.std_student_id', '=', 'std.std_student_id')
            ->join('sponsor as sp', 'sp.spn_sponsor_code', '=', 'ss.spn_sponsor_code')
            ->leftJoin('stud_sponsor_period_cover as spc', 'spc.ssp_id', '=', 'ss.ssp_id');

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                'LOWER(CONCAT_WS(\'|\', '
                    .'IFNULL(std.std_student_id, \'\'), '
                    .'IFNULL(std.std_student_name, \'\'), '
                    .'IFNULL(std.std_ic_no, \'\'), '
                    .'IFNULL(std.std_passport, \'\'), '
                    .'IFNULL(sp.spn_sponsor_code, \'\'), '
                    .'IFNULL(sp.spn_sponsor_name, \'\')'
                    .')) LIKE ?',
                [$like]
            );
        }

        foreach ($request->except(['page', 'limit', 'q', 'sort_by', 'sort_dir']) as $key => $value) {
            if (preg_match('/^sf_\d+$/', (string) $key) && trim((string) $value) !== '') {
                // Registry drives many optional filters; apply loose OR semantics only when needed.
            }
        }

        $total = (int) (clone $base)->select(DB::raw('COUNT(DISTINCT std.std_student_id) AS __cnt'))->value('__cnt');

        $rows = (clone $base)
            ->select([
                'std.std_student_id',
                DB::raw('IFNULL(NULLIF(std.std_ic_no,\'\'), std.std_passport) AS ic_passport'),
                'std.std_student_name',
                'std.std_program_level',
                DB::raw("std.std_extended_field->>'$.std_status_desc' AS stud_status"),
                'sp.spn_sponsor_code',
                'sp.spn_sponsor_name',
                DB::raw('IF(sp.spn_status_cd = \'1\', \'ACTIVE\', \'INACTIVE\') AS sponsor_active'),
                'std.std_current_sem',
                'spc.spc_date_from',
                'spc.spc_date_to',
            ])
            ->orderBy('std.std_student_id')
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        $data = collect($rows)->map(fn ($r) => [
            'std_student_id' => $r->std_student_id,
            'ic_passport' => (string) ($r->ic_passport ?? ''),
            'std_student_name' => $r->std_student_name,
            'program' => $r->std_program_level,
            'stud_status' => (string) ($r->stud_status ?? ''),
            'spn_sponsor_code' => $r->spn_sponsor_code,
            'spn_sponsor_name' => $r->spn_sponsor_name,
            'sponsor_status' => (string) ($r->sponsor_active ?? ''),
            'std_current_sem' => $r->std_current_sem,
            'spc_date_from' => $r->spc_date_from,
            'spc_date_to' => $r->spc_date_to,
            'sponsor_code' => $r->spn_sponsor_code,
            'sponsor_name' => $r->spn_sponsor_name,
            'student_status' => (string) ($r->stud_status ?? ''),
            'semester' => $r->std_current_sem,
            'start_date' => $r->spc_date_from,
            'end_date' => $r->spc_date_to,
            'claim_amount' => '',
            'cim_invoice_no' => '',
            'cim_invoice_date' => '',
        ])->all();

        return [
            'rows' => $data,
            'total' => $total,
            'connector' => 'stud_sponsor:listing:'.$menuId,
        ];
    }

    /**
     * Wide invoice + student payload for remaining report menus (1798, 1800–1816, 1949+, …).
     *
     * @return array{rows: array<int, array<string, mixed>>, total: int, connector: string}
     */
    private function shellInvoiceStudentReportWideDefault(int $menuId, Request $request, int $page, int $limit, string $q): array
    {
        $base = $this->invoiceShellCoreBase();
        $this->applyProgramSemesterCategorySmartFilters($base, $request);
        $this->applyInvoiceShellGlobalSearch($base, $q);

        $total = (clone $base)->distinct()->count(DB::raw('cim.cim_cust_invoice_id'));

        $catSq = $this->categoryDescriptionSubquery();
        $itemSq = $this->firstFeeItemCodeSubquery();

        $rows = (clone $base)
            ->select([
                'cim.cim_invoice_no',
                'cim.cim_invoice_date',
                'cim.cim_status',
                'cim.cim_cust_id',
                'cim.cim_cust_name',
                'cim.cim_semester_id',
                'cim.cim_batch_no',
                'cim.cim_nett_amt',
                'cim.cim_bal_amt',
                'std.std_program_level',
                'std.std_program',
                'std.std_faculty_code',
                'std.std_college_code',
                'std.std_gs_code',
                'std.std_study_center',
                DB::raw('IFNULL(NULLIF(std.std_ic_no,\'\'), std.std_passport) AS ic_no'),
                'std.std_citizenship_status',
                'std.std_method_study',
                DB::raw("std.std_extended_field->>'$.std_status_desc' AS student_status_desc"),
                DB::raw("({$catSq}) AS fee_category_desc"),
                DB::raw("({$itemSq}) AS fee_item_code"),
                DB::raw("cim.cim_extended_field->>'\$.fee_code' AS fi_kod"),
            ])
            ->distinct()
            ->orderByDesc('cim.cim_invoice_date')
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        $data = $rows->map(function ($r) {
            $invDate = '';
            if (! empty($r->cim_invoice_date)) {
                $ts = strtotime((string) $r->cim_invoice_date);
                $invDate = $ts ? date('d/m/Y', $ts) : (string) $r->cim_invoice_date;
            }
            $paid = ($r->cim_nett_amt !== null && $r->cim_bal_amt !== null)
                ? (float) $r->cim_nett_amt - (float) $r->cim_bal_amt
                : null;
            $feesFmt = $r->cim_nett_amt !== null ? number_format((float) $r->cim_nett_amt, 2, '.', '') : '';

            return [
                'invoice_no' => $r->cim_invoice_no,
                'matric_no' => $r->cim_cust_id,
                'student_id' => $r->cim_cust_id,
                'student_name' => $r->cim_cust_name,
                'invoice_date' => $invDate,
                'program' => $r->std_program_level ?? $r->std_program,
                'faculty' => $r->std_faculty_code,
                'ptj' => $r->std_college_code,
                'category' => (string) ($r->fee_category_desc ?? ''),
                'cim_batch_no' => $r->cim_batch_no,
                'fees' => $feesFmt,
                'amount' => $feesFmt,
                'ic_no' => (string) ($r->ic_no ?? ''),
                'citizenship_status' => (string) ($r->std_citizenship_status ?? ''),
                'citizenship' => (string) ($r->std_citizenship_status ?? ''),
                'semester' => $r->cim_semester_id,
                'semester_id' => $r->cim_semester_id,
                'status' => (string) ($r->student_status_desc ?? $r->cim_status ?? ''),
                'invoice_status' => (string) ($r->cim_status ?? ''),
                'graduate_school' => (string) ($r->std_gs_code ?? ''),
                'college' => (string) ($r->std_college_code ?? ''),
                'study_center' => (string) ($r->std_study_center ?? ''),
                'prog_level' => (string) ($r->std_program_level ?? ''),
                'program_level' => (string) ($r->std_program_level ?? ''),
                'fee_item_code' => (string) ($r->fee_item_code ?? ''),
                'item' => (string) ($r->fee_item_code ?? ''),
                'sub_item' => '',
                'fund_type' => '',
                'activity' => '',
                'cost_centre' => '',
                'bal_amt' => $r->cim_bal_amt !== null ? number_format((float) $r->cim_bal_amt, 2, '.', '') : '',
                'paid_amt' => $paid !== null ? number_format($paid, 2, '.', '') : '',
                'room_number' => '',
                'gender' => '',
                'date' => $invDate,
                'senate_date' => '',
                'graduate_date' => '',
                'batch_invoice' => $r->cim_batch_no,
                'faculty_code' => $r->std_faculty_code,
            ];
        })->all();

        return [
            'rows' => $data,
            'total' => $total,
            'connector' => 'cust_invoice_master:invoice_student_wide:'.$menuId,
        ];
    }

    private function likeEscape(string $needle): string
    {
        return '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $needle).'%';
    }
}
