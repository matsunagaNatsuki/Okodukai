import 'dart:async';
import 'dart:convert';

import 'package:http/http.dart' as http;

import '../../config/api_config.dart';
import '../../models/child_saving_goal.dart';
import '../api_service.dart';
import '../auth_storage_service.dart';

class ChildSavingGoalService {
  ChildSavingGoalService({
    ApiService? apiService,
    AuthStorageService? storageService,
  }) : _apiService = apiService ?? ApiService(),
       _storageService = storageService ?? AuthStorageService();

  final ApiService _apiService;
  final AuthStorageService _storageService;

  Future<ChildSavingGoalData> fetchSavingGoal() async {
    final token = await _token();
    try {
      final response = await _apiService.get(
        ApiConfig.childSavingGoalEndpoint,
        headers: {'Authorization': 'Bearer $token'},
      );
      return _decodeData(response);
    } on TimeoutException {
      throw const ChildSavingGoalException('通信がタイムアウトしました。');
    } on http.ClientException {
      throw const ChildSavingGoalException('サーバーに接続できません。');
    } on FormatException catch (error) {
      throw ChildSavingGoalException('貯金目標の形式が正しくありません。${error.message}');
    }
  }

  Future<ChildSavingGoalData> saveSavingGoal({
    required String wantedItem,
    required int targetAmount,
    required bool isEditing,
  }) async {
    final token = await _token();
    try {
      final body = {'wanted_item': wantedItem, 'target_amount': targetAmount};
      final headers = {'Authorization': 'Bearer $token'};
      final response = isEditing
          ? await _apiService.put(
              ApiConfig.childSavingGoalEndpoint,
              headers: headers,
              body: body,
            )
          : await _apiService.post(
              ApiConfig.childSavingGoalEndpoint,
              headers: headers,
              body: body,
            );
      return _decodeData(response);
    } on TimeoutException {
      throw const ChildSavingGoalException('通信がタイムアウトしました。');
    } on http.ClientException {
      throw const ChildSavingGoalException('サーバーに接続できません。');
    } on FormatException catch (error) {
      throw ChildSavingGoalException('貯金目標の形式が正しくありません。${error.message}');
    }
  }

  Future<String> _token() async {
    final token = await _storageService.readToken();
    if (token == null || token.isEmpty) {
      throw const ChildSavingGoalException('ログイン情報が見つかりません。');
    }
    return token;
  }

  ChildSavingGoalData _decodeData(http.Response response) {
    final decoded = jsonDecode(utf8.decode(response.bodyBytes));
    if (decoded is! Map<String, dynamic>) {
      throw const FormatException('JSONの形式が正しくありません。');
    }
    if (response.statusCode < 200 || response.statusCode >= 300) {
      final message = decoded['message'];
      throw ChildSavingGoalException(
        message is String && message.isNotEmpty ? message : '貯金目標を保存できませんでした。',
      );
    }
    return ChildSavingGoalData.fromJson(decoded);
  }
}

class ChildSavingGoalException implements Exception {
  const ChildSavingGoalException(this.message);

  final String message;
}
