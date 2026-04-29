<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreJobscopeRequest;
use App\Http\Requests\UpdateJobscopeRequest;
use App\Http\Traits\ApiResponse;
use App\Services\PurchasingJobscopeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Purchasing / Setup / List Of Jobscope (menu 1932) — `jobscope` on mysql_secondary.
 */
class PurchasingJobscopeController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected PurchasingJobscopeService $service,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->input('page', 1));
        $limit = max(1, min(200, (int) $request->input('limit', 10)));
        $q = trim((string) $request->input('q', ''));

        $pack = $this->service->paginateList($request, $page, $limit, $q);

        return $this->sendOk($pack['rows'], [
            'page' => $page,
            'limit' => $limit,
            'total' => $pack['total'],
            'totalPages' => $pack['total'] > 0 ? (int) ceil($pack['total'] / max(1, $limit)) : 1,
        ]);
    }

    /**
     * Shared dropdown payloads for listings + popup (camelCase outbound).
     */
    public function formOptions(): JsonResponse
    {
        return $this->sendOk([
            'levels' => $this->service->levelOptions(),
            'categories' => $this->service->categoryOptions(),
            'smartCategories' => $this->service->categoryFilterOptions(),
            'statuses' => $this->service->statusDropdownOptions(),
        ]);
    }

    public function parentOptions(Request $request): JsonResponse
    {
        $opts = $this->service->parentOptions($request);

        return $this->sendOk($opts);
    }

    public function show(int $id): JsonResponse
    {
        $row = $this->service->findById($id);
        if (! $row) {
            return $this->sendError(404, 'NOT_FOUND', 'Jobscope not found');
        }

        $payload = $this->service->rowToFormPayload($row);
        if ($payload === null) {
            return $this->sendError(404, 'NOT_FOUND', 'Jobscope not found');
        }

        return $this->sendOk($payload);
    }

    public function store(StoreJobscopeRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $id = $this->service->create([
            'level' => trim((string) $validated['level']),
            'category' => trim((string) $validated['category']),
            'parent' => isset($validated['parent']) ? trim((string) $validated['parent']) : '',
            'code' => trim((string) $validated['code']),
            'name' => trim((string) $validated['name']),
            'status' => trim((string) $validated['status']),
        ]);

        return $this->sendCreated([
            'id' => $id,
        ]);
    }

    public function update(UpdateJobscopeRequest $request, int $id): JsonResponse
    {
        if (! $this->service->findById($id)) {
            return $this->sendError(404, 'NOT_FOUND', 'Jobscope not found');
        }

        $validated = $request->validated();

        $this->service->update($id, [
            'level' => trim((string) $validated['level']),
            'category' => trim((string) $validated['category']),
            'parent' => isset($validated['parent']) ? trim((string) $validated['parent']) : '',
            'code' => trim((string) $validated['code']),
            'name' => trim((string) $validated['name']),
            'status' => trim((string) $validated['status']),
        ]);

        return $this->sendOk(['success' => true]);
    }
}
