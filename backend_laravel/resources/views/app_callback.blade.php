<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mindra — Resultado del pago</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #7C3CC8, #3C14B4);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            padding: 24px;
        }
        .card {
            background: white;
            border-radius: 24px;
            padding: 40px 32px;
            max-width: 400px;
            width: 100%;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0,0,0,0.25);
        }
        .icon {
            width: 72px;
            height: 72px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 36px;
        }
        .icon.success { background: #f0fdf4; }
        .icon.pending { background: #fffbeb; }
        .icon.failure { background: #fef2f2; }
        h1 { font-size: 22px; font-weight: 800; color: #0f172a; margin-bottom: 10px; }
        p  { font-size: 14px; color: #64748b; line-height: 1.6; margin-bottom: 28px; }
        .btn {
            display: inline-block;
            padding: 14px 28px;
            border-radius: 14px;
            font-size: 15px;
            font-weight: 700;
            text-decoration: none;
            color: white;
            background: linear-gradient(135deg, #00A0F0, #7C3CC8);
            width: 100%;
        }
        .order-id {
            margin-top: 20px;
            font-size: 12px;
            color: #94a3b8;
        }
    </style>
</head>
<body>
    <div class="card">
        @php
            $status = request('status', 'pending');
            $orderId = request('order', '—');
        @endphp

        @if($status === 'success')
            <div class="icon success">✅</div>
            <h1>¡Pago exitoso!</h1>
            <p>Tu plan Pro ya está activo. Regresa a la app Mindra y toca <strong>"Ya pagué, verificar"</strong> para confirmar tu suscripción.</p>

        @elseif($status === 'pending')
            <div class="icon pending">⏳</div>
            <h1>Pago en proceso</h1>
            <p>Tu pago está siendo procesado. Puede tardar unos minutos. Regresa a la app y toca <strong>"Ya pagué, verificar"</strong> en unos momentos.</p>

        @else
            <div class="icon failure">❌</div>
            <h1>Pago no completado</h1>
            <p>No se pudo procesar el pago. Puedes intentarlo de nuevo desde la app Mindra.</p>
        @endif

        <a href="#" class="btn" onclick="window.close(); return false;">
            Volver a la app
        </a>

        <p class="order-id">Orden #{{ $orderId }}</p>
    </div>

    <script>
        // Intenta cerrar la pestaña o redirigir después de 3 segundos
        // en dispositivos que lo permitan.
        @if($status === 'success')
        setTimeout(function() {
            window.close();
        }, 3000);
        @endif
    </script>
</body>
</html>
