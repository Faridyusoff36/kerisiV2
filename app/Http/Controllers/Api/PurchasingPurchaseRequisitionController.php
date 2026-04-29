<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePurchaseRequisitionRequest;
use App\Http\Traits\ApiResponse;
use App\Services\PurchasingPurchaseRequisitionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Purchasing / Purchase Requisition / New Purchase Requisition (MENUID 1771).
 */
class PurchasingPurchaseRequisitionController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected PurchasingPurchaseRequisitionService $service,
    ) {}

    /**
     * All dropdown payloads for the new/edit PR header form (`mysql_secondary`).
     */
    public function options(): JsonResponse
    {
        return $this->sendOk($this->service->formOptionsPayload());
    }

    /**
     * Cost centres filtered after PTJ (`oun_code`) is chosen — matches legacy cascading behaviour.
     */
    public function costCentres(Request $request): JsonResponse
    {
        $oun = trim((string) $request->input('oun_code', ''));

        return $this->sendOk([
            'costCentres' => $oun === ''
                ? $this->service->dropdownOptionsCostCentres(null)
                : $this->service->costCentresForPtj($oun),
        ]);
    }

    public function show(int $id): JsonResponse
    {
        if ($id < 1) {
            return $this->sendError(400, 'BAD_REQUEST', 'Invalid rqm_requisition_id');
        }

        $row = $this->service->findMasterForApi($id);
        if ($row === null) {
            return $this->sendError(404, 'NOT_FOUND', 'Purchase Requisition not found');
        }

        return $this->sendOk($row);
    }

    public function store(StorePurchaseRequisitionRequest $request): JsonResponse
    {
        $data = $request->sanitizedForPersist();

        try {
            $id = $this->service->create($data);
        } catch (\Throwable $e) {
            report($e);

            return $this->sendError(500, 'INTERNAL_ERROR', 'Unable to save Purchase Requisition');
        }

        $row = $this->service->findMasterForApi($id);
        if ($row === null) {
            return $this->sendError(500, 'INTERNAL_ERROR', 'Purchase Requisition not found after insert');
        }

        return $this->sendCreated($row);
    }

    public function update(StorePurchaseRequisitionRequest $request, int $id): JsonResponse
    {
        if ($id < 1) {
            return $this->sendError(400, 'BAD_REQUEST', 'Invalid id');
        }

        if ($this->service->findMasterForApi($id) === null) {
            return $this->sendError(404, 'NOT_FOUND', 'Purchase Requisition not found');
        }

        try {
            $this->service->update($id, $request->sanitizedForPersist());
        } catch (\Throwable $e) {
            report($e);

            return $this->sendError(500, 'INTERNAL_ERROR', 'Unable to update Purchase Requisition');
        }

        $row = $this->service->findMasterForApi($id);

        return $this->sendOk($row);
    }
}
