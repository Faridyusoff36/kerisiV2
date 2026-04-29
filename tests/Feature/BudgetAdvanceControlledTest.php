<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Contract tests for Budget Advance Controlled (PAGEID 1784 / MENUID 2160).
 *
 * Reads from the FIMS schema (mysql_secondary) which is not provisioned in
 * the SQLite in-memory test database, so only the auth guard is covered.
 */
class BudgetAdvanceControlledTest extends TestCase
{
    use RefreshDatabase;

    public function test_listing_requires_authentication(): void
    {
        $response = $this->getJson('/api/budget/advance-controlled');

        $response->assertStatus(401);
        $response->assertJsonPath('error.code', 'UNAUTHORIZED');
    }
}
