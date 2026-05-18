@extends('superadmin._layout')
@section('title', 'Sesiones')

@section('panel')
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;overflow:hidden;">
    <div style="padding:20px 24px;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;">
        <div>
            <h3 style="font-size:1rem;font-weight:700;color:#0f172a;margin:0;">Todas las sesiones del sistema</h3>
            <p style="font-size:.8125rem;color:#94a3b8;margin:4px 0 0;">Historial global de interacciones</p>
        </div>
        <div style="display:flex;gap:8px;align-items:center;">
            <form method="GET" action="{{ route('superadmin.sessions') }}" style="display:flex;gap:8px;">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar usuario..."
                       style="padding:8px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:.8125rem;outline:none;width:180px;">
                <button type="submit" style="padding:8px 14px;border:none;border-radius:8px;background:#4f46e5;color:#fff;font-size:.75rem;font-weight:600;cursor:pointer;">Buscar</button>
            </form>
            <form method="POST" action="{{ route('superadmin.sessions.export') }}">
                @csrf
                <button type="submit" style="padding:8px 14px;border:1px solid #e2e8f0;border-radius:8px;background:#fff;font-size:.75rem;font-weight:600;color:#334155;cursor:pointer;">Exportar CSV</button>
            </form>
        </div>
    </div>

    <table style="width:100%;border-collapse:collapse;">
        <thead>
            <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;">
                <th style="text-align:left;padding:12px 16px;font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;">Fecha</th>
                <th style="text-align:left;padding:12px 16px;font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;">Usuario</th>
                <th style="text-align:left;padding:12px 16px;font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;">Texto</th>
                <th style="text-align:left;padding:12px 16px;font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;">Respuesta</th>
                <th style="text-align:center;padding:12px 16px;font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;">Ansiedad</th>
                <th style="text-align:center;padding:12px 16px;font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;">Acción</th>
            </tr>
        </thead>
        <tbody>
            @forelse($records as $record)
            @php
                $pct = $record->predicted_probability ? round($record->predicted_probability * 100) : null;
                $color = $pct !== null ? ($pct > 65 ? '#dc2626' : ($pct > 40 ? '#d97706' : '#16a34a')) : '#94a3b8';
                $bg = $pct !== null ? ($pct > 65 ? '#fef2f2' : ($pct > 40 ? '#fffbeb' : '#f0fdf4')) : '#f8fafc';
            @endphp
            <tr style="border-bottom:1px solid #f8fafc;" onmouseover="this.style.background='#fafbff'" onmouseout="this.style.background='transparent'">
                <td style="padding:12px 16px;font-size:.8125rem;color:#64748b;white-space:nowrap;">{{ $record->created_at->format('d/m/Y H:i') }}</td>
                <td style="padding:12px 16px;">
                    @if($record->user)
                        <a href="{{ route('superadmin.users.detail', $record->user) }}" style="font-size:.8125rem;font-weight:600;color:#4f46e5;">{{ $record->user->name }}</a>
                    @else
                        <span style="font-size:.8125rem;color:#94a3b8;">—</span>
                    @endif
                </td>
                <td style="padding:12px 16px;font-size:.8125rem;color:#334155;max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $record->input_text ?: '(audio)' }}</td>
                <td style="padding:12px 16px;font-size:.8125rem;color:#64748b;max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $record->generated_text ?: '—' }}</td>
                <td style="padding:12px 16px;text-align:center;">
                    @if($pct !== null)
                        <span style="font-size:.75rem;font-weight:700;padding:3px 10px;border-radius:9999px;background:{{ $bg }};color:{{ $color }};">{{ $pct }}%</span>
                    @else
                        <span style="color:#cbd5e1;font-size:.75rem;">—</span>
                    @endif
                </td>
                <td style="padding:12px 16px;text-align:center;">
                    <form method="POST" action="{{ route('superadmin.sessions.delete', $record) }}" onsubmit="return confirm('¿Eliminar esta sesión?');" style="display:inline;">
                        @csrf
                        <button type="submit" style="font-size:.6875rem;font-weight:600;color:#dc2626;padding:4px 10px;border-radius:8px;border:1px solid #fecaca;background:#fef2f2;cursor:pointer;">Eliminar</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" style="padding:48px;text-align:center;color:#94a3b8;">Sin sesiones registradas.</td></tr>
            @endforelse
        </tbody>
    </table>

    @if($records->hasPages())
    <div style="padding:16px 24px;border-top:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;">
        <span style="font-size:.8125rem;color:#94a3b8;">{{ $records->firstItem() }}-{{ $records->lastItem() }} de {{ $records->total() }}</span>
        <div style="display:flex;gap:6px;">
            @if(!$records->onFirstPage())<a href="{{ $records->previousPageUrl() }}" style="padding:6px 12px;border-radius:8px;font-size:.8125rem;color:#4f46e5;background:#eef2ff;font-weight:600;">Anterior</a>@endif
            @if($records->hasMorePages())<a href="{{ $records->nextPageUrl() }}" style="padding:6px 12px;border-radius:8px;font-size:.8125rem;color:#4f46e5;background:#eef2ff;font-weight:600;">Siguiente</a>@endif
        </div>
    </div>
    @endif
</div>
@endsection
