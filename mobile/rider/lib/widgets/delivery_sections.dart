import 'package:flutter/material.dart';
import '../core/theme.dart';
import '../data/parcel_flow.dart';

/// Shared delivery-detail building blocks so the at-hub, out-for-delivery,
/// failed and delivered screens keep one visual language.
const List<String> kDeliverySteps = [
  'Inbound',
  'Sorted',
  'At Hub',
  'Out for Del.',
  'Delivered',
];

int deliveryStepFor(ParcelStatus status) => switch (status) {
      ParcelStatus.readyForPickup => 0,
      ParcelStatus.assigned => 2,
      ParcelStatus.pickedUp || ParcelStatus.atHub => 2,
      ParcelStatus.outForDelivery ||
      ParcelStatus.failedDelivery ||
      ParcelStatus.returnToSender =>
        3,
      ParcelStatus.delivered || ParcelStatus.returned => 4,
    };

class DeliveryProgressCard extends StatelessWidget {
  final int step;
  final bool completed;
  const DeliveryProgressCard(
      {super.key, required this.step, this.completed = false});

  @override
  Widget build(BuildContext context) {
    final c = MC.of(context);
    final current = completed ? kDeliverySteps.length - 1 : step;
    return MCard(
      color: c.lowest,
      child: Column(children: [
        Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
          Text('Delivery Progress', style: ts(18, w: FontWeight.w700, c: c.text)),
          Text('STEP ${current + 1} OF ${kDeliverySteps.length}',
              style: ts(12, w: FontWeight.w800, c: c.primary)),
        ]),
        const SizedBox(height: 12),
        Row(children: [
          for (var i = 0; i < kDeliverySteps.length; i++)
            Expanded(
              child: Column(children: [
                CircleAvatar(
                  radius: 16,
                  backgroundColor: completed || i < step
                      ? c.primary
                      : i == step
                          ? c.primaryContainer
                          : c.highest,
                  child: Icon(
                    completed || i < step
                        ? Icons.check
                        : [
                            Icons.assignment,
                            Icons.inventory,
                            Icons.warehouse,
                            Icons.local_shipping,
                            Icons.verified,
                          ][i],
                    size: 16,
                    color: completed || i < step
                        ? c.lowest
                        : i == step
                            ? Colors.white
                            : c.muted,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  kDeliverySteps[i],
                  textAlign: TextAlign.center,
                  style: ts(12,
                      w: i == step ? FontWeight.w800 : FontWeight.w600,
                      c: i > step && !completed ? c.outline : c.text),
                ),
              ]),
            ),
        ]),
      ]),
    );
  }
}

class DeliveryRecipientCard extends StatelessWidget {
  final DispatchParcel parcel;
  final VoidCallback onCall;
  final VoidCallback onMessage;
  const DeliveryRecipientCard(
      {super.key,
      required this.parcel,
      required this.onCall,
      required this.onMessage});

  @override
  Widget build(BuildContext context) {
    final c = MC.of(context);
    return MCard(
      color: c.lowest,
      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        Row(children: [
          IconCircle(Icons.person, size: 48, bg: c.tint, fg: c.primary),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text('RECIPIENT', style: ts(12, w: FontWeight.w800, c: c.muted)),
                  Text(parcel.buyer,
                      style: ts(18, w: FontWeight.w800, c: c.text),
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis),
                  Text(parcel.phone, style: ts(12, c: c.muted)),
                ]),
          ),
        ]),
        const SizedBox(height: 12),
        Row(children: [
          Expanded(
            child: OutlinedButton.icon(
              onPressed: onCall,
              icon: const Icon(Icons.call, size: 18),
              label: const Text('Call'),
              style: OutlinedButton.styleFrom(
                minimumSize: const Size.fromHeight(48),
                shape: const StadiumBorder(),
                foregroundColor: c.primary,
                backgroundColor: c.container,
                side: BorderSide.none,
              ),
            ),
          ),
          const SizedBox(width: 8),
          Expanded(
            child: FilledButton.icon(
              onPressed: onMessage,
              icon: const Icon(Icons.chat, size: 18),
              label: const Text('Message'),
              style: FilledButton.styleFrom(
                minimumSize: const Size.fromHeight(48),
                shape: const StadiumBorder(),
                backgroundColor: c.primary,
                foregroundColor: c.lowest,
              ),
            ),
          ),
        ]),
      ]),
    );
  }
}

