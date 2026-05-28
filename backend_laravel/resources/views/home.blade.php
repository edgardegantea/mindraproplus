<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Mindra — IA para el Bienestar Emocional</title>
    <link rel="icon" type="image/png" href="/assets/img/mindra1.png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script>
    (function(){var t=localStorage.getItem('mindra_theme')||'light';var r=t==='auto'?(window.matchMedia('(prefers-color-scheme:dark)').matches?'dark':'light'):t;document.documentElement.setAttribute('data-theme',r);document.documentElement.setAttribute('data-font',localStorage.getItem('mindra_font')||'normal');document.documentElement.setAttribute('data-contrast',localStorage.getItem('mindra_contrast')==='1'?'high':'normal');document.documentElement.setAttribute('data-motion',localStorage.getItem('mindra_motion')==='1'?'reduced':'normal');})();
    </script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; color: #0f172a; -webkit-font-smoothing: antialiased; }
        a { text-decoration: none; color: inherit; }
        img { display: block; max-width: 100%; }

        .glass-nav {
            position: fixed; top: 0; left: 0; right: 0; z-index: 50;
            background: rgba(255,255,255,.85);
            backdrop-filter: blur(14px); -webkit-backdrop-filter: blur(14px);
            border-bottom: 1px solid rgba(226,232,240,.6);
        }
        .nav-inner {
            max-width: 80rem; margin: 0 auto; padding: 0 2rem;
            height: 64px; display: flex; align-items: center; justify-content: space-between;
        }
        .nav-brand { display: flex; align-items: center; gap: 10px; }
        .nav-links { display: flex; align-items: center; gap: 24px; font-size: .8125rem; font-weight: 600; }
        .nav-links a { color: #64748b; transition: color .15s; }
        .nav-links a:hover { color: #4f46e5; }
        .nav-divider { width: 1px; height: 16px; background: #e2e8f0; }
        .btn-primary {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            padding: 10px 22px; border-radius: 12px; font-size: .875rem; font-weight: 700;
            background: linear-gradient(135deg, #38bdf8, #6366f1, #9333ea); color: #fff;
            box-shadow: 0 4px 14px rgba(99,102,241,.35); transition: all .2s;
        }
        .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(147,51,234,.4); }
        .btn-secondary {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            padding: 10px 22px; border-radius: 12px; font-size: .875rem; font-weight: 700;
            background: var(--bg-card, #fff); color: var(--text-secondary, #334155);
            border: 1.5px solid var(--border-default, #e2e8f0); transition: all .2s;
        }
        .btn-secondary:hover { border-color: #a78bfa; background: var(--accent-light, #f5f3ff); }
        .btn-dark {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            padding: 12px 28px; border-radius: 14px; font-size: .9375rem; font-weight: 700;
            background: #0f172a; color: #fff; transition: all .2s;
        }
        .btn-dark:hover { background: linear-gradient(135deg, #38bdf8, #6366f1, #9333ea); }

        @keyframes fadeUp { from { opacity:0; transform:translateY(16px); } to { opacity:1; transform:translateY(0); } }
        @keyframes float { 0%,100% { transform:translateY(0); } 50% { transform:translateY(-8px); } }
        .anim-fade { animation: fadeUp .6s ease-out both; }
        .anim-fade-d1 { animation-delay: .1s; }
        .anim-fade-d2 { animation-delay: .25s; }
        .anim-fade-d3 { animation-delay: .4s; }

        @media (max-width: 768px) {
            .hero-grid { flex-direction: column !important; }
            .hero-text { text-align: center; }
            .hero-text h1 { font-size: 2.25rem !important; }
            .hero-ctas { justify-content: center !important; }
            .steps-grid { grid-template-columns: 1fr !important; }
            .features-grid { grid-template-columns: 1fr !important; }
            .cta-title { font-size: 2rem !important; }
            .nav-links-desktop { display: none !important; }
        }
    </style>
</head>
<body>

{{-- ── Nav ──────────────────────────────────────────────────────────────────── --}}
<header class="glass-nav">
    <div class="nav-inner">
        <a href="{{ route('home') }}" class="nav-brand">
            <img src="/assets/img/mindra1.png" alt="" style="height:40px;width:auto;">
            <img src="/assets/img/mindra2.png" alt="Mindra" style="height:80px;width:auto;">
        </a>

        <nav class="nav-links nav-links-desktop">
            <a href="#como-funciona">¿Cómo funciona?</a>
            <a href="#planes">Planes</a>
            <a href="#beneficios">Beneficios</a>
            <span class="nav-divider"></span>
            @auth
                @if(auth()->user()->isSuperAdmin())
                    <a href="{{ route('superadmin.dashboard') }}" style="color:#4f46e5;">SuperAdmin</a>
                @elseif(auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" style="color:#4f46e5;">Panel Admin</a>
                @endif
                <a href="{{ route('dashboard') }}" style="color:#4f46e5;">Mi dashboard</a>
            @else
                <a href="{{ route('login') }}">Iniciar sesión</a>
                <a href="#planes" class="btn-primary" style="padding:8px 18px;font-size:.8125rem;color:#fff;">Empezar</a>
            @endauth
        </nav>
    </div>
</header>

{{-- ── Hero ─────────────────────────────────────────────────────────────────── --}}
<section style="padding:120px 1.5rem 80px;background:linear-gradient(160deg,#f8faff 0%,#fff 40%,#f0f0ff 100%);position:relative;overflow:hidden;">
    {{-- Decorative blobs --}}
    <div style="position:absolute;top:-100px;right:-100px;width:450px;height:450px;background:radial-gradient(circle,rgba(99,102,241,.08),transparent 70%);border-radius:9999px;pointer-events:none;"></div>
    <div style="position:absolute;bottom:-120px;left:-80px;width:400px;height:400px;background:radial-gradient(circle,rgba(139,92,246,.06),transparent 70%);border-radius:9999px;pointer-events:none;"></div>

    <div style="max-width:80rem;margin:0 auto;padding-left:2rem;padding-right:2rem;display:flex;align-items:center;gap:60px;" class="hero-grid">

        {{-- Text --}}
        <div style="flex:1;min-width:0;" class="hero-text">
            <div class="anim-fade" style="display:inline-flex;align-items:center;gap:8px;padding:6px 14px;border-radius:9999px;background:#eef2ff;border:1px solid #c7d2fe;font-size:.75rem;font-weight:700;color:#4338ca;margin-bottom:24px;">
                <span style="position:relative;display:flex;width:8px;height:8px;">
                    <span style="position:absolute;inset:0;border-radius:9999px;background:#818cf8;opacity:.6;animation:float 2s ease-in-out infinite;"></span>
                    <span style="position:relative;width:8px;height:8px;border-radius:9999px;background:#4f46e5;"></span>
                </span>
                INTELIGENCIA EMOCIONAL CON IA
            </div>

            <h1 class="anim-fade anim-fade-d1" style="font-size:3.25rem;font-weight:900;line-height:1.1;letter-spacing:-.02em;margin-bottom:20px;">
                Comprende tu bienestar<br>
                <span style="background:linear-gradient(135deg,#38bdf8,#6366f1,#9333ea);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">con IA de vanguardia</span>
            </h1>

            <p class="anim-fade anim-fade-d2" style="font-size:1.125rem;color:#475569;line-height:1.7;max-width:520px;margin-bottom:32px;">
                Mindra detecta patrones emocionales a través del lenguaje natural y la voz, brindándote un espacio seguro y privado para tu crecimiento personal.
            </p>

            <div class="anim-fade anim-fade-d3 hero-ctas" style="display:flex;align-items:center;gap:14px;flex-wrap:wrap;">
                @auth
                    <a href="{{ route('chat') }}" class="btn-primary" style="padding:14px 28px;font-size:1rem;">
                        Ir al Chat
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width:16px;height:16px;"><path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.638L10.23 5.29a.75.75 0 1 1 1.04-1.08l5.5 5.25a.75.75 0 0 1 0 1.08l-5.5 5.25a.75.75 0 1 1-1.04-1.08l4.158-3.96H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd"/></svg>
                    </a>
                    <a href="{{ route('dashboard') }}" class="btn-secondary">Ver historial</a>
                @else
                    <a href="#planes" class="btn-primary" style="padding:14px 28px;font-size:1rem;">Prueba Mindra gratis</a>
                    <a href="#como-funciona" class="btn-secondary">Saber más</a>
                @endauth
            </div>
        </div>

        {{-- Chat mockup --}}
        <div style="flex:0 0 380px;position:relative;" class="anim-fade anim-fade-d3">
            <div style="position:absolute;inset:-4px;background:linear-gradient(135deg,#38bdf8,#6366f1,#9333ea);border-radius:28px;opacity:.08;filter:blur(2px);"></div>
            <div style="position:relative;background:#fff;border-radius:24px;border:1px solid #e8edf5;padding:24px;box-shadow:0 8px 40px rgba(0,0,0,.08),0 2px 8px rgba(0,0,0,.04);">
                {{-- Header --}}
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:20px;padding-bottom:14px;border-bottom:1px solid #f1f5f9;">
                    <div style="width:36px;height:36px;border-radius:9999px;overflow:hidden;border:2px solid #c7d2fe;box-shadow:0 2px 6px rgba(99,102,241,.15);">
                        <img src="/assets/img/mindra1.png" alt="" style="width:100%;height:100%;object-fit:cover;">
                    </div>
                    <div>
                        <p style="font-size:.8125rem;font-weight:700;color:#1e293b;">Mindra</p>
                        <p style="font-size:.625rem;color:#22c55e;font-weight:600;display:flex;align-items:center;gap:4px;">
                            <span style="width:5px;height:5px;background:#22c55e;border-radius:9999px;display:inline-block;"></span>
                            En línea
                        </p>
                    </div>
                </div>
                {{-- Messages --}}
                <div style="display:flex;flex-direction:column;gap:10px;">
                    <div style="background:#fff;border:1px solid #e8edf5;color:#334155;font-size:.8125rem;line-height:1.6;padding:10px 13px;border-radius:16px 16px 16px 4px;box-shadow:0 1px 3px rgba(0,0,0,.05);max-width:90%;">
                        Hola, soy Mindra. ¿Cómo te has sentido hoy? Estoy aquí para escucharte.
                    </div>
                    <div style="align-self:flex-end;background:linear-gradient(135deg,#38bdf8,#6366f1,#9333ea);color:#fff;font-size:.8125rem;line-height:1.6;padding:10px 13px;border-radius:16px 16px 4px 16px;box-shadow:0 2px 8px rgba(79,70,229,.25);max-width:85%;">
                        Me he sentido un poco abrumado con el trabajo...
                    </div>
                    <div style="background:#fff;border:1px solid #e8edf5;color:#334155;font-size:.8125rem;line-height:1.6;padding:10px 13px;border-radius:16px 16px 16px 4px;box-shadow:0 1px 3px rgba(0,0,0,.05);max-width:90%;">
                        Entiendo. Esa sensación es válida. ¿Qué aspectos están pesando más hoy?
                    </div>
                    {{-- Anxiety indicator --}}
                    <div style="background:#fff;border:1px solid #e8edf5;border-radius:12px;padding:9px 11px;box-shadow:0 1px 3px rgba(0,0,0,.04);max-width:75%;">
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:5px;">
                            <span style="font-size:.5625rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;">Nivel de ansiedad</span>
                            <span style="font-size:.6875rem;font-weight:800;color:#d97706;">42%</span>
                        </div>
                        <div style="height:4px;border-radius:9999px;background:#f1f5f9;overflow:hidden;">
                            <div style="height:100%;width:42%;border-radius:9999px;background:linear-gradient(90deg,#fbbf24,#f59e0b);"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ── Cómo funciona ────────────────────────────────────────────────────────── --}}
<section id="como-funciona" style="padding:100px 1.5rem;background:#fff;">
    <div style="max-width:80rem;margin:0 auto;padding-left:2rem;padding-right:2rem;">
        <div style="text-align:center;margin-bottom:60px;">
            <span style="font-size:.75rem;font-weight:800;color:#4f46e5;text-transform:uppercase;letter-spacing:.15em;display:block;margin-bottom:10px;">Proceso inteligente</span>
            <h2 style="font-size:2.5rem;font-weight:900;color:#0f172a;letter-spacing:-.02em;">¿Cómo funciona Mindra?</h2>
        </div>

        <div class="steps-grid" style="display:grid;grid-template-columns:repeat(3,1fr);gap:24px;">
            {{-- Step 1 --}}
            <div style="padding:36px 32px;border-radius:24px;background:var(--bg-surface,#f8fafc);border:1px solid var(--border-light,#f1f5f9);transition:all .3s;text-align:center;"
                 onmouseover="this.style.background='var(--bg-surface-hover,#fff)';this.style.boxShadow='0 8px 30px rgba(99,102,241,.1)';this.style.borderColor='var(--border-accent,#e0e7ff)';"
                 onmouseout="this.style.background='var(--bg-surface,#f8fafc)';this.style.boxShadow='none';this.style.borderColor='var(--border-light,#f1f5f9)';">
                <div style="width:72px;height:72px;border-radius:20px;background:linear-gradient(135deg,#eef2ff,#e0e7ff);border:1px solid #c7d2fe;display:flex;align-items:center;justify-content:center;margin:0 auto 24px;box-shadow:0 6px 16px rgba(99,102,241,.12);">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" style="width:36px;height:36px;">
                        <path d="M12 18.75C15.3137 18.75 18 16.0637 18 12.75V11.25C18 7.93629 15.3137 5.25 12 5.25C8.68629 5.25 6 7.93629 6 11.25V12.75C6 16.0637 8.68629 18.75 12 18.75Z" fill="#c7d2fe"/>
                        <path d="M12 2.25C9.92893 2.25 8.25 3.92893 8.25 6V12C8.25 14.0711 9.92893 15.75 12 15.75C14.0711 15.75 15.75 14.0711 15.75 12V6C15.75 3.92893 14.0711 2.25 12 2.25Z" stroke="#4f46e5" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M19.5 12C19.5 16.1421 16.1421 19.5 12 19.5C7.85786 19.5 4.5 16.1421 4.5 12" stroke="#4f46e5" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M12 19.5V22.5" stroke="#4f46e5" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M9 22.5H15" stroke="#4f46e5" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M9.75 8.25H14.25" stroke="#4f46e5" stroke-width="1" stroke-linecap="round" opacity=".5"/>
                        <path d="M8.25 10.5H15.75" stroke="#4f46e5" stroke-width="1" stroke-linecap="round" opacity=".5"/>
                        <path d="M9.75 12.75H14.25" stroke="#4f46e5" stroke-width="1" stroke-linecap="round" opacity=".5"/>
                    </svg>
                </div>
                <div style="font-size:.6875rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.08em;margin-bottom:8px;">Paso 1</div>
                <h3 style="font-size:1.25rem;font-weight:800;color:#0f172a;margin-bottom:8px;">Exprésate</h3>
                <p style="font-size:.875rem;color:#64748b;line-height:1.7;">
                    Escribe o graba un audio sobre cómo te sientes. Nuestra IA procesa tu expresión con total confidencialidad.
                </p>
            </div>

            {{-- Step 2 --}}
            <div style="padding:36px 32px;border-radius:24px;background:var(--bg-surface,#f8fafc);border:1px solid var(--border-light,#f1f5f9);transition:all .3s;text-align:center;"
                 onmouseover="this.style.background='var(--bg-surface-hover,#fff)';this.style.boxShadow='0 8px 30px rgba(139,92,246,.1)';this.style.borderColor='var(--border-accent,#ede9fe)';"
                 onmouseout="this.style.background='var(--bg-surface,#f8fafc)';this.style.boxShadow='none';this.style.borderColor='var(--border-light,#f1f5f9)';">
                <div style="width:72px;height:72px;border-radius:20px;background:linear-gradient(135deg,#f5f3ff,#ede9fe);border:1px solid #ddd6fe;display:flex;align-items:center;justify-content:center;margin:0 auto 24px;box-shadow:0 6px 16px rgba(139,92,246,.12);">
                    <img src="/assets/img/mindra1.png" alt="Mindra IA" style="width:42px;height:42px;object-fit:contain;">
                </div>
                <div style="font-size:.6875rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.08em;margin-bottom:8px;">Paso 2</div>
                <h3 style="font-size:1.25rem;font-weight:800;color:#0f172a;margin-bottom:8px;">Análisis IA</h3>
                <p style="font-size:.875rem;color:#64748b;line-height:1.7;">
                    Modelos de procesamiento de lenguaje natural identifican indicadores de bienestar y niveles de ansiedad en tiempo real.
                </p>
            </div>

            {{-- Step 3 --}}
            <div style="padding:36px 32px;border-radius:24px;background:var(--bg-surface,#f8fafc);border:1px solid var(--border-light,#f1f5f9);transition:all .3s;text-align:center;"
                 onmouseover="this.style.background='var(--bg-surface-hover,#fff)';this.style.boxShadow='0 8px 30px rgba(16,185,129,.1)';this.style.borderColor='var(--border-accent,#d1fae5)';"
                 onmouseout="this.style.background='var(--bg-surface,#f8fafc)';this.style.boxShadow='none';this.style.borderColor='var(--border-light,#f1f5f9)';">
                <div style="width:72px;height:72px;border-radius:20px;background:linear-gradient(135deg,#f0fdf4,#dcfce7);border:1px solid #bbf7d0;display:flex;align-items:center;justify-content:center;margin:0 auto 24px;box-shadow:0 6px 16px rgba(22,163,74,.12);">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" style="width:36px;height:36px;">
                        <rect x="3" y="3" width="18" height="18" rx="3" fill="#dcfce7"/>
                        <path d="M4.5 17.5L8.5 13L11 15.5L15 10L19.5 6.5" stroke="#16a34a" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M4.5 17.5L8.5 13L11 15.5L15 10L19.5 6.5" stroke="#16a34a" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" opacity=".15" style="filter:blur(2px)"/>
                        <circle cx="8.5" cy="13" r="2" fill="#bbf7d0" stroke="#16a34a" stroke-width="1"/>
                        <circle cx="15" cy="10" r="2" fill="#bbf7d0" stroke="#16a34a" stroke-width="1"/>
                        <circle cx="19.5" cy="6.5" r="2" fill="#bbf7d0" stroke="#16a34a" stroke-width="1"/>
                        <path d="M16.5 6.5L19.5 6.5" stroke="#16a34a" stroke-width="1.25" stroke-linecap="round" opacity=".5"/>
                        <path d="M19.5 6.5L19.5 9.5" stroke="#16a34a" stroke-width="1.25" stroke-linecap="round" opacity=".5"/>
                    </svg>
                </div>
                <div style="font-size:.6875rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.08em;margin-bottom:8px;">Paso 3</div>
                <h3 style="font-size:1.25rem;font-weight:800;color:#0f172a;margin-bottom:8px;">Evolución</h3>
                <p style="font-size:.875rem;color:#64748b;line-height:1.7;">
                    Visualiza tu progreso con un historial detallado y recibe recomendaciones personalizadas para tu bienestar.
                </p>
            </div>
        </div>
    </div>
</section>

{{-- ── Beneficios ───────────────────────────────────────────────────────────── --}}
<section id="beneficios" style="padding:100px 1.5rem;background:#f8fafc;border-top:1px solid #f1f5f9;border-bottom:1px solid #f1f5f9;">
    <div style="max-width:80rem;margin:0 auto;padding-left:2rem;padding-right:2rem;">
        <div style="text-align:center;margin-bottom:60px;">
            <span style="font-size:.75rem;font-weight:800;color:#4f46e5;text-transform:uppercase;letter-spacing:.15em;display:block;margin-bottom:10px;">Por qué elegir Mindra</span>
            <h2 style="font-size:2.5rem;font-weight:900;color:#0f172a;letter-spacing:-.02em;">Privacidad y rigor científico</h2>
        </div>

        <div class="features-grid" style="display:grid;grid-template-columns:repeat(2,1fr);gap:20px;">
            {{-- Feature 1 --}}
            <div style="display:flex;gap:16px;padding:24px;border-radius:18px;background:var(--bg-card,#fff);border:1px solid var(--border-default,#e8edf5);transition:box-shadow .2s;"
                 onmouseover="this.style.boxShadow='0 4px 20px rgba(0,0,0,.06)'" onmouseout="this.style.boxShadow='none'">
                <div style="flex-shrink:0;width:44px;height:44px;border-radius:12px;background:#f0fdf4;border:1px solid #bbf7d0;display:flex;align-items:center;justify-content:center;">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#16a34a" style="width:22px;height:22px;">
                        <path fill-rule="evenodd" d="M12 1.5a5.25 5.25 0 0 0-5.25 5.25v3a3 3 0 0 0-3 3v6.75a3 3 0 0 0 3 3h10.5a3 3 0 0 0 3-3v-6.75a3 3 0 0 0-3-3v-3c0-2.9-2.35-5.25-5.25-5.25Zm3.75 8.25v-3a3.75 3.75 0 1 0-7.5 0v3h7.5Z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div>
                    <h4 style="font-size:1rem;font-weight:700;color:#0f172a;margin-bottom:4px;">Confidencialidad total</h4>
                    <p style="font-size:.8125rem;color:#64748b;line-height:1.65;">Tus datos nunca se comparten con terceros. Cifrado en tránsito y almacenamiento restringido.</p>
                </div>
            </div>

            {{-- Feature 2 --}}
            <div style="display:flex;gap:16px;padding:24px;border-radius:18px;background:var(--bg-card,#fff);border:1px solid var(--border-default,#e8edf5);transition:box-shadow .2s;"
                 onmouseover="this.style.boxShadow='0 4px 20px rgba(0,0,0,.06)'" onmouseout="this.style.boxShadow='none'">
                <div style="flex-shrink:0;width:44px;height:44px;border-radius:12px;background:#eef2ff;border:1px solid #c7d2fe;display:flex;align-items:center;justify-content:center;">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#4f46e5" style="width:22px;height:22px;">
                        <path fill-rule="evenodd" d="M9 4.5a.75.75 0 0 1 .721.544l.813 2.846a3.75 3.75 0 0 0 2.576 2.576l2.846.813a.75.75 0 0 1 0 1.442l-2.846.813a3.75 3.75 0 0 0-2.576 2.576l-.813 2.846a.75.75 0 0 1-1.442 0l-.813-2.846a3.75 3.75 0 0 0-2.576-2.576l-2.846-.813a.75.75 0 0 1 0-1.442l2.846-.813A3.75 3.75 0 0 0 8.466 7.89l.813-2.846A.75.75 0 0 1 9 4.5Z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div>
                    <h4 style="font-size:1rem;font-weight:700;color:#0f172a;margin-bottom:4px;">Base científica</h4>
                    <p style="font-size:.8125rem;color:#64748b;line-height:1.65;">Desarrollado por investigadores en computación afectiva con modelos validados académicamente.</p>
                </div>
            </div>

            {{-- Feature 3 --}}
            <div style="display:flex;gap:16px;padding:24px;border-radius:18px;background:var(--bg-card,#fff);border:1px solid var(--border-default,#e8edf5);transition:box-shadow .2s;"
                 onmouseover="this.style.boxShadow='0 4px 20px rgba(0,0,0,.06)'" onmouseout="this.style.boxShadow='none'">
                <div style="flex-shrink:0;width:44px;height:44px;border-radius:12px;background:#f5f3ff;border:1px solid #ddd6fe;display:flex;align-items:center;justify-content:center;">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#7c3aed" style="width:22px;height:22px;">
                        <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25ZM12.75 6a.75.75 0 0 0-1.5 0v6c0 .414.336.75.75.75h4.5a.75.75 0 0 0 0-1.5h-3.75V6Z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div>
                    <h4 style="font-size:1rem;font-weight:700;color:#0f172a;margin-bottom:4px;">Disponibilidad 24/7</h4>
                    <p style="font-size:.8125rem;color:#64748b;line-height:1.65;">Acceso inmediato a apoyo emocional inteligente desde cualquier dispositivo, en cualquier momento.</p>
                </div>
            </div>

            {{-- Feature 4 --}}
            <div style="display:flex;gap:16px;padding:24px;border-radius:18px;background:var(--bg-card,#fff);border:1px solid var(--border-default,#e8edf5);transition:box-shadow .2s;"
                 onmouseover="this.style.boxShadow='0 4px 20px rgba(0,0,0,.06)'" onmouseout="this.style.boxShadow='none'">
                <div style="flex-shrink:0;width:44px;height:44px;border-radius:12px;background:#fffbeb;border:1px solid #fde68a;display:flex;align-items:center;justify-content:center;">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#d97706" style="width:22px;height:22px;">
                        <path fill-rule="evenodd" d="M2.25 13.5a8.25 8.25 0 0 1 8.25-8.25.75.75 0 0 1 .75.75v6.75H18a.75.75 0 0 1 .75.75 8.25 8.25 0 0 1-16.5 0Z" clip-rule="evenodd"/>
                        <path fill-rule="evenodd" d="M12.75 3a.75.75 0 0 1 .75-.75 8.25 8.25 0 0 1 8.25 8.25.75.75 0 0 1-.75.75h-7.5a.75.75 0 0 1-.75-.75V3Z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div>
                    <h4 style="font-size:1rem;font-weight:700;color:#0f172a;margin-bottom:4px;">Historial y métricas</h4>
                    <p style="font-size:.8125rem;color:#64748b;line-height:1.65;">Calendario de bienestar, sesiones detalladas y evolución de tu estado emocional en el tiempo.</p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ── Planes ───────────────────────────────────────────────────────────────── --}}
<section id="planes" style="padding:100px 1.5rem;background:#fff;">
    <div style="max-width:80rem;margin:0 auto;padding-left:2rem;padding-right:2rem;">
        <div style="text-align:center;margin-bottom:60px;">
            <span style="font-size:.75rem;font-weight:800;color:#4f46e5;text-transform:uppercase;letter-spacing:.15em;display:block;margin-bottom:10px;">Elige tu plan</span>
            <h2 style="font-size:2.5rem;font-weight:900;color:#0f172a;letter-spacing:-.02em;">Un plan para cada necesidad</h2>
            <p style="font-size:1rem;color:#64748b;margin-top:12px;max-width:560px;margin-left:auto;margin-right:auto;line-height:1.7;">
                Desde uso personal gratuito hasta implementaciones institucionales completas.
            </p>
        </div>

        <div class="steps-grid" style="display:grid;grid-template-columns:repeat(3,1fr);gap:24px;align-items:stretch;">

            {{-- Plan Free --}}
            <div style="display:flex;flex-direction:column;padding:36px 28px;border-radius:24px;background:var(--bg-surface,#f8fafc);border:1px solid var(--border-default,#e8edf5);transition:all .3s;"
                 onmouseover="this.style.boxShadow='0 8px 30px rgba(0,0,0,.07)';this.style.borderColor='var(--border-accent,#c7d2fe)';"
                 onmouseout="this.style.boxShadow='none';this.style.borderColor='var(--border-default,#e8edf5)';">
                <div style="margin-bottom:20px;">
                    <div style="width:56px;height:56px;border-radius:16px;background:#f0fdf4;border:1px solid #bbf7d0;display:flex;align-items:center;justify-content:center;">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" style="width:30px;height:30px;" fill="none" stroke="#16a34a" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"/>
                        </svg>
                    </div>
                </div>
                <h3 style="font-size:1.375rem;font-weight:800;color:#0f172a;margin-bottom:4px;">Free</h3>
                <p style="font-size:.8125rem;color:#64748b;margin-bottom:20px;line-height:1.6;">Acceso básico y gratuito para uso personal.</p>
                <div style="margin-bottom:24px;">
                    <span style="font-size:2.5rem;font-weight:900;color:#0f172a;">$0.00</span>
                    <span style="font-size:.875rem;color:#94a3b8;font-weight:500;"> MXN / siempre</span>
                </div>
                <ul style="list-style:none;padding:0;margin:0 0 28px;display:flex;flex-direction:column;gap:10px;">
                    @foreach(['Chat con IA (texto y voz)', 'Detección básica de ansiedad', 'App móvil + versión web'] as $feat)
                    <li style="display:flex;align-items:center;gap:8px;font-size:.8125rem;color:#475569;">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="#16a34a" style="width:16px;height:16px;flex-shrink:0;"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd"/></svg>
                        {{ $feat }}
                    </li>
                    @endforeach
                </ul>
                <div style="margin-top:auto;display:flex;flex-direction:column;gap:10px;">
                    <a href="https://app.mindra.cafined.org" target="_blank" rel="noopener noreferrer" class="btn-secondary" style="width:100%;justify-content:center;">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width:16px;height:16px;"><path fill-rule="evenodd" d="M4.25 5.5a.75.75 0 0 0-.75.75v8.5c0 .414.336.75.75.75h8.5a.75.75 0 0 0 .75-.75v-4a.75.75 0 0 1 1.5 0v4A2.25 2.25 0 0 1 12.75 17h-8.5A2.25 2.25 0 0 1 2 14.75v-8.5A2.25 2.25 0 0 1 4.25 4h5a.75.75 0 0 1 0 1.5h-5Zm7.876-2.326a.75.75 0 0 1 .75-.75h3.498a.75.75 0 0 1 .75.75v3.5a.75.75 0 0 1-1.5 0V5.31l-5.22 5.22a.75.75 0 1 1-1.06-1.06l5.22-5.22h-1.69a.75.75 0 0 1-.748-.576Z" clip-rule="evenodd"/></svg>
                        Versión Web
                    </a>
                    <a href="javascript:void(0)" onclick="window.open('https://mindra.cafined.org/assets/mindra-latest.apk','_blank');return false;" class="btn-secondary" style="width:100%;justify-content:center;background:#f0fdf4;border-color:#bbf7d0;color:#15803d;cursor:pointer;"
                       onmouseover="this.style.background=document.documentElement.getAttribute('data-theme')==='dark'?'rgba(74,222,128,.2)':'#dcfce7'" onmouseout="this.style.background=document.documentElement.getAttribute('data-theme')==='dark'?'rgba(74,222,128,.1)':'#f0fdf4'">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width:16px;height:16px;"><path d="M10.75 2.75a.75.75 0 0 0-1.5 0v8.614L6.295 8.235a.75.75 0 1 0-1.09 1.03l4.25 4.5a.75.75 0 0 0 1.09 0l4.25-4.5a.75.75 0 0 0-1.09-1.03l-2.955 3.129V2.75Z"/><path d="M3.5 12.75a.75.75 0 0 0-1.5 0v2.5A2.75 2.75 0 0 0 4.75 18h10.5A2.75 2.75 0 0 0 18 15.25v-2.5a.75.75 0 0 0-1.5 0v2.5c0 .69-.56 1.25-1.25 1.25H4.75c-.69 0-1.25-.56-1.25-1.25v-2.5Z"/></svg>
                        Descargar APK
                    </a>
                </div>
            </div>

            {{-- Plan Pro --}}
            <div style="display:flex;flex-direction:column;padding:36px 28px;border-radius:24px;background:linear-gradient(160deg,#eef2ff,#fff);border:2px solid #4f46e5;position:relative;transition:all .3s;box-shadow:0 8px 30px rgba(79,70,229,.12);">
                {{-- Badge Popular --}}
                <div style="position:absolute;top:-12px;left:50%;transform:translateX(-50%);background:linear-gradient(135deg,#38bdf8,#6366f1,#9333ea);color:#fff;font-size:.6875rem;font-weight:800;padding:5px 16px;border-radius:9999px;text-transform:uppercase;letter-spacing:.06em;box-shadow:0 4px 12px rgba(79,70,229,.3);">
                    Más popular
                </div>
                <div style="margin-bottom:20px;">
                    <div style="width:56px;height:56px;border-radius:16px;background:#eef2ff;border:1px solid #c7d2fe;display:flex;align-items:center;justify-content:center;">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" style="width:30px;height:30px;" fill="none" stroke="#4f46e5" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m3.75 13.5 10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z"/>
                        </svg>
                    </div>
                </div>
                <h3 style="font-size:1.375rem;font-weight:800;color:#0f172a;margin-bottom:4px;">Pro</h3>
                <p style="font-size:.8125rem;color:#64748b;margin-bottom:20px;line-height:1.6;">Análisis de emociones, historial y funciones avanzadas.</p>
                <div style="margin-bottom:24px;">
                    <span style="font-size:2.5rem;font-weight:900;color:#4f46e5;">$149</span>
                    <span style="font-size:.875rem;color:#94a3b8;font-weight:500;"> MXN / mes</span>
                </div>
                <ul style="list-style:none;padding:0;margin:0 0 28px;display:flex;flex-direction:column;gap:10px;">
                    @foreach(['Todo lo del plan Free', 'Análisis de emociones detallado', 'Historial de sesiones (últimas 20)', 'Calendario de bienestar', 'Recomendaciones personalizadas'] as $feat)
                    <li style="display:flex;align-items:center;gap:8px;font-size:.8125rem;color:#475569;">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="#4f46e5" style="width:16px;height:16px;flex-shrink:0;"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd"/></svg>
                        {{ $feat }}
                    </li>
                    @endforeach
                </ul>
                <div style="margin-top:auto;">
                    <a href="{{ route('plans.pro') }}" class="btn-primary" style="width:100%;justify-content:center;padding:14px 24px;">
                        Suscribirme ahora
                    </a>
                    @guest
                        <p style="text-align:center;margin-top:10px;font-size:.75rem;color:#64748b;">
                            ¿Ya tienes cuenta? <a href="{{ route('login') }}" style="color:#4f46e5;font-weight:600;">Inicia sesión</a>
                        </p>
                    @endguest
                </div>
            </div>

            {{-- Plan Plus --}}
            <div style="display:flex;flex-direction:column;padding:36px 28px;border-radius:24px;background:#0f172a;border:1px solid #1e293b;transition:all .3s;"
                 onmouseover="this.style.boxShadow='0 8px 30px rgba(15,23,42,.5)'" onmouseout="this.style.boxShadow='none'">
                <div style="margin-bottom:20px;">
                    <div style="width:56px;height:56px;border-radius:16px;background:rgba(124,60,200,.15);border:1px solid rgba(124,60,200,.3);display:flex;align-items:center;justify-content:center;">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" style="width:30px;height:30px;" fill="none" stroke="#a78bfa" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456Z"/>
                        </svg>
                    </div>
                </div>
                <h3 style="font-size:1.375rem;font-weight:800;color:#fff;margin-bottom:4px;">Plus</h3>
                <p style="font-size:.8125rem;color:#94a3b8;margin-bottom:20px;line-height:1.6;">Acceso completo: análisis facial, estadísticas y todo Pro.</p>
                <div style="margin-bottom:24px;">
                    <span style="font-size:2.5rem;font-weight:900;color:#a78bfa;">A medida</span>
                </div>
                <ul style="list-style:none;padding:0;margin:0 0 28px;display:flex;flex-direction:column;gap:10px;">
                    @foreach(['Todo lo del plan Pro', 'Análisis facial en tiempo real', 'Estadísticas avanzadas y gráficas', 'Historial ilimitado completo', 'Alertas de crisis + reporte clínico PDF'] as $feat)
                    <li style="display:flex;align-items:center;gap:8px;font-size:.8125rem;color:#cbd5e1;">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="#a78bfa" style="width:16px;height:16px;flex-shrink:0;"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd"/></svg>
                        {{ $feat }}
                    </li>
                    @endforeach
                </ul>
                <div style="margin-top:auto;">
                    <a href="{{ route('plans.plus') }}" style="display:flex;align-items:center;justify-content:center;gap:8px;width:100%;padding:14px 24px;border-radius:12px;font-size:.875rem;font-weight:700;background:linear-gradient(135deg,#7c3cc8,#3c14b4);color:#fff;box-shadow:0 4px 14px rgba(124,60,200,.35);transition:all .2s;"
                       onmouseover="this.style.transform='translateY(-1px)';this.style.boxShadow='0 6px 20px rgba(124,60,200,.5)'"
                       onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='0 4px 14px rgba(124,60,200,.35)'">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width:16px;height:16px;"><path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0 0 10 1.944 11.954 11.954 0 0 0 17.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001Zm11.541 3.708a1 1 0 0 0-1.414-1.414L9 10.586 7.707 9.293a1 1 0 0 0-1.414 1.414l2 2a1 1 0 0 0 1.414 0l4-4Z" clip-rule="evenodd"/></svg>
                        Suscribirme ahora
                    </a>
                    @guest
                        <p style="text-align:center;margin-top:10px;font-size:.75rem;color:#64748b;">
                            ¿Ya tienes cuenta? <a href="{{ route('login') }}" style="color:#a78bfa;font-weight:600;">Inicia sesión</a>
                        </p>
                    @endguest
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ── Privacidad ───────────────────────────────────────────────────────────── --}}
<section id="privacidad-section" style="padding:80px 1.5rem;background:#fff;">
    <div style="max-width:80rem;margin:0 auto;padding-left:2rem;padding-right:2rem;">
        <div style="border-radius:24px;background:linear-gradient(135deg,#eef2ff,#f5f3ff);border:1px solid #c7d2fe;padding:40px;display:flex;align-items:flex-start;gap:20px;">
            <div style="flex-shrink:0;width:52px;height:52px;border-radius:14px;background:#fff;border:1px solid #c7d2fe;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 8px rgba(99,102,241,.1);">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#4f46e5" style="width:26px;height:26px;">
                    <path fill-rule="evenodd" d="M12 1.5a5.25 5.25 0 0 0-5.25 5.25v3a3 3 0 0 0-3 3v6.75a3 3 0 0 0 3 3h10.5a3 3 0 0 0 3-3v-6.75a3 3 0 0 0-3-3v-3c0-2.9-2.35-5.25-5.25-5.25Zm3.75 8.25v-3a3.75 3.75 0 1 0-7.5 0v3h7.5Z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div>
                <h3 style="font-size:1.25rem;font-weight:800;color:#1e1b4b;margin-bottom:8px;">Tu privacidad es nuestra prioridad</h3>
                <p style="font-size:.9rem;color:#475569;line-height:1.75;margin-bottom:16px;">
                    Los textos y audios que compartes se procesan de forma confidencial y se utilizan exclusivamente para mejorar tu experiencia. No se comparten con terceros ni se emplean con fines comerciales. Puedes solicitar la eliminación de tus datos en cualquier momento.
                </p>
                <div style="display:flex;gap:10px;flex-wrap:wrap;">
                    <a href="{{ route('legal.privacy') }}" style="font-size:.8125rem;font-weight:600;color:var(--accent,#4f46e5);padding:6px 14px;border-radius:9999px;background:var(--bg-card,#fff);border:1px solid var(--border-accent,#c7d2fe);transition:all .15s;"
                       onmouseover="this.style.background='var(--accent,#4f46e5)';this.style.color='#fff'" onmouseout="this.style.background='var(--bg-card,#fff)';this.style.color='var(--accent,#4f46e5)'">
                        Política de privacidad
                    </a>
                    <a href="{{ route('legal.consent') }}" style="font-size:.8125rem;font-weight:600;color:var(--accent,#4f46e5);padding:6px 14px;border-radius:9999px;background:var(--bg-card,#fff);border:1px solid var(--border-accent,#c7d2fe);transition:all .15s;"
                       onmouseover="this.style.background='var(--accent,#4f46e5)';this.style.color='#fff'" onmouseout="this.style.background='var(--bg-card,#fff)';this.style.color='var(--accent,#4f46e5)'">
                        Consentimiento informado
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ── CTA ──────────────────────────────────────────────────────────────────── --}}
<section style="padding:100px 1.5rem;">
    <div style="max-width:80rem;margin:0 auto;background:#0f172a;border-radius:32px;padding:64px 48px;text-align:center;position:relative;overflow:hidden;box-shadow:0 20px 60px rgba(15,23,42,.3);">
        {{-- Glow --}}
        <div style="position:absolute;top:-100px;right:-60px;width:350px;height:350px;background:radial-gradient(circle,rgba(99,102,241,.25),transparent 70%);border-radius:9999px;pointer-events:none;"></div>
        <div style="position:absolute;bottom:-80px;left:-40px;width:300px;height:300px;background:radial-gradient(circle,rgba(139,92,246,.15),transparent 70%);border-radius:9999px;pointer-events:none;"></div>

        <h2 class="cta-title" style="font-size:2.75rem;font-weight:900;color:#fff;line-height:1.15;margin-bottom:16px;position:relative;z-index:1;">
            Toma el control de tu<br>bienestar emocional
        </h2>
        <p style="font-size:1.0625rem;color:#94a3b8;line-height:1.7;max-width:480px;margin:0 auto 32px;position:relative;z-index:1;">
            Comienza a entender mejor tus emociones con el apoyo de inteligencia artificial. Es gratis, confidencial y accesible.
        </p>
        <div style="display:flex;align-items:center;justify-content:center;gap:14px;flex-wrap:wrap;position:relative;z-index:1;">
            @auth
                <a href="{{ route('chat') }}" class="btn-primary" style="padding:14px 32px;font-size:1rem;">Continuar al chat</a>
            @else
                <a href="{{ route('register') }}" class="btn-primary" style="padding:14px 32px;font-size:1rem;">Empezar ahora</a>
                <a href="{{ route('login') }}" style="padding:14px 32px;border-radius:14px;font-size:1rem;font-weight:700;color:#fff;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.15);transition:all .2s;"
                   onmouseover="this.style.background='rgba(255,255,255,.15)'" onmouseout="this.style.background='rgba(255,255,255,.08)'">
                    Iniciar sesión
                </a>
            @endauth
        </div>
    </div>
</section>

{{-- ── Footer ───────────────────────────────────────────────────────────────── --}}
<footer style="background:#fff;border-top:1px solid #e8edf5;padding:60px 1.5rem 24px;">
    <div style="max-width:80rem;margin:0 auto;padding-left:2rem;padding-right:2rem;">
        <div style="display:grid;grid-template-columns:2fr 1fr 1fr 1fr;gap:40px;margin-bottom:40px;" class="features-grid">
            {{-- Brand --}}
            <div>
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;">
                    <img src="/assets/img/mindra1.png" alt="" style="height:52px;width:auto;">
                    <img src="/assets/img/mindra2.png" alt="Mindra" style="height:46px;width:auto;">
                </div>
                <p style="font-size:.8125rem;color:#64748b;line-height:1.65;max-width:280px;margin-bottom:14px;">
                    Liderando el futuro del bienestar emocional a través de la computación afectiva e inteligencia artificial.
                </p>
                <div style="display:flex;align-items:center;gap:8px;">
                    <span style="display:inline-flex;align-items:center;gap:4px;font-size:.625rem;font-weight:700;padding:3px 9px;border-radius:9999px;background:#f0fdf4;color:#16a34a;border:1px solid #bbf7d0;text-transform:uppercase;letter-spacing:.05em;">
                        <span style="width:5px;height:5px;background:#22c55e;border-radius:9999px;display:inline-block;"></span>
                        Activo
                    </span>
                    <span style="font-size:.625rem;font-weight:600;color:#cbd5e1;">v1.0</span>
                </div>
            </div>

            {{-- Plataforma --}}
            <div>
                <p style="font-size:.6875rem;font-weight:700;color:#0f172a;text-transform:uppercase;letter-spacing:.08em;margin-bottom:14px;">Plataforma</p>
                <ul style="list-style:none;padding:0;display:flex;flex-direction:column;gap:9px;">
                    <li><a href="{{ route('chat') }}" style="font-size:.8125rem;color:#64748b;" onmouseover="this.style.color='var(--accent,#4f46e5)'" onmouseout="this.style.color='var(--text-muted,#64748b)'">Chat con Mindra</a></li>
                    <li><a href="{{ route('dashboard') }}" style="font-size:.8125rem;color:#64748b;" onmouseover="this.style.color='var(--accent,#4f46e5)'" onmouseout="this.style.color='var(--text-muted,#64748b)'">Mi historial</a></li>
                    <li><a href="{{ route('login') }}" style="font-size:.8125rem;color:#64748b;" onmouseover="this.style.color='var(--accent,#4f46e5)'" onmouseout="this.style.color='var(--text-muted,#64748b)'">Acceso</a></li>
                </ul>
            </div>

            {{-- Legal --}}
            <div>
                <p style="font-size:.6875rem;font-weight:700;color:#0f172a;text-transform:uppercase;letter-spacing:.08em;margin-bottom:14px;">Legal</p>
                <ul style="list-style:none;padding:0;display:flex;flex-direction:column;gap:9px;">
                    <li><a href="{{ route('legal.privacy') }}" style="font-size:.8125rem;color:#64748b;" onmouseover="this.style.color='var(--accent,#4f46e5)'" onmouseout="this.style.color='var(--text-muted,#64748b)'">Privacidad</a></li>
                    <li><a href="{{ route('legal.terms') }}" style="font-size:.8125rem;color:#64748b;" onmouseover="this.style.color='var(--accent,#4f46e5)'" onmouseout="this.style.color='var(--text-muted,#64748b)'">Términos</a></li>
                    <li><a href="{{ route('legal.consent') }}" style="font-size:.8125rem;color:#64748b;" onmouseover="this.style.color='var(--accent,#4f46e5)'" onmouseout="this.style.color='var(--text-muted,#64748b)'">Consentimiento</a></li>
                    <li><a href="{{ route('legal.cookies') }}" style="font-size:.8125rem;color:#64748b;" onmouseover="this.style.color='var(--accent,#4f46e5)'" onmouseout="this.style.color='var(--text-muted,#64748b)'">Cookies</a></li>
                </ul>
            </div>

            {{-- Contacto --}}
            <div>
                <p style="font-size:.6875rem;font-weight:700;color:#0f172a;text-transform:uppercase;letter-spacing:.08em;margin-bottom:14px;">Contacto</p>
                <ul style="list-style:none;padding:0;display:flex;flex-direction:column;gap:9px;">
                    <li><a href="https://cafined.org" target="_blank" rel="noopener" style="font-size:.8125rem;color:#64748b;" onmouseover="this.style.color='var(--accent,#4f46e5)'" onmouseout="this.style.color='var(--text-muted,#64748b)'">Lab. Computación Afectiva e Innovación Educativa</a></li>
                    <li><a href="mailto:cafined@itsm.edu.mx" style="font-size:.8125rem;color:#64748b;" onmouseover="this.style.color='var(--accent,#4f46e5)'" onmouseout="this.style.color='var(--text-muted,#64748b)'">cafined@itsm.edu.mx</a></li>
                </ul>
            </div>
        </div>

        {{-- Bottom bar --}}
        <div style="padding-top:20px;border-top:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">
            <span style="font-size:.75rem;color:#94a3b8;">© {{ date('Y') }} Mindra. Todos los derechos reservados. Created by Roberto Ángel Meléndez-Armenta. Developed by edegantea.</span>
            <div style="display:flex;gap:16px;">
                <a href="{{ route('legal.privacy') }}" style="font-size:.6875rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;" onmouseover="this.style.color='var(--accent,#4f46e5)'" onmouseout="this.style.color='var(--text-faint,#94a3b8)'">Privacidad</a>
                <a href="{{ route('legal.terms') }}" style="font-size:.6875rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;" onmouseover="this.style.color='var(--accent,#4f46e5)'" onmouseout="this.style.color='var(--text-faint,#94a3b8)'">Términos</a>
            </div>
        </div>
    </div>
</footer>

@include('partials.accessibility')
</body>
</html>
