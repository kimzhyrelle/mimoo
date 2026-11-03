import 'package:flutter_local_notifications/flutter_local_notifications.dart';

/// Lockscreen / local job-offer notifications.
/// Firebase / FCM has been removed — the app runs fully offline.
/// Offers are seeded locally; this class handles only local notifications.
class JobOfferNotifications {
  static final _plugin = FlutterLocalNotificationsPlugin();
  static const _channel = AndroidNotificationChannel('job_offers', 'Job offers',
      description: 'New pickup and delivery offers', importance: Importance.max);

  static Future<void> init(void Function(String offerId, String action) onAction) async {
    await _plugin.initialize(
      const InitializationSettings(
        android: AndroidInitializationSettings('@mipmap/ic_launcher'),
        iOS: DarwinInitializationSettings(),
      ),
      onDidReceiveNotificationResponse: (r) {
        if (r.payload != null) onAction(r.payload!, r.actionId ?? 'open');
      },
    );
    await _plugin
        .resolvePlatformSpecificImplementation<AndroidFlutterLocalNotificationsPlugin>()
        ?.createNotificationChannel(_channel);
  }

  /// Ask for notification permission (Android 13+ and iOS).
  static Future<void> requestPermission() async {
    final android = _plugin
        .resolvePlatformSpecificImplementation<AndroidFlutterLocalNotificationsPlugin>();
    await android?.requestNotificationsPermission();
    final ios = _plugin
        .resolvePlatformSpecificImplementation<IOSFlutterLocalNotificationsPlugin>();
    await ios?.requestPermissions(alert: true, badge: true, sound: true);
  }

  static Future<void> showJobOffer({
    required String offerId,
    required String area,
    required String payout,
    required String cod,
    required String km,
    int timeoutSeconds = 40,
  }) =>
      _plugin.show(
        offerId.hashCode,
        '🔥 New job offer · Pickup in $area',
        'Payout $payout · COD $cod · $km km total',
        NotificationDetails(
          android: AndroidNotificationDetails(
            _channel.id,
            _channel.name,
            importance: Importance.max,
            priority: Priority.max,
            category: AndroidNotificationCategory.call,
            visibility: NotificationVisibility.public,
            timeoutAfter: timeoutSeconds * 1000,
            showProgress: true,
            maxProgress: timeoutSeconds,
            progress: timeoutSeconds,
            fullScreenIntent: true,
            ongoing: true,
            actions: const [
              AndroidNotificationAction('decline', 'Decline'),
              AndroidNotificationAction('accept', 'Accept',
                  showsUserInterface: true),
            ],
          ),
        ),
        payload: offerId,
      );
}
