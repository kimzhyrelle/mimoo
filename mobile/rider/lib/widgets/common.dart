import 'package:flutter/material.dart';
import '../core/theme.dart';
import '../l10n/generated/app_localizations.dart';

/// Widgets that are unique to the older screens. Shared primitives (MCard,
/// PillButton, InfoBanner) live in core/theme.dart so there is one implementation.
class MChip extends StatelessWidget {
  final String text;
  final Color bg, fg;
  final IconData? icon;
  const MChip(this.text,
      {super.key, this.bg = MC.high, this.fg = MC.primary, this.icon});
  @override
  Widget build(BuildContext context) {
    final palette = MC.of(context);
    final background = themedColor(palette, bg);
    final foreground = themedColor(palette, fg);
    return Container(
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 3),
        decoration: BoxDecoration(
            color: background, borderRadius: BorderRadius.circular(MRadii.pill)),
        child: Row(mainAxisSize: MainAxisSize.min, children: [
          if (icon != null) ...[
            Icon(icon, size: 13, color: foreground),
            const SizedBox(width: 4)
          ],
          Text(text.toUpperCase(), style: ts(12, w: FontWeight.w800, c: foreground)),
        ]));
  }
}

class MField extends StatelessWidget {
  final String label;
  final String? hint, error, initial;
  final bool obscure, required;
  final IconData? prefix;
  final Widget? suffix;
  final TextInputType? keyboard;
  final TextEditingController? controller;
  final ValueChanged<String>? onChanged;
  const MField(this.label,
      {super.key,
      this.hint,
      this.error,
      this.initial,
      this.obscure = false,
      this.required = false,
      this.prefix,
      this.suffix,
      this.keyboard,
      this.controller,
      this.onChanged});
  @override
  Widget build(BuildContext context) {
    final c = MC.of(context);
    return Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
      Text.rich(TextSpan(
          text: label,
          style: ts(12, w: FontWeight.w600, c: c.text),
          children: [
            if (required)
              TextSpan(
                  text: ' *',
                  style: TextStyle(color: c.coral, fontWeight: FontWeight.w700))
          ])),
      const SizedBox(height: 6),
      TextFormField(
        controller: controller,
        initialValue: controller == null ? initial : null,
        obscureText: obscure,
        keyboardType: keyboard,
        onChanged: onChanged,
        style: ts(14, c: c.text),
        decoration: InputDecoration(
          hintText: hint,
          prefixIcon: prefix != null
              ? Icon(prefix, size: 20, color: c.muted)
              : null,
          suffixIcon: suffix,
          filled: true,
          fillColor:
              error != null ? c.errorContainer.withValues(alpha: 0.5) : c.low,
          contentPadding:
              const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
          border: OutlineInputBorder(
              borderRadius: BorderRadius.circular(MRadii.field),
              borderSide: BorderSide.none),
          focusedBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(MRadii.field),
              borderSide: BorderSide(color: c.primary, width: 1.5)),
        ),
      ),
      if (error != null)
        Padding(
            padding: const EdgeInsets.only(top: 4, left: 12),
            child: Text(error!,
                style: ts(12, w: FontWeight.w600, c: c.coral))),
    ]);
  }
}

/// Password input with a show/hide toggle. Wraps [MField] so validation and
/// styling stay consistent with the rest of the forms.
class PasswordField extends StatefulWidget {
  final String label;
  final String? hint, error;
  final TextEditingController controller;
  final bool required;
  final IconData? prefix;
  final ValueChanged<String>? onChanged;
  const PasswordField(this.label,
      {super.key,
      this.hint,
      this.error,
      required this.controller,
      this.required = true,
      this.prefix = Icons.lock_outline,
      this.onChanged});
  @override
  State<PasswordField> createState() => _PasswordFieldState();
}

class _PasswordFieldState extends State<PasswordField> {
  bool _obscure = true;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    return MField(
      widget.label,
      controller: widget.controller,
      hint: widget.hint,
      error: widget.error,
      required: widget.required,
      prefix: widget.prefix,
      obscure: _obscure,
      onChanged: widget.onChanged,
      suffix: IconButton(
        tooltip: _obscure ? l10n.showPassword : l10n.hidePassword,
        onPressed: () => setState(() => _obscure = !_obscure),
        icon: Icon(_obscure
            ? Icons.visibility_off_outlined
            : Icons.visibility_outlined),
      ),
    );
  }
}

