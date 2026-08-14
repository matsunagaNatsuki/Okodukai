import 'dart:async';
import 'dart:convert';

import 'package:http/http.dart' as http;

import '../../config/api_config.dart';
import '../../models/child_home_data.dart';
import '../api_service.dart';
import '../auth_storage_service.dart';

class ChildHomeService {
  ChildHomeService({ApiService? apiService, AuthStorageService? storageService})
    : _apiService = apiService ?? ApiService(),
      _storageService = storageService ?? AuthStorageService();

  final ApiService _apiService;
  final AuthStorageService _storageService;

  Future<ChildHomeData> fetchHome() async {
    try {
      final token = await _storageService.readToken();
      if (token == null || token.isEmpty) {
        throw const ChildHomeException('ログイン情報が見つかりません。');
      }

      final response = await _apiService.get(
        ApiConfig.childHomeEndpoint,
        headers: {'Authorization': 'Bearer $token'},
      );
      final decoded = jsonDecode(utf8.decode(response.bodyBytes));
      if (decoded is! Map<String, dynamic>) {
        throw const FormatException('JSONの形式が正しくありません。');
      }
      if (response.statusCode < 200 || response.statusCode >= 300) {
        final message = decoded['message'];
        throw ChildHomeException(
          message is String && message.isNotEmpty
              ? message
              : 'ホーム情報を取得できませんでした。',
        );
      }
      return ChildHomeData.fromJson(decoded);
    } on TimeoutException {
      throw const ChildHomeException('通信がタイムアウトしました。');
    } on http.ClientException {
      throw const ChildHomeException('サーバーに接続できません。');
    } on FormatException catch (error) {
      throw ChildHomeException('ホーム情報の形式が正しくありません。${error.message}');
    }
  }
}

class ChildHomeException implements Exception {
  const ChildHomeException(this.message);

  final String message;
}
