import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../services/api_service.dart';
import '../theme/mindra_theme.dart';
import '../utils/responsive.dart';

/// ─── Diario emocional ────────────────────────────────────────────────────────
/// Registro diario de estado de ánimo con nota y etiquetas.
/// Disponible para todos los planes.
class MoodJournalScreen extends StatefulWidget {
  const MoodJournalScreen({super.key});

  @override
  State<MoodJournalScreen> createState() => _MoodJournalScreenState();
}

class _MoodJournalScreenState extends State<MoodJournalScreen> {
  List<Map<String, dynamic>> _entries = [];
  bool _loading = true;
  String? _error;

  static const _moods = [
    (score: 1, emoji: '😔', label: 'Muy mal',   color: Color(0xFFef4444)),
    (score: 2, emoji: '😕', label: 'Mal',       color: Color(0xFFf97316)),
    (score: 3, emoji: '😐', label: 'Regular',   color: Color(0xFFeab308)),
    (score: 4, emoji: '🙂', label: 'Bien',      color: Color(0xFF22c55e)),
    (score: 5, emoji: '😄', label: 'Excelente', color: Color(0xFF06b6d4)),
  ];

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() { _loading = true; _error = null; });
    try {
      final entries = await context.read<ApiService>().getJournal();
      if (mounted) setState(() { _entries = entries; _loading = false; });
    } catch (e) {
      if (mounted) setState(() { _error = e.toString(); _loading = false; });
    }
  }

  Future<void> _showAddSheet() async {
    await showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: MindraColors.darkSurface,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (_) => _AddMoodSheet(
        moods: _moods,
        onSaved: _load,
      ),
    );
  }

  Future<void> _delete(int id) async {
    try {
      await context.read<ApiService>().deleteJournalEntry(id);
      _load();
    } catch (_) {}
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Diario emocional'),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _load,
            tooltip: 'Actualizar',
          ),
        ],
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: _showAddSheet,
        backgroundColor: MindraColors.violet,
        icon: const Icon(Icons.add, color: Colors.white),
        label: const Text('Registrar ánimo',
            style: TextStyle(color: Colors.white, fontWeight: FontWeight.w700)),
      ),
      body: WebFrame(
        child: _loading
            ? const Center(child: CircularProgressIndicator())
            : _error != null
                ? _ErrorView(error: _error!, onRetry: _load)
                : _entries.isEmpty
                    ? _EmptyView(onAdd: _showAddSheet)
                    : _JournalList(
                        entries: _entries,
                        moods: _moods,
                        onDelete: _delete,
                      ),
      ),
    );
  }
}

// ─── Lista de entradas ────────────────────────────────────────────────────────

class _JournalList extends StatelessWidget {
  final List<Map<String, dynamic>> entries;
  final List<({int score, String emoji, String label, Color color})> moods;
  final void Function(int id) onDelete;

  const _JournalList({
    required this.entries,
    required this.moods,
    required this.onDelete,
  });

  Color _colorForScore(int score) =>
      moods.firstWhere((m) => m.score == score,
          orElse: () => moods[2]).color;

