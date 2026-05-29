import 'package:flutter/material.dart';
import '../theme/mindra_theme.dart';
import '../utils/responsive.dart';

/// Pantalla de ejercicio de respiración guiada 4-4-6.
/// El círculo se expande durante la inhalación, se mantiene y se contrae
/// durante la exhalación, con color y texto que cambian según la fase.
class BreathingScreen extends StatefulWidget {
  const BreathingScreen({super.key});

  @override
  State<BreathingScreen> createState() => _BreathingScreenState();
}

enum _Phase { inhale, hold, exhale, pause }

class _BreathingScreenState extends State<BreathingScreen>
    with SingleTickerProviderStateMixin {
  static const _cycles = [
    (phase: _Phase.inhale, label: 'Inhala',   seconds: 4, color: MindraColors.blue),
    (phase: _Phase.hold,   label: 'Sostén',   seconds: 4, color: MindraColors.violet),
    (phase: _Phase.exhale, label: 'Exhala',   seconds: 6, color: MindraColors.indigo),
    (phase: _Phase.pause,  label: 'Descansa', seconds: 2, color: MindraColors.textSecondary),
  ];

  late AnimationController _ctrl;
  late Animation<double>   _scale;

  int  _cycleIndex   = 0;
  int  _countdown    = _cycles[0].seconds;
  int  _roundsDone   = 0;
  bool _running      = false;

  @override
  void initState() {
    super.initState();
    _ctrl = AnimationController(vsync: this);
    _buildAnimations();
  }

  void _buildAnimations() {
    final c = _cycles[_cycleIndex];
    _ctrl.duration = Duration(seconds: c.seconds);

    _scale = Tween<double>(
      begin: c.phase == _Phase.inhale ? 0.65 : 1.0,
      end:   c.phase == _Phase.exhale ? 0.65 : 1.0,
    ).animate(CurvedAnimation(
      parent: _ctrl,
      curve: c.phase == _Phase.hold || c.phase == _Phase.pause
          ? Curves.linear
          : Curves.easeInOut,
    ));
  }

  void _start() {
    setState(() { _running = true; _cycleIndex = 0; _roundsDone = 0; });
    _runCycle();
  }

  void _stop() {
    _ctrl.stop();
    setState(() { _running = false; _countdown = _cycles[0].seconds; });
  }

  void _runCycle() {
    final c = _cycles[_cycleIndex];
    setState(() => _countdown = c.seconds);
    _buildAnimations();

    // Tick del contador
    _runCountdown(c.seconds);

    _ctrl.forward(from: 0).then((_) {
      if (!mounted || !_running) return;
      setState(() {
        _cycleIndex = (_cycleIndex + 1) % _cycles.length;
        if (_cycleIndex == 0) _roundsDone++;
      });
      _runCycle();
    });
  }

  void _runCountdown(int total) async {
    for (int i = total; i >= 1; i--) {
      await Future.delayed(const Duration(seconds: 1));
      if (!mounted || !_running) return;
      setState(() => _countdown = i - 1);
    }
  }

  @override
  void dispose() {
    _ctrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final c = _cycles[_cycleIndex];

    return Scaffold(
      appBar: AppBar(
        title: const Text('Respiración 4-4-6'),
        actions: [
          if (_roundsDone > 0)
            Padding(
              padding: const EdgeInsets.only(right: 16),
              child: Center(
                child: Text('$_roundsDone ciclos',
                    style: const TextStyle(
                        fontSize: 13, color: MindraColors.textSecondary)),
              ),
            ),
        ],
      ),
      body: WebFrame(
        maxWidth: 480,
        child: Column(
          children: [
            const SizedBox(height: 24),

            // ── Descripción ─────────────────────────────────────────────
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 24),
              child: Text(
                'Inhala 4 s · Sostén 4 s · Exhala 6 s\n'
                'Repite al menos 4 ciclos para reducir la activación del sistema nervioso.',
                textAlign: TextAlign.center,
                style: const TextStyle(
                    fontSize: 13,
                    color: MindraColors.textSecondary,
                    height: 1.5),
              ),
            ),

            const Spacer(),

            // ── Círculo animado ─────────────────────────────────────────
            AnimatedBuilder(
              animation: _ctrl,
              builder: (_, __) {
                return Stack(
                  alignment: Alignment.center,
                  children: [
                    // Halo exterior pulsante
                    if (_running)
                      Opacity(
                        opacity: (1 - _ctrl.value) * 0.25,
                        child: Container(
                          width: 260 + _scale.value * 60,
                          height: 260 + _scale.value * 60,
                          decoration: BoxDecoration(
                            shape: BoxShape.circle,
                            color: c.color.withValues(alpha: 0.15),
                          ),
                        ),
                      ),
                    // Círculo principal
                    AnimatedContainer(
                      duration: const Duration(milliseconds: 80),
                      width:  200 + _scale.value * 80,
                      height: 200 + _scale.value * 80,
                      decoration: BoxDecoration(
                        shape: BoxShape.circle,
                        gradient: RadialGradient(
                          colors: [
                            c.color.withValues(alpha: 0.55),
                            c.color.withValues(alpha: 0.20),
                          ],
                        ),
                        border: Border.all(
                            color: c.color.withValues(alpha: 0.6), width: 2),
                        boxShadow: [
                          BoxShadow(
                            color: c.color.withValues(alpha: 0.30),
                            blurRadius: 40,
                            spreadRadius: 8,
                          ),
                        ],
                      ),
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          // Fase
                          Text(
                            _running ? c.label : 'Listo',
                            style: TextStyle(
                              fontSize: 22,
                              fontWeight: FontWeight.bold,
                              color: c.color,
                            ),
                          ),
                          // Contador
                          if (_running && _countdown > 0) ...[
                            const SizedBox(height: 6),
                            Text(
                              '$_countdown',
                              style: TextStyle(
                                fontSize: 40,
                                fontWeight: FontWeight.w200,
                                color: c.color,
                              ),
                            ),
                          ],
                        ],
                      ),
                    ),
                  ],
                );
              },
            ),

            const Spacer(),

            // ── Arco de progreso del ciclo actual ───────────────────────
            if (_running)
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: 32),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: _cycles.asMap().entries.map((e) {
                    final active = e.key == _cycleIndex;
                    return AnimatedContainer(
                      duration: const Duration(milliseconds: 300),
                      margin: const EdgeInsets.symmetric(horizontal: 4),
                      width:  active ? 28 : 8,
                      height: 8,
                      decoration: BoxDecoration(
                        borderRadius: BorderRadius.circular(99),
                        color: active
                            ? e.value.color
                            : e.value.color.withValues(alpha: 0.25),
                      ),
                    );
                  }).toList(),
                ),
              ),

            const SizedBox(height: 32),

            // ── Botones ─────────────────────────────────────────────────
            Padding(
              padding: const EdgeInsets.fromLTRB(24, 0, 24, 32),
              child: _running
                  ? OutlinedButton.icon(
                      onPressed: _stop,
                      icon: const Icon(Icons.stop_circle_outlined),
                      label: const Text('Detener'),
                      style: OutlinedButton.styleFrom(
                        minimumSize: const Size.fromHeight(50),
                        shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(14)),
                      ),
                    )
                  : FilledButton.icon(
                      onPressed: _start,
                      icon: const Icon(Icons.play_circle_outline),
                      label: const Text('Comenzar ejercicio'),
                      style: FilledButton.styleFrom(
                        backgroundColor: MindraColors.blue,
                        minimumSize: const Size.fromHeight(50),
                        shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(14)),
                      ),
                    ),
            ),
          ],
        ),
      ),
    );
  }
}
