import 'dart:convert';

import 'package:flutter_test/flutter_test.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';

import 'package:flutter_okodukai_app/config/api_config.dart';
import 'package:flutter_okodukai_app/services/api_service.dart';
import 'package:flutter_okodukai_app/services/auth_storage_service.dart';
import 'package:flutter_okodukai_app/services/child/child_expense_history_service.dart';

void main() {
  test('Laravelの支出履歴ページネーションをModelへ変換する', () async {
    final client = MockClient((request) async {
      expect(request.url.path, '/api${ApiConfig.childExpensesEndpoint}');
      expect(request.url.queryParameters['page'], '2');
      expect(request.headers['Authorization'], 'Bearer child-token');

      return http.Response(
        jsonEncode({
          'data': {
            'current_balance': 1250,
            'expenses': {
              'data': [
                {
                  'id': 2,
                  'description': 'ジュース',
                  'amount': 120,
                  'used_on': '2026-08-10',
                },
              ],
              'current_page': 2,
              'last_page': 3,
            },
          },
        }),
        200,
        headers: {'content-type': 'application/json; charset=utf-8'},
      );
    });
    final service = ChildExpenseHistoryService(
      apiService: ApiService(client: client),
      storageService: _TokenStorageService(),
    );

    final page = await service.fetchExpenses(page: 2);

    expect(page.currentBalance, 1250);
    expect(page.currentPage, 2);
    expect(page.lastPage, 3);
    expect(page.hasNextPage, isTrue);
    expect(page.expenses.single.description, 'ジュース');
  });
}

class _TokenStorageService extends AuthStorageService {
  @override
  Future<String?> readToken() async => 'child-token';
}
