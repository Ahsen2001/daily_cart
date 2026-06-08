import 'package:flutter/material.dart';

import '../constants/app_strings.dart';
import 'app_logo.dart';

class CustomAppBar extends StatelessWidget implements PreferredSizeWidget {
  const CustomAppBar({
    this.title = AppStrings.appName,
    this.showLogo = true,
    this.actions,
    super.key,
  });

  final String title;
  final bool showLogo;
  final List<Widget>? actions;

  @override
  Size get preferredSize => const Size.fromHeight(kToolbarHeight);

  @override
  Widget build(BuildContext context) {
    return AppBar(
      titleSpacing: 20,
      title: Row(
        children: [
          if (showLogo) ...[
            const AppLogo(size: 34, showShadow: false),
            const SizedBox(width: 10),
          ],
          Flexible(child: Text(title)),
        ],
      ),
      actions: actions,
    );
  }
}
