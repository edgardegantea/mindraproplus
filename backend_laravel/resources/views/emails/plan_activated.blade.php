<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Plan {{ $planName }} activo — Mindra</title>
<style>
  body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:#f1f5f9;margin:0;padding:0;color:#0f172a;}
  .wrap{max-width:600px;margin:32px auto;}
  .header{border-radius:16px 16px 0 0;padding:36px;text-align:center;
    background:{{ $planSlug === 'plus' ? 'linear-gradient(135deg,#4c1d95,#7c3aed)' : 'linear-gradient(135deg,#1e3a8a,#4f46e5)' }};}
  .badge{display:inline-block;background:rgba(255,255,255,.15);color:#fff;font-size:.75rem;font-weight:700;padding:4px 14px;border-radius:9999px;margin-bottom:12px;border:1px solid rgba(255,255,255,.25);}
  .plan-name{font-size:2rem;font-weight:900;color:#fff;margin:0;}
  .plan-sub{color:rgba(255,255,255,.75);font-size:.9375rem;margin:8px 0 0;}
  .body{background:#fff;padding:36px;}
  .hi{font-size:1.1rem;font-weight:800;color:#0f172a;margin:0 0 12px;}
  .text{font-size:.9375rem;color:#475569;line-height:1.7;margin:0 0 20px;}
  .feats{border-radius:14px;padding:20px 24px;margin:20px 0;
    background:{{ $planSlug === 'plus' ? '#faf5ff' : '#eef2ff' }};
    border:1.5px solid {{ $planSlug === 'plus' ? '#ddd6fe' : '#c7d2fe' }};}
  .feats h4{font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;
    color:{{ $planSlug === 'plus' ? '#7c3aed' : '#4f46e5' }};margin:0 0 14px;}
  .feat-row{display:flex;align-items:center;gap:10px;margin-bottom:8px;font-size:.875rem;color:#1e293b;}
  .feat-row svg{flex-shrink:0;}
  .cta{display:block;text-decoration:none;text-align:center;padding:14px 28px;border-radius:12px;font-weight:700;font-size:1rem;margin:24px 0 8px;color:#fff!important;
    background:{{ $planSlug === 'plus' ? 'linear-gradient(135deg,#7c3aed,#4c1d95)' : 'linear-gradient(135deg,#4f46e5,#1e3a8a)' }};}
  .info-box{background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:16px 20px;margin:16px 0;font-size:.875rem;color:#475569;line-height:1.6;}
  .footer{background:#f8fafc;border-radius:0 0 16px 16px;padding:20px 36px;text-align:center;}
  .footer p{font-size:.75rem;color:#94a3b8;margin:4px 0;}
  @media(max-width:520px){.wrap{margin:0;}.header,.body,.footer{padding:24px 18px;}}
</style>
</head>
<body>
<div class="wrap">
  <div class="header">
    <div class="badge">{{ $planSlug === 'plus' ? '✦ Plan Plus' : '⭐ Plan Pro' }}</div>
    <div class="plan-name">¡Plan {{ $planName }} activo!</div>
    <div class="plan-sub">Tu cuenta ya tiene acceso a todas las funciones {{ $planSlug === 'plus' ? 'institucionales' : 'avanzadas' }}</div>
  </div>

  <div class="body">
    <p class="hi">Hola, {{ $user->name }} 🎉</p>
    <p class="text">
      Tu plan <strong>{{ $planName }}</strong> de Mindra ha sido activado exitosamente. A partir de ahora tienes acceso a las siguientes características:
    </p>

    <div class="feats">
      <h4>Características desbloqueadas</h4>

      @php
        $allFeatures = [
          'texto'              => ['Chat de texto con IA', true],
          'audio'              => ['Chat de audio / análisis de voz', true],
          'emociones'          => ['Detección de emociones faciales y de voz', $planSlug !== 'free'],
          'historial'          => ['Historial de sesiones', $planSlug !== 'free'],
          'historial_completo' => ['Historial ilimitado completo', $planSlug === 'plus'],
          'estadisticas'       => ['Estadísticas avanzadas de bienestar', $planSlug !== 'free'],
          'imagen'             => ['Análisis facial en tiempo real', $planSlug === 'plus'],
          'reporte_clinico'    => ['Reporte clínico PDF (30 días)', $planSlug === 'plus'],
          'crisis_alerts'      => ['Alertas automáticas de crisis', $planSlug === 'plus'],
          'multimodal'         => ['Detección multimodal combinada', $planSlug === 'plus'],
        ];
        $active = !empty($features) ? $features : $allFeatures;
      @endphp

      @foreach($allFeatures as $key => [$label, $default])
        @php $enabled = isset($features[$key]) ? $features[$key] : $default; @endphp
        @if($enabled)
        <div class="feat-row">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="{{ $planSlug === 'plus' ? '#7c3aed' : '#4f46e5' }}" width="16" height="16">
            <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd"/>
          </svg>
          {{ $label }}
        </div>
        @endif
      @endforeach
    </div>

    <a href="https://mindra.cafined.org" class="cta">Abrir Mindra y comenzar →</a>

    <div class="info-box">
      📱 <strong>App móvil:</strong> Si usas la app de Mindra, cierra sesión y vuelve a iniciarla para que tu nuevo plan se actualice correctamente.
    </div>

    @if($planSlug === 'plus')
    <div class="info-box" style="background:#faf5ff;border-color:#ddd6fe;color:#4c1d95;">
      💜 <strong>Soporte dedicado:</strong> Como usuario Plus tienes acceso a soporte prioritario. Escríbenos a <a href="mailto:{{ config('mail.from.address') }}" style="color:#7c3aed;">{{ config('mail.from.address') }}</a> para cualquier consulta.
    </div>
    @endif
  </div>

  <div class="footer">
    <p><strong>Mindra</strong> · mindra.cafined.org</p>
    <p>Laboratorio CAFINED — Computación Afectiva e Innovación Educativa</p>
  </div>
</div>
</body>
</html>
