import 'dart:async';
import 'dart:convert';

import 'package:http/http.dart' as http;

import '../config/api_config.dart';
import 'api_exception.dart';
import 'auth_storage_service.dart';

class ApiService {
  ApiService({
    http.Client? client,
    AuthStorageService? storageService,
    this.onUnauthorized,
  }) : _client = client ?? http.Client(),
       _storageService = storageService ?? AuthStorageService();

  final http.Client _client;
  final AuthStorageService _storageService;
  final Future<void> Function()? onUnauthorized;

  Uri buildUri(String path) {
    final normalizedPath = path.startsWith('/') ? path : '/$path';
    return Uri.parse('${ApiConfig.baseUrl}$normalizedPath');
  }

  Future<http.Response> get(String path, {Map<String, String>? headers}) async {
    final response = await _client
        .get(buildUri(path), headers: _headers(headers))
        .timeout(ApiConfig.timeout);
    return _processResponse(response);
  }

  Future<http.Response> post(
    String path, {
    Map<String, String>? headers,
    Object? body,
  }) async {
    final response = await _client
        .post(
          buildUri(path),
          headers: _headers(headers),
          body: body == null ? null : jsonEncode(body),
        )
        .timeout(ApiConfig.timeout);
    return _processResponse(response);
  }

  Future<http.Response> put(
    String path, {
    Map<String, String>? headers,
    Object? body,
  }) async {
    final response = await _client
        .put(
          buildUri(path),
          headers: _headers(headers),
          body: body == null ? null : jsonEncode(body),
        )
        .timeout(ApiConfig.timeout);
    return _processResponse(response);
  }

  Future<http.Response> delete(
    String path, {
    Map<String, String>? headers,
  }) async {
    final response = await _client
        .delete(buildUri(path), headers: _headers(headers))
        .timeout(ApiConfig.timeout);
    return _processResponse(response);
  }

  Future<http.Response> multipart(
    String method,
    String path, {
    Map<String, String>? headers,
    Map<String, String>? fields,
    List<http.MultipartFile>? files,
  }) async {
    final request = http.MultipartRequest(method, buildUri(path));
    request.headers.addAll({'Accept': 'application/json', ...?headers});
    request.fields.addAll(fields ?? const {});
    request.files.addAll(files ?? const []);
    final streamedResponse = await _client
        .send(request)
        .timeout(ApiConfig.timeout);
    return _processResponse(await http.Response.fromStream(streamedResponse));
  }

  Future<Map<String, dynamic>> getJson(
    String path, {
    bool authenticated = false,
    String fallbackMessage = 'データを取得できませんでした。',
  }) => requestJson(
    'GET',
    path,
    authenticated: authenticated,
    fallbackMessage: fallbackMessage,
  );

  Future<Map<String, dynamic>> postJson(
    String path, {
    Object? body,
    bool authenticated = false,
    String fallbackMessage = 'データを送信できませんでした。',
  }) => requestJson(
    'POST',
    path,
    body: body,
    authenticated: authenticated,
    fallbackMessage: fallbackMessage,
  );

  Future<Map<String, dynamic>> putJson(
    String path, {
    Object? body,
    bool authenticated = true,
    String fallbackMessage = 'データを更新できませんでした。',
  }) => requestJson(
    'PUT',
    path,
    body: body,
    authenticated: authenticated,
    fallbackMessage: fallbackMessage,
  );

  Future<Map<String, dynamic>> deleteJson(
    String path, {
    bool authenticated = true,
    String fallbackMessage = 'データを削除できませんでした。',
  }) => requestJson(
    'DELETE',
    path,
    authenticated: authenticated,
    fallbackMessage: fallbackMessage,
    allowEmptyResponse: true,
  );

  Future<Map<String, dynamic>> requestJson(
    String method,
    String path, {
    Object? body,
    bool authenticated = false,
    String fallbackMessage = 'API通信に失敗しました。',
    bool allowEmptyResponse = false,
  }) async {
    try {
      final headers = await _authorizedHeaders(authenticated);
      final response = switch (method.toUpperCase()) {
        'GET' => await get(path, headers: headers),
        'POST' => await post(path, headers: headers, body: body),
        'PUT' => await put(path, headers: headers, body: body),
        'DELETE' => await delete(path, headers: headers),
        _ => throw ArgumentError.value(method, 'method', '未対応のHTTPメソッドです。'),
      };
      return await decodeJsonResponse(
        response,
        fallbackMessage: fallbackMessage,
        allowEmptyResponse: allowEmptyResponse,
      );
    } on ApiException {
      rethrow;
    } on TimeoutException {
      throw const ApiException(
        '通信がタイムアウトしました。時間をおいてお試しください。',
        type: ApiExceptionType.timeout,
      );
    } on http.ClientException {
      throw const ApiException(
        'サーバーに接続できません。通信環境をご確認ください。',
        type: ApiExceptionType.network,
      );
    }
  }

  Future<Map<String, dynamic>> decodeJsonResponse(
    http.Response response, {
    required String fallbackMessage,
    bool allowEmptyResponse = false,
  }) async {
    if (response.statusCode == 401) {
      await _handleUnauthorized();
      throw const ApiException(
        'ログインの有効期限が切れました。もう一度ログインしてください。',
        type: ApiExceptionType.unauthorized,
        statusCode: 401,
      );
    }
    if (allowEmptyResponse &&
        (response.statusCode == 204 || response.bodyBytes.isEmpty)) {
      return <String, dynamic>{};
    }
    final Object? decoded;
    try {
      decoded = jsonDecode(utf8.decode(response.bodyBytes));
    } on FormatException {
      throw ApiException(
        'サーバーから正しい応答を受け取れませんでした。',
        type: ApiExceptionType.invalidResponse,
        statusCode: response.statusCode,
      );
    }
    if (decoded is! Map<String, dynamic>) {
      throw ApiException(
        'サーバーから正しい応答を受け取れませんでした。',
        type: ApiExceptionType.invalidResponse,
        statusCode: response.statusCode,
      );
    }
    if (response.statusCode < 200 || response.statusCode >= 300) {
      throw ApiException(
        errorMessage(decoded, fallback: fallbackMessage),
        type: ApiExceptionType.server,
        statusCode: response.statusCode,
      );
    }
    return decoded;
  }

  String errorMessage(Map<String, dynamic> json, {required String fallback}) {
    final errors = json['errors'];
    if (errors is Map<String, dynamic>) {
      for (final value in errors.values) {
        if (value is List && value.isNotEmpty && value.first is String) {
          return value.first as String;
        }
      }
    }
    final message = json['message'];
    return message is String && message.isNotEmpty ? message : fallback;
  }

  Future<Map<String, String>> _authorizedHeaders(bool authenticated) async {
    if (!authenticated) return const {};
    final token = await _storageService.readToken();
    if (token == null || token.isEmpty) {
      throw const ApiException(
        'ログイン情報が見つかりません。',
        type: ApiExceptionType.unauthorized,
        statusCode: 401,
      );
    }
    return {'Authorization': 'Bearer $token'};
  }

  Future<void> _handleUnauthorized() async {
    await _storageService.clearSession();
    await onUnauthorized?.call();
  }

  Future<http.Response> _processResponse(http.Response response) async {
    if (response.statusCode == 401) {
      await _handleUnauthorized();
    }
    return response;
  }

  Map<String, String> _headers(Map<String, String>? headers) {
    return <String, String>{
      'Accept': 'application/json',
      'Content-Type': 'application/json',
      ...?headers,
    };
  }

  void close() {
    _client.close();
  }
}
