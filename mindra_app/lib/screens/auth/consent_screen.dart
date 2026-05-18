import 'package:flutter/foundation.dart' show kIsWeb;
import 'package:flutter/material.dart';
import '../../theme/mindra_theme.dart';
import '../../utils/responsive.dart';

/// Pantalla de consentimiento informado que debe aceptarse antes
/// de completar el registro. Recibe los datos del formulario previo
/// y los pasa al callback [onAccepted] si el usuario acepta.
class ConsentScreen extends StatefulWidget {
  final String name;
  final String email;
  final String password;
  /// Se llama cuando el usuario acepta el consentimiento.
  final VoidCallback onAccepted;

  const ConsentScreen({
    super.key,
    required this.name,
    required this.email,
    required this.password,
    required this.onAccepted,
  });

  @override
  State<ConsentScreen> createState() => _ConsentScreenState();
}

class _ConsentScreenState extends State<ConsentScreen> {
  bool _acceptedTerms = false;
  bool _acceptedData = false;
  bool _acceptedResearch = false;

  bool get _canProceed => _acceptedTerms && _acceptedData;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Consentimiento informado'),
      ),
      body: _buildBody(context),
    );
  }

  Widget _buildBody(BuildContext context) {
    final content = Column(
      children: [
        // ── Barra de progreso del registro ──────────────────────────────
        _StepIndicator(step: 2, total: 2),

        // ── Contenido scrollable ────────────────────────────────────────
        Expanded(
          child: SingleChildScrollView(
            padding: const EdgeInsets.fromLTRB(24, 16, 24, 8),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                  // Cabecera
                  Center(
                    child: Container(
                      padding: const EdgeInsets.all(14),
                      decoration: BoxDecoration(
                        gradient: MindraColors.gradientDeep,
                        borderRadius: BorderRadius.circular(18),
                      ),
                      child: const Icon(
                        Icons.shield_outlined,
                        size: 40,
                        color: Colors.white,
                      ),
                    ),
                  ),
                  const SizedBox(height: 16),
                  const Center(
                    child: Text(
                      'Tu privacidad importa',
                      style: TextStyle(
                        fontSize: 22,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ),
                  const SizedBox(height: 6),
                  Center(
                    child: Text(
                      'Lee con atención antes de continuar.',
                      style: TextStyle(
                        color: MindraColors.textSecondary,
                        fontSize: 13,
                      ),
                    ),
                  ),

                  const SizedBox(height: 24),
                  _Section(
                    icon: Icons.psychology_alt_outlined,
                    title: '¿Qué es Mindra?',
                    body:
                        'Mindra es una aplicación de apoyo emocional que usa inteligencia artificial para detectar indicadores de ansiedad a partir de texto y voz. '
                        'No sustituye la atención psicológica o psiquiátrica profesional.',
                  ),
                  _Section(
                    icon: Icons.data_usage_outlined,
                    title: 'Uso de tus datos',
                    body:
                        'Tus mensajes y grabaciones de voz se envían a nuestros servidores para su análisis. '
                        'Los resultados se almacenan asociados a tu cuenta para mostrarte tu historial. '
                        'No compartimos tu información personal con terceros con fines comerciales.',
                  ),
                  _Section(
                    icon: Icons.science_outlined,
                    title: 'Uso con fines de investigación (opcional)',
                    body:
                        'Esta aplicación forma parte de una investigación doctoral sobre detección de ansiedad. '
                        'Si lo autorizas, tus datos anonimizados podrán usarse para mejorar los modelos de IA. '
                        'Tu participación es completamente voluntaria y puedes revocarla en cualquier momento desde tu perfil.',
                  ),
                  _Section(
                    icon: Icons.warning_amber_outlined,
                    title: 'Limitaciones importantes',
                    body:
                        '• Mindra no es un servicio de emergencias.\n'
                        '• Si estás en crisis, contacta a una línea de ayuda o acude al servicio de urgencias más cercano.\n'
                        '• Los resultados de IA son orientativos y no constituyen un diagnóstico clínico.',
                  ),
                  _Section(
                    icon: Icons.lock_outlined,
                    title: 'Tus derechos',
                    body:
                        'Tienes derecho a acceder, rectificar y eliminar tus datos en cualquier momento. '
                        'Puedes solicitar la eliminación de tu cuenta y toda la información asociada desde la sección de Perfil.',
                  ),

                  const SizedBox(height: 24),
                  const Divider(),
                  const SizedBox(height: 8),

                  // ── Checkboxes ──────────────────────────────────────────
                  _ConsentCheckbox(
                    value: _acceptedTerms,
                    onChanged: (v) => setState(() => _acceptedTerms = v ?? false),
                    label: 'He leído y acepto los ',
                    linkText: 'Términos de uso y Política de privacidad.',
                    required: true,
                  ),
                  const SizedBox(height: 12),
                  _ConsentCheckbox(
                    value: _acceptedData,
                    onChanged: (v) => setState(() => _acceptedData = v ?? false),
                    label:
                        'Consiento el procesamiento de mis mensajes y grabaciones de voz '
                        'para los fines descritos anteriormente.',
                    required: true,
                  ),
                  const SizedBox(height: 12),
                  _ConsentCheckbox(
                    value: _acceptedResearch,
                    onChanged: (v) =>
                        setState(() => _acceptedResearch = v ?? false),
                    label:
                        '(Opcional) Autorizo el uso anonimizado de mis datos para investigación científica sobre ansiedad.',
                    required: false,
                  ),

                  const SizedBox(height: 32),
                ],
              ),
            ),
          ),

        // ── Botones fijos en la parte inferior ───────────────────────────
        _BottomBar(
          canProceed: _canProceed,
          onAccept: widget.onAccepted,
          onCancel: () => Navigator.pop(context),
        ),
      ],
    );

    // En web centramos el contenido con ancho máximo preservando la altura completa.
    if (!kIsWeb) return content;
    return Row(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        const Expanded(child: SizedBox()),
        SizedBox(width: kAuthFormMaxWidth, child: content),
        const Expanded(child: SizedBox()),
      ],
    );
  }
}

