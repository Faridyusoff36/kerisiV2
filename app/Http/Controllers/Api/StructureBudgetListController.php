<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\StructureBudget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Budget > Structure Budget List (PAGEID 1071 / MENUID 1334).
 *
 * Source BL: SWS_DT_SETUP_BUDGETSTRUCTURELIST. The legacy datatable joins
 * structure_budget with activity_type, lkp_budget_code, organization_unit
 * and budget to render a per-(year × ptj × costcentre × fund × activity)
 * row. Editing structure rows is outside this batch (legacy depends on
 * organization_authorization and account_main lookups that aren't part of
 * the migration scope yet); we ship a read-only datatable with smart and
 * top filters plus PDF / CSV / Excel exports.
 */
class StructureBudgetListController extends Controller
{
    use ApiResponse;

    private const SORTABLE = [
        'sbId' => 'SB.sbg_budget_id',
        'sbYear' => 'B.bdg_year',
        'sbFund' => 'SB.fty_fund_type',
        'sbActivity' => 'SB.at_activity_code',
        'sbOun' => 'SB.oun_code',
        'sbCcr' => 'SB.ccr_costcentre',
        'sbBudgetCode' => 'SB.lbc_budget_code',
        'sbStatus' => 'B.bdg_status',
    ];

    public function index(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->input('page', 1));
        $limit = max(1, min(500, (int) $request->input('limit', 10)));
        $sortKey = (string) $request->input('sort_by', 'sbId');
        $sortDir = strtolower((string) $request->input('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';
        $sortCol = self::SORTABLE[$sortKey] ?? self::SORTABLE['sbId'];

        $query = $this->baseQuery($request);

        $total = (clone $query)->count('SB.sbg_budget_id');

        $rows = (clone $query)
            ->select([
                'SB.sbg_budget_id',
                'SB.fty_fund_type',
                'SB.at_activity_code',
                'SB.oun_code',
                'SB.ccr_costcentre',
                'SB.lbc_budget_code',
                'B.bdg_status',
                'B.bdg_year',
                'B.bdg_initial_amt',
                'B.bdg_topup_amt',
                'B.bdg_virement_amt',
                'B.bdg_balance_amt',
                'AT.at_activity_description_bm',
                'OU.oun_desc',
                'CC.ccr_costcentre_desc',
                'LBC.lbc_description',
            ])
            ->orderBy($sortCol, $sortDir)
            ->orderBy('SB.sbg_budget_id')
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        $baseIndex = ($page - 1) * $limit;
        $data = $rows->values()->map(fn ($row, $idx) => [
            'index' => $baseIndex + $idx + 1,
            'sbBudgetId' => (string) $row->sbg_budget_id,
            'sbYear' => $row->bdg_year,
            'sbFund' => $row->fty_fund_type,
            'sbActivity' => $row->at_activity_code,
            'sbActivityDesc' => $row->at_activity_description_bm,
            'sbOun' => $row->oun_code,
            'sbOunDesc' => $row->oun_desc,
            'sbCcr' => $row->ccr_costcentre,
            'sbCcrDesc' => $row->ccr_costcentre_desc,
            'sbBudgetCode' => $row->lbc_budget_code,
            'sbBudgetCodeDesc' => $row->lbc_description,
            'sbStatus' => $row->bdg_status,
            'sbInitialAmt' => $row->bdg_initial_amt !== null ? (float) $row->bdg_initial_amt : null,
            'sbTopupAmt' => $row->bdg_topup_amt !== null ? (float) $row->bdg_topup_amt : null,
            'sbVirementAmt' => $row->bdg_virement_amt !== null ? (float) $row->bdg_virement_amt : null,
            'sbBalanceAmt' => $row->bdg_balance_amt !== null ? (float) $row->bdg_balance_amt : null,
        ]);

        return $this->sendOk($data, [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'totalPages' => (int) ceil(max(1, $total) / $limit),
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

        $ouns = $conn->table('structure_budget')
            ->select('oun_code')
            ->whereNotNull('oun_code')
            ->groupBy('oun_code')
            ->orderBy('oun_code')
            ->limit(2000)
            ->pluck('oun_code')
            ->map(fn ($v) => ['id' => (string) $v, 'label' => (string) $v])
            ->values();

        $ccrs = $conn->table('structure_budget')
            ->select('ccr_costcentre')
            ->whereNotNull('ccr_costcentre')
            ->groupBy('ccr_costcentre')
            ->orderBy('ccr_costcentre')
            ->limit(5000)
            ->pluck('ccr_costcentre')
            ->map(fn ($v) => ['id' => (string) $v, 'label' => (string) $v])
            ->values();

        $budgetCodes = $conn->table('structure_budget')
            ->select('lbc_budget_code')
            ->whereNotNull('lbc_budget_code')
            ->groupBy('lbc_budget_code')
            ->orderBy('lbc_budget_code')
            ->limit(2000)
            ->pluck('lbc_budget_code')
            ->map(fn ($v) => ['id' => (string) $v, 'label' => (string) $v])
            ->values();

        $statuses = collect(['ACTIVE', 'INACTIVE'])
            ->map(fn ($s) => ['id' => $s, 'label' => $s])
            ->values();

        return $this->sendOk([
            'topFilter' => [
                'year' => $years,
                'fund' => $funds,
                'activity' => $activities,
                'oun' => $ouns,
                'ccr' => $ccrs,
            ],
            'smartFilter' => [
                'year' => $years,
                'fund' => $funds,
                'activity' => $activities,
                'oun' => $ouns,
                'ccr' => $ccrs,
                'budgetCode' => $budgetCodes,
                'status' => $statuses,
                'deficit' => [
                    ['id' => 'Y', 'label' => 'YES'],
                    ['id' => 'N', 'label' => 'NO'],
                ],
            ],
        ]);
    }

    private function baseQuery(Request $request): Builder
    {
        $query = StructureBudget::query()
            ->from('structure_budget as SB')
            ->leftJoin('budget as B', 'SB.sbg_budget_id', '=', 'B.sbg_budget_id')
            ->leftJoin('activity_type as AT', 'SB.at_activity_code', '=', 'AT.at_activity_code')
            ->leftJoin('organization_unit as OU', 'SB.oun_code', '=', 'OU.oun_code')
            ->leftJoin('costcentre as CC', 'SB.ccr_costcentre', '=', 'CC.ccr_costcentre')
            ->leftJoin('lkp_budget_code as LBC', 'SB.lbc_budget_code', '=', 'LBC.lbc_budget_code');

        if (($needle = trim((string) $request->input('q'))) !== '') {
            $like = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $needle).'%';
            $query->where(function ($w) use ($like) {
                $w->where('SB.sbg_budget_id', 'like', $like)
                    ->orWhere('SB.fty_fund_type', 'like', $like)
                    ->orWhere('SB.at_activity_code', 'like', $like)
                    ->orWhere('SB.oun_code', 'like', $like)
                    ->orWhere('SB.ccr_costcentre', 'like', $like)
                    ->orWhere('SB.lbc_budget_code', 'like', $like)
                    ->orWhere('AT.at_activity_desc', 'like', $like)
                    ->orWhere('OU.oun_desc', 'like', $like);
            });
        }

        // Top filter (camelCase from middleware: tfYear etc.)
        $this->applyEqual($query, $request, 'tf_year', 'B.bdg_year');
        $this->applyEqual($query, $request, 'tf_fund', 'SB.fty_fund_type');
        $this->applyEqual($query, $request, 'tf_activity', 'SB.at_activity_code');
        $this->applyEqual($query, $request, 'tf_oun', 'SB.oun_code');
        $this->applyEqual($query, $request, 'tf_ccr', 'SB.ccr_costcentre');

        // Smart filter
        $this->applyEqual($query, $request, 'sm_year', 'B.bdg_year');
        $this->applyEqual($query, $request, 'sm_fund', 'SB.fty_fund_type');
        $this->applyEqual($query, $request, 'sm_activity', 'SB.at_activity_code');
        $this->applyEqual($query, $request, 'sm_oun', 'SB.oun_code');
        $this->applyEqual($query, $request, 'sm_ccr', 'SB.ccr_costcentre');
        $this->applyEqual($query, $request, 'sm_budget_code', 'SB.lbc_budget_code');
        $this->applyEqual($query, $request, 'sm_status', 'B.bdg_status');

        if (($v = trim((string) $request->input('sm_deficit'))) !== '') {
            // Y = balance < 0, N = balance >= 0 (legacy uses bdg_balance_amt).
            if (strtoupper($v) === 'Y') {
                $query->where('B.bdg_balance_amt', '<', 0);
            } elseif (strtoupper($v) === 'N') {
                $query->where(function ($w) {
                    $w->where('B.bdg_balance_amt', '>=', 0)
                        ->orWhereNull('B.bdg_balance_amt');
                });
            }
        }

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
