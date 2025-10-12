import 'dart:convert';
import 'package:shared_preferences/shared_preferences.dart';

class AuthStore {
  AuthStore._();
  static final AuthStore instance = AuthStore._();

  static const String _tokenKey = 'auth_token';
  static const String _userKey = 'user';

  String? _token;
  Map<String, dynamic>? _user;

  Future<void> setAuth(String token, Map<String, dynamic> user) async {
    _token = token;
    _user = user;
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_tokenKey, token);
    await prefs.setString(_userKey, jsonEncode(user));
  }

  Future<void> clearAuth() async {
    _token = null;
    _user = null;
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(_tokenKey);
    await prefs.remove(_userKey);
  }

  Future<String?> getToken() async {
    if (_token != null) return _token;
    final prefs = await SharedPreferences.getInstance();
    final t = prefs.getString(_tokenKey);
    _token = t;
    return t;
  }

  Future<Map<String, dynamic>?> getUser() async {
    if (_user != null) return _user;
    final prefs = await SharedPreferences.getInstance();
    final s = prefs.getString(_userKey);
    if (s == null) return null;
    try {
      final map = jsonDecode(s) as Map<String, dynamic>;
      _user = map;
      return map;
    } catch (_) {
      return null;
    }
  }

  Future<bool> isAuthenticated() async {
    final t = await getToken();
    return t != null && t.isNotEmpty;
  }
}
