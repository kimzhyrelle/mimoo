import 'package:flutter/material.dart';
import '../core/theme.dart';

/// Widgets that are unique to the older screens. Shared primitives (MCard,
/// PillButton, InfoBanner) live in core/theme.dart so there is one implementation.
class MChip extends StatelessWidget {
  final String text;
  final Color bg, fg;
  final IconData? icon;
  const MChip(this.text, {super.key, this.bg = MC.high, this.fg = MC.primary, this.icon});
  @override
  Widget build(BuildContext context) => Container(
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 3),
        decoration: BoxDecoration(color: bg, borderRadius: BorderRadius.circular(99)),
        child: Row(mainAxisSize: MainAxisSize.min, children: [
          if (icon != null) ...[Icon(icon, size: 13, color: fg), const SizedBox(width: 4)],
          Text(text.toUpperCase(), style: MT.badge.copyWith(color: fg)),
        ]),
      );
}

class MField extends StatelessWidget {
  final String label;
  final String? hint, error, initial;
  final bool obscure, required;
  final IconData? prefix;
  final TextInputType? keyboard;
  const MField(this.label, {super.key, this.hint, this.error, this.initial, this.obscure = false, this.required = false, this.prefix, this.keyboard});
  @override
  Widget build(BuildContext context) => Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        Text.rich(TextSpan(text: label, style: MT.labelMd, children: [if (required) const TextSpan(text: ' *', style: TextStyle(color: MC.tertiaryContainer))])),
        const SizedBox(height: 6),
        TextFormField(
          initialValue: initial,
          obscureText: obscure,
          keyboardType: keyboard,
          style: MT.bodyMd,
          decoration: InputDecoration(
            hintText: hint,
            prefixIcon: prefix != null ? Icon(prefix, size: 20, color: MC.onSurfaceVariant) : null,
            filled: true,
            fillColor: error != null ? MC.errorContainer.withValues(alpha: 0.5) : MC.low,
            contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
            border: OutlineInputBorder(borderRadius: BorderRadius.circular(99), borderSide: BorderSide.none),
            focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(99), borderSide: const BorderSide(color: MC.primaryContainer, width: 1.5)),
          ),
        ),
        if (error != null) Padding(padding: const EdgeInsets.only(top: 4, left: 12), child: Text(error!, style: MT.bodySm.copyWith(color: MC.error, fontWeight: FontWeight.w600))),
      ]);
}

class IconDot extends StatelessWidget {
  final IconData icon;
  final double size;
  final Color bg, fg;
  const IconDot(this.icon, {super.key, this.size = 40, this.bg = MC.high, this.fg = MC.primary});
  @override
  Widget build(BuildContext context) => Container(width: size, height: size, decoration: BoxDecoration(color: bg, shape: BoxShape.circle), child: Icon(icon, size: size * 0.5, color: fg));
}

class RouteTimeline extends StatelessWidget {
  final String fromLabel, from, toLabel, to;
  const RouteTimeline({super.key, required this.fromLabel, required this.from, required this.toLabel, required this.to});
  @override
  Widget build(BuildContext context) => Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
        Column(children: [
          const SizedBox(height: 4),
          Container(width: 12, height: 12, decoration: const BoxDecoration(color: MC.primaryContainer, shape: BoxShape.circle)),
          Container(width: 2, height: 40, color: MC.highest),
          Container(width: 12, height: 12, decoration: const BoxDecoration(color: MC.tertiaryContainer, shape: BoxShape.circle)),
        ]),
        const SizedBox(width: 12),
        Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Text(fromLabel.toUpperCase(), style: MT.badge.copyWith(color: MC.primary)),
          Text(from, style: MT.labelLg),
          const SizedBox(height: 14),
          Text(toLabel.toUpperCase(), style: MT.badge.copyWith(color: MC.tertiary)),
          Text(to, style: MT.labelLg),
        ])),
      ]);
}

class Keypad extends StatelessWidget {
  final void Function(String) onKey;
  final VoidCallback onBackspace;
  final String bottomLeft;
  const Keypad({super.key, required this.onKey, required this.onBackspace, this.bottomLeft = '.'});
  @override
  Widget build(BuildContext context) {
    final keys = ['1', '2', '3', '4', '5', '6', '7', '8', '9', bottomLeft, '0', '<'];
    return GridView.count(
      crossAxisCount: 3, shrinkWrap: true, physics: const NeverScrollableScrollPhysics(),
      mainAxisSpacing: 8, crossAxisSpacing: 8, childAspectRatio: 2.2,
      children: keys.map((k) => Material(
        color: MC.container, borderRadius: BorderRadius.circular(10),
        child: InkWell(
          borderRadius: BorderRadius.circular(10),
          onTap: () => k == '<' ? onBackspace() : onKey(k),
          child: Center(child: k == '<' ? const Icon(Icons.backspace_outlined) : Text(k, style: MT.headlineMd)),
        ),
      )).toList(),
    );
  }
}
