class CategoryModel {
  const CategoryModel({
    required this.id,
    required this.name,
    required this.image,
    this.productCount = 0,
  });

  final int id;
  final String name;
  final String image;
  final int productCount;

  factory CategoryModel.fromJson(Map<String, dynamic> json) {
    return CategoryModel(
      id: _toInt(json['id']),
      name: (json['name'] ?? '').toString(),
      image: (json['image'] ?? json['image_url'] ?? '').toString(),
      productCount: _toInt(
        json['product_count'] ?? json['products_count'] ?? json['count'],
      ),
    );
  }

  static int _toInt(Object? value) {
    if (value is int) {
      return value;
    }
    return int.tryParse(value?.toString() ?? '') ?? 0;
  }
}
