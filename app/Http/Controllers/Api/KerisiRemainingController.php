<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreKerisiWpnCancelRequest;
use App\Http\Traits\ApiResponse;
use App\Services\KerisiRemainingShellListService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KerisiRemainingController extends Controller
{
    use ApiResponse;

    public function index(Request $request, int $menuId): JsonResponse
    {
        $allowedMenus = config('kerisi_remaining.menu_ids', []);
        if (! in_array($menuId, $allowedMenus, true)) {
            return $this->sendError(404, 'NOT_FOUND', 'Not a registered Kerisi menu');
        }

        $page = max(1, (int) $request->input('page', 1));
        $limit = max(1, min(200, (int) $request->input('limit', 10)));

        $pack = app(KerisiRemainingShellListService::class)->fetch($menuId, $request);
        $rows = $pack['rows'] ?? [];
        $total = $pack['total'] ?? 0;
        $connector = $pack['connector'] ?? 'unknown';

        $meta = [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'totalPages' => $total > 0 ? (int) ceil($total / $limit) : 1,
            'connector' => $connector,
            'menuId' => $menuId,
        ];

        if (! empty($pack['shellError'])) {
            $meta['shellError'] = $pack['shellError'];
        }

        if (! empty($pack['top_filter_options']) && is_array($pack['top_filter_options'])) {
            $meta['top_filter_options'] = $pack['top_filter_options'];
        }

        if (! empty($pack['smart_filter_options']) && is_array($pack['smart_filter_options'])) {
            $meta['smart_filter_options'] = $pack['smart_filter_options'];
        }

        if (! empty($pack['form_options']) && is_array($pack['form_options'])) {
            $meta['form_options'] = $pack['form_options'];
        }

        if (! empty($pack['form_values']) && is_array($pack['form_values'])) {
            $meta['form_values'] = $pack['form_values'];
        }

        if (! empty($pack['extra_datatable_rows']) && is_array($pack['extra_datatable_rows'])) {
            $meta['extra_datatable_rows'] = $pack['extra_datatable_rows'];
        }

        if (array_key_exists('grand_total_pom_order_amt_rm', $pack)) {
            $meta['grand_total_pom_order_amt_rm'] = $pack['grand_total_pom_order_amt_rm'];
        }

        return $this->sendOk($rows, $meta);
    }

    /**
     * Purchasing / List of PR To Be Cancel — Details PR grid (linked GRN/WPN/PO/Bill rows).
     *
     * Query: ?rqm_requisition_no=… and/or ?rqm_requisition_id=…
     */
    public function prToCancelDetails(Request $request): JsonResponse
    {
        $rqmNo = trim((string) $request->input('rqm_requisition_no', ''));
        if ($rqmNo === '') {
            $rid = $request->input('rqm_requisition_id');
            if ($rid !== null && $rid !== '') {
                $rqmNo = (string) (DB::connection('mysql_secondary')
                    ->table('requisition_master')
                    ->where('rqm_requisition_id', (int) $rid)
                    ->value('rqm_requisition_no') ?? '');
                $rqmNo = trim($rqmNo);
            }
        }

        if ($rqmNo === '') {
            return $this->sendError(422, 'VALIDATION_ERROR', 'rqm_requisition_no or rqm_requisition_id is required');
        }

        $rows = app(KerisiRemainingShellListService::class)->purchasingPrToCancelDetailRows($rqmNo);

        return $this->sendOk($rows);
    }

    /**
     * Purchasing / Work Progress Note Cancel — submit cancel (legacy `processcancelwpn_entry`).
     * workflowSubmit was commented in legacy; this updates master + detail rows only.
     */
    public function wpnCancel(StoreKerisiWpnCancelRequest $request): JsonResponse
    {
        $username = $request->user()?->name ?? $request->user()?->email ?? 'system';
        $selectedId = (string) $request->validated()['selected_id'];

        $result = app(KerisiRemainingShellListService::class)->processWpnCancelEntry($selectedId, $username);

        if (! ($result['success'] ?? false)) {
            return $this->sendError(400, 'BAD_REQUEST', (string) ($result['errorMsg'] ?? $result['message'] ?? 'WPN cancel failed'));
        }

        return $this->sendOk([
            'status' => 'ok',
            'successMessage' => (string) ($result['successMessage'] ?? ''),
            'wpnNo' => $result['wpnNo'] ?? null,
        ]);
    }
}
