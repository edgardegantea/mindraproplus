@extends('contracts._layout', [
    'planSlug'  => 'free',
    'planName'  => 'Free',
    'planColor' => '#64748b',
    'planBadge' => '🆓 Plan Free',
    'docTitle'  => 'Acuerdo de Servicio Gratuito',
])

@section('contract-body')

{{-- 1. Partes --}}
<div class="section">
    <div class="section-title"><span class="section-num">1</span> Identificación de las partes</div>
    <div class="parties-grid">
        <div class="party-card">
            <p class="party-label">El Usuario</p>
            <p class="party-name">Persona física mayor de 18 años</p>
            <p class="party-detail">Identificado con el correo electrónico registrado en la plataforma. En adelante <strong>"el Usuario"</strong>.</p>
        </div>
        <div class="party-card">
            <p class="party-label">El Proveedor</p>
            <p class="party-name">Laboratorio CAFINED — Mindra</p>
            <p class="party-detail">Plataforma de bienestar emocional. Sitio: <strong>mindra.cafined.org</strong>. En adelante <strong>"Mindra"</strong>.</p>
        </div>
    </div>
</div>

{{-- 2. Objeto --}}
<div class="section">
    <div class="section-title"><span class="section-num">2</span> Objeto del acuerdo</div>
    <p>El presente acuerdo regula el acceso y uso del <strong>Plan Free</strong> de Mindra, un servicio de acceso gratuito e ilimitado en el tiempo que permite al Usuario interactuar con la plataforma de bienestar emocional bajo las condiciones aquí descritas.</p>
    <p>La activación del Plan Free se produce de forma automática al completar el registro de cuenta en Mindra, sin requerir ningún pago ni compromiso económico.</p>
</div>

{{-- 3. Servicios incluidos --}}
<div class="section">
    <div class="section-title"><span class="section-num">3</span> Servicios incluidos en el Plan Free</div>
    <table class="feature-table">
        <thead>
            <tr>
                <th>Función</th>
                <th>Disponibilidad</th>
            </tr>
        </thead>
        <tbody>
            <tr><td>Chat de texto con IA de bienestar</td><td class="check">✓ Incluido</td></tr>
            <tr><td>Detección básica de niveles de ansiedad</td><td class="check">✓ Incluido</td></tr>
            <tr><td>Acceso a la app móvil (iOS / Android)</td><td class="check">✓ Incluido</td></tr>
            <tr><td>Acceso a la versión web</td><td class="check">✓ Incluido</td></tr>
            <tr><td>Chat por audio (voz)</td><td class="check">✓ Incluido</td></tr>
            <tr><td>Análisis de emociones detallado</td><td class="cross">✗ No incluido</td></tr>
            <tr><td>Historial de sesiones</td><td class="cross">✗ No incluido</td></tr>
            <tr><td>Análisis facial en tiempo real</td><td class="cross">✗ No incluido</td></tr>
            <tr><td>Estadísticas avanzadas</td><td class="cross">✗ No incluido</td></tr>
            <tr><td>Reporte clínico PDF</td><td class="cross">✗ No incluido</td></tr>
            <tr><td>Alertas automáticas de crisis</td><td class="cross">✗ No incluido</td></tr>
        </tbody>
    </table>
</div>

{{-- 4. Precio --}}
<div class="section">
    <div class="section-title"><span class="section-num">4</span> Precio y condiciones económicas</div>
    <p>El Plan Free es completamente <strong>gratuito</strong>. Mindra no solicitará ningún dato de pago ni realizará cobro alguno por el acceso a las funciones incluidas en este plan.</p>
    <p>Mindra se reserva el derecho de modificar las funciones disponibles en el Plan Free con un aviso previo de al menos <strong>30 días</strong> mediante notificación al correo registrado o aviso en la plataforma.</p>
</div>

