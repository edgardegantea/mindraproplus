<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>@yield('title', 'Admin') — Mindra</title>
    <link rel="icon" type="image/png" href="/assets/img/mindra1.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Inter',-apple-system,BlinkMacSystemFont,sans-serif; background:#f8fafc; color:#0f172a; -webkit-font-smoothing:antialiased; }
        a { text-decoration:none; color:inherit; }

        .adm-layout { display:flex; min-height:100vh; }
        .adm-sidebar {
            width:260px; background:#0f172a; color:#fff; padding:24px 0; position:fixed;
            top:0; left:0; bottom:0; display:flex; flex-direction:column; z-index:40;
        }
        .adm-sidebar-brand { display:flex; align-items:center; gap:10px; padding:0 24px 20px; border-bottom:1px solid #1e293b; margin-bottom:16px; }
        .adm-sidebar-nav { flex:1; padding:0 12px; display:flex; flex-direction:column; gap:4px; }
        .adm-link {
            display:flex; align-items:center; gap:10px; padding:10px 14px; border-radius:10px;
            font-size:.875rem; font-weight:500; color:#94a3b8; transition:all .15s;
        }
        .adm-link:hover { background:rgba(255,255,255,.06); color:#e2e8f0; }
        .adm-link.active { background:linear-gradient(135deg,rgba(56,189,248,.15),rgba(99,102,241,.15)); color:#fff; font-weight:600; }
        .adm-link svg { width:18px; height:18px; flex-shrink:0; }
        .adm-sidebar-footer { padding:16px 24px; border-top:1px solid #1e293b; margin-top:auto; }
        .adm-main { flex:1; margin-left:260px; }
        .adm-topbar {
            background:#fff; border-bottom:1px solid #e2e8f0; padding:16px 32px;
            display:flex; align-items:center; justify-content:space-between; position:sticky; top:0; z-index:30;
        }
        .adm-content { padding:32px; }

        @media (max-width:1024px) {
            .adm-sidebar { display:none; }
            .adm-main { margin-left:0; }
        }
    </style>
    @stack('styles')
</head>
<body>
<div class="adm-layout">

    <aside class="adm-sidebar">
        <div class="adm-sidebar-brand">
            <img src="/assets/img/mindra1.png" alt="" style="height:32px;width:auto;">
            <div>
                <p style="font-size:.875rem;font-weight:700;margin:0;">Mindra</p>
                <p style="font-size:.625rem;color:#64748b;margin:0;font-weight:600;">ADMINISTRADOR</p>
            </div>
        </div>

        <nav class="adm-sidebar-nav">
            <a href="{{ route('admin.dashboard') }}" class="adm-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M2.25 13.5a8.25 8.25 0 0 1 8.25-8.25.75.75 0 0 1 .75.75v6.75H18a.75.75 0 0 1 .75.75 8.25 8.25 0 0 1-16.5 0Z" clip-rule="evenodd"/></svg>
                Dashboard
            </a>
            <a href="{{ route('admin.users') }}" class="adm-link {{ request()->routeIs('admin.users') || request()->routeIs('admin.user') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M7 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6ZM14.5 9a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5ZM1.615 16.428a1.224 1.224 0 0 1-.569-1.175 6.002 6.002 0 0 1 11.908 0c.058.467-.172.92-.57 1.174A9.953 9.953 0 0 1 7 18a9.953 9.953 0 0 1-5.385-1.572ZM14.5 16h-.106c.07-.297.088-.611.048-.933a7.47 7.47 0 0 0-1.588-3.755 4.502 4.502 0 0 1 5.874 2.636.818.818 0 0 1-.36.98A7.465 7.465 0 0 1 14.5 16Z"/></svg>
                Usuarios
            </a>
            <a href="{{ route('admin.sessions') }}" class="adm-link {{ request()->routeIs('admin.sessions') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 2c-2.236 0-4.43.18-6.57.524C1.993 2.755 1 3.925 1 5.261v5.478c0 1.336.993 2.506 2.43 2.737.527.085 1.058.156 1.592.213l.1.012 1.609 2.796A1 1 0 0 0 7.598 17l2.083-3.62c.15.005.3.008.451.012h-.001c2.236 0 4.43-.18 6.57-.524C18.007 12.637 19 11.467 19 10.131V5.261c0-1.336-.993-2.506-2.43-2.737A32.47 32.47 0 0 0 10 2Z" clip-rule="evenodd"/></svg>
                Sesiones
            </a>
            <a href="{{ route('admin.reports') }}" class="adm-link {{ request()->routeIs('admin.reports') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M1 2.75A.75.75 0 0 1 1.75 2h16.5a.75.75 0 0 1 .75.75v14.5a.75.75 0 0 1-.75.75H1.75a.75.75 0 0 1-.75-.75V2.75ZM5 14a1 1 0 0 1 1-1h1a1 1 0 1 1 0 2H6a1 1 0 0 1-1-1Zm4-3a1 1 0 0 1 1-1h1a1 1 0 1 1 0 2h-1a1 1 0 0 1-1-1Zm5-4a1 1 0 0 0-1 1v6a1 1 0 1 0 2 0V8a1 1 0 0 0-1-1Z" clip-rule="evenodd"/></svg>
                Reportes
            </a>
            <a href="{{ route('admin.institution') }}" class="adm-link {{ request()->routeIs('admin.institution') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M1 2.75A.75.75 0 0 1 1.75 2h16.5a.75.75 0 0 1 0 1.5H18v12.5h.25a.75.75 0 0 1 0 1.5H1.75a.75.75 0 0 1 0-1.5H2V3.5h-.25A.75.75 0 0 1 1 2.75ZM10 10a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z" clip-rule="evenodd"/></svg>
                Institución
            </a>

            <div style="margin-top:20px;padding-top:16px;border-top:1px solid #1e293b;">
                <a href="{{ route('chat') }}" class="adm-link">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 2c-2.236 0-4.43.18-6.57.524C1.993 2.755 1 3.925 1 5.261v5.478c0 1.336.993 2.506 2.43 2.737.527.085 1.058.156 1.592.213l.1.012 1.609 2.796A1 1 0 0 0 7.598 17l2.083-3.62c.15.005.3.008.451.012h-.001c2.236 0 4.43-.18 6.57-.524C18.007 12.637 19 11.467 19 10.131V5.261c0-1.336-.993-2.506-2.43-2.737A32.47 32.47 0 0 0 10 2Z" clip-rule="evenodd"/></svg>
                    Chat Mindra
                </a>
                <a href="{{ route('home') }}" class="adm-link">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9.293 2.293a1 1 0 0 1 1.414 0l7 7A1 1 0 0 1 17 11h-1v6a1 1 0 0 1-1 1h-2a1 1 0 0 1-1-1v-3a1 1 0 0 0-1-1H9a1 1 0 0 0-1 1v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-6H3a1 1 0 0 1-.707-1.707l7-7Z" clip-rule="evenodd"/></svg>
                    Inicio
                </a>
            </div>
        </nav>

        <div class="adm-sidebar-footer">
            <div style="display:flex;align-items:center;gap:10px;">
                <div style="width:32px;height:32px;border-radius:9999px;background:linear-gradient(135deg,#38bdf8,#6366f1,#9333ea);display:flex;align-items:center;justify-content:center;color:#fff;font-size:.75rem;font-weight:700;">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div style="flex:1;min-width:0;">
                    <p style="font-size:.8125rem;font-weight:600;color:#e2e8f0;margin:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ auth()->user()->name }}</p>
                    <p style="font-size:.6875rem;color:#64748b;margin:0;">{{ auth()->user()->institution?->name ?? 'Admin' }}</p>
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

    <div class="adm-main">
        <header class="adm-topbar">
            <h1 style="font-size:1.25rem;font-weight:800;color:#0f172a;">@yield('title', 'Panel Admin')</h1>
            <span style="font-size:.75rem;color:#94a3b8;">{{ now()->format('d M Y, H:i') }}</span>
        </header>

        <div class="adm-content">
            @if(session('success'))
                <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:12px;padding:14px 18px;margin-bottom:20px;display:flex;align-items:center;gap:10px;">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="#16a34a" style="width:18px;height:18px;flex-shrink:0;"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd"/></svg>
                    <p style="font-size:.875rem;color:#15803d;margin:0;">{{ session('success') }}</p>
                </div>
            @endif
            @yield('panel')
        </div>
    </div>
</div>
</body>
</html>
