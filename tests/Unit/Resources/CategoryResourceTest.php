<?php

namespace Tests\Unit\Resources;

use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CategoryResourceTest extends TestCase
{
    public function test_category_resource_returns_full_image_url()
    {
        $mockFileService = $this->createMock(\App\Services\FileService::class);
        $mockFileService->method('getAbsoluteUrl')
            ->with('categories/tech.png')
            ->willReturn('http://s3.test/categories/tech.png');
        
        $this->app->instance(\App\Services\FileService::class, $mockFileService);

        $category = new Category([
            'id' => 1,
            'name' => 'Tech',
            'image_path' => 'categories/tech.png'
        ]);

        $resource = (new CategoryResource($category))->toArray(request());

        $this->assertEquals('http://s3.test/categories/tech.png', $resource['image_path']);
    }

    public function test_category_resource_handles_null_image()
    {
        $category = new Category([
            'id' => 2,
            'name' => 'No Image'
        ]);
        $resource = (new CategoryResource($category))->toArray(request());

        $this->assertNull($resource['image_path']);
    }
}
