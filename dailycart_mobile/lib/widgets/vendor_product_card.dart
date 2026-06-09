import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';

import '../models/vendor_product_model.dart';
import '../theme/app_colors.dart';
import '../utils/currency_formatter.dart';
import 'dailycart_card.dart';
import 'stock_status_badge.dart';

class VendorProductCard extends StatelessWidget {
  const VendorProductCard({
    required this.product,
    required this.onTap,
    this.onEdit,
    this.onDelete,
    super.key,
  });

  final VendorProductModel product;
  final VoidCallback onTap;
  final VoidCallback? onEdit;
  final VoidCallback? onDelete;

  @override
  Widget build(BuildContext context) {
    return InkWell(
      borderRadius: BorderRadius.circular(22),
      onTap: onTap,
      child: DailyCartCard(
        child: Row(
          children: [
            ClipRRect(
              borderRadius: BorderRadius.circular(16),
              child: product.image.isEmpty
                  ? Container(
                      width: 76,
                      height: 76,
                      color: AppColors.lightBackground,
                      child: const Icon(Icons.inventory_2_outlined),
                    )
                  : CachedNetworkImage(
                      imageUrl: product.image,
                      width: 76,
                      height: 76,
                      fit: BoxFit.cover,
                    ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    product.name,
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(fontWeight: FontWeight.w900),
                  ),
                  Text(product.categoryName.isEmpty ? '-' : product.categoryName),
                  Text(CurrencyFormatter.lkr(product.price)),
                  const SizedBox(height: 6),
                  StockStatusBadge(
                    status: product.status,
                    stockQuantity: product.stockQuantity,
                  ),
                ],
              ),
            ),
            PopupMenuButton<String>(
              onSelected: (value) {
                if (value == 'edit') {
                  onEdit?.call();
                }
                if (value == 'delete') {
                  onDelete?.call();
                }
              },
              itemBuilder: (context) => const [
                PopupMenuItem(value: 'edit', child: Text('Edit')),
                PopupMenuItem(value: 'delete', child: Text('Delete')),
              ],
            ),
          ],
        ),
      ),
    );
  }
}
