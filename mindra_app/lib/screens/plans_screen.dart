import 'package:flutter/foundation.dart' show kIsWeb;
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:url_launcher/url_launcher.dart';
import '../models/plan.dart';
import '../providers/auth_provider.dart';
import '../providers/plan_provider.dart';
import '../services/api_service.dart';
import '../theme/mindra_theme.dart';
import '../utils/responsive.dart';

class PlansScreen extends StatefulWidget {
  const PlansScreen({super.key});

  @override
  State<PlansScreen> createState() => _PlansScreenState();
}

class _PlansScreenState extends State<PlansScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback(
        (_) => context.read<PlanProvider>().loadPlans());
  }

  // ── Pago con MercadoPago ─────────────────────────────────────────────────

  /// En web muestra un diálogo indicando que el pago solo está disponible
  /// en la app móvil. En móvil abre el checkout de MercadoPago.
  Future<void> _startPayment(Plan plan) async {
    if (plan.isFree) return;

    // Plus es "A medida" — abrir la página web de información/suscripción.
    if (plan.isPlus) {
      final url = Uri.parse('https://mindra.cafined.org/planes/plus');
      if (!await launchUrl(url, mode: LaunchMode.externalApplication)) {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
            content: Text('No se pudo abrir el navegador'),
            backgroundColor: MindraColors.error,
          ));
        }
      }
      return;
    }

    if (kIsWeb) {
      await showDialog(
        context: context,
        builder: (_) => _WebUpgradeDialog(plan: plan),
      );
      return;
    }

    // Pro tiene checkout con MercadoPago.
    await showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: MindraColors.darkSurface,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (_) => _CheckoutSheet(plan: plan),
    );

    // Al cerrar el sheet el usuario puede haber pagado → refrescar plan.
    if (!mounted) return;
    await context.read<AuthProvider>().refreshPlan();
    if (!mounted) return;
    context.read<PlanProvider>().loadPlans();
  }

  @override
  Widget build(BuildContext context) {
    final planProv = context.watch<PlanProvider>();
    final currentSlug =
        context.watch<AuthProvider>().effectivePlan?.slug ?? 'free';

    return Scaffold(
      appBar: AppBar(title: const Text('Planes')),
      body: planProv.loading
          ? const Center(child: CircularProgressIndicator())
          : planProv.error != null
              ? Center(
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Text(planProv.error!),
                      const SizedBox(height: 12),
                      FilledButton(
                          onPressed: planProv.loadPlans,
                          child: const Text('Reintentar')),
                    ],
                  ),
                )
              : WebFrame(
                  child: ListView(
                    padding: const EdgeInsets.all(16),
                    children: [
                      // Banner informativo en versión web
                      if (kIsWeb)
                        _WebOnlyBanner(),
                      const Padding(
                        padding: EdgeInsets.only(bottom: 16),
                        child: Text(
                          'Elige el plan que más se adapte a ti',
                          style: TextStyle(
                              fontSize: 18, fontWeight: FontWeight.bold),
                        ),
                      ),
                      ...planProv.plans.map((p) => _PlanCard(
                            plan: p,
                            isCurrent: p.slug == currentSlug,
                            onUpgrade:
                                p.isFree || p.slug == currentSlug
                                    ? null
                                    : () => _startPayment(p),
                          )),
                      const SizedBox(height: 12),
                      _SecurityFooter(),
                    ],
                  ),
                ),
    );
  }
}

// ─── Tarjeta de plan ─────────────────────────────────────────────────────────

class _PlanCard extends StatelessWidget {
  final Plan plan;
  final bool isCurrent;
  final VoidCallback? onUpgrade;

  const _PlanCard(
      {required this.plan, required this.isCurrent, this.onUpgrade});

  /// Features que se muestran por plan (solo las incluidas, sin ✗).
  static const _featuresBySlug = {
    'free': [
      'Chat con IA (texto)',
      'Detección de ansiedad básica',
      'App móvil + versión web',
    ],
    'pro': [
      'Todo lo del plan Free',
      'Análisis por audio (voz)',
      'Historial completo ilimitado',
      'Recomendaciones personalizadas',
      'Soporte prioritario',
    ],
    'plus': [
      'Todo lo del plan Pro',
      'Análisis facial (detección de emociones)',
      'Estadísticas personales avanzadas',
      'Historial ilimitado completo',
      'Soporte prioritario',
    ],
  };

