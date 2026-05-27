@extends('superadmin._layout')
@section('title', 'Suscripciones')

@section('panel')
{{-- Filters --}}
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:16px 24px;margin-bottom:20px;display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
    <form method="GET" action="{{ route('superadmin.subscriptions') }}" style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
        <select name="plan" style="padding:8px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:.8125rem;background:#fff;">
            <option value="">Todos los planes</option>
            @foreach($plans as $plan)
                <option value="{{ $plan->slug }}" {{ request('plan') === $plan->slug ? 'selected' : '' }}>{{ $plan->name }}</option>
            @endforeach
        </select>
        <select name="status" style="padding:8px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:.8125rem;background:#fff;">
            <option value="">Todos los estados</option>
            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Activas</option>
            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Canceladas</option>
            <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expiradas</option>
        </select>
        <button type="submit" style="padding:8px 16px;border:none;border-radius:8px;background:#4f46e5;color:#fff;font-size:.8125rem;font-weight:600;cursor:pointer;">Filtrar</button>
        @if(request()->hasAny(['plan', 'status']))
            <a href="{{ route('superadmin.subscriptions') }}" style="font-size:.8125rem;color:#64748b;">Limpiar</a>
        @endif
    </form>
</div>

<div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;overflow:hidden;">
    <table style="width:100%;border-collapse:collapse;">
        <thead>
            <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;">
                <th style="text-align:left;padding:12px 16px;font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;">Usuario</th>
                <th style="text-align:left;padding:12px 16px;font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;">Plan</th>
                <th style="text-align:center;padding:12px 16px;font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;">Estado</th>
                <th style="text-align:left;padding:12px 16px;font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;">Proveedor</th>
                <th style="text-align:left;padding:12px 16px;font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;">Inicio</th>
                <th style="text-align:left;padding:12px 16px;font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;">Expiración</th>
                <th style="text-align:center;padding:12px 16px;font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($subscriptions as $sub)
            @php
                $isActive = $sub->status === 'active';
                $statusColor = $isActive ? '#16a34a' : ($sub->status === 'cancelled' ? '#dc2626' : '#d97706');
                $statusBg = $isActive ? '#f0fdf4' : ($sub->status === 'cancelled' ? '#fef2f2' : '#fffbeb');
            @endphp
            <tr style="border-bottom:1px solid #f8fafc;" onmouseover="this.style.background='#fafbff'" onmouseout="this.style.background='transparent'">
                <td style="padding:12px 16px;">
                    <a href="{{ route('superadmin.subscriptions.show', $sub) }}" style="font-size:.6875rem;color:#94a3b8;display:block;margin-bottom:2px;">#{{ $sub->id }}</a>
                    @if($sub->user)
                        <a href="{{ route('superadmin.users.detail', $sub->user) }}" style="font-size:.8125rem;font-weight:600;color:#4f46e5;">{{ $sub->user->name }}</a>
                        <p style="font-size:.6875rem;color:#94a3b8;margin:2px 0 0;">{{ $sub->user->email }}</p>
                    @else
                        <span style="font-size:.8125rem;color:#94a3b8;">—</span>
                    @endif
                </td>
                <td style="padding:12px 16px;">
                    <span style="font-size:.75rem;font-weight:700;padding:3px 10px;border-radius:6px;background:#f5f3ff;color:#7c3aed;border:1px solid #ddd6fe;">{{ $sub->plan?->name ?? '—' }}</span>
                </td>
                <td style="padding:12px 16px;text-align:center;">
                    <span style="font-size:.6875rem;font-weight:700;padding:3px 10px;border-radius:9999px;background:{{ $statusBg }};color:{{ $statusColor }};">{{ ucfirst($sub->status) }}</span>
                </td>
                <td style="padding:12px 16px;font-size:.8125rem;color:#64748b;">{{ $sub->provider ?? 'manual' }}</td>
                <td style="padding:12px 16px;font-size:.8125rem;color:#64748b;">{{ $sub->started_at ? \Carbon\Carbon::parse($sub->started_at)->format('d/m/Y') : '—' }}</td>
                <td style="padding:12px 16px;font-size:.8125rem;color:#64748b;">{{ $sub->expires_at ? \Carbon\Carbon::parse($sub->expires_at)->format('d/m/Y') : 'Permanente' }}</td>
                <td style="padding:12px 16px;text-align:center;">
                    <div style="display:flex;gap:4px;justify-content:center;flex-wrap:wrap;">
                        @if($isActive)
                            <form method="POST" action="{{ route('superadmin.subscriptions.update', $sub) }}" style="display:inline;">
                                @csrf
                                <input type="hidden" name="action" value="cancel">
                                <button type="submit" style="font-size:.6875rem;font-weight:600;color:#dc2626;padding:4px 8px;border-radius:6px;border:1px solid #fecaca;background:#fef2f2;cursor:pointer;">Cancelar</button>
                            </form>
                            <form method="POST" action="{{ route('superadmin.subscriptions.update', $sub) }}" style="display:inline;">
                                @csrf
                                <input type="hidden" name="action" value="extend">
                                <input type="hidden" name="days" value="30">
                                <button type="submit" style="font-size:.6875rem;font-weight:600;color:#4f46e5;padding:4px 8px;border-radius:6px;border:1px solid #c7d2fe;background:#eef2ff;cursor:pointer;">+30d</button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('superadmin.subscriptions.update', $sub) }}" style="display:inline;">
                                @csrf
                                <input type="hidden" name="action" value="activate">
                                <button type="submit" style="font-size:.6875rem;font-weight:600;color:#16a34a;padding:4px 8px;border-radius:6px;border:1px solid #bbf7d0;background:#f0fdf4;cursor:pointer;">Activar</button>
                            </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" style="padding:48px;text-align:center;color:#94a3b8;">Sin suscripciones registradas.</td></tr>
            @endforelse
        </tbody>
    </table>

    @if($subscriptions->hasPages())
    <div style="padding:16px 24px;border-top:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;">
        <span style="font-size:.8125rem;color:#94a3b8;">{{ $subscriptions->firstItem() }}-{{ $subscriptions->lastItem() }} de {{ $subscriptions->total() }}</span>
        <div style="display:flex;gap:6px;">
            @if(!$subscriptions->onFirstPage())<a href="{{ $subscriptions->previousPageUrl() }}" style="padding:6px 12px;border-radius:8px;font-size:.8125rem;color:#4f46e5;background:#eef2ff;font-weight:600;">Anterior</a>@endif
            @if($subscriptions->hasMorePages())<a href="{{ $subscriptions->nextPageUrl() }}" style="padding:6px 12px;border-radius:8px;font-size:.8125rem;color:#4f46e5;background:#eef2ff;font-weight:600;">Siguiente</a>@endif
        </div>
    </div>
    @endif
</div>
@endsection
