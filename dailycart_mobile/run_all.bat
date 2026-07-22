@echo off
echo ===================================================
echo Launching all 3 DailyCart apps concurrently...
echo ===================================================

echo [1/3] Starting Customer App on Android Emulator (emulator-5554)...
start "DailyCart Customer" cmd /k "flutter run --flavor customer -t lib/main_customer.dart -d emulator-5554"

echo [2/3] Starting Vendor App on Edge Web...
start "DailyCart Vendor" cmd /k "flutter run --flavor vendor -t lib/main_vendor.dart -d edge"

echo [3/3] Starting Rider App on Chrome Web...
start "DailyCart Rider" cmd /k "flutter run --flavor rider -t lib/main_rider.dart -d chrome"

echo ===================================================
echo Launch complete! 3 terminal windows have opened.
echo ===================================================
