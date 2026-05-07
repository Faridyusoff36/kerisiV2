<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAssetDisposeMethodRequest;
use App\Http\Requests\UpdateAssetDisposeMethodRequest;
use App\Http\Traits\ApiResponse;
use App\Models\AssetDisposeMethod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AssetDisposeMethodController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $page = (int) $request->input('page', 1);
        $limit = (int) $request->input('limit', 15);
        $q = $request->input('q');
        $sortBy = $request->input('sort_by', 'adt_code');
        $sortDir = $request->input('sort_dir', 'asc');

        $allowedSortBy = ['adt_code', 'adt_name', 'adt_status'];
        if (! in_array($sortBy, $allowedSortBy, true)) {
            $sortBy = 'adt_code';
        }
        if (! in_array($sortDir, ['asc', 'desc'], true)) {
            $sortDir = 'asc';
        }

        $query = AssetDisposeMethod::query();

        if ($q) {
            $needle = mb_strtolower(trim((string) $q), 'UTF-8');
            $like = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $needle).'%';

            $query->where(function ($builder) use ($like) {
                $builder->whereRaw('CAST(adt_id AS CHAR) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(IFNULL(adt_code, "")) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(IFNULL(adt_name, "")) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(IF(adt_status = 1, "active", "inactive")) LIKE ?', [$like]);
            });
        }

        $total = (clone $query)->count();

        if ($sortBy === 'adt_status') {
            $query->orderBy('adt_status', $sortDir);
        } else {
            $query->orderBy($sortBy, $sortDir);
        }

        $rows = $query
            ->skip(max(0, ($page - 1) * $limit))
            ->take($limit)
            ->get();

        $data = $rows->values()->map(function (AssetDisposeMethod $row, int $i) use ($page, $limit) {
            $statusLabel = ((int) $row->adt_status === 1) ? 'ACTIVE' : 'INACTIVE';

            return [
                'index' => (($page - 1) * $limit) + $i + 1,
                'adt_id' => (int) $row->adt_id,
                'adt_code' => $row->adt_code,
                'adt_name' => $row->adt_name,
                'adt_status' => $statusLabel,
                'adt_status_value' => (int) $row->adt_status,
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
        $row = AssetDisposeMethod::query()->find($id);
        if (! $row) {
            return $this->sendError(404, 'NOT_FOUND', 'Dispose method not found');
        }

        return $this->sendOk([
            'adt_id' => (int) $row->adt_id,
            'adt_code' => $row->adt_code,
            'adt_name' => $row->adt_name,
            'adt_status' => (int) $row->adt_status,
        ]);
    }

    public function store(StoreAssetDisposeMethodRequest $request): JsonResponse
    {
        $data = $request->validated();

        $code = Str::upper(trim($data['adt_code']));
        $exists = AssetDisposeMethod::query()->where('adt_code', $code)->exists();
        if ($exists) {
            return $this->sendError(409, 'DUPLICATE_CODE', 'Dispose code already exists');
        }

        $nextId = ((int) AssetDisposeMethod::query()->max('adt_id')) + 1;
        $status = ((int) $data['adt_status'] === 1) ? 1 : 0;
        $statusDesc = $status === 1 ? 'ACTIVE' : 'INACTIVE';

        $row = AssetDisposeMethod::query()->create([
            'adt_id' => $nextId,
            'adt_code' => $code,
            'adt_name' => trim($data['adt_name']),
            'adt_status' => $status,
            'adt_extended_field' => json_encode(['statusDesc' => $statusDesc]),
            'createdby' => $request->user()?->name ?? 'system',
        ]);

        return $this->sendCreated([
            'id' => (int) $row->adt_id,
            'adt_id' => (int) $row->adt_id,
        ]);
    }

    public function update(UpdateAssetDisposeMethodRequest $request, int $id): JsonResponse
    {
        $row = AssetDisposeMethod::query()->find($id);
        if (! $row) {
            return $this->sendError(404, 'NOT_FOUND', 'Dispose method not found');
        }

        $data = $request->validated();
        $code = Str::upper(trim($data['adt_code']));

        $exists = AssetDisposeMethod::query()
            ->where('adt_code', $code)
            ->where('adt_id', '!=', $id)
            ->exists();
        if ($exists) {
            return $this->sendError(409, 'DUPLICATE_CODE', 'Dispose code already exists');
        }

        $status = ((int) $data['adt_status'] === 1) ? 1 : 0;
        $statusDesc = $status === 1 ? 'ACTIVE' : 'INACTIVE';

        $row->update([
            'adt_code' => $code,
            'adt_name' => trim($data['adt_name']),
            'adt_status' => $status,
            'adt_extended_field' => json_encode(['statusDesc' => $statusDesc]),
            'updatedby' => $request->user()?->name ?? 'system',
        ]);

        return $this->sendOk(['success' => true]);
    }
}
