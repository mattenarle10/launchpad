import 'package:flutter/material.dart';
import '../styles/colors.dart';

enum ToastType { success, error, warning, info }

class Toast {
  static void show(
    BuildContext context,
    String message, {
    ToastType type = ToastType.info,
    Duration duration = const Duration(seconds: 4),
  }) {
    final config = _getConfig(type);
    
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Row(
          children: [
            Icon(config.icon, color: Colors.white, size: 20),
            const SizedBox(width: 12),
            Expanded(
              child: Text(
                message,
                style: const TextStyle(
                  color: Colors.white,
                  fontSize: 14,
                  fontWeight: FontWeight.w500,
                ),
              ),
            ),
          ],
        ),
        backgroundColor: config.color,
        behavior: SnackBarBehavior.floating,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(8),
        ),
        margin: const EdgeInsets.only(
          top: 50,
          right: 16,
          left: 16,
        ),
        duration: duration,
      ),
    );
  }

  static void success(BuildContext context, String message) {
    show(context, message, type: ToastType.success);
  }

  static void error(BuildContext context, String message) {
    show(context, message, type: ToastType.error);
  }

  static void warning(BuildContext context, String message) {
    show(context, message, type: ToastType.warning);
  }

  static void info(BuildContext context, String message) {
    show(context, message, type: ToastType.info);
  }

  static _ToastConfig _getConfig(ToastType type) {
    switch (type) {
      case ToastType.success:
        return _ToastConfig(
          color: const Color(0xFF10B981),
          icon: Icons.check_circle,
        );
      case ToastType.error:
        return _ToastConfig(
          color: const Color(0xFFEF4444),
          icon: Icons.error,
        );
      case ToastType.warning:
        return _ToastConfig(
          color: const Color(0xFFF59E0B),
          icon: Icons.warning_amber_rounded,
        );
      case ToastType.info:
        return _ToastConfig(
          color: AppColors.primary,
          icon: Icons.info,
        );
    }
  }
}

class _ToastConfig {
  final Color color;
  final IconData icon;

  _ToastConfig({required this.color, required this.icon});
}
