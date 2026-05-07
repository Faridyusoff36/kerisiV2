<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Read-only composite of `room_main` + building + location + PTJ
 * — Kerisi menu 2746 / legacy AM_ASSETINVENTORY_LOCATION_LISTING.
 */
class AssetRoomLocationListingController extends Controller
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

        $base = $this->cx()->table('room_main as rmm')
            ->leftJoin('building_main as bdm', 'rmm.bdm_code', '=', 'bdm.bdm_code')
            ->leftJoin('building_location as bdl', 'rmm.bdl_code', '=', 'bdl.bdl_code')
            ->leftJoin('organization_unit as ou', 'rmm.oun_code', '=', 'ou.oun_code')
            ->select([
                'rmm.rmm_id',
                'rmm.rmm_code',
                'rmm.rmm_name',
                'rmm.rmm_level_code',
                'rmm.rmm_level',
                'rmm.bdl_code',
                'rmm.bdm_code',
                'rmm.oun_code',
                'rmm.rmm_status',
                DB::raw("CONCAT_WS(' - ', bdl.bdl_code, bdl.bdl_desc) AS location_label"),
                DB::raw("CONCAT_WS(' - ', bdm.bdm_code, bdm.bdm_name) AS building_label"),
                DB::raw("CONCAT_WS(' - ', ou.oun_code, ou.oun_desc) AS ptj_label"),
            ]);

        if ($q !== '') {
            $like = $this->likeEscape($q);
            $base->whereRaw(
                'LOWER(CONCAT_WS("|",
                  IFNULL(rmm.rmm_code,""), IFNULL(rmm.rmm_name,""),
                  IFNULL(rmm.rmm_level_code,""), IFNULL(rmm.rmm_level,""),
                  IFNULL(rmm.bdl_code,""), IFNULL(rmm.bdm_code,""), IFNULL(rmm.oun_code,""),
                  IFNULL(bdl.bdl_desc,""), IFNULL(bdm.bdm_name,""), IFNULL(ou.oun_desc,""))) LIKE ?',
                [$like]
            );
        }

        $total = (int) (clone $base)->distinct()->count(DB::raw('rmm.rmm_id'));

        $ordered = clone $base;
        $ordered->orderBy('rmm.rmm_id', $sortDir === 'desc' ? 'desc' : 'asc');

        $rows = $ordered
            ->forPage($page, $limit)
            ->get();

        $data = $rows->values()->map(function ($row, int $i) use ($page, $limit) {
            return [
                'index' => (($page - 1) * $limit) + $i + 1,
                'rmm_id' => (int) $row->rmm_id,
                'rmm_code' => (string) ($row->rmm_code ?? ''),
                'rmm_name' => (string) ($row->rmm_name ?? ''),
                'rmm_level_code' => (string) ($row->rmm_level_code ?? ''),
                'rmm_level' => (string) ($row->rmm_level ?? ''),
                'location_label' => (string) ($row->location_label ?? '—'),
                'building_label' => (string) ($row->building_label ?? '—'),
                'ptj_label' => (string) ($row->ptj_label ?? '—'),
                'rmm_status' => (string) ($row->rmm_status ?? ''),
            ];
        });

        return $this->sendOk($data, [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'totalPages' => $limit > 0 ? (int) ceil($total / $limit) : 1,
        ]);
    }
}
