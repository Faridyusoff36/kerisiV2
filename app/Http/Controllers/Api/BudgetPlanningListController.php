<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\BudgetPlanningDetails;
use App\Models\BudgetPlanningMaster;
use App\Models\LookupDetail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Shared list controller for the Budget Planning suite.
 *
 * Backs five legacy pages, each filtered by a different `bpm_type`:
 *
 *   - PAGEID 2056 / MENUID 2506  Dasar Sedia Ada       (BL MM_API_BUDGET_BUDGETPLANNINGLIST,
 *                                                      bpm_type = 'YEARLY' / '02')
 *   - PAGEID 2489 / MENUID 3012  Allocation 2 List     (BL MM_API_BUDGET_BUDGETPLANNINGALLO2LIST,
 *                                                      bpm_type = 'ALLOCATION 2')
 *   - PAGEID 2490 / MENUID 3013  Allocation 3 List     (BL MM_API_BUDGET_BUDGETPLANNINGALLO3LIST,
 *                                                      bpm_type = 'ALLOCATION 3')
 *   - PAGEID 2635 / MENUID 3196  Dasar Baru / One Off  (BL CH9_BUDGET_ONE_OFF,
 *                                                      bpm_type IN VOTTYPE AND != '02')
 *   - PAGEID 2713 / MENUID 3279  Planning to Initial   (BL CH9_PLANNING_TO_INITIAL,
 *                                                      bpm_type IN VOTTYPE AND bpm_status = 'ENDORSE')
 *
 * The `scope` query parameter selects which of the five filters to
 * apply. Allowed values:
 *   yearly | allocation_2 | allocation_3 | one_off | to_initial
 *
 * Workflow actions (post-to-initial, generate warrant, full edit
 * popups) live on legacy pages MENUID 1516 / 2477 / 3214 which are not
 * part of this batch. We expose:
 *   - GET    /budget/planning-list   (with `scope` + smart filters)
 *   - DELETE /budget/planning-list/{id}
 *   - POST   /budget/planning-list/{id}/duplicate
 *
 * The bulk "post to initial" workflow is intentionally NOT migrated
 * here — the legacy version writes to budget_allocation_master /
 * budget_allocation_detl via the `get_generate_budget_id_year`
 * stored procedure and depends on additional checks that haven't
 * landed in this app yet.
 */
class BudgetPlanningListController extends Controller
{
    use ApiResponse;

    private const SCOPES = [
        'yearly',
        'allocation_2',
        'allocation_3',
        'one_off',
        'to_initial',
    ];

    private const SORTABLE = [
        'bpmId' => 'BPM.bpm_id',
        'bpmPlanningNo' => 'BPM.bpm_planning_no',
        'bpmYear' => 'BPM.bpm_year',
        'bpmType' => 'BPM.bpm_type',
        'bpmTotalAmt' => 'BPM.bpm_total_amt',
        'bpmStatus' => 'BPM.bpm_status',
        'bpmOunCode' => 'BPM.bpm_oun_code',
        'bpmCcrCostcentre' => 'BPM.bpm_ccr_costcentre',
        'createddate' => 'BPM.createddate',
    ];

