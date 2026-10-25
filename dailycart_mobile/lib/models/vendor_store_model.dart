class VendorStoreModel {
  const VendorStoreModel({
    required this.id,
    required this.slug,
    this.description = '',
    this.openingHours = '',
    this.deliveryEstimate = '',
    this.minimumOrder = 0,
    this.contactPhone = '',
    this.contactEmail = '',
    this.logo = '',
    this.coverImage = '',
    this.status = 'pending',
    this.isEnabled = false,
    this.isFeatured = false,
    this.banners = const [],
  });

  final int id;
  final String slug;
  final String description;
  final String openingHours;
  final String deliveryEstimate;
  final double minimumOrder;
  final String contactPhone;
  final String contactEmail;
  final String logo;
  final String coverImage;
  final String status;
  final bool isEnabled;
  final bool isFeatured;
  final List<VendorStoreBannerModel> banners;

  factory VendorStoreModel.fromJson(Map<String, dynamic> json) =>
      VendorStoreModel(
        id: _int(json['id']),
        slug: (json['slug'] ?? '').toString(),
        description: (json['description'] ?? '').toString(),
        openingHours: (json['opening_hours'] ?? '').toString(),
        deliveryEstimate: (json['delivery_estimate'] ?? '').toString(),
        minimumOrder: _double(json['minimum_order']),
        contactPhone: (json['contact_phone'] ?? '').toString(),
        contactEmail: (json['contact_email'] ?? '').toString(),
        logo: (json['logo'] ?? '').toString(),
        coverImage: (json['cover_image'] ?? '').toString(),
        status: (json['profile_status'] ?? 'pending').toString(),
        isEnabled: json['is_enabled'] == true || json['is_enabled'] == 1,
        isFeatured: json['is_featured'] == true || json['is_featured'] == 1,
        banners: _maps(
          json['banners'],
        ).map(VendorStoreBannerModel.fromJson).toList(growable: false),
      );
}

class VendorStoreBannerModel {
  const VendorStoreBannerModel({
    required this.id,
    required this.image,
    this.title = '',
    this.linkUrl = '',
    this.isActive = true,
  });
  final int id;
  final String image;
  final String title;
  final String linkUrl;
  final bool isActive;

  factory VendorStoreBannerModel.fromJson(Map<String, dynamic> json) =>
      VendorStoreBannerModel(
        id: _int(json['id']),
        image: (json['image'] ?? '').toString(),
        title: (json['title'] ?? '').toString(),
        linkUrl: (json['link_url'] ?? '').toString(),
        isActive: json['is_active'] != false && json['is_active'] != 0,
      );
}

class VendorStoreAnalyticsModel {
  const VendorStoreAnalyticsModel({
    this.approvedProducts = 0,
    this.followers = 0,
    this.orders = 0,
    this.revenue = 0,
    this.averageRating = 0,
  });
  final int approvedProducts;
  final int followers;
  final int orders;
  final double revenue;
  final double averageRating;

  factory VendorStoreAnalyticsModel.fromJson(Map<String, dynamic> json) =>
      VendorStoreAnalyticsModel(
        approvedProducts: _int(json['approved_products']),
        followers: _int(json['followers']),
        orders: _int(json['orders']),
        revenue: _double(json['revenue']),
        averageRating: _double(json['average_rating']),
      );
}

int _int(Object? value) =>
    value is int ? value : int.tryParse(value?.toString() ?? '') ?? 0;
double _double(Object? value) => value is num
    ? value.toDouble()
    : double.tryParse(value?.toString() ?? '') ?? 0;
List<Map<String, dynamic>> _maps(Object? value) => value is List
    ? value
          .whereType<Map>()
          .map((row) => row.map((key, item) => MapEntry(key.toString(), item)))
          .toList()
    : const [];
