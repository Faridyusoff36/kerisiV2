<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\Budget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Budget > Reports > Total Allocation Report (PAGEID 1626 / MENUID 1968).
 *
 * Source BL: SWS_DT_REPORT_TOTAL_ALLOCATION. The legacy report aggregates
 * `budget` ledger rows (bdg_initial_amt + bdg_topup_amt + bdg_virement_amt
 * = total allocation) joined with `structure_budget` for the fund /
 * activity / cost-centre / budget-code dimensions, and rolls up by year +
 * fund + PTJ. It is read-only.
 *
 * Top filter: Year (required to render the report).
 * Smart filter: Fund Type / Activity / OUN / CCR / Budget Code.
 */
class TotalAllocationReportController extends Controller
{
    use ApiResponse;

    private const SORTABLE = [
        'rptYear' => 'B.bdg_year',
        'rptFund' => 'SB.fty_fund_type',
        'rptActivity' => 'SB.at_activity_code',
        'rptOun' => 'SB.oun_code',
        'rptCcr' => 'SB.ccr_costcentre',
        'rptBudgetCode' => 'SB.lbc_budget_code',
        'rptInitial' => 'B.bdg_initial_amt',
        'rptTopup' => 'B.bdg_topup_amt',
        'rptVirement' => 'B.bdg_virement_amt',
        'rptTotal' => 'B.bdg_initial_amt',
    ];

