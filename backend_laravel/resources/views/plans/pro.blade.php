@extends('layouts.app')

@section('title', 'Plan Pro — Suscripción Individual')

@push('styles')
<style>
    .pro-page {
        max-width: 78rem;
        margin: 0 auto;
        display: grid;
        grid-template-columns: 3fr 2fr;
        gap: 36px;
        align-items: start;
    }
    .form-card {
        background: #fff;
        border-radius: 24px;
        border: 1px solid #e8edf5;
        padding: 40px;
        box-shadow: 0 4px 24px rgba(0,0,0,.04);
    }
    .summary-card {
        background: linear-gradient(160deg, #eef2ff, #fff);
        border-radius: 24px;
        border: 2px solid #4f46e5;
        padding: 32px;
        position: sticky;
        top: 88px;
    }
    .form-header { margin-bottom: 32px; }
    .form-header h1 { font-size: 1.625rem; font-weight: 900; color: #0f172a; margin-bottom: 6px; }
    .form-header p { font-size: .875rem; color: #64748b; line-height: 1.6; }
    .form-section-title {
        font-size: .75rem; font-weight: 700; color: #94a3b8;
        text-transform: uppercase; letter-spacing: .08em;
        margin-bottom: 16px; padding-bottom: 8px; border-bottom: 1px solid #f1f5f9;
    }
    .form-group { margin-bottom: 18px; }
    .form-label { display: block; font-size: .8125rem; font-weight: 700; color: #1e293b; margin-bottom: 6px; }
    .form-label .req { color: #dc2626; margin-left: 2px; }
    .form-input {
        width: 100%; padding: 12px 16px; border-radius: 12px;
        border: 1.5px solid #e2e8f0; font-size: .875rem; color: #1e293b;
        background: #f8fafc; transition: border-color .15s, box-shadow .15s; font-family: inherit;
    }
    .form-input:focus { outline: none; border-color: #4f46e5; box-shadow: 0 0 0 3px rgba(79,70,229,.1); background: #fff; }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    .form-error { font-size: .75rem; color: #dc2626; margin-top: 4px; }
    .form-submit {
        width: 100%; padding: 14px 28px; border-radius: 14px; border: none;
        font-size: 1rem; font-weight: 800; color: #fff;
        background: linear-gradient(135deg, #00b1ea, #009ee3);
        cursor: pointer; box-shadow: 0 4px 14px rgba(0,158,227,.3);
        transition: all .2s; font-family: inherit; margin-top: 8px;
        display: flex; align-items: center; justify-content: center; gap: 10px;
    }
    .form-submit:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(0,158,227,.4); }

    .billing-option {
        display: flex; align-items: center; gap: 12px;
        padding: 14px 16px; border-radius: 12px;
        border: 1.5px solid #e2e8f0; cursor: pointer;
        transition: all .15s; background: #f8fafc;
    }
    .billing-option:hover { border-color: #c7d2fe; background: #faf9ff; }
    .billing-option.active { border-color: #4f46e5; background: #eef2ff; box-shadow: 0 0 0 3px rgba(79,70,229,.1); }
    .billing-option input[type="radio"] { accent-color: #4f46e5; width: 16px; height: 16px; }
    .billing-label { font-size: .8125rem; font-weight: 600; color: #1e293b; }
    .billing-desc { font-size: .75rem; color: #64748b; }

    .success-banner {
        background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 14px;
        padding: 16px 20px; margin-bottom: 24px; display: flex; align-items: center; gap: 12px;
    }
    .success-banner svg { width: 22px; height: 22px; color: #16a34a; flex-shrink: 0; }
    .success-banner p { font-size: .875rem; color: #15803d; font-weight: 600; }
    .error-banner {
        background: #fef2f2; border: 1px solid #fecaca; border-radius: 14px;
        padding: 16px 20px; margin-bottom: 24px; display: flex; align-items: center; gap: 12px;
    }
    .error-banner svg { width: 22px; height: 22px; color: #dc2626; flex-shrink: 0; }
    .error-banner p { font-size: .875rem; color: #991b1b; font-weight: 600; }

    .summary-price { font-size: 2.25rem; font-weight: 900; color: #4f46e5; }
    .summary-period { font-size: .875rem; color: #64748b; font-weight: 500; }
    .summary-feature { display: flex; align-items: center; gap: 8px; font-size: .8125rem; color: #475569; padding: 4px 0; }
    .summary-feature svg { width: 16px; height: 16px; flex-shrink: 0; }

    .secure-badge {
        display: flex; align-items: center; gap: 8px;
        padding: 10px 14px; border-radius: 10px;
        background: #e8f4fd; border: 1px solid #b3daef;
        font-size: .75rem; font-weight: 600; color: #0066b2;
        margin-top: 16px;
    }
    .secure-badge svg { width: 16px; height: 16px; flex-shrink: 0; }

    .mp-badge {
        display: flex; align-items: center; justify-content: center; gap: 8px;
        margin-top: 12px; padding: 10px; border-radius: 10px;
        background: #f8fafc; border: 1px solid #e2e8f0;
    }
    .mp-badge span { font-size: .75rem; color: #64748b; }

    @media (max-width: 768px) {
        .pro-page { grid-template-columns: 1fr; }
        .summary-card { position: static; order: -1; }
        .form-row { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')
<div class="pro-page">

    {{-- Left: Form --}}
    <div class="form-card">
        <div class="form-header">
            <div style="display:inline-flex;align-items:center;justify-content:center;width:48px;height:48px;border-radius:14px;background:#eef2ff;border:1px solid #c7d2fe;margin-bottom:14px;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#4f46e5" stroke-width="1.5" style="width:26px;height:26px;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m3.75 13.5 10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z"/>
                </svg>
            </div>
            <h1>Suscripción Plan Pro</h1>
            <p>Completa tus datos y serás redirigido a MercadoPago para realizar el pago de forma segura.</p>
        </div>

        @if (session('success'))
            <div class="success-banner">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                    <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm13.36-1.814a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094l3.75-5.25Z" clip-rule="evenodd"/>
                </svg>
                <p>{{ session('success') }}</p>
            </div>
        @endif

        @if (session('error'))
            <div class="error-banner">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                    <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25Zm-1.72 6.97a.75.75 0 1 0-1.06 1.06L10.94 12l-1.72 1.72a.75.75 0 1 0 1.06 1.06L12 13.06l1.72 1.72a.75.75 0 1 0 1.06-1.06L13.06 12l1.72-1.72a.75.75 0 1 0-1.06-1.06L12 10.94l-1.72-1.72Z" clip-rule="evenodd"/>
                </svg>
                <p>{{ session('error') }}</p>
            </div>
        @endif

        <form method="POST" action="{{ route('plans.pro.submit') }}">
            @csrf

            {{-- Datos personales --}}
            <div style="margin-bottom:28px;">
                <p class="form-section-title">Datos del suscriptor</p>

                <div class="form-group">
                    <label class="form-label">Nombre completo <span class="req">*</span></label>
                    <input type="text" name="full_name" class="form-input" value="{{ old('full_name', auth()->user()->name ?? '') }}" placeholder="Tu nombre y apellidos" required>
                    @error('full_name') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Correo electrónico <span class="req">*</span></label>
                        <input type="email" name="email" class="form-input" value="{{ old('email', auth()->user()->email ?? '') }}" placeholder="tu@correo.com" required>
                        @error('email') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Teléfono</label>
                        <input type="tel" name="phone" class="form-input" value="{{ old('phone') }}" placeholder="+52 (XXX) XXX-XXXX">
                        @error('phone') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- Periodo de facturación --}}
            <div style="margin-bottom:28px;">
                <p class="form-section-title">Periodo de facturación</p>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                    <label class="billing-option {{ old('billing_period', 'monthly') === 'monthly' ? 'active' : '' }}" onclick="selectBilling(this)">
                        <input type="radio" name="billing_period" value="monthly" {{ old('billing_period', 'monthly') === 'monthly' ? 'checked' : '' }}>
                        <div>
                            <div class="billing-label">Mensual</div>
                            <div class="billing-desc">$149 MXN / mes</div>
                        </div>
                    </label>
                    <label class="billing-option {{ old('billing_period') === 'annual' ? 'active' : '' }}" onclick="selectBilling(this)">
                        <input type="radio" name="billing_period" value="annual" {{ old('billing_period') === 'annual' ? 'checked' : '' }}>
                        <div>
                            <div class="billing-label">Anual <span style="font-size:.6875rem;color:#16a34a;font-weight:700;background:#f0fdf4;padding:2px 6px;border-radius:4px;margin-left:4px;">-20%</span></div>
                            <div class="billing-desc">$1,430 MXN / año</div>
                        </div>
                    </label>
                </div>
                @error('billing_period') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            {{-- Terms --}}
            <div style="margin-bottom:20px;">
                <label style="display:flex;align-items:flex-start;gap:10px;cursor:pointer;">
                    <input type="checkbox" name="accept_terms" value="1" {{ old('accept_terms') ? 'checked' : '' }} required style="margin-top:3px;width:16px;height:16px;accent-color:#4f46e5;">
                    <span style="font-size:.8125rem;color:#475569;line-height:1.5;">
                        Acepto los <a href="{{ route('legal.terms') }}" target="_blank" style="color:#4f46e5;font-weight:600;">Términos y condiciones</a>, la <a href="{{ route('legal.privacy') }}" target="_blank" style="color:#4f46e5;font-weight:600;">Política de privacidad</a> y el <a href="{{ route('contracts.pro') }}" target="_blank" style="color:#4f46e5;font-weight:600;">Contrato de suscripción Pro</a>.
                    </span>
                </label>
                @error('accept_terms') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <button type="submit" class="form-submit">
                <svg viewBox="0 0 24 24" style="width:22px;height:22px;" fill="none">
                    <rect width="24" height="24" rx="4" fill="#fff"/>
                    <path d="M12 4C7.58 4 4 7.58 4 12s3.58 8 8 8 8-3.58 8-8-3.58-8-8-8Z" fill="#009ee3"/>
                    <path d="M15.5 10.5c0-1.38-1.12-2.5-2.5-2.5h-3v8h1.5v-3H13c1.38 0 2.5-1.12 2.5-2.5Zm-2.5 1H11.5v-2H13c.55 0 1 .45 1 1s-.45 1-1 1Z" fill="#fff"/>
                </svg>
                Pagar con MercadoPago
            </button>

            <div class="mp-badge">
                <svg viewBox="0 0 40 16" style="height:16px;" fill="none">
                    <rect width="40" height="16" rx="3" fill="#009ee3"/>
                    <text x="5" y="12" fill="#fff" font-size="8" font-weight="700" font-family="Inter,sans-serif">MP</text>
                    <text x="17" y="12" fill="#fff" font-size="6" font-family="Inter,sans-serif">Checkout</text>
                </svg>
                <span>Tarjeta, transferencia, OXXO y más</span>
            </div>

            <p style="text-align:center;margin-top:12px;font-size:.75rem;color:#94a3b8;">
                Serás redirigido al checkout seguro de MercadoPago.
            </p>
        </form>
    </div>

    {{-- Right: Summary --}}
    <div class="summary-card">
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;">
            <div style="width:44px;height:44px;border-radius:12px;background:#eef2ff;border:1px solid #c7d2fe;display:flex;align-items:center;justify-content:center;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#4f46e5" stroke-width="1.5" style="width:24px;height:24px;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m3.75 13.5 10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z"/>
                </svg>
            </div>
            <div>
                <h3 style="font-size:1.125rem;font-weight:800;color:#0f172a;">Plan Pro</h3>
                <p style="font-size:.75rem;color:#64748b;">Suscripción individual</p>
            </div>
        </div>

        <div style="margin-bottom:20px;padding-bottom:18px;border-bottom:1px solid #e0e7ff;">
            <span class="summary-price" id="summaryPrice">$149</span>
            <span class="summary-period" id="summaryPeriod"> MXN / mes</span>
        </div>

        <div style="margin-bottom:20px;">
            <p style="font-size:.6875rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.08em;margin-bottom:10px;">Incluye</p>
            @foreach(['Chat con IA (texto y voz)', 'Análisis de emociones detallado', 'Historial de sesiones (últimas 20)', 'Calendario de bienestar', 'Recomendaciones personalizadas'] as $feature)
            <div class="summary-feature">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="#4f46e5"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd"/></svg>
                {{ $feature }}
            </div>
            @endforeach
        </div>

        <div style="margin-bottom:16px;padding:14px;border-radius:12px;background:#fff;border:1px solid #e2e8f0;">
            <p style="font-size:.6875rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;margin-bottom:8px;">Métodos de pago aceptados</p>
            <div style="display:flex;flex-wrap:wrap;gap:6px;">
                <span style="font-size:.6875rem;padding:4px 8px;border-radius:6px;background:#f8fafc;border:1px solid #e2e8f0;color:#475569;font-weight:600;">Visa</span>
                <span style="font-size:.6875rem;padding:4px 8px;border-radius:6px;background:#f8fafc;border:1px solid #e2e8f0;color:#475569;font-weight:600;">Mastercard</span>
                <span style="font-size:.6875rem;padding:4px 8px;border-radius:6px;background:#f8fafc;border:1px solid #e2e8f0;color:#475569;font-weight:600;">AMEX</span>
                <span style="font-size:.6875rem;padding:4px 8px;border-radius:6px;background:#f8fafc;border:1px solid #e2e8f0;color:#475569;font-weight:600;">OXXO</span>
                <span style="font-size:.6875rem;padding:4px 8px;border-radius:6px;background:#f8fafc;border:1px solid #e2e8f0;color:#475569;font-weight:600;">SPEI</span>
                <span style="font-size:.6875rem;padding:4px 8px;border-radius:6px;background:#f8fafc;border:1px solid #e2e8f0;color:#475569;font-weight:600;">Débito</span>
            </div>
        </div>

        <div class="secure-badge">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                <path fill-rule="evenodd" d="M12 1.5a5.25 5.25 0 0 0-5.25 5.25v3a3 3 0 0 0-3 3v6.75a3 3 0 0 0 3 3h10.5a3 3 0 0 0 3-3v-6.75a3 3 0 0 0-3-3v-3c0-2.9-2.35-5.25-5.25-5.25Zm3.75 8.25v-3a3.75 3.75 0 1 0-7.5 0v3h7.5Z" clip-rule="evenodd"/>
            </svg>
            Pago 100% seguro con MercadoPago
        </div>

        <div style="margin-top:16px;padding:12px;border-radius:10px;background:#f8fafc;border:1px solid #e2e8f0;">
            <p style="font-size:.75rem;color:#64748b;line-height:1.6;text-align:center;">
                Puedes cancelar en cualquier momento desde tu panel de usuario.
            </p>
        </div>

        <div style="margin-top:12px;text-align:center;">
            <a href="{{ route('contracts.pro') }}" target="_blank"
               style="font-size:.75rem;color:#6366f1;text-decoration:none;display:inline-flex;align-items:center;gap:4px;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" style="width:13px;height:13px;">
                    <path fill-rule="evenodd" d="M4 2a1.5 1.5 0 0 0-1.5 1.5v9A1.5 1.5 0 0 0 4 14h8a1.5 1.5 0 0 0 1.5-1.5V5.621a1.5 1.5 0 0 0-.44-1.06L9.94 2.439A1.5 1.5 0 0 0 8.878 2H4Zm1 7.75a.75.75 0 0 1 .75-.75h4.5a.75.75 0 0 1 0 1.5h-4.5a.75.75 0 0 1-.75-.75Zm.75-3.25a.75.75 0 0 0 0 1.5h4.5a.75.75 0 0 0 0-1.5h-4.5Z" clip-rule="evenodd"/>
                </svg>
                Ver contrato de suscripción Pro
            </a>
        </div>
    </div>
</div>

<script>
function selectBilling(el) {
    document.querySelectorAll('.billing-option').forEach(function(opt) { opt.classList.remove('active'); });
    el.classList.add('active');
    var period = el.querySelector('input').value;
    document.getElementById('summaryPrice').textContent = period === 'annual' ? '$1,430' : '$149';
    document.getElementById('summaryPeriod').textContent = period === 'annual' ? ' MXN / año' : ' MXN / mes';
}
</script>
@endsection
