<?php

namespace Tests\Feature\Jobs;

use App\Dto\ExternalCategorySyncResultDto;
use App\Jobs\SyncExternalCategoriesJob;
use App\Services\Interfaces\ExternalCategorySyncServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class SyncExternalCategoriesJobTest extends TestCase
{
    use RefreshDatabase;

    private ExternalCategorySyncServiceInterface|MockInterface $mockService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockService = Mockery::mock(ExternalCategorySyncServiceInterface::class);
    }

    public function test_job_calls_sync_service(): void
    {
        $this->mockService->shouldReceive('sync')
            ->once()
            ->andReturn(new ExternalCategorySyncResultDto(ok: true, message: 'Success', synced: 0, status: 200));

        $this->app->instance(ExternalCategorySyncServiceInterface::class, $this->mockService);

        $job = new SyncExternalCategoriesJob;
        $job->handle($this->mockService);

        $this->assertTrue(true);
    }
}
