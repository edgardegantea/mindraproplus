@extends('superadmin._layout')
@section('title', 'Solicitud Plus — Detalle')

@push('styles')
<style>
.detail-grid { display:grid; grid-template-columns: 1fr 380px; gap:24px; align-items:start; }
@media(max-width:960px){ .detail-grid{grid-template-columns:1fr;} }
.card { background:#fff; border:1px solid #e2e8f0; border-radius:16px; padding:28px; margin-bottom:20px; }
.card-title { font-size:.75rem; font-weight:700; text-transform:uppercase; letter-spacing:.07em; color:#7c3aed; border-bottom:2px solid #ede9fe; padding-bottom:8px; margin:0 0 20px; }
.info-row { display:flex; gap:8px; padding:7px 0; border-bottom:1px solid #f1f5f9; font-size:.875rem; }
.info-row:last-child { border-bottom:none; }
.info-label { font-weight:600; color:#64748b; min-width:160px; flex-shrink:0; }
.info-value { color:#1e293b; word-break:break-word; }
.badge-status { display:inline-block; padding:4px 12px; border-radius:9999px; font-size:.75rem; font-weight:700; }
.feat-toggle { display:flex; align-items:center; gap:10px; padding:9px 12px; border-radius:10px; border:1.5px solid #e2e8f0; cursor:pointer; transition:background .15s, border-color .15s; }
.feat-toggle:hover { background:#faf5ff; border-color:#c4b5fd; }
.feat-toggle input[type=checkbox] { width:18px; height:18px; accent-color:#7c3aed; cursor:pointer; }
.feat-toggle.checked { background:#f5f3ff; border-color:#a78bfa; }
.btn { padding:10px 20px; border-radius:10px; border:none; cursor:pointer; font-size:.875rem; font-weight:700; font-family:inherit; transition:opacity .2s; }
.btn:hover { opacity:.85; }
.btn-primary { background:linear-gradient(135deg,#7c3aed,#4f46e5); color:#fff; }
.btn-warning { background:#fbbf24; color:#78350f; }
.btn-danger { background:#fee2e2; color:#dc2626; border:1.5px solid #fca5a5; }
.status-timeline { display:flex; flex-direction:column; gap:0; }
.timeline-step { display:flex; gap:12px; padding:10px 0; position:relative; }
.timeline-dot { width:20px; height:20px; border-radius:50%; flex-shrink:0; margin-top:2px; display:flex; align-items:center; justify-content:center; font-size:.625rem; font-weight:700; color:#fff; }
.timeline-line { position:absolute; left:9px; top:22px; bottom:-10px; width:2px; background:#e2e8f0; }
</style>
@endpush

@section('panel')
<div style="margin-bottom:20px;">
    <a href="{{ route('superadmin.pro-orders') }}" style="font-size:.8125rem;color:#64748b;text-decoration:none;">← Volver a solicitudes</a>
</div>

@php
    $notes = is_array($proOrder->notes) ? $proOrder->notes : [];
    $statusMap = [
        'inquiry'   => ['#2563eb','#eff6ff','#bfdbfe','🔵 Nueva solicitud'],
        'in_review' => ['#d97706','#fffbeb','#fde68a','🟡 En revisión'],
        'paid'      => ['#16a34a','#f0fdf4','#bbf7d0','🟢 Aprobada'],
        'rejected'  => ['#dc2626','#fef2f2','#fecaca','🔴 Rechazada'],
        'pending'   => ['#9333ea','#faf5ff','#ddd6fe','⚪ Pendiente'],
    ];
    [$sColor, $sBg, $sBorder, $sLabel] = $statusMap[$proOrder->status] ?? ['#64748b','#f8fafc','#e2e8f0', ucfirst($proOrder->status)];
@endphp

{{-- Header --}}
<div style="display:flex;align-items:center;gap:16px;margin-bottom:24px;flex-wrap:wrap;">
    <div>
        <h1 style="font-size:1.5rem;font-weight:800;color:#0f172a;margin:0 0 4px;">
            Solicitud Plus #{{ $proOrder->id }}
        </h1>
        <div style="font-size:.875rem;color:#64748b;">
            Recibida el {{ $proOrder->created_at->format('d/m/Y \a \l\a\s H:i') }}
        </div>
    </div>
    <span class="badge-status" style="background:{{ $sBg }};color:{{ $sColor }};border:1.5px solid {{ $sBorder }};">{{ $sLabel }}</span>
</div>

<div class="detail-grid">

    {{-- ── Columna izquierda: datos de la solicitud ─── --}}
    <div>

        {{-- 1. Solicitante --}}
        <div class="card">
            <div class="card-title">1. Datos del solicitante</div>
            <div class="info-row"><span class="info-label">Nombre</span><span class="info-value">{{ $notes['requester_name'] ?? $proOrder->full_name }}</span></div>
            <div class="info-row"><span class="info-label">Cargo / Puesto</span><span class="info-value">{{ $notes['requester_position'] ?? '—' }}</span></div>
            <div class="info-row"><span class="info-label">Correo</span><span class="info-value">{{ $notes['requester_email'] ?? $proOrder->email }}</span></div>
            <div class="info-row"><span class="info-label">Teléfono / WhatsApp</span><span class="info-value">{{ $notes['requester_phone'] ?? '—' }}</span></div>
            @if($proOrder->user)
            <div class="info-row">
                <span class="info-label">Usuario en plataforma</span>
                <span class="info-value">
                    <a href="{{ route('superadmin.users.detail', $proOrder->user) }}" style="color:#4f46e5;font-weight:600;">{{ $proOrder->user->name }}</a>
                    <span style="font-size:.75rem;color:#94a3b8;margin-left:6px;">ID {{ $proOrder->user_id }}</span>
                </span>
            </div>
            @endif
        </div>

        {{-- 2. Institución --}}
        <div class="card">
            <div class="card-title">2. Institución / empresa</div>
            <div class="info-row"><span class="info-label">Nombre</span><span class="info-value">{{ $notes['org_name'] ?? '—' }}</span></div>
            <div class="info-row"><span class="info-label">Tipo</span><span class="info-value">{{ $notes['org_type_label'] ?? $notes['org_type'] ?? '—' }}</span></div>
            <div class="info-row"><span class="info-label">Giro / Sector</span><span class="info-value">{{ $notes['org_sector'] ?? '—' }}</span></div>
            <div class="info-row"><span class="info-label">Sitio web</span><span class="info-value">
                @if($notes['org_website'] ?? null)<a href="{{ $notes['org_website'] }}" target="_blank" style="color:#4f46e5;">{{ $notes['org_website'] }}</a>@else —@endif
            </span></div>
            <div class="info-row"><span class="info-label">País</span><span class="info-value">{{ $notes['org_country'] ?? '—' }}</span></div>
            <div class="info-row"><span class="info-label">Estado / Provincia</span><span class="info-value">{{ $notes['org_state'] ?? '—' }}</span></div>
            <div class="info-row"><span class="info-label">Ciudad</span><span class="info-value">{{ $notes['org_city'] ?? '—' }}</span></div>
            <div class="info-row"><span class="info-label">Dirección</span><span class="info-value">{{ $notes['org_address'] ?? '—' }}</span></div>
        </div>

        {{-- 3. Facturación --}}
        <div class="card">
            <div class="card-title">3. Datos de facturación</div>
            <div class="info-row"><span class="info-label">RFC / N° fiscal</span><span class="info-value">{{ $notes['billing_rfc'] ?? '—' }}</span></div>
            <div class="info-row"><span class="info-label">Razón social</span><span class="info-value">{{ $notes['billing_razon_social'] ?? '—' }}</span></div>
            <div class="info-row"><span class="info-label">Régimen fiscal</span><span class="info-value">{{ $notes['billing_regimen'] ?? '—' }}</span></div>
            <div class="info-row"><span class="info-label">Uso de CFDI</span><span class="info-value">{{ $notes['billing_cfdi'] ?? '—' }}</span></div>
            <div class="info-row"><span class="info-label">Email facturas</span><span class="info-value">{{ $notes['billing_email'] ?? '—' }}</span></div>
        </div>

        {{-- 4. Proyecto --}}
        <div class="card">
            <div class="card-title">4. Descripción del proyecto</div>
            <div class="info-row"><span class="info-label">Tipo de uso</span><span class="info-value">{{ $notes['use_case_label'] ?? $notes['use_case'] ?? '—' }}</span></div>
            <div class="info-row"><span class="info-label">N° estimado usuarios</span><span class="info-value">{{ $notes['num_users'] ?? '—' }}</span></div>
            <div class="info-row"><span class="info-label">¿Cómo nos encontró?</span><span class="info-value">{{ $notes['how_found'] ?? '—' }}</span></div>
            @if($notes['project_description'] ?? null)
            <div style="padding:10px 0;">
                <div class="info-label" style="margin-bottom:6px;">Descripción del proyecto</div>
                <div style="background:#f8fafc;border-radius:10px;padding:14px;font-size:.875rem;color:#1e293b;line-height:1.65;white-space:pre-wrap;">{{ $notes['project_description'] }}</div>
            </div>
            @endif
            @if($notes['additional_comments'] ?? null)
            <div style="padding:10px 0;">
                <div class="info-label" style="margin-bottom:6px;">Comentarios adicionales</div>
                <div style="background:#f8fafc;border-radius:10px;padding:14px;font-size:.875rem;color:#1e293b;line-height:1.65;white-space:pre-wrap;">{{ $notes['additional_comments'] }}</div>
            </div>
            @endif
        </div>

    </div>

    {{-- ── Columna derecha: acciones ─── --}}
    <div>

        {{-- Timeline de estado --}}
        <div class="card">
            <div class="card-title">Estado y seguimiento</div>
            <div class="status-timeline">
                @foreach([
                    ['inquiry',   '#2563eb', 'Nueva solicitud recibida'],
                    ['in_review', '#d97706', 'En revisión por el equipo'],
                    ['paid',      '#16a34a', 'Aprobada y activada'],
                ] as [$stepSlug, $stepColor, $stepLabel])
                @php
                    $statuses = ['inquiry','in_review','paid','rejected'];
                    $currentIdx = array_search($proOrder->status, $statuses);
                    $stepIdx    = array_search($stepSlug, $statuses);
                    $isDone     = $currentIdx !== false && $stepIdx <= $currentIdx;
                    $isCurrent  = $proOrder->status === $stepSlug;
                @endphp
                <div class="timeline-step">
                    @if(!$loop->last)<div class="timeline-line"></div>@endif
                    <div class="timeline-dot" style="background:{{ $isDone ? $stepColor : '#e2e8f0' }};{{ $isCurrent ? 'box-shadow:0 0 0 3px '.str_replace('#','rgba(',str_replace(')',',.3)',$stepColor)).';' : '' }}">
                        {{ $isDone ? '✓' : ($loop->iteration) }}
                    </div>
                    <div style="font-size:.8125rem;{{ $isCurrent ? 'font-weight:700;color:#1e293b;' : ($isDone ? 'color:#475569;' : 'color:#94a3b8;') }}">
                        {{ $stepLabel }}
                        @if($isCurrent && $proOrder->reviewed_at)
                            <div style="font-size:.6875rem;color:#94a3b8;">{{ $proOrder->reviewed_at->format('d/m/Y H:i') }}</div>
                        @endif
                    </div>
                </div>
                @endforeach
                @if($proOrder->status === 'rejected')
                <div class="timeline-step">
                    <div class="timeline-dot" style="background:#dc2626;">✗</div>
                    <div style="font-size:.8125rem;font-weight:700;color:#dc2626;">Rechazada</div>
                </div>
                @endif
            </div>

            @if($proOrder->status_notes)
            <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:10px;padding:12px;margin-top:16px;font-size:.8125rem;color:#78350f;">
                <strong>Notas internas:</strong><br>{{ $proOrder->status_notes }}
            </div>
            @endif
        </div>

        @if(in_array($proOrder->status, ['inquiry', 'in_review']))
        {{-- ── Formulario de revisión ─── --}}
        <form method="POST" action="{{ route('superadmin.pro-orders.review', $proOrder) }}" id="reviewForm">
            @csrf

            {{-- Admin asignado --}}
            <div class="card">
                <div class="card-title">Admin de la institución</div>
                <p style="font-size:.8125rem;color:#64748b;margin:0 0 14px;line-height:1.5;">
                    Selecciona el usuario que administrará esta institución en la plataforma. Recibirá el rol <strong>admin</strong> y la suscripción Plus activa.
                </p>
                <select name="assigned_admin_id" style="width:100%;padding:10px 12px;border-radius:10px;border:1.5px solid #e2e8f0;background:#f8fafc;font-size:.875rem;font-family:inherit;color:#1e293b;">
                    <option value="">— Sin asignar (usar solicitante) —</option>
                    @foreach($allUsers as $u)
                    <option value="{{ $u->id }}"
                        {{ $proOrder->assigned_admin_id == $u->id ? 'selected' : '' }}
                        style="{{ in_array($u->role, ['admin','superadmin']) ? 'font-weight:600;' : '' }}">
                        {{ $u->name }} — {{ $u->email }} [{{ $u->role }}]
                    </option>
                    @endforeach
                </select>
                <p style="font-size:.75rem;color:#94a3b8;margin:8px 0 0;">Si no se asigna, el plan se activa en la cuenta del solicitante (ID {{ $proOrder->user_id ?? 'sin cuenta' }}).</p>
            </div>

            {{-- Features --}}
            <div class="card">
                <div class="card-title">Características habilitadas</div>
                <p style="font-size:.8125rem;color:#64748b;margin:0 0 14px;line-height:1.5;">
                    Selecciona las features que tendrá activas esta institución. Pueden modificarse más tarde editando la suscripción.
                </p>
                <div style="display:flex;flex-direction:column;gap:8px;" id="featList">
                    @foreach($plusFeatures as $key => $label)
                    @php $checked = $defaultFeatures[$key] ?? false; @endphp
                    <label class="feat-toggle {{ $checked ? 'checked' : '' }}" id="lbl_{{ $key }}" onclick="toggleFeat('{{ $key }}')">
                        <input type="checkbox" name="features[{{ $key }}]" value="1"
                            {{ $checked ? 'checked' : '' }}
                            onchange="updateLabel('{{ $key }}', this.checked)">
                        <span style="font-size:.875rem;font-weight:500;color:#1e293b;flex:1;">{{ $label }}</span>
                        <span id="chip_{{ $key }}" style="font-size:.6875rem;font-weight:700;padding:2px 8px;border-radius:6px;
                            {{ $checked ? 'background:#f0fdf4;color:#16a34a;' : 'background:#f1f5f9;color:#94a3b8;' }}">
                            {{ $checked ? 'Activo' : 'Inactivo' }}
                        </span>
                    </label>
                    @endforeach
                </div>
                <div style="display:flex;gap:8px;margin-top:12px;">
                    <button type="button" onclick="toggleAll(true)" style="font-size:.75rem;padding:5px 12px;border-radius:7px;border:1px solid #bbf7d0;background:#f0fdf4;color:#16a34a;cursor:pointer;font-weight:600;">Todas ✓</button>
                    <button type="button" onclick="toggleAll(false)" style="font-size:.75rem;padding:5px 12px;border-radius:7px;border:1px solid #fecaca;background:#fef2f2;color:#dc2626;cursor:pointer;font-weight:600;">Ninguna ✗</button>
                </div>
            </div>

            {{-- Notas internas --}}
            <div class="card">
                <div class="card-title">Notas internas</div>
                <p style="font-size:.75rem;color:#94a3b8;margin:0 0 10px;">Solo visibles para el equipo de Mindra. No se envían al solicitante.</p>
                <textarea name="status_notes" rows="3" placeholder="Observaciones internas, precio acordado, condiciones especiales…"
                    style="width:100%;padding:10px 12px;border-radius:10px;border:1.5px solid #e2e8f0;background:#f8fafc;font-size:.875rem;font-family:inherit;resize:vertical;box-sizing:border-box;">{{ old('status_notes', $proOrder->status_notes) }}</textarea>
            </div>

            {{-- Mensaje al solicitante --}}
            <div class="card" style="border-color:#c7d2fe;background:linear-gradient(135deg,#fafbff,#f5f3ff);">
                <div class="card-title" style="color:#4f46e5;border-color:#c7d2fe;">Mensaje al solicitante</div>
                <p style="font-size:.75rem;color:#6366f1;margin:0 0 12px;line-height:1.5;">
                    Se incluirá en el email que se envía al cambiar el estatus. Puedes personalizar la comunicación con el solicitante.
                </p>
                <textarea name="admin_message" rows="4"
                    placeholder="Ej: Hemos revisado tu solicitud y necesitamos información adicional sobre… / Tu acceso quedará activo en las próximas 24 horas…"
                    style="width:100%;padding:10px 12px;border-radius:10px;border:1.5px solid #c7d2fe;background:#fff;font-size:.875rem;font-family:inherit;resize:vertical;box-sizing:border-box;outline:none;"
                    onfocus="this.style.borderColor='#6366f1'" onblur="this.style.borderColor='#c7d2fe'">{{ old('admin_message') }}</textarea>
                <label style="display:flex;align-items:center;gap:8px;margin-top:10px;cursor:pointer;font-size:.8125rem;color:#4f46e5;font-weight:600;">
                    <input type="checkbox" name="send_email" value="1" checked style="width:15px;height:15px;accent-color:#4f46e5;cursor:pointer;">
                    Enviar notificación por email al solicitante
                </label>
            </div>

            {{-- Acciones --}}
            <div class="card" style="display:flex;flex-direction:column;gap:10px;">
                <button type="submit" name="action" value="approve" class="btn btn-primary"
                    onclick="return confirm('¿Aprobar esta solicitud y activar la suscripción Plus?')">
                    ✅ Aprobar y activar suscripción
                </button>
                <button type="submit" name="action" value="in_review" class="btn btn-warning">
                    🔍 Marcar como En revisión
                </button>
                <button type="submit" name="action" value="reject" class="btn btn-danger"
                    onclick="return confirm('¿Rechazar esta solicitud?')">
                    ✗ Rechazar solicitud
                </button>
            </div>

        </form>

        @elseif($proOrder->status === 'paid')
        {{-- Ya aprobada --}}
        <div class="card">
            <div class="card-title">Suscripción activa</div>
            @if($proOrder->assignedAdmin)
            <div class="info-row">
                <span class="info-label">Admin asignado</span>
                <span class="info-value">
                    <a href="{{ route('superadmin.users.detail', $proOrder->assignedAdmin) }}" style="color:#4f46e5;font-weight:600;">{{ $proOrder->assignedAdmin->name }}</a>
                </span>
            </div>
            @endif
            <div class="info-row">
                <span class="info-label">Aprobada</span>
                <span class="info-value">{{ $proOrder->paid_at?->format('d/m/Y H:i') }}</span>
            </div>
            @if($proOrder->reviewer)
            <div class="info-row">
                <span class="info-label">Aprobada por</span>
                <span class="info-value">{{ $proOrder->reviewer->name }}</span>
            </div>
            @endif
            <div style="margin-top:16px;">
                <a href="{{ route('superadmin.subscriptions', ['plan' => 'plus']) }}"
                   style="font-size:.8125rem;font-weight:600;color:#4f46e5;padding:8px 16px;border-radius:10px;border:1px solid #c7d2fe;background:#eef2ff;text-decoration:none;">
                    Ver suscripción →
                </a>
            </div>
        </div>
        @endif

    </div>
</div>

<script>
function updateLabel(key, checked) {
    const chip = document.getElementById('chip_' + key);
    const lbl  = document.getElementById('lbl_' + key);
    if (checked) {
        chip.textContent = 'Activo';
        chip.style.cssText = 'font-size:.6875rem;font-weight:700;padding:2px 8px;border-radius:6px;background:#f0fdf4;color:#16a34a;';
        lbl.classList.add('checked');
    } else {
        chip.textContent = 'Inactivo';
        chip.style.cssText = 'font-size:.6875rem;font-weight:700;padding:2px 8px;border-radius:6px;background:#f1f5f9;color:#94a3b8;';
        lbl.classList.remove('checked');
    }
}
function toggleFeat(key) { /* handled by checkbox onchange */ }
function toggleAll(val) {
    document.querySelectorAll('#featList input[type=checkbox]').forEach(cb => {
        cb.checked = val;
        updateLabel(cb.name.match(/\[(\w+)\]/)[1], val);
    });
}
</script>
@endsection
