# DailyCart Mobile Apps

Shared Flutter codebase for the DailyCart Customer, Vendor, and Rider apps on
Android and iOS.

## Production app identities

| Flavor | Android application ID | iOS bundle ID | Deep-link scheme |
| --- | --- | --- | --- |
| `customer` | `com.dailycart.customer` | `com.dailycart.customer` | `dailycart-customer` |
| `vendor` | `com.dailycart.vendor` | `com.dailycart.vendor` | `dailycart-vendor` |
| `rider` | `com.dailycart.rider` | `com.dailycart.rider` | `dailycart-rider` |

The old `com.dailycart.app` identity is not a production app identity.

## Local setup

Validated toolchain baseline:

- Flutter `3.44.6` stable / Dart `3.12.2`
- JDK `21`
- Android SDK `36.1`
- Gradle `9.1.0` / Android Gradle Plugin `9.0.1`

1. Install Flutter stable and confirm `flutter doctor -v` reports no issues.
2. Copy `.env.example` to `.env`.
3. Do not run `flutter create .` over this project: the checked-in Android
   flavors and iOS schemes are the source of truth.
4. Resolve the locked dependencies:

```bash
flutter pub get --enforce-lockfile
```

5. Start the Laravel backend, then run one role app:

```bash
flutter run --flavor customer -t lib/main_customer.dart
flutter run --flavor vendor -t lib/main_vendor.dart
flutter run --flavor rider -t lib/main_rider.dart
```

On Windows, `run_project.bat customer`, `run_project.bat vendor`, and
`run_project.bat rider` provide the same launch commands.

## Flavor builds

```bash
flutter build appbundle --flavor customer -t lib/main_customer.dart
flutter build appbundle --flavor vendor -t lib/main_vendor.dart
flutter build appbundle --flavor rider -t lib/main_rider.dart

flutter build ipa --flavor customer -t lib/main_customer.dart
flutter build ipa --flavor vendor -t lib/main_vendor.dart
flutter build ipa --flavor rider -t lib/main_rider.dart
```

Android release builds currently use the debug signing key only for local
validation. Configure `android/key.properties` and a production signing
configuration before creating a store artifact.

Each role must be registered as a separate Android and iOS app in the same
Firebase project. Place the downloaded Android files at:

```text
android/app/src/customer/google-services.json
android/app/src/vendor/google-services.json
android/app/src/rider/google-services.json
```

Place the matching iOS files at:

```text
ios/Runner/Firebase/customer/GoogleService-Info.plist
ios/Runner/Firebase/vendor/GoogleService-Info.plist
ios/Runner/Firebase/rider/GoogleService-Info.plist
```

The Android plugin and iOS copy phase select the configuration for the active
role automatically. Never reuse a Firebase configuration registered for a
different application or bundle identifier.

For Android emulator, use `10.0.2.2` to reach Laravel running on your host machine.

## App icons

The launcher icon source is `assets/images/logo.png`.

After the platform folders exist, generate Android and iOS icons:

```bash
flutter pub get
dart run flutter_launcher_icons
```
