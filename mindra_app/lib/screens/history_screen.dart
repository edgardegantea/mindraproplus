import 'package:flutter/foundation.dart' show kIsWeb;
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../models/inference_result.dart';
import '../providers/auth_provider.dart';
import '../services/api_service.dart';
import '../theme/mindra_theme.dart';
import '../utils/responsive.dart';
import 'plans_screen.dart';

class HistoryScreen extends StatefulWidget {
  const HistoryScreen({super.key});

  @override
  State<HistoryScreen> createState() => _HistoryScreenState();
}

class _HistoryScreenState extends State<HistoryScreen> {
  List<InferenceResult>? _records;
  bool _loading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    final plan = context.read<AuthProvider>().effectivePlan;
    if (plan == null || !plan.hasFeature('historial')) {
      setState(() { _loading = false; _error = 'upgrade'; });
      return;
    }
    try {
      final records = await context.read<ApiService>().getHistory();
      if (mounted) setState(() { _records = records; _loading = false; });
    } on ApiException catch (e) {
      if (mounted) setState(() { _error = e.message; _loading = false; });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Historial'),
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : _error == 'upgrade'
              ? _UpgradeWall()
              : _error != null
                  ? Center(child: Text(_error!))
                  : (_records == null || _records!.isEmpty)
                      ? const Center(
                          child: Text('Aún no tienes sesiones registradas.'))
                      : WebFrame(
                          child: ListView.separated(
                            padding: const EdgeInsets.all(16),
                            itemCount: _records!.length,
                            separatorBuilder: (context, index) => const Divider(),
                            itemBuilder: (context, i) => _RecordTile(_records![i]),
                          ),
                        ),
    );
  }
}

class _UpgradeWall extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(
              kIsWeb ? Icons.smartphone_outlined : Icons.lock_outline,
              size: 64,
              color: MindraColors.blue,
            ),
            const SizedBox(height: 16),
            Text(
              kIsWeb
                  ? 'El historial está disponible en la app móvil con los planes Pro y Plus.'
                  : 'El historial está disponible en los planes Pro y Plus.',
              textAlign: TextAlign.center,
              style: const TextStyle(fontSize: 16),
            ),
            const SizedBox(height: 24),
            FilledButton(
              onPressed: () => Navigator.push(context,
                  MaterialPageRoute(builder: (_) => const PlansScreen())),
              child: Text(kIsWeb ? 'Ver planes' : 'Ver planes'),
            ),
          ],
        ),
      ),
    );
  }
}

class _RecordTile extends StatelessWidget {
  final InferenceResult r;
  const _RecordTile(this.r);

  @override
  Widget build(BuildContext context) {
    final date = r.createdAt;
    final dateStr = date != null
        ? '${date.day}/${date.month}/${date.year} ${date.hour}:${date.minute.toString().padLeft(2, '0')}'
        : '';
    final etiqueta = r.etiqueta ?? '';
    final labelColor =
        etiqueta.toLowerCase().contains('ansiedad') ? MindraColors.warning : MindraColors.blue;

    return ListTile(
      contentPadding: EdgeInsets.zero,
      title: Text(
        r.botResponse.isNotEmpty ? r.botResponse : '(Sin respuesta)',
        maxLines: 2,
        overflow: TextOverflow.ellipsis,
      ),
      subtitle: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          if (etiqueta.isNotEmpty)
            Container(
              margin: const EdgeInsets.only(top: 4),
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
              decoration: BoxDecoration(
                color: labelColor.withValues(alpha: 0.1),
                borderRadius: BorderRadius.circular(8),
                border: Border.all(color: labelColor.withValues(alpha: 0.4)),
              ),
              child: Text(etiqueta,
                  style: TextStyle(color: labelColor, fontSize: 12)),
            ),
          if (r.emotionLabel != null)
            Padding(
              padding: const EdgeInsets.only(top: 4),
              child: Text('Emoción: ${r.emotionLabel}',
                  style: const TextStyle(fontSize: 12, color: MindraColors.textSecondary)),
            ),
          if (dateStr.isNotEmpty)
            Text(dateStr,
                style: const TextStyle(fontSize: 11, color: MindraColors.textSecondary)),
        ],
      ),
    );
  }
}
