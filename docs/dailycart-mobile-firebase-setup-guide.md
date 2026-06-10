# DailyCart Firebase Setup Guide

## Firebase Products

Enable these Firebase products:

1. Cloud Messaging
2. Analytics
3. Crashlytics

## Flutter Packages

Configured in `dailycart_mobile/pubspec.yaml`:

- `firebase_core`
- `firebase_messaging`
- `firebase_analytics`
- `firebase_crashlytics`
- `flutter_local_notifications`

Run:

```powershell
cd dailycart_mobile
flutter pub get
```

## Android Configuration

1. Create an Android app in Firebase with package name `com.dailycart.app`.
2. Download `google-services.json`.
3. Place it at:

```text
dailycart_mobile/android/app/google-services.json
```

4. Confirm Gradle Firebase plugin setup is present after running FlutterFire configuration.
5. Enable notification permission for Android 13+ in native configuration if needed.

## iOS Configuration

1. Create an iOS app in Firebase with bundle ID `com.dailycart.app`.
2. Download `GoogleService-Info.plist`.
3. Add it to:

```text
dailycart_mobile/ios/Runner/GoogleService-Info.plist
```

4. Open Xcode and ensure the plist is included in the Runner target.
5. Enable Push Notifications and Background Modes.
6. Upload APNs key or certificate in Firebase Console.

## FlutterFire Recommended Setup

Install FlutterFire CLI and run:

```powershell
dart pub global activate flutterfire_cli
cd dailycart_mobile
flutterfire configure
```

## Crashlytics Setup

1. Add `firebase_crashlytics`.
2. Initialize Crashlytics after `Firebase.initializeApp()`.
3. Forward Flutter framework errors to Crashlytics.
4. Upload Android symbols and iOS dSYMs during release.

## Analytics Events

Track:

- `login`
- `product_view`
- `add_to_cart`
- `checkout_started`
- `payment_success`
- `order_delivered`

Do not send passwords, tokens, phone verification codes, or full addresses as analytics parameters.
