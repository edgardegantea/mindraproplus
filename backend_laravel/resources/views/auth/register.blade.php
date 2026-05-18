<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Registro — Mindra</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>(function(){var t=localStorage.getItem('mindra_theme')||'light';var r=t==='auto'?(window.matchMedia('(prefers-color-scheme:dark)').matches?'dark':'light'):t;document.documentElement.setAttribute('data-theme',r);document.documentElement.setAttribute('data-font',localStorage.getItem('mindra_font')||'normal');document.documentElement.setAttribute('data-contrast',localStorage.getItem('mindra_contrast')==='1'?'high':'normal');document.documentElement.setAttribute('data-motion',localStorage.getItem('mindra_motion')==='1'?'reduced':'normal');})();</script>
</head>
<body class="min-h-screen bg-slate-50 flex items-center justify-center p-4">

    <div class="w-full max-w-sm">
        <div class="flex justify-center mb-3">
            <img src="/assets/img/mindra2.png" alt="Mindra" class="h-16">
        </div>
        <p class="text-center text-slate-500 text-sm mb-8">Crea tu cuenta para guardar tus sesiones</p>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8">
            <form method="POST" action="{{ route('register') }}" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1" for="name">
                        Nombre
                    </label>
                    <input
                        id="name" name="name" type="text"
                        value="{{ old('name') }}"
                        required autofocus autocomplete="name"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('name') border-red-400 @enderror"
                    />
                    @error('name')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1" for="email">
                        Correo electrónico
                    </label>
                    <input
                        id="email" name="email" type="email"
                        value="{{ old('email') }}"
                        required autocomplete="email"
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
                        required autocomplete="new-password"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('password') border-red-400 @enderror"
                    />
                    @error('password')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1" for="password_confirmation">
                        Confirmar contraseña
                    </label>
                    <input
                        id="password_confirmation" name="password_confirmation" type="password"
                        required autocomplete="new-password"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    />
                </div>

                <button type="submit"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 rounded-lg text-sm transition-colors">
                    Crear cuenta
                </button>
            </form>
        </div>

        <p class="text-center text-sm text-slate-500 mt-4">
            ¿Ya tienes cuenta?
            <a href="{{ route('login') }}" class="text-indigo-600 hover:underline">Inicia sesión</a>
        </p>
    </div>

@include('partials.accessibility')
</body>
</html>
