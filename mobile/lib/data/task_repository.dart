import 'dart:convert';

import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:http/http.dart' as http;

import '../l10n/generated/app_localizations_ar.dart';
import '../models/task.dart';

class ApiException implements Exception {
  const ApiException(this.message, {this.statusCode});

  final String message;
  final int? statusCode;

  @override
  String toString() => message;
}

abstract interface class TaskRepository {
  Future<String?> readApiBaseUrl();
  Future<void> saveApiBaseUrl(String value);
  Future<String?> readToken();
  Future<void> saveToken(String token);
  Future<void> clearToken();
  Future<Map<String, dynamic>> currentUser(String token);
  Future<Map<String, dynamic>> authenticate({
    required String endpoint,
    required Map<String, String> body,
  });
  Future<void> logout(String token);
  Future<List<Task>> getTasks(String token);
  Future<Task> createTask(
    String token, {
    required String title,
    required String description,
  });
  Future<Task> updateTask(
    String token,
    int id, {
    required String title,
    required String description,
  });
  Future<Task> toggleTask(String token, int id);
  Future<void> deleteTask(String token, int id);
}

class HttpTaskRepository implements TaskRepository {
  HttpTaskRepository({
    FlutterSecureStorage? secureStorage,
    http.Client? client,
    this._defaultBaseUrl = 'http://10.0.2.2:8000',
  }) : _secureStorage = secureStorage ?? const FlutterSecureStorage(),
       _client = client ?? http.Client();

  static const _baseUrlKey = 'api_base_url';
  static const _tokenKey = 'sanctum_token';
  static const _apiPrefix = '/api/v1';

  final FlutterSecureStorage _secureStorage;
  final http.Client _client;
  final String _defaultBaseUrl;
  String? _baseUrl;

  @override
  Future<String?> readApiBaseUrl() async {
    _baseUrl = await _secureStorage.read(key: _baseUrlKey) ?? _defaultBaseUrl;
    return _baseUrl;
  }

  @override
  Future<void> saveApiBaseUrl(String value) async {
    final uri = Uri.tryParse(value.trim());
    if (uri == null ||
        !uri.hasAuthority ||
        !const {'http', 'https'}.contains(uri.scheme) ||
        uri.host.isEmpty) {
      throw ApiException(AppLocalizationsAr().invalidApiUrl);
    }
    final normalized = value.trim().replaceFirst(RegExp(r'/+$'), '');
    _baseUrl = normalized;
    await _secureStorage.write(key: _baseUrlKey, value: normalized);
  }

  @override
  Future<String?> readToken() => _secureStorage.read(key: _tokenKey);

  @override
  Future<void> saveToken(String token) =>
      _secureStorage.write(key: _tokenKey, value: token);

  @override
  Future<void> clearToken() => _secureStorage.delete(key: _tokenKey);

  Uri _uri(String path) {
    final base = _baseUrl ?? _defaultBaseUrl;
    return Uri.parse('$base$_apiPrefix$path');
  }

  Map<String, String> _headers([String? token]) => {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
    if (token != null) 'Authorization': 'Bearer $token',
  };

  Future<dynamic> _request(
    String method,
    String path, {
    String? token,
    Map<String, Object?>? body,
  }) async {
    try {
      final uri = _uri(path);
      final headers = _headers(token);
      final response = switch (method) {
        'GET' => await _client.get(uri, headers: headers),
        'POST' => await _client.post(
          uri,
          headers: headers,
          body: jsonEncode(body ?? const {}),
        ),
        'PUT' => await _client.put(
          uri,
          headers: headers,
          body: jsonEncode(body ?? const {}),
        ),
        'PATCH' => await _client.patch(
          uri,
          headers: headers,
          body: jsonEncode(body ?? const {}),
        ),
        'DELETE' => await _client.delete(uri, headers: headers),
        _ => throw ArgumentError.value(method, 'method'),
      };
      final decoded = response.body.isEmpty ? null : jsonDecode(response.body);
      if (response.statusCode < 200 || response.statusCode >= 300) {
        throw ApiException(
          _errorMessage(decoded, response.statusCode),
          statusCode: response.statusCode,
        );
      }
      return decoded;
    } on ApiException {
      rethrow;
    } on FormatException {
      throw ApiException(AppLocalizationsAr().unreadableResponse);
    } catch (error) {
      throw ApiException(AppLocalizationsAr().networkError);
    }
  }

  String _errorMessage(dynamic response, int statusCode) {
    final strings = AppLocalizationsAr();
    if (statusCode == 401) {
      return strings.sessionExpired;
    }
    if (statusCode == 403) {
      return strings.permissionDenied;
    }
    if (statusCode == 404) {
      return strings.taskNotFound;
    }

    if (response is Map<String, dynamic>) {
      final errors = response['errors'];
      if (errors is Map<String, dynamic>) {
        for (final value in errors.values) {
          if (value is List && value.isNotEmpty) return value.first.toString();
        }
      }
      final message = response['message'];
      if (message is String && message.isNotEmpty) return message;
    }
    return strings.requestFailed(statusCode);
  }

  @override
  Future<Map<String, dynamic>> authenticate({
    required String endpoint,
    required Map<String, String> body,
  }) async {
    final response = await _request('POST', endpoint, body: body);
    if (response is! Map<String, dynamic>) {
      throw ApiException(AppLocalizationsAr().invalidServerResponse);
    }
    return response;
  }

  @override
  Future<Map<String, dynamic>> currentUser(String token) async {
    final response = await _request('GET', '/me', token: token);
    if (response is! Map<String, dynamic> ||
        response['user'] is! Map<String, dynamic>) {
      throw ApiException(AppLocalizationsAr().invalidUserResponse);
    }
    return response['user'] as Map<String, dynamic>;
  }

  @override
  Future<void> logout(String token) async {
    await _request('POST', '/logout', token: token);
  }

  @override
  Future<List<Task>> getTasks(String token) async {
    final response = await _request('GET', '/tasks', token: token);
    if (response is! List) {
      throw ApiException(AppLocalizationsAr().invalidTaskListResponse);
    }
    return response
        .map((item) => Task.fromJson(item as Map<String, dynamic>))
        .toList();
  }

  @override
  Future<Task> createTask(
    String token, {
    required String title,
    required String description,
  }) async {
    final response = await _request(
      'POST',
      '/tasks',
      token: token,
      body: {'title': title, 'description': description},
    );
    return Task.fromJson(response as Map<String, dynamic>);
  }

  @override
  Future<Task> updateTask(
    String token,
    int id, {
    required String title,
    required String description,
  }) async {
    final response = await _request(
      'PUT',
      '/tasks/$id',
      token: token,
      body: {'title': title, 'description': description},
    );
    return Task.fromJson(response as Map<String, dynamic>);
  }

  @override
  Future<Task> toggleTask(String token, int id) async {
    final response = await _request(
      'PATCH',
      '/tasks/$id/complete',
      token: token,
    );
    return Task.fromJson(response as Map<String, dynamic>);
  }

  @override
  Future<void> deleteTask(String token, int id) async {
    await _request('DELETE', '/tasks/$id', token: token);
  }
}
