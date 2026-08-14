import 'dart:async';
import 'dart:convert';

import 'package:http/http.dart' as http;
import 'package:intl/intl.dart';

import '../../config/api_config.dart';
import '../../models/child_expense_result.dart';
import '../api_service.dart';
import '../auth_storage_service.dart';

class ChildExpenseService {
  ChildExpenseService({
    ApiService? apiService,
    AuthStorageService? storageService,
  }) : _apiService = apiService ?? ApiService(),
       _storageService = storageService ?? AuthStorageService();

  final ApiService _apiService;
  final AuthStorageService _storageService;

  Future<ChildExpenseResult> createExpense({
    required String description,
    required int amount,
    required DateTime usedOn,
  }) async {
    try {
      final token = await _storageService.readToken();
      if (token == null || token.isEmpty) {
        throw const ChildExpenseException('ログイン情報が見つかりません。');
      }

      final response = await _apiService.post(
        ApiConfig.childExpensesEndpoint,
        headers: {'Authorization': 'Bearer $token'},
        body: {
          'description': description,
          'amount': amount,
          'used_on': DateFormat('yyyy-MM-dd').format(usedOn),
        },
      );
      final decoded = jsonDecode(utf8.decode(response.bodyBytes));
      if (decoded is! Map<String, dynamic>) {
        throw const FormatException('JSONの形式が正しくありません。');
      }
      if (response.statusCode < 200 || response.statusCode >= 300) {
        throw ChildExpenseException(_errorMessage(decoded));
      }
      return ChildExpenseResult.fromJson(decoded);
    } on TimeoutException {
      throw const ChildExpenseException('通信がタイムアウトしました。');
    } on http.ClientException {
      throw const ChildExpenseException('サーバーに接続できません。');
    } on FormatException catch (error) {
      throw ChildExpenseException('登録結果の形式が正しくありません。${error.message}');
    }
  }

  String _errorMessage(Map<String, dynamic> json) {
    final errors = json['errors'];
    if (errors is Map<String, dynamic>) {
      for (final value in errors.values) {
        if (value is List && value.isNotEmpty && value.first is String) {
          return value.first as String;
        }
      }
    }
    final message = json['message'];
    return message is String && message.isNotEmpty ? message : '支出を登録できませんでした。';
  }
}

class ChildExpenseException implements Exception {
  const ChildExpenseException(this.message);

  final String message;
}
