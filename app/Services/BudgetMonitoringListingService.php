<?php

namespace App\Services;

use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Budget / Monitoring / Budget Listing detail tables (PAGEID 1510 / MENUID 1831).
 *
 * Port of legacy `API_BDG_MONITORING_LISTING` (see FLC_BL.json). Uses the
 * `mysql_secondary` connection (DB_SECOND_DATABASE). SQL-shaped joins with
 * {@see BudgetMonitoringQueryService} are kept in this service layer.
 */
class BudgetMonitoringListingService
{
    /** @var list<string> */
    public const SECTIONS = [
        'initial',
        'increment_decrement',
        'virement',
        'prerequisition_v2',
        'requisition_v2',
        'commitment_v2',
        'expenses_v2',
    ];

    private const TASK_IDS = ['05', '06', 'APPROVE', 'REJECT', 'COMPLETE'];

    /**
     * @return array{rows: Collection<int, object>, total: int, footer: array<string, float|string>}
     */
    public function run(string $section, Request $request): array
    {
        $bgdId = trim((string) $request->input('bgd_id', ''));
        $year = trim((string) $request->input('year', ''));
        if ($bgdId === '' || $year === '') {
            return [
                'rows' => collect(),
                'total' => 0,
                'footer' => [],
            ];
        }

        $page = max(1, (int) $request->input('page', 1));
        $limit = max(1, min(100, (int) $request->input('limit', 10)));
        $q = trim((string) $request->input('q', ''));
        $like = $q !== '' ? '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], mb_strtolower($q, 'UTF-8')).'%' : null;

        return match ($section) {
            'initial' => $this->initial($bgdId, $year, $like, $page, $limit, $request),
            'increment_decrement' => $this->incrementDecrement($bgdId, $year, $like, $page, $limit, $request),
            'virement' => $this->virement($bgdId, $year, $like, $page, $limit, $request),
            'prerequisition_v2' => $this->preRequisition($bgdId, $year, $like, $page, $limit, $request),
            'requisition_v2' => $this->requisitionV2($bgdId, $year, $like, $page, $limit, $request),
            'commitment_v2' => $this->commitmentV2($bgdId, $year, $like, $page, $limit, $request),
            'expenses_v2' => $this->expensesV2($bgdId, $year, $like, $page, $limit, $request),
            default => ['rows' => collect(), 'total' => 0, 'footer' => []],
        };
    }

    /**
     * @return array{rows: Collection<int, object>, total: int, footer: array<string, float|string>}
     */
    private function initial(string $bgdId, string $year, ?string $like, int $page, int $limit, Request $request): array
    {
        $sortBy = (string) $request->input('sort_by', 'trans_date');
        $sortDir = strtolower((string) $request->input('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';
        $map = [
            'bdg_year' => 'B.bdg_year',
            'trans_date' => 'BAM.createddate',
            'bdg_ref_id' => 'BAM.bam_allocation_no',
            'bdg_initial_amt' => 'BAD.initial_amt',
        ];
        $orderCol = $map[$sortBy] ?? 'BAM.createddate';

        $base = DB::connection('mysql_secondary')
            ->table('budget as B')
            ->join('budget_allocation_detl as BAD', 'BAD.bad_sbg_id', '=', 'B.sbg_budget_id')
            ->join('budget_allocation_master as BAM', 'BAM.bam_id', '=', 'BAD.bad_master_id')
            ->join('structure_budget as SB', 'B.sbg_budget_id', '=', 'SB.sbg_budget_id')
            ->join('quarter_budget as QB', 'BAM.bam_quarter_id', '=', 'QB.qbu_quarter_id')
            ->where('BAM.bam_status_cd', 'APPROVE')
            ->whereRaw(
                "CONCAT_WS('-', SB.fty_fund_type, SB.at_activity_code, SB.oun_code, SB.ccr_costcentre, SB.lbc_budget_code) = ?",
                [$bgdId]
            )
            ->where('B.bdg_year', $year);

        if ($like !== null) {
            $base->whereRaw(
                "LOWER(CONCAT_WS(0x1F,
                    IFNULL(B.bdg_year,''),
                    CONCAT_WS('-', SB.fty_fund_type, SB.at_activity_code, SB.oun_code, SB.ccr_costcentre, SB.lbc_budget_code),
                    CONCAT_WS(' - ', QB.qbu_quarter_id, IFNULL(QB.qbu_description,'')),
                    IFNULL(BAM.bam_allocation_no,''),
                    IFNULL(BAM.createddate,''),
                    IFNULL(BAD.initial_amt,''),
                    IFNULL(BAM.bam_allocation_no,''),
                    IFNULL(BAM.bam_status_cd,'')
                )) LIKE ?",
                [$like]
            );
        }

        $total = (clone $base)->count('BAD.bad_detl_id');

        $sumRow = (clone $base)->selectRaw('SUM(IFNULL(BAD.initial_amt, 0)) AS amt')->first();
        $footerAmt = (float) ($sumRow->amt ?? 0);

        $rows = (clone $base)
            ->select([
                'B.bdg_year',
                DB::raw("CONCAT_WS('-', SB.fty_fund_type, SB.at_activity_code, SB.oun_code, SB.ccr_costcentre, SB.lbc_budget_code) AS bdg_budget_id"),
                DB::raw("CONCAT_WS(' - ', QB.qbu_quarter_id, QB.qbu_description) AS allocation"),
                'BAM.createddate as trans_date',
                DB::raw('IFNULL(BAD.initial_amt, 0) AS bdg_initial_amt'),
                DB::raw('BAM.bam_allocation_no AS bdg_ref_id'),
                DB::raw('BAM.bam_status_cd AS bdg_status'),
            ])
            ->orderByRaw("$orderCol $sortDir")
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        return [
            'rows' => $rows,
            'total' => $total,
            'footer' => ['bdg_initial_amt' => $footerAmt],
        ];
    }

