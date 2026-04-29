<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Contract tests for V2 Budget Summary report (legacy `V2_BUDGET_SUMMARY_API` — menus 3382, 3389, 3393).
 *
 * Uses `mysql_secondary`, not provisioned in SQLite CI, so only auth is asserted.
 */
class BudgetV2BudgetSummaryTest extends TestCase
{
    use RefreshDatabase;

    public function test_listing_requires_authentication(): void
    {
        $response = $this->postJson('/api/budget/report/v2-budget-summary/listing', [
            'bdg_year' => '2024',
        ]);

        $response->assertStatus(401);
        $response->assertJsonPath('error.code', 'UNAUTHORIZED');
    }
}
