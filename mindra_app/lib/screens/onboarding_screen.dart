import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/auth_provider.dart';
import '../services/storage_service.dart';
import '../theme/mindra_theme.dart';

/// Pantalla de bienvenida que se muestra UNA SOLA VEZ justo después del
/// registro exitoso. Explica qué es Mindra, inicia con el plan Free y
/// ofrece actualizar a Pro.
class OnboardingScreen extends StatefulWidget {
  const OnboardingScreen({super.key});

  @override
  State<OnboardingScreen> createState() => _OnboardingScreenState();
}

class _OnboardingScreenState extends State<OnboardingScreen> {
  final _pageCtrl = PageController();
  int _page = 0;

  static const _pages = [
    _OnboardingPage(
      gradient: MindraColors.gradientDeep,
      emoji: '🧠',
      title: 'Bienvenido/a a Mindra',
      body:
          'Mindra es tu compañero de apoyo emocional basado en inteligencia '
          'artificial. Puedes hablar de lo que sientes y obtendrás respuestas '
          'empáticas en texto y voz.',
    ),
    _OnboardingPage(
      gradient: MindraColors.gradientMain,
      emoji: '🎙️',
      title: 'Detectamos cómo te sientes',
      body:
          'Analizamos tus mensajes y notas de voz para identificar señales de '
          'ansiedad y emociones. Todo de forma privada, solo para ti.',
    ),
    _OnboardingPage(
      gradient: LinearGradient(
        begin: Alignment.topLeft,
        end: Alignment.bottomRight,
        colors: [Color(0xFF00A0F0), Color(0xFF3C14B4)],
      ),
      emoji: '⭐',
      title: 'Empieza con el plan Free',
      body:
          'Chat de texto y voz ilimitado sin costo. Cuando quieras acceder a '
          'análisis de emociones, historial e imagen, puedes actualizar a Pro '
          'desde la sección Planes.',
    ),
  ];

  void _next() {
    if (_page < _pages.length - 1) {
      _pageCtrl.nextPage(
        duration: const Duration(milliseconds: 320),
        curve: Curves.easeInOut,
      );
    } else {
      _finish();
    }
  }

  Future<void> _finish() async {
    await context.read<StorageService>().setOnboardingCompleted();
    // AuthProvider ya está en state=authenticated → el router cambia a _MainShell
    if (mounted) context.read<AuthProvider>().notifyOnboardingDone();
  }

