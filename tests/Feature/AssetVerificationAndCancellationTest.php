<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Contract: Asset Verification + Cancellation list endpoints require auth.
 * Data lives on mysql_secondary (not provisioned in SQLite).
 */
class AssetVerificationAndCancellationTest extends TestCase
{
    use RefreshDatabase;

    public function test_verification_index_requires_authentication(): void
    {
        $this->json('GET', '/api/asset/verification')->assertStatus(401)->assertJsonPath('error.code', 'UNAUTHORIZED');
    }

    public function test_verification_update_requires_authentication(): void
    {
        $this->json('PUT', '/api/asset/verification/1', [])->assertStatus(401)->assertJsonPath('error.code', 'UNAUTHORIZED');
    }

    public function test_cancellation_assets_requires_authentication(): void
    {
        $this->json('GET', '/api/asset/cancellation/assets')->assertStatus(401)->assertJsonPath('error.code', 'UNAUTHORIZED');
    }

    public function test_cancellation_journals_requires_authentication(): void
    {
        $this->json('GET', '/api/asset/cancellation/journals')->assertStatus(401)->assertJsonPath('error.code', 'UNAUTHORIZED');
    }
}
