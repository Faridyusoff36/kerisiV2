<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBudgetCodeRequest;
use App\Http\Requests\UpdateBudgetCodeRequest;
use App\Http\Traits\ApiResponse;
use App\Models\AccountMain;
use App\Models\LkpBudgetCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Budget Setup > Budget Code page (PAGEID 1475 / MENUID 1796).
 *
 * Refactored from the legacy BL `MM_API_BUDGET_SETUP_BUDGETCODE`. The legacy
 * BL exposed the standard four entry points: list (`?BudgetCode=1`),
 * `getData`, `saveItem`, and `download` for CSV/PDF (handled client-side
 * here via `useDatatableFeatures`).
 */
class BudgetCodeController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $page = (int) $request->input('page', 1);
        $limit = (int) $request->input('limit', 10);
        $q = trim((string) $request->input('q', ''));

        $query = LkpBudgetCode::query()
            ->select([
                'lbc_id',
                'lbc_level',
                'lbc_budget_code',
                'lbc_description',
                'lbc_status',
            ]);

        if ($q !== '') {
            $needle = mb_strtolower($q, 'UTF-8');
            $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $needle) . '%';
            $query->where(function ($builder) use ($like) {
                $builder->whereRaw('LOWER(IFNULL(lbc_id, "")) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(IFNULL(lbc_level, "")) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(IFNULL(lbc_budget_code, "")) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(IFNULL(lbc_description, "")) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(IF(lbc_status = 1, "ACTIVE", "INACTIVE")) LIKE ?', [$like]);
            });
        }

        if ($request->filled('lbc_level_filter')) {
            $query->where('lbc_level', (int) $request->input('lbc_level_filter'));
        }
        if ($request->filled('lbc_budget_code_filter')) {
            $query->where('lbc_budget_code', (string) $request->input('lbc_budget_code_filter'));
        }
        if ($request->filled('lbc_description_filter')) {
            $needle = mb_strtolower((string) $request->input('lbc_description_filter'), 'UTF-8');
            $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $needle) . '%';
            $query->whereRaw('LOWER(IFNULL(lbc_description, "")) LIKE ?', [$like]);
        }
        if ($request->filled('lbc_status_filter')) {
            $status = strtoupper((string) $request->input('lbc_status_filter')) === 'ACTIVE' ? '1' : '0';
            $query->where('lbc_status', $status);
        }

        $total = (clone $query)->count();
        $rows = $query->orderBy('lbc_level', 'asc')
            ->orderBy('lbc_budget_code', 'asc')
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get()
            ->values()
            ->map(fn ($row, $idx) => [
                'index' => (($page - 1) * $limit) + $idx + 1,
                'lbc_id' => (int) $row->lbc_id,
                'lbc_level' => (int) $row->lbc_level,
                'lbc_budget_code' => (string) $row->lbc_budget_code,
                'lbc_description' => (string) ($row->lbc_description ?? ''),
                'lbc_status' => (string) $row->lbc_status === '1' ? 'ACTIVE' : 'INACTIVE',
                'lbc_status_value' => (string) $row->lbc_status === '1' ? 1 : 0,
            ]);

        return $this->sendOk($rows, [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'totalPages' => $limit > 0 ? (int) ceil($total / $limit) : 0,
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $row = LkpBudgetCode::query()->where('lbc_id', $id)->first();
        if (! $row) {
            return $this->sendError(404, 'NOT_FOUND', 'Budget Code not found');
        }

        return $this->sendOk([
            'lbc_id' => (int) $row->lbc_id,
            'lbc_level' => (int) $row->lbc_level,
            'lbc_budget_code' => (string) $row->lbc_budget_code,
            'lbc_description' => (string) ($row->lbc_description ?? ''),
            'lbc_status' => (string) $row->lbc_status === '1' ? 'ACTIVE' : 'INACTIVE',
            'lbc_status_value' => (string) $row->lbc_status === '1' ? 1 : 0,
        ]);
    }

    public function options(): JsonResponse
    {
        $levels = AccountMain::query()
            ->where('acm_acct_status', '1')
            ->where('acm_acct_level', '>=', 3)
            ->select('acm_acct_level')
            ->distinct()
            ->orderBy('acm_acct_level')
            ->pluck('acm_acct_level')
            ->map(fn ($lvl) => ['id' => (string) $lvl, 'label' => (string) $lvl])
            ->values();

        $codes = LkpBudgetCode::query()
            ->select('lbc_budget_code')
            ->whereNotNull('lbc_budget_code')
            ->distinct()
            ->orderBy('lbc_budget_code')
            ->pluck('lbc_budget_code')
            ->filter()
            ->map(fn ($code) => ['id' => (string) $code, 'label' => (string) $code])
            ->values();

        return $this->sendOk([
            'smartFilter' => [
                'level' => $levels,
                'budgetCode' => $codes,
                'status' => [
                    ['id' => 'ACTIVE', 'label' => 'ACTIVE'],
                    ['id' => 'INACTIVE', 'label' => 'INACTIVE'],
                ],
            ],
            'popupModal' => [
                'level' => $levels,
                'budgetCode' => $codes,
                'status' => [
                    ['id' => 'ACTIVE', 'label' => 'ACTIVE'],
                    ['id' => 'INACTIVE', 'label' => 'INACTIVE'],
                ],
            ],
        ]);
    }

    public function store(StoreBudgetCodeRequest $request): JsonResponse
    {
        $data = $request->validated();
        $level = (int) $data['lbc_level'];
        $code = trim((string) $data['lbc_budget_code']);

        $exists = LkpBudgetCode::query()
            ->where('lbc_level', $level)
            ->where('lbc_budget_code', $code)
            ->exists();
        if ($exists) {
            return $this->sendError(400, 'BAD_REQUEST', 'Budget Code with this Level and Code already exists.');
        }

        $nextId = ((int) LkpBudgetCode::query()->max('lbc_id')) + 1;
        $row = LkpBudgetCode::create([
            'lbc_id' => $nextId,
            'lbc_level' => $level,
            'lbc_budget_code' => $code,
            'lbc_description' => $data['lbc_description'] ?? null,
            'lbc_status' => strtoupper((string) $data['lbc_status']) === 'ACTIVE' ? '1' : '0',
            'createddate' => now(),
            'createdby' => $request->user()?->name ?? 'system',
        ]);

        return $this->sendCreated(['id' => (int) $row->lbc_id]);
    }

    public function update(UpdateBudgetCodeRequest $request, int $id): JsonResponse
    {
        $row = LkpBudgetCode::query()->where('lbc_id', $id)->first();
        if (! $row) {
            return $this->sendError(404, 'NOT_FOUND', 'Budget Code not found');
        }

        $data = $request->validated();
        // Per the legacy BL only the status flips on update — the level and
        // budget_code are immutable once a row exists. We additionally keep
        // the description editable because the modal exposes it.
        $row->update([
            'lbc_description' => array_key_exists('lbc_description', $data)
                ? ($data['lbc_description'] ?? null)
                : $row->lbc_description,
            'lbc_status' => strtoupper((string) $data['lbc_status']) === 'ACTIVE' ? '1' : '0',
            'updateddate' => now(),
            'updatedby' => $request->user()?->name ?? 'system',
        ]);

        return $this->sendOk(['success' => true]);
    }
}
