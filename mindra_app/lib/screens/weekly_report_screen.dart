import 'package:fl_chart/fl_chart.dart';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../services/api_service.dart';
import '../theme/mindra_theme.dart';
import '../utils/responsive.dart';

class WeeklyReportScreen extends StatefulWidget {
  const WeeklyReportScreen({super.key});

  @override
  State<WeeklyReportScreen> createState() => _WeeklyReportScreenState();
}

class _WeeklyReportScreenState extends State<WeeklyReportScreen> {
  bool _loading = true;
  Map<String, dynamic>? _data;
  String? _error;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() { _loading = true; _error = null; });
    try {
      final api = context.read<ApiService>();
      final res = await api.getWeeklyReport();
      if (mounted) setState(() { _data = res; _loading = false; });
    } on ApiException catch (e) {
      if (mounted) setState(() { _error = e.message; _loading = false; });
    } catch (e) {
      if (mounted) setState(() { _error = 'No se pudo cargar el reporte'; _loading = false; });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Reporte semanal'),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _load,
            tooltip: 'Actualizar',
          ),
        ],
      ),
      body: WebFrame(
        maxWidth: 680,
        child: _loading
            ? const Center(child: CircularProgressIndicator())
            : _error != null
                ? _ErrorView(_error!, _load)
                : _ReportBody(data: _data!),
      ),
    );
  }
}

// ─── Cuerpo del reporte ───────────────────────────────────────────────────────
class _ReportBody extends StatelessWidget {
  final Map<String, dynamic> data;
  const _ReportBody({required this.data});

  @override
  Widget build(BuildContext context) {
    final sessions   = data['sessions'] as int? ?? 0;
    final activeDays = data['active_days'] as int? ?? 0;
    final avgProb    = (data['avg_probability'] as num?)?.toDouble();
    final trend      = data['trend'] as String? ?? 'unknown';
    final weekLabel  = data['week_label'] as String? ?? '';
    final dailyData  = List<Map<String, dynamic>>.from(
        (data['daily'] as List?) ?? []);
    final insights   = List<Map<String, dynamic>>.from(
        (data['insights'] as List?) ?? []);

    return ListView(
      padding: const EdgeInsets.fromLTRB(16, 16, 16, 40),
      children: [

        // ── Encabezado ──────────────────────────────────────────────────────
        if (weekLabel.isNotEmpty)
          Padding(
            padding: const EdgeInsets.only(bottom: 16),
            child: Row(children: [
              const Icon(Icons.calendar_month, size: 16, color: MindraColors.textSecondary),
              const SizedBox(width: 6),
              Text(weekLabel,
                  style: const TextStyle(color: MindraColors.textSecondary, fontSize: 13)),
            ]),
          ),

        // ── Stats ───────────────────────────────────────────────────────────
        Row(children: [
          Expanded(child: _StatCard(
            value: '$sessions',
            label: 'Sesiones',
            icon: Icons.chat_bubble_outline,
            color: MindraColors.blue,
          )),
          const SizedBox(width: 10),
          Expanded(child: _StatCard(
            value: '$activeDays',
            label: 'Días activo',
            icon: Icons.today_outlined,
            color: MindraColors.violet,
          )),
          const SizedBox(width: 10),
          Expanded(child: _StatCard(
            value: avgProb != null ? '${(avgProb * 100).round()}%' : '—',
            label: 'Ansiedad prom.',
            icon: Icons.show_chart,
            color: _trendColor(trend),
          )),
        ]),

        const SizedBox(height: 20),

        // ── Tendencia ───────────────────────────────────────────────────────
        _TrendBanner(trend: trend),

        const SizedBox(height: 20),

        // ── Actividad diaria ────────────────────────────────────────────────
        if (dailyData.isNotEmpty) ...[
          const _SectionTitle('Actividad diaria'),
          const SizedBox(height: 10),
          _DailyChart(daily: dailyData),
          const SizedBox(height: 20),
        ],

        // ── Sin sesiones ────────────────────────────────────────────────────
        if (sessions == 0) ...[
          Container(
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              color: MindraColors.violet.withValues(alpha: 0.08),
              borderRadius: BorderRadius.circular(16),
              border: Border.all(color: MindraColors.violet.withValues(alpha: 0.25)),
            ),
            child: Column(children: [
              const Text('😴', style: TextStyle(fontSize: 40)),
              const SizedBox(height: 10),
              const Text('Sin sesiones esta semana',
                  style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
              const SizedBox(height: 6),
              const Text('Solo 5 minutos al día pueden marcar la diferencia.',
                  textAlign: TextAlign.center,
                  style: TextStyle(color: MindraColors.textSecondary)),
            ]),
          ),
          const SizedBox(height: 20),
        ],

        // ── Insights ────────────────────────────────────────────────────────
        if (insights.isNotEmpty) ...[
          const _SectionTitle('Observaciones'),
          const SizedBox(height: 10),
          for (final insight in insights)
            _InsightRow(insight: insight),
          const SizedBox(height: 16),
        ],

        // ── Nota ────────────────────────────────────────────────────────────
        Container(
          padding: const EdgeInsets.all(14),
          decoration: BoxDecoration(
            color: MindraColors.blue.withValues(alpha: 0.07),
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: MindraColors.blue.withValues(alpha: 0.2)),
          ),
          child: const Row(children: [
            Icon(Icons.info_outline, size: 16, color: MindraColors.blue),
            SizedBox(width: 10),
            Expanded(
              child: Text(
                'El reporte semanal se envía automáticamente a tu correo cada lunes.',
                style: TextStyle(fontSize: 12, color: MindraColors.textSecondary, height: 1.5),
              ),
            ),
          ]),
        ),
      ],
    );
  }

  static Color _trendColor(String trend) {
    return switch (trend) {
      'improving' || 'stable_low' => const Color(0xFF16a34a),
      'worsening' || 'high'       => const Color(0xFFef4444),
      _                           => MindraColors.textSecondary,
    };
  }
}

