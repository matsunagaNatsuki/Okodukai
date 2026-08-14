import 'dart:convert';
import 'dart:typed_data';

import 'package:flutter_test/flutter_test.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';

import 'package:flutter_okodukai_app/config/api_config.dart';
import 'package:flutter_okodukai_app/services/api_service.dart';
import 'package:flutter_okodukai_app/services/auth_storage_service.dart';
import 'package:flutter_okodukai_app/services/child/child_profile_service.dart';

void main() {
  test('プロフィール画像をmultipart/form-dataで更新する', () async {
    final client = MockClient((request) async {
      expect(request.url.path, '/api${ApiConfig.childProfileEndpoint}');
      expect(request.method, 'POST');
      expect(request.headers['Authorization'], 'Bearer child-token');
      expect(
        request.headers['content-type'],
        startsWith('multipart/form-data;'),
      );
      final body = utf8.decode(request.bodyBytes);
      expect(body, contains('name="name"'));
      expect(body, contains('たろう'));
      expect(body, contains('name="_method"'));
      expect(body, contains('PUT'));
      expect(body, contains('name="profile_image"'));
      expect(body, contains('filename="profile.jpg"'));

      return http.Response(
        jsonEncode({
          'data': {
            'child': {
              'id': 2,
              'name': 'たろう',
              'profile_image_url': 'https://example.com/latest.jpg',
            },
          },
        }),
        200,
        headers: {'content-type': 'application/json; charset=utf-8'},
      );
    });
    final service = ChildProfileService(
      apiService: ApiService(client: client),
      storageService: _TokenStorageService(),
    );

    final profile = await service.updateProfile(
      name: 'たろう',
      imageBytes: Uint8List.fromList([1, 2, 3]),
      imageFileName: 'profile.jpg',
    );

    expect(profile.name, 'たろう');
    expect(profile.profileImageUrl, 'https://example.com/latest.jpg');
  });
}

class _TokenStorageService extends AuthStorageService {
  @override
  Future<String?> readToken() async => 'child-token';
}
