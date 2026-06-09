import 'package:flutter/material.dart';

import '../theme/app_colors.dart';

class SearchBarWidget extends StatelessWidget {
  const SearchBarWidget({
    required this.hintText,
    this.controller,
    this.onTap,
    this.onSubmitted,
    this.readOnly = false,
    super.key,
  });

  final String hintText;
  final TextEditingController? controller;
  final VoidCallback? onTap;
  final ValueChanged<String>? onSubmitted;
  final bool readOnly;

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color: AppColors.white,
        borderRadius: BorderRadius.circular(18),
        boxShadow: const [
          BoxShadow(
            color: AppColors.shadow,
            blurRadius: 22,
            offset: Offset(0, 12),
          ),
        ],
      ),
      child: TextField(
        controller: controller,
        readOnly: readOnly,
        onTap: onTap,
        onSubmitted: onSubmitted,
        textInputAction: TextInputAction.search,
        decoration: InputDecoration(
          hintText: hintText,
          prefixIcon: const Icon(Icons.search_rounded),
          suffixIcon: IconButton(
            tooltip: 'Voice search',
            onPressed: () {},
            icon: const Icon(Icons.mic_none_rounded),
          ),
          border: InputBorder.none,
          enabledBorder: InputBorder.none,
          focusedBorder: InputBorder.none,
        ),
      ),
    );
  }
}
