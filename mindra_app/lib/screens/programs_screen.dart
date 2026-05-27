import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../services/api_service.dart';
import '../theme/mindra_theme.dart';
import '../utils/responsive.dart';

// ─── Modelos locales ──────────────────────────────────────────────────────────
class _Program {
  final String slug;
  final String title;
  final String subtitle;
  final String description;
  final int totalDays;
  final Color color;
  final String emoji;
  final List<Map<String, dynamic>> days;
  bool enrolled;
  int currentDay;
  int progress;
  List<int> completedDays;
  String? completedAt;

  _Program({
    required this.slug,
    required this.title,
    required this.subtitle,
    required this.description,
    required this.totalDays,
    required this.color,
    required this.emoji,
    required this.days,
    this.enrolled = false,
    this.currentDay = 0,
    this.progress = 0,
    List<int>? completedDays,
    this.completedAt,
  }) : completedDays = completedDays ?? [];

  factory _Program.fromJson(Map<String, dynamic> j) {
    final colorHex = (j['color'] as String? ?? '#4f46e5').replaceFirst('#', '');
    final color = Color(int.parse('FF$colorHex', radix: 16));
    return _Program(
      slug:         j['slug'] as String,
      title:        j['title'] as String,
      subtitle:     j['subtitle'] as String? ?? '',
      description:  j['description'] as String? ?? '',
      totalDays:    j['total_days'] as int? ?? 14,
      color:        color,
      emoji:        j['emoji'] as String? ?? '📚',
      days:         List<Map<String, dynamic>>.from(j['days'] as List? ?? []),
      enrolled:     j['enrolled'] as bool? ?? false,
      currentDay:   j['current_day'] as int? ?? 0,
      progress:     j['progress'] as int? ?? 0,
      completedDays: List<int>.from(j['completed_days'] as List? ?? []),
      completedAt:  j['completed_at'] as String?,
    );
  }
}

// ─── Main Screen ──────────────────────────────────────────────────────────────
class ProgramsScreen extends StatefulWidget {
  const ProgramsScreen({super.key});

  @override
  State<ProgramsScreen> createState() => _ProgramsScreenState();
}

class _ProgramsScreenState extends State<ProgramsScreen> {
  List<_Program>? _programs;
  bool _loading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() { _loading = true; _error = null; });
    try {
      final data = await context.read<ApiService>().getPrograms();
      setState(() {
        _programs = data.map(_Program.fromJson).toList();
      });
    } on ApiException catch (e) {
      setState(() => _error = e.message);
    } finally {
      setState(() => _loading = false);
    }
  }

  Future<void> _enroll(_Program program) async {
    try {
      await context.read<ApiService>().enrollProgram(program.slug);
      await _load();
    } on ApiException catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(content: Text(e.message)));
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Programas'),
        actions: [
          IconButton(
              icon: const Icon(Icons.refresh),
              onPressed: _load),
        ],
      ),
      body: WebFrame(
        child: _loading
            ? const Center(child: CircularProgressIndicator())
            : _error != null
                ? _ErrorView(message: _error!, onRetry: _load)
                : RefreshIndicator(
                    onRefresh: _load,
                    child: ListView(
                      padding: const EdgeInsets.fromLTRB(16, 12, 16, 40),
                      children: [
                        // Header
                        const Text('Programas estructurados',
                            style: TextStyle(
                                fontSize: 22, fontWeight: FontWeight.bold)),
                        const SizedBox(height: 4),
                        const Text(
                          'Rutas guiadas de 7–14 días con técnicas y ejercicios diarios.',
                          style: TextStyle(
                              color: MindraColors.textSecondary, fontSize: 13),
                        ),
                        const SizedBox(height: 20),
                        for (final p in _programs ?? [])
                          _ProgramCard(
                            program: p,
                            onEnroll: () => _enroll(p),
                            onOpen: () => Navigator.push(
                              context,
                              MaterialPageRoute(
                                  builder: (_) => _ProgramDetail(
                                      program: p, onReload: _load)),
                            ),
                          ),
                      ],
                    ),
                  ),
      ),
    );
  }
}

// ─── Tarjeta de programa ──────────────────────────────────────────────────────
class _ProgramCard extends StatelessWidget {
  final _Program program;
  final VoidCallback onEnroll;
  final VoidCallback onOpen;
  const _ProgramCard({
    required this.program,
    required this.onEnroll,
    required this.onOpen,
  });