class IconDot extends StatelessWidget {
  final IconData icon;
  final double size;
  final Color bg, fg;
  const IconDot(this.icon,
      {super.key, this.size = 40, this.bg = MC.high, this.fg = MC.primary});
  @override
  Widget build(BuildContext context) {
    final palette = MC.of(context);
    return Container(
        width: size,
        height: size,
        decoration: BoxDecoration(
            color: themedColor(palette, bg), shape: BoxShape.circle),
        child: Icon(icon,
            size: size * 0.5, color: themedColor(palette, fg)));
  }
}

class RouteTimeline extends StatelessWidget {
  final String fromLabel, from, toLabel, to;
  const RouteTimeline(
      {super.key,
      required this.fromLabel,
      required this.from,
      required this.toLabel,
      required this.to});
  @override
  Widget build(BuildContext context) {
    final c = MC.of(context);
    return Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
      Column(children: [
        const SizedBox(height: 4),
        Container(
            width: 12,
            height: 12,
            decoration: BoxDecoration(color: c.primary, shape: BoxShape.circle)),
        Container(width: 2, height: 40, color: c.highest),
        Container(
            width: 12,
            height: 12,
            decoration: BoxDecoration(color: c.coral, shape: BoxShape.circle)),
      ]),
      const SizedBox(width: 12),
      Expanded(
          child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
            Text(fromLabel.toUpperCase(),
                style: ts(12, w: FontWeight.w800, c: c.primary)),
            Text(from, style: ts(14, w: FontWeight.w700, c: c.text)),
            const SizedBox(height: 14),
            Text(toLabel.toUpperCase(),
                style: ts(12, w: FontWeight.w800, c: c.coral)),
            Text(to, style: ts(14, w: FontWeight.w700, c: c.text)),
          ])),
    ]);
  }
}

class Keypad extends StatelessWidget {
  final void Function(String) onKey;
  final VoidCallback onBackspace;
  final String bottomLeft;
  const Keypad(
      {super.key,
      required this.onKey,
      required this.onBackspace,
      this.bottomLeft = '.'});
  @override
  Widget build(BuildContext context) {
    final c = MC.of(context);
    final keys = [
      '1',
      '2',
      '3',
      '4',
      '5',
      '6',
      '7',
      '8',
      '9',
      bottomLeft,
      '0',
      '<'
    ];
    return GridView.count(
      crossAxisCount: 3,
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      mainAxisSpacing: 8,
      crossAxisSpacing: 8,
      childAspectRatio: 2.2,
      children: keys
          .map((k) => Material(
                color: c.container,
                borderRadius: BorderRadius.circular(MRadii.control),
                child: InkWell(
                  borderRadius: BorderRadius.circular(MRadii.control),
                  onTap: () => k == '<' ? onBackspace() : onKey(k),
                  child: Center(
                      child: k == '<'
                          ? Icon(Icons.backspace_outlined, color: c.text)
                          : Text(k,
                              style: ts(18, w: FontWeight.w700, c: c.text))),
                ),
              ))
          .toList(),
    );
  }
}

class MSegmentedControl extends StatelessWidget {
  final List<String> segments;
  final int selectedIndex;
  final ValueChanged<int> onSelectionChanged;

  const MSegmentedControl({
    super.key,
    required this.segments,
    required this.selectedIndex,
    required this.onSelectionChanged,
  });

  @override
  Widget build(BuildContext context) {
    final c = MC.of(context);
    return Container(
      padding: const EdgeInsets.all(4),
      decoration: BoxDecoration(
        color: c.container,
        borderRadius: BorderRadius.circular(MRadii.pill),
      ),
      child: Row(
        children: [
          for (var index = 0; index < segments.length; index++)
            Expanded(
              child: Semantics(
                button: true,
                selected: index == selectedIndex,
                child: Material(
                  color: index == selectedIndex
                      ? c.primaryContainer
                      : Colors.transparent,
                  borderRadius: BorderRadius.circular(MRadii.pill),
                  child: InkWell(
                    borderRadius: BorderRadius.circular(MRadii.pill),
                    onTap: () => onSelectionChanged(index),
                    child: ConstrainedBox(
                      constraints: const BoxConstraints(minHeight: 48),
                      child: Center(
                        child: Padding(
                          padding: const EdgeInsets.symmetric(
                              horizontal: 8, vertical: 10),
                          child: Text(
                            segments[index],
                            textAlign: TextAlign.center,
                            style: ts(14,
                                w: FontWeight.w700,
                                c: index == selectedIndex
                                    ? c.lowest
                                    : c.onSurfaceVariant),
                          ),
                        ),
                      ),
                    ),
                  ),
                ),
              ),
            ),
        ],
      ),
    );
  }
}
