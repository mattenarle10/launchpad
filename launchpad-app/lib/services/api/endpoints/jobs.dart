import '../client.dart';
import 'base.dart';

class JobsApi extends BaseApiGroup {
  JobsApi(ApiClient client) : super(client, '/jobs');

  /// Get all active job opportunities
  Future<List<dynamic>> getAllJobs() async {
    final response = await get('');
    final data = response as Map<String, dynamic>;
    return data['data'] as List<dynamic>;
  }

  /// Get job details by ID
  Future<Map<String, dynamic>> getJobById(int jobId) async {
    final response = await get('/$jobId');
    return response as Map<String, dynamic>;
  }
}
