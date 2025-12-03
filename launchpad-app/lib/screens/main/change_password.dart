import 'package:flutter/material.dart';
import '../../styles/colors.dart';
import '../../components/custom_text_field.dart';
import '../../components/custom_button.dart';
import '../../components/toast.dart';
import '../../services/api/client.dart';
import '../../services/api/endpoints/auth.dart';

class ChangePasswordScreen extends StatefulWidget {
  const ChangePasswordScreen({super.key});

  @override
  State<ChangePasswordScreen> createState() => _ChangePasswordScreenState();
}

class _ChangePasswordScreenState extends State<ChangePasswordScreen> {
  final _formKey = GlobalKey<FormState>();
  final _currentPasswordController = TextEditingController();
  final _newPasswordController = TextEditingController();
  final _confirmPasswordController = TextEditingController();

  bool _isLoading = false;
  bool _obscureCurrentPassword = true;
  bool _obscureNewPassword = true;
  bool _obscureConfirmPassword = true;

  // Password requirement states
  bool _hasMinLength = false;
  bool _hasUppercase = false;
  bool _hasLowercase = false;
  bool _hasNumber = false;
  bool _hasSpecialChar = false;

  @override
  void initState() {
    super.initState();
    _newPasswordController.addListener(_validatePasswordRequirements);
  }

  @override
  void dispose() {
    _currentPasswordController.dispose();
    _newPasswordController.dispose();
    _confirmPasswordController.dispose();
    super.dispose();
  }

  void _validatePasswordRequirements() {
    final password = _newPasswordController.text;
    setState(() {
      _hasMinLength = password.length >= 8;
      _hasUppercase = RegExp(r'[A-Z]').hasMatch(password);
      _hasLowercase = RegExp(r'[a-z]').hasMatch(password);
      _hasNumber = RegExp(r'[0-9]').hasMatch(password);
      _hasSpecialChar = RegExp(r'[!@#$%^&*()_+\-=\[\]{};:"|,.<>/?]').hasMatch(password);
    });
  }

  bool get _isPasswordValid =>
      _hasMinLength && _hasUppercase && _hasLowercase && _hasNumber && _hasSpecialChar;

  Future<void> _changePassword() async {
    if (!_formKey.currentState!.validate()) return;

    if (!_isPasswordValid) {
      Toast.error(context, 'Please meet all password requirements');
      return;
    }

    setState(() => _isLoading = true);

    try {
      final authApi = AuthApi(ApiClient.I);
      await authApi.changePassword(
        currentPassword: _currentPasswordController.text,
        newPassword: _newPasswordController.text,
        confirmPassword: _confirmPasswordController.text,
      );

      if (mounted) {
        Toast.success(context, 'Password changed successfully!');
        Navigator.pop(context);
      }
    } catch (e) {
      if (mounted) {
        Toast.error(context, e.toString().replaceAll('Exception: ', ''));
      }
    } finally {
      if (mounted) {
        setState(() => _isLoading = false);
      }
    }
  }

  Widget _buildRequirementRow(String text, bool isMet) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 2),
      child: Row(
        children: [
          Icon(
            isMet ? Icons.check_circle : Icons.circle_outlined,
            size: 16,
            color: isMet ? Colors.green : const Color(0xFF9CA3AF),
          ),
          const SizedBox(width: 8),
          Text(
            text,
            style: TextStyle(
              fontSize: 12,
              color: isMet ? Colors.green : const Color(0xFF6B7280),
            ),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back, color: Color(0xFF4A6491)),
          onPressed: () => Navigator.pop(context),
        ),
        title: const Text(
          'Change Password',
          style: TextStyle(
            color: Color(0xFF4A6491),
            fontWeight: FontWeight.w600,
          ),
        ),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20.0),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Security Icon
              Center(
                child: Container(
                  width: 80,
                  height: 80,
                  decoration: BoxDecoration(
                    color: const Color(0xFFE8EFF9),
                    shape: BoxShape.circle,
                  ),
                  child: const Icon(
                    Icons.lock_outline,
                    size: 40,
                    color: Color(0xFF4A6491),
                  ),
                ),
              ),
              const SizedBox(height: 24),

              // Current Password
              CustomTextField(
                controller: _currentPasswordController,
                label: 'Current Password',
                obscureText: _obscureCurrentPassword,
                suffixIcon: IconButton(
                  icon: Icon(
                    _obscureCurrentPassword ? Icons.visibility_off : Icons.visibility,
                    color: const Color(0xFF9CA3AF),
                  ),
                  onPressed: () {
                    setState(() => _obscureCurrentPassword = !_obscureCurrentPassword);
                  },
                ),
                validator: (value) {
                  if (value == null || value.isEmpty) {
                    return 'Current password is required';
                  }
                  return null;
                },
              ),
              const SizedBox(height: 20),

              // New Password
              CustomTextField(
                controller: _newPasswordController,
                label: 'New Password',
                obscureText: _obscureNewPassword,
                suffixIcon: IconButton(
                  icon: Icon(
                    _obscureNewPassword ? Icons.visibility_off : Icons.visibility,
                    color: const Color(0xFF9CA3AF),
                  ),
                  onPressed: () {
                    setState(() => _obscureNewPassword = !_obscureNewPassword);
                  },
                ),
                validator: (value) {
                  if (value == null || value.isEmpty) {
                    return 'New password is required';
                  }
                  if (value == _currentPasswordController.text) {
                    return 'New password must be different from current';
                  }
                  return null;
                },
              ),
              const SizedBox(height: 12),

              // Password Requirements
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: const Color(0xFFF9FAFB),
                  borderRadius: BorderRadius.circular(8),
                  border: Border.all(color: const Color(0xFFE5E7EB)),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'Password Requirements:',
                      style: TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.w600,
                        color: Color(0xFF374151),
                      ),
                    ),
                    const SizedBox(height: 8),
                    _buildRequirementRow('At least 8 characters', _hasMinLength),
                    _buildRequirementRow('One uppercase letter (A-Z)', _hasUppercase),
                    _buildRequirementRow('One lowercase letter (a-z)', _hasLowercase),
                    _buildRequirementRow('One number (0-9)', _hasNumber),
                    _buildRequirementRow('One special character (!@#\$%...)', _hasSpecialChar),
                  ],
                ),
              ),
              const SizedBox(height: 20),

              // Confirm Password
              CustomTextField(
                controller: _confirmPasswordController,
                label: 'Confirm New Password',
                obscureText: _obscureConfirmPassword,
                suffixIcon: IconButton(
                  icon: Icon(
                    _obscureConfirmPassword ? Icons.visibility_off : Icons.visibility,
                    color: const Color(0xFF9CA3AF),
                  ),
                  onPressed: () {
                    setState(() => _obscureConfirmPassword = !_obscureConfirmPassword);
                  },
                ),
                validator: (value) {
                  if (value == null || value.isEmpty) {
                    return 'Please confirm your new password';
                  }
                  if (value != _newPasswordController.text) {
                    return 'Passwords do not match';
                  }
                  return null;
                },
              ),
              const SizedBox(height: 32),

              // Change Password Button
              CustomButton(
                text: _isLoading ? 'Changing...' : 'Change Password',
                onPressed: _isLoading ? null : _changePassword,
              ),
            ],
          ),
        ),
      ),
    );
  }
}