    private function transactionBase(string $bgdId, string $year, ?string $like, string $systemClause, array $systemBindings = []): Builder
    {
        $q = DB::connection('mysql_secondary')
            ->table('budget_transaction as bt')
            ->join('budget as bdg', function ($j) {
                $j->on('bdg.sbg_budget_id', '=', 'bt.sbg_budget_id')
                    ->on('bdg.bdg_budget_id', '=', 'bt.bdg_budget_id');
            })
            ->join('structure_budget as sb', 'sb.sbg_budget_id', '=', 'bt.sbg_budget_id')
            ->whereIn('bt.bgt_task_id', self::TASK_IDS)
            ->whereRaw(
                "CONCAT_WS('-', sb.fty_fund_type, sb.at_activity_code, sb.oun_code, sb.ccr_costcentre, sb.lbc_budget_code) = ?",
                [$bgdId]
            )
            ->where('bdg.bdg_year', $year);

        if ($systemClause !== '') {
            $q->whereRaw($systemClause, $systemBindings);
        }

        if ($like !== null) {
            $q->whereRaw(
                "LOWER(CONCAT_WS(0x1F,
                    IFNULL(bdg.bdg_year,''),
                    CONCAT_WS('-', sb.fty_fund_type, sb.at_activity_code, sb.oun_code, sb.ccr_costcentre, sb.lbc_budget_code),
                    IFNULL(bt.bgt_trans_date,''),
                    IFNULL(bt.bgt_ref,''),
                    IFNULL(bt.bgt_trans_amt,'')
                )) LIKE ?",
                [$like]
            );
        }

        return $q;
    }

    /**
     * @return array{rows: Collection<int, object>, total: int, footer: array<string, float|string>}
     */
    private function incrementDecrement(string $bgdId, string $year, ?string $like, int $page, int $limit, Request $request): array
    {
        $sortBy = (string) $request->input('sort_by', 'bgt_trans_date');
        $sortDir = strtolower((string) $request->input('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';
        $map = ['bdg_year' => 'bdg.bdg_year', 'bgt_trans_date' => 'bt.bgt_trans_date', 'bgt_ref' => 'bt.bgt_ref', 'bgt_trans_amt' => 'bt.bgt_trans_amt'];
        $orderCol = $map[$sortBy] ?? 'bt.bgt_trans_date';

        $base = $this->transactionBase($bgdId, $year, $like, "bt.bgt_system_id IN ('DECREMENT', 'INCREMENT')");
        $total = (clone $base)->count('bt.bgt_id');
        $sumRow = (clone $base)->selectRaw('SUM(IFNULL(bt.bgt_trans_amt, 0)) AS amt')->first();

        $rows = (clone $base)
            ->select([
                'bdg.bdg_year',
                DB::raw("CONCAT_WS('-', sb.fty_fund_type, sb.at_activity_code, sb.oun_code, sb.ccr_costcentre, sb.lbc_budget_code) AS bdg_budget_id"),
                'bt.bgt_trans_date',
                'bt.bgt_ref',
                DB::raw('IFNULL(bt.bgt_trans_amt, 0) AS bgt_trans_amt'),
            ])
            ->orderByRaw("$orderCol $sortDir")
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        return [
            'rows' => $rows,
            'total' => $total,
            'footer' => ['bgt_trans_amt' => (float) ($sumRow->amt ?? 0)],
        ];
    }

    /**
     * @return array{rows: Collection<int, object>, total: int, footer: array<string, float|string>}
     */
    private function virement(string $bgdId, string $year, ?string $like, int $page, int $limit, Request $request): array
    {
        $sortBy = (string) $request->input('sort_by', 'bgt_trans_date');
        $sortDir = strtolower((string) $request->input('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';
        $map = ['bdg_year' => 'bdg.bdg_year', 'bgt_trans_date' => 'bt.bgt_trans_date', 'bgt_ref' => 'bt.bgt_ref', 'bgt_trans_amt' => 'bt.bgt_trans_amt'];
        $orderCol = $map[$sortBy] ?? 'bt.bgt_trans_date';

        $base = $this->transactionBase($bgdId, $year, $like, "bt.bgt_system_id = 'VIREMENT'");
        $total = (clone $base)->count('bt.bgt_id');
        $sumRow = (clone $base)->selectRaw('SUM(IFNULL(bt.bgt_trans_amt, 0)) AS amt')->first();

        $rows = (clone $base)
            ->select([
                'bdg.bdg_year',
                DB::raw("CONCAT_WS('-', sb.fty_fund_type, sb.at_activity_code, sb.oun_code, sb.ccr_costcentre, sb.lbc_budget_code) AS bdg_budget_id"),
                'bt.bgt_trans_date',
                'bt.bgt_ref',
                DB::raw('IFNULL(bt.bgt_trans_amt, 0) AS bgt_trans_amt'),
            ])
            ->orderByRaw("$orderCol $sortDir")
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        return [
            'rows' => $rows,
            'total' => $total,
            'footer' => ['bgt_trans_amt' => (float) ($sumRow->amt ?? 0)],
        ];
    }

    /**
     * @return array{rows: Collection<int, object>, total: int, footer: array<string, float|string>}
     */
    private function preRequisition(string $bgdId, string $year, ?string $like, int $page, int $limit, Request $request): array
    {
        $sortBy = (string) $request->input('sort_by', 'bgt_trans_date');
        $sortDir = strtolower((string) $request->input('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';
        $map = [
            'sbg_budget_id' => 'sb.sbg_budget_id',
            'bgt_trans_date' => 'bt.bgt_trans_date',
            'bgt_ref' => 'bt.bgt_ref',
            'bgt_trans_amt' => 'bt.bgt_trans_amt',
        ];
        $orderCol = $map[$sortBy] ?? 'bt.bgt_trans_date';

        $base = $this->transactionBase($bgdId, $year, null, "bt.bgt_system_id = 'PRE_REQ'");
        if ($like !== null) {
            $base->whereRaw(
                "LOWER(CONCAT_WS(0x1F,
                    CONCAT_WS('-', sb.fty_fund_type, sb.at_activity_code, sb.oun_code, sb.ccr_costcentre, sb.lbc_budget_code),
                    IFNULL(sb.ccr_costcentre,''),
                    IFNULL(sb.at_activity_code,''),
                    IFNULL(sb.lbc_budget_code,''),
                    IFNULL(bt.bgt_trans_date,''),
                    IFNULL(bt.bgt_ref,''),
                    IFNULL(bt.bgt_trans_amt,'')
                )) LIKE ?",
                [$like]
            );
        }

        $total = (clone $base)->count('bt.bgt_id');
        $sumRow = (clone $base)->selectRaw('SUM(IFNULL(bt.bgt_trans_amt, 0)) AS amt')->first();

        $rows = (clone $base)
            ->select([
                DB::raw("CONCAT_WS('-', sb.fty_fund_type, sb.at_activity_code, sb.oun_code, sb.ccr_costcentre, sb.lbc_budget_code) AS sbg_budget_id"),
                'sb.fty_fund_type',
                'sb.at_activity_code',
                'sb.oun_code',
                'bt.acm_acct_code',
                'sb.lbc_budget_code',
                'sb.ccr_costcentre',
                'bt.bgt_ref',
                'bt.bgt_trans_date',
                DB::raw('IFNULL(bt.bgt_trans_amt, 0) AS bgt_trans_amt'),
            ])
            ->orderByRaw("$orderCol $sortDir")
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        return [
            'rows' => $rows,
            'total' => $total,
            'footer' => ['bgt_trans_amt' => (float) ($sumRow->amt ?? 0)],
        ];
    }

    /**
     * @return array{rows: Collection<int, object>, total: int, footer: array<string, float|string>}
     */
    private function requisitionV2(string $bgdId, string $year, ?string $like, int $page, int $limit, Request $request): array
    {
        $sortBy = (string) $request->input('sort_by', 'bgt_trans_date');
        $sortDir = strtolower((string) $request->input('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';

        $sub = DB::connection('mysql_secondary')
            ->table('budget_transaction as bt')
            ->join('budget as bdg', function ($j) {
                $j->on('bdg.sbg_budget_id', '=', 'bt.sbg_budget_id')
                    ->on('bdg.bdg_budget_id', '=', 'bt.bdg_budget_id');
            })
            ->join('structure_budget as sb', 'sb.sbg_budget_id', '=', 'bt.sbg_budget_id')
            ->leftJoin('requisition_master as rm', 'rm.rqm_requisition_no', '=', 'bt.bgt_ref')
            ->where('bt.bgt_system_id', 'RQUISITION')
            ->whereIn('bt.bgt_task_id', self::TASK_IDS)
            ->whereRaw(
                "CONCAT_WS('-', sb.fty_fund_type, sb.at_activity_code, sb.oun_code, sb.ccr_costcentre, sb.lbc_budget_code) = ?",
                [$bgdId]
            )
            ->where('bdg.bdg_year', $year)
            ->select([
                'bt.bgt_budget_detl_id',
                'bt.bdg_budget_id',
                'bt.sbg_budget_id',
                'sb.fty_fund_type',
                'sb.at_activity_code',
                'sb.oun_code',
                'sb.ccr_costcentre',
                DB::raw('sb.lbc_budget_code AS acm_acct_code'),
                'bt.bgt_trans_date',
                'bt.bgt_system_id',
                'bt.bgt_ref',
                'bt.bgt_trans_type',
                DB::raw('IFNULL(bt.bgt_trans_amt, 0) AS bgt_trans_amt'),
                'bt.pageid',
                'bt.bgt_task_id',
                'bdg.bdg_year',
                'rm.rqm_requisition_no',
            ]);

        if ($like !== null) {
            $sub->whereRaw(
                "LOWER(CONCAT_WS(0x1F,
                    IFNULL(bt.bdg_budget_id,''),
                    IFNULL(bt.sbg_budget_id,''),
                    IFNULL(sb.fty_fund_type,''),
                    IFNULL(sb.at_activity_code,''),
                    IFNULL(sb.oun_code,''),
                    IFNULL(sb.ccr_costcentre,''),
                    IFNULL(sb.lbc_budget_code,''),
                    IFNULL(bt.bgt_trans_date,''),
                    IFNULL(bt.bgt_system_id,''),
                    IFNULL(bt.bgt_ref,''),
                    IFNULL(bt.bgt_trans_type,''),
                    IFNULL(bt.bgt_trans_amt,''),
                    IFNULL(bt.pageid,''),
                    IFNULL(bt.bgt_task_id,''),
                    IFNULL(bdg.bdg_year,''),
                    IFNULL(rm.rqm_requisition_no,'')
                )) LIKE ?",
                [$like]
            );
        }

        $wrapped = DB::connection('mysql_secondary')->query()->fromSub($sub, 'tbl');
        $total = (int) (clone $wrapped)->count();
        $sumRow = (clone $wrapped)->selectRaw('SUM(IFNULL(bgt_trans_amt, 0)) AS amt')->first();

        $orderMap = [
            'bdg_budget_id' => 'bdg_budget_id',
            'bgt_trans_date' => 'bgt_trans_date',
            'bgt_trans_amt' => 'bgt_trans_amt',
        ];
        $orderCol = $orderMap[$sortBy] ?? 'bgt_trans_date';

        $rows = (clone $wrapped)
            ->orderBy($orderCol, $sortDir)
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        return [
            'rows' => $rows,
            'total' => $total,
            'footer' => ['bgt_trans_amt' => (float) ($sumRow->amt ?? 0)],
        ];
    }

    /**
     * @return array{rows: Collection<int, object>, total: int, footer: array<string, float|string>}
     */
    private function commitmentV2(string $bgdId, string $year, ?string $like, int $page, int $limit, Request $request): array
    {
        $sortBy = (string) $request->input('sort_by', 'bgt_trans_date');
        $sortDir = strtolower((string) $request->input('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';
        $map = [
            'bdg_budget_id' => 'bt.bdg_budget_id',
            'bgt_trans_date' => 'bt.bgt_trans_date',
            'bgt_trans_amt' => 'bt.bgt_trans_amt',
        ];
        $orderCol = $map[$sortBy] ?? 'bt.bgt_trans_date';

        $base = $this->transactionBase($bgdId, $year, $like, "bt.bgt_system_id = 'PO'");
        $total = (int) (clone $base)->distinct()->count('bt.bgt_budget_detl_id');
        $sumRow = (clone $base)->selectRaw('SUM(IFNULL(bt.bgt_trans_amt, 0)) AS amt')->first();

        $rows = (clone $base)
            ->select([
                'bt.bgt_budget_detl_id',
                'bt.bdg_budget_id',
                'bt.sbg_budget_id',
                'sb.lbc_budget_code',
                'sb.oun_code',
                'sb.ccr_costcentre',
                'sb.fty_fund_type',
                'sb.at_activity_code',
                'bt.bgt_trans_date',
                'bt.bgt_ref',
                DB::raw('IFNULL(bt.bgt_trans_amt, 0) AS bgt_trans_amt'),
            ])
            ->distinct()
            ->orderByRaw("$orderCol $sortDir")
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        return [
            'rows' => $rows,
            'total' => $total,
            'footer' => ['bgt_trans_amt' => (float) ($sumRow->amt ?? 0)],
        ];
    }

    /**
     * @return array{rows: Collection<int, object>, total: int, footer: array<string, float|string>}
     */
    private function expensesV2(string $bgdId, string $year, ?string $like, int $page, int $limit, Request $request): array
    {
        $sortBy = (string) $request->input('sort_by', 'bgt_trans_date');
        $sortDir = strtolower((string) $request->input('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';

        $inner = DB::connection('mysql_secondary')
            ->table('budget_transaction as bt')
            ->join('structure_budget as sb', 'sb.sbg_budget_id', '=', 'bt.sbg_budget_id')
            ->join('budget as bdg', function ($j) {
                $j->on('bt.sbg_budget_id', '=', 'bdg.sbg_budget_id')
                    ->on('bt.bdg_budget_id', '=', 'bdg.bdg_budget_id');
            })
            ->where('bdg.bdg_status', 'APPROVED')
            ->whereIn('bt.bgt_task_id', self::TASK_IDS)
            ->whereRaw(
                "CONCAT_WS('-', sb.fty_fund_type, sb.at_activity_code, sb.oun_code, sb.ccr_costcentre, sb.lbc_budget_code) = ?",
                [$bgdId]
            )
            ->where('bdg.bdg_year', $year)
            ->whereNotIn('bt.bgt_system_id', ['REVENUE', 'RQUISITION', 'PO', 'INCREMENT', 'DECREMENT', 'VIREMENT', 'PRE_REQ'])
            ->select([
                'bt.bgt_budget_detl_id',
                'bt.bdg_budget_id',
                'bt.sbg_budget_id',
                DB::raw('bt.createddate AS bgt_trans_date'),
                'bt.bgt_system_id',
                'bt.bgt_ref',
                DB::raw('IFNULL(bt.bgt_trans_amt, 0) AS bgt_trans_amt'),
                'sb.oun_code',
                'bt.acm_acct_code',
                DB::raw('sb.lbc_budget_code AS bdg_budget_code'),
                'sb.fty_fund_type',
                'sb.at_activity_code',
                'sb.ccr_costcentre',
            ]);

        $outer = DB::connection('mysql_secondary')->query()->fromSub($inner, 'expenses');
        if ($like !== null) {
            $outer->whereRaw(
                "LOWER(CONCAT_WS(0x1F,
                    IFNULL(bdg_budget_id,''),
                    IFNULL(sbg_budget_id,''),
                    IFNULL(bgt_trans_date,''),
                    IFNULL(bgt_system_id,''),
                    IFNULL(bgt_ref,''),
                    IFNULL(bgt_trans_amt,''),
                    IFNULL(oun_code,''),
                    IFNULL(acm_acct_code,''),
                    IFNULL(bdg_budget_code,''),
                    IFNULL(fty_fund_type,''),
                    IFNULL(at_activity_code,''),
                    IFNULL(ccr_costcentre,'')
                )) LIKE ?",
                [$like]
            );
        }

        $total = (int) (clone $outer)->distinct()->count('bgt_budget_detl_id');
        $sumRow = (clone $outer)->selectRaw('SUM(IFNULL(bgt_trans_amt, 0)) AS amt')->first();

        $orderMap = [
            'bgt_trans_date' => 'bgt_trans_date',
            'bgt_trans_amt' => 'bgt_trans_amt',
        ];
        $orderCol = $orderMap[$sortBy] ?? 'bgt_trans_date';

        $rows = (clone $outer)
            ->orderBy($orderCol, $sortDir)
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        return [
            'rows' => $rows,
            'total' => $total,
            'footer' => ['bgt_trans_amt' => (float) ($sumRow->amt ?? 0)],
        ];
    }
}
