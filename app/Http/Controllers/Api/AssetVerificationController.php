<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateAssetVerificationRequest;
use App\Http\Traits\ApiResponse;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Asset > Asset Verification (PAGEID 2123 / MENUID 2574).
 *
 * Legacy BL: `AFQ_ASSETVERIFICATION_API`. RBAC / PTJ scoping from
 * `organization_authorization` is not wired to Sanctum users yet — same
 * limitation as {@see AssetInventoryListController}.
 */
class AssetVerificationController extends Controller
{
    use ApiResponse;

    private const CONN = 'mysql_secondary';

    private const SORTABLE = [
        'aim_asset_code',
        'aim_asset_desc',
        'aim_model',
        'aim_brand_name',
        'aim_serial_no',
        'aim_cur_bldg',
        'aim_cur_room',
        'aim_real_cur_bldg',
        'aim_real_cur_room',
        'aim_asset_status',
        'aim_verification_sts',
        'aim_verify_date',
    ];

    public function index(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->input('page', 1));
        $limit = max(1, min(200, (int) $request->input('limit', 10)));
        $q = trim((string) $request->input('q', ''));
        $sortBy = (string) $request->input('sort_by', 'aim_asset_code');
        $sortDir = strtolower((string) $request->input('sort_dir', 'asc')) === 'desc' ? 'desc' : 'asc';
        if (! in_array($sortBy, self::SORTABLE, true)) {
            $sortBy = 'aim_asset_code';
        }

