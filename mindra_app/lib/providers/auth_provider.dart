import 'package:flutter/foundation.dart';
import '../models/user.dart';
import '../models/plan.dart';
import '../services/api_service.dart';
import '../services/push_notification_service.dart';
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

  /// Token de sesión actual (útil para guardar en biometría).
  String? get currentToken => _token;

  String? _token;

  Future<void> init() async {
    final token = await _storage.getToken();
    if (token == null) {
      _state = AuthState.unauthenticated;
      notifyListeners();
      return;
    }
    try {
      _api.setToken(token);
      _token = token;
      _user = await _api.me();
      _state = AuthState.authenticated;
      await _loadCurrentPlan();
      // Onboarding ya completado en sesiones previas
      _needsOnboarding = false;
    } catch (_) {
      await _storage.clearToken();
      _api.setToken(null);
      _token = null;
      _state = AuthState.unauthenticated;
    }
    notifyListeners();
  }

  Future<void> login(String email, String password) async {
    final data = await _api.login(email, password);
    await _setSession(data);
  }

  /// Login usando un token existente (p. ej. recuperado desde biometría).
  /// Lanza una excepción si el token ya no es válido.
  Future<void> loginWithToken(String token) async {
    _api.setToken(token);
    _user = await _api.me(); // lanza si el token expiró
    _token = token;
    await _storage.saveToken(token);
    _state = AuthState.authenticated;
    await _loadCurrentPlan();
    notifyListeners();
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
    _token = null;
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
    _token = token;
    _user = user;
    _state = AuthState.authenticated;
    await _loadCurrentPlan();
    notifyListeners();
    // Registrar token FCM en el servidor (best-effort, no bloquea el login)
    if (!kIsWeb) {
      _registerFcmToken();
    }
  }

  Future<void> _registerFcmToken() async {
    try {
      final fcmToken = await PushNotificationService.getToken();
      if (fcmToken == null) return;
      final platform = defaultTargetPlatform.name.toLowerCase();
      await _api.registerDeviceToken(fcmToken, platform: platform);
      // Renovaciones automáticas de token
      PushNotificationService.onTokenRefresh.listen((newToken) async {
        try {
          await _api.registerDeviceToken(newToken, platform: platform);
        } catch (_) {}
      });
    } catch (_) {}
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
