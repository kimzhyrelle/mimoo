import 'dart:async';

import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter/material.dart';

import 'core/connectivity.dart';
import 'core/theme.dart';
import 'data/mock_data.dart';
import 'mimoo_screens.dart';
import 'screens/chat.dart';
import 'screens/delivered.dart';
import 'screens/delivery_detail.dart';
import 'screens/login.dart';
import 'screens/main_shell.dart';
import 'screens/pod_flow.dart';
import 'screens/registration.dart';

final GlobalKey<NavigatorState> mimooNavigatorKey = GlobalKey<NavigatorState>();

abstract final class MimooRoutes {
  static const activationSetPassword = '/activation/set-password';
  static const activationLinkExpired = '/activation/link-expired';
  static const activationSuccess = '/activation/success';
  static const registrationApproved = '/registration/approved';
  static const registrationRejected = '/registration/rejected';
  static const suspended = '/suspended';
  static const jobOffer = '/job-offer';
  static const pickupDetail = '/pickup-detail';
  static const scanWaybill = '/scan-waybill';
  static const outForDelivery = '/out-for-delivery';
  static const codRemittance = '/cod-remittance';
  static const notifications = '/notifications';
}

Future<void> main() async {
  WidgetsFlutterBinding.ensureInitialized();
  ConnectivityMonitor.start();
  await JobOfferNotifications.init(_onNotificationAction);
  unawaited(_initFirebaseMessaging());
  runApp(const MimooRiderApp());
}

/// Lockscreen notification action handler. Accept/open opens the offer details
/// (accepting there leads into the pickup flow). Decline simply dismisses.
void _onNotificationAction(String offerId, String action) {
  if (action == 'decline') return;
  final navigator = mimooNavigatorKey.currentState;
  if (navigator == null) return;
  navigator.pushNamed(MimooRoutes.jobOffer, arguments: offerId);
}

/// TODO(firebase): add google-services.json / GoogleService-Info.plist and the
/// Firebase Gradle/CocoaPods setup. Without that configuration this call is
/// caught and the app still runs with local demo notifications.
Future<void> _initFirebaseMessaging() async {
  try {
    await Firebase.initializeApp();
    FirebaseMessaging.onBackgroundMessage(firebaseMessagingBackgroundHandler);
    await FirebaseMessaging.instance.requestPermission();
  } catch (error) {
    debugPrint('Firebase is not configured yet; push messaging disabled: $error');
  }
}

class MimooRiderApp extends StatelessWidget {
  const MimooRiderApp({super.key});

