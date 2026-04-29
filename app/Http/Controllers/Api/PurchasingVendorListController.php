<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\VendCustomerSupplier;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Purchasing / List of Vendor (PAGEID 1376 / MENUID 1685).
 *
 * Source: FIMS `ZR_PURCHASING_VENDORLIST_BL` on `vend_customer_supplier`
 * (`DB_SECOND_DATABASE`).
 */
class PurchasingVendorListController extends Controller
{
    use ApiResponse;

    private const SORTABLE = [
        'vcs_vendor_code',
        'vcs_vendor_name',
        'vcs_registration_no',
        'vcs_reg_exp_date',
        'vcs_vendor_status',
    ];

    public function index(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->input('page', 1));
        $limit = max(1, min(100, (int) $request->input('limit', 10)));
        $q = trim((string) $request->input('q', ''));
        $sortBy = (string) $request->input('sort_by', 'vcs_vendor_code');
        $sortDir = strtolower((string) $request->input('sort_dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        if (! in_array($sortBy, self::SORTABLE, true)) {
            $sortBy = 'vcs_vendor_code';
        }

        $query = VendCustomerSupplier::query();

        if ($q !== '') {
            $needle = mb_strtolower($q, 'UTF-8');
            $like = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $needle).'%';
            $query->where(function (Builder $b) use ($like) {
                foreach ([
                    'vcs_vendor_code', 'vcs_vendor_name', 'vcs_addr1', 'vcs_addr2', 'vcs_addr3',
                    'vcs_town', 'vcs_state', 'vcs_registration_no', 'vcs_tel_no', 'vcs_fax_no',
                    'vcs_contact_person', 'vcs_iscreditor', 'vcs_isdebtor', 'vcs_vendor_status',
                ] as $col) {
                    $b->orWhereRaw('LOWER(IFNULL('.$col.", '')) LIKE ?", [$like]);
                }
                $b->orWhereRaw(
                    'LOWER(IFNULL(DATE_FORMAT(vcs_reg_exp_date, ?), "")) LIKE ?',
                    ['%d/%m/%Y', $like]
                );
            });
        }

        $total = (clone $query)->count('vcs_id');

        $rows = $query
            ->select([
                'vcs_vendor_code',
                'vcs_vendor_name',
                DB::raw("TRIM(CONCAT_WS(' ', NULLIF(vcs_addr1,''), NULLIF(vcs_addr2,''), NULLIF(vcs_addr3,''), NULLIF(vcs_town,''), NULLIF(vcs_state,''))) as vcs_address"),
                'vcs_registration_no',
                'vcs_reg_exp_date',
                'vcs_tel_no',
                'vcs_fax_no',
                'vcs_contact_person',
                'vcs_iscreditor',
                'vcs_isdebtor',
                'vcs_vendor_status',
            ])
            ->orderBy($sortBy, $sortDir)
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        $data = $rows->values()->map(function (object $r, int $i) use ($page, $limit): array {
            return [
                'index' => (($page - 1) * $limit) + $i + 1,
                'vcs_vendor_code' => $r->vcs_vendor_code,
                'vcs_vendor_name' => $r->vcs_vendor_name,
                'vcs_address' => $r->vcs_address,
                'vcs_registration_no' => $r->vcs_registration_no,
                'vcs_reg_exp_date' => $r->vcs_reg_exp_date,
                'vcs_tel_no' => $r->vcs_tel_no,
                'vcs_fax_no' => $r->vcs_fax_no,
                'vcs_contact_person' => $r->vcs_contact_person,
                'vcs_iscreditor' => $r->vcs_iscreditor === 'Y' ? 'YES' : 'NO',
                'vcs_isdebtor' => $r->vcs_isdebtor === 'Y' ? 'YES' : 'NO',
                'vcs_vendor_status' => $r->vcs_vendor_status === '1' ? 'ACTIVE' : 'INACTIVE',
            ];
        });

        return $this->sendOk($data, [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'totalPages' => (int) ceil($total / max(1, $limit)),
        ]);
    }
}
