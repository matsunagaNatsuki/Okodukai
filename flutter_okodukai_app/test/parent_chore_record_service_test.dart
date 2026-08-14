import 'dart:convert';

import 'package:flutter_test/flutter_test.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';

import 'package:flutter_okodukai_app/config/api_config.dart';
import 'package:flutter_okodukai_app/services/api_service.dart';
import 'package:flutter_okodukai_app/services/auth_storage_service.dart';
import 'package:flutter_okodukai_app/services/parent/parent_chore_record_service.dart';

void main() {
  test('対象の子どもと登録済みお手伝い設定を取得する', () async {
    final service = _service((request) async {
      expect(request.method, 'GET');
      expect(
        request.url.path,
        '/api${ApiConfig.parentChildChoreRecordsEndpoint(12)}',
      );
      expect(request.headers['Authorization'], 'Bearer parent-token');
      return http.Response(
        jsonEncode({
          'data': {
            'child': {'id': 12, 'name': 'たろう'},
            'chore_settings': [
              {'id': 3, 'description': 'お皿洗い', 'reward_amount': 100},
            ],
          },
        }),
        200,
        headers: {'content-type': 'application/json; charset=utf-8'},
      );
    });

    final data = await service.fetchFormData(12);

    expect(data.childName, 'たろう');
    expect(data.choreSettings.single.description, 'お皿洗い');
    expect(data.choreSettings.single.rewardAmount, 100);
  });

  test('お手伝い実績を登録して更新後残高を受け取る', () async {
    final service = _service((request) async {
      expect(request.method, 'POST');
      expect(jsonDecode(request.body), {
        'chore_setting_id': 3,
        'reward_amount': 100,
        'performed_on': '2026-08-12',
      });
      return http.Response(
        jsonEncode({
          'data': {
            'chore_record': {'id': 20},
            'current_balance': 1350,
          },
        }),
        201,
        headers: {'content-type': 'application/json; charset=utf-8'},
      );
    });

    final result = await service.createRecord(
      childUserId: 12,
      choreSettingId: 3,
      rewardAmount: 100,
      performedOn: DateTime(2026, 8, 12),
    );

    expect(result.recordId, 20);
    expect(result.currentBalance, 1350);
  });
}

ParentChoreRecordService _service(
  Future<http.Response> Function(http.Request) handler,
) => ParentChoreRecordService(
  apiService: ApiService(client: MockClient(handler)),
  storageService: _TokenStorageService(),
);

class _TokenStorageService extends AuthStorageService {
  @override
  Future<String?> readToken() async => 'parent-token';
}
