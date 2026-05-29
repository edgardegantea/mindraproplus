<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Tu resumen semanal — Mindra</title>
<style>
  body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:#f1f5f9;margin:0;padding:0;color:#0f172a;}
  .wrap{max-width:600px;margin:32px auto;}
  .header{background:linear-gradient(135deg,#0f172a,#1e293b);border-radius:16px 16px 0 0;padding:36px;text-align:center;}
  .logo{font-size:1.75rem;font-weight:900;color:#fff;letter-spacing:-.02em;}
  .logo span{background:linear-gradient(135deg,#38bdf8,#818cf8,#a78bfa);-webkit-background-clip:text;-webkit-text-fill-color:transparent;}
  .badge{display:inline-block;background:rgba(255,255,255,.1);color:rgba(255,255,255,.8);font-size:.75rem;font-weight:600;padding:4px 12px;border-radius:999px;margin-top:10px;border:1px solid rgba(255,255,255,.2);}
  .body{background:#fff;padding:36px;}
  .hi{font-size:1.25rem;font-weight:800;color:#0f172a;margin:0 0 12px;}
  .text{font-size:.9375rem;color:#475569;line-height:1.7;margin:0 0 16px;}

  /* Stats grid */
  .stats-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin:20px 0;}
  .stat-card{background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:14px 12px;text-align:center;}
  .stat-card .num{font-size:1.5rem;font-weight:900;color:#4f46e5;line-height:1;}
  .stat-card .lbl{font-size:.7rem;color:#94a3b8;margin-top:4px;font-weight:500;}

  /* Trend banner */
  .trend-banner{border-radius:12px;padding:14px 18px;margin:18px 0;display:flex;align-items:center;gap:12px;}
  .trend-banner .icon{font-size:1.75rem;flex-shrink:0;}
  .trend-banner .content .title{font-size:.9rem;font-weight:700;margin:0 0 2px;}
  .trend-banner .content .desc{font-size:.8125rem;line-height:1.55;margin:0;}

  /* Insight list */
  .insight-list{margin:16px 0;padding:0;list-style:none;}
  .insight-list li{display:flex;align-items:flex-start;gap:9px;font-size:.875rem;color:#475569;line-height:1.6;padding:7px 0;border-bottom:1px solid #f1f5f9;}
  .insight-list li:last-child{border-bottom:none;}
  .insight-list .dot{width:8px;height:8px;border-radius:50%;flex-shrink:0;margin-top:6px;}

  /* Bar chart simulation */
  .bar-row{display:flex;align-items:center;gap:8px;margin:5px 0;}
  .bar-label{font-size:.7rem;color:#64748b;width:28px;text-align:right;flex-shrink:0;}
  .bar-track{flex:1;background:#f1f5f9;border-radius:999px;height:8px;overflow:hidden;}
  .bar-fill{height:100%;border-radius:999px;transition:width .3s;}
  .bar-value{font-size:.7rem;color:#94a3b8;width:28px;text-align:left;flex-shrink:0;}

  /* Program progress */
  .prog-row{display:flex;align-items:center;justify-content:space-between;padding:10px 0;border-bottom:1px solid #f1f5f9;}
  .prog-row:last-child{border-bottom:none;}

  .cta{display:block;background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff!important;text-decoration:none;text-align:center;padding:14px 28px;border-radius:12px;font-weight:700;font-size:1rem;margin:24px 0 8px;}

  .section-title{font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#94a3b8;margin:24px 0 10px;}
  .card{background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:16px 18px;margin:12px 0;}

  .footer{background:#f8fafc;border-radius:0 0 16px 16px;padding:20px 36px;text-align:center;}
  .footer p{font-size:.75rem;color:#94a3b8;margin:4px 0;}
  .unsubscribe{font-size:.7rem;color:#cbd5e1;margin-top:10px;}

  @media(max-width:520px){.wrap{margin:0;}.header,.body,.footer{padding:24px 18px;}.stats-grid{grid-template-columns:1fr 1fr;}}
</style>
</head>
<body>
<div class="wrap">

  {{-- Header --}}
  <div class="header">
    <div class="logo">mind<span>ra</span></div>
    <div style="color:rgba(255,255,255,.6);font-size:.875rem;margin-top:6px;">Tu resumen semanal de bienestar</div>
    <div class="badge">📅 {{ $stats['week_label'] }}</div>
  </div>

  <div class="body">

    <p class="hi">Hola, {{ $user->name }} 👋</p>
    <p class="text">
      Aquí tienes un resumen de tu actividad en Mindra durante los últimos 7 días.
      Revisarlo puede ayudarte a identificar patrones y celebrar tu progreso.
    </p>

    {{-- ── Stats principales ──────────────────────────────────────────── --}}
    <div class="stats-grid">
      <div class="stat-card">
        <div class="num">{{ $stats['sessions'] }}</div>
        <div class="lbl">sesiones</div>
      </div>
      <div class="stat-card">
        <div class="num">{{ $stats['active_days'] }}</div>
        <div class="lbl">días activo</div>
      </div>
      <div class="stat-card">
        @php
          $pct = $stats['avg_prob'] !== null ? round($stats['avg_prob'] * 100) : '—';
        @endphp
        <div class="num" style="color:{{ $stats['trend_color'] }};">{{ $pct }}{{ is_numeric($pct) ? '%' : '' }}</div>
        <div class="lbl">ansiedad prom.</div>
      </div>
    </div>

    {{-- ── Tendencia ──────────────────────────────────────────────────── --}}
    @if($stats['sessions'] > 0)
    @php
      $trendConfig = [
        'improving'   => ['bg'=>'#f0fdf4', 'border'=>'#bbf7d0', 'title'=>'¡Vas mejorando!', 'color'=>'#15803d', 'icon'=>'📈', 'desc'=>'Tus niveles de ansiedad han bajado esta semana comparado con sesiones anteriores. ¡Continúa con esa racha!'],
        'worsening'   => ['bg'=>'#fff7ed', 'border'=>'#fed7aa', 'title'=>'Semana más difícil', 'color'=>'#c2410c', 'icon'=>'🌊', 'desc'=>'Esta semana los indicadores subieron un poco. Es normal tener períodos así. Prueba alguna técnica del programa de manejo de ansiedad.'],
        'high'        => ['bg'=>'#fef2f2', 'border'=>'#fecaca', 'title'=>'Ansiedad persistente', 'color'=>'#b91c1c', 'icon'=>'🔔', 'desc'=>'Has tenido niveles altos de forma constante. Considera hablar con un profesional de la salud mental.'],
        'stable_low'  => ['bg'=>'#f0fdf4', 'border'=>'#bbf7d0', 'title'=>'Niveles estables y bajos', 'color'=>'#15803d', 'icon'=>'🌿', 'desc'=>'Tu bienestar se mantiene en buenos niveles. La constancia está dando frutos. ¡Sigue así!'],
        'stable'      => ['bg'=>'#f8fafc', 'border'=>'#e2e8f0', 'title'=>'Semana estable', 'color'=>'#475569', 'icon'=>'😌', 'desc'=>'Tu bienestar se ha mantenido estable. Pequeñas variaciones son completamente normales.'],
        'unknown'     => ['bg'=>'#f8fafc', 'border'=>'#e2e8f0', 'title'=>'Primeras sesiones', 'color'=>'#475569', 'icon'=>'🌱', 'desc'=>'Con más sesiones Mindra podrá mostrarte tendencias más precisas. ¡Sigue usando la app!'],
      ];
      $t = $trendConfig[$stats['trend']] ?? $trendConfig['unknown'];
    @endphp
    <div class="trend-banner" style="background:{{ $t['bg'] }};border:1px solid {{ $t['border'] }};">
      <div class="icon">{{ $t['icon'] }}</div>
      <div class="content">
        <p class="title" style="color:{{ $t['color'] }};">{{ $t['title'] }}</p>
        <p class="desc" style="color:{{ $t['color'] }}99;">{{ $t['desc'] }}</p>
      </div>
    </div>
    @endif

    {{-- ── Actividad diaria ───────────────────────────────────────────── --}}
    @if(!empty($stats['daily_probs']))
    <p class="section-title">Actividad diaria</p>
    <div class="card">
      @foreach($stats['daily_probs'] as $day)
      <div class="bar-row">
        <span class="bar-label">{{ $day['label'] }}</span>
        <div class="bar-track">
          <div class="bar-fill" style="width:{{ round($day['prob'] * 100) }}%;background:{{ $day['prob'] > 0.6 ? '#f87171' : ($day['prob'] > 0.4 ? '#fb923c' : '#4ade80') }};"></div>
        </div>
        <span class="bar-value">{{ round($day['prob'] * 100) }}%</span>
      </div>
      @endforeach
      @foreach($stats['missing_days'] ?? [] as $day)
      <div class="bar-row">
        <span class="bar-label" style="color:#cbd5e1;">{{ $day }}</span>
        <div class="bar-track"><div class="bar-fill" style="width:2%;background:#e2e8f0;"></div></div>
        <span class="bar-value" style="color:#cbd5e1;">—</span>
      </div>
      @endforeach
    </div>
    @endif

    {{-- ── Programas activos ──────────────────────────────────────────── --}}
    @if(!empty($stats['programs']))
    <p class="section-title">Tus programas de bienestar</p>
    <div class="card">
      @foreach($stats['programs'] as $prog)
      <div class="prog-row">
        <div>
          <span style="font-size:.875rem;font-weight:600;color:#1e293b;">{{ $prog['emoji'] }} {{ $prog['title'] }}</span>
          <p style="font-size:.75rem;color:#94a3b8;margin:2px 0 0;">{{ $prog['days_done'] }} / {{ $prog['total_days'] }} días completados</p>
        </div>
        <span style="font-size:.9rem;font-weight:700;color:#6366f1;">{{ $prog['progress'] }}%</span>
      </div>
      @endforeach
    </div>
    @endif

    {{-- ── Insights ──────────────────────────────────────────────────── --}}
    @if(!empty($stats['insights']))
    <p class="section-title">Observaciones de la semana</p>
    <ul class="insight-list">
      @foreach($stats['insights'] as $insight)
      <li>
        <span class="dot" style="background:{{ $insight['color'] ?? '#6366f1' }};"></span>
        {{ $insight['text'] }}
      </li>
      @endforeach
    </ul>
    @endif

    {{-- ── CTA --}}
    <a href="https://mindra.cafined.org/chat" class="cta">Continuar con Mindra →</a>

    @if($stats['sessions'] === 0)
    <div style="background:linear-gradient(135deg,#faf5ff,#ede9fe);border:1.5px solid #c4b5fd;border-radius:12px;padding:16px 18px;text-align:center;margin-top:12px;">
      <div style="font-size:1.25rem;margin-bottom:6px;">😴</div>
      <p style="font-size:.875rem;font-weight:700;color:#6d28d9;margin:0 0 4px;">Esta semana no tuviste sesiones</p>
      <p style="font-size:.8125rem;color:#7c3aed;margin:0 0 10px;">Solo 5 minutos al día pueden marcar una diferencia.</p>
      <a href="https://mindra.cafined.org/chat" style="display:inline-block;font-size:.8125rem;font-weight:700;color:#7c3aed;padding:7px 18px;border-radius:9px;border:1.5px solid #a78bfa;background:#fff;text-decoration:none;">Empezar ahora</a>
    </div>
    @endif

  </div>

  <div class="footer">
    <p><strong>Mindra</strong> · mindra.cafined.org</p>
    <p>Laboratorio CAFINED — Computación Afectiva e Innovación Educativa</p>
    <p style="margin-top:8px;" class="unsubscribe">
      Recibes este correo porque tienes una cuenta en Mindra.<br>
      Este es un reporte automático semanal — no responder a este mensaje.
    </p>
  </div>

</div>
</body>
</html>
