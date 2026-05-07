<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Legacy: PAGEID ~1278 / Kerisi menu 1562 — `building_location` setup.
 */
class AssetBuildingLocationController extends Controller
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
            'sort_by' => ['sometimes', 'string', 'in:bdl_code,bdl_desc,bdl_id'],
            'sort_dir' => ['sometimes', 'string', 'in:asc,desc'],
        ]);

        $page = max(1, (int) ($validated['page'] ?? 1));
        $limit = max(1, min(200, (int) ($validated['limit'] ?? 15)));
        $q = trim((string) ($validated['q'] ?? ''));
        $sortBy = (string) ($validated['sort_by'] ?? 'bdl_code');
        $sortDir = (string) ($validated['sort_dir'] ?? 'asc');

        $base = $this->cx()->table('building_location');

        if ($q !== '') {
            $like = $this->likeEscape($q);
            $base->whereRaw(
                'LOWER(CONCAT_WS("|", IFNULL(CAST(bdl_id AS CHAR),""), IFNULL(bdl_code,""), IFNULL(bdl_desc,""), IFNULL(bdl_status,""))) LIKE ?',
                [$like]
            );
        }

        $total = (clone $base)->count();

        $rows = $base
            ->orderBy($sortBy, $sortDir === 'desc' ? 'desc' : 'asc')
            ->orderBy('bdl_id')
            ->forPage($page, $limit)
            ->get([
                'bdl_id',
                'bdl_code',
                'bdl_desc',
                'bdl_status',
                'createdby',
                'createddate',
            ]);

        $data = $rows->values()->map(function ($row, int $i) use ($page, $limit) {
            $st = (string) ($row->bdl_status ?? '');

            return [
                'index' => (($page - 1) * $limit) + $i + 1,
                'bdl_id' => (int) $row->bdl_id,
                'bdl_code' => (string) $row->bdl_code,
                'bdl_desc' => (string) ($row->bdl_desc ?? ''),
                'bdl_status' => $st === '1' ? 'ACTIVE' : 'INACTIVE',
                'bdl_status_value' => $st,
                'createdby' => $row->createdby ? (string) $row->createdby : '',
                'createddate' => $row->createddate ? (string) $row->createddate : '',
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
        $row = $this->cx()->table('building_location')->where('bdl_id', $id)->first();
        if (! $row) {
            return $this->sendError(404, 'NOT_FOUND', 'Location not found');
        }

        $st = (string) ($row->bdl_status ?? '');

        return $this->sendOk([
            'bdl_id' => (int) $row->bdl_id,
            'bdl_code' => (string) $row->bdl_code,
            'bdl_desc' => (string) ($row->bdl_desc ?? ''),
            'bdl_status' => $st === '1' ? 1 : 0,
            'createdby' => $row->createdby ? (string) $row->createdby : null,
            'createddate' => $row->createddate ? (string) $row->createddate : null,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'bdl_code' => ['required', 'string', 'max:10'],
            'bdl_desc' => ['required', 'string', 'max:20'],
            'bdl_status' => ['required', 'integer', 'in:0,1'],
        ]);

        $code = Str::upper(trim($data['bdl_code']));
        if ($this->cx()->table('building_location')->where('bdl_code', $code)->exists()) {
            return $this->sendError(409, 'DUPLICATE_CODE', 'Location code already exists');
        }

        $nextId = ((int) $this->cx()->table('building_location')->max('bdl_id')) + 1;

        $this->cx()->table('building_location')->insert([
            'bdl_id' => $nextId,
            'bdl_code' => $code,
            'bdl_desc' => Str::upper(trim($data['bdl_desc'])),
            'bdl_status' => ((int) $data['bdl_status'] === 1) ? '1' : '0',
            'createdby' => $request->user()?->name ?? 'system',
            'createddate' => now()->toDateString(),
        ]);

        return $this->sendCreated(['id' => $nextId, 'bdl_id' => $nextId]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $exists = $this->cx()->table('building_location')->where('bdl_id', $id)->first();
        if (! $exists) {
            return $this->sendError(404, 'NOT_FOUND', 'Location not found');
        }

        $data = $request->validate([
            'bdl_code' => ['required', 'string', 'max:10'],
            'bdl_desc' => ['required', 'string', 'max:20'],
            'bdl_status' => ['required', 'integer', 'in:0,1'],
        ]);

        $code = Str::upper(trim($data['bdl_code']));
        $dup = $this->cx()->table('building_location')
            ->where('bdl_code', $code)
            ->where('bdl_id', '!=', $id)
            ->exists();
        if ($dup) {
            return $this->sendError(409, 'DUPLICATE_CODE', 'Location code already exists');
        }

        $this->cx()->table('building_location')->where('bdl_id', $id)->update([
            'bdl_code' => $code,
            'bdl_desc' => Str::upper(trim($data['bdl_desc'])),
            'bdl_status' => ((int) $data['bdl_status'] === 1) ? '1' : '0',
            'updatedby' => $request->user()?->name ?? 'system',
            'updateddate' => now()->toDateString(),
        ]);

        return $this->sendOk(['success' => true]);
    }

    public function destroy(int $id): JsonResponse
    {
        $row = $this->cx()->table('building_location')->where('bdl_id', $id)->first();
        if (! $row) {
            return $this->sendError(404, 'NOT_FOUND', 'Location not found');
        }

        $code = (string) $row->bdl_code;
        $refs = (int) $this->cx()->table('asset_verification_setup')->where('bdl_code', $code)->count()
            + (int) $this->cx()->table('room_main')->where('bdl_code', $code)->count();

        if ($refs > 0) {
            return $this->sendError(423, 'IN_USE', 'Location is referenced elsewhere; deactivate instead.', [
                'references' => $refs,
            ]);
        }

        $this->cx()->table('building_location')->where('bdl_id', $id)->delete();

        return $this->sendOk(['success' => true]);
    }
}
