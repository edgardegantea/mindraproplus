@extends('contracts._layout', [
    'planSlug'  => 'plus',
    'planName'  => 'Plus',
    'planColor' => '#7c3aed',
    'planBadge' => '✦ Plan Plus',
    'docTitle'  => 'Contrato de Acceso Plus',
])

@section('contract-body')

{{-- 1. Partes --}}
<div class="section">
    <div class="section-title"><span class="section-num">1</span> Identificación de las partes</div>
    <div class="parties-grid">
        <div class="party-card">
            <p class="party-label">El Beneficiario</p>
            <p class="party-name">Investigador, clínico o institución autorizada</p>
            <p class="party-detail">Identificado con los datos proporcionados en el formulario de solicitud aprobado por Mindra. En adelante <strong>"el Beneficiario"</strong>.</p>
        </div>
        <div class="party-card">
            <p class="party-label">El Proveedor</p>
            <p class="party-name">Laboratorio CAFINED — Mindra</p>
            <p class="party-detail">Plataforma de bienestar emocional para investigación y uso clínico. Sitio: <strong>mindra.cafined.org</strong>. En adelante <strong>"Mindra"</strong>.</p>
        </div>
    </div>
    <p style="margin-top:14px;font-size:.875rem;background:#f5f3ff;border:1px solid #ddd6fe;border-radius:10px;padding:12px 14px;color:#5b21b6;">
        <strong>Nota:</strong> Las condiciones específicas de precio, número de usuarios, modalidad de uso y vigencia se establecen en la <strong>carta de aceptación</strong> emitida por Mindra al aprobar la solicitud del Beneficiario, la cual forma parte integral de este contrato.
    </p>
</div>

{{-- 2. Objeto --}}
<div class="section">
    <div class="section-title"><span class="section-num">2</span> Objeto del contrato</div>
    <p>El presente contrato regula el acceso al <strong>Plan Plus</strong> de Mindra, orientado a investigadores, clínicos y profesionales de la salud mental adscritos al Laboratorio CAFINED o instituciones aliadas, para fines de <strong>investigación académica, uso clínico supervisado o implementación institucional</strong>.</p>
    <p>El acceso se activa mediante solicitud formal aprobada por el equipo de Mindra, previo análisis del caso de uso, la institución y las necesidades del proyecto. No existe acceso automático al Plan Plus.</p>
</div>

{{-- 3. Servicios incluidos --}}
<div class="section">
    <div class="section-title"><span class="section-num">3</span> Servicios incluidos en el Plan Plus</div>
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
            <tr><td>Historial de sesiones completo e ilimitado</td><td class="check">✓ Incluido</td></tr>
            <tr><td>Análisis facial de emociones en tiempo real</td><td class="check">✓ Incluido</td></tr>
            <tr><td>Análisis multimodal combinado (texto + audio + imagen)</td><td class="check">✓ Incluido</td></tr>
            <tr><td>Estadísticas avanzadas y tendencias</td><td class="check">✓ Incluido</td></tr>
            <tr><td>Reporte clínico en PDF (últimos 30 días)</td><td class="check">✓ Incluido</td></tr>
            <tr><td>Alertas automáticas de crisis por correo electrónico</td><td class="check">✓ Incluido</td></tr>
            <tr><td>Soporte prioritario dedicado</td><td class="check">✓ Incluido</td></tr>
            <tr><td>Acceso a la app móvil (iOS / Android)</td><td class="check">✓ Incluido</td></tr>
            <tr><td>Acceso a la versión web</td><td class="check">✓ Incluido</td></tr>
        </tbody>
    </table>
    <p>El alcance exacto de las funciones habilitadas puede ser personalizado según el proyecto del Beneficiario, conforme a lo indicado en la carta de aceptación.</p>
</div>

