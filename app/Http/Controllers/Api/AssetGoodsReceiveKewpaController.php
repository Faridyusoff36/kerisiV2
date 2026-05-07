<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Kew PA 1–2 registers derived from GRN header — Kerisi menus 2589 / 2597.
 * Receipt subset: endorsed GRN; rejection subset: cancelled GRN.
 */
class AssetGoodsReceiveKewpaController extends Controller
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
            'variant' => ['required', 'string', 'in:receive,reject'],
        ]);

        $page = max(1, (int) ($validated['page'] ?? 1));
        $limit = max(1, min(200, (int) ($validated['limit'] ?? 15)));
        $q = trim((string) ($validated['q'] ?? ''));
        $sortDir = (string) ($validated['sort_dir'] ?? 'desc');
        $variant = (string) $validated['variant'];

        $base = $this->cx()->table('goods_receive_master');

        if ($variant === 'receive') {
            $base->whereRaw('UPPER(TRIM(IFNULL(grm_status,""))) = ?', ['ENDORSE']);
        } else {
            $base->whereRaw('UPPER(TRIM(IFNULL(grm_status,""))) = ?', ['CANCEL']);
        }

        if ($q !== '') {
            $like = $this->likeEscape($q);
            $base->whereRaw(
                'LOWER(CONCAT_WS("|",
                  IFNULL(CAST(grm_receive_id AS CHAR),""),
                  IFNULL(grm_receive_no,""), IFNULL(IFNULL(grm_reference_doc,""),""),
                  IFNULL(vcs_vendor_code,""))) LIKE ?',
                [$like]
            );
        }

        $total = (clone $base)->count();

        $rows = $base
            ->orderBy('grm_receive_id', $sortDir === 'asc' ? 'asc' : 'desc')
            ->forPage($page, $limit)
            ->get([
                'grm_receive_id',
                'grm_receive_no',
                'grm_receive_date',
                'grm_status',
                'vcs_vendor_code',
                'org_code',
                'pom_order_no',
                'grm_total_amt_rm',
                'bim_bills_no',
                'createddate',
            ]);

        $data = $rows->values()->map(function ($row, int $i) use ($page, $limit) {
            return [
                'index' => (($page - 1) * $limit) + $i + 1,
                'grm_receive_id' => (int) $row->grm_receive_id,
                'grm_receive_no' => (string) ($row->grm_receive_no ?? ''),
                'grm_receive_date' => $row->grm_receive_date ? (string) $row->grm_receive_date : '',
                'grm_status' => (string) ($row->grm_status ?? ''),
                'vendor_code' => $row->vcs_vendor_code ? (string) $row->vcs_vendor_code : '',
                'org_code' => $row->org_code ? (string) $row->org_code : '',
                'po_no' => $row->pom_order_no ? (string) $row->pom_order_no : '',
                'total_rm' => $row->grm_total_amt_rm !== null ? (string) $row->grm_total_amt_rm : '',
                'bim_bills_no' => $row->bim_bills_no ? (string) $row->bim_bills_no : '',
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
}
