import 'package:dio/dio.dart';
import '../client.dart';
import 'base.dart';

class StudentApi extends BaseApiGroup {
  StudentApi(ApiClient client) : super(client, '/students');

  /// Register a new student (no auth required)
  /// Requires FormData with:
  /// - email, id_number, first_name, last_name, course, contact_num, password, company_name, cor (File - Certificate of Registration)
  Future<Map<String, dynamic>> register(FormData formData) async {
    final response = await client.post('/students/register', formData, skipAuth: true);
    return response as Map<String, dynamic>;
  }

  /// Login student
  Future<Map<String, dynamic>> login(String idNumber, String password) async {
    final response = await client.post(
      '/auth/login',
      {
        'username': idNumber,
        'password': password,
        'userType': 'student',
      },
      skipAuth: true,
    );
    return response as Map<String, dynamic>;
  }

  /// Get student profile by ID
  Future<Map<String, dynamic>> getProfile(int studentId) async {
    final response = await get('/$studentId');
    return response as Map<String, dynamic>;
  }

  /// Get student OJT progress
  Future<Map<String, dynamic>> getOjtProgress(int studentId) async {
    final response = await get('/$studentId/ojt');
    return response as Map<String, dynamic>;
  }

  /// Get daily reports
  Future<List<dynamic>> getDailyReports(int studentId) async {
    final response = await get('/$studentId/reports/daily');
    final data = response as Map<String, dynamic>;
    return data['data'] as List<dynamic>;
  }

  /// Submit daily report
  Future<Map<String, dynamic>> submitDailyReport(int studentId, FormData formData) async {
    final response = await post('/$studentId/reports/daily', formData);
    return response as Map<String, dynamic>;
  }
}
