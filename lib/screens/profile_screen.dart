import 'dart:typed_data';

import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import '../core/theme.dart';
import '../l10n/generated/app_localizations.dart';
import '../widgets/common.dart';

/// Personal information only. Vehicle documents, password and notifications
/// live in the drawer / Settings screen.
class ProfileScreen extends StatefulWidget {
  const ProfileScreen({super.key});
  @override
  State<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends State<ProfileScreen> {
  static const _initialPhone = '09189876543';
  static const _initialAddress = '45 Kalayaan Ave., Brgy. Central, Quezon City';
  late final _phone = TextEditingController(text: _initialPhone);
  late final _address = TextEditingController(text: _initialAddress);
  String _savedPhone = _initialPhone;
  String _savedAddress = _initialAddress;
  Uint8List? _photo;
  bool _photoChanged = false;
  bool _saving = false;

  bool get _dirty => _phone.text != _savedPhone || _address.text != _savedAddress || _photoChanged;

  @override
  void initState() {
    super.initState();
    _phone.addListener(_rebuild);
    _address.addListener(_rebuild);
  }

  void _rebuild() => setState(() {});

  @override
  void dispose() {
    _phone.dispose();
    _address.dispose();
    super.dispose();
  }

  Future<void> _changePhoto() async {
    final l10n = AppLocalizations.of(context);
    final source = await showModalBottomSheet<ImageSource>(
      context: context,
      backgroundColor: MC.of(context).lowest,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(28))),
      builder: (ctx) {
        final c = MC.of(ctx);
        return SafeArea(
          child: Padding(
            padding: const EdgeInsets.all(20),
            child: Column(mainAxisSize: MainAxisSize.min, crossAxisAlignment: CrossAxisAlignment.start, children: [
              Center(child: Container(width: 44, height: 5, decoration: BoxDecoration(color: c.outlineVariant, borderRadius: BorderRadius.circular(9)))),
              const SizedBox(height: 16),
              Text(l10n.profileChangePhoto, style: ts(20, w: FontWeight.w700, c: c.text)),
              const SizedBox(height: 8),
              ListTile(
                contentPadding: EdgeInsets.zero,
                leading: IconDot(Icons.photo_camera_outlined, bg: c.tint, fg: c.primary),
                title: Text(l10n.profileTakePhoto),
                onTap: () => Navigator.pop(ctx, ImageSource.camera),
              ),
              ListTile(
                contentPadding: EdgeInsets.zero,
                leading: IconDot(Icons.photo_library_outlined, bg: c.tint, fg: c.primary),
                title: Text(l10n.profileChooseGallery),
                onTap: () => Navigator.pop(ctx, ImageSource.gallery),
              ),
            ]),
          ),
        );
      },
    );
    if (source == null || !mounted) return;
    final messenger = ScaffoldMessenger.of(context);
    try {
      final file = await ImagePicker().pickImage(source: source, imageQuality: 70, maxWidth: 1600);
      if (file == null) return;
      final bytes = await file.readAsBytes();
      if (!mounted) return;
      // TODO(api): upload the new avatar and persist it on the rider profile.
      setState(() {
        _photo = bytes;
        _photoChanged = true;
      });
    } catch (error) {
      messenger.showSnackBar(SnackBar(content: Text('Could not update the photo: $error')));
    }
  }

  Future<void> _save() async {
    if (!_dirty || _saving) return;
    setState(() => _saving = true);
    // TODO(api): PATCH the rider contact number and residential address.
    await Future<void>.delayed(const Duration(milliseconds: 500));
    if (!mounted) return;
    setState(() {
      _saving = false;
      _savedPhone = _phone.text;
      _savedAddress = _address.text;
      _photoChanged = false;
    });
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(AppLocalizations.of(context).profileUpdated)));
  }

  Future<void> _confirmDiscard() async {
    final l10n = AppLocalizations.of(context);
    final discard = await showDialog<bool>(
      context: context,
      builder: (ctx) {
        final c = MC.of(ctx);
        return AlertDialog(
          backgroundColor: c.lowest,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
          title: Text(l10n.profileDiscardTitle, style: MT.headlineMd.copyWith(color: c.text)),
          content: Text(l10n.profileDiscardBody),
          actions: [
            TextButton(onPressed: () => Navigator.pop(ctx, false), child: Text(l10n.profileKeepEditing)),
            FilledButton(
              style: FilledButton.styleFrom(backgroundColor: c.coral),
              onPressed: () => Navigator.pop(ctx, true),
              child: Text(l10n.profileDiscard),
            ),
          ],
        );
      },
    );
    if (discard == true && mounted) Navigator.of(context).pop();
  }

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final c = MC.of(context);
    return PopScope(
        canPop: !_dirty,
        onPopInvokedWithResult: (didPop, _) {
          if (!didPop) _confirmDiscard();
        },
        child: MScaffold(
          title: l10n.profileTitle,
          onBack: () => Navigator.maybePop(context),
          body: ListView(padding: MS.pagePadding, children: [
            MCard(
              color: c.lowest,
              child: Column(children: [
                Stack(children: [
                  CircleAvatar(
                    radius: 44,
                    backgroundColor: c.highest,
                    backgroundImage: _photo == null ? null : MemoryImage(_photo!),
                    child: _photo == null ? Icon(Icons.person, size: 44, color: c.primary) : null,
                  ),
                  Positioned(
                    right: 0,
                    bottom: 0,
                    child: InkWell(
                      onTap: _changePhoto,
                      borderRadius: BorderRadius.circular(99),
                      child: Container(
                        width: 32,
                        height: 32,
                        decoration: BoxDecoration(color: c.primary, shape: BoxShape.circle, border: Border.all(color: c.lowest, width: 2)),
                        child: const Icon(Icons.edit, size: 16, color: Colors.white),
                      ),
                    ),
                  ),
                ]),
                const SizedBox(height: 10),
                Text('Pedro Jose Garcia', style: MT.headlineMd.copyWith(color: c.text)),
                Text('Bilis Hatid Express Inc. · Quezon City Hub', style: ts(12, c: c.muted), textAlign: TextAlign.center),
                const SizedBox(height: 8),
                TextButton.icon(
                  onPressed: _changePhoto,
                  icon: const Icon(Icons.photo_camera_outlined, size: 18),
                  label: Text(l10n.profileChangePhoto),
                ),
              ]),
            ),
            const SizedBox(height: 16),
            MCard(color: c.low, child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              Row(children: [
                Icon(Icons.verified_user_outlined, size: 20, color: c.primary),
                const SizedBox(width: 8),
                Text(l10n.profileVerifiedIdentity, style: MT.labelLg.copyWith(color: c.text)),
              ]),
              const SizedBox(height: 10),
              _ReadOnly(label: l10n.profileFullLegalName, value: 'Pedro Jose Garcia'),
              _ReadOnly(label: l10n.profileDateOfBirth, value: 'Oct 24, 1995'),
              _ReadOnly(label: l10n.profileLicenseNumber, value: 'N01-23-456789'),
              const SizedBox(height: 4),
              Text(l10n.profileContactLogistics, style: ts(12, c: c.muted)),
            ])),
            const SizedBox(height: 16),
            MField(l10n.profileContactNumber, required: true, controller: _phone, keyboard: TextInputType.phone, prefix: Icons.phone_outlined),
            const SizedBox(height: 12),
            MField(l10n.profileResidentialAddress, required: true, controller: _address, prefix: Icons.home_outlined),
            const SizedBox(height: 20),
            PillButton(
              l10n.profileSaveChanges,
              leading: Icons.save_outlined,
              trailing: null,
              loading: _saving,
              onPressed: _dirty ? _save : null,
            ),
          ]),
        ),
      );
  }
}

class _ReadOnly extends StatelessWidget {
  final String label, value;
  const _ReadOnly({required this.label, required this.value});
  @override
  Widget build(BuildContext context) {
    final c = MC.of(context);
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        Text(label, style: ts(12, c: c.muted)),
        Container(
          width: double.infinity,
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
          decoration: BoxDecoration(color: c.highest.withValues(alpha: .8), borderRadius: BorderRadius.circular(99)),
          child: Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
            Flexible(child: Text(value, style: ts(14, w: FontWeight.w500, c: c.text))),
            Icon(Icons.lock, size: 18, color: c.outline),
          ]),
        ),
      ]),
    );
  }
}
