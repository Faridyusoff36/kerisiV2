<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Services\KerisiRemainingShellListService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

        return $this->sendOk($rows, $meta);
    }
}
