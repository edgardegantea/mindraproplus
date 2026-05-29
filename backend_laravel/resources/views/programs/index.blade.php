@extends('layouts.app')
@section('title', $active ? $active['title'] : 'Programas de bienestar')

@push('styles')
<style>
    .prog-card {
        background:#fff; border-radius:20px; border:1px solid #e2e8f0;
        overflow:hidden; transition:box-shadow .2s, transform .15s; cursor:pointer;
        display:flex; flex-direction:column;
    }
    .prog-card:hover { box-shadow:0 8px 32px rgb(99 102 241 / .12); transform:translateY(-2px); }
    .prog-card.enrolled { border-color:#a5b4fc; }
    .prog-card.completed { border-color:#4ade80; }

    .prog-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(300px,1fr)); gap:1.25rem; }

    .day-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(260px,1fr)); gap:.875rem; }
    .day-card {
        background:#fff; border-radius:14px; border:1px solid #e2e8f0;
        padding:1rem; transition:box-shadow .15s, border-color .15s;
    }
    .day-card.done { border-color:#bbf7d0; background:#f0fdf4; }
    .day-card.current { border-color:#a5b4fc; background:#eef2ff; box-shadow:0 0 0 2px #c7d2fe; }
    .day-card.locked { opacity:.55; }

    .progress-ring-wrap { position:relative; width:56px; height:56px; flex-shrink:0; }
    .progress-ring-wrap svg { transform:rotate(-90deg); }
    .progress-ring-label { position:absolute; inset:0; display:flex; align-items:center; justify-content:center; font-size:.8rem; font-weight:700; color:#4f46e5; }
</style>
@endpush

@section('content')

@if(session('success'))
<div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:12px;padding:14px 18px;margin-bottom:1.5rem;display:flex;align-items:center;gap:10px;color:#15803d;font-size:.875rem;font-weight:500;">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width:18px;flex-shrink:0;"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd"/></svg>
    {{ session('success') }}
</div>
@endif

@if($active)
{{-- ─── Vista de programa activo ─────────────────────────────────────────────── --}}

<div style="margin-bottom:1.5rem;">
    <a href="{{ route('programs') }}" style="display:inline-flex;align-items:center;gap:6px;font-size:.875rem;color:#64748b;text-decoration:none;font-weight:500;"
       onmouseover="this.style.color='#4f46e5'" onmouseout="this.style.color='#64748b'">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width:16px;"><path fill-rule="evenodd" d="M17 10a.75.75 0 0 1-.75.75H5.612l4.158 3.96a.75.75 0 1 1-1.04 1.08l-5.5-5.25a.75.75 0 0 1 0-1.08l5.5-5.25a.75.75 0 1 1 1.04 1.08L5.612 9.25H16.25A.75.75 0 0 1 17 10Z" clip-rule="evenodd"/></svg>
        Todos los programas
    </a>
</div>

@php
    $enrollment  = $active['enrollment'] ?? null;
    $daysDone    = $active['days_done'] ?? [];
    $totalDays   = count($active['days']);
    $doneCount   = count($daysDone);
    $progressPct = $totalDays ? round(($doneCount / $totalDays) * 100) : 0;
    $currentDay  = $enrollment?->current_day ?? 1;
    $isCompleted = $enrollment?->isCompleted() ?? false;
@endphp

{{-- Header del programa --}}
<div style="background:linear-gradient(135deg,{{ $active['color'] }}22,{{ $active['color'] }}08);border:1px solid {{ $active['color'] }}44;border-radius:24px;padding:2rem;margin-bottom:1.5rem;">
    <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:1rem;">
        <div style="display:flex;align-items:center;gap:1rem;">
            <span style="font-size:2.5rem;">{{ $active['emoji'] }}</span>
            <div>
                <h1 style="font-size:1.5rem;font-weight:800;color:#1e293b;margin:0;">{{ $active['title'] }}</h1>
                <p style="font-size:.9rem;color:#64748b;margin:4px 0 0;">{{ $active['subtitle'] }}</p>
                <p style="font-size:.8125rem;color:#94a3b8;margin:6px 0 0;max-width:560px;line-height:1.6;">{{ $active['description'] }}</p>
            </div>
        </div>

        @if(!$enrollment)
        <form method="POST" action="{{ route('programs.enroll', $active['slug']) }}">
            @csrf
            <button type="submit" style="padding:.75rem 1.75rem;border-radius:12px;background:{{ $active['color'] }};color:#fff;font-weight:700;font-size:.9375rem;border:none;cursor:pointer;white-space:nowrap;">
                Iniciar programa
            </button>
        </form>
        @elseif($isCompleted)
        <div style="text-align:center;">
            <span style="display:inline-block;font-size:2rem;margin-bottom:4px;">🏆</span>
            <p style="font-size:.875rem;font-weight:700;color:#16a34a;margin:0;">¡Completado!</p>
        </div>
        @else
        <div style="text-align:right;">
            <p style="font-size:.75rem;color:#64748b;margin:0 0 4px;">Progreso</p>
            <p style="font-size:1.5rem;font-weight:800;color:{{ $active['color'] }};margin:0;">{{ $progressPct }}%</p>
            <p style="font-size:.75rem;color:#94a3b8;margin:2px 0 0;">{{ $doneCount }} / {{ $totalDays }} días</p>
        </div>
        @endif
    </div>

    @if($enrollment && !$isCompleted)
    <div style="margin-top:1.25rem;">
        <div style="display:flex;justify-content:space-between;margin-bottom:4px;">
            <span style="font-size:.75rem;color:#64748b;font-weight:500;">{{ $doneCount }} días completados</span>
            <span style="font-size:.75rem;color:#64748b;font-weight:500;">{{ $totalDays - $doneCount }} restantes</span>
        </div>
        <div style="background:#ffffff88;border-radius:999px;height:8px;overflow:hidden;">
            <div style="width:{{ $progressPct }}%;height:100%;background:{{ $active['color'] }};border-radius:999px;transition:width .5s;"></div>
        </div>
    </div>
    @endif
</div>

{{-- Días del programa --}}
<h2 style="font-size:1.1rem;font-weight:700;color:#1e293b;margin:0 0 1rem;">Contenido del programa</h2>

<div class="day-grid">
    @foreach($active['days'] as $day)
    @php
        $isDone    = in_array($day['day'], $daysDone);
        $isCurrent = $enrollment && !$isDone && ($day['day'] === $currentDay);
        $isLocked  = $enrollment && !$isDone && !$isCurrent;
        $dayClass  = $isDone ? 'done' : ($isCurrent ? 'current' : ($enrollment ? 'locked' : ''));
    @endphp
    <div class="day-card {{ $dayClass }}" x-data="{open:false}" x-cloak>
        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:.75rem;">
            <div style="display:flex;align-items:center;gap:.75rem;flex:1;min-width:0;">
                <span style="font-size:1.4rem;flex-shrink:0;">{{ $day['icon'] }}</span>
                <div style="min-width:0;">
                    <p style="font-size:.7rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;margin:0;">Día {{ $day['day'] }}</p>
                    <p style="font-size:.9rem;font-weight:700;color:#1e293b;margin:2px 0 0;line-height:1.3;">{{ $day['title'] }}</p>
                    <p style="font-size:.75rem;color:#94a3b8;margin:2px 0 0;">⏱ {{ $day['duration'] }} min</p>
                </div>
            </div>
            @if($isDone)
                <span style="color:#16a34a;flex-shrink:0;">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width:22px;height:22px;"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd"/></svg>
                </span>
            @elseif($isCurrent)
                <span style="font-size:.65rem;font-weight:700;background:#6366f1;color:#fff;padding:2px 8px;border-radius:999px;white-space:nowrap;flex-shrink:0;">HOY</span>
            @elseif($isLocked)
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width:18px;color:#cbd5e1;flex-shrink:0;"><path fill-rule="evenodd" d="M10 1a4.5 4.5 0 0 0-4.5 4.5V9H5a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2h-.5V5.5A4.5 4.5 0 0 0 10 1Zm3 8V5.5a3 3 0 1 0-6 0V9h6Z" clip-rule="evenodd"/></svg>
            @endif
        </div>

        {{-- Expandir contenido --}}
        @if(!$isLocked || $isDone)
        <button type="button" @click="open=!open"
                style="margin-top:.75rem;display:flex;align-items:center;gap:4px;font-size:.75rem;color:#6366f1;font-weight:600;background:none;border:none;cursor:pointer;padding:0;">
            <span x-text="open?'Ocultar':'Ver contenido'"></span>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width:14px;transition:transform .2s;" :style="open?'transform:rotate(180deg)':''">
                <path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/>
            </svg>
        </button>

        <div x-show="open" x-transition style="margin-top:.75rem;padding:.75rem;background:#f8fafc;border-radius:10px;border:1px solid #e2e8f0;">
            <p style="font-size:.8125rem;color:#475569;line-height:1.65;margin:0;">{{ $day['content'] }}</p>

            @if($enrollment && !$isDone && $isCurrent)
            <form method="POST" action="{{ route('programs.complete-day', [$active['slug'], $day['day']]) }}" style="margin-top:.875rem;">
                @csrf
                <button type="submit" style="padding:.5rem 1.25rem;border-radius:10px;background:#6366f1;color:#fff;font-weight:700;font-size:.8rem;border:none;cursor:pointer;width:100%;">
                    ✓ Marcar como completado
                </button>
            </form>
            @elseif($enrollment && !$isDone && !$isCurrent && !$isLocked)
            {{-- Permitir completar días anteriores perdidos --}}
            <form method="POST" action="{{ route('programs.complete-day', [$active['slug'], $day['day']]) }}" style="margin-top:.875rem;">
                @csrf
                <button type="submit" style="padding:.5rem 1.25rem;border-radius:10px;background:#94a3b8;color:#fff;font-weight:600;font-size:.8rem;border:none;cursor:pointer;width:100%;">
                    Completar este día
                </button>
            </form>
            @endif
        </div>
        @else
        <p style="font-size:.75rem;color:#94a3b8;margin:.5rem 0 0;">Completa el día anterior para desbloquear</p>
        @endif
    </div>
    @endforeach
</div>

@else
{{-- ─── Catálogo de programas ────────────────────────────────────────────────── --}}

<div style="margin-bottom:1.5rem;">
    <h1 style="font-size:1.5rem;font-weight:700;color:#1e293b;margin:0 0 4px;">Programas de bienestar</h1>
    <p style="color:#64748b;font-size:.9rem;margin:0;">
        Programas estructurados con técnicas cognitivo-conductuales y mindfulness para mejorar tu salud mental.
    </p>
</div>

<div class="prog-grid">
    @foreach($programs as $prog)
    @php
        $enrolled  = $prog['enrollment'] !== null;
        $completed = $prog['completed'];
        $progress  = $prog['progress'];
    @endphp
    <div class="prog-card {{ $enrolled ? ($completed ? 'completed' : 'enrolled') : '' }}">
        {{-- Color top bar --}}
        <div style="height:5px;background:{{ $prog['color'] }};"></div>

        <div style="padding:1.25rem;flex:1;display:flex;flex-direction:column;">
            {{-- Header --}}
            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:.75rem;margin-bottom:.875rem;">
                <div style="display:flex;align-items:center;gap:.75rem;">
                    <span style="font-size:2rem;">{{ $prog['emoji'] }}</span>
                    <div>
                        <h3 style="font-size:1rem;font-weight:700;color:#1e293b;margin:0;">{{ $prog['title'] }}</h3>
                        <p style="font-size:.75rem;color:#94a3b8;margin:2px 0 0;">{{ $prog['subtitle'] }}</p>
                    </div>
                </div>
                @if($completed)
                    <span style="display:inline-flex;align-items:center;gap:4px;font-size:.7rem;font-weight:700;background:#f0fdf4;color:#16a34a;border:1px solid #bbf7d0;padding:3px 8px;border-radius:999px;white-space:nowrap;">
                        🏆 Completado
                    </span>
                @elseif($enrolled)
                    <span style="display:inline-flex;align-items:center;gap:4px;font-size:.7rem;font-weight:700;background:#eef2ff;color:#4338ca;border:1px solid #c7d2fe;padding:3px 8px;border-radius:999px;white-space:nowrap;">
                        En curso
                    </span>
                @endif
            </div>

            <p style="font-size:.8125rem;color:#64748b;line-height:1.6;flex:1;margin:0 0 1rem;">
                {{ $prog['description'] }}
            </p>

            {{-- Stats --}}
            <div style="display:flex;gap:1rem;margin-bottom:1rem;flex-wrap:wrap;">
                <span style="font-size:.75rem;color:#64748b;display:flex;align-items:center;gap:4px;">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" style="width:13px;color:{{ $prog['color'] }};"><path fill-rule="evenodd" d="M1 8.74c0 .983.713 1.825 1.69 1.943l.385.047a.75.75 0 0 1 .516.317l.213.302c.557.788 1.717.787 2.274 0l.214-.302a.75.75 0 0 1 .516-.317l.385-.047c.977-.118 1.69-.96 1.69-1.943V7.26c0-.983-.713-1.825-1.69-1.943l-.385-.047a.75.75 0 0 1-.516-.317L6.077 4.65c-.557-.788-1.717-.787-2.274 0l-.214.302a.75.75 0 0 1-.516.317L2.69 5.316C1.712 5.434 1 6.277 1 7.26v1.48Z" clip-rule="evenodd"/></svg>
                    {{ $prog['total_days'] }} días
                </span>
                @if($enrolled && !$completed)
                <span style="font-size:.75rem;color:#64748b;">
                    {{ $prog['days_done'] }} completados
                </span>
                @endif
            </div>

            {{-- Progress bar (enrolled) --}}
            @if($enrolled)
            <div style="margin-bottom:1rem;">
                <div style="display:flex;justify-content:space-between;margin-bottom:3px;">
                    <span style="font-size:.7rem;color:#94a3b8;">Progreso</span>
                    <span style="font-size:.7rem;font-weight:600;color:{{ $prog['color'] }};">{{ $progress }}%</span>
                </div>
                <div style="background:#f1f5f9;border-radius:999px;height:5px;overflow:hidden;">
                    <div style="width:{{ $progress }}%;height:100%;background:{{ $prog['color'] }};border-radius:999px;"></div>
                </div>
            </div>
            @endif

            {{-- CTA --}}
            <a href="{{ route('programs', ['programa' => $prog['slug']]) }}"
               style="display:block;text-align:center;padding:.65rem 1rem;border-radius:12px;background:{{ $prog['color'] }};color:#fff;font-weight:700;font-size:.875rem;text-decoration:none;transition:opacity .15s;"
               onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'">
                {{ $completed ? 'Ver programa' : ($enrolled ? 'Continuar' : 'Ver programa') }}
            </a>
        </div>
    </div>
    @endforeach
</div>

<div style="background:#eef2ff;border:1px solid #c7d2fe;border-radius:14px;padding:14px 18px;margin-top:1.5rem;display:flex;align-items:flex-start;gap:10px;">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width:18px;flex-shrink:0;color:#6366f1;margin-top:2px;">
        <path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-7-4a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM9 9a.75.75 0 0 0 0 1.5h.253a.25.25 0 0 1 .244.304l-.459 2.066A1.75 1.75 0 0 0 10.747 15H11a.75.75 0 0 0 0-1.5h-.253a.25.25 0 0 1-.244-.304l.459-2.066A1.75 1.75 0 0 0 9.253 9H9Z" clip-rule="evenodd"/>
    </svg>
    <p style="font-size:.8125rem;color:#4338ca;margin:0;line-height:1.6;">
        Los programas complementan el chat con Mindra. Se recomienda practicar las técnicas mientras las usas en tus conversaciones diarias.
    </p>
</div>

@endif
@endsection
