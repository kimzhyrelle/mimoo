import 'package:flutter/material.dart';
import 'cart_state.dart';

class CartProvider extends InheritedNotifier<CartState> {
  const CartProvider({
    super.key,
    required CartState cart,
    required super.child,
  }) : super(notifier: cart);

  static final CartState defaultCart = CartState();

  static CartState of(BuildContext context) {
    final provider = context.dependOnInheritedWidgetOfExactType<CartProvider>();
    if (provider?.notifier != null) {
      return provider!.notifier!;
    }
    return defaultCart;
  }
}
