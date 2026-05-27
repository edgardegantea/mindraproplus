<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Bienvenido/a a Mindra</title>
<style>
  body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:#f1f5f9;margin:0;padding:0;color:#0f172a;}
  .wrap{max-width:600px;margin:32px auto;}
  .header{background:linear-gradient(135deg,#0f172a,#1e293b);border-radius:16px 16px 0 0;padding:36px;text-align:center;}
  .logo{font-size:1.75rem;font-weight:900;color:#fff;letter-spacing:-.02em;}
  .logo span{background:linear-gradient(135deg,#38bdf8,#818cf8,#a78bfa);-webkit-background-clip:text;-webkit-text-fill-color:transparent;}
  .body{background:#fff;padding:36px;}
  .hi{font-size:1.25rem;font-weight:800;color:#0f172a;margin:0 0 12px;}
  .text{font-size:.9375rem;color:#475569;line-height:1.7;margin:0 0 20px;}
  .feat-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin:24px 0;}
  .feat-card{background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:14px 16px;}
  .feat-card .icon{font-size:1.25rem;margin-bottom:6px;}
  .feat-card .title{font-size:.8125rem;font-weight:700;color:#1e293b;}
  .feat-card .desc{font-size:.75rem;color:#64748b;margin-top:3px;}
  .cta{display:block;background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff!important;text-decoration:none;text-align:center;padding:14px 28px;border-radius:12px;font-weight:700;font-size:1rem;margin:24px 0 8px;}
  .plan-box{background:linear-gradient(135deg,#f8fafc,#f1f5f9);border:1px solid #e2e8f0;border-radius:12px;padding:18px 20px;margin:20px 0;}
  .plan-box h4{font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#94a3b8;margin:0 0 10px;}
  .plan-row{display:flex;align-items:center;gap:8px;margin-bottom:6px;font-size:.875rem;color:#334155;}
  .check{color:#16a34a;font-weight:700;}
  .cross{color:#94a3b8;}
  .upgrade-box{background:linear-gradient(135deg,#faf5ff,#ede9fe);border:1.5px solid #c4b5fd;border-radius:12px;padding:18px 20px;margin:20px 0;text-align:center;}
  .footer{background:#f8fafc;border-radius:0 0 16px 16px;padding:20px 36px;text-align:center;}
  .footer p{font-size:.75rem;color:#94a3b8;margin:4px 0;}
  @media(max-width:520px){.wrap{margin:0;}.header,.body,.footer{padding:24px 18px;}.feat-grid{grid-template-columns:1fr;}}
</style>
</head>
<body>
<div class="wrap">
  <div class="header">
    <div class="logo">mind<span>ra</span></div>
    <div style="color:rgba(255,255,255,.6);font-size:.875rem;margin-top:6px;">Bienestar emocional con inteligencia artificial</div>
  </div>

  <div class="body">
    <p class="hi">¡Hola, {{ $user->name }}! 👋</p>
    <p class="text">
      Tu cuenta en <strong>Mindra</strong> ha sido creada exitosamente. Estamos felices de tenerte con nosotros.<br>
      Mindra usa inteligencia artificial para ayudarte a monitorear tu bienestar emocional mediante análisis de texto y voz.
    </p>

    <div class="feat-grid">
      <div class="feat-card">
        <div class="icon">💬</div>
        <div class="title">Chat con IA</div>
        <div class="desc">Conversa sobre cómo te sientes — texto o audio</div>
      </div>
      <div class="feat-card">
        <div class="icon">🎙️</div>
        <div class="title">Análisis de voz</div>
        <div class="desc">Detectamos señales de ansiedad en tu voz</div>
      </div>
      <div class="feat-card">
        <div class="icon">📅</div>
        <div class="title">Calendario</div>
        <div class="desc">Visualiza tu bienestar día a día</div>
      </div>
      <div class="feat-card">
        <div class="icon">🔒</div>
        <div class="title">Privado y seguro</div>
        <div class="desc">Tus datos siempre protegidos</div>
      </div>
    </div>

    <div class="plan-box">
      <h4>Plan Free — lo que tienes hoy</h4>
      <div class="plan-row"><span class="check">✓</span> Chat con IA (texto y audio)</div>
      <div class="plan-row"><span class="check">✓</span> Calendario de bienestar básico</div>
      <div class="plan-row"><span class="cross">○</span> Análisis de emociones <span style="font-size:.75rem;color:#94a3b8;">(Pro/Plus)</span></div>
      <div class="plan-row"><span class="cross">○</span> Historial completo <span style="font-size:.75rem;color:#94a3b8;">(Pro/Plus)</span></div>
      <div class="plan-row"><span class="cross">○</span> Análisis facial y reportes clínicos <span style="font-size:.75rem;color:#94a3b8;">(Plus)</span></div>
    </div>

    <a href="https://mindra.cafined.org" class="cta">Abrir Mindra →</a>

    <div class="upgrade-box">
      <div style="font-size:.875rem;font-weight:700;color:#6d28d9;margin-bottom:4px;">¿Quieres más? Conoce los planes Pro y Plus</div>
      <div style="font-size:.8125rem;color:#7c3aed;">Desbloquea análisis de emociones, historial completo, reportes clínicos y más.</div>
      <a href="https://mindra.cafined.org/#planes" style="display:inline-block;margin-top:12px;font-size:.8125rem;font-weight:700;color:#7c3aed;padding:7px 18px;border-radius:9px;border:1.5px solid #a78bfa;background:#fff;text-decoration:none;">Ver planes</a>
    </div>
  </div>

  <div class="footer">
    <p><strong>Mindra</strong> · mindra.cafined.org</p>
    <p>Laboratorio CAFINED — Computación Afectiva e Innovación Educativa</p>
    <p style="margin-top:8px;">Si no creaste esta cuenta, ignora este mensaje.</p>
  </div>
</div>
</body>
</html>
