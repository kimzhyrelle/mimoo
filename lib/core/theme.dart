import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:intl/intl.dart';

abstract final class MS {
  static const xs = 4.0;
  static const sm = 8.0;
  static const md = 12.0;
  static const lg = 16.0;
  static const xl = 24.0;
  static const xxl = 32.0;
  static const pagePadding = EdgeInsets.fromLTRB(20, 16, 20, 24);
}

abstract final class MRadii {
  static const card = 12.0;
  static const control = 10.0;
  static const field = 99.0;
  static const pill = 99.0;
  static const stadium = StadiumBorder();
}

/// Mimoo Rider design tokens (from the Stitch export).
///
/// This is the single source of truth for the app. The older screens used a
/// second palette with different names, so the legacy names are kept below as
/// clearly marked aliases. New code should prefer the semantic names at the top.
class MC {
  static MimooColors of(BuildContext context) {
    final theme = Theme.of(context);
    return theme.extension<MimooColors>() ??
        (theme.brightness == Brightness.dark
            ? MimooColors.dark
            : MimooColors.light);
  }

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

class MimooColors extends ThemeExtension<MimooColors> {
  final Color bg;
  final Color coral;
  final Color container;
  final Color errorContainer;
  final Color green;
  final Color greenSoft;
  final Color highest;
  final Color low;
  final Color lowest;
  final Color muted;
  final Color onSurfaceVariant;
  final Color outline;
  final Color outlineVariant;
  final Color primary;
  final Color primaryContainer;
  final Color primaryFixed;
  final Color secondary;
  final Color surface;
  final Color text;
  final Color tertiaryContainer;
  final Color tint;
  final Color tintLow;

  const MimooColors({
    required this.bg,
    required this.coral,
    required this.container,
    required this.errorContainer,
    required this.green,
    required this.greenSoft,
    required this.highest,
    required this.low,
    required this.lowest,
    required this.muted,
    required this.onSurfaceVariant,
    required this.outline,
    required this.outlineVariant,
    required this.primary,
    required this.primaryContainer,
    required this.primaryFixed,
    required this.secondary,
    required this.surface,
    required this.text,
    required this.tertiaryContainer,
    required this.tint,
    required this.tintLow,
  });

  static const light = MimooColors(
    bg: MC.bg,
    coral: MC.coral,
    container: MC.container,
    errorContainer: MC.errorContainer,
    green: MC.green,
    greenSoft: MC.greenSoft,
    highest: MC.highest,
    low: MC.low,
    lowest: MC.lowest,
    muted: MC.muted,
    onSurfaceVariant: MC.onSurfaceVariant,
    outline: MC.outline,
    outlineVariant: MC.outlineVariant,
    primary: MC.primary,
    primaryContainer: MC.primaryContainer,
    primaryFixed: MC.primaryFixed,
    secondary: MC.secondary,
    surface: MC.surface,
    text: MC.text,
    tertiaryContainer: MC.tertiaryContainer,
    tint: MC.tint,
    tintLow: MC.tintLow,
  );

  static const dark = MimooColors(
    bg: Color(0xFF15111D),
    coral: Color(0xFFFF8A9B),
    container: Color(0xFF332D40),
    errorContainer: Color(0xFF5A1C26),
    green: Color(0xFF5CD17C),
    greenSoft: Color(0xFF163D28),
    highest: Color(0xFF40394D),
    low: Color(0xFF292535),
    lowest: Color(0xFF201C29),
    muted: Color(0xFFD0C8D9),
    onSurfaceVariant: Color(0xFFD0C8D9),
    outline: Color(0xFF9A91A6),
    outlineVariant: Color(0xFF51495D),
    primary: Color(0xFF9A7AFF),
    primaryContainer: Color(0xFF8056EF),
    primaryFixed: Color(0xFF4B3380),
    secondary: Color(0xFFCEB8FF),
    surface: Color(0xFF15111D),
    text: Color(0xFFF4EFFA),
    tertiaryContainer: Color(0xFFFF8DA4),
    tint: Color(0xFF302747),
    tintLow: Color(0xFF262138),
  );

