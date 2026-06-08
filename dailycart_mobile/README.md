# DailyCart Mobile App

Flutter mobile app for DailyCart customers, built for Android and iOS.

## Local setup

1. Install Flutter latest stable.
2. Copy `.env.example` to `.env`.
3. Generate Android and iOS platform folders:

```bash
flutter create --project-name dailycart_mobile --org com.dailycart --platforms=android,ios .
```

4. Start the Laravel backend.
5. Run:

```bash
flutter pub get
flutter run
```

For Android emulator, use `10.0.2.2` to reach Laravel running on your host machine.

## App icons

The launcher icon source is `assets/images/logo.png`.

After the platform folders exist, generate Android and iOS icons:

```bash
flutter pub get
dart run flutter_launcher_icons
```
