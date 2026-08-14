import 'dart:convert';

import 'package:flutter_test/flutter_test.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';

import 'package:flutter_okodukai_app/config/api_config.dart';
import 'package:flutter_okodukai_app/services/api_service.dart';
import 'package:flutter_okodukai_app/services/auth_storage_service.dart';
import 'package:flutter_okodukai_app/services/parent/parent_child_saving_goal_service.dart';

void main() {
  test('選択した子どもの貯金目標を取得して達成率を計算する', () async {
    final client = MockClient((request) async {
      expect(
        request.url.path,
        '/api${ApiConfig.parentChildSavingGoalEndpoint(12)}',
      );
      expect(request.method, 'GET');
      expect(request.headers['Authorization'], 'Bearer parent-token');
      return http.Response(
        jsonEncode({
          'data': {
            'child': {'id': 12, 'name': 'たろう'},
            'current_balance': 2000,
            'saving_goal': {
              'id': 4,
              'wanted_item': 'ゲーム',
              'target_amount': 5000,
            },
          },
        }),
        200,
        headers: {'content-type': 'application/json; charset=utf-8'},
      );
    });
    final service = ParentChildSavingGoalService(
      apiService: ApiService(client: client),
      storageService: _TokenStorageService(),
    );

    final data = await service.fetchSavingGoal(12);

    expect(data.childName, 'たろう');
    expect(data.savingGoal?.wantedItem, 'ゲーム');
    expect(data.savingGoal?.remainingAmount, 3000);
    expect(data.savingGoal?.achievementPercent, 40);
  });
}

class _TokenStorageService extends AuthStorageService {
  @override
  Future<String?> readToken() async => 'parent-token';
}
