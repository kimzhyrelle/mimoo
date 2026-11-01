import 'package:flutter/material.dart';
import '../core/theme.dart';
import '../data/parcel_flow.dart';
import '../data/repositories/app_repositories_scope.dart';
import '../widgets/common.dart';

/// Money only. Job-level records live in History and live work lives in Jobs.
class EarningsTab extends StatefulWidget {
  const EarningsTab({super.key});
  @override
  State<EarningsTab> createState() => _EarningsTabState();
}

class _EarningsTabState extends State<EarningsTab> {
  bool _week = false;

  @override
  Widget build(BuildContext context) {
    final parcels = AppRepositoriesScope.of(context).parcels;
    return ListenableBuilder(
      listenable: parcels,
      builder: (context, _) {
        final now = DateTime.now();
        final today = DateTime(now.year, now.month, now.day);
        final from =
            _week ? today.subtract(Duration(days: today.weekday - 1)) : today;
        bool inRange(DateTime? date) => date != null && !date.isBefore(from);
        final pickups = parcels.parcels
            .where((p) => p.pickupFee > 0 && inRange(p.pickedUpAt))
            .length;
        final deliveries = parcels.parcels
            .where((p) => p.deliveryFee > 0 && inRange(p.deliveredAt))
            .length;
        final failed = parcels.statusLogs
            .where((log) =>
                log.toStatus == ParcelStatus.failedDelivery.value &&
                !log.createdAt.isBefore(from))
            .length;
        final pickupPay = parcels.parcels
            .where((p) => p.pickupFee > 0 && inRange(p.pickedUpAt))
            .fold<double>(0, (total, p) => total + p.pickupFee);
        final deliveryPay = parcels.parcels
            .where((p) => p.deliveryFee > 0 && inRange(p.deliveredAt))
            .fold<double>(0, (total, p) => total + p.deliveryFee);
        final earnings = pickupPay + deliveryPay;
        final jobs = pickups + deliveries;
        return Center(
          child: ConstrainedBox(
            constraints: const BoxConstraints(maxWidth: 600),
            child: ListView(padding: MS.pagePadding, children: [
              Text('Earnings', style: MT.headlineLg),
              Text('Money only. Open History for job details.',
                  style: MT.bodySm.copyWith(color: MC.onSurfaceVariant)),
              const SizedBox(height: MS.md),
              MSegmentedControl(
                segments: const ['Today', 'This Week'],
                selectedIndex: _week ? 1 : 0,
                onSelectionChanged: (index) =>
                    setState(() => _week = index == 1),
              ),
              const SizedBox(height: MS.xl),
              MCard(
                  radius: 16,
                  child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const MChip('Net Payout Accrued',
                            bg: MC.container, fg: MC.primaryContainer),
                        const SizedBox(height: 8),
                        Text(peso(earnings),
                            style: MT.currency.copyWith(fontSize: 32)),
                        Text(
                            _week
                                ? 'This week · $jobs jobs completed'
                                : 'Today · $jobs jobs completed',
                            style:
                                MT.bodyMd.copyWith(color: MC.onSurfaceVariant)),
                      ])),
              const SizedBox(height: MS.md),
              MCard(
                  color: MC.low,
                  child: Row(children: [
                    const IconDot(Icons.account_balance_wallet,
                        bg: MC.secondaryContainer, fg: MC.onSecondaryContainer),
                    const SizedBox(width: 10),
                    Expanded(
                        child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                          Text('COD ON-HAND',
                              style: MT.badge
                                  .copyWith(color: MC.onSurfaceVariant)),
                          Text('${peso(parcels.codOnHand)} to Remit',
                              style: MT.headlineMd),
                        ])),
                  ])),
              const SizedBox(height: MS.md),
              MCard(
                  child: Column(children: [
                _row(
                    Icons.inventory_2,
                    '$pickups pickup${pickups == 1 ? '' : 's'}',
                    '$pickups × ${peso(30)}',
                    peso(pickupPay)),
                _row(
                    Icons.check_circle,
                    '$deliveries successful deliver${deliveries == 1 ? 'y' : 'ies'}',
                    '$deliveries × ${peso(50)}',
                    peso(deliveryPay)),
                _row(
                    Icons.cancel,
                    '$failed failed attempt${failed == 1 ? '' : 's'}',
                    'No pay for failed attempts',
                    peso(0),
                    muted: true),
                Container(
                    margin: const EdgeInsets.only(top: 8),
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                        color: MC.low, borderRadius: BorderRadius.circular(12)),
                    child: Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Text(
                              _week
                                  ? "THIS WEEK'S NET TOTAL"
                                  : "TODAY'S NET TOTAL",
                              style: MT.badge),
                          Text(peso(pickupPay + deliveryPay),
                              style: MT.headlineLg
                                  .copyWith(color: MC.primaryContainer)),
                        ])),
              ])),
              const SizedBox(height: MS.md),
              InfoBanner(Icons.info_outline,
                  'COD is collected cash, not rider pay. It must be remitted in full.',
                  bg: MC.container, fg: MC.onSurfaceVariant),
            ]),
          ),
        );
      },
    );
  }

  Widget _row(IconData i, String t, String s, String amt,
          {bool muted = false}) =>
      Padding(
        padding: const EdgeInsets.symmetric(vertical: MS.md),
        child: Row(children: [
          IconDot(i,
              size: 32,
              bg: MC.container,
              fg: muted ? MC.outline : MC.primaryContainer),
          const SizedBox(width: 10),
          Expanded(
              child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                Text(t,
                    style: MT.bodyMd.copyWith(
                        fontWeight: FontWeight.w500,
                        color: muted ? MC.onSurfaceVariant : MC.onSurface)),
                Text(s, style: MT.bodySm),
              ])),
          Text(amt,
              style: MT.headlineMd
                  .copyWith(color: muted ? MC.outline : MC.primaryContainer)),
        ]),
      );
}
