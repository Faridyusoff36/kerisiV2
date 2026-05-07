<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAssetSecretariatRequest;
use App\Http\Requests\UpdateAssetSecretariatRequest;
use App\Http\Traits\ApiResponse;
use App\Models\AssetSecretariat;
use App\Models\LookupDetail;
use App\Models\Staff;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssetSecretariatController extends Controller
{
    use ApiResponse;

    public function options(Request $request): JsonResponse
    {
        $itemSubcats = LookupDetail::query()
            ->where('lma_code_name', 'ITEM_SUBCAT_TYPE')
            ->where('lde_status', 1)
            ->orderBy('lde_sorting')
            ->get(['lde_value', 'lde_description'])
            ->map(fn (LookupDetail $r) => [
                'value' => (string) $r->lde_value,
                'label' => trim((string) $r->lde_description).' ('.(string) $r->lde_value.')',
            ]);

        $needle = mb_strtolower(trim((string) $request->input('staff_q', '')), 'UTF-8');

        $staffOpts = Staff::query()
            ->when($needle !== '', function ($qb) use ($needle) {
                $like = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $needle).'%';
                $qb->where(function ($w) use ($like) {
                    $w->whereRaw('LOWER(IFNULL(stf_staff_id, "")) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(IFNULL(stf_staff_name, "")) LIKE ?', [$like]);
                });
            })
            ->orderBy('stf_staff_name')
            ->limit($needle !== '' ? 100 : 900)
            ->get(['stf_staff_id', 'stf_staff_name'])
            ->map(fn (Staff $s) => [
                'value' => (string) $s->stf_staff_id,
                'label' => trim((string) $s->stf_staff_name).' ('.(string) $s->stf_staff_id.')',
            ]);

        return $this->sendOk([
            'itemSubcats' => $itemSubcats,
            'staff' => $staffOpts,
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $page = (int) $request->input('page', 1);
        $limit = (int) $request->input('limit', 5);
        $q = $request->input('q');
        $sortBy = $request->input('sort_by', 'isc_type');
        $sortDir = $request->input('sort_dir', 'asc');

        $allowedSortBy = ['isc_type', 'stf_staff_id', 'stf_staff_id_superior', 'stf_staff_id_hod', 'ast_status', 'created_date'];
        if (! in_array($sortBy, $allowedSortBy, true)) {
            $sortBy = 'isc_type';
        }
        if (! in_array($sortDir, ['asc', 'desc'], true)) {
            $sortDir = 'asc';
        }

        $base = AssetSecretariat::query()->with(['staff', 'superior', 'hod']);

        if ($q) {
            $needle = mb_strtolower(trim((string) $q), 'UTF-8');
            $like = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $needle).'%';

            $base->where(function ($builder) use ($like) {
                $builder->whereRaw('CAST(ast_id AS CHAR) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(IFNULL(isc_type, "")) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(IFNULL(stf_staff_id, "")) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(IFNULL(stf_staff_id_superior, "")) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(IFNULL(stf_staff_id_hod, "")) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(IF(ast_status = 1, "active", "inactive")) LIKE ?', [$like]);
            });
        }

        $total = (clone $base)->count();

        if ($sortBy === 'created_date') {
            $base->orderBy('createddate', $sortDir);
        } else {
            $base->orderBy($sortBy, $sortDir);
        }

        $rows = $base
            ->skip(max(0, ($page - 1) * $limit))
            ->take($limit)
            ->get();

        $iscValues = $rows->pluck('isc_type')->filter()->unique()->values();
        $iscLabels = $iscValues->isEmpty()
            ? collect()
            : LookupDetail::query()
                ->where('lma_code_name', 'ITEM_SUBCAT_TYPE')
                ->whereIn('lde_value', $iscValues)
                ->get()
                ->keyBy(fn (LookupDetail $d) => (string) $d->lde_value);

        $data = $rows->values()->map(function (AssetSecretariat $row, int $i) use ($page, $limit, $iscLabels) {
            $staff = $row->staff;
            $sup = $row->superior;
            $hod = $row->hod;
            $isc = $iscLabels->get((string) $row->isc_type);

            return [
                'index' => (($page - 1) * $limit) + $i + 1,
                'ast_id' => (int) $row->ast_id,
                'isc_type' => (string) $row->isc_type,
                'isc_type_display' => $isc ? trim((string) $isc->lde_description).' ('.(string) $row->isc_type.')' : (string) $row->isc_type,
                'stf_staff_id' => (string) $row->stf_staff_id,
                'stf_staff_id_superior' => (string) $row->stf_staff_id_superior,
                'stf_staff_id_hod' => (string) $row->stf_staff_id_hod,
                'staff_label' => $staff ? trim((string) $staff->stf_staff_name).' ('.(string) $staff->stf_staff_id.')' : (string) $row->stf_staff_id,
                'superior_label' => $sup ? trim((string) $sup->stf_staff_name).' ('.(string) $sup->stf_staff_id.')' : (string) $row->stf_staff_id_superior,
                'hod_label' => $hod ? trim((string) $hod->stf_staff_name).' ('.(string) $hod->stf_staff_id.')' : (string) $row->stf_staff_id_hod,
                'ast_status' => ((int) $row->ast_status === 1) ? 'ACTIVE' : 'INACTIVE',
                'ast_status_value' => (int) $row->ast_status,
                'created_date' => $row->createddate?->format('Y-m-d H:i:s'),
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
        $row = AssetSecretariat::query()->find($id);
        if (! $row) {
            return $this->sendError(404, 'NOT_FOUND', 'Secretariat row not found');
        }

        return $this->sendOk([
            'ast_id' => (int) $row->ast_id,
            'isc_type' => (string) $row->isc_type,
            'stf_staff_id' => (string) $row->stf_staff_id,
            'stf_staff_id_superior' => (string) $row->stf_staff_id_superior,
            'stf_staff_id_hod' => (string) $row->stf_staff_id_hod,
            'ast_status' => (int) $row->ast_status,
        ]);
    }

    public function store(StoreAssetSecretariatRequest $request): JsonResponse
    {
        $data = $request->validated();

        $nextId = ((int) AssetSecretariat::query()->max('ast_id')) + 1;
        $status = ((int) $data['ast_status'] === 1) ? 1 : 0;

        $row = AssetSecretariat::query()->create([
            'ast_id' => $nextId,
            'isc_type' => $data['isc_type'],
            'stf_staff_id' => $data['stf_staff_id'],
            'stf_staff_id_superior' => $data['stf_staff_id_superior'],
            'stf_staff_id_hod' => $data['stf_staff_id_hod'],
        'ast_status' => $status,
            'createdby' => $request->user()?->name ?? 'system',
            'createddate' => now(),
        ]);

        return $this->sendCreated([
            'id' => (int) $row->ast_id,
            'ast_id' => (int) $row->ast_id,
        ]);
    }

    public function update(UpdateAssetSecretariatRequest $request, int $id): JsonResponse
    {
        $row = AssetSecretariat::query()->find($id);
        if (! $row) {
            return $this->sendError(404, 'NOT_FOUND', 'Secretariat row not found');
        }

        $data = $request->validated();
        $status = ((int) $data['ast_status'] === 1) ? 1 : 0;

        $row->update([
            'isc_type' => $data['isc_type'],
            'stf_staff_id' => $data['stf_staff_id'],
            'stf_staff_id_superior' => $data['stf_staff_id_superior'],
            'stf_staff_id_hod' => $data['stf_staff_id_hod'],
            'ast_status' => $status,
        ]);

        return $this->sendOk(['success' => true]);
    }

    public function destroy(int $id): JsonResponse
    {
        $row = AssetSecretariat::query()->find($id);
        if (! $row) {
            return $this->sendError(404, 'NOT_FOUND', 'Secretariat row not found');
        }

        $row->delete();

        return $this->sendOk(['success' => true]);
    }
}
