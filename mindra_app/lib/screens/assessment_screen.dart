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

// ─── Datos PHQ-9 ─────────────────────────────────────────────────────────────
const _kPhq9Questions = [
  'Poco interés o placer al hacer las cosas',
  'Sentirme desanimado/a, deprimido/a o sin esperanzas',
  'Problemas para dormir, mantenerse dormido/a, o dormir demasiado',
  'Sentirme cansado/a o tener poca energía',
  'Tener poco apetito o comer en exceso',
  'Sentirme mal conmigo mismo/a, o sentir que soy un fracaso',
  'Dificultad para concentrarme (leer, ver televisión, etc.)',
  'Moverme o hablar tan lento que otros lo notan (o lo contrario)',
  'Pensamientos de que estaría mejor muerto/a o de hacerme daño',
];

const _kOptions = ['Nunca', 'Varios días', 'Más de la mitad', 'Casi siempre'];

// ─── Colores de severidad ─────────────────────────────────────────────────────
const _kSeverityColors = {
  'minimal':           Color(0xFF16a34a),
  'mild':              Color(0xFFca8a04),
  'moderate':          Color(0xFFea580c),
  'moderately_severe': Color(0xFFdc2626),
  'severe':            Color(0xFF991b1b),
};
const _kSeverityIcons = {
  'minimal':           '✅',
  'mild':              '⚠️',
  'moderate':          '🔶',
  'moderately_severe': '🔴',
  'severe':            '🆘',
};

// ─── Textos de consejo ────────────────────────────────────────────────────────
const _kAdviceGad7 = {
  'minimal':  'Tu nivel de ansiedad está dentro del rango normal. Sigue practicando técnicas de bienestar.',
  'mild':     'Tienes ansiedad leve. La respiración consciente y el mindfulness pueden ayudarte.',
  'moderate': 'Tu ansiedad es moderada. Se recomienda consultar con un psicólogo o psiquiatra.',
  'moderately_severe': 'Tu ansiedad es moderada-grave. Es importante que busques apoyo profesional pronto.',
  'severe':   'Tu nivel de ansiedad es grave. Por favor busca ayuda profesional. Usa el botón SOS para recursos de apoyo.',
};
const _kAdvicePhq9 = {
  'minimal':  'Tu estado de ánimo está en niveles normales. ¡Continúa con tus hábitos de bienestar!',
  'mild':     'Hay algunos síntomas leves de ánimo bajo. El diario emocional y las técnicas de Mindra pueden ayudarte.',
  'moderate': 'Los síntomas son moderados. Hablar con un profesional de salud mental puede marcarte la diferencia.',
  'moderately_severe': 'Los síntomas son significativos. Te recomendamos buscar apoyo profesional.',
  'severe':   'Los síntomas son graves. Por favor busca atención profesional cuanto antes.',
};

// ─── Screen principal ─────────────────────────────────────────────────────────
class AssessmentScreen extends StatefulWidget {
  const AssessmentScreen({super.key});

  @override
  State<AssessmentScreen> createState() => _AssessmentScreenState();
}

