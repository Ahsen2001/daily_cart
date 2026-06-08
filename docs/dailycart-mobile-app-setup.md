# DailyCart Mobile App Setup

The Flutter source lives in `dailycart_mobile`.

## Current status

- Flutter app shell is scaffolded.
- Brand theme is implemented with Poppins, green/orange colors, rounded controls, and soft shadows.
- App routing is wired with splash, login, home, products, cart, checkout, PayHere WebView, and map screens.
- Laravel API client is configured through `.env`.
- Firebase Messaging and local notification bootstrap code is in place.
- Google Maps screen is in place.

## Required local install

Flutter is not currently available on this machine's PATH. Install Flutter latest stable, then run:

```bash
cd dailycart_mobile
flutter create --project-name dailycart_mobile --org com.dailycart --platforms=android,ios .
flutter pub get
dart run flutter_launcher_icons
flutter run
```

## Android

For an Android emulator, keep:

```env
API_BASE_URL=http://10.0.2.2:8000/api
```

Start Laravel from the repository root:

```bash
php artisan serve
```

Then run the mobile app:

```bash
cd dailycart_mobile
flutter run
```

## iOS

iOS requires macOS and Xcode. For the iOS simulator, use your Mac host IP or localhost depending on where the Laravel server is running:

```env
API_BASE_URL=http://127.0.0.1:8000/api
```

## Next backend step

The Laravel app currently has web routes, but no dedicated mobile API routes or token auth. Add Laravel Sanctum or another bearer-token API auth layer before connecting real login, cart, checkout, and notifications.

## UI foundation

The app entrypoint now uses:

- `lib/main.dart`
- `lib/app.dart`
- `lib/routes/app_router.dart`
- `lib/theme/light_theme.dart`
- `lib/theme/app_colors.dart`
- `lib/theme/app_text_styles.dart`

Reusable UI is in `lib/widgets`, including buttons, text fields, loading, empty, error, app bar, app drawer, cards, and logo widgets.
