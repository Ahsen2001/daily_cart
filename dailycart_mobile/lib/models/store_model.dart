import 'category_model.dart';
import 'product_model.dart';
import 'promotion_model.dart';

class StoreModel {
  const StoreModel({
    required this.id,
    required this.slug,
    required this.name,
    required this.description,
    required this.logo,
    required this.coverImage,
    required this.deliveryEstimate,
    required this.minimumOrder,
    required this.isFeatured,
    required this.followersCount,
    this.rating = 0,
    this.isFollowing = false,
    this.contactPhone = '',
    this.contactEmail = '',
    this.address = '',
    this.city = '',
    this.district = '',
    this.banners = const [],
    this.categories = const [],
    this.products = const [],
    this.offers = const [],
    this.reviews = const [],
  });

  final int id;
  final String slug;
  final String name;
  final String description;
  final String logo;
  final String coverImage;
  final String deliveryEstimate;
  final double minimumOrder;
  final bool isFeatured;
  final int followersCount;
  final double rating;
  final bool isFollowing;
  final String contactPhone;
  final String contactEmail;
  final String address;
  final String city;
  final String district;
  final List<StoreBannerModel> banners;
  final List<CategoryModel> categories;
  final List<ProductModel> products;
  final List<PromotionModel> offers;
  final List<StoreReviewModel> reviews;

  factory StoreModel.fromJson(Map<String, dynamic> json) {
    return StoreModel(
      id: _int(json['id']),
      slug: (json['slug'] ?? '').toString(),
      name: (json['name'] ?? 'DailyCart Store').toString(),
      description: (json['description'] ?? '').toString(),
      logo: (json['logo'] ?? '').toString(),
      coverImage: (json['cover_image'] ?? '').toString(),
      deliveryEstimate: (json['delivery_estimate'] ?? '').toString(),
      minimumOrder: _double(json['minimum_order']),
      isFeatured: json['is_featured'] == true,
      followersCount: _int(json['followers_count']),
      rating: _double(json['rating']),
      isFollowing: json['is_following'] == true,
      contactPhone: (json['contact_phone'] ?? '').toString(),
      contactEmail: (json['contact_email'] ?? '').toString(),
      address: (json['address'] ?? '').toString(),
      city: (json['city'] ?? '').toString(),
      district: (json['district'] ?? '').toString(),
      banners: _maps(
        json['banners'],
      ).map(StoreBannerModel.fromJson).toList(growable: false),
      categories: _maps(
        json['categories'],
      ).map(CategoryModel.fromJson).toList(growable: false),
      products: _maps(json['products'])
          .map(ProductModel.fromJson)
          .where((product) => product.isVisibleForCustomer)
          .toList(growable: false),
      offers: _maps(
        json['offers'],
      ).map(PromotionModel.fromJson).toList(growable: false),
      reviews: _maps(
        json['reviews'],
      ).map(StoreReviewModel.fromJson).toList(growable: false),
    );
  }

  StoreModel copyWith({bool? isFollowing, int? followersCount}) {
    return StoreModel(
      id: id,
      slug: slug,
      name: name,
      description: description,
      logo: logo,
      coverImage: coverImage,
      deliveryEstimate: deliveryEstimate,
      minimumOrder: minimumOrder,
      isFeatured: isFeatured,
      followersCount: followersCount ?? this.followersCount,
      rating: rating,
      isFollowing: isFollowing ?? this.isFollowing,
      contactPhone: contactPhone,
      contactEmail: contactEmail,
      address: address,
      city: city,
      district: district,
      banners: banners,
      categories: categories,
      products: products,
      offers: offers,
      reviews: reviews,
    );
  }
}

class StoreBannerModel {
  const StoreBannerModel({
    required this.id,
    required this.title,
    required this.image,
  });
  final int id;
  final String title;
  final String image;

  factory StoreBannerModel.fromJson(Map<String, dynamic> json) =>
      StoreBannerModel(
        id: _int(json['id']),
        title: (json['title'] ?? '').toString(),
        image: (json['image'] ?? '').toString(),
      );
}

class StoreReviewModel {
  const StoreReviewModel({
    required this.id,
    required this.customerName,
    required this.rating,
    required this.comment,
  });
  final int id;
  final String customerName;
  final double rating;
  final String comment;

  factory StoreReviewModel.fromJson(Map<String, dynamic> json) =>
      StoreReviewModel(
        id: _int(json['id']),
        customerName: (json['customer_name'] ?? 'Customer').toString(),
        rating: _double(json['rating']),
        comment: (json['comment'] ?? '').toString(),
      );
}

List<Map<String, dynamic>> _maps(Object? value) {
  if (value is! List) return const [];
  return value
      .whereType<Map>()
      .map((item) => Map<String, dynamic>.from(item))
      .toList();
}

int _int(Object? value) =>
    value is int ? value : int.tryParse(value?.toString() ?? '') ?? 0;

double _double(Object? value) => value is num
    ? value.toDouble()
    : double.tryParse(value?.toString() ?? '') ?? 0;
