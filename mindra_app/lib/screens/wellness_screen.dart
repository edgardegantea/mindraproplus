import 'package:fl_chart/fl_chart.dart';
import 'package:flutter/material.dart';
import 'package:pdf/pdf.dart';
import 'package:pdf/widgets.dart' as pw;
import 'package:printing/printing.dart';
import 'package:provider/provider.dart';
import 'package:table_calendar/table_calendar.dart';

import '../providers/auth_provider.dart';
import '../services/api_service.dart';
import '../theme/mindra_theme.dart';
import '../utils/responsive.dart';
import 'assessment_screen.dart';
import 'history_screen.dart';
import 'library_screen.dart';
import 'mood_journal_screen.dart';
import 'plans_screen.dart';
import 'programs_screen.dart';
import 'techniques_screen.dart';
import 'weekly_report_screen.dart';

class WellnessScreen extends StatefulWidget {
  const WellnessScreen({super.key});

  @override
  State<WellnessScreen> createState() => _WellnessScreenState();
}

class _WellnessScreenState extends State<WellnessScreen>
    with SingleTickerProviderStateMixin {
  late final TabController _tabs;
  bool _loading = false;
  String? _error;

  // Datos cargados del backend
  Map<DateTime, double> _calendarData = {};
  List<Map<String, dynamic>> _weeklyTrend = [];
  List<Map<String, dynamic>> _emotions = [];

  DateTime _focusedDay = DateTime.now();
  DateTime? _selectedDay;

  @override
  void initState() {
    super.initState();
    _tabs = TabController(length: 3, vsync: this);
    _tabs.addListener(_onTabChanged);
  }

  @override
  void dispose() {
    _tabs.removeListener(_onTabChanged);
    _tabs.dispose();
    super.dispose();
  }

  void _onTabChanged() {
    if (!_tabs.indexIsChanging && _tabs.index > 0 && !_loading && _calendarData.isEmpty) {
      _checkAndLoad();
    }
  }

  Future<void> _checkAndLoad() async {
    final plan = context.read<AuthProvider>().effectivePlan;
    if (plan == null || !plan.hasFeature('historial')) {
      setState(() { _loading = false; _error = 'upgrade'; });
      return;
    }
    await _load();
  }

  Future<void> _load() async {
    setState(() { _loading = true; _error = null; });
    try {
      final api  = context.read<ApiService>();
      final plan = context.read<AuthProvider>().effectivePlan;

      final calRaw = await api.getCalendar();

      // Tendencias solo disponibles en Plus (feature:estadisticas)
      List<Map<String, dynamic>> weekly   = [];
      List<Map<String, dynamic>> emotions = [];
      if (plan?.hasFeature('estadisticas') == true) {
        final trendsRaw = await api.getTrends();
        weekly   = List<Map<String, dynamic>>.from((trendsRaw['weekly']   as List?) ?? []);
        emotions = List<Map<String, dynamic>>.from((trendsRaw['emotions'] as List?) ?? []);
      }

      final calMap = <DateTime, double>{};
      for (final row in calRaw) {
        final d = DateTime.tryParse(row['date'] as String? ?? '');
        final v = (row['avg_anxiety'] as num?)?.toDouble();
        if (d != null && v != null) calMap[DateTime(d.year, d.month, d.day)] = v;
      }

      setState(() {
        _calendarData = calMap;
        _weeklyTrend  = weekly;
        _emotions     = emotions;
        _loading      = false;
      });
    } on ApiException catch (e) {
      setState(() { _error = e.message; _loading = false; });
    }
  }

  Widget _analyticsContent(Widget child, {bool requiresStats = false}) {
    if (_loading) return const Center(child: CircularProgressIndicator());
    if (_error == 'upgrade') return _UpgradeWall();
    if (_error != null) return _ErrorView(_error!, _load);
    if (requiresStats) {
      final hasStats =
          context.read<AuthProvider>().effectivePlan?.hasFeature('estadisticas') == true;
      if (!hasStats) return _UpgradeWall(message: 'Las tendencias y estadísticas avanzadas están disponibles en el plan Plus.');
    }
    return child;
  }

  @override
  Widget build(BuildContext context) {
    final hasPdf = !_loading && _error == null;
    final hasClinic = hasPdf &&
        (context.read<AuthProvider>().effectivePlan?.hasFeature('reporte_clinico') ?? false);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Bienestar'),
        actions: [
          if (hasPdf)
            IconButton(
              icon: const Icon(Icons.picture_as_pdf_outlined),
              tooltip: 'Reporte semanal PDF',
              onPressed: _exportPdf,
            ),
          if (hasClinic)
            IconButton(
              icon: const Icon(Icons.medical_information_outlined,
                  color: MindraColors.indigo),
              tooltip: 'Reporte clínico (30 días)',
              onPressed: _exportClinicalReport,
            ),
        ],
        bottom: TabBar(
          controller: _tabs,
          tabs: const [
            Tab(icon: Icon(Icons.grid_view_outlined), text: 'Herramientas'),
            Tab(icon: Icon(Icons.calendar_month_outlined), text: 'Calendario'),
            Tab(icon: Icon(Icons.show_chart_outlined), text: 'Tendencias'),
          ],
        ),
      ),
      body: WebFrame(
        child: TabBarView(
          controller: _tabs,
          children: [
            const _HerramientasTab(),
            _analyticsContent(_CalendarTab(
              data: _calendarData,
              focusedDay: _focusedDay,
              selectedDay: _selectedDay,
              onDaySelected: (sel, foc) =>
                  setState(() { _selectedDay = sel; _focusedDay = foc; }),
            )),
            _analyticsContent(
              _TrendsTab(weekly: _weeklyTrend, emotions: _emotions),
              requiresStats: true,
            ),
          ],
        ),
      ),
    );
  }

  Future<void> _exportPdf() async {
    try {
      final report = await context.read<ApiService>().getWeeklyReport();
      final data   = report['report'] as Map<String, dynamic>;
      final pdf    = pw.Document();

      pdf.addPage(pw.Page(
        pageFormat: PdfPageFormat.a4,
        margin: const pw.EdgeInsets.all(32),
        build: (ctx) => pw.Column(
          crossAxisAlignment: pw.CrossAxisAlignment.start,
          children: [
            pw.Text('Reporte Semanal Mindra',
                style: pw.TextStyle(fontSize: 22, fontWeight: pw.FontWeight.bold)),
            pw.SizedBox(height: 6),
            pw.Text(
              'Período: ${data['period']?['from'] ?? ''} → ${data['period']?['to'] ?? ''}',
              style: const pw.TextStyle(fontSize: 11, color: PdfColors.grey600),
            ),
            pw.Divider(height: 24),
            _pdfStat('Sesiones totales', '${data['total_sessions'] ?? 0}'),
            _pdfStat('Ansiedad promedio',
                '${((data['avg_anxiety'] as num? ?? 0) * 100).toStringAsFixed(1)}%'),
            if (data['peak_day'] != null)
              _pdfStat('Día con mayor nivel', '${data['peak_day']}'),
            pw.SizedBox(height: 16),
            if (data['emotions'] is Map && (data['emotions'] as Map).isNotEmpty) ...[
              pw.Text('Emociones detectadas',
                  style: pw.TextStyle(fontSize: 13, fontWeight: pw.FontWeight.bold)),
              pw.SizedBox(height: 6),
              for (final entry in (data['emotions'] as Map).entries)
                pw.Text('  • ${entry.key}: ${entry.value}',
                    style: const pw.TextStyle(fontSize: 11)),
              pw.SizedBox(height: 16),
            ],
            pw.Text('Sesiones recientes',
                style: pw.TextStyle(fontSize: 13, fontWeight: pw.FontWeight.bold)),
            pw.SizedBox(height: 6),
            for (final r in (data['records'] as List? ?? []).take(10))
              pw.Padding(
                padding: const pw.EdgeInsets.only(bottom: 6),
                child: pw.Column(
                  crossAxisAlignment: pw.CrossAxisAlignment.start,
                  children: [
                    pw.Text(
                      '${r['created_at']?.toString().substring(0, 10) ?? ''} — ${r['predicted_label'] ?? ''}',
                      style: const pw.TextStyle(fontSize: 10, color: PdfColors.grey700),
                    ),
                    pw.Text(
                      (r['input_text'] as String? ?? '').length > 120
                          ? '${(r['input_text'] as String).substring(0, 120)}…'
                          : r['input_text'] as String? ?? '',
                      style: const pw.TextStyle(fontSize: 11),
                    ),
                  ],
                ),
              ),
            pw.Spacer(),
            pw.Text('Generado por Mindra · mindra.cafined.org',
                style: const pw.TextStyle(fontSize: 9, color: PdfColors.grey400)),
          ],
        ),
      ));

      await Printing.layoutPdf(onLayout: (_) async => pdf.save());
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context)
            .showSnackBar(SnackBar(content: Text('Error al generar PDF: $e')));
      }
    }
  }

  Future<void> _exportClinicalReport() async {
    try {
      final raw     = await context.read<ApiService>().getClinicalReport();
      final clinical = raw['clinical'] as Map<String, dynamic>;
      final summary  = clinical['summary']  as Map<String, dynamic>;
      final user     = clinical['user']     as Map<String, dynamic>;
      final period   = clinical['period']   as Map<String, dynamic>;
      final emotions = clinical['emotion_distribution'] is Map
          ? Map<String, dynamic>.from(clinical['emotion_distribution'] as Map)
          : <String, dynamic>{};
      final timeline = clinical['anxiety_timeline'] is Map
          ? Map<String, dynamic>.from(clinical['anxiety_timeline'] as Map)
          : <String, dynamic>{};
      final records  = (clinical['records'] as List?) ?? [];
      final pdf = pw.Document();

      pdf.addPage(pw.MultiPage(
        pageFormat: PdfPageFormat.a4,
        margin: const pw.EdgeInsets.all(36),
        build: (ctx) => [
          pw.Text('Reporte Clínico — Mindra',
              style: pw.TextStyle(fontSize: 20, fontWeight: pw.FontWeight.bold)),
          pw.SizedBox(height: 4),
          pw.Text('Generado para uso clínico/terapéutico. Datos privados y confidenciales.',
              style: const pw.TextStyle(fontSize: 9, color: PdfColors.grey600)),
          pw.Divider(height: 20),
          // Datos del usuario
          pw.Text('Paciente', style: pw.TextStyle(fontSize: 13, fontWeight: pw.FontWeight.bold)),
          pw.SizedBox(height: 4),
          _pdfRow('Nombre', user['name']?.toString() ?? ''),
          _pdfRow('Email',  user['email']?.toString() ?? ''),
          _pdfRow('Período', '${period['from']} → ${period['to']}'),
          pw.SizedBox(height: 14),
          // Resumen
          pw.Text('Resumen', style: pw.TextStyle(fontSize: 13, fontWeight: pw.FontWeight.bold)),
          pw.SizedBox(height: 4),
          _pdfRow('Total de sesiones',   '${summary['total_sessions'] ?? 0}'),
          _pdfRow('Ansiedad promedio',   '${((summary['avg_anxiety'] as num? ?? 0) * 100).toStringAsFixed(1)}%'),
          _pdfRow('Pico de ansiedad',    '${((summary['max_anxiety'] as num? ?? 0) * 100).toStringAsFixed(1)}%'),
          _pdfRow('Episodios de crisis (>75%)', '${summary['crisis_episodes'] ?? 0}'),
          pw.SizedBox(height: 14),
          // Distribución de emociones
          if (emotions.isNotEmpty) ...[
            pw.Text('Distribución de emociones',
                style: pw.TextStyle(fontSize: 13, fontWeight: pw.FontWeight.bold)),
            pw.SizedBox(height: 4),
            for (final e in emotions.entries)
              _pdfRow(e.key, '${(e.value as Map?)?['count'] ?? 0} sesiones'),
            pw.SizedBox(height: 14),
          ],
          // Timeline de ansiedad
          if (timeline.isNotEmpty) ...[
            pw.Text('Evolución diaria de ansiedad',
                style: pw.TextStyle(fontSize: 13, fontWeight: pw.FontWeight.bold)),
            pw.SizedBox(height: 4),
            for (final t in timeline.entries)
              _pdfRow(
                t.key,
                'Prom: ${(((t.value as Map?)?['avg'] as num? ?? 0) * 100).toStringAsFixed(1)}%  '
                'Máx: ${(((t.value as Map?)?['max'] as num? ?? 0) * 100).toStringAsFixed(1)}%  '
                'Sesiones: ${(t.value as Map?)?['count'] ?? 0}',
              ),
            pw.SizedBox(height: 14),
          ],
          // Sesiones
          pw.Text('Sesiones recientes',
              style: pw.TextStyle(fontSize: 13, fontWeight: pw.FontWeight.bold)),
          pw.SizedBox(height: 4),
          for (final r in records.take(25))
            pw.Padding(
              padding: const pw.EdgeInsets.only(bottom: 8),
              child: pw.Column(
                crossAxisAlignment: pw.CrossAxisAlignment.start,
                children: [
                  pw.Row(children: [
                    pw.Text(
                      r['created_at']?.toString().substring(0, 10) ?? '',
                      style: const pw.TextStyle(fontSize: 9, color: PdfColors.grey700),
                    ),
                    pw.SizedBox(width: 8),
                    if (r['predicted_label'] != null)
                      pw.Text('${r['predicted_label']}',
                          style: const pw.TextStyle(fontSize: 9, color: PdfColors.deepPurple)),
                    if (r['emotion_label'] != null)
                      pw.Text(' · Emoción: ${r['emotion_label']}',
                          style: const pw.TextStyle(fontSize: 9, color: PdfColors.indigo)),
                    if (r['predicted_probability'] != null)
                      pw.Text(' · ${((r['predicted_probability'] as num) * 100).toStringAsFixed(1)}%',
                          style: const pw.TextStyle(fontSize: 9, color: PdfColors.grey700)),
                  ]),
                  pw.Text(
                    (r['input_text'] as String? ?? '').length > 150
                        ? '${(r['input_text'] as String).substring(0, 150)}…'
                        : r['input_text'] as String? ?? '',
                    style: const pw.TextStyle(fontSize: 10),
                  ),
                ],
              ),
            ),
          pw.SizedBox(height: 16),
          pw.Text(
            'Este reporte es generado automáticamente por Mindra y no sustituye el diagnóstico clínico.\nmindra.cafined.org',
            style: const pw.TextStyle(fontSize: 8, color: PdfColors.grey500),
          ),
        ],
      ));

      await Printing.layoutPdf(onLayout: (_) async => pdf.save());
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context)
            .showSnackBar(SnackBar(content: Text('Error al generar reporte clínico: $e')));
      }
    }
  }

  pw.Widget _pdfRow(String label, String value) => pw.Padding(
        padding: const pw.EdgeInsets.only(bottom: 3),
        child: pw.Row(crossAxisAlignment: pw.CrossAxisAlignment.start, children: [
          pw.SizedBox(
            width: 160,
            child: pw.Text('$label:',
                style: pw.TextStyle(fontSize: 10, fontWeight: pw.FontWeight.bold)),
          ),
          pw.Expanded(
            child: pw.Text(value, style: const pw.TextStyle(fontSize: 10)),
          ),
        ]),
      );

  pw.Widget _pdfStat(String label, String value) => pw.Padding(
        padding: const pw.EdgeInsets.only(bottom: 4),
        child: pw.Row(children: [
          pw.Text('$label: ',
              style: pw.TextStyle(fontSize: 11, fontWeight: pw.FontWeight.bold)),
          pw.Text(value, style: const pw.TextStyle(fontSize: 11)),
        ]),
      );
}

