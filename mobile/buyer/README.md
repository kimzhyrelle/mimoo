# Mobile Buyer — Flutter Ecommerce App

A full-featured mobile ecommerce app built with Flutter and Material 3.

## Features

- Product grid with search and category filters
- Product detail page with hero image and ratings
- Shopping cart with quantity controls and order summary
- Free shipping threshold logic
- Profile page with account settings
- Reactive state management using `ChangeNotifier` (no external packages)

## Project Structure

```
mobile-buyer/
├── lib/
│   └── main.dart          # All app code (models, state, pages, widgets)
├── android/               # Android platform files
├── ios/                   # iOS platform files
├── web/                   # Web entry point
├── assets/
│   └── images/            # Local image assets (optional)
├── pubspec.yaml
├── analysis_options.yaml
└── README.md
```

## Getting Started

1. Make sure Flutter is installed: https://docs.flutter.dev/get-started/install
2. Install dependencies:
   ```bash
   flutter pub get
   ```
3. Run on a connected device or emulator:
   ```bash
   flutter run
   ```

## Requirements

- Flutter SDK >= 3.0.0
- Dart SDK >= 3.0.0
