<?php

namespace App\Models;

use App\Dto\ProductFiltersDto;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Laravel\Scout\Searchable;

/**
 * @property int $id
 * @property int $category_id
 * @property string $name
 * @property string $description
 * @property float $price
 * @property int $quantity
 * @property bool $is_active
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Category $category
 * @property Collection<int, ProductImage> $images
 * @method static Builder<Product> filter(ProductFiltersDto $filters)
 */
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory, Searchable;

    protected $fillable = [
        'category_id',
        'name',
        'description',
        'price',
        'quantity',
        'is_active',
    ];

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
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return HasMany<ProductImage, $this>
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    /**
     * @return HasMany<Review, $this>
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * @return HasMany<CartItem, $this>
     */
    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * @param  Builder<Product>  $query
     * @return Builder<Product>
     */
    public function scopeFilter(Builder $query, ProductFiltersDto $filters): Builder
    {
        return $query
            ->when($filters->categoryId !== null, fn ($q) => $q->where('category_id', $filters->categoryId))
            ->when($filters->isActive !== null, fn ($q) => $q->where('is_active', $filters->isActive))
            ->when($filters->priceMin !== null, fn ($q) => $q->where('price', '>=', $filters->priceMin))
            ->when($filters->priceMax !== null, fn ($q) => $q->where('price', '<=', $filters->priceMax))
            ->when($filters->inStock === true, fn ($q) => $q->where('quantity', '>', 0));
    }
}
