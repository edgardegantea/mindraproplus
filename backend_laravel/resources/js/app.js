const state = {
    user: null,
    mode: 'login',
    loading: false,
    message: null,
    history: [],
    prediction: null,
};

const getCookie = (name) => {
    const cookie = document.cookie.split('; ').find((item) => item.startsWith(name + '='));
    return cookie ? decodeURIComponent(cookie.split('=')[1]) : null;
};

const baseHeaders = () => ({
    Accept: 'application/json',
    'X-XSRF-TOKEN': getCookie('XSRF-TOKEN') || '',
    'X-CSRF-TOKEN': getCookie('XSRF-TOKEN') || '',
});

const api = async (path, options = {}) => {
    const response = await fetch(path, {
        credentials: 'same-origin',
        headers: {
            ...baseHeaders(),
            ...(options.headers || {}),
        },
        ...options,
    });
    const isJson = response.headers.get('content-type')?.includes('application/json');
    const data = isJson ? await response.json() : null;
    if (!response.ok && data) {
        throw data;
    }
    return data;
};

const getCsrfCookie = async () => {
    const response = await fetch('/sanctum/csrf-cookie', {
        credentials: 'same-origin',
    });
    if (!response.ok) {
        throw new Error('No se pudo obtener el token CSRF.');
    }
};

const setMessage = (message, type = 'info') => {
    state.message = { text: message, type };
    render();
};

const loadUser = async () => {
    state.loading = true;
    render();
    try {
        const data = await api('/api/auth/me');
        state.user = data.user;
        await loadHistory();
    } catch (error) {
        state.user = null;
    }
    state.loading = false;
    render();
};

const loadHistory = async () => {
    if (!state.user) {
        state.history = [];
        return;
    }
    try {
        const data = await api('/api/inference/history');
        state.history = data.history || [];
    } catch (error) {
        state.history = [];
    }
};

const handleAuth = async (event) => {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    const payload = Object.fromEntries(formData.entries());
    const endpoint = state.mode === 'login' ? '/api/auth/login' : '/api/auth/register';

    try {
        await getCsrfCookie();
        const data = await api(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(payload),
        });

        state.user = data.user;
        state.message = { text: state.mode === 'login' ? 'Has iniciado sesión.' : 'Registro completado.' , type: 'success' };
        await loadHistory();
    } catch (error) {
        if (error.errors) {
            state.message = { text: Object.values(error.errors).flat().join(' '), type: 'error' };
        } else {
            state.message = { text: error.message || 'Hubo un error en la autenticación.', type: 'error' };
        }
    }
    render();
};

const handleLogout = async () => {
    try {
        await getCsrfCookie();
        await api('/api/auth/logout', { method: 'POST' });
        state.user = null;
        state.history = [];
        state.prediction = null;
        state.message = { text: 'Sesión cerrada.', type: 'success' };
        render();
    } catch (error) {
        state.message = { text: error.message || 'Error al cerrar sesión.', type: 'error' };
        render();
    }
};

const handlePrediction = async (event) => {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);

    if (!formData.get('texto') && !formData.get('audio')?.name && !formData.get('image')?.name) {
        setMessage('Agrega texto, audio o imagen antes de predecir.', 'error');
        return;
    }

    try {
        await getCsrfCookie();
        const response = await fetch('/api/inference/predict', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                ...baseHeaders(),
            },
            body: formData,
        });

        const data = await response.json();
        if (!response.ok) {
            throw data;
        }
        state.prediction = data;
        state.message = { text: 'Predicción generada con éxito.', type: 'success' };
        await loadHistory();
    } catch (error) {
        if (error.errors) {
            state.message = { text: Object.values(error.errors).flat().join(' '), type: 'error' };
        } else {
            state.message = { text: error.error || error.message || 'Error al generar predicción.', type: 'error' };
        }
    }
    render();
};

const renderMessage = () => {
    if (!state.message) return '';
    const color = state.message.type === 'error' ? 'bg-rose-100 text-rose-800 border-rose-200' : 'bg-emerald-100 text-emerald-900 border-emerald-200';
    return `<div class="mb-4 rounded-xl border px-4 py-3 ${color}">${state.message.text}</div>`;
};

const compactDate = (value) => new Date(value).toLocaleString();

