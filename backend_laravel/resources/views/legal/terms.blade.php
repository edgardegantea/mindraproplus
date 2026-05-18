@extends('layouts.app')
@section('title', 'Términos de Uso')

@push('styles')
<style>
    .legal-body { max-width: 48rem; margin-left: auto; margin-right: auto; }
    .legal-section { margin-bottom: 2rem; }
    .legal-section h2 { font-size: 1.25rem; font-weight: 700; color: #1e293b; margin: 1.5rem 0 .75rem; }
    .legal-section p, .legal-section li { font-size: 1rem; color: #475569; line-height: 1.75; }
    .legal-section ul { padding-left: 1.25rem; margin: .5rem 0; display: flex; flex-direction: column; gap: .35rem; }
    .warning-box { background: #fff7ed; border: 1px solid #fed7aa; border-radius: 12px; padding: 1rem 1.25rem; margin-bottom: 2rem; display: flex; gap: 12px; }
</style>
@endpush

@section('content')

@include('legal._header', [
    'icon'     => 'M4.75 2A2.75 2.75 0 0 0 2 4.75v10.5A2.75 2.75 0 0 0 4.75 18h10.5A2.75 2.75 0 0 0 18 15.25V4.75A2.75 2.75 0 0 0 15.25 2H4.75ZM10 6a.75.75 0 0 1 .75.75v3.5a.75.75 0 0 1-1.5 0v-3.5A.75.75 0 0 1 10 6Zm0 9a1 1 0 1 1 0-2 1 1 0 0 1 0 2Z',
    'title'    => 'Términos de Uso',
    'subtitle' => 'Condiciones para el uso de la plataforma Mindra',
    'color'    => 'slate',
])

<div class="legal-body">

    <div class="warning-box">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
             style="width:18px;height:18px;flex-shrink:0;color:#ea580c;margin-top:1px;">
            <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495ZM10 5a.75.75 0 0 1 .75.75v3.5a.75.75 0 0 1-1.5 0v-3.5A.75.75 0 0 1 10 5Zm0 9a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd"/>
        </svg>
        <div>
            <p style="font-size:.8125rem;font-weight:700;color:#c2410c;margin:0 0 4px;">Aviso importante</p>
            <p style="font-size:.8125rem;color:#9a3412;margin:0;line-height:1.6;">
                Mindra es una herramienta profesional de apoyo al bienestar emocional. <strong>No sustituye el diagnóstico, tratamiento ni atención de un profesional de salud mental.</strong> Si experimentas una crisis emocional grave, contacta a un servicio de salud mental o línea de emergencias.
            </p>
        </div>
    </div>

    <div class="legal-section">
        <h2>1. Aceptación de los términos</h2>
        <p>Al registrarte y utilizar Mindra aceptas estos Términos de Uso en su totalidad. Si no estás de acuerdo, no debes usar la plataforma. El uso continuado tras modificaciones implica aceptación de los nuevos términos.</p>
    </div>

    <div class="legal-section">
        <h2>2. Descripción del servicio</h2>
        <p>Mindra es una plataforma profesional de salud mental digital que utiliza inteligencia artificial para analizar patrones emocionales a través del texto y la voz. El servicio ofrece análisis de bienestar emocional, seguimiento de niveles de ansiedad y recomendaciones personalizadas, disponible en planes Free, Pro y Full (institucional).</p>
    </div>

    <div class="legal-section">
        <h2>3. Elegibilidad</h2>
        <p>Para usar Mindra debes:</p>
        <ul>
            <li>Tener 18 años o más, o contar con autorización de un tutor legal.</li>
            <li>Proporcionar información de registro veraz y actualizada.</li>
            <li>Usar la plataforma solo para los fines previstos de apoyo al bienestar emocional.</li>
        </ul>
    </div>

    <div class="legal-section">
        <h2>4. Uso permitido</h2>
        <ul>
            <li>Interactuar con Mindra de forma honesta para obtener análisis de bienestar personal.</li>
            <li>Revisar tu historial de sesiones y métricas de ansiedad.</li>
            <li>Utilizar las funcionalidades correspondientes a tu plan contratado.</li>
        </ul>
    </div>

    <div class="legal-section">
        <h2>5. Uso prohibido</h2>
        <ul>
            <li>Intentar vulnerar, manipular o hacer ingeniería inversa de los sistemas de Mindra.</li>
            <li>Introducir datos falsos o malintencionados con el fin de alterar el funcionamiento del servicio.</li>
            <li>Compartir credenciales de acceso con terceros.</li>
            <li>Utilizar la plataforma para actividades ilegales o contrarias a la ética profesional.</li>
            <li>Reproducir, distribuir o publicar el contenido de la plataforma sin autorización.</li>
        </ul>
    </div>

    <div class="legal-section">
        <h2>6. Planes y suscripciones</h2>
        <p>Mindra ofrece diferentes planes de servicio (Free, Pro, Full). Las condiciones específicas de cada plan, incluyendo precio, funcionalidades y límites, se detallan en la página de planes. Mindra se reserva el derecho de modificar los precios y características de los planes con previo aviso de 30 días.</p>
    </div>

    <div class="legal-section">
        <h2>7. Limitación de responsabilidad</h2>
        <p>Mindra no se responsabiliza de:</p>
        <ul>
            <li>Decisiones tomadas con base exclusiva en los resultados proporcionados por la plataforma.</li>
            <li>Interrupciones del servicio por mantenimiento programado o causas de fuerza mayor.</li>
            <li>Daños derivados del uso indebido de la plataforma.</li>
        </ul>
        <p style="margin-top:.75rem;">Los resultados de Mindra son orientativos y no constituyen diagnóstico clínico.</p>
    </div>

    <div class="legal-section">
        <h2>8. Propiedad intelectual</h2>
        <p>El código, diseño, modelos de IA, marca y contenido de Mindra son propiedad de sus titulares. Los datos que tú aportas son de tu propiedad; al utilizarlos otorgas una licencia no exclusiva para la prestación del servicio y la mejora de los modelos de IA en los términos descritos en la Política de Uso de Datos.</p>
    </div>

    <div class="legal-section">
        <h2>9. Terminación</h2>
        <p>Mindra puede suspender o eliminar cuentas que incumplan estos términos, previa notificación cuando sea posible. Puedes solicitar la cancelación de tu cuenta y la eliminación de tus datos en cualquier momento.</p>
    </div>

    <div class="legal-section">
        <h2>10. Legislación aplicable</h2>
        <p>Estos términos se rigen por la legislación mexicana vigente, incluyendo la Ley Federal de Protección de Datos Personales en Posesión de los Particulares. Cualquier controversia se resolverá en los tribunales competentes de México.</p>
    </div>

</div>

@include('legal._footer_links')
@endsection
