@extends('superadmin._layout')
@section('title', 'Dashboard')

@section('panel')
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:20px;margin-bottom:32px;">
    {{-- Total usuarios --}}
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:24px;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
            <span style="font-size:.75rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;">Usuarios</span>
            <div style="width:36px;height:36px;border-radius:10px;background:#eef2ff;display:flex;align-items:center;justify-content:center;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="#4f46e5" style="width:18px;height:18px;"><path d="M7 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6ZM14.5 9a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5ZM1.615 16.428a1.224 1.224 0 0 1-.569-1.175 6.002 6.002 0 0 1 11.908 0c.058.467-.172.92-.57 1.174A9.953 9.953 0 0 1 7 18a9.953 9.953 0 0 1-5.385-1.572ZM14.5 16h-.106c.07-.297.088-.611.048-.933a7.47 7.47 0 0 0-1.588-3.755 4.502 4.502 0 0 1 5.874 2.636.818.818 0 0 1-.36.98A7.465 7.465 0 0 1 14.5 16Z"/></svg>
            </div>
        </div>
        <p style="font-size:2rem;font-weight:900;color:#0f172a;margin:0;">{{ number_format($totalUsers) }}</p>
        <p style="font-size:.75rem;color:#64748b;margin:4px 0 0;">{{ $activeUsers }} activos (7d)</p>
    </div>

    {{-- Interacciones --}}
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:24px;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
            <span style="font-size:.75rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;">Interacciones</span>
            <div style="width:36px;height:36px;border-radius:10px;background:#f0fdf4;display:flex;align-items:center;justify-content:center;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="#16a34a" style="width:18px;height:18px;"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm.75-11.25a.75.75 0 0 0-1.5 0v2.5h-2.5a.75.75 0 0 0 0 1.5h2.5v2.5a.75.75 0 0 0 1.5 0v-2.5h2.5a.75.75 0 0 0 0-1.5h-2.5v-2.5Z" clip-rule="evenodd"/></svg>
            </div>
        </div>
        <p style="font-size:2rem;font-weight:900;color:#0f172a;margin:0;">{{ number_format($totalRecords) }}</p>
        <p style="font-size:.75rem;color:#64748b;margin:4px 0 0;">Total de sesiones de IA</p>
    </div>

    {{-- Instituciones --}}
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:24px;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
            <span style="font-size:.75rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;">Instituciones</span>
            <div style="width:36px;height:36px;border-radius:10px;background:#f5f3ff;display:flex;align-items:center;justify-content:center;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="#7c3aed" style="width:18px;height:18px;"><path fill-rule="evenodd" d="M1 2.75A.75.75 0 0 1 1.75 2h10.5a.75.75 0 0 1 0 1.5H2.562l3.616 3.616a3.75 3.75 0 0 1 0 5.304l2.508 2.508a.75.75 0 0 1-1.06 1.06l-2.508-2.507a3.75 3.75 0 0 1-5.304 0L1.75 9.865V12.25a.75.75 0 0 1-1.5 0V2.75Z" clip-rule="evenodd"/></svg>
            </div>
        </div>
        <p style="font-size:2rem;font-weight:900;color:#0f172a;margin:0;">{{ $totalInstitutions }}</p>
        <p style="font-size:.75rem;color:#64748b;margin:4px 0 0;">Plan Full activo</p>
    </div>

    {{-- Ansiedad promedio --}}
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:24px;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
            <span style="font-size:.75rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;">Ansiedad prom.</span>
            <div style="width:36px;height:36px;border-radius:10px;background:#fffbeb;display:flex;align-items:center;justify-content:center;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="#d97706" style="width:18px;height:18px;"><path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-8-5a.75.75 0 0 1 .75.75v4.5a.75.75 0 0 1-1.5 0v-4.5A.75.75 0 0 1 10 5Zm0 10a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd"/></svg>
            </div>
        </div>
        @php $avgPct = $avgProbability ? round($avgProbability * 100) : 0; @endphp
        <p style="font-size:2rem;font-weight:900;color:#0f172a;margin:0;">{{ $avgPct }}%</p>
        <div style="height:6px;border-radius:9999px;background:#f1f5f9;margin-top:8px;overflow:hidden;">
            <div style="height:100%;width:{{ $avgPct }}%;border-radius:9999px;background:{{ $avgPct > 65 ? '#ef4444' : ($avgPct > 40 ? '#f59e0b' : '#22c55e') }};"></div>
        </div>
    </div>
