@extends('superadmin._layout')
@section('title', 'Editar: ' . $institution->name)

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
.stat-row { display:flex; justify-content:space-between; align-items:center; padding:9px 0; border-bottom:1px solid #f1f5f9; font-size:.8125rem; }
.stat-row:last-child { border-bottom:none; }
.stat-label { color:#64748b; }
.stat-value { font-weight:700; color:#334155; }
</style>
@endpush

@section('panel')
<a href="{{ route('superadmin.institutions') }}" style="display:inline-flex;align-items:center;gap:6px;font-size:.8125rem;font-weight:600;color:#4f46e5;margin-bottom:20px;">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" style="width:14px;height:14px;"><path fill-rule="evenodd" d="M9.78 4.22a.75.75 0 0 1 0 1.06L7.06 8l2.72 2.72a.75.75 0 1 1-1.06 1.06L5.47 8.53a.75.75 0 0 1 0-1.06l3.25-3.25a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd"/></svg>
    Volver a instituciones
</a>

@php
$typeLabels = ['universidad'=>'Universidad','hospital'=>'Hospital','centro_salud'=>'Centro de salud',
               'empresa'=>'Empresa privada','gobierno'=>'Gobierno / Sector público','ong'=>'ONG / Asociación',
               'laboratorio'=>'Laboratorio de investigación','consultorio'=>'Consultorio / Clínica','otro'=>'Otro'];
$isExpired   = $institution->contract_ends_at && \Carbon\Carbon::parse($institution->contract_ends_at)->isPast();
$expiresSoon = $institution->contract_ends_at && !$isExpired && \Carbon\Carbon::parse($institution->contract_ends_at)->diffInDays(now()) <= 30;
@endphp

<div style="display:grid;grid-template-columns:1fr 320px;gap:24px;align-items:start;">

    {{-- ── Columna izquierda: formulario ── --}}
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:28px;">

        {{-- Header --}}
        <div style="display:flex;align-items:center;gap:14px;margin-bottom:28px;">
            @if($institution->logo_url)
                <img src="{{ $institution->logo_url }}" alt="" style="width:52px;height:52px;border-radius:12px;object-fit:contain;border:1.5px solid #e2e8f0;background:#f8fafc;">
            @else
                <div style="width:52px;height:52px;border-radius:14px;background:#f5f3ff;border:1.5px solid #ddd6fe;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#7c3aed" style="width:28px;height:28px;"><path fill-rule="evenodd" d="M3 2.25a.75.75 0 0 0 0 1.5v16.5h-.75a.75.75 0 0 0 0 1.5H15v-18a.75.75 0 0 0 0-1.5H3ZM6.75 19.5v-2.25a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75v2.25a.75.75 0 0 1-.75.75h-3a.75.75 0 0 1-.75-.75ZM6 6.75A.75.75 0 0 1 6.75 6h.75a.75.75 0 0 1 0 1.5h-.75A.75.75 0 0 1 6 6.75ZM6.75 9a.75.75 0 0 0 0 1.5h.75a.75.75 0 0 0 0-1.5h-.75ZM6 12.75a.75.75 0 0 1 .75-.75h.75a.75.75 0 0 1 0 1.5h-.75a.75.75 0 0 1-.75-.75ZM10.5 6a.75.75 0 0 0 0 1.5h.75a.75.75 0 0 0 0-1.5h-.75Zm-.75 3.75A.75.75 0 0 1 10.5 9h.75a.75.75 0 0 1 0 1.5h-.75a.75.75 0 0 1-.75-.75ZM10.5 12a.75.75 0 0 0 0 1.5h.75a.75.75 0 0 0 0-1.5h-.75ZM16.5 6.75v15h5.25a.75.75 0 0 0 0-1.5H21v-12a.75.75 0 0 0 0-1.5h-4.5Zm1.5 4.5a.75.75 0 0 1 .75-.75h.008a.75.75 0 0 1 0 1.5H18.75a.75.75 0 0 1-.75-.75Zm.75 2.25a.75.75 0 0 0 0 1.5h.008a.75.75 0 0 0 0-1.5H18.75Zm-.75 4.5a.75.75 0 0 1 .75-.75h.008a.75.75 0 0 1 0 1.5H18.75a.75.75 0 0 1-.75-.75Z" clip-rule="evenodd"/></svg>
                </div>
            @endif
            <div>
                <h3 style="font-size:1.125rem;font-weight:800;color:#0f172a;margin:0;">{{ $institution->name }}</h3>
                <div style="display:flex;align-items:center;gap:8px;margin-top:4px;">
                    <code style="font-size:.75rem;color:#64748b;">{{ $institution->slug }}</code>
                    @if($institution->type)
                        <span style="font-size:.6875rem;font-weight:700;padding:2px 8px;border-radius:6px;background:#f5f3ff;color:#7c3aed;">{{ $typeLabels[$institution->type] ?? $institution->type }}</span>
                    @endif
                    @if($institution->is_active ?? true)
                        <span style="font-size:.6875rem;font-weight:700;padding:2px 8px;border-radius:6px;background:#f0fdf4;color:#16a34a;">Activa</span>
                    @else
                        <span style="font-size:.6875rem;font-weight:700;padding:2px 8px;border-radius:6px;background:#f8fafc;color:#94a3b8;">Inactiva</span>
                    @endif
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('superadmin.institutions.update', $institution) }}">
            @csrf
            @method('PUT')

            {{-- 1. Datos generales --}}
            <p class="section-title">1. Datos generales</p>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:24px;">
                <div class="inst-field">
                    <label class="inst-label">Nombre <span style="color:#e11d48;">*</span></label>
                    <input type="text" name="name" class="inst-input" required value="{{ old('name', $institution->name) }}">
                </div>
                <div class="inst-field">
                    <label class="inst-label">Slug (único) <span style="color:#e11d48;">*</span></label>
                    <input type="text" name="slug" class="inst-input" required value="{{ old('slug', $institution->slug) }}" style="font-family:monospace;">
                </div>
                <div class="inst-field">
                    <label class="inst-label">Tipo de institución</label>
                    <select name="type" class="inst-input">
                        <option value="">— Seleccionar —</option>
                        @foreach(['Universidad'=>'universidad','Hospital'=>'hospital','Centro de salud'=>'centro_salud','Empresa privada'=>'empresa','Gobierno / Sector público'=>'gobierno','ONG / Asociación'=>'ong','Laboratorio de investigación'=>'laboratorio','Consultorio / Clínica'=>'consultorio','Otro'=>'otro'] as $lbl => $val)
                            <option value="{{ $val }}" {{ old('type', $institution->type) === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="inst-field">
                    <label class="inst-label">Estado</label>
                    <select name="is_active" class="inst-input">
                        <option value="1" {{ old('is_active', $institution->is_active ?? true) ? 'selected' : '' }}>✅ Activa</option>
                        <option value="0" {{ !old('is_active', $institution->is_active ?? true) ? 'selected' : '' }}>⛔ Inactiva</option>
                    </select>
                </div>
                <div class="inst-field" style="grid-column:span 2;">
                    <label class="inst-label">Descripción</label>
                    <textarea name="description" rows="2" class="inst-input" style="resize:vertical;" placeholder="Descripción breve de la institución">{{ old('description', $institution->description) }}</textarea>
                </div>
                <div class="inst-field">
                    <label class="inst-label">Sitio web</label>
                    <input type="url" name="website" class="inst-input" placeholder="https://www.institucion.edu.mx" value="{{ old('website', $institution->website) }}">
                </div>
                <div class="inst-field">
                    <label class="inst-label">URL del logo</label>
                    <input type="url" name="logo_url" class="inst-input" placeholder="https://…/logo.png" value="{{ old('logo_url', $institution->logo_url) }}">
                </div>
            </div>

            {{-- 2. Contacto --}}
            <p class="section-title">2. Información de contacto</p>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;margin-bottom:24px;">
                <div class="inst-field">
                    <label class="inst-label">Nombre del responsable</label>
                    <input type="text" name="contact_name" class="inst-input" placeholder="Dr. Juan Pérez" value="{{ old('contact_name', $institution->contact_name) }}">
                </div>
                <div class="inst-field">
                    <label class="inst-label">Email de contacto</label>
                    <input type="email" name="contact_email" class="inst-input" placeholder="contacto@institucion.edu" value="{{ old('contact_email', $institution->contact_email) }}">
                </div>
                <div class="inst-field">
                    <label class="inst-label">Teléfono / WhatsApp</label>
                    <input type="text" name="contact_phone" class="inst-input" placeholder="+52 55 1234 5678" value="{{ old('contact_phone', $institution->contact_phone) }}">
                </div>
            </div>

            {{-- 3. Ubicación --}}
            <p class="section-title">3. Ubicación</p>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:16px;margin-bottom:24px;">
                <div class="inst-field">
                    <label class="inst-label">País</label>
                    <input type="text" name="country" class="inst-input" placeholder="México" value="{{ old('country', $institution->country) }}">
                </div>
                <div class="inst-field">
                    <label class="inst-label">Estado / Provincia</label>
                    <input type="text" name="state" class="inst-input" placeholder="Nuevo León" value="{{ old('state', $institution->state) }}">
                </div>
                <div class="inst-field">
                    <label class="inst-label">Ciudad / Municipio</label>
                    <input type="text" name="city" class="inst-input" placeholder="Monterrey" value="{{ old('city', $institution->city) }}">
                </div>
                <div class="inst-field">
                    <label class="inst-label">Dirección</label>
                    <input type="text" name="address" class="inst-input" placeholder="Av. Principal 123, Col. Centro" value="{{ old('address', $institution->address) }}">
                </div>
            </div>

            {{-- 4. Contrato --}}
            <p class="section-title">4. Contrato y licencia</p>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;margin-bottom:24px;">
                <div class="inst-field">
                    <label class="inst-label">Inicio de contrato</label>
                    <input type="date" name="contract_starts_at" class="inst-input" value="{{ old('contract_starts_at', $institution->contract_starts_at?->format('Y-m-d')) }}">
                </div>
                <div class="inst-field">
                    <label class="inst-label">
                        Fin de contrato
                        @if($isExpired) <span style="color:#dc2626;font-size:.625rem;">⚠ VENCIDO</span>
                        @elseif($expiresSoon) <span style="color:#d97706;font-size:.625rem;">⚠ PRÓXIMO</span>
                        @endif
                    </label>
                    <input type="date" name="contract_ends_at" class="inst-input" value="{{ old('contract_ends_at', $institution->contract_ends_at?->format('Y-m-d')) }}"
                           style="{{ $isExpired ? 'border-color:#fca5a5;background:#fef2f2;' : ($expiresSoon ? 'border-color:#fde68a;background:#fffbeb;' : '') }}">
                </div>
                <div class="inst-field">
                    <label class="inst-label">Límite de usuarios (licencia)</label>
                    <input type="number" name="max_users" class="inst-input" min="1" placeholder="Sin límite" value="{{ old('max_users', $institution->max_users) }}">
                </div>
            </div>

            {{-- 5. Notas internas --}}
            <p class="section-title">5. Notas internas</p>
            <div style="margin-bottom:28px;">
                <textarea name="notes" rows="4" class="inst-input" style="resize:vertical;" placeholder="Observaciones internas, condiciones del contrato, historial de cambios, precio acordado…">{{ old('notes', $institution->notes) }}</textarea>
            </div>

            {{-- Submit --}}
            @if($errors->any())
                <div style="padding:10px 14px;background:#fef2f2;border:1px solid #fecaca;border-radius:10px;margin-bottom:16px;">
                    @foreach($errors->all() as $error)
                        <p style="font-size:.8125rem;color:#dc2626;margin:0;">{{ $error }}</p>
                    @endforeach
                </div>
            @endif
            <div style="display:flex;justify-content:flex-end;">
                <button type="submit" style="padding:11px 28px;border:none;border-radius:10px;background:linear-gradient(135deg,#38bdf8,#6366f1,#9333ea);color:#fff;font-size:.875rem;font-weight:700;cursor:pointer;transition:opacity .15s;"
                        onmouseover="this.style.opacity='.9'" onmouseout="this.style.opacity='1'">
                    Guardar cambios
                </button>
            </div>
        </form>
    </div>

    {{-- ── Columna derecha: resumen + usuarios ── --}}
    <div style="display:flex;flex-direction:column;gap:16px;">

        {{-- Estadísticas --}}
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:20px;">
            <h4 style="font-size:.875rem;font-weight:800;color:#0f172a;margin:0 0 14px;">Resumen</h4>
            <div class="stat-row">
                <span class="stat-label">ID</span>
                <span class="stat-value">#{{ $institution->id }}</span>
            </div>
            <div class="stat-row">
                <span class="stat-label">Usuarios asignados</span>
                <span class="stat-value" style="{{ ($institution->max_users && $institution->users_count > $institution->max_users) ? 'color:#dc2626;' : 'color:#4f46e5;' }}">
                    {{ $institution->users_count }}
                    @if($institution->max_users)
                        <span style="font-weight:400;color:#94a3b8;font-size:.75rem;"> / {{ $institution->max_users }}</span>
                    @endif
                </span>
            </div>
            @if($institution->contract_ends_at)
            <div class="stat-row">
                <span class="stat-label">Vencimiento</span>
                <span class="stat-value" style="{{ $isExpired ? 'color:#dc2626;' : ($expiresSoon ? 'color:#d97706;' : '') }}">
                    {{ \Carbon\Carbon::parse($institution->contract_ends_at)->format('d/m/Y') }}
                    @if($isExpired) <br><span style="font-size:.6875rem;font-weight:600;">Vencido hace {{ \Carbon\Carbon::parse($institution->contract_ends_at)->diffForHumans() }}</span>
                    @elseif($expiresSoon) <br><span style="font-size:.6875rem;font-weight:600;">Vence en {{ \Carbon\Carbon::parse($institution->contract_ends_at)->diffForHumans() }}</span>
                    @endif
                </span>
            </div>
            @endif
            <div class="stat-row">
                <span class="stat-label">Creada</span>
                <span class="stat-value" style="font-weight:500;">{{ $institution->created_at->format('d/m/Y') }}</span>
            </div>
            <div class="stat-row">
                <span class="stat-label">Actualizada</span>
                <span class="stat-value" style="font-weight:500;">{{ $institution->updated_at->format('d/m/Y H:i') }}</span>
            </div>
        </div>

        {{-- Usuarios recientes --}}
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:20px;">
            <h4 style="font-size:.875rem;font-weight:800;color:#0f172a;margin:0 0 14px;">Usuarios asignados</h4>
            @forelse($recentUsers as $u)
            <div style="display:flex;align-items:center;gap:10px;padding:8px 0;border-bottom:1px solid #f8fafc;">
                <div style="width:28px;height:28px;border-radius:9999px;background:linear-gradient(135deg,#38bdf8,#6366f1);display:flex;align-items:center;justify-content:center;color:#fff;font-size:.6875rem;font-weight:800;flex-shrink:0;">
                    {{ strtoupper(substr($u->name, 0, 1)) }}
                </div>
                <div style="min-width:0;flex:1;">
                    <p style="font-size:.8125rem;font-weight:600;color:#1e293b;margin:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $u->name }}</p>
                    <p style="font-size:.6875rem;color:#94a3b8;margin:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $u->email }}</p>
                </div>
                <a href="{{ route('superadmin.users.detail', $u) }}" style="font-size:.6875rem;color:#4f46e5;flex-shrink:0;">Ver</a>
            </div>
            @empty
            <p style="font-size:.8125rem;color:#94a3b8;margin:0;text-align:center;padding:16px 0;">Sin usuarios asignados</p>
            @endforelse
            @if($institution->users_count > 10)
                <p style="font-size:.75rem;color:#94a3b8;margin:12px 0 0;text-align:center;">
                    + {{ $institution->users_count - 10 }} usuarios más —
                    <a href="{{ route('superadmin.users', ['institution' => $institution->id]) }}" style="color:#4f46e5;font-weight:600;">ver todos</a>
                </p>
            @endif
        </div>

    </div>
</div>
@endsection
