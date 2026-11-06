import 'package:flutter/material.dart';
import '../models/product.dart';

class WishlistState extends ChangeNotifier {
  final List<Product> _items = [];

  List<Product> get items => List.unmodifiable(_items);

  int get count => _items.length;

  bool contains(Product product) => _items.any((p) => p.id == product.id);

  void toggle(Product product) {
    if (contains(product)) {
      _items.removeWhere((p) => p.id == product.id);
    } else {
      _items.add(product);
    }
    notifyListeners();
  }

  void remove(Product product) {
    _items.removeWhere((p) => p.id == product.id);
    notifyListeners();
  }

  void removeAll(List<Product> products) {
    for (final p in products) {
      _items.removeWhere((item) => item.id == p.id);
    }
    notifyListeners();
  }

  void clear() {
    _items.clear();
    notifyListeners();
  }
}