class _AssessmentScreenState extends State<AssessmentScreen>
    with SingleTickerProviderStateMixin {
  late final TabController _tabCtrl;

  // Estado del formulario
  String _type = 'gad7';
  List<int?> _answers = List.filled(7, null);
  int _page = 0;
  bool _loading = false;
  Map<String, dynamic>? _result;
  String? _error;

  // Historial
  List<Map<String, dynamic>> _history = [];
  bool _histLoading = false;

  @override
  void initState() {
    super.initState();
    _tabCtrl = TabController(length: 2, vsync: this);
    _tabCtrl.addListener(_onTabChanged);
    _loadHistory();
  }

  @override
  void dispose() {
    _tabCtrl.removeListener(_onTabChanged);
    _tabCtrl.dispose();
    super.dispose();
  }

  void _onTabChanged() {
    if (!_tabCtrl.indexIsChanging) {
      final newType = _tabCtrl.index == 0 ? 'gad7' : 'phq9';
      if (newType != _type) {
        setState(() {
          _type = newType;
          _answers = List.filled(_questions.length, null);
          _page = 0;
          _result = null;
          _error = null;
        });
      }
    }
  }

  List<String> get _questions =>
      _type == 'gad7' ? _kGad7Questions : _kPhq9Questions;

  int get _maxScore => _type == 'gad7' ? 21 : 27;

  bool get _canAdvance => _answers[_page] != null;
  bool get _isLastQuestion => _page == _questions.length - 1;

  void _next() {
    if (_isLastQuestion) {
      _submit();
    } else {
      setState(() => _page++);
    }
  }

  void _prev() {
    if (_page > 0) setState(() => _page--);
  }

  void _resetForm() {
    setState(() {
      _answers = List.filled(_questions.length, null);
      _page = 0;
      _result = null;
      _error = null;
    });
  }

  Future<void> _submit() async {
    setState(() { _loading = true; _error = null; });
    try {
      final api = context.read<ApiService>();
      final res = await api.submitAssessment(
        type: _type,
        answers: _answers.map((a) => a!).toList(),
      );
      setState(() { _result = res; });
      _loadHistory(); // Recargar historial después de guardar
    } on ApiException catch (e) {
      setState(() => _error = e.message);
    } finally {
      setState(() => _loading = false);
    }
  }

  Future<void> _loadHistory() async {
    setState(() => _histLoading = true);
    try {
      final api = context.read<ApiService>();
      final list = await api.getAssessmentHistory();
      if (mounted) setState(() => _history = list);
    } catch (_) {
      // silencioso si falla
    } finally {
      if (mounted) setState(() => _histLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final totalQ = _questions.length;
    final showResult = _result != null && !_loading;

    return Scaffold(
      appBar: AppBar(
        title: const Text('Evaluaciones'),
        bottom: TabBar(
          controller: _tabCtrl,
          tabs: const [
            Tab(text: 'GAD-7  (Ansiedad)'),
            Tab(text: 'PHQ-9  (Estado de ánimo)'),
          ],
          labelColor: MindraColors.blue,
          unselectedLabelColor: MindraColors.textSecondary,
          indicatorColor: MindraColors.blue,
        ),
      ),
      body: TabBarView(
        controller: _tabCtrl,
        physics: showResult ? const NeverScrollableScrollPhysics() : null,
        children: [
          _buildTabBody(totalQ, showResult),
          _buildTabBody(totalQ, showResult),
        ],
      ),
    );
  }

  Widget _buildTabBody(int totalQ, bool showResult) {
    return WebFrame(
      maxWidth: 600,
      child: Column(
        children: [
          // Barra de progreso
          if (!showResult)
            LinearProgressIndicator(
              value: (_page + 1) / totalQ,
              backgroundColor: MindraColors.darkSurface,
              color: _type == 'gad7' ? MindraColors.indigo : MindraColors.violet,
              minHeight: 3,
            ),
          Expanded(
            child: showResult
                ? _ResultPage(
                    result: _result!,
                    maxScore: _maxScore,
                    type: _type,
                    onReset: _resetForm,
                  )
                : _buildForm(totalQ),
          ),
          // Historial al fondo (solo visible cuando no se muestra resultado)
          if (!showResult && _history.isNotEmpty) _HistoryPanel(history: _history, loading: _histLoading),
        ],
      ),
    );
  }

  Widget _buildForm(int totalQ) {
    return Padding(
      padding: const EdgeInsets.all(24),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text('Pregunta ${_page + 1} de $totalQ',
              style: const TextStyle(fontSize: 12, color: MindraColors.textSecondary)),
          const SizedBox(height: 16),
          const Text(
            'Durante las últimas 2 semanas, ¿con qué frecuencia te has sentido molestado/a por:',
            style: TextStyle(fontSize: 13, color: MindraColors.textSecondary),
          ),
          const SizedBox(height: 8),
          Text(
            '"${_questions[_page]}"',
            style: const TextStyle(fontSize: 19, fontWeight: FontWeight.bold, height: 1.4),
          ),
          // Alerta especial para última pregunta del PHQ-9 (suicidio)
          if (_type == 'phq9' && _page == 8) ...[
            const SizedBox(height: 10),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
              decoration: BoxDecoration(
                color: Colors.orange.withValues(alpha: 0.1),
                borderRadius: BorderRadius.circular(10),
                border: Border.all(color: Colors.orange.withValues(alpha: 0.4)),
              ),
              child: const Row(children: [
                Icon(Icons.info_outline, color: Colors.orange, size: 16),
                SizedBox(width: 8),
                Expanded(
                  child: Text(
                    'Si tienes estos pensamientos con frecuencia, por favor comunícate con un profesional de salud mental o llama a la Línea de la Vida: 800 911 2000.',
                    style: TextStyle(fontSize: 11, color: Colors.orange, height: 1.4),
                  ),
                ),
              ]),
            ),
          ],
          const SizedBox(height: 28),
          for (int i = 0; i < _kOptions.length; i++) ...[
            _OptionTile(
              label: _kOptions[i],
              value: i,
              selected: _answers[_page] == i,
              color: _type == 'gad7' ? MindraColors.indigo : MindraColors.violet,
              onTap: () => setState(() => _answers[_page] = i),
            ),
            const SizedBox(height: 10),
          ],
          if (_error != null) ...[
            const SizedBox(height: 8),
            Text(_error!, style: const TextStyle(color: Colors.red, fontSize: 13)),
          ],
          const Spacer(),
          Row(children: [
            if (_page > 0)
              OutlinedButton.icon(
                onPressed: _prev,
                icon: const Icon(Icons.arrow_back_ios, size: 14),
                label: const Text('Anterior'),
              ),
            const Spacer(),
            FilledButton.icon(
              onPressed: _loading ? null : (_canAdvance ? _next : null),
              icon: _loading
                  ? const SizedBox(width: 16, height: 16,
                      child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                  : Icon(_isLastQuestion ? Icons.check : Icons.arrow_forward_ios, size: 16),
              label: Text(_loading ? 'Guardando...' : _isLastQuestion ? 'Ver resultado' : 'Siguiente'),
              style: FilledButton.styleFrom(
                backgroundColor: _type == 'gad7' ? MindraColors.indigo : MindraColors.violet,
              ),
            ),
          ]),
        ],
      ),
    );
  }
}

