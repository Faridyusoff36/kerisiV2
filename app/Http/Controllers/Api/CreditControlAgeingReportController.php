<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Ageing reports from {@see rep_aging_debtor} (mysql_secondary).
 */
class CreditControlAgeingReportController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $kind = (string) $request->input('kind', '');
        $endRaw = (string) $request->input('tf_end_date', '');
        if ($endRaw === '') {
            return $this->sendError(400, 'BAD_REQUEST', 'tf_end_date (dd/mm/yyyy) is required');
        }
        try {
            $end = Carbon::createFromFormat('d/m/Y', $endRaw)->format('Y-m-d');
        } catch (\Throwable) {
            return $this->sendError(400, 'BAD_REQUEST', 'tf_end_date must be dd/mm/yyyy');
        }

        return match ($kind) {
            'creditor_summary' => $this->creditorSummary($request, $end, false),
            'creditor_summary_ext' => $this->creditorSummary($request, $end, true),
            'creditor_details' => $this->creditorDetails($request, $end, false),
            'creditor_details_ext' => $this->creditorDetails($request, $end, true),
            /** Creditor AP listing (MENUID 3375): subsidiary creditor accounts + posted bill nos only. */
            'creditor_ap_listing' => $this->creditorApListing($request, $end),
            'debtor_summary_ext' => $this->debtorSummary($request, $end),
            'debtor_details_ext' => $this->debtorDetails($request, $end),
            'advance_listing' => $this->advanceListing($request, $end),
            default => $this->sendError(400, 'BAD_REQUEST', 'Invalid kind'),
        };
    }

    private function baseRep(string $end, string $acctGroup): \Illuminate\Database\Query\Builder
    {
        return DB::connection('mysql_secondary')->table('rep_aging_debtor AS rad')
            ->join('account_main AS am', 'rad.acm_acct_code', '=', 'am.acm_acct_code')
            ->where('rad.pde_status', 'APPROVE')
            ->where('am.acm_acct_group', $acctGroup)
            ->whereRaw('DATE(rad.pde_trans_date) <= ?', [$end]);
    }

    private function applyFilters($q, Request $request): void
    {
        $map = [
            'tf_customer_type' => 'rad.pde_payto_type',
            'oun_code' => 'rad.oun_code',
            'fty_fund_type' => 'rad.fty_fund_type',
            'at_activity_code' => 'rad.at_activity_code',
            'ccr_costcentre' => 'rad.ccr_costcentre',
            'tf_customer_id' => 'rad.pde_payto_id',
            'acm_acct_code' => 'rad.acm_acct_code',
            'tf_region' => 'vo.pejabat',
        ];
        foreach ($map as $param => $col) {
            $v = trim((string) $request->input($param, ''));
            if ($v !== '') {
                $q->where($col, $v);
            }
        }
    }

    /** Seven (standard) or eight (split >2y) DATEDIFF placeholders — balance column has none. */
    private function bucketSqlFragment(bool $splitOldBuckets): string
    {
        $sgn = "IF(rad.pde_trans_type = 'DT', rad.pde_trans_amt, -rad.pde_trans_amt)";
        $d = 'DATEDIFF(?, DATE(rad.pde_trans_date))';
        $parts = [
            "SUM({$sgn}) AS balance_as_date",
            "SUM(CASE WHEN ({$d}) BETWEEN 0 AND 30 THEN {$sgn} ELSE 0 END) AS days_0_30",
            "SUM(CASE WHEN ({$d}) BETWEEN 31 AND 60 THEN {$sgn} ELSE 0 END) AS days_31_60",
            "SUM(CASE WHEN ({$d}) BETWEEN 61 AND 90 THEN {$sgn} ELSE 0 END) AS days_61_90",
            "SUM(CASE WHEN ({$d}) BETWEEN 91 AND 180 THEN {$sgn} ELSE 0 END) AS days_91_180",
            "SUM(CASE WHEN ({$d}) BETWEEN 181 AND 365 THEN {$sgn} ELSE 0 END) AS days_6_12_mo",
            "SUM(CASE WHEN ({$d}) BETWEEN 366 AND 730 THEN {$sgn} ELSE 0 END) AS days_12_24_mo",
        ];
        if ($splitOldBuckets) {
            $parts[] = "SUM(CASE WHEN ({$d}) BETWEEN 731 AND 2190 THEN {$sgn} ELSE 0 END) AS days_2_6_yr";
            $parts[] = "SUM(CASE WHEN ({$d}) > 2190 THEN {$sgn} ELSE 0 END) AS days_over_6_yr";
        } else {
            $parts[] = "SUM(CASE WHEN ({$d}) > 730 THEN {$sgn} ELSE 0 END) AS days_over_24_mo";
        }

        return implode(",\n", $parts);
    }

    private function bucketBindings(bool $splitOldBuckets, string $end): array
    {
        $n = $splitOldBuckets ? 8 : 7;

        return array_fill(0, $n, $end);
    }

    private function runGroupedPage(Request $request, \Illuminate\Database\Query\Builder $sub, string $kind): JsonResponse
    {
        $page = max(1, (int) $request->input('page', 1));
        $limit = max(1, min(500, (int) $request->input('limit', 100)));

        $subSql = '('.$sub->toSql().')';
        $subBindings = $sub->getBindings();

        $total = (int) DB::connection('mysql_secondary')->selectOne(
            "SELECT COUNT(*) AS c FROM {$subSql} AS agg",
            $subBindings
        )->c;

        $offset = ($page - 1) * $limit;
        $rows = DB::connection('mysql_secondary')->select(
            "SELECT * FROM {$subSql} AS agg LIMIT ? OFFSET ?",
            array_merge($subBindings, [$limit, $offset])
        );

        $data = collect($rows)->values()->map(fn ($r, int $i) => array_merge(
            ['index' => $offset + $i + 1],
            (array) json_decode(json_encode($r), true)
        ));

        return $this->sendOk($data, [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'totalPages' => (int) ceil($total / max(1, $limit)),
            'kind' => $kind,
        ]);
    }

    private function creditorSummary(Request $request, string $end, bool $extended): JsonResponse
    {
        $frag = $this->bucketSqlFragment($extended);
        $bind = $this->bucketBindings($extended, $end);

        $q = $this->baseRep($end, 'CREDITOR')
            ->leftJoin('v_organization_unit AS vo', 'vo.l4_oun_code', '=', 'rad.oun_code');
        $this->applyFilters($q, $request);

        $cols = [
            DB::raw('vo.pejabat AS region'),
            'rad.oun_code AS ptj',
            'rad.pde_payto_type AS cust_type',
            'rad.pde_payto_id AS id_no',
            'rad.pde_payto_name AS cust_name',
        ];
        if ($extended) {
            $cols[] = DB::raw('rad.fty_fund_type AS fund_type');
        }

        $sub = $q->clone()
            ->select($cols)
            ->selectRaw($frag, $bind);

        $group = $extended
            ? ['vo.pejabat', 'rad.fty_fund_type', 'rad.oun_code', 'rad.pde_payto_type', 'rad.pde_payto_id', 'rad.pde_payto_name']
            : ['vo.pejabat', 'rad.oun_code', 'rad.pde_payto_type', 'rad.pde_payto_id', 'rad.pde_payto_name'];

        $sub->groupBy($group);

        return $this->runGroupedPage($request, $sub, $extended ? 'creditor_summary_ext' : 'creditor_summary');
    }

    private function creditorDetails(Request $request, string $end, bool $extended): JsonResponse
    {
        $frag = $this->bucketSqlFragment($extended);
        $bind = $this->bucketBindings($extended, $end);

        $q = $this->baseRep($end, 'CREDITOR')
            ->leftJoin('v_organization_unit AS vo', 'vo.l4_oun_code', '=', 'rad.oun_code');
        $this->applyFilters($q, $request);

        $sub = $q->clone()
            ->select(
                DB::raw('vo.pejabat AS region'),
                'rad.fty_fund_type AS fund_type',
                'rad.at_activity_code AS activity',
                'rad.oun_code AS ptj',
                'rad.ccr_costcentre AS cost_centre',
                'rad.pde_payto_type AS cust_type',
                'rad.pde_payto_id AS id_no',
                'rad.pde_payto_name AS cust_name',
                'rad.pde_document_no AS document_no',
                'rad.acm_acct_code AS account_code',
                'am.acm_acct_desc AS account_desc',
            )
            ->selectRaw($frag, $bind)
            ->groupBy(
                'vo.pejabat', 'rad.fty_fund_type', 'rad.at_activity_code', 'rad.oun_code', 'rad.ccr_costcentre',
                'rad.pde_payto_type', 'rad.pde_payto_id', 'rad.pde_payto_name', 'rad.pde_document_no',
                'rad.acm_acct_code', 'am.acm_acct_desc',
            );

        return $this->runGroupedPage($request, $sub, $extended ? 'creditor_details_ext' : 'creditor_details');
    }

    /**
     * Creditor AP listing: same bucket grid as extended creditor details, scoped to
     * subsidiary creditor account codes and document numbers present in bills_master
     * (excluding reject/entry/draft/cancel), mirroring legacy SZ/HQL creditor AP reports.
     */
    private function creditorApListing(Request $request, string $end): JsonResponse
    {
        $frag = $this->bucketSqlFragment(true);
        $bind = $this->bucketBindings(true, $end);

        $q = $this->baseRep($end, 'CREDITOR')
            ->leftJoin('v_organization_unit AS vo', 'vo.l4_oun_code', '=', 'rad.oun_code')
            ->whereIn('rad.acm_acct_code', function ($sub) {
                $sub->select('acm_acct_code')->from('account_main')
                    ->where('acm_flag_subsidiary', 'Y')
                    ->where('acm_acct_group', 'CREDITOR');
            })
            ->whereIn('rad.pde_document_no', function ($sub) {
                $sub->select('bim_bills_no')->from('bills_master')
                    ->whereNotIn('bim_Status', ['REJECT', 'ENTRY', 'DRAFT', 'CANCEL']);
            });
        $this->applyFilters($q, $request);

        $sub = $q->clone()
            ->select(
                DB::raw('vo.pejabat AS region'),
                'rad.fty_fund_type AS fund_type',
                'rad.at_activity_code AS activity',
                'rad.oun_code AS ptj',
                'rad.ccr_costcentre AS cost_centre',
                'rad.pde_payto_type AS cust_type',
                'rad.pde_payto_id AS id_no',
                'rad.pde_payto_name AS cust_name',
                'rad.pde_document_no AS document_no',
                'rad.acm_acct_code AS account_code',
                'am.acm_acct_desc AS account_desc',
            )
            ->selectRaw($frag, $bind)
            ->groupBy(
                'vo.pejabat', 'rad.fty_fund_type', 'rad.at_activity_code', 'rad.oun_code', 'rad.ccr_costcentre',
                'rad.pde_payto_type', 'rad.pde_payto_id', 'rad.pde_payto_name', 'rad.pde_document_no',
                'rad.acm_acct_code', 'am.acm_acct_desc',
            );

        return $this->runGroupedPage($request, $sub, 'creditor_ap_listing');
    }

    private function debtorSummary(Request $request, string $end): JsonResponse
    {
        $frag = $this->bucketSqlFragment(true);
        $bind = $this->bucketBindings(true, $end);

        $q = $this->baseRep($end, 'DEBTOR')
            ->leftJoin('v_organization_unit AS vo', 'vo.l4_oun_code', '=', 'rad.oun_code');
        $this->applyFilters($q, $request);

        $sub = $q->clone()
            ->select(
                DB::raw('vo.pejabat AS region'),
                'rad.oun_code AS ptj',
                'rad.pde_payto_type AS cust_type',
                'rad.pde_payto_id AS id_no',
                'rad.pde_payto_name AS cust_name',
                'rad.acm_acct_code AS account_code',
            )
            ->selectRaw($frag, $bind)
            ->groupBy('vo.pejabat', 'rad.oun_code', 'rad.pde_payto_type', 'rad.pde_payto_id', 'rad.pde_payto_name', 'rad.acm_acct_code');

        return $this->runGroupedPage($request, $sub, 'debtor_summary_ext');
    }

    private function debtorDetails(Request $request, string $end): JsonResponse
    {
        $frag = $this->bucketSqlFragment(true);
        $bind = $this->bucketBindings(true, $end);

        $q = $this->baseRep($end, 'DEBTOR')
            ->leftJoin('v_organization_unit AS vo', 'vo.l4_oun_code', '=', 'rad.oun_code');
        $this->applyFilters($q, $request);

        $sub = $q->clone()
            ->select(
                DB::raw('vo.pejabat AS region'),
                'rad.fty_fund_type AS fund_type',
                'rad.at_activity_code AS activity',
                'rad.oun_code AS ptj',
                'rad.ccr_costcentre AS cost_centre',
                'rad.pde_payto_type AS cust_type',
                'rad.pde_payto_id AS id_no',
                'rad.pde_payto_name AS cust_name',
                'rad.pde_document_no AS document_no',
                'rad.acm_acct_code AS account_code',
                'am.acm_acct_desc AS account_desc',
            )
            ->selectRaw($frag, $bind)
            ->groupBy(
                'vo.pejabat', 'rad.fty_fund_type', 'rad.at_activity_code', 'rad.oun_code', 'rad.ccr_costcentre',
                'rad.pde_payto_type', 'rad.pde_payto_id', 'rad.pde_payto_name', 'rad.pde_document_no',
                'rad.acm_acct_code', 'am.acm_acct_desc',
            );

        return $this->runGroupedPage($request, $sub, 'debtor_details_ext');
    }

    /** Placeholder: same bucket grid as creditor details until advance join spec is wired. */
    private function advanceListing(Request $request, string $end): JsonResponse
    {
        return $this->creditorDetails($request, $end, true);
    }
}
