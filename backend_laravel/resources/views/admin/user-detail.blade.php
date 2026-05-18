@extends('admin._layout')
@section('title', $user->name)

@section('panel')
{{-- Back link --}}
<a href="{{ route('admin.users') }}" style="display:inline-flex;align-items:center;gap:6px;font-size:.8125rem;font-weight:600;color:#4f46e5;margin-bottom:20px;">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" style="width:14px;height:14px;"><path fill-rule="evenodd" d="M9.78 4.22a.75.75 0 0 1 0 1.06L7.06 8l2.72 2.72a.75.75 0 1 1-1.06 1.06L5.47 8.53a.75.75 0 0 1 0-1.06l3.25-3.25a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd"/></svg>
    Volver a usuarios
</a>

{{-- Profile header --}}
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:28px;margin-bottom:24px;display:flex;align-items:center;gap:20px;">
    <div style="width:64px;height:64px;border-radius:9999px;background:linear-gradient(135deg,#38bdf8,#6366f1,#9333ea);display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.5rem;font-weight:900;flex-shrink:0;">
        {{ strtoupper(substr($user->name, 0, 1)) }}
    </div>
    <div style="flex:1;">
        <h2 style="font-size:1.375rem;font-weight:900;color:#0f172a;margin:0;">{{ $user->name }}</h2>
        <p style="font-size:.8125rem;color:#64748b;margin:4px 0 0;">{{ $user->email }}</p>
        <div style="display:flex;align-items:center;gap:12px;margin-top:8px;">
            <span style="font-size:.6875rem;font-weight:700;padding:3px 10px;border-radius:6px;background:#f8fafc;color:#64748b;border:1px solid #e2e8f0;">{{ $user->role }}</span>
            <span style="font-size:.75rem;color:#94a3b8;">Registrado {{ $user->created_at->translatedFormat('d M Y') }}</span>
        </div>
    </div>
    <form method="POST" action="{{ route('admin.user.update', $user) }}">
        @csrf
        <input type="hidden" name="action" value="toggle_access">
        <button type="submit" style="padding:8px 16px;border-radius:10px;border:1px solid;font-size:.8125rem;font-weight:600;cursor:pointer;
            {{ $user->role === 'user' ? 'color:#16a34a;border-color:#bbf7d0;background:#f0fdf4;' : 'color:#d97706;border-color:#fde68a;background:#fffbeb;' }}">
            {{ $user->role === 'user' ? 'Elevar acceso' : 'Restringir' }}
        </button>
    </form>
</div>

{{-- Stats cards --}}
@php
    $pct = $avgProbability !== null ? round($avgProbability * 100) : null;
    $color = $pct !== null ? ($pct > 65 ? '#dc2626' : ($pct > 40 ? '#d97706' : '#16a34a')) : '#94a3b8';
    $levelLabel = $pct === null ? '—' : ($pct > 65 ? 'Alto' : ($pct > 40 ? 'Moderado' : 'Bajo'));
@endphp
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:28px;">
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:24px;text-align:center;">
        <p style="font-size:2rem;font-weight:900;color:#0f172a;margin:0;">{{ $user->inference_records_count }}</p>
        <p style="font-size:.75rem;font-weight:600;color:#94a3b8;margin:6px 0 0;text-transform:uppercase;">Sesiones totales</p>
    </div>
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:24px;text-align:center;">
        <p style="font-size:2rem;font-weight:900;color:{{ $color }};margin:0;">{{ $pct !== null ? $pct . '%' : '—' }}</p>
        <p style="font-size:.75rem;font-weight:600;color:#94a3b8;margin:6px 0 0;text-transform:uppercase;">Ansiedad promedio</p>
    </div>
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:24px;text-align:center;">
        <p style="font-size:2rem;font-weight:900;color:{{ $color }};margin:0;">{{ $levelLabel }}</p>
        <p style="font-size:.75rem;font-weight:600;color:#94a3b8;margin:6px 0 0;text-transform:uppercase;">Nivel general</p>
    </div>
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:24px;text-align:center;">
        @php $lastSession = $user->inferenceRecords()->latest()->first(); @endphp
        <p style="font-size:1.125rem;font-weight:700;color:#0f172a;margin:0;">{{ $lastSession?->created_at?->diffForHumans() ?? 'Nunca' }}</p>
        <p style="font-size:.75rem;font-weight:600;color:#94a3b8;margin:6px 0 0;text-transform:uppercase;">Última actividad</p>
    </div>
</div>

