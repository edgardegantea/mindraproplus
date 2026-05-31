import 'dart:io';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';

/// Handler de mensajes en background (top-level, fuera de clase)
@pragma('vm:entry-point')
Future<void> firebaseMessagingBackgroundHandler(RemoteMessage message) async {
  // No necesita inicializar Firebase aquí si ya está inicializado en main.
  // Solo logueamos; la notificación la gestiona FCM directamente en background.
}

class PushNotificationService {
  static final _messaging = FirebaseMessaging.instance;
  static final _localNotifications = FlutterLocalNotificationsPlugin();

  static const _androidChannel = AndroidNotificationChannel(
    'mindra_alerts',
    'Alertas Mindra',
    description: 'Alertas de bienestar y crisis de Mindra',
    importance: Importance.high,
    playSound: true,
  );

  /// Inicializa FCM y notificaciones locales.
  /// Llama desde main() después de Firebase.initializeApp().
  static Future<void> init() async {
    // Registrar handler de background
    FirebaseMessaging.onBackgroundMessage(firebaseMessagingBackgroundHandler);

    // Pedir permiso (iOS/Android 13+)
    await _messaging.requestPermission(
      alert: true,
      badge: true,
      sound: true,
    );

    // Crear canal Android
    await _localNotifications
        .resolvePlatformSpecificImplementation<
            AndroidFlutterLocalNotificationsPlugin>()
        ?.createNotificationChannel(_androidChannel);

    // Inicializar plugin de notificaciones locales
    const androidSettings =
        AndroidInitializationSettings('@mipmap/ic_launcher');
    const darwinSettings = DarwinInitializationSettings();
    await _localNotifications.initialize(
      const InitializationSettings(
          android: androidSettings, iOS: darwinSettings),
    );

    // Escuchar mensajes en foreground
    FirebaseMessaging.onMessage.listen(_showLocalNotification);

    // Escuchar tap en notificación cuando la app estaba en background
    FirebaseMessaging.onMessageOpenedApp.listen(_handleNotificationTap);
  }

  /// Obtiene el FCM token del dispositivo.
  /// Devuelve null si no está disponible (simulador, sin permisos, etc.)
  static Future<String?> getToken() async {
    try {
      if (Platform.isIOS) {
        await _messaging.getAPNSToken();
      }
      return await _messaging.getToken();
    } catch (_) {
      return null;
    }
  }

  /// Escucha cambios de token (rotación automática de FCM).
  static Stream<String> get onTokenRefresh => _messaging.onTokenRefresh;

  static void _showLocalNotification(RemoteMessage message) {
    final notification = message.notification;
    if (notification == null) return;

    _localNotifications.show(
      message.hashCode,
      notification.title,
      notification.body,
      NotificationDetails(
        android: AndroidNotificationDetails(
          _androidChannel.id,
          _androidChannel.name,
          channelDescription: _androidChannel.description,
          importance: Importance.high,
          priority: Priority.high,
          icon: '@mipmap/ic_launcher',
        ),
        iOS: const DarwinNotificationDetails(
          presentAlert: true,
          presentBadge: true,
          presentSound: true,
        ),
      ),
    );
  }

  static void _handleNotificationTap(RemoteMessage message) {
    // Navegar a la pantalla correcta según message.data['type'].
    // La navegación real depende del contexto — el router la maneja
    // cuando se recarga el estado del AuthProvider.
  }
}
