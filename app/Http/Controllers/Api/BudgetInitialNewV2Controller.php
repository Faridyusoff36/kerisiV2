<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\BudgetAllocationMaster;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Budget / New Initial V2 (PAGEID 1277 / MENUID 1560).
 *
 * Legacy list BL: `SWS_DT_BUDGET_INITIAL_NEW_V2` (`mode=datatable&id=bam_id`).
 * Read-only port: master header from `budget_allocation_master` and detail
 * lines with the same joins as legacy (fund_type, activity_type, organization_unit,
 * costcentre, optional structure_budget, lkp_budget_code subquery for labels).
 *
 * Create/update/detail modals and workflow are not migrated here.
 */
class BudgetInitialNewV2Controller extends Controller
{
    use ApiResponse;

    private const SORTABLE = [
        'budgetId' => 'bad.bad_sbg_id',
        'fund' => 'bad.fty_fund_type',
        'activity' => 'bad.at_activity_code',
        'ptj' => 'bad.oun_code',
        'ccr' => 'bad.ccr_costcentre',
        'budgetCode' => 'bad.budget_code',
        'amount' => 'bad.initial_amt',
    ];

    public function master(int $bamId): JsonResponse
    {
        $m = BudgetAllocationMaster::on('mysql_secondary')
            ->with('quarter')
            ->find($bamId);

        if ($m === null) {
            return $this->sendError(404, 'not_found', 'Budget allocation master not found.');
        }

        $conn = 'mysql_secondary';
        $unregistered = 0;
        $errorDataFile = 0;

        if (Schema::connection($conn)->hasTable('temp_structure_budget')) {
            $unregistered = (int) DB::connection($conn)
                ->table('temp_structure_budget')
                ->where('bam_id', $bamId)
                ->count();
        }
        if (Schema::connection($conn)->hasTable('temp_upload_initial_error')) {
            $errorDataFile = (int) DB::connection($conn)
                ->table('temp_upload_initial_error')
                ->where('bam_id', $bamId)
                ->count();
        }

        $q = $m->quarter;

        return $this->sendOk([
            'bam_id' => (int) $m->bam_id,
            'reference' => $m->bam_allocation_no,
            'quarter_id' => $m->bam_quarter_id,
            'quarter_label' => $q !== null
                ? trim((string) $q->qbu_description.' ('.$q->qbu_year.')')
                : null,
            'year' => $m->bam_year,
            'endorse_doc' => $m->bam_endorse_doc,
            'file_name' => $m->bam_file_name,
            'stat' => $m->bam_status_cd,
            'total' => $m->bam_total !== null ? (float) $m->bam_total : null,
            'unregistered_count' => $unregistered,
            'error_data_file_count' => $errorDataFile,
        ]);
    }

    public function details(Request $request): JsonResponse
    {
        $bamId = (int) $request->input('bam_id', 0);
        if ($bamId < 1) {
            return $this->sendError(422, 'validation_error', 'bam_id is required.');
        }

        $page = max(1, (int) $request->input('page', 1));
        $limit = max(1, min(200, (int) $request->input('limit', 10)));
        $q = trim((string) $request->input('q', ''));
        $sortKey = (string) $request->input('sort_by', 'budgetId');
        $sortDir = strtolower((string) $request->input('sort_dir', 'asc')) === 'desc' ? 'desc' : 'asc';
        $orderCol = self::SORTABLE[$sortKey] ?? self::SORTABLE['budgetId'];

        $base = $this->detailsBase($bamId, $q);

        $total = (clone $base)->count();

        $grandQuery = $this->detailsBase($bamId, $q);
        $grandTotalRaw = $grandQuery->sum('bad.initial_amt');
        $grandTotal = $grandTotalRaw !== null ? (float) $grandTotalRaw : 0.0;

        $rows = (clone $base)
            ->select([
                'bad.bad_detl_id',
                'bad.bad_sbg_id',
                DB::raw("CONCAT_WS(' - ', bad.fty_fund_type, ft.fty_fund_desc) AS fund_lbl"),
                DB::raw("CONCAT_WS(' - ', bad.at_activity_code, at.at_activity_description_bm) AS activity_lbl"),
                DB::raw("CONCAT_WS(' - ', bad.oun_code, oun.oun_desc) AS ptj_lbl"),
                DB::raw("CONCAT_WS(' - ', bad.ccr_costcentre, cc.ccr_costcentre_desc) AS ccr_lbl"),
                DB::raw("CONCAT_WS(' - ', bad.budget_code,
                    (SELECT lbc.lbc_description FROM lkp_budget_code lbc
                     WHERE lbc.lbc_budget_code = bad.budget_code LIMIT 1)) AS budget_code_lbl"),
                'bad.initial_amt',
                'bam.bam_status_cd',
            ])
            ->orderBy($orderCol, $sortDir)
            ->orderBy('bad.bad_detl_id')
            ->offset(($page - 1) * $limit)
            ->limit($limit)
            ->get();

        $baseIndex = ($page - 1) * $limit;
        $pageTotal = 0.0;
        $data = $rows->map(function (object $row, int $idx) use (&$pageTotal, $baseIndex) {
            $amt = $row->initial_amt !== null ? (float) $row->initial_amt : 0.0;
            $pageTotal += $amt;

            return [
                'index' => $baseIndex + $idx + 1,
                'id' => (int) $row->bad_detl_id,
                'budget_id' => $row->bad_sbg_id !== null ? (string) $row->bad_sbg_id : null,
                'fund' => $row->fund_lbl,
                'activity' => $row->activity_lbl,
                'ptj' => $row->ptj_lbl,
                'ccr' => $row->ccr_lbl,
                'budget_code' => $row->budget_code_lbl,
                'initial_amt' => $row->initial_amt !== null ? (float) $row->initial_amt : null,
                'stat' => $row->bam_status_cd,
            ];
        })->values();

        return $this->sendOk($data, [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'totalPages' => (int) ceil(max(1, $total) / $limit),
            'grandTotal' => $grandTotal,
            'pageTotal' => $pageTotal,
        ]);
    }

