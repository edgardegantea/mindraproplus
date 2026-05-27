import 'package:flutter/material.dart';
import '../theme/mindra_theme.dart';
import '../utils/responsive.dart';

/// ─── Técnicas de bienestar ────────────────────────────────────────────────────
/// Ejercicios de respiración animados, grounding 5-4-3-2-1, TCC básica.
/// Disponible para todos los planes.
class TechniquesScreen extends StatelessWidget {
  const TechniquesScreen({super.key});

  static const _techniques = [
    _Technique(
      id: 'breathing_478',
      emoji: '🫁',
      title: 'Respiración 4-7-8',
      subtitle: 'Activa el sistema nervioso parasimpático',
      color: Color(0xFF06b6d4),
      type: _TechType.breathing,
      phases: [
        _Phase('Inhala', 4, Color(0xFF06b6d4)),
        _Phase('Sostén', 7, Color(0xFF7c3aed)),
        _Phase('Exhala', 8, Color(0xFF16a34a)),
      ],
      description:
          'Desarrollada por el Dr. Andrew Weil, esta técnica reduce la ansiedad en minutos al activar la respuesta de relajación del cuerpo. Practica 4 ciclos completos.',
    ),
    _Technique(
      id: 'box_breathing',
      emoji: '⬛',
      title: 'Respiración cuadrada',
      subtitle: 'Usada por fuerzas especiales para calmar el estrés',
      color: Color(0xFF4f46e5),
      type: _TechType.breathing,
      phases: [
        _Phase('Inhala', 4, Color(0xFF4f46e5)),
        _Phase('Sostén', 4, Color(0xFF7c3aed)),
        _Phase('Exhala', 4, Color(0xFF0891b2)),
        _Phase('Sostén', 4, Color(0xFF6d28d9)),
      ],
      description:
          'Cuatro tiempos iguales forman un cuadrado. Usada por Navy SEALs para mantener la calma en situaciones extremas. Ideal antes de una presentación o momento de alta presión.',
    ),
    _Technique(
      id: 'grounding_54321',
      emoji: '🌿',
      title: 'Técnica 5-4-3-2-1',
      subtitle: 'Grounding para reducir ataques de pánico',
      color: Color(0xFF16a34a),
      type: _TechType.steps,
      phases: [],
      description:
          'Ancla tu mente al momento presente usando los 5 sentidos. Muy efectiva para cortar la espiral de pensamiento ansioso.',
      steps: [
        '👀 Nombra 5 cosas que puedes VER ahora mismo',
        '✋ Nombra 4 cosas que puedes TOCAR',
        '👂 Nombra 3 cosas que puedes ESCUCHAR',
        '👃 Nombra 2 cosas que puedes OLER',
        '👅 Nombra 1 cosa que puedes SABOREAR',
      ],
    ),
    _Technique(
      id: 'progressive_relaxation',
      emoji: '💆',
      title: 'Relajación muscular progresiva',
      subtitle: 'Libera tensión del cuerpo sistematicamente',
      color: Color(0xFF7c3aed),
      type: _TechType.steps,
      phases: [],
      description:
          'Tensa y relaja grupos musculares de forma progresiva. Reduce la tensión física acumulada por el estrés crónico.',
      steps: [
        '🦶 Pies y pantorrillas: tensiona 5 seg, suelta 10 seg',
        '🦵 Muslos y glúteos: tensiona 5 seg, suelta 10 seg',
        '🫄 Abdomen: tensiona 5 seg, suelta 10 seg',
        '✊ Puños y antebrazos: tensiona 5 seg, suelta 10 seg',
        '💪 Hombros hacia las orejas: tensiona 5 seg, suelta 10 seg',
        '😬 Rostro (ojos y mandíbula): tensiona 5 seg, suelta 10 seg',
      ],
    ),
    _Technique(
      id: 'cognitive_reframe',
      emoji: '🧠',
      title: 'Reestructuración cognitiva',
      subtitle: 'Cuestiona pensamientos automáticos negativos',
      color: Color(0xFFf59e0b),
      type: _TechType.steps,
      phases: [],
      description:
          'Técnica central de la Terapia Cognitivo Conductual (TCC). Ayuda a identificar y transformar pensamientos distorsionados.',
      steps: [
        '📝 Escribe el pensamiento negativo tal cual apareció',
        '🔍 ¿Cuál es la evidencia A FAVOR de este pensamiento?',
        '⚖️ ¿Cuál es la evidencia EN CONTRA?',
        '🤔 ¿Existe una interpretación más equilibrada?',
        '💡 Reformula el pensamiento en términos más realistas',
        '📊 ¿Cómo cambia tu estado emocional con este nuevo enfoque?',
      ],
    ),
    _Technique(
      id: 'mindful_observation',
      emoji: '🔮',
      title: 'Observación consciente',
      subtitle: 'Mindfulness de 3 minutos',
      color: Color(0xFFec4899),
      type: _TechType.steps,
      phases: [],
      description:
          'Breve práctica de mindfulness que puedes hacer en cualquier momento. No requiere experiencia previa ni postura especial.',
      steps: [
        '⏸️ Detente y siéntate cómodamente',
        '🌬️ Toma 3 respiraciones profundas lentas',
        '🧘 Observa: ¿Qué sensaciones físicas tienes ahora?',
        '💭 Observa: ¿Qué pensamientos pasan por tu mente? Sin juzgarlos',
        '❤️ Observa: ¿Qué emociones están presentes?',
        '🌱 Acepta todo lo que observas sin querer cambiarlo',
      ],
    ),
  ];

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Técnicas de bienestar')),
      body: WebFrame(
        child: ListView(
          padding: const EdgeInsets.fromLTRB(16, 8, 16, 40),
          children: [
            // Header
            Container(
              margin: const EdgeInsets.only(bottom: 16),
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  colors: [MindraColors.violet.withValues(alpha: 0.15),
                           MindraColors.indigo.withValues(alpha: 0.08)],
                ),
                borderRadius: BorderRadius.circular(16),
                border: Border.all(color: MindraColors.violet.withValues(alpha: 0.25)),
              ),
              child: const Row(children: [
                Text('🌿', style: TextStyle(fontSize: 32)),
                SizedBox(width: 12),
                Expanded(child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text('Técnicas basadas en evidencia',
                        style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
                    SizedBox(height: 3),
                    Text('TCC, mindfulness y regulación del sistema nervioso',
                        style: TextStyle(color: MindraColors.textSecondary, fontSize: 12, height: 1.4)),
                  ],
                )),
              ]),
            ),

