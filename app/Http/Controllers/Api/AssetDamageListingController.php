<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Damage report register vs draft applications — menus 3470 / 3481.
 * Backed by `asset_damage_master` (legacy YUS_DAMAGE_REPORT_LIST_API subset).
 */
class AssetDamageListingController extends Controller
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
            'scope' => ['required', 'string', 'in:reports,applications'],
        ]);

        $page = max(1, (int) ($validated['page'] ?? 1));
        $limit = max(1, min(200, (int) ($validated['limit'] ?? 15)));
        $q = trim((string) ($validated['q'] ?? ''));
        $sortDir = (string) ($validated['sort_dir'] ?? 'desc');
        $scope = (string) $validated['scope'];

        $base = $this->cx()->table('asset_damage_master');

        if ($scope === 'applications') {
            $base->where(function ($w) {
                $w->whereNull('drm_status')
                    ->orWhereRaw('TRIM(drm_status) = ?', [''])
                    ->orWhereRaw('UPPER(TRIM(IFNULL(drm_status,""))) IN (?,?,?,?,?,?)',
                        ['DRAFT', 'NEW', 'SUBMIT', 'PENDING', 'IN_PROGRESS', 'IN PROGRESS']);
            });
        }

        if ($q !== '') {
            $like = $this->likeEscape($q);
            $base->whereRaw(
                'LOWER(CONCAT_WS("|",
                    IFNULL(CAST(drm_id AS CHAR),""),
                    IFNULL(drm_report_no,""),
                    IFNULL(drm_description,""))) LIKE ?',
                [$like]
            );
        }

        $total = (clone $base)->count();
        $rows = $base
            ->orderBy('drm_id', $sortDir === 'asc' ? 'asc' : 'desc')
            ->forPage($page, $limit)
            ->get([
                'drm_id',
                'drm_report_no',
                'drm_qty_asset',
                'drm_description',
                'drm_status',
                'drm_total_amt',
                'createddate',
                'createdby',
            ]);

        $data = $rows->values()->map(function ($row, int $i) use ($page, $limit) {
            return [
                'index' => (($page - 1) * $limit) + $i + 1,
                'drm_id' => (int) $row->drm_id,
                'drm_report_no' => (string) ($row->drm_report_no ?? ''),
                'drm_qty_asset' => (int) ($row->drm_qty_asset ?? 0),
                'drm_description' => $row->drm_description ? (string) $row->drm_description : '',
                'drm_status' => $row->drm_status ? (string) $row->drm_status : '—',
                'drm_total_amt' => $row->drm_total_amt !== null ? (string) $row->drm_total_amt : '',
                'createddate' => $row->createddate ? (string) $row->createddate : '',
                'createdby' => $row->createdby ? (string) $row->createdby : '',
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
