<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{

    use HasFactory, Notifiable, HasRoles {
        hasRole as hasRoleViaSpatie;
    }

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    public function hasRole($roles, $guard = null): bool
    {
        if (is_array($roles)) {
            foreach ($roles as $r) {
                if ($this->hasRole($r, $guard)) {
                    return true;
                }
            }
            return false;
        }

        if ($roles instanceof \Illuminate\Support\Collection) {
            return $roles->contains(function ($r) use ($guard) {
                return $this->hasRole($r, $guard);
            });
        }

        $roleName = is_string($roles) ? $roles : $roles->name;

        // If the database column is populated, check it first.
        if (!empty($this->role)) {
            return strtolower($this->role) === strtolower($roleName);
        }

        // Fallback to Spatie's native role check if column is empty
        return $this->hasRoleViaSpatie($roles, $guard);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isUser(): bool
    {
        return $this->hasRole('user');
    }

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
