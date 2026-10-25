import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:image_picker/image_picker.dart';

import '../../providers/vendor_store_provider.dart';
import '../../utils/currency_formatter.dart';
import '../../widgets/app_drawer.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/custom_button.dart';
import '../../widgets/custom_text_field.dart';
import '../../widgets/dailycart_card.dart';
import '../../widgets/error_widget.dart';
import '../../widgets/loading_widget.dart';
import '../../widgets/vendor_bottom_navigation.dart';

class VendorStoreScreen extends ConsumerStatefulWidget {
  const VendorStoreScreen({super.key});

  @override
  ConsumerState<VendorStoreScreen> createState() => _VendorStoreScreenState();
}

class _VendorStoreScreenState extends ConsumerState<VendorStoreScreen> {
  final _formKey = GlobalKey<FormState>();
  final _slug = TextEditingController();
  final _description = TextEditingController();
  final _hours = TextEditingController();
  final _estimate = TextEditingController();
  final _minimum = TextEditingController();
  final _phone = TextEditingController();
  final _email = TextEditingController();
  bool _filled = false;
  String? _logoPath;
  String? _coverPath;

  @override
  void initState() {
    super.initState();
    Future.microtask(() => ref.read(vendorStoreProvider).load());
  }

  @override
  void dispose() {
    for (final controller in [
      _slug,
      _description,
      _hours,
      _estimate,
      _minimum,
      _phone,
      _email,
    ]) {
      controller.dispose();
    }
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(vendorStoreProvider);
    final store = state.store;
    if (store != null && !_filled) {
      _filled = true;
      _slug.text = store.slug;
      _description.text = store.description;
      _hours.text = store.openingHours;
      _estimate.text = store.deliveryEstimate;
      _minimum.text = store.minimumOrder.toStringAsFixed(2);
      _phone.text = store.contactPhone;
      _email.text = store.contactEmail;
    }

    return Scaffold(
      appBar: const CustomAppBar(title: 'Store Settings'),
      drawer: const AppDrawer(roleName: 'Vendor'),
      body: state.isLoading && store == null
          ? const LoadingWidget(message: 'Loading store settings...')
          : store == null
          ? DailyCartErrorWidget(
              title: 'Store unavailable',
              message: state.errorMessage ?? 'Store settings were not found.',
              onRetry: () => ref.read(vendorStoreProvider).load(),
            )
          : RefreshIndicator(
              onRefresh: () => ref.read(vendorStoreProvider).load(),
              child: ListView(
                physics: const AlwaysScrollableScrollPhysics(),
                padding: const EdgeInsets.all(20),
                children: [
                  if (store.coverImage.isNotEmpty)
                    ClipRRect(
                      borderRadius: BorderRadius.circular(20),
                      child: CachedNetworkImage(
                        imageUrl: store.coverImage,
                        height: 170,
                        width: double.infinity,
                        fit: BoxFit.cover,
                      ),
                    ),
                  const SizedBox(height: 14),
                  _analytics(state),
                  const SizedBox(height: 14),
                  DailyCartCard(
                    child: Form(
                      key: _formKey,
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            'Public store information',
                            style: Theme.of(context).textTheme.titleMedium
                                ?.copyWith(fontWeight: FontWeight.w900),
                          ),
                          const SizedBox(height: 14),
                          CustomTextField(
                            label: 'Store URL slug',
                            controller: _slug,
                            icon: Icons.link_outlined,
                            validator: _required,
                          ),
                          const SizedBox(height: 12),
                          CustomTextField(
                            label: 'Description',
                            controller: _description,
                            icon: Icons.description_outlined,
                            maxLines: 4,
                          ),
                          const SizedBox(height: 12),
                          CustomTextField(
                            label: 'Opening hours',
                            controller: _hours,
                            icon: Icons.schedule_outlined,
                            hintText: 'Mon-Sun, 8:00 AM - 9:00 PM',
                          ),
                          const SizedBox(height: 12),
                          CustomTextField(
                            label: 'Delivery estimate',
                            controller: _estimate,
                            icon: Icons.delivery_dining_outlined,
                          ),
                          const SizedBox(height: 12),
                          CustomTextField(
                            label: 'Minimum order',
                            controller: _minimum,
                            icon: Icons.payments_outlined,
                            keyboardType: const TextInputType.numberWithOptions(
                              decimal: true,
                            ),
                          ),
                          const SizedBox(height: 12),
                          CustomTextField(
                            label: 'Public contact phone',
                            controller: _phone,
                            icon: Icons.phone_outlined,
                          ),
                          const SizedBox(height: 12),
                          CustomTextField(
                            label: 'Public contact email',
                            controller: _email,
                            icon: Icons.email_outlined,
                            keyboardType: TextInputType.emailAddress,
                          ),
                          const SizedBox(height: 14),
                          Row(
                            children: [
                              Expanded(
                                child: OutlinedButton.icon(
                                  onPressed: () => _pickStoreImage(true),
                                  icon: const Icon(Icons.image_outlined),
                                  label: Text(
                                    _logoPath == null
                                        ? 'Choose logo'
                                        : 'Logo selected',
                                  ),
                                ),
                              ),
                              const SizedBox(width: 10),
                              Expanded(
                                child: OutlinedButton.icon(
                                  onPressed: () => _pickStoreImage(false),
                                  icon: const Icon(Icons.panorama_outlined),
                                  label: Text(
                                    _coverPath == null
                                        ? 'Choose cover'
                                        : 'Cover selected',
                                  ),
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 14),
                          CustomButton(
                            label: 'Save Store Settings',
                            isLoading: state.isLoading,
                            onPressed: _save,
                          ),
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(height: 20),
                  Row(
                    children: [
                      Expanded(
                        child: Text(
                          'Store banners',
                          style: Theme.of(context).textTheme.titleMedium
                              ?.copyWith(fontWeight: FontWeight.w900),
                        ),
                      ),
                      FilledButton.icon(
                        onPressed: _addBanner,
                        icon: const Icon(Icons.add_photo_alternate_outlined),
                        label: const Text('Add'),
                      ),
                    ],
                  ),
                  const SizedBox(height: 8),
                  if (store.banners.isEmpty)
                    const DailyCartCard(
                      child: Text('No store banners have been uploaded.'),
                    ),
                  for (final banner in store.banners)
                    Padding(
                      padding: const EdgeInsets.only(bottom: 12),
                      child: DailyCartCard(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            ClipRRect(
                              borderRadius: BorderRadius.circular(14),
                              child: CachedNetworkImage(
                                imageUrl: banner.image,
                                height: 140,
                                width: double.infinity,
                                fit: BoxFit.cover,
                              ),
                            ),
                            ListTile(
                              contentPadding: EdgeInsets.zero,
                              title: Text(
                                banner.title.isEmpty
                                    ? 'Store banner'
                                    : banner.title,
                              ),
                              subtitle: banner.linkUrl.isEmpty
                                  ? null
                                  : Text(banner.linkUrl),
                              trailing: IconButton(
                                tooltip: 'Delete banner',
                                onPressed: () => _deleteBanner(banner.id),
                                icon: const Icon(Icons.delete_outline),
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                ],
              ),
            ),
      bottomNavigationBar: const VendorBottomNavigation(currentIndex: 4),
    );
  }

  Widget _analytics(VendorStoreProvider state) {
    final value = state.analytics;
    final metrics = [
      (
        'Products',
        '${value?.approvedProducts ?? 0}',
        Icons.inventory_2_outlined,
      ),
      ('Followers', '${value?.followers ?? 0}', Icons.people_outline),
      ('Orders', '${value?.orders ?? 0}', Icons.receipt_long_outlined),
      (
        'Rating',
        (value?.averageRating ?? 0).toStringAsFixed(1),
        Icons.star_outline,
      ),
      (
        'Revenue',
        CurrencyFormatter.lkr(value?.revenue ?? 0),
        Icons.trending_up,
      ),
    ];
    return SizedBox(
      height: 104,
      child: ListView.separated(
        scrollDirection: Axis.horizontal,
        itemCount: metrics.length,
        separatorBuilder: (_, _) => const SizedBox(width: 10),
        itemBuilder: (_, index) => SizedBox(
          width: 125,
          child: DailyCartCard(
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Icon(metrics[index].$3),
                const SizedBox(height: 4),
                Text(
                  metrics[index].$2,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: const TextStyle(fontWeight: FontWeight.w900),
                ),
                Text(metrics[index].$1),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Future<void> _pickStoreImage(bool logo) async {
    final image = await ImagePicker().pickImage(
      source: ImageSource.gallery,
      imageQuality: 85,
      maxWidth: 1800,
    );
    if (image == null) return;
    setState(() => logo ? _logoPath = image.path : _coverPath = image.path);
  }

  Future<void> _save() async {
    if (!_formKey.currentState!.validate()) return;
    final ok = await ref
        .read(vendorStoreProvider)
        .save(
          {
            'slug': _slug.text.trim(),
            'description': _description.text.trim(),
            'opening_hours': _hours.text.trim(),
            'delivery_estimate': _estimate.text.trim(),
            'minimum_order': double.tryParse(_minimum.text) ?? 0,
            'contact_phone': _phone.text.trim(),
            'contact_email': _email.text.trim(),
          },
          logoPath: _logoPath,
          coverPath: _coverPath,
        );
    if (!mounted) return;
    if (ok) {
      setState(() {
        _logoPath = null;
        _coverPath = null;
        _filled = false;
      });
    }
    _message(
      ok
          ? 'Store settings updated.'
          : ref.read(vendorStoreProvider).errorMessage ??
                'Unable to update store settings.',
    );
  }

  Future<void> _addBanner() async {
    final image = await ImagePicker().pickImage(
      source: ImageSource.gallery,
      imageQuality: 88,
      maxWidth: 2200,
    );
    if (image == null || !mounted) return;
    final title = TextEditingController();
    final link = TextEditingController();
    final submit = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Add store banner'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            TextField(
              controller: title,
              decoration: const InputDecoration(labelText: 'Title (optional)'),
            ),
            const SizedBox(height: 10),
            TextField(
              controller: link,
              decoration: const InputDecoration(
                labelText: 'Link URL (optional)',
              ),
              keyboardType: TextInputType.url,
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Cancel'),
          ),
          FilledButton(
            onPressed: () => Navigator.pop(context, true),
            child: const Text('Upload'),
          ),
        ],
      ),
    );
    final titleValue = title.text.trim();
    final linkValue = link.text.trim();
    title.dispose();
    link.dispose();
    if (submit != true) return;
    final ok = await ref
        .read(vendorStoreProvider)
        .addBanner(image.path, title: titleValue, linkUrl: linkValue);
    if (mounted) {
      _message(
        ok
            ? 'Store banner uploaded.'
            : ref.read(vendorStoreProvider).errorMessage ??
                  'Unable to upload banner.',
      );
    }
  }

  Future<void> _deleteBanner(int id) async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Remove banner?'),
        content: const Text('This removes the banner from your public store.'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Cancel'),
          ),
          FilledButton(
            onPressed: () => Navigator.pop(context, true),
            child: const Text('Remove'),
          ),
        ],
      ),
    );
    if (confirmed != true) return;
    final ok = await ref.read(vendorStoreProvider).deleteBanner(id);
    if (mounted) {
      _message(
        ok
            ? 'Store banner removed.'
            : ref.read(vendorStoreProvider).errorMessage ??
                  'Unable to remove banner.',
      );
    }
  }

  void _message(String value) => ScaffoldMessenger.of(
    context,
  ).showSnackBar(SnackBar(content: Text(value)));
  static String? _required(String? value) =>
      value == null || value.trim().isEmpty ? 'This field is required.' : null;
}