  @override
  Widget build(BuildContext context) {
    final p = program;
    final isCompleted = p.completedAt != null;

    return Container(
      margin: const EdgeInsets.only(bottom: 14),
      decoration: BoxDecoration(
        color: MindraColors.darkSurface,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: p.color.withValues(alpha: 0.25), width: 1),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Banner
          Container(
            height: 90,
            decoration: BoxDecoration(
              gradient: LinearGradient(
                colors: [p.color, p.color.withValues(alpha: 0.5)],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
              borderRadius: const BorderRadius.vertical(top: Radius.circular(15)),
            ),
            padding: const EdgeInsets.all(16),
            child: Row(children: [
              Text(p.emoji, style: const TextStyle(fontSize: 36)),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                  Text(p.title,
                      style: const TextStyle(
                          color: Colors.white,
                          fontSize: 17,
                          fontWeight: FontWeight.bold)),
                  Text(p.subtitle,
                      style: TextStyle(
                          color: Colors.white.withValues(alpha: 0.8),
                          fontSize: 12)),
                ]),
              ),
              if (isCompleted)
                Container(
                  padding: const EdgeInsets.symmetric(
                      horizontal: 10, vertical: 4),
                  decoration: BoxDecoration(
                    color: Colors.white.withValues(alpha: 0.2),
                    borderRadius: BorderRadius.circular(99),
                  ),
                  child: const Text('✅ Completado',
                      style: TextStyle(
                          color: Colors.white,
                          fontSize: 11,
                          fontWeight: FontWeight.w600)),
                ),
            ]),
          ),

          Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
              Text(p.description,
                  style: const TextStyle(
                      fontSize: 13,
                      color: MindraColors.textSecondary,
                      height: 1.4),
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis),
              const SizedBox(height: 12),

              // Progreso
              if (p.enrolled) ...[
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text('Día ${p.currentDay} de ${p.totalDays}',
                        style: const TextStyle(
                            fontSize: 12,
                            color: MindraColors.textSecondary)),
                    Text('${p.progress}%',
                        style: TextStyle(
                            fontSize: 12,
                            fontWeight: FontWeight.w600,
                            color: p.color)),
                  ],
                ),
                const SizedBox(height: 6),
                ClipRRect(
                  borderRadius: BorderRadius.circular(99),
                  child: LinearProgressIndicator(
                    value: p.progress / 100,
                    minHeight: 6,
                    backgroundColor: p.color.withValues(alpha: 0.15),
                    color: p.color,
                  ),
                ),
                const SizedBox(height: 12),
              ],

              // Duración
              Row(children: [
                Icon(Icons.schedule_outlined,
                    size: 14, color: MindraColors.textSecondary),
                const SizedBox(width: 4),
                Text('${p.totalDays} días · ~10–20 min/día',
                    style: const TextStyle(
                        fontSize: 12, color: MindraColors.textSecondary)),
              ]),
              const SizedBox(height: 12),

              // Botón
              if (!p.enrolled)
                FilledButton.icon(
                  onPressed: onEnroll,
                  icon: const Icon(Icons.play_arrow, size: 16),
                  label: const Text('Comenzar programa'),
                  style: FilledButton.styleFrom(
                      backgroundColor: p.color,
                      minimumSize: const Size.fromHeight(40)),
                )
              else
                OutlinedButton.icon(
                  onPressed: onOpen,
                  icon: const Icon(Icons.arrow_forward, size: 16),
                  label: Text(isCompleted ? 'Ver programa' : 'Continuar'),
                  style: OutlinedButton.styleFrom(
                      foregroundColor: p.color,
                      side: BorderSide(color: p.color),
                      minimumSize: const Size.fromHeight(40)),
                ),
            ]),
          ),
        ],
      ),
    );
  }
}

// ─── Detalle del programa ─────────────────────────────────────────────────────
class _ProgramDetail extends StatefulWidget {
  final _Program program;
  final VoidCallback onReload;
  const _ProgramDetail({required this.program, required this.onReload});

  @override
  State<_ProgramDetail> createState() => _ProgramDetailState();
}

class _ProgramDetailState extends State<_ProgramDetail> {
  bool _completing = false;

