import '../client.dart';

class BaseApiGroup {
  BaseApiGroup(this.client, this.basePath);
  final ApiClient client;
  final String basePath;

  String _join(String a, String b) {
    if (a.endsWith('/') && b.startsWith('/')) return a + b.substring(1);
    if (!a.endsWith('/') && !b.startsWith('/')) return '$a/$b';
    return '$a$b';
  }

  Future<dynamic> get(String path, {Map<String, dynamic>? query, Map<String, dynamic>? headers, bool skipAuth = false}) {
    return client.get(_join(basePath, path), query: query, headers: headers, skipAuth: skipAuth);
  }

  Future<dynamic> post(String path, dynamic data, {Map<String, dynamic>? query, Map<String, dynamic>? headers, bool skipAuth = false}) {
    return client.post(_join(basePath, path), data, query: query, headers: headers, skipAuth: skipAuth);
  }

  Future<dynamic> put(String path, dynamic data, {Map<String, dynamic>? query, Map<String, dynamic>? headers, bool skipAuth = false}) {
    return client.put(_join(basePath, path), data, query: query, headers: headers, skipAuth: skipAuth);
  }

  Future<dynamic> delete(String path, {Map<String, dynamic>? query, Map<String, dynamic>? headers, bool skipAuth = false}) {
    return client.delete(_join(basePath, path), query: query, headers: headers, skipAuth: skipAuth);
  }
}
