var e={user:null,mode:`login`,loading:!1,message:null,history:[],prediction:null},t=e=>{let t=document.cookie.split(`; `).find(t=>t.startsWith(e+`=`));return t?decodeURIComponent(t.split(`=`)[1]):null},n=()=>({Accept:`application/json`,"X-XSRF-TOKEN":t(`XSRF-TOKEN`)||``,"X-CSRF-TOKEN":t(`XSRF-TOKEN`)||``}),r=async(e,t={})=>{let r=await fetch(e,{credentials:`same-origin`,headers:{...n(),...t.headers||{}},...t}),i=r.headers.get(`content-type`)?.includes(`application/json`)?await r.json():null;if(!r.ok&&i)throw i;return i},i=async()=>{if(!(await fetch(`/sanctum/csrf-cookie`,{credentials:`same-origin`})).ok)throw Error(`No se pudo obtener el token CSRF.`)},a=(t,n=`info`)=>{e.message={text:t,type:n},p()},o=async()=>{e.loading=!0,p();try{e.user=(await r(`/api/auth/me`)).user,await s()}catch{e.user=null}e.loading=!1,p()},s=async()=>{if(!e.user){e.history=[];return}try{e.history=(await r(`/api/inference/history`)).history||[]}catch{e.history=[]}},c=async t=>{t.preventDefault();let n=t.target,a=new FormData(n),o=Object.fromEntries(a.entries()),c=e.mode===`login`?`/api/auth/login`:`/api/auth/register`;try{await i(),e.user=(await r(c,{method:`POST`,headers:{"Content-Type":`application/json`},body:JSON.stringify(o)})).user,e.message={text:e.mode===`login`?`Has iniciado sesión.`:`Registro completado.`,type:`success`},await s()}catch(t){t.errors?e.message={text:Object.values(t.errors).flat().join(` `),type:`error`}:e.message={text:t.message||`Hubo un error en la autenticación.`,type:`error`}}p()},l=async()=>{try{await i(),await r(`/api/auth/logout`,{method:`POST`}),e.user=null,e.history=[],e.prediction=null,e.message={text:`Sesión cerrada.`,type:`success`},p()}catch(t){e.message={text:t.message||`Error al cerrar sesión.`,type:`error`},p()}},u=async t=>{t.preventDefault();let r=t.target,o=new FormData(r);if(!o.get(`texto`)&&!o.get(`audio`)?.name&&!o.get(`image`)?.name){a(`Agrega texto, audio o imagen antes de predecir.`,`error`);return}try{await i();let t=await fetch(`/api/inference/predict`,{method:`POST`,credentials:`same-origin`,headers:{...n()},body:o}),r=await t.json();if(!t.ok)throw r;e.prediction=r,e.message={text:`Predicción generada con éxito.`,type:`success`},await s()}catch(t){t.errors?e.message={text:Object.values(t.errors).flat().join(` `),type:`error`}:e.message={text:t.error||t.message||`Error al generar predicción.`,type:`error`}}p()},d=()=>e.message?`<div class="mb-4 rounded-xl border px-4 py-3 ${e.message.type===`error`?`bg-rose-100 text-rose-800 border-rose-200`:`bg-emerald-100 text-emerald-900 border-emerald-200`}">${e.message.text}</div>`:``,f=e=>new Date(e).toLocaleString(),p=()=>{let t=document.getElementById(`app`);if(!t)return;if(e.loading){t.innerHTML=`<div class="rounded-3xl bg-white p-10 shadow-xl text-center">Cargando...</div>`;return}e.user?t.innerHTML=`
            <div class="w-full max-w-5xl rounded-3xl bg-white p-8 shadow-xl">
                <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:justify-between lg:items-center">
                    <div>
                        <h1 class="text-3xl font-semibold">Bienvenido, ${e.user.name}</h1>
                        <p class="text-sm text-slate-500">Usa el modelo de inferencia para analizar texto, audio o imagen.</p>
                    </div>
                    <button id="logout-button" class="rounded-full border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cerrar sesión</button>
                </div>
                ${d()}
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

                        ${e.prediction?`
                            <div class="mt-6 rounded-3xl bg-slate-50 p-4 text-sm text-slate-700">
                                <h3 class="mb-3 font-semibold">Resultado</h3>
                                <pre class="whitespace-pre-wrap text-sm">${JSON.stringify(e.prediction,null,2)}</pre>
                            </div>
                        `:``}
                    </div>
                    <div class="space-y-6">
                        <div class="rounded-3xl border border-slate-200 p-6">
                            <h2 class="mb-4 text-xl font-semibold">Historial reciente</h2>
                            ${e.history.length===0?`<p class="text-sm text-slate-500">No hay inferencias recientes.</p>`:``}
                            <div class="space-y-4">
                                ${e.history.map(e=>`
                                    <div class="rounded-3xl bg-slate-50 p-4 text-sm">
                                        <p class="font-semibold">${f(e.created_at)}</p>
                                        <p>Texto: ${e.input_text||`<em>sin texto</em>`}</p>
                                        <p>Predicción: ${e.predicted_label||`N/A`} (${e.predicted_probability?.toFixed(2)??`N/A`})</p>
                                        <p>Emoción: ${e.emotion_label||`N/A`} (${e.emotion_probability?.toFixed(2)??`N/A`})</p>
                                    </div>
                                `).join(``)}
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        `:t.innerHTML=`
            <div class="w-full max-w-3xl rounded-3xl bg-white p-8 shadow-xl">
                <div class="mb-6 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="text-2xl font-semibold">Anxiety AI</h1>
                        <p class="text-sm text-slate-500">Accede con tu cuenta para usar el servicio de inferencia.</p>
                    </div>
                    <div class="flex gap-2">
                        <button type="button" class="rounded-full border px-4 py-2 text-sm ${e.mode===`login`?`bg-slate-900 text-white`:`bg-white text-slate-700`}" data-action="set-mode" data-mode="login">Iniciar sesión</button>
                        <button type="button" class="rounded-full border px-4 py-2 text-sm ${e.mode===`register`?`bg-slate-900 text-white`:`bg-white text-slate-700`}" data-action="set-mode" data-mode="register">Registrarse</button>
                    </div>
                </div>
                ${d()}
                <form id="auth-form" class="grid gap-4">
                    ${e.mode===`register`?`
                        <label class="space-y-1 text-sm">
                            <span>Nombre</span>
                            <input name="name" type="text" required class="w-full rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-slate-900" />
                        </label>
                    `:``}
                    <label class="space-y-1 text-sm">
                        <span>Correo electrónico</span>
                        <input name="email" type="email" required class="w-full rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-slate-900" />
                    </label>
                    <label class="space-y-1 text-sm">
                        <span>Contraseña</span>
                        <input name="password" type="password" required minlength="8" class="w-full rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-slate-900" />
                    </label>
                    ${e.mode===`register`?`
                        <label class="space-y-1 text-sm">
                            <span>Confirmar contraseña</span>
                            <input name="password_confirmation" type="password" required minlength="8" class="w-full rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-slate-900" />
                        </label>
                    `:``}
                    <button type="submit" class="mt-2 rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white hover:bg-slate-700">${e.mode===`login`?`Entrar`:`Crear cuenta`}</button>
                </form>
            </div>
        `,document.querySelectorAll(`[data-action="set-mode"]`).forEach(t=>{t.addEventListener(`click`,t=>{e.mode=t.currentTarget.getAttribute(`data-mode`),e.message=null,p()})});let n=document.getElementById(`auth-form`);n&&n.addEventListener(`submit`,c);let r=document.getElementById(`logout-button`);r&&r.addEventListener(`click`,l);let i=document.getElementById(`predict-form`);i&&i.addEventListener(`submit`,u)};window.addEventListener(`DOMContentLoaded`,()=>{o(),p()});