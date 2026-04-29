<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Contract tests for the Letter Phrase setup endpoints (PAGEID 2911 / MENUID 3506).
 *
 * The data lives in the legacy FIMS `lookup_parameter_main` table on the
 * `mysql_secondary` connection, which is not provisioned in the test SQLite
 * in-memory database. Following the convention established by
 * `AccountCodePpiTest`, `AccountPayableTest`, `CashbookTest` and the rest of
 * the FIMS feature tests, we cover the auth guard on every endpoint and the
 * form request validation for the update endpoint (422 happens before any
 * secondary-DB query, so it can be tested here without provisioning the
 * legacy schema).
 */
class LetterPhraseTest extends TestCase
{
    use RefreshDatabase;

    private function assertUnauthorized(string $method, string $uri): void
    {
        $response = $this->json($method, $uri);
        $response->assertStatus(401);
        $response->assertJsonPath('error.code', 'UNAUTHORIZED');
    }

    public function test_list_requires_authentication(): void
    {
        $this->assertUnauthorized('GET', '/api/setup/letter-phrase');
    }

    public function test_show_requires_authentication(): void
    {
        $this->assertUnauthorized('GET', '/api/setup/letter-phrase/ANY');
    }

    public function test_update_requires_authentication(): void
    {
        $this->assertUnauthorized('PUT', '/api/setup/letter-phrase/ANY');
    }

    public function test_update_rejects_body_without_required_phrase_malay(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $response = $this->putJson('/api/setup/letter-phrase/ANY', []);

        $response->assertStatus(422);
        $response->assertJsonPath('error.code', 'VALIDATION_ERROR');

        $details = $response->json('error.details') ?? [];
        $this->assertNotEmpty($details, 'Expected validation details for the missing Phrase Malay field.');
    }

    public function test_update_rejects_phrase_malay_exceeding_max_length(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $response = $this->putJson('/api/setup/letter-phrase/ANY', [
            'lpmValueDescBm' => str_repeat('a', 256),
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('error.code', 'VALIDATION_ERROR');
    }
}
