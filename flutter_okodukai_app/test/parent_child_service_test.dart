import 'dart:convert';

import 'package:flutter_test/flutter_test.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';

import 'package:flutter_okodukai_app/config/api_config.dart';
import 'package:flutter_okodukai_app/services/api_service.dart';
import 'package:flutter_okodukai_app/services/auth_storage_service.dart';
import 'package:flutter_okodukai_app/services/parent/parent_child_service.dart';

void main() {
  test('保護者と同じ家族のお子様一覧をModelへ変換する', () async {
    final client = MockClient((request) async {
      expect(request.url.path, '/api${ApiConfig.parentChildrenEndpoint}');
      expect(request.method, 'GET');
      expect(request.headers['Authorization'], 'Bearer parent-token');
      return http.Response(
        jsonEncode({
          'data': {
            'children': [
              {
                'id': 2,
                'name': 'たろう',
                'login_id': 'taro',
                'role': 'child',
                'profile_image_url': 'https://example.com/taro.png',
              },
            ],
          },
        }),
        200,
        headers: {'content-type': 'application/json; charset=utf-8'},
      );
    });
    final service = ParentChildService(
      apiService: ApiService(client: client),
      storageService: _TokenStorageService(),
    );

    final children = await service.fetchChildren();

    expect(children.single.name, 'たろう');
    expect(children.single.loginId, 'taro');
    expect(children.single.profileImageUrl, 'https://example.com/taro.png');
  });
}

class _TokenStorageService extends AuthStorageService {
  @override
  Future<String?> readToken() async => 'parent-token';
}
