import 'dart:async';
import 'dart:convert';

import 'package:http/http.dart' as http;

import '../../config/api_config.dart';
import '../../models/child_chore_history.dart';
import '../api_service.dart';
import '../auth_storage_service.dart';

class ChildChoreHistoryService {
  ChildChoreHistoryService({
    ApiService? apiService,
    AuthStorageService? storageService,
  }) : _apiService = apiService ?? ApiService(),
       _storageService = storageService ?? AuthStorageService();

  final ApiService _apiService;
  final AuthStorageService _storageService;

  Future<ChildChoreHistoryPage> fetchChores({int page = 1}) async {
    try {
      final token = await _storageService.readToken();
      if (token == null || token.isEmpty) {
        throw const ChildChoreHistoryException('ログイン情報が見つかりません。');
      }
      final response = await _apiService.get(
        '${ApiConfig.childChoreHistoryEndpoint}?page=$page',
        headers: {'Authorization': 'Bearer $token'},
      );
      final decoded = jsonDecode(utf8.decode(response.bodyBytes));
      if (decoded is! Map<String, dynamic>) {
        throw const FormatException('JSONの形式が正しくありません。');
      }
      if (response.statusCode < 200 || response.statusCode >= 300) {
        final message = decoded['message'];
        throw ChildChoreHistoryException(
          message is String && message.isNotEmpty
              ? message
              : 'お手伝い履歴を取得できませんでした。',
        );
      }
      return ChildChoreHistoryPage.fromJson(decoded);
    } on TimeoutException {
      throw const ChildChoreHistoryException('通信がタイムアウトしました。');
    } on http.ClientException {
      throw const ChildChoreHistoryException('サーバーに接続できません。');
    } on FormatException catch (error) {
      throw ChildChoreHistoryException('お手伝い履歴の形式が正しくありません。${error.message}');
    }
  }
}

class ChildChoreHistoryException implements Exception {
  const ChildChoreHistoryException(this.message);

  final String message;
}
