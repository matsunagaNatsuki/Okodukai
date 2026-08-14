import 'dart:async';
import 'dart:convert';

import 'package:http/http.dart' as http;
import 'package:intl/intl.dart';

import '../../config/api_config.dart';
import '../../models/parent_chore_record.dart';
import '../api_service.dart';
import '../auth_storage_service.dart';

class ParentChoreRecordService {
  ParentChoreRecordService({
    ApiService? apiService,
    AuthStorageService? storageService,
  }) : _apiService = apiService ?? ApiService(),
       _storageService = storageService ?? AuthStorageService();

  final ApiService _apiService;
  final AuthStorageService _storageService;

  Future<ParentChoreRecordFormData> fetchFormData(int childUserId) async {
    return _guard(() async {
      final response = await _apiService.get(
        ApiConfig.parentChildChoreRecordsEndpoint(childUserId),
        headers: await _headers(),
      );
      final decoded = _decode(response, 'お手伝い設定を取得できませんでした。');
      final result = ParentChoreRecordFormData.fromJson(decoded);
      _verifyChildId(result.childUserId, childUserId);
      return result;
    });
  }

  Future<ParentChoreRecordResult> createRecord({
    required int childUserId,
    required int choreSettingId,
    required int rewardAmount,
    required DateTime performedOn,
  }) async {
    if (childUserId < 1 || choreSettingId < 1 || rewardAmount < 1) {
      throw const ParentChoreRecordException('入力内容が正しくありません。');
    }
    return _guard(() async {
      final response = await _apiService.post(
        ApiConfig.parentChildChoreRecordsEndpoint(childUserId),
        headers: await _headers(),
        body: {
          'chore_setting_id': choreSettingId,
          'reward_amount': rewardAmount,
          'performed_on': DateFormat('yyyy-MM-dd').format(performedOn),
        },
      );
      final decoded = _decode(response, 'お手伝い実績を登録できませんでした。');
      return ParentChoreRecordResult.fromJson(decoded);
    });
  }

  Future<Map<String, String>> _headers() async {
    final token = await _storageService.readToken();
    if (token == null || token.isEmpty) {
      throw const ParentChoreRecordException('ログイン情報が見つかりません。');
    }
    return {'Authorization': 'Bearer $token'};
  }

  Map<String, dynamic> _decode(http.Response response, String fallback) {
    final decoded = jsonDecode(utf8.decode(response.bodyBytes));
    if (decoded is! Map<String, dynamic>) {
      throw const FormatException('JSONの形式が正しくありません。');
    }
    if (response.statusCode < 200 || response.statusCode >= 300) {
      final message = decoded['message'];
      throw ParentChoreRecordException(
        message is String && message.isNotEmpty ? message : fallback,
      );
    }
    return decoded;
  }

  void _verifyChildId(int responseId, int requestedId) {
    if (responseId != requestedId) {
      throw const FormatException('指定したお子様と取得結果が一致しません。');
    }
  }

  Future<T> _guard<T>(Future<T> Function() action) async {
    try {
      return await action();
    } on ParentChoreRecordException {
      rethrow;
    } on TimeoutException {
      throw const ParentChoreRecordException('通信がタイムアウトしました。');
    } on http.ClientException {
      throw const ParentChoreRecordException('サーバーに接続できません。');
    } on FormatException catch (error) {
      throw ParentChoreRecordException('お手伝い実績の形式が正しくありません。${error.message}');
    }
  }
}

class ParentChoreRecordException implements Exception {
  const ParentChoreRecordException(this.message);
  final String message;
}
