import '../client.dart';
import 'base.dart';

class StudentApi extends BaseApiGroup {
  StudentApi(ApiClient client) : super(client, '/api/student');
}
