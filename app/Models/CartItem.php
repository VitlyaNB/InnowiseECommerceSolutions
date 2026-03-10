<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int|null $user_id
 * @property string|null $session_id
 * @property int $product_id
 * @property int $quantity
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property Product $product
 * 
 * @method static \Illuminate\Database\Eloquent\Builder<CartItem> query()
 * @method static \Illuminate\Database\Eloquent\Builder<CartItem> where(string|array<string, mixed>|\Closure $column, mixed $operator = null, mixed $value = null, string $boolean = 'and')
 * @method static CartItem create(array<string, mixed> $attributes = [])
 * @method static CartItem find(mixed $id, array<int, string> $columns = ['*'])
 */
class CartItem extends Model
{
    /** @use HasFactory<\Illuminate\Database\Eloquent\Factories\Factory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_id',
        'product_id',
        'quantity',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Product, $this>
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
