<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Services\KerisiPayrollShellListService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Shell list endpoint for all Payroll pages from PAGE_MENUID1122_LEVEL3.json.
 * Each menuId dispatches to the matching ORM query in KerisiPayrollShellListService.
 * Route: GET /api/payroll/kerisi/{menuId}
 */
class KerisiPayrollController extends Controller
{
    use ApiResponse;

    public function index(Request $request, int $menuId): JsonResponse
    {
        /** @var array<int, int> $payrollMenus */
        $payrollMenus = config('kerisi_payroll.menu_ids', []);
        if (! in_array($menuId, $payrollMenus, true)) {
            return $this->sendError(404, 'NOT_FOUND', 'Not a Payroll Kerisi menu');
        }

        $page  = max(1, (int) $request->input('page', 1));
        $limit = max(1, min(200, (int) $request->input('limit', 10)));

        $pack      = app(KerisiPayrollShellListService::class)->fetch($menuId, $request);
        $rows      = $pack['rows'] ?? [];
        $total     = (int) ($pack['total'] ?? 0);
        $connector = (string) ($pack['connector'] ?? 'payroll_shell_preview');

        $meta = [
            'page'       => $page,
            'limit'      => $limit,
            'total'      => $total,
            'totalPages' => $total > 0 ? (int) ceil($total / $limit) : 0,
            'menuId'     => $menuId,
            'connector'  => $connector,
        ];

        if (! empty($pack['shellError'])) {
            $meta['shellError'] = $pack['shellError'];
        }

        return $this->sendOk($rows, $meta);
    }
}
