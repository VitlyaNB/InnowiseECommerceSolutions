<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property string|null $image_path
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Database\Eloquent\Collection<int, Product> $products
 * 
 * @method static \Illuminate\Database\Eloquent\Builder<Category> query()
 * @method static \Illuminate\Database\Eloquent\Builder<Category> where(string|array<string, mixed>|\Closure $column, mixed $operator = null, mixed $value = null, string $boolean = 'and')
 * @method static Category create(array<string, mixed> $attributes = [])
 * @method static Category findOrFail(mixed $id, array<int, string> $columns = ['*'])
 * @method static Category find(mixed $id, array<int, string> $columns = ['*'])
 */
class Category extends Model
{
    /** @use HasFactory<\Database\Factories\CategoryFactory> */
    use HasFactory;

    protected $fillable = ['name', 'image_path'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<Product, $this>
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
