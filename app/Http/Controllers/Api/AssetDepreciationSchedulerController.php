<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Legacy: HQL_ASSET_DEPRECITION_API / asset_depr_scheduler (Kerisi menu 3338).
 */
class AssetDepreciationSchedulerController extends Controller
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
            'sort_by' => ['sometimes', 'string', 'in:id,org,day,is_open,update_date'],
            'sort_dir' => ['sometimes', 'string', 'in:asc,desc'],
        ]);

        $page = max(1, (int) ($validated['page'] ?? 1));
        $limit = max(1, min(200, (int) ($validated['limit'] ?? 15)));
        $q = trim((string) ($validated['q'] ?? ''));
        $sortBy = (string) ($validated['sort_by'] ?? 'id');
        $sortDir = (string) ($validated['sort_dir'] ?? 'asc');

        $base = $this->cx()->table('asset_depr_scheduler as ad')
            ->join('organization as o', 'o.org_code', '=', 'ad.org_code')
            ->select([
                'ad.adsc_id as id',
                'o.org_desc as org',
                'ad.adsc_day as day',
                'ad.adsc_isopen as adsc_isopen',
                DB::raw("IF(ad.adsc_isopen = 'Y', 'YES', 'NO') AS is_open"),
                'ad.createddate as created_date',
                'ad.updateddate as update_date',
                'ad.updatedby as update_by',
            ]);

        if ($q !== '') {
            $like = $this->likeEscape($q);
            $base->whereRaw(
                'LOWER(CONCAT_WS("|", IFNULL(CAST(ad.adsc_id AS CHAR),""), IFNULL(o.org_desc,""), IFNULL(ad.adsc_day,""), IFNULL(ad.adsc_isopen,""), IFNULL(ad.updatedby,""))) LIKE ?',
                [$like]
            );
        }

        $sortMap = [
            'id' => 'ad.adsc_id',
            'org' => 'o.org_desc',
            'day' => 'ad.adsc_day',
            'is_open' => 'ad.adsc_isopen',
            'update_date' => 'ad.updateddate',
        ];
        $col = $sortMap[$sortBy] ?? 'ad.adsc_id';
        $base->orderBy($col, $sortDir === 'desc' ? 'desc' : 'asc');

        $total = (clone $base)->count();
        $rows = $base->forPage($page, $limit)->get();

        $data = $rows->values()->map(function ($row, int $i) use ($page, $limit) {
            return [
                'index' => (($page - 1) * $limit) + $i + 1,
                'id' => (int) $row->id,
                'org' => (string) $row->org,
                'day' => (string) $row->day,
                'adsc_isopen' => (string) $row->adsc_isopen,
                'is_open' => (string) $row->is_open,
                'created_date' => $row->created_date ?? null,
                'update_date' => $row->update_date ?? null,
                'update_by' => $row->update_by ?? null,
            ];
        });

        return $this->sendOk($data, [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'totalPages' => $limit > 0 ? (int) ceil($total / $limit) : 1,
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'adsc_day' => ['required', 'string', 'max:50'],
            'adsc_isopen' => ['required', 'string', 'max:5'],
        ]);

        $updated = $this->cx()->table('asset_depr_scheduler')->where('adsc_id', $id)->update([
            'adsc_day' => $data['adsc_day'],
            'adsc_isopen' => strtoupper($data['adsc_isopen']) === 'N' ? 'N' : 'Y',
            'updateddate' => now()->format('Y-m-d H:i:s'),
            'updatedby' => $request->user()?->name ?? 'system',
        ]);

        if (! $updated) {
            return $this->sendError(404, 'NOT_FOUND', 'Scheduler row not found');
        }

        return $this->sendOk(['success' => true]);
    }
}
