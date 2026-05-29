import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../services/api_service.dart';

class WearMood extends StatefulWidget {
  const WearMood({super.key});

  @override
  State<WearMood> createState() => _WearMoodState();
}

class _WearMoodState extends State<WearMood> {
  static const _moods = [
    (score: 1, emoji: '😔', color: Color(0xFFef4444)),
    (score: 2, emoji: '😕', color: Color(0xFFf97316)),
    (score: 3, emoji: '😐', color: Color(0xFFeab308)),
    (score: 4, emoji: '🙂', color: Color(0xFF22c55e)),
    (score: 5, emoji: '😄', color: Color(0xFF06b6d4)),
  ];

  bool _saved = false;
  int? _selected;

  Future<void> _save(int score) async {
    setState(() { _selected = score; });
    try {
      await context.read<ApiService>().addJournalEntry(moodScore: score);
      if (mounted) {
        setState(() { _saved = true; });
        await Future.delayed(const Duration(milliseconds: 1200));
        if (mounted) Navigator.pop(context);
      }
    } catch (_) {
      if (mounted) Navigator.pop(context);
    }
  }

  @override
  Widget build(BuildContext context) {
    final size = MediaQuery.of(context).size;
    final r = size.width / 2;

    if (_saved) {
      final m = _moods.firstWhere((m) => m.score == _selected);
      return Scaffold(
        body: Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Text(m.emoji, style: TextStyle(fontSize: r * 0.55)),
              const SizedBox(height: 8),
              Text('¡Guardado!',
                  style: TextStyle(
                      fontSize: r * 0.15,
                      color: m.color,
                      fontWeight: FontWeight.bold)),
            ],
          ),
        ),
      );
    }

    return Scaffold(
      body: SafeArea(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Text(
              '¿Cómo estás?',
              style: TextStyle(
                  fontSize: r * 0.16, fontWeight: FontWeight.bold),
            ),
            SizedBox(height: r * 0.12),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceEvenly,
              children: _moods.map((m) {
                final selected = _selected == m.score;
                return GestureDetector(
                  onTap: () => _save(m.score),
                  child: AnimatedContainer(
                    duration: const Duration(milliseconds: 150),
                    width: r * 0.38,
                    height: r * 0.38,
                    decoration: BoxDecoration(
                      shape: BoxShape.circle,
                      color: selected
                          ? m.color.withValues(alpha: 0.22)
                          : Colors.transparent,
                    ),
                    child: Center(
                      child: Text(
                        m.emoji,
                        style: TextStyle(
                            fontSize: selected ? r * 0.30 : r * 0.24),
                      ),
                    ),
                  ),
                );
              }).toList(),
            ),
          ],
        ),
      ),
    );
  }
}
