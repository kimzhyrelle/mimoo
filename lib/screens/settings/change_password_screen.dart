import 'package:flutter/material.dart';
import '../../core/theme.dart';
import '../../data/repositories/app_repositories_scope.dart';
import '../../l10n/generated/app_localizations.dart';
import '../../widgets/common.dart';

class ChangePasswordScreen extends StatefulWidget {
  const ChangePasswordScreen({super.key});
  @override
  State<ChangePasswordScreen> createState() => _ChangePasswordScreenState();
}

class _ChangePasswordScreenState extends State<ChangePasswordScreen> {
  final _current = TextEditingController();
  final _newPass = TextEditingController();
  final _confirm = TextEditingController();
  bool _saving = false;

  bool get _hasLength => _newPass.text.length >= 8;
  bool get _hasUpper => RegExp(r'[A-Z]').hasMatch(_newPass.text);
  bool get _hasNumberOrSpecial => RegExp(r'[0-9!@#\$%^&*(),.?":{}|<>_\-]').hasMatch(_newPass.text);
  bool get _matches => _confirm.text.isNotEmpty && _confirm.text == _newPass.text;
  bool get _canSubmit => _current.text.isNotEmpty && _hasLength && _hasUpper && _hasNumberOrSpecial && _matches;

  @override
  void initState() {
    super.initState();
    _current.addListener(_rebuild);
    _newPass.addListener(_rebuild);
    _confirm.addListener(_rebuild);
  }

  void _rebuild() => setState(() {});

  @override
  void dispose() {
    _current.dispose();
    _newPass.dispose();
    _confirm.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    if (!_canSubmit || _saving) return;
    final l10n = AppLocalizations.of(context);
    final messenger = ScaffoldMessenger.of(context);
    setState(() => _saving = true);
    try {
      await AppRepositoriesScope.of(context).riderProfile.changePassword(
            currentPassword: _current.text,
            newPassword: _newPass.text,
          );
      if (!mounted) return;
      setState(() => _saving = false);
      Navigator.pop(context);
      messenger
          .showSnackBar(SnackBar(content: Text(l10n.changePasswordUpdated)));
    } catch (error) {
      if (!mounted) return;
      setState(() => _saving = false);
      final msg = error.toString().replaceFirst('Exception: ', '');
      messenger.showSnackBar(
        SnackBar(content: Text(msg.isEmpty ? 'Could not update password.' : msg)),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final c = MC.of(context);
    final l10n = AppLocalizations.of(context);
    return MScaffold(
      title: l10n.settingsChangePassword,
      body: ListView(padding: MS.pagePadding, children: [
        MField(l10n.changePasswordCurrent, obscure: true, required: true, controller: _current, prefix: Icons.lock_outline),
        const SizedBox(height: 12),
        MField(l10n.changePasswordNew, obscure: true, required: true, controller: _newPass, prefix: Icons.password),
        const SizedBox(height: 12),
        Container(
          padding: const EdgeInsets.all(12),
          decoration: BoxDecoration(color: c.low, borderRadius: BorderRadius.circular(12)),
          child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            _Rule(label: l10n.changePasswordRuleLength, met: _hasLength),
            _Rule(label: l10n.changePasswordRuleUpper, met: _hasUpper),
            _Rule(label: l10n.changePasswordRuleNumber, met: _hasNumberOrSpecial),
            _Rule(label: l10n.changePasswordRuleMatch, met: _matches),
          ]),
        ),
        const SizedBox(height: 12),
        MField(l10n.changePasswordConfirm, obscure: true, required: true, controller: _confirm, prefix: Icons.lock_reset),
        const SizedBox(height: 20),
        PillButton(
          l10n.changePasswordSubmit,
          leading: Icons.lock_reset,
          trailing: null,
          loading: _saving,
          onPressed: _canSubmit ? _submit : null,
        ),
      ]),
    );
  }
}

class _Rule extends StatelessWidget {
  final String label;
  final bool met;
  const _Rule({required this.label, required this.met});
  @override
  Widget build(BuildContext context) {
    final c = MC.of(context);
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 3),
      child: Row(children: [
        Icon(met ? Icons.check_circle : Icons.radio_button_unchecked, size: 18, color: met ? c.green : c.outline),
        const SizedBox(width: 8),
        Expanded(child: Text(label, style: ts(12, w: FontWeight.w600, c: met ? c.green : c.muted))),
      ]),
    );
  }
}
