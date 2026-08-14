import 'dart:convert';

import 'package:flutter_test/flutter_test.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';

import 'package:flutter_okodukai_app/config/api_config.dart';
import 'package:flutter_okodukai_app/models/child_saving_goal.dart';
import 'package:flutter_okodukai_app/services/api_service.dart';
import 'package:flutter_okodukai_app/services/auth_storage_service.dart';
import 'package:flutter_okodukai_app/services/child/child_saving_goal_service.dart';

void main() {
  test('達成率は100%を超えて計算しプログレスバー値は100%までにする', () {
    const goal = ChildSavingGoal(
      id: 1,
      wantedItem: 'ゲーム',
      targetAmount: 5000,
      currentBalance: 6000,
    );

    expect(goal.achievementPercent, 120);
    expect(goal.progress, 1);
    expect(goal.remainingAmount, 0);
    expect(goal.isAchieved, isTrue);
  });

  test('登録済み目標はPUTで更新する', () async {
    final client = MockClient((request) async {
      expect(request.url.path, '/api${ApiConfig.childSavingGoalEndpoint}');
      expect(request.method, 'PUT');
      expect(request.headers['Authorization'], 'Bearer child-token');
      expect(jsonDecode(request.body), {
        'wanted_item': 'ゲーム',
        'target_amount': 5000,
      });
      return http.Response(
        jsonEncode({
          'data': {
            'current_balance': 1250,
            'saving_goal': {
              'id': 1,
              'wanted_item': 'ゲーム',
              'target_amount': 5000,
            },
          },
        }),
        200,
        headers: {'content-type': 'application/json; charset=utf-8'},
      );
    });
    final service = ChildSavingGoalService(
      apiService: ApiService(client: client),
      storageService: _TokenStorageService(),
    );

    final result = await service.saveSavingGoal(
      wantedItem: 'ゲーム',
      targetAmount: 5000,
      isEditing: true,
    );

    expect(result.savingGoal?.wantedItem, 'ゲーム');
    expect(result.savingGoal?.achievementPercent, 25);
  });
}

class _TokenStorageService extends AuthStorageService {
  @override
  Future<String?> readToken() async => 'child-token';
}
