<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * `/api/project-monitoring/projects/{cpaProjectNo}` requires auth; secondary DB rows are optional in PHPUnit.
 */
class CapitalProjectProfileTest extends TestCase
{
    public function test_get_single_project_requires_auth(): void
    {
        $this->getJson('/api/project-monitoring/projects/CPA-TEST-001')
            ->assertUnauthorized();
    }

    public function test_patch_project_requires_auth(): void
    {
        $this->patchJson('/api/project-monitoring/projects/CPA-TEST-001', [])
            ->assertUnauthorized();
    }
}
