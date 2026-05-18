@extends('layouts.app')
@section('title', 'Uso de Datos')

@push('styles')
<style>
    .legal-body { max-width: 48rem; margin-left: auto; margin-right: auto; }
    .legal-section { margin-bottom: 2rem; }
    .legal-section h2 { font-size: 1.25rem; font-weight: 700; color: #1e293b; margin: 1.5rem 0 .75rem; }
    .legal-section p, .legal-section li { font-size: 1rem; color: #475569; line-height: 1.75; }
    .legal-section ul { padding-left: 1.25rem; margin: .5rem 0; display: flex; flex-direction: column; gap: .35rem; }
    .data-card { border-radius: 12px; border: 1px solid; padding: 1rem 1.25rem; margin-bottom: 1rem; }
</style>
@endpush

@section('content')

@include('legal._header', [
    'icon'     => 'M3 3.5A1.5 1.5 0 0 1 4.5 2h6.879a1.5 1.5 0 0 1 1.06.44l2.122 2.12a1.5 1.5 0 0 1 .439 1.061V14.5A1.5 1.5 0 0 1 13.5 16h-9A1.5 1.5 0 0 1 3 14.5v-11Z',
    'title'    => 'Política de Uso de Datos',
    'subtitle' => 'Cómo utilizamos tu información en Mindra',
    'color'    => 'violet',
])

<div class="legal-body">

    <div class="legal-section">
        <h2>Tipos de datos y su uso específico</h2>

        <div class="data-card" style="background:#f0fdf4;border-color:#bbf7d0;">
            <p style="font-size:.75rem;font-weight:700;color:#15803d;text-transform:uppercase;letter-spacing:.06em;margin:0 0 6px;">Texto de conversación</p>
            <p style="font-size:.875rem;color:#166534;margin:0;line-height:1.6;">
                Se analiza mediante modelos de procesamiento de lenguaje natural para estimar el nivel de ansiedad. Se almacena vinculado a tu sesión para que puedas revisar tu historial. Se utiliza de forma anonimizada para mejorar continuamente la calidad del servicio.
            </p>
        </div>

        <div class="data-card" style="background:#eef2ff;border-color:#c7d2fe;">
            <p style="font-size:.75rem;font-weight:700;color:#4338ca;text-transform:uppercase;letter-spacing:.06em;margin:0 0 6px;">Grabaciones de voz</p>
            <p style="font-size:.875rem;color:#3730a3;margin:0;line-height:1.6;">
                Se transcriben automáticamente y el audio original se almacena de forma cifrada. La transcripción se procesa igual que el texto. Los archivos de audio no se escuchan por personas salvo en casos de auditoría técnica documentada.
            </p>
        </div>

        <div class="data-card" style="background:#fffbeb;border-color:#fde68a;">
            <p style="font-size:.75rem;font-weight:700;color:#b45309;text-transform:uppercase;letter-spacing:.06em;margin:0 0 6px;">Resultados de análisis</p>
            <p style="font-size:.875rem;color:#92400e;margin:0;line-height:1.6;">
                Las puntuaciones de ansiedad (0–100 %) se muestran en tu historial personal. De forma agregada y anonimizada, se utilizan para evaluar y mejorar el rendimiento de nuestros modelos de IA.
            </p>
        </div>

        <div class="data-card" style="background:#f8fafc;border-color:#e2e8f0;">
            <p style="font-size:.75rem;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.06em;margin:0 0 6px;">Metadatos de sesión</p>
            <p style="font-size:.875rem;color:#64748b;margin:0;line-height:1.6;">
                IP y agente de usuario se almacenan únicamente con fines de seguridad (detección de abuso, auditoría de acceso). No se emplean para perfilar usuarios ni para análisis de comportamiento.
            </p>
        </div>
    </div>

    <div class="legal-section">
        <h2>Lo que nunca haremos con tus datos</h2>
        <ul>
            <li>Vender, ceder o arrendar tu información a terceros.</li>
            <li>Utilizarlos con fines publicitarios o de marketing.</li>
            <li>Compartirlos con otras empresas o instituciones sin tu consentimiento explícito.</li>
            <li>Usarlos para tomar decisiones automatizadas con efectos legales o equivalentes sobre ti.</li>
            <li>Publicar contenido identificable de tus conversaciones en ningún medio.</li>
        </ul>
    </div>

    <div class="legal-section">
        <h2>Anonimización y mejora del servicio</h2>
        <p>Cuando los datos se utilizan para mejorar los modelos de IA, se aplican técnicas de anonimización (eliminación de identificadores directos, generalización, perturbación estadística) de modo que sea imposible vincularlos con una persona concreta.</p>
    </div>

    <div class="legal-section">
        <h2>Transferencias internacionales</h2>
        <p>Los modelos de IA se alojan en servidores seguros con ubicación controlada. En caso de utilizar servicios externos (p. ej., transcripción de voz en la nube), se garantizan cláusulas contractuales que aseguran un nivel de protección equivalente al exigido por la normativa aplicable.</p>
    </div>

    <div class="legal-section">
        <h2>Control de acceso</h2>
        <p>Solo el personal autorizado de Mindra bajo acuerdo de confidencialidad tiene acceso a los datos en bruto. Los accesos se registran en bitácora de auditoría y se revisan periódicamente.</p>
    </div>

</div>

@include('legal._footer_links')
@endsection
