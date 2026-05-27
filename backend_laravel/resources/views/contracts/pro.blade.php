@extends('contracts._layout', [
    'planSlug'  => 'pro',
    'planName'  => 'Pro',
    'planColor' => '#4f46e5',
    'planBadge' => '⭐ Plan Pro',
    'docTitle'  => 'Contrato de Suscripción Pro',
])

@section('contract-body')

{{-- 1. Partes --}}
<div class="section">
    <div class="section-title"><span class="section-num">1</span> Identificación de las partes</div>
    <div class="parties-grid">
        <div class="party-card">
            <p class="party-label">El Suscriptor</p>
            <p class="party-name">Persona física mayor de 18 años</p>
            <p class="party-detail">Identificado con el correo electrónico y los datos de pago registrados en MercadoPago. En adelante <strong>"el Suscriptor"</strong>.</p>
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
    <div class="section-title"><span class="section-num">2</span> Objeto del contrato</div>
    <p>El presente contrato regula las condiciones de la <strong>suscripción al Plan Pro</strong> de Mindra, que otorga al Suscriptor acceso a las funciones avanzadas de análisis de bienestar emocional durante el período de vigencia contratado.</p>
    <p>La suscripción se activa de forma automática una vez que el pago es acreditado por el procesador de pagos MercadoPago, y permanece activa hasta la fecha de vencimiento indicada en el perfil del Suscriptor.</p>
</div>

{{-- 3. Servicios incluidos --}}
<div class="section">
    <div class="section-title"><span class="section-num">3</span> Servicios incluidos en el Plan Pro</div>
    <table class="feature-table">
        <thead>
            <tr>
                <th>Función</th>
                <th>Disponibilidad</th>
            </tr>
        </thead>
        <tbody>
            <tr><td>Chat de texto con IA de bienestar</td><td class="check">✓ Incluido</td></tr>
            <tr><td>Chat por audio (transcripción y análisis de voz)</td><td class="check">✓ Incluido</td></tr>
            <tr><td>Análisis de ansiedad y etiquetas de emoción</td><td class="check">✓ Incluido</td></tr>
            <tr><td>Historial de sesiones (últimas 20)</td><td class="check">✓ Incluido</td></tr>
            <tr><td>Recomendaciones personalizadas</td><td class="check">✓ Incluido</td></tr>
            <tr><td>Acceso a la app móvil (iOS / Android)</td><td class="check">✓ Incluido</td></tr>
            <tr><td>Acceso a la versión web</td><td class="check">✓ Incluido</td></tr>
            <tr><td>Análisis facial en tiempo real</td><td class="cross">✗ No incluido (Plan Plus)</td></tr>
            <tr><td>Estadísticas avanzadas</td><td class="cross">✗ No incluido (Plan Plus)</td></tr>
            <tr><td>Reporte clínico PDF (30 días)</td><td class="cross">✗ No incluido (Plan Plus)</td></tr>
            <tr><td>Alertas automáticas de crisis por correo</td><td class="cross">✗ No incluido (Plan Plus)</td></tr>
            <tr><td>Análisis multimodal combinado</td><td class="cross">✗ No incluido (Plan Plus)</td></tr>
        </tbody>
    </table>
</div>

{{-- 4. Precio --}}
<div class="section">
    <div class="section-title"><span class="section-num">4</span> Precio y condiciones de pago</div>
    <table class="feature-table">
        <thead>
            <tr><th>Modalidad</th><th>Precio</th><th>Vigencia</th></tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Mensual</strong></td>
                <td>$149 MXN + IVA</td>
                <td>30 días desde la activación</td>
            </tr>
            <tr>
                <td><strong>Anual</strong></td>
                <td>$1,430 MXN + IVA</td>
                <td>365 días desde la activación (ahorro 20%)</td>
            </tr>
        </tbody>
    </table>
    <p>El pago se procesa a través de <strong>MercadoPago</strong>, aceptando tarjetas Visa, Mastercard, American Express, débito bancario, OXXO y transferencia SPEI.</p>
    <p><strong>La suscripción no se renueva automáticamente.</strong> Al vencerse el período, el Suscriptor vuelve al Plan Free. Para continuar en Plan Pro debe realizar un nuevo pago.</p>
    <p>Los precios están expresados en <strong>pesos mexicanos (MXN)</strong> e incluyen el impuesto al valor agregado (IVA) conforme a la legislación vigente.</p>
</div>

{{-- 5. Activación --}}
<div class="section">
    <div class="section-title"><span class="section-num">5</span> Activación del servicio</div>
    <p>El Plan Pro se activa dentro de los <strong>5 minutos</strong> siguientes a la confirmación del pago por MercadoPago. En caso de demora, el Suscriptor puede contactar a soporte en <strong>{{ config('mail.from.address', 'contacto@cafined.org') }}</strong>.</p>
    <p>La fecha de activación y vencimiento son visibles en todo momento en la sección <strong>"Mi plan"</strong> del perfil del usuario.</p>
