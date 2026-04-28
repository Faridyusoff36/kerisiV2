<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Services\KerisiArShellListService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Shell list endpoint for all Account Receivable pages from PAGE_MENUID1024_LEVEL3.json.
 * Each menuId dispatches to the matching ORM query in KerisiArShellListService.
 * Route: GET /api/account-receivable/kerisi-ar/{menuId}
 */
class KerisiArController extends Controller
{
    use ApiResponse;

    public function index(Request $request, int $menuId): JsonResponse
    {
        /** @var array<int, int> $arMenus */
        $arMenus = config('kerisi_ar.menu_ids', []);
        if (! in_array($menuId, $arMenus, true)) {
            return $this->sendError(404, 'NOT_FOUND', 'Not an Account Receivable Kerisi menu');
        }

        $page  = max(1, (int) $request->input('page', 1));
        $limit = max(1, min(200, (int) $request->input('limit', 10)));

        $pack      = app(KerisiArShellListService::class)->fetch($menuId, $request);
        $rows      = $pack['rows'] ?? [];
        $total     = (int) ($pack['total'] ?? 0);
        $connector = (string) ($pack['connector'] ?? 'ar_shell_preview');

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
