<?php

namespace Tests\Feature\Observers;

use App\Models\ProductImage;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductImageObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_deletes_file_from_storage_on_model_delete()
    {
        $mockFileService = $this->createMock(\App\Services\FileService::class);
        $mockFileService->expects($this->once())
            ->method('delete')
            ->with('products/image.jpg', 's3');
        $this->app->instance(\App\Services\FileService::class, $mockFileService);

        $product = Product::factory()->create();
        $image = ProductImage::create([
            'product_id' => $product->id,
            'image_path' => 'products/image.jpg'
        ]);

        $image->delete();
    }
}
