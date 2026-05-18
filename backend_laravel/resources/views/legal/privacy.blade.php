@extends('layouts.app')
@section('title', 'Política de Privacidad')

@push('styles')
<style>
    .legal-body { max-width: 48rem; margin-left: auto; margin-right: auto; }
    .legal-section { margin-bottom: 2rem; }
    .legal-section h2 { font-size: 1.25rem; font-weight: 700; color: #1e293b; margin: 1.5rem 0 .75rem; }
    .legal-section p, .legal-section li { font-size: 1rem; color: #475569; line-height: 1.75; }
    .legal-section ul { padding-left: 1.25rem; margin: .5rem 0; display: flex; flex-direction: column; gap: .35rem; }
    .legal-highlight { background: #eef2ff; border: 1px solid #c7d2fe; border-radius: 12px; padding: 1rem 1.25rem; margin-bottom: 2rem; }
    .legal-highlight p { font-size: .875rem; color: #3730a3; line-height: 1.65; margin: 0; }
</style>
@endpush

@section('content')

@include('legal._header', [
    'icon'     => 'M10 1a4.5 4.5 0 0 0-4.5 4.5V9H5a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2h-.5V5.5A4.5 4.5 0 0 0 10 1Zm3 8V5.5a3 3 0 1 0-6 0V9h6Z',
    'title'    => 'Política de Privacidad',
    'subtitle' => 'Última actualización: ' . date('d/m/Y'),
    'color'    => 'indigo',
])

<div class="legal-body">

    <div class="legal-highlight">
        <p>En Mindra, la privacidad y seguridad de tus datos son nuestra prioridad. Esta política explica de forma transparente cómo recopilamos, usamos y protegemos tu información personal.</p>
    </div>

    <div class="legal-section">
        <h2>1. Responsable del tratamiento</h2>
        <p>El responsable del tratamiento de datos es <strong>Mindra</strong>, plataforma profesional de bienestar emocional asistida por inteligencia artificial, operada por el Laboratorio de Computación Afectiva e Innovación Educativa (CAFINED).</p>
    </div>

    <div class="legal-section">
        <h2>2. Datos que recopilamos</h2>
        <ul>
            <li><strong>Datos de registro:</strong> nombre y dirección de correo electrónico, necesarios para crear tu cuenta.</li>
            <li><strong>Texto e interacciones:</strong> los mensajes de texto que escribes durante el chat con Mindra.</li>
            <li><strong>Grabaciones de audio:</strong> cuando utilizas la función de voz, se captura el audio para transcripción y análisis.</li>
            <li><strong>Metadatos de uso:</strong> fecha y hora de las sesiones, dirección IP y agente de usuario (navegador), con fines de seguridad y calidad del servicio.</li>
            <li><strong>Resultados de análisis:</strong> puntuaciones de probabilidad de ansiedad generadas automáticamente por nuestros modelos de IA.</li>
        </ul>
    </div>

    <div class="legal-section">
        <h2>3. Finalidad del tratamiento</h2>
        <ul>
            <li>Proveer el servicio de apoyo emocional y análisis de bienestar de Mindra.</li>
            <li>Mejorar continuamente la precisión y calidad de nuestros modelos de inteligencia artificial.</li>
            <li>Generar reportes agregados y anonimizados para clientes institucionales del plan Full.</li>
            <li>Garantizar la seguridad, integridad y correcto funcionamiento de la plataforma.</li>
        </ul>
    </div>

    <div class="legal-section">
        <h2>4. Base legal</h2>
        <p>El tratamiento se basa en el <strong>consentimiento informado</strong> que otorgas al registrarte y aceptar estos términos, así como en el <strong>interés legítimo</strong> de Mindra para la prestación y mejora del servicio, conforme a la Ley Federal de Protección de Datos Personales en Posesión de los Particulares (LFPDPPP) y normativa aplicable.</p>
    </div>

    <div class="legal-section">
        <h2>5. Confidencialidad y seguridad</h2>
        <p>Tus datos se almacenan en servidores con acceso restringido y medidas de seguridad de nivel empresarial. No se venden, arriendan ni comparten con terceros con fines comerciales. Se aplican medidas técnicas (cifrado en tránsito y reposo, control de acceso basado en roles, monitoreo de seguridad) para proteger la integridad de la información.</p>
    </div>

    <div class="legal-section">
        <h2>6. Período de conservación</h2>
        <p>Los datos se conservarán mientras tu cuenta esté activa y durante un período de <strong>2 años</strong> posteriores a la cancelación de la misma, salvo obligación legal que exija un plazo mayor. Transcurrido ese período, los datos serán eliminados o anonimizados de forma irreversible.</p>
    </div>

    <div class="legal-section">
        <h2>7. Tus derechos</h2>
        <p>Puedes ejercer en cualquier momento los siguientes derechos (derechos ARCO):</p>
        <ul>
            <li><strong>Acceso:</strong> conocer qué datos personales tenemos sobre ti.</li>
            <li><strong>Rectificación:</strong> corregir datos inexactos o incompletos.</li>
            <li><strong>Cancelación:</strong> solicitar la eliminación de tus datos ("derecho al olvido").</li>
            <li><strong>Oposición:</strong> oponerte al uso de tus datos para determinados fines.</li>
            <li><strong>Portabilidad:</strong> recibir tus datos en formato estructurado y legible.</li>
        </ul>
        <p style="margin-top:.75rem;">Para ejercer estos derechos, contacta a nuestro equipo a través de <strong>cafined@itsm.edu.mx</strong>.</p>
    </div>

    <div class="legal-section">
        <h2>8. Cambios en esta política</h2>
        <p>Cualquier modificación sustancial será notificada mediante aviso en la plataforma con al menos 15 días de antelación. El uso continuado de Mindra tras ese plazo implica aceptación de los cambios.</p>
    </div>

</div>

@include('legal._footer_links')
@endsection
