# mimoo_rider

A new Flutter project.

## Getting Started

This project is a starting point for a Flutter application.

A few resources to get you started if this is your first Flutter project:

- [Learn Flutter](https://docs.flutter.dev/get-started/learn-flutter)
- [Write your first Flutter app](https://docs.flutter.dev/get-started/codelab)
- [Flutter learning resources](https://docs.flutter.dev/reference/learning-resources)

For help getting started with Flutter development, view the
[online documentation](https://docs.flutter.dev/), which offers tutorials,
samples, guidance on mobile development, and a full API reference.

## Screen routes

The screens exported by `lib/mimoo_screens.dart` are available through the
named routes declared in `MimooRoutes` in `lib/main.dart`. These screens require
data and callbacks, so pass a configured screen as the route argument:

```dart
Navigator.pushNamed(
  context,
  MimooRoutes.notifications,
  arguments: NotificationsScreen(items: notifications),
);
```

The routes cover activation, registration decisions, suspension, job offers,
pickup and delivery, waybill scanning, COD remittance, and notifications. Each
route checks that its argument has the expected screen type and reports a clear
error if it is missing or incorrect.

`showLocationPermissionSheet` is a modal helper, while `LocationOffBanner`,
`OfflineBanner`, and `SystemStateView` are inline widgets rather than routes.
