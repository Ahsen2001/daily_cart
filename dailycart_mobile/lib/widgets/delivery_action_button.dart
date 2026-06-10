import 'package:flutter/material.dart';

import 'custom_button.dart';

class DeliveryActionButton extends StatelessWidget {
  const DeliveryActionButton({
    required this.label,
    required this.icon,
    required this.onPressed,
    this.isSecondary = false,
    super.key,
  });

  final String label;
  final IconData icon;
  final VoidCallback? onPressed;
  final bool isSecondary;

  @override
  Widget build(BuildContext context) {
    return CustomButton(
      label: label,
      icon: icon,
      variant: isSecondary ? CustomButtonVariant.secondary : CustomButtonVariant.primary,
      onPressed: onPressed,
    );
  }
}
