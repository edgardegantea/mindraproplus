@extends('layouts.app')
@section('title', 'Comparar planes — Mindra')

@push('styles')
<style>
.compare-page { max-width:72rem; margin:0 auto; padding:56px 1.5rem 80px; }
.compare-table { width:100%; border-collapse:separate; border-spacing:0; border-radius:20px; overflow:hidden; box-shadow:0 4px 32px rgba(0,0,0,.07); }
.compare-table th, .compare-table td { padding:14px 20px; text-align:center; font-size:.9375rem; }
.compare-table thead th { background:var(--bg-card,#fff); font-weight:800; font-size:1rem; border-bottom:2px solid var(--border,#e8edf5); }
.compare-table tbody tr:nth-child(odd) td { background:var(--bg,#f8fafc); }
.compare-table tbody tr:nth-child(even) td { background:var(--bg-card,#fff); }
.compare-table tbody td:first-child { text-align:left; font-weight:600; color:var(--text,#1e293b); border-right:1px solid var(--border,#e8edf5); }
.compare-table tbody tr:hover td { background:var(--hover,#f1f5f9); }
.check { color:#16a34a; font-size:1.1rem; font-weight:700; }
.cross { color:#cbd5e1; font-size:1.1rem; }
.plan-col-free  { border-top:4px solid #64748b; }
.plan-col-pro   { border-top:4px solid #4f46e5; }
.plan-col-plus  { border-top:4px solid #7c3aed; }
.plan-name-free  { color:#64748b; }
.plan-name-pro   { color:#4f46e5; }
.plan-name-plus  { color:#7c3aed; }
.price-tag { font-size:1.5rem; font-weight:900; }
.price-period { font-size:.8rem; font-weight:500; color:#94a3b8; }
.btn-plan { display:inline-block; padding:10px 22px; border-radius:10px; font-size:.875rem; font-weight:700; text-decoration:none; transition:opacity .15s; border:none; cursor:pointer; font-family:inherit; }
.btn-plan:hover { opacity:.85; }
.category-row td { background:linear-gradient(90deg,#f8fafc,var(--bg,#f8fafc)) !important; font-size:.6875rem; font-weight:800; text-transform:uppercase; letter-spacing:.08em; color:#94a3b8; padding:10px 20px; }
.badge { display:inline-block; padding:3px 10px; border-radius:999px; font-size:.6875rem; font-weight:700; }
.faq-item { border:1px solid var(--border,#e8edf5); border-radius:14px; overflow:hidden; margin-bottom:8px; }
.faq-q { width:100%; padding:16px 20px; background:var(--bg-card,#fff); border:none; text-align:left; font-weight:700; font-size:.9375rem; cursor:pointer; font-family:inherit; color:var(--text,#1e293b); display:flex; justify-content:space-between; align-items:center; }
.faq-a { display:none; padding:0 20px 16px; font-size:.9rem; color:var(--text-muted,#64748b); line-height:1.7; }
.faq-item.open .faq-a { display:block; }
.faq-item.open .faq-icon { transform:rotate(180deg); }
.faq-icon { transition:transform .2s; flex-shrink:0; }
</style>
@endpush

@section('content')
<div class="compare-page">

    {{-- ── Header ─────────────────────────────────────────────────────────── --}}
    <div style="text-align:center;margin-bottom:48px;">
        <span style="display:inline-block;padding:4px 14px;border-radius:999px;background:#f5f3ff;color:#7c3aed;font-size:.75rem;font-weight:700;margin-bottom:12px;">✦ Planes Mindra</span>
        <h1 style="font-size:2.25rem;font-weight:900;color:var(--text,#0f172a);margin:0 0 12px;">Elige el plan ideal para ti</h1>
        <p style="font-size:1rem;color:var(--text-muted,#64748b);max-width:44rem;margin:0 auto;line-height:1.65;">
            Mindra es la única plataforma que combina análisis de texto, voz y expresión facial en tiempo real para el monitoreo del bienestar emocional.
        </p>
    </div>

    {{-- ── Tarjetas resumen ─────────────────────────────────────────────────── --}}
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;margin-bottom:48px;">
        @foreach([
            ['slug'=>'free','name'=>'Free','color'=>'#64748b','border'=>'#e2e8f0','bg'=>'#f8fafc','price'=>'Gratis','period'=>'siempre','badge'=>'🆓','cta'=>'Comenzar gratis','ctaColor'=>'#64748b','desc'=>'Exploración y chat de texto básico.'],
            ['slug'=>'pro', 'name'=>'Pro', 'color'=>'#4f46e5','border'=>'#c7d2fe','bg'=>'#eef2ff','price'=>'$149','period'=>'MXN / mes','badge'=>'⭐','cta'=>'Suscribirse','ctaColor'=>'#4f46e5','desc'=>'Audio, emociones e historial de sesiones.'],
            ['slug'=>'plus','name'=>'Plus','color'=>'#7c3aed','border'=>'#ddd6fe','bg'=>'#f5f3ff','price'=>'A medida','period'=>'uso institucional','badge'=>'✦','cta'=>'Solicitar acceso','ctaColor'=>'#7c3aed','desc'=>'Análisis facial, clínico y multimodal.'],
        ] as $p)
        <div style="background:{{ $p['bg'] }};border:2px solid {{ $p['border'] }};border-radius:20px;padding:24px;position:relative;">
            <div style="font-size:.75rem;font-weight:700;color:{{ $p['color'] }};margin-bottom:8px;">{{ $p['badge'] }} Plan {{ $p['name'] }}</div>
            <div style="font-size:1.75rem;font-weight:900;color:var(--text,#0f172a);">{{ $p['price'] }}</div>
            <div style="font-size:.8125rem;color:#94a3b8;margin-bottom:10px;">{{ $p['period'] }}</div>
            <p style="font-size:.875rem;color:var(--text-muted,#64748b);margin:0 0 16px;line-height:1.5;">{{ $p['desc'] }}</p>
            <a href="{{ route('plans.' . $p['slug']) }}"
               style="display:block;text-align:center;padding:10px;border-radius:10px;background:{{ $p['ctaColor'] }};color:#fff;font-size:.875rem;font-weight:700;text-decoration:none;">
                {{ $p['cta'] }}
            </a>
        </div>
        @endforeach
    </div>

    {{-- ── Tabla comparativa ────────────────────────────────────────────────── --}}
    <h2 style="font-size:1.25rem;font-weight:800;margin:0 0 20px;">Comparativa detallada</h2>
    <div style="overflow-x:auto;margin-bottom:48px;">
    <table class="compare-table">
        <thead>
            <tr>
                <th style="text-align:left;min-width:220px;">Función</th>
                <th class="plan-col-free" style="min-width:140px;">
                    <div class="plan-name-free" style="font-size:1.1rem;">🆓 Free</div>
                    <div style="font-size:.8rem;color:#94a3b8;font-weight:400;">Gratis</div>
                </th>
                <th class="plan-col-pro" style="min-width:140px;">
                    <div class="plan-name-pro" style="font-size:1.1rem;">⭐ Pro</div>
                    <div style="font-size:.8rem;color:#94a3b8;font-weight:400;">$149 MXN/mes</div>
                </th>
                <th class="plan-col-plus" style="min-width:140px;">
                    <div class="plan-name-plus" style="font-size:1.1rem;">✦ Plus</div>
                    <div style="font-size:.8rem;color:#94a3b8;font-weight:400;">A medida</div>
                </th>
            </tr>
        </thead>
        <tbody>
            @php
            $rows = [
                ['cat' => 'Chat e interacción'],
                ['label'=>'Chat de texto con IA',               'free'=>true,  'pro'=>true,  'plus'=>true ],
                ['label'=>'Chat por audio (voz)',                'free'=>true,  'pro'=>true,  'plus'=>true ],
                ['label'=>'Análisis de ansiedad en tiempo real', 'free'=>'Básico','pro'=>true,'plus'=>true],
                ['label'=>'Etiquetas de emoción',               'free'=>false, 'pro'=>true,  'plus'=>true ],
                ['label'=>'Análisis facial de emociones',        'free'=>false, 'pro'=>false, 'plus'=>true ],
                ['label'=>'Análisis multimodal combinado',       'free'=>false, 'pro'=>false, 'plus'=>true ],

                ['cat' => 'Historial y datos'],
                ['label'=>'Historial de sesiones',              'free'=>false, 'pro'=>'Últimas 20','plus'=>'Ilimitado'],
                ['label'=>'Calendario emocional (60 días)',     'free'=>false, 'pro'=>true,  'plus'=>true ],
                ['label'=>'Estadísticas y tendencias semanales','free'=>false, 'pro'=>false, 'plus'=>true ],
                ['label'=>'Exportar historial CSV',             'free'=>false, 'pro'=>true,  'plus'=>true ],
                ['label'=>'Diario emocional personal',          'free'=>true,  'pro'=>true,  'plus'=>true ],

                ['cat' => 'Informes y alertas'],
                ['label'=>'Reporte clínico PDF (30 días)',      'free'=>false, 'pro'=>false, 'plus'=>true ],
                ['label'=>'Reporte semanal de tendencias',      'free'=>false, 'pro'=>true,  'plus'=>true ],
                ['label'=>'Alertas automáticas de crisis',      'free'=>false, 'pro'=>false, 'plus'=>true ],

                ['cat' => 'Técnicas y recursos'],
                ['label'=>'Técnicas de bienestar (app)',        'free'=>true,  'pro'=>true,  'plus'=>true ],
                ['label'=>'Ejercicios de respiración guiados',  'free'=>true,  'pro'=>true,  'plus'=>true ],
                ['label'=>'Sesiones guiadas estructuradas',     'free'=>false, 'pro'=>true,  'plus'=>true ],

                ['cat' => 'Soporte y acceso'],
                ['label'=>'App móvil (iOS + Android)',          'free'=>true,  'pro'=>true,  'plus'=>true ],
                ['label'=>'Versión web',                        'free'=>true,  'pro'=>true,  'plus'=>true ],
                ['label'=>'Soporte por correo (48h)',           'free'=>false, 'pro'=>true,  'plus'=>true ],
                ['label'=>'Soporte prioritario dedicado (8h)',  'free'=>false, 'pro'=>false, 'plus'=>true ],
                ['label'=>'Contratos y facturas',               'free'=>false, 'pro'=>true,  'plus'=>true ],
            ];
            @endphp

            @foreach($rows as $row)
                @if(isset($row['cat']))
                    <tr class="category-row">
                        <td colspan="4">{{ $row['cat'] }}</td>
                    </tr>
                @else
                    <tr>
                        <td>{{ $row['label'] }}</td>
                        @foreach(['free','pro','plus'] as $col)
                            <td>
                                @php $val = $row[$col]; @endphp
                                @if($val === true)
                                    <span class="check">✓</span>
                                @elseif($val === false)
                                    <span class="cross">—</span>
                                @else
                                    <span style="font-size:.8125rem;font-weight:600;color:#64748b;">{{ $val }}</span>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endif
            @endforeach

            {{-- Fila de CTA --}}
            <tr>
                <td style="font-weight:700;">Comenzar</td>
                <td>
                    <a href="{{ route('register') }}" class="btn-plan" style="background:#64748b;color:#fff;">
                        Registrarse
                    </a>
                </td>
                <td>
                    <a href="{{ route('plans.pro') }}" class="btn-plan" style="background:#4f46e5;color:#fff;">
                        Suscribirse
                    </a>
                </td>
                <td>
                    <a href="{{ route('plans.plus') }}" class="btn-plan" style="background:#7c3aed;color:#fff;">
                        Solicitar
                    </a>
                </td>
            </tr>
        </tbody>
    </table>
    </div>

    {{-- ── Diferenciadores Mindra ────────────────────────────────────────────── --}}
    <h2 style="font-size:1.25rem;font-weight:800;margin:0 0 20px;">¿Por qué Mindra?</h2>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:16px;margin-bottom:48px;">
        @foreach([
            ['icon'=>'🎙️', 'title'=>'Voz + texto + imagen', 'desc'=>'Única plataforma en su clase con análisis multimodal combinado en tiempo real.'],
            ['icon'=>'🔬', 'title'=>'Respaldo académico',    'desc'=>'Desarrollada en el Laboratorio CAFINED por investigadores en salud mental.'],
            ['icon'=>'🔒', 'title'=>'Privacidad primero',    'desc'=>'Tus datos nunca se venden. Cumplimos LFPDPPP y los más altos estándares éticos.'],
            ['icon'=>'⚡', 'title'=>'Tiempo real',           'desc'=>'Resultados en segundos, no en días. IA optimizada para baja latencia.'],
            ['icon'=>'📱', 'title'=>'App + Web',             'desc'=>'Disponible en iOS, Android y navegador. Sincronización automática entre dispositivos.'],
            ['icon'=>'🇲🇽', 'title'=>'Hecho en México',      'desc'=>'Optimizado para el contexto cultural latinoamericano. Atención en español.'],
        ] as $feat)
        <div style="background:var(--bg-card,#fff);border:1px solid var(--border,#e8edf5);border-radius:16px;padding:20px;">
            <div style="font-size:2rem;margin-bottom:8px;">{{ $feat['icon'] }}</div>
            <p style="font-weight:700;font-size:.9375rem;margin:0 0 6px;">{{ $feat['title'] }}</p>
            <p style="font-size:.8125rem;color:var(--text-muted,#64748b);line-height:1.6;margin:0;">{{ $feat['desc'] }}</p>
        </div>
        @endforeach
    </div>

    {{-- ── FAQ ──────────────────────────────────────────────────────────────── --}}
    <h2 style="font-size:1.25rem;font-weight:800;margin:0 0 20px;">Preguntas frecuentes</h2>
    @foreach([
        ['q'=>'¿Puedo cambiar de plan en cualquier momento?',
         'a'=>'Sí. Puedes actualizar a Pro o Plus cuando quieras. Al hacer un nuevo pago, tu plan se actualiza de inmediato. No existe penalización por cambio de plan.'],
        ['q'=>'¿Hay renovación automática?',
         'a'=>'No. Mindra no realiza cobros automáticos. Recibirás un correo cuando tu plan esté próximo a vencer y podrás renovar manualmente si deseas continuar.'],
        ['q'=>'¿Qué pasa con mis datos si cancelo?',
         'a'=>'Puedes exportar todo tu historial como CSV antes de cancelar. Tras la cancelación, tus datos se conservan por 12 meses adicionales antes de ser eliminados definitivamente.'],
        ['q'=>'¿Mindra reemplaza a un psicólogo o terapeuta?',
         'a'=>'No. Mindra es una herramienta de apoyo al bienestar emocional. No emite diagnósticos clínicos y no sustituye la atención profesional. Te recomendamos siempre consultar a un especialista ante situaciones de crisis.'],
        ['q'=>'¿El Plan Plus es para empresas o personas individuales?',
         'a'=>'Principalmente para investigadores, clínicos e instituciones. Sin embargo, si tienes necesidades avanzadas como individuo, puedes solicitarlo y evaluamos tu caso.'],
        ['q'=>'¿Puedo probar antes de pagar?',
         'a'=>'Sí. El Plan Free no tiene límite de tiempo. Puedes usar Mindra de forma gratuita indefinidamente y actualizar cuando lo necesites.'],
    ] as $faq)
    <div class="faq-item">
        <button class="faq-q" onclick="this.parentNode.classList.toggle('open')">
            {{ $faq['q'] }}
            <svg class="faq-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width:16px;height:16px;flex-shrink:0;">
                <path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/>
            </svg>
        </button>
        <div class="faq-a">{{ $faq['a'] }}</div>
    </div>
    @endforeach

</div>
@endsection
