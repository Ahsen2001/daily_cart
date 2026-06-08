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
      UserRole.customer => '/customer',
      UserRole.vendor => '/vendor',
      UserRole.rider => '/rider',
    };
  }

  static UserRole fromName(String? value) {
    return UserRole.values.firstWhere(
      (role) => role.name == value,
      orElse: () => UserRole.customer,
    );
  }
}
