import 'package:dailycart_mobile/models/category_model.dart';
import 'package:dailycart_mobile/models/product_model.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  group('mobile catalog parsing', () {
    test('parses the category envelope fields returned by Laravel', () {
      final category = CategoryModel.fromJson({
        'id': 7,
        'name': 'Fresh Food',
        'image': 'https://example.test/category.webp',
        'products_count': 11,
      });

      expect(category.id, 7);
      expect(category.image, 'https://example.test/category.webp');
      expect(category.productCount, 11);
    });

    test('keeps a missing discount nullable and parses shelf flags', () {
      final product = ProductModel.fromJson({
        'id': 19,
        'name': 'Mobile Product',
        'price': 1250,
        'discount_price': null,
        'image': 'https://example.test/product.webp',
        'stock_quantity': 4,
        'status': 'approved',
        'is_featured': true,
      });

      expect(product.discountPrice, isNull);
      expect(product.hasDiscount, isFalse);
      expect(product.isFeatured, isTrue);
      expect(product.isAvailable, isTrue);
    });
  });
}