        $base = $this->baseQuery($request);

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('__',
                    IFNULL(aim.aim_asset_code,''),
                    IFNULL(aim.aim_asset_desc,''),
                    IFNULL(aim.aim_model,''),
                    IFNULL(aim.aim_brand_name,''),
                    IFNULL(aim.aim_serial_no,''),
                    IFNULL(aim.aim_cur_bldg,''),
                    IFNULL(aim.aim_cur_room,''),
                    IFNULL(aim.aim_real_cur_bldg,''),
                    IFNULL(aim.aim_real_cur_room,''),
                    IFNULL(aim.aim_asset_status,''),
                    IFNULL(aim.aim_verification_sts,'')
                )) LIKE ?",
                [$like]
            );
        }

        $total = (clone $base)->count();

        $rows = (clone $base)
            ->select([
                'aim.aim_asset_id',
                'aim.aim_asset_code',
                'aim.aim_asset_desc',
                'aim.aim_model',
                'aim.aim_brand_name',
                'aim.aim_serial_no',
                DB::raw("CONCAT_WS(' - ', aim.aim_cur_bldg, bdm.bdm_name) AS current_building"),
                DB::raw("CONCAT_WS(' - ', aim.aim_cur_room, rmm.rmm_name) AS current_room"),
                DB::raw("CONCAT_WS(' - ', aim.aim_real_cur_bldg, bdmr.bdm_name) AS actual_building"),
                DB::raw("CONCAT_WS(' - ', aim.aim_real_cur_room, rmmr.rmm_name) AS actual_room"),
                'aim.aim_asset_status AS asset_status_label',
                'aim.aim_verification_sts',
                DB::raw('DATE_FORMAT(IFNULL(aim.aim_verify2_date, aim.aim_verify_date), \'%Y-%m-%d\') AS verify_day'),
            ])
            ->orderBy('aim.'.$sortBy, $sortDir)
            ->orderBy('aim.aim_asset_id', 'desc')
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        $data = $rows->map(function ($r, int $i) use ($page, $limit) {
            $locked = strtoupper((string) ($r->aim_verification_sts ?? '')) === 'CHECKED';

            return [
                'index' => (($page - 1) * $limit) + $i + 1,
                'assetId' => (int) $r->aim_asset_id,
                'assetCode' => $r->aim_asset_code,
                'assetDescription' => $r->aim_asset_desc,
                'model' => $r->aim_model,
                'brand' => $r->aim_brand_name,
                'serialNo' => $r->aim_serial_no,
                'currentBuilding' => $r->current_building,
                'currentRoom' => $r->current_room,
                'actualBuilding' => $r->actual_building,
                'actualRoom' => $r->actual_room,
                'assetStatus' => $r->asset_status_label,
                'verificationStatus' => $r->aim_verification_sts,
                'verifyDate' => $r->verify_day,
                'verificationLocked' => $locked,
            ];
        })->all();

        return $this->sendOk($data, [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'totalPages' => (int) ceil($total / max(1, $limit)),
        ]);
    }

    public function show(int $assetId): JsonResponse
    {
        $row = DB::connection(self::CONN)
            ->table('asset_inventory_main as aim')
            ->leftJoin('building_main as bdm', 'aim.aim_cur_bldg', '=', 'bdm.bdm_code')
            ->leftJoin('room_main as rmm', function ($join) {
                $join->on('rmm.bdm_code', '=', 'bdm.bdm_code')
                    ->on('rmm.rmm_code', '=', 'aim.aim_cur_room');
            })
            ->where('aim.aim_asset_id', $assetId)
            ->select([
                'aim.aim_asset_code',
                'aim.aim_asset_desc',
                'aim.aim_model',
                'aim.aim_brand_name',
                'aim.aim_serial_no',
                'aim.aim_cur_bldg',
                'aim.aim_cur_room',
                'aim.aim_real_cur_bldg',
                'aim.aim_real_cur_room',
                'aim.aim_asset_status',
                'aim.aim_extended_field',
                DB::raw('DATE_FORMAT(IFNULL(aim.aim_verify2_date, aim.aim_verify_date), \'%d/%m/%Y\') AS verify_label'),
                DB::raw("CONCAT_WS(' - ', aim.aim_cur_bldg, bdm.bdm_name) AS cur_bldg_label"),
                DB::raw("CONCAT_WS(' - ', aim.aim_cur_room, rmm.rmm_name) AS cur_room_label"),
            ])
            ->first();

        if (! $row) {
            return $this->sendError(404, 'NOT_FOUND', 'Asset not found');
        }

        $ext = [];
        if (! empty($row->aim_extended_field)) {
            $decoded = json_decode((string) $row->aim_extended_field, true);
            $ext = is_array($decoded) ? $decoded : [];
        }

        return $this->sendOk([
            'assetCode' => $row->aim_asset_code,
            'assetDescription' => $row->aim_asset_desc,
            'model' => $row->aim_model,
            'brandName' => $row->aim_brand_name,
            'serialNo' => $row->aim_serial_no,
            'currentBuilding' => $row->cur_bldg_label,
            'currentRoom' => $row->cur_room_label,
            'realCurBuilding' => $row->aim_real_cur_bldg,
            'realCurRoom' => $row->aim_real_cur_room,
            'realCurBuildingDesc' => $ext['aim_real_cur_bldg_desc'] ?? null,
            'realCurRoomDesc' => $ext['aim_real_cur_room_desc'] ?? null,
            'assetStatus' => $row->aim_asset_status,
            'verifyDate' => $row->verify_label,
        ]);
    }

    public function update(UpdateAssetVerificationRequest $request, int $assetId): JsonResponse
    {
        $conn = DB::connection(self::CONN);
        $current = $conn->table('asset_inventory_main')->where('aim_asset_id', $assetId)->first();
        if (! $current) {
            return $this->sendError(404, 'NOT_FOUND', 'Asset not found');
        }

        if (strtoupper((string) ($current->aim_verification_sts ?? '')) === 'CHECKED') {
            return $this->sendError(409, 'CONFLICT', 'This asset verification is locked.');
        }

        $ext = [];
        if (! empty($current->aim_extended_field)) {
            $decoded = json_decode((string) $current->aim_extended_field, true);
            $ext = is_array($decoded) ? $decoded : [];
        }
        $ext['aim_real_cur_bldg_desc'] = $request->input('real_cur_building_desc');
        $ext['aim_real_cur_room_desc'] = $request->input('real_cur_room_desc');

        $conn->table('asset_inventory_main')
            ->where('aim_asset_id', $assetId)
            ->update([
                'aim_real_cur_bldg' => $request->input('real_cur_building'),
                'aim_real_cur_room' => $request->input('real_cur_room'),
                'aim_extended_field' => json_encode($ext, JSON_UNESCAPED_UNICODE),
                'aim_asset_status' => $request->input('asset_status'),
                'aim_verification_sts' => 'DRAFT',
            ]);

        return $this->sendOk(['success' => true]);
    }

    private function baseQuery(Request $request): QueryBuilder
    {
        $b = DB::connection(self::CONN)
            ->table('asset_inventory_main as aim')
            ->leftJoin('building_main as bdm', 'aim.aim_cur_bldg', '=', 'bdm.bdm_code')
            ->leftJoin('room_main as rmm', function ($join) {
                $join->on('rmm.bdm_code', '=', 'bdm.bdm_code')
                    ->on('rmm.rmm_code', '=', 'aim.aim_cur_room');
            })
            ->leftJoin('building_main as bdmr', 'aim.aim_real_cur_bldg', '=', 'bdmr.bdm_code')
            ->leftJoin('room_main as rmmr', function ($join) {
                $join->on('rmmr.bdm_code', '=', 'bdmr.bdm_code')
                    ->on('rmmr.rmm_code', '=', 'aim.aim_real_cur_room');
            });

        return $b;
    }

    private function likeEscape(string $needle): string
    {
        return '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $needle).'%';
    }
}
