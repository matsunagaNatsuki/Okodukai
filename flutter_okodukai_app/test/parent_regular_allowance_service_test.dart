import 'dart:convert';

import 'package:flutter_test/flutter_test.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';

import 'package:flutter_okodukai_app/config/api_config.dart';
import 'package:flutter_okodukai_app/services/api_service.dart';
import 'package:flutter_okodukai_app/services/auth_storage_service.dart';
import 'package:flutter_okodukai_app/services/parent/parent_regular_allowance_service.dart';

void main() {
  test('既存の定期おこづかい設定を取得する', () async {
    final service = _service((request) async {
      expect(request.method, 'GET');
      expect(
        request.url.path,
        '/api${ApiConfig.parentRegularAllowanceEndpoint(12)}',
      );
      expect(request.headers['Authorization'], 'Bearer parent-token');
      return _response();
    });

    final data = await service.fetchSetting(12);

    expect(data.childName, 'たろう');
    expect(data.setting?.amount, 1500);
    expect(data.setting?.paymentDay, 15);
    expect(data.setting?.isActive, isTrue);
  });

  test('設定がない場合も対象のお子様を取得できる', () async {
    final service = _service(
      (request) async => http.Response(
        jsonEncode({
          'data': {
            'child': {'id': 12, 'name': 'たろう'},
            'regular_allowance': null,
          },
        }),
        200,
        headers: {'content-type': 'application/json; charset=utf-8'},
      ),
    );

    final data = await service.fetchSetting(12);

    expect(data.childName, 'たろう');
    expect(data.setting, isNull);
  });

  test('既存設定をPUTで保存する', () async {
    final service = _service((request) async {
      expect(request.method, 'PUT');
      expect(jsonDecode(request.body), {
        'amount': 1500,
        'payment_day': 15,
        'is_active': true,
      });
      return _response();
    });

    final data = await service.saveSetting(
      childUserId: 12,
      amount: 1500,
      paymentDay: 15,
      isActive: true,
      isEditing: true,
    );

    expect(data.setting?.amount, 1500);
  });
}

ParentRegularAllowanceService _service(
  Future<http.Response> Function(http.Request) handler,
) {
  return ParentRegularAllowanceService(
    apiService: ApiService(client: MockClient(handler)),
    storageService: _TokenStorageService(),
  );
}

http.Response _response() => http.Response(
  jsonEncode({
    'data': {
      'child': {'id': 12, 'name': 'たろう'},
      'regular_allowance': {
        'id': 3,
        'amount': 1500,
        'payment_day': 15,
        'is_active': true,
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
