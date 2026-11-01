import 'package:flutter/material.dart';
import '../core/theme.dart';
import '../data/parcel_flow.dart';
import '../data/repositories/app_repositories_scope.dart';
import '../l10n/generated/app_localizations.dart';
import '../widgets/common.dart';

class DeliveredArgs {
  final String tracking;
  final String? photoPath;
  const DeliveredArgs({required this.tracking, this.photoPath});
}

class DeliveredScreen extends StatelessWidget {
  final DeliveredArgs args;
  const DeliveredScreen({super.key, required this.args});

  @override
  Widget build(BuildContext context) {
    final parcels = AppRepositoriesScope.of(context).parcels;
    return ListenableBuilder(
      listenable: parcels,
      builder: (context, _) {
        final parcel = parcels.byTracking(args.tracking);
        if (parcel == null) {
          return Scaffold(
            appBar: AppBar(title: const Text('Mimoo Rider')),
            body: Center(
                child: Text(AppLocalizations.of(context).routeMissingData)),
          );
        }
        return Scaffold(
          body: SafeArea(
            child: MPageBody(
                child: ListView(padding: MS.pagePadding, children: [
              const SizedBox(height: 24),
              Center(
                  child: Container(
                      width: 80,
                      height: 80,
                      decoration: BoxDecoration(
                          color: MC.lowest,
                          shape: BoxShape.circle,
                          boxShadow: [
                            BoxShadow(color: MC.primaryFixed, blurRadius: 24)
                          ]),
                      child: const Icon(Icons.check_circle,
                          size: 44, color: MC.green))),
              const SizedBox(height: 16),
              Center(
                  child: Text(
                      'Delivered at ${parcel.deliveredAt == null ? clock(DateTime.now()) : clock(parcel.deliveredAt!)}',
                      style: MT.headlineLg)),
              Center(
                  child: Text('Handover completed · ${parcel.tracking}',
                      style: MT.bodySm)),
              const SizedBox(height: 12),
              Center(
                  child: Container(
                      padding: const EdgeInsets.symmetric(
                          horizontal: 16, vertical: 8),
                      decoration: BoxDecoration(
                          color: MC.greenBg,
                          borderRadius: BorderRadius.circular(99)),
                      child: Text('+₱50.00 Delivery Pay',
                          style: MT.labelLg
                              .copyWith(color: const Color(0xFF166534))))),
              if (args.photoPath != null) ...[
                const SizedBox(height: 8),
                Center(
                    child: Text('Live photo proof attached',
                        style: MT.bodySm.copyWith(color: MC.green))),
              ],
              const SizedBox(height: 20),
              MCard(
                  child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                    Text('Delivery Summary', style: MT.labelLg),
                    const SizedBox(height: 8),
                    Text(parcel.buyer, style: MT.headlineMd),
                    Text(parcel.address, style: MT.bodySm),
                    Text('${parcel.areaName} · Route ${parcel.routeCode}',
                        style: MT.bodySm.copyWith(
                            color: MC.primary, fontWeight: FontWeight.w600)),
                    const SizedBox(height: 12),
                    MCard(
                      color: MC.high,
                      child: Row(children: [
                        const IconDot(Icons.account_balance_wallet,
                            bg: MC.primaryFixed),
                        const SizedBox(width: 10),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                  parcel.isCod
                                      ? 'COD Collected'
                                      : 'Paid online',
                                  style: MT.labelMd),
                              Text(
                                parcel.isCod
                                    ? 'Total on hand: ${peso(parcels.codOnHand)}'
                                    : 'No cash to remit',
                                style: MT.bodySm,
                              ),
                            ],
                          ),
                        ),
                        Text(parcel.isCod ? peso(parcel.cod) : '₱0.00',
                            style: MT.currency.copyWith(color: MC.primary)),
                      ]),
                    ),
                  ])),
              const SizedBox(height: 20),
              PillButton('Back to Jobs',
                  onPressed: () => Navigator.pushNamedAndRemoveUntil(
                      context, '/home', (_) => false)),
              const SizedBox(height: 8),
              PillButton.secondary('View COD Balance',
                  leading: Icons.account_balance,
                  onPressed: () =>
                      Navigator.pushNamed(context, '/cod-remittance')),
            ])),
          ),
        );
      },
    );
  }
}
