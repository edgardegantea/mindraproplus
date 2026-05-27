@extends('superadmin._layout')
@section('title', 'Gestión de Usuarios')

@section('panel')
{{-- Filtros --}}
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:20px 24px;margin-bottom:24px;display:flex;align-items:center;gap:16px;flex-wrap:wrap;">
    <form method="GET" action="{{ route('superadmin.users') }}" style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;flex:1;">
        <div style="flex:1;min-width:200px;">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por nombre o email..."
                   style="width:100%;padding:10px 14px;border:1px solid #e2e8f0;border-radius:10px;font-size:.875rem;outline:none;transition:border .15s;"
                   onfocus="this.style.borderColor='#6366f1'" onblur="this.style.borderColor='#e2e8f0'">
        </div>
        <select name="role" style="padding:10px 14px;border:1px solid #e2e8f0;border-radius:10px;font-size:.875rem;background:#fff;outline:none;cursor:pointer;">
            <option value="">Todos los roles</option>
            <option value="user" {{ request('role') === 'user' ? 'selected' : '' }}>Usuario</option>
            <option value="psychologist" {{ request('role') === 'psychologist' ? 'selected' : '' }}>Psicólogo</option>
            <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
            <option value="superadmin" {{ request('role') === 'superadmin' ? 'selected' : '' }}>SuperAdmin</option>
        </select>
        <select name="plan" style="padding:10px 14px;border:1px solid #e2e8f0;border-radius:10px;font-size:.875rem;background:#fff;outline:none;cursor:pointer;">
            <option value="">Todos los planes</option>
            <option value="free" {{ request('plan') === 'free' ? 'selected' : '' }}>Free</option>
            <option value="pro" {{ request('plan') === 'pro' ? 'selected' : '' }}>Pro</option>
            <option value="plus" {{ request('plan') === 'plus' ? 'selected' : '' }}>Plus</option>
        </select>
        <button type="submit" style="padding:10px 20px;border:none;border-radius:10px;background:linear-gradient(135deg,#38bdf8,#6366f1,#9333ea);color:#fff;font-size:.875rem;font-weight:600;cursor:pointer;transition:opacity .15s;"
                onmouseover="this.style.opacity='.9'" onmouseout="this.style.opacity='1'">Filtrar</button>
        @if(request()->hasAny(['search','role','plan']))
            <a href="{{ route('superadmin.users') }}" style="font-size:.8125rem;color:#94a3b8;font-weight:500;">Limpiar</a>
        @endif
    </form>
</div>