{{-- 4. Precio --}}
<div class="section">
    <div class="section-title"><span class="section-num">4</span> Precio y condiciones económicas</div>
    <p>El precio del Plan Plus se determina <strong>caso por caso</strong> en función del tipo de institución, el número de usuarios, el período de acceso y el alcance del proyecto. Las condiciones económicas quedan establecidas en la <strong>carta de aceptación</strong> correspondiente.</p>
    <p>Para proyectos académicos y de investigación adscritos al Laboratorio CAFINED, el acceso puede ser otorgado <strong>sin costo</strong> a criterio del equipo de Mindra.</p>
    <p>Cualquier cobro acordado se facturará conforme a los datos de facturación proporcionados en la solicitud y el método de pago acordado entre las partes.</p>
</div>

{{-- 5. Confidencialidad --}}
<div class="section">
    <div class="section-title"><span class="section-num">5</span> Confidencialidad y datos clínicos</div>
    <p>Dada la naturaleza clínica y/o académica del Plan Plus, ambas partes se comprometen a:</p>
    <ul>
        <li><strong>Mindra:</strong> No divulgar ni compartir con terceros los datos de sesión, reportes clínicos ni resultados de análisis asociados al proyecto del Beneficiario sin su consentimiento expreso.</li>
        <li><strong>El Beneficiario:</strong> Usar los datos generados en la plataforma exclusivamente para los fines declarados en la solicitud aprobada (investigación, uso clínico o institucional).</li>
        <li>Ambas partes guardarán la confidencialidad sobre los términos económicos acordados.</li>
        <li>Esta cláusula de confidencialidad permanece vigente por <strong>3 años</strong> posteriores a la terminación del contrato.</li>
    </ul>
</div>

{{-- 6. Uso ético y responsabilidad --}}
<div class="section">
    <div class="section-title"><span class="section-num">6</span> Uso ético y responsabilidad del Beneficiario</div>
    <p>El Beneficiario se compromete a:</p>
    <ul>
        <li>Obtener el <strong>consentimiento informado</strong> de cada usuario final antes de utilizar Mindra en el marco de su proyecto.</li>
        <li>Informar a los usuarios finales sobre el uso de inteligencia artificial en el análisis de sus respuestas.</li>
        <li>No utilizar los resultados de Mindra como único criterio diagnóstico. Los resultados deben ser revisados e interpretados por un profesional de salud mental calificado.</li>
        <li>Cumplir con los protocolos éticos de su institución y las normativas de investigación con seres humanos aplicables (Declaración de Helsinki, NOM-012-SSA3-2012, entre otras).</li>
        <li>Reportar de inmediato a Mindra cualquier situación de crisis detectada en un usuario final que requiera intervención urgente.</li>
    </ul>
</div>

{{-- 7. Obligaciones de Mindra --}}
<div class="section">
    <div class="section-title"><span class="section-num">7</span> Obligaciones de Mindra</div>
    <ul>
        <li>Garantizar el acceso a todas las funciones del Plan Plus durante el período acordado.</li>
        <li>Asignar un contacto dedicado de soporte con tiempo de respuesta máximo de <strong>8 horas hábiles</strong>.</li>
        <li>Notificar al Beneficiario con al menos <strong>30 días de anticipación</strong> ante cualquier cambio sustancial en el servicio.</li>
        <li>Emitir los reportes clínicos en PDF solicitados dentro del plazo de <strong>24 horas hábiles</strong>.</li>
        <li>Mantener la integridad y respaldo de los datos del proyecto durante el período contractual y por <strong>12 meses</strong> adicionales después del vencimiento.</li>
    </ul>
</div>

{{-- 8. Vigencia --}}
<div class="section">
    <div class="section-title"><span class="section-num">8</span> Vigencia y renovación</div>
    <p>La vigencia del Plan Plus se establece en la carta de aceptación, con duraciones típicas de <strong>3, 6 o 12 meses</strong> según el proyecto.</p>
    <p>La renovación debe ser solicitada con al menos <strong>15 días de anticipación</strong> antes del vencimiento. Mindra se reserva el derecho de revisar las condiciones al momento de la renovación.</p>
    <p>Al término del contrato, los datos del proyecto permanecen accesibles en modo lectura por <strong>30 días adicionales</strong> para permitir la exportación.</p>
