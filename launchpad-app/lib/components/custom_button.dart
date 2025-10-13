import 'package:flutter/material.dart';
import '../styles/colors.dart';

class CustomButton extends StatelessWidget {
  final String text;
  final VoidCallback? onPressed;
  final bool isLoading;
  final bool isOutlined;
  final Color? color;
  final Color? textColor;

  const CustomButton({
    super.key,
    required this.text,
    required this.onPressed,
    this.isLoading = false,
    this.isOutlined = false,
    this.color,
    this.textColor,
  });

  @override
  Widget build(BuildContext context) {
    final btnColor = color ?? AppColors.primary;
    final btnTextColor = textColor ?? (isOutlined ? btnColor : Colors.white);

    return SizedBox(
      width: double.infinity,
      height: 48,
      child: isOutlined
          ? OutlinedButton(
              onPressed: isLoading ? null : onPressed,
              style: OutlinedButton.styleFrom(
                side: BorderSide(color: btnColor, width: 1.5),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(8),
                ),
                backgroundColor: Colors.transparent,
              ),
              child: isLoading
                  ? SizedBox(
                      width: 20,
                      height: 20,
                      child: CircularProgressIndicator(
                        strokeWidth: 2,
                        valueColor: AlwaysStoppedAnimation<Color>(btnColor),
                      ),
                    )
                  : Text(
                      text,
                      style: TextStyle(
                        color: btnTextColor,
                        fontSize: 15,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
            )
          : ElevatedButton(
              onPressed: isLoading ? null : onPressed,
              style: ElevatedButton.styleFrom(
                backgroundColor: btnColor,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(8),
                ),
                elevation: 0,
              ),
              child: isLoading
                  ? const SizedBox(
                      width: 20,
                      height: 20,
                      child: CircularProgressIndicator(
                        strokeWidth: 2,
                        valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
                      ),
                    )
                  : Text(
                      text,
                      style: TextStyle(
                        color: btnTextColor,
                        fontSize: 15,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
            ),
    );
  }
}

