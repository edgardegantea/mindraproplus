import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/auth_provider.dart';
import '../theme/mindra_theme.dart';
import '../utils/responsive.dart';
import 'plans_screen.dart';

class ProfileScreen extends StatelessWidget {
  const ProfileScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final user = auth.user;
    final plan = auth.effectivePlan;
    final planName = plan?.name ?? 'Free';
    final planColor = plan?.isPlus == true
        ? MindraColors.indigo
        : plan?.isPro == true
            ? MindraColors.violet
            : MindraColors.blue;

    return Scaffold(
      appBar: AppBar(
        title: const Text('Mi perfil'),
      ),
      body: WebFrame(
        maxWidth: 600,
        child: ListView(
          padding: const EdgeInsets.all(24),
          children: [
          const SizedBox(height: 8),
          Center(
            child: CircleAvatar(
              radius: 44,
              backgroundColor: MindraColors.darkSurface,
              child: const Icon(Icons.person, size: 52, color: MindraColors.blue),
            ),
          ),
          const SizedBox(height: 16),
          if (user != null) ...[
            Center(
              child: Text(user.name,
                  style: const TextStyle(
                      fontSize: 22, fontWeight: FontWeight.bold)),
            ),
            Center(
              child: Text(user.email,
                  style: const TextStyle(color: Colors.black54, fontSize: 14)),
            ),
          ],
          const SizedBox(height: 28),
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: planColor.withValues(alpha: 0.07),
              borderRadius: BorderRadius.circular(14),
              border: Border.all(color: planColor.withValues(alpha: 0.25)),
            ),
            child: Row(children: [
              Icon(Icons.workspace_premium, color: planColor, size: 28),
              const SizedBox(width: 14),
              Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                Text('Plan actual',
                    style: TextStyle(color: planColor, fontSize: 12)),
                Text(planName,
                    style: TextStyle(
                        color: planColor,
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
          const SizedBox(height: 40),
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