// ─── HERRAMIENTAS ─────────────────────────────────────────────────────────────

class _HerramientasTab extends StatefulWidget {
  const _HerramientasTab();

  @override
  State<_HerramientasTab> createState() => _HerramientasTabState();
}

class _HerramientasTabState extends State<_HerramientasTab> {
  // ── Racha ──────────────────────────────────────────────────────────────────
  int _streak = 0;
  bool _activeToday = false;

  // ── Ánimo ──────────────────────────────────────────────────────────────────
  static const _moods = [
    (score: 1, emoji: '😔', color: Color(0xFFef4444)),
    (score: 2, emoji: '😕', color: Color(0xFFf97316)),
    (score: 3, emoji: '😐', color: Color(0xFFeab308)),
    (score: 4, emoji: '🙂', color: Color(0xFF22c55e)),
    (score: 5, emoji: '😄', color: Color(0xFF06b6d4)),
  ];
  int? _selectedMood;
  bool _moodSaved = false;
  bool _saving = false;

  @override
  void initState() {
    super.initState();
    _loadStreak();
  }

  Future<void> _loadStreak() async {
    try {
      final data = await context.read<ApiService>().getStreak();
      if (mounted) {
        setState(() {
          _streak = data['current_streak'] as int? ?? 0;
          _activeToday = data['active_today'] as bool? ?? false;
        });
      }
    } catch (_) {}
  }

