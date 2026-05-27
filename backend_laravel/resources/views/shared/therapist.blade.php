<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reporte de seguimiento — Mindra</title>
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'Segoe UI', system-ui, sans-serif; background: #f8fafc; color: #1e293b; font-size: 14px; }
  .header { background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); color: #fff; padding: 32px 24px 24px; }
  .header h1 { font-size: 22px; font-weight: 700; margin-bottom: 4px; }
  .header p  { font-size: 13px; opacity: .8; }
  .badge { display: inline-block; background: rgba(255,255,255,.2); border-radius: 99px; padding: 3px 12px; font-size: 11px; font-weight: 600; margin-top: 10px; }
  .warning { background: #fef3c7; border-left: 4px solid #f59e0b; padding: 12px 16px; margin: 20px 24px; border-radius: 6px; font-size: 12px; color: #92400e; }
  .container { max-width: 820px; margin: 0 auto; padding: 0 16px 60px; }
  .section { background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; padding: 20px; margin-top: 20px; }
  .section h2 { font-size: 15px; font-weight: 700; color: #334155; margin-bottom: 14px; display: flex; align-items: center; gap: 8px; }
  .meta-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 12px; }
  .meta-card { background: #f8fafc; border-radius: 8px; padding: 14px; text-align: center; }
  .meta-card .val { font-size: 28px; font-weight: 800; color: #4f46e5; }
  .meta-card .lbl { font-size: 11px; color: #64748b; margin-top: 4px; }
  table { width: 100%; border-collapse: collapse; font-size: 13px; }
  th { background: #f1f5f9; text-align: left; padding: 8px 10px; font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: .5px; }
  td { padding: 8px 10px; border-bottom: 1px solid #f1f5f9; }
  tr:last-child td { border-bottom: none; }
  .chip { display: inline-block; padding: 2px 8px; border-radius: 99px; font-size: 11px; font-weight: 600; }
  .chip-minimal  { background: #dcfce7; color: #166534; }
  .chip-mild     { background: #fef9c3; color: #854d0e; }
  .chip-moderate { background: #fed7aa; color: #9a3412; }
  .chip-severe   { background: #fecaca; color: #991b1b; }
  .mood-bar { display: flex; align-items: center; gap: 8px; }
  .mood-fill { height: 8px; border-radius: 99px; background: #4f46e5; }
  .footer { text-align: center; font-size: 11px; color: #94a3b8; margin-top: 32px; }
  @media print {
    .warning { display: none; }
    body { background: #fff; }
    .section { border: 1px solid #e2e8f0; box-shadow: none; }
  }
</style>
</head>
<body>

<div class="header">
  <h1>Reporte de seguimiento emocional</h1>
  <p>Paciente: <strong>{{ $user->name }}</strong></p>
  <p>Generado: {{ now()->format('d/m/Y H:i') }} · Válido hasta: {{ $share->expires_at->format('d/m/Y') }}</p>
  <span class="badge">Confidencial — solo para uso profesional</span>
</div>

<div class="warning">
  ⚠️ Este reporte es generado automáticamente por Mindra y no reemplaza una evaluación clínica. Los datos provienen del autoregistro del paciente.
</div>

<div class="container">

  {{-- Resumen general --}}
  <div class="section">
    <h2>📊 Resumen del período</h2>
    @php
      $avgMood = $journal->avg('mood_score') ? round($journal->avg('mood_score'), 1) : '—';
      $totalSessions = $history->count();
      $daysActive = $journal->groupBy(fn($e) => $e->created_at->toDateString())->count();
    @endphp
    <div class="meta-grid">
      <div class="meta-card">
        <div class="val">{{ $avgMood }}</div>
        <div class="lbl">Ánimo promedio<br><small>(escala 1–5)</small></div>
      </div>
      <div class="meta-card">
        <div class="val">{{ $journal->count() }}</div>
        <div class="lbl">Registros de ánimo</div>
      </div>
      <div class="meta-card">
        <div class="val">{{ $totalSessions }}</div>
        <div class="lbl">Sesiones de chat</div>
      </div>
      <div class="meta-card">
        <div class="val">{{ $daysActive }}</div>
        <div class="lbl">Días activos</div>
      </div>
    </div>
  </div>

  {{-- Evaluación GAD-7 --}}
  @if($assessment)
  <div class="section">
    <h2>🧠 Última evaluación GAD-7</h2>
    <p style="margin-bottom:12px;">
      Fecha: <strong>{{ $assessment->created_at->format('d/m/Y') }}</strong> ·
      Puntaje total: <strong>{{ $assessment->score }} / 21</strong> ·
      <span class="chip chip-{{ $assessment->severity }}">
        {{ \App\Models\Assessment::severityLabel($assessment->severity) }}
      </span>
    </p>
    @php
      $gad7Questions = [
        'Sentirse nervioso/a, ansioso/a o al límite',
        'No poder dejar de preocuparse',
        'Preocuparse demasiado por cosas distintas',
        'Dificultad para relajarse',
        'Estar tan inquieto/a que no puede quedarse quieto/a',
        'Irritarse o enojarse con facilidad',
        'Sentir miedo como si algo terrible fuera a ocurrir',
      ];
    @endphp
    <table>
      <thead><tr><th>#</th><th>Pregunta</th><th>Respuesta (0–3)</th></tr></thead>
      <tbody>
        @foreach($gad7Questions as $i => $q)
          <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $q }}</td>
            <td><strong>{{ $assessment->answers[$i] ?? '—' }}</strong></td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @endif

  {{-- Diario emocional --}}
  @if($journal->count() > 0)
  <div class="section">
    <h2>📓 Diario emocional (últimos 30 registros)</h2>
    <table>
      <thead><tr><th>Fecha</th><th>Estado</th><th>Puntuación</th><th>Nota</th></tr></thead>
      <tbody>
        @foreach($journal as $entry)
          <tr>
            <td>{{ $entry->created_at->format('d/m/Y H:i') }}</td>
            <td>{{ $entry->mood_emoji }} {{ $entry->mood_label }}</td>
            <td>
              <div class="mood-bar">
                <div class="mood-fill" style="width:{{ $entry->mood_score * 20 }}px;"></div>
                <span>{{ $entry->mood_score }}/5</span>
              </div>
            </td>
            <td style="max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
              {{ $entry->note ?? '—' }}
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @endif

  {{-- Historial de sesiones --}}
  @if($history->count() > 0)
  <div class="section">
    <h2>💬 Sesiones recientes de chat</h2>
    <table>
      <thead><tr><th>Fecha</th><th>Texto</th><th>Etiqueta</th><th>Emoción</th><th>Prob.</th></tr></thead>
      <tbody>
        @foreach($history as $rec)
          <tr>
            <td style="white-space:nowrap;">{{ $rec->created_at->format('d/m/Y') }}</td>
            <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
              {{ Str::limit($rec->texto ?? '', 60) }}
            </td>
            <td>{{ $rec->etiqueta ?? '—' }}</td>
            <td>{{ $rec->emocion ?? '—' }}</td>
            <td>{{ $rec->probabilidad ? number_format($rec->probabilidad * 100, 0) . '%' : '—' }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @endif

  <div class="footer">
    <p>Generado por <strong>Mindra</strong> · mindra.cafined.org · CAFINED · Morelia, Michoacán</p>
    <p style="margin-top:4px;">Este enlace expira el {{ $share->expires_at->format('d/m/Y') }} y no puede ser compartido.</p>
    <button onclick="window.print()" style="margin-top:12px;padding:8px 20px;background:#4f46e5;color:#fff;border:none;border-radius:8px;cursor:pointer;font-size:13px;">
      🖨 Imprimir / Guardar PDF
    </button>
  </div>

</div>
</body>
</html>