class DeliveryAddressCard extends StatelessWidget {
  final DispatchParcel parcel;
  const DeliveryAddressCard({super.key, required this.parcel});

  @override
  Widget build(BuildContext context) {
    final c = MC.of(context);
    return MCard(
      color: c.low,
      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        Text('DROP-OFF ADDRESS',
            style: ts(12, w: FontWeight.w800, c: c.muted)),
        const SizedBox(height: 4),
        Text(parcel.address, style: ts(16, w: FontWeight.w700, c: c.text)),
        const SizedBox(height: 4),
        Row(children: [
          Icon(Icons.alt_route, size: 16, color: c.primary),
          const SizedBox(width: 6),
          Expanded(
            child: Text(
              '${parcel.areaName ?? ''}${kDot}Route ${parcel.routeCode ?? '—'}',
              style: ts(12, w: FontWeight.w600, c: c.primary),
            ),
          ),
        ]),
      ]),
    );
  }
}

class DeliveryNoteBanner extends StatelessWidget {
  final DispatchParcel parcel;
  const DeliveryNoteBanner({super.key, required this.parcel});

  @override
  Widget build(BuildContext context) {
    final c = MC.of(context);
    return InfoBanner(
      Icons.doorbell,
      'Rider Delivery Note',
      body: '“${parcel.landmark}”',
      bg: c.tint,
      fg: c.primary,
    );
  }
}

class DeliveryParcelCard extends StatelessWidget {
  final DispatchParcel parcel;
  const DeliveryParcelCard({super.key, required this.parcel});

  @override
  Widget build(BuildContext context) {
    final c = MC.of(context);
    final carrier = carrierLabels[parcel.carrier] ?? parcel.carrierLabel;
    return MCard(
      color: c.lowest,
      child: Row(children: [
        IconCircle(Icons.inventory_2, bg: c.tint, fg: c.primary),
        const SizedBox(width: 12),
        Expanded(
          child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(parcel.item ?? 'Parcel',
                    style: ts(15, w: FontWeight.w700, c: c.text)),
                Text('$carrier$kDot${parcel.weightLabel}',
                    style: ts(12, c: c.muted)),
              ]),
        ),
      ]),
    );
  }
}

class DeliveryPaymentCard extends StatelessWidget {
  final DispatchParcel parcel;
  const DeliveryPaymentCard({super.key, required this.parcel});

  @override
  Widget build(BuildContext context) {
    final c = MC.of(context);
    if (!parcel.isCod) {
      return Container(
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: c.greenSoft,
          borderRadius: BorderRadius.circular(14),
        ),
        child: Row(children: [
          IconCircle(Icons.verified, bg: c.green, fg: c.lowest, size: 40),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text('Paid online',
                      style: ts(14, w: FontWeight.w700, c: c.green)),
                  Text('No cash to collect for this parcel.',
                      style: ts(12, c: c.green)),
                ]),
          ),
        ]),
      );
    }
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: c.errorContainer,
        borderRadius: BorderRadius.circular(14),
      ),
      child: Row(children: [
        IconCircle(Icons.payments, bg: c.coral, fg: c.lowest, size: 44),
        const SizedBox(width: 12),
        Expanded(
          child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Pill('Cash on delivery', bg: c.coral, fg: c.lowest),
                const SizedBox(height: 4),
                Text('Collect exact amount',
                    style: ts(12, w: FontWeight.w600, c: c.coral)),
              ]),
        ),
        Text(peso(parcel.cod),
            style: ts(24, w: FontWeight.w800, c: c.coral)),
      ]),
    );
  }
}

class DeliveryOverdueBanner extends StatelessWidget {
  final DispatchParcel parcel;
  const DeliveryOverdueBanner({super.key, required this.parcel});

  @override
  Widget build(BuildContext context) {
    final c = MC.of(context);
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: c.errorContainer,
        borderRadius: BorderRadius.circular(12),
      ),
      child: Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
        Icon(Icons.warning, color: c.coral, size: 20),
        const SizedBox(width: 10),
        Expanded(
          child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text('Overdue$kDot${parcel.failReason ?? 'Delivery failed'}',
                    style: ts(13, w: FontWeight.w700, c: c.coral)),
                Text(
                    'Return the parcel to the hub. Logistics clears it when it is scanned back in.',
                    style: ts(12, c: c.coral, h: 1.4)),
              ]),
        ),
      ]),
    );
  }
}
