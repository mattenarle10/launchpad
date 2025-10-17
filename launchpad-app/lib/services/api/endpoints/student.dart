import 'package:dio/dio.dart';
import '../client.dart';
import 'base.dart';

class StudentApi extends BaseApiGroup {
  StudentApi(ApiClient client) : super(client, '/students');

  /// Register a new student (no auth required)
  /// Requires FormData with:
  /// - email, id_number, first_name, last_name, course, contact_num, password, cor (File - Certificate of Registration)
  /// Note: company will be assigned by CDC during verification
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

  /// Get student details by ID (for CDC/admin use)
  Future<Map<String, dynamic>> getStudentById(int studentId) async {
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

  /// Get student evaluation from partner company
  Future<Map<String, dynamic>> getEvaluation() async {
    final response = await get('/evaluation');
    return response as Map<String, dynamic>;
  }

  /// Get student performance score from partner company
  Future<Map<String, dynamic>> getPerformance() async {
    final response = await get('/performance');
    return response as Map<String, dynamic>;
  }

  /// Get student profile
  Future<Map<String, dynamic>> getProfile() async {
    final response = await client.get('/profile');
    return response as Map<String, dynamic>;
  }

  /// Update student profile
  Future<Map<String, dynamic>> updateProfile({
    required String firstName,
    required String lastName,
    required String email,
    required String contactNum,
    String? specialization,
  }) async {
    final data = {
      'first_name': firstName,
      'last_name': lastName,
      'email': email,
      'contact_num': contactNum,
    };
    
    if (specialization != null && specialization.isNotEmpty) {
      data['specialization'] = specialization;
    }
    
    final response = await client.put('/profile', data);
    return response as Map<String, dynamic>;
  }
  
  /// Get evaluation history
  Future<Map<String, dynamic>> getEvaluationHistory() async {
    return await client.get('/students/evaluations');
  }
}
