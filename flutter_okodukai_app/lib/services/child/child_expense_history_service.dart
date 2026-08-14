import 'dart:async';
import 'dart:convert';

import 'package:http/http.dart' as http;

import '../../config/api_config.dart';
import '../../models/child_expense_history.dart';
import '../api_service.dart';
import '../auth_storage_service.dart';

class ChildExpenseHistoryService {
  ChildExpenseHistoryService({
    ApiService? apiService,
    AuthStorageService? storageService,
  }) : _apiService = apiService ?? ApiService(),
       _storageService = storageService ?? AuthStorageService();

  final ApiService _apiService;
  final AuthStorageService _storageService;

  Future<ChildExpenseHistoryPage> fetchExpenses({int page = 1}) async {
    try {
      final token = await _storageService.readToken();
      if (token == null || token.isEmpty) {
        throw const ChildExpenseHistoryException('ログイン情報が見つかりません。');
      }

      final response = await _apiService.get(
        '${ApiConfig.childExpensesEndpoint}?page=$page',
        headers: {'Authorization': 'Bearer $token'},
      );
      final decoded = jsonDecode(utf8.decode(response.bodyBytes));
      if (decoded is! Map<String, dynamic>) {
        throw const FormatException('JSONの形式が正しくありません。');
      }
      if (response.statusCode < 200 || response.statusCode >= 300) {
        final message = decoded['message'];
        throw ChildExpenseHistoryException(
          message is String && message.isNotEmpty
              ? message
              : '支出履歴を取得できませんでした。',
        );
      }
      return ChildExpenseHistoryPage.fromJson(decoded);
    } on TimeoutException {
      throw const ChildExpenseHistoryException('通信がタイムアウトしました。');
    } on http.ClientException {
      throw const ChildExpenseHistoryException('サーバーに接続できません。');
    } on FormatException catch (error) {
      throw ChildExpenseHistoryException('支出履歴の形式が正しくありません。${error.message}');
    }
  }
}

class ChildExpenseHistoryException implements Exception {
  const ChildExpenseHistoryException(this.message);

  final String message;
}