  Color get _color => plan.isPlus
      ? MindraColors.indigo
      : plan.isPro
          ? MindraColors.violet
          : MindraColors.blue;

  @override
  Widget build(BuildContext context) {
    final features = _featuresBySlug[plan.slug] ?? [];

    return Card(
      margin: const EdgeInsets.only(bottom: 16),
      elevation: isCurrent ? 4 : 1,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(16),
        side: isCurrent
            ? BorderSide(color: _color, width: 2)
            : BorderSide.none,
      ),
      child: Padding(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Nombre + badge "Actual"
            Row(children: [
              Text(plan.name,
                  style: TextStyle(
                      fontSize: 22,
                      fontWeight: FontWeight.bold,
                      color: _color)),
              const Spacer(),
              if (isCurrent)
                Container(
                  padding:
                      const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                  decoration: BoxDecoration(
                      color: _color,
                      borderRadius: BorderRadius.circular(12)),
                  child: const Text('Actual',
                      style: TextStyle(color: Colors.white, fontSize: 12)),
                ),
            ]),
            const SizedBox(height: 4),
            Text(plan.formattedPrice,
                style: TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.w600,
                    color: _color)),
            const SizedBox(height: 6),
            Text(plan.description,
                style: const TextStyle(color: MindraColors.textSecondary)),
            const SizedBox(height: 12),

            // Features (solo las incluidas ✓)
            ...features.map((feat) => Padding(
                  padding: const EdgeInsets.symmetric(vertical: 3),
                  child: Row(children: [
                    Icon(Icons.check_circle,
                        size: 18, color: MindraColors.success),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Text(feat,
                          style: const TextStyle(
                              color: MindraColors.textPrimary)),
                    ),
                  ]),
                )),

            // Botón de upgrade (solo planes de pago no actuales)
            if (onUpgrade != null) ...[
              const SizedBox(height: 16),
              SizedBox(
                width: double.infinity,
                child: FilledButton.icon(
                  style: FilledButton.styleFrom(
                    backgroundColor: _color,
                    padding: const EdgeInsets.symmetric(vertical: 13),
                    shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(10)),
                  ),
                  onPressed: onUpgrade,
                  icon: Icon(plan.isPlus ? Icons.open_in_browser : Icons.payment, size: 18),
                  label: Text(plan.isPlus ? 'Más información' : 'Suscribirse a ${plan.name}'),
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }
}

// ─── Sheet de checkout ────────────────────────────────────────────────────────

class _CheckoutSheet extends StatefulWidget {
  final Plan plan;
  const _CheckoutSheet({required this.plan});

  @override
  State<_CheckoutSheet> createState() => _CheckoutSheetState();
}

class _CheckoutSheetState extends State<_CheckoutSheet> {
  String _period = 'monthly';
  bool _loading = false;
  bool _waitingPayment = false;
  int? _orderId;

  /// Precios en MXN para cada plan y periodo
  static const _prices = {
    'pro':  {'monthly': 149, 'annual': 1430},
    'plus': {'monthly': 199, 'annual': 1910},
  };

  int get _priceMonthly => _prices[widget.plan.slug]?['monthly'] ?? 149;
  int get _priceAnnual  => _prices[widget.plan.slug]?['annual']  ?? 1430;
  int get _price => _period == 'annual' ? _priceAnnual : _priceMonthly;
  String get _periodLabel => _period == 'annual' ? 'año' : 'mes';

