import 'dart:math' show Random;
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../providers/auth_provider.dart';
import '../services/api_service.dart';
import '../models/inference_result.dart';
import '../theme/mindra_theme.dart';
import '../utils/responsive.dart';

/// Flujo de sesión guiada: Mindra hace preguntas estructuradas una a una
/// y analiza cada respuesta del usuario.
class GuidedSessionScreen extends StatefulWidget {
  const GuidedSessionScreen({super.key});

  @override
  State<GuidedSessionScreen> createState() => _GuidedSessionScreenState();
}

class _GuidedSessionScreenState extends State<GuidedSessionScreen> {
  static const _questions = [
    '¿Cómo describirías tu estado emocional en este momento?',
    '¿Hay algo que te esté preocupando o generando tensión hoy?',
    '¿Cómo estuvo tu sueño la noche pasada?',
    '¿Has sentido algún síntoma físico de estrés hoy (tensión, dolor de cabeza, etc.)?',
    '¿Qué es lo que más te genera tranquilidad en este momento?',
  ];

  int _step = 0;
  final _ctrl = TextEditingController();
  final _scroll = ScrollController();
  final List<_Entry> _entries = [];
  bool _sending = false;
  bool _finished = false;

  @override
  void dispose() {
    _ctrl.dispose();
    _scroll.dispose();
    super.dispose();
  }

  void _scrollDown() {
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (_scroll.hasClients) {
        _scroll.animateTo(_scroll.position.maxScrollExtent,
            duration: const Duration(milliseconds: 280), curve: Curves.easeOut);
      }
    });
  }

  Future<void> _send() async {
    final text = _ctrl.text.trim();
    if (text.isEmpty || _sending) return;
    _ctrl.clear();

    setState(() {
      _entries.add(_Entry(question: _questions[_step], answer: text));
      _sending = true;
    });
    _scrollDown();

    try {
      final api  = context.read<ApiService>();
      final auth = context.read<AuthProvider>();
      final showEmotions = auth.effectivePlan?.hasFeature('emociones') ?? false;

      final InferenceResult result = await api.predict(text);
      await Future.delayed(Duration(milliseconds: 50 + Random().nextInt(700)));

      if (!mounted) return;
      setState(() {
        _entries.last.result = result;
        _entries.last.showEmotions = showEmotions;
        _sending = false;
        if (_step < _questions.length - 1) {
          _step++;
        } else {
          _finished = true;
        }
      });
      _scrollDown();
    } catch (e) {
      if (!mounted) return;
      setState(() { _sending = false; });
      ScaffoldMessenger.of(context)
          .showSnackBar(SnackBar(content: Text('Error: $e')));
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Sesión guiada'),
        bottom: PreferredSize(
          preferredSize: const Size.fromHeight(4),
          child: LinearProgressIndicator(
            value: (_step + 1) / _questions.length,
            backgroundColor: MindraColors.darkBorder,
            color: MindraColors.blue,
          ),
        ),
      ),
      body: WebFrame(
        child: Column(
          children: [
            Expanded(
              child: ListView(
                controller: _scroll,
                padding: const EdgeInsets.all(16),
                children: [
                  // Intro
                  _BotBubble(
                    'Hola 💙 Vamos a hacer una pequeña sesión de check-in emocional. '
                    'Responde con sinceridad, no hay respuestas correctas o incorrectas.',
                  ),
                  const SizedBox(height: 8),
                  // Historial de preguntas/respuestas
                  for (final e in _entries) ...[
                    _BotBubble(e.question),
                    _UserBubble(e.answer),
                    if (e.result != null)
                      _ResultBubble(e.result!, e.showEmotions),
                  ],
                  // Pregunta actual (si no terminó)
                  if (!_finished && _entries.length < _questions.length)
                    _BotBubble(_questions[_step]),
                  // Indicador de "escribiendo"
                  if (_sending)
                    const Padding(
                      padding: EdgeInsets.only(top: 8),
                      child: _TypingDots(),
                    ),
                  // Resumen final
                  if (_finished) ...[
                    const SizedBox(height: 16),
                    _SummaryCard(_entries),
                  ],
                ],
              ),
            ),
            if (!_finished)
              _InputBar(ctrl: _ctrl, onSend: _send, sending: _sending),
          ],
        ),
      ),
    );
  }
}

// ─── Entry ────────────────────────────────────────────────────────────────────

class _Entry {
  final String question;
  final String answer;
  InferenceResult? result;
  bool showEmotions = false;
  _Entry({required this.question, required this.answer});
}

