<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Iniciar sesión — Mindra</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>(function(){var t=localStorage.getItem('mindra_theme')||'light';var r=t==='auto'?(window.matchMedia('(prefers-color-scheme:dark)').matches?'dark':'light'):t;document.documentElement.setAttribute('data-theme',r);document.documentElement.setAttribute('data-font',localStorage.getItem('mindra_font')||'normal');document.documentElement.setAttribute('data-contrast',localStorage.getItem('mindra_contrast')==='1'?'high':'normal');document.documentElement.setAttribute('data-motion',localStorage.getItem('mindra_motion')==='1'?'reduced':'normal');})();</script>
</head>
<body class="min-h-screen bg-slate-50 flex items-center justify-center p-4">

    <div class="w-full max-w-sm">
        <div class="flex justify-center mb-0">
            <img src="/assets/img/mindra2.png" alt="Mindra" class="" style="height:200px;width:auto;">
        </div>
        <p class="text-center text-slate-500 text-sm mb-0"></p>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8">
            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1" for="email">
                        Correo electrónico
                    </label>
                    <input
                        id="email" name="email" type="email"
                        value="{{ old('email') }}"
                        required autofocus autocomplete="email"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('email') border-red-400 @enderror"
                    />
                    @error('email')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1" for="password">
                        Contraseña
                    </label>
                    <input
                        id="password" name="password" type="password"
                        required autocomplete="current-password"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    />
                </div>

                <div class="flex items-center gap-2">
                    <input id="remember" name="remember" type="checkbox"
                           class="rounded border-slate-300 text-indigo-600" />
                    <label for="remember" class="text-sm text-slate-600">Recordarme</label>
                </div>

                <button type="submit"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 rounded-lg text-sm transition-colors">
                    Iniciar sesión
                </button>
            </form>
        </div>

        <p class="text-center text-sm text-slate-500 mt-4">
            ¿No tienes cuenta?
            <a href="{{ route('register') }}" class="text-indigo-600 hover:underline">Regístrate</a>
        </p>
        <p class="text-center text-sm text-slate-400 mt-2">
            <a href="{{ route('home') }}" class="hover:underline hover:text-slate-600 transition-colors">
                ← Volver a la página principal
            </a>
        </p>
    </div>

@include('partials.accessibility')
</body>
</html>
