@extends('layouts.app')
@section('title', 'Historial')

@push('styles')
<style>
    /* Layout principal: columna en móvil, 2 columnas en desktop */
    .dash-grid { display: flex; flex-direction: column; gap: 1.5rem; }
    @media (min-width: 900px) {
        .dash-grid { flex-direction: row; align-items: flex-start; }
        .dash-col-cal { flex: 0 0 300px; position: sticky; top: 1.5rem; }
        .dash-col-sessions { flex: 1; min-width: 0; }
    }

    /* Calendario */
    .cal-grid-header { display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); }
    .cal-grid-days   { display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); gap: 3px; }
    .cal-cell {
        aspect-ratio: 1;
        border-radius: 10px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        cursor: default;
        transition: transform .1s, box-shadow .1s;
        position: relative;
    }
    .cal-cell.has-data { cursor: pointer; }
    .cal-cell.has-data:hover { transform: scale(1.08); box-shadow: 0 2px 8px rgb(0 0 0/.12); z-index: 2; }
    .cal-cell.selected { outline: 2px solid #6366f1; outline-offset: 1px; }
    .cal-cell.is-today .today-ring {
        position: absolute; inset: 1px; border-radius: 8px;
        outline: 2px solid #6366f1; outline-offset: 1px;
        pointer-events: none;
    }

    /* Sesiones: transición expand */
    .session-detail { overflow: hidden; }
    [x-cloak] { display: none !important; }

    /* Burbujas chat en el detalle */
    .chat-row-user  { display: flex; justify-content: flex-end; margin-bottom: .5rem; }
    .chat-row-mindra { display: flex; align-items: flex-end; gap: .5rem; margin-bottom: .5rem; }
    .bubble-user   { background: #4f46e5; color: #fff; border-radius: 1rem 1rem 0.2rem 1rem; padding: .6rem 1rem; font-size: .8125rem; line-height: 1.5; max-width: 75%; }
    .bubble-mindra { background: #fff; border: 1px solid #e2e8f0; color: #475569; border-radius: 1rem 1rem 1rem 0.2rem; padding: .6rem 1rem; font-size: .8125rem; line-height: 1.5; max-width: 75%; box-shadow: 0 1px 3px rgb(0 0 0/.07); }
</style>
@endpush

@section('content')

{{-- ─── Encabezado ──────────────────────────────────────────────────────────── --}}
<div class="mb-8">
    <h1 class="text-2xl font-semibold text-slate-800">Mis sesiones con Mindra</h1>
    <p class="text-slate-500 text-sm mt-1">Historial de tus conversaciones y análisis de bienestar</p>
</div>

{{-- ─── Upgrade prompt (plan Free) ─────────────────────────────────────────── --}}
@if(empty($canHistorial))
<div style="background:linear-gradient(135deg,#eef2ff,#f5f3ff);border:2px solid #c7d2fe;border-radius:20px;padding:40px;text-align:center;margin-bottom:2rem;">
    <div style="font-size:2.5rem;margin-bottom:12px;">📊</div>
    <h2 style="font-size:1.25rem;font-weight:800;color:#3730a3;margin:0 0 8px;">Tu historial está disponible en Plan Pro</h2>
    <p style="color:#6366f1;font-size:.9375rem;line-height:1.6;max-width:420px;margin:0 auto 24px;">
        Con el plan Pro accedes a tus últimas 20 sesiones, análisis de bienestar y estadísticas personalizadas.
        Con Plus, historial ilimitado y reporte clínico.
    </p>
    <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
        <a href="{{ route('plans.pro') }}" style="padding:12px 24px;border-radius:12px;background:linear-gradient(135deg,#6366f1,#9333ea);color:#fff;font-weight:700;font-size:.9375rem;text-decoration:none;">
            Ver plan Pro — $149 MXN/mes
        </a>
        <a href="{{ route('plans.plus') }}" style="padding:12px 24px;border-radius:12px;background:linear-gradient(135deg,#7c3aed,#4c1d95);color:#fff;font-weight:700;font-size:.9375rem;text-decoration:none;">
            Ver plan Plus — $199 MXN/mes
        </a>
    </div>
</div>
@else

{{-- ─── Stats ───────────────────────────────────────────────────────────────── --}}
@if(!empty($canEstadisticas))
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
    <div class="bg-white rounded-2xl border border-slate-200 p-5">
        <p class="text-xs font-medium text-slate-400 uppercase tracking-wide">Análisis totales</p>
        <p class="text-3xl font-bold text-slate-800 mt-2">{{ $totalInferences }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-slate-200 p-5">
        <p class="text-xs font-medium text-slate-400 uppercase tracking-wide">Probabilidad media</p>
        <p class="text-3xl font-bold mt-2 {{ ($avgProbability ?? 0) > 0.5 ? 'text-amber-600' : 'text-emerald-600' }}">
            {{ $avgProbability !== null ? number_format($avgProbability * 100, 1) . '%' : '—' }}
        </p>
    </div>
    <div class="bg-white rounded-2xl border border-slate-200 p-5">
        <p class="text-xs font-medium text-slate-400 uppercase tracking-wide">Alta ansiedad</p>
        <p class="text-3xl font-bold text-rose-500 mt-2">{{ $highAnxietyCount }}</p>
    </div>
</div>
@else
{{-- Pro sin estadísticas avanzadas: banner upgrade a Plus --}}
<div style="background:linear-gradient(135deg,#faf5ff,#f5f3ff);border:1.5px solid #ddd6fe;border-radius:14px;padding:16px 20px;margin-bottom:1.5rem;display:flex;align-items:center;gap:16px;">
    <span style="font-size:1.5rem;">📈</span>
    <div style="flex:1;">
        <p style="font-size:.875rem;font-weight:700;color:#7c3aed;margin:0 0 2px;">Estadísticas avanzadas disponibles en Plus</p>
        <p style="font-size:.8125rem;color:#9333ea;margin:0;">Calendario de bienestar, tendencias semanales y reporte clínico en PDF.</p>
    </div>
    <a href="{{ route('plans.plus') }}" style="padding:8px 16px;border-radius:9px;background:#7c3aed;color:#fff;font-size:.8125rem;font-weight:700;text-decoration:none;white-space:nowrap;">Ver Plus →</a>
</div>
@endif

{{-- ─── Layout: Calendario + Sesiones ─────────────────────────────────────── --}}
@php
    $sessionDatesMap  = $sessions->mapWithKeys(fn($s) => [(string)$s->id => $s->created_at->format('Y-m-d')]);
    $sessionLevelsMap = $sessions->mapWithKeys(function($s) {
        $pct   = round(($s->inference_records_avg_predicted_probability ?? 0) * 100);
        $level = $pct > 65 ? 'high' : ($pct > 40 ? 'moderate' : 'low');
        return [(string)$s->id => $level];
    });
@endphp

<div
    x-data="dashboard({{ $calendarData->toJson() }}, {{ $sessionDatesMap->toJson() }}, {{ $sessionLevelsMap->toJson() }})"
    x-cloak
    class="dash-grid"
>

    {{-- ── Columna izquierda: Calendario ───────────────────────────────────── --}}
    <div class="dash-col-cal">
        <div class="bg-white rounded-2xl border border-slate-200 p-5">

            {{-- Header mes --}}
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;">
                <button @click="prevMonth"
                        style="padding:6px;border-radius:8px;border:1px solid var(--border-input,#e2e8f0);background:var(--bg-card,#fff);cursor:pointer;display:flex;align-items:center;color:var(--text-muted,#64748b);"
                        onmouseover="this.style.background='var(--bg-surface,#f8fafc)'" onmouseout="this.style.background='var(--bg-card,#fff)'">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width:14px;height:14px">
                        <path fill-rule="evenodd" d="M11.78 5.22a.75.75 0 0 1 0 1.06L8.06 10l3.72 3.72a.75.75 0 1 1-1.06 1.06l-4.25-4.25a.75.75 0 0 1 0-1.06l4.25-4.25a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd"/>
                    </svg>
                </button>
                <span x-text="monthLabel" style="font-size:.875rem;font-weight:600;color:#1e293b;"></span>
                <button @click="nextMonth"
                        style="padding:6px;border-radius:8px;border:1px solid var(--border-input,#e2e8f0);background:var(--bg-card,#fff);cursor:pointer;display:flex;align-items:center;color:var(--text-muted,#64748b);"
                        onmouseover="this.style.background='var(--bg-surface,#f8fafc)'" onmouseout="this.style.background='var(--bg-card,#fff)'">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width:14px;height:14px">
                        <path fill-rule="evenodd" d="M8.22 5.22a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 0 1-1.06-1.06L11.94 10 8.22 6.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/>
                    </svg>
                </button>
            </div>

            {{-- Cabeceras días de la semana --}}
            <div class="cal-grid-header" style="margin-bottom:4px;">
                <template x-for="d in ['Lu','Ma','Mi','Ju','Vi','Sa','Do']" :key="d">
                    <div x-text="d" style="text-align:center;font-size:.6875rem;font-weight:600;color:#94a3b8;padding:2px 0;"></div>
                </template>
            </div>

            {{-- Días del mes --}}
            <div class="cal-grid-days">
                <template x-for="(cell, i) in days" :key="i">
                    <div>
                        {{-- Celda vacía (padding) --}}
                        <template x-if="cell === null">
                            <div style="aspect-ratio:1;"></div>
                        </template>

                        {{-- Celda con día --}}
                        <template x-if="cell !== null">
                            <div
                                class="cal-cell"
                                :class="{ 'has-data': cell.data, 'selected': cell.key === selectedDate, 'is-today': cell.isToday }"
                                @click="cell.data && toggleDate(cell.key)"
                                :title="cell.data ? cell.data.avg + '% · ' + cell.data.cnt + ' análisis' : ''"
                                :style="cell.data
                                    ? (cell.data.avg > 65
                                        ? 'background:#fff1f2;'
                                        : cell.data.avg > 40
                                            ? 'background:#fffbeb;'
                                            : 'background:#f0fdf4;')
                                    : 'background:transparent;'"
                            >
                                <div class="today-ring" x-show="cell.isToday"></div>

                                <span :style="cell.data
                                    ? (cell.data.avg > 65 ? 'color:#be123c;font-weight:700;' : cell.data.avg > 40 ? 'color:#b45309;font-weight:700;' : 'color:#15803d;font-weight:700;')
                                    : 'color:#cbd5e1;'"
                                    style="font-size:.75rem;line-height:1;"
                                    x-text="cell.date">
                                </span>

                                <template x-if="cell.data">
                                    <span :style="cell.data.avg > 65 ? 'color:#e11d48;' : cell.data.avg > 40 ? 'color:#d97706;' : 'color:#16a34a;'"
                                          style="font-size:.5625rem;font-weight:600;line-height:1;margin-top:2px;"
                                          x-text="cell.data.avg + '%'">
                                    </span>
                                </template>
                            </div>
                        </template>
                    </div>
                </template>
            </div>

            {{-- Leyenda --}}
            <div style="display:flex;align-items:center;gap:12px;margin-top:14px;padding-top:12px;border-top:1px solid #f1f5f9;">
                <span style="font-size:.6875rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;">Nivel</span>
                <span style="display:flex;align-items:center;gap:4px;font-size:.75rem;color:#15803d;">
                    <span style="width:8px;height:8px;border-radius:50%;background:#4ade80;display:inline-block;"></span>Bajo
                </span>
                <span style="display:flex;align-items:center;gap:4px;font-size:.75rem;color:#b45309;">
                    <span style="width:8px;height:8px;border-radius:50%;background:#fbbf24;display:inline-block;"></span>Moderado
                </span>
                <span style="display:flex;align-items:center;gap:4px;font-size:.75rem;color:#be123c;">
                    <span style="width:8px;height:8px;border-radius:50%;background:#fb7185;display:inline-block;"></span>Alto
                </span>
            </div>

            {{-- Instrucción --}}
            <p style="font-size:.6875rem;color:#94a3b8;margin-top:8px;text-align:center;">
                Haz clic en un día marcado para filtrar sesiones
            </p>
        </div>
    </div>

    {{-- ── Columna derecha: Sesiones ────────────────────────────────────────── --}}
    <div class="dash-col-sessions">

        {{-- Filtro por nivel --}}
        <div style="display:flex;align-items:center;gap:6px;margin-bottom:12px;flex-wrap:wrap;">
            <span style="font-size:.6875rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;margin-right:2px;">Nivel:</span>
            <template x-for="lvl in levelFilters" :key="lvl.value">
                <button
                    type="button"
                    @click="toggleLevel(lvl.value)"
                    style="display:flex;align-items:center;gap:5px;padding:4px 11px;border-radius:9999px;font-size:.75rem;font-weight:500;cursor:pointer;transition:all .15s;border:1.5px solid transparent;font-family:inherit;"
                    :style="selectedLevel === lvl.value
                        ? lvl.activeStyle
                        : 'background:#f8fafc;border-color:#e2e8f0;color:#64748b;'"
                >
                    <span :style="'width:7px;height:7px;border-radius:50%;background:' + lvl.dot + ';display:inline-block;'"></span>
                    <span x-text="lvl.label"></span>
                </button>
            </template>
        </div>

        {{-- Banner de filtro por fecha --}}
        <div x-show="selectedDate" x-transition
             style="display:flex;align-items:center;justify-content:space-between;background:#eef2ff;border:1px solid #c7d2fe;border-radius:12px;padding:.625rem 1rem;margin-bottom:12px;">
            <div style="display:flex;align-items:center;gap:.5rem;font-size:.875rem;color:#4338ca;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width:15px;height:15px;">
                    <path fill-rule="evenodd" d="M5.75 2a.75.75 0 0 1 .75.75V4h7V2.75a.75.75 0 0 1 1.5 0V4h.25A2.75 2.75 0 0 1 18 6.75v8.5A2.75 2.75 0 0 1 15.25 18H4.75A2.75 2.75 0 0 1 2 15.25v-8.5A2.75 2.75 0 0 1 4.75 4H5V2.75A.75.75 0 0 1 5.75 2Zm-1 5.5c-.69 0-1.25.56-1.25 1.25v6.5c0 .69.56 1.25 1.25 1.25h10.5c.69 0 1.25-.56 1.25-1.25v-6.5c0-.69-.56-1.25-1.25-1.25H4.75Z" clip-rule="evenodd"/>
                </svg>
                <span>Día: <strong x-text="selectedDateFormatted"></strong>
                    · <span x-text="filteredCount"></span>
                    <span x-text="filteredCount === 1 ? 'resultado' : 'resultados'"></span>
                </span>
            </div>
            <button @click="selectedDate = null; open = null"
                    style="font-size:.75rem;color:#6366f1;background:none;border:none;cursor:pointer;padding:2px 6px;border-radius:6px;font-family:inherit;"
                    onmouseover="this.style.background='var(--accent-light,#e0e7ff)'" onmouseout="this.style.background='none'">
                × Limpiar
            </button>
        </div>

        {{-- Lista de sesiones --}}
        @forelse ($sessions as $session)
            @php
                $avgPct = round(($session->inference_records_avg_predicted_probability ?? 0) * 100);
                $recommendations = \App\Http\Controllers\Web\DashboardController::recommendations($avgPct);
                $color = $avgPct > 65 ? 'rose' : ($avgPct > 40 ? 'amber' : 'emerald');
                $styles = [
                    'rose'    => ['badge_bg'=>'#fff1f2','badge_text'=>'#be123c','badge_border'=>'#fecdd3','bar'=>'#fb7185','text'=>'#be123c','rec_bg'=>'#fff1f2','rec_border'=>'#fecdd3','rec_title'=>'#e11d48','rec_text'=>'#9f1239','dot'=>'#fb7185'],
                    'amber'   => ['badge_bg'=>'#fffbeb','badge_text'=>'#b45309','badge_border'=>'#fde68a','bar'=>'#fbbf24','text'=>'#b45309','rec_bg'=>'#fffbeb','rec_border'=>'#fde68a','rec_title'=>'#d97706','rec_text'=>'#92400e','dot'=>'#fbbf24'],
                    'emerald' => ['badge_bg'=>'#f0fdf4','badge_text'=>'#15803d','badge_border'=>'#bbf7d0','bar'=>'#4ade80','text'=>'#15803d','rec_bg'=>'#f0fdf4','rec_border'=>'#bbf7d0','rec_title'=>'#16a34a','rec_text'=>'#14532d','dot'=>'#4ade80'],
                ];
                $s = $styles[$color];
                $sid = $session->id;
                $label = $avgPct > 65 ? 'Alta ansiedad' : ($avgPct > 40 ? 'Ansiedad moderada' : 'Sin indicadores fuertes');
            @endphp

            <div
                x-show="showSession({{ $sid }})"
                x-transition
                class="mb-4 bg-white rounded-2xl border border-slate-200 overflow-hidden"
            >
                {{-- Cabecera clickable --}}
                <button
                    type="button"
                    @click="open = open === {{ $sid }} ? null : {{ $sid }}"
                    style="width:100%;display:flex;align-items:center;gap:12px;padding:14px 20px;text-align:left;background:none;border:none;cursor:pointer;transition:background .15s;"
                    onmouseover="this.style.background='var(--bg-surface,#f8fafc)'" onmouseout="this.style.background='none'"
                >
                    {{-- % circular --}}
                    <div style="flex-shrink:0;width:52px;display:flex;flex-direction:column;align-items:center;gap:4px;">
                        <span style="font-size:1.25rem;font-weight:700;color:{{ $s['text'] }};">{{ $avgPct }}%</span>
                        <div style="width:44px;height:5px;border-radius:9999px;background:#f1f5f9;overflow:hidden;">
                            <div style="height:100%;border-radius:9999px;background:{{ $s['bar'] }};width:{{ $avgPct }}%;"></div>
                        </div>
                    </div>

                    {{-- Info --}}
                    <div style="flex:1;min-width:0;">
                        <div style="display:flex;flex-wrap:wrap;align-items:center;gap:8px;margin-bottom:2px;">
                            <span style="font-size:.875rem;font-weight:500;color:#1e293b;">
                                {{ $session->created_at->format('d/m/Y, H:i') }}
                            </span>
                            <span style="font-size:.6875rem;font-weight:600;padding:2px 8px;border-radius:9999px;border:1px solid {{ $s['badge_border'] }};background:{{ $s['badge_bg'] }};color:{{ $s['badge_text'] }};">
                                {{ $label }}
                            </span>
                        </div>
                        <span style="font-size:.75rem;color:#94a3b8;">
                            {{ $session->inference_records_count }} análisis · promedio {{ $avgPct }}%
                        </span>
                    </div>

                    {{-- Chevron --}}
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                         :style="'width:16px;height:16px;flex-shrink:0;color:#cbd5e1;transition:transform .2s;' + (open === {{ $sid }} ? 'transform:rotate(180deg);' : '')">
                        <path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/>
                    </svg>
                </button>

                {{-- Detalle expandible --}}
                <div
                    x-show="open === {{ $sid }}"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    style="border-top:1px solid #f1f5f9;"
                >
                    {{-- Recomendaciones --}}
                    <div style="padding:16px 20px 0;">
                        <div style="border-radius:14px;border:1px solid {{ $s['rec_border'] }};background:{{ $s['rec_bg'] }};padding:14px 16px;">
                            <p style="font-size:.6875rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:{{ $s['rec_title'] }};margin-bottom:10px;">
                                Recomendaciones para esta sesión
                            </p>
                            <ul style="list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:8px;">
                                @foreach ($recommendations as $rec)
                                <li style="display:flex;align-items:flex-start;gap:8px;font-size:.8125rem;color:{{ $s['rec_text'] }};">
                                    <span style="flex-shrink:0;width:6px;height:6px;border-radius:50%;background:{{ $s['dot'] }};margin-top:5px;"></span>
                                    {{ $rec }}
                                </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>

                    {{-- Conversación --}}
                    <div style="padding:16px 20px 20px;">
                        <p style="font-size:.6875rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;margin-bottom:12px;">Conversación</p>

                        @forelse ($session->inferenceRecords->sortByDesc('created_at') as $record)
                            @php
                                $rPct = round(($record->predicted_probability ?? 0) * 100);
                                $botResp = is_array($record->notes) ? ($record->notes['bot_response'] ?? null) : null;
                                $rc = $rPct > 65 ? ['bg'=>'#fff1f2','border'=>'#fecdd3','text'=>'#be123c','bar'=>'#fb7185']
                                    : ($rPct > 40 ? ['bg'=>'#fffbeb','border'=>'#fde68a','text'=>'#b45309','bar'=>'#fbbf24']
                                    : ['bg'=>'#f0fdf4','border'=>'#bbf7d0','text'=>'#15803d','bar'=>'#4ade80']);
                            @endphp
                            <div style="margin-bottom:14px;">
                                @if ($record->input_text || $record->generated_text)
                                <div class="chat-row-user">
                                    <div class="bubble-user">{{ $record->input_text ?: $record->generated_text }}</div>
                                </div>
                                @endif

                                @if ($botResp)
                                <div class="chat-row-mindra">
                                    <div style="width:24px;height:24px;border-radius:9999px;overflow:hidden;border:1px solid #e2e8f0;flex-shrink:0;">
                                        <img src="/assets/img/mindra1.png" alt="Mindra" style="width:100%;height:100%;object-fit:cover;">
                                    </div>
                                    <div class="bubble-mindra">{{ $botResp }}</div>
                                </div>
                                @endif

                                <div style="display:flex;justify-content:flex-start;padding-left:32px;margin-top:4px;">
                                    <div style="display:inline-flex;align-items:center;gap:8px;border-radius:8px;padding:5px 10px;background:{{ $rc['bg'] }};border:1px solid {{ $rc['border'] }};">
                                        <div style="width:48px;height:4px;border-radius:9999px;background:#e2e8f0;overflow:hidden;">
                                            <div style="height:100%;background:{{ $rc['bar'] }};width:{{ $rPct }}%;border-radius:9999px;"></div>
                                        </div>
                                        <span style="font-size:.6875rem;font-weight:700;color:{{ $rc['text'] }};">{{ $rPct }}%</span>
                                        <span style="font-size:.6875rem;color:{{ $rc['text'] }};">{{ $record->predicted_label }}</span>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p style="font-size:.875rem;color:#94a3b8;">Sin mensajes registrados.</p>
                        @endforelse
                    </div>
                </div>
            </div>

        @empty
            <div class="bg-white rounded-2xl border border-slate-200 p-12 text-center">
                <p class="text-slate-400 text-sm">Aún no tienes sesiones registradas.</p>
                <a href="{{ route('chat') }}" class="mt-3 inline-block text-indigo-600 text-sm hover:underline">
                    Iniciar una conversación →
                </a>
            </div>
        @endforelse

        @if ($sessions->hasPages())
            <div class="mt-6">{{ $sessions->links() }}</div>
        @endif
    </div>

</div>{{-- /x-data --}}

<script>
function dashboard(calData, sessionDates, sessionLevels) {
    return {
        calData,
        sessionDates,
        sessionLevels,
        year:          new Date().getFullYear(),
        month:         new Date().getMonth(),
        selectedDate:  null,
        selectedLevel: null,
        open:          null,

        levelFilters: [
            { value: null,       label: 'Todos',    dot: '#94a3b8', activeStyle: 'background:#f1f5f9;border-color:#cbd5e1;color:#475569;' },
            { value: 'low',      label: 'Bajo',     dot: '#4ade80', activeStyle: 'background:#f0fdf4;border-color:#86efac;color:#15803d;' },
            { value: 'moderate', label: 'Moderado', dot: '#fbbf24', activeStyle: 'background:#fffbeb;border-color:#fde68a;color:#b45309;' },
            { value: 'high',     label: 'Alto',     dot: '#fb7185', activeStyle: 'background:#fff1f2;border-color:#fecdd3;color:#be123c;' },
        ],

        get monthLabel() {
            const s = new Date(this.year, this.month, 1)
                .toLocaleString('es', { month: 'long', year: 'numeric' });
            return s.charAt(0).toUpperCase() + s.slice(1);
        },

        prevMonth() {
            if (this.month === 0) { this.month = 11; this.year--; }
            else this.month--;
        },

        nextMonth() {
            if (this.month === 11) { this.month = 0; this.year++; }
            else this.month++;
        },

        get days() {
            const y = this.year, m = this.month;
            const firstDow = new Date(y, m, 1).getDay();
            const offset   = (firstDow + 6) % 7;
            const total    = new Date(y, m + 1, 0).getDate();
            const today    = new Date().toISOString().slice(0, 10);

            const cells = [];
            for (let i = 0; i < offset; i++) cells.push(null);
            for (let d = 1; d <= total; d++) {
                const key = `${y}-${String(m+1).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
                cells.push({ date: d, key, data: this.calData[key] || null, isToday: key === today });
            }
            return cells;
        },

        toggleDate(key) {
            this.selectedDate = this.selectedDate === key ? null : key;
            this.open = null;
        },

        toggleLevel(value) {
            this.selectedLevel = this.selectedLevel === value ? null : value;
            this.open = null;
        },

        showSession(id) {
            const sid = String(id);
            if (this.selectedDate  && this.sessionDates[sid]  !== this.selectedDate)  return false;
            if (this.selectedLevel && this.sessionLevels[sid] !== this.selectedLevel) return false;
            return true;
        },

        get selectedDateFormatted() {
            if (!this.selectedDate) return '';
            const [y, m, d] = this.selectedDate.split('-');
            return `${parseInt(d)}/${parseInt(m)}/${y}`;
        },

        get filteredCount() {
            if (!this.selectedDate) return 0;
            return Object.keys(this.sessionDates)
                .filter(id => this.showSession(parseInt(id))).length;
        },
    };
}
</script>

@endif {{-- canHistorial --}}

@endsection
