<?php
namespace Tests\Unit;

use App\Jobs\ProcessOrganizationOnboarding;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProcessOrganizationOnboardingTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_is_idempotent()
    {
        $org = Organization::create([
            'batch_id' => 'test-batch',
            'name'     => 'Test Org',
            'domain'   => 'test.com',
            'status'   => 'completed',
        ]);

        (new ProcessOrganizationOnboarding($org->id))->handle();

        $this->assertEquals('completed', $org->fresh()->status);
    }
}
