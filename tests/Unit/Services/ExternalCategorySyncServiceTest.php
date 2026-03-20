<?php

namespace Tests\Unit\Services;

use App\Dto\CategoryDto;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use App\Services\ExternalCategorySyncService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class ExternalCategorySyncServiceTest extends TestCase
{
    private CategoryRepositoryInterface|MockInterface $categoryRepository;

    private ExternalCategorySyncService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->categoryRepository = Mockery::mock(CategoryRepositoryInterface::class);
        $this->service = new ExternalCategorySyncService($this->categoryRepository);
    }

    public function test_it_syncs_categories_from_external_api(): void
    {
        Config::set('services.external_project.api_url', 'https://external-api.com/categories');

        Http::fake([
            'https://external-api.com/categories' => Http::response([
                ['name' => 'External Cat 1'],
                ['name' => 'External Cat 2'],
            ], 200),
        ]);

        $this->categoryRepository->shouldReceive('existsByName')->with('External Cat 1')->once()->andReturnFalse();
        $this->categoryRepository->shouldReceive('existsByName')->with('External Cat 2')->once()->andReturnFalse();
        $this->categoryRepository->shouldReceive('create')
            ->with(Mockery::type(CategoryDto::class))
            ->twice()
            ->andReturnUsing(static fn (CategoryDto $dto): CategoryDto => new CategoryDto(id: 1, name: $dto->name));

        $result = $this->service->sync();

        $this->assertTrue($result->ok);
        $this->assertSame(2, $result->synced);
        $this->assertSame(200, $result->status);
    }
}
