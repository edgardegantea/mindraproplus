class InferenceResult {
  final String botResponse;
  final String? etiqueta;
  final double? probabilidadAnsiedad;
  final String? emotionLabel;
  final double? emotionProbability;
  final String plan;
  final DateTime? createdAt;
  /// true cuando el servidor ML no estaba disponible y se usó el fallback local.
  final bool isFallback;

  const InferenceResult({
    required this.botResponse,
    this.etiqueta,
    this.probabilidadAnsiedad,
    this.emotionLabel,
    this.emotionProbability,
    required this.plan,
    this.createdAt,
    this.isFallback = false,
  });

  factory InferenceResult.fromJson(Map<String, dynamic> json) {
    final notes = json['notes'] as Map<String, dynamic>?;
    return InferenceResult(
      botResponse: notes?['bot_response'] as String? ??
          json['bot_response'] as String? ??
          '',
      etiqueta: json['etiqueta'] as String? ?? json['predicted_label'] as String?,
      probabilidadAnsiedad:
          (json['probabilidad_ansiedad'] ?? json['predicted_probability'])
              ?.toDouble(),
      emotionLabel: json['emotion_label'] as String?,
      emotionProbability: json['emotion_probability']?.toDouble(),
      plan: notes?['plan'] as String? ?? json['plan'] as String? ?? 'free',
      createdAt: json['created_at'] != null
          ? DateTime.tryParse(json['created_at'] as String)
          : null,
      isFallback: json['fallback'] == true ||
          json['model_name'] == 'fallback_mindra',
    );
  }
}
