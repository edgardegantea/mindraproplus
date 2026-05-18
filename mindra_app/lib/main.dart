import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import 'theme/mindra_theme.dart';
import 'providers/auth_provider.dart';
import 'providers/plan_provider.dart';
import 'services/api_service.dart';
import 'services/storage_service.dart';
import 'screens/auth/login_screen.dart';
import 'screens/chat_screen.dart';
import 'screens/history_screen.dart';
import 'screens/onboarding_screen.dart';
import 'screens/plans_screen.dart';
import 'screens/profile_screen.dart';
import 'screens/splash_screen.dart';
import 'utils/responsive.dart';

void main() {
  WidgetsFlutterBinding.ensureInitialized();

  final api = ApiService();
  final storage = StorageService();

  runApp(
    MultiProvider(
      providers: [
        Provider<ApiService>.value(value: api),
        Provider<StorageService>.value(value: storage),
        ChangeNotifierProvider(
          create: (_) => AuthProvider(api, storage)..init(),
        ),
        ChangeNotifierProvider(
          create: (_) => PlanProvider(api),
        ),
      ],
      child: const MindraApp(),
    ),
  );
}

class MindraApp extends StatelessWidget {
  const MindraApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Mindra',
      debugShowCheckedModeBanner: false,
      theme: MindraTheme.dark,
      home: const _AppRouter(),
    );
  }
}

// ─── ROUTER: decide qué pantalla mostrar según el estado de auth ─────────────
class _AppRouter extends StatelessWidget {
  const _AppRouter();

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();

    return switch (auth.state) {
      // Mientras init() valida el token guardado mostramos el splash de carga.
      AuthState.unknown => const InitSplashScreen(),
      // Nuevo usuario: mostrar onboarding antes del shell principal.
      AuthState.authenticated when auth.needsOnboarding => const OnboardingScreen(),
      AuthState.authenticated => const _MainShell(),
      AuthState.unauthenticated => const LoginScreen(),
    };
  }
}

// ─── SHELL con navegación inferior (móvil) o lateral (web/tablet) ────────────
class _MainShell extends StatefulWidget {
  const _MainShell();

  @override
  State<_MainShell> createState() => _MainShellState();
}

class _MainShellState extends State<_MainShell> {
  int _index = 0;

  static const _screens = [
    _HomeTab(),
    HistoryScreen(),
    PlansScreen(),
    ProfileScreen(),
  ];

  static const _destinations = [
    (icon: Icons.home_outlined, selected: Icons.home, label: 'Inicio'),
    (icon: Icons.history_outlined, selected: Icons.history, label: 'Historial'),
    (icon: Icons.star_outline, selected: Icons.star, label: 'Planes'),
    (icon: Icons.person_outline, selected: Icons.person, label: 'Perfil'),
  ];

  @override
  Widget build(BuildContext context) {
    final wide = isWideScreen(context);

    if (wide) {
      // ── Pantalla ancha: NavigationRail lateral ──────────────────────────────
      return Scaffold(
        body: Row(
          children: [
            NavigationRail(
              selectedIndex: _index,
              onDestinationSelected: (i) => setState(() => _index = i),
              labelType: NavigationRailLabelType.all,
              minWidth: 80,
              leading: Padding(
                padding: const EdgeInsets.symmetric(vertical: 16),
                child: Image.asset('assets/icons/mindra1.png',
                    width: 36, height: 36, fit: BoxFit.contain),
              ),
              destinations: [
                for (final d in _destinations)
                  NavigationRailDestination(
                    icon: Icon(d.icon),
                    selectedIcon: Icon(d.selected),
                    label: Text(d.label),
                  ),
              ],
            ),
            const VerticalDivider(thickness: 1, width: 1),
            Expanded(
              child: IndexedStack(index: _index, children: _screens),
            ),
          ],
        ),
      );
    }

    // ── Pantalla estrecha: NavigationBar inferior ───────────────────────────
    return Scaffold(
      body: IndexedStack(index: _index, children: _screens),
      bottomNavigationBar: NavigationBar(
        selectedIndex: _index,
        onDestinationSelected: (i) => setState(() => _index = i),
        destinations: [
          for (final d in _destinations)
            NavigationDestination(
              icon: Icon(d.icon),
              selectedIcon: Icon(d.selected),
              label: d.label,
            ),
        ],
      ),
    );
  }
}