// ─── Widgets de soporte ───────────────────────────────────────────────────────

class _StatCard extends StatelessWidget {
  final String value;
  final String label;
  final IconData icon;
  final Color color;
  const _StatCard({required this.value, required this.label, required this.icon, required this.color});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 14),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.08),
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: color.withValues(alpha: 0.25)),
      ),
      child: Column(children: [
        Icon(icon, size: 20, color: color),
        const SizedBox(height: 6),
        Text(value, style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: color)),
        const SizedBox(height: 2),
        Text(label, style: const TextStyle(fontSize: 10, color: MindraColors.textSecondary),
            textAlign: TextAlign.center),
      ]),
    );
  }
}

class _TrendBanner extends StatelessWidget {
  final String trend;
  const _TrendBanner({required this.trend});

  static const _config = {
    'improving':   (icon: '📈', title: '¡Vas mejorando!', color: Color(0xFF16a34a),
        desc: 'Tus niveles de ansiedad bajaron esta semana. ¡Continúa así!'),
    'worsening':   (icon: '🌊', title: 'Semana más difícil', color: Color(0xFFea580c),
        desc: 'Los indicadores subieron un poco. Prueba una técnica de respiración o el programa de ansiedad.'),
    'high':        (icon: '🔔', title: 'Ansiedad persistente', color: Color(0xFFef4444),
        desc: 'Has tenido niveles altos de forma constante. Considera hablar con un profesional.'),
    'stable_low':  (icon: '🌿', title: 'Niveles estables y bajos', color: Color(0xFF16a34a),
        desc: 'Tu bienestar se mantiene en buenos niveles. ¡La constancia da resultados!'),
    'stable':      (icon: '😌', title: 'Semana estable', color: MindraColors.textSecondary,
        desc: 'Tu bienestar se mantuvo estable esta semana. Pequeñas variaciones son normales.'),
    'unknown':     (icon: '🌱', title: 'Comenzando tu seguimiento', color: MindraColors.blue,
        desc: 'Con más sesiones, Mindra podrá mostrarte tendencias más precisas.'),
  };

  @override
  Widget build(BuildContext context) {
    final c = _config[trend] ?? _config['unknown']!;
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: c.color.withValues(alpha: 0.08),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: c.color.withValues(alpha: 0.3)),
      ),
      child: Row(children: [
        Text(c.icon, style: const TextStyle(fontSize: 28)),
        const SizedBox(width: 14),
        Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Text(c.title, style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: c.color)),
          const SizedBox(height: 3),
          Text(c.desc, style: TextStyle(fontSize: 12, color: c.color.withValues(alpha: 0.75), height: 1.4)),
        ])),
      ]),
    );
  }
}

class _DailyChart extends StatefulWidget {
  final List<Map<String, dynamic>> daily;
  const _DailyChart({required this.daily});

  @override
  State<_DailyChart> createState() => _DailyChartState();
}

class _DailyChartState extends State<_DailyChart> {
  int? _touchedIndex;

  static Color _barColor(double prob) {
    if (prob > 0.60) return const Color(0xFFef4444);
    if (prob > 0.40) return const Color(0xFFf97316);
    return const Color(0xFF22c55e);
  }

