import 'package:flutter/material.dart';
import '../models/order.dart';

/// Minimal in-memory order state kept for backward-compat.
/// Real order data is fetched directly by OrdersPage via OrderRepository.
class OrderState extends ChangeNotifier {
  final List<Order> _orders = [];

  List<Order> get orders => List.unmodifiable(_orders);

  List<Order> get activeOrders => _orders
      .where((o) =>
          o.status != OrderStatus.completed &&
          o.status != OrderStatus.cancelled)
      .toList();

  List<Order> get completedOrders =>
      _orders.where((o) => o.status == OrderStatus.completed).toList();

  void addOrder(Order order) {
    _orders.add(order);
    notifyListeners();
  }

  /// No-op kept for compat — real status updates come from API refreshes.
  void updateStatus(int orderId, OrderStatus status) {
    notifyListeners();
  }
}
