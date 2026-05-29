import 'package:flutter/material.dart';

// ─── Colores extraídos del logo Mindra ────────────────────────────────────────
class MindraColors {
  MindraColors._();

  // Primarios
  static const blue    = Color(0xFF00A0F0); // Azul eléctrico del logo
  static const violet  = Color(0xFF7C3CC8); // Violeta del logo
  static const indigo  = Color(0xFF3C14B4); // Índigo del logo

  // Fondo y superficies (dark)
  static const dark        = Color(0xFF0A0F1C); // Fondo principal
  static const darkSurface = Color(0xFF131929); // Cards y paneles
  static const darkBorder  = Color(0xFF1E2A42); // Bordes sutiles

  // Fondo y superficies (light)
  static const lightBg      = Color(0xFFF5F7FC); // Fondo principal claro
  static const lightSurface = Color(0xFFFFFFFF); // Cards y paneles claros
  static const lightBorder  = Color(0xFFE2E8F0); // Bordes sutiles claros

  // Texto (dark)
  static const textPrimary   = Color(0xFFE8EFFE); // Texto principal (dark)
  static const textSecondary = Color(0xFF8A9BBF); // Texto secundario (dark)

  // Texto (light)
  static const textPrimaryLight   = Color(0xFF0F172A); // Texto principal (light)
  static const textSecondaryLight = Color(0xFF64748B); // Texto secundario (light)

  // Semánticos
  static const success = Color(0xFF00D084); // Verde éxito
  static const warning = Color(0xFFFFB038); // Naranja advertencia
  static const error   = Color(0xFFFF4D6A); // Rojo error

  // Gradientes del logo
  static const gradientMain = LinearGradient(
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
    colors: [blue, violet],
  );

  static const gradientDeep = LinearGradient(
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
    colors: [violet, indigo],
  );
}

// ─── Tema Mindra ─────────────────────────────────────────────────────────────
class MindraTheme {
  MindraTheme._();

