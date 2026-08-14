import 'dart:convert';

import 'package:flutter_test/flutter_test.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';

import 'package:flutter_okodukai_app/config/api_config.dart';
import 'package:flutter_okodukai_app/services/api_service.dart';
import 'package:flutter_okodukai_app/services/auth_storage_service.dart';
import 'package:flutter_okodukai_app/services/parent/parent_chore_setting_service.dart';

void main() {
  test('家族のお手伝い設定一覧を取得する', () async {
    final service = _service((request) async {
      expect(request.method, 'GET');
      expect(request.url.path, '/api${ApiConfig.parentChoreSettingsEndpoint}');
      expect(request.headers['Authorization'], 'Bearer parent-token');
      return http.Response(
        jsonEncode({
          'data': {
            'chore_settings': [
              {'id': 1, 'description': 'お皿洗い', 'reward_amount': 100},
            ],
          },
        }),
        200,
        headers: {'content-type': 'application/json; charset=utf-8'},
      );
    });

    final settings = await service.fetchSettings();

    expect(settings.single.description, 'お皿洗い');
    expect(settings.single.rewardAmount, 100);
  });

  test('お手伝い設定を登録・編集・削除できる', () async {
    var call = 0;
    final service = _service((request) async {
      call++;
      expect(request.headers['Authorization'], 'Bearer parent-token');
      if (call == 1) {
        expect(request.method, 'POST');
        expect(jsonDecode(request.body), {
          'description': 'お風呂掃除',
          'reward_amount': 200,
        });
        return _settingResponse('お風呂掃除', 200);
      }
      if (call == 2) {
        expect(request.method, 'PUT');
        expect(
          request.url.path,
          '/api${ApiConfig.parentChoreSettingEndpoint(5)}',
        );
        return _settingResponse('お風呂掃除', 250);
      }
      expect(request.method, 'DELETE');
      expect(
        request.url.path,
        '/api${ApiConfig.parentChoreSettingEndpoint(5)}',
      );
      return http.Response('', 204);
    });

    final created = await service.createSetting(
      description: 'お風呂掃除',
      rewardAmount: 200,
    );
    final updated = await service.updateSetting(
      settingId: created.id,
      description: 'お風呂掃除',
      rewardAmount: 250,
    );
    await service.deleteSetting(updated.id);

    expect(updated.rewardAmount, 250);
    expect(call, 3);
  });
}

ParentChoreSettingService _service(
  Future<http.Response> Function(http.Request) handler,
) => ParentChoreSettingService(
  apiService: ApiService(client: MockClient(handler)),
  storageService: _TokenStorageService(),
);

http.Response _settingResponse(String description, int rewardAmount) =>
    http.Response(
      jsonEncode({
        'data': {
          'chore_setting': {
            'id': 5,
            'description': description,
            'reward_amount': rewardAmount,
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