  Future<void> _saveMood(int score) async {
    setState(() { _selectedMood = score; _saving = true; });
    try {
      await context.read<ApiService>().addJournalEntry(moodScore: score);
      if (mounted) setState(() { _moodSaved = true; _saving = false; });
    } catch (_) {
      if (mounted) setState(() { _saving = false; });
    }
  }

  static const _tools = [
    (emoji: '🧘', label: 'Técnicas',   desc: 'Respiración y mindfulness', color: Color(0xFF7c3aed)),
    (emoji: '📓', label: 'Diario',     desc: 'Registra tus emociones',    color: Color(0xFF4f46e5)),
    (emoji: '🗓️', label: 'Programas',  desc: 'Planes estructurados',      color: Color(0xFF0891b2)),
    (emoji: '📚', label: 'Biblioteca', desc: 'Guías y recursos',           color: Color(0xFF16a34a)),
    (emoji: '🧠', label: 'Evaluación', desc: 'Test GAD-7 y PHQ-9',        color: Color(0xFFdc2626)),
    (emoji: '📊', label: 'Reporte',    desc: 'Resumen semanal',            color: Color(0xFF6366f1)),
    (emoji: '📜', label: 'Historial',  desc: 'Tus sesiones anteriores',    color: Color(0xFF64748b)),
    (emoji: '⭐', label: 'Planes',     desc: 'Gestiona tu suscripción',    color: Color(0xFFd97706)),
  ];

