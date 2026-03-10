<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $product_id
 * @property string $image_path
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class ProductImage extends Model
{
    /** @use HasFactory<\Illuminate\Database\Eloquent\Factories\Factory> */
    use HasFactory;

    protected $fillable = ['product_id', 'image_path'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Product, $this>
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
