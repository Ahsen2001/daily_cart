import 'package:flutter/material.dart';

import '../constants/app_assets.dart';

class AppLogo extends StatelessWidget {
  const AppLogo({
    this.size = 96,
    this.showShadow = true,
    super.key,
  });

  final double size;
  final bool showShadow;

  @override
  Widget build(BuildContext context) {
    return Container(
      width: size,
      height: size,
      padding: EdgeInsets.all(size * 0.08),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(size * 0.25),
        boxShadow: showShadow
            ? const [
                BoxShadow(
                  color: Color(0x2415803D),
                  blurRadius: 28,
                  offset: Offset(0, 14),
                ),
              ]
            : null,
      ),
      child: Image.asset(
        AppAssets.logo,
        fit: BoxFit.contain,
      ),
    );
  }
}
