<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Preventive / corrective maintenance master listings and scheduled maintenance —
 * Kerisi menus 3492 / 3498 / 3502 (+ draft filters for 3493 / 3499).
 */
class AssetMaintenanceListingController extends Controller
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
            'listing' => ['required', 'string', 'in:preventive,corrective,schedule'],
            'amt_status' => ['sometimes', 'string', 'max:50'],
        ]);

        $page = max(1, (int) ($validated['page'] ?? 1));
        $limit = max(1, min(200, (int) ($validated['limit'] ?? 15)));
        $q = trim((string) ($validated['q'] ?? ''));
        $sortDir = (string) ($validated['sort_dir'] ?? 'desc');
        $listing = (string) $validated['listing'];
        $amtStatus = isset($validated['amt_status']) ? trim((string) $validated['amt_status']) : '';

        if ($listing === 'schedule') {
            return $this->scheduleListing($page, $limit, $q, $sortDir);
        }

        $typeLetter = $listing === 'preventive' ? 'P' : 'C';

        $base = $this->cx()->table('asset_maintenance_master as amm')
            ->where('amm.amt_maintenance_type', $typeLetter);

        if ($amtStatus !== '') {
            $likeSt = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $amtStatus).'%';
            $base->whereRaw('amm.amt_status LIKE ?', [$likeSt]);
        }

        if ($q !== '') {
            $like = $this->likeEscape($q);
            $base->whereRaw(
                'LOWER(CONCAT_WS("|",
                    IFNULL(CAST(amm.amt_maintain_id AS CHAR),""),
                    IFNULL(amm.amt_maintain_no,""), IFNULL(amm.aim_asset_code,""),
                    IFNULL(amm.amt_description,""), IFNULL(amm.amt_status,""),
                    IFNULL(amm.vcs_vendor_code,""))) LIKE ?',
                [$like]
            );
        }

        $total = (clone $base)->count();
        $rows = $base
            ->orderBy('amm.amt_maintain_id', $sortDir === 'asc' ? 'asc' : 'desc')
            ->forPage($page, $limit)
            ->get([
                'amm.amt_maintain_id',
                'amm.amt_maintain_no',
                'amm.aim_asset_code',
                'amm.amt_description',
                'amm.amt_maintenance_type',
                'amm.amt_status',
                'amm.amt_total_cost',
                'amm.vcs_vendor_code',
                'amm.amt_complete_date',
                'amm.createddate',
            ]);

        $data = $rows->values()->map(function ($row, int $i) use ($page, $limit, $listing) {
            return [
                'index' => (($page - 1) * $limit) + $i + 1,
                'id' => (int) $row->amt_maintain_id,
                'reference_no' => (string) ($row->amt_maintain_no ?? ''),
                'asset_code' => (string) ($row->aim_asset_code ?? ''),
                'description' => $row->amt_description ? (string) $row->amt_description : '',
                'maintenance_kind' => $listing,
                'status' => $row->amt_status ? (string) $row->amt_status : '—',
                'total_cost' => $row->amt_total_cost !== null ? (string) $row->amt_total_cost : '',
                'vendor_code' => $row->vcs_vendor_code ? (string) $row->vcs_vendor_code : '',
                'complete_date' => $row->amt_complete_date ? (string) $row->amt_complete_date : '',
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

    private function scheduleListing(int $page, int $limit, string $q, string $sortDir): JsonResponse
    {
        $base = $this->cx()->table('asset_schedule_maintenance as asm');

        if ($q !== '') {
            $like = $this->likeEscape($q);
            $base->whereRaw(
                'LOWER(CONCAT_WS("|",
                  IFNULL(CAST(asm.asm_id AS CHAR),""),
                  IFNULL(asm.aim_asset_code,""),
                  IFNULL(asm.asm_schedule_method,""),
                  IFNULL(asm.asm_status,""),
                  IFNULL(asm.asm_notes,""))) LIKE ?',
                [$like]
            );
        }

        $total = (clone $base)->count();
        $rows = $base
            ->orderBy('asm.asm_id', $sortDir === 'asc' ? 'asc' : 'desc')
            ->forPage($page, $limit)
            ->get([
                'asm.asm_id',
                'asm.aim_asset_code',
                'asm.asm_schedule_method',
                'asm.asm_status',
                'asm.asm_start_date',
                'asm.amps_period_type',
                'asm.asm_notes',
            ]);

        $data = $rows->values()->map(function ($row, int $i) use ($page, $limit) {
            return [
                'index' => (($page - 1) * $limit) + $i + 1,
                'id' => (int) $row->asm_id,
                'asset_code' => (string) ($row->aim_asset_code ?? ''),
                'schedule_method' => (string) ($row->asm_schedule_method ?? ''),
                'period_type' => (string) ($row->amps_period_type ?? ''),
                'status' => $row->asm_status ? (string) $row->asm_status : '—',
                'start_date' => $row->asm_start_date ? (string) $row->asm_start_date : '',
                'notes' => $row->asm_notes ? (string) $row->asm_notes : '',
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
