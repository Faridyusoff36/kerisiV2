<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\LookupDetail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Legacy: NR_DEPRECATION_LOOKUP / lookup_details DEPR_GROUP (Kerisi menu 2455).
 */
class AssetDepreciationGroupController extends Controller
{
    use ApiResponse;

    private function likeEscape(string $needle): string
    {
        return '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], mb_strtolower($needle, 'UTF-8')).'%';
    }

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'page' => ['sometimes', 'integer', 'min:1'],
            'limit' => ['sometimes', 'integer', 'min:1', 'max:200'],
            'q' => ['sometimes', 'string', 'max:200'],
            'sort_by' => ['sometimes', 'string', 'in:lde_value,lde_description,lde_status'],
            'sort_dir' => ['sometimes', 'string', 'in:asc,desc'],
        ]);

        $page = max(1, (int) ($validated['page'] ?? 1));
        $limit = max(1, min(200, (int) ($validated['limit'] ?? 15)));
        $q = trim((string) ($validated['q'] ?? ''));
        $sortBy = (string) ($validated['sort_by'] ?? 'lde_value');
        $sortDir = (string) ($validated['sort_dir'] ?? 'asc');

        $query = LookupDetail::query()
            ->where('lma_code_name', 'DEPR_GROUP')
            ->select([
                'lde_id',
                'lde_value',
                'lde_description',
                'lde_status',
                DB::raw("IFNULL(JSON_UNQUOTE(JSON_EXTRACT(lde_extended_field, '$.depr_rate')), '') AS depr_rate"),
                DB::raw("IFNULL(JSON_UNQUOTE(JSON_EXTRACT(lde_extended_field, '$.estimated_life')), '') AS estimated_life"),
                DB::raw("IFNULL(JSON_UNQUOTE(JSON_EXTRACT(lde_extended_field, '$.statusDesc')), '') AS status_desc"),
            ]);

        if ($q !== '') {
            $like = $this->likeEscape($q);
            $query->whereRaw(
                'LOWER(CONCAT_WS("|", IFNULL(lde_value,""), IFNULL(lde_description,""), IFNULL(lde_status,""),
                    IFNULL(JSON_UNQUOTE(JSON_EXTRACT(lde_extended_field, "$.depr_rate")),""),
                    IFNULL(JSON_UNQUOTE(JSON_EXTRACT(lde_extended_field, "$.estimated_life")),""),
                    IFNULL(JSON_UNQUOTE(JSON_EXTRACT(lde_extended_field, "$.statusDesc")),""))) LIKE ?',
                [$like]
            );
        }

        $sortMap = [
            'lde_value' => 'lde_value',
            'lde_description' => 'lde_description',
            'lde_status' => 'lde_status',
        ];
        $col = $sortMap[$sortBy] ?? 'lde_value';
        $query->orderBy($col, $sortDir === 'desc' ? 'desc' : 'asc')->orderBy('lde_id');

        $total = (clone $query)->count();
        $rows = $query
            ->forPage($page, $limit)
            ->get();

        $data = $rows->values()->map(function ($row, int $i) use ($page, $limit) {
            return [
                'index' => (($page - 1) * $limit) + $i + 1,
                'lde_id' => (int) $row->lde_id,
                'lde_value' => (string) $row->lde_value,
                'lde_description' => (string) $row->lde_description,
                'depr_rate' => (string) $row->depr_rate,
                'estimated_life' => (string) $row->estimated_life,
                'status_desc' => (string) $row->status_desc,
                'lde_status' => (string) $row->lde_status,
            ];
        });

        return $this->sendOk($data, [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'totalPages' => $limit > 0 ? (int) ceil($total / $limit) : 1,
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $row = LookupDetail::query()->where('lma_code_name', 'DEPR_GROUP')->where('lde_id', $id)->first();
        if (! $row) {
            return $this->sendError(404, 'NOT_FOUND', 'Depreciation group not found');
        }
        $ext = is_array($row->lde_extended_field) ? $row->lde_extended_field : [];

        return $this->sendOk([
            'lde_id' => (int) $row->lde_id,
            'lde_value' => (string) $row->lde_value,
            'lde_description' => (string) $row->lde_description,
            'depr_rate' => (string) ($ext['depr_rate'] ?? ''),
            'estimated_life' => (string) ($ext['estimated_life'] ?? ''),
            'status_desc' => (string) ($ext['statusDesc'] ?? $ext['status_desc'] ?? ''),
            'lde_status' => (string) $row->lde_status,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'lde_value' => ['required', 'string', 'max:100', Rule::unique(LookupDetail::class, 'lde_value')->where(fn ($q) => $q->where('lma_code_name', 'DEPR_GROUP'))],
            'lde_description' => ['required', 'string', 'max:500'],
            'depr_rate' => ['nullable', 'string', 'max:50'],
            'estimated_life' => ['nullable', 'string', 'max:50'],
            'status_desc' => ['nullable', 'string', 'max:100'],
            'lde_status' => ['required', 'string', 'max:20'],
        ]);

        $nextId = ((int) LookupDetail::query()->max('lde_id')) + 1;
        $extended = [
            'depr_rate' => $data['depr_rate'] ?? '',
            'estimated_life' => $data['estimated_life'] ?? '',
            'statusDesc' => $data['status_desc'] ?? '',
        ];

        LookupDetail::query()->create([
            'lde_id' => $nextId,
            'lma_code_name' => 'DEPR_GROUP',
            'lde_value' => trim($data['lde_value']),
            'lde_description' => trim($data['lde_description']),
            'lde_status' => $data['lde_status'],
            'lde_extended_field' => $extended,
            'createddate' => now()->format('Y-m-d H:i:s'),
        ]);

        return $this->sendCreated(['lde_id' => $nextId]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $row = LookupDetail::query()->where('lma_code_name', 'DEPR_GROUP')->where('lde_id', $id)->first();
        if (! $row) {
            return $this->sendError(404, 'NOT_FOUND', 'Depreciation group not found');
        }

        $data = $request->validate([
            'lde_value' => ['required', 'string', 'max:100', Rule::unique(LookupDetail::class, 'lde_value')->where(fn ($q) => $q->where('lma_code_name', 'DEPR_GROUP'))->ignore($id, 'lde_id')],
            'lde_description' => ['required', 'string', 'max:500'],
            'depr_rate' => ['nullable', 'string', 'max:50'],
            'estimated_life' => ['nullable', 'string', 'max:50'],
            'status_desc' => ['nullable', 'string', 'max:100'],
            'lde_status' => ['required', 'string', 'max:20'],
        ]);

        $extended = [
            'depr_rate' => $data['depr_rate'] ?? '',
            'estimated_life' => $data['estimated_life'] ?? '',
            'statusDesc' => $data['status_desc'] ?? '',
        ];

        $row->update([
            'lde_value' => trim($data['lde_value']),
            'lde_description' => trim($data['lde_description']),
            'lde_status' => $data['lde_status'],
            'lde_extended_field' => $extended,
            'updateddate' => now()->format('Y-m-d H:i:s'),
        ]);

        return $this->sendOk(['success' => true]);
    }

    public function destroy(int $id): JsonResponse
    {
        $deleted = LookupDetail::query()->where('lma_code_name', 'DEPR_GROUP')->where('lde_id', $id)->delete();

        return $deleted ? $this->sendOk(['success' => true]) : $this->sendError(404, 'NOT_FOUND', 'Depreciation group not found');
    }
}
