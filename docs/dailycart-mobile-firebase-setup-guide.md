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

Create three Android apps in the same Firebase project. The package name in
Firebase must exactly match the corresponding production application ID:

| Role | Firebase Android package | Configuration path |
| --- | --- | --- |
| Customer | `com.dailycart.customer` | `dailycart_mobile/android/app/src/customer/google-services.json` |
| Vendor | `com.dailycart.vendor` | `dailycart_mobile/android/app/src/vendor/google-services.json` |
| Rider | `com.dailycart.rider` | `dailycart_mobile/android/app/src/rider/google-services.json` |

For each role:

1. In Firebase Console, open Project settings > Your apps and register the
   exact package shown above.
2. Add the debug and release SHA-1/SHA-256 fingerprints.
3. Download that app's `google-services.json` to the matching flavor directory.
4. Rebuild that flavor. The Gradle Google Services plugin is enabled
   automatically when its file is present.

Do not place one shared file at `android/app/google-services.json`, and do not
reuse the Customer file for Vendor or Rider. A local build can run without the
file, but Firebase initialization is intentionally skipped and push tokens will
not be registered.

Example verification:

```powershell
flutter run --flavor customer -t lib/main_customer.dart
```

The Android 13+ notification permission and separate role channel IDs are
already declared in the project.

## iOS Configuration

Create three iOS apps with bundle IDs `com.dailycart.customer`,
`com.dailycart.vendor`, and `com.dailycart.rider`. Download each app's
`GoogleService-Info.plist` and keep the files separated by role. The checked-in
Xcode build phase copies the matching role file into the Runner bundle as
`GoogleService-Info.plist` for the active `DAILYCART_FLAVOR`.

Recommended source locations:

```text
dailycart_mobile/ios/Runner/Firebase/customer/GoogleService-Info.plist
dailycart_mobile/ios/Runner/Firebase/vendor/GoogleService-Info.plist
dailycart_mobile/ios/Runner/Firebase/rider/GoogleService-Info.plist
```

Enable Push Notifications and Background Modes > Remote notifications for the
Runner target, then upload the APNs authentication key in Firebase Console.

## FlutterFire Recommended Setup

Install FlutterFire CLI when creating or refreshing the native Firebase apps:

```powershell
dart pub global activate flutterfire_cli
cd dailycart_mobile
flutterfire configure
```

Do not accept a generated `com.dailycart.app` registration. Select or create
the three final role-specific app identities above.

## Laravel Firebase HTTP v1

The mobile configuration files identify client apps; they do not authorize the
Laravel server to send notifications. On the server, also configure:

```dotenv
FIREBASE_PROJECT_ID=your-firebase-project-id
FIREBASE_CREDENTIALS=/absolute/private/path/firebase-service-account.json
```

Keep the service-account JSON outside source control and run the Laravel queue
worker so queued notification retries can execute:

```powershell
php artisan queue:work
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