</div>

{{-- Distribución de planes --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:32px;">
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:24px;">
        <h3 style="font-size:1rem;font-weight:700;color:#0f172a;margin:0 0 20px;">Distribución por plan</h3>
        @php
            $planColors = ['free' => ['#e2e8f0','#64748b'], 'pro' => ['#c7d2fe','#4f46e5'], 'plus' => ['#ddd6fe','#7c3aed']];
            $planLabels = ['free' => 'Free', 'pro' => 'Pro', 'plus' => 'Plus'];
            $planTotal = $planDistribution->sum() ?: 1;
        @endphp
        <div style="display:flex;flex-direction:column;gap:14px;">
            @foreach(['free','pro','plus'] as $slug)
                @php
                    $count = $planDistribution->get($slug, 0);
                    $pct = round($count / $planTotal * 100);
                    $colors = $planColors[$slug];
                @endphp
                <div>
                    <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
                        <span style="font-size:.875rem;font-weight:600;color:#334155;">{{ $planLabels[$slug] }}</span>
                        <span style="font-size:.875rem;font-weight:700;color:{{ $colors[1] }};">{{ $count }} ({{ $pct }}%)</span>
                    </div>
                    <div style="height:8px;border-radius:9999px;background:#f1f5f9;overflow:hidden;">
                        <div style="height:100%;width:{{ $pct }}%;border-radius:9999px;background:{{ $colors[1] }};transition:width .3s;"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Instituciones --}}
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:24px;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
            <h3 style="font-size:1rem;font-weight:700;color:#0f172a;margin:0;">Instituciones</h3>
            <a href="{{ route('superadmin.institutions') }}" style="font-size:.8125rem;font-weight:600;color:#4f46e5;text-decoration:none;">Ver todas</a>
        </div>
        @forelse($institutions as $inst)
            <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 0;{{ !$loop->last ? 'border-bottom:1px solid #f1f5f9;' : '' }}">
                <div>
                    <p style="font-size:.875rem;font-weight:600;color:#1e293b;margin:0;">{{ $inst->name }}</p>
                    <p style="font-size:.75rem;color:#94a3b8;margin:2px 0 0;">{{ $inst->contact_email }}</p>
                </div>
                <span style="font-size:.75rem;font-weight:700;color:#4f46e5;background:#eef2ff;padding:4px 10px;border-radius:9999px;">{{ $inst->users_count }} usuarios</span>
            </div>
        @empty
            <p style="font-size:.875rem;color:#94a3b8;text-align:center;padding:24px 0;">Sin instituciones registradas</p>
        @endforelse
    </div>
</div>

