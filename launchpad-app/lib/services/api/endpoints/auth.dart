import 'package:dio/dio.dart';
import '../client.dart';
import 'base.dart';

class AuthApi extends BaseApiGroup {
  AuthApi(ApiClient client) : super(client, '/auth');

  /// Change password for authenticated user
  Future<Map<String, dynamic>> changePassword({
    required String currentPassword,
    required String newPassword,
    required String confirmPassword,
  }) async {
    try {
      final response = await post('/change-password', {
        'current_password': currentPassword,
        'new_password': newPassword,
        'confirm_password': confirmPassword,
      });
      return response;
    } on DioException catch (e) {
      final message = e.response?.data?['message'] ?? 'Failed to change password';
      throw Exception(message);
    }
  }

  /// Validate password complexity (client-side)
  static Map<String, dynamic> validatePasswordComplexity(String password) {
    final errors = <String>[];

    if (password.length < 8) {
      errors.add('Password must be at least 8 characters');
    }

    if (!RegExp(r'[A-Z]').hasMatch(password)) {
      errors.add('Password must contain at least one uppercase letter');
    }

    if (!RegExp(r'[a-z]').hasMatch(password)) {
      errors.add('Password must contain at least one lowercase letter');
    }

    if (!RegExp(r'[0-9]').hasMatch(password)) {
      errors.add('Password must contain at least one number');
    }

    if (!RegExp(r'[!@#$%^&*()_+\-=\[\]{};:"|,.<>/?]').hasMatch(password)) {
      errors.add('Password must contain at least one special character');
    }

    return {
      'valid': errors.isEmpty,
      'errors': errors,
    };
  }
}
