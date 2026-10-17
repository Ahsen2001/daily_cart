import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../providers/store_provider.dart';
import '../../routes/app_routes.dart';
import '../../widgets/customer_bottom_navigation.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/empty_state_widget.dart';
import '../../widgets/error_widget.dart';
import '../../widgets/loading_widget.dart';
import '../../widgets/store_card.dart';

class StoresScreen extends ConsumerStatefulWidget {
  const StoresScreen({this.initialFeatured = false, super.key});

  final bool initialFeatured;

  @override
  ConsumerState<StoresScreen> createState() => _StoresScreenState();
}

class _StoresScreenState extends ConsumerState<StoresScreen> {
  final _search = TextEditingController();
  late bool _featuredOnly;

  @override
  void initState() {
    super.initState();
    _featuredOnly = widget.initialFeatured;
    Future.microtask(_load);
  }

  @override
  void dispose() {
    _search.dispose();
    super.dispose();
  }

  Future<void> _load() => ref
      .read(storeProvider)
      .getStores(search: _search.text, featured: _featuredOnly);

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(storeProvider);
    return Scaffold(
      appBar: const CustomAppBar(title: 'Browse Stores'),
      body: Column(
        children: [
          Padding(
            padding: const EdgeInsets.fromLTRB(20, 12, 20, 10),
            child: TextField(
              controller: _search,
              textInputAction: TextInputAction.search,
              onSubmitted: (_) => _load(),
              decoration: InputDecoration(
                hintText: 'Search trusted local stores',
                prefixIcon: const Icon(Icons.search_rounded),
                suffixIcon: IconButton(
                  tooltip: 'Search stores',
                  onPressed: _load,
                  icon: const Icon(Icons.arrow_forward_rounded),
                ),
              ),
            ),
          ),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 20),
            child: Row(
              children: [
                FilterChip(
                  label: const Text('Featured stores'),
                  selected: _featuredOnly,
                  onSelected: (value) {
                    setState(() => _featuredOnly = value);
                    _load();
                  },
                ),
                const Spacer(),
                Text('${state.stores.length} stores'),
              ],
            ),
          ),
          const SizedBox(height: 8),
          Expanded(child: _body(state)),
        ],
      ),
      bottomNavigationBar: const CustomerBottomNavigation(currentIndex: 1),
    );
  }

  Widget _body(StoreProvider state) {
    if (state.isLoading && state.stores.isEmpty) {
      return const LoadingWidget(message: 'Loading stores...');
    }
    if (state.errorMessage != null && state.stores.isEmpty) {
      return DailyCartErrorWidget(
        title: 'Unable to load stores',
        message: state.errorMessage!,
        onRetry: _load,
      );
    }
    if (state.stores.isEmpty) {
      return const EmptyStateWidget(
        title: 'No stores found',
        message: 'Try another search or remove the featured filter.',
        icon: Icons.storefront_outlined,
      );
    }
    return RefreshIndicator(
      onRefresh: _load,
      child: ListView.separated(
        physics: const AlwaysScrollableScrollPhysics(),
        padding: const EdgeInsets.fromLTRB(20, 8, 20, 24),
        itemCount: state.stores.length,
        separatorBuilder: (_, _) => const SizedBox(height: 14),
        itemBuilder: (context, index) {
          final store = state.stores[index];
          return StoreCard(
            store: store,
            onTap: () =>
                context.push('${AppRoutes.storeDetails}/${store.slug}'),
          );
        },
      ),
    );
  }
}