// ─── Widgets auxiliares ───────────────────────────────────────────────────────

class _StepIndicator extends StatelessWidget {
  final int step;
  final int total;
  const _StepIndicator({required this.step, required this.total});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(24, 12, 24, 0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Paso $step de $total',
            style: const TextStyle(
                fontSize: 12, color: MindraColors.textSecondary),
          ),
          const SizedBox(height: 6),
          ClipRRect(
            borderRadius: BorderRadius.circular(4),
            child: LinearProgressIndicator(
              value: step / total,
              backgroundColor: MindraColors.darkBorder,
              valueColor:
                  const AlwaysStoppedAnimation<Color>(MindraColors.blue),
              minHeight: 5,
            ),
          ),
        ],
      ),
    );
  }
}

class _Section extends StatelessWidget {
  final IconData icon;
  final String title;
  final String body;
  const _Section(
      {required this.icon, required this.title, required this.body});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 20),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            margin: const EdgeInsets.only(top: 2),
            padding: const EdgeInsets.all(8),
            decoration: BoxDecoration(
              color: MindraColors.blue.withValues(alpha: 0.12),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Icon(icon, size: 20, color: MindraColors.blue),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: const TextStyle(
                    fontWeight: FontWeight.w600,
                    fontSize: 15,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  body,
                  style: const TextStyle(
                    fontSize: 13,
                    color: MindraColors.textSecondary,
                    height: 1.55,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _ConsentCheckbox extends StatelessWidget {
  final bool value;
  final ValueChanged<bool?> onChanged;
  final String label;
  final String? linkText;
  final bool required;

  const _ConsentCheckbox({
    required this.value,
    required this.onChanged,
    required this.label,
    this.linkText,
    required this.required,
  });

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: () => onChanged(!value),
      borderRadius: BorderRadius.circular(10),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
        decoration: BoxDecoration(
          color: value
              ? MindraColors.blue.withValues(alpha: 0.08)
              : MindraColors.darkSurface,
          borderRadius: BorderRadius.circular(10),
          border: Border.all(
            color: value ? MindraColors.blue : MindraColors.darkBorder,
          ),
        ),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            SizedBox(
              width: 24,
              height: 24,
              child: Checkbox(
                value: value,
                onChanged: onChanged,
                activeColor: MindraColors.blue,
                side: const BorderSide(color: MindraColors.textSecondary),
                materialTapTargetSize: MaterialTapTargetSize.shrinkWrap,
              ),
            ),
            const SizedBox(width: 10),
            Expanded(
              child: RichText(
                text: TextSpan(
                  style: const TextStyle(
                    fontSize: 13,
                    color: MindraColors.textPrimary,
                    height: 1.5,
                  ),
                  children: [
                    TextSpan(text: label),
                    if (linkText != null)
                      TextSpan(
                        text: linkText,
                        style: const TextStyle(
                          color: MindraColors.blue,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                    if (required)
                      const TextSpan(
                        text: ' *',
                        style: TextStyle(color: MindraColors.error),
                      ),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _BottomBar extends StatelessWidget {
  final bool canProceed;
  final VoidCallback onAccept;
  final VoidCallback onCancel;

  const _BottomBar({
    required this.canProceed,
    required this.onAccept,
    required this.onCancel,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.fromLTRB(24, 12, 24, 24),
      decoration: const BoxDecoration(
        color: MindraColors.darkSurface,
        border: Border(top: BorderSide(color: MindraColors.darkBorder)),
      ),
      child: SafeArea(
        top: false,
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            if (!canProceed)
              Padding(
                padding: const EdgeInsets.only(bottom: 8),
                child: Text(
                  'Debes aceptar los campos obligatorios (*) para continuar.',
                  style: TextStyle(
                    fontSize: 12,
                    color: MindraColors.warning,
                  ),
                  textAlign: TextAlign.center,
                ),
              ),
            FilledButton(
              onPressed: canProceed ? onAccept : null,
              style: FilledButton.styleFrom(
                padding: const EdgeInsets.symmetric(vertical: 15),
              ),
              child: const Text(
                'Acepto y crear mi cuenta',
                style: TextStyle(fontSize: 15, fontWeight: FontWeight.w600),
              ),
            ),
            const SizedBox(height: 8),
            OutlinedButton(
              onPressed: onCancel,
              style: OutlinedButton.styleFrom(
                padding: const EdgeInsets.symmetric(vertical: 14),
              ),
              child: const Text('Volver al formulario'),
            ),
          ],
        ),
      ),
    );
  }
}