  @override
  Widget build(BuildContext context) => MaterialApp(
        title: 'Mimoo Rider',
        debugShowCheckedModeBanner: false,
        navigatorKey: mimooNavigatorKey,
        theme: mimooTheme(),
        initialRoute: '/login',
        routes: {
          '/login': (_) => const LoginScreen(),
          '/register': (_) => const RegistrationScreen(),
          '/pending': (_) => const PendingApprovalScreen(),
          '/home': (_) => const MainShell(),
          '/delivery': (_) => const DeliveryDetailScreen(),
          '/pod': (_) => const PodFlowScreen(),
          '/delivered': (context) {
            final argument = ModalRoute.of(context)?.settings.arguments;
            return DeliveredScreen(photoPath: argument is String ? argument : null);
          },
          '/chat': (_) => const ChatListScreen(),
          '/chat/thread': (_) => const ChatThreadScreen(),
          MimooRoutes.notifications: (context) => NotificationsScreen(
                items: mockNotifications,
                onTap: (notification) {
                  if (notification.kind == NotifKind.jobOffer) {
                    Navigator.of(context).pushNamed(MimooRoutes.jobOffer);
                  }
                },
              ),
          MimooRoutes.jobOffer: (context) {
            final argument = ModalRoute.of(context)?.settings.arguments;
            final offerId = argument is String ? argument : 'OFFER-1001';
            return JobOfferScreen(
              offer: mockJobOffer(id: offerId),
              onAccept: () async {
                Navigator.of(context).pushReplacementNamed(MimooRoutes.pickupDetail);
                return OfferResult.accepted;
              },
              onDecline: () => Navigator.of(context).popUntil((route) => route.isFirst),
              onBackHome: () => Navigator.of(context).popUntil((route) => route.isFirst),
              onExpired: () => Navigator.of(context).popUntil((route) => route.isFirst),
            );
          },
          MimooRoutes.pickupDetail: (context) => PickupDetailScreen(
                job: mockPickupJob,
                onBack: () => Navigator.of(context).pop(),
                onScanWaybill: () => Navigator.of(context).pushNamed(MimooRoutes.scanWaybill),
              ),
          MimooRoutes.scanWaybill: (context) => ScanWaybillScreen(
                expectedTracking: mockPickupJob.tracking,
                pickupPay: mockPickupJob.pay,
                cod: 500,
                // TODO(api): validate the scanned waybill against the backend.
                onValidate: (code) async => code == mockPickupJob.tracking
                    ? ScanResult(ScanOutcome.ok, code)
                    : ScanResult(ScanOutcome.mismatch, code),
                onConfirmed: (_) => Navigator.of(context).pop(),
                onBack: () => Navigator.of(context).pop(),
              ),
          MimooRoutes.outForDelivery: (context) => OutForDeliveryScreen(
                parcel: mockHubParcel,
                onBack: () => Navigator.of(context).pop(),
                // TODO(api): persist the "out for delivery" status before navigating.
                onMarkOut: () async {
                  await Future<void>.delayed(const Duration(milliseconds: 500));
                  if (!context.mounted) return;
                  Navigator.of(context).pushReplacementNamed('/delivery');
                },
              ),
          MimooRoutes.codRemittance: (context) => CodRemittanceScreen(
                parcels: mockCollectedParcels,
                fleetName: 'Bilis Hatid Express Inc.',
                dueLabel: '10:00 PM',
                // TODO(api): post the remittance and refresh the wallet balance.
                onSubmit: (amount, method) async {
                  await Future<void>.delayed(const Duration(milliseconds: 400));
                },
              ),
          MimooRoutes.activationSetPassword: (context) => SetPasswordScreen(
                riderId: 'RDR-2026-0842',
                fleetName: 'Bilis Hatid Express Inc.',
                expiresAt: DateTime.now().add(const Duration(hours: 23, minutes: 59)),
                // TODO(api): submit the password to the activation endpoint.
                onActivate: (password) async {
                  Navigator.of(context).pushReplacementNamed(MimooRoutes.activationSuccess);
                },
              ),
          MimooRoutes.activationLinkExpired: (context) => LinkExpiredScreen(
                fleetName: 'Bilis Hatid Express Inc.',
                email: 'pedrojose.garcia@example.com',
                phone: '+639171234567',
                onBackToLogin: () => Navigator.of(context).pushNamedAndRemoveUntil('/login', (_) => false),
                // TODO(api): ask the logistics company to resend the invitation.
                onRequestResend: () async {
                  await Future<void>.delayed(const Duration(milliseconds: 400));
                },
              ),
          MimooRoutes.activationSuccess: (context) => ActivationSuccessScreen(
                riderName: 'Pedro Jose Garcia',
                fleetName: 'Bilis Hatid Express Inc.',
                vehicle: 'Motorcycle · 123ABC',
                hub: 'Quezon City Hub',
                referenceId: 'RDR-2026-0842',
                onLogin: () => Navigator.of(context).pushNamedAndRemoveUntil('/login', (_) => false),
              ),
          MimooRoutes.registrationApproved: (context) => RegistrationApprovedScreen(
                fleetName: 'Bilis Hatid Express Inc.',
                riderId: 'RDR-2026-0842',
                hubName: 'Quezon City Hub',
                onLogin: () => Navigator.of(context).pushNamedAndRemoveUntil('/login', (_) => false),
              ),
          MimooRoutes.registrationRejected: (context) => RegistrationRejectedScreen(
                reason: "Driver's license photo is blurry and the expiry date is unreadable.",
                supportPhone: '+639171234567',
                onEditAndResubmit: () => Navigator.of(context).pushNamed('/register'),
              ),
          MimooRoutes.suspended: (context) => SuspendedScreen(
                fleetName: 'Bilis Hatid Express Inc.',
                reason: 'Unremitted COD balance past cutoff',
                dateLabel: 'Oct 26, 2026',
                hotline: '+639171234567',
                coordinatorName: 'Ana Dela Cruz',
                coordinatorHub: 'Quezon City Hub',
                coordinatorSms: '+639181234567',
                onLogout: () => Navigator.of(context).pushNamedAndRemoveUntil('/login', (_) => false),
              ),
        },
      );
}
