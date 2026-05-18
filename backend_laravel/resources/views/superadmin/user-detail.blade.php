@extends('superadmin._layout')
@section('title', $user->name)

@section('panel')
{{-- Header del usuario --}}
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:28px;margin-bottom:24px;display:flex;align-items:center;gap:20px;flex-wrap:wrap;">
    <div style="width:56px;height:56px;border-radius:9999px;background:linear-gradient(135deg,#38bdf8,#6366f1,#9333ea);display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.5rem;font-weight:800;flex-shrink:0;">
        {{ strtoupper(substr($user->name, 0, 1)) }}
    </div>
    <div style="flex:1;min-width:0;">
        <h2 style="font-size:1.25rem;font-weight:800;color:#0f172a;margin:0 0 4px;">{{ $user->name }}</h2>
        <p style="font-size:.875rem;color:#64748b;margin:0;">{{ $user->email }}</p>
    </div>
    <div style="display:flex;gap:10px;align-items:center;">
        @php
            $roleBg = match($user->role) {
                'superadmin' => 'background:#fef2f2;color:#dc2626;border-color:#fecaca;',
                'admin' => 'background:#f5f3ff;color:#7c3aed;border-color:#ddd6fe;',
                'psychologist' => 'background:#f0fdf4;color:#16a34a;border-color:#bbf7d0;',
                default => 'background:#f8fafc;color:#64748b;border-color:#e2e8f0;',
            };
        @endphp
        <span style="font-size:.75rem;font-weight:700;padding:5px 12px;border-radius:8px;border:1px solid;{{ $roleBg }}">{{ $user->role }}</span>
        <span style="font-size:.75rem;font-weight:700;padding:5px 12px;border-radius:8px;border:1px solid;background:#eef2ff;color:#4f46e5;border-color:#c7d2fe;">{{ $activePlan ? ucfirst($activePlan->name) : 'Free' }}</span>
    </div>
</div>

