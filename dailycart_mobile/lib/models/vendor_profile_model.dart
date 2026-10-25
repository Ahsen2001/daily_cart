class VendorProfileModel {
  const VendorProfileModel({
    required this.id,
    required this.shopName,
    required this.ownerName,
    required this.email,
    required this.phone,
    this.address = '',
    this.businessRegistrationNumber = '',
    this.approvalStatus = 'pending',
    this.shopLogo = '',
    this.city = '',
    this.district = '',
    this.province = '',
    this.latitude,
    this.longitude,
    this.commissionRate = 0,
  });

  final int id;
  final String shopName;
  final String ownerName;
  final String email;
  final String phone;
  final String address;
  final String businessRegistrationNumber;
  final String approvalStatus;
  final String shopLogo;
  final String city;
  final String district;
  final String province;
  final double? latitude;
  final double? longitude;
  final double commissionRate;

  bool get isApproved => approvalStatus.toLowerCase() == 'approved';

  factory VendorProfileModel.fromJson(Map<String, dynamic> json) {
    return VendorProfileModel(
      id: _toInt(json['id']),
      shopName: (json['shop_name'] ?? json['store_name'] ?? '').toString(),
      ownerName: (json['owner_name'] ?? json['name'] ?? '').toString(),
      email: (json['email'] ?? '').toString(),
      phone: (json['phone'] ?? '').toString(),
      address: (json['address'] ?? '').toString(),
      businessRegistrationNumber:
          (json['business_registration_number'] ?? json['brn'] ?? '')
              .toString(),
      approvalStatus: (json['approval_status'] ?? json['status'] ?? 'pending')
          .toString(),
      shopLogo: (json['shop_logo'] ?? json['logo'] ?? '').toString(),
      city: (json['city'] ?? '').toString(),
      district: (json['district'] ?? '').toString(),
      province: (json['province'] ?? '').toString(),
      latitude: _toNullableDouble(json['latitude']),
      longitude: _toNullableDouble(json['longitude']),
      commissionRate: _toDouble(json['commission_rate']),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'shop_name': shopName,
      'owner_name': ownerName,
      'email': email,
      'phone': phone,
      'address': address,
      'business_registration_number': businessRegistrationNumber,
      'city': city,
      'district': district,
      'province': province,
      'latitude': latitude,
      'longitude': longitude,
    };
  }

  static int _toInt(Object? value) {
    if (value is int) {
      return value;
    }
    return int.tryParse(value?.toString() ?? '') ?? 0;
  }

  static double _toDouble(Object? value) => value is num
      ? value.toDouble()
      : double.tryParse(value?.toString() ?? '') ?? 0;

  static double? _toNullableDouble(Object? value) {
    if (value == null || value.toString().isEmpty) return null;
    return _toDouble(value);
  }
}
