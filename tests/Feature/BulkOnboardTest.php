<?php
namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BulkOnboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_bulk_onboard_returns_batch_id()
    {
        $payload = [
            'organizations' => [
                ['name' => 'Acme', 'domain' => 'acme.com'],
                ['name' => 'Beta', 'domain' => 'beta.com'],
            ],
        ];

        $response = $this->postJson('/api/bulk-onboard', $payload);

        $response->assertStatus(202)
            ->assertJsonStructure(['batch_id', 'status']);

        $this->assertDatabaseCount('organizations', 2);
    }
}
