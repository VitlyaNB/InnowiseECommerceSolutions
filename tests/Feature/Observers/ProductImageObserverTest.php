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
        Storage::fake('s3');

        $path = 'products/image.jpg';
        Storage::disk('s3')->put($path, 'content');

        $product = Product::factory()->create();
        $image = ProductImage::create([
            'product_id' => $product->id,
            'image_path' => $path
        ]);

        $image->delete();

        Storage::disk('s3')->assertMissing($path);
    }
}
