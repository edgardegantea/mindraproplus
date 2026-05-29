<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
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

    public function moodJournals()
    {
        return $this->hasMany(MoodJournal::class);
    }

    public function assessments()
    {
        return $this->hasMany(Assessment::class);
    }

    public function therapistShares()
    {
        return $this->hasMany(TherapistShare::class);
    }

    public function programEnrollments()
    {
        return $this->hasMany(ProgramEnrollment::class);
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

    /**
     * Plan activo del usuario.
     *
     * Cacheado 5 minutos por user_id para evitar un DB hit en cada request
     * autenticado. Se invalida en SubscriptionService cuando cambia la suscripción.
     * Clave: "user_plan:{id}"
     */
    public function activePlan(): ?Plan
    {
        return Cache::remember("user_plan:{$this->id}", 300, function () {
            return $this->subscriptions()
                ->where('status', 'active')
                ->where(function ($q) {
                    $q->whereNull('expires_at')
                      ->orWhere('expires_at', '>=', now());
                })
                ->latest('expires_at')
                ->first()?->plan;
        });
    }

    /**
     * Invalida la caché del plan activo de este usuario.
     * Llamar desde SubscriptionService después de crear/activar una suscripción.
     */
    public function forgetPlanCache(): void
    {
        Cache::forget("user_plan:{$this->id}");
    }

    /**
     * Devuelve la suscripción activa con su plan cargado.
     */
    public function activeSubscription(): ?\App\Models\Subscription
    {
        return $this->subscriptions()
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>=', now());
            })
            ->latest('expires_at')
            ->with('plan')
            ->first();
    }

    /**
     * Features efectivas del usuario según su suscripción activa.
     * Si no tiene suscripción activa se devuelven las features del plan Free.
     *
     * Memoizado con once() — se calcula una sola vez por instancia/request.
     * Evita múltiples DB hits cuando controllers o middleware lo llaman varias
     * veces sobre el mismo usuario en la misma request.
     */
    public function features(): array
    {
        return once(function () {
            $sub = $this->activeSubscription();

            if ($sub) {
                return $sub->effectiveFeatures();
            }

            // Sin suscripción activa → features del plan Free
            return Plan::free()->features ?? [
                'texto'        => true,
                'audio'        => true,
                'emociones'    => false,
                'historial'    => false,
                'imagen'       => false,
                'estadisticas' => false,
            ];
        });
    }

    public function notificationPreference()
    {
        return $this->hasOne(\App\Models\NotificationPreference::class);
    }

    public function crisisEvents()
    {
        return $this->hasMany(\App\Models\CrisisEvent::class);
    }

    /**
     * Datos seguros para exponer en respuestas API.
     * No incluye role, institution_id ni otros campos internos.
     */
    public function toApiArray(): array
    {
        return [
            'id'                => $this->id,
            'name'              => $this->name,
            'email'             => $this->email,
            'email_verified_at' => $this->email_verified_at?->toIso8601String(),
            'created_at'        => $this->created_at->toIso8601String(),
        ];
    }
}