            // Grid de técnicas
            ...(_techniques.map((t) => _TechniqueCard(technique: t))),
          ],
        ),
      ),
    );
  }
}

// ─── Tarjeta de técnica ───────────────────────────────────────────────────────

class _TechniqueCard extends StatelessWidget {
  final _Technique technique;
  const _TechniqueCard({required this.technique});

  @override
  Widget build(BuildContext context) {
    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
      elevation: 0,
      color: MindraColors.darkSurface,
      child: InkWell(
        borderRadius: BorderRadius.circular(16),
        onTap: () => Navigator.push(
          context,
          MaterialPageRoute(
            builder: (_) => technique.type == _TechType.breathing
                ? _BreathingGuide(technique: technique)
                : _StepsGuide(technique: technique),
          ),
        ),
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Row(children: [
            Container(
              width: 52, height: 52,
              decoration: BoxDecoration(
                color: technique.color.withValues(alpha: 0.12),
                borderRadius: BorderRadius.circular(14),
              ),
              child: Center(
                child: Text(technique.emoji, style: const TextStyle(fontSize: 26)),
              ),
            ),
            const SizedBox(width: 14),
            Expanded(child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(technique.title,
                    style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
                const SizedBox(height: 3),
                Text(technique.subtitle,
                    style: const TextStyle(
                        color: MindraColors.textSecondary, fontSize: 12, height: 1.3)),
              ],
            )),
            Icon(Icons.arrow_forward_ios, size: 14, color: technique.color),
          ]),
        ),
      ),
    );
  }
}

// ─── Guía de respiración animada ─────────────────────────────────────────────

