import 'package:flutter/material.dart';
import '../../core/theme.dart';
import '../../l10n/generated/app_localizations.dart';

class LegalScreen extends StatelessWidget {
  final String title, body;
  const LegalScreen({super.key, required this.title, required this.body});

  @override
  Widget build(BuildContext context) {
    final c = MC.of(context);
    final l10n = AppLocalizations.of(context);
    return MScaffold(
      title: title,
      body: ListView(padding: MS.pagePadding, children: [
        Text(title, style: ts(22, w: FontWeight.w700, c: c.text)),
        const SizedBox(height: 4),
        Text(l10n.legalLastUpdated, style: ts(12, c: c.muted)),
        const SizedBox(height: 16),
        Container(
          width: double.infinity,
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(color: c.lowest, borderRadius: BorderRadius.circular(16)),
          child: Text(body, style: ts(14, c: c.text, h: 1.5)),
        ),
      ]),
    );
  }
}