  @override
  MimooColors copyWith({
    Color? bg,
    Color? coral,
    Color? container,
    Color? errorContainer,
    Color? green,
    Color? greenSoft,
    Color? highest,
    Color? low,
    Color? lowest,
    Color? muted,
    Color? onSurfaceVariant,
    Color? outline,
    Color? outlineVariant,
    Color? primary,
    Color? primaryContainer,
    Color? primaryFixed,
    Color? secondary,
    Color? surface,
    Color? text,
    Color? tertiaryContainer,
    Color? tint,
    Color? tintLow,
  }) =>
      MimooColors(
        bg: bg ?? this.bg,
        coral: coral ?? this.coral,
        container: container ?? this.container,
        errorContainer: errorContainer ?? this.errorContainer,
        green: green ?? this.green,
        greenSoft: greenSoft ?? this.greenSoft,
        highest: highest ?? this.highest,
        low: low ?? this.low,
        lowest: lowest ?? this.lowest,
        muted: muted ?? this.muted,
        onSurfaceVariant: onSurfaceVariant ?? this.onSurfaceVariant,
        outline: outline ?? this.outline,
        outlineVariant: outlineVariant ?? this.outlineVariant,
        primary: primary ?? this.primary,
        primaryContainer: primaryContainer ?? this.primaryContainer,
        primaryFixed: primaryFixed ?? this.primaryFixed,
        secondary: secondary ?? this.secondary,
        surface: surface ?? this.surface,
        text: text ?? this.text,
        tertiaryContainer: tertiaryContainer ?? this.tertiaryContainer,
        tint: tint ?? this.tint,
        tintLow: tintLow ?? this.tintLow,
      );

