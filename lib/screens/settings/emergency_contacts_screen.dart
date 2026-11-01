import 'package:flutter/material.dart';
import '../../core/theme.dart';
import '../../data/models/entities.dart' as db;
import '../../data/repositories/app_repositories_scope.dart';
import '../../l10n/generated/app_localizations.dart';
import '../../widgets/common.dart';

class EmergencyContactsScreen extends StatefulWidget {
  const EmergencyContactsScreen({super.key});
  @override
  State<EmergencyContactsScreen> createState() =>
      _EmergencyContactsScreenState();
}

class _EmergencyContactsScreenState extends State<EmergencyContactsScreen> {
  late final _contactsRepository =
      AppRepositoriesScope.of(context).emergencyContacts;
  List<db.EmergencyContact> get _contacts => _contactsRepository.contacts;

  Future<void> _edit({int? index}) async {
    final l10n = AppLocalizations.of(context);
    final existing = index == null ? null : _contacts[index];
    final name = TextEditingController(text: existing?.name ?? '');
    final relationship =
        TextEditingController(text: existing?.relationship ?? '');
    final phone = TextEditingController(text: existing?.phone ?? '');
    final result = await showDialog<db.EmergencyContact>(
      context: context,
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setDialogState) {
          final valid =
              name.text.trim().isNotEmpty && phone.text.trim().isNotEmpty;
          final palette = MC.of(ctx);
          return AlertDialog(
            backgroundColor: palette.lowest,
            shape:
                RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
            title: Text(
                existing == null
                    ? l10n.emergencyAddTitle
                    : l10n.emergencyEditTitle,
                style: MT.headlineMd.copyWith(color: palette.text)),
            content: SingleChildScrollView(
              child: Column(mainAxisSize: MainAxisSize.min, children: [
                TextField(
                    controller: name,
                    onChanged: (_) => setDialogState(() {}),
                    decoration:
                        InputDecoration(labelText: l10n.emergencyFullName)),
                const SizedBox(height: 8),
                TextField(
                    controller: relationship,
                    decoration: InputDecoration(
                        labelText: l10n.emergencyRelationship,
                        hintText: l10n.emergencyRelationshipHint)),
                const SizedBox(height: 8),
                TextField(
                    controller: phone,
                    onChanged: (_) => setDialogState(() {}),
                    keyboardType: TextInputType.phone,
                    decoration:
                        InputDecoration(labelText: l10n.emergencyPhone)),
              ]),
            ),
            actions: [
              TextButton(
                  onPressed: () => Navigator.pop(ctx),
                  child: Text(l10n.commonCancel)),
              FilledButton(
                onPressed: valid
                    ? () => Navigator.pop(
                        ctx,
                        db.EmergencyContact(
                          id: existing?.id,
                          name: name.text.trim(),
                          relationship: relationship.text.trim(),
                          phone: phone.text.trim(),
                        ))
                    : null,
                child: Text(l10n.commonSave),
              ),
            ],
          );
        },
      ),
    );
    name.dispose();
    relationship.dispose();
    phone.dispose();
    if (result == null) return;
    try {
      await _contactsRepository.saveContact(result);
      if (mounted) setState(() {});
    } catch (error) {
      if (mounted) {
        ScaffoldMessenger.of(context)
            .showSnackBar(SnackBar(content: Text('$error')));
      }
    }
  }

  Future<void> _delete(int index) async {
    final l10n = AppLocalizations.of(context);
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (ctx) {
        final palette = MC.of(ctx);
        return AlertDialog(
          backgroundColor: palette.lowest,
          shape:
              RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
          title: Text(l10n.emergencyRemoveTitle,
              style: MT.headlineMd.copyWith(color: palette.text)),
          content: Text(l10n.emergencyRemoveBody(_contacts[index].name)),
          actions: [
            TextButton(
                onPressed: () => Navigator.pop(ctx, false),
                child: Text(l10n.commonCancel)),
            FilledButton(
              style: FilledButton.styleFrom(backgroundColor: palette.coral),
              onPressed: () => Navigator.pop(ctx, true),
              child: Text(l10n.commonRemove),
            ),
          ],
        );
      },
    );
    if (confirmed != true) return;
    final id = _contacts[index].id;
    if (id == null) return;
    try {
      await _contactsRepository.delete(id);
      if (mounted) setState(() {});
    } catch (error) {
      if (mounted) {
        ScaffoldMessenger.of(context)
            .showSnackBar(SnackBar(content: Text('$error')));
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final c = MC.of(context);
    final l10n = AppLocalizations.of(context);
    return MScaffold(
      title: l10n.settingsEmergencyContact,
      body: ListView(padding: MS.pagePadding, children: [
        InfoBanner(Icons.health_and_safety_outlined, l10n.emergencyInfoTitle,
            body: l10n.emergencyInfoBody),
        const SizedBox(height: 12),
        if (_contacts.isEmpty)
          MCard(
              color: c.low,
              child: Column(children: [
                IconDot(Icons.contact_emergency_outlined,
                    size: 56, bg: c.tint, fg: c.primary),
                const SizedBox(height: 8),
                Text(l10n.settingsEmergencyNone,
                    style: MT.headlineMd.copyWith(color: c.text),
                    textAlign: TextAlign.center),
                const SizedBox(height: 4),
                Text(l10n.emergencyEmptyBody,
                    style: ts(12, c: c.muted), textAlign: TextAlign.center),
              ]))
        else
          for (var i = 0; i < _contacts.length; i++)
            Padding(
              padding: const EdgeInsets.only(bottom: 12),
              child: MCard(
                color: c.lowest,
                child: Row(children: [
                  IconDot(Icons.person, size: 44, bg: c.tint, fg: c.primary),
                  const SizedBox(width: 12),
                  Expanded(
                      child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                        Text(_contacts[i].name,
                            style: ts(15, w: FontWeight.w700, c: c.text)),
                        if (_contacts[i].relationship.isNotEmpty)
                          Text(_contacts[i].relationship,
                              style: ts(12, c: c.muted)),
                        Text(_contacts[i].phone,
                            style: ts(13, w: FontWeight.w600, c: c.text)),
                      ])),
                  IconButton(
                      tooltip: l10n.emergencyEditTooltip,
                      onPressed: () => _edit(index: i),
                      icon: Icon(Icons.edit_outlined, color: c.primary)),
                  IconButton(
                      tooltip: l10n.emergencyDeleteTooltip,
                      onPressed: () => _delete(i),
                      icon: Icon(Icons.delete_outline, color: c.coral)),
                ]),
              ),
            ),
        const SizedBox(height: 8),
        PillButton.secondary(
          l10n.emergencyAddAction,
          leading: Icons.person_add_alt_1,
          onPressed: _contacts.length >= 2 ? null : () => _edit(),
        ),
        if (_contacts.length >= 2) ...[
          const SizedBox(height: 8),
          Text(l10n.emergencyMaxNote,
              style: ts(12, c: c.muted), textAlign: TextAlign.center),
        ],
      ]),
    );
  }
}
