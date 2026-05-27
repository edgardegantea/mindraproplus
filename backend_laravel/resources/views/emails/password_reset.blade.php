<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Restablecer contraseña — Mindra</title>
  <style>
    body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background:#f6f6f6; margin:0; padding:0; }
    .wrapper { max-width:560px; margin:40px auto; background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,.08); }
    .header { background:#4f46e5; padding:32px 40px; text-align:center; }
    .header h1 { color:#fff; margin:0; font-size:24px; letter-spacing:-.5px; }
    .header p  { color:#c7d2fe; margin:6px 0 0; font-size:14px; }
    .body { padding:40px; color:#374151; }
    .body h2 { font-size:20px; margin:0 0 12px; color:#111827; }
    .body p  { line-height:1.6; margin:0 0 16px; }
    .btn { display:inline-block; background:#4f46e5; color:#fff !important; padding:14px 32px;
           border-radius:8px; text-decoration:none; font-weight:600; font-size:15px; margin:8px 0; }
    .note { font-size:12px; color:#9ca3af; background:#f9fafb; border-radius:8px; padding:12px 16px; margin-top:24px; }
    .footer { padding:24px 40px; text-align:center; font-size:12px; color:#9ca3af; border-top:1px solid #f3f4f6; }
  </style>
</head>
<body>
  <div class="wrapper">
    <div class="header">
      <h1>🧠 Mindra</h1>
      <p>Tu acompañante de bienestar emocional</p>
    </div>
    <div class="body">
      <h2>Restablece tu contraseña</h2>
      <p>Hola, <strong>{{ $user->name }}</strong>.</p>
      <p>Recibimos una solicitud para restablecer la contraseña de tu cuenta. Haz clic en el botón para crear una nueva:</p>

      <p style="text-align:center;">
        <a href="{{ $resetUrl }}" class="btn">Restablecer contraseña</a>
      </p>

      <p>Este enlace es válido por <strong>60 minutos</strong>. Si no solicitaste este cambio, puedes ignorar este correo — tu contraseña actual seguirá siendo la misma.</p>

      <div class="note">
        ¿El botón no funciona? Copia y pega este enlace en tu navegador:<br>
        <a href="{{ $resetUrl }}" style="word-break:break-all;color:#4f46e5;">{{ $resetUrl }}</a>
      </div>
    </div>
    <div class="footer">
      © {{ date('Y') }} Mindra · <a href="{{ config('app.url') }}/privacidad" style="color:#9ca3af;">Privacidad</a>
      <br>Si tienes problemas, escríbenos a <a href="mailto:noreply@mindra.cafined.org" style="color:#9ca3af;">noreply@mindra.cafined.org</a>
    </div>
  </div>
</body>
</html>
