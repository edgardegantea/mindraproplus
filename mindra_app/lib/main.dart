import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import 'theme/mindra_theme.dart';
import 'providers/auth_provider.dart';
import 'providers/plan_provider.dart';
import 'services/api_service.dart';
import 'services/storage_service.dart';
import 'services/notification_service.dart';
import 'screens/auth/login_screen.dart';
import 'screens/chat_screen.dart';
import 'screens/onboarding_screen.dart';
import 'screens/profile_screen.dart';
import 'screens/splash_screen.dart';
import 'screens/wellness_screen.dart';
import 'utils/responsive.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();

  final api      = ApiService();
  final storage  = StorageService();
  final notifs   = NotificationService();
  await notifs.init();

  runApp(
    MultiProvider(
      providers: [
        Provider<ApiService>.value(value: api),
        Provider<StorageService>.value(value: storage),
        Provider<NotificationService>.value(value: notifs),
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

  void switchTab(int index) => setState(() => _index = index);

  static const _screens = [
    _HomeTab(),
    WellnessScreen(),
    ProfileScreen(),
  ];

  static const _destinations = [
    (icon: Icons.home_outlined,             selected: Icons.home,             label: 'Inicio'),
    (icon: Icons.spa_outlined,              selected: Icons.spa,              label: 'Bienestar'),
    (icon: Icons.person_outline,            selected: Icons.person,           label: 'Perfil'),
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
    final auth     = context.watch<AuthProvider>();
    final name     = auth.user?.name.split(' ').first ?? 'Amigo/a';
    final plan     = auth.effectivePlan;
    final planColor = plan?.isPlus == true
        ? MindraColors.indigo
        : plan?.isPro == true
            ? MindraColors.violet
            : MindraColors.blue;
    final hour     = DateTime.now().hour;
    final greeting = hour < 12 ? 'Buenos días' : hour < 19 ? 'Buenas tardes' : 'Buenas noches';

    return Scaffold(
      appBar: AppBar(
        title: const _MindraLogoTitle(),
        actions: [
          IconButton(
            onPressed: () => _SosSheet.show(context),
            icon: Container(
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
              decoration: BoxDecoration(
                color: Colors.red.withValues(alpha: 0.15),
                borderRadius: BorderRadius.circular(8),
                border: Border.all(color: Colors.red.withValues(alpha: 0.4)),
              ),
              child: const Text('SOS',
                  style: TextStyle(
                      color: Colors.red,
                      fontSize: 12,
                      fontWeight: FontWeight.bold)),
            ),
            tooltip: 'Apoyo inmediato',
          ),
          const SizedBox(width: 4),
        ],
      ),
      body: WebFrame(
        child: ListView(
          padding: const EdgeInsets.fromLTRB(16, 12, 16, 40),
          children: [

            // ── Saludo ────────────────────────────────────────────────────
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 24),
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                  colors: [
                    planColor.withValues(alpha: 0.15),
                    MindraColors.darkSurface,
                  ],
                ),
                borderRadius: BorderRadius.circular(22),
                border: Border.all(color: planColor.withValues(alpha: 0.22)),
              ),
              child: Row(children: [
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text('$greeting,',
                          style: const TextStyle(
                              fontSize: 14,
                              color: MindraColors.textSecondary)),
                      const SizedBox(height: 4),
                      Text(name,
                          style: const TextStyle(
                              fontSize: 28, fontWeight: FontWeight.bold)),
                      const SizedBox(height: 12),
                      _PlanBadge(planName: plan?.name ?? 'Free', color: planColor),
                    ],
                  ),
                ),
                const SizedBox(width: 16),
                Container(
                  width: 72,
                  height: 72,
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    shape: BoxShape.circle,
                    gradient: MindraColors.gradientMain,
                    boxShadow: [
                      BoxShadow(
                        color: MindraColors.blue.withValues(alpha: 0.35),
                        blurRadius: 18,
                        spreadRadius: 2,
                      ),
                    ],
                  ),
                  child: Image.asset('assets/icons/mindra1.png',
                      fit: BoxFit.contain),
                ),
              ]),
            ),

            const SizedBox(height: 20),

            // ── HERO: Conversar con Mindra ────────────────────────────────
            GestureDetector(
              onTap: () => Navigator.push(context,
                  MaterialPageRoute(builder: (_) => const ChatScreen())),
              child: Container(
                width: double.infinity,
                height: MediaQuery.of(context).size.height * 0.44,
                decoration: BoxDecoration(
                  gradient: LinearGradient(
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                    colors: [
                      MindraColors.darkSurface,
                      MindraColors.violet.withValues(alpha: 0.22),
                    ],
                  ),
                  borderRadius: BorderRadius.circular(28),
                  border: Border.all(
                      color: MindraColors.violet.withValues(alpha: 0.40)),
                  boxShadow: [
                    BoxShadow(
                      color: MindraColors.violet.withValues(alpha: 0.18),
                      blurRadius: 32,
                      offset: const Offset(0, 12),
                    ),
                  ],
                ),
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Image.asset('assets/icons/mindra1.png',
                        width: 148, height: 148, fit: BoxFit.contain),
                    const SizedBox(height: 24),
                    const Text('Conversar con Mindra',
                        style: TextStyle(
                            fontSize: 26,
                            fontWeight: FontWeight.bold,
                            color: Colors.white)),
                    const SizedBox(height: 8),
                    const Text('Presiona para comenzar tu sesión',
                        style: TextStyle(
                            fontSize: 13,
                            color: MindraColors.textSecondary)),
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

// ─── SOS Sheet ────────────────────────────────────────────────────────────────

class _SosSheet {
  static void show(BuildContext context) {
    showModalBottomSheet(
      context: context,
      backgroundColor: MindraColors.darkSurface,
      shape: const RoundedRectangleBorder(
          borderRadius: BorderRadius.vertical(top: Radius.circular(24))),
      builder: (_) => const _SosContent(),
    );
  }
}

class _SosContent extends StatelessWidget {
  const _SosContent();

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(24, 20, 24, 36),
      child: Column(mainAxisSize: MainAxisSize.min, children: [
        Container(
          width: 40, height: 4,
          decoration: BoxDecoration(
              color: Colors.white24,
              borderRadius: BorderRadius.circular(99)),
        ),
        const SizedBox(height: 16),
        Container(
          padding: const EdgeInsets.all(12),
          decoration: BoxDecoration(
              color: Colors.red.withValues(alpha: 0.12),
              shape: BoxShape.circle),
          child: const Icon(Icons.emergency, color: Colors.red, size: 32),
        ),
        const SizedBox(height: 12),
        const Text('¿Necesitas apoyo inmediato?',
            style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
        const SizedBox(height: 4),
        const Text(
          'Estás en un espacio seguro. Estas líneas están disponibles ahora mismo.',
          textAlign: TextAlign.center,
          style: TextStyle(color: MindraColors.textSecondary, fontSize: 13),
        ),
        const SizedBox(height: 20),
        _SosOption(
          emoji: '📞',
          title: 'SAPTEL',
          subtitle: 'Crisis emocional · Gratuito 24/7',
          detail: '55 5259-8121',
          color: Colors.red,
          onTap: () {},
        ),
        const SizedBox(height: 10),
        _SosOption(
          emoji: '🏥',
          title: 'IMSS Salud Mental',
          subtitle: 'Apoyo psicológico gratuito',
          detail: '800 890-2000',
          color: const Color(0xFFdc2626),
          onTap: () {},
        ),
        const SizedBox(height: 10),
        _SosOption(
          emoji: '🚨',
          title: 'Emergencias',
          subtitle: 'Riesgo inmediato para ti o alguien más',
          detail: '911',
          color: Colors.orange,
          onTap: () {},
        ),
        const SizedBox(height: 10),
        _SosOption(
          emoji: '💬',
          title: 'Hablar con Mindra',
          subtitle: 'IA de apoyo disponible ahora',
          detail: 'Abrir chat',
          color: MindraColors.blue,
          onTap: () {
            Navigator.pop(context);
            Navigator.push(context,
                MaterialPageRoute(builder: (_) => const ChatScreen()));
          },
        ),
        const SizedBox(height: 16),
        const Text(
          'Si estás pensando en hacerte daño, llama al SAPTEL ahora.',
          textAlign: TextAlign.center,
          style: TextStyle(fontSize: 11, color: Colors.red),
        ),
      ]),
    );
  }
}

class _SosOption extends StatelessWidget {
  final String emoji;
  final String title;
  final String subtitle;
  final String detail;
  final Color color;
  final VoidCallback onTap;
  const _SosOption({
    required this.emoji, required this.title, required this.subtitle,
    required this.detail, required this.color, required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
        decoration: BoxDecoration(
          color: color.withValues(alpha: 0.08),
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: color.withValues(alpha: 0.3)),
        ),
        child: Row(children: [
          Text(emoji, style: const TextStyle(fontSize: 24)),
          const SizedBox(width: 12),
          Expanded(child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(title, style: const TextStyle(
                  fontSize: 14, fontWeight: FontWeight.w600)),
              Text(subtitle, style: const TextStyle(
                  fontSize: 11, color: MindraColors.textSecondary)),
            ],
          )),
          Text(detail, style: TextStyle(
              fontSize: 13, fontWeight: FontWeight.bold, color: color)),
          const SizedBox(width: 4),
          Icon(Icons.arrow_forward_ios, size: 12, color: color),
        ]),
      ),
    );
  }
}

// ─── Widgets compartidos ──────────────────────────────────────────────────────

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

