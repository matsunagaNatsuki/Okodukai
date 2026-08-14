import 'dart:convert';

import 'package:flutter_test/flutter_test.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';

import 'package:flutter_okodukai_app/config/api_config.dart';
import 'package:flutter_okodukai_app/services/api_service.dart';
import 'package:flutter_okodukai_app/services/auth_storage_service.dart';
import 'package:flutter_okodukai_app/services/parent/parent_child_management_service.dart';

void main() {
  test('選択したchild user idで管理データを取得する', () async {
    const childUserId = 12;
    final client = MockClient((request) async {
      expect(
        request.url.path,
        '/api${ApiConfig.parentChildDetailEndpoint(childUserId)}',
      );
      expect(request.method, 'GET');
      expect(request.headers['Authorization'], 'Bearer parent-token');
      return http.Response(
        jsonEncode({
          'data': {
            'child': {
              'id': childUserId,
              'name': 'たろう',
              'login_id': 'taro',
              'profile_image_url': null,
            },
            'current_balance': 1250,
          },
        }),
        200,
        headers: {'content-type': 'application/json; charset=utf-8'},
      );
    });
    final service = ParentChildManagementService(
      apiService: ApiService(client: client),
      storageService: _TokenStorageService(),
    );

    final data = await service.fetchChild(childUserId);

    expect(data.childUserId, childUserId);
    expect(data.name, 'たろう');
    expect(data.currentBalance, 1250);
  });
}

class _TokenStorageService extends AuthStorageService {
  @override
  Future<String?> readToken() async => 'parent-token';
}