  static const _tips = [
    ('🌬️', 'Respira profundo', 'Tres respiraciones lentas pueden reducir la ansiedad en segundos.'),
    ('🧠', 'Cuestiona tus pensamientos', '¿Ese pensamiento negativo tiene evidencia real? Intenta reformularlo.'),
    ('🌿', 'Grounding 5-4-3-2-1', 'Nombra 5 cosas que ves, 4 que tocas, 3 que escuchas, 2 que hueles, 1 que saboreas.'),
    ('💧', 'Hidratación', 'La deshidratación puede intensificar los síntomas de ansiedad.'),
    ('🚶', 'Muévete', '10 minutos de caminata reducen el cortisol significativamente.'),
    ('📓', 'Escribe', 'Poner en palabras tus emociones las hace más manejables.'),
    ('😴', 'Prioriza el sueño', 'Dormir menos de 7h eleva el riesgo de ansiedad hasta 30%.'),
    ('🤝', 'Conéctate', 'Hablar con alguien de confianza es una de las mejores herramientas contra el estrés.'),
  ];

  void _navigate(String label) {
    final route = switch (label) {
      'Técnicas'   => MaterialPageRoute(builder: (_) => const TechniquesScreen()),
      'Diario'     => MaterialPageRoute(builder: (_) => const MoodJournalScreen()),
      'Programas'  => MaterialPageRoute(builder: (_) => const ProgramsScreen()),
      'Biblioteca' => MaterialPageRoute(builder: (_) => const LibraryScreen()),
      'Evaluación' => MaterialPageRoute(builder: (_) => const AssessmentScreen()),
      'Reporte'    => MaterialPageRoute(builder: (_) => const WeeklyReportScreen()),
      'Historial'  => MaterialPageRoute(builder: (_) => const HistoryScreen()),
      'Planes'     => MaterialPageRoute(builder: (_) => const PlansScreen()),
      _            => null,
    };
    if (route != null) Navigator.push(context, route);
  }