{{-- 5. Obligaciones del usuario --}}
<div class="section">
    <div class="section-title"><span class="section-num">5</span> Obligaciones del Usuario</div>
    <ul>
        <li>Usar la plataforma de forma personal, no transferible y para fines lícitos de bienestar emocional.</li>
        <li>No intentar acceder a funciones reservadas a planes de pago sin la suscripción correspondiente.</li>
        <li>No compartir credenciales de acceso con terceros.</li>
        <li>No utilizar la plataforma para fines comerciales, de investigación o institucionales sin contar con el plan adecuado.</li>
        <li>Mantener actualizada la información de contacto (correo electrónico).</li>
        <li>Aceptar y cumplir los <a href="{{ route('legal.terms') }}">Términos de Uso</a> y la <a href="{{ route('legal.privacy') }}">Política de Privacidad</a> de Mindra.</li>
    </ul>
</div>

{{-- 6. Obligaciones de Mindra --}}
<div class="section">
    <div class="section-title"><span class="section-num">6</span> Obligaciones de Mindra</div>
    <ul>
        <li>Mantener disponible el servicio con un esfuerzo razonable de continuidad, sin garantizar disponibilidad ininterrumpida.</li>
        <li>Notificar cambios significativos en las condiciones del plan con al menos 30 días de anticipación.</li>
        <li>Proteger los datos personales del Usuario conforme a la <a href="{{ route('legal.privacy') }}">Política de Privacidad</a> y la normativa aplicable.</li>
        <li>No comercializar datos personales identificables con terceros.</li>
    </ul>
</div>

{{-- 7. Vigencia y cancelación --}}
<div class="section">
    <div class="section-title"><span class="section-num">7</span> Vigencia y cancelación</div>
    <p>El presente acuerdo entra en vigor en el momento del registro de la cuenta y permanece vigente de forma indefinida mientras el Usuario mantenga activa su cuenta en Mindra.</p>
    <p>El Usuario puede cancelar su cuenta en cualquier momento desde la plataforma o enviando un correo a <strong>{{ config('mail.from.address', 'contacto@cafined.org') }}</strong>, sin penalización alguna. La cancelación implica la eliminación de los datos asociados conforme a la política de retención de datos.</p>
    <p>Mindra puede suspender o cancelar una cuenta en caso de incumplimiento de los términos de uso, notificando al Usuario por correo electrónico con al menos <strong>5 días hábiles</strong> de anticipación, salvo en casos de uso ilícito o fraude.</p>
</div>

{{-- 8. Datos personales --}}
<div class="section">
    <div class="section-title"><span class="section-num">8</span> Tratamiento de datos personales</div>
    <p>El Usuario acepta que Mindra procesa sus datos personales (correo, interacciones de chat, datos de sesión) con el fin de prestar el servicio y mejorar los modelos de análisis de bienestar emocional, conforme a la <a href="{{ route('legal.privacy') }}">Política de Privacidad</a>.</p>
    <p>Los datos de interacción pueden ser utilizados de forma anonimizada para investigación académica en el marco del Laboratorio CAFINED, sin que sea posible identificar al Usuario de manera individual.</p>
</div>

{{-- 9. Limitación de responsabilidad --}}
<div class="section">
    <div class="section-title"><span class="section-num">9</span> Limitación de responsabilidad</div>
    <p>Mindra es una herramienta de apoyo al bienestar emocional. <strong>No es un dispositivo médico, no emite diagnósticos clínicos y no sustituye la atención de un profesional de salud mental.</strong></p>
    <p>Mindra no asume responsabilidad por decisiones tomadas por el Usuario basándose exclusivamente en los análisis o recomendaciones de la plataforma. El servicio se provee "tal cual" (<em>as is</em>) sin garantía de exactitud clínica.</p>
</div>

{{-- 10. Ley aplicable --}}
<div class="section">
    <div class="section-title"><span class="section-num">10</span> Ley aplicable y jurisdicción</div>
    <p>El presente acuerdo se rige por las leyes de los <strong>Estados Unidos Mexicanos</strong>. Para cualquier controversia, las partes se someten a la jurisdicción de los tribunales competentes de <strong>Morelia, Michoacán, México</strong>, renunciando a cualquier otro fuero que pudiera corresponderles.</p>
</div>

@endsection
