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
     * Атрибуты, которые можно массово назначать.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role', // Поле role используется в репозитории
    ];

    /**
     * Атрибуты, которые должны быть скрыты при преобразовании в массив или JSON.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Преобразование типов данных (casts).
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed', // Автоматическое хэширование пароля
        ];
    }

    /**
     * Хелпер для проверки, является ли пользователь администратором.
     * Полезно для чистого кода в других частях системы.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
