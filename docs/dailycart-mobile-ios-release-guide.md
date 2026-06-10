# DailyCart iOS Release Guide

## Release Identity

App name: DailyCart  
Bundle ID: `com.dailycart.app`  
Release artifact: IPA

## Apple Developer Setup

1. Enroll in Apple Developer Program.
2. Create App ID with bundle ID `com.dailycart.app`.
3. Enable Push Notifications.
4. Enable Associated Domains only if required later.
5. Create distribution certificate.
6. Create App Store provisioning profile.

## Xcode Configuration

1. Open:

```powershell
open dailycart_mobile/ios/Runner.xcworkspace
```

2. Select Runner target.
3. Set Bundle Identifier to `com.dailycart.app`.
4. Set Display Name to `DailyCart`.
5. Select Team.
6. Configure Signing & Capabilities.
7. Add Push Notifications and Background Modes.
8. Add `GoogleService-Info.plist` to Runner target.

## Build Command

```powershell
cd dailycart_mobile
flutter clean
flutter pub get
flutter build ipa --release
```

## Xcode Archive Process

1. Open `ios/Runner.xcworkspace`.
2. Select `Any iOS Device`.
3. Product > Archive.
4. Validate archive.
5. Distribute App > App Store Connect.
6. Upload.

## Final iOS QA

1. Test on a physical iPhone.
2. Test push notification permission.
3. Test PayHere WebView.
4. Test Google Maps rendering.
5. Test camera/photo permission for image upload and delivery proof.
