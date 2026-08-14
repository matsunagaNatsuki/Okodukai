import 'dart:async';
import 'dart:convert';

import 'package:http/http.dart' as http;

import '../../config/api_config.dart';
import '../../models/parent_child_history.dart';
import '../api_service.dart';
import '../auth_storage_service.dart';

class ParentChildHistoryService {
  ParentChildHistoryService({
    ApiService? apiService,
    AuthStorageService? storageService,
  }) : _apiService = apiService ?? ApiService(),
       _storageService = storageService ?? AuthStorageService();

  final ApiService _apiService;
  final AuthStorageService _storageService;

  Future<ParentExpenseHistoryPage> fetchExpenses({
    required int childUserId,
    int page = 1,
  }) => _fetch(
    childUserId: childUserId,
    page: page,
    endpoint: ApiConfig.parentChildExpensesEndpoint(childUserId),
    label: '支出履歴',
    parse: ParentExpenseHistoryPage.fromJson,
    responseChildId: (result) => result.childUserId,
  );

  Future<ParentChoreHistoryPage> fetchChores({
    required int childUserId,
    int page = 1,
  }) => _fetch(
    childUserId: childUserId,
    page: page,
    endpoint: ApiConfig.parentChildChoreHistoryEndpoint(childUserId),
    label: 'お手伝い履歴',
    parse: ParentChoreHistoryPage.fromJson,
    responseChildId: (result) => result.childUserId,
  );

  Future<T> _fetch<T>({
    required int childUserId,
    required int page,
    required String endpoint,
    required String label,
    required T Function(Map<String, dynamic>) parse,
    required int Function(T) responseChildId,
  }) async {
    try {
      final token = await _storageService.readToken();
      if (token == null || token.isEmpty) {
        throw const ParentChildHistoryException('ログイン情報が見つかりません。');
      }
      final response = await _apiService.get(
        '$endpoint?page=$page',
        headers: {'Authorization': 'Bearer $token'},
      );
      final decoded = jsonDecode(utf8.decode(response.bodyBytes));
      if (decoded is! Map<String, dynamic>) {
        throw const FormatException('JSONの形式が正しくありません。');
      }
      if (response.statusCode < 200 || response.statusCode >= 300) {
        final message = decoded['message'];
        throw ParentChildHistoryException(
          message is String && message.isNotEmpty
              ? message
              : '$labelを取得できませんでした。',
        );
      }
      final result = parse(decoded);
      if (responseChildId(result) != childUserId) {
        throw const FormatException('指定したお子様と取得結果が一致しません。');
      }
      return result;
    } on ParentChildHistoryException {
      rethrow;
    } on TimeoutException {
      throw const ParentChildHistoryException('通信がタイムアウトしました。');
    } on http.ClientException {
      throw const ParentChildHistoryException('サーバーに接続できません。');
    } on FormatException catch (error) {
      throw ParentChildHistoryException('$labelの形式が正しくありません。${error.message}');
    }
  }
}

class ParentChildHistoryException implements Exception {
  const ParentChildHistoryException(this.message);
  final String message;
}