</div>

{{-- 9. Terminación anticipada --}}
<div class="section">
    <div class="section-title"><span class="section-num">9</span> Terminación anticipada</div>
    <p>Cualquiera de las partes puede rescindir el contrato con <strong>15 días de aviso por escrito</strong>. En caso de incumplimiento grave (uso no autorizado, violación de ética de investigación, impago), Mindra puede rescindir de forma inmediata.</p>
    <p>En caso de rescisión por parte de Mindra sin causa imputable al Beneficiario, se reembolsará el monto proporcional al período no utilizado.</p>
</div>

{{-- 10. Datos personales e investigación --}}
<div class="section">
    <div class="section-title"><span class="section-num">10</span> Datos personales y uso académico</div>
    <p>Los datos generados en el marco del Plan Plus son propiedad del Beneficiario para los fines de su proyecto. Mindra los utiliza únicamente para prestar el servicio y, de forma anonimizada, para el desarrollo y mejora de sus modelos de análisis en el Laboratorio CAFINED.</p>
    <p>El Beneficiario puede solicitar la exportación completa de los datos de su proyecto en cualquier momento. Los datos exportados se entregán en formato JSON o CSV.</p>
    <p>El tratamiento de datos cumple con la <em>Ley Federal de Protección de Datos Personales en Posesión de los Particulares</em> (LFPDPPP) y las normativas de ética en investigación aplicables.</p>
</div>

{{-- 11. Propiedad intelectual y publicaciones --}}
<div class="section">
    <div class="section-title"><span class="section-num">11</span> Propiedad intelectual y publicaciones</div>
    <p>La plataforma, modelos y algoritmos de Mindra son propiedad exclusiva del Laboratorio CAFINED. El Beneficiario no adquiere derechos sobre dichos activos.</p>
    <p>Los resultados de investigación generados a partir del uso de Mindra son propiedad del Beneficiario. Se solicita que en publicaciones académicas se incluya el siguiente crédito:</p>
    <blockquote style="background:#f5f3ff;border-left:4px solid #7c3aed;padding:12px 16px;border-radius:0 8px 8px 0;margin:10px 0;font-style:italic;font-size:.875rem;color:#4c1d95;">
        "Se utilizó la plataforma Mindra (Laboratorio CAFINED) para el análisis de bienestar emocional."
    </blockquote>
    <p>Mindra puede citar al Beneficiario como institución aliada en materiales de comunicación, previo consentimiento.</p>
</div>

{{-- 12. Limitación de responsabilidad --}}
<div class="section">
    <div class="section-title"><span class="section-num">12</span> Limitación de responsabilidad</div>
    <p>Mindra es una herramienta de apoyo al bienestar emocional y análisis académico. <strong>No emite diagnósticos clínicos.</strong> La responsabilidad sobre el uso clínico recae íntegramente en el Beneficiario y los profesionales a su cargo.</p>
    <p>La responsabilidad máxima de Mindra ante el Beneficiario queda limitada al monto abonado por la suscripción durante el semestre inmediato anterior al evento que genera la reclamación.</p>
</div>

{{-- 13. Ley aplicable --}}
<div class="section">
    <div class="section-title"><span class="section-num">13</span> Ley aplicable y jurisdicción</div>
    <p>El presente contrato se rige por las leyes de los <strong>Estados Unidos Mexicanos</strong>, incluyendo la <em>Ley Federal de Protección de Datos Personales en Posesión de los Particulares</em> y la <em>Ley Federal del Derecho de Autor</em>. Para cualquier controversia las partes se someten a los tribunales de <strong>Morelia, Michoacán, México</strong>.</p>
</div>

@endsection