const render = () => {
    const app = document.getElementById('app');
    if (!app) return;

    if (state.loading) {
        app.innerHTML = `<div class="rounded-3xl bg-white p-10 shadow-xl text-center">Cargando...</div>`;
        return;
    }

    if (!state.user) {
        app.innerHTML = `
            <div class="w-full max-w-3xl rounded-3xl bg-white p-8 shadow-xl">
                <div class="mb-6 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="text-2xl font-semibold">Anxiety AI</h1>
                        <p class="text-sm text-slate-500">Accede con tu cuenta para usar el servicio de inferencia.</p>
                    </div>
                    <div class="flex gap-2">
                        <button type="button" class="rounded-full border px-4 py-2 text-sm ${state.mode === 'login' ? 'bg-slate-900 text-white' : 'bg-white text-slate-700'}" data-action="set-mode" data-mode="login">Iniciar sesión</button>
                        <button type="button" class="rounded-full border px-4 py-2 text-sm ${state.mode === 'register' ? 'bg-slate-900 text-white' : 'bg-white text-slate-700'}" data-action="set-mode" data-mode="register">Registrarse</button>
                    </div>
                </div>
                ${renderMessage()}
                <form id="auth-form" class="grid gap-4">
                    ${state.mode === 'register' ? `
                        <label class="space-y-1 text-sm">
                            <span>Nombre</span>
                            <input name="name" type="text" required class="w-full rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-slate-900" />
                        </label>
                    ` : ''}
                    <label class="space-y-1 text-sm">
                        <span>Correo electrónico</span>
                        <input name="email" type="email" required class="w-full rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-slate-900" />
                    </label>
                    <label class="space-y-1 text-sm">
                        <span>Contraseña</span>
                        <input name="password" type="password" required minlength="8" class="w-full rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-slate-900" />
                    </label>
                    ${state.mode === 'register' ? `
                        <label class="space-y-1 text-sm">
                            <span>Confirmar contraseña</span>
                            <input name="password_confirmation" type="password" required minlength="8" class="w-full rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-slate-900" />
                        </label>
                    ` : ''}
                    <button type="submit" class="mt-2 rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white hover:bg-slate-700">${state.mode === 'login' ? 'Entrar' : 'Crear cuenta'}</button>
                </form>
            </div>
        `;
    } else {
        app.innerHTML = `
            <div class="w-full max-w-5xl rounded-3xl bg-white p-8 shadow-xl">
                <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:justify-between lg:items-center">
                    <div>
                        <h1 class="text-3xl font-semibold">Bienvenido, ${state.user.name}</h1>
                        <p class="text-sm text-slate-500">Usa el modelo de inferencia para analizar texto, audio o imagen.</p>
                    </div>
                    <button id="logout-button" class="rounded-full border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cerrar sesión</button>
                </div>
                ${renderMessage()}
                <section class="grid gap-6 lg:grid-cols-[1.3fr_0.7fr]">
                    <div class="rounded-3xl border border-slate-200 p-6">
                        <h2 class="mb-4 text-xl font-semibold">Nueva inferencia</h2>
                        <form id="predict-form" class="grid gap-4">
                            <label class="space-y-1 text-sm">
                                <span>Texto</span>
                                <textarea name="texto" rows="4" class="w-full rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-slate-900"></textarea>
                            </label>
                            <label class="space-y-1 text-sm">
                                <span>Audio (opcional)</span>
                                <input name="audio" type="file" accept="audio/*" class="w-full text-sm" />
                            </label>
                            <label class="space-y-1 text-sm">
                                <span>Imagen (opcional)</span>
                                <input name="image" type="file" accept="image/*" class="w-full text-sm" />
                            </label>
                            <label class="space-y-1 text-sm">
                                <span>Duración aproximada (segundos)</span>
                                <input name="duration_seconds" type="number" min="0" step="0.1" class="w-full rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-slate-900" />
                            </label>
                            <button type="submit" class="rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white hover:bg-slate-700">Enviar a inferencia</button>
                        </form>

                        ${state.prediction ? `
                            <div class="mt-6 rounded-3xl bg-slate-50 p-4 text-sm text-slate-700">
                                <h3 class="mb-3 font-semibold">Resultado</h3>
                                <pre class="whitespace-pre-wrap text-sm">${JSON.stringify(state.prediction, null, 2)}</pre>
                            </div>
                        ` : ''}
                    </div>
                    <div class="space-y-6">
                        <div class="rounded-3xl border border-slate-200 p-6">
                            <h2 class="mb-4 text-xl font-semibold">Historial reciente</h2>
                            ${state.history.length === 0 ? '<p class="text-sm text-slate-500">No hay inferencias recientes.</p>' : ''}
                            <div class="space-y-4">
                                ${state.history.map((record) => `
                                    <div class="rounded-3xl bg-slate-50 p-4 text-sm">
                                        <p class="font-semibold">${compactDate(record.created_at)}</p>
                                        <p>Texto: ${record.input_text || '<em>sin texto</em>'}</p>
                                        <p>Predicción: ${record.predicted_label || 'N/A'} (${record.predicted_probability?.toFixed(2) ?? 'N/A'})</p>
                                        <p>Emoción: ${record.emotion_label || 'N/A'} (${record.emotion_probability?.toFixed(2) ?? 'N/A'})</p>
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        `;
    }

    document.querySelectorAll('[data-action="set-mode"]').forEach((button) => {
        button.addEventListener('click', (event) => {
            state.mode = event.currentTarget.getAttribute('data-mode');
            state.message = null;
            render();
        });
    });

    const authForm = document.getElementById('auth-form');
    if (authForm) {
        authForm.addEventListener('submit', handleAuth);
    }

    const logoutButton = document.getElementById('logout-button');
    if (logoutButton) {
        logoutButton.addEventListener('click', handleLogout);
    }

    const predictForm = document.getElementById('predict-form');
    if (predictForm) {
        predictForm.addEventListener('submit', handlePrediction);
    }
};

window.addEventListener('DOMContentLoaded', () => {
    loadUser();
    render();
});