</div>

{{-- 6. Obligaciones del suscriptor --}}
<div class="section">
    <div class="section-title"><span class="section-num">6</span> Obligaciones del Suscriptor</div>
    <ul>
        <li>Usar la suscripción de forma personal e intransferible. Está prohibido compartir la cuenta.</li>
        <li>No intentar descompilar, replicar ni distribuir el servicio o sus modelos de análisis.</li>
        <li>No usar el servicio para fines comerciales de reventa o para proyectos institucionales sin un plan adecuado.</li>
        <li>Mantener actualizados los datos de contacto y pago.</li>
        <li>Cumplir los <a href="{{ route('legal.terms') }}">Términos de Uso</a> y la <a href="{{ route('legal.privacy') }}">Política de Privacidad</a>.</li>
    </ul>
</div>

{{-- 7. Obligaciones de Mindra --}}
<div class="section">
    <div class="section-title"><span class="section-num">7</span> Obligaciones de Mindra</div>
    <ul>
        <li>Mantener disponibles todas las funciones del Plan Pro durante el período vigente.</li>
        <li>Notificar al Suscriptor por correo electrónico con al menos <strong>15 días de anticipación</strong> ante cambios de precio o eliminación de funciones.</li>
        <li>Proteger los datos personales e interacciones del Suscriptor conforme a la <a href="{{ route('legal.privacy') }}">Política de Privacidad</a>.</li>
        <li>Brindar soporte técnico por correo electrónico con tiempo de respuesta máximo de <strong>48 horas hábiles</strong>.</li>
    </ul>
</div>

{{-- 8. Política de cancelación y reembolsos --}}
<div class="section">
    <div class="section-title"><span class="section-num">8</span> Cancelación y política de reembolsos</div>
    <p>El Suscriptor puede cancelar su suscripción en cualquier momento desde su perfil o contactando a soporte. La cancelación entra en efecto al vencimiento del período actual; no se realizan reembolsos proporcionales por días no utilizados.</p>
    <p><strong>Excepción:</strong> Si el servicio presenta una interrupción total mayor a 72 horas continuas imputable a Mindra, el Suscriptor tendrá derecho a solicitar una extensión equivalente del período de suscripción o un reembolso proporcional.</p>
    <p>Los reembolsos, cuando apliquen, se procesan a través de MercadoPago en un plazo de <strong>5 a 10 días hábiles</strong>.</p>
</div>

{{-- 9. Datos personales --}}
<div class="section">
    <div class="section-title"><span class="section-num">9</span> Tratamiento de datos personales</div>
    <p>El Suscriptor acepta que Mindra procesa sus datos de sesión (texto, audio transcrito, análisis de ansiedad y emoción) para prestar el servicio y mejorar los modelos de análisis, conforme a la <a href="{{ route('legal.privacy') }}">Política de Privacidad</a>.</p>
    <p>Los datos pueden utilizarse de forma <strong>anonimizada</strong> para investigación académica en el Laboratorio CAFINED. El Suscriptor puede solicitar la eliminación de sus datos escribiendo a <strong>{{ config('mail.from.address', 'contacto@cafined.org') }}</strong>.</p>
</div>

{{-- 10. Limitación de responsabilidad --}}
<div class="section">
    <div class="section-title"><span class="section-num">10</span> Limitación de responsabilidad</div>
    <p>Mindra es una herramienta de apoyo al bienestar emocional. <strong>No emite diagnósticos clínicos ni sustituye la atención de un profesional de salud mental.</strong></p>
    <p>La responsabilidad máxima de Mindra ante el Suscriptor, por cualquier causa, está limitada al monto abonado en la suscripción durante el mes inmediato anterior al evento que genera la reclamación.</p>
</div>

{{-- 11. Propiedad intelectual --}}
<div class="section">
    <div class="section-title"><span class="section-num">11</span> Propiedad intelectual</div>
    <p>Todos los modelos de inteligencia artificial, algoritmos, interfaces y contenidos de la plataforma Mindra son propiedad exclusiva del Laboratorio CAFINED. La suscripción otorga una <strong>licencia de uso personal, no exclusiva e intransferible</strong> para acceder al servicio durante el período contratado.</p>
</div>

{{-- 12. Ley aplicable --}}
<div class="section">
    <div class="section-title"><span class="section-num">12</span> Ley aplicable y jurisdicción</div>
    <p>El presente contrato se rige por las leyes de los <strong>Estados Unidos Mexicanos</strong> y, en lo que corresponda, por la <em>Ley Federal de Protección de Datos Personales en Posesión de los Particulares</em>. Para cualquier controversia, las partes se someten a los tribunales competentes de <strong>Morelia, Michoacán, México</strong>.</p>
</div>

@endsection