{{-- Activity chart + Weekly averages --}}
<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;margin-bottom:28px;">
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:24px;">
        <h3 style="font-size:1rem;font-weight:700;color:#0f172a;margin:0 0 20px;">Actividad (30 días)</h3>
        @php $maxA = $userChart->max() ?: 1; @endphp
        <div style="display:flex;align-items:flex-end;gap:3px;height:100px;">
            @foreach($userChart as $date => $count)
                <div style="flex:1;background:linear-gradient(180deg,#6366f1,#4f46e5);border-radius:3px 3px 0 0;height:{{ round($count/$maxA*100) }}%;min-height:2px;"
                     title="{{ \Carbon\Carbon::parse($date)->format('d M') }}: {{ $count }} sesiones"></div>
            @endforeach
        </div>
        <div style="display:flex;justify-content:space-between;margin-top:8px;">
            <span style="font-size:.625rem;color:#94a3b8;">{{ $userChart->keys()->first() }}</span>
            <span style="font-size:.625rem;color:#94a3b8;">{{ $userChart->keys()->last() }}</span>
        </div>
    </div>

    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:24px;">
        <h3 style="font-size:1rem;font-weight:700;color:#0f172a;margin:0 0 20px;">Tendencia semanal</h3>
        @if($weeklyAvg->isNotEmpty())
            <div style="display:flex;flex-direction:column;gap:12px;">
                @foreach($weeklyAvg as $week => $avg)
                    @php
                        $wPct = round($avg * 100);
                        $wColor = $wPct > 65 ? '#dc2626' : ($wPct > 40 ? '#d97706' : '#16a34a');
                        $wBg = $wPct > 65 ? '#fef2f2' : ($wPct > 40 ? '#fffbeb' : '#f0fdf4');
                    @endphp
                    <div>
                        <div style="display:flex;justify-content:space-between;margin-bottom:4px;">
                            <span style="font-size:.75rem;color:#64748b;">Semana {{ $week }}</span>
                            <span style="font-size:.75rem;font-weight:700;color:{{ $wColor }};">{{ $wPct }}%</span>
                        </div>
                        <div style="height:8px;border-radius:9999px;background:#f1f5f9;overflow:hidden;">
                            <div style="height:100%;width:{{ $wPct }}%;border-radius:9999px;background:{{ $wColor }};"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p style="font-size:.875rem;color:#94a3b8;text-align:center;">Sin datos suficientes.</p>
        @endif
    </div>
</div>

{{-- Interaction history --}}
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;overflow:hidden;">
    <div style="padding:20px 24px;border-bottom:1px solid #f1f5f9;">
        <h3 style="font-size:1rem;font-weight:700;color:#0f172a;margin:0;">Historial de interacciones</h3>
        <p style="font-size:.8125rem;color:#94a3b8;margin:4px 0 0;">Todas las sesiones registradas de este usuario</p>
    </div>

    <table style="width:100%;border-collapse:collapse;">
        <thead>
            <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;">
                <th style="text-align:left;padding:12px 16px;font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;">Fecha</th>
                <th style="text-align:left;padding:12px 16px;font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;">Texto enviado</th>
                <th style="text-align:left;padding:12px 16px;font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;">Respuesta IA</th>
                <th style="text-align:center;padding:12px 16px;font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;">Ansiedad</th>
                <th style="text-align:center;padding:12px 16px;font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;">Audio</th>
            </tr>
        </thead>
        <tbody>
            @forelse($records as $record)
            @php
                $rPct = $record->predicted_probability !== null ? round($record->predicted_probability * 100) : null;
                $rColor = $rPct !== null ? ($rPct > 65 ? '#dc2626' : ($rPct > 40 ? '#d97706' : '#16a34a')) : '#94a3b8';
                $rBg = $rPct !== null ? ($rPct > 65 ? '#fef2f2' : ($rPct > 40 ? '#fffbeb' : '#f0fdf4')) : '#f8fafc';
            @endphp
            <tr style="border-bottom:1px solid #f8fafc;" onmouseover="this.style.background='#fafbff'" onmouseout="this.style.background='transparent'">
                <td style="padding:12px 16px;font-size:.8125rem;color:#64748b;white-space:nowrap;">{{ $record->created_at->format('d/m/Y H:i') }}</td>
                <td style="padding:12px 16px;font-size:.8125rem;color:#334155;max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $record->input_text ?: '(audio)' }}</td>
                <td style="padding:12px 16px;font-size:.8125rem;color:#64748b;max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $record->generated_text ?: '—' }}</td>
                <td style="padding:12px 16px;text-align:center;">
                    @if($rPct !== null)
                        <span style="font-size:.75rem;font-weight:700;padding:3px 10px;border-radius:9999px;background:{{ $rBg }};color:{{ $rColor }};">{{ $rPct }}%</span>
                    @else
                        <span style="color:#cbd5e1;font-size:.75rem;">—</span>
                    @endif
                </td>
                <td style="padding:12px 16px;text-align:center;">
                    @if($record->audio_filename)
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="#6366f1" style="width:16px;height:16px;display:inline;"><path d="M7 4a3 3 0 0 1 6 0v6a3 3 0 1 1-6 0V4Z"/></svg>
                    @else
                        <span style="color:#cbd5e1;">—</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="5" style="padding:48px;text-align:center;color:#94a3b8;">Sin interacciones registradas.</td></tr>
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
