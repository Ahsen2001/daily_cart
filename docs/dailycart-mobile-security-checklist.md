# DailyCart Mobile Security Checklist

## Authentication And Authorization

1. Store Sanctum token only in `flutter_secure_storage`.
2. Do not store passwords, OTPs, card data, or PayHere secrets on device.
3. Confirm all authenticated API calls send `Authorization: Bearer TOKEN`.
4. Confirm customer, vendor, and rider endpoints are server-side scoped.
5. Confirm protected screens redirect to login when token is missing.
6. Confirm logout clears token, user id, role, name, and email.

## API Security

1. Use HTTPS only: `https://dailycart.lk/api`.
2. Reject non-HTTPS production API URLs.
3. Validate all form fields on mobile and Laravel backend.
4. Validate image type, size, and dimensions on Laravel backend.
5. Confirm vendor can manage only own products.
6. Confirm vendor can view only own orders and earnings.
7. Confirm rider can update only assigned deliveries.
8. Confirm customer can review only delivered orders.

## Firebase Security

1. Store FCM token against authenticated user only.
2. Refresh FCM token on login and token rotation.
3. Do not include private data in notification payloads.
4. Use Firebase project restrictions and correct Android/iOS app identifiers.

## Release Security

1. Keep `key.properties` out of Git.
2. Keep Android keystore backed up in a private secure location.
3. Keep Apple certificates and provisioning profiles private.
4. Do not commit `.env` production secrets.
5. Enable Play App Signing and App Store signing protections.
