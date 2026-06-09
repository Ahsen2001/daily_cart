import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../providers/category_provider.dart';
import '../../routes/app_routes.dart';
import '../../widgets/category_card.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/empty_state_widget.dart';
import '../../widgets/loading_widget.dart';

class CategoryScreen extends ConsumerStatefulWidget {
  const CategoryScreen({super.key});

  @override
  ConsumerState<CategoryScreen> createState() => _CategoryScreenState();
}

class _CategoryScreenState extends ConsumerState<CategoryScreen> {
  @override
  void initState() {
    super.initState();
    Future.microtask(() => ref.read(categoryProvider).getCategories());
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(categoryProvider);

    return Scaffold(
      appBar: const CustomAppBar(title: 'Categories'),
      body: state.isLoading
          ? const LoadingWidget(message: 'Loading categories...')
          : state.categories.isEmpty
              ? const EmptyStateWidget(
                  title: 'No categories found',
                  message: 'Categories will appear here once available.',
                  icon: Icons.category_outlined,
                )
              : GridView.builder(
                  padding: const EdgeInsets.all(20),
                  gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                    crossAxisCount: 2,
                    mainAxisSpacing: 14,
                    crossAxisSpacing: 14,
                    childAspectRatio: 0.82,
                  ),
                  itemCount: state.categories.length,
                  itemBuilder: (context, index) {
                    final category = state.categories[index];
                    return CategoryCard(
                      category: category,
                      onTap: () => context.push(
                        '${AppRoutes.products}?categoryId=${category.id}&categoryName=${Uri.encodeComponent(category.name)}',
                      ),
                    );
                  },
                ),
    );
  }
}
