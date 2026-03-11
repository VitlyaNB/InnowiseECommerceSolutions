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
        Storage::fake('s3');
        $category = new Category([
            'id' => 1,
            'name' => 'Tech',
            'image_path' => 'categories/tech.png'
        ]);

        $resource = (new CategoryResource($category))->toArray(request());

        $this->assertStringContainsString('categories/tech.png', $resource['image_path']);
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
