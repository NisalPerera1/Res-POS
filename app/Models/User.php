<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, HasApiTokens;

    protected $fillable = [
        'name', 'pin', 'role', 'avatar', 'color', 'is_active', 'last_login_at'
    ];

    protected $hidden = ['pin'];

    protected $casts = [
        'is_active'     => 'boolean',
        'last_login_at' => 'datetime',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function isAdmin(): bool    { return $this->role === 'admin'; }
    public function isCashier(): bool  { return $this->role === 'cashier'; }
    public function isWaiter(): bool   { return $this->role === 'waiter'; }
    public function isKitchen(): bool  { return $this->role === 'kitchen'; }
}