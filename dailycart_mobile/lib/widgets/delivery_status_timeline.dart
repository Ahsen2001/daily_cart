import 'package:flutter/material.dart';

import '../theme/app_colors.dart';

class DeliveryStatusTimeline extends StatelessWidget {
  const DeliveryStatusTimeline({
    required this.currentStatus,
    super.key,
  });

  final String currentStatus;

  static const _steps = [
    ('assigned', 'Assigned'),
    ('picked_up', 'Picked Up'),
    ('on_the_way', 'On the Way'),
    ('delivered', 'Delivered'),
  ];

  @override
  Widget build(BuildContext context) {
    final currentIndex = _steps.indexWhere((step) => step.$1 == currentStatus);
    final safeIndex = currentIndex < 0 ? 0 : currentIndex;

    return Column(
      children: [
        for (var index = 0; index < _steps.length; index++)
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Column(
                children: [
                  AnimatedContainer(
                    duration: const Duration(milliseconds: 220),
                    width: 28,
                    height: 28,
                    decoration: BoxDecoration(
                      color: index <= safeIndex
                          ? AppColors.primaryGreen
                          : AppColors.border,
                      shape: BoxShape.circle,
                    ),
                    child: Icon(
                      index <= safeIndex
                          ? Icons.check_rounded
                          : Icons.circle_outlined,
                      color: index <= safeIndex
                          ? AppColors.white
                          : AppColors.mutedText,
                      size: 18,
                    ),
                  ),
                  if (index != _steps.length - 1)
                    Container(
                      width: 3,
                      height: 34,
                      color: index <= safeIndex
                          ? AppColors.primaryGreen
                          : AppColors.border,
                    ),
                ],
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Padding(
                  padding: const EdgeInsets.only(top: 4),
                  child: Text(
                    _steps[index].$2,
                    style: TextStyle(
                      fontWeight:
                          index <= safeIndex ? FontWeight.w900 : FontWeight.w500,
                    ),
                  ),
                ),
              ),
            ],
          ),
      ],
    );
  }
}
