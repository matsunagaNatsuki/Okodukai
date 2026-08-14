import 'dart:async';
import 'dart:convert';

import 'package:http/http.dart' as http;

import '../../config/api_config.dart';
import '../../models/parent_child_management_data.dart';
import '../api_service.dart';
import '../auth_storage_service.dart';

class ParentChildManagementService {
  ParentChildManagementService({
    ApiService? apiService,
    AuthStorageService? storageService,
  }) : _apiService = apiService ?? ApiService(),
       _storageService = storageService ?? AuthStorageService();

  final ApiService _apiService;
  final AuthStorageService _storageService;

  Future<ParentChildManagementData> fetchChild(int childUserId) async {
    if (childUserId <= 0) {
      throw const ParentChildManagementException('お子様の指定が正しくありません。');
    }
    try {
      final token = await _storageService.readToken();
      if (token == null || token.isEmpty) {
        throw const ParentChildManagementException('ログイン情報が見つかりません。');
      }
      final response = await _apiService.get(
        ApiConfig.parentChildDetailEndpoint(childUserId),
        headers: {'Authorization': 'Bearer $token'},
      );
      final decoded = jsonDecode(utf8.decode(response.bodyBytes));
      if (decoded is! Map<String, dynamic>) {
        throw const FormatException('JSONの形式が正しくありません。');
      }
      if (response.statusCode < 200 || response.statusCode >= 300) {
        final message = decoded['message'];
        throw ParentChildManagementException(
          message is String && message.isNotEmpty
              ? message
              : 'お子様の情報を取得できませんでした。',
        );
      }
      final result = ParentChildManagementData.fromJson(decoded);
      if (result.childUserId != childUserId) {
        throw const FormatException('指定したお子様と取得結果が一致しません。');
      }
      return result;
    } on TimeoutException {
      throw const ParentChildManagementException('通信がタイムアウトしました。');
    } on http.ClientException {
      throw const ParentChildManagementException('サーバーに接続できません。');
    } on FormatException catch (error) {
      throw ParentChildManagementException(
        'お子様情報の形式が正しくありません。${error.message}',
      );
    }
  }
}

class ParentChildManagementException implements Exception {
  const ParentChildManagementException(this.message);

  final String message;
}
