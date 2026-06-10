# DailyCart Final Deployment Checklist

## Final Folder Structure

```text
dailycart_mobile/
  android/
  ios/
  assets/
    images/
    icons/
  lib/
    config/
    constants/
    models/
    providers/
    routes/
    screens/
      auth/
      customer/
      vendor/
      rider/
      onboarding/
      splash/
    services/
    theme/
    utils/
    widgets/
  test/
  pubspec.yaml
```

## Submission Checklist

1. Android package name is `com.dailycart.app`.
2. iOS bundle ID is `com.dailycart.app`.
3. App name is `DailyCart`.
4. Version is updated.
5. App icon generated.
6. Splash screen generated.
7. Firebase configured.
8. Crashlytics configured.
9. Analytics configured.
10. Store screenshots prepared.
11. Privacy, terms, and refund policy URLs live.
12. Production Laravel API tested.
13. PayHere tested.
14. Google Maps tested.
15. Push notifications tested.

## University Project Presentation Checklist

1. Explain the problem: grocery and daily essentials ordering.
2. Explain user roles: customer, vendor, rider, admin.
3. Show architecture: Flutter mobile app, Laravel API, Sanctum, Firebase, PayHere, Google Maps.
4. Demonstrate customer flow.
5. Demonstrate vendor flow.
6. Demonstrate rider flow.
7. Explain security: secure token storage and role-based API authorization.
8. Explain testing strategy.
9. Explain release process for Play Store and App Store.

## Demonstration Flow

1. Customer registers.
2. Customer orders product.
3. Vendor confirms order.
4. Rider picks up and delivers order.
5. Customer receives order.
6. Customer reviews product.
