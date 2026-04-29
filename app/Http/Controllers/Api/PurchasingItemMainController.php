<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Services\PurchasingItemMainService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Purchasing / Setup / Item Main (menu 1820 / legacy PAGE 1499).
 */
class PurchasingItemMainController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected PurchasingItemMainService $service,
    ) {}

    /**
     * Distinct ITEM_CATEGORY groups (legacy Search Group dropdown).
     */
    public function groups(): JsonResponse
    {
        $opts = $this->service->groupLookupOptions();

        return $this->sendOk($opts);
    }

    /**
     * Main Category grid — lookup_details rows scoped by grouplookup.
     */
    public function mainCategories(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->input('page', 1));
        $limit = max(1, min(100, (int) $request->input('limit', 10)));
        $q = trim((string) $request->input('q', ''));

        $pack = $this->service->mainCategories($request, $page, $limit, $q);

        return $this->sendOk($pack['rows'], [
            'page' => $page,
            'limit' => $limit,
            'total' => $pack['total'],
            'totalPages' => $pack['total'] > 0 ? (int) ceil($pack['total'] / max(1, $limit)) : 1,
        ]);
    }

    public function subcategories(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->input('page', 1));
        $limit = max(1, min(100, (int) $request->input('limit', 10)));
        $q = trim((string) $request->input('q', ''));

        $pack = $this->service->subcategories($request, $page, $limit, $q);

        return $this->sendOk($pack['rows'], [
            'page' => $page,
            'limit' => $limit,
            'total' => $pack['total'],
            'totalPages' => $pack['total'] > 0 ? (int) ceil($pack['total'] / max(1, $limit)) : 1,
        ]);
    }

    public function subsiri(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->input('page', 1));
        $limit = max(1, min(100, (int) $request->input('limit', 10)));
        $q = trim((string) $request->input('q', ''));

        $pack = $this->service->subsiri($request, $page, $limit, $q);

        return $this->sendOk($pack['rows'], [
            'page' => $page,
            'limit' => $limit,
            'total' => $pack['total'],
            'totalPages' => $pack['total'] > 0 ? (int) ceil($pack['total'] / max(1, $limit)) : 1,
        ]);
    }

    public function itemLines(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->input('page', 1));
        $limit = max(1, min(100, (int) $request->input('limit', 10)));
        $q = trim((string) $request->input('q', ''));

        $pack = $this->service->itemLines($request, $page, $limit, $q);

        return $this->sendOk($pack['rows'], [
            'page' => $page,
            'limit' => $limit,
            'total' => $pack['total'],
            'totalPages' => $pack['total'] > 0 ? (int) ceil($pack['total'] / max(1, $limit)) : 1,
        ]);
    }
}
