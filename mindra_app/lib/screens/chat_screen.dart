import 'dart:io' show Platform;
import 'dart:math' show Random;
import 'package:camera/camera.dart';
import 'package:flutter/foundation.dart' show kIsWeb;
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:record/record.dart';
import 'package:permission_handler/permission_handler.dart';
import 'package:cross_file/cross_file.dart';
import 'package:path_provider/path_provider.dart';
import '../providers/auth_provider.dart';
import '../services/api_service.dart';
import '../models/inference_result.dart';
import '../theme/mindra_theme.dart';
import '../utils/responsive.dart';

// permission_handler solo funciona en iOS/Android.
bool get _needsMobilePermission =>
    !kIsWeb && (Platform.isAndroid || Platform.isIOS);

class _Msg {
  final String text;
  final bool isUser;
  final String? etiqueta;
  final double? probAnsiedad;
  final String? emotionLabel;

  const _Msg({
    required this.text,
    required this.isUser,
    this.etiqueta,
    this.probAnsiedad,
    this.emotionLabel,
  });
}

class ChatScreen extends StatefulWidget {
  const ChatScreen({super.key});

  @override
  State<ChatScreen> createState() => _ChatScreenState();
}

class _ChatScreenState extends State<ChatScreen> {
  final _textCtrl = TextEditingController();
  final _scrollCtrl = ScrollController();
  final _recorder = AudioRecorder();
  final List<_Msg> _messages = [];
  bool _isRecording = false;
  bool _sending = false;
  XFile? _pendingImage; // imagen facial capturada, pendiente de enviar

  @override
  void initState() {
    super.initState();
    final name = context.read<AuthProvider>().user?.name ?? '';
    _messages.add(_Msg(
      text: name.isNotEmpty
          ? 'Hola $name, estoy aquí para escucharte. ¿Cómo te sientes hoy?'
          : 'Hola, estoy aquí para escucharte. ¿Cómo te sientes hoy?',
      isUser: false,
    ));
  }

  @override
  void dispose() {
    _textCtrl.dispose();
    _scrollCtrl.dispose();
    _recorder.dispose();
    super.dispose();
  }

