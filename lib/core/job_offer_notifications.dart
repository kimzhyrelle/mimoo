import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';

/// Lockscreen job-offer push. This is an OS notification, not an in-app screen.
/// Send a data-only FCM message and call showJobOffer() from the background handler.
/// Accept/Decline taps arrive in onDidReceiveNotificationResponse -> response.actionId.
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
      onDidReceiveNotificationResponse: (r) { if (r.payload != null) onAction(r.payload!, r.actionId ?? 'open'); },
    );
    await _plugin.resolvePlatformSpecificImplementation<AndroidFlutterLocalNotificationsPlugin>()?.createNotificationChannel(_channel);
  }

  /// Ask for notification permission (Android 13+ and iOS). Call this when the
  /// rider goes online or after login, not while the app is starting.
  static Future<void> requestPermission() async {
    final android = _plugin.resolvePlatformSpecificImplementation<AndroidFlutterLocalNotificationsPlugin>();
    await android?.requestNotificationsPermission();
    final ios = _plugin.resolvePlatformSpecificImplementation<IOSFlutterLocalNotificationsPlugin>();
    await ios?.requestPermissions(alert: true, badge: true, sound: true);
  }

  static Future<void> showJobOffer({required String offerId, required String area, required String payout, required String cod, required String km, int timeoutSeconds = 40}) =>
      _plugin.show(offerId.hashCode, '🔥 New job offer · Pickup in $area', 'Payout $payout · COD $cod · $km km total',
        NotificationDetails(android: AndroidNotificationDetails(_channel.id, _channel.name, importance: Importance.max, priority: Priority.max,
          category: AndroidNotificationCategory.call, visibility: NotificationVisibility.public, timeoutAfter: timeoutSeconds * 1000,
          showProgress: true, maxProgress: timeoutSeconds, progress: timeoutSeconds, fullScreenIntent: true, ongoing: true,
          actions: const [AndroidNotificationAction('decline', 'Decline'), AndroidNotificationAction('accept', 'Accept', showsUserInterface: true)])),
        payload: offerId);
}

/// Background FCM handler for data-only job-offer messages.
///
/// TODO(firebase): add android/app/google-services.json and
/// ios/Runner/GoogleService-Info.plist, then enable the Firebase plugins in the
/// platform build files. Until those files exist Firebase.initializeApp() will
/// throw, which is caught so the app keeps running in demo mode.
@pragma('vm:entry-point')
Future<void> firebaseMessagingBackgroundHandler(RemoteMessage message) async {
  try {
    await Firebase.initializeApp();
  } catch (error) {
    debugPrint('Firebase is not configured yet; background message ignored: $error');
    return;
  }

  final data = message.data;
  final offerId = data['offerId'] ?? data['offer_id'];
  if (offerId == null) return;

  try {
    await JobOfferNotifications.showJobOffer(
      offerId: '$offerId',
      area: data['area'] ?? 'your area',
      payout: data['payout'] ?? '₱0.00',
      cod: data['cod'] ?? '₱0.00',
      km: data['km'] ?? '0',
      timeoutSeconds: int.tryParse('${data['timeoutSeconds'] ?? ''}') ?? 40,
    );
  } catch (error) {
    debugPrint('Could not show background job offer notification: $error');
  }
}
