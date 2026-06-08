import 'package:flutter/widgets.dart';

class Responsive {
  static bool isSmall(BuildContext context) {
    return MediaQuery.sizeOf(context).width < 360;
  }

  static bool isTablet(BuildContext context) {
    return MediaQuery.sizeOf(context).width >= 700;
  }

  static double horizontalPadding(BuildContext context) {
    final width = MediaQuery.sizeOf(context).width;
    if (width >= 900) {
      return 48;
    }
    if (width >= 600) {
      return 32;
    }
    return 20;
  }

  static double maxContentWidth(BuildContext context) {
    return isTablet(context) ? 520 : double.infinity;
  }
}
