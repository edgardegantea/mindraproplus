@extends('superadmin._layout')

@section('title', 'Suscripción #' . $subscription->id)

@section('content')
@php
    $sub        = $subscription;
    $isActive   = $sub->status === 'active';
    $statusColor= $isActive ? '#16a34a' : ($sub->status === 'cancelled' ? '#dc2626' : '#d97706');
    $statusBg   = $isActive ? '#f0fdf4' : ($sub->status === 'cancelled' ? '#fef2f2' : '#fffbeb');

    $effectiveFeatures = $sub->features_override ?? $sub->plan?->features ?? [];
@endphp

<!-- Breadcrumb -->
<div style="display:flex;align-items:center;gap:8px;margin-bottom:20px;font-size:.8125rem;color:#64748b;">
    <a href="{{ route('superadmin.subscriptions') }}" style="color:#4f46e5;font-weight:600;">Suscripciones</a>
    <span>/</span>
    <span>#{{ $sub->id }}</span>
</div>

@if(session('success'))
<div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:12px 16px;margin-bottom:20px;color:#166534;font-size:.875rem;">
    ✅ {{ session('success') }}
</div>
@endif

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;">

    {{-- Datos del usuario --}}
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:20px;">
        <h2 style="font-size:.9375rem;font-weight:700;color:#334155;margin-bottom:14px;">👤 Usuario</h2>
        @if($sub->user)
            <p style="font-size:.8125rem;margin-bottom:6px;">
                <span style="color:#64748b;">Nombre:</span>
                <a href="{{ route('superadmin.users.detail', $sub->user) }}" style="color:#4f46e5;font-weight:600;">{{ $sub->user->name }}</a>
            </p>
            <p style="font-size:.8125rem;margin-bottom:6px;"><span style="color:#64748b;">Email:</span> {{ $sub->user->email }}</p>
            <p style="font-size:.8125rem;"><span style="color:#64748b;">Rol:</span> {{ $sub->user->role }}</p>
        @else
            <p style="color:#94a3b8;font-size:.8125rem;">Usuario eliminado</p>
        @endif
    </div>

    {{-- Datos de la suscripción --}}
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:20px;">
        <h2 style="font-size:.9375rem;font-weight:700;color:#334155;margin-bottom:14px;">📋 Suscripción</h2>
        <p style="font-size:.8125rem;margin-bottom:6px;">
            <span style="color:#64748b;">ID:</span> #{{ $sub->id }}
        </p>
        <p style="font-size:.8125rem;margin-bottom:6px;">
            <span style="color:#64748b;">Plan:</span>
            <span style="font-weight:700;color:#7c3aed;">{{ $sub->plan?->name ?? '—' }}</span>
        </p>
        <p style="font-size:.8125rem;margin-bottom:6px;">
            <span style="color:#64748b;">Estado:</span>
            <span style="font-weight:700;padding:2px 8px;border-radius:99px;background:{{ $statusBg }};color:{{ $statusColor }};">
                {{ ucfirst($sub->status) }}
            </span>
        </p>
        <p style="font-size:.8125rem;margin-bottom:6px;">
            <span style="color:#64748b;">Proveedor:</span> {{ $sub->provider ?? 'manual' }}
        </p>
        <p style="font-size:.8125rem;margin-bottom:6px;">
            <span style="color:#64748b;">Inicio:</span>
            {{ $sub->started_at ? \Carbon\Carbon::parse($sub->started_at)->format('d/m/Y H:i') : '—' }}
        </p>
        <p style="font-size:.8125rem;">
            <span style="color:#64748b;">Vence:</span>
            {{ $sub->expires_at ? \Carbon\Carbon::parse($sub->expires_at)->format('d/m/Y H:i') : 'Permanente' }}
        </p>
    </div>
</div>

