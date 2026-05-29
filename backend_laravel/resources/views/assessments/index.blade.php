@extends('layouts.app')
@section('title', 'Evaluaciones')

@push('styles')
<style>
    .assessment-tabs { display:flex; gap:.5rem; margin-bottom:1.5rem; flex-wrap:wrap; }
    .tab-btn {
        padding:.5rem 1.25rem; border-radius:999px; font-size:.875rem; font-weight:600;
        border:2px solid #e2e8f0; background:#fff; cursor:pointer; transition:all .15s;
        color:#64748b;
    }
    .tab-btn.active { background:#4f46e5; border-color:#4f46e5; color:#fff; }
    .tab-btn:not(.active):hover { border-color:#a5b4fc; color:#4f46e5; }

    .question-row {
        display:grid; grid-template-columns:1fr auto; gap:1rem; align-items:center;
        padding:1rem; border-radius:12px; border:1px solid #e2e8f0; background:#fff;
        margin-bottom:.75rem;
    }
    .question-row:hover { border-color:#a5b4fc; background:#faf9ff; }
    .option-grid { display:flex; gap:.4rem; flex-wrap:wrap; justify-content:flex-end; }
    .opt-label {
        display:flex; flex-direction:column; align-items:center; gap:2px;
        cursor:pointer; padding:.35rem .6rem; border-radius:8px;
        border:1.5px solid #e2e8f0; background:#f8fafc;
        font-size:.7rem; color:#64748b; font-weight:500;
        transition:all .12s; min-width:52px; text-align:center;
        user-select:none;
    }
    .opt-label:has(input:checked),
    .opt-label.selected { border-color:#6366f1; background:#eef2ff; color:#4338ca; }
    .opt-label input { display:none; }
    .opt-num { font-size:1rem; font-weight:700; }

    .severity-pill {
        display:inline-flex; align-items:center; gap:4px; padding:3px 10px;
        border-radius:999px; font-size:.75rem; font-weight:600;
    }
    .history-row { display:grid; grid-template-columns:auto 1fr auto auto; gap:1rem; align-items:center; padding:1rem; border-bottom:1px solid #f1f5f9; }
    .history-row:last-child { border-bottom:none; }

    .score-bar-track { background:#e2e8f0; border-radius:999px; height:6px; width:120px; overflow:hidden; }
    .score-bar-fill  { height:100%; border-radius:999px; transition:width .4s; }
</style>
@endpush

@section('content')

{{-- Header --}}
<div class="mb-6">
    <h1 style="font-size:1.5rem;font-weight:700;color:#1e293b;margin:0 0 4px;">Evaluaciones clínicas</h1>
    <p style="color:#64748b;font-size:.9rem;margin:0;">
        Instrumentos validados (GAD-7 y PHQ-9) para monitorear tus niveles de ansiedad y estado de ánimo.
    </p>
</div>

@if(session('success'))
<div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:12px;padding:14px 18px;margin-bottom:1.5rem;display:flex;align-items:center;gap:10px;color:#15803d;font-size:.875rem;font-weight:500;">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width:18px;flex-shrink:0;"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd"/></svg>
    {{ session('success') }}
</div>
@endif

@if($errors->any())
<div style="background:#fef2f2;border:1px solid #fecaca;border-radius:12px;padding:14px 18px;margin-bottom:1.5rem;color:#b91c1c;font-size:.875rem;">
    @foreach($errors->all() as $error) <p style="margin:2px 0;">{{ $error }}</p> @endforeach
</div>
@endif

<div style="display:grid;grid-template-columns:1fr;gap:1.5rem;" x-data="assessment()">

    {{-- ── Formulario ────────────────────────────────────────────────────────── --}}
    <div style="background:#fff;border-radius:20px;border:1px solid #e2e8f0;overflow:hidden;">
        <div style="padding:1.25rem 1.5rem;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.75rem;">
            <div>
                <h2 style="font-size:1.05rem;font-weight:700;color:#1e293b;margin:0;">Nueva evaluación</h2>
                <p style="font-size:.8rem;color:#94a3b8;margin:2px 0 0;">Responde honestamente basándote en las últimas dos semanas</p>
            </div>
            <div class="assessment-tabs">
                <button type="button" @click="switchTab('gad7')" :class="tab==='gad7'?'active':''" class="tab-btn">
                    GAD-7 <span style="font-size:.7rem;opacity:.75;">(Ansiedad)</span>
                </button>
                <button type="button" @click="switchTab('phq9')" :class="tab==='phq9'?'active':''" class="tab-btn">
                    PHQ-9 <span style="font-size:.7rem;opacity:.75;">(Estado de ánimo)</span>
                </button>
            </div>
        </div>

        <form method="POST" action="{{ route('assessments.store') }}" @submit="return validate()">
            @csrf
            <input type="hidden" name="type" :value="tab">

            <div style="padding:1.5rem;">
                {{-- Info del instrumento --}}
                <div style="background:#f8fafc;border-radius:12px;padding:12px 16px;margin-bottom:1.25rem;font-size:.8125rem;color:#64748b;border:1px solid #e2e8f0;">
                    <template x-if="tab==='gad7'">
                        <span><strong style="color:#4338ca;">GAD-7</strong> — Escala de Trastorno de Ansiedad Generalizada (7 ítems). Tiempo estimado: ~2 min. Puntaje máximo: 21.</span>
                    </template>
                    <template x-if="tab==='phq9'">
                        <span><strong style="color:#7c3aed;">PHQ-9</strong> — Cuestionario de Salud del Paciente (9 ítems). Tiempo estimado: ~3 min. Puntaje máximo: 27.</span>
                    </template>
                </div>

                {{-- Opciones de frecuencia --}}
                <div style="display:flex;gap:.5rem;margin-bottom:1.25rem;flex-wrap:wrap;">
                    @foreach($options as $val => $label)
                        <span style="display:inline-flex;align-items:center;gap:5px;font-size:.75rem;padding:4px 10px;border-radius:999px;background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;">
                            <strong style="color:#1e293b;">{{ $val }}</strong> = {{ $label }}
                        </span>
                    @endforeach
                </div>

                {{-- Preguntas GAD-7 --}}
                <template x-if="tab==='gad7'">
                    <div>
                        @foreach($gad7Questions as $i => $question)
                        <div class="question-row">
                            <div>
                                <span style="font-size:.7rem;font-weight:600;color:#a5b4fc;text-transform:uppercase;letter-spacing:.05em;">Ítem {{ $i + 1 }}</span>
                                <p style="font-size:.9rem;color:#374151;margin:2px 0 0;line-height:1.5;">{{ $question }}</p>
                            </div>
                            <div class="option-grid">
                                @foreach($options as $val => $label)
                                <label class="opt-label" @click="mark('gad7', {{ $i }}, {{ $val }})">
                                    <input type="radio" name="answers[{{ $i }}]" value="{{ $val }}" x-bind:checked="answers.gad7[{{ $i }}] === {{ $val }}">
                                    <span class="opt-num">{{ $val }}</span>
                                    <span>{{ $label }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    </div>
                </template>

                {{-- Preguntas PHQ-9 --}}
                <template x-if="tab==='phq9'">
                    <div>
                        @foreach($phq9Questions as $i => $question)
                        <div class="question-row">
                            <div>
                                <span style="font-size:.7rem;font-weight:600;color:#c084fc;text-transform:uppercase;letter-spacing:.05em;">Ítem {{ $i + 1 }}</span>
                                <p style="font-size:.9rem;color:#374151;margin:2px 0 0;line-height:1.5;">{{ $question }}</p>
                            </div>
                            <div class="option-grid">
                                @foreach($options as $val => $label)
                                <label class="opt-label" @click="mark('phq9', {{ $i }}, {{ $val }})">
                                    <input type="radio" name="answers[{{ $i }}]" value="{{ $val }}" x-bind:checked="answers.phq9[{{ $i }}] === {{ $val }}">
                                    <span class="opt-num">{{ $val }}</span>
                                    <span>{{ $label }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    </div>
                </template>

                {{-- Progreso y submit --}}
                <div style="margin-top:1.5rem;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;">
                    <div>
                        <p style="font-size:.8rem;color:#64748b;margin:0 0 4px;">
                            Respondidas: <strong x-text="progress().answered" style="color:#4f46e5;"></strong>
                            / <strong x-text="progress().total"></strong>
                        </p>
                        <div style="width:200px;background:#e2e8f0;border-radius:999px;height:5px;overflow:hidden;">
                            <div style="height:100%;background:#6366f1;border-radius:999px;transition:width .3s;"
                                 :style="'width:'+progress().pct+'%'"></div>
                        </div>
                    </div>
                    <button type="submit"
                            style="padding:.75rem 2rem;border-radius:12px;background:linear-gradient(135deg,#6366f1,#7c3aed);color:#fff;font-weight:700;font-size:.9375rem;border:none;cursor:pointer;transition:opacity .15s;"
                            :disabled="progress().pct < 100"
                            :style="progress().pct < 100 ? 'opacity:.5;cursor:not-allowed' : ''">
                        Calcular resultado
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- ── Historial ─────────────────────────────────────────────────────────── --}}
    @if($recent->count())
    <div style="background:#fff;border-radius:20px;border:1px solid #e2e8f0;overflow:hidden;">
        <div style="padding:1.25rem 1.5rem;border-bottom:1px solid #f1f5f9;">
            <h2 style="font-size:1.05rem;font-weight:700;color:#1e293b;margin:0;">Historial de evaluaciones</h2>
            <p style="font-size:.8rem;color:#94a3b8;margin:2px 0 0;">Últimas 10 evaluaciones registradas</p>
        </div>
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:.8125rem;">
                <thead>
                    <tr style="background:#f8fafc;">
                        <th style="padding:.75rem 1rem;text-align:left;font-weight:600;color:#475569;border-bottom:1px solid #e2e8f0;">Fecha</th>
                        <th style="padding:.75rem 1rem;text-align:left;font-weight:600;color:#475569;border-bottom:1px solid #e2e8f0;">Instrumento</th>
                        <th style="padding:.75rem 1rem;text-align:center;font-weight:600;color:#475569;border-bottom:1px solid #e2e8f0;">Puntaje</th>
                        <th style="padding:.75rem 1rem;text-align:center;font-weight:600;color:#475569;border-bottom:1px solid #e2e8f0;">Severidad</th>
                        <th style="padding:.75rem 1rem;text-align:left;font-weight:600;color:#475569;border-bottom:1px solid #e2e8f0;">Barra</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recent as $a)
                    @php
                        $max = $a['type'] === 'PHQ-9' ? 27 : 21;
                        $pct = round(($a['score'] / $max) * 100);
                        $barColor = $a['severity_color'];
                    @endphp
                    <tr style="border-bottom:1px solid #f8fafc;">
                        <td style="padding:.75rem 1rem;color:#475569;">{{ $a['created_at'] }}</td>
                        <td style="padding:.75rem 1rem;">
                            <span style="font-weight:700;color:{{ $a['type']==='PHQ-9'?'#7c3aed':'#4338ca' }};">{{ $a['type'] }}</span>
                        </td>
                        <td style="padding:.75rem 1rem;text-align:center;font-weight:700;color:#1e293b;">
                            {{ $a['score'] }}<span style="color:#94a3b8;font-weight:400;">/{{ $max }}</span>
                        </td>
                        <td style="padding:.75rem 1rem;text-align:center;">
                            <span class="severity-pill" style="background:{{ $a['severity_color'] }}22;color:{{ $a['severity_color'] }};border:1px solid {{ $a['severity_color'] }}55;">
                                {{ $a['severity_label'] }}
                            </span>
                        </td>
                        <td style="padding:.75rem 1rem;">
                            <div class="score-bar-track">
                                <div class="score-bar-fill" style="width:{{ $pct }}%;background:{{ $barColor }};"></div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @else
    <div style="background:#f8fafc;border:2px dashed #e2e8f0;border-radius:20px;padding:3rem;text-align:center;">
        <div style="font-size:3rem;margin-bottom:12px;">📋</div>
        <p style="color:#94a3b8;font-size:.9rem;margin:0;">Aún no tienes evaluaciones. ¡Completa tu primera arriba!</p>
    </div>
    @endif

</div>

{{-- Nota clínica --}}
<div style="background:#fffbeb;border:1px solid #fde68a;border-radius:14px;padding:14px 18px;margin-top:.5rem;display:flex;align-items:flex-start;gap:10px;">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width:18px;flex-shrink:0;color:#d97706;margin-top:2px;">
        <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495ZM10 5a.75.75 0 0 1 .75.75v3.5a.75.75 0 0 1-1.5 0v-3.5A.75.75 0 0 1 10 5Zm0 9a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd"/>
    </svg>
    <p style="font-size:.8125rem;color:#92400e;margin:0;line-height:1.6;">
        <strong>Importante:</strong> Estas evaluaciones son herramientas de seguimiento y no reemplazan un diagnóstico clínico.
        Si obtienes puntajes altos de forma consistente, te recomendamos buscar orientación de un profesional de la salud mental.
    </p>
</div>

@endsection

@push('styles')
<script>
function assessment() {
    return {
        tab: 'gad7',
        answers: { gad7: {}, phq9: {} },
        switchTab(t) {
            this.tab = t;
            // reset radio names by re-rendering — Alpine handles it
        },
        mark(type, idx, val) {
            this.answers[type][idx] = val;
        },
        progress() {
            const total = this.tab === 'gad7' ? 7 : 9;
            const answered = Object.keys(this.answers[this.tab]).length;
            return { total, answered, pct: Math.round((answered / total) * 100) };
        },
        validate() {
            const p = this.progress();
            if (p.answered < p.total) {
                alert(`Por favor responde las ${p.total} preguntas antes de continuar.`);
                return false;
            }
            return true;
        }
    }
}
</script>
@endpush
