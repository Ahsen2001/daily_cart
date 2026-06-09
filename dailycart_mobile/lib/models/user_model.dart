import 'user_role.dart';

class UserModel {
  const UserModel({
    required this.id,
    required this.name,
    required this.email,
    required this.phone,
    required this.role,
    this.status,
    this.isApproved = true,
  });

  final int id;
  final String name;
  final String email;
  final String phone;
  final UserRole role;
  final String? status;
  final bool isApproved;

  factory UserModel.fromJson(Map<String, dynamic> json) {
    return UserModel(
      id: _toInt(json['id']),
      name: (json['name'] ?? '').toString(),
      email: (json['email'] ?? '').toString(),
      phone: (json['phone'] ?? '').toString(),
      role: UserRole.fromName(json['role']?.toString()),
      status: json['status']?.toString(),
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
      isApproved: _toBool(values['is_approved'], fallback: true),
    );
  }

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
