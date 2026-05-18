import 'package:flutter/foundation.dart' show kIsWeb;
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../services/api_service.dart';
import '../../theme/mindra_theme.dart';
import '../../utils/responsive.dart';
import 'consent_screen.dart';

class RegisterScreen extends StatefulWidget {
  const RegisterScreen({super.key});

  @override
  State<RegisterScreen> createState() => _RegisterScreenState();
}

class _RegisterScreenState extends State<RegisterScreen> {
  final _formKey = GlobalKey<FormState>();
  final _nameCtrl = TextEditingController();
  final _emailCtrl = TextEditingController();
  final _passCtrl = TextEditingController();
  final _confirmCtrl = TextEditingController();
  bool _loading = false;
  bool _obscure = true;

  @override
  void dispose() {
    _nameCtrl.dispose();
    _emailCtrl.dispose();
    _passCtrl.dispose();
    _confirmCtrl.dispose();
    super.dispose();
  }

  /// Valida el formulario y navega al consentimiento.
  /// El registro real ocurre solo cuando el usuario acepta el consentimiento.
  void _goToConsent() {
    if (!_formKey.currentState!.validate()) return;

    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (_) => ConsentScreen(
          name: _nameCtrl.text.trim(),
          email: _emailCtrl.text.trim(),
          password: _passCtrl.text,
          onAccepted: _doRegister,
        ),
      ),
    );
  }

  /// Llamado desde ConsentScreen una vez que el usuario acepta.
  Future<void> _doRegister() async {
    // Cierra la pantalla de consentimiento
    if (mounted) Navigator.pop(context);

    setState(() => _loading = true);
    try {
      await context.read<AuthProvider>().register(
            _nameCtrl.text.trim(),
            _emailCtrl.text.trim(),
            _passCtrl.text,
          );
      // AuthProvider cambia el estado → el router de main.dart
      // redirige automáticamente al shell principal.
    } on ApiException catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(e.message),
            backgroundColor: MindraColors.error,
          ),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Error de conexión: $e'),
            backgroundColor: MindraColors.error,
          ),
        );
      }
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Crear cuenta'),
      ),
      body: kIsWeb
          ? Container(
              width: double.infinity,
              height: double.infinity,
              decoration:
                  const BoxDecoration(gradient: MindraColors.gradientDeep),
              child: Center(
                child: SingleChildScrollView(
                  padding: const EdgeInsets.symmetric(vertical: 32, horizontal: 24),
                  child: ConstrainedBox(
                    constraints:
                        const BoxConstraints(maxWidth: kAuthFormMaxWidth),
                    child: Card(
                      elevation: 8,
                      shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(24)),
                      color: MindraColors.darkSurface,
                      child: Padding(
                        padding: const EdgeInsets.fromLTRB(32, 32, 32, 28),
                        child: _formBody(),
                      ),
                    ),
                  ),
                ),
              ),
            )
          : SingleChildScrollView(
              padding: const EdgeInsets.all(24),
              child: _formBody(),
            ),
    );
  }

  Widget _formBody() {
    return Form(
      key: _formKey,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
              // ── Indicador de paso ─────────────────────────────────────
              _StepHeader(),

              const SizedBox(height: 24),

              // ── Nombre ────────────────────────────────────────────────
              TextFormField(
                controller: _nameCtrl,
                textCapitalization: TextCapitalization.words,
                decoration: const InputDecoration(
                  labelText: 'Nombre completo',
                  prefixIcon: Icon(Icons.person_outline),
                ),
                validator: (v) =>
                    v == null || v.trim().isEmpty ? 'Ingresa tu nombre' : null,
              ),
              const SizedBox(height: 16),

              // ── Correo ────────────────────────────────────────────────
              TextFormField(
                controller: _emailCtrl,
                keyboardType: TextInputType.emailAddress,
                decoration: const InputDecoration(
                  labelText: 'Correo electrónico',
                  prefixIcon: Icon(Icons.email_outlined),
                ),
                validator: (v) =>
                    v == null || !v.contains('@') ? 'Correo inválido' : null,
              ),
              const SizedBox(height: 16),

              // ── Contraseña ────────────────────────────────────────────
              TextFormField(
                controller: _passCtrl,
                obscureText: _obscure,
                decoration: InputDecoration(
                  labelText: 'Contraseña',
                  prefixIcon: const Icon(Icons.lock_outlined),
                  suffixIcon: IconButton(
                    icon: Icon(
                        _obscure ? Icons.visibility : Icons.visibility_off),
                    onPressed: () => setState(() => _obscure = !_obscure),
                  ),
                ),
                validator: (v) =>
                    v == null || v.length < 6 ? 'Mínimo 6 caracteres' : null,
              ),
              const SizedBox(height: 16),

              // ── Confirmar contraseña ──────────────────────────────────
              TextFormField(
                controller: _confirmCtrl,
                obscureText: _obscure,
                decoration: const InputDecoration(
                  labelText: 'Confirmar contraseña',
                  prefixIcon: Icon(Icons.lock_outlined),
                ),
                validator: (v) => v != _passCtrl.text
                    ? 'Las contraseñas no coinciden'
                    : null,
              ),

              const SizedBox(height: 32),

              // ── Botón continuar (→ consentimiento) ────────────────────
              FilledButton.icon(
                icon: _loading
                    ? const SizedBox(
                        height: 18,
                        width: 18,
                        child: CircularProgressIndicator(
                            strokeWidth: 2, color: Colors.white),
                      )
                    : const Icon(Icons.arrow_forward_rounded),
                label: const Text('Continuar',
                    style: TextStyle(fontSize: 16)),
                style: FilledButton.styleFrom(
                  padding: const EdgeInsets.symmetric(vertical: 15),
                ),
                onPressed: _loading ? null : _goToConsent,
              ),

              const SizedBox(height: 12),
              TextButton(
                onPressed: () => Navigator.pop(context),
                child: const Text('¿Ya tienes cuenta? Inicia sesión'),
              ),
            ],
          ),
        );
  }
}

/// Cabecera que indica que este es el paso 1 de 2.
class _StepHeader extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'Paso 1 de 2',
          style: TextStyle(fontSize: 12, color: MindraColors.textSecondary),
        ),
        const SizedBox(height: 6),
        ClipRRect(
          borderRadius: BorderRadius.circular(4),
          child: const LinearProgressIndicator(
            value: 0.5,
            backgroundColor: MindraColors.darkBorder,
            valueColor:
                AlwaysStoppedAnimation<Color>(MindraColors.blue),
            minHeight: 5,
          ),
        ),
        const SizedBox(height: 16),
        const Text(
          'Tus datos de acceso',
          style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
        ),
        const SizedBox(height: 4),
        const Text(
          'Luego revisarás el consentimiento informado.',
          style: TextStyle(
              fontSize: 13, color: MindraColors.textSecondary),
        ),
      ],
    );
  }
}