  @override
  Widget build(BuildContext context) {
    final tip = _tips[DateTime.now().day % _tips.length];

    return ListView(
      padding: const EdgeInsets.fromLTRB(16, 16, 16, 40),
      children: [

        // ── Racha + Check-in ─────────────────────────────────────────────────
        Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
          // Racha
          Expanded(
            flex: 2,
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
              decoration: BoxDecoration(
                color: Theme.of(context).colorScheme.surface,
                borderRadius: BorderRadius.circular(14),
                border: Border.all(color: Theme.of(context).dividerColor),
              ),
              child: Row(children: [
                Text(_streak > 0 ? '🔥' : '💤',
                    style: const TextStyle(fontSize: 22)),
                const SizedBox(width: 8),
                Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                  Text(
                    _streak > 0 ? '$_streak días' : 'Sin racha',
                    style: const TextStyle(fontSize: 14, fontWeight: FontWeight.bold),
                  ),
                  Text(
                    _activeToday ? '✅ Hoy activo' : 'Registra hoy',
                    style: const TextStyle(
                        fontSize: 10, color: MindraColors.textSecondary),
                  ),
                ]),
              ]),
            ),
          ),
          const SizedBox(width: 10),
          // Ánimo
          Expanded(
            flex: 3,
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
              decoration: BoxDecoration(
                color: Theme.of(context).colorScheme.surface,
                borderRadius: BorderRadius.circular(14),
                border: Border.all(color: Theme.of(context).dividerColor),
              ),
              child: _moodSaved
                  ? Row(mainAxisAlignment: MainAxisAlignment.center, children: [
                      Text(
                        _moods.firstWhere((m) => m.score == _selectedMood).emoji,
                        style: const TextStyle(fontSize: 20),
                      ),
                      const SizedBox(width: 6),
                      const Text('¡Guardado!',
                          style: TextStyle(
                              fontSize: 12, color: MindraColors.textSecondary)),
                    ])
                  : Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        const Text('¿Cómo estás?',
                            style: TextStyle(
                                fontSize: 11, fontWeight: FontWeight.w700)),
                        const SizedBox(height: 6),
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: _moods.map((m) => GestureDetector(
                            onTap: _saving ? null : () => _saveMood(m.score),
                            child: AnimatedContainer(
                              duration: const Duration(milliseconds: 150),
                              padding: const EdgeInsets.all(4),
                              decoration: BoxDecoration(
                                color: _selectedMood == m.score
                                    ? m.color.withValues(alpha: 0.15)
                                    : Colors.transparent,
                                borderRadius: BorderRadius.circular(8),
                              ),
                              child: Text(m.emoji,
                                  style: TextStyle(
                                      fontSize:
                                          _selectedMood == m.score ? 24 : 20)),
                            ),
                          )).toList(),
                        ),
                      ],
                    ),
            ),
          ),
        ]),

        const SizedBox(height: 20),

        // ── Herramientas ─────────────────────────────────────────────────────
        const Text('Herramientas',
            style: TextStyle(
                fontSize: 12,
                fontWeight: FontWeight.w700,
                color: MindraColors.textSecondary,
                letterSpacing: .5)),
        const SizedBox(height: 10),
        GridView.count(
          crossAxisCount: 3,
          shrinkWrap: true,
          physics: const NeverScrollableScrollPhysics(),
          mainAxisSpacing: 10,
          crossAxisSpacing: 10,
          childAspectRatio: 0.85,
          children: _tools.take(6).map((t) => GestureDetector(
            onTap: () => _navigate(t.label),
            child: Container(
              decoration: BoxDecoration(
                color: t.color.withValues(alpha: 0.08),
                borderRadius: BorderRadius.circular(16),
                border: Border.all(color: t.color.withValues(alpha: 0.25)),
              ),
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Text(t.emoji, style: const TextStyle(fontSize: 26)),
                  const SizedBox(height: 6),
                  Text(t.label,
                      textAlign: TextAlign.center,
                      style: TextStyle(
                          fontSize: 11,
                          fontWeight: FontWeight.w700,
                          color: t.color),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis),
                  const SizedBox(height: 2),
                  Padding(
                    padding: const EdgeInsets.symmetric(horizontal: 4),
                    child: Text(t.desc,
                        textAlign: TextAlign.center,
                        style: const TextStyle(
                            fontSize: 9,
                            color: MindraColors.textSecondary),
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis),
                  ),
                ],
              ),
            ),
          )).toList(),
        ),
        const SizedBox(height: 10),
        GestureDetector(
          onTap: () => _navigate('Planes'),
          child: Container(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
            decoration: BoxDecoration(
              gradient: const LinearGradient(
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
                colors: [Color(0x1Fd97706), Color(0x09d97706)],
              ),
              borderRadius: BorderRadius.circular(14),
              border: Border.all(
                  color: const Color(0xFFd97706).withValues(alpha: 0.3)),
            ),
            child: const Row(children: [
              Text('⭐', style: TextStyle(fontSize: 22)),
              SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text('Planes',
                        style: TextStyle(
                            fontSize: 14, fontWeight: FontWeight.w700)),
                    Text('Gestiona tu suscripción y desbloquea funciones',
                        style: TextStyle(
                            fontSize: 11,
                            color: MindraColors.textSecondary)),
                  ],
                ),
              ),
              Icon(Icons.arrow_forward_ios,
                  size: 14, color: Color(0xFFd97706)),
            ]),
          ),
        ),

        const SizedBox(height: 20),

        // ── Tip del día ───────────────────────────────────────────────────────
        Container(
          padding: const EdgeInsets.all(14),
          decoration: BoxDecoration(
            gradient: LinearGradient(
              colors: [
                MindraColors.violet.withValues(alpha: 0.10),
                MindraColors.blue.withValues(alpha: 0.06),
              ],
            ),
            borderRadius: BorderRadius.circular(14),
            border: Border.all(color: MindraColors.violet.withValues(alpha: 0.2)),
          ),
          child: Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Text(tip.$1, style: const TextStyle(fontSize: 26)),
            const SizedBox(width: 12),
            Expanded(child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(children: [
                  const Text('💡 Tip del día · ',
                      style: TextStyle(
                          fontSize: 10,
                          color: MindraColors.textSecondary,
                          fontWeight: FontWeight.w700,
                          letterSpacing: .3)),
                  Text(tip.$2,
                      style: const TextStyle(
                          fontSize: 10,
                          color: MindraColors.violet,
                          fontWeight: FontWeight.w700)),
                ]),
                const SizedBox(height: 4),
                Text(tip.$3,
                    style: const TextStyle(
                        fontSize: 13,
                        color: MindraColors.textSecondary,
                        height: 1.5)),
              ],
            )),
          ]),
        ),
      ],
    );
  }
}