    public function index(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->input('page', 1));
        $limit = max(1, min(500, (int) $request->input('limit', 10)));
        $sortKey = (string) $request->input('sort_by', 'rptYear');
        $sortDir = strtolower((string) $request->input('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';
        $sortCol = self::SORTABLE[$sortKey] ?? self::SORTABLE['rptYear'];

        $query = $this->baseQuery($request);

        $total = (clone $query)->count('B.bdg_budget_id');

        $rows = (clone $query)
            ->select([
                'B.bdg_budget_id',
                'B.bdg_year',
                'B.bdg_initial_amt',
                'B.bdg_topup_amt',
                'B.bdg_virement_amt',
                'B.bdg_balance_amt',
                'SB.fty_fund_type',
                'SB.at_activity_code',
                'SB.oun_code',
                'SB.ccr_costcentre',
                'SB.lbc_budget_code',
                'AT.at_activity_description_bm',
                'OU.oun_desc',
                'CC.ccr_costcentre_desc',
                'LBC.lbc_description',
                'FT.fty_fund_desc',
            ])
            ->orderBy($sortCol, $sortDir)
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        $baseIndex = ($page - 1) * $limit;
        $data = $rows->values()->map(function ($row, $idx) use ($baseIndex) {
            $initial = (float) ($row->bdg_initial_amt ?? 0);
            $topup = (float) ($row->bdg_topup_amt ?? 0);
            $virement = (float) ($row->bdg_virement_amt ?? 0);

            return [
                'index' => $baseIndex + $idx + 1,
                'rptYear' => $row->bdg_year,
                'rptFund' => $row->fty_fund_type,
                'rptFundDesc' => $row->fty_fund_desc,
                'rptActivity' => $row->at_activity_code,
                'rptActivityDesc' => $row->at_activity_description_bm,
                'rptOun' => $row->oun_code,
                'rptOunDesc' => $row->oun_desc,
                'rptCcr' => $row->ccr_costcentre,
                'rptCcrDesc' => $row->ccr_costcentre_desc,
                'rptBudgetCode' => $row->lbc_budget_code,
                'rptBudgetCodeDesc' => $row->lbc_description,
                'rptInitial' => $initial,
                'rptTopup' => $topup,
                'rptVirement' => $virement,
                'rptTotal' => $initial + $topup + $virement,
                'rptBalance' => $row->bdg_balance_amt !== null ? (float) $row->bdg_balance_amt : null,
            ];
        });

        // Footer totals across the filtered (not paginated) set.
        $totals = (clone $query)
            ->selectRaw('SUM(B.bdg_initial_amt) as initial_total, SUM(B.bdg_topup_amt) as topup_total, SUM(B.bdg_virement_amt) as virement_total')
            ->first();

        return $this->sendOk($data, [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'totalPages' => (int) ceil(max(1, $total) / $limit),
            'totals' => [
                'initial' => $totals?->initial_total !== null ? (float) $totals->initial_total : 0.0,
                'topup' => $totals?->topup_total !== null ? (float) $totals->topup_total : 0.0,
                'virement' => $totals?->virement_total !== null ? (float) $totals->virement_total : 0.0,
                'grand' => (float) ($totals?->initial_total ?? 0)
                    + (float) ($totals?->topup_total ?? 0)
                    + (float) ($totals?->virement_total ?? 0),
            ],
        ]);
    }

    public function options(): JsonResponse
    {
        $conn = DB::connection('mysql_secondary');

        $years = $conn->table('budget')
            ->select('bdg_year')
            ->distinct()
            ->whereNotNull('bdg_year')
            ->orderByDesc('bdg_year')
            ->pluck('bdg_year')
            ->filter()
            ->map(fn ($y) => ['id' => (string) $y, 'label' => (string) $y])
            ->values();

        $funds = $conn->table('fund_type')
            ->select('fty_fund_type', 'fty_fund_desc')
            ->orderBy('fty_fund_type')
            ->get()
            ->map(fn ($r) => [
                'id' => (string) $r->fty_fund_type,
                'label' => trim($r->fty_fund_type.' - '.($r->fty_fund_desc ?? '')),
            ])
            ->values();

        $activities = $conn->table('activity_type')
            ->select('at_activity_code', 'at_activity_description_bm')
            ->orderBy('at_activity_code')
            ->limit(2000)
            ->get()
            ->map(fn ($r) => [
                'id' => (string) $r->at_activity_code,
                'label' => trim($r->at_activity_code.' - '.($r->at_activity_description_bm ?? '')),
            ])
            ->values();

        $ouns = $conn->table('organization_unit')
            ->select('oun_code', 'oun_desc')
            ->whereNotNull('oun_code')
            ->orderBy('oun_code')
            ->limit(2000)
            ->get()
            ->map(fn ($r) => [
                'id' => (string) $r->oun_code,
                'label' => trim($r->oun_code.' - '.($r->oun_desc ?? '')),
            ])
            ->values();

        $ccrs = $conn->table('costcentre')
            ->select('ccr_costcentre', 'ccr_costcentre_desc')
            ->whereNotNull('ccr_costcentre')
            ->orderBy('ccr_costcentre')
            ->limit(5000)
            ->get()
            ->map(fn ($r) => [
                'id' => (string) $r->ccr_costcentre,
                'label' => trim($r->ccr_costcentre.' - '.($r->ccr_costcentre_desc ?? '')),
            ])
            ->values();

        $budgetCodes = $conn->table('lkp_budget_code')
            ->select('lbc_budget_code', 'lbc_description')
            ->orderBy('lbc_budget_code')
            ->get()
            ->map(fn ($r) => [
                'id' => (string) $r->lbc_budget_code,
                'label' => trim($r->lbc_budget_code.' - '.($r->lbc_description ?? '')),
            ])
            ->values();

        return $this->sendOk([
            'topFilter' => [
                'year' => $years,
            ],
            'smartFilter' => [
                'fund' => $funds,
                'activity' => $activities,
                'oun' => $ouns,
                'ccr' => $ccrs,
                'budgetCode' => $budgetCodes,
            ],
        ]);
    }

    private function baseQuery(Request $request): Builder
    {
        $query = Budget::query()
            ->from('budget as B')
            ->join('structure_budget as SB', 'B.sbg_budget_id', '=', 'SB.sbg_budget_id')
            ->leftJoin('activity_type as AT', 'SB.at_activity_code', '=', 'AT.at_activity_code')
            ->leftJoin('organization_unit as OU', 'SB.oun_code', '=', 'OU.oun_code')
            ->leftJoin('costcentre as CC', 'SB.ccr_costcentre', '=', 'CC.ccr_costcentre')
            ->leftJoin('lkp_budget_code as LBC', 'SB.lbc_budget_code', '=', 'LBC.lbc_budget_code')
            ->leftJoin('fund_type as FT', 'SB.fty_fund_type', '=', 'FT.fty_fund_type');

        $year = $request->input('tf_year');
        if ($year !== null && $year !== '') {
            $query->where('B.bdg_year', (string) $year);
        }

        if (($needle = trim((string) $request->input('q'))) !== '') {
            $like = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $needle).'%';
            $query->where(function ($w) use ($like) {
                $w->where('SB.fty_fund_type', 'like', $like)
                    ->orWhere('SB.at_activity_code', 'like', $like)
                    ->orWhere('SB.oun_code', 'like', $like)
                    ->orWhere('SB.ccr_costcentre', 'like', $like)
                    ->orWhere('SB.lbc_budget_code', 'like', $like)
                    ->orWhere('AT.at_activity_description_bm', 'like', $like)
                    ->orWhere('OU.oun_desc', 'like', $like)
                    ->orWhere('CC.ccr_costcentre_desc', 'like', $like)
                    ->orWhere('LBC.lbc_description', 'like', $like);
            });
        }

        $this->applyEqual($query, $request, 'sm_fund', 'SB.fty_fund_type');
        $this->applyEqual($query, $request, 'sm_activity', 'SB.at_activity_code');
        $this->applyEqual($query, $request, 'sm_oun', 'SB.oun_code');
        $this->applyEqual($query, $request, 'sm_ccr', 'SB.ccr_costcentre');
        $this->applyEqual($query, $request, 'sm_budget_code', 'SB.lbc_budget_code');

        return $query;
    }

    private function applyEqual(Builder $query, Request $request, string $param, string $column): void
    {
        $v = $request->input($param);
        if ($v !== null && $v !== '') {
            $query->where($column, (string) $v);
        }
    }
}
