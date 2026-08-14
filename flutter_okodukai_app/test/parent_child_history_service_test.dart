import 'dart:convert';

import 'package:flutter_test/flutter_test.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';

import 'package:flutter_okodukai_app/config/api_config.dart';
import 'package:flutter_okodukai_app/services/api_service.dart';
import 'package:flutter_okodukai_app/services/auth_storage_service.dart';
import 'package:flutter_okodukai_app/services/parent/parent_child_history_service.dart';

void main() {
  test('保護者用支出履歴のページネーションを変換する', () async {
    final service = _service((request) async {
      expect(
        request.url.path,
        '/api${ApiConfig.parentChildExpensesEndpoint(12)}',
      );
      expect(request.url.queryParameters['page'], '2');
      expect(request.headers['Authorization'], 'Bearer parent-token');
      return _response('expenses', {
        'id': 8,
        'description': '本',
        'amount': 500,
        'used_on': '2026-08-10',
      });
    });

    final page = await service.fetchExpenses(childUserId: 12, page: 2);

    expect(page.childName, 'たろう');
    expect(page.currentBalance, 1250);
    expect(page.currentPage, 2);
    expect(page.lastPage, 3);
    expect(page.expenses.single.description, '本');
  });

  test('保護者用お手伝い履歴のページネーションを変換する', () async {
    final service = _service((request) async {
      expect(
        request.url.path,
        '/api${ApiConfig.parentChildChoreHistoryEndpoint(12)}',
      );
      return _response('chores', {
        'id': 9,
        'description': 'お皿洗い',
        'reward_amount': 100,
        'performed_on': '2026-08-11',
      });
    });

    final page = await service.fetchChores(childUserId: 12, page: 2);

    expect(page.childName, 'たろう');
    expect(page.currentBalance, 1250);
    expect(page.chores.single.rewardAmount, 100);
  });
}

ParentChildHistoryService _service(
  Future<http.Response> Function(http.Request) handler,
) => ParentChildHistoryService(
  apiService: ApiService(client: MockClient(handler)),
  storageService: _TokenStorageService(),
);

http.Response _response(String key, Map<String, dynamic> item) => http.Response(
  jsonEncode({
    'data': {
      'child': {'id': 12, 'name': 'たろう'},
      'current_balance': 1250,
      key: {
        'data': [item],
        'current_page': 2,
        'last_page': 3,
      },
    },
  }),
  200,
  headers: {'content-type': 'application/json; charset=utf-8'},
);

class _TokenStorageService extends AuthStorageService {
  @override
  Future<String?> readToken() async => 'parent-token';
}
