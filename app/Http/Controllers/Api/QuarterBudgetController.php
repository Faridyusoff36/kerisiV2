<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateQuarterBudgetRequest;
use App\Http\Traits\ApiResponse;
use App\Models\QuarterBudget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Budget > Setup > Allocation (PAGEID 1035 / MENUID 1294).
 *
 * Source BL: SWS_DT_SETUP_QUARTER (`mode=datatable` + `mode=form`). The
 * legacy COMPONENT_JS for this page is a copy/paste of the Fund Type
 * page and is dead code on the original site (it talks to
 * `api/ANIS_FUND_TYPE`). We honour what the BL actually documents:
 *
 *   - List with smart filters on Year / Allocation (description) /
 *     Start Date / End Date / Status.
 *   - Show single row for the edit popup (Year, Description, Start
 *     Date, End Date, Status).
 *   - Update those columns via popup (no Add path is documented; we
 *     don't expose `store` until that BL ships).
 */
class QuarterBudgetController extends Controller
{
    use ApiResponse;

    private const SORTABLE = [
        'qbuYear' => 'qbu_year',
        'qbuDescription' => 'qbu_description',
        'qbuStartDate' => 'qbu_start_date',
        'qbuEndDate' => 'qbu_end_date',
        'qbuStatus' => 'qbu_status',
    ];

    public function index(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->input('page', 1));
        $limit = max(1, min(200, (int) $request->input('limit', 10)));
        $sortKey = (string) $request->input('sort_by', 'qbuYear');
        $sortDir = strtolower((string) $request->input('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';
        $sortCol = self::SORTABLE[$sortKey] ?? 'qbu_year';

        $query = $this->baseQuery($request);

        $total = (clone $query)->count('qbu_quarter_id');

        $rows = (clone $query)
            ->orderBy($sortCol, $sortDir)
            ->orderBy('qbu_description')
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        $baseIndex = ($page - 1) * $limit;
        $data = $rows->values()->map(fn ($row, $idx) => [
            'index' => $baseIndex + $idx + 1,
            'qbuQuarterId' => (string) $row->qbu_quarter_id,
            'qbuYear' => $row->qbu_year !== null ? (int) $row->qbu_year : null,
            'qbuDescription' => $row->qbu_description,
            'qbuStartDate' => optional($row->qbu_start_date)?->format('Y-m-d'),
            'qbuEndDate' => optional($row->qbu_end_date)?->format('Y-m-d'),
            'qbuStatus' => (string) ($row->qbu_status ?? ''),
        ]);

        return $this->sendOk($data, [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'totalPages' => (int) ceil(max(1, $total) / $limit),
        ]);
    }

    public function show(string $id): JsonResponse
    {
        $row = QuarterBudget::query()->where('qbu_quarter_id', $id)->first();
        if (!$row) {
            return $this->sendError(404, 'NOT_FOUND', 'Allocation not found');
        }

        return $this->sendOk([
            'qbuQuarterId' => (string) $row->qbu_quarter_id,
            'qbuYear' => $row->qbu_year !== null ? (int) $row->qbu_year : null,
            'qbuDescription' => $row->qbu_description,
            'qbuStartDate' => optional($row->qbu_start_date)?->format('Y-m-d'),
            'qbuEndDate' => optional($row->qbu_end_date)?->format('Y-m-d'),
            'qbuStatus' => (string) ($row->qbu_status ?? ''),
        ]);
    }

    public function options(): JsonResponse
    {
        $years = QuarterBudget::query()
            ->select('qbu_year')
            ->distinct()
            ->whereNotNull('qbu_year')
            ->orderByDesc('qbu_year')
            ->pluck('qbu_year')
            ->filter()
            ->map(fn ($y) => ['id' => (string) $y, 'label' => (string) $y])
            ->values();

        $statuses = collect(['ACTIVE', 'INACTIVE'])
            ->map(fn ($s) => ['id' => $s, 'label' => $s])
            ->values();

        return $this->sendOk([
            'smartFilter' => [
                'year' => $years,
                'status' => $statuses,
            ],
            'popupModal' => [
                'status' => $statuses,
            ],
        ]);
    }

    public function update(UpdateQuarterBudgetRequest $request, string $id): JsonResponse
    {
        $row = QuarterBudget::query()->where('qbu_quarter_id', $id)->first();
        if (!$row) {
            return $this->sendError(404, 'NOT_FOUND', 'Allocation not found');
        }

        $row->fill($request->validated());
        $row->save();

        return $this->sendOk([
            'qbuQuarterId' => (string) $row->qbu_quarter_id,
            'successMessage' => 'Allocation updated.',
        ]);
    }

    private function baseQuery(Request $request): Builder
    {
        $query = QuarterBudget::query();

        if (($needle = trim((string) $request->input('q'))) !== '') {
            $like = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $needle).'%';
            $query->where(function ($w) use ($like) {
                $w->where('qbu_year', 'like', $like)
                    ->orWhere('qbu_description', 'like', $like)
                    ->orWhere('qbu_status', 'like', $like);
            });
        }

        if (($v = $request->input('sm_year')) !== null && $v !== '') {
            $query->where('qbu_year', (string) $v);
        }
        if (($v = trim((string) $request->input('sm_description'))) !== '') {
            $like = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $v).'%';
            $query->where('qbu_description', 'like', $like);
        }
        if (($v = trim((string) $request->input('sm_start_date'))) !== '') {
            $query->whereDate('qbu_start_date', $v);
        }
        if (($v = trim((string) $request->input('sm_end_date'))) !== '') {
            $query->whereDate('qbu_end_date', $v);
        }
        if (($v = trim((string) $request->input('sm_status'))) !== '') {
            $query->where('qbu_status', $v);
        }

        return $query;
    }
}