// ─── HOME TAB ────────────────────────────────────────────────────────────────
class _HomeTab extends StatelessWidget {
  const _HomeTab();

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final name = auth.user?.name ?? 'Amigo/a';
    final plan = auth.effectivePlan;
    final planName = plan?.name ?? 'Free';
    final planColor = plan?.isPlus == true
        ? MindraColors.indigo
        : plan?.isPro == true
            ? MindraColors.violet
            : MindraColors.blue;

    return Scaffold(
      appBar: AppBar(
        title: const _MindraLogoTitle(),
      ),
      body: WebFrame(
        child: Center(
          child: Padding(
            padding: const EdgeInsets.symmetric(horizontal: 28),
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                const _MindraLogo(size: 88),
                const SizedBox(height: 24),
                Text(
                  'Hola, $name',
                  style: const TextStyle(
                      fontSize: 30, fontWeight: FontWeight.bold),
                ),
                const SizedBox(height: 10),
                _PlanBadge(planName: planName, color: planColor),
                const SizedBox(height: 20),
                const Text(
                  '¿Cómo te sientes hoy?\nEstoy aquí para escucharte.',
                  textAlign: TextAlign.center,
                  style: TextStyle(fontSize: 16, height: 1.6),
                ),
                const SizedBox(height: 48),
                _GradientButton(
                  label: 'Conversar con Mindra',
                  icon: Icons.chat_bubble_outline,
                  onPressed: () => Navigator.push(context,
                      MaterialPageRoute(builder: (_) => const ChatScreen())),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

// ─── Widgets compartidos ──────────────────────────────────────────────────────

class _MindraLogo extends StatelessWidget {
  final double size;
  const _MindraLogo({required this.size});

  @override
  Widget build(BuildContext context) {
    return Image.asset('assets/icons/mindra1.png',
        width: size, height: size, fit: BoxFit.contain);
  }
}

class _MindraLogoTitle extends StatelessWidget {
  const _MindraLogoTitle();

  @override
  Widget build(BuildContext context) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Image.asset('assets/icons/mindra1.png',
            width: 28, height: 28, fit: BoxFit.contain),
        const SizedBox(width: 10),
        const Text('Mindra',
            style: TextStyle(fontWeight: FontWeight.bold, fontSize: 20)),
      ],
    );
  }
}

class _PlanBadge extends StatelessWidget {
  final String planName;
  final Color color;
  const _PlanBadge({required this.planName, required this.color});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 5),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.15),
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: color.withValues(alpha: 0.4)),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(Icons.workspace_premium, size: 14, color: color),
          const SizedBox(width: 5),
          Text('Plan $planName',
              style: TextStyle(color: color, fontWeight: FontWeight.w600)),
        ],
      ),
    );
  }
}

class _GradientButton extends StatelessWidget {
  final String label;
  final IconData icon;
  final VoidCallback onPressed;
  const _GradientButton(
      {required this.label, required this.icon, required this.onPressed});

  @override
  Widget build(BuildContext context) {
    return DecoratedBox(
      decoration: BoxDecoration(
        gradient: MindraColors.gradientMain,
        borderRadius: BorderRadius.circular(30),
        boxShadow: [
          BoxShadow(
            color: MindraColors.blue.withValues(alpha: 0.35),
            blurRadius: 16,
            offset: const Offset(0, 6),
          ),
        ],
      ),
      child: ElevatedButton.icon(
        icon: Icon(icon),
        label: Text(label, style: const TextStyle(fontSize: 16)),
        style: ElevatedButton.styleFrom(
          backgroundColor: Colors.transparent,
          foregroundColor: Colors.white,
          shadowColor: Colors.transparent,
          padding: const EdgeInsets.symmetric(horizontal: 32, vertical: 16),
          shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(30)),
        ),
        onPressed: onPressed,
      ),
    );
  }
}
