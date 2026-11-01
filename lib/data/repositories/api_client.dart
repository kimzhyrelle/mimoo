import 'dart:convert';

import 'package:flutter/foundation.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:http/http.dart' as http;

class ApiException implements Exception {
  const ApiException(this.statusCode, this.message);
  final int statusCode;
  final String message;

  @override
  String toString() => message;
}

class ApiClient {
  ApiClient({http.Client? client, FlutterSecureStorage? storage})
      : _client = client ?? http.Client(),
        _storage = storage ?? const FlutterSecureStorage();

  static const _tokenKey = 'mimoo.api.accessToken';
  static const _configuredBaseUrl =
      String.fromEnvironment('MIMOO_API_BASE_URL');

  final http.Client _client;
  final FlutterSecureStorage _storage;
  String? _token;

  Future<bool> hasSavedSession() async =>
      _token != null || await _storage.read(key: _tokenKey) != null;

  String get baseUrl {
    if (_configuredBaseUrl.isNotEmpty) {
      return _configuredBaseUrl.replaceFirst(RegExp(r'/$'), '');
    }
    if (kIsWeb) return 'http://localhost:8080';
    return switch (defaultTargetPlatform) {
      TargetPlatform.android => 'http://10.0.2.2:8080',
      _ => 'http://127.0.0.1:8080',
    };
  }

  Future<void> signIn(String email, String password) async {
    final response = await _request(
      'POST',
      '/api/auth/login',
      body: {'email': email, 'password': password},
      authenticated: false,
    );
    final body = response as Map<String, dynamic>;
    final token = body['token'];
    if (token is! String || token.isEmpty) {
      throw const ApiException(502, 'The server returned an invalid session.');
    }
    _token = token;
    await _storage.write(key: _tokenKey, value: token);
  }

  Future<void> signOut() async {
    _token = null;
    await _storage.delete(key: _tokenKey);
  }

  Future<Map<String, dynamic>> get(String path) async {
    final value = await _request('GET', path);
    if (value is! Map<String, dynamic>) {
      throw const ApiException(502, 'The server returned an invalid response.');
    }
    return value;
  }

  Future<dynamic> post(String path, {Object? body}) =>
      _request('POST', path, body: body);

  Future<dynamic> patch(String path, {Object? body}) =>
      _request('PATCH', path, body: body);

  Future<dynamic> put(String path, {Object? body}) =>
      _request('PUT', path, body: body);

  Future<dynamic> delete(String path) => _request('DELETE', path);

  Future<dynamic> _request(
    String method,
    String path, {
    Object? body,
    bool authenticated = true,
  }) async {
    var token = _token;
    if (authenticated && token == null) {
      token = await _storage.read(key: _tokenKey);
      _token = token;
    }
    final headers = <String, String>{'Accept': 'application/json'};
    if (body != null) headers['Content-Type'] = 'application/json';
    if (authenticated && token != null) {
      headers['Authorization'] = 'Bearer $token';
    }
    final uri = Uri.parse('$baseUrl$path');
    final encodedBody = body == null ? null : jsonEncode(body);
    final response = switch (method) {
      'GET' => await _client.get(uri, headers: headers),
      'POST' => await _client.post(uri, headers: headers, body: encodedBody),
      'PATCH' => await _client.patch(uri, headers: headers, body: encodedBody),
      'PUT' => await _client.put(uri, headers: headers, body: encodedBody),
      'DELETE' => await _client.delete(uri, headers: headers),
      _ => throw ArgumentError.value(method, 'method'),
    };
    if (response.statusCode == 204) return null;
    dynamic decoded;
    try {
      decoded = jsonDecode(response.body);
    } on FormatException {
      throw ApiException(
        response.statusCode,
        'The server returned invalid JSON (${response.statusCode}).',
      );
    }
    if (response.statusCode < 200 || response.statusCode >= 300) {
      final message =
          decoded is Map<String, dynamic> && decoded['error'] is String
              ? decoded['error'] as String
              : 'Request failed (${response.statusCode}).';
      throw ApiException(response.statusCode, message);
    }
    return decoded;
  }

  void close() => _client.close();
}
