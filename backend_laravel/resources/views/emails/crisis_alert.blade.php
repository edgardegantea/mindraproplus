<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Alerta de bienestar — Mindra</title>
<style>
  body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:#f1f5f9;margin:0;padding:0;color:#0f172a;}
  .wrap{max-width:580px;margin:32px auto;}
  .header{border-radius:16px 16px 0 0;padding:32px 36px;background:linear-gradient(135deg,#7f1d1d,#dc2626);text-align:center;}
  .alert-icon{font-size:2.5rem;margin-bottom:8px;}
  .header h1{font-size:1.5rem;font-weight:800;color:#fff;margin:0;}
  .header p{color:rgba(255,255,255,.8);font-size:.9rem;margin:6px 0 0;}
  .body{background:#fff;padding:36px;}
  .hi{font-size:1rem;font-weight:700;color:#0f172a;margin:0 0 10px;}
  .text{font-size:.9375rem;color:#475569;line-height:1.7;margin:0 0 20px;}
  .alert-box{background:#fef2f2;border:2px solid #fca5a5;border-radius:14px;padding:20px 24px;margin:16px 0;}
  .alert-box .pct{font-size:2.5rem;font-weight:900;color:#dc2626;line-height:1;}
  .alert-box .lbl{font-size:.875rem;color:#991b1b;font-weight:600;margin-top:4px;}
  .tips-title{font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#7f1d1d;margin:20px 0 10px;}
  .tip{display:flex;align-items:flex-start;gap:10px;margin-bottom:10px;font-size:.875rem;color:#1e293b;line-height:1.5;}
  .tip-num{width:22px;height:22px;border-radius:50%;background:#dc2626;color:#fff;font-size:.6875rem;font-weight:800;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:1px;}
  .cta{display:block;text-decoration:none;text-align:center;padding:13px 24px;border-radius:12px;font-weight:700;font-size:.9375rem;margin:24px 0 8px;color:#fff!important;background:linear-gradient(135deg,#4f46e5,#7c3aed);}
  .calm-box{background:#f0fdf4;border:1.5px solid #bbf7d0;border-radius:12px;padding:16px 20px;margin-top:16px;font-size:.875rem;color:#166534;line-height:1.6;}
  .footer{background:#f8fafc;border-radius:0 0 16px 16px;padding:20px 36px;text-align:center;}
  .footer p{font-size:.75rem;color:#94a3b8;margin:4px 0;}
  @media(max-width:520px){.wrap{margin:0;}.header,.body,.footer{padding:24px 18px;}}
</style>
</head>
<body>
<div class="wrap">

  <div class="header">
    <div class="alert-icon">⚠️</div>
    <h1>Detectamos un momento difícil</h1>
    <p>Mindra está aquí para apoyarte</p>
  </div>

  <div class="body">
    <p class="hi">Hola, {{ $user->name }}</p>
    <p class="text">
      Durante tu última sesión con Mindra se detectó un nivel elevado de ansiedad.
      Queremos que sepas que no estás solo/a, y que hay recursos que pueden ayudarte ahora mismo.
    </p>

    <div class="alert-box">
      <div class="pct">{{ $probability }}%</div>
      <div class="lbl">Nivel de ansiedad detectado: {{ $label }}</div>
    </div>

    <p class="tips-title">Qué puedes hacer ahora</p>

    <div class="tip">
      <div class="tip-num">1</div>
      <span><strong>Respiración 4-7-8:</strong> Inhala 4 segundos, sostén 7 segundos, exhala lentamente durante 8 segundos. Repite 4 veces.</span>
    </div>
    <div class="tip">
      <div class="tip-num">2</div>
      <span><strong>Técnica 5-4-3-2-1:</strong> Nombra 5 cosas que puedes ver, 4 que puedes tocar, 3 que puedes escuchar, 2 que puedes oler, 1 que puedes saborear.</span>
    </div>
    <div class="tip">
      <div class="tip-num">3</div>
      <span><strong>Mueve el cuerpo:</strong> Levántate, estira los brazos, toma agua. El movimiento físico rompe el ciclo de la ansiedad.</span>
    </div>
    <div class="tip">
      <div class="tip-num">4</div>
      <span><strong>Habla con alguien:</strong> Línea de crisis en México: <strong>800-290-0024</strong> (SAPTEL, 24 hrs, gratuito).</span>
    </div>

    <a href="https://mindra.cafined.org/chat" class="cta">Volver al chat con Mindra →</a>

    <div class="calm-box">
      💚 <strong>Recuerda:</strong> Este nivel de ansiedad es una señal que tu cuerpo te envía, no un juicio sobre ti.
      Con el apoyo adecuado, estos momentos pasan. Mindra seguirá aquí cuando quieras hablar.
    </div>
  </div>

  <div class="footer">
    <p><strong>Mindra</strong> · mindra.cafined.org</p>
    <p>Este mensaje se envió porque tu plan Plus tiene activadas las alertas automáticas de bienestar.</p>
    <p style="margin-top:8px;color:#cbd5e1;">Laboratorio CAFINED · Si no deseas recibir estas alertas, contáctanos.</p>
  </div>

</div>
</body>
</html>
