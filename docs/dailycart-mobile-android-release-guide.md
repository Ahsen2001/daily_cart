# DailyCart Android Release Guide

## Release Identity

App name: DailyCart  
Package name: `com.dailycart.app`  
Release artifact: Android App Bundle  
Output:

```text
build/app/outputs/bundle/release/app-release.aab
```

## Pre-Build Checklist

1. Confirm API URL is `https://dailycart.lk/api`.
2. Confirm `google-services.json` exists.
3. Confirm app icon is generated from `assets/images/logo.png`.
4. Confirm release version in `pubspec.yaml`.
5. Confirm `key.properties` is not committed.

## Generate Keystore

Run from a secure folder:

```powershell
keytool -genkey -v -keystore dailycart-release-key.jks -keyalg RSA -keysize 2048 -validity 10000 -alias dailycart
```

Back up the keystore securely. Losing it can block future app updates.

## key.properties

Create:

```text
dailycart_mobile/android/key.properties
```

Example:

```properties
storePassword=YOUR_STORE_PASSWORD
keyPassword=YOUR_KEY_PASSWORD
keyAlias=dailycart
storeFile=C:\\secure\\dailycart-release-key.jks
```

## Signing Configuration

Configure `android/app/build.gradle` to load `key.properties` and use `signingConfigs.release`.

## Build Commands

```powershell
cd dailycart_mobile
flutter clean
flutter pub get
flutter build appbundle --release
```

## Final Android QA

1. Install release build on physical Android device.
2. Test login, cart, checkout, PayHere, notifications, maps, vendor flow, and rider flow.
3. Confirm no debug banners or test URLs remain.
4. Upload `.aab` to Google Play Console.
