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
 * Budget > Reports > Laporan Belanjawan (PAGEID 2873 / MENUID 3457).
 *
 * Source BL: YUS_BELANJAWAN_REPO_API (`dt_listing=1`). Joins the
 * `budget` ledger with `structure_budget` and (optionally) `account_main`
 * for the account series / account code dimensions. Each row exposes the
 * full ledger picture: opening, initial, additional, virement, top-up,
 * allocated, locked, pre_request, request, commit, expenses, balance,
 * plus the expenses_percentage rollup.
 *
 * Read-only. Top filters: Year (required), Fund, Date Range, Account
 * Code Series. Smart filter mirrors the dimensional columns.
 */
class LaporanBelanjawanController extends Controller
{
    use ApiResponse;

    private const SORTABLE = [
        'fund' => 'SB.fty_fund_type',
        'activity' => 'SB.at_activity_code',
        'costcentre' => 'SB.ccr_costcentre',
        'account' => 'AM.acm_acct_code',
        'accountSeries' => 'AM.acm_acct_parent',
        'opening' => 'B.bdg_bal_carryforward',
        'initial' => 'B.bdg_initial_amt',
        'additional' => 'B.bdg_additional_amt',
        'virement' => 'B.bdg_virement_amt',
        'topup' => 'B.bdg_topup_amt',
        'allocated' => 'B.bdg_allocated_amt',
        'locked' => 'B.bdg_lock_amt',
        'preRequest' => 'B.bdg_pre_request_amt',
        'request' => 'B.bdg_request_amt',
        'commit' => 'B.bdg_commit_amt',
        'expenses' => 'B.bdg_expenses_amt',
        'balance' => 'B.bdg_balance_amt',
    ];

    public function index(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->input('page', 1));
        $limit = max(1, min(500, (int) $request->input('limit', 10)));
        $sortKey = (string) $request->input('sort_by', 'fund');
        $sortDir = strtolower((string) $request->input('sort_dir', 'asc')) === 'desc' ? 'desc' : 'asc';
        $sortCol = self::SORTABLE[$sortKey] ?? self::SORTABLE['fund'];

        $query = $this->baseQuery($request);

        $total = (clone $query)->count('B.bdg_budget_id');

        $rows = (clone $query)
            ->select([
                'B.bdg_budget_id',
                'B.bdg_year',
                'B.bdg_bal_carryforward as opening',
                'B.bdg_initial_amt as initial',
                'B.bdg_additional_amt as additional',
                'B.bdg_virement_amt as virement',
                'B.bdg_topup_amt as topup',
                'B.bdg_allocated_amt as allocated',
                'B.bdg_lock_amt as locked',
                'B.bdg_pre_request_amt as pre_request',
                'B.bdg_request_amt as request_amt',
                'B.bdg_commit_amt as commit_amt',
                'B.bdg_expenses_amt as expenses',
                'B.bdg_balance_amt as balance',
                'SB.fty_fund_type as fund',
                'SB.at_activity_code as activity',
                'SB.ccr_costcentre as costcentre',
                'SB.lbc_budget_code',
                'AM.acm_acct_code as account',
                'AM.acm_acct_parent as account_series',
                'AM.acm_acct_desc as account_desc',
                'FT.fty_fund_desc',
                'AT.at_activity_description_bm',
                'CC.ccr_costcentre_desc',
            ])
            ->orderBy($sortCol, $sortDir)
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        $baseIndex = ($page - 1) * $limit;
        $data = $rows->values()->map(function ($row, $idx) use ($baseIndex) {
            $allocated = (float) ($row->allocated ?? 0);
            $expenses = (float) ($row->expenses ?? 0);
            $expensesPercentage = $allocated > 0 ? round(($expenses / $allocated) * 100, 2) : 0.0;

            return [
                'index' => $baseIndex + $idx + 1,
                'fund' => $row->fund,
                'fundDesc' => $row->fty_fund_desc,
                'activity' => $row->activity,
                'activityDesc' => $row->at_activity_description_bm,
                'costcentre' => $row->costcentre,
                'costcentreDesc' => $row->ccr_costcentre_desc,
                'accountSeries' => $row->account_series,
                'account' => $row->account,
                'accountDesc' => $row->account_desc,
                'glacctCode' => null,
                'opening' => (float) ($row->opening ?? 0),
                'initial' => (float) ($row->initial ?? 0),
                'additional' => (float) ($row->additional ?? 0),
                'virement' => (float) ($row->virement ?? 0),
                'topup' => (float) ($row->topup ?? 0),
                'allocated' => $allocated,
                'locked' => (float) ($row->locked ?? 0),
                'preRequest' => (float) ($row->pre_request ?? 0),
                'request' => (float) ($row->request_amt ?? 0),
                'commit' => (float) ($row->commit_amt ?? 0),
                'expenses' => $expenses,
                'balance' => (float) ($row->balance ?? 0),
                'expensesPercentage' => $expensesPercentage,
            ];
        });

