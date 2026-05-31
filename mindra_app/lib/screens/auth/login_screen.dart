import 'package:flutter/foundation.dart' show kIsWeb;
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../services/api_service.dart';
import '../../services/biometric_service.dart';
import '../../theme/mindra_theme.dart';
import '../../utils/responsive.dart';
import 'register_screen.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _formKey = GlobalKey<FormState>();
  final _emailCtrl = TextEditingController();
  final _passCtrl = TextEditingController();
  bool _loading = false;
  bool _obscure = true;
  bool _bioAvailable = false;
  bool _bioEnabled = false;

  @override
  void initState() {
    super.initState();
    _checkBiometrics();
  }

  Future<void> _checkBiometrics() async {
    if (kIsWeb) return;
    final available = await BiometricService.isAvailable();
    final enabled   = await BiometricService.isEnabled();
    if (mounted) setState(() { _bioAvailable = available; _bioEnabled = enabled; });
    // Auto-login biométrico si está habilitado
    if (available && enabled) {
      _tryBiometricLogin();
    }
  }

  Future<void> _tryBiometricLogin() async {
    final ok = await BiometricService.authenticate();
    if (!ok || !mounted) return;
    final token = await BiometricService.getSavedToken();
    if (token == null || !mounted) return;
    setState(() => _loading = true);
    try {
      await context.read<AuthProvider>().loginWithToken(token);
    } catch (_) {
      // Token expirado — limpiar biometría guardada
      await BiometricService.clearSavedToken();
      if (mounted) setState(() { _bioEnabled = false; _loading = false; });
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  void dispose() {
    _emailCtrl.dispose();
    _passCtrl.dispose();
    super.dispose();
  }

  Future<void> _login() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _loading = true);
    try {
      await context.read<AuthProvider>().login(
            _emailCtrl.text.trim(),
            _passCtrl.text,
          );
      // Ofrecer activar biometría después del login exitoso
      if (mounted && _bioAvailable && !_bioEnabled) {
        _offerBiometricSetup();
      }
    } on ApiException catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(e.message), backgroundColor: Colors.red),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
              content: Text('Error de conexión: $e'),
              backgroundColor: Colors.red),
        );
      }
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  void _offerBiometricSetup() {
    final token = context.read<AuthProvider>().currentToken;
    if (token == null) return;
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: const Text('¿Activar acceso con huella / Face ID?'),
        duration: const Duration(seconds: 6),
        action: SnackBarAction(
          label: 'Activar',
          onPressed: () async {
            final ok = await BiometricService.authenticate();
            if (ok) {
              await BiometricService.saveTokenForBio(token);
              await BiometricService.setEnabled(true);
              if (mounted) setState(() => _bioEnabled = true);
            }
          },
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return kIsWeb ? _buildWeb(context) : _buildMobile(context);
  }

  // ── Versión web: fondo con gradiente, formulario centrado en tarjeta ─────────
  Widget _buildWeb(BuildContext context) {
    return Scaffold(
      body: Container(
        width: double.infinity,
        height: double.infinity,
        decoration: const BoxDecoration(gradient: MindraColors.gradientDeep),
        child: Center(
          child: SingleChildScrollView(
            padding: const EdgeInsets.all(24),
            child: ConstrainedBox(
              constraints:
                  const BoxConstraints(maxWidth: kAuthFormMaxWidth),
              child: Card(
                elevation: 8,
                shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(24)),
                color: MindraColors.darkSurface,
                child: Padding(
                  padding: const EdgeInsets.fromLTRB(32, 40, 32, 36),
                  child: _formContent(context),
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }

  // ── Versión móvil: scroll normal ─────────────────────────────────────────────
  Widget _buildMobile(BuildContext context) {
    return Scaffold(
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(28),
          child: _formContent(context),
        ),
      ),
    );
  }

  Widget _formContent(BuildContext context) {
    return Form(
      key: _formKey,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          const SizedBox(height: 8),
          Center(
            child: Image.asset('assets/icons/mindra1.png',
                width: 96, height: 96, fit: BoxFit.contain),
          ),
          const SizedBox(height: 20),
          const Text(
            'Mindra',
            textAlign: TextAlign.center,
            style: TextStyle(
                fontSize: 36,
                fontWeight: FontWeight.bold,
                color: MindraColors.blue),
          ),
          const Text(
            'Tu espacio seguro para hablar',
            textAlign: TextAlign.center,
            style:
                TextStyle(color: MindraColors.textSecondary, fontSize: 15),
          ),
          const SizedBox(height: 40),
          TextFormField(
            controller: _emailCtrl,
            keyboardType: TextInputType.emailAddress,
            decoration: const InputDecoration(
              labelText: 'Correo electrónico',
              border: OutlineInputBorder(),
              prefixIcon: Icon(Icons.email_outlined),
            ),
            validator: (v) =>
                v == null || !v.contains('@') ? 'Correo inválido' : null,
          ),
          const SizedBox(height: 16),
          TextFormField(
            controller: _passCtrl,
            obscureText: _obscure,
            decoration: InputDecoration(
              labelText: 'Contraseña',
              border: const OutlineInputBorder(),
              prefixIcon: const Icon(Icons.lock_outlined),
              suffixIcon: IconButton(
                icon:
                    Icon(_obscure ? Icons.visibility : Icons.visibility_off),
                onPressed: () => setState(() => _obscure = !_obscure),
              ),
            ),
            validator: (v) =>
                v == null || v.length < 6 ? 'Mínimo 6 caracteres' : null,
          ),
          const SizedBox(height: 28),
          FilledButton(
            style: FilledButton.styleFrom(
              padding: const EdgeInsets.symmetric(vertical: 16),
            ),
            onPressed: _loading ? null : _login,
            child: _loading
                ? const SizedBox(
                    height: 20,
                    width: 20,
                    child: CircularProgressIndicator(
                        strokeWidth: 2, color: Colors.white))
                : const Text('Iniciar sesión',
                    style: TextStyle(fontSize: 16)),
          ),
          const SizedBox(height: 16),
          if (!kIsWeb && _bioAvailable && _bioEnabled)
            OutlinedButton.icon(
              icon: const Icon(Icons.fingerprint),
              label: const Text('Entrar con huella / Face ID'),
              style: OutlinedButton.styleFrom(
                padding: const EdgeInsets.symmetric(vertical: 14),
              ),
              onPressed: _loading ? null : _tryBiometricLogin,
            ),
          if (!kIsWeb && _bioAvailable && _bioEnabled)
            const SizedBox(height: 8),
          TextButton(
            onPressed: () => Navigator.push(context,
                MaterialPageRoute(builder: (_) => const RegisterScreen())),
            child: const Text('¿No tienes cuenta? Regístrate aquí'),
          ),
        ],
      ),
    );
  }
}