  @override
  MimooColors lerp(ThemeExtension<MimooColors>? other, double t) {
    if (other is! MimooColors) return this;
    return MimooColors(
      bg: Color.lerp(bg, other.bg, t) ?? bg,
      coral: Color.lerp(coral, other.coral, t) ?? coral,
      container: Color.lerp(container, other.container, t) ?? container,
      errorContainer:
          Color.lerp(errorContainer, other.errorContainer, t) ?? errorContainer,
      green: Color.lerp(green, other.green, t) ?? green,
      greenSoft: Color.lerp(greenSoft, other.greenSoft, t) ?? greenSoft,
      highest: Color.lerp(highest, other.highest, t) ?? highest,
      low: Color.lerp(low, other.low, t) ?? low,
      lowest: Color.lerp(lowest, other.lowest, t) ?? lowest,
      muted: Color.lerp(muted, other.muted, t) ?? muted,
      onSurfaceVariant:
          Color.lerp(onSurfaceVariant, other.onSurfaceVariant, t) ??
              onSurfaceVariant,
      outline: Color.lerp(outline, other.outline, t) ?? outline,
      outlineVariant:
          Color.lerp(outlineVariant, other.outlineVariant, t) ?? outlineVariant,
      primary: Color.lerp(primary, other.primary, t) ?? primary,
      primaryContainer:
          Color.lerp(primaryContainer, other.primaryContainer, t) ??
              primaryContainer,
      primaryFixed:
          Color.lerp(primaryFixed, other.primaryFixed, t) ?? primaryFixed,
      secondary: Color.lerp(secondary, other.secondary, t) ?? secondary,
      surface: Color.lerp(surface, other.surface, t) ?? surface,
      text: Color.lerp(text, other.text, t) ?? text,
      tertiaryContainer:
          Color.lerp(tertiaryContainer, other.tertiaryContainer, t) ??
              tertiaryContainer,
      tint: Color.lerp(tint, other.tint, t) ?? tint,
      tintLow: Color.lerp(tintLow, other.tintLow, t) ?? tintLow,
    );
  }
}

String peso(num v) => '₱${NumberFormat('#,##0.00', 'en_PH').format(v)}';

ThemeData mimooTheme({Brightness brightness = Brightness.light}) {
  final dark = brightness == Brightness.dark;
  final palette = dark ? MimooColors.dark : MimooColors.light;
  final base = ThemeData(
    useMaterial3: true,
    brightness: brightness,
    colorScheme: ColorScheme.fromSeed(
      seedColor: MC.violet,
      brightness: brightness,
      primary: palette.primary,
      surface: palette.bg,
      error: palette.coral,
    ),
    scaffoldBackgroundColor: palette.bg,
  );
  return base.copyWith(
    textTheme: GoogleFonts.plusJakartaSansTextTheme(base.textTheme)
        .apply(bodyColor: palette.text, displayColor: palette.text),
    elevatedButtonTheme: ElevatedButtonThemeData(
      style: ElevatedButton.styleFrom(
        minimumSize: const Size(48, 48),
        shape: MRadii.stadium,
      ),
    ),
    filledButtonTheme: FilledButtonThemeData(
      style: FilledButton.styleFrom(
        minimumSize: const Size(48, 48),
        shape: MRadii.stadium,
      ),
    ),
    outlinedButtonTheme: OutlinedButtonThemeData(
      style: OutlinedButton.styleFrom(
        minimumSize: const Size(48, 48),
        shape: MRadii.stadium,
      ),
    ),
    textButtonTheme: TextButtonThemeData(
      style: TextButton.styleFrom(
        minimumSize: const Size(48, 48),
        shape: MRadii.stadium,
      ),
    ),
    extensions: [palette],
  );
}

TextStyle ts(double size,
        {FontWeight w = FontWeight.w400, Color c = MC.text, double? h}) =>
    GoogleFonts.plusJakartaSans(
        fontSize: size, fontWeight: w, color: c, height: h);

TextStyle _t(double s, FontWeight w,
        {double? h, double ls = 0, Color c = MC.onSurface}) =>
    GoogleFonts.plusJakartaSans(
        fontSize: s,
        fontWeight: w,
        height: h != null ? h / s : null,
        letterSpacing: ls,
        color: c);

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
  static final badge = _t(12, FontWeight.w800, h: 16, ls: 0.66);
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
    this.radius = MRadii.card,
    this.onTap,
  });

  @override
  Widget build(BuildContext context) => Container(
        width: double.infinity,
        decoration: BoxDecoration(
          color: color,
          borderRadius: BorderRadius.circular(radius),
          boxShadow: const [
            BoxShadow(
                color: Color(0x0F2E1065), blurRadius: 8, offset: Offset(0, 2))
          ],
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
  final String text;
  final Color bg, fg;
  final IconData? icon;
  const Pill(this.text,
      {super.key, this.bg = MC.tintHi, this.fg = MC.violetDark, this.icon});
  @override
  Widget build(BuildContext context) => Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
          color: bg, borderRadius: BorderRadius.circular(MRadii.pill)),
      child: Row(mainAxisSize: MainAxisSize.min, children: [
        if (icon != null) ...[
          Icon(icon, size: 14, color: fg),
          const SizedBox(width: 4)
        ],
        Flexible(
            child: Text(text.toUpperCase(),
                style: ts(12, w: FontWeight.w800, c: fg))),
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
          elevation:
              style == BtnStyle.primary || style == BtnStyle.danger ? 2 : 0,
          shape: MRadii.stadium,
          side: style == BtnStyle.outline
              ? const BorderSide(color: MC.tintHi, width: 1.5)
              : null,
        ),
        child: loading
            ? SizedBox(
                width: 20,
                height: 20,
                child: CircularProgressIndicator(
                    strokeWidth: 2.5, color: foreground))
            : Row(mainAxisAlignment: MainAxisAlignment.center, children: [
                if (leading != null) ...[
                  Icon(leading,
                      size: 20,
                      color: onPressed == null ? MC.muted : foreground),
                  const SizedBox(width: 8)
                ],
                Flexible(
                    child: Text(label,
                        style: ts(14,
                            w: FontWeight.w700,
                            c: onPressed == null ? MC.muted : foreground))),
                if (endIcon != null) ...[
                  const SizedBox(width: 8),
                  Icon(endIcon,
                      size: 20,
                      color: onPressed == null ? MC.muted : foreground)
                ],
              ]),
      ),
    );
  }
}

