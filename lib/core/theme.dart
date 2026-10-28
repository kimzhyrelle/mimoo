import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

/// Mimoo Rider design tokens (from the Stitch export).
///
/// This is the single source of truth for the app. The older screens used a
/// second palette with different names, so the legacy names are kept below as
/// clearly marked aliases. New code should prefer the semantic names at the top.
class MC {
  // Semantic palette (newer screens).
  static const violet = Color(0xFF6D3FE0);
  static const violetDark = Color(0xFF541CC7);
  static const deep = Color(0xFF2E1065);
  static const coral = Color(0xFFC1123E);
  static const coralDark = Color(0xFF98002C);
  static const coralSoft = Color(0xFFFFDADB);
  static const errorSoft = Color(0xFFFFDAD6);
  static const bg = Color(0xFFFDF8FF);
  static const tint = Color(0xFFF1EBFF);
  static const tintLow = Color(0xFFF7F1FF);
  static const tintHi = Color(0xFFEBE5FF);
  static const text = Color(0xFF1C192C);
  static const muted = Color(0xFF494455);
  static const outline = Color(0xFF7A7486);
  static const green = Color(0xFF16A34A);
  static const greenSoft = Color(0xFFDCFCE7);
  static const amber = Color(0xFFB45309);
  static const amberSoft = Color(0xFFFEF3C7);

  // Legacy aliases (older screens). Prefer the names above in new code.
  static const primary = violetDark;
  static const primaryContainer = violet;
  static const primaryFixed = Color(0xFFE8DDFF);
  static const secondary = Color(0xFF6950A2);
  static const secondaryFixed = Color(0xFFEADDFF);
  static const secondaryContainer = Color(0xFFC0A5FE);
  static const onSecondaryContainer = Color(0xFF4F3687);
  static const tertiary = coralDark;
  static const tertiaryContainer = coral;
  static const tertiaryFixed = coralSoft;
  static const error = Color(0xFFBA1A1A);
  static const errorContainer = errorSoft;
  static const surface = bg;
  static const lowest = Colors.white;
  static const low = tintLow;
  static const container = tint;
  static const high = tintHi;
  static const highest = Color(0xFFE6DFFA);
  static const onSurface = text;
  static const onSurfaceVariant = muted;
  static const outlineVariant = Color(0xFFCBC3D7);
  static const inverseSurface = Color(0xFF312E42);
  static const header = deep;
  static const greenBg = greenSoft;
  static const amberBg = amberSoft;
  static const amberText = Color(0xFF92400E);
}

String peso(num v) =>
    '₱${v.toStringAsFixed(2).replaceAllMapped(RegExp(r'\B(?=(\d{3})+(?!\d))'), (m) => ',')}';

ThemeData mimooTheme() {
  final base = ThemeData(
    useMaterial3: true,
    colorScheme: ColorScheme.fromSeed(seedColor: MC.violet, primary: MC.violetDark, surface: MC.bg, error: MC.coral),
    scaffoldBackgroundColor: MC.bg,
  );
  return base.copyWith(textTheme: GoogleFonts.plusJakartaSansTextTheme(base.textTheme).apply(bodyColor: MC.text, displayColor: MC.text));
}

TextStyle ts(double size, {FontWeight w = FontWeight.w400, Color c = MC.text, double? h}) =>
    GoogleFonts.plusJakartaSans(fontSize: size, fontWeight: w, color: c, height: h);

TextStyle _t(double s, FontWeight w, {double? h, double ls = 0, Color c = MC.onSurface}) =>
    GoogleFonts.plusJakartaSans(fontSize: s, fontWeight: w, height: h != null ? h / s : null, letterSpacing: ls, color: c);

/// Legacy text styles used by the older screens. New code should use [ts].
class MT {
  static final headlineXl = _t(28, FontWeight.w800, h: 34, ls: -0.56);
  static final headlineLg = _t(22, FontWeight.w700, h: 28, ls: -0.22);
  static final headlineMd = _t(18, FontWeight.w700, h: 24);
  static final currency = _t(24, FontWeight.w800, h: 28, ls: -0.48);
  static final bodyLg = _t(16, FontWeight.w500, h: 24);
  static final bodyMd = _t(14, FontWeight.w400, h: 20);
  static final bodySm = _t(12, FontWeight.w400, h: 16, c: MC.onSurfaceVariant);
  static final labelLg = _t(14, FontWeight.w700, h: 18, ls: 0.14);
  static final labelMd = _t(12, FontWeight.w600, h: 16);
  static final badge = _t(11, FontWeight.w800, h: 14, ls: 0.66);
}

class MCard extends StatelessWidget {
  final Widget child;
  final EdgeInsets padding;
  final Color color;
  final double radius;
  final VoidCallback? onTap;

  const MCard({
    super.key,
    required this.child,
    this.padding = const EdgeInsets.all(16),
    this.color = Colors.white,
    this.radius = 12,
    this.onTap,
  });

  @override
  Widget build(BuildContext context) => Container(
        width: double.infinity,
        decoration: BoxDecoration(
          color: color,
          borderRadius: BorderRadius.circular(radius),
          boxShadow: const [BoxShadow(color: Color(0x0F2E1065), blurRadius: 8, offset: Offset(0, 2))],
        ),
        child: Material(
          color: Colors.transparent,
          borderRadius: BorderRadius.circular(radius),
          clipBehavior: Clip.antiAlias,
          child: InkWell(
            borderRadius: BorderRadius.circular(radius),
            onTap: onTap,
            child: Padding(padding: padding, child: child),
          ),
        ),
      );
}

