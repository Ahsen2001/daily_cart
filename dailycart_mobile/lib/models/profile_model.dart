class ProfileModel {
  const ProfileModel({
    required this.id,
    required this.name,
    required this.email,
    required this.phone,
    this.profilePhoto = '',
    this.joinedDate,
  });

  final int id;
  final String name;
  final String email;
  final String phone;
  final String profilePhoto;
  final DateTime? joinedDate;

  factory ProfileModel.fromJson(Map<String, dynamic> json) {
    final user = json['user'] is Map<String, dynamic>
        ? json['user'] as Map<String, dynamic>
        : json;
    return ProfileModel(
      id: _toInt(user['id']),
      name: (user['name'] ?? '').toString(),
      email: (user['email'] ?? '').toString(),
      phone: (user['phone'] ?? '').toString(),
      profilePhoto:
          (user['profile_photo'] ?? user['profile_photo_url'] ?? '').toString(),
      joinedDate: DateTime.tryParse((user['created_at'] ?? '').toString()),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'name': name,
      'email': email,
      'phone': phone,
    };
  }

  ProfileModel copyWith({
    String? name,
    String? email,
    String? phone,
    String? profilePhoto,
  }) {
    return ProfileModel(
      id: id,
      name: name ?? this.name,
      email: email ?? this.email,
      phone: phone ?? this.phone,
      profilePhoto: profilePhoto ?? this.profilePhoto,
      joinedDate: joinedDate,
    );
  }

  static int _toInt(Object? value) {
    if (value is int) {
      return value;
    }
    return int.tryParse(value?.toString() ?? '') ?? 0;
  }
}
