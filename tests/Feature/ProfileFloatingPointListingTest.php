<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Contract test for Floating Point Profile listing (PAGEID 1943 / MENUID 2375).
 *
 * Reads FIMS (`mysql_secondary`); PHPUnit uses SQLite memory for the primary app DB,
 * so only the authentication guard is verified here — same pattern as {@see BudgetNotExistsTest}.
 */
class ProfileFloatingPointListingTest extends TestCase
{
    use RefreshDatabase;

    public function test_listing_requires_authentication(): void
    {
        $response = $this->getJson('/api/general-ledger/profile-floating-point-listing');

        $response->assertStatus(401);
        $response->assertJsonPath('error.code', 'UNAUTHORIZED');
    }
}
