import 'package:flutter/material.dart';

class DailyCartButton extends StatelessWidget {
  const DailyCartButton({
    required this.label,
    required this.onPressed,
    this.icon,
    super.key,
  });

  final String label;
  final IconData? icon;
  final VoidCallback? onPressed;

  @override
  Widget build(BuildContext context) {
    final child = icon == null
        ? Text(label)
        : Row(
            mainAxisAlignment: MainAxisAlignment.center,
            mainAxisSize: MainAxisSize.min,
            children: [
              Icon(icon, size: 20),
              const SizedBox(width: 10),
              Text(label),
            ],
          );

    return ElevatedButton(
      onPressed: onPressed,
      child: child,
    );
  }
}
