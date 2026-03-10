<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

/**
 * @property int $id
 * @property int $category_id
 * @property string $name
 * @property string $description
 * @property float $price
 * @property float|null $old_price
 * @property int $quantity
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property Category $category
 * @property \Illuminate\Database\Eloquent\Collection<int, ProductImage> $images
 * 
 * @method static \Illuminate\Database\Eloquent\Builder<Product> query()
 * @method static \Illuminate\Database\Eloquent\Builder<Product> where(string|array<string, mixed>|\Closure $column, mixed $operator = null, mixed $value = null, string $boolean = 'and')
 * @method static Product create(array<string, mixed> $attributes = [])
 * @method static Product findOrFail(mixed $id, array<int, string> $columns = ['*'])
 * @method static Product find(mixed $id, array<int, string> $columns = ['*'])
 * @method static \Illuminate\Database\Eloquent\Builder<Product> whereIn(string $column, mixed $values, string $boolean = 'and', bool $not = false)
 */
class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory, Searchable;

    protected $fillable = [
        'category_id',
        'name',
        'description',
        'price',
        'old_price',
        'quantity',
        'is_active',
    ];

    /**
     * @return string
     */
    public function searchableAs(): string
    {
        return 'products_index';
    }

    /**
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => (float) $this->price,
            'quantity' => (int) $this->quantity,
            'category_id' => (int) $this->category_id,
            'category_name' => $this->category ? $this->category->name : '',
            'is_active' => (bool) $this->is_active,
            'created_at' => $this->created_at,
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Category, $this>
     */
    public function category(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<ProductImage, $this>
     */
    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<Review, $this>
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<CartItem, $this>
     */
    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<OrderItem, $this>
     */
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
