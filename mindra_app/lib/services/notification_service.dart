import 'dart:math';
import 'package:flutter/foundation.dart' show kIsWeb;
import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:timezone/data/latest_all.dart' as tz;
import 'package:timezone/timezone.dart' as tz;

class NotificationService {
  static const _channelId      = 'mindra_reminders';
  static const _channelName    = 'Recordatorios Mindra';
  static const _notifId        = 1;
  static const _notifStreakId  = 2;
  static const _prefKey        = 'reminder_hour';   // -1 = desactivado
  static const _prefStreak     = 'last_streak';

  final FlutterLocalNotificationsPlugin _plugin =
      FlutterLocalNotificationsPlugin();
  final _rng = Random();

  // ── Mensajes contextuales por racha ───────────────────────────────────────

  static const _msgNoStreak = [
    ('¿Cómo te sientes hoy? 💙', 'Tómate un momento para hablar con Mindra.'),
    ('Tu bienestar importa 🌿', 'Registra tu ánimo de hoy en menos de 30 segundos.'),
    ('Mindra está aquí 🤝', '¿Qué tal ha sido tu día? Cuéntame.'),
  ];

  static const _msgStreakShort = [  // 2–6 días
    ('¡Llevas {n} días seguidos! 🔥', 'No pierdas la racha — registra tu ánimo hoy.'),
    ('{n} días activo/a 💪', 'Estás construyendo un hábito saludable. ¡Sigue!'),
    ('Racha de {n} días 🎯', 'Un poco cada día marca la diferencia.'),
  ];

  static const _msgStreakMedium = [  // 7–29 días
    ('🔥 ¡Una semana completa!', '¿Cómo te sientes después de {n} días con Mindra?'),
    ('{n} días de autocuidado 🏆', 'Tu constancia está dando frutos. Check-in de hoy.'),
    ('Racha de {n} días 🌟', 'Eres de las personas más comprometidas con su bienestar.'),
  ];

  static const _msgStreakLong = [  // 30+ días
    ('🏆 ¡{n} días increíbles!', 'Tu constancia es inspiradora. ¿Cómo estás hoy?'),
    ('Un mes de Mindra 🎉', '{n} días cuidando tu salud mental. ¡Impresionante!'),
    ('Leyenda de {n} días 🌙', 'Mindra es parte de tu rutina. ¡Comparte cómo te sientes!'),
  ];

  Future<void> init() async {
    if (kIsWeb) return;
    tz.initializeTimeZones();

    const android = AndroidInitializationSettings('@mipmap/ic_launcher');
    const ios     = DarwinInitializationSettings(
      requestAlertPermission: true,
      requestBadgePermission: true,
      requestSoundPermission: true,
    );
    await _plugin.initialize(
      const InitializationSettings(android: android, iOS: ios),
    );
  }

  /// Devuelve la hora configurada (-1 si está desactivada).
  Future<int> getReminderHour() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getInt(_prefKey) ?? -1;
  }

  /// Programa el recordatorio diario con mensaje adaptado a la racha.
  Future<void> scheduleDaily(int hour, {int streak = 0}) async {
    if (kIsWeb) return;
    await _plugin.cancelAll();

    if (hour < 0) {
      final prefs = await SharedPreferences.getInstance();
      await prefs.setInt(_prefKey, -1);
      return;
    }

    final prefs = await SharedPreferences.getInstance();
    await prefs.setInt(_prefKey, hour);
    await prefs.setInt(_prefStreak, streak);

    final msg = _pickMessage(streak);

    final now = tz.TZDateTime.now(tz.local);
    var scheduled =
        tz.TZDateTime(tz.local, now.year, now.month, now.day, hour);
    if (scheduled.isBefore(now)) {
      scheduled = scheduled.add(const Duration(days: 1));
    }

    const androidDetails = AndroidNotificationDetails(
      _channelId,
      _channelName,
      channelDescription: 'Recordatorio diario de check-in emocional',
      importance: Importance.defaultImportance,
      priority: Priority.defaultPriority,
    );
    const iosDetails = DarwinNotificationDetails();
    const details = NotificationDetails(android: androidDetails, iOS: iosDetails);

    await _plugin.zonedSchedule(
      _notifId,
      msg.$1,
      msg.$2,
      scheduled,
      details,
      androidScheduleMode: AndroidScheduleMode.exactAllowWhileIdle,
      uiLocalNotificationDateInterpretation:
          UILocalNotificationDateInterpretation.absoluteTime,
      matchDateTimeComponents: DateTimeComponents.time,
    );
  }

  /// Envía notificación de logro de racha de forma inmediata.
  Future<void> sendStreakAchievement(int streak) async {
    if (kIsWeb) return;

    final (title, body) = _streakAchievementMessage(streak);
    if (title == null) return;

    const androidDetails = AndroidNotificationDetails(
      _channelId,
      _channelName,
      importance: Importance.high,
      priority: Priority.high,
    );
    const details = NotificationDetails(
        android: androidDetails, iOS: DarwinNotificationDetails());

    await _plugin.show(_notifStreakId, title, body, details);
  }

  /// Reprograma con la racha actualizada (llamar tras cada check-in exitoso).
  Future<void> updateStreak(int newStreak) async {
    if (kIsWeb) return;
    final prefs = await SharedPreferences.getInstance();
    final hour  = prefs.getInt(_prefKey) ?? -1;
    if (hour >= 0) {
      await scheduleDaily(hour, streak: newStreak);
    }
    await sendStreakAchievement(newStreak);
  }

  Future<void> cancel() => scheduleDaily(-1);

  // ── Helpers ───────────────────────────────────────────────────────────────

  (String, String) _pickMessage(int streak) {
    List<(String, String)> pool;
    if (streak >= 30) {
      pool = _msgStreakLong;
    } else if (streak >= 7) {
      pool = _msgStreakMedium;
    } else if (streak >= 2) {
      pool = _msgStreakShort;
    } else {
      pool = _msgNoStreak;
    }
    final (title, body) = pool[_rng.nextInt(pool.length)];
    return (
      title.replaceAll('{n}', '$streak'),
      body.replaceAll('{n}', '$streak'),
    );
  }

  (String?, String?) _streakAchievementMessage(int streak) {
    if (streak == 3)  return ('🔥 ¡3 días seguidos!', 'Estás construyendo un hábito saludable.');
    if (streak == 7)  return ('🏅 ¡Una semana!', '7 días de autocuidado. ¡Eres increíble!');
    if (streak == 14) return ('💎 ¡Dos semanas!', 'Tu constancia con Mindra es admirable.');
    if (streak == 30) return ('🏆 ¡Un mes!', '30 días de bienestar. ¡Logro desbloqueado!');
    if (streak % 30 == 0) {
      return ('🌟 ¡${streak ~/ 30} meses!', '$streak días de autocuidado continuo. Impresionante.');
    }
    return (null, null);
  }
}
