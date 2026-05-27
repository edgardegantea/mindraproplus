@extends('layouts.app')
@section('title', 'Mi plan')

@push('styles')
<style>
    .plan-page { max-width:56rem; margin:0 auto; padding:48px 1.5rem 80px; }
    .plan-hero { border-radius:20px; padding:32px; margin-bottom:28px; }
    .plan-hero.free { background:linear-gradient(135deg,#f8fafc,#f1f5f9); border:2px solid #e2e8f0; }
    .plan-hero.pro  { background:linear-gradient(135deg,#eef2ff,#ede9fe); border:2px solid #c7d2fe; }
    .plan-hero.plus { background:linear-gradient(135deg,#fdf4ff,#ede9fe); border:2px solid #ddd6fe; }
    .feat-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(220px,1fr)); gap:12px; margin-top:20px; }
    .feat-card { background:#fff; border-radius:12px; border:1.5px solid #e2e8f0; padding:14px 16px; display:flex; align-items:center; gap:10px; }
    .feat-card.on  { border-color:#bbf7d0; background:#f0fdf4; }
    .feat-card.off { opacity:.55; }
    .upgrade-grid { display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-top:24px; }
    @media(max-width:600px){ .upgrade-grid{ grid-template-columns:1fr; } }
    .upgrade-card { border-radius:16px; padding:24px; text-decoration:none; transition:transform .15s,box-shadow .15s; }
    .upgrade-card:hover { transform:translateY(-2px); box-shadow:0 8px 24px rgba(0,0,0,.1); }
</style>
@endpush

@section('content')
<div class="plan-page">

    {{-- ── Encabezado ────────────────────────────────────────────────────────── --}}
    <div style="margin-bottom:28px;">
        <h1 style="font-size:1.625rem;font-weight:800;color:#0f172a;margin:0 0 4px;">Mi plan</h1>
        <p style="color:#64748b;font-size:.9375rem;margin:0;">Estado de tu suscripción y funciones disponibles.</p>
    </div>

    {{-- ── Hero: estado del plan ───────────────────────────────────────────── --}}
    @php
        $slug       = $plan->slug ?? 'free';
        $isFree     = $slug === 'free';
        $isPro      = $slug === 'pro';
        $isPlus     = $slug === 'plus';
        $expiresAt  = $subscription?->expires_at;
        $daysLeft   = $expiresAt ? (int) now()->diffInDays($expiresAt, false) : null;
        $planColors = [
            'free' => ['#64748b','#e2e8f0'],
            'pro'  => ['#4f46e5','#c7d2fe'],
            'plus' => ['#7c3aed','#ddd6fe'],
        ];
        [$planColor, $planBorder] = $planColors[$slug] ?? $planColors['free'];
    @endphp

    <div class="plan-hero {{ $slug }}">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:16px;">
            <div>
                <div style="display:inline-flex;align-items:center;gap:8px;padding:5px 14px;border-radius:9999px;background:{{ $planColor }};color:#fff;font-size:.8125rem;font-weight:700;margin-bottom:10px;">
                    @if($isPlus) ✦ Plan Plus
                    @elseif($isPro) ⭐ Plan Pro
                    @else 🆓 Plan Free
                    @endif
                </div>
                <h2 style="font-size:1.375rem;font-weight:800;color:#0f172a;margin:0 0 6px;">
                    {{ $user->name }}
                </h2>
                <p style="font-size:.875rem;color:#64748b;margin:0;">{{ $user->email }}</p>
            </div>

            <div style="text-align:right;">
                @if($subscription && $expiresAt)
                    @if($daysLeft > 0)
                    <div style="font-size:.75rem;color:#64748b;margin-bottom:4px;">Vence el</div>
                    <div style="font-size:1.125rem;font-weight:800;color:#0f172a;">
                        {{ $expiresAt->format('d/m/Y') }}
                    </div>
                    <div style="font-size:.75rem;margin-top:2px;
                        color:{{ $daysLeft <= 7 ? '#dc2626' : ($daysLeft <= 30 ? '#d97706' : '#16a34a') }};font-weight:600;">
                        {{ $daysLeft <= 7 ? '⚠ ' : '' }}{{ $daysLeft }} días restantes
                    </div>
                    @else
                    <div style="padding:6px 14px;border-radius:8px;background:#fef2f2;border:1.5px solid #fecaca;color:#dc2626;font-size:.8125rem;font-weight:700;">
                        ⚠ Suscripción vencida
                    </div>
                    @endif
                @elseif(!$subscription || $isFree)
                    <div style="padding:6px 14px;border-radius:8px;background:#f1f5f9;border:1.5px solid #e2e8f0;color:#64748b;font-size:.8125rem;font-weight:600;">
                        Sin fecha de vencimiento
                    </div>
                @else
                    <div style="padding:6px 14px;border-radius:8px;background:#f0fdf4;border:1.5px solid #bbf7d0;color:#16a34a;font-size:.8125rem;font-weight:700;">
                        ✓ Activo
                    </div>
                @endif
            </div>
        </div>

        {{-- Detalles de suscripción --}}
        @if($subscription)
        <div style="margin-top:16px;padding-top:16px;border-top:1.5px solid {{ $planBorder }};display:flex;gap:24px;flex-wrap:wrap;">
            <div>
                <p style="font-size:.6875rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;margin:0 0 2px;">Iniciada</p>
                <p style="font-size:.875rem;font-weight:600;color:#1e293b;margin:0;">{{ $subscription->started_at?->format('d/m/Y') ?? '—' }}</p>
            </div>
            <div>
                <p style="font-size:.6875rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;margin:0 0 2px;">Proveedor</p>
                <p style="font-size:.875rem;font-weight:600;color:#1e293b;margin:0;">{{ ucfirst($subscription->provider ?? 'manual') }}</p>
            </div>
            @if($subscription->external_subscription_id)
            <div>
                <p style="font-size:.6875rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;margin:0 0 2px;">Ref. de pago</p>
                <p style="font-size:.875rem;font-weight:600;color:#1e293b;margin:0;font-family:monospace;">{{ Str::limit($subscription->external_subscription_id, 18) }}</p>
            </div>
            @endif
        </div>
        @endif
    </div>

    {{-- ── Features del plan activo ─────────────────────────────────────────── --}}
    <div style="background:#fff;border-radius:16px;border:1px solid #e8edf5;padding:24px;margin-bottom:28px;">
        <h3 style="font-size:.875rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#64748b;margin:0 0 16px;">Funciones de tu plan</h3>
        @php
            $featureLabels = [
                'texto'           => ['💬', 'Chat de texto'],
                'audio'           => ['🎤', 'Chat de audio / voz'],
                'emociones'       => ['📊', 'Análisis de ansiedad y emociones'],
                'historial'       => ['🕐', 'Historial de sesiones'],
                'imagen'          => ['📷', 'Análisis facial en tiempo real'],
                'estadisticas'    => ['📈', 'Estadísticas avanzadas'],
                'crisis_alerts'   => ['⚠️', 'Alertas automáticas de crisis'],
                'reporte_clinico' => ['📋', 'Reporte clínico (30 días)'],
                'multimodal'      => ['✦', 'Análisis multimodal combinado'],
            ];
        @endphp
        <div class="feat-grid">
            @foreach($featureLabels as $key => [$icon, $label])
            <div class="feat-card {{ !empty($features[$key]) ? 'on' : 'off' }}">
                <span style="font-size:1.125rem;flex-shrink:0;">{{ $icon }}</span>
                <div>
                    <p style="font-size:.8125rem;font-weight:600;color:{{ !empty($features[$key]) ? '#166534' : '#94a3b8' }};margin:0;">
                        {{ $label }}
                    </p>
                    <p style="font-size:.6875rem;color:{{ !empty($features[$key]) ? '#16a34a' : '#cbd5e1' }};margin:0;font-weight:600;">
                        {{ !empty($features[$key]) ? 'Disponible' : 'No incluido' }}
                    </p>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- ── Acciones / Upgrade ──────────────────────────────────────────────── --}}
    @if($isFree)
    <div style="background:#fff;border-radius:16px;border:1px solid #e8edf5;padding:24px;">
        <h3 style="font-size:.875rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#64748b;margin:0 0 16px;">Mejora tu experiencia</h3>
        <div class="upgrade-grid">
            <a href="{{ route('plans.pro') }}" class="upgrade-card" style="background:linear-gradient(135deg,#eef2ff,#ede9fe);border:2px solid #c7d2fe;">
                <div style="font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#4f46e5;margin-bottom:8px;">⭐ Plan Pro</div>
                <div style="font-size:1.5rem;font-weight:900;color:#3730a3;margin-bottom:4px;">$149 <span style="font-size:.875rem;font-weight:500;color:#6366f1;">MXN/mes</span></div>
                <p style="font-size:.8125rem;color:#6366f1;margin:0 0 14px;line-height:1.5;">Análisis de ansiedad, emociones y las últimas 20 sesiones.</p>
                <div style="font-size:.8125rem;font-weight:700;color:#4f46e5;">Ver plan Pro →</div>
            </a>
            <a href="{{ route('plans.plus') }}" class="upgrade-card" style="background:linear-gradient(135deg,#fdf4ff,#ede9fe);border:2px solid #ddd6fe;">
                <div style="font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#7c3aed;margin-bottom:8px;">✦ Plan Plus</div>
                <div style="font-size:1.5rem;font-weight:900;color:#4c1d95;margin-bottom:4px;">$199 <span style="font-size:.875rem;font-weight:500;color:#9333ea;">MXN/mes</span></div>
                <p style="font-size:.8125rem;color:#9333ea;margin:0 0 14px;line-height:1.5;">Todo lo de Pro + análisis facial, estadísticas, reporte clínico y alertas de crisis.</p>
                <div style="font-size:.8125rem;font-weight:700;color:#7c3aed;">Ver plan Plus →</div>
            </a>
        </div>
    </div>

    @elseif($isPro)
    <div style="background:#fff;border-radius:16px;border:1px solid #e8edf5;padding:24px;">
        <h3 style="font-size:.875rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#64748b;margin:0 0 16px;">Desbloquea más con Plus</h3>
        <a href="{{ route('plans.plus') }}" class="upgrade-card" style="display:block;background:linear-gradient(135deg,#fdf4ff,#ede9fe);border:2px solid #ddd6fe;">
            <div style="font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#7c3aed;margin-bottom:8px;">✦ Plan Plus — $199 MXN/mes</div>
            <p style="font-size:.875rem;color:#9333ea;margin:0 0 10px;line-height:1.5;">
                Agrega análisis facial en tiempo real, historial ilimitado, estadísticas avanzadas, reporte clínico PDF y alertas automáticas de crisis.
            </p>
            <div style="font-size:.875rem;font-weight:700;color:#7c3aed;">Actualizar a Plus →</div>
        </a>
        @if($daysLeft !== null && $daysLeft <= 14)
        <div style="margin-top:16px;padding:14px 16px;border-radius:12px;background:#fef2f2;border:1.5px solid #fecaca;">
            <p style="font-size:.875rem;font-weight:700;color:#dc2626;margin:0 0 4px;">⚠ Tu suscripción vence pronto</p>
            <p style="font-size:.8125rem;color:#991b1b;margin:0;">Renueva tu plan Pro antes del {{ $expiresAt->format('d/m/Y') }} para no perder el acceso.</p>
            <a href="{{ route('plans.pro') }}" style="display:inline-block;margin-top:10px;padding:7px 16px;border-radius:9px;background:#dc2626;color:#fff;font-size:.8125rem;font-weight:700;text-decoration:none;">Renovar ahora →</a>
        </div>
        @endif
    </div>

    @else
    {{-- Plus: soporte prioritario --}}
    <div style="background:linear-gradient(135deg,#fdf4ff,#f5f3ff);border-radius:16px;border:2px solid #ddd6fe;padding:24px;">
        <p style="font-size:.875rem;font-weight:700;color:#7c3aed;margin:0 0 6px;">💜 Soporte prioritario Plus</p>
        <p style="font-size:.875rem;color:#9333ea;margin:0 0 12px;line-height:1.5;">
            Como usuario Plus tienes soporte dedicado. Para cualquier consulta, reporte de problema o solicitud especial escríbenos directamente.
        </p>
        <a href="mailto:{{ config('mail.from.address') }}" style="display:inline-block;padding:8px 18px;border-radius:9px;background:#7c3aed;color:#fff;font-size:.875rem;font-weight:700;text-decoration:none;">
            Contactar soporte →
        </a>
        @if($daysLeft !== null && $daysLeft <= 30)
        <div style="margin-top:16px;padding:14px 16px;border-radius:12px;background:#fef2f2;border:1.5px solid #fecaca;">
            <p style="font-size:.875rem;font-weight:700;color:#dc2626;margin:0 0 4px;">⚠ Tu suscripción vence en {{ $daysLeft }} días</p>
            <p style="font-size:.8125rem;color:#991b1b;margin:0;">Contacta a soporte para renovar antes del {{ $expiresAt->format('d/m/Y') }}.</p>
        </div>
        @endif
    </div>
    @endif

</div>
@endsection
