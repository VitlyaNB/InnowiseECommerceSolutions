<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Атрибуты, которые можно массово присваивать.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'balance', // <--- Добавили баланс
    ];

    /**
     * Атрибуты, которые должны быть скрыты при сериализации.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Атрибуты, которые нужно приводить к определенным типам.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'balance' => 'decimal:2', // <--- Гарантирует, что это всегда число с 2 знаками (float)
        ];
    }
}
