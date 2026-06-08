import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

import '../../../shared/widgets/price_text.dart';
import '../../../shared/widgets/soft_panel.dart';

class ProductsScreen extends StatelessWidget {
  const ProductsScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final products = [
      ('1', 'Fresh Carrots', '1 kg pack', 650),
      ('2', 'Green Apples', 'Imported, 500 g', 890),
      ('3', 'Wholemeal Bread', 'Fresh bakery loaf', 520),
      ('4', 'Fresh Milk', '1 liter bottle', 430),
    ];

    return Scaffold(
      appBar: AppBar(title: const Text('Products')),
      body: ListView.separated(
        padding: const EdgeInsets.all(20),
        itemBuilder: (context, index) {
          final product = products[index];
          return InkWell(
            borderRadius: BorderRadius.circular(22),
            onTap: () => context.push('/products/${product.$1}'),
            child: SoftPanel(
              child: Row(
                children: [
                  const Icon(Icons.shopping_basket_outlined),
                  const SizedBox(width: 14),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          product.$2,
                          style: Theme.of(context).textTheme.titleMedium,
                        ),
                        Text(product.$3),
                      ],
                    ),
                  ),
                  PriceText(product.$4),
                ],
              ),
            ),
          );
        },
        separatorBuilder: (context, index) => const SizedBox(height: 12),
        itemCount: products.length,
      ),
    );
  }
}
