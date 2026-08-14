import 'dart:async';
import 'dart:convert';

import 'package:http/http.dart' as http;

import '../../config/api_config.dart';
import '../../models/parent_regular_allowance.dart';
import '../api_service.dart';
import '../auth_storage_service.dart';

class ParentRegularAllowanceService {
  ParentRegularAllowanceService({
    ApiService? apiService,
    AuthStorageService? storageService,
  }) : _apiService = apiService ?? ApiService(),
       _storageService = storageService ?? AuthStorageService();

  final ApiService _apiService;
  final AuthStorageService _storageService;

  Future<ParentRegularAllowanceData> fetchSetting(int childUserId) async {
    final token = await _token();
    try {
      final response = await _apiService.get(
        ApiConfig.parentRegularAllowanceEndpoint(childUserId),
        headers: {'Authorization': 'Bearer $token'},
      );
      return _decode(response, childUserId);
    } on TimeoutException {
      throw const ParentRegularAllowanceException('通信がタイムアウトしました。');
    } on http.ClientException {
      throw const ParentRegularAllowanceException('サーバーに接続できません。');
    } on FormatException catch (error) {
      throw ParentRegularAllowanceException(
        '定期おこづかい設定の形式が正しくありません。${error.message}',
      );
    }
  }

  Future<ParentRegularAllowanceData> saveSetting({
    required int childUserId,
    required int amount,
    required int paymentDay,
    required bool isActive,
    required bool isEditing,
  }) async {
    if (amount < 1 || paymentDay < 1 || paymentDay > 31) {
      throw const ParentRegularAllowanceException('入力内容が正しくありません。');
    }
    final token = await _token();
    final endpoint = ApiConfig.parentRegularAllowanceEndpoint(childUserId);
    final headers = {'Authorization': 'Bearer $token'};
    final body = {
      'amount': amount,
      'payment_day': paymentDay,
      'is_active': isActive,
    };
    try {
      final response = isEditing
          ? await _apiService.put(endpoint, headers: headers, body: body)
          : await _apiService.post(endpoint, headers: headers, body: body);
      final result = _decode(response, childUserId);
      if (result.setting == null) {
        throw const FormatException('保存後の設定が含まれていません。');
      }
      return result;
    } on TimeoutException {
      throw const ParentRegularAllowanceException('通信がタイムアウトしました。');
    } on http.ClientException {
      throw const ParentRegularAllowanceException('サーバーに接続できません。');
    } on FormatException catch (error) {
      throw ParentRegularAllowanceException(
        '定期おこづかい設定の形式が正しくありません。${error.message}',
      );
    }
  }

  Future<String> _token() async {
    final token = await _storageService.readToken();
    if (token == null || token.isEmpty) {
      throw const ParentRegularAllowanceException('ログイン情報が見つかりません。');
    }
    return token;
  }

  ParentRegularAllowanceData _decode(http.Response response, int childUserId) {
    final decoded = jsonDecode(utf8.decode(response.bodyBytes));
    if (decoded is! Map<String, dynamic>) {
      throw const FormatException('JSONの形式が正しくありません。');
    }
    if (response.statusCode < 200 || response.statusCode >= 300) {
      final message = decoded['message'];
      throw ParentRegularAllowanceException(
        message is String && message.isNotEmpty
            ? message
            : '定期おこづかい設定を保存できませんでした。',
      );
    }
    final result = ParentRegularAllowanceData.fromJson(decoded);
    if (result.childUserId != childUserId) {
      throw const FormatException('指定したお子様と取得結果が一致しません。');
    }
    return result;
  }
}

class ParentRegularAllowanceException implements Exception {
  const ParentRegularAllowanceException(this.message);

  final String message;
}
