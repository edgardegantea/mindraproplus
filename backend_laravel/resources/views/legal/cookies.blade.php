@extends('layouts.app')
@section('title', 'Política de Cookies')

@push('styles')
<style>
    .legal-body { max-width: 48rem; margin-left: auto; margin-right: auto; }
    .legal-section { margin-bottom: 2rem; }
    .legal-section h2 { font-size: 1.25rem; font-weight: 700; color: #1e293b; margin: 1.5rem 0 .75rem; }
    .legal-section p, .legal-section li { font-size: 1rem; color: #475569; line-height: 1.75; }
    .legal-section ul { padding-left: 1.25rem; margin: .5rem 0; display: flex; flex-direction: column; gap: .35rem; }
    .cookie-table { width: 100%; border-collapse: collapse; font-size: .8125rem; }
    .cookie-table th { background: #f8fafc; color: #64748b; font-weight: 600; padding: 8px 12px; text-align: left; border-bottom: 2px solid #e2e8f0; }
    .cookie-table td { padding: 9px 12px; border-bottom: 1px solid #f1f5f9; color: #475569; vertical-align: top; }
    .cookie-table tr:last-child td { border-bottom: none; }
</style>
@endpush

@section('content')

@include('legal._header', [
    'icon'     => 'M10 2a.75.75 0 0 1 .75.75v.258a33.186 33.186 0 0 1 6.668 2.354l.262-.752a.75.75 0 0 1 1.416.494l-4 11.5a.75.75 0 0 1-1.416-.494l.82-2.355a30.895 30.895 0 0 0-3.5-1.198v.242a.75.75 0 0 1-1.5 0v-.242a30.9 30.9 0 0 0-3.5 1.198l.82 2.355a.75.75 0 0 1-1.416.494l-4-11.5a.75.75 0 0 1 1.416-.494l.262.752A33.17 33.17 0 0 1 9.25 3.008V2.75A.75.75 0 0 1 10 2Z',
    'title'    => 'Política de Cookies',
    'subtitle' => 'Qué cookies usamos y para qué',
    'color'    => 'amber',
])

<div class="legal-body">

    <div class="legal-section">
        <h2>¿Qué son las cookies?</h2>
        <p>Las cookies son pequeños archivos de texto que un sitio web almacena en tu dispositivo cuando lo visitas. Se usan para mantener sesiones activas, recordar preferencias y recopilar métricas de uso.</p>
    </div>

    <div class="legal-section">
        <h2>Cookies que utiliza Mindra</h2>
        <p style="margin-bottom:1rem;">Mindra utiliza exclusivamente cookies <strong>estrictamente necesarias</strong>. No empleamos cookies de seguimiento publicitario ni de terceros.</p>

        <div style="border-radius:12px;border:1px solid #e2e8f0;overflow:hidden;">
            <table class="cookie-table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Tipo</th>
                        <th>Duración</th>
                        <th>Finalidad</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code style="background:#f1f5f9;padding:2px 6px;border-radius:4px;font-size:.75rem;">mindra_session</code></td>
                        <td>Sesión</td>
                        <td>2 horas</td>
                        <td>Mantiene la sesión autenticada del usuario durante la navegación.</td>
                    </tr>
                    <tr>
                        <td><code style="background:#f1f5f9;padding:2px 6px;border-radius:4px;font-size:.75rem;">XSRF-TOKEN</code></td>
                        <td>Seguridad</td>
                        <td>Sesión</td>
                        <td>Token de protección contra ataques CSRF (falsificación de petición).</td>
                    </tr>
                    <tr>
                        <td><code style="background:#f1f5f9;padding:2px 6px;border-radius:4px;font-size:.75rem;">remember_web_*</code></td>
                        <td>Persistencia</td>
                        <td>30 días</td>
                        <td>Permite mantener la sesión activa si el usuario eligió "recordarme".</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="legal-section">
        <h2>Cookies de terceros</h2>
        <p>Mindra <strong>no instala cookies de terceros</strong>. No utilizamos Google Analytics, Meta Pixel ni ningún otro sistema de rastreo externo.</p>
    </div>

    <div class="legal-section">
        <h2>Cómo gestionar o eliminar cookies</h2>
        <p>Puedes eliminar o bloquear cookies desde la configuración de tu navegador:</p>
        <ul>
            <li><strong>Chrome:</strong> Configuración → Privacidad y seguridad → Cookies.</li>
            <li><strong>Firefox:</strong> Preferencias → Privacidad y seguridad → Cookies y datos del sitio.</li>
            <li><strong>Safari:</strong> Preferencias → Privacidad → Gestionar datos de sitios web.</li>
            <li><strong>Edge:</strong> Configuración → Cookies y permisos de sitio.</li>
        </ul>
        <p style="margin-top:.75rem;">Ten en cuenta que bloquear las cookies de sesión impedirá que puedas iniciar sesión en la plataforma.</p>
    </div>

    <div class="legal-section">
        <h2>Consentimiento</h2>
        <p>Al iniciar sesión en Mindra aceptas el uso de las cookies estrictamente necesarias descritas en esta política. No requerimos tu consentimiento explícito adicional para estas cookies, ya que son indispensables para el funcionamiento de la plataforma.</p>
    </div>

</div>

@include('legal._footer_links')
@endsection
