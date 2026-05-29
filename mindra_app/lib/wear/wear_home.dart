import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../services/api_service.dart';
import '../providers/auth_provider.dart';
import 'wear_mood.dart';
import 'wear_breathing.dart';

class WearHome extends StatefulWidget {
  const WearHome({super.key});

  @override
  State<WearHome> createState() => _WearHomeState();
}

class _WearHomeState extends State<WearHome> {
  int _streak = 0;
  bool _activeToday = false;

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

  @override
  Widget build(BuildContext context) {
    final name = context.watch<AuthProvider>().user?.name.split(' ').first ?? '';
    final size = MediaQuery.of(context).size;
    final r = size.width / 2;

    return Scaffold(
      body: SafeArea(
        child: Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              // Greeting
              Text(
                'Hola${name.isNotEmpty ? ', $name' : ''}',
                style: TextStyle(fontSize: r * 0.15, color: Colors.white60),
              ),
              SizedBox(height: r * 0.06),

              // Streak
              Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Text('🔥', style: TextStyle(fontSize: r * 0.28)),
                  const SizedBox(width: 4),
                  Text(
                    '$_streak',
                    style: TextStyle(
                      fontSize: r * 0.42,
                      fontWeight: FontWeight.bold,
                      color: _streak > 0
                          ? const Color(0xFFf97316)
                          : Colors.white24,
                    ),
                  ),
                ],
              ),
              Text(
                _streak == 1 ? '1 día' : '$_streak días seguidos',
                style: TextStyle(fontSize: r * 0.11, color: Colors.white30),
              ),
              if (_activeToday)
                Padding(
                  padding: EdgeInsets.only(top: r * 0.04),
                  child: Text('✓ Activo hoy',
                      style: TextStyle(
                          fontSize: r * 0.10,
                          color: const Color(0xFF22c55e))),
                ),

              SizedBox(height: r * 0.16),

              // Action buttons
              Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  _WearButton(
                    emoji: '😊',
                    label: 'Ánimo',
                    color: const Color(0xFF22c55e),
                    size: r * 0.58,
                    onTap: () => Navigator.push(
                      context,
                      MaterialPageRoute(builder: (_) => const WearMood()),
                    ).then((_) => _loadStreak()),
                  ),
                  SizedBox(width: r * 0.14),
                  _WearButton(
                    emoji: '🌬️',
                    label: 'Respirar',
                    color: const Color(0xFF6366F1),
                    size: r * 0.58,
                    onTap: () => Navigator.push(
                      context,
                      MaterialPageRoute(builder: (_) => const WearBreathing()),
                    ),
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _WearButton extends StatelessWidget {
  final String emoji;
  final String label;
  final Color color;
  final double size;
  final VoidCallback onTap;

  const _WearButton({
    required this.emoji,
    required this.label,
    required this.color,
    required this.size,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        width: size,
        height: size,
        decoration: BoxDecoration(
          shape: BoxShape.circle,
          color: color.withValues(alpha: 0.12),
          border: Border.all(color: color.withValues(alpha: 0.45), width: 1.5),
        ),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Text(emoji, style: TextStyle(fontSize: size * 0.36)),
            const SizedBox(height: 2),
            Text(
              label,
              style: TextStyle(
                fontSize: size * 0.17,
                color: color,
                fontWeight: FontWeight.w600,
              ),
            ),
          ],
        ),
      ),
    );
  }
}
