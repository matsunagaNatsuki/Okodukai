import 'dart:async';
import 'dart:convert';

import 'package:http/http.dart' as http;

import '../config/api_config.dart';
import '../models/child_login_result.dart';
import '../models/parent_registration_result.dart';
import '../models/parent_login_result.dart';
import 'api_service.dart';
import 'auth_storage_service.dart';

class AuthService {
  AuthService({ApiService? apiService, AuthStorageService? storageService})
    : _apiService = apiService ?? ApiService(),
      _storageService = storageService ?? AuthStorageService();

  final ApiService _apiService;
  final AuthStorageService _storageService;

  Future<String?> getSavedToken() => _storageService.readToken();

  Future<String?> getSavedRole() => _storageService.readUserRole();

  Future<void> clearLocalSession() => _storageService.clearSession();

  Future<void> logout() async {
    try {
      final token = await getSavedToken();
      if (token == null || token.isEmpty) return;

      final response = await _apiService.post(
        ApiConfig.logoutEndpoint,
        headers: {'Authorization': 'Bearer $token'},
      );
      if (response.statusCode < 200 || response.statusCode >= 300) {
        throw const AuthException('ログアウト通信に失敗しました。');
      }
    } on TimeoutException {
      throw const AuthException('ログアウト通信がタイムアウトしました。');
    } on http.ClientException {
      throw const AuthException('ログアウト通信に失敗しました。');
    } finally {
      await clearLocalSession();
    }
  }

  Future<RememberedChildLogin> getRememberedChildLogin() {
    return _storageService.readRememberedChildLogin();
  }

  Future<ChildLoginResult> loginChild({
    required String familyCode,
    required String loginId,
    required String password,
    required bool rememberLogin,
  }) async {
    try {
      final response = await _apiService.post(
        ApiConfig.childLoginEndpoint,
        body: {
          'family_code': familyCode,
          'login_id': loginId,
          'password': password,
        },
      );
      final json = _decodeResponse(response);

      if (response.statusCode < 200 || response.statusCode >= 300) {
        throw AuthException(_errorMessage(json, fallback: 'ログインに失敗しました。'));
      }

      final result = ChildLoginResult.fromJson(json);
      await _storageService.saveChildSession(
        token: result.token,
        childId: result.child.id,
        familyCode: familyCode,
      );
      await _storageService.setRememberedChildLogin(
        familyCode: rememberLogin ? familyCode : null,
        loginId: rememberLogin ? loginId : null,
      );
      return result;
    } on ChildRoleException catch (error) {
      throw AuthException(error.message);
    } on TimeoutException {
      throw const AuthException('通信がタイムアウトしました。時間をおいてお試しください。');
    } on http.ClientException {
      throw const AuthException('サーバーに接続できません。通信環境をご確認ください。');
    } on FormatException catch (error) {
      throw AuthException('サーバーから正しい応答を受け取れませんでした。${error.message}');
    }
  }

  Future<String?> getRememberedParentEmail() {
    return _storageService.readRememberedParentEmail();
  }

  Future<void> requestPasswordResetCode({required String email}) async {
    await _passwordResetRequest(
      ApiConfig.passwordResetCodeEndpoint,
      body: {'email': email},
      fallback: '確認コードを送信できませんでした。',
    );
  }

  Future<String> verifyPasswordResetCode({
    required String email,
    required String code,
  }) async {
    final json = await _passwordResetRequest(
      ApiConfig.passwordResetVerifyEndpoint,
      body: {'email': email, 'code': code},
      fallback: '確認コードを確認できませんでした。',
    );
    final data = json['data'] is Map<String, dynamic>
        ? json['data'] as Map<String, dynamic>
        : json;
    final resetToken = data['reset_token'] ?? data['token'];
    if (resetToken is! String || resetToken.isEmpty) {
      throw const AuthException('再設定に必要な情報を取得できませんでした。もう一度お試しください。');
    }
    return resetToken;
  }

  Future<void> resetPassword({
    required String email,
    required String resetToken,
    required String password,
    required String passwordConfirmation,
  }) async {
    await _passwordResetRequest(
      ApiConfig.passwordResetEndpoint,
      body: {
        'email': email,
        'reset_token': resetToken,
        'password': password,
        'password_confirmation': passwordConfirmation,
      },
      fallback: 'パスワードを再設定できませんでした。',
    );
  }

