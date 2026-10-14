<?php

namespace App\Models;

use App\Domain\Access\RoleGrant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'agency_unit',
        'phone',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    public function roleGrants(): HasMany
    {
        return $this->hasMany(RoleGrant::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->roleGrants()->where('role', 'super_admin')->exists();
    }

    public function hasRole(string $role): bool
    {
        return $this->roleGrants()->where('role', $role)->exists();
    }
}
