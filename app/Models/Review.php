<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = ['user_id', 'product_id', 'parent_id', 'rating', 'comment'];

    protected $with = ['user']; // Всегда подгружаем автора

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function replies()
    {
        return $this->hasMany(Review::class, 'parent_id')->orderBy('created_at', 'asc');
    }

    public function likes()
    {
        return $this->hasMany(ReviewLike::class);
    }

    // Проверка, лайкнул ли текущий юзер (для API)
    public function getIsLikedAttribute()
    {
        if (!auth('sanctum')->check()) return false;
        return $this->likes()->where('user_id', auth('sanctum')->id())->exists();
    }
}
