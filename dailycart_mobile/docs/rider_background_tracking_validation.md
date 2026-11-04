# Rider active-delivery background validation

Run this matrix with a real active rider delivery against a non-production API.
Never use simulated points against production orders.

## Android (Android 12, 13, 14 and 15)

1. Grant precise location, **Allow all the time**, and notifications.
2. Accept a delivery and confirm notification ID `4182` displays:
   “DailyCart is sharing your location for an active delivery.”
3. Lock the screen for 10 minutes. Confirm current location and `recorded_at`
   continue to advance at the active status interval.
4. Disable network for 5 minutes while moving. Confirm the rider app displays
   “Offline: location updates waiting”. Restore network and verify queued UUIDs
   upload once, newest first, without duplicate history records.
5. Swipe the app away. Confirm the foreground notification remains and the
   backend continues receiving locations.
6. While an active tracking session exists, restart the device. Unlock once,
   then confirm the foreground service resumes and queued/current points sync.
7. Mark the delivery delivered, failed, cancelled, or reassigned. Confirm the
   foreground notification disappears immediately and no later points arrive.
8. Sign out during an active delivery and confirm the same stop behavior.
9. On Xiaomi/Redmi devices, repeat after enabling DailyCart in **Autostart** and
   setting battery use to **No restrictions**. MIUI/HyperOS may otherwise block
   third-party boot receivers even when Android permissions are correct.

Useful non-destructive checks:

```text
adb shell dumpsys activity services id.flutter.flutter_background_service
adb shell dumpsys package com.dailycart.rider
adb logcat | findstr /I "flutter_background_service Geolocator DailyCart"
```

## iOS (current and previous major iOS releases)

1. Build the Rider scheme in Xcode and verify Background Modes > Location
   updates is enabled.
2. Grant Always Location and Precise Location.
3. Repeat locked screen, offline/reconnect, and terminal-state tests above.
4. Verify the blue background-location indicator and the DailyCart usage text.
5. Test low-power mode and poor-GPS conditions.

iOS does not allow an application to launch arbitrary continuous GPS work at
device boot, and a user force-quit prevents background relaunch. DailyCart
resumes the authorized active session when the Rider app is next launched. This
is an operating-system constraint, not an application fallback opportunity.

## Acceptance evidence

Capture the order/delivery ID, device model, OS version, permission state,
timestamps before and after each transition, backend accepted/rejected counts,
and any relevant log lines. Do not include auth or tracking tokens.
