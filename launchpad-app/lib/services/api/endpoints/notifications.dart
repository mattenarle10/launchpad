import '../client.dart';
import 'base.dart';

class NotificationsApi extends BaseApiGroup {
  NotificationsApi(ApiClient client) : super(client, '/notifications');

  /// Get student's notifications
  Future<List<dynamic>> getStudentNotifications() async {
    final response = await get('/student');
    
    if (response is Map<String, dynamic>) {
      final data = response['data'];
      if (data is List) {
        return data;
      }
    }
    
    throw Exception('Unexpected response structure: $response');
  }

  /// Mark notification as read
  Future<Map<String, dynamic>> markAsRead(int notificationId) async {
    final response = await put('/$notificationId/read', {});
    return response as Map<String, dynamic>;
  }

  /// Get all notifications (CDC only)
  Future<List<dynamic>> getAllNotifications() async {
    final response = await get('');
    final data = response as Map<String, dynamic>;
    return data['data'] as List<dynamic>;
  }

  /// Create notification (CDC only)
  Future<Map<String, dynamic>> createNotification({
    required String title,
    required String message,
    required String recipientType,
    List<int>? studentIds,
  }) async {
    final data = {
      'title': title,
      'message': message,
      'recipient_type': recipientType,
      if (studentIds != null) 'student_ids': studentIds,
    };
    final response = await post('', data);
    return response as Map<String, dynamic>;
  }

  /// Delete notification (CDC only)
  Future<Map<String, dynamic>> deleteNotification(int notificationId) async {
    final response = await delete('/$notificationId');
    return response as Map<String, dynamic>;
  }
}
