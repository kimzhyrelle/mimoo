import 'package:flutter/widgets.dart';

/// Tracks whether a rider currently has an active session. Pre-login screens
/// stay light; the rider's appearance preference only applies after sign-in.
class AuthController extends ChangeNotifier {
  bool _isSignedIn = false;
  String? _startupError;

  bool get isSignedIn => _isSignedIn;
  String? get startupError => _startupError;

  void setStartupError(String? message) {
    if (_startupError == message) return;
    _startupError = message;
    notifyListeners();
  }

  void signIn() {
    _startupError = null;
    if (_isSignedIn) return;
    _isSignedIn = true;
    notifyListeners();
  }

  void signOut() {
    if (!_isSignedIn) return;
    _isSignedIn = false;
    notifyListeners();
  }
}

/// Provides the controller to the widget tree without adding a state package.
class AuthScope extends InheritedNotifier<AuthController> {
  const AuthScope(
      {super.key, required AuthController controller, required super.child})
      : super(notifier: controller);

  static AuthController of(BuildContext context) {
    final scope = context.dependOnInheritedWidgetOfExactType<AuthScope>();
    assert(scope != null, 'AuthScope is missing above this widget');
    return scope!.notifier!;
  }
}
