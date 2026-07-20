import 'user_role.dart';

class UserModel {
  const UserModel({
    required this.id,
    required this.name,
    required this.email,
    required this.phone,
    required this.role,
    this.status,
    this.approvalStatus,
    this.isEmailVerified = false,
    this.isPhoneVerified = false,
    this.isApproved = true,
  });

  final int id;
  final String name;
  final String email;
  final String phone;
  final UserRole role;
  final String? status;
  final String? approvalStatus;
  final bool isEmailVerified;
  final bool isPhoneVerified;
  final bool isApproved;

  factory UserModel.fromJson(Map<String, dynamic> json) {
    return UserModel(
      id: _toInt(json['id']),
      name: (json['name'] ?? '').toString(),
      email: (json['email'] ?? '').toString(),
      phone: (json['phone'] ?? '').toString(),
      role: UserRole.fromName(json['role']?.toString()),
      status: json['status']?.toString(),
      approvalStatus: json['approval_status']?.toString(),
      isEmailVerified: _toBool(
        json['is_email_verified'] ?? (json['email_verified_at'] != null),
        fallback: false,
      ),
      isPhoneVerified: _toBool(
        json['is_phone_verified'] ?? (json['phone_verified_at'] != null),
        fallback: false,
      ),
      isApproved: _toBool(json['is_approved'] ?? json['approved'], fallback: true),
    );
  }

  Map<String, String> toStorageMap() {
    return {
      'id': id.toString(),
      'name': name,
      'email': email,
      'phone': phone,
      'role': role.name,
      'status': status ?? '',
      'approval_status': approvalStatus ?? '',
      'is_email_verified': isEmailVerified.toString(),
      'is_phone_verified': isPhoneVerified.toString(),
      'is_approved': isApproved.toString(),
    };
  }

  bool get isPendingApproval {
    final normalizedStatus = status?.toLowerCase().trim();
    final pendingStatus = normalizedStatus == 'pending' ||
        normalizedStatus == 'pending_approval' ||
        normalizedStatus == 'awaiting_approval';

    return (role == UserRole.vendor || role == UserRole.rider) &&
        (!isApproved || pendingStatus);
  }

  static UserModel? fromStorageMap(Map<String, String?> values) {
    final id = values['id'];
    final name = values['name'];
    final email = values['email'];
    final role = values['role'];

    if (id == null || name == null || email == null || role == null) {
      return null;
    }

    return UserModel(
      id: _toInt(id),
      name: name,
      email: email,
      phone: values['phone'] ?? '',
      role: UserRole.fromName(role),
      status: values['status']?.isEmpty ?? true ? null : values['status'],
      approvalStatus: values['approval_status']?.isEmpty ?? true
          ? null
          : values['approval_status'],
      isEmailVerified: _toBool(
        values['is_email_verified'],
        fallback: false,
      ),
      isPhoneVerified: _toBool(
        values['is_phone_verified'],
        fallback: false,
      ),
      isApproved: _toBool(values['is_approved'], fallback: true),
    );
  }

  bool get requiresVerification => !isEmailVerified || !isPhoneVerified;

  static int _toInt(Object? value) {
    if (value is int) {
      return value;
    }
    return int.tryParse(value?.toString() ?? '') ?? 0;
  }

  static bool _toBool(Object? value, {required bool fallback}) {
    if (value is bool) {
      return value;
    }

    final normalized = value?.toString().toLowerCase();
    if (normalized == '1' || normalized == 'true' || normalized == 'yes') {
      return true;
    }
    if (normalized == '0' || normalized == 'false' || normalized == 'no') {
      return false;
    }

    return fallback;
  }
}