  Future<void> _pay() async {
    setState(() => _loading = true);
    try {
      final data = await context.read<ApiService>()
          .createCheckout(widget.plan.slug, _period);
      final url  = Uri.parse(data['checkout_url'] as String);
      _orderId   = data['order_id'] as int;

      if (!await launchUrl(url, mode: LaunchMode.externalApplication)) {
        throw Exception('No se pudo abrir el navegador');
      }

      // Mostrar estado de espera dentro del sheet
      if (mounted) setState(() { _loading = false; _waitingPayment = true; });
    } on ApiException catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(e.message), backgroundColor: MindraColors.error),
        );
        setState(() => _loading = false);
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
              content: Text('Error: $e'),
              backgroundColor: MindraColors.error),
        );
        setState(() => _loading = false);
      }
    }
  }

  Future<void> _verify() async {
    if (_orderId == null) return;
    setState(() => _loading = true);
    try {
      final status = await context.read<ApiService>().checkOrderStatus(_orderId!);
      if (!mounted) return;
      await context.read<AuthProvider>().refreshPlan();
      if (!mounted) return;
      if (status == 'paid') {
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
          content: Text('🎉 ¡Plan activado correctamente!'),
          backgroundColor: MindraColors.success,
        ));
        Navigator.pop(context);
      } else if (status == 'pending') {
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
          content: Text('Tu pago está en proceso. Intenta de nuevo en unos minutos.'),
          backgroundColor: MindraColors.warning,
        ));
        setState(() => _loading = false);
      } else {
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
          content: Text('El pago no fue confirmado. Puedes intentarlo de nuevo.'),
          backgroundColor: MindraColors.error,
        ));
        setState(() { _loading = false; _waitingPayment = false; });
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error: $e'), backgroundColor: MindraColors.error),
        );
        setState(() => _loading = false);
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final color = widget.plan.isPlus ? MindraColors.indigo : MindraColors.violet;

    return Padding(
      padding: EdgeInsets.only(
          bottom: MediaQuery.of(context).viewInsets.bottom),
      child: SingleChildScrollView(
        padding: const EdgeInsets.fromLTRB(24, 20, 24, 32),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Handle
            Center(
              child: Container(
                width: 40, height: 4,
                decoration: BoxDecoration(
                  color: MindraColors.darkBorder,
                  borderRadius: BorderRadius.circular(2),
                ),
              ),
            ),
            const SizedBox(height: 20),

            // Título
            Row(children: [
              Icon(Icons.workspace_premium, color: color, size: 28),
              const SizedBox(width: 12),
              Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                Text('Plan ${widget.plan.name}',
                    style: TextStyle(
                        fontSize: 20,
                        fontWeight: FontWeight.bold,
                        color: color)),
                const Text('Suscripción individual',
                    style: TextStyle(
                        color: MindraColors.textSecondary, fontSize: 13)),
              ]),
            ]),

            const SizedBox(height: 24),

            if (!_waitingPayment) ...[
              // Selector de periodo
              const Text('Periodo de facturación',
                  style: TextStyle(
                      fontWeight: FontWeight.w600, fontSize: 13,
                      color: MindraColors.textSecondary)),
              const SizedBox(height: 10),
              Row(children: [
                Expanded(child: _PeriodOption(
                  label: 'Mensual',
                  price: '\$$_priceMonthly MXN/mes',
                  selected: _period == 'monthly',
                  onTap: () => setState(() => _period = 'monthly'),
                  color: color,
                )),
                const SizedBox(width: 10),
                Expanded(child: _PeriodOption(
                  label: 'Anual',
                  price: '\$$_priceAnnual MXN/año',
                  badge: '-20%',
                  selected: _period == 'annual',
                  onTap: () => setState(() => _period = 'annual'),
                  color: color,
                )),
              ]),

              const SizedBox(height: 20),

              // Precio total
              Container(
                padding: const EdgeInsets.all(14),
                decoration: BoxDecoration(
                  color: color.withValues(alpha: 0.08),
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: color.withValues(alpha: 0.2)),
                ),
                child: Row(children: [
                  Icon(Icons.receipt_long_outlined, color: color, size: 20),
                  const SizedBox(width: 10),
                  Text('Total: ',
                      style: TextStyle(color: color, fontWeight: FontWeight.w600)),
                  Text('\$$_price MXN/$_periodLabel',
                      style: TextStyle(
                          color: color,
                          fontSize: 17,
                          fontWeight: FontWeight.bold)),
                ]),
              ),

              const SizedBox(height: 20),

              // Métodos de pago aceptados
              const Text('Métodos de pago aceptados',
                  style: TextStyle(
                      fontSize: 12, color: MindraColors.textSecondary)),
              const SizedBox(height: 8),
              Wrap(spacing: 6, runSpacing: 6, children: [
                for (final m in ['Visa', 'Mastercard', 'AMEX', 'OXXO', 'SPEI', 'Débito'])
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                    decoration: BoxDecoration(
                      color: MindraColors.dark,
                      borderRadius: BorderRadius.circular(6),
                      border: Border.all(color: MindraColors.darkBorder),
                    ),
                    child: Text(m,
                        style: const TextStyle(
                            fontSize: 12,
                            color: MindraColors.textPrimary,
                            fontWeight: FontWeight.w600)),
                  ),
              ]),

              const SizedBox(height: 24),

              // Botón pagar con MP
              SizedBox(
                width: double.infinity,
                child: FilledButton.icon(
                  onPressed: _loading ? null : _pay,
                  style: FilledButton.styleFrom(
                    backgroundColor: const Color(0xFF009EE3),
                    padding: const EdgeInsets.symmetric(vertical: 15),
                    shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12)),
                  ),
                  icon: _loading
                      ? const SizedBox(
                          width: 18, height: 18,
                          child: CircularProgressIndicator(
                              strokeWidth: 2, color: Colors.white))
                      : const Icon(Icons.open_in_browser, size: 20),
                  label: const Text('Pagar con MercadoPago',
                      style: TextStyle(
                          fontSize: 15, fontWeight: FontWeight.w700)),
                ),
              ),
            ] else ...[
              // ── Estado: esperando confirmación de pago ─────────────────
              Center(
                child: Column(children: [
                  const Icon(Icons.hourglass_top_rounded,
                      size: 60, color: MindraColors.warning),
                  const SizedBox(height: 16),
                  const Text('Esperando confirmación de pago',
                      style: TextStyle(
                          fontSize: 17, fontWeight: FontWeight.bold),
                      textAlign: TextAlign.center),
                  const SizedBox(height: 10),
                  const Text(
                    'Completa el pago en el navegador y luego regresa aquí. '
                    'Toca el botón cuando hayas terminado.',
                    textAlign: TextAlign.center,
                    style: TextStyle(
                        color: MindraColors.textSecondary, fontSize: 14, height: 1.5),
                  ),
                  const SizedBox(height: 28),
                  SizedBox(
                    width: double.infinity,
                    child: FilledButton.icon(
                      onPressed: _loading ? null : _verify,
                      style: FilledButton.styleFrom(
                        backgroundColor: MindraColors.success,
                        padding: const EdgeInsets.symmetric(vertical: 15),
                        shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(12)),
                      ),
                      icon: _loading
                          ? const SizedBox(
                              width: 18, height: 18,
                              child: CircularProgressIndicator(
                                  strokeWidth: 2, color: Colors.white))
                          : const Icon(Icons.check_circle_outline, size: 20),
                      label: const Text('Ya pagué, verificar',
                          style: TextStyle(
                              fontSize: 15, fontWeight: FontWeight.w700)),
                    ),
                  ),
                  const SizedBox(height: 12),
                  TextButton(
                    onPressed: () => setState(() => _waitingPayment = false),
                    child: const Text('Volver a intentar'),
                  ),
                ]),
              ),
            ],

            const SizedBox(height: 16),
            // Badge seguridad
            Row(mainAxisAlignment: MainAxisAlignment.center, children: [
              const Icon(Icons.lock_outline,
                  size: 13, color: MindraColors.textSecondary),
              const SizedBox(width: 4),
              Text(
                'Pago 100% seguro · Cancela cuando quieras',
                style: const TextStyle(
                    fontSize: 11, color: MindraColors.textSecondary),
              ),
            ]),
          ],
        ),
      ),
    );
  }
}

