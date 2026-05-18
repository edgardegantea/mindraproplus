@extends('layouts.app')

@section('title', 'Plan Full — Contratación Institucional')

@push('styles')
<style>
    .form-card {
        max-width: 48rem;
        margin: 0 auto;
        background: #fff;
        border-radius: 24px;
        border: 1px solid #e8edf5;
        padding: 48px;
        box-shadow: 0 4px 24px rgba(0,0,0,.04);
    }
    .form-header {
        text-align: center;
        margin-bottom: 40px;
    }
    .form-header h1 {
        font-size: 2rem;
        font-weight: 900;
        color: #0f172a;
        margin-bottom: 8px;
    }
    .form-header p {
        font-size: .9375rem;
        color: #64748b;
        line-height: 1.7;
    }
    .form-group {
        margin-bottom: 20px;
    }
    .form-label {
        display: block;
        font-size: .8125rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 6px;
    }
    .form-label .required {
        color: #dc2626;
        margin-left: 2px;
    }
    .form-input, .form-select, .form-textarea {
        width: 100%;
        padding: 12px 16px;
        border-radius: 12px;
        border: 1.5px solid #e2e8f0;
        font-size: .875rem;
        color: #1e293b;
        background: #f8fafc;
        transition: border-color .15s, box-shadow .15s;
        font-family: inherit;
    }
    .form-input:focus, .form-select:focus, .form-textarea:focus {
        outline: none;
        border-color: #4f46e5;
        box-shadow: 0 0 0 3px rgba(79,70,229,.1);
        background: #fff;
    }
    .form-textarea {
        min-height: 100px;
        resize: vertical;
    }
    .form-select {
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3E%3C/svg%3E");
        background-position: right 12px center;
        background-repeat: no-repeat;
        background-size: 20px;
        padding-right: 40px;
    }
    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }
    .form-error {
        font-size: .75rem;
        color: #dc2626;
        margin-top: 4px;
    }
    .form-submit {
        width: 100%;
        padding: 14px 28px;
        border-radius: 14px;
        border: none;
        font-size: 1rem;
        font-weight: 800;
        color: #fff;
        background: linear-gradient(135deg, #38bdf8, #6366f1, #9333ea);
        cursor: pointer;
        box-shadow: 0 4px 14px rgba(79,70,229,.3);
        transition: all .2s;
        font-family: inherit;
        margin-top: 12px;
    }
    .form-submit:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 20px rgba(79,70,229,.4);
    }
    .success-banner {
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        border-radius: 14px;
        padding: 16px 20px;
        margin-bottom: 28px;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .success-banner svg {
        width: 22px;
        height: 22px;
        color: #16a34a;
        flex-shrink: 0;
    }
    .success-banner p {
        font-size: .875rem;
        color: #15803d;
        font-weight: 600;
    }
    .features-checklist {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
        margin-top: 6px;
    }
    .features-checklist label {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: .8125rem;
        color: #475569;
        cursor: pointer;
        padding: 6px 10px;
        border-radius: 8px;
        transition: background .15s;
    }
    .features-checklist label:hover {
        background: #f1f5f9;
    }
    .features-checklist input[type="checkbox"] {
        width: 16px;
        height: 16px;
        accent-color: #4f46e5;
    }
    @media (max-width: 640px) {
        .form-card { padding: 28px 20px; }
        .form-row { grid-template-columns: 1fr; }
        .features-checklist { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')
<div class="form-card">

    <div class="form-header">
        <div style="display:inline-flex;align-items:center;justify-content:center;width:56px;height:56px;border-radius:16px;background:#0f172a;margin-bottom:16px;">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#a78bfa" style="width:28px;height:28px;">
                <path d="M11.584 2.376a.75.75 0 0 1 .832 0l9 6a.75.75 0 1 1-.832 1.248L12 3.901 3.416 9.624a.75.75 0 0 1-.832-1.248l9-6Z"/>
                <path fill-rule="evenodd" d="M20.25 10.332v9.918H21a.75.75 0 0 1 0 1.5H3a.75.75 0 0 1 0-1.5h.75v-9.918a.75.75 0 0 1 .634-.74A49.109 49.109 0 0 1 12 9c2.59 0 5.134.202 7.616.592a.75.75 0 0 1 .634.74Zm-7.5 2.418a.75.75 0 0 0-1.5 0v6.75a.75.75 0 0 0 1.5 0v-6.75Zm3-.75a.75.75 0 0 1 .75.75v6.75a.75.75 0 0 1-1.5 0v-6.75a.75.75 0 0 1 .75-.75ZM9 12.75a.75.75 0 0 0-1.5 0v6.75a.75.75 0 0 0 1.5 0v-6.75Z" clip-rule="evenodd"/>
            </svg>
        </div>
        <h1>Plan Full — Institucional</h1>
        <p>Completa el siguiente formulario para solicitar información sobre la implementación del plan Full para tu institución o empresa.</p>
    </div>

    @if (session('success'))
        <div class="success-banner">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm13.36-1.814a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094l3.75-5.25Z" clip-rule="evenodd"/>
            </svg>
            <p>{{ session('success') }}</p>
        </div>
    @endif

    <form method="POST" action="{{ route('plans.full.submit') }}">
        @csrf

        {{-- Datos de la institución --}}
        <div style="margin-bottom:28px;">
            <p style="font-size:.75rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.08em;margin-bottom:16px;padding-bottom:8px;border-bottom:1px solid #f1f5f9;">Datos de la institución</p>

            <div class="form-group">
                <label class="form-label">Nombre de la institución o empresa <span class="required">*</span></label>
                <input type="text" name="institution_name" class="form-input" value="{{ old('institution_name') }}" placeholder="Ej: Universidad Tecnológica de Monterrey">
                @error('institution_name') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Tipo de organización <span class="required">*</span></label>
                <select name="institution_type" class="form-select">
                    <option value="">Selecciona una opción</option>
                    <option value="universidad" {{ old('institution_type') == 'universidad' ? 'selected' : '' }}>Universidad / Institución educativa</option>
                    <option value="empresa" {{ old('institution_type') == 'empresa' ? 'selected' : '' }}>Empresa privada</option>
                    <option value="gobierno" {{ old('institution_type') == 'gobierno' ? 'selected' : '' }}>Gobierno / Sector público</option>
                    <option value="salud" {{ old('institution_type') == 'salud' ? 'selected' : '' }}>Centro de salud / Hospital</option>
                    <option value="ong" {{ old('institution_type') == 'ong' ? 'selected' : '' }}>ONG / Fundación</option>
                    <option value="otro" {{ old('institution_type') == 'otro' ? 'selected' : '' }}>Otro</option>
                </select>
                @error('institution_type') <p class="form-error">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Datos de contacto --}}
        <div style="margin-bottom:28px;">
            <p style="font-size:.75rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.08em;margin-bottom:16px;padding-bottom:8px;border-bottom:1px solid #f1f5f9;">Datos de contacto</p>

            <div class="form-group">
                <label class="form-label">Nombre completo del responsable <span class="required">*</span></label>
                <input type="text" name="contact_name" class="form-input" value="{{ old('contact_name') }}" placeholder="Nombre y apellidos">
                @error('contact_name') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Correo electrónico <span class="required">*</span></label>
                    <input type="email" name="contact_email" class="form-input" value="{{ old('contact_email') }}" placeholder="correo@institucion.edu">
                    @error('contact_email') <p class="form-error">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Teléfono</label>
                    <input type="tel" name="contact_phone" class="form-input" value="{{ old('contact_phone') }}" placeholder="+52 (XXX) XXX-XXXX">
                    @error('contact_phone') <p class="form-error">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- Requerimientos --}}
        <div style="margin-bottom:28px;">
            <p style="font-size:.75rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.08em;margin-bottom:16px;padding-bottom:8px;border-bottom:1px solid #f1f5f9;">Requerimientos del servicio</p>

            <div class="form-group">
                <label class="form-label">Cantidad estimada de usuarios <span class="required">*</span></label>
                <select name="user_count" class="form-select">
                    <option value="">Selecciona un rango</option>
                    <option value="1-50" {{ old('user_count') == '1-50' ? 'selected' : '' }}>1 — 50 usuarios</option>
                    <option value="51-200" {{ old('user_count') == '51-200' ? 'selected' : '' }}>51 — 200 usuarios</option>
                    <option value="201-500" {{ old('user_count') == '201-500' ? 'selected' : '' }}>201 — 500 usuarios</option>
                    <option value="501-1000" {{ old('user_count') == '501-1000' ? 'selected' : '' }}>501 — 1,000 usuarios</option>
                    <option value="1001+" {{ old('user_count') == '1001+' ? 'selected' : '' }}>Más de 1,000 usuarios</option>
                </select>
                @error('user_count') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Características de interés</label>
                <div class="features-checklist">
                    <label><input type="checkbox" name="feat_admin_panel" value="1" {{ old('feat_admin_panel') ? 'checked' : '' }}> Panel administrativo</label>
                    <label><input type="checkbox" name="feat_reports" value="1" {{ old('feat_reports') ? 'checked' : '' }}> Reportes agregados</label>
                    <label><input type="checkbox" name="feat_api" value="1" {{ old('feat_api') ? 'checked' : '' }}> API de integración</label>
                    <label><input type="checkbox" name="feat_branding" value="1" {{ old('feat_branding') ? 'checked' : '' }}> Personalización de marca</label>
                    <label><input type="checkbox" name="feat_sso" value="1" {{ old('feat_sso') ? 'checked' : '' }}> Single Sign-On (SSO)</label>
                    <label><input type="checkbox" name="feat_audio" value="1" {{ old('feat_audio') ? 'checked' : '' }}> Análisis por voz</label>
                    <label><input type="checkbox" name="feat_mobile" value="1" {{ old('feat_mobile') ? 'checked' : '' }}> App móvil dedicada</label>
                    <label><input type="checkbox" name="feat_support" value="1" {{ old('feat_support') ? 'checked' : '' }}> Soporte dedicado 24/7</label>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Comentarios adicionales</label>
                <textarea name="comments" class="form-textarea" placeholder="Describe brevemente tus necesidades, plazos, presupuesto u otra información relevante...">{{ old('comments') }}</textarea>
                @error('comments') <p class="form-error">{{ $message }}</p> @enderror
            </div>
        </div>

        <button type="submit" class="form-submit">Enviar solicitud</button>

        <p style="text-align:center;margin-top:14px;font-size:.75rem;color:#94a3b8;">
            Nos pondremos en contacto contigo en un plazo de 24-48 horas hábiles.
        </p>
    </form>
</div>
@endsection
