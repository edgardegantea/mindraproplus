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
  Future<InferenceResult> predict(String text,
      {XFile? audioFile, XFile? imageFile}) async {
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

    if (imageFile != null) {
      final bytes = await imageFile.readAsBytes();
      request.files.add(http.MultipartFile.fromBytes(
        'image',
        bytes,
        filename: imageFile.name.isNotEmpty ? imageFile.name : 'face.jpg',
      ));
    }

    late http.Response res;
    try {
      final streamed = await request.send();
      res = await http.Response.fromStream(streamed);
    } catch (_) {
      throw ApiException('No se pudo conectar con el servidor. Verifica tu conexión e intenta de nuevo.');
    }

    if (res.statusCode != 200) {
      Map<String, dynamic> data = {};
      try { data = jsonDecode(res.body) as Map<String, dynamic>; } catch (_) {}
      throw ApiException(data['error'] as String? ?? 'Error al procesar la solicitud (${res.statusCode})');
    }
    return InferenceResult.fromJson(jsonDecode(res.body) as Map<String, dynamic>);
  }

  /// Envía una solicitud de acceso al plan Plus.
  Future<void> submitPlusRequest({
    required String requesterName,
    required String requesterEmail,
    String requesterPosition  = '',
    String requesterPhone     = '',
    String orgName            = '',
    String orgType            = 'otro',
    String orgSector          = '',
    String orgWebsite         = '',
    String orgCountry         = '',
    String orgState           = '',
    String orgCity            = '',
    String orgAddress         = '',
    String billingRfc         = '',
    String billingRazonSocial = '',
    String billingRegimen     = '',
    String billingCfdi        = '',
    String billingEmail       = '',
    String useCase            = 'otro',
    String numUsers           = '',
    String projectDescription = '',
    String howFound           = '',
    String additionalComments = '',
  }) async {
    final res = await http.post(
      Uri.parse('$_baseUrl/plus/request'),
      headers: _headers,
      body: jsonEncode({
        'requester_name':     requesterName,
        'requester_email':    requesterEmail,
        'requester_position': requesterPosition,
        'requester_phone':    requesterPhone,
        'org_name':           orgName,
        'org_type':           orgType,
        'org_sector':         orgSector,
        'org_website':        orgWebsite,
        'org_country':        orgCountry,
        'org_state':          orgState,
        'org_city':           orgCity,
        'org_address':        orgAddress,
        'billing_rfc':             billingRfc,
        'billing_razon_social':    billingRazonSocial,
        'billing_regimen':         billingRegimen,
        'billing_cfdi':            billingCfdi,
        'billing_email':           billingEmail,
        'use_case':           useCase,
        'num_users':          numUsers,
        'project_description':projectDescription,
        'how_found':          howFound,
        'additional_comments':additionalComments,
      }),
    );
    if (res.statusCode != 200 && res.statusCode != 201) {
      final data = jsonDecode(res.body) as Map<String, dynamic>;
      throw ApiException(data['message'] as String? ?? 'Error al enviar solicitud');
    }
  }

  Future<Map<String, dynamic>> getClinicalReport() async {
    final res = await http.get(
      Uri.parse('$_baseUrl/inference/clinical-report'),
      headers: _headers,
    );
    if (res.statusCode != 200) throw ApiException('No se pudo generar el reporte clínico');
    return jsonDecode(res.body) as Map<String, dynamic>;
  }

  Future<List<Map<String, dynamic>>> getCalendar() async {
    final res = await http.get(
      Uri.parse('$_baseUrl/inference/calendar'),
      headers: _headers,
    );
    if (res.statusCode != 200) throw ApiException('No se pudo cargar el calendario');
    final data = jsonDecode(res.body) as Map<String, dynamic>;
    return List<Map<String, dynamic>>.from(data['calendar'] as List);
  }

  Future<Map<String, dynamic>> getTrends() async {
    final res = await http.get(
      Uri.parse('$_baseUrl/inference/trends'),
      headers: _headers,
    );
    if (res.statusCode != 200) throw ApiException('No se pudieron cargar las tendencias');
    return jsonDecode(res.body) as Map<String, dynamic>;
  }

  Future<Map<String, dynamic>> getWeeklyReport() async {
    final res = await http.get(
      Uri.parse('$_baseUrl/inference/weekly-report'),
      headers: _headers,
    );
    if (res.statusCode != 200) throw ApiException('No se pudo generar el reporte');
    return jsonDecode(res.body) as Map<String, dynamic>;
  }

  Future<List<InferenceResult>> getHistory() async {
    final res = await http.get(
      Uri.parse('$_baseUrl/inference/history'),
      headers: _headers,
    );
    if (res.statusCode != 200) throw ApiException('No se pudo cargar el historial');
    final data = jsonDecode(res.body) as Map<String, dynamic>;
    return (data['history'] as List<dynamic>)
        .map((r) => InferenceResult.fromJson(r as Map<String, dynamic>))
        .toList();
  }

  // ── Diario emocional ──────────────────────────────────────────────────────

  Future<List<Map<String, dynamic>>> getJournal() async {
    final res = await http.get(
      Uri.parse('$_baseUrl/journal'),
      headers: _headers,
    );
    if (res.statusCode != 200) throw ApiException('No se pudo cargar el diario');
    final data = jsonDecode(res.body) as Map<String, dynamic>;
    return List<Map<String, dynamic>>.from(data['journal'] as List);
  }

  Future<Map<String, dynamic>?> getJournalToday() async {
    final res = await http.get(
      Uri.parse('$_baseUrl/journal/today'),
      headers: _headers,
    );
    if (res.statusCode != 200) return null;
    final data = jsonDecode(res.body) as Map<String, dynamic>;
    return data['entry'] as Map<String, dynamic>?;
  }

  Future<Map<String, dynamic>> addJournalEntry({
    required int moodScore,
    String? note,
    List<String>? tags,
  }) async {
    final res = await http.post(
      Uri.parse('$_baseUrl/journal'),
      headers: _headers,
      body: jsonEncode({
        'mood_score': moodScore,
        if (note != null && note.isNotEmpty) 'note': note,
        if (tags != null && tags.isNotEmpty) 'tags': tags,
      }),
    );
    final data = jsonDecode(res.body) as Map<String, dynamic>;
    if (res.statusCode != 201) {
      throw ApiException(data['message'] as String? ?? 'Error al guardar');
    }
    return data['entry'] as Map<String, dynamic>;
  }

  Future<void> deleteJournalEntry(int id) async {
    await http.delete(
      Uri.parse('$_baseUrl/journal/$id'),
      headers: _headers,
    );
  }

  // ── Exportar CSV ──────────────────────────────────────────────────────────

  /// Devuelve la URL de descarga del CSV de historial de inferencias.
  String get inferenceExportUrl => '$_baseUrl/inference/export';

  /// Devuelve la URL de descarga del CSV del diario emocional.
  String get journalExportUrl => '$_baseUrl/journal/export';

  /// Retorna los headers de autorización para usar en launchUrl con headers.
  Map<String, String> get authHeaders => _headers;

  // ── Evaluaciones GAD-7 / PHQ-9 ────────────────────────────────────────────

  Future<Map<String, dynamic>> submitAssessment({
    required String type,
    required List<int> answers,
  }) async {
    final res = await http.post(
      Uri.parse('$_baseUrl/assessments'),
      headers: _headers,
      body: jsonEncode({'type': type, 'answers': answers}),
    );
    final data = jsonDecode(res.body) as Map<String, dynamic>;
    if (res.statusCode != 201) {
      throw ApiException(data['message'] as String? ?? 'Error al guardar evaluación');
    }
    return data['assessment'] as Map<String, dynamic>;
  }

  Future<Map<String, dynamic>?> getLatestAssessment() async {
    final res = await http.get(
      Uri.parse('$_baseUrl/assessments/latest'),
      headers: _headers,
    );
    if (res.statusCode != 200) return null;
    final data = jsonDecode(res.body) as Map<String, dynamic>;
    return data['assessment'] as Map<String, dynamic>?;
  }

  Future<List<Map<String, dynamic>>> getAssessmentHistory() async {
    final res = await http.get(
      Uri.parse('$_baseUrl/assessments'),
      headers: _headers,
    );
    if (res.statusCode != 200) return [];
    final data = jsonDecode(res.body) as Map<String, dynamic>;
    return List<Map<String, dynamic>>.from(
        (data['assessments'] as List?) ?? []);
  }

  // ── Racha ─────────────────────────────────────────────────────────────────

  Future<Map<String, dynamic>> getStreak() async {
    final res = await http.get(
      Uri.parse('$_baseUrl/streak'),
      headers: _headers,
    );
    if (res.statusCode != 200) return {'current_streak': 0, 'longest_streak': 0, 'total_days': 0, 'active_today': false};
    return jsonDecode(res.body) as Map<String, dynamic>;
  }

  // ── Enlace para terapeuta ─────────────────────────────────────────────────

  Future<Map<String, dynamic>> generateTherapistShare() async {
    final res = await http.post(
      Uri.parse('$_baseUrl/share/therapist'),
      headers: _headers,
    );
    final data = jsonDecode(res.body) as Map<String, dynamic>;
    if (res.statusCode != 200) {
      throw ApiException(data['message'] as String? ?? 'Error al generar enlace');
    }
    return data;
  }

  // ── Programas estructurados ───────────────────────────────────────────────

  Future<List<Map<String, dynamic>>> getPrograms() async {
    final res = await http.get(
      Uri.parse('$_baseUrl/programs'),
      headers: _headers,
    );
    if (res.statusCode != 200) throw ApiException('No se pudieron cargar los programas');
    final data = jsonDecode(res.body) as Map<String, dynamic>;
    return List<Map<String, dynamic>>.from(data['programs'] as List);
  }

  Future<void> enrollProgram(String slug) async {
    final res = await http.post(
      Uri.parse('$_baseUrl/programs/$slug/enroll'),
      headers: _headers,
    );
    if (res.statusCode != 201) {
      final data = jsonDecode(res.body) as Map<String, dynamic>;
      throw ApiException(data['message'] as String? ?? 'Error al inscribirse');
    }
  }

  Future<void> completeProgramDay(String slug, int day) async {
    final res = await http.post(
      Uri.parse('$_baseUrl/programs/$slug/complete-day'),
      headers: _headers,
      body: jsonEncode({'day': day}),
    );
    if (res.statusCode != 200) {
      final data = jsonDecode(res.body) as Map<String, dynamic>;
      throw ApiException(data['message'] as String? ?? 'Error al completar día');
    }
  }

  // ── FCM Device Tokens ─────────────────────────────────────────────────────

  /// Registra el token FCM del dispositivo en el servidor.
  Future<void> registerDeviceToken(String token,
      {String platform = 'android'}) async {
    await _post('/auth/device-token', {'token': token, 'platform': platform});
  }

  /// Elimina el token FCM del servidor (p. ej. al cerrar sesión).
  Future<void> unregisterDeviceToken(String token) async {
    await _delete('/auth/device-token', {'token': token});
  }

  // ── Helpers privados ──────────────────────────────────────────────────────

  Future<void> _post(String path, Map<String, dynamic> body) async {
    await http.post(
      Uri.parse('$_baseUrl$path'),
      headers: _headers,
      body: jsonEncode(body),
    );
  }

  Future<void> _delete(String path, Map<String, dynamic> body) async {
    await http.delete(
      Uri.parse('$_baseUrl$path'),
      headers: _headers,
      body: jsonEncode(body),
    );
  }

  /// GET /api/chat/history — conversaciones agrupadas por sesión
  Future<List<Map<String, dynamic>>> getChatHistory({int page = 1}) async {
    final res = await http.get(
      Uri.parse('$_baseUrl/chat/history?page=$page&per_page=10'),
      headers: _headers,
    );
    if (res.statusCode != 200) return [];
    final data = jsonDecode(res.body) as Map<String, dynamic>;
    return List<Map<String, dynamic>>.from((data['sessions'] as List?) ?? []);
  }
}

class ApiException implements Exception {
  final String message;
  const ApiException(this.message);
  @override
  String toString() => message;
}
