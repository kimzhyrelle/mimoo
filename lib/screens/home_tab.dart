import 'dart:async';

import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:permission_handler/permission_handler.dart';
import '../core/job_offer_notifications.dart';
import '../core/theme.dart';
import '../data/mock_data.dart';
import '../widgets/common.dart';
import 'location_permission_sheet.dart';
import 'pickup_detail_screen.dart';

class HomeTab extends StatefulWidget {
  const HomeTab({super.key});
  @override
  State<HomeTab> createState() => _HomeTabState();
}

class _HomeTabState extends State<HomeTab> with WidgetsBindingObserver {
  bool online = true;
  bool _locationOn = true;
  bool _pendingGoOnline = false;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);
    unawaited(_refreshLocationStatus());
  }

  @override
  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
    super.dispose();
  }

  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    if (state == AppLifecycleState.resumed) unawaited(_syncLocationOnResume());
  }

  Future<void> _syncLocationOnResume() async {
    final status = await Permission.location.status;
    if (!mounted) return;
    final granted = status.isGranted;
    final shouldGoOnline = granted && _pendingGoOnline;
    setState(() {
      _locationOn = granted;
      if (shouldGoOnline) {
        online = true;
        _pendingGoOnline = false;
      } else if (!granted) {
        online = false;
      }
    });
    if (shouldGoOnline) unawaited(JobOfferNotifications.requestPermission());
  }

  Future<void> _refreshLocationStatus() async {
    final status = await Permission.location.status;
    if (!mounted) return;
    setState(() {
      _locationOn = status.isGranted;
      if (!status.isGranted) online = false;
    });
  }

  /// Called before going Online. Shows the permission sheet when needed and
  /// only returns true once location access is actually granted.
  Future<bool> _ensureLocation() async {
    var status = await Permission.location.status;
    if (status.isGranted) {
      if (mounted) setState(() => _locationOn = true);
      return true;
    }

    if (status.isDenied) {
      status = await Permission.location.request();
      if (status.isGranted) {
        if (mounted) setState(() => _locationOn = true);
        return true;
      }
    }

    if (!mounted) return false;
    await showLocationPermissionSheet(context, permanentlyDenied: status.isPermanentlyDenied);
    status = await Permission.location.status;
    if (status.isGranted) {
      if (mounted) setState(() => _locationOn = true);
      return true;
    }

    if (mounted) setState(() => _locationOn = false);
    return false;
  }

  Future<void> _toggle(bool v) async {
    if (v) {
      _pendingGoOnline = true;
      final granted = await _ensureLocation();
      if (!granted || !mounted) {
        if (mounted) setState(() => online = false);
        return;
      }
      _pendingGoOnline = false;
      setState(() => online = true);
      unawaited(JobOfferNotifications.requestPermission());
      return;
    }

    _pendingGoOnline = false;

    // Going offline with a picked-up parcel → confirmation dialog (screen 8)
    final stay = await showDialog<bool>(
      context: context,
      builder: (c) => Dialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        child: Padding(
          padding: const EdgeInsets.all(20),
          child: Column(mainAxisSize: MainAxisSize.min, children: [
            const IconDot(Icons.inventory_2, size: 64, bg: MC.tertiaryFixed, fg: MC.tertiaryContainer),
            const SizedBox(height: 12),
            Text('You have 1 active parcel', style: MT.headlineLg, textAlign: TextAlign.center),
            const SizedBox(height: 8),
            Text('You can go Offline, but you must still deliver it to the buyer. No new jobs will be offered while offline.', style: MT.bodyMd.copyWith(color: MC.onSurfaceVariant), textAlign: TextAlign.center),
            const SizedBox(height: 16),
            PillButton('Stay Online', onPressed: () => Navigator.pop(c, true)),
            const SizedBox(height: 8),
            TextButton.icon(onPressed: () => Navigator.pop(c, false), icon: const Icon(Icons.power_settings_new, color: MC.tertiaryContainer), label: Text('Go Offline', style: MT.labelLg.copyWith(color: MC.tertiaryContainer))),
          ]),
        ),
      ),
    );
    if (stay == true || stay == null) return;
    setState(() => online = false);
  }

  @override
  Widget build(BuildContext context) => ListView(padding: EdgeInsets.zero, children: [
        Container(
          color: MC.primary,
          padding: const EdgeInsets.fromLTRB(16, 16, 16, 28),
          child: Row(children: [
            Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              Text('Pedro Jose Garcia', style: MT.headlineLg.copyWith(color: Colors.white)),
              Text('Bilis Hatid Express Inc. • Quezon City Hub', style: MT.bodySm.copyWith(color: const Color(0xFFE4D9FF))),
            ])),
            const CircleAvatar(radius: 24, backgroundColor: MC.highest, child: Icon(Icons.person, color: MC.primary)),
          ]),
        ),
        Transform.translate(
          offset: const Offset(0, -12),
          child: Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16),
            child: Column(children: [
              if (!_locationOn) ...[
                const LocationOffBanner(),
                const SizedBox(height: 8),
              ],
              MCard(child: Row(children: [
                const IconDot(Icons.power_settings_new, fg: MC.primaryContainer),
                const SizedBox(width: 8),
                Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                  Text(online ? 'ONLINE' : 'OFFLINE', style: MT.labelLg.copyWith(color: online ? MC.green : MC.onSurfaceVariant)),
                  Text('Fairview, QC • Oct 28, 2026', style: MT.bodySm),
                ])),
                Switch(value: online, onChanged: _toggle, activeThumbColor: Colors.white, activeTrackColor: MC.green),
              ])),
              const SizedBox(height: 16),
              Row(children: const [
                Expanded(child: _Stat('Active', '1', '/3', 'Capacity OK')),
                SizedBox(width: 8),
                Expanded(child: _Stat('Earned', '₱210', '', '4 trips')),
                SizedBox(width: 8),
                Expanded(child: _Stat('COD Remit', '₱1,800', '', 'Due 10:00 PM', warn: true)),
              ]),
              const SizedBox(height: 16),
              Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
                Text('Active Dispatch', style: MT.headlineMd),
                Row(children: [const Icon(Icons.circle, size: 8, color: MC.primaryContainer), const SizedBox(width: 4), Text('LIVE PRIORITY', style: MT.badge.copyWith(color: MC.primaryContainer))]),
              ]),
              const SizedBox(height: 8),
              MCard(
                onTap: () => Navigator.pushNamed(context, '/delivery'),
                child: Column(children: [
                  Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
                    Row(children: const [MChip('Assigned', bg: MC.tertiaryFixed, fg: MC.tertiary), SizedBox(width: 6), MChip('Bilis Express', bg: MC.highest, fg: MC.primaryContainer)]),
                    Text('#MM-98241', style: MT.labelMd.copyWith(color: MC.onSurfaceVariant)),
                  ]),
                  const SizedBox(height: 12),
                  const RouteTimeline(fromLabel: 'Pickup point', from: 'Maria Clara Handicrafts', toLabel: 'Drop-off destination', to: 'Katipunan Ave, Loyola Heights, QC'),
                  const SizedBox(height: 12),
                  if (mockActivePickup != null)
                    PillButton(
                      'Navigate to Pickup',
                      height: 48,
                      onPressed: () => showNavigationPicker(
                        context,
                        address: mockActivePickup!.address,
                        etaMins: mockActivePickup!.etaMins,
                        km: mockActivePickup!.km,
                        title: 'Navigate to Seller',
                      ),
                    ),
                ]),
              ),
              if (kDebugMode && online) ...[
                const SizedBox(height: 8),
                Row(mainAxisAlignment: MainAxisAlignment.end, children: [
                  TextButton.icon(
                    onPressed: () => Navigator.pushNamed(context, '/job-offer'),
                    icon: const Icon(Icons.local_fire_department, size: 18),
                    label: const Text('DEBUG: Simulate job offer'),
                  ),
                  TextButton.icon(
                    onPressed: () => JobOfferNotifications.showJobOffer(
                      offerId: 'OFFER-1001',
                      area: 'Commonwealth, QC',
                      payout: '₱30.00',
                      cod: '₱500.00',
                      km: '7.4',
                    ),
                    icon: const Icon(Icons.notifications_active, size: 18),
                    label: const Text('Test notification'),
                  ),
                ]),
              ],
            ]),
          ),
        ),
      ]);
}

class _Stat extends StatelessWidget {
  final String label, value, suffix, sub;
  final bool warn;
  const _Stat(this.label, this.value, this.suffix, this.sub, {this.warn = false});
  @override
  Widget build(BuildContext context) => MCard(
        color: warn ? MC.tertiaryFixed : MC.lowest,
        padding: const EdgeInsets.all(8),
        child: SizedBox(height: 80, child: Column(crossAxisAlignment: CrossAxisAlignment.start, mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
          Text(label.toUpperCase(), style: MT.badge.copyWith(color: MC.onSurfaceVariant), overflow: TextOverflow.ellipsis),
          Text.rich(TextSpan(text: value, style: MT.currency.copyWith(color: warn ? MC.tertiary : MC.primary), children: [TextSpan(text: suffix, style: MT.bodyMd)])),
          Text(sub, style: MT.badge.copyWith(color: warn ? MC.tertiary : MC.secondary), overflow: TextOverflow.ellipsis),
        ])),
      );
}