{{-- Tabla de usuarios --}}
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;overflow:hidden;">
    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;">
                    <th style="text-align:left;padding:14px 16px;font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">Usuario</th>
                    <th style="text-align:left;padding:14px 16px;font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">Rol</th>
                    <th style="text-align:left;padding:14px 16px;font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">Plan</th>
                    <th style="text-align:left;padding:14px 16px;font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">Institución</th>
                    <th style="text-align:center;padding:14px 16px;font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">Sesiones</th>
                    <th style="text-align:center;padding:14px 16px;font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                @php
                    $activeSub = $user->subscriptions->first();
                    $currentPlan = $activeSub?->plan?->slug ?? 'free';
                    $planLabel = match($currentPlan) { 'pro' => 'Pro', 'plus' => 'Plus', default => 'Free' };
                    $planBg = match($currentPlan) { 'pro' => 'background:#eef2ff;color:#4f46e5;border-color:#c7d2fe;', 'plus' => 'background:#f5f3ff;color:#7c3aed;border-color:#ddd6fe;', default => 'background:#f8fafc;color:#64748b;border-color:#e2e8f0;' };
                @endphp
                <tr style="border-bottom:1px solid #f1f5f9;" onmouseover="this.style.background='#fafbff'" onmouseout="this.style.background='transparent'">
                    <td style="padding:14px 16px;">
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div style="width:36px;height:36px;border-radius:9999px;background:linear-gradient(135deg,#38bdf8,#6366f1,#9333ea);display:flex;align-items:center;justify-content:center;color:#fff;font-size:.8125rem;font-weight:700;flex-shrink:0;">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            <div>
                                <p style="font-size:.875rem;font-weight:600;color:#1e293b;margin:0;">{{ $user->name }}</p>
                                <p style="font-size:.75rem;color:#94a3b8;margin:2px 0 0;">{{ $user->email }}</p>
                                <p style="font-size:.6875rem;color:#cbd5e1;margin:2px 0 0;">Registro: {{ $user->created_at->format('d/m/Y') }}</p>
                            </div>
                        </div>
                    </td>
                    <td style="padding:14px 16px;">
                        @php $userRoles = $user->allRoles(); @endphp
                        <form method="POST" action="{{ route('superadmin.users.update', $user) }}" id="rolesForm{{ $user->id }}" style="display:inline;">
                            @csrf
                            <input type="hidden" name="action" value="set_roles">
                            <div style="display:flex;flex-wrap:wrap;gap:4px;">
                                @foreach(['user', 'psychologist', 'admin', 'superadmin'] as $r)
                                    <label style="display:flex;align-items:center;gap:3px;font-size:.6875rem;font-weight:600;padding:2px 6px;border-radius:6px;cursor:pointer;
                                        {{ in_array($r, $userRoles) ? 'background:#eef2ff;color:#4f46e5;border:1px solid #c7d2fe;' : 'background:#f8fafc;color:#94a3b8;border:1px solid #e2e8f0;' }}">
                                        <input type="checkbox" name="roles[]" value="{{ $r }}" {{ in_array($r, $userRoles) ? 'checked' : '' }}
                                               onchange="document.getElementById('rolesForm{{ $user->id }}').submit()"
                                               style="width:10px;height:10px;accent-color:#4f46e5;">
                                        {{ $r }}
                                    </label>
                                @endforeach
                            </div>
                        </form>
                    </td>
                    <td style="padding:14px 16px;">
                        <form method="POST" action="{{ route('superadmin.users.update', $user) }}" style="display:inline;">
                            @csrf
                            <input type="hidden" name="action" value="set_plan">
                            <select name="plan_slug" onchange="this.form.submit()" style="padding:5px 8px;border:1px solid;border-radius:8px;font-size:.75rem;font-weight:700;cursor:pointer;outline:none;{{ $planBg }}">
                                <option value="free" {{ $currentPlan === 'free' ? 'selected' : '' }}>Free</option>
                                @foreach($plans as $plan)
                                    @if($plan->slug !== 'free')
                                        <option value="{{ $plan->slug }}" {{ $currentPlan === $plan->slug ? 'selected' : '' }}>{{ ucfirst($plan->name) }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </form>
                    </td>
                    <td style="padding:14px 16px;">
                        <form method="POST" action="{{ route('superadmin.users.update', $user) }}" style="display:inline;">
                            @csrf
                            <input type="hidden" name="action" value="set_institution">
                            <select name="institution_id" onchange="this.form.submit()" style="padding:5px 8px;border:1px solid #e2e8f0;border-radius:8px;font-size:.75rem;background:#fff;cursor:pointer;outline:none;max-width:140px;">
                                <option value="">Sin asignar</option>
                                @foreach($institutions as $inst)
                                    <option value="{{ $inst->id }}" {{ $user->institution_id == $inst->id ? 'selected' : '' }}>{{ $inst->name }}</option>
                                @endforeach
                            </select>
                        </form>
                    </td>
                    <td style="padding:14px 16px;text-align:center;font-size:.875rem;font-weight:600;color:#334155;">{{ $user->inference_records_count }}</td>
                    <td style="padding:14px 16px;text-align:center;">
                        <div style="display:flex;align-items:center;justify-content:center;gap:5px;flex-wrap:wrap;">
                            <a href="{{ route('superadmin.users.detail', $user) }}"
                               style="font-size:.6875rem;font-weight:600;color:#4f46e5;padding:5px 10px;border-radius:8px;border:1px solid #c7d2fe;background:#eef2ff;">Ver</a>
                            @if($currentPlan !== 'free')
                            <form method="POST" action="{{ route('superadmin.users.update', $user) }}" style="display:inline;">
                                @csrf
                                <input type="hidden" name="action" value="notify_plan">
                                <button type="submit" title="Reenviar email de activación de plan"
                                        style="font-size:.6875rem;font-weight:600;color:#16a34a;padding:5px 10px;border-radius:8px;border:1px solid #bbf7d0;background:#f0fdf4;cursor:pointer;">📧</button>
                            </form>
                            @endif
                            <form method="POST" action="{{ route('superadmin.users.update', $user) }}" style="display:inline;">
                                @csrf
                                <input type="hidden" name="action" value="toggle_status">
                                <button type="submit"
                                        style="font-size:.6875rem;font-weight:600;color:#dc2626;padding:5px 10px;border-radius:8px;border:1px solid #fecaca;background:#fef2f2;cursor:pointer;">Desact.</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="padding:48px;text-align:center;color:#94a3b8;font-size:.875rem;">No se encontraron usuarios con los filtros aplicados.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($users->hasPages())
    <div style="padding:16px 24px;border-top:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;">
        <span style="font-size:.8125rem;color:#94a3b8;">Mostrando {{ $users->firstItem() }}-{{ $users->lastItem() }} de {{ $users->total() }}</span>
        <div style="display:flex;gap:6px;">
            @if($users->onFirstPage())
                <span style="padding:6px 12px;border-radius:8px;font-size:.8125rem;color:#cbd5e1;background:#f8fafc;">Anterior</span>
            @else
                <a href="{{ $users->previousPageUrl() }}" style="padding:6px 12px;border-radius:8px;font-size:.8125rem;color:#4f46e5;background:#eef2ff;font-weight:600;">Anterior</a>
            @endif
            @if($users->hasMorePages())
                <a href="{{ $users->nextPageUrl() }}" style="padding:6px 12px;border-radius:8px;font-size:.8125rem;color:#4f46e5;background:#eef2ff;font-weight:600;">Siguiente</a>
            @else
                <span style="padding:6px 12px;border-radius:8px;font-size:.8125rem;color:#cbd5e1;background:#f8fafc;">Siguiente</span>
            @endif
        </div>
    </div>
    @endif
</div>
@endsection
