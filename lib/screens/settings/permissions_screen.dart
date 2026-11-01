import 'package:flutter/material.dart';
import 'package:permission_handler/permission_handler.dart';
import '../../core/theme.dart';
import '../../l10n/generated/app_localizations.dart';
import '../../widgets/common.dart';

class PermissionsScreen extends StatefulWidget {
  const PermissionsScreen({super.key});
  @override
  State<PermissionsScreen> createState() => _PermissionsScreenState();
}

class _PermissionsScreenState extends State<PermissionsScreen> with WidgetsBindingObserver {
  PermissionStatus? _location, _camera, _notification;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);
    _refresh();
  }

  @override
  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
    super.dispose();
  }

  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    if (state == AppLifecycleState.resumed) _refresh();
  }

  Future<void> _refresh() async {
    final location = await Permission.location.status;
    final camera = await Permission.camera.status;
    final notification = await Permission.notification.status;
    if (!mounted) return;
    setState(() {
      _location = location;
      _camera = camera;
      _notification = notification;
    });
  }

  String _label(PermissionStatus? status) {
    final l10n = AppLocalizations.of(context);
    if (status == null) return l10n.permissionsChecking;
    if (status.isGranted || status.isLimited) return l10n.permissionsAllowed;
    if (status.isPermanentlyDenied) return l10n.permissionsBlocked;
    if (status.isDenied) return l10n.permissionsNotAllowed;
    return l10n.permissionsUnknown;
  }

  Color _color(PermissionStatus? status) {
    if (status == null) return MC.of(context).muted;
    return status.isGranted || status.isLimited ? MC.of(context).green : MC.of(context).coral;
  }

  @override
  Widget build(BuildContext context) {
    final c = MC.of(context);
    final l10n = AppLocalizations.of(context);
    return MScaffold(
      title: l10n.permissionsTitle,
      body: ListView(padding: MS.pagePadding, children: [
        MCard(color: c.lowest, padding: EdgeInsets.zero, child: Column(children: [
          _row(Icons.place_outlined, l10n.permissionsLocation, _location),
          Divider(height: 1, indent: 64, color: c.outlineVariant),
          _row(Icons.photo_camera_outlined, l10n.permissionsCamera, _camera),
          Divider(height: 1, indent: 64, color: c.outlineVariant),
          _row(Icons.notifications_outlined, l10n.permissionsNotifications, _notification),
        ])),
        const SizedBox(height: 12),
        InfoBanner(Icons.info_outline, l10n.permissionsInfoTitle,
            body: l10n.permissionsInfoBody),
        const SizedBox(height: 16),
        PillButton(
          l10n.permissionsOpenSettings,
          leading: Icons.settings,
          trailing: null,
          onPressed: () async {
            await openAppSettings();
            await _refresh();
          },
        ),
      ]),
    );
  }

  Widget _row(IconData icon, String label, PermissionStatus? status) {
    final c = MC.of(context);
    return ListTile(
      minVerticalPadding: MS.md,
      leading: IconDot(icon, size: 36, bg: c.tint, fg: c.primary),
      title: Text(label, style: ts(14, w: FontWeight.w600, c: c.text)),
      trailing: Text(_label(status), style: ts(12, w: FontWeight.w700, c: _color(status))),
    );
  }
}
