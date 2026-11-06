import 'package:flutter/material.dart';
import 'wishlist_state.dart';

class WishlistProvider extends InheritedNotifier<WishlistState> {
  const WishlistProvider({
    super.key,
    required WishlistState wishlist,
    required super.child,
  }) : super(notifier: wishlist);

  static final WishlistState defaultWishlist = WishlistState();

  static WishlistState of(BuildContext context) {
    final provider =
        context.dependOnInheritedWidgetOfExactType<WishlistProvider>();
    if (provider?.notifier != null) return provider!.notifier!;
    return defaultWishlist;
  }
}