// ─── Opción de respuesta ──────────────────────────────────────────────────────
class _OptionTile extends StatelessWidget {
  final String label;
  final int value;
  final bool selected;
  final Color color;
  final VoidCallback onTap;

  const _OptionTile({
    required this.label, required this.value,
    required this.selected, required this.color, required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 180),
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
        decoration: BoxDecoration(
          color: selected ? color.withValues(alpha: 0.12) : Theme.of(context).colorScheme.surface,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: selected ? color : Colors.transparent, width: 2),
        ),
        child: Row(children: [
          Container(
            width: 22, height: 22,
            decoration: BoxDecoration(
              shape: BoxShape.circle,
              color: selected ? color : Colors.transparent,
              border: Border.all(color: selected ? color : MindraColors.textSecondary, width: 2),
            ),
            child: selected ? const Icon(Icons.check, size: 14, color: Colors.white) : null,
          ),
          const SizedBox(width: 14),
          Expanded(child: Text(label, style: TextStyle(
              fontSize: 15,
              fontWeight: selected ? FontWeight.w600 : FontWeight.normal,
              color: selected ? color : null))),
          Text('$value', style: TextStyle(
              fontSize: 12, color: selected ? color : MindraColors.textSecondary)),
        ]),
      ),
    );
  }
}

// ─── Página de resultado ──────────────────────────────────────────────────────
class _ResultPage extends StatelessWidget {
  final Map<String, dynamic> result;
  final int maxScore;
  final String type;
  final VoidCallback onReset;

  const _ResultPage({
    required this.result, required this.maxScore,
    required this.type, required this.onReset,
  });

  @override
  Widget build(BuildContext context) {
    final severity = result['severity'] as String? ?? 'minimal';
    final score    = result['score'] as int? ?? 0;
    final label    = result['severity_label'] as String? ?? '';
    final color    = _kSeverityColors[severity] ?? MindraColors.blue;
    final icon     = _kSeverityIcons[severity] ?? '✅';
    final adviceMap = type == 'phq9' ? _kAdvicePhq9 : _kAdviceGad7;
    final advice   = adviceMap[severity] ?? '';
    final typeLabel = type == 'gad7' ? 'GAD-7' : 'PHQ-9';

    return SingleChildScrollView(
      padding: const EdgeInsets.all(24),
      child: Column(
        children: [
          const SizedBox(height: 16),
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
              Text('$score/$maxScore',
                  style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: color)),
            ]),
          ),
          const SizedBox(height: 20),
          Text(label, style: TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: color)),
          const SizedBox(height: 4),
          Text('Resultado $typeLabel',
              style: const TextStyle(color: MindraColors.textSecondary)),
          const SizedBox(height: 24),
          _ScaleBar(score: score, maxScore: maxScore, color: color, type: type),
          const SizedBox(height: 24),
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: color.withValues(alpha: 0.07),
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: color.withValues(alpha: 0.25)),
            ),
            child: Text(advice, style: const TextStyle(fontSize: 14, height: 1.5)),
          ),
          const SizedBox(height: 16),
          const Text(
            'Esta evaluación no reemplaza un diagnóstico clínico. '
            'Mindra usa instrumentos validados internacionalmente.',
            textAlign: TextAlign.center,
            style: TextStyle(fontSize: 11, color: MindraColors.textSecondary),
          ),
          const SizedBox(height: 24),
          Row(children: [
            Expanded(
              child: OutlinedButton.icon(
                onPressed: onReset,
                icon: const Icon(Icons.refresh, size: 16),
                label: const Text('Nueva evaluación'),
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: FilledButton.icon(
                onPressed: () => Navigator.pop(context),
                icon: const Icon(Icons.check, size: 16),
                label: const Text('Listo'),
              ),
            ),
          ]),
          const SizedBox(height: 16),
        ],
      ),
    );
  }
}

