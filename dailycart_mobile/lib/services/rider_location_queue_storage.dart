import 'package:flutter_secure_storage/flutter_secure_storage.dart';

abstract interface class RiderLocationQueueStorage {
  Future<String?> read(String key);

  Future<void> write(String key, String value);

  Future<void> delete(String key);
}

/// Device-bound encrypted storage for delivery coordinates and sequence data.
///
/// Android uses AES-GCM with a key protected by Android Keystore. On iOS the
/// Keychain item becomes available after the first unlock so an already active
/// delivery can continue while the screen is locked. Queue data is never
/// synchronized through iCloud and cannot migrate to a different device.
class SecureRiderLocationQueueStorage implements RiderLocationQueueStorage {
  SecureRiderLocationQueueStorage({FlutterSecureStorage? storage})
    : _storage =
          storage ??
          const FlutterSecureStorage(
            aOptions: AndroidOptions(
              storageNamespace: 'dailycart_rider_location_outbox',
              resetOnError: false,
              migrateOnAlgorithmChange: true,
              migrateWithBackup: true,
            ),
            iOptions: IOSOptions(
              accountName: 'dailycart_rider_location_outbox',
              accessibility: KeychainAccessibility.first_unlock_this_device,
              synchronizable: false,
            ),
          );

  final FlutterSecureStorage _storage;

  @override
  Future<String?> read(String key) => _storage.read(key: key);

  @override
  Future<void> write(String key, String value) =>
      _storage.write(key: key, value: value);

  @override
  Future<void> delete(String key) => _storage.delete(key: key);
}