// ─── CALENDARIO ───────────────────────────────────────────────────────────────

class _CalendarTab extends StatelessWidget {
  final Map<DateTime, double> data;
  final DateTime focusedDay;
  final DateTime? selectedDay;
  final void Function(DateTime, DateTime) onDaySelected;

  const _CalendarTab({
    required this.data,
    required this.focusedDay,
    required this.selectedDay,
    required this.onDaySelected,
  });

  Color _colorFor(double v) {
    if (v < 0.3) return Colors.green.shade400;
    if (v < 0.6) return Colors.orange.shade400;
    return Colors.red.shade400;
  }

  @override
  Widget build(BuildContext context) {
    final selected = selectedDay;
    final selValue = selected != null
        ? data[DateTime(selected.year, selected.month, selected.day)]
        : null;

    return SingleChildScrollView(
      padding: const EdgeInsets.all(16),
      child: Column(
        children: [
          // Leyenda
          Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              _Legend(color: Colors.green.shade400, label: 'Bajo (<30%)'),
              const SizedBox(width: 16),
              _Legend(color: Colors.orange.shade400, label: 'Moderado'),
              const SizedBox(width: 16),
              _Legend(color: Colors.red.shade400, label: 'Alto (>60%)'),
            ],
          ),
          const SizedBox(height: 12),
          TableCalendar(
            firstDay: DateTime.now().subtract(const Duration(days: 60)),
            lastDay: DateTime.now(),
            focusedDay: focusedDay,
            selectedDayPredicate: (d) => isSameDay(d, selectedDay),
            onDaySelected: onDaySelected,
            calendarFormat: CalendarFormat.month,
            headerStyle: const HeaderStyle(
              formatButtonVisible: false,
              titleCentered: true,
            ),
            calendarStyle: const CalendarStyle(
              outsideDaysVisible: false,
            ),
            calendarBuilders: CalendarBuilders(
              defaultBuilder: (ctx, day, focDay) {
                final key = DateTime(day.year, day.month, day.day);
                final val = data[key];
                if (val == null) return null;
                return Container(
                  margin: const EdgeInsets.all(4),
                  decoration: BoxDecoration(
                    color: _colorFor(val).withValues(alpha: 0.25),
                    shape: BoxShape.circle,
                    border: Border.all(color: _colorFor(val), width: 1.5),
                  ),
                  child: Center(
                    child: Text('${day.day}',
                        style: TextStyle(
                            fontSize: 13,
                            fontWeight: FontWeight.w600,
                            color: _colorFor(val))),
                  ),
                );
              },
            ),
          ),
          if (selValue != null) ...[
            const SizedBox(height: 16),
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: Theme.of(context).colorScheme.surface,
                borderRadius: BorderRadius.circular(14),
              ),
              child: Row(
                children: [
                  Icon(Icons.insights, color: _colorFor(selValue)),
                  const SizedBox(width: 10),
                  Text(
                    'Ansiedad promedio ese día: ${(selValue * 100).toStringAsFixed(1)}%',
                    style: TextStyle(color: _colorFor(selValue), fontWeight: FontWeight.w600),
                  ),
                ],
              ),
            ),
          ],
          if (data.isEmpty)
            const Padding(
              padding: EdgeInsets.only(top: 32),
              child: Text('Aún no hay sesiones registradas este mes.',
                  textAlign: TextAlign.center,
                  style: TextStyle(color: MindraColors.textSecondary)),
            ),
        ],
      ),
    );
  }
}

