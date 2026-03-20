<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property int $product_id
 * @property int|null $parent_id
 * @property int|null $rating
 * @property string $comment
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property User $user
 * @property Product $product
 * @property Collection<int, ReviewLike> $likes
 * @property Collection<int, Review> $replies
 * @property int $likes_count
 */
class Review extends Model
{
    /** @use HasFactory<Factory> */
    use HasFactory;

    protected $fillable = [
        'product_id',
        'user_id',
        'parent_id',
        'rating',
        'comment',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return HasMany<ReviewLike, $this>
     */
    public function likes(): HasMany
    {
        return $this->hasMany(ReviewLike::class);
    }

    /**
     * @return HasMany<Review, $this>
     */
    public function replies(): HasMany
    {
        return $this->hasMany(Review::class, 'parent_id');
    }
}
