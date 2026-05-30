import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:permission_handler/permission_handler.dart';
import 'package:provider/provider.dart' show Consumer, ReadContext, WatchContext;
import 'package:url_launcher/url_launcher.dart';
import '../providers/auth_provider.dart';
import '../providers/theme_provider.dart';
import '../services/api_service.dart' show ApiService, ApiException;
import '../services/notification_service.dart';
import '../theme/mindra_theme.dart';
import '../utils/responsive.dart';
import 'plans_screen.dart';
import 'assessment_screen.dart';

class ProfileScreen extends StatefulWidget {
  const ProfileScreen({super.key});

  @override
  State<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends State<ProfileScreen> {
  int _reminderHour = -1;

  @override
  void initState() {
    super.initState();
    _loadReminderHour();
  }

  Future<void> _loadReminderHour() async {
    final h = await context.read<NotificationService>().getReminderHour();
    if (mounted) setState(() => _reminderHour = h);
  }

  Future<void> _setReminder(int hour) async {
    if (hour >= 0) {
      // Solicitar permiso de notificaciones (Android 13+ / iOS)
      final status = await Permission.notification.request();
      if (!mounted) return;
      if (status.isDenied || status.isPermanentlyDenied) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: const Text(
                'Activa los permisos de notificación en Ajustes para recibir recordatorios.'),
            action: SnackBarAction(
              label: 'Abrir',
              onPressed: openAppSettings,
            ),
          ),
        );
        return;
      }
    }
    await context.read<NotificationService>().scheduleDaily(hour);
    if (mounted) setState(() => _reminderHour = hour);
  }

  void _showReminderPicker() {
    showModalBottomSheet(
      context: context,
      backgroundColor: Theme.of(context).colorScheme.surface,
      shape: const RoundedRectangleBorder(
          borderRadius: BorderRadius.vertical(top: Radius.circular(20))),
      builder: (_) => Padding(
        padding: const EdgeInsets.all(24),
        child: Column(mainAxisSize: MainAxisSize.min, children: [
          const Text('Recordatorio diario',
              style: TextStyle(fontSize: 17, fontWeight: FontWeight.bold)),
          const SizedBox(height: 6),
          const Text('¿A qué hora quieres que Mindra te recuerde hacer tu check-in?',
              textAlign: TextAlign.center,
              style: TextStyle(color: MindraColors.textSecondary, fontSize: 13)),
          const SizedBox(height: 16),
          Wrap(
            spacing: 8,
            runSpacing: 8,
            children: [
              for (final h in [7, 8, 9, 10, 12, 15, 18, 20, 21, 22])
                ChoiceChip(
                  label: Text('${h.toString().padLeft(2, '0')}:00'),
                  selected: _reminderHour == h,
                  onSelected: (_) {
                    Navigator.pop(context);
                    _setReminder(h);
                  },
                ),
            ],
          ),
          const SizedBox(height: 12),
          if (_reminderHour >= 0)
            TextButton.icon(
              icon: const Icon(Icons.notifications_off_outlined, color: Colors.red),
              label: const Text('Desactivar recordatorio',
                  style: TextStyle(color: Colors.red)),
              onPressed: () {
                Navigator.pop(context);
                _setReminder(-1);
              },
            ),
        ]),
      ),
    );
  }

  Future<void> _showTherapistShare(BuildContext ctx) async {
    // Mostrar loader
    showDialog(
        context: ctx,
        barrierDismissible: false,
        builder: (_) => const Center(child: CircularProgressIndicator()));
    try {
      final data = await ctx.read<ApiService>().generateTherapistShare();
      if (!mounted || !ctx.mounted) return;
      Navigator.pop(ctx); // cerrar loader
      final url     = data['url'] as String? ?? '';
      final expires = data['expires_at'] as String? ?? '';
      showModalBottomSheet(
        context: ctx,
        backgroundColor: Theme.of(context).colorScheme.surface,
        shape: const RoundedRectangleBorder(
            borderRadius: BorderRadius.vertical(top: Radius.circular(20))),
        builder: (_) => Padding(
          padding: const EdgeInsets.all(24),
          child: Column(mainAxisSize: MainAxisSize.min, children: [
            const Icon(Icons.link, size: 40, color: MindraColors.violet),
            const SizedBox(height: 12),
            const Text('Enlace para terapeuta',
                style: TextStyle(fontSize: 17, fontWeight: FontWeight.bold)),
            const SizedBox(height: 6),
            Text('Válido por 7 días · Expira: ${expires.split('T').first}',
                style: const TextStyle(
                    color: MindraColors.textSecondary, fontSize: 12)),
            const SizedBox(height: 16),
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: MindraColors.dark,
                borderRadius: BorderRadius.circular(10),
              ),
              child: SelectableText(url,
                  style: const TextStyle(fontSize: 12, color: MindraColors.blue)),
            ),
            const SizedBox(height: 12),
            FilledButton.icon(
              onPressed: () {
                Clipboard.setData(ClipboardData(text: url));
                ScaffoldMessenger.of(ctx).showSnackBar(
                    const SnackBar(content: Text('✅ Enlace copiado')));
                Navigator.pop(ctx);
              },
              icon: const Icon(Icons.copy, size: 16),
              label: const Text('Copiar enlace'),
              style: FilledButton.styleFrom(
                  backgroundColor: MindraColors.violet,
                  minimumSize: const Size.fromHeight(44)),
            ),
          ]),
        ),
      );
    } on ApiException catch (e) {
      if (!mounted || !ctx.mounted) return;
      Navigator.pop(ctx);
      ScaffoldMessenger.of(ctx).showSnackBar(
          SnackBar(content: Text(e.message), backgroundColor: Colors.red));
    }
  }

  @override
  Widget build(BuildContext context) {
    final auth     = context.watch<AuthProvider>();
    final user     = auth.user;
    final plan     = auth.effectivePlan;
    final planName = plan?.name ?? 'Free';
    final isDark   = Theme.of(context).brightness == Brightness.dark;

    // Color decorativo (fondos, bordes, iconos sobre su propio tint)
    final planColor = plan?.isPlus == true
        ? MindraColors.indigo
        : plan?.isPro == true
            ? MindraColors.violet
            : MindraColors.blue;
    // Color accesible para texto/iconos sobre fondos del tema (WCAG AA)
    final planTextCol = MindraColors.planTextColor(planColor, isDark: isDark);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Mi perfil'),
      ),
      body: WebFrame(
        maxWidth: 600,
        child: ListView(
          padding: const EdgeInsets.all(24),
          children: [
          const SizedBox(height: 4),
          Container(
            padding: const EdgeInsets.symmetric(vertical: 24, horizontal: 20),
            decoration: BoxDecoration(
              gradient: LinearGradient(
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
                colors: [
                  planColor.withValues(alpha: 0.14),
                  Theme.of(context).colorScheme.surface,
                ],
              ),
              borderRadius: BorderRadius.circular(20),
              border: Border.all(color: planColor.withValues(alpha: 0.2)),
            ),
            child: Column(
              children: [
                CircleAvatar(
                  radius: 38,
                  backgroundColor: planColor.withValues(alpha: 0.18),
                  child: Icon(Icons.person, size: 44, color: planTextCol),
                ),
                const SizedBox(height: 12),
                if (user != null) ...[
                  Text(user.name,
                      style: const TextStyle(
                          fontSize: 20, fontWeight: FontWeight.bold)),
                  const SizedBox(height: 3),
                  Text(user.email,
                      style: TextStyle(
                          color: Theme.of(context).colorScheme.onSurface.withValues(alpha: 0.6),
                          fontSize: 13)),
                ],
              ],
            ),
          ),
          const SizedBox(height: 16),
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: planColor.withValues(alpha: 0.07),
              borderRadius: BorderRadius.circular(14),
              border: Border.all(color: planColor.withValues(alpha: 0.25)),
            ),
            child: Row(children: [
              Icon(Icons.workspace_premium, color: planTextCol, size: 28),
              const SizedBox(width: 14),
              Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                Text('Plan actual',
                    style: TextStyle(color: planTextCol, fontSize: 12)),
                Text(planName,
                    style: TextStyle(
                        color: planTextCol,
                        fontSize: 20,
                        fontWeight: FontWeight.bold)),
              ]),
              const Spacer(),
              TextButton(
                onPressed: () => Navigator.push(context,
                    MaterialPageRoute(builder: (_) => const PlansScreen())),
                child: const Text('Cambiar'),
              ),
            ]),
          ),
          const SizedBox(height: 12),
          // ── Contrato del plan actual ──────────────────────────────────────
          ListTile(
            contentPadding: EdgeInsets.zero,
            leading: Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: planColor.withValues(alpha: 0.12),
                shape: BoxShape.circle,
              ),
              child: Icon(Icons.description_outlined, color: planTextCol),
            ),
            title: const Text('Ver contrato'),
            subtitle: Text('Términos del plan $planName',
                style: TextStyle(
                    fontSize: 12,
                    color: Theme.of(context).colorScheme.onSurface.withValues(alpha: 0.6))),
            trailing: const Icon(Icons.open_in_new, size: 16),
            onTap: () async {
              final slug = plan?.slug ?? 'free';
              final contractSlug = switch (slug) {
                'free' => 'free',
                'pro'  => 'pro',
                _      => 'plus',
              };
              final url = Uri.parse(
                  'https://mindra.cafined.org/contratos/$contractSlug');
              try {
                await launchUrl(url, mode: LaunchMode.externalApplication);
              } catch (_) {
                if (!mounted || !context.mounted) return;
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(content: Text('No se pudo abrir el contrato')));
              }
            },
          ),
          const SizedBox(height: 24),

          // ── Tema de la app ────────────────────────────────────────────────
          Consumer<ThemeProvider>(
            builder: (ctx, themeProvider, _) {
              final themeIsDark = themeProvider.isDark;
              final accentCol   = MindraColors.planTextColor(MindraColors.blue, isDark: themeIsDark);
              return ListTile(
                contentPadding: EdgeInsets.zero,
                leading: Container(
                  padding: const EdgeInsets.all(8),
                  decoration: BoxDecoration(
                    color: accentCol.withValues(alpha: 0.12),
                    shape: BoxShape.circle,
                  ),
                  child: Icon(
                    themeIsDark ? Icons.dark_mode_outlined : Icons.light_mode_outlined,
                    color: accentCol,
                  ),
                ),
                title: const Text('Tema de la app'),
                subtitle: Text(
                  themeIsDark ? 'Modo oscuro activo' : 'Modo claro activo',
                  style: TextStyle(
                    fontSize: 12,
                    color: Theme.of(context).colorScheme.onSurface.withValues(alpha: 0.6),
                  ),
                ),
                trailing: Switch(
                  value: themeIsDark,
                  activeThumbColor: accentCol,
                  activeTrackColor: accentCol.withValues(alpha: 0.3),
                  onChanged: (_) => themeProvider.toggle(),
                ),
              );
            },
          ),

          // ── Recordatorio diario ───────────────────────────────────────────
          ListTile(
            contentPadding: EdgeInsets.zero,
            leading: Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: MindraColors.blue.withValues(alpha: 0.12),
                shape: BoxShape.circle,
              ),
              child: const Icon(Icons.notifications_outlined,
                  color: MindraColors.blue),
            ),
            title: const Text('Recordatorio diario'),
            subtitle: Text(
              _reminderHour >= 0
                  ? 'Activado a las ${_reminderHour.toString().padLeft(2, '0')}:00'
                  : 'Desactivado',
              style: TextStyle(
                  fontSize: 12,
                  color: Theme.of(context).colorScheme.onSurface.withValues(alpha: 0.55)),
            ),
            trailing: const Icon(Icons.chevron_right),
            onTap: _showReminderPicker,
          ),
          const SizedBox(height: 16),

          // ── Evaluación GAD-7 ──────────────────────────────────────────
          ListTile(
            contentPadding: EdgeInsets.zero,
            leading: Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: const Color(0xFFdc2626).withValues(alpha: 0.12),
                shape: BoxShape.circle,
              ),
              child: const Icon(Icons.psychology_outlined,
                  color: Color(0xFFdc2626)),
            ),
            title: const Text('Evaluación GAD-7'),
            subtitle: const Text('Mide tu nivel de ansiedad en 7 preguntas',
                style: TextStyle(
                    fontSize: 12, color: MindraColors.textSecondary)),
            trailing: const Icon(Icons.chevron_right),
            onTap: () => Navigator.push(context,
                MaterialPageRoute(builder: (_) => const AssessmentScreen())),
          ),

          // ── Compartir con terapeuta ───────────────────────────────────
          ListTile(
            contentPadding: EdgeInsets.zero,
            leading: Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: MindraColors.violet.withValues(alpha: 0.12),
                shape: BoxShape.circle,
              ),
              child: const Icon(Icons.share_outlined, color: MindraColors.violet),
            ),
            title: const Text('Compartir con terapeuta'),
            subtitle: const Text(
                'Genera un enlace de solo lectura (7 días)',
                style: TextStyle(
                    fontSize: 12, color: MindraColors.textSecondary)),
            trailing: const Icon(Icons.chevron_right),
            onTap: () => _showTherapistShare(context),
          ),

          const SizedBox(height: 16),
          OutlinedButton.icon(
            icon: const Icon(Icons.logout, color: Colors.red),
            label: const Text('Cerrar sesión',
                style: TextStyle(color: Colors.red, fontSize: 15)),
            style: OutlinedButton.styleFrom(
              side: const BorderSide(color: Colors.red),
              padding: const EdgeInsets.symmetric(vertical: 14),
              shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12)),
            ),
            onPressed: () => context.read<AuthProvider>().logout(),
          ),
        ],
      ),
      ),
    );
  }
}
