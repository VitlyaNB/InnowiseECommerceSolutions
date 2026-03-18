<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $user_id
 * @property int $product_id
 * @property int|null $parent_id
 * @property int|null $rating
 * @property string $comment
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property User $user
 * @property Product $product
 * @property \Illuminate\Database\Eloquent\Collection<int, ReviewLike> $likes
 * @property \Illuminate\Database\Eloquent\Collection<int, Review> $replies
 * @property int $likes_count
 * 
 * @method static \Illuminate\Database\Eloquent\Builder<Review> query()
 * @method static \Illuminate\Database\Eloquent\Builder<Review> where(string|array<string, mixed>|\Closure $column, mixed $operator = null, mixed $value = null, string $boolean = 'and')
 * @method static Review create(array<string, mixed> $attributes = [])
 * @method static Review find(mixed $id, array<int, string> $columns = ['*'])
 * @method static \Illuminate\Database\Eloquent\Builder<Review> whereNull(string|array<int, string> $columns, string $boolean = 'and', bool $not = false)
 */
class Review extends Model
{
    /** @use HasFactory<\Illuminate\Database\Eloquent\Factories\Factory> */
    use HasFactory;

    protected $fillable = [
        'product_id',
        'user_id',
        'parent_id',
        'rating',
        'comment',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<ReviewLike, $this>
     */
    public function likes(): HasMany
    {
        return $this->hasMany(ReviewLike::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<Review, $this>
     */
    public function replies(): HasMany
    {
        return $this->hasMany(Review::class, 'parent_id');
    }
}
