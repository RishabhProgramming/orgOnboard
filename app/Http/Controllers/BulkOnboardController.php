<?php

namespace App\Http\Controllers;

use App\Http\Requests\BulkOnboardRequest;
use App\Jobs\ProcessOrganizationOnboarding;
use App\Models\Organization;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BulkOnboardController extends Controller
{
    public function store(BulkOnboardRequest $request)
    {
        $data = $request->validated();

        $batchId = (string) Str::uuid();
        $now     = now();

        $rows = collect($data['organizations'])->map(fn ($org) => [
            'batch_id'      => $batchId,
            'name'          => $org['name'],
            'domain'        => strtolower($org['domain']),
            'contact_email' => $org['contact_email'] ?? null,
            'status'        => 'pending',
            'created_at'    => $now,
            'updated_at'    => $now,
        ]);

        // Chunked bulk insert (skip duplicates by domain)
        $rows->chunk(500)->each(function ($chunk) {
            Organization::upsert(
                $chunk->toArray(),
                ['domain'],
                [] // do not update existing rows
            );
        });

        // Dispatch jobs
        Organization::where('batch_id', $batchId)
            ->pluck('id')
            ->each(fn ($id) => ProcessOrganizationOnboarding::dispatch($id));

        Log::info('Bulk onboard request accepted', [
            'batch_id' => $batchId,
            'count'    => $rows->count(),
        ]);

        return response()->json([
            'batch_id' => $batchId,
            'status'   => 'accepted',
        ], 202);
    }
}
