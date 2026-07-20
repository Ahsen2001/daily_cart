enum DailyCartAppFlavor {
  customer,
  vendor,
  rider;

  static DailyCartAppFlavor fromName(String value) {
    return switch (value.trim().toLowerCase()) {
      'vendor' => DailyCartAppFlavor.vendor,
      'rider' => DailyCartAppFlavor.rider,
      _ => DailyCartAppFlavor.customer,
    };
  }

  String get name => switch (this) {
        DailyCartAppFlavor.customer => 'customer',
        DailyCartAppFlavor.vendor => 'vendor',
        DailyCartAppFlavor.rider => 'rider',
      };

  String get displayName => switch (this) {
        DailyCartAppFlavor.customer => 'DailyCart Customer',
        DailyCartAppFlavor.vendor => 'DailyCart Vendor',
        DailyCartAppFlavor.rider => 'DailyCart Rider',
      };

  String get androidApplicationId => switch (this) {
        DailyCartAppFlavor.customer => 'com.dailycart.customer',
        DailyCartAppFlavor.vendor => 'com.dailycart.vendor',
        DailyCartAppFlavor.rider => 'com.dailycart.rider',
      };

  String get iosBundleId => androidApplicationId;
}

class AppIdentity {
  AppIdentity._();

  static const configuredFlavor = String.fromEnvironment(
    'DAILY_CART_FLAVOR',
    defaultValue: 'customer',
  );

  static DailyCartAppFlavor? _configuredIdentity;

  static DailyCartAppFlavor get environmentFlavor {
    return DailyCartAppFlavor.fromName(configuredFlavor);
  }

  static DailyCartAppFlavor get flavor {
    return _configuredIdentity ?? environmentFlavor;
  }

  static void configure(DailyCartAppFlavor flavor) {
    _configuredIdentity = flavor;
  }

  static String get displayName => flavor.displayName;

  static String get androidApplicationId => flavor.androidApplicationId;

  static String get iosBundleId => flavor.iosBundleId;

  static bool get isCustomer => flavor == DailyCartAppFlavor.customer;

  static bool get isVendor => flavor == DailyCartAppFlavor.vendor;

  static bool get isRider => flavor == DailyCartAppFlavor.rider;
}
