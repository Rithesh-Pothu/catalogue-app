<?php

namespace Tests\Feature;

use Tests\TestCase;

class HealthTest extends TestCase
{
    public function test_the_api_reports_that_the_backend_is_running(): void
    {
        $response = $this->getJson('/api/health');

        $response->assertOk()->assertExactJson(['status' => 'ok']);
    }
}