  void _scrollToBottom() {
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (_scrollCtrl.hasClients) {
        _scrollCtrl.animateTo(
          _scrollCtrl.position.maxScrollExtent,
          duration: const Duration(milliseconds: 280),
          curve: Curves.easeOut,
        );
      }
    });
  }

  Future<void> _toggleRecording() async {
    if (_sending) return;

    if (_isRecording) {
      final path = await _recorder.stop();
      setState(() => _isRecording = false);
      if (path != null) _send(audioPath: path);
      return;
    }

    // En iOS/Android pedimos permiso explícito. En macOS lo maneja el SO.
    if (_needsMobilePermission) {
      final status = await Permission.microphone.request();
      if (!status.isGranted) {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('Permiso de micrófono denegado')),
          );
        }
        return;
      }
    }

    if (!await _recorder.hasPermission()) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Sin acceso al micrófono')),
        );
      }
      return;
    }

    String? path;
    if (!kIsWeb) {
      final dir = await getApplicationDocumentsDirectory();
      path = '${dir.path}/audio_${DateTime.now().millisecondsSinceEpoch}.m4a';
    }
    await _recorder.start(
      const RecordConfig(encoder: AudioEncoder.aacLc),
      path: path ?? '',
    );
    setState(() => _isRecording = true);
  }

  /// Abre la cámara frontal y captura una foto para análisis facial (Plus).
  Future<void> _captureFace() async {
    if (kIsWeb || _sending) return;
    try {
      final cameras = await availableCameras();
      final front = cameras.firstWhere(
        (c) => c.lensDirection == CameraLensDirection.front,
        orElse: () => cameras.first,
      );
      if (!mounted) return;
      final image = await Navigator.push<XFile?>(
        context,
        MaterialPageRoute(builder: (_) => _FaceCaptureScreen(camera: front)),
      );
      if (image != null) {
        setState(() => _pendingImage = image);
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('📸 Foto capturada — escribe un mensaje y envía para incluir el análisis facial.'),
            duration: Duration(seconds: 3),
          ),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error al abrir cámara: $e')),
        );
      }
    }
  }

  /// Muestra diálogo de alerta de crisis cuando ansiedad > 75%.
  void _showCrisisAlert() {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (_) => AlertDialog(
        backgroundColor: MindraColors.darkSurface,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(18)),
        title: Row(children: [
          Icon(Icons.warning_amber_rounded, color: Colors.red.shade400),
          const SizedBox(width: 8),
          const Text('Nivel de alerta elevado'),
        ]),
        content: const Text(
          'Mindra detectó un nivel de ansiedad alto en tu mensaje.\n\n'
          'Si estás pasando por un momento difícil, recuerda que hay apoyo disponible:\n\n'
          '• Habla con alguien de confianza\n'
          '• Línea de la vida: 800 911 2000 (México, 24h)\n'
          '• Respira profundo — estás a salvo.',
          style: TextStyle(height: 1.5, fontSize: 14),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Entendido'),
          ),
        ],
      ),
    );
  }

  Future<void> _send({String? audioPath}) async {
    final text = _textCtrl.text.trim();
    if (text.isEmpty && audioPath == null) return;
    _textCtrl.clear();

    final imageToSend = _pendingImage;
    setState(() {
      _messages.add(_Msg(
        text: audioPath != null
            ? (imageToSend != null ? '🎵 Nota de voz + 📸 análisis facial' : '🎵 Nota de voz')
            : (imageToSend != null ? '$text\n📸 Análisis facial incluido' : text),
        isUser: true,
      ));
      _sending = true;
      _pendingImage = null;
    });
    _scrollToBottom();

    final api = context.read<ApiService>();
    final auth = context.read<AuthProvider>();
    final showEmotions = auth.effectivePlan?.hasFeature('emociones') ?? false;
    final hasCrisisAlerts = auth.effectivePlan?.hasFeature('crisis_alerts') ?? false;

    try {
      XFile? audioFile;
      if (audioPath != null) audioFile = XFile(audioPath);

      final InferenceResult result =
          await api.predict(text, audioFile: audioFile, imageFile: imageToSend);

      // Alerta de crisis (Plus) si ansiedad > 75%
      if (hasCrisisAlerts &&
          result.probabilidadAnsiedad != null &&
          result.probabilidadAnsiedad! > 0.75) {
        if (mounted) _showCrisisAlert();
      }

      // Delay aleatorio (50–1000 ms) para simular que Mindra "está escribiendo"
      // incluso cuando el backend ya respondió, haciendo la UX más natural.
      await Future.delayed(
        Duration(milliseconds: 50 + Random().nextInt(951)),
      );

      if (!mounted) return;
      setState(() {
        _messages.add(_Msg(
          text: result.botResponse.isNotEmpty
              ? result.botResponse
              : '(Sin respuesta del servidor)',
          isUser: false,
          etiqueta: showEmotions ? result.etiqueta : null,
          probAnsiedad: showEmotions ? result.probabilidadAnsiedad : null,
          emotionLabel: showEmotions ? result.emotionLabel : null,
        ));
        _sending = false;
      });
    } catch (e) {
      setState(() {
        _messages.add(_Msg(
          text: 'En este momento tengo dificultades para conectarme. Por favor intenta en unos momentos. Si necesitas apoyo urgente, usa el botón SOS.',
          isUser: false,
        ));
        _sending = false;
      });
    }
    _scrollToBottom();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Row(
          children: [
            Image.asset(
              'assets/icons/mindra1.png',
              width: 32,
              height: 32,
            ),
            const SizedBox(width: 10),
            const Text('Mindra'),
          ],
        ),
      ),
      body: WebFrame(
        child: Column(
          children: [
            Expanded(
              child: ListView.builder(
                controller: _scrollCtrl,
                padding: const EdgeInsets.symmetric(vertical: 8),
                itemCount: _messages.length + (_sending ? 1 : 0),
                itemBuilder: (_, i) {
                  if (_sending && i == _messages.length) {
                    return const _TypingBubble();
                  }
                  return _buildBubble(_messages[i]);
                },
              ),
            ),
            _buildInput(),
          ],
        ),
      ),
    );
  }

  Widget _buildBubble(_Msg msg) {
    final isUser = msg.isUser;
    final bubble = Container(
      margin: EdgeInsets.only(
        top: 4, bottom: 4,
        left: isUser ? 60 : 12,
        right: isUser ? 12 : 60,
      ),
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
      decoration: BoxDecoration(
        color: isUser ? MindraColors.blue.withValues(alpha: 0.18) : MindraColors.darkSurface,
        borderRadius: BorderRadius.only(
          topLeft: const Radius.circular(18),
          topRight: const Radius.circular(18),
          bottomLeft: Radius.circular(isUser ? 18 : 4),
          bottomRight: Radius.circular(isUser ? 4 : 18),
        ),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.06),
            blurRadius: 4,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(msg.text, style: const TextStyle(fontSize: 15, height: 1.4)),
          if (msg.etiqueta != null) ...[
            const SizedBox(height: 6),
            _Chip(msg.etiqueta!, Colors.orange.shade700),
          ],
          if (msg.probAnsiedad != null) ...[
            const SizedBox(height: 4),
            _Chip(
              'Ansiedad: ${(msg.probAnsiedad! * 100).toStringAsFixed(1)}%',
              msg.probAnsiedad! > 0.5 ? Colors.red.shade600 : Colors.green.shade600,
            ),
          ],
          if (msg.emotionLabel != null) ...[
            const SizedBox(height: 4),
            _Chip('Emoción: ${msg.emotionLabel!}', Colors.indigo),
          ],
        ],
      ),
    );

    return Align(
      alignment: isUser ? Alignment.centerRight : Alignment.centerLeft,
      child: bubble,
    );
  }

  Widget _buildInput() {
    final plan = context.read<AuthProvider>().effectivePlan;
    final hasAudio  = plan?.hasFeature('audio')   ?? false;
    final hasCamera = plan?.hasFeature('imagen')  ?? false;

    return SafeArea(
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          // Badge de imagen facial pendiente
          if (_pendingImage != null)
            Container(
              width: double.infinity,
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 6),
              color: MindraColors.indigo.withValues(alpha: 0.15),
              child: Row(children: [
                const Icon(Icons.face, size: 16, color: MindraColors.indigo),
                const SizedBox(width: 6),
                const Expanded(
                  child: Text('Foto facial lista para enviar',
                      style: TextStyle(fontSize: 12, color: MindraColors.indigo)),
                ),
                GestureDetector(
                  onTap: () => setState(() => _pendingImage = null),
                  child: const Icon(Icons.close, size: 16, color: MindraColors.indigo),
                ),
              ]),
            ),
          Container(
            padding: const EdgeInsets.fromLTRB(12, 8, 12, 8),
            decoration: BoxDecoration(
              color: MindraColors.darkSurface,
              border: const Border(top: BorderSide(color: MindraColors.darkBorder)),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withValues(alpha: 0.18),
                  blurRadius: 8,
                  offset: const Offset(0, -2),
                ),
              ],
            ),
            child: Row(
              children: [
                // Botón de cámara facial (Plus)
                if (hasCamera && !kIsWeb) ...[
                  GestureDetector(
                    onTap: _captureFace,
                    child: AnimatedContainer(
                      duration: const Duration(milliseconds: 200),
                      padding: const EdgeInsets.all(10),
                      decoration: BoxDecoration(
                        shape: BoxShape.circle,
                        color: _pendingImage != null
                            ? MindraColors.indigo
                            : MindraColors.indigo.withValues(alpha: 0.15),
                      ),
                      child: Icon(
                        _pendingImage != null ? Icons.face : Icons.face_outlined,
                        color: MindraColors.indigo,
                        size: 22,
                      ),
                    ),
                  ),
                  const SizedBox(width: 6),
                ],
                Expanded(
                  child: TextField(
                    controller: _textCtrl,
                    maxLines: null,
                    textInputAction: TextInputAction.send,
                    onSubmitted: (_) => _send(),
                    decoration: InputDecoration(
                      hintText: 'Escribe un mensaje…',
                      hintStyle: const TextStyle(color: MindraColors.textSecondary),
                      filled: true,
                      fillColor: MindraColors.dark,
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(24),
                        borderSide: const BorderSide(color: MindraColors.darkBorder),
                      ),
                      enabledBorder: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(24),
                        borderSide: const BorderSide(color: MindraColors.darkBorder),
                      ),
                      focusedBorder: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(24),
                        borderSide: const BorderSide(color: MindraColors.blue, width: 1.5),
                      ),
                      contentPadding:
                          const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                    ),
                  ),
                ),
                if (hasAudio) ...[
                  const SizedBox(width: 8),
                  GestureDetector(
                    onTap: _toggleRecording,
                    child: AnimatedContainer(
                      duration: const Duration(milliseconds: 200),
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        shape: BoxShape.circle,
                        color: _isRecording ? MindraColors.error : MindraColors.blue,
                        boxShadow: _isRecording
                            ? [BoxShadow(color: MindraColors.error.withValues(alpha: 0.4), blurRadius: 12, spreadRadius: 2)]
                            : [],
                      ),
                      child: Icon(
                        _isRecording ? Icons.stop : Icons.mic,
                        color: Colors.white,
                        size: 22,
                      ),
                    ),
                  ),
                ],
                const SizedBox(width: 6),
                IconButton(
                  icon: const Icon(Icons.send_rounded, color: MindraColors.blue),
                  onPressed: () => _send(),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

// ─── Indicador de escritura: tres puntos que rebotan ─────────────────────────

class _TypingBubble extends StatefulWidget {
  const _TypingBubble();

  @override
  State<_TypingBubble> createState() => _TypingBubbleState();
}

class _TypingBubbleState extends State<_TypingBubble>
    with SingleTickerProviderStateMixin {
  late final AnimationController _ctrl;

  @override
  void initState() {
    super.initState();
    _ctrl = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 900),
    )..repeat();
  }

  @override
  void dispose() {
    _ctrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Align(
      alignment: Alignment.centerLeft,
      child: Container(
        margin: const EdgeInsets.only(left: 12, top: 4, bottom: 4, right: 60),
        padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 14),
        decoration: BoxDecoration(
          color: MindraColors.darkSurface,
          borderRadius: const BorderRadius.only(
            topLeft: Radius.circular(18),
            topRight: Radius.circular(18),
            bottomLeft: Radius.circular(4),
            bottomRight: Radius.circular(18),
          ),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.06),
              blurRadius: 4,
              offset: const Offset(0, 2),
            ),
          ],
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: List.generate(3, (i) {
            // Cada punto tiene un desfase de 200 ms respecto al anterior
            final offset = i * 0.22;
            return AnimatedBuilder(
              animation: _ctrl,
              builder: (context, child) {
                // Valor en [0..1] con desfase, convertido a seno para que rebote
                final t = (_ctrl.value - offset).clamp(0.0, 1.0);
                final dy = -6.0 * (t < 0.5 ? t * 2 : (1 - t) * 2);
                return Transform.translate(
                  offset: Offset(0, dy),
                  child: Container(
                    margin: const EdgeInsets.symmetric(horizontal: 3),
                    width: 8,
                    height: 8,
                    decoration: BoxDecoration(
                      color: MindraColors.blue.withValues(alpha: 0.7),
                      shape: BoxShape.circle,
                    ),
                  ),
                );
              },
            );
          }),
        ),
      ),
    );
  }
}

