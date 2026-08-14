import 'dart:convert';

import 'package:flutter_test/flutter_test.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';

import 'package:flutter_okodukai_app/config/api_config.dart';
import 'package:flutter_okodukai_app/services/api_service.dart';
import 'package:flutter_okodukai_app/services/auth_storage_service.dart';
import 'package:flutter_okodukai_app/services/child/child_chore_history_service.dart';

void main() {
  test('お手伝い履歴のページネーションをModelへ変換する', () async {
    final client = MockClient((request) async {
      expect(request.url.path, '/api${ApiConfig.childChoreHistoryEndpoint}');
      expect(request.url.queryParameters['page'], '2');
      expect(request.headers['Authorization'], 'Bearer child-token');
      return http.Response(
        jsonEncode({
          'data': {
            'chores': {
              'data': [
                {
                  'id': 3,
                  'description': '食器洗い',
                  'reward_amount': 100,
                  'completed_on': '2026-08-11',
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
    final service = ChildChoreHistoryService(
      apiService: ApiService(client: client),
      storageService: _TokenStorageService(),
    );

    final page = await service.fetchChores(page: 2);

    expect(page.currentPage, 2);
    expect(page.lastPage, 3);
    expect(page.hasNextPage, isTrue);
    expect(page.chores.single.description, '食器洗い');
    expect(page.chores.single.rewardAmount, 100);
  });
}

class _TokenStorageService extends AuthStorageService {
  @override
  Future<String?> readToken() async => 'child-token';
}
