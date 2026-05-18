import 'package:flutter/material.dart';
import '../theme/mindra_theme.dart';
import 'auth/login_screen.dart';

/// Splash screen animado que se muestra al abrir la app por primera vez
/// (cuando no hay sesión activa). Después de 2.5 s navega al Login.
class SplashScreen extends StatefulWidget {
  const SplashScreen({super.key});

  @override
  State<SplashScreen> createState() => _SplashScreenState();
}

class _SplashScreenState extends State<SplashScreen>
    with SingleTickerProviderStateMixin {
  late final AnimationController _ctrl;
  late final Animation<double> _fade;
  late final Animation<double> _scale;
  late final Animation<double> _slideY;

  @override
  void initState() {
    super.initState();

    _ctrl = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 1400),
    );

    _fade = CurvedAnimation(parent: _ctrl, curve: Curves.easeIn);

    _scale = Tween<double>(begin: 0.72, end: 1.0).animate(
      CurvedAnimation(parent: _ctrl, curve: Curves.easeOutBack),
    );

    _slideY = Tween<double>(begin: 30, end: 0).animate(
      CurvedAnimation(
        parent: _ctrl,
        curve: const Interval(0.35, 1.0, curve: Curves.easeOut),
      ),
    );

    _ctrl.forward();

    // Después de la animación navega a Login
    Future.delayed(const Duration(milliseconds: 2600), _navigate);
  }

  void _navigate() {
    if (!mounted) return;
    Navigator.of(context).pushReplacement(
      PageRouteBuilder(
        pageBuilder: (context, animation, secondary) => const LoginScreen(),
        transitionsBuilder: (context, animation, secondary, child) =>
            FadeTransition(opacity: animation, child: child),
        transitionDuration: const Duration(milliseconds: 500),
      ),
    );
  }

  @override
  void dispose() {
    _ctrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Container(
        decoration: const BoxDecoration(gradient: MindraColors.gradientDeep),
        child: SafeArea(
          child: Center(
            child: AnimatedBuilder(
              animation: _ctrl,
              builder: (context, child) => FadeTransition(
                opacity: _fade,
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    // ── Logo ──────────────────────────────────────────────
                    ScaleTransition(
                      scale: _scale,
                      child: Image.asset(
                        'assets/icons/mindra1.png',
                        width: 130,
                        height: 130,
                        fit: BoxFit.contain,
                      ),
                    ),

                    const SizedBox(height: 32),

                    // ── Nombre + tagline ──────────────────────────────────
                    Transform.translate(
                      offset: Offset(0, _slideY.value),
                      child: Column(
                        children: [
                          const Text(
                            'Mindra',
                            style: TextStyle(
                              fontSize: 48,
                              fontWeight: FontWeight.bold,
                              color: Colors.white,
                              letterSpacing: 2,
                            ),
                          ),
                          const SizedBox(height: 8),
                          Text(
                            'Tu espacio seguro para hablar',
                            style: TextStyle(
                              fontSize: 16,
                              color: Colors.white.withValues(alpha: 0.75),
                              letterSpacing: 0.4,
                            ),
                          ),
                        ],
                      ),
                    ),

                    const SizedBox(height: 72),

                    // ── Indicador de carga ────────────────────────────────
                    Transform.translate(
                      offset: Offset(0, _slideY.value),
                      child: SizedBox(
                        width: 120,
                        child: LinearProgressIndicator(
                          backgroundColor: Colors.white.withValues(alpha: 0.2),
                          color: Colors.white.withValues(alpha: 0.7),
                          borderRadius: BorderRadius.circular(4),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }
}

/// Splash de inicialización: se muestra mientras `AuthProvider.init()`
/// valida el token guardado. No navega sola; el router en main.dart
/// cambia de pantalla cuando cambia `auth.state`.
class InitSplashScreen extends StatelessWidget {
  const InitSplashScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Container(
        decoration: const BoxDecoration(gradient: MindraColors.gradientDeep),
        child: Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Image.asset(
                'assets/icons/mindra1.png',
                width: 100,
                height: 100,
                fit: BoxFit.contain,
              ),
              const SizedBox(height: 28),
              const Text(
                'Mindra',
                style: TextStyle(
                  fontSize: 40,
                  fontWeight: FontWeight.bold,
                  color: Colors.white,
                  letterSpacing: 2,
                ),
              ),
              const SizedBox(height: 8),
              Text(
                'Tu espacio seguro para hablar',
                style: TextStyle(
                  fontSize: 15,
                  color: Colors.white.withValues(alpha: 0.72),
                ),
              ),
              const SizedBox(height: 56),
              const CircularProgressIndicator(
                color: Colors.white54,
                strokeWidth: 2.5,
              ),
            ],
          ),
        ),
      ),
    );
  }
}