  Future<Map<String, dynamic>> _passwordResetRequest(
    String endpoint, {
    required Map<String, dynamic> body,
    required String fallback,
  }) async {
    try {
      final response = await _apiService.post(endpoint, body: body);
      final json = _decodeResponse(response);
      if (response.statusCode < 200 || response.statusCode >= 300) {
        throw AuthException(_errorMessage(json, fallback: fallback));
      }
      return json;
    } on AuthException {
      rethrow;
    } on TimeoutException {
      throw const AuthException('通信がタイムアウトしました。時間をおいてお試しください。');
    } on http.ClientException {
      throw const AuthException('サーバーに接続できません。通信環境をご確認ください。');
    } on FormatException catch (error) {
      throw AuthException('サーバーから正しい応答を受け取れませんでした。${error.message}');
    }
  }

  Future<ParentLoginResult> loginParent({
    required String email,
    required String password,
    required bool rememberEmail,
  }) async {
    try {
      final response = await _apiService.post(
        ApiConfig.parentLoginEndpoint,
        body: {'email': email, 'password': password},
      );
      final json = _decodeResponse(response);

      if (response.statusCode < 200 || response.statusCode >= 300) {
        throw AuthException(_errorMessage(json, fallback: 'ログインに失敗しました。'));
      }

      final result = ParentLoginResult.fromJson(json);
      await _storageService.saveParentSession(
        token: result.token,
        parentId: result.parent.id,
        familyCode: result.familyCode,
      );
      await _storageService.setRememberedParentEmail(
        rememberEmail ? email : null,
      );
      return result;
    } on ParentRoleException catch (error) {
      throw AuthException(error.message);
    } on TimeoutException {
      throw const AuthException('通信がタイムアウトしました。時間をおいてお試しください。');
    } on http.ClientException {
      throw const AuthException('サーバーに接続できません。通信環境をご確認ください。');
    } on FormatException catch (error) {
      throw AuthException('サーバーから正しい応答を受け取れませんでした。${error.message}');
    }
  }

  Future<ParentRegistrationResult> registerParent({
    required String name,
    required String email,
    required String password,
    required String passwordConfirmation,
  }) async {
    try {
      final response = await _apiService.post(
        ApiConfig.parentRegisterEndpoint,
        body: {
          'name': name,
          'email': email,
          'password': password,
          'password_confirmation': passwordConfirmation,
        },
      );
      final json = _decodeResponse(response);

      if (response.statusCode < 200 || response.statusCode >= 300) {
        throw AuthException(_errorMessage(json, fallback: '登録に失敗しました。'));
      }

      final result = ParentRegistrationResult.fromJson(json);
      await _storageService.saveParentSession(
        token: result.token,
        parentId: result.parent.id,
        familyCode: result.familyCode,
      );
      return result;
    } on TimeoutException {
      throw const AuthException('通信がタイムアウトしました。時間をおいてお試しください。');
    } on http.ClientException {
      throw const AuthException('サーバーに接続できません。通信環境をご確認ください。');
    } on FormatException catch (error) {
      throw AuthException('サーバーから正しい応答を受け取れませんでした。${error.message}');
    }
  }

  Map<String, dynamic> _decodeResponse(http.Response response) {
    final decoded = jsonDecode(utf8.decode(response.bodyBytes));
    if (decoded is! Map<String, dynamic>) {
      throw const FormatException('JSONの形式が正しくありません。');
    }
    return decoded;
  }

  String _errorMessage(Map<String, dynamic> json, {required String fallback}) {
    final errors = json['errors'];
    if (errors is Map<String, dynamic>) {
      for (final value in errors.values) {
        if (value is List && value.isNotEmpty && value.first is String) {
          return value.first as String;
        }
      }
    }

    final message = json['message'];
    if (message is String && message.isNotEmpty) {
      return message;
    }

    return '$fallback入力内容をご確認ください。';
  }
}

class AuthException implements Exception {
  const AuthException(this.message);

  final String message;
}