// ─── Selector de periodo ──────────────────────────────────────────────────────

class _PeriodOption extends StatelessWidget {
  final String label;
  final String price;
  final String? badge;
  final bool selected;
  final VoidCallback onTap;
  final Color color;

  const _PeriodOption({
    required this.label,
    required this.price,
    this.badge,
    required this.selected,
    required this.onTap,
    required this.color,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 200),
        padding: const EdgeInsets.all(12),
        decoration: BoxDecoration(
          color: selected ? color.withValues(alpha: 0.1) : MindraColors.dark,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(
              color: selected ? color : MindraColors.darkBorder, width: selected ? 2 : 1),
        ),
        child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Row(children: [
            Text(label,
                style: TextStyle(
                    fontWeight: FontWeight.w600,
                    color: selected ? color : MindraColors.textPrimary)),
            if (badge != null) ...[
              const SizedBox(width: 6),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                decoration: BoxDecoration(
                  color: MindraColors.success.withValues(alpha: 0.15),
                  borderRadius: BorderRadius.circular(4),
                ),
                child: Text(badge!,
                    style: const TextStyle(
                        fontSize: 10,
                        color: MindraColors.success,
                        fontWeight: FontWeight.bold)),
              ),
            ],
          ]),
          const SizedBox(height: 4),
          Text(price,
              style: TextStyle(
                  fontSize: 12,
                  color: selected ? color : MindraColors.textSecondary)),
        ]),
      ),
    );
  }
}

