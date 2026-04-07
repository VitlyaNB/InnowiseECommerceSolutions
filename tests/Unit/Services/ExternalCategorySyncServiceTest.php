<?php

namespace Tests\Unit\Services;

use App\Dto\CategoryDto;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use App\Services\ExternalCategoryApiClient;
use App\Services\ExternalCategorySyncService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

class ExternalCategorySyncServiceTest extends TestCase
{
    private CategoryRepositoryInterface&MockObject $categoryRepository;

    private ExternalCategorySyncService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->categoryRepository = $this->createMock(CategoryRepositoryInterface::class);
        $this->service = new ExternalCategorySyncService($this->categoryRepository, new ExternalCategoryApiClient);
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

        $this->categoryRepository
            ->expects($this->exactly(2))
            ->method('existsByName')
            ->with($this->logicalOr(
                $this->equalTo('External Cat 1'),
                $this->equalTo('External Cat 2')
            ))
            ->willReturn(false);

        $this->categoryRepository
            ->expects($this->exactly(2))
            ->method('create')
            ->with($this->isInstanceOf(CategoryDto::class))
            ->willReturnCallback(static fn (CategoryDto $dto): CategoryDto => new CategoryDto(id: 1, name: $dto->name));

        $result = $this->service->sync();

        $this->assertTrue($result->status);
        $this->assertSame(2, $result->synced);
        $this->assertSame(200, $result->httpStatus);
    }
}
