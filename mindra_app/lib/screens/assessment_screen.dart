import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../services/api_service.dart';
import '../theme/mindra_theme.dart';
import '../utils/responsive.dart';

// ─── Datos GAD-7 ─────────────────────────────────────────────────────────────
const _kGad7Questions = [
  'Sentirme nervioso/a, ansioso/a o al límite',
  'No poder dejar de preocuparme',
  'Preocuparme demasiado por cosas distintas',
  'Dificultad para relajarme',
  'Estar tan inquieto/a que no puedo quedarme quieto/a',
  'Irritarme o enojarme con facilidad',
  'Sentir miedo como si algo terrible fuera a ocurrir',
];

const _kOptions = ['Nunca', 'Varios días', 'Más de la mitad', 'Casi siempre'];

// ─── Screen ───────────────────────────────────────────────────────────────────
class AssessmentScreen extends StatefulWidget {
  const AssessmentScreen({super.key});

  @override
  State<AssessmentScreen> createState() => _AssessmentScreenState();
}

class _AssessmentScreenState extends State<AssessmentScreen> {
  final List<int?> _answers = List.filled(7, null);
  int _page = 0; // 0..6 = preguntas, 7 = resultado
  bool _loading = false;
  Map<String, dynamic>? _result;
  String? _error;

  bool get _canAdvance => _answers[_page] != null;

  void _next() {
    if (_page < 6) {
      setState(() => _page++);
    } else {
      _submit();
    }
  }

  void _prev() {
    if (_page > 0) setState(() => _page--);
  }

  Future<void> _submit() async {
    setState(() { _loading = true; _error = null; });
    try {
      final api = context.read<ApiService>();
      final res = await api.submitAssessment(
        type: 'gad7',
        answers: _answers.map((a) => a!).toList(),
      );
      setState(() { _result = res; _page = 7; });
    } on ApiException catch (e) {
      setState(() => _error = e.message);
    } finally {
      setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Evaluación GAD-7'),
        bottom: _page < 7
            ? PreferredSize(
                preferredSize: const Size.fromHeight(4),
                child: LinearProgressIndicator(
                  value: (_page + 1) / 7,
                  backgroundColor: MindraColors.darkSurface,
                  color: MindraColors.indigo,
                ),
              )
            : null,
      ),
      body: WebFrame(
        maxWidth: 560,
        child: _page < 7 ? _QuestionPage(
          index: _page,
          totalQuestions: 7,
          question: _kGad7Questions[_page],
          selected: _answers[_page],
          options: _kOptions,
          loading: _loading,
          error: _error,
          canAdvance: _canAdvance,
          onSelect: (v) => setState(() => _answers[_page] = v),
          onNext: _canAdvance ? _next : null,
          onPrev: _page > 0 ? _prev : null,
        ) : _ResultPage(result: _result!),
      ),
    );
  }
}

// ─── Página de pregunta ───────────────────────────────────────────────────────
class _QuestionPage extends StatelessWidget {
  final int index;
  final int totalQuestions;
  final String question;
  final int? selected;
  final List<String> options;
  final bool loading;
  final String? error;
  final bool canAdvance;
  final ValueChanged<int> onSelect;
  final VoidCallback? onNext;
  final VoidCallback? onPrev;

  const _QuestionPage({
    required this.index,
    required this.totalQuestions,
    required this.question,
    required this.selected,
    required this.options,
    required this.loading,
    this.error,
    required this.canAdvance,
    required this.onSelect,
    this.onNext,
    this.onPrev,
  });

  @override
  Widget build(BuildContext context) {
    final isLast = index == totalQuestions - 1;
    return Padding(
      padding: const EdgeInsets.all(24),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Indicador
          Text('Pregunta ${index + 1} de $totalQuestions',
              style: const TextStyle(
                  fontSize: 12, color: MindraColors.textSecondary)),
          const SizedBox(height: 16),

          // Pregunta
          Text(
            'Durante las últimas 2 semanas, ¿con qué frecuencia te has sentido molestado/a por:',
            style: const TextStyle(fontSize: 13, color: MindraColors.textSecondary),
          ),
          const SizedBox(height: 8),
          Text(
            '"$question"',
            style: const TextStyle(
                fontSize: 19, fontWeight: FontWeight.bold, height: 1.4),
          ),
          const SizedBox(height: 28),

          // Opciones
          for (int i = 0; i < options.length; i++) ...[
            _OptionTile(
              label: options[i],
              value: i,
              selected: selected == i,
              onTap: () => onSelect(i),
            ),
            const SizedBox(height: 10),
          ],

          if (error != null) ...[
            const SizedBox(height: 8),
            Text(error!, style: const TextStyle(color: Colors.red, fontSize: 13)),
          ],

          const Spacer(),

          // Navegación
          Row(
            children: [
              if (onPrev != null)
                OutlinedButton.icon(
                  onPressed: onPrev,
                  icon: const Icon(Icons.arrow_back_ios, size: 14),
                  label: const Text('Anterior'),
                ),
              const Spacer(),
              FilledButton.icon(
                onPressed: loading ? null : onNext,
                icon: loading
                    ? const SizedBox(
                        width: 16, height: 16,
                        child: CircularProgressIndicator(
                            strokeWidth: 2, color: Colors.white))
                    : Icon(isLast ? Icons.check : Icons.arrow_forward_ios,
                        size: 16),
                label: Text(loading
                    ? 'Guardando...'
                    : isLast
                        ? 'Ver resultado'
                        : 'Siguiente'),
              ),
            ],
          ),
        ],
      ),
    );
  }
}

class _OptionTile extends StatelessWidget {
  final String label;
  final int value;
  final bool selected;
  final VoidCallback onTap;

