<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>@yield('title', 'SuperAdmin') — Mindra</title>
    <link rel="icon" type="image/png" href="/assets/img/mindra1.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Inter',-apple-system,BlinkMacSystemFont,sans-serif; background:#f8fafc; color:#0f172a; -webkit-font-smoothing:antialiased; }
        a { text-decoration:none; color:inherit; }

        .sa-layout { display:flex; min-height:100vh; }

        .sa-sidebar {
            width:260px; background:#0f172a; color:#fff; padding:24px 0; position:fixed;
            top:0; left:0; bottom:0; display:flex; flex-direction:column; z-index:40;
        }
        .sa-sidebar-brand { display:flex; align-items:center; gap:10px; padding:0 24px 24px; border-bottom:1px solid #1e293b; margin-bottom:16px; }
        .sa-sidebar-nav { flex:1; padding:0 12px; display:flex; flex-direction:column; gap:4px; }
        .sa-sidebar-link {
            display:flex; align-items:center; gap:10px; padding:10px 14px; border-radius:10px;
            font-size:.875rem; font-weight:500; color:#94a3b8; transition:all .15s;
        }
        .sa-sidebar-link:hover { background:rgba(255,255,255,.06); color:#e2e8f0; }
        .sa-sidebar-link.active { background:linear-gradient(135deg,rgba(56,189,248,.15),rgba(99,102,241,.15)); color:#fff; font-weight:600; }
        .sa-sidebar-link svg { width:18px; height:18px; flex-shrink:0; }
        .sa-sidebar-footer { padding:16px 24px; border-top:1px solid #1e293b; margin-top:auto; }

        .sa-main { flex:1; margin-left:260px; }
        .sa-topbar {
            background:#fff; border-bottom:1px solid #e2e8f0; padding:16px 32px;
            display:flex; align-items:center; justify-content:space-between; position:sticky; top:0; z-index:30;
        }
        .sa-content { padding:32px; }

        @media (max-width:1024px) {
            .sa-sidebar { display:none; }
            .sa-main { margin-left:0; }
        }
    </style>
    @stack('styles')
</head>
<body>
<div class="sa-layout">

    {{-- Sidebar --}}
    <aside class="sa-sidebar">
        <div class="sa-sidebar-brand">
            <img src="/assets/img/mindra1.png" alt="" style="height:32px;width:auto;">
            <div>
                <p style="font-size:.875rem;font-weight:700;margin:0;">Mindra</p>
                <p style="font-size:.625rem;color:#64748b;margin:0;font-weight:600;">SUPERADMIN</p>
            </div>
        </div>

        <nav class="sa-sidebar-nav">
            <a href="{{ route('superadmin.dashboard') }}" class="sa-sidebar-link {{ request()->routeIs('superadmin.dashboard') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M2.25 13.5a8.25 8.25 0 0 1 8.25-8.25.75.75 0 0 1 .75.75v6.75H18a.75.75 0 0 1 .75.75 8.25 8.25 0 0 1-16.5 0Z" clip-rule="evenodd"/></svg>
                Dashboard
            </a>
            <a href="{{ route('superadmin.users') }}" class="sa-sidebar-link {{ request()->routeIs('superadmin.users*') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M7 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6ZM14.5 9a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5ZM1.615 16.428a1.224 1.224 0 0 1-.569-1.175 6.002 6.002 0 0 1 11.908 0c.058.467-.172.92-.57 1.174A9.953 9.953 0 0 1 7 18a9.953 9.953 0 0 1-5.385-1.572ZM14.5 16h-.106c.07-.297.088-.611.048-.933a7.47 7.47 0 0 0-1.588-3.755 4.502 4.502 0 0 1 5.874 2.636.818.818 0 0 1-.36.98A7.465 7.465 0 0 1 14.5 16Z"/></svg>
                Usuarios
            </a>
            <a href="{{ route('superadmin.institutions') }}" class="sa-sidebar-link {{ request()->routeIs('superadmin.institutions*') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M1 2.75A.75.75 0 0 1 1.75 2h16.5a.75.75 0 0 1 0 1.5H18v12.5h.25a.75.75 0 0 1 0 1.5H1.75a.75.75 0 0 1 0-1.5H2V3.5h-.25A.75.75 0 0 1 1 2.75ZM10 10a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z" clip-rule="evenodd"/></svg>
                Instituciones
            </a>
            <a href="{{ route('superadmin.sessions') }}" class="sa-sidebar-link {{ request()->routeIs('superadmin.sessions*') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 2c-2.236 0-4.43.18-6.57.524C1.993 2.755 1 3.925 1 5.261v5.478c0 1.336.993 2.506 2.43 2.737.527.085 1.058.156 1.592.213l.1.012 1.609 2.796A1 1 0 0 0 7.598 17l2.083-3.62c.15.005.3.008.451.012h-.001c2.236 0 4.43-.18 6.57-.524C18.007 12.637 19 11.467 19 10.131V5.261c0-1.336-.993-2.506-2.43-2.737A32.47 32.47 0 0 0 10 2Z" clip-rule="evenodd"/></svg>
                Sesiones
            </a>
            <a href="{{ route('superadmin.plan-requests') }}" class="sa-sidebar-link {{ request()->routeIs('superadmin.plan-requests*') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-7-4a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM9 9a.75.75 0 0 0 0 1.5h.253a.25.25 0 0 1 .244.304l-.459 2.066A1.75 1.75 0 0 0 10.747 15H11a.75.75 0 0 0 0-1.5h-.253a.25.25 0 0 1-.244-.304l.459-2.066A1.75 1.75 0 0 0 9.253 9H9Z" clip-rule="evenodd"/></svg>
                Solicitudes
            </a>
            <a href="{{ route('superadmin.subscriptions') }}" class="sa-sidebar-link {{ request()->routeIs('superadmin.subscriptions*') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M2.5 4A1.5 1.5 0 0 0 1 5.5V6h18v-.5A1.5 1.5 0 0 0 17.5 4h-15ZM19 8.5H1v6A1.5 1.5 0 0 0 2.5 16h15a1.5 1.5 0 0 0 1.5-1.5v-6ZM3 13.25a.75.75 0 0 1 .75-.75h1.5a.75.75 0 0 1 0 1.5h-1.5a.75.75 0 0 1-.75-.75Zm4.75-.75a.75.75 0 0 0 0 1.5h3.5a.75.75 0 0 0 0-1.5h-3.5Z" clip-rule="evenodd"/></svg>
                Suscripciones
            </a>
            <a href="{{ route('superadmin.pro-orders') }}" class="sa-sidebar-link {{ request()->routeIs('superadmin.pro-orders*') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="m3.75 13.5 10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z" clip-rule="evenodd"/></svg>
                Solicitudes Plus
            </a>
            <a href="{{ route('superadmin.groups') }}" class="sa-sidebar-link {{ request()->routeIs('superadmin.groups*') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M10 9a3 3 0 1 0 0-6 3 3 0 0 0 0 6ZM6 8a2 2 0 1 1-4 0 2 2 0 0 1 4 0ZM1.49 15.326a.78.78 0 0 1-.358-.442 3 3 0 0 1 4.308-3.516 6.484 6.484 0 0 0-1.905 3.959c-.023.222-.014.442.025.654a4.97 4.97 0 0 1-2.07-.655ZM16.44 15.98a4.97 4.97 0 0 0 2.07-.654.78.78 0 0 0 .357-.442 3 3 0 0 0-4.308-3.517 6.484 6.484 0 0 1 1.907 3.96 2.32 2.32 0 0 1-.026.654ZM18 8a2 2 0 1 1-4 0 2 2 0 0 1 4 0ZM5.304 16.19a.844.844 0 0 1-.277-.71 5 5 0 0 1 9.947 0 .843.843 0 0 1-.277.71A6.975 6.975 0 0 1 10 18a6.974 6.974 0 0 1-4.696-1.81Z"/></svg>
                Grupos
            </a>

            <div style="margin-top:20px;padding-top:16px;border-top:1px solid #1e293b;">
                <a href="{{ route('chat') }}" class="sa-sidebar-link">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 2c-2.236 0-4.43.18-6.57.524C1.993 2.755 1 3.925 1 5.261v5.478c0 1.336.993 2.506 2.43 2.737.527.085 1.058.156 1.592.213l.1.012 1.609 2.796A1 1 0 0 0 7.598 17l2.083-3.62c.15.005.3.008.451.012h-.001c2.236 0 4.43-.18 6.57-.524C18.007 12.637 19 11.467 19 10.131V5.261c0-1.336-.993-2.506-2.43-2.737A32.47 32.47 0 0 0 10 2Z" clip-rule="evenodd"/></svg>
                    Chat
                </a>
                <a href="{{ route('home') }}" class="sa-sidebar-link">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9.293 2.293a1 1 0 0 1 1.414 0l7 7A1 1 0 0 1 17 11h-1v6a1 1 0 0 1-1 1h-2a1 1 0 0 1-1-1v-3a1 1 0 0 0-1-1H9a1 1 0 0 0-1 1v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-6H3a1 1 0 0 1-.707-1.707l7-7Z" clip-rule="evenodd"/></svg>
                    Inicio
                </a>
            </div>
        </nav>

        <div class="sa-sidebar-footer">
            <div style="display:flex;align-items:center;gap:10px;">
                <div style="width:32px;height:32px;border-radius:9999px;background:linear-gradient(135deg,#38bdf8,#6366f1,#9333ea);display:flex;align-items:center;justify-content:center;color:#fff;font-size:.75rem;font-weight:700;">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div style="flex:1;min-width:0;">
                    <p style="font-size:.8125rem;font-weight:600;color:#e2e8f0;margin:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ auth()->user()->name }}</p>
                    <p style="font-size:.6875rem;color:#64748b;margin:0;">SuperAdmin</p>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}" style="margin-top:12px;">
                @csrf
                <button type="submit" style="width:100%;padding:8px;border:1px solid #1e293b;border-radius:8px;background:transparent;color:#94a3b8;font-size:.75rem;font-weight:600;cursor:pointer;transition:all .15s;"
                        onmouseover="this.style.background='#1e293b';this.style.color='#fff'" onmouseout="this.style.background='transparent';this.style.color='#94a3b8'">
                    Cerrar sesión
                </button>
            </form>
        </div>
    </aside>

    {{-- Main --}}
    <div class="sa-main">
        <header class="sa-topbar">
            <h1 style="font-size:1.25rem;font-weight:800;color:#0f172a;">@yield('title', 'SuperAdmin')</h1>
            <div style="display:flex;align-items:center;gap:12px;">
                <span style="font-size:.75rem;color:#94a3b8;">{{ now()->format('d M Y, H:i') }}</span>
            </div>
        </header>

        <div class="sa-content">
            @if(session('success'))
                <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:12px;padding:14px 18px;margin-bottom:20px;display:flex;align-items:flex-start;gap:10px;">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="#16a34a" style="width:18px;height:18px;flex-shrink:0;margin-top:1px;"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd"/></svg>
                    <div style="flex:1;">
                        @if(is_array(session('success')))
                            <p style="font-size:.875rem;font-weight:700;color:#15803d;margin:0 0 6px;">{{ session('success')['title'] }}</p>
                            @foreach(session('success')['lines'] as $line)
                                <p style="font-size:.8125rem;color:#166534;margin:2px 0;">{{ $line }}</p>
                            @endforeach
                        @else
                            <p style="font-size:.875rem;color:#15803d;margin:0;">{{ session('success') }}</p>
                        @endif
                    </div>
                </div>
            @endif

            @if(session('info'))
                <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:12px;padding:14px 18px;margin-bottom:20px;display:flex;align-items:flex-start;gap:10px;">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="#2563eb" style="width:18px;height:18px;flex-shrink:0;margin-top:1px;"><path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-7-4a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM9 9a.75.75 0 0 0 0 1.5h.253a.25.25 0 0 1 .244.304l-.459 2.066A1.75 1.75 0 0 0 10.747 15H11a.75.75 0 0 0 0-1.5h-.253a.25.25 0 0 1-.244-.304l.459-2.066A1.75 1.75 0 0 0 9.253 9H9Z" clip-rule="evenodd"/></svg>
                    <div style="flex:1;">
                        @if(is_array(session('info')))
                            <p style="font-size:.875rem;font-weight:700;color:#1d4ed8;margin:0 0 6px;">{{ session('info')['title'] }}</p>
                            @foreach(session('info')['lines'] as $line)
                                <p style="font-size:.8125rem;color:#1e40af;margin:2px 0;">{{ $line }}</p>
                            @endforeach
                        @else
                            <p style="font-size:.875rem;color:#1d4ed8;margin:0;">{{ session('info') }}</p>
                        @endif
                    </div>
                </div>
            @endif

            @if(session('warning'))
                <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:12px;padding:14px 18px;margin-bottom:20px;display:flex;align-items:flex-start;gap:10px;">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="#d97706" style="width:18px;height:18px;flex-shrink:0;margin-top:1px;"><path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495ZM10 5a.75.75 0 0 1 .75.75v3.5a.75.75 0 0 1-1.5 0v-3.5A.75.75 0 0 1 10 5Zm0 9a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd"/></svg>
                    <div style="flex:1;">
                        @if(is_array(session('warning')))
                            <p style="font-size:.875rem;font-weight:700;color:#b45309;margin:0 0 6px;">{{ session('warning')['title'] }}</p>
                            @foreach(session('warning')['lines'] as $line)
                                <p style="font-size:.8125rem;color:#92400e;margin:2px 0;">{{ $line }}</p>
                            @endforeach
                        @else
                            <p style="font-size:.875rem;color:#b45309;margin:0;">{{ session('warning') }}</p>
                        @endif
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:12px;padding:14px 18px;margin-bottom:20px;display:flex;align-items:flex-start;gap:10px;">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="#dc2626" style="width:18px;height:18px;flex-shrink:0;margin-top:1px;"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16ZM8.28 7.22a.75.75 0 0 0-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 1 0 1.06 1.06L10 11.06l1.72 1.72a.75.75 0 1 0 1.06-1.06L11.06 10l1.72-1.72a.75.75 0 0 0-1.06-1.06L10 8.94 8.28 7.22Z" clip-rule="evenodd"/></svg>
                    <div style="flex:1;">
                        @if(is_array(session('error')))
                            <p style="font-size:.875rem;font-weight:700;color:#dc2626;margin:0 0 6px;">{{ session('error')['title'] }}</p>
                            @foreach(session('error')['lines'] as $line)
                                <p style="font-size:.8125rem;color:#b91c1c;margin:2px 0;">{{ $line }}</p>
                            @endforeach
                        @else
                            <p style="font-size:.875rem;color:#dc2626;margin:0;">{{ session('error') }}</p>
                        @endif
                    </div>
                </div>
            @endif

            @yield('panel')
        </div>
    </div>
</div>
</body>
</html>