class _Legend extends StatelessWidget {
  final Color color;
  final String label;
  const _Legend({required this.color, required this.label});

  @override
  Widget build(BuildContext context) {
    return Row(mainAxisSize: MainAxisSize.min, children: [
      Container(width: 12, height: 12,
          decoration: BoxDecoration(color: color, shape: BoxShape.circle)),
      const SizedBox(width: 4),
      Text(label, style: const TextStyle(fontSize: 11, color: MindraColors.textSecondary)),
    ]);
  }
}

// ─── TENDENCIAS ───────────────────────────────────────────────────────────────

class _TrendsTab extends StatelessWidget {
  final List<Map<String, dynamic>> weekly;
  final List<Map<String, dynamic>> emotions;

  const _TrendsTab({required this.weekly, required this.emotions});

  @override
  Widget build(BuildContext context) {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text('Ansiedad promedio por semana',
              style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
          const SizedBox(height: 12),
          if (weekly.isEmpty)
            const Center(
                child: Padding(
              padding: EdgeInsets.symmetric(vertical: 32),
              child: Text('Sin datos suficientes todavía.',
                  style: TextStyle(color: MindraColors.textSecondary)),
            ))
          else
            SizedBox(
              height: 200,
              child: LineChart(
                LineChartData(
                  minY: 0,
                  maxY: 1,
                  gridData: const FlGridData(show: true),
                  borderData: FlBorderData(show: false),
                  titlesData: FlTitlesData(
                    leftTitles: AxisTitles(
                      sideTitles: SideTitles(
                        showTitles: true,
                        reservedSize: 36,
                        getTitlesWidget: (v, _) => Text(
                          '${(v * 100).toInt()}%',
                          style: const TextStyle(
                              fontSize: 10, color: MindraColors.textSecondary),
                        ),
                      ),
                    ),
                    bottomTitles: AxisTitles(
                      sideTitles: SideTitles(
                        showTitles: true,
                        reservedSize: 28,
                        interval: 1,
                        getTitlesWidget: (v, _) {
                          final i = v.toInt();
                          if (i < 0 || i >= weekly.length) return const SizedBox();
                          final dateStr = weekly[i]['week_start'] as String? ?? '';
                          if (dateStr.length < 7) return const SizedBox();
                          return Text(dateStr.substring(5), // MM-DD
                              style: const TextStyle(
                                  fontSize: 9, color: MindraColors.textSecondary));
                        },
                      ),
                    ),
                    topTitles: const AxisTitles(
                        sideTitles: SideTitles(showTitles: false)),
                    rightTitles: const AxisTitles(
                        sideTitles: SideTitles(showTitles: false)),
                  ),
                  lineBarsData: [
                    LineChartBarData(
                      spots: [
                        for (int i = 0; i < weekly.length; i++)
                          FlSpot(i.toDouble(),
                              (weekly[i]['avg_anxiety'] as num?)?.toDouble() ?? 0),
                      ],
                      isCurved: true,
                      color: MindraColors.blue,
                      barWidth: 2.5,
                      dotData: const FlDotData(show: true),
                      belowBarData: BarAreaData(
                        show: true,
                        color: MindraColors.blue.withValues(alpha: 0.12),
                      ),
                    ),
                  ],
                ),
              ),
            ),
          if (emotions.isNotEmpty) ...[
            const SizedBox(height: 28),
            const Text('Emociones detectadas (últimos 30 días)',
                style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
            const SizedBox(height: 12),
            ...emotions.map((e) {
              final label = e['emotion_label'] as String? ?? '';
              final total = (e['total'] as num?)?.toInt() ?? 0;
              final maxCount =
                  (emotions.first['total'] as num?)?.toInt() ?? 1;
              final pct = total / maxCount;
              return Padding(
                padding: const EdgeInsets.only(bottom: 10),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text(label,
                            style: const TextStyle(
                                fontSize: 13, fontWeight: FontWeight.w500)),
                        Text('$total sesiones',
                            style: const TextStyle(
                                fontSize: 12,
                                color: MindraColors.textSecondary)),
                      ],
                    ),
                    const SizedBox(height: 4),
                    ClipRRect(
                      borderRadius: BorderRadius.circular(4),
                      child: LinearProgressIndicator(
                        value: pct,
                        minHeight: 8,
                        backgroundColor: Theme.of(context).dividerColor,
                        color: MindraColors.blue,
                      ),
                    ),
                  ],
                ),
              );
            }),
          ],
        ],
      ),
    );
  }
}

