class AddressModel {
  const AddressModel({
    required this.id,
    required this.fullName,
    required this.phoneNumber,
    required this.addressLine1,
    this.addressLine2 = '',
    required this.city,
    required this.district,
    this.postalCode = '',
    this.landmark = '',
    this.latitude,
    this.longitude,
    this.isDefault = false,
  });

  final int id;
  final String fullName;
  final String phoneNumber;
  final String addressLine1;
  final String addressLine2;
  final String city;
  final String district;
  final String postalCode;
  final String landmark;
  final double? latitude;
  final double? longitude;
  final bool isDefault;

  factory AddressModel.fromJson(Map<String, dynamic> json) {
    return AddressModel(
      id: _toInt(json['id']),
      fullName:
          (json['recipient_name'] ?? json['full_name'] ?? json['name'] ?? '')
              .toString(),
      phoneNumber: (json['phone'] ?? json['phone_number'] ?? '').toString(),
      addressLine1: (json['address_line_1'] ?? json['address_line1'] ?? '')
          .toString(),
      addressLine2: (json['address_line_2'] ?? json['address_line2'] ?? '')
          .toString(),
      city: (json['city'] ?? '').toString(),
      district: (json['district'] ?? '').toString(),
      postalCode: (json['postal_code'] ?? '').toString(),
      landmark: (json['landmark'] ?? '').toString(),
      latitude: _toNullableDouble(json['latitude']),
      longitude: _toNullableDouble(json['longitude']),
      isDefault: _toBool(json['is_default']),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'recipient_name': fullName,
      'phone': phoneNumber,
      'address_line_1': addressLine1,
      'address_line_2': addressLine2,
      'city': city,
      'district': district,
      'postal_code': postalCode,
      'landmark': landmark,
      'latitude': latitude,
      'longitude': longitude,
      'formatted_address': displayAddress,
      'is_default': isDefault,
    };
  }

  String get displayAddress {
    final parts = [
      addressLine1,
      if (addressLine2.isNotEmpty) addressLine2,
      city,
      district,
      if (postalCode.isNotEmpty) postalCode,
    ];
    return parts.where((part) => part.trim().isNotEmpty).join(', ');
  }

  AddressModel copyWith({
    int? id,
    String? fullName,
    String? phoneNumber,
    String? addressLine1,
    String? addressLine2,
    String? city,
    String? district,
    String? postalCode,
    String? landmark,
    double? latitude,
    double? longitude,
    bool? isDefault,
  }) {
    return AddressModel(
      id: id ?? this.id,
      fullName: fullName ?? this.fullName,
      phoneNumber: phoneNumber ?? this.phoneNumber,
      addressLine1: addressLine1 ?? this.addressLine1,
      addressLine2: addressLine2 ?? this.addressLine2,
      city: city ?? this.city,
      district: district ?? this.district,
      postalCode: postalCode ?? this.postalCode,
      landmark: landmark ?? this.landmark,
      latitude: latitude ?? this.latitude,
      longitude: longitude ?? this.longitude,
      isDefault: isDefault ?? this.isDefault,
    );
  }

  static int _toInt(Object? value) {
    if (value is int) {
      return value;
    }
    return int.tryParse(value?.toString() ?? '') ?? 0;
  }

  static double? _toNullableDouble(Object? value) {
    if (value == null || value.toString().isEmpty) {
      return null;
    }
    if (value is num) {
      return value.toDouble();
    }
    return double.tryParse(value.toString());
  }

  static bool _toBool(Object? value) {
    if (value is bool) {
      return value;
    }
    final normalized = value?.toString().toLowerCase();
    return normalized == '1' || normalized == 'true' || normalized == 'yes';
  }
}
