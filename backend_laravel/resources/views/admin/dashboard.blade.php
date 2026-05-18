@extends('admin._layout')
@section('title', 'Dashboard')

@section('panel')
{{-- Stats --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:20px;margin-bottom:32px;">
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:24px;">
        <span style="font-size:.75rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;">Usuarios</span>
        <p style="font-size:2rem;font-weight:900;color:#0f172a;margin:8px 0 0;">{{ $totalUsers }}</p>
        <p style="font-size:.75rem;color:#64748b;margin:4px 0 0;">{{ $activeUsers }} activos (7d)</p>
    </div>
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:24px;">
        <span style="font-size:.75rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;">Interacciones</span>
        <p style="font-size:2rem;font-weight:900;color:#0f172a;margin:8px 0 0;">{{ number_format($totalRecords) }}</p>
        <p style="font-size:.75rem;color:#64748b;margin:4px 0 0;">Total de sesiones</p>
    </div>
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:24px;">
        @php $avgPct = $avgProbability ? round($avgProbability * 100) : 0; @endphp
        <span style="font-size:.75rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;">Ansiedad prom.</span>
        <p style="font-size:2rem;font-weight:900;color:{{ $avgPct > 65 ? '#dc2626' : ($avgPct > 40 ? '#d97706' : '#16a34a') }};margin:8px 0 0;">{{ $avgPct }}%</p>
        <div style="height:6px;border-radius:9999px;background:#f1f5f9;margin-top:8px;overflow:hidden;">
            <div style="height:100%;width:{{ $avgPct }}%;border-radius:9999px;background:{{ $avgPct > 65 ? '#ef4444' : ($avgPct > 40 ? '#f59e0b' : '#22c55e') }};"></div>
        </div>
    </div>
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:24px;">
        <span style="font-size:.75rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;">Distribución</span>
        <div style="display:flex;gap:8px;margin-top:12px;">
            @foreach(['low' => ['Bajo','#22c55e'], 'moderate' => ['Medio','#f59e0b'], 'high' => ['Alto','#ef4444']] as $key => [$label, $color])
                <div style="flex:1;text-align:center;">
                    <p style="font-size:1.25rem;font-weight:800;color:{{ $color }};margin:0;">{{ round($levels[$key] / $levelsTotal * 100) }}%</p>
                    <p style="font-size:.625rem;color:#94a3b8;margin:2px 0 0;">{{ $label }}</p>
                </div>
            @endforeach
        </div>
    </div>
</div>

{{-- Gráfica --}}
<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;margin-bottom:32px;">
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:24px;">
        <h3 style="font-size:1rem;font-weight:700;color:#0f172a;margin:0 0 20px;">Actividad (30 días)</h3>
        @php $maxA = $activityChart->max() ?: 1; @endphp
        <div style="display:flex;align-items:flex-end;gap:3px;height:120px;">
            @foreach($activityChart as $date => $count)
                <div style="flex:1;background:linear-gradient(180deg,#6366f1,#4f46e5);border-radius:3px 3px 0 0;height:{{ round($count/$maxA*100) }}%;min-height:2px;"
                     title="{{ \Carbon\Carbon::parse($date)->format('d M') }}: {{ $count }}"></div>
            @endforeach
        </div>
        <div style="display:flex;justify-content:space-between;margin-top:8px;">
            <span style="font-size:.625rem;color:#94a3b8;">{{ $activityChart->keys()->first() }}</span>
            <span style="font-size:.625rem;color:#94a3b8;">{{ $activityChart->keys()->last() }}</span>
        </div>
    </div>

    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:24px;">
        <h3 style="font-size:1rem;font-weight:700;color:#0f172a;margin:0 0 20px;">Accesos rápidos</h3>
        <div style="display:flex;flex-direction:column;gap:10px;">
            <a href="{{ route('admin.users') }}" style="display:flex;align-items:center;gap:10px;padding:12px 14px;border-radius:10px;border:1px solid #e2e8f0;transition:all .15s;"
               onmouseover="this.style.borderColor='#c7d2fe';this.style.background='#eef2ff'" onmouseout="this.style.borderColor='#e2e8f0';this.style.background='transparent'">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="#4f46e5" style="width:18px;height:18px;"><path d="M7 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6ZM14.5 9a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z"/></svg>
                <span style="font-size:.875rem;font-weight:600;color:#334155;">Gestionar usuarios</span>
            </a>
            <a href="{{ route('admin.sessions') }}" style="display:flex;align-items:center;gap:10px;padding:12px 14px;border-radius:10px;border:1px solid #e2e8f0;transition:all .15s;"
               onmouseover="this.style.borderColor='#c7d2fe';this.style.background='#eef2ff'" onmouseout="this.style.borderColor='#e2e8f0';this.style.background='transparent'">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="#4f46e5" style="width:18px;height:18px;"><path fill-rule="evenodd" d="M10 2c-2.236 0-4.43.18-6.57.524C1.993 2.755 1 3.925 1 5.261v5.478c0 1.336.993 2.506 2.43 2.737.527.085 1.058.156 1.592.213l.1.012 1.609 2.796A1 1 0 0 0 7.598 17l2.083-3.62c.15.005.3.008.451.012h-.001c2.236 0 4.43-.18 6.57-.524C18.007 12.637 19 11.467 19 10.131V5.261c0-1.336-.993-2.506-2.43-2.737A32.47 32.47 0 0 0 10 2Z" clip-rule="evenodd"/></svg>
                <span style="font-size:.875rem;font-weight:600;color:#334155;">Ver sesiones</span>
            </a>
            <a href="{{ route('admin.reports') }}" style="display:flex;align-items:center;gap:10px;padding:12px 14px;border-radius:10px;border:1px solid #e2e8f0;transition:all .15s;"
               onmouseover="this.style.borderColor='#c7d2fe';this.style.background='#eef2ff'" onmouseout="this.style.borderColor='#e2e8f0';this.style.background='transparent'">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="#4f46e5" style="width:18px;height:18px;"><path fill-rule="evenodd" d="M1 2.75A.75.75 0 0 1 1.75 2h16.5a.75.75 0 0 1 .75.75v14.5a.75.75 0 0 1-.75.75H1.75a.75.75 0 0 1-.75-.75V2.75Z" clip-rule="evenodd"/></svg>
                <span style="font-size:.875rem;font-weight:600;color:#334155;">Generar reportes</span>
            </a>
        </div>
    </div>
</div>

{{-- Tabla de usuarios --}}
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;overflow:hidden;">
    <div style="padding:20px 24px;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;">
        <h3 style="font-size:1rem;font-weight:700;color:#0f172a;margin:0;">Usuarios de la institución</h3>
        <a href="{{ route('admin.users') }}" style="font-size:.8125rem;font-weight:600;color:#4f46e5;">Ver todos</a>
    </div>
    <table style="width:100%;border-collapse:collapse;">
        <thead>
            <tr style="border-bottom:2px solid #f1f5f9;">
                <th style="text-align:left;padding:12px 16px;font-size:.75rem;font-weight:700;color:#94a3b8;text-transform:uppercase;">Usuario</th>
                <th style="text-align:center;padding:12px 16px;font-size:.75rem;font-weight:700;color:#94a3b8;text-transform:uppercase;">Sesiones</th>
                <th style="text-align:center;padding:12px 16px;font-size:.75rem;font-weight:700;color:#94a3b8;text-transform:uppercase;">Ansiedad</th>
                <th style="text-align:center;padding:12px 16px;font-size:.75rem;font-weight:700;color:#94a3b8;text-transform:uppercase;">Última actividad</th>
                <th style="text-align:center;padding:12px 16px;font-size:.75rem;font-weight:700;color:#94a3b8;text-transform:uppercase;">Acción</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users->take(10) as $user)
            @php
                $uAvg = $user->inference_records_avg_predicted_probability ? round($user->inference_records_avg_predicted_probability * 100) : null;
                $lastRecord = $user->inferenceRecords->first();
            @endphp
            <tr style="border-bottom:1px solid #f8fafc;">
                <td style="padding:12px 16px;">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div style="width:32px;height:32px;border-radius:9999px;background:linear-gradient(135deg,#38bdf8,#6366f1);display:flex;align-items:center;justify-content:center;color:#fff;font-size:.75rem;font-weight:700;">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                        <div>
                            <p style="font-size:.875rem;font-weight:600;color:#1e293b;margin:0;">{{ $user->name }}</p>
                            <p style="font-size:.6875rem;color:#94a3b8;margin:0;">{{ $user->email }}</p>
                        </div>
                    </div>
                </td>
                <td style="padding:12px 16px;text-align:center;font-size:.875rem;font-weight:600;color:#334155;">{{ $user->inference_records_count }}</td>
                <td style="padding:12px 16px;text-align:center;">
                    @if($uAvg !== null)
                        <span style="font-size:.75rem;font-weight:700;padding:3px 10px;border-radius:9999px;background:{{ $uAvg > 65 ? '#fef2f2' : ($uAvg > 40 ? '#fffbeb' : '#f0fdf4') }};color:{{ $uAvg > 65 ? '#dc2626' : ($uAvg > 40 ? '#d97706' : '#16a34a') }};">{{ $uAvg }}%</span>
                    @else
                        <span style="color:#cbd5e1;font-size:.75rem;">—</span>
                    @endif
                </td>
                <td style="padding:12px 16px;text-align:center;font-size:.8125rem;color:#64748b;">{{ $lastRecord?->created_at?->diffForHumans() ?? 'Nunca' }}</td>
                <td style="padding:12px 16px;text-align:center;">
                    <a href="{{ route('admin.user', $user) }}" style="font-size:.6875rem;font-weight:600;color:#4f46e5;padding:4px 10px;border-radius:8px;border:1px solid #c7d2fe;background:#eef2ff;">Seguimiento</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
