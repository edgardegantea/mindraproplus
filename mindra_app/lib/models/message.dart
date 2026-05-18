class Message {
  final String text;
  final bool isUser;
  final double? anxietyProbability;
  final String? emotionLabel;
  final String? botResponse;

  Message({
    required this.text,
    required this.isUser,
    this.anxietyProbability,
    this.emotionLabel,
    this.botResponse,
  });
}
