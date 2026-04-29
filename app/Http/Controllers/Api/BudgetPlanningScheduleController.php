<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBudgetPlanningScheduleRequest;
use App\Http\Requests\UpdateBudgetPlanningScheduleRequest;
use App\Http\Traits\ApiResponse;
use App\Models\BudgetPlanningSchedule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Budget Setup > Budget Planning Schedule (PAGEID 2872 / MENUID 3456).
 *
 * Refactored from the legacy BL `SNA_API_BUDGET_SETUP_BDGPLANNINGSCHEDULE`,
 * which exposed `process_planning` (insert/update), `deleteDetail`,
 * `display_detail`, and the datatable feed `dt_listplanning`.
 *
 * Notes vs legacy:
 *   - The cron-job e-mail blast (`?cronjob_email=1`) is intentionally NOT
 *     ported. That branch reads from `fims.FLC_USER_GROUP*` and triggers
 *     Mirth via curl_post — both are infra-specific and should be wired
 *     through a Laravel scheduled job in a follow-up.
 */
class BudgetPlanningScheduleController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $page = (int) $request->input('page', 1);
        $limit = (int) $request->input('limit', 10);
        $q = trim((string) $request->input('q', ''));

        $query = BudgetPlanningSchedule::query()
            ->select([
                'bps_id',
                'bps_year_budget',
                'bps_plan_startDate',
                'bps_plan_endDate',
                'bps_status',
            ]);

        if ($q !== '') {
            $needle = mb_strtolower($q, 'UTF-8');
            $like = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $needle).'%';
            $query->where(function ($builder) use ($like) {
                $builder->whereRaw('LOWER(IFNULL(bps_year_budget, "")) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(IF(bps_status = 1, "ACTIVE", "INACTIVE")) LIKE ?', [$like]);
            });
        }

        // Top filter: legacy "Year" free-text input (`year_filter` after middleware).
        $yearRaw = trim((string) $request->input('year_filter', ''));
        if ($yearRaw !== '') {
            $year = filter_var($yearRaw, FILTER_VALIDATE_INT);
            if ($year !== false && $year > 0) {
                $query->where('bps_year_budget', $year);
            }
        }

        $total = (clone $query)->count();
        $rows = $query->orderBy('bps_year_budget', 'desc')
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get()
            ->values()
            ->map(function ($row, $idx) use ($page, $limit) {
                $startDate = $row->bps_plan_startDate?->format('d/m/Y');
                $endDate = $row->bps_plan_endDate?->format('d/m/Y');

                return [
                    'index' => (($page - 1) * $limit) + $idx + 1,
                    'bps_id' => (int) $row->bps_id,
                    'bps_year_budget' => (int) $row->bps_year_budget,
                    'bps_plan_start_date' => $row->bps_plan_startDate?->format('Y-m-d'),
                    'bps_plan_end_date' => $row->bps_plan_endDate?->format('Y-m-d'),
                    'planning_date' => $startDate && $endDate ? $startDate.' - '.$endDate : null,
                    'bps_status' => (string) $row->bps_status === '1' ? 'ACTIVE' : 'INACTIVE',
                    'bps_status_value' => (string) $row->bps_status === '1' ? 1 : 0,
                    'is_current_year' => (int) $row->bps_year_budget === (int) date('Y'),
                ];
            });

        return $this->sendOk($rows, [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'totalPages' => $limit > 0 ? (int) ceil($total / $limit) : 0,
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $row = BudgetPlanningSchedule::query()->where('bps_id', $id)->first();
        if (! $row) {
            return $this->sendError(404, 'NOT_FOUND', 'Budget Planning Schedule not found');
        }

        return $this->sendOk([
            'bps_id' => (int) $row->bps_id,
            'bps_year_budget' => (int) $row->bps_year_budget,
            'bps_plan_start_date' => $row->bps_plan_startDate?->format('Y-m-d'),
            'bps_plan_end_date' => $row->bps_plan_endDate?->format('Y-m-d'),
            'bps_status' => (string) $row->bps_status === '1' ? 'ACTIVE' : 'INACTIVE',
            'bps_status_value' => (string) $row->bps_status === '1' ? 1 : 0,
        ]);
    }

    public function options(): JsonResponse
    {
        $years = BudgetPlanningSchedule::query()
            ->select('bps_year_budget')
            ->whereNotNull('bps_year_budget')
            ->distinct()
            ->orderBy('bps_year_budget', 'desc')
            ->pluck('bps_year_budget')
            ->map(fn ($yr) => ['id' => (string) $yr, 'label' => (string) $yr])
            ->values();

        return $this->sendOk([
            'topFilter' => [
                'years' => $years,
            ],
            'popupModal' => [
                'status' => [
                    ['id' => 'ACTIVE', 'label' => 'ACTIVE'],
                    ['id' => 'INACTIVE', 'label' => 'INACTIVE'],
                ],
            ],
        ]);
    }

    public function store(StoreBudgetPlanningScheduleRequest $request): JsonResponse
    {
        $data = $request->validated();
        $year = (int) $data['bps_year_budget'];

        $existing = BudgetPlanningSchedule::query()
            ->where('bps_year_budget', $year)
            ->first();
        if ($existing) {
            $status = (string) $existing->bps_status === '1' ? 'ACTIVE' : 'INACTIVE';

            return $this->sendError(400, 'BAD_REQUEST', "Year that you enter already exists and the status is <b>{$status}</b>.");
        }

        $nextId = ((int) BudgetPlanningSchedule::query()->max('bps_id')) + 1;
        $row = BudgetPlanningSchedule::create([
            'bps_id' => $nextId,
            'bps_year_budget' => $year,
            'bps_plan_startDate' => $data['bps_plan_start_date'],
            'bps_plan_endDate' => $data['bps_plan_end_date'],
            'bps_status' => strtoupper((string) $data['bps_status']) === 'ACTIVE' ? '1' : '0',
            'createddate' => now(),
            'createdby' => $request->user()?->name ?? 'system',
        ]);

        return $this->sendCreated([
            'id' => (int) $row->bps_id,
            'success_message' => 'New Schedule for Budget Planning successfully saved.',
        ]);
    }

    public function update(UpdateBudgetPlanningScheduleRequest $request, int $id): JsonResponse
    {
        $row = BudgetPlanningSchedule::query()->where('bps_id', $id)->first();
        if (! $row) {
            return $this->sendError(404, 'NOT_FOUND', 'Budget Planning Schedule not found');
        }

        // Guard rails from the legacy `plan.edit` UI: the row for the current
        // calendar year cannot be edited (the legacy table renderer disables
        // the edit/delete icons via `row.current_year==row.bps_year_budget`).
        if ((int) $row->bps_year_budget === (int) date('Y')) {
            return $this->sendError(400, 'BAD_REQUEST', 'Schedule for the current year cannot be edited.');
        }

        $data = $request->validated();
        $row->update([
            'bps_plan_startDate' => $data['bps_plan_start_date'],
            'bps_plan_endDate' => $data['bps_plan_end_date'],
            'bps_status' => strtoupper((string) $data['bps_status']) === 'ACTIVE' ? '1' : '0',
            'updateddate' => now(),
            'updatedby' => $request->user()?->name ?? 'system',
        ]);

        return $this->sendOk([
            'success' => true,
            'success_message' => "Schedule for Budget Planning Year <b>{$row->bps_year_budget}</b> successfully updated.",
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $row = BudgetPlanningSchedule::query()->where('bps_id', $id)->first();
        if (! $row) {
            return $this->sendError(404, 'NOT_FOUND', 'Budget Planning Schedule not found');
        }

        if ((int) $row->bps_year_budget === (int) date('Y')) {
            return $this->sendError(400, 'BAD_REQUEST', 'Schedule for the current year cannot be deleted.');
        }

        $row->delete();

        return $this->sendOk(['success' => true]);
    }
}
