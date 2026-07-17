import 'package:flutter/material.dart';

class CustomTextField extends StatelessWidget {
  const CustomTextField({
    required this.label,
    this.controller,
    this.icon,
    this.keyboardType,
    this.obscureText = false,
    this.textInputAction,
    this.validator,
    this.maxLength,
    this.onChanged,
    this.hintText,
    this.helperText,
    this.suffixIcon,
    this.enabled = true,
    this.autofillHints,
    this.onFieldSubmitted,
    super.key,
  });

  final String label;
  final TextEditingController? controller;
  final IconData? icon;
  final TextInputType? keyboardType;
  final bool obscureText;
  final TextInputAction? textInputAction;
  final String? Function(String?)? validator;
  final int? maxLength;
  final ValueChanged<String>? onChanged;
  final String? hintText;
  final String? helperText;
  final Widget? suffixIcon;
  final bool enabled;
  final Iterable<String>? autofillHints;
  final ValueChanged<String>? onFieldSubmitted;

  @override
  Widget build(BuildContext context) {
    return TextFormField(
      controller: controller,
      keyboardType: keyboardType,
      obscureText: obscureText,
      textInputAction: textInputAction,
      validator: validator,
      maxLength: maxLength,
      onChanged: onChanged,
      enabled: enabled,
      autofillHints: autofillHints,
      onFieldSubmitted: onFieldSubmitted,
      decoration: InputDecoration(
        labelText: label,
        hintText: hintText,
        helperText: helperText,
        prefixIcon: icon == null ? null : Icon(icon),
        suffixIcon: suffixIcon,
        counterText: maxLength == null ? null : '',
      ),
    );
  }
}