  @override
  void dispose() {
    _pageCtrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final user = context.read<AuthProvider>().user;
    return Scaffold(
      body: Stack(
        children: [
          // ── PageView con slides ──────────────────────────────────────────
          PageView.builder(
            controller: _pageCtrl,
            onPageChanged: (i) => setState(() => _page = i),
            itemCount: _pages.length,
            itemBuilder: (_, i) => _PageSlide(
              page: _pages[i],
              userName: i == 0 ? user?.name : null,
            ),
          ),

          // ── Indicadores de página ────────────────────────────────────────
          Positioned(
            bottom: 120,
            left: 0,
            right: 0,
            child: Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: List.generate(
                _pages.length,
                (i) => AnimatedContainer(
                  duration: const Duration(milliseconds: 280),
                  margin: const EdgeInsets.symmetric(horizontal: 4),
                  width: _page == i ? 24 : 8,
                  height: 8,
                  decoration: BoxDecoration(
                    color: Colors.white
                        .withValues(alpha: _page == i ? 1.0 : 0.4),
                    borderRadius: BorderRadius.circular(4),
                  ),
                ),
              ),
            ),
          ),

          // ── Botones ──────────────────────────────────────────────────────
          Positioned(
            bottom: 0,
            left: 0,
            right: 0,
            child: SafeArea(
              top: false,
              child: Padding(
                padding: const EdgeInsets.fromLTRB(28, 0, 28, 20),
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    SizedBox(
                      width: double.infinity,
                      child: ElevatedButton(
                        onPressed: _next,
                        style: ElevatedButton.styleFrom(
                          backgroundColor: Colors.white,
                          foregroundColor: MindraColors.violet,
                          padding: const EdgeInsets.symmetric(vertical: 15),
                          shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(14)),
                          elevation: 0,
                        ),
                        child: Text(
                          _page == _pages.length - 1
                              ? 'Comenzar'
                              : 'Siguiente',
                          style: const TextStyle(
                              fontSize: 16, fontWeight: FontWeight.w700),
                        ),
                      ),
                    ),
                    if (_page < _pages.length - 1) ...[
                      const SizedBox(height: 8),
                      TextButton(
                        onPressed: _finish,
                        child: Text(
                          'Saltar',
                          style: TextStyle(
                              color: Colors.white.withValues(alpha: 0.7),
                              fontSize: 14),
                        ),
                      ),
                    ],
                  ],
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}

// ─── Slide individual ─────────────────────────────────────────────────────────

class _OnboardingPage {
  final Gradient gradient;
  final String emoji;
  final String title;
  final String body;
  const _OnboardingPage({
    required this.gradient,
    required this.emoji,
    required this.title,
    required this.body,
  });
}

class _PageSlide extends StatelessWidget {
  final _OnboardingPage page;
  final String? userName;

  const _PageSlide({required this.page, this.userName});

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(gradient: page.gradient),
      child: SafeArea(
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 36),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              // Logo + emoji
              Stack(
                alignment: Alignment.bottomRight,
                children: [
                  Image.asset(
                    'assets/icons/mindra1.png',
                    width: 110,
                    height: 110,
                    fit: BoxFit.contain,
                  ),
                  Container(
                    padding: const EdgeInsets.all(8),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(16),
                      boxShadow: [
                        BoxShadow(
                          color: Colors.black.withValues(alpha: 0.15),
                          blurRadius: 8,
                        )
                      ],
                    ),
                    child: Text(page.emoji,
                        style: const TextStyle(fontSize: 22)),
                  ),
                ],
              ),

              const SizedBox(height: 40),

              // Título (con nombre si es la primera slide)
              Text(
                userName != null ? '¡Hola, ${userName!.split(' ').first}!' : page.title,
                textAlign: TextAlign.center,
                style: const TextStyle(
                  fontSize: 28,
                  fontWeight: FontWeight.bold,
                  color: Colors.white,
                  height: 1.2,
                ),
              ),

              if (userName != null) ...[
                const SizedBox(height: 8),
                Text(
                  page.title,
                  textAlign: TextAlign.center,
                  style: TextStyle(
                    fontSize: 18,
                    color: Colors.white.withValues(alpha: 0.85),
                    fontWeight: FontWeight.w600,
                  ),
                ),
              ],

              const SizedBox(height: 20),

              // Cuerpo
              Text(
                page.body,
                textAlign: TextAlign.center,
                style: TextStyle(
                  fontSize: 16,
                  color: Colors.white.withValues(alpha: 0.82),
                  height: 1.6,
                ),
              ),

              // En el último slide mostramos el badge de plan Free
              if (userName == null && page.emoji == '⭐') ...[
                const SizedBox(height: 32),
                _FreePlanBadge(),
              ],
            ],
          ),
        ),
      ),
    );
  }
}

class _FreePlanBadge extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 14),
      decoration: BoxDecoration(
        color: Colors.white.withValues(alpha: 0.15),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: Colors.white.withValues(alpha: 0.3)),
      ),
      child: Column(
        children: [
          const Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              Icon(Icons.workspace_premium, color: Colors.white, size: 18),
              SizedBox(width: 8),
              Text(
                'Tu plan actual: Free',
                style: TextStyle(
                    color: Colors.white,
                    fontWeight: FontWeight.bold,
                    fontSize: 15),
              ),
            ],
          ),
          const SizedBox(height: 10),
          _FeatureRow(icon: Icons.check_circle, label: 'Chat de texto', on: true),
          _FeatureRow(icon: Icons.check_circle, label: 'Mensajes de voz', on: true),
          _FeatureRow(icon: Icons.lock_outline, label: 'Análisis de emociones', on: false),
          _FeatureRow(icon: Icons.lock_outline, label: 'Historial de sesiones', on: false),
          _FeatureRow(icon: Icons.lock_outline, label: 'Análisis facial', on: false),
        ],
      ),
    );
  }
}

class _FeatureRow extends StatelessWidget {
  final IconData icon;
  final String label;
  final bool on;
  const _FeatureRow({required this.icon, required this.label, required this.on});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 3),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 15,
              color: on ? Colors.white : Colors.white.withValues(alpha: 0.4)),
          const SizedBox(width: 8),
          Text(
            label,
            style: TextStyle(
              fontSize: 13,
              color: on ? Colors.white : Colors.white.withValues(alpha: 0.5),
            ),
          ),
        ],
      ),
    );
  }
}