  static ThemeData get light {
    final base = ColorScheme.light(
      primary:          MindraColors.blue,
      onPrimary:        Colors.white,
      secondary:        MindraColors.violet,
      onSecondary:      Colors.white,
      tertiary:         MindraColors.indigo,
      onTertiary:       Colors.white,
      surface:          MindraColors.lightSurface,
      onSurface:        MindraColors.textPrimaryLight,
      error:            MindraColors.error,
      onError:          Colors.white,
      outline:          MindraColors.lightBorder,
      surfaceContainerHighest: MindraColors.lightBorder,
    );

    return ThemeData(
      useMaterial3: true,
      colorScheme: base,
      scaffoldBackgroundColor: MindraColors.lightBg,
      brightness: Brightness.light,

      appBarTheme: const AppBarTheme(
        backgroundColor: MindraColors.lightBg,
        surfaceTintColor: Colors.transparent,
        elevation: 0,
        centerTitle: false,
        foregroundColor: MindraColors.textPrimaryLight,
        titleTextStyle: TextStyle(
          color: MindraColors.textPrimaryLight,
          fontSize: 20,
          fontWeight: FontWeight.w600,
          letterSpacing: 0.3,
        ),
      ),

      navigationBarTheme: NavigationBarThemeData(
        backgroundColor: MindraColors.lightSurface,
        indicatorColor: MindraColors.blue.withValues(alpha: 0.15),
        iconTheme: WidgetStateProperty.resolveWith((states) {
          if (states.contains(WidgetState.selected)) {
            return const IconThemeData(color: MindraColors.blue);
          }
          return const IconThemeData(color: MindraColors.textSecondaryLight);
        }),
        labelTextStyle: WidgetStateProperty.resolveWith((states) {
          if (states.contains(WidgetState.selected)) {
            return const TextStyle(color: MindraColors.blue, fontSize: 12, fontWeight: FontWeight.w600);
          }
          return const TextStyle(color: MindraColors.textSecondaryLight, fontSize: 12);
        }),
      ),

      cardTheme: CardThemeData(
        color: MindraColors.lightSurface,
        surfaceTintColor: Colors.transparent,
        elevation: 0,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(16),
          side: const BorderSide(color: MindraColors.lightBorder),
        ),
      ),

      filledButtonTheme: FilledButtonThemeData(
        style: FilledButton.styleFrom(
          backgroundColor: MindraColors.blue,
          foregroundColor: Colors.white,
          padding: const EdgeInsets.symmetric(vertical: 14, horizontal: 24),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
          textStyle: const TextStyle(fontSize: 15, fontWeight: FontWeight.w600),
        ),
      ),

      outlinedButtonTheme: OutlinedButtonThemeData(
        style: OutlinedButton.styleFrom(
          foregroundColor: MindraColors.blue,
          side: const BorderSide(color: MindraColors.blue),
          padding: const EdgeInsets.symmetric(vertical: 14, horizontal: 24),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
        ),
      ),

      textButtonTheme: TextButtonThemeData(
        style: TextButton.styleFrom(foregroundColor: MindraColors.blue),
      ),

      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: MindraColors.lightSurface,
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: MindraColors.lightBorder),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: MindraColors.lightBorder),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: MindraColors.blue, width: 2),
        ),
        labelStyle: const TextStyle(color: MindraColors.textSecondaryLight),
        hintStyle: const TextStyle(color: MindraColors.textSecondaryLight),
        prefixIconColor: MindraColors.textSecondaryLight,
        suffixIconColor: MindraColors.textSecondaryLight,
      ),

      textTheme: const TextTheme(
        displayLarge:  TextStyle(color: MindraColors.textPrimaryLight, fontWeight: FontWeight.bold),
        displayMedium: TextStyle(color: MindraColors.textPrimaryLight, fontWeight: FontWeight.bold),
        headlineLarge: TextStyle(color: MindraColors.textPrimaryLight, fontWeight: FontWeight.bold),
        headlineMedium:TextStyle(color: MindraColors.textPrimaryLight, fontWeight: FontWeight.w600),
        titleLarge:    TextStyle(color: MindraColors.textPrimaryLight, fontWeight: FontWeight.w600),
        titleMedium:   TextStyle(color: MindraColors.textPrimaryLight, fontWeight: FontWeight.w500),
        bodyLarge:     TextStyle(color: MindraColors.textPrimaryLight),
        bodyMedium:    TextStyle(color: MindraColors.textSecondaryLight),
        labelLarge:    TextStyle(color: MindraColors.textPrimaryLight, fontWeight: FontWeight.w600),
      ),

      dividerTheme: const DividerThemeData(color: MindraColors.lightBorder, thickness: 1),

      snackBarTheme: SnackBarThemeData(
        backgroundColor: MindraColors.lightSurface,
        contentTextStyle: const TextStyle(color: MindraColors.textPrimaryLight),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
        behavior: SnackBarBehavior.floating,
      ),
    );
  }

  static ThemeData get dark {
    final base = ColorScheme.dark(
      primary:          MindraColors.blue,
      onPrimary:        Colors.white,
      secondary:        MindraColors.violet,
      onSecondary:      Colors.white,
      tertiary:         MindraColors.indigo,
      onTertiary:       Colors.white,
      surface:          MindraColors.darkSurface,
      onSurface:        MindraColors.textPrimary,
      error:            MindraColors.error,
      onError:          Colors.white,
      outline:          MindraColors.darkBorder,
      surfaceContainerHighest: MindraColors.darkBorder,
    );

    return ThemeData(
      useMaterial3: true,
      colorScheme: base,
      scaffoldBackgroundColor: MindraColors.dark,
      brightness: Brightness.dark,

      // ── AppBar ──────────────────────────────────────────────────────────────
      appBarTheme: const AppBarTheme(
        backgroundColor: MindraColors.dark,
        surfaceTintColor: Colors.transparent,
        elevation: 0,
        centerTitle: false,
        foregroundColor: MindraColors.textPrimary,
        titleTextStyle: TextStyle(
          color: MindraColors.textPrimary,
          fontSize: 20,
          fontWeight: FontWeight.w600,
          letterSpacing: 0.3,
        ),
      ),

      // ── NavigationBar ───────────────────────────────────────────────────────
      navigationBarTheme: NavigationBarThemeData(
        backgroundColor: MindraColors.darkSurface,
        indicatorColor: MindraColors.blue.withValues(alpha: 0.2),
        iconTheme: WidgetStateProperty.resolveWith((states) {
          if (states.contains(WidgetState.selected)) {
            return const IconThemeData(color: MindraColors.blue);
          }
          return const IconThemeData(color: MindraColors.textSecondary);
        }),
        labelTextStyle: WidgetStateProperty.resolveWith((states) {
          if (states.contains(WidgetState.selected)) {
            return const TextStyle(
                color: MindraColors.blue,
                fontSize: 12,
                fontWeight: FontWeight.w600);
          }
          return const TextStyle(
              color: MindraColors.textSecondary, fontSize: 12);
        }),
      ),

      // ── Cards ───────────────────────────────────────────────────────────────
      cardTheme: CardThemeData(
        color: MindraColors.darkSurface,
        surfaceTintColor: Colors.transparent,
        elevation: 0,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(16),
          side: const BorderSide(color: MindraColors.darkBorder),
        ),
      ),

      // ── FilledButton ────────────────────────────────────────────────────────
      filledButtonTheme: FilledButtonThemeData(
        style: FilledButton.styleFrom(
          backgroundColor: MindraColors.blue,
          foregroundColor: Colors.white,
          padding: const EdgeInsets.symmetric(vertical: 14, horizontal: 24),
          shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(12)),
          textStyle: const TextStyle(
              fontSize: 15, fontWeight: FontWeight.w600),
        ),
      ),

      // ── OutlinedButton ──────────────────────────────────────────────────────
      outlinedButtonTheme: OutlinedButtonThemeData(
        style: OutlinedButton.styleFrom(
          foregroundColor: MindraColors.blue,
          side: const BorderSide(color: MindraColors.blue),
          padding: const EdgeInsets.symmetric(vertical: 14, horizontal: 24),
          shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(12)),
        ),
      ),

      // ── TextButton ──────────────────────────────────────────────────────────
      textButtonTheme: TextButtonThemeData(
        style: TextButton.styleFrom(
          foregroundColor: MindraColors.blue,
        ),
      ),

      // ── TextField ───────────────────────────────────────────────────────────
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: MindraColors.darkSurface,
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: MindraColors.darkBorder),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: MindraColors.darkBorder),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: MindraColors.blue, width: 2),
        ),
        labelStyle: const TextStyle(color: MindraColors.textSecondary),
        hintStyle: const TextStyle(color: MindraColors.textSecondary),
        prefixIconColor: MindraColors.textSecondary,
        suffixIconColor: MindraColors.textSecondary,
      ),

      // ── Texto ───────────────────────────────────────────────────────────────
      textTheme: const TextTheme(
        displayLarge:  TextStyle(color: MindraColors.textPrimary, fontWeight: FontWeight.bold),
        displayMedium: TextStyle(color: MindraColors.textPrimary, fontWeight: FontWeight.bold),
        headlineLarge: TextStyle(color: MindraColors.textPrimary, fontWeight: FontWeight.bold),
        headlineMedium:TextStyle(color: MindraColors.textPrimary, fontWeight: FontWeight.w600),
        titleLarge:    TextStyle(color: MindraColors.textPrimary, fontWeight: FontWeight.w600),
        titleMedium:   TextStyle(color: MindraColors.textPrimary, fontWeight: FontWeight.w500),
        bodyLarge:     TextStyle(color: MindraColors.textPrimary),
        bodyMedium:    TextStyle(color: MindraColors.textSecondary),
        labelLarge:    TextStyle(color: MindraColors.textPrimary, fontWeight: FontWeight.w600),
      ),

      // ── Divider ─────────────────────────────────────────────────────────────
      dividerTheme: const DividerThemeData(
        color: MindraColors.darkBorder,
        thickness: 1,
      ),

      // ── SnackBar ────────────────────────────────────────────────────────────
      snackBarTheme: SnackBarThemeData(
        backgroundColor: MindraColors.darkSurface,
        contentTextStyle: const TextStyle(color: MindraColors.textPrimary),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
        behavior: SnackBarBehavior.floating,
      ),
    );
  }
}