// ─── UPGRADE WALL ─────────────────────────────────────────────────────────────

class _UpgradeWall extends StatelessWidget {
  final String message;
  const _UpgradeWall({
    this.message = 'El calendario de bienestar está disponible en los planes Pro y Plus.',
  });

  @override
  Widget build(BuildContext context) => Center(
        child: Padding(
          padding: const EdgeInsets.all(32),
          child: Column(mainAxisSize: MainAxisSize.min, children: [
            const Icon(Icons.lock_outline, size: 64, color: MindraColors.blue),
            const SizedBox(height: 16),
            Text(
              message,
              textAlign: TextAlign.center,
              style: const TextStyle(fontSize: 16),
            ),
            const SizedBox(height: 24),
            FilledButton(
              onPressed: () => Navigator.push(context,
                  MaterialPageRoute(builder: (_) => const PlansScreen())),
              child: const Text('Ver planes'),
            ),
          ]),
        ),
      );
}

class _ErrorView extends StatelessWidget {
  final String msg;
  final VoidCallback onRetry;
  const _ErrorView(this.msg, this.onRetry);

  @override
  Widget build(BuildContext context) => Center(
        child: Column(mainAxisSize: MainAxisSize.min, children: [
          Text(msg, textAlign: TextAlign.center),
          const SizedBox(height: 16),
          FilledButton(onPressed: onRetry, child: const Text('Reintentar')),
        ]),
      );
}