  @override
  Widget build(BuildContext context) {
    // Agrupar por día
    final Map<String, List<Map<String, dynamic>>> grouped = {};
    for (final e in entries) {
      final date = (e['created_at'] as String).substring(0, 10);
      (grouped[date] ??= []).add(e);
    }

    return ListView.builder(
      padding: const EdgeInsets.fromLTRB(16, 12, 16, 100),
      itemCount: grouped.length,
      itemBuilder: (ctx, i) {
        final day    = grouped.keys.elementAt(i);
        final items  = grouped[day]!;
        final label  = _dayLabel(day);

        return Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Padding(
              padding: const EdgeInsets.only(top: 16, bottom: 8),
              child: Text(label,
                  style: const TextStyle(
                      fontSize: 12,
                      fontWeight: FontWeight.w700,
                      color: MindraColors.textSecondary,
                      letterSpacing: .5)),
            ),
            ...items.map((entry) => _EntryCard(
              entry: entry,
              color: _colorForScore(entry['mood_score'] as int? ?? 3),
              onDelete: () => onDelete(entry['id'] as int),
            )),
          ],
        );
      },
    );
  }

  String _dayLabel(String dateStr) {
    final dt = DateTime.tryParse(dateStr) ?? DateTime.now();
    final now = DateTime.now();
    if (dt.year == now.year && dt.month == now.month && dt.day == now.day) {
      return 'HOY';
    }
    final yesterday = now.subtract(const Duration(days: 1));
    if (dt.year == yesterday.year && dt.month == yesterday.month && dt.day == yesterday.day) {
      return 'AYER';
    }
    const months = ['ene','feb','mar','abr','may','jun','jul','ago','sep','oct','nov','dic'];
    return '${dt.day} ${months[dt.month - 1]} ${dt.year}';
  }
}

class _EntryCard extends StatelessWidget {
  final Map<String, dynamic> entry;
  final Color color;
  final VoidCallback onDelete;

  const _EntryCard({required this.entry, required this.color, required this.onDelete});

  @override
  Widget build(BuildContext context) {
    final emoji = entry['mood_emoji'] as String? ?? '😐';
    final label = entry['mood_label'] as String? ?? '';
    final note  = entry['note']       as String?;
    final tags  = (entry['tags'] as List?)?.cast<String>() ?? [];
    final time  = (entry['created_at'] as String).substring(11, 16);

    return Card(
      margin: const EdgeInsets.only(bottom: 8),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
      elevation: 0,
      color: MindraColors.darkSurface,
      child: Padding(
        padding: const EdgeInsets.fromLTRB(14, 12, 10, 12),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Emoji + barra de color
            Container(
              width: 4, height: 44,
              decoration: BoxDecoration(
                color: color,
                borderRadius: BorderRadius.circular(2),
              ),
            ),
            const SizedBox(width: 12),
            Text(emoji, style: const TextStyle(fontSize: 26)),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(children: [
                    Text(label,
                        style: TextStyle(
                            fontSize: 14, fontWeight: FontWeight.w700, color: color)),
                    const Spacer(),
                    Text(time,
                        style: const TextStyle(
                            fontSize: 11, color: MindraColors.textSecondary)),
                  ]),
                  if (note != null && note.isNotEmpty) ...[
                    const SizedBox(height: 4),
                    Text(note,
                        style: const TextStyle(
                            fontSize: 13, color: MindraColors.textSecondary, height: 1.4),
                        maxLines: 3, overflow: TextOverflow.ellipsis),
                  ],
                  if (tags.isNotEmpty) ...[
                    const SizedBox(height: 6),
                    Wrap(spacing: 4, runSpacing: 4,
                      children: tags.map((t) => Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                        decoration: BoxDecoration(
                          color: color.withValues(alpha: 0.12),
                          borderRadius: BorderRadius.circular(20),
                        ),
                        child: Text(t,
                            style: TextStyle(fontSize: 11, color: color,
                                fontWeight: FontWeight.w600)),
                      )).toList(),
                    ),
                  ],
                ],
              ),
            ),
            IconButton(
              icon: const Icon(Icons.delete_outline, size: 18),
              onPressed: onDelete,
              color: MindraColors.textSecondary,
              padding: EdgeInsets.zero,
              constraints: const BoxConstraints(minWidth: 32, minHeight: 32),
            ),
          ],
        ),
      ),
    );
  }
}

// ─── Sheet para añadir entrada ────────────────────────────────────────────────

class _AddMoodSheet extends StatefulWidget {
  final List<({int score, String emoji, String label, Color color})> moods;
  final VoidCallback onSaved;

  const _AddMoodSheet({required this.moods, required this.onSaved});