// ─── Chip de etiqueta/emoción ─────────────────────────────────────────────────

class _Chip extends StatelessWidget {
  final String label;
  final Color color;
  const _Chip(this.label, this.color);

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(8),
        border: Border.all(color: color.withValues(alpha: 0.35)),
      ),
      child: Text(label,
          style: TextStyle(color: color, fontSize: 12, fontWeight: FontWeight.w500)),
    );
  }
}

// ─── Pantalla de captura facial (Plus) ───────────────────────────────────────

class _FaceCaptureScreen extends StatefulWidget {
  final CameraDescription camera;
  const _FaceCaptureScreen({required this.camera});

  @override
  State<_FaceCaptureScreen> createState() => _FaceCaptureScreenState();
}

class _FaceCaptureScreenState extends State<_FaceCaptureScreen> {
  late CameraController _ctrl;
  bool _ready = false;
  bool _capturing = false;

  @override
  void initState() {
    super.initState();
    _ctrl = CameraController(widget.camera, ResolutionPreset.medium,
        enableAudio: false);
    _ctrl.initialize().then((_) {
      if (mounted) setState(() => _ready = true);
    });
  }

  @override
  void dispose() {
    _ctrl.dispose();
    super.dispose();
  }

  Future<void> _capture() async {
    if (!_ready || _capturing) return;
    setState(() => _capturing = true);
    try {
      final file = await _ctrl.takePicture();
      if (mounted) Navigator.pop(context, XFile(file.path));
    } catch (e) {
      if (mounted) {
        setState(() => _capturing = false);
        ScaffoldMessenger.of(context)
            .showSnackBar(SnackBar(content: Text('Error al capturar: $e')));
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.black,
      appBar: AppBar(
        backgroundColor: Colors.black,
        foregroundColor: Colors.white,
        title: const Text('Análisis facial'),
      ),
      body: _ready
          ? Stack(
              alignment: Alignment.center,
              children: [
                CameraPreview(_ctrl),
                // Guía oval para centrar el rostro
                CustomPaint(
                  size: MediaQuery.of(context).size,
                  painter: _OvalGuidePainter(),
                ),
                Positioned(
                  bottom: 48,
                  child: Column(children: [
                    const Text('Centra tu rostro en el óvalo',
                        style: TextStyle(color: Colors.white70, fontSize: 13)),
                    const SizedBox(height: 16),
                    GestureDetector(
                      onTap: _capture,
                      child: Container(
                        width: 68,
                        height: 68,
                        decoration: BoxDecoration(
                          shape: BoxShape.circle,
                          border: Border.all(color: Colors.white, width: 3),
                          color: _capturing
                              ? Colors.white54
                              : Colors.white.withValues(alpha: 0.15),
                        ),
                        child: _capturing
                            ? const CircularProgressIndicator(color: Colors.white)
                            : const Icon(Icons.camera_alt,
                                color: Colors.white, size: 32),
                      ),
                    ),
                  ]),
                ),
              ],
            )
          : const Center(child: CircularProgressIndicator()),
    );
  }
}

class _OvalGuidePainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()
      ..color = Colors.white.withValues(alpha: 0.5)
      ..style = PaintingStyle.stroke
      ..strokeWidth = 2;
    canvas.drawOval(
      Rect.fromCenter(
          center: Offset(size.width / 2, size.height * 0.42),
          width: size.width * 0.62,
          height: size.height * 0.46),
      paint,
    );
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}
