<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string $role
 * @property float $balance
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * 
 * @method static \Illuminate\Database\Eloquent\Builder<User> query()
 * @method static \Illuminate\Database\Eloquent\Builder<User> where(string|array<string, mixed>|\Closure $column, mixed $operator = null, mixed $value = null, string $boolean = 'and')
 * @method static User create(array<string, mixed> $attributes = [])
 * @method static User findOrFail(mixed $id, array<int, string> $columns = ['*'])
 * @method static User find(mixed $id, array<int, string> $columns = ['*'])
 * @method static \Illuminate\Database\Eloquent\Builder<User> orderBy(string $column, string $direction = 'asc')
 * @method static User|null first(array<int, string> $columns = ['*'])
 * @method static \Illuminate\Database\Eloquent\Collection<int, User> get(array<int, string> $columns = ['*'])
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;


    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'balance',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<Order, $this>
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'balance' => 'decimal:2',
        ];
    }
}
