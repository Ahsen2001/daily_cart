class RiderProfileModel {
  const RiderProfileModel({
    required this.id,
    required this.name,
    required this.email,
    required this.phone,
    this.vehicleType = '',
    this.vehicleNumber = '',
    this.licenseNumber = '',
    this.approvalStatus = 'pending',
    this.profilePhoto = '',
    this.availabilityStatus = 'unavailable',
    this.address = '',
    this.city = '',
    this.district = '',
    this.province = '',
    this.latitude,
    this.longitude,
  });

  final int id;
  final String name;
  final String email;
  final String phone;
  final String vehicleType;
  final String vehicleNumber;
  final String licenseNumber;
  final String approvalStatus;
  final String profilePhoto;
  final String availabilityStatus;
  final String address;
  final String city;
  final String district;
  final String province;
  final double? latitude;
  final double? longitude;

  bool get isApproved =>
      const {'approved', 'verified'}.contains(approvalStatus.toLowerCase());

  factory RiderProfileModel.fromJson(Map<String, dynamic> json) {
    return RiderProfileModel(
      id: _toInt(json['id']),
      name: (json['name'] ?? '').toString(),
      email: (json['email'] ?? '').toString(),
      phone: (json['phone'] ?? '').toString(),
      vehicleType: (json['vehicle_type'] ?? '').toString(),
      vehicleNumber: (json['vehicle_number'] ?? '').toString(),
      licenseNumber: (json['license_number'] ?? '').toString(),
      approvalStatus: (json['approval_status'] ?? json['status'] ?? 'pending')
          .toString(),
      profilePhoto: (json['profile_photo'] ?? json['avatar'] ?? '').toString(),
      availabilityStatus: (json['availability_status'] ?? 'unavailable')
          .toString(),
      address: (json['address'] ?? '').toString(),
      city: (json['city'] ?? '').toString(),
      district: (json['district'] ?? '').toString(),
      province: (json['province'] ?? '').toString(),
      latitude: _toNullableDouble(json['latitude']),
      longitude: _toNullableDouble(json['longitude']),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'name': name,
      'email': email,
      'phone': phone,
      'vehicle_type': vehicleType,
      'vehicle_number': vehicleNumber,
      'license_number': licenseNumber,
      'address': address,
      'city': city,
      'district': district,
      'province': province,
      'latitude': latitude,
      'longitude': longitude,
    };
  }

  static int _toInt(Object? value) {
    if (value is int) return value;
    return int.tryParse(value?.toString() ?? '') ?? 0;
  }

  static double? _toNullableDouble(Object? value) {
    if (value == null || value.toString().isEmpty) return null;
    if (value is num) return value.toDouble();
    return double.tryParse(value.toString());
  }
}
