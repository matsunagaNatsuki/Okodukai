import 'dart:async';
import 'dart:convert';

import 'package:http/http.dart' as http;

import '../../config/api_config.dart';
import '../../models/parent_chore_setting.dart';
import '../api_service.dart';
import '../auth_storage_service.dart';

class ParentChoreSettingService {
  ParentChoreSettingService({
    ApiService? apiService,
    AuthStorageService? storageService,
  }) : _apiService = apiService ?? ApiService(),
       _storageService = storageService ?? AuthStorageService();

  final ApiService _apiService;
  final AuthStorageService _storageService;

  Future<List<ParentChoreSetting>> fetchSettings() async {
    return _guard(() async {
      final response = await _apiService.get(
        ApiConfig.parentChoreSettingsEndpoint,
        headers: await _headers(),
      );
      final decoded = _decode(response, fallback: 'お手伝い設定を取得できませんでした。');
      return ParentChoreSettingList.fromJson(decoded).settings;
    });
  }

  Future<ParentChoreSetting> createSetting({
    required String description,
    required int rewardAmount,
  }) async {
    _validate(description, rewardAmount);
    return _guard(() async {
      final response = await _apiService.post(
        ApiConfig.parentChoreSettingsEndpoint,
        headers: await _headers(),
        body: {'description': description, 'reward_amount': rewardAmount},
      );
      return _settingFromResponse(response);
    });
  }

  Future<ParentChoreSetting> updateSetting({
    required int settingId,
    required String description,
    required int rewardAmount,
  }) async {
    _validate(description, rewardAmount);
    return _guard(() async {
      final response = await _apiService.put(
        ApiConfig.parentChoreSettingEndpoint(settingId),
        headers: await _headers(),
        body: {'description': description, 'reward_amount': rewardAmount},
      );
      return _settingFromResponse(response);
    });
  }

  Future<void> deleteSetting(int settingId) async {
    await _guard(() async {
      final response = await _apiService.delete(
        ApiConfig.parentChoreSettingEndpoint(settingId),
        headers: await _headers(),
      );
      _decode(response, fallback: 'お手伝い設定を削除できませんでした。');
    });
  }

  ParentChoreSetting _settingFromResponse(http.Response response) {
    final decoded = _decode(response, fallback: 'お手伝い設定を保存できませんでした。');
    final data = decoded['data'] is Map<String, dynamic>
        ? decoded['data'] as Map<String, dynamic>
        : decoded;
    final setting = data['chore_setting'] is Map<String, dynamic>
        ? data['chore_setting'] as Map<String, dynamic>
        : data;
    return ParentChoreSetting.fromJson(setting);
  }

  Future<Map<String, String>> _headers() async {
    final token = await _storageService.readToken();
    if (token == null || token.isEmpty) {
      throw const ParentChoreSettingException('ログイン情報が見つかりません。');
    }
    return {'Authorization': 'Bearer $token'};
  }

  Map<String, dynamic> _decode(
    http.Response response, {
    required String fallback,
  }) {
    if (response.statusCode == 204) return <String, dynamic>{};
    final decoded = jsonDecode(utf8.decode(response.bodyBytes));
    if (decoded is! Map<String, dynamic>) {
      throw const FormatException('JSONの形式が正しくありません。');
    }
    if (response.statusCode < 200 || response.statusCode >= 300) {
      final message = decoded['message'];
      throw ParentChoreSettingException(
        message is String && message.isNotEmpty ? message : fallback,
      );
    }
    return decoded;
  }

  void _validate(String description, int rewardAmount) {
    if (description.trim().isEmpty || rewardAmount < 1) {
      throw const ParentChoreSettingException('入力内容が正しくありません。');
    }
  }

  Future<T> _guard<T>(Future<T> Function() action) async {
    try {
      return await action();
    } on ParentChoreSettingException {
      rethrow;
    } on TimeoutException {
      throw const ParentChoreSettingException('通信がタイムアウトしました。');
    } on http.ClientException {
      throw const ParentChoreSettingException('サーバーに接続できません。');
    } on FormatException catch (error) {
      throw ParentChoreSettingException('お手伝い設定の形式が正しくありません。${error.message}');
    }
  }
}

class ParentChoreSettingException implements Exception {
  const ParentChoreSettingException(this.message);
  final String message;
}
