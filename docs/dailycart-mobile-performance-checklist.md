# DailyCart Mobile Performance Checklist

## Implemented Package Support

The mobile app is configured to use:

- `cached_network_image` for product, profile, promotion, and shop images.
- `shimmer` for skeleton loading placeholders.
- `connectivity_plus` for offline/network messaging.
- Provider/Riverpod state containers to avoid unnecessary rebuilds.

## Required Optimizations

1. Add pagination query parameters for product, order, delivery, review, coupon, and promotion lists.
2. Use lazy loading on list screens with page size 10-20.
3. Cache images with `CachedNetworkImage`.
4. Show shimmer skeletons while list data loads.
5. Cache stable API responses such as categories and available promotions.
6. Show offline message when connectivity is unavailable.
7. Compress images before upload where possible.
8. Avoid loading full-size product images in list cards.
9. Use release builds for performance testing.
10. Test scrolling performance on low-end Android devices.

## Performance Test Commands

```powershell
cd dailycart_mobile
flutter run --profile
flutter build appbundle --release
flutter build ipa --release
```

## Acceptance Targets

1. App starts in under 3 seconds on a modern device.
2. Product list scrolls smoothly without visible jank.
3. Images load progressively and do not block the UI.
4. API errors never freeze screens.
5. Upload screens show loading state until complete.
