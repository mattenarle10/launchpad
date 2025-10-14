import 'dart:io';
import 'package:dio/dio.dart';
import '../client.dart';
import 'base.dart';

class ReportApi extends BaseApiGroup {
  ReportApi(ApiClient client) : super(client, '/students');

  /// Submit a daily report
  Future<Map<String, dynamic>> submitDailyReport({
    required int studentId,
    required DateTime reportDate,
    required String description,
    required String activityType,
    required File reportFile,
    required String fileName,
    double hoursRequested = 8.0,
  }) async {
    try {
      // Format date as YYYY-MM-DD
      final dateStr = _formatDate(reportDate);

      // Prepare form data
      final formData = FormData.fromMap({
        'report_date': dateStr,
        'hours_requested': hoursRequested.toString(),
        'description': description.trim().isEmpty 
            ? 'Daily OJT Activities' 
            : description.trim(),
        'activity_type': activityType.trim().isEmpty 
            ? 'Daily Activities' 
            : activityType.trim(),
        'report_file': await MultipartFile.fromFile(
          reportFile.path,
          filename: fileName,
        ),
      });

      // Submit via API
      final response = await post('/$studentId/reports/daily', formData);
      return response as Map<String, dynamic>;
    } catch (e) {
      throw Exception(_parseError(e));
    }
  }

  /// Get daily reports for a student
  Future<List<dynamic>> getDailyReports(int studentId) async {
    try {
      final response = await get('/$studentId/reports/daily');
      final data = response as Map<String, dynamic>;
      return data['data'] as List<dynamic>;
    } catch (e) {
      throw Exception(_parseError(e));
    }
  }

  /// Format date to YYYY-MM-DD
  String _formatDate(DateTime date) {
    return '${date.year}-${date.month.toString().padLeft(2, '0')}-${date.day.toString().padLeft(2, '0')}';
  }

  /// Parse error message
  String _parseError(dynamic error) {
    if (error is DioException) {
      if (error.response?.data != null && error.response!.data is Map) {
        final data = error.response!.data as Map<String, dynamic>;
        return data['message'] ?? 'Failed to process request';
      }
      return error.message ?? 'Network error occurred';
    }
    return error.toString().replaceAll('Exception: ', '');
  }

  // ========== VALIDATION HELPERS ==========

  /// Validate description
  static String? validateDescription(String? value) {
    if (value == null || value.trim().isEmpty) {
      return null; // Optional field
    }
    if (value.trim().length < 10) {
      return 'Description should be at least 10 characters';
    }
    return null;
  }

  /// Validate activity type
  static String? validateActivityType(String? value) {
    if (value == null || value.trim().isEmpty) {
      return null; // Optional field
    }
    if (value.trim().length < 3) {
      return 'Activity type should be at least 3 characters';
    }
    return null;
  }

  /// Validate hours requested
  static String? validateHours(double? hours) {
    if (hours == null || hours <= 0) {
      return 'Hours must be greater than 0';
    }
    if (hours > 24) {
      return 'Hours cannot exceed 24 per day';
    }
    return null;
  }

  /// Check if report date is valid (not future, not too old)
  static String? validateReportDate(DateTime date) {
    final now = DateTime.now();
    final today = DateTime(now.year, now.month, now.day);
    final selectedDay = DateTime(date.year, date.month, date.day);
    
    if (selectedDay.isAfter(today)) {
      return 'Cannot submit report for future dates';
    }
    
    final thirtyDaysAgo = today.subtract(const Duration(days: 30));
    if (selectedDay.isBefore(thirtyDaysAgo)) {
      return 'Cannot submit report older than 30 days';
    }
    
    return null;
  }
}