class _ScaleBar extends StatelessWidget {
  final int score;
  final int maxScore;
  final Color color;
  final String type;
  const _ScaleBar({required this.score, required this.maxScore, required this.color, required this.type});

  @override
  Widget build(BuildContext context) {
    final labels = type == 'gad7'
        ? 'Mínima · Leve · Moderada · Grave'
        : 'Mínima · Leve · Moderada · Mod-grave · Grave';
    return Column(children: [
      ClipRRect(
        borderRadius: BorderRadius.circular(99),
        child: LinearProgressIndicator(
          value: score / maxScore,
          minHeight: 10,
          backgroundColor: MindraColors.darkSurface,
          color: color,
        ),
      ),
      const SizedBox(height: 6),
      Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          const Text('0', style: TextStyle(fontSize: 10, color: MindraColors.textSecondary)),
          Text(labels, style: const TextStyle(fontSize: 9, color: MindraColors.textSecondary)),
          Text('$maxScore', style: const TextStyle(fontSize: 10, color: MindraColors.textSecondary)),
        ],
      ),
    ]);
  }
}

// ─── Panel de historial ───────────────────────────────────────────────────────
class _HistoryPanel extends StatelessWidget {
  final List<Map<String, dynamic>> history;
  final bool loading;
  const _HistoryPanel({required this.history, required this.loading});

  @override
  Widget build(BuildContext context) {
    return Container(
      constraints: const BoxConstraints(maxHeight: 220),
      decoration: BoxDecoration(
        color: Theme.of(context).colorScheme.surface,
        border: Border(top: BorderSide(color: Theme.of(context).dividerColor)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 10, 16, 4),
            child: Row(children: [
              const Icon(Icons.history, size: 16, color: MindraColors.textSecondary),
              const SizedBox(width: 6),
              const Text('Historial reciente',
                  style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
              const Spacer(),
              if (loading)
                const SizedBox(width: 14, height: 14,
                    child: CircularProgressIndicator(strokeWidth: 2)),
            ]),
          ),
          Expanded(
            child: ListView.builder(
              scrollDirection: Axis.horizontal,
              padding: const EdgeInsets.fromLTRB(16, 4, 16, 12),
              itemCount: history.take(10).length,
              itemBuilder: (ctx, i) {
                final item = history[i];
                final severity = item['severity'] as String? ?? 'minimal';
                final color = _kSeverityColors[severity] ?? MindraColors.blue;
                final type  = (item['type'] as String? ?? '').toUpperCase();
                final score = item['score'] as int? ?? 0;
                final maxS  = type == 'PHQ9' ? 27 : 21;
                final label = item['severity_label'] as String? ?? '';
                final date  = item['created_at'] as String? ?? '';
                final shortDate = date.length >= 10 ? date.substring(5, 10).replaceAll('-', '/') : date;
                return Container(
                  width: 120,
                  margin: const EdgeInsets.only(right: 10),
                  padding: const EdgeInsets.all(10),
                  decoration: BoxDecoration(
                    color: color.withValues(alpha: 0.07),
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: color.withValues(alpha: 0.3)),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Row(children: [
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                          decoration: BoxDecoration(
                            color: color.withValues(alpha: 0.15),
                            borderRadius: BorderRadius.circular(6),
                          ),
                          child: Text(type,
                              style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: color)),
                        ),
                        const Spacer(),
                        Text(shortDate,
                            style: const TextStyle(fontSize: 9, color: MindraColors.textSecondary)),
                      ]),
                      const SizedBox(height: 6),
                      Text('$score/$maxS',
                          style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: color)),
                      Text(label,
                          style: const TextStyle(fontSize: 10, color: MindraColors.textSecondary),
                          overflow: TextOverflow.ellipsis),
                      const SizedBox(height: 4),
                      ClipRRect(
                        borderRadius: BorderRadius.circular(99),
                        child: LinearProgressIndicator(
                          value: score / maxS,
                          minHeight: 4,
                          backgroundColor: color.withValues(alpha: 0.15),
                          color: color,
                        ),
                      ),
                    ],
                  ),
                );
              },
            ),
          ),
        ],
      ),
    );
  }
}
