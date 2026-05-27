<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $type === 'notification' ? 'Nueva solicitud Plus' : 'Solicitud recibida — Mindra Plus' }}</title>
<style>
  body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f1f5f9; margin: 0; padding: 0; color: #0f172a; }
  .wrapper { max-width: 620px; margin: 32px auto; }
  .header { background: linear-gradient(135deg, #7c3aed, #3b0764); border-radius: 16px 16px 0 0; padding: 32px 36px; text-align: center; }
  .header img { width: 52px; height: 52px; margin-bottom: 12px; }
  .header h1 { color: #fff; font-size: 1.4rem; margin: 0; font-weight: 800; }
  .header p  { color: rgba(255,255,255,.75); font-size: .9rem; margin: 6px 0 0; }
  .body { background: #fff; padding: 32px 36px; }
  .section { margin-bottom: 28px; }
  .section-title {
    font-size: .75rem; font-weight: 700; text-transform: uppercase; letter-spacing: .08em;
    color: #7c3aed; border-bottom: 1px solid #ede9fe; padding-bottom: 6px; margin-bottom: 14px;
  }
  .row { display: flex; gap: 8px; margin-bottom: 8px; }
  .row .label { font-size: .8125rem; font-weight: 600; color: #64748b; min-width: 180px; }
  .row .value { font-size: .8125rem; color: #0f172a; }
  .highlight-box {
    background: #f5f3ff; border: 1.5px solid #ddd6fe; border-radius: 12px;
    padding: 16px 20px; margin: 20px 0;
  }
  .highlight-box p { margin: 0; font-size: .9rem; line-height: 1.6; }
  .cta-btn {
    display: inline-block; background: linear-gradient(135deg, #7c3aed, #3b0764);
    color: #fff !important; text-decoration: none; padding: 12px 28px;
    border-radius: 10px; font-weight: 700; font-size: .9375rem; margin: 8px 0;
  }
  .footer { background: #f8fafc; border-radius: 0 0 16px 16px; padding: 20px 36px; text-align: center; }
  .footer p { font-size: .75rem; color: #94a3b8; margin: 4px 0; }
  @media(max-width:520px){ .wrapper{margin:0;} .header,.body,.footer{padding:24px 20px;} .row{flex-direction:column;gap:2px;} .row .label{min-width:auto;} }
</style>
</head>
<body>
<div class="wrapper">

  @php
    $headerConfig = [
      'confirmation' => ['¡Solicitud recibida!',       'Hemos registrado tu solicitud de acceso al Plan Plus.',  '#7c3aed', '#3b0764'],
      'notification' => ['📩 Nueva solicitud Plan Plus','Un usuario ha solicitado acceso al plan Plus de Mindra.','#1e293b', '#0f172a'],
      'in_review'    => ['🔍 Solicitud en revisión',    'El equipo de Mindra está evaluando tu solicitud.',        '#b45309', '#78350f'],
      'approved'     => ['🎉 ¡Acceso Plus aprobado!',   'Tu cuenta institucional Plus ya está activa en Mindra.',  '#15803d', '#14532d'],
      'rejected'     => ['Actualización de solicitud',  'Hemos evaluado tu solicitud del Plan Plus.',              '#dc2626', '#7f1d1d'],
    ];
    [$hTitle, $hSub, $hFrom, $hTo] = $headerConfig[$type] ?? $headerConfig['confirmation'];
  @endphp
  <div class="header" style="background:linear-gradient(135deg,{{ $hFrom }},{{ $hTo }});">
    <h1>{{ $hTitle }}</h1>
    <p>{{ $hSub }}</p>
  </div>

  <div class="body">

    @if($type === 'confirmation')
    <div class="highlight-box">
      <p>Hola <strong>{{ $data['requester_name'] }}</strong>, gracias por tu interés en el <strong>Plan Plus de Mindra</strong>. Hemos registrado tu solicitud y nos pondremos en contacto contigo en un plazo máximo de <strong>24 horas hábiles</strong>.</p>
    </div>
    @elseif($type === 'in_review')
    <div class="highlight-box" style="background:#fffbeb;border-color:#fde68a;">
      <p>Hola <strong>{{ $data['requester_name'] }}</strong>, tu solicitud de acceso al <strong>Plan Plus de Mindra</strong> para <strong>{{ $data['org_name'] ?? '' }}</strong> está siendo revisada por nuestro equipo. Te notificaremos en cuanto tengamos una respuesta.</p>
    </div>
    @elseif($type === 'approved')
    <div class="highlight-box" style="background:#f0fdf4;border-color:#bbf7d0;">
      <p>¡Excelente noticia, <strong>{{ $data['requester_name'] }}</strong>! Tu solicitud para <strong>{{ $data['org_name'] ?? '' }}</strong> ha sido <strong>aprobada</strong>. Tu cuenta Plus ya está activa. Si tienes dudas o necesitas apoyo, responde este correo.</p>
    </div>
    @elseif($type === 'rejected')
    <div class="highlight-box" style="background:#fef2f2;border-color:#fecaca;">
      <p>Hola <strong>{{ $data['requester_name'] }}</strong>, tras evaluar tu solicitud para <strong>{{ $data['org_name'] ?? '' }}</strong>, por el momento no podemos aprobarla.
      @if(!empty($data['status_notes']))<br><br><strong>Motivo:</strong> {{ $data['status_notes'] }}@endif
      <br><br>Si tienes preguntas, responde este correo y con gusto te orientamos.</p>
    </div>
    @endif

    {{-- Mensaje del admin para confirmation (bloque aparte, al final del highlight-box) --}}
    @if($type === 'confirmation' && !empty($data['admin_message']))
    <div style="background:#f0f4ff;border-left:4px solid #6366f1;border-radius:0 10px 10px 0;padding:14px 18px;margin:16px 0 0;">
      <div style="font-size:.6875rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#6366f1;margin-bottom:6px;">Mensaje del equipo Mindra</div>
      <p style="font-size:.875rem;color:#1e293b;margin:0;line-height:1.65;white-space:pre-wrap;">{{ $data['admin_message'] }}</p>
    </div>
    @endif

    @if(in_array($type, ['in_review','approved','rejected'])) {{-- Solo resumen para notificaciones de estado --}}

    {{-- Mensaje personalizado del administrador --}}
    @if(!empty($data['admin_message']))
    <div style="background:#f0f4ff;border-left:4px solid #6366f1;border-radius:0 10px 10px 0;padding:14px 18px;margin:0 0 20px;">
      <div style="font-size:.6875rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#6366f1;margin-bottom:6px;">Mensaje del equipo Mindra</div>
      <p style="font-size:.875rem;color:#1e293b;margin:0;line-height:1.65;white-space:pre-wrap;">{{ $data['admin_message'] }}</p>
    </div>
    @endif

    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:16px 20px;margin:16px 0;font-size:.875rem;color:#334155;">
      <div style="font-weight:700;margin-bottom:8px;">Resumen de la solicitud</div>
      <div style="display:flex;gap:8px;margin-bottom:4px;"><span style="color:#64748b;min-width:120px;">Organización:</span><strong>{{ $data['org_name'] ?? '—' }}</strong></div>
      <div style="display:flex;gap:8px;margin-bottom:4px;"><span style="color:#64748b;min-width:120px;">Tipo:</span>{{ $data['org_type_label'] ?? '—' }}</div>
      <div style="display:flex;gap:8px;"><span style="color:#64748b;min-width:120px;">Uso:</span>{{ $data['use_case_label'] ?? '—' }}</div>
    </div>
    @if($type === 'approved')
    <div style="text-align:center;margin:20px 0;">
      <a href="https://mindra.cafined.org" class="cta-btn">Abrir Mindra →</a>
    </div>
    @endif
    @else
    {{-- Detalle completo para confirmation y notification --}}
    {{-- SOLICITANTE --}}
    <div class="section">
      <div class="section-title">Datos del solicitante</div>
      <div class="row"><span class="label">Nombre</span><span class="value">{{ $data['requester_name'] }}</span></div>
      <div class="row"><span class="label">Cargo / Puesto</span><span class="value">{{ $data['requester_position'] ?? '—' }}</span></div>
      <div class="row"><span class="label">Correo electrónico</span><span class="value">{{ $data['requester_email'] }}</span></div>
      <div class="row"><span class="label">Teléfono / WhatsApp</span><span class="value">{{ $data['requester_phone'] ?? '—' }}</span></div>
    </div>

    {{-- INSTITUCIÓN --}}
    <div class="section">
      <div class="section-title">Datos de la institución / empresa</div>
      <div class="row"><span class="label">Nombre</span><span class="value">{{ $data['org_name'] ?? '—' }}</span></div>
      <div class="row"><span class="label">Tipo</span><span class="value">{{ $data['org_type_label'] ?? $data['org_type'] ?? '—' }}</span></div>
      <div class="row"><span class="label">Giro / Sector</span><span class="value">{{ $data['org_sector'] ?? '—' }}</span></div>
      <div class="row"><span class="label">Sitio web</span><span class="value">{{ $data['org_website'] ?? '—' }}</span></div>
      <div class="row"><span class="label">País</span><span class="value">{{ $data['org_country_name'] ?? $data['org_country'] ?? '—' }}</span></div>
      <div class="row"><span class="label">Estado / Provincia</span><span class="value">{{ $data['org_state_name'] ?? $data['org_state_other'] ?? '—' }}</span></div>
      <div class="row"><span class="label">Municipio / Ciudad</span><span class="value">{{ $data['org_city'] ?? '—' }}</span></div>
      @if(!empty($data['org_full_address']))
      <div class="row"><span class="label">Dirección completa</span><span class="value">{{ $data['org_full_address'] }}</span></div>
      @else
      @if(!empty($data['org_street']))<div class="row"><span class="label">Calle / Avenida</span><span class="value">{{ $data['org_street'] }}{{ !empty($data['org_ext_number']) ? ' #'.$data['org_ext_number'] : '' }}{{ !empty($data['org_int_number']) ? ' Int.'.$data['org_int_number'] : '' }}</span></div>@endif
      @if(!empty($data['org_neighborhood']))<div class="row"><span class="label">Colonia</span><span class="value">{{ $data['org_neighborhood'] }}</span></div>@endif
      @if(!empty($data['org_zip']))<div class="row"><span class="label">Código postal</span><span class="value">{{ $data['org_zip'] }}</span></div>@endif
      @endif
    </div>

    {{-- FACTURACIÓN --}}
    <div class="section">
      <div class="section-title">Datos de facturación</div>
      <div class="row"><span class="label">RFC / Número fiscal</span><span class="value">{{ $data['billing_rfc'] ?? '—' }}</span></div>
      <div class="row"><span class="label">Razón social</span><span class="value">{{ $data['billing_razon_social'] ?? '—' }}</span></div>
      <div class="row"><span class="label">Régimen fiscal</span><span class="value">{{ $data['billing_regimen'] ?? '—' }}</span></div>
      <div class="row"><span class="label">Uso de CFDI</span><span class="value">{{ $data['billing_cfdi'] ?? '—' }}</span></div>
      <div class="row"><span class="label">Email para facturas</span><span class="value">{{ $data['billing_email'] ?? '—' }}</span></div>
    </div>

    {{-- PROYECTO --}}
    <div class="section">
      <div class="section-title">Descripción del proyecto</div>
      <div class="row"><span class="label">Tipo de uso</span><span class="value">{{ $data['use_case_label'] ?? $data['use_case'] ?? '—' }}</span></div>
      <div class="row"><span class="label">Usuarios estimados</span><span class="value">{{ $data['num_users'] ?? '—' }}</span></div>
      <div class="row"><span class="label">¿Cómo nos encontró?</span><span class="value">{{ $data['how_found'] ?? '—' }}</span></div>
      @if(!empty($data['project_description']))
      <div style="margin-top:10px;">
        <div class="label" style="margin-bottom:6px;">Descripción</div>
        <div style="background:#f8fafc;border-radius:8px;padding:12px;font-size:.8125rem;line-height:1.6;">{{ $data['project_description'] }}</div>
      </div>
      @endif
      @if(!empty($data['additional_comments']))
      <div style="margin-top:10px;">
        <div class="label" style="margin-bottom:6px;">Comentarios adicionales</div>
        <div style="background:#f8fafc;border-radius:8px;padding:12px;font-size:.8125rem;line-height:1.6;">{{ $data['additional_comments'] }}</div>
      </div>
      @endif
    </div>

    @if($type === 'confirmation')
    <div style="text-align:center;margin-top:24px;">
      <a href="https://mindra.cafined.org" class="cta-btn">Visitar Mindra</a>
    </div>
    @endif
    @endif {{-- end @else (detalle completo) --}}

  </div>

  <div class="footer">
    <p><strong>Mindra</strong> · mindra.cafined.org</p>
    <p>Laboratorio de Computación Afectiva e Innovación Educativa — CAFINED</p>
    @if($type === 'confirmation')
    <p style="margin-top:8px;">Si no realizaste esta solicitud, ignora este correo.</p>
    @endif
  </div>

</div>
</body>
</html>
