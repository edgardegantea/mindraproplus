import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:cross_file/cross_file.dart';
import '../models/user.dart';
import '../models/plan.dart';
import '../models/inference_result.dart';

class ApiService {
  static const _baseUrl = 'https://mindra.cafined.org/api';

  String? _token;

  void setToken(String? token) => _token = token;

  Map<String, String> get _headers => {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        if (_token != null) 'Authorization': 'Bearer $_token',
      };

  // ── AUTH ──────────────────────────────────────────
  Future<Map<String, dynamic>> login(String email, String password) async {
    final res = await http.post(
      Uri.parse('$_baseUrl/auth/login'),
      headers: _headers,
      body: jsonEncode({'email': email, 'password': password}),
    );
    final data = jsonDecode(res.body) as Map<String, dynamic>;
    if (res.statusCode != 200) {
      throw ApiException(data['message'] as String? ?? 'Credenciales inválidas');
    }
    return data;
  }

  Future<Map<String, dynamic>> register(
      String name, String email, String password) async {
    final res = await http.post(
      Uri.parse('$_baseUrl/auth/register'),
      headers: _headers,
      body: jsonEncode({
        'name': name,
        'email': email,
        'password': password,
        'password_confirmation': password,
      }),
    );
    final data = jsonDecode(res.body) as Map<String, dynamic>;
    if (res.statusCode != 201) {
      final errors = data['errors'] as Map?;
      final msg = errors?.values.first?.first ?? data['message'] ?? 'Error al registrarse';
      throw ApiException(msg.toString());
    }
    return data;
  }

  Future<void> logout() async {
    await http.post(Uri.parse('$_baseUrl/auth/logout'), headers: _headers);
  }

  Future<AppUser> me() async {
    final res = await http.get(
      Uri.parse('$_baseUrl/auth/me'),
      headers: _headers,
    );
    if (res.statusCode != 200) throw ApiException('Sesión expirada');
    final data = jsonDecode(res.body) as Map<String, dynamic>;
    return AppUser.fromJson(data['user'] as Map<String, dynamic>);
  }

  // ── PLANES ────────────────────────────────────────
  Future<List<Plan>> getPlans() async {
    final res = await http.get(
      Uri.parse('$_baseUrl/plans'),
      headers: {'Accept': 'application/json'},
    );
    if (res.statusCode != 200) throw ApiException('No se pudieron cargar los planes');
    final data = jsonDecode(res.body) as Map<String, dynamic>;
    return (data['plans'] as List<dynamic>)
        .map((p) => Plan.fromJson(p as Map<String, dynamic>))
        .toList();
  }

  Future<Map<String, dynamic>> getCurrentSubscription() async {
    final res = await http.get(
      Uri.parse('$_baseUrl/subscriptions/current'),
      headers: _headers,
    );
    if (res.statusCode != 200) throw ApiException('No se pudo obtener la suscripción');
    return jsonDecode(res.body) as Map<String, dynamic>;
  }

  Future<void> subscribe(String planSlug) async {
    final res = await http.post(
      Uri.parse('$_baseUrl/subscriptions'),
      headers: _headers,
      body: jsonEncode({'plan_slug': planSlug}),
    );
    if (res.statusCode != 201) {
      final data = jsonDecode(res.body) as Map<String, dynamic>;
      throw ApiException(data['message'] as String? ?? 'Error al suscribirse');
    }
  }

  /// Crea una preferencia MercadoPago para [planSlug] ('pro' o 'plus').
  /// Devuelve { checkout_url, order_id, amount, currency, period, plan_slug }.
  Future<Map<String, dynamic>> createCheckout(
      String planSlug, String billingPeriod) async {
    final res = await http.post(
      Uri.parse('$_baseUrl/checkout/$planSlug'),
      headers: _headers,
      body: jsonEncode({'billing_period': billingPeriod}),
    );
    final data = jsonDecode(res.body) as Map<String, dynamic>;
    if (res.statusCode != 200) {
      throw ApiException(data['message'] as String? ?? 'Error al crear el pago');
    }
    return data;
  }

  /// @deprecated Usa [createCheckout]
  Future<Map<String, dynamic>> createProCheckout(String billingPeriod) =>
      createCheckout('pro', billingPeriod);

  /// Verifica el estado de una orden de pago en el servidor.
  /// Retorna el campo 'status': 'pending' | 'paid' | 'failed'
  Future<String> checkOrderStatus(int orderId) async {
    final res = await http.get(
      Uri.parse('$_baseUrl/checkout/orders/$orderId'),
      headers: _headers,
    );
    if (res.statusCode != 200) throw ApiException('No se pudo verificar el pago');
    final data = jsonDecode(res.body) as Map<String, dynamic>;
    return data['status'] as String? ?? 'pending';
  }

  // ── INFERENCIA ────────────────────────────────────
  Future<InferenceResult> predict(String text, {XFile? audioFile}) async {
    final uri = Uri.parse('$_baseUrl/inference/predict');
    final request = http.MultipartRequest('POST', uri);
    request.headers['Accept'] = 'application/json';
    if (_token != null) request.headers['Authorization'] = 'Bearer $_token';
    request.fields['texto'] = text.isNotEmpty ? text : ' ';

    if (audioFile != null) {
      final bytes = await audioFile.readAsBytes();
      request.files.add(http.MultipartFile.fromBytes(
        'audio',
        bytes,
        filename: audioFile.name.isNotEmpty ? audioFile.name : 'audio.m4a',
      ));
    }

    final streamed = await request.send();
    final res = await http.Response.fromStream(streamed);

    if (res.statusCode != 200) {
      final data = jsonDecode(res.body) as Map<String, dynamic>;
      throw ApiException(data['error'] as String? ?? 'Error al procesar');
    }
    return InferenceResult.fromJson(jsonDecode(res.body) as Map<String, dynamic>);
  }

  Future<List<InferenceResult>> getHistory() async {
    final res = await http.get(
      Uri.parse('$_baseUrl/inference/history'),
      headers: _headers,
    );
    if (res.statusCode != 200) throw ApiException('No se pudo cargar el historial');
    final data = jsonDecode(res.body) as Map<String, dynamic>;
    return (data['records'] as List<dynamic>)
        .map((r) => InferenceResult.fromJson(r as Map<String, dynamic>))
        .toList();
  }
}

class ApiException implements Exception {
  final String message;
  const ApiException(this.message);
  @override
  String toString() => message;
}
