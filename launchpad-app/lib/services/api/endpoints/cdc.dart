import '../api_client.dart';
import 'base_api_group.dart';

class CdcApi extends BaseApiGroup {
  CdcApi(ApiClient client) : super(client, '/api/cdc');
}
