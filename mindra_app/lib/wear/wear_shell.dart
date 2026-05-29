import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/auth_provider.dart';
import 'wear_home.dart';
import 'wear_login.dart';

class WearShell extends StatelessWidget {
  const WearShell({super.key});

  @override
  Widget build(BuildContext context) {
    final state = context.watch<AuthProvider>().state;
    return switch (state) {
      AuthState.unknown => const Scaffold(
          body: Center(child: CircularProgressIndicator()),
        ),
      AuthState.authenticated => const WearHome(),
      AuthState.unauthenticated => const WearLogin(),
    };
  }
}
