@extends('admin._layout')
@section('title', 'Reportes')

@section('panel')
{{-- Selector de período --}}
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:20px 24px;margin-bottom:24px;display:flex;align-items:center;gap:16px;">
    <span style="font-size:.875rem;font-weight:600;color:#334155;">Período:</span>
    <div style="display:flex;gap:6px;">
        @foreach(['7' => '7 días', '14' => '14 días', '30' => '30 días', '90' => '90 días'] as $val => $label)
            <a href="{{ route('admin.reports', ['period' => $val]) }}"
               style="padding:8px 14px;border-radius:8px;font-size:.8125rem;font-weight:600;transition:all .15s;
               {{ $period == $val ? 'background:linear-gradient(135deg,#38bdf8,#6366f1,#9333ea);color:#fff;' : 'background:#f8fafc;color:#64748b;border:1px solid #e2e8f0;' }}">{{ $label }}</a>
        @endforeach
    </div>
</div>

{{-- Métricas del período --}}
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:20px;margin-bottom:32px;">
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:28px;text-align:center;">
        <p style="font-size:2.5rem;font-weight:900;color:#0f172a;margin:0;">{{ number_format($totalSessions) }}</p>
        <p style="font-size:.875rem;color:#64748b;margin:8px 0 0;">Sesiones en el período</p>
    </div>
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:28px;text-align:center;">
        <p style="font-size:2.5rem;font-weight:900;color:#0f172a;margin:0;">{{ $activeUsersInPeriod }}</p>
        <p style="font-size:.875rem;color:#64748b;margin:8px 0 0;">Usuarios activos</p>
    </div>
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:28px;text-align:center;">
        @php $avgPct = $avgAnxiety ? round($avgAnxiety * 100) : 0; @endphp
        <p style="font-size:2.5rem;font-weight:900;color:{{ $avgPct > 65 ? '#dc2626' : ($avgPct > 40 ? '#d97706' : '#16a34a') }};margin:0;">{{ $avgPct }}%</p>
        <p style="font-size:.875rem;color:#64748b;margin:8px 0 0;">Ansiedad promedio</p>
    </div>
</div>

{{-- Distribución de niveles --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:32px;">
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:28px;">
        <h3 style="font-size:1rem;font-weight:700;color:#0f172a;margin:0 0 24px;">Distribución por nivel de ansiedad</h3>
        @php
            $distTotal = ($levelDist->sum() ?: 1);
            $distData = [
                'low' => ['Bajo (0-40%)', '#22c55e', '#f0fdf4', $levelDist->get('low', 0)],
                'moderate' => ['Moderado (41-65%)', '#f59e0b', '#fffbeb', $levelDist->get('moderate', 0)],
                'high' => ['Alto (66-100%)', '#ef4444', '#fef2f2', $levelDist->get('high', 0)],
            ];
        @endphp
        <div style="display:flex;flex-direction:column;gap:16px;">
            @foreach($distData as [$label, $color, $bg, $count])
                @php $pct = round($count / $distTotal * 100); @endphp
                <div>
                    <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
                        <span style="font-size:.875rem;font-weight:600;color:#334155;">{{ $label }}</span>
                        <span style="font-size:.875rem;font-weight:700;color:{{ $color }};">{{ $count }} ({{ $pct }}%)</span>
                    </div>
                    <div style="height:10px;border-radius:9999px;background:#f1f5f9;overflow:hidden;">
                        <div style="height:100%;width:{{ $pct }}%;border-radius:9999px;background:{{ $color }};"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Top usuarios --}}
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:28px;">
        <h3 style="font-size:1rem;font-weight:700;color:#0f172a;margin:0 0 20px;">Usuarios más activos</h3>
        <div style="display:flex;flex-direction:column;gap:10px;">
            @forelse($topUsers as $i => $user)
                @php $uAvg = $user->inference_records_avg_predicted_probability ? round($user->inference_records_avg_predicted_probability * 100) : null; @endphp
                <div style="display:flex;align-items:center;gap:10px;padding:8px 0;{{ !$loop->last ? 'border-bottom:1px solid #f1f5f9;' : '' }}">
                    <span style="width:20px;font-size:.75rem;font-weight:700;color:#94a3b8;">{{ $i + 1 }}</span>
                    <div style="flex:1;min-width:0;">
                        <a href="{{ route('admin.user', $user) }}" style="font-size:.8125rem;font-weight:600;color:#1e293b;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;display:block;">{{ $user->name }}</a>
                    </div>
                    <span style="font-size:.75rem;font-weight:600;color:#4f46e5;">{{ $user->inference_records_count }} sesiones</span>
                    @if($uAvg !== null)
                        <span style="font-size:.6875rem;font-weight:700;padding:2px 8px;border-radius:9999px;background:{{ $uAvg > 65 ? '#fef2f2' : ($uAvg > 40 ? '#fffbeb' : '#f0fdf4') }};color:{{ $uAvg > 65 ? '#dc2626' : ($uAvg > 40 ? '#d97706' : '#16a34a') }};">{{ $uAvg }}%</span>
                    @endif
                </div>
            @empty
                <p style="font-size:.875rem;color:#94a3b8;text-align:center;">Sin actividad en el período.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
