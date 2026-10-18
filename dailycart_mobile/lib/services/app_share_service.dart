import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:share_plus/share_plus.dart';

class AppShareService {
  const AppShareService._();

  static Future<void> shareText(
    BuildContext context, {
    required String text,
    required String subject,
  }) async {
    final renderBox = context.findRenderObject();
    final origin = renderBox is RenderBox
        ? renderBox.localToGlobal(Offset.zero) & renderBox.size
        : null;
    await SharePlus.instance.share(
      ShareParams(text: text, subject: subject, sharePositionOrigin: origin),
    );
  }

  static Future<void> copyLink(String link) {
    return Clipboard.setData(ClipboardData(text: link));
  }
}