// ─── Footer de seguridad ──────────────────────────────────────────────────────

class _SecurityFooter extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: MindraColors.darkSurface,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: MindraColors.darkBorder),
      ),
      child: const Column(
        children: [
          Row(children: [
            Icon(Icons.shield_outlined, size: 16, color: MindraColors.blue),
            SizedBox(width: 8),
            Text('Pago seguro con MercadoPago',
                style: TextStyle(
                    fontSize: 13,
                    fontWeight: FontWeight.w600,
                    color: MindraColors.textPrimary)),
          ]),
          SizedBox(height: 6),
          Text(
            'Puedes cancelar tu suscripción en cualquier momento desde tu perfil. '
            'No se realizan cobros adicionales sin tu autorización.',
            style: TextStyle(fontSize: 12, color: MindraColors.textSecondary, height: 1.5),
          ),
        ],
      ),
    );
  }
}

// ─── Banner web-only ──────────────────────────────────────────────────────────

class _WebOnlyBanner extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(bottom: 20),
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
      decoration: BoxDecoration(
        color: MindraColors.blue.withValues(alpha: 0.08),
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: MindraColors.blue.withValues(alpha: 0.3)),
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Icon(Icons.smartphone_outlined,
              color: MindraColors.blue, size: 22),
          const SizedBox(width: 12),
          const Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Versión web — Plan Free',
                  style: TextStyle(
                      color: MindraColors.blue,
                      fontWeight: FontWeight.bold,
                      fontSize: 14),
                ),
                SizedBox(height: 4),
                Text(
                  'La versión web de Mindra incluye el plan Free. '
                  'Para acceder a los planes Pro y Plus descarga la app móvil.',
                  style: TextStyle(
                      color: MindraColors.textSecondary,
                      fontSize: 13,
                      height: 1.5),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

// ─── Diálogo de upgrade en web ────────────────────────────────────────────────

class _WebUpgradeDialog extends StatelessWidget {
  final Plan plan;
  const _WebUpgradeDialog({required this.plan});

  Color get _color =>
      plan.isPlus ? MindraColors.indigo : MindraColors.violet;

  @override
  Widget build(BuildContext context) {
    return AlertDialog(
      backgroundColor: MindraColors.darkSurface,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
      contentPadding: const EdgeInsets.all(28),
      content: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Container(
            padding: const EdgeInsets.all(14),
            decoration: BoxDecoration(
              color: _color.withValues(alpha: 0.12),
              shape: BoxShape.circle,
            ),
            child: Icon(Icons.smartphone_outlined, color: _color, size: 32),
          ),
          const SizedBox(height: 18),
          Text(
            'Plan ${plan.name} solo en la app',
            style: const TextStyle(
                fontSize: 18, fontWeight: FontWeight.bold),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 10),
          Text(
            'La suscripción al plan ${plan.name} está disponible '
            'únicamente desde la app móvil de Mindra (iOS / Android / Mac).\n\n'
            'Descarga la app, inicia sesión con tu cuenta y activa tu plan desde la sección Planes.',
            style: const TextStyle(
                color: MindraColors.textSecondary, fontSize: 13, height: 1.6),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 24),
          SizedBox(
            width: double.infinity,
            child: FilledButton(
              onPressed: () async {
                final url = Uri.parse('https://mindra.cafined.org');
                if (await canLaunchUrl(url)) await launchUrl(url);
              },
              style: FilledButton.styleFrom(
                backgroundColor: _color,
                padding: const EdgeInsets.symmetric(vertical: 13),
                shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12)),
              ),
              child: const Text('Más información'),
            ),
          ),
          const SizedBox(height: 8),
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Entendido'),
          ),
        ],
      ),
    );
  }
}
