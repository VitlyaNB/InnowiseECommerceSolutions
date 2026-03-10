<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int|null $user_id
 * @property string|null $session_id
 * @property int $product_id
 * @property \Illuminate\Support\Carbon $viewed_at
 * 
 * @method static \Illuminate\Database\Eloquent\Builder<ProductView> query()
 * @method static ProductView updateOrCreate(array<string, mixed> $attributes, array<string, mixed> $values = [])
 * @method static \Illuminate\Database\Eloquent\Builder<ProductView> where(string|array<string, mixed>|\Closure $column, mixed $operator = null, mixed $value = null, string $boolean = 'and')
 */
class ProductView extends Model
{
    /** @use HasFactory<\Illuminate\Database\Eloquent\Factories\Factory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_id',
        'product_id',
        'viewed_at',
    ];

    public $timestamps = false;

    /** @var array<string, string> */
    protected $casts = [
        'viewed_at' => 'datetime',
    ];
}
