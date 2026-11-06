import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import '../core/app_info.dart';
import '../core/auth_controller.dart';
import '../core/error_messages.dart';
import '../core/job_offer_notifications.dart';
import '../core/settings_controller.dart';
import '../core/theme.dart';
import '../data/repositories/app_repositories_scope.dart';
import '../widgets/common.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});
  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  bool _hide = true;
  bool _isLoading = false;
  bool _debugVisible = false;
  int _debugPresses = 0;
  String? _error;
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();

  @override
  void dispose() {
    _emailController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  Future<void> _login() async {
    if (_isLoading) return;
    setState(() {
      _isLoading = true;
      _error = null;
    });
    try {
      await AppRepositoriesScope.of(context)
          .signIn(_emailController.text.trim(), _passwordController.text);
      if (!mounted) return;
      AuthScope.of(context).signIn();
      Navigator.pushReplacementNamed(context, '/home');
    } catch (error) {
      if (!mounted) return;
      setState(() => _error = friendlyError(error));
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  Future<void> _loginWithPasskey() async {
    if (_isLoading) return;
    setState(() {
      _isLoading = true;
      _error = null;
    });
    try {
      final settings = SettingsScope.of(context);
      final authScope = AuthScope.of(context);

      if (!settings.passkeySetUp) {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
              content: Text(
                  'No passkey registered. Sign in with your password first, then set up a passkey in Settings.')));
        }
        return;
      }

      final ok = await settings.authenticateBiometric('Sign in to Mimoo Rider');
      if (!mounted) return;
      if (!ok) {
        setState(() => _error = 'Biometric authentication failed. Please try again.');
        return;
      }

      // Biometrics passed — sign in by refreshing local data without password check.
      await AppRepositoriesScope.of(context).signIn('rider@mimoo.ph', '___passkey___');
      if (!mounted) return;
      authScope.signIn();
      Navigator.pushReplacementNamed(context, '/home');
    } catch (error) {
      if (!mounted) return;
      setState(() => _error = friendlyError(error));
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  void _revealDebugTools() {
    if (!kDebugMode || _debugVisible) return;
    if (++_debugPresses >= 5) setState(() => _debugVisible = true);
  }

  void _clearError() {
    if (_error != null) setState(() => _error = null);
  }

  @override
  Widget build(BuildContext context) {
    final startupError = AuthScope.of(context).startupError;
    final message = _error ?? startupError;
    return Scaffold(
      backgroundColor: MC.bg,
      body: SafeArea(
        child: LayoutBuilder(
          builder: (context, constraints) => SingleChildScrollView(
            padding: const EdgeInsets.all(20),
            child: Center(
              child: ConstrainedBox(
                constraints: const BoxConstraints(maxWidth: 480),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    _brandHeader(),
                    const SizedBox(height: 16),
                    _loginCard(message),
                    const SizedBox(height: 16),
                    MCard(
                      color: MC.container,
                      child: Row(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Icon(Icons.info,
                                color: MC.primaryContainer, size: 20),
                            const SizedBox(width: 10),
                            Expanded(
                                child: Text.rich(TextSpan(
                                    style: MT.bodySm.copyWith(
                                        color: MC.onSurface),
                                    children: const [
                                  TextSpan(
                                      text: 'Need access? ',
                                      style: TextStyle(
                                          fontWeight: FontWeight.w700)),
                                  TextSpan(
                                      text:
                                          'Ask your logistics company to provision your rider account.'),
                                ]))),
                          ]),
                    ),
                    if (kDebugMode && _debugVisible) ...[
                      const SizedBox(height: 16),
                      _debugTools(),
                    ],
                  ],
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }

  Widget _brandHeader() => GestureDetector(
        onLongPress: _revealDebugTools,
        child: Container(
          padding: const EdgeInsets.symmetric(vertical: 30, horizontal: 24),
          decoration: BoxDecoration(
            gradient: const LinearGradient(
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
              colors: [MC.violetDark, MC.violet],
            ),
            borderRadius: BorderRadius.circular(20),
          ),
          child: Text(
            kRiderWordmark,
            textAlign: TextAlign.center,
            style: MT.headlineXl.copyWith(
                color: Colors.white, letterSpacing: -0.5),
          ),
        ),
      );

  Widget _loginCard(String? message) => MCard(
        radius: 20,
        padding: const EdgeInsets.all(20),
        child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(children: [
                Expanded(
                    child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                      Text('Welcome Back, Rider', style: MT.headlineLg),
                      Text(
                          'Sign in to check dispatch feeds & start your shift',
                          style: MT.bodyMd
                              .copyWith(color: MC.onSurfaceVariant)),
                    ])),
                const IconDot(Icons.two_wheeler, size: 48),
              ]),
              const SizedBox(height: 16),
              MField('Email Address',
                  prefix: Icons.mail_outline,
                  keyboard: TextInputType.emailAddress,
                  controller: _emailController,
                  onChanged: (_) => _clearError()),
              const SizedBox(height: 16),
              MField('Password',
                  prefix: Icons.lock_outline,
                  obscure: _hide,
                  controller: _passwordController,
                  onChanged: (_) => _clearError()),
              Align(
                alignment: Alignment.centerRight,
                child: TextButton(
                    onPressed: () => setState(() => _hide = !_hide),
                    child: Text(_hide ? 'Show password' : 'Hide password',
                        style: MT.labelMd.copyWith(color: MC.primary))),
              ),
              const SizedBox(height: 4),
              PillButton('Log In',
                  loading: _isLoading, onPressed: _isLoading ? null : _login),
              const SizedBox(height: 8),
              PillButton.secondary(
                'Sign in with Passkey',
                leading: Icons.fingerprint,
                trailing: null,
                onPressed: _isLoading ? null : _loginWithPasskey,
              ),
              if (message != null) ...[
                const SizedBox(height: 12),
                _errorBanner(message, closable: _error != null),
              ],
            ]),
      );

  Widget _errorBanner(String message, {required bool closable}) => Container(
        padding: const EdgeInsets.fromLTRB(12, 8, 4, 8),
        decoration: BoxDecoration(
          color: MC.errorSoft,
          borderRadius: BorderRadius.circular(12),
        ),
        child: Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
          const Padding(
            padding: EdgeInsets.only(top: 4),
            child: Icon(Icons.error_outline, color: MC.coral, size: 20),
          ),
          const SizedBox(width: 10),
          Expanded(
            child: Padding(
              padding: const EdgeInsets.symmetric(vertical: 4),
              child: Text(message,
                  style: MT.bodySm.copyWith(
                      color: MC.coral, fontWeight: FontWeight.w600)),
            ),
          ),
          if (closable)
            IconButton(
              tooltip: 'Dismiss',
              onPressed: _clearError,
              icon: const Icon(Icons.close, size: 18, color: MC.coral),
            )
          else
            const SizedBox(width: 8),
        ]),
      );

  Widget _debugTools() => MCard(
        color: MC.low,
        child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text('DEBUG TOOLS',
                  style: MT.badge.copyWith(color: MC.onSurfaceVariant)),
              const SizedBox(height: 4),
              Wrap(spacing: 8, runSpacing: 4, children: [
                TextButton(
                    onPressed: () => Navigator.pushNamed(
                        context, '/activation/set-password'),
                    child: const Text('Activation')),
                TextButton(
                    onPressed: () => Navigator.pushNamed(
                        context, '/activation/link-expired'),
                    child: const Text('Link Expired')),
                TextButton(
                    onPressed: () => Navigator.pushNamed(
                        context, '/registration/approved'),
                    child: const Text('Approved')),
                TextButton(
                    onPressed: () => Navigator.pushNamed(
                        context, '/registration/rejected'),
                    child: const Text('Rejected')),
                TextButton(
                    onPressed: () =>
                        Navigator.pushNamed(context, '/suspended'),
                    child: const Text('Suspended')),
                TextButton(
                  onPressed: () => JobOfferNotifications.showJobOffer(
                    offerId: 'OFFER-1001',
                    area: 'Commonwealth, QC',
                    payout: '₱30.00',
                    cod: '₱500.00',
                    km: '7.4',
                  ),
                  child: const Text('Test Notification'),
                ),
              ]),
            ]),
      );
}
