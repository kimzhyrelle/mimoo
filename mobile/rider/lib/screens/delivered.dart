import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';
import '../core/theme.dart';
import '../data/parcel_flow.dart';
import '../data/repositories/app_repositories_scope.dart';
import '../l10n/generated/app_localizations.dart';
import '../widgets/custody_timeline.dart';
import '../widgets/delivery_sections.dart';

class DeliveredArgs {
  final String tracking;
  final String? photoPath;
  const DeliveredArgs({required this.tracking, this.photoPath});
}

class DeliveredScreen extends StatelessWidget {
  final DeliveredArgs args;
  const DeliveredScreen({super.key, required this.args});

  Future<void> _call(BuildContext context, String phone) async {
    final messenger = ScaffoldMessenger.of(context);
    final uri = Uri.parse('tel:$phone');
    var opened = false;
    try {
      opened = await canLaunchUrl(uri) && await launchUrl(uri);
    } catch (_) {
      opened = false;
    }
    if (!opened && context.mounted) {
      messenger.showSnackBar(
          const SnackBar(content: Text("Couldn't start the call")));
    }
  }

  @override
  Widget build(BuildContext context) {
    final c = MC.of(context);
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
          backgroundColor: c.bg,
          body: SafeArea(
            child: MPageBody(
                child: ListView(padding: MS.pagePadding, children: [
              const SizedBox(height: 24),
              Center(
                  child: Container(
                      width: 80,
                      height: 80,
                      decoration: BoxDecoration(
                          color: c.lowest,
                          shape: BoxShape.circle,
                          boxShadow: [
                            BoxShadow(color: c.primaryFixed, blurRadius: 24)
                          ]),
                      child: Icon(Icons.check_circle,
                          size: 44, color: c.green))),
              const SizedBox(height: 16),
              Center(
                  child: Text(
                      'Delivered at ${parcel.deliveredAt == null ? clock(DateTime.now()) : clock(parcel.deliveredAt!)}',
                      style: ts(22, w: FontWeight.w700, c: c.text))),
              Center(
                  child: Text('Handover completed$kDot${parcel.tracking}',
                      style: ts(12, c: c.muted))),
              const SizedBox(height: 12),
              Center(
                  child: Container(
                      padding: const EdgeInsets.symmetric(
                          horizontal: 16, vertical: 8),
                      decoration: BoxDecoration(
                          color: c.greenSoft,
                          borderRadius: BorderRadius.circular(99)),
                      child: Text('+₱50.00 Delivery Pay',
                          style: ts(14,
                              w: FontWeight.w700, c: c.green)))),
              if (args.photoPath != null) ...[
                const SizedBox(height: 8),
                Center(
                    child: Text('Live photo proof attached',
                        style: ts(12, c: c.green))),
              ],
              const SizedBox(height: 20),
              DeliveryProgressCard(step: 4, completed: true),
              const SizedBox(height: 12),
              DeliveryRecipientCard(
                parcel: parcel,
                onCall: () => _call(context, parcel.phone),
                onMessage: () =>
                    Navigator.pushNamed(context, '/chat/thread'),
              ),
              const SizedBox(height: 12),
              DeliveryAddressCard(parcel: parcel),
              if (parcel.landmark?.isNotEmpty == true) ...[
                const SizedBox(height: 12),
                DeliveryNoteBanner(parcel: parcel),
              ],
              const SizedBox(height: 12),
              DeliveryParcelCard(parcel: parcel),
              const SizedBox(height: 12),
              DeliveryPaymentCard(parcel: parcel),
              const SizedBox(height: 12),
              CustodyTimeline(events: parcel.custody),
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