class MSubAppBar extends StatelessWidget implements PreferredSizeWidget {
  final Widget title;
  final List<Widget> actions;
  final Color backgroundColor;
  final Color foregroundColor;
  final VoidCallback? onBack;
  final Widget? leading;
  final PreferredSizeWidget? bottom;
  final double toolbarHeight;

  const MSubAppBar({
    super.key,
    required this.title,
    this.actions = const [],
    this.backgroundColor = MC.header,
    this.foregroundColor = Colors.white,
    this.onBack,
    this.leading,
    this.bottom,
    this.toolbarHeight = 64,
  });

  @override
  Size get preferredSize =>
      Size.fromHeight(toolbarHeight + (bottom?.preferredSize.height ?? 0));

  @override
  Widget build(BuildContext context) => AppBar(
        backgroundColor: backgroundColor,
        foregroundColor: foregroundColor,
        toolbarHeight: toolbarHeight,
        title: title,
        actions: actions,
        leading: leading ??
            (onBack == null
                ? null
                : IconButton(
                    icon: const Icon(Icons.arrow_back),
                    onPressed: onBack,
                  )),
        bottom: bottom,
      );
}

class MScaffold extends StatelessWidget {
  final String? title;
  final Widget body;
  final VoidCallback? onBack;
  final Widget? bottom;
  final Widget? drawer;
  final List<Widget>? actions;
  const MScaffold(
      {super.key,
      this.title,
      required this.body,
      this.onBack,
      this.bottom,
      this.drawer,
      this.actions});
  @override
  Widget build(BuildContext context) => Scaffold(
      backgroundColor: MC.of(context).bg,
      drawer: drawer,
      appBar: title == null
          ? null
          : MSubAppBar(
              backgroundColor: MC.deep,
              foregroundColor: Colors.white,
              actions: actions ?? const [],
              onBack: onBack,
              title: Text(title!,
                  style: ts(18, w: FontWeight.w700, c: Colors.white)),
            ),
      body: SafeArea(child: MPageBody(child: body)),
      bottomNavigationBar: bottom);
}

class MPageBody extends StatelessWidget {
  const MPageBody({super.key, required this.child});

  final Widget child;

  @override
  Widget build(BuildContext context) => Center(
        child: ConstrainedBox(
          constraints: const BoxConstraints(maxWidth: 600),
          child: child,
        ),
      );
}

class IconCircle extends StatelessWidget {
  final IconData icon;
  final Color bg, fg;
  final double size;
  const IconCircle(this.icon,
      {super.key,
      this.bg = MC.tintHi,
      this.fg = MC.violetDark,
      this.size = 40});
  @override
  Widget build(BuildContext context) => Container(
      width: size,
      height: size,
      decoration: BoxDecoration(color: bg, shape: BoxShape.circle),
      child: Icon(icon, size: size * .5, color: fg));
}

class InfoBanner extends StatelessWidget {
  final IconData icon;
  final String title;
  final String? body;
  final Color bg, fg;
  const InfoBanner(this.icon, this.title,
      {super.key, this.body, this.bg = MC.tint, this.fg = MC.violetDark});
  @override
  Widget build(BuildContext context) => Container(
      padding: const EdgeInsets.all(12),
      decoration:
          BoxDecoration(color: bg, borderRadius: BorderRadius.circular(12)),
      child: Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
        Icon(icon, color: fg, size: 20),
        const SizedBox(width: 10),
        Expanded(
            child:
                Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Text(title, style: ts(13, w: FontWeight.w700, c: fg)),
          if (body != null) Text(body!, style: ts(12, c: MC.muted, h: 1.4)),
        ])),
      ]));
}