  Future<void> _completeDay(int day) async {
    setState(() => _completing = true);
    try {
      await context.read<ApiService>().completeProgramDay(
          widget.program.slug, day);
      widget.onReload();
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('✅ Día $day completado. ¡Sigue así!'),
            backgroundColor: widget.program.color,
          ),
        );
        Navigator.pop(context);
      }
    } on ApiException catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(content: Text(e.message)));
      }
    } finally {
      if (mounted) setState(() => _completing = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final p = widget.program;
    final nextDay = p.currentDay + 1;

    return Scaffold(
      appBar: AppBar(title: Text(p.title)),
      body: WebFrame(
        child: ListView(
          padding: const EdgeInsets.fromLTRB(16, 16, 16, 40),
          children: [
            // Header card
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  colors: [p.color, p.color.withValues(alpha: 0.6)],
                ),
                borderRadius: BorderRadius.circular(14),
              ),
              child: Row(children: [
                Text(p.emoji, style: const TextStyle(fontSize: 40)),
                const SizedBox(width: 14),
                Expanded(
                  child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                    Text(p.subtitle,
                        style: TextStyle(
                            color: Colors.white.withValues(alpha: 0.9),
                            fontSize: 12)),
                    const SizedBox(height: 2),
                    Text('${p.progress}% completado',
                        style: const TextStyle(
                            color: Colors.white,
                            fontSize: 16,
                            fontWeight: FontWeight.bold)),
                    const SizedBox(height: 8),
                    ClipRRect(
                      borderRadius: BorderRadius.circular(99),
                      child: LinearProgressIndicator(
                        value: p.progress / 100,
                        minHeight: 6,
                        backgroundColor: Colors.white.withValues(alpha: 0.2),
                        color: Colors.white,
                      ),
                    ),
                  ]),
                ),
              ]),
            ),
            const SizedBox(height: 20),

            const Text('Sesiones del programa',
                style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
            const SizedBox(height: 12),

            for (final day in p.days) ...[
              _DayTile(
                day: day,
                isDone: p.completedDays.contains(day['day'] as int),
                isNext: (day['day'] as int) == nextDay,
                color: p.color,
                completing: _completing,
                onComplete: () => _completeDay(day['day'] as int),
              ),
              const SizedBox(height: 8),
            ],
          ],
        ),
      ),
    );
  }
}

class _DayTile extends StatelessWidget {
  final Map<String, dynamic> day;
  final bool isDone;
  final bool isNext;
  final Color color;
  final bool completing;
  final VoidCallback onComplete;
  const _DayTile({
    required this.day,
    required this.isDone,
    required this.isNext,
    required this.color,
    required this.completing,
    required this.onComplete,
  });

  static const _typeIcons = {
    'breathing': '🌬️',
    'tcc':       '💭',
    'grounding': '🌿',
    'pmr':       '💪',
    'mindful':   '🧘',
    'journal':   '📓',
    'body_scan': '🫁',
    'visual':    '✨',
    'review':    '📊',
    'info':      '📖',
  };

  @override
  Widget build(BuildContext context) {
    final dayNum  = day['day'] as int;
    final title   = day['title'] as String;
    final type    = day['type'] as String? ?? 'info';
    final duration = day['duration'] as int? ?? 10;
    final typeEmoji = _typeIcons[type] ?? '📖';

    final locked = !isDone && !isNext && dayNum > 1;

    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: isDone
            ? color.withValues(alpha: 0.08)
            : isNext
                ? MindraColors.darkSurface
                : MindraColors.dark,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(
          color: isDone
              ? color.withValues(alpha: 0.3)
              : isNext
                  ? color
                  : Colors.transparent,
          width: isNext ? 2 : 1,
        ),
      ),
      child: Row(children: [
        // Número
        Container(
          width: 36, height: 36,
          decoration: BoxDecoration(
            shape: BoxShape.circle,
            color: isDone
                ? color
                : isNext
                    ? color.withValues(alpha: 0.15)
                    : MindraColors.darkSurface,
          ),
          alignment: Alignment.center,
          child: isDone
              ? const Icon(Icons.check, size: 18, color: Colors.white)
              : locked
                  ? Icon(Icons.lock_outline, size: 16,
                      color: MindraColors.textSecondary)
                  : Text('$dayNum',
                      style: TextStyle(
                          fontSize: 13,
                          fontWeight: FontWeight.bold,
                          color: isNext ? color : MindraColors.textSecondary)),
        ),
        const SizedBox(width: 12),
        Expanded(
          child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Text(title,
                style: TextStyle(
                    fontSize: 14,
                    fontWeight: isNext ? FontWeight.w600 : FontWeight.normal,
                    color: locked ? MindraColors.textSecondary : null)),
            Text('$typeEmoji $type · $duration min',
                style: const TextStyle(
                    fontSize: 11, color: MindraColors.textSecondary)),
          ]),
        ),
        if (isNext)
          FilledButton(
            onPressed: completing ? null : onComplete,
            style: FilledButton.styleFrom(
                backgroundColor: color,
                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                textStyle: const TextStyle(fontSize: 12)),
            child: completing
                ? const SizedBox(
                    width: 14, height: 14,
                    child: CircularProgressIndicator(
                        strokeWidth: 2, color: Colors.white))
                : const Text('Completar'),
          ),
        if (isDone)
          Text('✅', style: TextStyle(fontSize: 18, color: color)),
      ]),
    );
  }
}

class _ErrorView extends StatelessWidget {
  final String message;
  final VoidCallback onRetry;
  const _ErrorView({required this.message, required this.onRetry});

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Column(mainAxisSize: MainAxisSize.min, children: [
        const Icon(Icons.error_outline, size: 48, color: Colors.red),
        const SizedBox(height: 12),
        Text(message, style: const TextStyle(color: Colors.red)),
        const SizedBox(height: 16),
        OutlinedButton(onPressed: onRetry, child: const Text('Reintentar')),
      ]),
    );
  }
}
