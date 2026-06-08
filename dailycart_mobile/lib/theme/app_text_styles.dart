import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

import 'app_colors.dart';

class AppTextStyles {
  static TextTheme get textTheme => GoogleFonts.poppinsTextTheme().apply(
        bodyColor: AppColors.textColor,
        displayColor: AppColors.textColor,
      );

  static TextStyle get display => GoogleFonts.poppins(
        color: AppColors.textColor,
        fontSize: 32,
        fontWeight: FontWeight.w800,
        height: 1.15,
      );

  static TextStyle get title => GoogleFonts.poppins(
        color: AppColors.textColor,
        fontSize: 22,
        fontWeight: FontWeight.w800,
      );

  static TextStyle get body => GoogleFonts.poppins(
        color: AppColors.textColor,
        fontSize: 15,
        fontWeight: FontWeight.w500,
        height: 1.45,
      );

  static TextStyle get muted => body.copyWith(
        color: AppColors.mutedText,
        fontWeight: FontWeight.w400,
      );

  static TextStyle get button => GoogleFonts.poppins(
        color: AppColors.white,
        fontSize: 16,
        fontWeight: FontWeight.w700,
      );
}
