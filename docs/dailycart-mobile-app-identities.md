# DailyCart Mobile App Identities

## Final production decision

DailyCart ships three independently installable mobile apps from one shared
Flutter codebase. Admin and Super Admin remain web-only.

| Role | App name | Android application ID | iOS bundle ID | Deep-link scheme |
| --- | --- | --- | --- | --- |
| Customer | DailyCart Customer | `com.dailycart.customer` | `com.dailycart.customer` | `dailycart-customer` |
| Vendor | DailyCart Vendor | `com.dailycart.vendor` | `com.dailycart.vendor` | `dailycart-vendor` |
| Rider | DailyCart Rider | `com.dailycart.rider` | `com.dailycart.rider` | `dailycart-rider` |

The former `com.dailycart.app` Android identity is retired and must not be used
for a production store listing, production Firebase registration, universal
link, or signing profile.

## Shared native implementation identity

Android uses `com.dailycart.mobile` as the Gradle namespace and Kotlin package
for shared native code. This is intentionally separate from the three public
application IDs supplied by product flavors.

## Build contract

Every Flutter command must pass the native flavor and its matching role
entrypoint:

```text
--flavor <role> -t lib/main_<role>.dart
```

Valid role values are `customer`, `vendor`, and `rider`. The shared
`lib/main.dart` entrypoint defaults to `customer` for tests and tools that do
not provide a flavor. The iOS flavor configurations set their matching Flutter
entrypoint automatically.

## Firebase registration contract

Create six Firebase app registrations in one DailyCart Firebase project:

- Android Customer: `com.dailycart.customer`
- Android Vendor: `com.dailycart.vendor`
- Android Rider: `com.dailycart.rider`
- iOS Customer: `com.dailycart.customer`
- iOS Vendor: `com.dailycart.vendor`
- iOS Rider: `com.dailycart.rider`

Android configuration files belong in the matching flavor source directory.
iOS configuration files must be assigned to their matching Xcode flavor build
configuration. A Firebase file registered to one package or bundle ID must
never be copied to another role app.

## Store and signing contract

Each identity requires an independent Play Store listing, App Store record,
signing configuration, push entitlement, privacy declaration, deep-link
association, analytics app registration, and Crashlytics app registration.