    public function index(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->input('page', 1));
        $limit = max(1, min(200, (int) $request->input('limit', 10)));
        $sortKey = (string) $request->input('sort_by', 'bpmId');
        $sortDir = strtolower((string) $request->input('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';
        $sortCol = self::SORTABLE[$sortKey] ?? self::SORTABLE['bpmId'];

        $scope = $this->resolveScope($request);
        $query = $this->baseQuery($request, $scope);

        $total = (clone $query)->count('BPM.bpm_id');

        $rows = (clone $query)
            ->select([
                'BPM.bpm_id',
                'BPM.bpm_planning_no',
                'BPM.bpm_planning_no_prev',
                'BPM.bpm_year',
                'BPM.bpm_oun_code',
                'BPM.bpm_ccr_costcentre',
                'BPM.fty_fund_type',
                'BPM.at_activity_code',
                'BPM.bpm_remark',
                'BPM.bpm_total_amt',
                'BPM.bpm_status',
                'BPM.bpm_type',
                'BPM.duplicate_count',
                'BPM.createddate',
                'BPM.updateddate',
                'OU.oun_desc',
                'CC.ccr_costcentre_desc',
            ])
            ->orderBy($sortCol, $sortDir)
            ->orderByDesc('BPM.bpm_id')
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        $baseIndex = ($page - 1) * $limit;
        $data = $rows->values()->map(fn ($row, $idx) => [
            'index' => $baseIndex + $idx + 1,
            'bpmId' => (int) $row->bpm_id,
            'bpmPlanningNo' => $row->bpm_planning_no,
            'bpmPlanningNoPrev' => $row->bpm_planning_no_prev,
            'bpmYear' => $row->bpm_year,
            'bpmOunCode' => $row->bpm_oun_code,
            'bpmOunDesc' => $row->oun_desc,
            'bpmCcrCostcentre' => $row->bpm_ccr_costcentre,
            'bpmCcrCostcentreDesc' => $row->ccr_costcentre_desc,
            'bpmFundType' => $row->fty_fund_type,
            'bpmActivityCode' => $row->at_activity_code,
            'bpmRemark' => $row->bpm_remark,
            'bpmTotalAmt' => $row->bpm_total_amt !== null ? (float) $row->bpm_total_amt : null,
            'bpmStatus' => $row->bpm_status,
            'bpmType' => $row->bpm_type,
            'duplicateCount' => $row->duplicate_count !== null ? (int) $row->duplicate_count : 0,
            'createddate' => optional($row->createddate)?->toIso8601String(),
            'updateddate' => optional($row->updateddate)?->toIso8601String(),
            'canEdit' => !in_array(strtoupper((string) ($row->bpm_status ?? '')), ['ENDORSE', 'POSTED', 'CANCEL'], true),
            'canDelete' => !in_array(strtoupper((string) ($row->bpm_status ?? '')), ['ENDORSE', 'POSTED'], true),
        ]);

        return $this->sendOk($data, [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'totalPages' => (int) ceil(max(1, $total) / $limit),
            'scope' => $scope,
        ]);
    }

    public function options(Request $request): JsonResponse
    {
        $scope = $this->resolveScope($request);
        $base = $this->scopeBaseQuery($scope);

        $years = (clone $base)
            ->select('bpm_year')
            ->distinct()
            ->whereNotNull('bpm_year')
            ->orderByDesc('bpm_year')
            ->pluck('bpm_year')
            ->filter()
            ->map(fn ($y) => ['id' => (string) $y, 'label' => (string) $y])
            ->values();

        $statuses = (clone $base)
            ->select('bpm_status')
            ->distinct()
            ->whereNotNull('bpm_status')
            ->orderBy('bpm_status')
            ->pluck('bpm_status')
            ->filter()
            ->map(fn ($s) => ['id' => (string) $s, 'label' => (string) $s])
            ->values();

        $ouns = (clone $base)
            ->select('bpm_oun_code')
            ->distinct()
            ->whereNotNull('bpm_oun_code')
            ->orderBy('bpm_oun_code')
            ->limit(2000)
            ->pluck('bpm_oun_code')
            ->filter()
            ->map(fn ($v) => ['id' => (string) $v, 'label' => (string) $v])
            ->values();

        $ccrs = (clone $base)
            ->select('bpm_ccr_costcentre')
            ->distinct()
            ->whereNotNull('bpm_ccr_costcentre')
            ->orderBy('bpm_ccr_costcentre')
            ->limit(5000)
            ->pluck('bpm_ccr_costcentre')
            ->filter()
            ->map(fn ($v) => ['id' => (string) $v, 'label' => (string) $v])
            ->values();

        $types = (clone $base)
            ->select('bpm_type')
            ->distinct()
            ->whereNotNull('bpm_type')
            ->orderBy('bpm_type')
            ->pluck('bpm_type')
            ->filter()
            ->map(fn ($v) => ['id' => (string) $v, 'label' => (string) $v])
            ->values();

        return $this->sendOk([
            'smartFilter' => [
                'year' => $years,
                'status' => $statuses,
                'oun' => $ouns,
                'ccr' => $ccrs,
                'type' => $types,
            ],
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $row = BudgetPlanningMaster::query()->where('bpm_id', $id)->first();
        if (!$row) {
            return $this->sendError(404, 'NOT_FOUND', 'Budget planning record not found');
        }

        if (in_array(strtoupper((string) $row->bpm_status), ['ENDORSE', 'POSTED'], true)) {
            return $this->sendError(409, 'CONFLICT', 'Endorsed / posted records cannot be deleted.');
        }

        DB::connection('mysql_secondary')->transaction(function () use ($row) {
            BudgetPlanningDetails::query()->where('bpm_id', $row->bpm_id)->delete();
            $row->delete();
        });

        return $this->sendOk(['success' => true]);
    }

    public function duplicate(Request $request, int $id): JsonResponse
    {
        $row = BudgetPlanningMaster::query()->where('bpm_id', $id)->first();
        if (!$row) {
            return $this->sendError(404, 'NOT_FOUND', 'Budget planning record not found');
        }

        $conn = DB::connection('mysql_secondary');

        return $conn->transaction(function () use ($row, $conn) {
            // Mimic legacy getSeqNo() with MAX(bpm_id) + 1.
            $nextId = (int) (BudgetPlanningMaster::query()->max('bpm_id') ?? 0) + 1;

            $clone = $row->replicate(['createddate', 'updateddate']);
            $clone->bpm_id = $nextId;
            $clone->bpm_planning_no = ($row->bpm_planning_no ? $row->bpm_planning_no.'-COPY' : 'COPY-'.$nextId);
            $clone->bpm_planning_no_prev = $row->bpm_planning_no;
            $clone->bpm_status = 'DRAFT';
            $clone->duplicate_count = (int) ($row->duplicate_count ?? 0) + 1;
            $clone->createddate = now();
            $clone->updateddate = now();
            $clone->save();

            // Copy details with fresh primary keys.
            $detailRows = BudgetPlanningDetails::query()->where('bpm_id', $row->bpm_id)->get();
            $nextDetailId = (int) (BudgetPlanningDetails::query()->max('bpd_id') ?? 0);
            foreach ($detailRows as $detail) {
                $nextDetailId += 1;
                $detailClone = $detail->replicate(['createddate', 'updateddate']);
                $detailClone->bpd_id = $nextDetailId;
                $detailClone->bpm_id = $nextId;
                $detailClone->createddate = now();
                $detailClone->updateddate = now();
                $detailClone->save();
            }

            // Bump duplicate_count on the source row to mirror the legacy
            // bookkeeping behaviour.
            $row->duplicate_count = (int) ($row->duplicate_count ?? 0) + 1;
            $row->save();

            return $this->sendCreated([
                'bpmId' => $clone->bpm_id,
                'bpmPlanningNo' => $clone->bpm_planning_no,
                'successMessage' => 'Budget planning duplicated.',
            ]);
        });
    }

    private function resolveScope(Request $request): string
    {
        $scope = strtolower((string) $request->input('scope', 'yearly'));
        if (!in_array($scope, self::SCOPES, true)) {
            $scope = 'yearly';
        }

        return $scope;
    }

    private function baseQuery(Request $request, string $scope): Builder
    {
        $query = BudgetPlanningMaster::query()
            ->from('budget_planning_master as BPM')
            ->leftJoin('organization_unit as OU', 'BPM.bpm_oun_code', '=', 'OU.oun_code')
            ->leftJoin('costcentre as CC', 'BPM.bpm_ccr_costcentre', '=', 'CC.ccr_costcentre');

        $this->applyScope($query, $scope, 'BPM');

        if (($needle = trim((string) $request->input('q'))) !== '') {
            $like = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $needle).'%';
            $query->where(function ($w) use ($like) {
                $w->where('BPM.bpm_planning_no', 'like', $like)
                    ->orWhere('BPM.bpm_year', 'like', $like)
                    ->orWhere('BPM.bpm_oun_code', 'like', $like)
                    ->orWhere('BPM.bpm_ccr_costcentre', 'like', $like)
                    ->orWhere('BPM.bpm_remark', 'like', $like)
                    ->orWhere('BPM.bpm_status', 'like', $like)
                    ->orWhere('BPM.bpm_type', 'like', $like);
            });
        }

        $this->applyLike($query, $request, 'sm_planning_no', 'BPM.bpm_planning_no');
        $this->applyEqual($query, $request, 'sm_year', 'BPM.bpm_year');
        $this->applyEqual($query, $request, 'sm_oun', 'BPM.bpm_oun_code');
        $this->applyEqual($query, $request, 'sm_ccr', 'BPM.bpm_ccr_costcentre');
        $this->applyEqual($query, $request, 'sm_status', 'BPM.bpm_status');
        $this->applyEqual($query, $request, 'sm_type', 'BPM.bpm_type');
        $this->applyLike($query, $request, 'sm_title', 'BPM.bpm_remark');

        if (($v = $request->input('sm_amount')) !== null && $v !== '') {
            $query->where('BPM.bpm_total_amt', (float) $v);
        }

        return $query;
    }

    private function scopeBaseQuery(string $scope): Builder
    {
        $query = BudgetPlanningMaster::query();
        $this->applyScope($query, $scope);

        return $query;
    }

    private function applyScope(Builder $query, string $scope, ?string $alias = null): void
    {
        $col = $alias ? $alias.'.bpm_type' : 'bpm_type';
        $statusCol = $alias ? $alias.'.bpm_status' : 'bpm_status';

        switch ($scope) {
            case 'allocation_2':
                $query->where($col, 'ALLOCATION 2');
                break;
            case 'allocation_3':
                $query->where($col, 'ALLOCATION 3');
                break;
            case 'one_off':
                $query->whereIn($col, $this->lookupVotTypes())
                    ->where($col, '!=', '02');
                break;
            case 'to_initial':
                $query->whereIn($col, $this->lookupVotTypes())
                    ->where($statusCol, 'ENDORSE');
                break;
            case 'yearly':
            default:
                // Legacy BL accepts both 'YEARLY' and '02' depending on the
                // tenant — keep both.
                $query->whereIn($col, ['YEARLY', '02']);
                break;
        }
    }

    private function lookupVotTypes(): array
    {
        return LookupDetail::query()
            ->where('lma_code_name', 'VOTTYPE')
            ->pluck('lde_value')
            ->filter()
            ->map(fn ($v) => (string) $v)
            ->values()
            ->all();
    }

    private function applyEqual(Builder $query, Request $request, string $param, string $column): void
    {
        $v = $request->input($param);
        if ($v !== null && $v !== '') {
            $query->where($column, (string) $v);
        }
    }

    private function applyLike(Builder $query, Request $request, string $param, string $column): void
    {
        $v = trim((string) $request->input($param, ''));
        if ($v === '') {
            return;
        }
        $like = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $v).'%';
        $query->where($column, 'like', $like);
    }
}