  @override
  Widget build(BuildContext context) {
    final data = widget.daily;
    if (data.isEmpty) return const SizedBox.shrink();

    final bars = data.asMap().entries.map((e) {
      final i    = e.key;
      final d    = e.value;
      final prob = (d['avg_anxiety'] as num?)?.toDouble() ?? 0.0;
      final col  = _barColor(prob);
      final touched = _touchedIndex == i;

      return BarChartGroupData(
        x: i,
        barRods: [
          BarChartRodData(
            toY: (prob * 100).roundToDouble(),
            color: touched ? col : col.withValues(alpha: 0.75),
            width: touched ? 18 : 14,
            borderRadius: const BorderRadius.vertical(top: Radius.circular(6)),
            backDrawRodData: BackgroundBarChartRodData(
              show: true,
              toY: 100,
              color: col.withValues(alpha: 0.07),
            ),
          ),
        ],
      );
    }).toList();

    return Container(
      padding: const EdgeInsets.fromLTRB(8, 16, 8, 8),
      decoration: BoxDecoration(
        color: Theme.of(context).colorScheme.surface,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: Theme.of(context).dividerColor),
      ),
      child: SizedBox(
        height: 180,
        child: BarChart(
          BarChartData(
            maxY: 100,
            barTouchData: BarTouchData(
              touchTooltipData: BarTouchTooltipData(
                getTooltipColor: (_) => MindraColors.darkSurface,
                getTooltipItem: (group, _, rod, __) {
                  final label = (data[group.x]['day_label'] as String?) ?? '';
                  return BarTooltipItem(
                    '$label\n${rod.toY.round()}%',
                    const TextStyle(
                        color: Colors.white,
                        fontSize: 12,
                        fontWeight: FontWeight.w600),
                  );
                },
              ),
              touchCallback: (event, response) {
                setState(() {
                  _touchedIndex = event is FlTapUpEvent
                      ? null
                      : response?.spot?.touchedBarGroupIndex;
                });
              },
            ),
            titlesData: FlTitlesData(
              bottomTitles: AxisTitles(
                sideTitles: SideTitles(
                  showTitles: true,
                  getTitlesWidget: (value, _) {
                    final i = value.toInt();
                    if (i < 0 || i >= data.length) return const SizedBox.shrink();
                    return Padding(
                      padding: const EdgeInsets.only(top: 6),
                      child: Text(
                        (data[i]['day_label'] as String?) ?? '',
                        style: const TextStyle(
                            fontSize: 11, color: MindraColors.textSecondary),
                      ),
                    );
                  },
                ),
              ),
              leftTitles: AxisTitles(
                sideTitles: SideTitles(
                  showTitles: true,
                  reservedSize: 32,
                  interval: 25,
                  getTitlesWidget: (value, _) => Text(
                    '${value.toInt()}%',
                    style: const TextStyle(
                        fontSize: 9, color: MindraColors.textSecondary),
                  ),
                ),
              ),
              topTitles:   const AxisTitles(sideTitles: SideTitles(showTitles: false)),
              rightTitles: const AxisTitles(sideTitles: SideTitles(showTitles: false)),
            ),
            gridData: FlGridData(
              show: true,
              horizontalInterval: 25,
              getDrawingHorizontalLine: (_) => FlLine(
                color: Theme.of(context).dividerColor,
                strokeWidth: 0.8,
              ),
              drawVerticalLine: false,
            ),
            borderData: FlBorderData(show: false),
            barGroups: bars,
          ),
        ),
      ),
    );
  }
}

class _InsightRow extends StatelessWidget {
  final Map<String, dynamic> insight;
  const _InsightRow({required this.insight});

  @override
  Widget build(BuildContext context) {
    final text  = insight['text'] as String? ?? '';
    final color = _parseColor(insight['color'] as String?);
    return Container(
      margin: const EdgeInsets.only(bottom: 8),
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.07),
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: color.withValues(alpha: 0.22)),
      ),
      child: Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
        Container(
          width: 8, height: 8, margin: const EdgeInsets.only(top: 5),
          decoration: BoxDecoration(color: color, shape: BoxShape.circle),
        ),
        const SizedBox(width: 10),
        Expanded(child: Text(text, style: const TextStyle(fontSize: 13, height: 1.4))),
      ]),
    );
  }

  static Color _parseColor(String? hex) {
    if (hex == null || hex.isEmpty) return MindraColors.blue;
    try {
      final clean = hex.replaceFirst('#', '');
      return Color(int.parse('FF$clean', radix: 16));
    } catch (_) {
      return MindraColors.blue;
    }
  }
}

class _SectionTitle extends StatelessWidget {
  final String title;
  const _SectionTitle(this.title);

  @override
  Widget build(BuildContext context) => Text(title,
      style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w700,
          color: MindraColors.textSecondary, letterSpacing: 0.5));
}

class _ErrorView extends StatelessWidget {
  final String message;
  final VoidCallback onRetry;
  const _ErrorView(this.message, this.onRetry);

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Column(mainAxisSize: MainAxisSize.min, children: [
          const Icon(Icons.error_outline, size: 48, color: MindraColors.error),
          const SizedBox(height: 12),
          Text(message, textAlign: TextAlign.center,
              style: const TextStyle(color: MindraColors.textSecondary)),
          const SizedBox(height: 16),
          FilledButton.icon(
            onPressed: onRetry,
            icon: const Icon(Icons.refresh),
            label: const Text('Reintentar'),
          ),
        ]),
      ),
    );
  }
}
