<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Legacy: HQL_ASSET_VERIFICATION_OFF_API / asset_verification_setup (Kerisi menu 3471).
 *
 * Listing omits legacy DB1 joins (FLC user group); inspector role labels are not available on mysql_secondary alone.
 */
class AssetVerificationOfficerController extends Controller
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

        $base = $this->cx()->table('asset_verification_setup as avs')
            ->join('building_location as bdl', 'avs.bdl_code', '=', 'bdl.bdl_code')
            ->join('building_main as bdm', 'avs.bdm_code', '=', 'bdm.bdm_code')
            ->leftJoin('room_main as rmm', function ($j) {
                $j->on('avs.bdm_code', '=', 'rmm.bdm_code')
                    ->whereColumn('avs.rmm_level_code', 'rmm.rmm_level_code');
            })
            ->join('staff as stf', 'avs.stf_staff_id', '=', 'stf.stf_staff_id')
            ->select([
                'avs.avs_id',
                DB::raw("CONCAT_WS(' - ', stf.stf_staff_id, stf.stf_staff_name) AS staff_label"),
                'stf.stf_staff_id',
                DB::raw("CONCAT_WS(' - ', bdl.bdl_code, bdl.bdl_desc) AS location_label"),
                'bdl.bdl_code as location_code',
                DB::raw("CONCAT_WS(' - ', bdm.bdm_code, bdm.bdm_name) AS building_label"),
                'bdm.bdm_code as building_code',
                DB::raw("CONCAT_WS(' - ', rmm.rmm_level_code, rmm.rmm_level) AS level_label"),
                'rmm.rmm_level_code as level_code',
                DB::raw('IF(avs.avs_status = 1, "ACTIVE", "INACTIVE") AS status_label'),
                'avs.avs_status',
            ])
            ->distinct();

        if ($q !== '') {
            $like = $this->likeEscape($q);
            $base->whereRaw(
                'LOWER(CONCAT_WS("|",
                    IFNULL(stf.stf_staff_id,""), IFNULL(stf.stf_staff_name,""),
                    IFNULL(bdl.bdl_code,""), IFNULL(bdl.bdl_desc,""),
                    IFNULL(bdm.bdm_code,""), IFNULL(bdm.bdm_name,""),
                    IFNULL(rmm.rmm_level_code,""), IFNULL(rmm.rmm_level,""),
                    IFNULL(CAST(avs.avs_status AS CHAR),""))) LIKE ?',
                [$like]
            );
        }

        $countBase = clone $base;
        $total = (int) $this->cx()->query()->fromSub($countBase, 'cnt_sub')->count();

        $pageBase = clone $base;
        $rows = $this->cx()->query()->fromSub($pageBase, 'page_sub')
            ->orderBy('avs_id', $sortDir === 'desc' ? 'desc' : 'asc')
            ->forPage($page, $limit)
            ->get();

        $data = $rows->values()->map(function ($row, int $i) use ($page, $limit) {
            return [
                'index' => (($page - 1) * $limit) + $i + 1,
                'id' => (int) $row->avs_id,
                'staff_label' => (string) $row->staff_label,
                'staff_id' => (string) $row->stf_staff_id,
                'role_label' => '—',
                'location_label' => (string) $row->location_label,
                'location_code' => (string) $row->location_code,
                'building_label' => (string) $row->building_label,
                'building_code' => (string) $row->building_code,
                'level_label' => (string) $row->level_label,
                'level_code' => (string) ($row->level_code ?? ''),
                'status_label' => (string) $row->status_label,
                'avs_status' => (int) $row->avs_status,
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
        $row = $this->cx()->table('asset_verification_setup')->where('avs_id', $id)->first();
        if (! $row) {
            return $this->sendError(404, 'NOT_FOUND', 'Setup not found');
        }

        return $this->sendOk([
            'id' => (int) $row->avs_id,
            'stf_staff_id' => (string) $row->stf_staff_id,
            'bdl_code' => (string) $row->bdl_code,
            'bdm_code' => (string) $row->bdm_code,
            'rmm_level_code' => (string) ($row->rmm_level_code ?? ''),
            'avs_status' => (int) $row->avs_status,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'stf_staff_id' => ['required', 'string', 'max:50'],
            'bdl_code' => ['required', 'string', 'max:50'],
            'bdm_code' => ['required', 'string', 'max:50'],
            'rmm_level_code' => ['nullable', 'string', 'max:50'],
            'avs_status' => ['required', 'integer', 'in:0,1'],
        ]);

        $nextId = ((int) $this->cx()->table('asset_verification_setup')->max('avs_id')) + 1;

        $this->cx()->table('asset_verification_setup')->insert([
            'avs_id' => $nextId,
            'stf_staff_id' => trim($data['stf_staff_id']),
            'bdl_code' => trim($data['bdl_code']),
            'bdm_code' => trim($data['bdm_code']),
            'rmm_level_code' => trim((string) ($data['rmm_level_code'] ?? '')),
            'avs_status' => (int) $data['avs_status'],
            'createdby' => $request->user()?->name ?? 'system',
            'createddate' => now()->format('Y-m-d H:i:s'),
        ]);

        return $this->sendCreated(['id' => $nextId]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $exists = $this->cx()->table('asset_verification_setup')->where('avs_id', $id)->exists();
        if (! $exists) {
            return $this->sendError(404, 'NOT_FOUND', 'Setup not found');
        }

        $data = $request->validate([
            'stf_staff_id' => ['required', 'string', 'max:50'],
            'bdl_code' => ['required', 'string', 'max:50'],
            'bdm_code' => ['required', 'string', 'max:50'],
            'rmm_level_code' => ['nullable', 'string', 'max:50'],
            'avs_status' => ['required', 'integer', 'in:0,1'],
        ]);

        $this->cx()->table('asset_verification_setup')->where('avs_id', $id)->update([
            'stf_staff_id' => trim($data['stf_staff_id']),
            'bdl_code' => trim($data['bdl_code']),
            'bdm_code' => trim($data['bdm_code']),
            'rmm_level_code' => trim((string) ($data['rmm_level_code'] ?? '')),
            'avs_status' => (int) $data['avs_status'],
            'updatedby' => $request->user()?->name ?? 'system',
        ]);

        return $this->sendOk(['success' => true]);
    }

    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->cx()->table('asset_verification_setup')->where('avs_id', $id)->delete();

        return $deleted ? $this->sendOk(['success' => true]) : $this->sendError(404, 'NOT_FOUND', 'Setup not found');
    }
}
