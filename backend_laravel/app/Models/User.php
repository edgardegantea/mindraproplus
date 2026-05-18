<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'role', 'institution_id'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // --- Relationships ---

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function currentSubscription()
    {
        return $this->hasOne(Subscription::class)->latestOfMany();
    }

    public function inferenceRecords()
    {
        return $this->hasMany(InferenceRecord::class);
    }

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class)->withTimestamps();
    }

    public function planRequests()
    {
        return $this->hasMany(PlanRequest::class);
    }

    // --- Role helpers (multi-role via role_user pivot) ---

    public function allRoles(): array
    {
        $pivotRoles = \Illuminate\Support\Facades\DB::table('role_user')
            ->where('user_id', $this->id)
            ->pluck('role')
            ->toArray();

        if (empty($pivotRoles)) {
            return [$this->role ?? 'user'];
        }

        return array_unique(array_merge([$this->role], $pivotRoles));
    }

    public function hasRole(string $role): bool
    {
        if ($this->role === $role) {
            return true;
        }

        return \Illuminate\Support\Facades\DB::table('role_user')
            ->where('user_id', $this->id)
            ->where('role', $role)
            ->exists();
    }

    public function assignRole(string $role): void
    {
        \Illuminate\Support\Facades\DB::table('role_user')->insertOrIgnore([
            'user_id' => $this->id,
            'role' => $role,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function removeRole(string $role): void
    {
        \Illuminate\Support\Facades\DB::table('role_user')
            ->where('user_id', $this->id)
            ->where('role', $role)
            ->delete();
    }

    public function syncRoles(array $roles): void
    {
        \Illuminate\Support\Facades\DB::table('role_user')
            ->where('user_id', $this->id)
            ->delete();

        foreach ($roles as $role) {
            $this->assignRole($role);
        }

        $this->update(['role' => $roles[0] ?? 'user']);
    }

    // --- Legacy single-role checks (still used by middleware) ---

    public function isAdmin(): bool
    {
        return $this->hasRole('admin') || $this->hasRole('superadmin');
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('superadmin');
    }

    // --- Plan helpers ---

    public function activePlan(): ?Plan
    {
        return $this->subscriptions()
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>=', now());
            })
            ->latest('expires_at')
            ->first()?->plan;
    }
}
