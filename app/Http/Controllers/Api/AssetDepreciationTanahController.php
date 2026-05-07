<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Legacy: NR_DEPRECATION_SETUP / asset_depr_tanah (Kerisi menu 2483).
 */
class AssetDepreciationTanahController extends Controller
{
    use ApiResponse;

    private function cx()
    {
        return DB::connection('mysql_secondary');
    }

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
            'sort_dir' => ['sometimes', 'string', 'in:asc,desc'],
        ]);

        $page = max(1, (int) ($validated['page'] ?? 1));
        $limit = max(1, min(200, (int) ($validated['limit'] ?? 15)));
        $q = trim((string) ($validated['q'] ?? ''));
        $sortDir = (string) ($validated['sort_dir'] ?? 'asc');

        $base = $this->cx()->table('asset_depr_tanah')
            ->select([
                'adt_depr_id',
                DB::raw("CONCAT(itm_item_code, ' - ', IFNULL(JSON_UNQUOTE(JSON_EXTRACT(adt_extended_field, '$.aim_asset_desc')), '')) AS itm_item_label"),
                'itm_item_code',
                'adt_estimated_life',
                'adt_status',
                DB::raw("IF(CAST(IFNULL(adt_status,'') AS CHAR) = '1', 'ACTIVE', 'INACTIVE') AS status_label"),
                'createddate',
                'createdby',
            ]);

        if ($q !== '') {
            $like = $this->likeEscape($q);
            $base->whereRaw(
                'LOWER(CONCAT_WS("|", IFNULL(CAST(adt_depr_id AS CHAR),""), IFNULL(itm_item_code,""), IFNULL(adt_estimated_life,""), IFNULL(adt_status,""))) LIKE ?',
                [$like]
            );
        }

        $total = (clone $base)->count();
        $rows = $base
            ->orderBy('adt_depr_id', $sortDir === 'desc' ? 'desc' : 'asc')
            ->forPage($page, $limit)
            ->get();

        $data = $rows->values()->map(function ($row, int $i) use ($page, $limit) {
            return [
                'index' => (($page - 1) * $limit) + $i + 1,
                'adt_depr_id' => (int) $row->adt_depr_id,
                'itm_item_code' => (string) $row->itm_item_code,
                'itm_item_label' => (string) $row->itm_item_label,
                'adt_estimated_life' => (string) $row->adt_estimated_life,
                'adt_status' => (string) $row->adt_status,
                'status_label' => (string) $row->status_label,
                'createddate' => $row->createddate ?? null,
                'createdby' => $row->createdby ?? null,
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
        $row = $this->cx()->table('asset_depr_tanah')->where('adt_depr_id', $id)->first();
        if (! $row) {
            return $this->sendError(404, 'NOT_FOUND', 'Record not found');
        }

        $extRaw = $row->adt_extended_field ?? null;
        $ext = [];
        if (is_string($extRaw) && $extRaw !== '') {
            $dec = json_decode($extRaw, true);
            $ext = is_array($dec) ? $dec : [];
        }

        return $this->sendOk([
            'adt_depr_id' => (int) $row->adt_depr_id,
            'itm_item_code' => (string) $row->itm_item_code,
            'adt_estimated_life' => (string) ($row->adt_estimated_life ?? ''),
            'adt_status' => (string) ($row->adt_status ?? ''),
            'status_desc' => (string) ($ext['statusDesc'] ?? ''),
            'aim_asset_desc' => (string) ($ext['aim_asset_desc'] ?? ''),
            'createddate' => $row->createddate ?? null,
            'createdby' => $row->createdby ?? null,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'itm_item_code' => ['required', 'string', 'max:120'],
            'adt_estimated_life' => ['nullable', 'string', 'max:50'],
            'adt_status' => ['required', 'string', 'max:10'],
            'status_desc' => ['nullable', 'string', 'max:100'],
            'aim_asset_desc' => ['nullable', 'string', 'max:500'],
        ]);

        $nextId = ((int) $this->cx()->table('asset_depr_tanah')->max('adt_depr_id')) + 1;
        $extended = json_encode([
            'statusDesc' => $data['status_desc'] ?? '',
            'aim_asset_desc' => $data['aim_asset_desc'] ?? '',
        ]);

        $this->cx()->table('asset_depr_tanah')->insert([
            'adt_depr_id' => $nextId,
            'itm_item_code' => trim($data['itm_item_code']),
            'adt_estimated_life' => $data['adt_estimated_life'] ?? '',
            'adt_status' => $data['adt_status'],
            'adt_extended_field' => $extended,
            'createddate' => now()->format('Y-m-d H:i:s'),
            'createdby' => $request->user()?->name ?? 'system',
        ]);

        return $this->sendCreated(['adt_depr_id' => $nextId]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $exists = $this->cx()->table('asset_depr_tanah')->where('adt_depr_id', $id)->exists();
        if (! $exists) {
            return $this->sendError(404, 'NOT_FOUND', 'Record not found');
        }

        $data = $request->validate([
            'itm_item_code' => ['required', 'string', 'max:120'],
            'adt_estimated_life' => ['nullable', 'string', 'max:50'],
            'adt_status' => ['required', 'string', 'max:10'],
            'status_desc' => ['nullable', 'string', 'max:100'],
            'aim_asset_desc' => ['nullable', 'string', 'max:500'],
        ]);

        $row = $this->cx()->table('asset_depr_tanah')->where('adt_depr_id', $id)->first();
        $ext = [];
        if ($row && is_string($row->adt_extended_field ?? null) && $row->adt_extended_field !== '') {
            $dec = json_decode($row->adt_extended_field, true);
            $ext = is_array($dec) ? $dec : [];
        }
        $ext['statusDesc'] = $data['status_desc'] ?? ($ext['statusDesc'] ?? '');
        if (array_key_exists('aim_asset_desc', $data)) {
            $ext['aim_asset_desc'] = $data['aim_asset_desc'];
        }

        $this->cx()->table('asset_depr_tanah')->where('adt_depr_id', $id)->update([
            'itm_item_code' => trim($data['itm_item_code']),
            'adt_estimated_life' => $data['adt_estimated_life'] ?? '',
            'adt_status' => $data['adt_status'],
            'adt_extended_field' => json_encode($ext),
        ]);

        return $this->sendOk(['success' => true]);
    }

    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->cx()->table('asset_depr_tanah')->where('adt_depr_id', $id)->delete();

        return $deleted ? $this->sendOk(['success' => true]) : $this->sendError(404, 'NOT_FOUND', 'Record not found');
    }
}
