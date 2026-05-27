@extends('superadmin._layout')
@section('title', 'Instituciones')

@push('styles')
<style>
.inst-field { display:flex; flex-direction:column; gap:6px; }
.inst-label { font-size:.75rem; font-weight:700; color:#64748b; letter-spacing:.03em; }
.inst-input {
    width:100%; padding:10px 14px; border:1.5px solid #e2e8f0; border-radius:10px;
    font-size:.875rem; outline:none; background:#fff; font-family:inherit;
    transition:border-color .15s;
}
.inst-input:focus { border-color:#6366f1; }
select.inst-input { cursor:pointer; }
.section-title {
    font-size:.6875rem; font-weight:800; text-transform:uppercase; letter-spacing:.08em;
    color:#6366f1; border-bottom:2px solid #eef2ff; padding-bottom:8px; margin:0 0 16px;
}

/* Formulario deslizable */
.inst-form-wrap {
    overflow: hidden;
    max-height: 0;
    opacity: 0;
    transition: max-height .4s cubic-bezier(.4,0,.2,1), opacity .3s ease, margin .3s ease;
    margin-bottom: 0;
}
.inst-form-wrap.open {
    max-height: 2000px;
    opacity: 1;
    margin-bottom: 24px;
}
</style>
@endpush

@section('panel')

{{-- ── Formulario: Nueva institución (colapsable) ──────────────────────── --}}
<div class="inst-form-wrap" id="instFormWrap">
<div style="background:#fff;border:1px solid #c7d2fe;border-radius:16px;padding:28px;">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
        <h3 style="font-size:1rem;font-weight:800;color:#0f172a;margin:0;">Nueva institución</h3>
        <button type="button" onclick="toggleInstForm()" title="Cerrar"
                style="width:28px;height:28px;border-radius:8px;border:1.5px solid #e2e8f0;background:#f8fafc;color:#94a3b8;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all .15s;"
                onmouseover="this.style.background='#fef2f2';this.style.color='#dc2626';this.style.borderColor='#fecaca'"
                onmouseout="this.style.background='#f8fafc';this.style.color='#94a3b8';this.style.borderColor='#e2e8f0'">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width:14px;height:14px;">
                <path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z"/>
            </svg>
        </button>
    </div>

    <form method="POST" action="{{ route('superadmin.institutions.store') }}">
        @csrf

        {{-- 1. Datos generales --}}
        <p class="section-title">1. Datos generales</p>
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:24px;">
            <div class="inst-field">
                <label class="inst-label">Nombre <span style="color:#e11d48;">*</span></label>
                <input type="text" name="name" class="inst-input" required placeholder="Ej: Universidad Tecnológica" value="{{ old('name') }}">
            </div>
            <div class="inst-field">
                <label class="inst-label">Slug (único) <span style="color:#e11d48;">*</span></label>
                <input type="text" name="slug" class="inst-input" required placeholder="universidad-tecnologica" value="{{ old('slug') }}"
                       style="font-family:monospace;" oninput="this.value=this.value.toLowerCase().replace(/[^a-z0-9-]/g,'-')">
            </div>
            <div class="inst-field">
                <label class="inst-label">Tipo de institución</label>
                <select name="type" class="inst-input">
                    <option value="">— Seleccionar —</option>
                    @foreach(['Universidad'=>'universidad','Hospital'=>'hospital','Centro de salud'=>'centro_salud','Empresa privada'=>'empresa','Gobierno / Sector público'=>'gobierno','ONG / Asociación'=>'ong','Laboratorio de investigación'=>'laboratorio','Consultorio / Clínica'=>'consultorio','Otro'=>'otro'] as $label => $val)
                        <option value="{{ $val }}" {{ old('type') === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="inst-field" style="grid-column:span 2;">
                <label class="inst-label">Descripción</label>
                <input type="text" name="description" class="inst-input" placeholder="Descripción breve de la institución" value="{{ old('description') }}">
            </div>
            <div class="inst-field">
                <label class="inst-label">Sitio web</label>
                <input type="url" name="website" class="inst-input" placeholder="https://www.institucion.edu.mx" value="{{ old('website') }}">
            </div>
            <div class="inst-field">
                <label class="inst-label">URL del logo</label>
                <input type="url" name="logo_url" class="inst-input" placeholder="https://…/logo.png" value="{{ old('logo_url') }}">
            </div>
        </div>

        {{-- 2. Contacto --}}
        <p class="section-title">2. Información de contacto</p>
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:24px;">
            <div class="inst-field">
                <label class="inst-label">Nombre del responsable</label>
                <input type="text" name="contact_name" class="inst-input" placeholder="Dr. Juan Pérez" value="{{ old('contact_name') }}">
            </div>
            <div class="inst-field">
                <label class="inst-label">Email de contacto</label>
                <input type="email" name="contact_email" class="inst-input" placeholder="contacto@institucion.edu" value="{{ old('contact_email') }}">
            </div>
            <div class="inst-field">
                <label class="inst-label">Teléfono / WhatsApp</label>
                <input type="text" name="contact_phone" class="inst-input" placeholder="+52 55 1234 5678" value="{{ old('contact_phone') }}">
            </div>
        </div>

        {{-- 3. Ubicación --}}
        <p class="section-title">3. Ubicación</p>
        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px;">
            <div class="inst-field">
                <label class="inst-label">País</label>
                <input type="text" name="country" class="inst-input" placeholder="México" value="{{ old('country') }}">
            </div>
            <div class="inst-field">
                <label class="inst-label">Estado / Provincia</label>
                <input type="text" name="state" class="inst-input" placeholder="Nuevo León" value="{{ old('state') }}">
            </div>
            <div class="inst-field">
                <label class="inst-label">Ciudad / Municipio</label>
                <input type="text" name="city" class="inst-input" placeholder="Monterrey" value="{{ old('city') }}">
            </div>
            <div class="inst-field">
                <label class="inst-label">Dirección</label>
                <input type="text" name="address" class="inst-input" placeholder="Av. Principal 123, Col. Centro" value="{{ old('address') }}">
            </div>
        </div>

        {{-- 4. Contrato y configuración --}}
        <p class="section-title">4. Contrato y configuración</p>
        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px;">
            <div class="inst-field">
                <label class="inst-label">Inicio de contrato</label>
                <input type="date" name="contract_starts_at" class="inst-input" value="{{ old('contract_starts_at') }}">
            </div>
            <div class="inst-field">
                <label class="inst-label">Fin de contrato</label>
                <input type="date" name="contract_ends_at" class="inst-input" value="{{ old('contract_ends_at') }}">
            </div>
            <div class="inst-field">
                <label class="inst-label">Límite de usuarios</label>
                <input type="number" name="max_users" class="inst-input" min="1" placeholder="Ej: 100" value="{{ old('max_users') }}">
            </div>
            <div class="inst-field">
                <label class="inst-label">Estado</label>
                <select name="is_active" class="inst-input">
                    <option value="1" {{ old('is_active', '1') === '1' ? 'selected' : '' }}>✅ Activa</option>
                    <option value="0" {{ old('is_active') === '0' ? 'selected' : '' }}>⛔ Inactiva</option>
                </select>
            </div>
        </div>

        {{-- 5. Notas internas --}}
        <p class="section-title">5. Notas internas</p>
        <div style="margin-bottom:24px;">
            <textarea name="notes" rows="3" class="inst-input" placeholder="Observaciones internas, condiciones del contrato, historial de cambios…" style="resize:vertical;">{{ old('notes') }}</textarea>
        </div>

        {{-- Submit --}}
        <div style="display:flex;align-items:center;justify-content:flex-end;gap:12px;">
            @if($errors->any())
                <div style="flex:1;padding:10px 14px;background:#fef2f2;border:1px solid #fecaca;border-radius:10px;">
                    @foreach($errors->all() as $error)
                        <p style="font-size:.8125rem;color:#dc2626;margin:0;">{{ $error }}</p>
                    @endforeach
                </div>
            @endif
            <button type="submit" style="padding:10px 28px;border:none;border-radius:10px;background:linear-gradient(135deg,#38bdf8,#6366f1,#9333ea);color:#fff;font-size:.875rem;font-weight:700;cursor:pointer;white-space:nowrap;transition:opacity .15s;"
                    onmouseover="this.style.opacity='.9'" onmouseout="this.style.opacity='1'">
                Crear institución
            </button>
        </div>
    </form>
</div>
</div>{{-- /inst-form-wrap --}}

{{-- ── Lista de instituciones ──────────────────────────────────────────────── --}}
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;overflow:hidden;">
    <div style="padding:18px 24px;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;gap:12px;">
        <div style="display:flex;align-items:center;gap:10px;">
            <h3 style="font-size:1rem;font-weight:800;color:#0f172a;margin:0;">Instituciones registradas</h3>
            <span style="font-size:.75rem;color:#94a3b8;background:#f8fafc;border:1px solid #e2e8f0;padding:2px 8px;border-radius:9999px;">{{ $institutions->count() }}</span>
        </div>
        <button type="button" id="btnNuevaInst" onclick="toggleInstForm()"
                style="display:flex;align-items:center;gap:7px;padding:8px 18px;border-radius:10px;border:none;background:linear-gradient(135deg,#6366f1,#9333ea);color:#fff;font-size:.8125rem;font-weight:700;cursor:pointer;transition:opacity .15s;font-family:inherit;"
                onmouseover="this.style.opacity='.88'" onmouseout="this.style.opacity='1'">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width:15px;height:15px;">
                <path d="M10.75 4.75a.75.75 0 0 0-1.5 0v4.5h-4.5a.75.75 0 0 0 0 1.5h4.5v4.5a.75.75 0 0 0 1.5 0v-4.5h4.5a.75.75 0 0 0 0-1.5h-4.5v-4.5Z"/>
            </svg>
            <span id="btnNuevaInstLabel">Nueva institución</span>
        </button>
    </div>
    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;">
                    <th style="text-align:left;padding:12px 16px;font-size:.7rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">Institución</th>
                    <th style="text-align:left;padding:12px 16px;font-size:.7rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">Contacto</th>
                    <th style="text-align:left;padding:12px 16px;font-size:.7rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">Ubicación</th>
                    <th style="text-align:center;padding:12px 16px;font-size:.7rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">Usuarios</th>
                    <th style="text-align:left;padding:12px 16px;font-size:.7rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">Contrato</th>
                    <th style="text-align:center;padding:12px 16px;font-size:.7rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">Estado</th>
                    <th style="text-align:center;padding:12px 16px;font-size:.7rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @php
                $typeLabels = ['universidad'=>'Universidad','hospital'=>'Hospital','centro_salud'=>'C. Salud',
                               'empresa'=>'Empresa','gobierno'=>'Gobierno','ong'=>'ONG',
                               'laboratorio'=>'Laboratorio','consultorio'=>'Consultorio','otro'=>'Otro'];
                @endphp
                @forelse($institutions as $inst)
                @php
                    $isExpired  = $inst->contract_ends_at && \Carbon\Carbon::parse($inst->contract_ends_at)->isPast();
                    $expiresSoon = $inst->contract_ends_at && !$isExpired && \Carbon\Carbon::parse($inst->contract_ends_at)->diffInDays(now()) <= 30;
                    $usersCount = $inst->users_count;
                    $usersOver  = $inst->max_users && $usersCount > $inst->max_users;
                @endphp
                <tr style="border-bottom:1px solid #f1f5f9;" onmouseover="this.style.background='#fafbff'" onmouseout="this.style.background='transparent'">
                    {{-- Institución --}}
                    <td style="padding:14px 16px;">
                        <div style="display:flex;align-items:center;gap:10px;">
                            @if($inst->logo_url)
                                <img src="{{ $inst->logo_url }}" alt="" style="width:36px;height:36px;border-radius:8px;object-fit:contain;border:1px solid #e2e8f0;background:#f8fafc;">
                            @else
                                <div style="width:36px;height:36px;border-radius:10px;background:#f5f3ff;border:1px solid #ddd6fe;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="#7c3aed" style="width:18px;height:18px;"><path fill-rule="evenodd" d="M1 2.75A.75.75 0 0 1 1.75 2h16.5a.75.75 0 0 1 0 1.5H18v12.5h.25a.75.75 0 0 1 0 1.5H1.75a.75.75 0 0 1 0-1.5H2V3.5h-.25A.75.75 0 0 1 1 2.75ZM10 10a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z" clip-rule="evenodd"/></svg>
                                </div>
                            @endif
                            <div>
                                <p style="font-size:.875rem;font-weight:700;color:#1e293b;margin:0;">{{ $inst->name }}</p>
                                <div style="display:flex;align-items:center;gap:6px;margin-top:2px;">
                                    <span style="font-size:.6875rem;color:#94a3b8;font-family:monospace;">{{ $inst->slug }}</span>
                                    @if($inst->type)
                                        <span style="font-size:.625rem;font-weight:700;padding:1px 6px;border-radius:5px;background:#f5f3ff;color:#7c3aed;">{{ $typeLabels[$inst->type] ?? $inst->type }}</span>
                                    @endif
                                </div>
                                @if($inst->website)
                                    <a href="{{ $inst->website }}" target="_blank" style="font-size:.6875rem;color:#6366f1;">{{ parse_url($inst->website, PHP_URL_HOST) }}</a>
                                @endif
                            </div>
                        </div>
                    </td>
                    {{-- Contacto --}}
                    <td style="padding:14px 16px;">
                        @if($inst->contact_name)
                            <p style="font-size:.8125rem;font-weight:600;color:#334155;margin:0;">{{ $inst->contact_name }}</p>
                        @endif
                        @if($inst->contact_email)
                            <p style="font-size:.75rem;color:#64748b;margin:2px 0 0;">{{ $inst->contact_email }}</p>
                        @endif
                        @if($inst->contact_phone)
                            <p style="font-size:.75rem;color:#94a3b8;margin:2px 0 0;">{{ $inst->contact_phone }}</p>
                        @endif
                        @if(!$inst->contact_name && !$inst->contact_email)
                            <span style="font-size:.8125rem;color:#cbd5e1;">—</span>
                        @endif
                    </td>
                    {{-- Ubicación --}}
                    <td style="padding:14px 16px;font-size:.8125rem;color:#64748b;">
                        @if($inst->city || $inst->state || $inst->country)
                            {{ collect([$inst->city, $inst->state, $inst->country])->filter()->implode(', ') }}
                        @else
                            <span style="color:#cbd5e1;">—</span>
                        @endif
                    </td>
                    {{-- Usuarios --}}
                    <td style="padding:14px 16px;text-align:center;">
                        <span style="font-size:.875rem;font-weight:700;{{ $usersOver ? 'color:#dc2626;' : 'color:#4f46e5;' }}background:{{ $usersOver ? '#fef2f2' : '#eef2ff' }};padding:4px 10px;border-radius:9999px;">
                            {{ $usersCount }}@if($inst->max_users)<span style="font-weight:400;color:#94a3b8;font-size:.75rem;"> / {{ $inst->max_users }}</span>@endif
                        </span>
                    </td>
                    {{-- Contrato --}}
                    <td style="padding:14px 16px;font-size:.8125rem;">
                        @if($inst->contract_starts_at || $inst->contract_ends_at)
                            <div style="color:#64748b;">
                                @if($inst->contract_starts_at)
                                    <div style="font-size:.6875rem;color:#94a3b8;">Inicio: {{ \Carbon\Carbon::parse($inst->contract_starts_at)->format('d/m/Y') }}</div>
                                @endif
                                @if($inst->contract_ends_at)
                                    <div style="font-weight:600;{{ $isExpired ? 'color:#dc2626;' : ($expiresSoon ? 'color:#d97706;' : 'color:#334155;') }}">
                                        Vence: {{ \Carbon\Carbon::parse($inst->contract_ends_at)->format('d/m/Y') }}
                                        @if($isExpired) <span style="font-size:.625rem;">⚠ Vencido</span>
                                        @elseif($expiressoon) <span style="font-size:.625rem;">⚠ Próximo</span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @else
                            <span style="color:#cbd5e1;">Sin fecha</span>
                        @endif
                    </td>
                    {{-- Estado --}}
                    <td style="padding:14px 16px;text-align:center;">
                        @if($inst->is_active ?? true)
                            <span style="font-size:.6875rem;font-weight:700;padding:4px 10px;border-radius:9999px;background:#f0fdf4;color:#16a34a;border:1px solid #bbf7d0;">Activa</span>
                        @else
                            <span style="font-size:.6875rem;font-weight:700;padding:4px 10px;border-radius:9999px;background:#f8fafc;color:#94a3b8;border:1px solid #e2e8f0;">Inactiva</span>
                        @endif
                    </td>
                    {{-- Acciones --}}
                    <td style="padding:14px 16px;text-align:center;">
                        <a href="{{ route('superadmin.institutions.edit', $inst) }}" style="font-size:.6875rem;font-weight:600;color:#4f46e5;padding:5px 12px;border-radius:8px;border:1px solid #c7d2fe;background:#eef2ff;">Editar</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="padding:48px;text-align:center;color:#94a3b8;font-size:.875rem;">Sin instituciones registradas. Crea la primera arriba.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<script>
function toggleInstForm() {
    var wrap  = document.getElementById('instFormWrap');
    var label = document.getElementById('btnNuevaInstLabel');
    var btn   = document.getElementById('btnNuevaInst');
    var open  = wrap.classList.toggle('open');
    label.textContent = open ? 'Cancelar' : 'Nueva institución';
    btn.style.background = open
        ? 'linear-gradient(135deg,#ef4444,#dc2626)'
        : 'linear-gradient(135deg,#6366f1,#9333ea)';
    if (open) wrap.scrollIntoView({ behavior:'smooth', block:'start' });
}

// Si hay errores de validación, abrir el formulario automáticamente
@if($errors->any())
document.addEventListener('DOMContentLoaded', function() { toggleInstForm(); });
@endif
</script>
@endsection
