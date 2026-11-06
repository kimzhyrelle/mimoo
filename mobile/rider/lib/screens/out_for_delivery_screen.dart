import 'package:flutter/material.dart';
import '../data/parcel_flow.dart';
import '../widgets/delivery_sections.dart';
import 'delivery_detail.dart';

/// Kept for the existing route/barrel exports; renders the unified delivery
/// detail screen.
const List<String> lifecycleSteps = kDeliverySteps;

class OutForDeliveryScreen extends StatelessWidget {
  final DispatchParcel parcel;
  final VoidCallback onBack;
  final Future<bool> Function() onMarkOut;
  const OutForDeliveryScreen({
    super.key,
    required this.parcel,
    required this.onBack,
    required this.onMarkOut,
  });

  @override
  Widget build(BuildContext context) => DeliveryDetailScreen(
        parcel: parcel,
        onBack: onBack,
        onMarkOut: onMarkOut,
      );
}
