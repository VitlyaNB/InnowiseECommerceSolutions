<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    protected static function booted(): void
    {
        static::deleting(function (Category $category) {
            $category->products()->each(fn (Product $p) => $p->delete());
        });
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
