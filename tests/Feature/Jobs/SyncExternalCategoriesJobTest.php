<?php

namespace Tests\Feature\Jobs;

use App\Jobs\SyncExternalCategoriesJob;
use App\Services\ExternalCategorySyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class SyncExternalCategoriesJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_calls_sync_service()
    {
        $mockService = Mockery::mock(ExternalCategorySyncService::class);
        $mockService->shouldReceive('sync')
            ->once()
            ->andReturn(['ok' => true, 'synced' => 0]);

        $job = new SyncExternalCategoriesJob();
        $job->handle($mockService);

        $this->assertTrue(true);
    }
}
