@extends('layouts.app')

@section('title', 'Plan Plus — Solicitud de acceso')

@push('styles')
<style>
    .plus-page { max-width: 82rem; margin: 0 auto; display: grid; grid-template-columns: 3fr 2fr; gap: 36px; align-items: start; padding: 56px 1.5rem 80px; }
    @media(max-width:900px){ .plus-page{grid-template-columns:1fr;} .summary-card{order:-1;position:static;} }
    .form-card { background:var(--bg-card,#fff); border-radius:24px; border:1px solid var(--border,#e8edf5); padding:40px; box-shadow:0 4px 24px rgba(0,0,0,.04); }
    .summary-card { background:linear-gradient(160deg,#f5f3ff,var(--bg-card,#fff)); border-radius:24px; border:2px solid #7c3aed; padding:32px; position:sticky; top:88px; }
    .section-title { font-size:.75rem; font-weight:700; text-transform:uppercase; letter-spacing:.08em; color:#7c3aed; border-bottom:2px solid #ede9fe; padding-bottom:8px; margin:28px 0 18px; display:flex; align-items:center; gap:8px; }
    .section-title:first-child { margin-top:0; }
    .section-num { width:22px; height:22px; border-radius:50%; background:#7c3aed; color:#fff; font-size:.6875rem; font-weight:800; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
    .form-grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
    .form-grid-3 { display:grid; grid-template-columns:2fr 1fr 1fr; gap:16px; }
    @media(max-width:600px){ .form-grid-2,.form-grid-3{grid-template-columns:1fr;} }
    .form-group { display:flex; flex-direction:column; gap:6px; }
    .form-group label { font-size:.8125rem; font-weight:600; color:var(--text,#0f172a); }
    .form-group label .req { color:#ef4444; margin-left:2px; }
    .form-group label .opt { font-size:.6875rem; font-weight:400; color:#94a3b8; margin-left:4px; }
    .form-group input, .form-group textarea, .form-group select {
        padding:10px 13px; border-radius:10px; border:1.5px solid var(--border,#e2e8f0);
        background:var(--bg,#f8fafc); color:var(--text,#0f172a); font-size:.9375rem;
        outline:none; transition:border-color .2s; font-family:inherit; width:100%; box-sizing:border-box;
    }
    .form-group input:focus, .form-group textarea:focus, .form-group select:focus { border-color:#7c3aed; background:#fff; }
    .form-group textarea { min-height:100px; resize:vertical; }
    .form-group .error-msg { font-size:.75rem; color:#ef4444; }
    .form-group .hint { font-size:.6875rem; color:#94a3b8; margin-top:2px; }
    .btn-plus { width:100%; padding:15px 24px; border-radius:12px; border:none; cursor:pointer; font-size:1rem; font-weight:700; color:#fff; background:linear-gradient(135deg,#7c3cc8,#3c14b4); box-shadow:0 4px 14px rgba(124,60,200,.35); transition:opacity .2s; font-family:inherit; margin-top:8px; }
    .btn-plus:hover { opacity:.88; }
    .feat-item { display:flex; align-items:center; gap:10px; padding:7px 0; font-size:.875rem; }
    .feat-icon { width:18px; height:18px; color:#7c3aed; flex-shrink:0; }
    .badge-plus { display:inline-block; padding:4px 12px; border-radius:999px; font-size:.75rem; font-weight:700; background:rgba(124,58,237,.12); color:#7c3aed; }
    .success-card { background:#f0fdf4; border:1.5px solid #bbf7d0; border-radius:16px; padding:28px; text-align:center; }
    .state-wrapper { display:none; }
    .state-wrapper.visible { display:flex; flex-direction:column; gap:6px; }
</style>
@endpush

@section('content')
<div class="plus-page">

    {{-- ── Formulario ────────────────────────────────────────────────── --}}
    <div class="form-card">
        <div style="margin-bottom:8px;">
            <span class="badge-plus">✦ Plan Plus</span>
        </div>
        <h1 style="font-size:1.65rem;font-weight:800;margin:8px 0 6px;">Solicitar acceso</h1>
        <p style="color:var(--text-muted,#64748b);font-size:.9375rem;line-height:1.6;margin:0 0 16px;">
            Completa el formulario y te contactaremos en menos de <strong>24 horas hábiles</strong>.
        </p>

        @if(session('success'))
        <div class="success-card" style="margin-top:20px;">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#16a34a" style="width:40px;height:40px;margin:0 auto 10px;display:block;"><path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm13.36-1.814a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094l3.75-5.25Z" clip-rule="evenodd"/></svg>
            <p style="font-weight:700;font-size:1.05rem;color:#15803d;margin:0 0 4px;">¡Solicitud enviada!</p>
            <p style="color:#166534;font-size:.9rem;margin:0;">{{ session('success') }}</p>
        </div>
        @else

        @if($errors->any())
        <div style="background:#fef2f2;border:1.5px solid #fecaca;border-radius:12px;padding:16px;margin-top:16px;color:#dc2626;font-size:.875rem;">
            <ul style="margin:0;padding-left:18px;">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul>
        </div>
        @endif

        <form action="{{ route('plans.plus.submit') }}" method="POST" novalidate>
            @csrf

            {{-- ── 1. Datos del solicitante ─────────────────────────── --}}
            <div class="section-title">
                <span class="section-num">1</span> Datos del solicitante
            </div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label>Nombre completo <span class="req">*</span></label>
                    <input type="text" name="requester_name" value="{{ old('requester_name', auth()->user()?->name) }}" placeholder="Tu nombre completo" required autocomplete="name">
                    @error('requester_name')<span class="error-msg">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label>Cargo / Puesto <span class="opt">(opcional)</span></label>
                    <input type="text" name="requester_position" value="{{ old('requester_position') }}" placeholder="Ej. Director de TI, Investigador…">
                </div>
                <div class="form-group">
                    <label>Correo electrónico <span class="req">*</span></label>
                    <input type="email" name="requester_email" value="{{ old('requester_email', auth()->user()?->email) }}" placeholder="correo@institución.edu" required autocomplete="email">
                    @error('requester_email')<span class="error-msg">{{ $message }}</span>@enderror
                    <span class="hint">Recibirás una copia de tu solicitud aquí.</span>
                </div>
                <div class="form-group">
                    <label>Teléfono / WhatsApp <span class="opt">(opcional)</span></label>
                    <input type="tel" name="requester_phone" value="{{ old('requester_phone') }}" placeholder="+52 800 000 0000" autocomplete="tel">
                </div>
            </div>

            {{-- ── 2. Datos de la institución ───────────────────────── --}}
            <div class="section-title">
                <span class="section-num">2</span> Institución / empresa
            </div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label>Nombre de la institución <span class="req">*</span></label>
                    <input type="text" name="org_name" value="{{ old('org_name') }}" placeholder="Nombre oficial" required>
                    @error('org_name')<span class="error-msg">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label>Tipo de institución <span class="req">*</span></label>
                    <select name="org_type" required>
                        <option value="" disabled {{ old('org_type') ? '' : 'selected' }}>Selecciona…</option>
                        @foreach(\App\Support\PlusRequestHelper::$orgTypes as $val => $label)
                            <option value="{{ $val }}" {{ old('org_type') === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('org_type')<span class="error-msg">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label>Giro / Sector <span class="opt">(opcional)</span></label>
                    <input type="text" name="org_sector" value="{{ old('org_sector') }}" placeholder="Ej. Salud mental, Educación superior…">
                </div>
                <div class="form-group">
                    <label>Sitio web <span class="opt">(opcional)</span></label>
                    <input type="url" name="org_website" value="{{ old('org_website') }}" placeholder="https://…">
                </div>
            </div>

            {{-- Dirección --}}
            <div style="background:#f8fafc;border-radius:14px;border:1px solid #e2e8f0;padding:20px;margin-top:8px;">
                <div style="font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin-bottom:16px;">Dirección de la institución</div>

                {{-- País --}}
                <div class="form-group" style="margin-bottom:14px;">
                    <label>País <span class="req">*</span></label>
                    <select name="org_country" id="org_country" required onchange="onCountryChange(this.value)">
                        <option value="" disabled {{ old('org_country') ? '' : 'selected' }}>Selecciona un país…</option>
                        @foreach(\App\Support\PlusRequestHelper::$countries as $code => $name)
                            <option value="{{ $code }}" {{ old('org_country','MX') === $code ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                    @error('org_country')<span class="error-msg">{{ $message }}</span>@enderror
                </div>

                <div class="form-grid-2" style="margin-bottom:14px;">
                    {{-- Estado (México: dropdown; otro país: texto) --}}
                    <div class="form-group state-wrapper" id="state_mx" style="{{ old('org_country','MX') === 'MX' ? 'display:flex;flex-direction:column;gap:6px;' : 'display:none;' }}">
                        <label>Estado <span class="opt">(opcional)</span></label>
                        <select name="org_state_code" id="org_state_code">
                            <option value="">Selecciona un estado…</option>
                            @foreach(\App\Support\PlusRequestHelper::$mexicoStates as $code => $name)
                                <option value="{{ $code }}" {{ old('org_state_code') === $code ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group" id="state_other" style="{{ old('org_country','MX') === 'MX' ? 'display:none;' : 'display:flex;flex-direction:column;gap:6px;' }}">
                        <label>Estado / Provincia <span class="opt">(opcional)</span></label>
                        <input type="text" name="org_state_other" id="org_state_other" value="{{ old('org_state_other') }}" placeholder="Ej. California, Ontario…">
                    </div>

                    {{-- Municipio / Ciudad --}}
                    <div class="form-group">
                        <label>Municipio / Ciudad <span class="opt">(opcional)</span></label>
                        <input type="text" name="org_city" value="{{ old('org_city') }}" placeholder="Ej. Morelia, Guadalajara…">
                    </div>
                </div>

                {{-- Calle + Número --}}
                <div class="form-grid-3" style="margin-bottom:14px;">
                    <div class="form-group">
                        <label>Calle / Avenida <span class="opt">(opcional)</span></label>
                        <input type="text" name="org_street" value="{{ old('org_street') }}" placeholder="Ej. Av. Lázaro Cárdenas">
                    </div>
                    <div class="form-group">
                        <label>Núm. exterior <span class="opt">(opcional)</span></label>
                        <input type="text" name="org_ext_number" value="{{ old('org_ext_number') }}" placeholder="Ej. 1450">
                    </div>
                    <div class="form-group">
                        <label>Núm. interior <span class="opt">(opcional)</span></label>
                        <input type="text" name="org_int_number" value="{{ old('org_int_number') }}" placeholder="Ej. 3B">
                    </div>
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label>Colonia / Barrio <span class="opt">(opcional)</span></label>
                        <input type="text" name="org_neighborhood" value="{{ old('org_neighborhood') }}" placeholder="Ej. Centro Histórico">
                    </div>
                    <div class="form-group">
                        <label>Código postal <span class="opt">(opcional)</span></label>
                        <input type="text" name="org_zip" value="{{ old('org_zip') }}" placeholder="Ej. 58000" maxlength="10" inputmode="numeric">
                    </div>
                </div>
            </div>

            {{-- ── 3. Datos de facturación ──────────────────────────── --}}
            <div class="section-title">
                <span class="section-num">3</span> Datos de facturación <span style="font-size:.6875rem;font-weight:400;text-transform:none;color:#94a3b8;letter-spacing:0;">(opcionales)</span>
            </div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label>RFC / Número fiscal <span class="opt">(opcional)</span></label>
                    <input type="text" name="billing_rfc" value="{{ old('billing_rfc') }}" placeholder="XAXX010101000" style="text-transform:uppercase;" maxlength="13">
                </div>
                <div class="form-group">
                    <label>Razón social <span class="opt">(opcional)</span></label>
                    <input type="text" name="billing_razon_social" value="{{ old('billing_razon_social') }}" placeholder="Nombre legal completo">
                </div>
                <div class="form-group">
                    <label>Régimen fiscal <span class="opt">(opcional)</span></label>
                    <select name="billing_regimen">
                        <option value="">Selecciona…</option>
                        @foreach([
                            '601' => '601 — General de Ley Personas Morales',
                            '603' => '603 — Personas Morales con Fines no Lucrativos',
                            '605' => '605 — Sueldos y Salarios',
                            '606' => '606 — Arrendamiento',
                            '608' => '608 — Demás ingresos',
                            '611' => '611 — Ingresos por Dividendos',
                            '612' => '612 — Personas Físicas con Actividades Empresariales',
                            '614' => '614 — Ingresos por intereses',
                            '616' => '616 — Sin obligaciones fiscales',
                            '621' => '621 — Incorporación Fiscal',
                            '625' => '625 — Régimen de las Actividades Empresariales con ingresos a través de Plataformas Tecnológicas',
                            '626' => '626 — Régimen Simplificado de Confianza',
                        ] as $k => $v)
                            <option value="{{ $k }}" {{ old('billing_regimen') === $k ? 'selected' : '' }}>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Uso de CFDI <span class="opt">(opcional)</span></label>
                    <select name="billing_cfdi">
                        <option value="">Selecciona…</option>
                        @foreach([
                            'G01' => 'G01 — Adquisición de mercancias',
                            'G03' => 'G03 — Gastos en general',
                            'I04' => 'I04 — Equipo de cómputo y accesorios',
                            'I08' => 'I08 — Otra maquinaria y equipo',
                            'D10' => 'D10 — Pagos por servicios educativos',
                            'S01' => 'S01 — Sin efectos fiscales',
                            'CP01'=> 'CP01 — Pagos',
                            'CN01'=> 'CN01 — Nómina',
                        ] as $k => $v)
                            <option value="{{ $k }}" {{ old('billing_cfdi') === $k ? 'selected' : '' }}>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" style="grid-column:span 2;">
                    <label>Correo para facturas <span class="opt">(opcional)</span></label>
                    <input type="email" name="billing_email" value="{{ old('billing_email') }}" placeholder="facturas@institución.edu">
                </div>
            </div>

            {{-- ── 4. Descripción del proyecto ──────────────────────── --}}
            <div class="section-title">
                <span class="section-num">4</span> Descripción del proyecto
            </div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label>Tipo de uso <span class="req">*</span></label>
                    <select name="use_case" required>
                        <option value="" disabled {{ old('use_case') ? '' : 'selected' }}>Selecciona…</option>
                        @foreach(\App\Support\PlusRequestHelper::$useCases as $val => $label)
                            <option value="{{ $val }}" {{ old('use_case') === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('use_case')<span class="error-msg">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label>Número estimado de usuarios <span class="opt">(opcional)</span></label>
                    <select name="num_users">
                        <option value="">Selecciona…</option>
                        @foreach(['1–10','11–50','51–100','101–500','Más de 500'] as $opt)
                            <option value="{{ $opt }}" {{ old('num_users') === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" style="grid-column:span 2;">
                    <label>Descripción del proyecto <span class="req">*</span></label>
                    <textarea name="project_description" placeholder="Describe cómo planeas usar Mindra en tu institución, los objetivos del proyecto y el perfil de los usuarios finales…" required style="min-height:130px;">{{ old('project_description') }}</textarea>
                    @error('project_description')<span class="error-msg">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label>¿Cómo nos encontraste? <span class="opt">(opcional)</span></label>
                    <select name="how_found">
                        <option value="">Selecciona…</option>
                        @foreach(['Redes sociales','Colega / recomendación','Búsqueda en Google','Evento / congreso','Publicación académica','Otro'] as $opt)
                            <option value="{{ $opt }}" {{ old('how_found') === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Comentarios adicionales <span class="opt">(opcional)</span></label>
                    <textarea name="additional_comments" placeholder="Cualquier información extra que quieras compartir…">{{ old('additional_comments') }}</textarea>
                </div>
            </div>

            <button type="submit" class="btn-plus">Enviar solicitud →</button>
            <p style="text-align:center;margin-top:12px;font-size:.75rem;color:#94a3b8;">
                Al enviar confirmas que aceptas el
                <a href="{{ route('contracts.plus') }}" target="_blank" style="color:#7c3aed;">Contrato de Acceso Plus</a>,
                los <a href="{{ route('legal.terms') }}" target="_blank" style="color:#7c3aed;">Términos de uso</a>
                y la <a href="{{ route('legal.privacy') }}" target="_blank" style="color:#7c3aed;">Política de privacidad</a>.
            </p>
        </form>

        @endif
    </div>

    {{-- ── Resumen del plan ────────────────────────────────────────────── --}}
    <div class="summary-card">
        <span class="badge-plus">Plan Plus</span>
        <div style="font-size:2rem;font-weight:800;margin:10px 0 4px;color:var(--text,#0f172a);">A medida</div>
        <p style="color:var(--text-muted,#64748b);font-size:.875rem;margin-bottom:24px;line-height:1.6;">
            Acceso exclusivo para investigadores, clínicos e instituciones aliadas. El precio se acuerda según el proyecto.
        </p>
        <div style="border-top:1px solid var(--border,#e8edf5);padding-top:20px;">
            @foreach([
                'Chat con IA (texto y audio)',
                'Análisis facial de emociones',
                'Detección multimodal combinada',
                'Historial ilimitado completo',
                'Reporte clínico PDF (30 días)',
                'Estadísticas personales avanzadas',
                'Alertas de crisis automáticas',
                'Soporte prioritario dedicado',
            ] as $feat)
            <div class="feat-item">
                <svg class="feat-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd"/></svg>
                <span>{{ $feat }}</span>
            </div>
            @endforeach
        </div>
        <div style="margin-top:24px;padding:14px;background:rgba(124,58,237,.07);border-radius:12px;font-size:.84rem;color:#6d28d9;line-height:1.55;">
            💜 Diseñado para investigadores y profesionales de la salud mental del Laboratorio CAFINED e instituciones aliadas.
        </div>
        <div style="margin-top:12px;padding:12px;background:var(--bg,#f8fafc);border-radius:12px;font-size:.84rem;color:var(--text-muted,#64748b);line-height:1.55;">
            📧 Al enviar recibirás una copia de tu solicitud en el correo indicado.
        </div>
        <div style="margin-top:12px;padding:12px;background:#fffbeb;border-radius:12px;font-size:.84rem;color:#92400e;line-height:1.55;">
            ⏱️ Respuesta en menos de <strong>24 horas hábiles</strong>.
        </div>
        <div style="margin-top:16px;text-align:center;">
            <a href="{{ route('contracts.plus') }}" target="_blank"
               style="font-size:.75rem;color:#7c3aed;text-decoration:none;display:inline-flex;align-items:center;gap:4px;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" style="width:13px;height:13px;">
                    <path fill-rule="evenodd" d="M4 2a1.5 1.5 0 0 0-1.5 1.5v9A1.5 1.5 0 0 0 4 14h8a1.5 1.5 0 0 0 1.5-1.5V5.621a1.5 1.5 0 0 0-.44-1.06L9.94 2.439A1.5 1.5 0 0 0 8.878 2H4Zm1 7.75a.75.75 0 0 1 .75-.75h4.5a.75.75 0 0 1 0 1.5h-4.5a.75.75 0 0 1-.75-.75Zm.75-3.25a.75.75 0 0 0 0 1.5h4.5a.75.75 0 0 0 0-1.5h-4.5Z" clip-rule="evenodd"/>
                </svg>
                Ver Contrato de Acceso Plus
            </a>
        </div>
    </div>

</div>

<script>
function onCountryChange(value) {
    const mxBlock    = document.getElementById('state_mx');
    const otherBlock = document.getElementById('state_other');
    if (value === 'MX') {
        mxBlock.style.display    = 'flex';
        mxBlock.style.flexDirection = 'column';
        mxBlock.style.gap        = '6px';
        otherBlock.style.display = 'none';
        document.getElementById('org_state_other').value = '';
    } else {
        mxBlock.style.display    = 'none';
        otherBlock.style.display = 'flex';
        otherBlock.style.flexDirection = 'column';
        otherBlock.style.gap     = '6px';
        document.getElementById('org_state_code').value  = '';
    }
}
// Inicializar al cargar
document.addEventListener('DOMContentLoaded', function() {
    const country = document.getElementById('org_country');
    if (country) onCountryChange(country.value);
});
</script>
@endsection