  @override
  State<_AddMoodSheet> createState() => _AddMoodSheetState();
}

class _AddMoodSheetState extends State<_AddMoodSheet> {
  int? _selectedScore;
  final _noteCtrl = TextEditingController();
  final _tagCtrl  = TextEditingController();
  final List<String> _tags = [];
  bool _saving = false;

  static const _suggestedTags = [
    'trabajo', 'familia', 'sueño', 'ejercicio',
    'ansiedad', 'estrés', 'gratitud', 'social',
  ];

  @override
  void dispose() {
    _noteCtrl.dispose();
    _tagCtrl.dispose();
    super.dispose();
  }

  Future<void> _save() async {
    if (_selectedScore == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Selecciona cómo te sientes')),
      );
      return;
    }
    setState(() => _saving = true);
    try {
      await context.read<ApiService>().addJournalEntry(
        moodScore: _selectedScore!,
        note: _noteCtrl.text.trim(),
        tags: _tags,
      );
      if (mounted) {
        widget.onSaved();
        Navigator.pop(context);
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error: $e'), backgroundColor: MindraColors.error),
        );
        setState(() => _saving = false);
      }
    }
  }

  void _addTag(String tag) {
    final t = tag.trim().toLowerCase();
    if (t.isNotEmpty && !_tags.contains(t) && _tags.length < 5) {
      setState(() { _tags.add(t); _tagCtrl.clear(); });
    }
  }

  @override
  Widget build(BuildContext context) {
    final selectedMood = _selectedScore != null
        ? widget.moods.firstWhere((m) => m.score == _selectedScore)
        : null;

    return Padding(
      padding: EdgeInsets.only(bottom: MediaQuery.of(context).viewInsets.bottom),
      child: SingleChildScrollView(
        padding: const EdgeInsets.fromLTRB(24, 16, 24, 32),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Handle
            Center(child: Container(
              width: 40, height: 4,
              decoration: BoxDecoration(
                color: MindraColors.darkBorder,
                borderRadius: BorderRadius.circular(2),
              ),
            )),
            const SizedBox(height: 20),

            const Text('¿Cómo te sientes ahora?',
                style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
            const SizedBox(height: 16),

            // Selector de ánimo
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceAround,
              children: widget.moods.map((m) {
                final isSelected = _selectedScore == m.score;
                return GestureDetector(
                  onTap: () => setState(() => _selectedScore = m.score),
                  child: AnimatedContainer(
                    duration: const Duration(milliseconds: 200),
                    padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 12),
                    decoration: BoxDecoration(
                      color: isSelected
                          ? m.color.withValues(alpha: 0.15)
                          : Colors.transparent,
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(
                        color: isSelected ? m.color : Colors.transparent,
                        width: 2,
                      ),
                    ),
                    child: Column(children: [
                      Text(m.emoji, style: TextStyle(
                          fontSize: isSelected ? 32 : 26)),
                      const SizedBox(height: 4),
                      Text(m.label,
                          style: TextStyle(
                              fontSize: 10,
                              fontWeight: FontWeight.w600,
                              color: isSelected ? m.color : MindraColors.textSecondary)),
                    ]),
                  ),
                );
              }).toList(),
            ),

            if (selectedMood != null) ...[
              const SizedBox(height: 4),
              Center(child: Text(
                'Seleccionaste: ${selectedMood.emoji} ${selectedMood.label}',
                style: TextStyle(fontSize: 12, color: selectedMood.color, fontWeight: FontWeight.w600),
              )),
            ],

            const SizedBox(height: 20),

            // Nota
            const Text('Nota (opcional)',
                style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600,
                    color: MindraColors.textSecondary)),
            const SizedBox(height: 8),
            TextField(
              controller: _noteCtrl,
              maxLines: 3,
              maxLength: 500,
              decoration: InputDecoration(
                hintText: '¿Qué está pasando? ¿Qué desencadenó este estado?',
                hintStyle: const TextStyle(
                    color: MindraColors.textSecondary, fontSize: 13),
                filled: true,
                fillColor: MindraColors.dark,
                border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12),
                    borderSide: const BorderSide(color: MindraColors.darkBorder)),
                enabledBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12),
                    borderSide: const BorderSide(color: MindraColors.darkBorder)),
                focusedBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12),
                    borderSide: const BorderSide(color: MindraColors.violet, width: 1.5)),
                counterStyle: const TextStyle(color: MindraColors.textSecondary, fontSize: 11),
              ),
            ),

            const SizedBox(height: 12),

            // Etiquetas sugeridas
            const Text('Etiquetas',
                style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600,
                    color: MindraColors.textSecondary)),
            const SizedBox(height: 8),
            Wrap(spacing: 6, runSpacing: 6,
              children: _suggestedTags.map((tag) {
                final isAdded = _tags.contains(tag);
                return GestureDetector(
                  onTap: () => isAdded
                      ? setState(() => _tags.remove(tag))
                      : _addTag(tag),
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 5),
                    decoration: BoxDecoration(
                      color: isAdded
                          ? MindraColors.violet.withValues(alpha: 0.15)
                          : MindraColors.dark,
                      borderRadius: BorderRadius.circular(20),
                      border: Border.all(
                        color: isAdded ? MindraColors.violet : MindraColors.darkBorder,
                      ),
                    ),
                    child: Text(tag,
                        style: TextStyle(
                            fontSize: 12,
                            fontWeight: FontWeight.w600,
                            color: isAdded ? MindraColors.violet : MindraColors.textSecondary)),
                  ),
                );
              }).toList(),
            ),

            const SizedBox(height: 24),

            // Botón guardar
            SizedBox(
              width: double.infinity,
              child: FilledButton.icon(
                onPressed: _saving ? null : _save,
                style: FilledButton.styleFrom(
                  backgroundColor: MindraColors.violet,
                  padding: const EdgeInsets.symmetric(vertical: 15),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                ),
                icon: _saving
                    ? const SizedBox(width: 18, height: 18,
                        child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                    : const Icon(Icons.check, size: 20),
                label: const Text('Guardar',
                    style: TextStyle(fontSize: 15, fontWeight: FontWeight.w700)),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

// ─── Estado vacío ─────────────────────────────────────────────────────────────

class _EmptyView extends StatelessWidget {
  final VoidCallback onAdd;
  const _EmptyView({required this.onAdd});

  @override
  Widget build(BuildContext context) => Center(
    child: Padding(
      padding: const EdgeInsets.all(40),
      child: Column(mainAxisSize: MainAxisSize.min, children: [
        const Text('📓', style: TextStyle(fontSize: 64)),
        const SizedBox(height: 16),
        const Text('Tu diario está vacío',
            style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
        const SizedBox(height: 8),
        const Text(
          'Registra cómo te sientes cada día para descubrir patrones en tu bienestar emocional.',
          textAlign: TextAlign.center,
          style: TextStyle(color: MindraColors.textSecondary, height: 1.5),
        ),
        const SizedBox(height: 28),
        FilledButton.icon(
          onPressed: onAdd,
          icon: const Icon(Icons.add),
          label: const Text('Primera entrada'),
          style: FilledButton.styleFrom(backgroundColor: MindraColors.violet),
        ),
      ]),
    ),
  );
}

class _ErrorView extends StatelessWidget {
  final String error;
  final VoidCallback onRetry;
  const _ErrorView({required this.error, required this.onRetry});

  @override
  Widget build(BuildContext context) => Center(
    child: Column(mainAxisSize: MainAxisSize.min, children: [
      Text(error, textAlign: TextAlign.center,
          style: const TextStyle(color: MindraColors.textSecondary)),
      const SizedBox(height: 12),
      FilledButton(onPressed: onRetry, child: const Text('Reintentar')),
    ]),
  );
}
