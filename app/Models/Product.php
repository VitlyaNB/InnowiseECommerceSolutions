<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable; // Импорт Scout

class Product extends Model
{
    use HasFactory, Searchable; // Подключение трейта

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
     * Настройка индексируемых данных для Elasticsearch.
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => (float) $this->price,
            'category_id' => (int) $this->category_id,
            'category_name' => $this->category ? $this->category->name : '',
            'is_active' => (bool) $this->is_active,
            'created_at' => $this->created_at,
        ];
    }

    public function category(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