{{-- Gráficas --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:32px;">
    {{-- Actividad 30 días --}}
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:24px;">
        <h3 style="font-size:1rem;font-weight:700;color:#0f172a;margin:0 0 20px;">Actividad (30 días)</h3>
        @php $maxActivity = $activityChart->max() ?: 1; @endphp
        <div style="display:flex;align-items:flex-end;gap:3px;height:120px;">
            @foreach($activityChart as $date => $count)
                <div style="flex:1;background:linear-gradient(180deg,#6366f1,#4f46e5);border-radius:3px 3px 0 0;height:{{ round($count/$maxActivity*100) }}%;min-height:2px;transition:height .3s;"
                     title="{{ \Carbon\Carbon::parse($date)->format('d M') }}: {{ $count }}"></div>
            @endforeach
        </div>
        <div style="display:flex;justify-content:space-between;margin-top:8px;">
            <span style="font-size:.625rem;color:#94a3b8;">{{ $activityChart->keys()->first() }}</span>
            <span style="font-size:.625rem;color:#94a3b8;">{{ $activityChart->keys()->last() }}</span>
        </div>
    </div>

    {{-- Registros 30 días --}}
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:24px;">
        <h3 style="font-size:1rem;font-weight:700;color:#0f172a;margin:0 0 20px;">Nuevos registros (30 días)</h3>
        @php $maxReg = $registrationChart->max() ?: 1; @endphp
        <div style="display:flex;align-items:flex-end;gap:3px;height:120px;">
            @foreach($registrationChart as $date => $count)
                <div style="flex:1;background:linear-gradient(180deg,#22c55e,#16a34a);border-radius:3px 3px 0 0;height:{{ round($count/$maxReg*100) }}%;min-height:2px;transition:height .3s;"
                     title="{{ \Carbon\Carbon::parse($date)->format('d M') }}: {{ $count }}"></div>
            @endforeach
        </div>
        <div style="display:flex;justify-content:space-between;margin-top:8px;">
            <span style="font-size:.625rem;color:#94a3b8;">{{ $registrationChart->keys()->first() }}</span>
            <span style="font-size:.625rem;color:#94a3b8;">{{ $registrationChart->keys()->last() }}</span>
        </div>
    </div>
</div>

{{-- Últimos usuarios --}}
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:24px;">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
        <h3 style="font-size:1rem;font-weight:700;color:#0f172a;margin:0;">Últimos usuarios registrados</h3>
        <a href="{{ route('superadmin.users') }}" style="font-size:.8125rem;font-weight:600;color:#4f46e5;text-decoration:none;">Ver todos</a>
    </div>
    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="border-bottom:2px solid #f1f5f9;">
                    <th style="text-align:left;padding:10px 12px;font-size:.75rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;">Usuario</th>
                    <th style="text-align:left;padding:10px 12px;font-size:.75rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;">Rol</th>
                    <th style="text-align:center;padding:10px 12px;font-size:.75rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;">Interacciones</th>
                    <th style="text-align:left;padding:10px 12px;font-size:.75rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;">Registro</th>
                    <th style="text-align:center;padding:10px 12px;font-size:.75rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;">Acción</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recentUsers as $user)
                <tr style="border-bottom:1px solid #f1f5f9;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                    <td style="padding:12px;">
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div style="width:32px;height:32px;border-radius:9999px;background:linear-gradient(135deg,#38bdf8,#6366f1,#9333ea);display:flex;align-items:center;justify-content:center;color:#fff;font-size:.75rem;font-weight:700;flex-shrink:0;">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            <div>
                                <p style="font-size:.875rem;font-weight:600;color:#1e293b;margin:0;">{{ $user->name }}</p>
                                <p style="font-size:.75rem;color:#94a3b8;margin:0;">{{ $user->email }}</p>
                            </div>
                        </div>
                    </td>
                    <td style="padding:12px;">
                        @php
                            $roleBg = match($user->role) {
                                'superadmin' => 'background:#fef2f2;color:#dc2626;border-color:#fecaca;',
                                'admin' => 'background:#f5f3ff;color:#7c3aed;border-color:#ddd6fe;',
                                'psychologist' => 'background:#f0fdf4;color:#16a34a;border-color:#bbf7d0;',
                                default => 'background:#f8fafc;color:#64748b;border-color:#e2e8f0;',
                            };
                        @endphp
                        <span style="font-size:.6875rem;font-weight:700;padding:3px 8px;border-radius:6px;border:1px solid;{{ $roleBg }}">{{ $user->role }}</span>
                    </td>
                    <td style="padding:12px;text-align:center;font-size:.875rem;font-weight:600;color:#334155;">{{ $user->inference_records_count }}</td>
                    <td style="padding:12px;font-size:.8125rem;color:#64748b;">{{ $user->created_at->format('d/m/Y') }}</td>
                    <td style="padding:12px;text-align:center;">
                        <a href="{{ route('superadmin.users.detail', $user) }}" style="font-size:.75rem;font-weight:600;color:#4f46e5;text-decoration:none;padding:4px 10px;border-radius:8px;border:1px solid #c7d2fe;background:#eef2ff;"
                           onmouseover="this.style.background='#4f46e5';this.style.color='#fff'" onmouseout="this.style.background='#eef2ff';this.style.color='#4f46e5'">Ver</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
