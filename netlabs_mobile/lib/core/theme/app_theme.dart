import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

/// Netlabs Design System — Slate/Indigo modern palette.
class NetlabsTheme {
  NetlabsTheme._();

  static const Color primary = Color(0xFF4F46E5);
  static const Color primaryLight = Color(0xFF818CF8);
  static const Color primaryDark = Color(0xFF3730A3);
  static const Color accent = Color(0xFF06B6D4);
  static const Color success = Color(0xFF10B981);
  static const Color warning = Color(0xFFF59E0B);
  static const Color danger = Color(0xFFEF4444);

  static const Color surface = Color(0xFFF8FAFC);
  static const Color card = Color(0xFFFFFFFF);
  static const Color dark = Color(0xFF0F172A);
  static const Color textPrimary = Color(0xFF1E293B);
  static const Color textSecondary = Color(0xFF64748B);
  static const Color textMuted = Color(0xFF94A3B8);
  static const Color border = Color(0xFFE2E8F0);

  static const double radiusSm = 12;
  static const double radiusMd = 16;
  static const double radiusLg = 20;
  static const double radiusXl = 24;

  static List<BoxShadow> get shadowLg => [
        BoxShadow(color: dark.withAlpha(10), blurRadius: 24, offset: const Offset(0, 8), spreadRadius: -4),
      ];
  static List<BoxShadow> get shadowMd => [
        BoxShadow(color: dark.withAlpha(12), blurRadius: 12, offset: const Offset(0, 4)),
      ];
  static List<BoxShadow> get shadowSm => [
        BoxShadow(color: dark.withAlpha(10), blurRadius: 4, offset: const Offset(0, 1)),
      ];

  static ThemeData get light => ThemeData(
        useMaterial3: true,
        brightness: Brightness.light,
        scaffoldBackgroundColor: surface,
        colorScheme: const ColorScheme.light(
          primary: primary, secondary: accent, surface: card,
          error: danger, onPrimary: Colors.white, onSecondary: Colors.white,
          onSurface: textPrimary, onError: Colors.white,
        ),
        textTheme: GoogleFonts.interTextTheme().copyWith(
          displayLarge: GoogleFonts.inter(fontSize: 32, fontWeight: FontWeight.w800, color: dark, letterSpacing: -0.5),
          headlineLarge: GoogleFonts.inter(fontSize: 24, fontWeight: FontWeight.w700, color: dark, letterSpacing: -0.3),
          titleLarge: GoogleFonts.inter(fontSize: 18, fontWeight: FontWeight.w600, color: textPrimary),
          titleMedium: GoogleFonts.inter(fontSize: 16, fontWeight: FontWeight.w600, color: textPrimary),
          bodyLarge: GoogleFonts.inter(fontSize: 15, fontWeight: FontWeight.w400, color: textPrimary, height: 1.6),
          bodyMedium: GoogleFonts.inter(fontSize: 14, fontWeight: FontWeight.w400, color: textSecondary, height: 1.5),
          labelLarge: GoogleFonts.inter(fontSize: 14, fontWeight: FontWeight.w600, color: Colors.white),
          labelSmall: GoogleFonts.inter(fontSize: 11, fontWeight: FontWeight.w600, color: textMuted, letterSpacing: 0.5),
        ),
        cardTheme: CardThemeData(color: card, elevation: 0, shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(radiusLg)), margin: EdgeInsets.zero),
        appBarTheme: AppBarTheme(backgroundColor: surface, elevation: 0, centerTitle: true,
          titleTextStyle: GoogleFonts.inter(fontSize: 17, fontWeight: FontWeight.w700, color: textPrimary),
          iconTheme: const IconThemeData(color: textPrimary),
        ),
        bottomNavigationBarTheme: const BottomNavigationBarThemeData(
          backgroundColor: card, elevation: 0, type: BottomNavigationBarType.fixed,
          selectedItemColor: primary, unselectedItemColor: textMuted,
          selectedLabelStyle: TextStyle(fontSize: 11, fontWeight: FontWeight.w600),
          unselectedLabelStyle: TextStyle(fontSize: 11, fontWeight: FontWeight.w500),
        ),
        inputDecorationTheme: InputDecorationTheme(
          filled: true, fillColor: surface,
          contentPadding: const EdgeInsets.symmetric(horizontal: 18, vertical: 14),
          border: OutlineInputBorder(borderRadius: BorderRadius.circular(radiusSm), borderSide: BorderSide.none),
          enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(radiusSm), borderSide: const BorderSide(color: border)),
          focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(radiusSm), borderSide: const BorderSide(color: primary, width: 1.5)),
          hintStyle: GoogleFonts.inter(fontSize: 14, color: textMuted),
        ),
        elevatedButtonTheme: ElevatedButtonThemeData(
          style: ElevatedButton.styleFrom(backgroundColor: primary, foregroundColor: Colors.white, elevation: 0,
            padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 14),
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(radiusMd)),
            textStyle: GoogleFonts.inter(fontSize: 15, fontWeight: FontWeight.w600)),
        ),
        chipTheme: ChipThemeData(backgroundColor: surface,
          labelStyle: GoogleFonts.inter(fontSize: 12, color: textPrimary),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(99)),
          side: const BorderSide(color: border),
          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
        ),
      );
}
