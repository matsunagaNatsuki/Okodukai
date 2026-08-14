import 'dart:async';
import 'dart:convert';

import 'package:http/http.dart' as http;

import '../../config/api_config.dart';
import '../../models/parent_child_saving_goal.dart';
import '../api_service.dart';
import '../auth_storage_service.dart';

class ParentChildSavingGoalService {
  ParentChildSavingGoalService({
    ApiService? apiService,
    AuthStorageService? storageService,
  }) : _apiService = apiService ?? ApiService(),
       _storageService = storageService ?? AuthStorageService();

  final ApiService _apiService;
  final AuthStorageService _storageService;

  Future<ParentChildSavingGoalData> fetchSavingGoal(int childUserId) async {
    try {
      final token = await _storageService.readToken();
      if (token == null || token.isEmpty) {
        throw const ParentChildSavingGoalException('ログイン情報が見つかりません。');
      }
      final response = await _apiService.get(
        ApiConfig.parentChildSavingGoalEndpoint(childUserId),
        headers: {'Authorization': 'Bearer $token'},
      );
      final decoded = jsonDecode(utf8.decode(response.bodyBytes));
      if (decoded is! Map<String, dynamic>) {
        throw const FormatException('JSONの形式が正しくありません。');
      }
      if (response.statusCode < 200 || response.statusCode >= 300) {
        final message = decoded['message'];
        throw ParentChildSavingGoalException(
          message is String && message.isNotEmpty
              ? message
              : '貯金目標を取得できませんでした。',
        );
      }
      final result = ParentChildSavingGoalData.fromJson(decoded);
      if (result.childUserId != childUserId) {
        throw const FormatException('指定したお子様と取得結果が一致しません。');
      }
      return result;
    } on ParentChildSavingGoalException {
      rethrow;
    } on TimeoutException {
      throw const ParentChildSavingGoalException('通信がタイムアウトしました。');
    } on http.ClientException {
      throw const ParentChildSavingGoalException('サーバーに接続できません。');
    } on FormatException catch (error) {
      throw ParentChildSavingGoalException('貯金目標の形式が正しくありません。${error.message}');
    }
  }
}

class ParentChildSavingGoalException implements Exception {
  const ParentChildSavingGoalException(this.message);
  final String message;
}