    private function detailsBase(int $bamId, string $q): Builder
    {
        $query = DB::connection('mysql_secondary')
            ->table('budget_allocation_detl as bad')
            ->join('budget_allocation_master as bam', 'bam.bam_id', '=', 'bad.bad_master_id')
            ->leftJoin('structure_budget as sb', 'bad.bad_sbg_id', '=', 'sb.sbg_budget_id')
            ->leftJoin('fund_type as ft', 'bad.fty_fund_type', '=', 'ft.fty_fund_type')
            ->leftJoin('organization_unit as oun', 'bad.oun_code', '=', 'oun.oun_code')
            ->leftJoin('costcentre as cc', 'bad.ccr_costcentre', '=', 'cc.ccr_costcentre')
            ->leftJoin('activity_type as at', 'bad.at_activity_code', '=', 'at.at_activity_code')
            ->where('bam.bam_id', $bamId);

        if ($q !== '') {
            $like = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $q).'%';
            $query->whereRaw(
                'CONCAT_WS(\'__\',
                    IFNULL(bad.bad_detl_id,\'\'),
                    IFNULL(bad.bad_sbg_id,\'\'),
                    IFNULL(CONCAT_WS(\' - \', bad.fty_fund_type, ft.fty_fund_desc),\'\'),
                    IFNULL(CONCAT_WS(\' - \', bad.at_activity_code, at.at_activity_description_bm),\'\'),
                    IFNULL(CONCAT_WS(\' - \', bad.oun_code, oun.oun_desc),\'\'),
                    IFNULL(CONCAT_WS(\' - \', bad.ccr_costcentre, cc.ccr_costcentre_desc),\'\'),
                    IFNULL(CONCAT_WS(\' - \', bad.budget_code,
                        (SELECT lbc2.lbc_description FROM lkp_budget_code lbc2
                         WHERE lbc2.lbc_budget_code = bad.budget_code LIMIT 1)),\'\'),
                    IFNULL(CONCAT_WS(\' - \', bad.cpa_project_no),\'\'),
                    IFNULL(JSON_UNQUOTE(JSON_EXTRACT(sb.sbg_extended_field, \'$.fundDesc\')),\'\'),
                    IFNULL(JSON_UNQUOTE(JSON_EXTRACT(sb.sbg_extended_field, \'$.activityDesc\')),\'\'),
                    IFNULL(JSON_UNQUOTE(JSON_EXTRACT(sb.sbg_extended_field, \'$.ounDesc\')),\'\'),
                    IFNULL(JSON_UNQUOTE(JSON_EXTRACT(sb.sbg_extended_field, \'$.costcentreDesc\')),\'\'),
                    IFNULL(JSON_UNQUOTE(JSON_EXTRACT(sb.sbg_extended_field, \'$.projectDesc\')),\'\'),
                    IFNULL(JSON_UNQUOTE(JSON_EXTRACT(sb.sbg_extended_field, \'$.budgetDesc\')),\'\'),
                    IFNULL(FORMAT(bad.initial_amt, 2),\'\'),
                    IFNULL(bam.bam_status_cd,\'\')
                ) LIKE ?',
                [$like]
            );
        }

        return $query;
    }
}
