<?php
namespace App\Jobs;

use App\Models\Organization;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessOrganizationOnboarding implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries     = 5;
    public array $backoff = [10, 30, 60, 120];

    public function __construct(public int $organizationId)
    {}

    public function handle(): void
    {
        $org = Organization::find($this->organizationId);

        if (! $org) {
            return;
        }

        // Idempotency: already processed
        if ($org->status === 'completed') {
            return;
        }

        $org->update(['status' => 'processing']);

        try {
            // Simulate heavy onboarding work
            // external API calls, provisioning, etc.
            sleep(1);

            $org->update([
                'status'       => 'completed',
                'processed_at' => now(),
            ]);

            Log::info('Organization onboarded', [
                'batch_id'        => $org->batch_id,
                'organization_id' => $org->id,
                'status'          => 'completed',
            ]);
        } catch (\Throwable $e) {
            $org->update([
                'status'        => 'failed',
                'failed_reason' => $e->getMessage(),
            ]);

            Log::error('Organization onboarding failed', [
                'batch_id'        => $org->batch_id,
                'organization_id' => $org->id,
                'error'           => $e->getMessage(),
            ]);

            throw $e; // allow retry
        }
    }
}
