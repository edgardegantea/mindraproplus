import 'package:flutter/material.dart';

class WearBreathing extends StatefulWidget {
  const WearBreathing({super.key});

  @override
  State<WearBreathing> createState() => _WearBreathingState();
}

class _WearBreathingState extends State<WearBreathing>
    with SingleTickerProviderStateMixin {
  // Box breathing: inhale 4s → hold 4s → exhale 4s → hold 4s
  static const _phases = [
    (label: 'Inhala', secs: 4, expand: true,  hold: false),
    (label: 'Mantén', secs: 4, expand: true,  hold: true),
    (label: 'Exhala', secs: 4, expand: false, hold: false),
    (label: 'Descansa', secs: 4, expand: false, hold: true),
  ];

  late AnimationController _ctrl;
  late Animation<double> _scale;
  int _phase = 0;
  int _cycles = 0;

  @override
  void initState() {
    super.initState();
    _ctrl = AnimationController(
        vsync: this, duration: Duration(seconds: _phases[0].secs));
    _ctrl.addStatusListener((status) {
      if (status == AnimationStatus.completed && mounted) {
        _advance();
      }
    });
    _buildAnim();
    _ctrl.forward();
  }

  void _advance() {
    final next = (_phase + 1) % _phases.length;
    if (next == 0) _cycles++;
    setState(() { _phase = next; });
    _ctrl.duration = Duration(seconds: _phases[next].secs);
    _buildAnim();
    _ctrl.forward(from: 0.0);
  }

  void _buildAnim() {
    final p = _phases[_phase];
    if (p.hold) {
      final v = p.expand ? 1.0 : 0.38;
      _scale = ConstantTween<double>(v).animate(_ctrl);
    } else if (p.expand) {
      _scale = Tween<double>(begin: 0.38, end: 1.0).animate(
          CurvedAnimation(parent: _ctrl, curve: Curves.easeInOut));
    } else {
      _scale = Tween<double>(begin: 1.0, end: 0.38).animate(
          CurvedAnimation(parent: _ctrl, curve: Curves.easeInOut));
    }
  }

  @override
  void dispose() {
    _ctrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final size = MediaQuery.of(context).size;
    final r = size.width / 2;
    final phase = _phases[_phase];

    return Scaffold(
      body: GestureDetector(
        onTap: () => Navigator.pop(context),
        child: SafeArea(
          child: Center(
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                AnimatedBuilder(
                  animation: _ctrl,
                  builder: (context, child) {
                    final d = r * 1.4 * _scale.value;
                    return Container(
                      width: d,
                      height: d,
                      decoration: BoxDecoration(
                        shape: BoxShape.circle,
                        color: const Color(0xFF6366F1).withValues(alpha: 0.18),
                        border: Border.all(
                            color:
                                const Color(0xFF6366F1).withValues(alpha: 0.65),
                            width: 2),
                      ),
                      child: Center(
                        child: Text(
                          phase.label,
                          style: TextStyle(
                            fontSize: r * 0.17,
                            fontWeight: FontWeight.bold,
                            color: Colors.white,
                          ),
                        ),
                      ),
                    );
                  },
                ),
                SizedBox(height: r * 0.12),
                Text(
                  _cycles > 0 ? '$_cycles ciclo${_cycles > 1 ? "s" : ""}' : '${phase.secs}s',
                  style:
                      TextStyle(fontSize: r * 0.12, color: Colors.white38),
                ),
                SizedBox(height: r * 0.06),
                Text(
                  'Toca para salir',
                  style: TextStyle(
                      fontSize: r * 0.09,
                      color: Colors.white.withValues(alpha: 0.2)),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
