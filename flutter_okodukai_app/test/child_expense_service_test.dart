import 'dart:convert';

import 'package:flutter_test/flutter_test.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';

import 'package:flutter_okodukai_app/config/api_config.dart';
import 'package:flutter_okodukai_app/services/api_service.dart';
import 'package:flutter_okodukai_app/services/auth_storage_service.dart';
import 'package:flutter_okodukai_app/services/child/child_expense_service.dart';

void main() {
  test('子どもの支出をPOSTして更新後残高を受け取る', () async {
    final client = MockClient((request) async {
      expect(request.url.path, '/api${ApiConfig.childExpensesEndpoint}');
      expect(request.method, 'POST');
      expect(request.headers['Authorization'], 'Bearer child-token');
      expect(jsonDecode(request.body), {
        'description': 'おやつ',
        'amount': 250,
        'used_on': '2026-08-11',
      });

      return http.Response(
        jsonEncode({
          'data': {
            'expense': {
              'id': 10,
              'description': 'おやつ',
              'amount': 250,
              'used_on': '2026-08-11',
            },
            'current_balance': 1000,
          },
        }),
        201,
        headers: {'content-type': 'application/json; charset=utf-8'},
      );
    });
    final service = ChildExpenseService(
      apiService: ApiService(client: client),
      storageService: _TokenStorageService(),
    );

    final result = await service.createExpense(
      description: 'おやつ',
      amount: 250,
      usedOn: DateTime(2026, 8, 11),
    );

    expect(result.description, 'おやつ');
    expect(result.currentBalance, 1000);
  });
}

class _TokenStorageService extends AuthStorageService {
  @override
  Future<String?> readToken() async => 'child-token';
}
