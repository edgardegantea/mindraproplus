@extends('layouts.app')
@section('title', 'Consentimiento Informado')

@push('styles')
<style>
    .legal-body { max-width: 48rem; margin-left: auto; margin-right: auto; }
    .legal-section { margin-bottom: 2rem; }
    .legal-section h2 { font-size: 1.25rem; font-weight: 700; color: #1e293b; margin: 1.5rem 0 .75rem; }
    .legal-section p, .legal-section li { font-size: 1rem; color: #475569; line-height: 1.75; }
    .legal-section ul { padding-left: 1.25rem; margin: .5rem 0; display: flex; flex-direction: column; gap: .35rem; }
    .consent-check { display: flex; align-items: flex-start; gap: 10px; padding: .75rem 1rem; border-radius: 10px; background: #f0fdf4; border: 1px solid #bbf7d0; margin-bottom: .5rem; }
    .consent-check svg { flex-shrink: 0; width: 16px; height: 16px; color: #16a34a; margin-top: 1px; }
    .consent-check p { font-size: .8125rem; color: #15803d; margin: 0; line-height: 1.55; }
</style>
@endpush

@section('content')

@include('legal._header', [
    'icon'     => 'M10 2a8 8 0 1 0 0 16A8 8 0 0 0 10 2Zm3.707 6.293a1 1 0 0 0-1.414-1.414L9 10.172 7.707 8.879a1 1 0 0 0-1.414 1.414l2 2a1 1 0 0 0 1.414 0l4-4Z',
    'title'    => 'Consentimiento Informado',
    'subtitle' => 'Autorización para el uso del servicio de análisis emocional',
    'color'    => 'emerald',
])

<div class="legal-body">

    <div class="legal-section">
        <h2>Acerca de Mindra</h2>
        <p><strong>Mindra</strong> es una plataforma profesional de salud mental digital que utiliza modelos de inteligencia artificial para analizar indicadores de ansiedad a partir del texto escrito y la voz, brindando apoyo emocional accesible y personalizado.</p>
    </div>

    <div class="legal-section">
        <h2>Propósito del servicio</h2>
        <p>Mindra tiene como objetivo ofrecer a sus usuarios una herramienta de apoyo emocional basada en IA, capaz de identificar patrones de ansiedad y proporcionar retroalimentación orientativa. El servicio contribuye a la detección temprana y al seguimiento del bienestar emocional.</p>
    </div>

    <div class="legal-section">
        <h2>Uso voluntario</h2>
        <p>El uso de Mindra es <strong>completamente voluntario</strong>. Puedes dejar de utilizar el servicio en cualquier momento sin necesidad de justificación y sin que ello tenga ninguna consecuencia negativa para ti. Solicitar la eliminación de tus datos no afectará tu acceso a la plataforma.</p>
    </div>

    <div class="legal-section">
        <h2>Qué implica usar Mindra</h2>
        <ul>
            <li>Interactuar con la plataforma mediante texto y/o audio de voz.</li>
            <li>Permitir el procesamiento automático de tus mensajes para estimar indicadores de ansiedad.</li>
            <li>Que tus datos sean procesados por modelos de IA para generar análisis personalizados.</li>
            <li>Que datos anonimizados puedan utilizarse para mejorar la calidad del servicio.</li>
        </ul>
    </div>

    <div class="legal-section">
        <h2>Consideraciones importantes</h2>
        <p><strong>Importante:</strong> el análisis emocional puede generar reflexiones que produzcan malestar temporal. Si experimentas angustia significativa, te recomendamos buscar apoyo de un profesional de salud mental. Mindra no sustituye la atención clínica profesional.</p>
        <p style="margin-top:.5rem;"><strong>Beneficios:</strong> acceso a un historial personal de bienestar emocional, retroalimentación orientativa sobre niveles de ansiedad, recomendaciones personalizadas y seguimiento de tu evolución emocional en el tiempo.</p>
    </div>

    <div class="legal-section">
        <h2>Confidencialidad</h2>
        <p>Tu identidad y datos personales se manejan con estricta confidencialidad. Tus conversaciones son privadas y están protegidas mediante cifrado. El acceso a datos identificables está restringido exclusivamente al personal autorizado de Mindra bajo acuerdo de confidencialidad.</p>
    </div>

    <div class="legal-section">
        <h2>Tus derechos como usuario</h2>
        <div class="consent-check">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd"/></svg>
            <p>Derecho a revocar tu consentimiento y dejar de usar el servicio en cualquier momento.</p>
        </div>
        <div class="consent-check">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd"/></svg>
            <p>Derecho a acceder, rectificar y eliminar tus datos personales.</p>
        </div>
        <div class="consent-check">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd"/></svg>
            <p>Derecho a solicitar la eliminación completa de tu cuenta y todos tus datos.</p>
        </div>
        <div class="consent-check">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd"/></svg>
            <p>Derecho a recibir soporte y atención sobre cualquier duda relacionada con el servicio.</p>
        </div>
    </div>

    <div class="legal-section">
        <h2>Manifestación del consentimiento</h2>
        <p>Al crear una cuenta en Mindra y utilizar la plataforma, manifiestas que:</p>
        <ul>
            <li>Has leído y comprendido este documento de consentimiento informado.</li>
            <li>Tienes al menos 18 años de edad o cuentas con autorización de un tutor legal.</li>
            <li>Utilizas el servicio de forma voluntaria y sin coacción.</li>
            <li>Autorizas el procesamiento de tus datos para la prestación del servicio en los términos descritos.</li>
        </ul>
    </div>

    <div class="legal-section">
        <h2>Contacto</h2>
        <p>Para preguntas sobre el servicio, para ejercer tus derechos o para cualquier consulta, contacta a nuestro equipo en <strong>cafined@itsm.edu.mx</strong>.</p>
    </div>

</div>

@include('legal._footer_links')
@endsection