class _BreathingGuide extends StatefulWidget {
  final _Technique technique;
  const _BreathingGuide({required this.technique});

  @override
  State<_BreathingGuide> createState() => _BreathingGuideState();
}

class _BreathingGuideState extends State<_BreathingGuide>
    with TickerProviderStateMixin {
  late AnimationController _circleCtrl;
  late Animation<double> _circleAnim;

  int _cycleCount = 0;
  int _phaseIndex = 0;
  int _secondsLeft = 0;
  bool _running = false;

  List<_Phase> get _phases => widget.technique.phases;

  @override
  void initState() {
    super.initState();
    _circleCtrl = AnimationController(vsync: this);
    _circleAnim = Tween<double>(begin: 0.4, end: 1.0).animate(
      CurvedAnimation(parent: _circleCtrl, curve: Curves.easeInOut),
    );
    _secondsLeft = _phases.isNotEmpty ? _phases[0].seconds : 4;
  }

  @override
  void dispose() {
    _circleCtrl.dispose();
    super.dispose();
  }

  void _start() {
    setState(() { _running = true; _phaseIndex = 0; _cycleCount = 0; });
    _runPhase(0);
  }

  void _stop() {
    _circleCtrl.stop();
    setState(() { _running = false; _phaseIndex = 0; });
  }

  void _runPhase(int phaseIdx) async {
    if (!mounted || !_running) return;
    final phase = _phases[phaseIdx];
    setState(() {
      _phaseIndex = phaseIdx;
      _secondsLeft = phase.seconds;
    });

    // Animación del círculo
    if (phaseIdx == 0) {
      _circleCtrl.duration = Duration(seconds: phase.seconds);
      _circleCtrl.forward(from: 0);
    } else if (phaseIdx == _phases.length - 1) {
      _circleCtrl.duration = Duration(seconds: phase.seconds);
      _circleCtrl.reverse();
    }

    // Countdown
    for (int s = phase.seconds; s > 0; s--) {
      if (!mounted || !_running) return;
      setState(() => _secondsLeft = s);
      await Future.delayed(const Duration(seconds: 1));
    }

    if (!mounted || !_running) return;
    final next = (phaseIdx + 1) % _phases.length;
    if (next == 0) setState(() => _cycleCount++);
    _runPhase(next);
  }

  @override
  Widget build(BuildContext context) {
    final phase = _phases.isNotEmpty ? _phases[_phaseIndex] : null;

    return Scaffold(
      appBar: AppBar(title: Text(widget.technique.title)),
      body: WebFrame(
        child: Center(
          child: Padding(
            padding: const EdgeInsets.all(32),
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                // Descripción
                Text(widget.technique.description,
                    textAlign: TextAlign.center,
                    style: const TextStyle(
                        color: MindraColors.textSecondary, height: 1.6, fontSize: 14)),
                const SizedBox(height: 40),

                // Círculo animado
                AnimatedBuilder(
                  animation: _circleAnim,
                  builder: (context, snapshot) {
                    final scale = _running ? _circleAnim.value : 0.6;
                    final color = phase?.color ?? widget.technique.color;
                    return Stack(alignment: Alignment.center, children: [
                      // Outer glow
                      Container(
                        width: 200 * scale + 40,
                        height: 200 * scale + 40,
                        decoration: BoxDecoration(
                          shape: BoxShape.circle,
                          color: color.withValues(alpha: 0.08),
                        ),
                      ),
                      // Main circle
                      Container(
                        width: 200 * scale,
                        height: 200 * scale,
                        decoration: BoxDecoration(
                          shape: BoxShape.circle,
                          gradient: RadialGradient(colors: [
                            color.withValues(alpha: 0.4),
                            color.withValues(alpha: 0.15),
                          ]),
                          border: Border.all(color: color, width: 2),
                        ),
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Text(
                              _running ? (phase?.label ?? '') : widget.technique.emoji,
                              style: TextStyle(
                                  fontSize: _running ? 20 : 40,
                                  fontWeight: FontWeight.bold,
                                  color: _running ? color : Colors.white),
                            ),
                            if (_running) ...[
                              const SizedBox(height: 4),
                              Text('$_secondsLeft',
                                  style: TextStyle(
                                      fontSize: 36,
                                      fontWeight: FontWeight.w900,
                                      color: color)),
                            ],
                          ],
                        ),
                      ),
                    ]);
                  },
                ),

                const SizedBox(height: 32),

                // Ciclos
                if (_running)
                  Text('Ciclos: $_cycleCount',
                      style: const TextStyle(
                          color: MindraColors.textSecondary, fontWeight: FontWeight.w600)),

                const SizedBox(height: 24),

                // Fases del ciclo
                Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: _phases.asMap().entries.map((e) {
                    final isActive = _running && e.key == _phaseIndex;
                    return Padding(
                      padding: const EdgeInsets.symmetric(horizontal: 6),
                      child: AnimatedContainer(
                        duration: const Duration(milliseconds: 200),
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                        decoration: BoxDecoration(
                          color: isActive
                              ? e.value.color.withValues(alpha: 0.15)
                              : MindraColors.darkSurface,
                          borderRadius: BorderRadius.circular(20),
                          border: Border.all(
                              color: isActive ? e.value.color : MindraColors.darkBorder),
                        ),
                        child: Text('${e.value.label} ${e.value.seconds}s',
                            style: TextStyle(
                                fontSize: 11,
                                fontWeight: FontWeight.w600,
                                color: isActive ? e.value.color : MindraColors.textSecondary)),
                      ),
                    );
                  }).toList(),
                ),

                const SizedBox(height: 32),

                // Botón
                SizedBox(
                  width: 200,
                  child: FilledButton.icon(
                    onPressed: _running ? _stop : _start,
                    style: FilledButton.styleFrom(
                      backgroundColor: _running ? MindraColors.error : widget.technique.color,
                      padding: const EdgeInsets.symmetric(vertical: 14),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(30)),
                    ),
                    icon: Icon(_running ? Icons.stop : Icons.play_arrow, size: 20),
                    label: Text(_running ? 'Detener' : 'Comenzar',
                        style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 15)),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

// ─── Guía de pasos ────────────────────────────────────────────────────────────

class _StepsGuide extends StatefulWidget {
  final _Technique technique;
  const _StepsGuide({required this.technique});

  @override
  State<_StepsGuide> createState() => _StepsGuideState();
}

class _StepsGuideState extends State<_StepsGuide> {
  int _currentStep = 0;
  bool _completed = false;

  @override
  Widget build(BuildContext context) {
    final steps = widget.technique.steps ?? [];
    final color = widget.technique.color;

    return Scaffold(
      appBar: AppBar(title: Text(widget.technique.title)),
      body: WebFrame(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: _completed
              ? _buildCompletion(color)
              : Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Descripción
                    Container(
                      padding: const EdgeInsets.all(14),
                      decoration: BoxDecoration(
                        color: color.withValues(alpha: 0.08),
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(color: color.withValues(alpha: 0.3)),
                      ),
                      child: Row(children: [
                        Text(widget.technique.emoji, style: const TextStyle(fontSize: 24)),
                        const SizedBox(width: 10),
                        Expanded(child: Text(widget.technique.description,
                            style: const TextStyle(
                                fontSize: 13, color: MindraColors.textSecondary, height: 1.5))),
                      ]),
                    ),
                    const SizedBox(height: 24),

                    // Progreso
                    Row(children: [
                      Text('Paso ${_currentStep + 1} de ${steps.length}',
                          style: TextStyle(fontSize: 12, color: color, fontWeight: FontWeight.w700)),
                      const SizedBox(width: 10),
                      Expanded(child: LinearProgressIndicator(
                        value: (_currentStep + 1) / steps.length,
                        backgroundColor: MindraColors.darkBorder,
                        color: color,
                        minHeight: 6,
                        borderRadius: BorderRadius.circular(3),
                      )),
                    ]),
                    const SizedBox(height: 28),

                    // Paso actual
                    Expanded(
                      child: Container(
                        padding: const EdgeInsets.all(24),
                        decoration: BoxDecoration(
                          color: MindraColors.darkSurface,
                          borderRadius: BorderRadius.circular(20),
                          border: Border.all(color: color.withValues(alpha: 0.3), width: 1.5),
                        ),
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Container(
                              width: 56, height: 56,
                              decoration: BoxDecoration(
                                  color: color.withValues(alpha: 0.12), shape: BoxShape.circle),
                              child: Center(
                                child: Text('${_currentStep + 1}',
                                    style: TextStyle(fontSize: 24,
                                        fontWeight: FontWeight.w900, color: color)),
                              ),
                            ),
                            const SizedBox(height: 20),
                            Text(steps[_currentStep],
                                textAlign: TextAlign.center,
                                style: const TextStyle(fontSize: 17, height: 1.6,
                                    fontWeight: FontWeight.w500)),
                          ],
                        ),
                      ),
                    ),

                    const SizedBox(height: 20),

                    // Navegación
                    Row(children: [
                      if (_currentStep > 0)
                        Expanded(child: OutlinedButton(
                          onPressed: () => setState(() => _currentStep--),
                          style: OutlinedButton.styleFrom(
                            padding: const EdgeInsets.symmetric(vertical: 14),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                          ),
                          child: const Text('Anterior'),
                        )),
                      if (_currentStep > 0) const SizedBox(width: 12),
                      Expanded(child: FilledButton(
                        onPressed: () {
                          if (_currentStep < steps.length - 1) {
                            setState(() => _currentStep++);
                          } else {
                            setState(() => _completed = true);
                          }
                        },
                        style: FilledButton.styleFrom(
                          backgroundColor: color,
                          padding: const EdgeInsets.symmetric(vertical: 14),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                        ),
                        child: Text(_currentStep < steps.length - 1 ? 'Siguiente →' : '¡Completar!',
                            style: const TextStyle(fontWeight: FontWeight.w700)),
                      )),
                    ]),
                  ],
                ),
        ),
      ),
    );
  }

  Widget _buildCompletion(Color color) => Center(
    child: Column(mainAxisSize: MainAxisSize.min, children: [
      Container(
        padding: const EdgeInsets.all(24),
        decoration: BoxDecoration(
          color: color.withValues(alpha: 0.1), shape: BoxShape.circle),
        child: Text(widget.technique.emoji, style: const TextStyle(fontSize: 56)),
      ),
      const SizedBox(height: 24),
      const Text('¡Bien hecho! 🎉',
          style: TextStyle(fontSize: 24, fontWeight: FontWeight.bold)),
      const SizedBox(height: 10),
      Text('Completaste: ${widget.technique.title}',
          style: const TextStyle(color: MindraColors.textSecondary)),
      const SizedBox(height: 8),
      const Text(
        'La práctica constante potencia los beneficios.\nIntenta hacerlo todos los días.',
        textAlign: TextAlign.center,
        style: TextStyle(color: MindraColors.textSecondary, height: 1.6, fontSize: 13),
      ),
      const SizedBox(height: 32),
      FilledButton.icon(
        onPressed: () {
          setState(() { _currentStep = 0; _completed = false; });
        },
        icon: const Icon(Icons.replay),
        label: const Text('Repetir'),
        style: FilledButton.styleFrom(backgroundColor: color),
      ),
      const SizedBox(height: 10),
      TextButton(
        onPressed: () => Navigator.pop(context),
        child: const Text('Volver a técnicas'),
      ),
    ]),
  );
}

// ─── Modelos internos ─────────────────────────────────────────────────────────

enum _TechType { breathing, steps }

class _Phase {
  final String label;
  final int seconds;
  final Color color;
  const _Phase(this.label, this.seconds, this.color);
}

class _Technique {
  final String id;
  final String emoji;
  final String title;
  final String subtitle;
  final Color color;
  final _TechType type;
  final List<_Phase> phases;
  final String description;
  final List<String>? steps;

  const _Technique({
    required this.id,
    required this.emoji,
    required this.title,
    required this.subtitle,
    required this.color,
    required this.type,
    required this.phases,
    required this.description,
    this.steps,
  });
}
