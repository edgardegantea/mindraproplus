import 'package:flutter/foundation.dart';
import '../models/user.dart';
import '../models/plan.dart';
import '../services/api_service.dart';
import '../services/storage_service.dart';

enum AuthState { unknown, authenticated, unauthenticated }

class AuthProvider extends ChangeNotifier {
  final ApiService _api;
  final StorageService _storage;

  AuthState _state = AuthState.unknown;
  AppUser? _user;
  Plan? _currentPlan;
  bool _needsOnboarding = false;

  AuthProvider(this._api, this._storage);

  AuthState get state => _state;
  AppUser? get user => _user;

  /// Plan real del usuario (puede ser Pro o Full en móvil).
  Plan? get currentPlan => _currentPlan;

  /// Plan efectivo según plataforma:
  /// - Web → siempre null (Free), sin importar la suscripción real.
  /// - Móvil → plan real del usuario.
  Plan? get effectivePlan => kIsWeb ? null : _currentPlan;

  bool get isAuthenticated => _state == AuthState.authenticated;
  /// true solo después de un registro nuevo, hasta que el onboarding termine.
  bool get needsOnboarding => _needsOnboarding;

  Future<void> init() async {
    final token = await _storage.getToken();
    if (token == null) {
      _state = AuthState.unauthenticated;
      notifyListeners();
      return;
    }
    try {
      _api.setToken(token);
      _user = await _api.me();
      _state = AuthState.authenticated;
      await _loadCurrentPlan();
      // Onboarding ya completado en sesiones previas
      _needsOnboarding = false;
    } catch (_) {
      await _storage.clearToken();
      _api.setToken(null);
      _state = AuthState.unauthenticated;
    }
    notifyListeners();
  }

  Future<void> login(String email, String password) async {
    final data = await _api.login(email, password);
    await _setSession(data);
  }

  Future<void> register(String name, String email, String password) async {
    final data = await _api.register(name, email, password);
    _needsOnboarding = true;   // Nuevo usuario → mostrar onboarding
    await _setSession(data);
  }

  /// Llamado desde OnboardingScreen cuando el usuario termina el tour.
  void notifyOnboardingDone() {
    _needsOnboarding = false;
    notifyListeners();
  }

  Future<void> logout() async {
    try {
      await _api.logout();
    } catch (_) {}
    await _storage.clearToken();
    _api.setToken(null);
    _user = null;
    _currentPlan = null;
    _state = AuthState.unauthenticated;
    notifyListeners();
  }

  Future<void> refreshPlan() async {
    await _loadCurrentPlan();
    notifyListeners();
  }

  Future<void> _setSession(Map<String, dynamic> data) async {
    final token = data['token'] as String;
    final user = AppUser.fromJson(data['user'] as Map<String, dynamic>);
    await _storage.saveToken(token);
    _api.setToken(token);
    _user = user;
    _state = AuthState.authenticated;
    await _loadCurrentPlan();
    notifyListeners();
  }

  Future<void> _loadCurrentPlan() async {
    try {
      final sub = await _api.getCurrentSubscription();
      final subData = sub['subscription'] as Map<String, dynamic>?;
      if (subData != null) {
        final planData = subData['plan'] as Map<String, dynamic>?;
        if (planData != null) {
          _currentPlan = Plan.fromJson(planData);
          return;
        }
      }
    } catch (_) {}
    _currentPlan = null;
  }
}
