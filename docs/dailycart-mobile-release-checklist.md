# DailyCart Mobile Release Checklist

## Versioning Strategy

- `1.0.0`: First production release.
- `1.0.1`: Patch release for bug fixes only.
- `1.1.0`: Minor release with new features.
- `2.0.0`: Major release with breaking UX/API changes.

Build number must increase for every store upload.

## Before Release

1. Run `flutter clean`.
2. Run `flutter pub get`.
3. Run `flutter analyze`.
4. Run unit/widget tests.
5. Test on Android physical device.
6. Test on iPhone physical device.
7. Confirm Firebase Android and iOS config files.
8. Confirm Google Maps API keys.
9. Confirm PayHere production/sandbox mode.
10. Confirm Laravel production API URL.

## App Icon And Splash

Run:

```powershell
cd dailycart_mobile
flutter pub run flutter_launcher_icons
flutter pub run flutter_native_splash:create
```

## Android Build

```powershell
cd dailycart_mobile
flutter build appbundle --release
```

## iOS Build

```powershell
cd dailycart_mobile
flutter build ipa --release
```

## Privacy Links

- Privacy Policy: `https://dailycart.lk/privacy-policy`
- Terms: `https://dailycart.lk/terms-and-conditions`
- Refund Policy: `https://dailycart.lk/refund-policy`