  const _OptionTile({
    required this.label,
    required this.value,
    required this.selected,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 180),
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
        decoration: BoxDecoration(
          color: selected
              ? MindraColors.indigo.withValues(alpha: 0.12)
              : MindraColors.darkSurface,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(
            color: selected ? MindraColors.indigo : Colors.transparent,
            width: 2,
          ),
        ),
        child: Row(children: [
          Container(
            width: 22, height: 22,
            decoration: BoxDecoration(
              shape: BoxShape.circle,
              color: selected ? MindraColors.indigo : Colors.transparent,
              border: Border.all(
                  color: selected ? MindraColors.indigo : MindraColors.textSecondary,
                  width: 2),
            ),
            child: selected
                ? const Icon(Icons.check, size: 14, color: Colors.white)
                : null,
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Text(label,
                style: TextStyle(
                    fontSize: 15,
                    fontWeight: selected ? FontWeight.w600 : FontWeight.normal,
                    color: selected ? MindraColors.indigo : null)),
          ),
          Text('$value', style: TextStyle(
              fontSize: 12,
              color: selected ? MindraColors.indigo : MindraColors.textSecondary)),
        ]),
      ),
    );
  }
}

// ─── Página de resultado ──────────────────────────────────────────────────────
class _ResultPage extends StatelessWidget {
  final Map<String, dynamic> result;
  const _ResultPage({required this.result});

  static const _colors = {
    'minimal':           Color(0xFF16a34a),
    'mild':              Color(0xFFca8a04),
    'moderate':          Color(0xFFea580c),
    'moderately_severe': Color(0xFFdc2626),
    'severe':            Color(0xFF991b1b),
  };

  static const _icons = {
    'minimal':  '✅',
    'mild':     '⚠️',
    'moderate': '🔶',
    'moderately_severe': '🔴',
    'severe':   '🆘',
  };

  static const _advice = {
    'minimal':
        'Tu nivel de ansiedad está dentro del rango normal. Sigue practicando técnicas de bienestar para mantenerlo.',
    'mild':
        'Tienes ansiedad leve. Las técnicas de respiración y mindfulness pueden ayudarte. Si persiste, considera hablar con un profesional.',
    'moderate':
        'Tu ansiedad es moderada. Se recomienda practicar regularmente las técnicas de Mindra y consultar con un psicólogo o psiquiatra.',
    'moderately_severe':
        'Tu ansiedad es moderada-grave. Es importante que consultes con un profesional de salud mental pronto.',
    'severe':
        'Tu nivel de ansiedad es grave. Por favor, busca ayuda profesional. Puedes usar el botón SOS de Mindra para recursos de apoyo inmediato.',
  };

  @override
  Widget build(BuildContext context) {
    final severity = result['severity'] as String? ?? 'minimal';
    final score    = result['score'] as int? ?? 0;
    final label    = result['severity_label'] as String? ?? '';
    final color    = _colors[severity] ?? MindraColors.blue;
    final icon     = _icons[severity] ?? '✅';
    final advice   = _advice[severity] ?? '';

    return Padding(
      padding: const EdgeInsets.all(24),
      child: Column(
        children: [
          const SizedBox(height: 24),
          // Círculo de resultado
          Container(
            width: 120, height: 120,
            decoration: BoxDecoration(
              shape: BoxShape.circle,
              color: color.withValues(alpha: 0.12),
              border: Border.all(color: color, width: 3),
            ),
            alignment: Alignment.center,
            child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [
              Text(icon, style: const TextStyle(fontSize: 32)),
              Text('$score/21',
                  style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold,
                      color: color)),
            ]),
          ),
          const SizedBox(height: 20),
          Text(label,
              style: TextStyle(fontSize: 22, fontWeight: FontWeight.bold,
                  color: color)),
          const SizedBox(height: 6),
          const Text('Nivel de ansiedad (GAD-7)',
              style: TextStyle(color: MindraColors.textSecondary)),
          const SizedBox(height: 24),

          // Barra de escala
          _ScaleBar(score: score, color: color),
          const SizedBox(height: 24),

          // Interpretación
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: color.withValues(alpha: 0.07),
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: color.withValues(alpha: 0.25)),
            ),
            child: Text(advice,
                style: const TextStyle(fontSize: 14, height: 1.5)),
          ),
          const SizedBox(height: 16),

          const Text(
            'Esta evaluación no reemplaza un diagnóstico clínico. Mindra utiliza el GAD-7, un instrumento validado internacionalmente.',
            textAlign: TextAlign.center,
            style: TextStyle(fontSize: 11, color: MindraColors.textSecondary),
          ),
          const Spacer(),
          FilledButton.icon(
            onPressed: () => Navigator.pop(context),
            icon: const Icon(Icons.check),
            label: const Text('Listo'),
            style: FilledButton.styleFrom(
                minimumSize: const Size.fromHeight(48)),
          ),
        ],
      ),
    );
  }
}

class _ScaleBar extends StatelessWidget {
  final int score;
  final Color color;
  const _ScaleBar({required this.score, required this.color});

  @override
  Widget build(BuildContext context) {
    return Column(children: [
      ClipRRect(
        borderRadius: BorderRadius.circular(99),
        child: LinearProgressIndicator(
          value: score / 21,
          minHeight: 10,
          backgroundColor: MindraColors.darkSurface,
          color: color,
        ),
      ),
      const SizedBox(height: 6),
      const Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text('0', style: TextStyle(fontSize: 10, color: MindraColors.textSecondary)),
          Text('Mínima  Leve  Moderada  Grave',
              style: TextStyle(fontSize: 10, color: MindraColors.textSecondary)),
          Text('21', style: TextStyle(fontSize: 10, color: MindraColors.textSecondary)),
        ],
      ),
    ]);
  }
}