{{-- Stats --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px;">
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:20px;text-align:center;">
        <p style="font-size:1.75rem;font-weight:900;color:#0f172a;margin:0;">{{ $user->inference_records_count }}</p>
        <p style="font-size:.75rem;color:#94a3b8;margin:4px 0 0;">Interacciones</p>
    </div>
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:20px;text-align:center;">
        @php $avgPct = $avgProbability ? round($avgProbability * 100) : 0; @endphp
        <p style="font-size:1.75rem;font-weight:900;color:{{ $avgPct > 65 ? '#dc2626' : ($avgPct > 40 ? '#d97706' : '#16a34a') }};margin:0;">{{ $avgPct }}%</p>
        <p style="font-size:.75rem;color:#94a3b8;margin:4px 0 0;">Ansiedad promedio</p>
    </div>
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:20px;text-align:center;">
        <p style="font-size:1.75rem;font-weight:900;color:#0f172a;margin:0;">{{ $user->created_at->format('d/m/Y') }}</p>
        <p style="font-size:.75rem;color:#94a3b8;margin:4px 0 0;">Registro</p>
    </div>
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:20px;text-align:center;">
        <p style="font-size:1.75rem;font-weight:900;color:#0f172a;margin:0;">{{ $user->institution?->name ?? '—' }}</p>
        <p style="font-size:.75rem;color:#94a3b8;margin:4px 0 0;">Institución</p>
    </div>
</div>

{{-- Gráfica de actividad --}}
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:24px;margin-bottom:24px;">
    <h3 style="font-size:1rem;font-weight:700;color:#0f172a;margin:0 0 20px;">Actividad (30 días)</h3>
    @php $maxChart = $userChart->max() ?: 1; @endphp
    <div style="display:flex;align-items:flex-end;gap:4px;height:100px;">
        @foreach($userChart as $date => $count)
            <div style="flex:1;background:{{ $count > 0 ? 'linear-gradient(180deg,#6366f1,#4f46e5)' : '#f1f5f9' }};border-radius:3px 3px 0 0;height:{{ $count > 0 ? round($count/$maxChart*100) : 8 }}%;min-height:3px;"
                 title="{{ \Carbon\Carbon::parse($date)->format('d M') }}: {{ $count }}"></div>
        @endforeach
    </div>
    <div style="display:flex;justify-content:space-between;margin-top:8px;">
        <span style="font-size:.625rem;color:#94a3b8;">{{ $userChart->keys()->first() }}</span>
        <span style="font-size:.625rem;color:#94a3b8;">{{ $userChart->keys()->last() }}</span>
    </div>
</div>

{{-- Historial de interacciones --}}
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;overflow:hidden;">
    <div style="padding:20px 24px;border-bottom:1px solid #f1f5f9;">
        <h3 style="font-size:1rem;font-weight:700;color:#0f172a;margin:0;">Historial de interacciones</h3>
    </div>
    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="background:#f8fafc;border-bottom:1px solid #e2e8f0;">
                    <th style="text-align:left;padding:12px 16px;font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;">Fecha</th>
                    <th style="text-align:left;padding:12px 16px;font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;">Texto</th>
                    <th style="text-align:left;padding:12px 16px;font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;">Respuesta IA</th>
                    <th style="text-align:center;padding:12px 16px;font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;">Ansiedad</th>
                    <th style="text-align:center;padding:12px 16px;font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;">Audio</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $record)
                @php
                    $pct = $record->predicted_probability ? round($record->predicted_probability * 100) : null;
                    $levelColor = $pct !== null ? ($pct > 65 ? '#dc2626' : ($pct > 40 ? '#d97706' : '#16a34a')) : '#94a3b8';
                    $levelBg = $pct !== null ? ($pct > 65 ? '#fef2f2' : ($pct > 40 ? '#fffbeb' : '#f0fdf4')) : '#f8fafc';
                @endphp
                <tr style="border-bottom:1px solid #f8fafc;" onmouseover="this.style.background='#fafbff'" onmouseout="this.style.background='transparent'">
                    <td style="padding:12px 16px;font-size:.8125rem;color:#64748b;white-space:nowrap;">{{ $record->created_at->format('d/m/Y H:i') }}</td>
                    <td style="padding:12px 16px;font-size:.8125rem;color:#334155;max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $record->input_text ?: '(audio)' }}</td>
                    <td style="padding:12px 16px;font-size:.8125rem;color:#64748b;max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $record->generated_text ?: '—' }}</td>
                    <td style="padding:12px 16px;text-align:center;">
                        @if($pct !== null)
                            <span style="font-size:.75rem;font-weight:700;padding:3px 10px;border-radius:9999px;background:{{ $levelBg }};color:{{ $levelColor }};">{{ $pct }}%</span>
                        @else
                            <span style="font-size:.75rem;color:#cbd5e1;">—</span>
                        @endif
                    </td>
                    <td style="padding:12px 16px;text-align:center;">
                        @if($record->audio_filename)
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="#6366f1" style="width:16px;height:16px;display:inline;"><path d="M7 4a3 3 0 0 1 6 0v6a3 3 0 1 1-6 0V4Z"/><path d="M5.5 9.643a.75.75 0 0 0-1.5 0V10c0 3.06 2.29 5.585 5.25 5.954V17.5h-1.5a.75.75 0 0 0 0 1.5h4.5a.75.75 0 0 0 0-1.5h-1.5v-1.546A6.001 6.001 0 0 0 16 10v-.357a.75.75 0 0 0-1.5 0V10a4.5 4.5 0 0 1-9 0v-.357Z"/></svg>
                        @else
                            <span style="font-size:.75rem;color:#cbd5e1;">—</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="padding:40px;text-align:center;color:#94a3b8;font-size:.875rem;">Sin interacciones registradas.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($records->hasPages())
    <div style="padding:16px 24px;border-top:1px solid #f1f5f9;display:flex;align-items:center;justify-content:between;">
        {{ $records->links() }}
    </div>
    @endif
</div>

<div style="margin-top:16px;">
    <a href="{{ route('superadmin.users') }}" style="font-size:.875rem;font-weight:600;color:#64748b;display:inline-flex;align-items:center;gap:6px;"
       onmouseover="this.style.color='#4f46e5'" onmouseout="this.style.color='#64748b'">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width:16px;height:16px;"><path fill-rule="evenodd" d="M17 10a.75.75 0 0 1-.75.75H5.612l4.158 3.96a.75.75 0 1 1-1.04 1.08l-5.5-5.25a.75.75 0 0 1 0-1.08l5.5-5.25a.75.75 0 1 1 1.04 1.08L5.612 9.25H16.25A.75.75 0 0 1 17 10Z" clip-rule="evenodd"/></svg>
        Volver a usuarios
    </a>
</div>
@endsection