class Pill extends StatelessWidget {
  final String text; final Color bg, fg; final IconData? icon;
  const Pill(this.text, {super.key, this.bg = MC.tintHi, this.fg = MC.violetDark, this.icon});
  @override
  Widget build(BuildContext context) => Container(
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
        decoration: BoxDecoration(color: bg, borderRadius: BorderRadius.circular(99)),
        child: Row(mainAxisSize: MainAxisSize.min, children: [
          if (icon != null) ...[Icon(icon, size: 14, color: fg), const SizedBox(width: 4)],
          Flexible(child: Text(text.toUpperCase(), style: ts(11, w: FontWeight.w800, c: fg))),
        ]));
}

enum BtnStyle { primary, outline, danger, tonal }

class PillButton extends StatelessWidget {
  final String label;
  final VoidCallback? onPressed;
  final IconData? icon;
  final IconData? trailing;
  final IconData? leading;
  final BtnStyle style;
  final bool loading;
  final Color? bg, fg;
  final double height;

  const PillButton(
    this.label, {
    super.key,
    this.onPressed,
    this.icon,
    this.trailing = Icons.arrow_forward,
    this.leading,
    this.style = BtnStyle.primary,
    this.loading = false,
    this.bg,
    this.fg,
    this.height = 52,
  });

  /// Legacy secondary style: white background with violet text. Mapped to the
  /// outline style so both old and new screens share one implementation.
  const PillButton.secondary(
    this.label, {
    super.key,
    this.onPressed,
    this.leading,
    this.trailing,
    this.height = 52,
  })  : icon = null,
        style = BtnStyle.outline,
        loading = false,
        bg = null,
        fg = null;

  const PillButton.danger(
    this.label, {
    super.key,
    this.onPressed,
    this.leading,
    this.trailing,
    this.height = 52,
  })  : icon = null,
        style = BtnStyle.danger,
        loading = false,
        bg = null,
        fg = null;

  @override
  Widget build(BuildContext context) {
    final (styleBg, styleFg) = switch (style) {
      BtnStyle.primary => (MC.violet, Colors.white),
      BtnStyle.danger => (MC.coral, Colors.white),
      BtnStyle.tonal => (MC.tintHi, MC.violetDark),
      BtnStyle.outline => (Colors.white, MC.violetDark),
    };
    final background = bg ?? styleBg;
    final foreground = fg ?? styleFg;
    final endIcon = icon ?? trailing;
    return SizedBox(
      width: double.infinity,
      height: height,
      child: ElevatedButton(
        onPressed: loading ? null : onPressed,
        style: ElevatedButton.styleFrom(
          backgroundColor: background,
          foregroundColor: foreground,
          disabledBackgroundColor: MC.highest,
          elevation: style == BtnStyle.primary || style == BtnStyle.danger ? 2 : 0,
          shape: const StadiumBorder(),
          side: style == BtnStyle.outline ? const BorderSide(color: MC.tintHi, width: 1.5) : null,
        ),
        child: loading
            ? SizedBox(width: 20, height: 20, child: CircularProgressIndicator(strokeWidth: 2.5, color: foreground))
            : Row(mainAxisAlignment: MainAxisAlignment.center, children: [
                if (leading != null) ...[Icon(leading, size: 20, color: onPressed == null ? MC.muted : foreground), const SizedBox(width: 8)],
                Flexible(child: Text(label, style: ts(14, w: FontWeight.w700, c: onPressed == null ? MC.muted : foreground))),
                if (endIcon != null) ...[const SizedBox(width: 8), Icon(endIcon, size: 20, color: onPressed == null ? MC.muted : foreground)],
              ]),
      ),
    );
  }
}

class MScaffold extends StatelessWidget {
  final String? title; final Widget body; final VoidCallback? onBack; final Widget? bottom; final List<Widget>? actions;
  const MScaffold({super.key, this.title, required this.body, this.onBack, this.bottom, this.actions});
  @override
  Widget build(BuildContext context) => Scaffold(
        backgroundColor: MC.bg,
        appBar: title == null ? null : AppBar(
            backgroundColor: MC.deep, foregroundColor: Colors.white, actions: actions,
            leading: onBack == null ? null : IconButton(icon: const Icon(Icons.arrow_back), onPressed: onBack),
            title: Text(title!, style: ts(18, w: FontWeight.w700, c: Colors.white))),
        body: SafeArea(child: body),
        bottomNavigationBar: bottom);
}

class IconCircle extends StatelessWidget {
  final IconData icon; final Color bg, fg; final double size;
  const IconCircle(this.icon, {super.key, this.bg = MC.tintHi, this.fg = MC.violetDark, this.size = 40});
  @override
  Widget build(BuildContext context) => Container(width: size, height: size,
      decoration: BoxDecoration(color: bg, shape: BoxShape.circle), child: Icon(icon, size: size * .5, color: fg));
}

class InfoBanner extends StatelessWidget {
  final IconData icon; final String title; final String? body; final Color bg, fg;
  const InfoBanner(this.icon, this.title, {super.key, this.body, this.bg = MC.tint, this.fg = MC.violetDark});
  @override
  Widget build(BuildContext context) => Container(
        padding: const EdgeInsets.all(12), decoration: BoxDecoration(color: bg, borderRadius: BorderRadius.circular(12)),
        child: Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Icon(icon, color: fg, size: 20), const SizedBox(width: 10),
          Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Text(title, style: ts(13, w: FontWeight.w700, c: fg)),
            if (body != null) Text(body!, style: ts(12, c: MC.muted, h: 1.4)),
          ])),
        ]));
}
