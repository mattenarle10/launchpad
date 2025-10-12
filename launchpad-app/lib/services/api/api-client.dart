import 'api-client.dart';
import 'endpoints/general.dart';
import 'endpoints/company.dart';
import 'endpoints/cdc.dart';
import 'endpoints/student.dart';

class Api {
  Api({ApiClient? client}) : client = client ?? ApiClient.I {
    general = GeneralApi(this.client);
    company = CompanyApi(this.client);
    cdc = CdcApi(this.client);
    student = StudentApi(this.client);
  }

  static final Api instance = Api();

  final ApiClient client;
  late final GeneralApi general;
  late final CompanyApi company;
  late final CdcApi cdc;
  late final StudentApi student;

  Future<void> setAuth(String token, Map<String, dynamic> user) => client.setAuth(token, user);
  Future<void> clearAuth() => client.clearAuth();
  Future<bool> isAuthenticated() => client.isAuthenticated();
  Future<Map<String, dynamic>?> getCurrentUser() => client.getCurrentUser();
}