        $totals = (clone $query)
            ->selectRaw('SUM(B.bdg_initial_amt) as initial_total,
                         SUM(B.bdg_additional_amt) as additional_total,
                         SUM(B.bdg_virement_amt) as virement_total,
                         SUM(B.bdg_topup_amt) as topup_total,
                         SUM(B.bdg_allocated_amt) as allocated_total,
                         SUM(B.bdg_expenses_amt) as expenses_total,
                         SUM(B.bdg_balance_amt) as balance_total')
            ->first();

        return $this->sendOk($data, [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'totalPages' => (int) ceil(max(1, $total) / $limit),
            'totals' => [
                'initial' => (float) ($totals?->initial_total ?? 0),
                'additional' => (float) ($totals?->additional_total ?? 0),
                'virement' => (float) ($totals?->virement_total ?? 0),
                'topup' => (float) ($totals?->topup_total ?? 0),
                'allocated' => (float) ($totals?->allocated_total ?? 0),
                'expenses' => (float) ($totals?->expenses_total ?? 0),
                'balance' => (float) ($totals?->balance_total ?? 0),
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

        $accountSeries = $conn->table('account_main')
            ->select('acm_acct_parent')
            ->whereNotNull('acm_acct_parent')
            ->groupBy('acm_acct_parent')
            ->orderBy('acm_acct_parent')
            ->limit(2000)
            ->pluck('acm_acct_parent')
            ->filter()
            ->map(fn ($v) => ['id' => (string) $v, 'label' => (string) $v])
            ->values();

        return $this->sendOk([
            'topFilter' => [
                'year' => $years,
                'fund' => $funds,
                'accountSeries' => $accountSeries,
            ],
            'smartFilter' => [
                'fund' => $funds,
                'accountSeries' => $accountSeries,
            ],
        ]);
    }

    private function baseQuery(Request $request): Builder
    {
        $query = Budget::query()
            ->from('budget as B')
            ->join('structure_budget as SB', 'B.sbg_budget_id', '=', 'SB.sbg_budget_id')
            ->leftJoin('account_main as AM', 'SB.acm_acct_code', '=', 'AM.acm_acct_code')
            ->leftJoin('fund_type as FT', 'SB.fty_fund_type', '=', 'FT.fty_fund_type')
            ->leftJoin('activity_type as AT', 'SB.at_activity_code', '=', 'AT.at_activity_code')
            ->leftJoin('costcentre as CC', 'SB.ccr_costcentre', '=', 'CC.ccr_costcentre');

        $year = $request->input('tf_year');
        if ($year !== null && $year !== '') {
            $query->where('B.bdg_year', (string) $year);
        }

        $fund = $request->input('tf_fund');
        if ($fund !== null && $fund !== '') {
            $query->where('SB.fty_fund_type', (string) $fund);
        }

        $accountSeries = $request->input('tf_account_series');
        if ($accountSeries !== null && $accountSeries !== '') {
            $query->where('AM.acm_acct_parent', (string) $accountSeries);
        }

        // Date range applies to the budget row's createddate as the
        // best available proxy (legacy uses transaction/createddate
        // depending on the snapshot type).
        if (($from = trim((string) $request->input('tf_date_from'))) !== '') {
            $query->whereDate('B.createddate', '>=', $from);
        }
        if (($to = trim((string) $request->input('tf_date_to'))) !== '') {
            $query->whereDate('B.createddate', '<=', $to);
        }

        if (($needle = trim((string) $request->input('q'))) !== '') {
            $like = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $needle).'%';
            $query->where(function ($w) use ($like) {
                $w->where('SB.fty_fund_type', 'like', $like)
                    ->orWhere('SB.at_activity_code', 'like', $like)
                    ->orWhere('SB.ccr_costcentre', 'like', $like)
                    ->orWhere('AM.acm_acct_code', 'like', $like)
                    ->orWhere('AM.acm_acct_parent', 'like', $like)
                    ->orWhere('FT.fty_fund_desc', 'like', $like)
                    ->orWhere('AT.at_activity_description_bm', 'like', $like)
                    ->orWhere('CC.ccr_costcentre_desc', 'like', $like);
            });
        }

        $this->applyEqual($query, $request, 'sm_fund', 'SB.fty_fund_type');
        $this->applyEqual($query, $request, 'sm_account_series', 'AM.acm_acct_parent');

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
