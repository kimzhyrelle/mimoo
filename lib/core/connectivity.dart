import 'dart:async';

import 'package:connectivity_plus/connectivity_plus.dart';
import 'package:flutter/material.dart';

import 'theme.dart';

/// App-wide connectivity state shared by the sync badge and offline banners.
abstract final class ConnectivityMonitor {
  static final ValueNotifier<bool> isOffline = ValueNotifier<bool>(false);
  static final Connectivity _connectivity = Connectivity();
  static StreamSubscription<List<ConnectivityResult>>? _subscription;
  static bool _started = false;

  static void start() {
    if (_started) return;
    _started = true;
    unawaited(refresh());
    _subscription = _connectivity.onConnectivityChanged.listen(_apply);
  }

  static Future<void> refresh() async {
    try {
      _apply(await _connectivity.checkConnectivity());
    } catch (error) {
      debugPrint('Connectivity check failed: $error');
    }
  }

  static Future<void> stop() async {
    await _subscription?.cancel();
    _subscription = null;
    _started = false;
  }

  static void _apply(List<ConnectivityResult> results) {
    isOffline.value = results.isEmpty || results.every((result) => result == ConnectivityResult.none);
  }
}

/// Renders the shared [OfflineBanner] while the device has no network.
class ConnectivityBanner extends StatelessWidget {
  const ConnectivityBanner({super.key});

  @override
  Widget build(BuildContext context) => ValueListenableBuilder<bool>(
        valueListenable: ConnectivityMonitor.isOffline,
        builder: (context, offline, _) => offline
            ? Padding(
                padding: const EdgeInsets.only(bottom: 12),
                child: OfflineBanner(onRetry: ConnectivityMonitor.refresh),
              )
            : const SizedBox.shrink(),
      );
}

/// Offline strip: place at the top of Jobs / POD screens, driven by connectivity_plus.
class OfflineBanner extends StatelessWidget {
  final VoidCallback onRetry;
  final String message;
  const OfflineBanner({super.key, required this.onRetry, this.message = 'No internet connection'});
  @override
  Widget build(BuildContext context) => Container(padding: const EdgeInsets.all(12), decoration: BoxDecoration(color: MC.errorSoft, borderRadius: BorderRadius.circular(12)),
    child: Row(children: [const Icon(Icons.wifi_off, color: MC.coral, size: 20), const SizedBox(width: 8), Expanded(child: Text(message, style: ts(12, w: FontWeight.w600, c: const Color(0xFF93000A)))),
      FilledButton.icon(style: FilledButton.styleFrom(backgroundColor: MC.coral, visualDensity: VisualDensity.compact), icon: const Icon(Icons.sync, size: 14), label: const Text('Retry'), onPressed: onRetry)]));
}