{{-- Acciones rápidas --}}
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:20px;margin-bottom:20px;">
    <h2 style="font-size:.9375rem;font-weight:700;color:#334155;margin-bottom:14px;">⚡ Acciones rápidas</h2>
    <div style="display:flex;gap:10px;flex-wrap:wrap;">

        @if($isActive)
        <form method="POST" action="{{ route('superadmin.subscriptions.update', $sub) }}">
            @csrf
            <input type="hidden" name="action" value="cancel">
            <button type="submit" style="font-size:.8125rem;font-weight:600;color:#dc2626;padding:8px 16px;border-radius:8px;border:1px solid #fecaca;background:#fef2f2;cursor:pointer;"
                onclick="return confirm('¿Cancelar esta suscripción?')">
                ✕ Cancelar suscripción
            </button>
        </form>
        <form method="POST" action="{{ route('superadmin.subscriptions.update', $sub) }}">
            @csrf
            <input type="hidden" name="action" value="extend">
            <input type="hidden" name="days" value="30">
            <button type="submit" style="font-size:.8125rem;font-weight:600;color:#4f46e5;padding:8px 16px;border-radius:8px;border:1px solid #c7d2fe;background:#eef2ff;cursor:pointer;">
                ＋30 días
            </button>
        </form>
        <form method="POST" action="{{ route('superadmin.subscriptions.update', $sub) }}">
            @csrf
            <input type="hidden" name="action" value="extend">
            <input type="hidden" name="days" value="365">
            <button type="submit" style="font-size:.8125rem;font-weight:600;color:#0891b2;padding:8px 16px;border-radius:8px;border:1px solid #bae6fd;background:#f0f9ff;cursor:pointer;">
                ＋1 año
            </button>
        </form>
        @else
        <form method="POST" action="{{ route('superadmin.subscriptions.update', $sub) }}">
            @csrf
            <input type="hidden" name="action" value="activate">
            <button type="submit" style="font-size:.8125rem;font-weight:600;color:#16a34a;padding:8px 16px;border-radius:8px;border:1px solid #bbf7d0;background:#f0fdf4;cursor:pointer;">
                ✓ Reactivar
            </button>
        </form>
        @endif

    </div>
</div>

{{-- Features override --}}
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:20px;margin-bottom:20px;">
    <h2 style="font-size:.9375rem;font-weight:700;color:#334155;margin-bottom:4px;">🔧 Features efectivas</h2>
    <p style="font-size:.8125rem;color:#64748b;margin-bottom:16px;">
        Puedes sobreescribir las features del plan base para este usuario específico.
        @if($sub->features_override)
            <span style="background:#fef3c7;color:#92400e;font-weight:600;padding:2px 8px;border-radius:6px;font-size:.75rem;">Override activo</span>
        @endif
    </p>
    <form method="POST" action="{{ route('superadmin.subscriptions.update', $sub) }}">
        @csrf
        <input type="hidden" name="action" value="update_features">
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:12px;margin-bottom:16px;">
            @foreach($effectiveFeatures as $feature => $value)
            <label style="display:flex;align-items:center;gap:10px;background:#f8fafc;padding:10px 14px;border-radius:8px;border:1px solid #e2e8f0;cursor:pointer;">
                <input type="checkbox" name="features[{{ $feature }}]" value="1"
                    {{ $value ? 'checked' : '' }}
                    style="width:16px;height:16px;accent-color:#4f46e5;">
                <span style="font-size:.8125rem;font-weight:500;">{{ $feature }}</span>
            </label>
            @endforeach
        </div>
        <button type="submit" style="font-size:.8125rem;font-weight:600;color:#fff;padding:8px 20px;border-radius:8px;border:none;background:#4f46e5;cursor:pointer;">
            Guardar features
        </button>
    </form>
</div>

{{-- Historial de inferencias --}}
@if($sub->user)
@php
    $records = $sub->user->inferenceRecords()->latest()->take(10)->get();
@endphp
@if($records->count() > 0)
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:20px;">
    <h2 style="font-size:.9375rem;font-weight:700;color:#334155;margin-bottom:14px;">💬 Últimas sesiones del usuario</h2>
    <table style="width:100%;border-collapse:collapse;font-size:.8125rem;">
        <thead>
            <tr style="background:#f8fafc;">
                <th style="padding:8px 12px;text-align:left;color:#64748b;font-size:.6875rem;font-weight:600;text-transform:uppercase;">Fecha</th>
                <th style="padding:8px 12px;text-align:left;color:#64748b;font-size:.6875rem;font-weight:600;text-transform:uppercase;">Texto</th>
                <th style="padding:8px 12px;text-align:left;color:#64748b;font-size:.6875rem;font-weight:600;text-transform:uppercase;">Etiqueta</th>
                <th style="padding:8px 12px;text-align:left;color:#64748b;font-size:.6875rem;font-weight:600;text-transform:uppercase;">Emoción</th>
            </tr>
        </thead>
        <tbody>
            @foreach($records as $rec)
            <tr style="border-bottom:1px solid #f8fafc;">
                <td style="padding:8px 12px;white-space:nowrap;">{{ $rec->created_at->format('d/m/Y') }}</td>
                <td style="padding:8px 12px;max-width:240px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                    {{ \Illuminate\Support\Str::limit($rec->texto ?? '', 60) }}
                </td>
                <td style="padding:8px 12px;">{{ $rec->etiqueta ?? '—' }}</td>
                <td style="padding:8px 12px;">{{ $rec->emocion ?? '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif
@endif

@endsection
