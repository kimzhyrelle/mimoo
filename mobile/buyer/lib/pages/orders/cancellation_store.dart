/// In-memory store for cancelled orders.
///
/// This lets orders_page.dart react to cancellations without a full state
/// management solution. Replace with a proper ChangeNotifier or Riverpod
/// provider when connecting to a real backend.
class CancellationStore {
  CancellationStore._();

  static final Map<String, CancelRecord> _records = {};

  /// Mark an order as cancelled.
  static void cancel({
    required String orderId,
    required String reason,
    required String comment,
    required bool isPaid,
  }) {
    _records[orderId] = CancelRecord(
      orderId: orderId,
      reason: reason,
      comment: comment,
      isPaid: isPaid,
      cancelledAt: DateTime.now(),
    );
  }

  /// Returns true if this order has been cancelled through the flow.
  static bool isCancelled(String orderId) => _records.containsKey(orderId);

  /// Retrieves the cancel record for an order, or null if not cancelled.
  static CancelRecord? recordFor(String orderId) => _records[orderId];
}

class CancelRecord {
  const CancelRecord({
    required this.orderId,
    required this.reason,
    required this.comment,
    required this.isPaid,
    required this.cancelledAt,
  });

  final String orderId;
  final String reason;
  final String comment;
  final bool isPaid;
  final DateTime cancelledAt;
}
