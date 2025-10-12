import '../api-client.dart';
import 'base.dart';

class CompanyApi extends BaseApiGroup {
  CompanyApi(ApiClient client) : super(client, '/api/company');
}
