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
      approvalStatus:
          (json['approval_status'] ?? json['status'] ?? 'pending').toString(),
      shopLogo: (json['shop_logo'] ?? json['logo'] ?? '').toString(),
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
    };
  }

  static int _toInt(Object? value) {
    if (value is int) {
      return value;
    }
    return int.tryParse(value?.toString() ?? '') ?? 0;
  }
}
