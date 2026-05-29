<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Reporte Mindra — {{ $user->name }}</title>
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 11px; color: #0f172a; background: #fff; }

  .header { background: #4f46e5; color: #fff; padding: 20px 28px; border-radius: 0; }
  .header h1 { font-size: 18px; font-weight: bold; margin-bottom: 4px; }
  .header .sub { font-size: 10px; opacity: .85; }

  .confidential { background: #fef2f2; border: 1px solid #fca5a5; padding: 8px 12px; margin: 14px 0; font-size: 9.5px; color: #991b1b; border-radius: 4px; }

  .section { margin: 14px 0; }
  .section-title { font-size: 10px; font-weight: bold; text-transform: uppercase; letter-spacing: .05em; color: #4f46e5; border-bottom: 1px solid #e2e8f0; padding-bottom: 4px; margin-bottom: 8px; }

  .meta-grid { display: table; width: 100%; }
  .meta-row  { display: table-row; }
  .meta-lbl  { display: table-cell; width: 35%; color: #64748b; padding: 2px 0; }
  .meta-val  { display: table-cell; font-weight: bold; padding: 2px 0; }

  .stats-grid { width: 100%; border-collapse: collapse; }
  .stats-grid td { border: 1px solid #e2e8f0; padding: 6px 10px; text-align: center; }
  .stats-grid th { background: #f8fafc; border: 1px solid #e2e8f0; padding: 6px 10px; font-size: 9px; text-transform: uppercase; color: #64748b; }
  .stat-val  { font-size: 18px; font-weight: bold; color: #4f46e5; }
  .stat-lbl  { font-size: 9px; color: #64748b; }

  .history-table { width: 100%; border-collapse: collapse; font-size: 10px; }
  .history-table th { background: #f1f5f9; border: 1px solid #e2e8f0; padding: 5px 8px; text-align: left; color: #64748b; font-size: 9px; text-transform: uppercase; }
  .history-table td { border: 1px solid #e2e8f0; padding: 5px 8px; vertical-align: top; }
  .history-table tr:nth-child(even) td { background: #f8fafc; }

  .badge { display: inline-block; padding: 2px 7px; border-radius: 9999px; font-size: 8.5px; font-weight: bold; }
  .badge-red  { background: #fef2f2; color: #dc2626; }
  .badge-yellow { background: #fffbeb; color: #d97706; }
  .badge-green  { background: #f0fdf4; color: #16a34a; }

  .journal-entry { margin-bottom: 6px; padding: 6px 10px; background: #f8fafc; border-left: 3px solid #c7d2fe; border-radius: 0 4px 4px 0; }
  .journal-entry .mood { font-size: 13px; }
  .journal-entry .note { color: #475569; margin-top: 2px; }

  .footer { margin-top: 20px; padding-top: 10px; border-top: 1px solid #e2e8f0; font-size: 9px; color: #94a3b8; text-align: center; }

  .page-break { page-break-before: always; }
</style>
</head>
<body>

<div class="header">
  <h1>Reporte de Bienestar — Mindra</h1>
  <div class="sub">
    Paciente: {{ $user->name }} &nbsp;|&nbsp;
    Generado: {{ now()->format('d/m/Y H:i') }} &nbsp;|&nbsp;
    Válido hasta: {{ $share->expires_at->format('d/m/Y') }}
  </div>
</div>

<div class="confidential">
  ⚠️ DOCUMENTO CONFIDENCIAL — Solo para uso profesional de salud. No distribuir sin consentimiento del paciente.
</div>

{{-- Resumen estadístico --}}
<div class="section">
  <div class="section-title">Resumen del período</div>
  <table class="stats-grid">
    <thead>
      <tr>
        <th>Estado de ánimo promedio</th>
        <th>Entradas diario</th>
        <th>Sesiones de chat</th>
        <th>Ansiedad promedio</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>
          <div class="stat-val">{{ $journal->count() > 0 ? number_format($journal->avg('mood_score'), 1) : '—' }}/5</div>
          <div class="stat-lbl">Últimas {{ $journal->count() }} entradas</div>
        </td>
        <td>
          <div class="stat-val">{{ $journal->count() }}</div>
          <div class="stat-lbl">Últimos 30 días</div>
        </td>
        <td>
          <div class="stat-val">{{ $history->count() }}</div>
          <div class="stat-lbl">Últimas sesiones</div>
        </td>
        <td>
          @php $avgProb = $history->whereNotNull('predicted_probability')->avg('predicted_probability') @endphp
          <div class="stat-val">{{ $avgProb ? round($avgProb * 100) . '%' : '—' }}</div>
          <div class="stat-lbl">Indicador IA</div>
        </td>
      </tr>
    </tbody>
  </table>
</div>

{{-- Historial de sesiones --}}
@if($history->count())
<div class="section">
  <div class="section-title">Historial de sesiones de chat (últimas {{ $history->count() }})</div>
  <table class="history-table">
    <thead>
      <tr>
        <th>Fecha</th>
        <th>Texto del usuario</th>
        <th>Etiqueta IA</th>
        <th>Ansiedad</th>
        <th>Emoción</th>
      </tr>
    </thead>
    <tbody>
      @foreach($history as $rec)
      <tr>
        <td style="white-space:nowrap;">{{ $rec->created_at->format('d/m/Y H:i') }}</td>
        <td>{{ Str::limit($rec->input_text ?: $rec->generated_text, 80) }}</td>
        <td>{{ $rec->predicted_label }}</td>
        <td>
          @if($rec->predicted_probability !== null)
            @php $pct = round($rec->predicted_probability * 100) @endphp
            <span class="badge {{ $pct > 65 ? 'badge-red' : ($pct > 40 ? 'badge-yellow' : 'badge-green') }}">
              {{ $pct }}%
            </span>
          @else
            —
          @endif
        </td>
        <td>{{ $rec->emotion_label ?: '—' }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>
</div>
@endif

{{-- Diario emocional --}}
@if($journal->count())
<div class="section {{ $history->count() > 5 ? 'page-break' : '' }}">
  <div class="section-title">Diario emocional (últimas {{ $journal->count() }} entradas)</div>
  @foreach($journal as $entry)
  <div class="journal-entry">
    <div style="display:flex;justify-content:space-between;align-items:center;">
      <span class="mood">{{ $entry->mood_emoji }} {{ $entry->mood_label }}</span>
      <span style="color:#94a3b8;font-size:9px;">{{ $entry->created_at->format('d/m/Y') }}</span>
    </div>
    @if($entry->note)
      <div class="note">{{ Str::limit($entry->note, 200) }}</div>
    @endif
  </div>
  @endforeach
</div>
@endif

{{-- Evaluación GAD-7 --}}
@if($assessment)
<div class="section">
  <div class="section-title">Evaluación GAD-7 más reciente</div>
  <div class="meta-grid">
    <div class="meta-row">
      <div class="meta-lbl">Fecha:</div>
      <div class="meta-val">{{ $assessment->created_at->format('d/m/Y') }}</div>
    </div>
    <div class="meta-row">
      <div class="meta-lbl">Puntuación:</div>
      <div class="meta-val">{{ $assessment->score ?? '—' }}/21</div>
    </div>
    <div class="meta-row">
      <div class="meta-lbl">Severidad:</div>
      <div class="meta-val">{{ $assessment->severity_label ?? '—' }}</div>
    </div>
  </div>
</div>
@endif

<div class="footer">
  <p>Este reporte fue generado automáticamente por Mindra · mindra.cafined.org</p>
  <p>Laboratorio CAFINED · Para uso exclusivo del profesional de salud autorizado por el paciente</p>
  <p>Generado: {{ now()->format('d/m/Y H:i:s') }} UTC{{ now()->format('P') }}</p>
</div>

</body>
</html>
