<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Contract tests for Umum Allocation PTJ (PAGEID 2515 / MENUID 3044 — HIDDEN_PAGE_LEVEL4).
 *
 * Queries the FIMS schema (`mysql_secondary`), not provisioned in SQLite CI, so only
 * the auth guard is asserted for list + options endpoints.
 */
class UmumAllocationPtjTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_requires_authentication(): void
    {
        $response = $this->getJson('/api/budget/report/umum-allocation-ptj');

        $response->assertStatus(401);
        $response->assertJsonPath('error.code', 'UNAUTHORIZED');
    }

    public function test_options_requires_authentication(): void
    {
        $response = $this->getJson('/api/budget/report/umum-allocation-ptj/options');

        $response->assertStatus(401);
        $response->assertJsonPath('error.code', 'UNAUTHORIZED');
    }
}
