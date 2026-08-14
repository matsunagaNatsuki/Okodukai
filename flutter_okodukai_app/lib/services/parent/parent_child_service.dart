import 'dart:async';
import 'dart:convert';

import 'package:http/http.dart' as http;

import '../../config/api_config.dart';
import '../../models/parent_child.dart';
import '../api_service.dart';
import '../auth_storage_service.dart';

class ParentChildService {
  ParentChildService({
    ApiService? apiService,
    AuthStorageService? storageService,
  }) : _apiService = apiService ?? ApiService(),
       _storageService = storageService ?? AuthStorageService();

  final ApiService _apiService;
  final AuthStorageService _storageService;

  Future<List<ParentChild>> fetchChildren() async {
    try {
      final token = await _storageService.readToken();
      if (token == null || token.isEmpty) {
        throw const ParentChildException('ログイン情報が見つかりません。');
      }
      final response = await _apiService.get(
        ApiConfig.parentChildrenEndpoint,
        headers: {'Authorization': 'Bearer $token'},
      );
      final decoded = jsonDecode(utf8.decode(response.bodyBytes));
      if (decoded is! Map<String, dynamic>) {
        throw const FormatException('JSONの形式が正しくありません。');
      }
      if (response.statusCode < 200 || response.statusCode >= 300) {
        final message = decoded['message'];
        throw ParentChildException(
          message is String && message.isNotEmpty
              ? message
              : 'お子様一覧を取得できませんでした。',
        );
      }
      return ParentChildList.fromJson(decoded).children;
    } on TimeoutException {
      throw const ParentChildException('通信がタイムアウトしました。');
    } on http.ClientException {
      throw const ParentChildException('サーバーに接続できません。');
    } on FormatException catch (error) {
      throw ParentChildException('お子様一覧の形式が正しくありません。${error.message}');
    }
  }
}

class ParentChildException implements Exception {
  const ParentChildException(this.message);

  final String message;
}