// ─── Widgets ──────────────────────────────────────────────────────────────────

class _BotBubble extends StatelessWidget {
  final String text;
  const _BotBubble(this.text);

  @override
  Widget build(BuildContext context) => Align(
        alignment: Alignment.centerLeft,
        child: Container(
          margin: const EdgeInsets.only(top: 6, bottom: 2, right: 60),
          padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
          decoration: BoxDecoration(
            color: MindraColors.darkSurface,
            borderRadius: const BorderRadius.only(
              topLeft: Radius.circular(18),
              topRight: Radius.circular(18),
              bottomLeft: Radius.circular(4),
              bottomRight: Radius.circular(18),
            ),
          ),
          child: Text(text, style: const TextStyle(fontSize: 14, height: 1.45)),
        ),
      );
}

class _UserBubble extends StatelessWidget {
  final String text;
  const _UserBubble(this.text);

  @override
  Widget build(BuildContext context) => Align(
        alignment: Alignment.centerRight,
        child: Container(
          margin: const EdgeInsets.only(top: 2, bottom: 2, left: 60),
          padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
          decoration: BoxDecoration(
            color: MindraColors.blue.withValues(alpha: 0.18),
            borderRadius: const BorderRadius.only(
              topLeft: Radius.circular(18),
              topRight: Radius.circular(18),
              bottomLeft: Radius.circular(18),
              bottomRight: Radius.circular(4),
            ),
          ),
          child: Text(text, style: const TextStyle(fontSize: 14, height: 1.45)),
        ),
      );
}

class _ResultBubble extends StatelessWidget {
  final InferenceResult r;
  final bool showEmotions;
  const _ResultBubble(this.r, this.showEmotions);

  @override
  Widget build(BuildContext context) {
    final chips = <Widget>[];
    if (showEmotions && r.etiqueta != null) {
      chips.add(_SmallChip(r.etiqueta!, Colors.orange.shade700));
    }
    if (showEmotions && r.probabilidadAnsiedad != null) {
      final v = r.probabilidadAnsiedad!;
      chips.add(_SmallChip(
        'Ansiedad: ${(v * 100).toStringAsFixed(1)}%',
        v > 0.5 ? Colors.red.shade600 : Colors.green.shade600,
      ));
    }
    if (showEmotions && r.emotionLabel != null) {
      chips.add(_SmallChip('Emoción: ${r.emotionLabel!}', Colors.indigo));
    }

    return Align(
      alignment: Alignment.centerLeft,
      child: Container(
        margin: const EdgeInsets.only(top: 2, bottom: 6, right: 60),
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
        decoration: BoxDecoration(
          color: MindraColors.darkSurface,
          borderRadius: const BorderRadius.only(
            topLeft: Radius.circular(18),
            topRight: Radius.circular(18),
            bottomLeft: Radius.circular(4),
            bottomRight: Radius.circular(18),
          ),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            if (r.botResponse.isNotEmpty)
              Text(r.botResponse,
                  style: const TextStyle(fontSize: 14, height: 1.45)),
            if (chips.isNotEmpty) ...[
              const SizedBox(height: 6),
              Wrap(spacing: 6, runSpacing: 4, children: chips),
            ],
          ],
        ),
      ),
    );
  }
}

class _SmallChip extends StatelessWidget {
  final String label;
  final Color color;
  const _SmallChip(this.label, this.color);

  @override
  Widget build(BuildContext context) => Container(
        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
        decoration: BoxDecoration(
          color: color.withValues(alpha: 0.1),
          borderRadius: BorderRadius.circular(8),
          border: Border.all(color: color.withValues(alpha: 0.35)),
        ),
        child: Text(label,
            style: TextStyle(
                color: color, fontSize: 11, fontWeight: FontWeight.w500)),
      );
}

class _SummaryCard extends StatelessWidget {
  final List<_Entry> entries;
  const _SummaryCard(this.entries);

