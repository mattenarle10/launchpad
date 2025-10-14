import 'package:dio/dio.dart';
import 'package:flutter_dotenv/flutter_dotenv.dart';
import 'auth.dart';

class ApiClient {
  ApiClient._() {
    final baseUrl = dotenv.maybeGet('API_BASE_URL') ?? 'http://localhost/LaunchPad/launchpad-api/public';
    _dio = Dio(BaseOptions(
      baseUrl: baseUrl,
      headers: {'Accept': 'application/json'},
      connectTimeout: const Duration(seconds: 60),
      receiveTimeout: const Duration(seconds: 60),
      sendTimeout: const Duration(seconds: 60),
    ));
    _dio.interceptors.add(InterceptorsWrapper(onRequest: (options, handler) async {
      final token = await _auth.getToken();
      if (token != null && options.extra['skipAuth'] != true) {
        options.headers['Authorization'] = 'Bearer $token';
      }
      handler.next(options);
    }));
  }

  static final ApiClient I = ApiClient._();

  late final Dio _dio;
  final AuthStore _auth = AuthStore.instance;

  Future<void> setAuth(String token, Map<String, dynamic> user) => _auth.setAuth(token, user);
  Future<void> clearAuth() => _auth.clearAuth();
  Future<bool> isAuthenticated() => _auth.isAuthenticated();
  Future<Map<String, dynamic>?> getCurrentUser() => _auth.getUser();
  Future<void> updateUser(Map<String, dynamic> user) => _auth.updateUser(user);

  Future<dynamic> request(
    String endpoint, {
    String method = 'GET',
    Map<String, dynamic>? query,
    dynamic data,
    Map<String, dynamic>? headers,
    bool skipAuth = false,
  }) async {
    try {
      final h = <String, dynamic>{};
      if (headers != null) h.addAll(headers);
      if (data != null && data is! FormData && !h.containsKey('Content-Type')) {
        h['Content-Type'] = 'application/json';
      }
      final options = Options(method: method, headers: h, extra: {'skipAuth': skipAuth});
      final resp = await _dio.request(
        endpoint,
        queryParameters: query,
        data: data,
        options: options,
      );
      return resp.data;
    } on DioException catch (e) {
      print('=== API Error ===');
      print('Endpoint: $method $endpoint');
      print('Request data: $data');
      print('Status code: ${e.response?.statusCode}');
      print('Response data: ${e.response?.data}');
      print('================');
      rethrow;
    }
  }

  Future<dynamic> get(
    String endpoint, {
    Map<String, dynamic>? query,
    Map<String, dynamic>? headers,
    bool skipAuth = false,
  }) {
    return request(endpoint, method: 'GET', query: query, headers: headers, skipAuth: skipAuth);
  }

  Future<dynamic> post(
    String endpoint,
    dynamic data, {
    Map<String, dynamic>? query,
    Map<String, dynamic>? headers,
    bool skipAuth = false,
  }) {
    return request(endpoint, method: 'POST', data: data, query: query, headers: headers, skipAuth: skipAuth);
  }

  Future<dynamic> put(
    String endpoint,
    dynamic data, {
    Map<String, dynamic>? query,
    Map<String, dynamic>? headers,
    bool skipAuth = false,
  }) {
    return request(endpoint, method: 'PUT', data: data, query: query, headers: headers, skipAuth: skipAuth);
  }

  Future<dynamic> delete(
    String endpoint, {
    Map<String, dynamic>? query,
    Map<String, dynamic>? headers,
    bool skipAuth = false,
  }) {
    return request(endpoint, method: 'DELETE', query: query, headers: headers, skipAuth: skipAuth);
  }
}
