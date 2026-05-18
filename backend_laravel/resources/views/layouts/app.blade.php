<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>@yield('title', 'Mindra') — {{ config('app.name', 'Mindra') }}</title>
    <link rel="icon" type="image/png" href="/assets/img/mindra1.png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>(function(){var t=localStorage.getItem('mindra_theme')||'light';var r=t==='auto'?(window.matchMedia('(prefers-color-scheme:dark)').matches?'dark':'light'):t;document.documentElement.setAttribute('data-theme',r);document.documentElement.setAttribute('data-font',localStorage.getItem('mindra_font')||'normal');document.documentElement.setAttribute('data-contrast',localStorage.getItem('mindra_contrast')==='1'?'high':'normal');document.documentElement.setAttribute('data-motion',localStorage.getItem('mindra_motion')==='1'?'reduced':'normal');})();</script>
</head>
<body class="min-h-screen bg-slate-50 text-slate-800">

    <nav class="bg-white border-b border-slate-200">
        <div class="mx-auto px-4 h-14 flex items-center justify-between" style="max-width:80rem;">
            <a href="{{ route('home') }}" style="display:flex;align-items:center;gap:8px;text-decoration:none;">
                <img src="/assets/img/mindra1.png" alt="" style="height:40px;width:auto;">
                <img src="/assets/img/mindra2.png" alt="Mindra" style="height:80px;width:auto;">
            </a>
            <div class="flex items-center gap-5 text-sm">
                <a href="{{ route('chat') }}"
                   class="font-medium {{ request()->routeIs('chat') ? 'text-indigo-600' : 'text-slate-500 hover:text-slate-800' }} transition-colors">
                    Chat
                </a>
                <a href="{{ route('dashboard') }}"
                   class="font-medium {{ request()->routeIs('dashboard') ? 'text-indigo-600' : 'text-slate-500 hover:text-slate-800' }} transition-colors">
                    Historial
                </a>
                @auth
                    @if (auth()->user()->isSuperAdmin())
                        <a href="{{ route('superadmin.dashboard') }}"
                           class="font-medium {{ request()->routeIs('superadmin.*') ? 'text-indigo-600' : 'text-slate-500 hover:text-slate-800' }} transition-colors">
                            SuperAdmin
                        </a>
                    @elseif (auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}"
                           class="font-medium {{ request()->routeIs('admin.*') ? 'text-indigo-600' : 'text-slate-500 hover:text-slate-800' }} transition-colors">
                            Panel Admin
                        </a>
                    @endif
                @endauth
                <span class="text-slate-300">|</span>
                @auth
                    <span class="text-slate-400">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="text-slate-400 hover:text-red-500 transition-colors">
                            Salir
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="text-slate-400 hover:text-indigo-600 transition-colors">Entrar</a>
                @endauth
            </div>
        </div>
    </nav>

    @stack('styles')
    <main class="mx-auto px-4 py-8" style="max-width:80rem;">
        @yield('content')
    </main>

    {{-- ── Footer ──────────────────────────────────────────────────────────────── --}}
    <footer style="background:#fff;border-top:1px solid #e8edf5;margin-top:auto;">

        {{-- Franja principal --}}
        <div style="max-width:80rem;margin:0 auto;padding:2.5rem 1.5rem 2rem;">
            <div style="display:grid;grid-template-columns:1fr;gap:2rem;">

                {{-- Columna marca --}}
                <div style="display:flex;flex-direction:column;gap:12px;">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <img src="/assets/img/mindra1.png" alt="" style="height:36px;width:auto;">
                        <img src="/assets/img/mindra2.png" alt="Mindra" style="height:40px;width:auto;">
                    </div>
                    <p style="font-size:.8125rem;color:#64748b;line-height:1.6;max-width:320px;">
                    </p>
                    <div style="display:flex;gap:8px;flex-wrap:wrap;">
                        <span style="display:inline-flex;align-items:center;gap:4px;font-size:.6875rem;padding:3px 9px;border-radius:9999px;background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0;font-weight:500;">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" style="width:10px;height:10px;">
                                <path fill-rule="evenodd" d="M8 1a3.5 3.5 0 1 1 0 7 3.5 3.5 0 0 1 0-7ZM4.5 8A4.5 4.5 0 0 0 0 12.5V14h16v-1.5A4.5 4.5 0 0 0 11.5 8h-7Z" clip-rule="evenodd"/>
                            </svg>
                            Salud Mental
                        </span>
                        <span style="display:inline-flex;align-items:center;gap:4px;font-size:.6875rem;padding:3px 9px;border-radius:9999px;background:#eef2ff;color:#4338ca;border:1px solid #c7d2fe;font-weight:500;">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" style="width:10px;height:10px;">
                                <path fill-rule="evenodd" d="M8 1a5 5 0 0 0-3.536 8.536A5 5 0 1 0 8 1Zm0 9a4 4 0 1 1 0-8 4 4 0 0 1 0 8Z" clip-rule="evenodd"/>
                            </svg>
                            Inteligencia Artificial
                        </span>
                    </div>
                </div>

                {{-- Columnas de enlaces (envueltas en flex responsive) --}}
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:1.5rem;">

                    {{-- Plataforma --}}
                    <div>
                        <p style="font-size:.6875rem;font-weight:700;color:#1e293b;text-transform:uppercase;letter-spacing:.07em;margin:0 0 10px;">Plataforma</p>
                        <ul style="list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:7px;">
                            <li><a href="{{ route('chat') }}"
                                   style="font-size:.8125rem;color:#64748b;text-decoration:none;"
                                   onmouseover="this.style.color='var(--accent,#4f46e5)'" onmouseout="this.style.color='var(--text-muted,#64748b)'">Chat con Mindra</a></li>
                            <li><a href="{{ route('dashboard') }}"
                                   style="font-size:.8125rem;color:#64748b;text-decoration:none;"
                                   onmouseover="this.style.color='var(--accent,#4f46e5)'" onmouseout="this.style.color='var(--text-muted,#64748b)'">Mi historial</a></li>
                        </ul>
                    </div>

                    {{-- Privacidad --}}
                    <div>
                        <p style="font-size:.6875rem;font-weight:700;color:#1e293b;text-transform:uppercase;letter-spacing:.07em;margin:0 0 10px;">Privacidad</p>
                        <ul style="list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:7px;">
                            <li><a href="{{ route('legal.privacy') }}"
                                   style="font-size:.8125rem;color:#64748b;text-decoration:none;"
                                   onmouseover="this.style.color='var(--accent,#4f46e5)'" onmouseout="this.style.color='var(--text-muted,#64748b)'">Política de privacidad</a></li>
                            <li><a href="{{ route('legal.data-usage') }}"
                                   style="font-size:.8125rem;color:#64748b;text-decoration:none;"
                                   onmouseover="this.style.color='var(--accent,#4f46e5)'" onmouseout="this.style.color='var(--text-muted,#64748b)'">Uso de datos</a></li>
                            <li><a href="{{ route('legal.cookies') }}"
                                   style="font-size:.8125rem;color:#64748b;text-decoration:none;"
                                   onmouseover="this.style.color='var(--accent,#4f46e5)'" onmouseout="this.style.color='var(--text-muted,#64748b)'">Política de cookies</a></li>
                        </ul>
                    </div>

                    {{-- Legal --}}
                    <div>
                        <p style="font-size:.6875rem;font-weight:700;color:#1e293b;text-transform:uppercase;letter-spacing:.07em;margin:0 0 10px;">Legal</p>
                        <ul style="list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:7px;">
                            <li><a href="{{ route('legal.privacy') }}"
                                   style="font-size:.8125rem;color:#64748b;text-decoration:none;"
                                   onmouseover="this.style.color='var(--accent,#4f46e5)'" onmouseout="this.style.color='var(--text-muted,#64748b)'">Política de privacidad</a></li>
                            <li><a href="{{ route('legal.terms') }}"
                                   style="font-size:.8125rem;color:#64748b;text-decoration:none;"
                                   onmouseover="this.style.color='var(--accent,#4f46e5)'" onmouseout="this.style.color='var(--text-muted,#64748b)'">Términos de uso</a></li>
                            <li><a href="{{ route('legal.consent') }}"
                                   style="font-size:.8125rem;color:#64748b;text-decoration:none;"
                                   onmouseover="this.style.color='var(--accent,#4f46e5)'" onmouseout="this.style.color='var(--text-muted,#64748b)'">Consentimiento informado</a></li>
                        </ul>
                    </div>

                    {{-- Contacto --}}
                    <div>
                        <p style="font-size:.6875rem;font-weight:700;color:#1e293b;text-transform:uppercase;letter-spacing:.07em;margin:0 0 10px;">Contacto</p>
                        <ul style="list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:7px;">
                            <li style="font-size:.8125rem;color:#64748b;">Laboratorio Computación Afectiva e Innovación educativa</li>
                            <li style="font-size:.8125rem;color:#64748b;">cafined@itsm.edu.mx</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        {{-- Aviso de privacidad resumido --}}
        <div style="max-width:80rem;margin:0 auto;padding:0 1.5rem 1.5rem;">
            <div style="border-radius:14px;background:#f8fafc;border:1px solid #e8edf5;padding:14px 18px;display:flex;align-items:flex-start;gap:12px;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                     style="width:16px;height:16px;flex-shrink:0;color:#6366f1;margin-top:1px;">
                    <path fill-rule="evenodd" d="M10 1a4.5 4.5 0 0 0-4.5 4.5V9H5a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2h-.5V5.5A4.5 4.5 0 0 0 10 1Zm3 8V5.5a3 3 0 1 0-6 0V9h6Z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <p style="font-size:.75rem;font-weight:600;color:#1e293b;margin:0 0 4px;">Privacidad y uso de datos</p>
                    <p style="font-size:.75rem;color:#64748b;line-height:1.6;margin:0;">
                        Los textos y audios que compartes con Mindra se procesan de forma
                        <strong style="color:#475569;">confidencial</strong> y se utilizan
                        exclusivamente para mejorar tu experiencia y brindarte resultados precisos.
                        <strong style="color:#475569;">No se comparten con terceros</strong>.
                        Puedes solicitar la eliminación de tus datos en cualquier momento.
                        Al usar esta plataforma aceptas el tratamiento de tus datos en los términos descritos.
                    </p>
                </div>
            </div>
        </div>

        {{-- Barra inferior --}}
        <div style="border-top:1px solid #e8edf5;background:#f8fafc;">
            <div style="max-width:80rem;margin:0 auto;padding:.875rem 1.5rem;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">
                <span style="font-size:.75rem;color:#94a3b8;">
                    © {{ date('Y') }} Mindra. Todos los derechos reservados.
                </span>
                <div style="display:flex;align-items:center;gap:14px;">
                    <span style="font-size:.6875rem;color:#cbd5e1;">Versión 1.0</span>
                    <span style="display:inline-flex;align-items:center;gap:4px;font-size:.6875rem;color:#94a3b8;">
                        <span style="width:6px;height:6px;border-radius:9999px;background:#4ade80;display:inline-block;"></span>
                        Servicio activo
                    </span>
                </div>
            </div>
        </div>

    </footer>

@include('partials.accessibility')
</body>
</html>
