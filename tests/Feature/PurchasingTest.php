<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Contract tests for the FIMS Purchasing migrations. The endpoints read
 * from the external `mysql_secondary` schema which is not provisioned in
 * the in-memory test database (same constraint as PortalTest), so only the
 * Sanctum auth guard is covered here.
 */
class PurchasingTest extends TestCase
{
    use RefreshDatabase;

    private function assertUnauthorized(string $method, string $uri): void
    {
        $response = $this->json($method, $uri);
        $response->assertStatus(401);
        $response->assertJsonPath('error.code', 'UNAUTHORIZED');
    }

    public function test_status_po_pr_list_requires_authentication(): void
    {
        $this->assertUnauthorized('GET', '/api/purchasing/status-po-pr');
    }

    public function test_status_po_pr_options_requires_authentication(): void
    {
        $this->assertUnauthorized('GET', '/api/purchasing/status-po-pr/options');
    }

    public function test_purchasing_jobscope_api_requires_authentication(): void
    {
        $this->assertUnauthorized('GET', '/api/purchasing/jobscope');
        $this->assertUnauthorized('GET', '/api/purchasing/jobscope/form-options');
        $this->assertUnauthorized('GET', '/api/purchasing/jobscope/parent-options?level=2&category=KK');
        $this->assertUnauthorized('GET', '/api/purchasing/jobscope/1');
        $this->assertUnauthorized('POST', '/api/purchasing/jobscope');
        $this->assertUnauthorized('PUT', '/api/purchasing/jobscope/1');
    }

    public function test_purchasing_item_main_endpoints_require_authentication(): void
    {
        $this->assertUnauthorized('GET', '/api/purchasing/item-main/groups');
        $this->assertUnauthorized('GET', '/api/purchasing/item-main/main-categories');
        $this->assertUnauthorized('GET', '/api/purchasing/item-main/subcategories');
        $this->assertUnauthorized('GET', '/api/purchasing/item-main/subsiri');
        $this->assertUnauthorized('GET', '/api/purchasing/item-main/item-lines');
    }

    public function test_purchase_requisition_api_requires_authentication(): void
    {
        $this->assertUnauthorized('GET', '/api/purchasing/purchase-requisition/options');
        $this->assertUnauthorized('GET', '/api/purchasing/purchase-requisition/cost-centres?oun_code=PKP');
        $this->assertUnauthorized('GET', '/api/purchasing/purchase-requisition/1');
        $this->assertUnauthorized('POST', '/api/purchasing/purchase-requisition');
        $this->assertUnauthorized('PUT', '/api/purchasing/purchase-requisition/1');
    }
}