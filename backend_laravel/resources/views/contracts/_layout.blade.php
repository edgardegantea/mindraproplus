{{--
  Layout compartido para contratos de plan.
  Variables esperadas:
    $planSlug   : 'free' | 'pro' | 'plus'
    $planName   : 'Free' | 'Pro' | 'Plus'
    $planColor  : color hex principal del plan
    $planBadge  : emoji + etiqueta  p.ej. '🆓 Plan Free'
    $docTitle   : título del documento
--}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>{{ $docTitle }} — Mindra</title>
    <style>
        /* ── Reset & base ─────────────────────────────────────────────── */
        *, *::before, *::after { box-sizing: border-box; }
        html { font-size: 16px; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f8fafc;
            color: #1e293b;
            margin: 0;
            padding: 0;
        }

        /* ── Top nav (oculto al imprimir) ─────────────────────────────── */
        .site-nav {
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            padding: 0 1.5rem;
            height: 56px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .site-nav a { text-decoration: none; color: #64748b; font-size: .875rem; }
        .site-nav a:hover { color: #4f46e5; }
        .nav-logo { display: flex; align-items: center; gap: 8px; }
        .nav-logo img { height: 38px; width: auto; }

        /* ── Toolbar de acciones (oculto al imprimir) ─────────────────── */
        .doc-toolbar {
            max-width: 800px;
            margin: 1.5rem auto .5rem;
            padding: 0 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
        }
        .breadcrumb { display: flex; align-items: center; gap: 6px; font-size: .8125rem; color: #94a3b8; }
        .breadcrumb a { color: #94a3b8; text-decoration: none; }
        .breadcrumb a:hover { color: #4f46e5; }
        .toolbar-actions { display: flex; gap: 8px; }
        .btn-print {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 8px 16px; border-radius: 9px; border: none; cursor: pointer;
            font-size: .875rem; font-weight: 600; font-family: inherit;
            background: {{ $planColor }}; color: #fff;
            text-decoration: none; transition: opacity .15s;
        }
        .btn-print:hover { opacity: .85; }
        .btn-back {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 8px 16px; border-radius: 9px; border: 1.5px solid #e2e8f0;
            background: #fff; color: #475569; font-size: .875rem; font-weight: 600;
            cursor: pointer; font-family: inherit; text-decoration: none; transition: all .15s;
        }
        .btn-back:hover { border-color: #c7d2fe; color: #4f46e5; }

        /* ── Documento ────────────────────────────────────────────────── */
        .doc-wrapper {
            max-width: 800px;
            margin: 0 auto 80px;
            padding: 0 1.5rem;
        }
        .doc-sheet {
            background: #fff;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 24px rgba(0,0,0,.05);
            padding: 48px 56px;
        }

        /* ── Encabezado del documento ─────────────────────────────────── */
        .doc-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            border-bottom: 2px solid {{ $planColor }}20;
            padding-bottom: 28px;
            margin-bottom: 32px;
            gap: 16px;
        }
        .doc-header-left {}
        .doc-badge {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 4px 12px; border-radius: 999px;
            background: {{ $planColor }}15; color: {{ $planColor }};
            font-size: .75rem; font-weight: 700; letter-spacing: .04em;
            margin-bottom: 10px;
        }
        .doc-title { font-size: 1.5rem; font-weight: 800; color: #0f172a; margin: 0 0 4px; }
        .doc-subtitle { font-size: .9rem; color: #64748b; margin: 0; }
        .doc-header-right { text-align: right; flex-shrink: 0; }
        .doc-logo { height: 52px; width: auto; }
        .doc-date { font-size: .75rem; color: #94a3b8; margin-top: 6px; }

        /* ── Aviso médico ─────────────────────────────────────────────── */
        .alert-box {
            background: #fff7ed; border: 1px solid #fed7aa; border-radius: 12px;
            padding: 14px 16px; margin-bottom: 28px;
            display: flex; gap: 12px; align-items: flex-start;
            font-size: .8125rem; color: #92400e; line-height: 1.6;
        }
        .alert-icon { flex-shrink: 0; margin-top: 1px; color: #ea580c; }

        /* ── Secciones ────────────────────────────────────────────────── */
        .section { margin-bottom: 28px; }
        .section-title {
            font-size: .6875rem; font-weight: 800; text-transform: uppercase;
            letter-spacing: .08em; color: {{ $planColor }};
            border-bottom: 1.5px solid {{ $planColor }}30;
            padding-bottom: 6px; margin: 0 0 14px;
            display: flex; align-items: center; gap: 8px;
        }
        .section-num {
            width: 20px; height: 20px; border-radius: 50%;
            background: {{ $planColor }}; color: #fff;
            font-size: .625rem; font-weight: 800;
            display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0;
        }
        .section p { font-size: .9375rem; color: #334155; line-height: 1.75; margin: 0 0 10px; }
        .section ul, .section ol {
            margin: 6px 0 12px; padding-left: 20px;
            display: flex; flex-direction: column; gap: 5px;
        }
        .section li { font-size: .9375rem; color: #334155; line-height: 1.65; }
        .section strong { color: #0f172a; }

        /* ── Tabla de partes ──────────────────────────────────────────── */
        .parties-grid {
            display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-top: 12px;
        }
        .party-card {
            background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px 16px;
        }
        .party-label { font-size: .6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #94a3b8; margin: 0 0 6px; }
        .party-name { font-size: .9375rem; font-weight: 700; color: #0f172a; margin: 0 0 3px; }
        .party-detail { font-size: .8125rem; color: #64748b; margin: 0; line-height: 1.5; }

        /* ── Feature table ────────────────────────────────────────────── */
        .feature-table { width: 100%; border-collapse: collapse; margin: 12px 0; font-size: .875rem; }
        .feature-table th {
            text-align: left; padding: 8px 12px; background: #f1f5f9;
            font-size: .6875rem; font-weight: 700; text-transform: uppercase;
            letter-spacing: .06em; color: #64748b; border-bottom: 1px solid #e2e8f0;
        }
        .feature-table td { padding: 9px 12px; border-bottom: 1px solid #f1f5f9; color: #334155; }
        .feature-table tr:last-child td { border-bottom: none; }
        .check { color: #16a34a; font-weight: 700; }
        .cross { color: #94a3b8; }

        /* ── Firma ────────────────────────────────────────────────────── */
        .signature-grid {
            display: grid; grid-template-columns: 1fr 1fr; gap: 32px; margin-top: 40px;
            padding-top: 28px; border-top: 1px solid #e2e8f0;
        }
        .signature-block { display: flex; flex-direction: column; gap: 4px; }
        .sig-line {
            border-bottom: 1.5px solid #94a3b8; height: 40px; margin-bottom: 8px;
        }
        .sig-label { font-size: .75rem; font-weight: 700; color: #64748b; }
        .sig-sublabel { font-size: .6875rem; color: #94a3b8; }

        /* ── Pie del documento ────────────────────────────────────────── */
        .doc-footer {
            margin-top: 36px; padding-top: 20px; border-top: 1px solid #f1f5f9;
            text-align: center; font-size: .75rem; color: #94a3b8; line-height: 1.6;
        }

        /* ── Contratos relacionados ───────────────────────────────────── */
        .related-contracts {
            max-width: 800px; margin: 0 auto 40px; padding: 0 1.5rem;
        }
        .related-title { font-size: .75rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #94a3b8; margin: 0 0 12px; }
        .related-grid { display: flex; flex-wrap: wrap; gap: 8px; }
        .related-link {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 7px 14px; border-radius: 9px; border: 1.5px solid #e2e8f0;
            background: #fff; color: #475569; font-size: .8125rem; font-weight: 600;
            text-decoration: none; transition: all .15s;
        }
        .related-link:hover { border-color: {{ $planColor }}60; color: {{ $planColor }}; }
        .related-link.active { background: {{ $planColor }}10; border-color: {{ $planColor }}50; color: {{ $planColor }}; pointer-events: none; }

        /* ── Print styles ─────────────────────────────────────────────── */
        @media print {
            body { background: #fff; font-size: 11pt; }
            .site-nav, .doc-toolbar, .related-contracts { display: none !important; }
            .doc-wrapper { max-width: 100%; margin: 0; padding: 0; }
            .doc-sheet {
                border: none; border-radius: 0; box-shadow: none;
                padding: 0; margin: 0;
            }
            .section p, .section li { font-size: 10.5pt; }
            .section-title { font-size: 7.5pt; }
            .doc-title { font-size: 18pt; }
            .signature-grid { break-inside: avoid; }
            .section { break-inside: avoid; }
            a { color: inherit !important; text-decoration: none; }
        }

        @media (max-width: 600px) {
            .doc-sheet { padding: 28px 20px; }
            .doc-header { flex-direction: column-reverse; }
            .doc-header-right { text-align: left; }
            .parties-grid, .signature-grid { grid-template-columns: 1fr; }
            .doc-logo { height: 36px; }
        }
    </style>
</head>
<body>

{{-- ── Navegación superior ─────────────────────────────────────────── --}}
<nav class="site-nav">
    <a href="{{ route('home') }}" class="nav-logo">
        <img src="/assets/img/mindra1.png" alt="">
        <img src="/assets/img/mindra2.png" alt="Mindra" style="height:52px;">
    </a>
    <div style="display:flex;gap:16px;align-items:center;">
        @if($planSlug !== 'free')
            <a href="{{ route('plans.' . $planSlug) }}">Ver plan {{ $planName }}</a>
        @else
            <a href="{{ route('home') }}#planes">Ver planes</a>
        @endif
        <a href="{{ route('legal.terms') }}">Términos</a>
    </div>
</nav>

{{-- ── Toolbar ──────────────────────────────────────────────────────── --}}
<div class="doc-toolbar">
    <nav class="breadcrumb">
        <a href="{{ route('home') }}">Inicio</a>
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width:12px;height:12px;"><path fill-rule="evenodd" d="M8.22 5.22a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 0 1-1.06-1.06L11.94 10 8.22 6.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/></svg>
        @if($planSlug !== 'free')
            <a href="{{ route('plans.' . $planSlug) }}">Plan {{ $planName }}</a>
        @else
            <a href="{{ route('home') }}#planes">Plan {{ $planName }}</a>
        @endif
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width:12px;height:12px;"><path fill-rule="evenodd" d="M8.22 5.22a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 0 1-1.06-1.06L11.94 10 8.22 6.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/></svg>
        <span>Contrato</span>
    </nav>
    <div class="toolbar-actions">
        <a href="{{ url()->previous() }}" class="btn-back">
            ← Volver
        </a>
        <button class="btn-print" onclick="window.print()">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width:16px;height:16px;">
                <path fill-rule="evenodd" d="M5 4v3H4a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v2a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1v-2h1a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-1V4a1 1 0 0 0-1-1H6a1 1 0 0 0-1 1Zm2 0h6v3H7V4Zm-1 9v-1h8v1a.5.5 0 0 1-.5.5h-7A.5.5 0 0 1 6 13Zm9-4a1 1 0 1 1 0 2 1 1 0 0 1 0-2Z" clip-rule="evenodd"/>
            </svg>
            Imprimir / Guardar PDF
        </button>
    </div>
</div>

{{-- ── Cuerpo del documento ─────────────────────────────────────────── --}}
<div class="doc-wrapper">
    <div class="doc-sheet">

        {{-- Encabezado --}}
        <div class="doc-header">
            <div class="doc-header-left">
                <div class="doc-badge">{{ $planBadge }}</div>
                <h1 class="doc-title">{{ $docTitle }}</h1>
                <p class="doc-subtitle">Condiciones de acceso al servicio Mindra — Plan {{ $planName }}</p>
            </div>
            <div class="doc-header-right">
                <img src="/assets/img/mindra1.png" alt="Mindra" class="doc-logo">
                <p class="doc-date">
                    Versión {{ date('Y') }}-01<br>
                    Actualizado: {{ \Carbon\Carbon::now()->locale('es')->isoFormat('D [de] MMMM [de] YYYY') }}
                </p>
            </div>
        </div>

        {{-- Aviso médico --}}
        <div class="alert-box">
            <svg class="alert-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width:18px;height:18px;">
                <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495ZM10 5a.75.75 0 0 1 .75.75v3.5a.75.75 0 0 1-1.5 0v-3.5A.75.75 0 0 1 10 5Zm0 9a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd"/>
            </svg>
            <span><strong>Aviso importante:</strong> Mindra es una herramienta de apoyo al bienestar emocional. <strong>No sustituye el diagnóstico ni la atención de un profesional de salud mental.</strong> Ante una crisis, contacta a un profesional o llama a la línea de apoyo SAPTEL: <strong>800 290 0024</strong>.</span>
        </div>

        {{-- Contenido específico del plan --}}
        @yield('contract-body')

        {{-- Firmas --}}
        <div class="signature-grid">
            <div class="signature-block">
                <div class="sig-line"></div>
                <p class="sig-label">Usuario / Representante legal</p>
                <p class="sig-sublabel">Nombre, firma y fecha</p>
            </div>
            <div class="signature-block">
                <div class="sig-line"></div>
                <p class="sig-label">CAFINED — Mindra</p>
                <p class="sig-sublabel">Sello digital de aceptación al registrar cuenta</p>
            </div>
        </div>

        {{-- Pie --}}
        <div class="doc-footer">
            Mindra es un producto del Laboratorio CAFINED.<br>
            Para dudas o aclaraciones: <strong>{{ config('mail.from.address', 'contacto@cafined.org') }}</strong> ·
            <a href="{{ route('legal.terms') }}" style="color:#94a3b8;">Términos de uso</a> ·
            <a href="{{ route('legal.privacy') }}" style="color:#94a3b8;">Privacidad</a><br>
            © {{ date('Y') }} CAFINED. Todos los derechos reservados.
        </div>

    </div>{{-- /doc-sheet --}}
</div>{{-- /doc-wrapper --}}

{{-- ── Contratos relacionados ───────────────────────────────────────── --}}
<div class="related-contracts">
    <p class="related-title">Contratos por plan</p>
    <div class="related-grid">
        <a href="{{ route('contracts.free') }}" class="related-link {{ $planSlug === 'free' ? 'active' : '' }}">
            🆓 Contrato Free
        </a>
        <a href="{{ route('contracts.pro') }}" class="related-link {{ $planSlug === 'pro' ? 'active' : '' }}">
            ⭐ Contrato Pro
        </a>
        <a href="{{ route('contracts.plus') }}" class="related-link {{ $planSlug === 'plus' ? 'active' : '' }}">
            ✦ Contrato Plus
        </a>
    </div>
</div>

</body>
</html>
