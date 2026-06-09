enum UserRole {
  customer,
  vendor,
  rider;

  String get label {
    return switch (this) {
      UserRole.customer => 'Customer',
      UserRole.vendor => 'Vendor',
      UserRole.rider => 'Rider',
    };
  }

  String get homeRoute {
    return switch (this) {
      UserRole.customer => '/customer-home',
      UserRole.vendor => '/vendor-dashboard',
      UserRole.rider => '/rider-dashboard',
    };
  }

  static UserRole fromName(String? value) {
    final normalized = value?.trim().toLowerCase().replaceAll('-', '_');
    return UserRole.values.firstWhere(
      (role) => role.name == normalized,
      orElse: () => UserRole.customer,
    );
  }
}
