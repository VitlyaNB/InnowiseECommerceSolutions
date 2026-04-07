<?php

namespace Tests\Feature\Jobs;

use App\Dto\ExternalCategorySyncResultDto;
use App\Jobs\SyncExternalCategoriesJob;
use App\Services\ExternalCategorySyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class SyncExternalCategoriesJobTest extends TestCase
{
    use RefreshDatabase;

    private ExternalCategorySyncService|MockInterface $mockService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockService = Mockery::mock(ExternalCategorySyncService::class);
    }

    public function test_job_calls_sync_service(): void
    {
        $this->mockService->shouldReceive('sync')
            ->once()
            ->andReturn(new ExternalCategorySyncResultDto(status: true, message: 'Success', synced: 0, httpStatus: 200));

        $this->app->instance(ExternalCategorySyncService::class, $this->mockService);

        $job = new SyncExternalCategoriesJob;
        $job->handle($this->mockService);

        $this->assertTrue(true);
    }
}