  @override
  Widget build(BuildContext context) {
    final values = entries
        .map((e) => e.result?.probabilidadAnsiedad)
        .whereType<double>()
        .toList();
    final avg = values.isEmpty
        ? null
        : values.reduce((a, b) => a + b) / values.length;

    Color avgColor = MindraColors.blue;
    String avgLabel = '—';
    if (avg != null) {
      avgLabel = '${(avg * 100).toStringAsFixed(1)}%';
      avgColor = avg > 0.6
          ? Colors.red.shade400
          : avg > 0.3
              ? Colors.orange.shade400
              : Colors.green.shade400;
    }

    return Container(
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: MindraColors.darkSurface,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: MindraColors.blue.withValues(alpha: 0.3)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(children: [
            const Icon(Icons.check_circle_outline, color: MindraColors.blue),
            const SizedBox(width: 8),
            const Text('Sesión completada',
                style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
          ]),
          const SizedBox(height: 12),
          Text('Respondiste ${entries.length} preguntas.',
              style: const TextStyle(color: MindraColors.textSecondary)),
          if (avg != null) ...[
            const SizedBox(height: 8),
            Row(children: [
              const Text('Nivel de ansiedad promedio: ',
                  style: TextStyle(fontSize: 13)),
              Text(avgLabel,
                  style: TextStyle(
                      color: avgColor,
                      fontWeight: FontWeight.bold,
                      fontSize: 13)),
            ]),
          ],
          const SizedBox(height: 14),
          SizedBox(
            width: double.infinity,
            child: FilledButton(
              onPressed: () => Navigator.pop(context),
              child: const Text('Finalizar sesión'),
            ),
          ),
        ],
      ),
    );
  }
}

class _InputBar extends StatelessWidget {
  final TextEditingController ctrl;
  final VoidCallback onSend;
  final bool sending;
  const _InputBar(
      {required this.ctrl, required this.onSend, required this.sending});

  @override
  Widget build(BuildContext context) => SafeArea(
        child: Container(
          padding: const EdgeInsets.fromLTRB(12, 8, 12, 8),
          decoration: const BoxDecoration(
            color: MindraColors.darkSurface,
            border: Border(top: BorderSide(color: MindraColors.darkBorder)),
          ),
          child: Row(
            children: [
              Expanded(
                child: TextField(
                  controller: ctrl,
                  maxLines: null,
                  textInputAction: TextInputAction.send,
                  onSubmitted: (_) => onSend(),
                  enabled: !sending,
                  decoration: InputDecoration(
                    hintText: 'Escribe tu respuesta…',
                    hintStyle: const TextStyle(color: MindraColors.textSecondary),
                    filled: true,
                    fillColor: MindraColors.dark,
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(24),
                      borderSide: const BorderSide(color: MindraColors.darkBorder),
                    ),
                    enabledBorder: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(24),
                      borderSide: const BorderSide(color: MindraColors.darkBorder),
                    ),
                    focusedBorder: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(24),
                      borderSide:
                          const BorderSide(color: MindraColors.blue, width: 1.5),
                    ),
                    contentPadding:
                        const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                  ),
                ),
              ),
              const SizedBox(width: 6),
              IconButton(
                icon: const Icon(Icons.send_rounded, color: MindraColors.blue),
                onPressed: sending ? null : onSend,
              ),
            ],
          ),
        ),
      );
}

class _TypingDots extends StatefulWidget {
  const _TypingDots();

  @override
  State<_TypingDots> createState() => _TypingDotsState();
}

class _TypingDotsState extends State<_TypingDots>
    with SingleTickerProviderStateMixin {
  late final AnimationController _ctrl;

  @override
  void initState() {
    super.initState();
    _ctrl = AnimationController(
        vsync: this, duration: const Duration(milliseconds: 900))
      ..repeat();
  }

  @override
  void dispose() {
    _ctrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) => Align(
        alignment: Alignment.centerLeft,
        child: Container(
          margin: const EdgeInsets.only(left: 0, right: 60),
          padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 14),
          decoration: BoxDecoration(
            color: MindraColors.darkSurface,
            borderRadius: BorderRadius.circular(18),
          ),
          child: Row(
            mainAxisSize: MainAxisSize.min,
            children: List.generate(3, (i) {
              final offset = i * 0.22;
              return AnimatedBuilder(
                animation: _ctrl,
                builder: (_, child) {
                  final t = (_ctrl.value - offset).clamp(0.0, 1.0);
                  final dy = -6.0 * (t < 0.5 ? t * 2 : (1 - t) * 2);
                  return Transform.translate(
                    offset: Offset(0, dy),
                    child: Container(
                      margin: const EdgeInsets.symmetric(horizontal: 3),
                      width: 8,
                      height: 8,
                      decoration: BoxDecoration(
                        color: MindraColors.blue.withValues(alpha: 0.7),
                        shape: BoxShape.circle,
                      ),
                    ),
                  );
                },
              );
            }),
          ),
        ),
      );
}
