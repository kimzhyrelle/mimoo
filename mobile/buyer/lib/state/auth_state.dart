import 'package:flutter/material.dart';
import '../models/user.dart';

enum AuthStatus { unauthenticated, pending, authenticated }

class AuthState extends ChangeNotifier {
  AuthStatus _status = AuthStatus.unauthenticated;
  User? _currentUser;

  AuthStatus get status => _status;
  User? get currentUser => _currentUser;
  bool get isAuthenticated => _status == AuthStatus.authenticated;

  Future<void> login(String email, String password) async {
    // TODO: connect to API
    notifyListeners();
  }

  Future<void> register(User user) async {
    // TODO: connect to API
    _status = AuthStatus.pending;
    notifyListeners();
  }

  void logout() {
    _status = AuthStatus.unauthenticated;
    _currentUser = null;
    notifyListeners();
  }
}
