import 'dart:convert';

import 'package:flutter_test/flutter_test.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';

import 'package:flutter_okodukai_app/services/api_exception.dart';
import 'package:flutter_okodukai_app/services/api_service.dart';
import 'package:flutter_okodukai_app/services/auth_storage_service.dart';

void main() {
  test('共通API処理がBearer Token・Content-Type・JSON decodeを行う', () async {
    final client = MockClient((request) async {
      expect(request.headers['Authorization'], 'Bearer saved-token');
      expect(request.headers['Content-Type'], 'application/json');
      return http.Response(
        jsonEncode({
          'data': {'id': 1},
        }),
        200,
        headers: {'content-type': 'application/json; charset=utf-8'},
      );
    });
    final service = ApiService(
      client: client,
      storageService: _ApiStorageService(),
    );

    final json = await service.getJson('/test', authenticated: true);

    expect((json['data'] as Map<String, dynamic>)['id'], 1);
  });

  test('401の場合は端末セッションを削除して共通例外へ変換する', () async {
    final storage = _ApiStorageService();
    var unauthorizedCalled = false;
    final service = ApiService(
      client: MockClient(
        (_) async => http.Response(
          jsonEncode({'message': 'Unauthenticated.'}),
          401,
          headers: {'content-type': 'application/json; charset=utf-8'},
        ),
      ),
      storageService: storage,
      onUnauthorized: () async => unauthorizedCalled = true,
    );

    expect(
      () => service.getJson('/private', authenticated: true),
      throwsA(
        isA<ApiException>()
            .having((error) => error.isUnauthorized, 'unauthorized', isTrue)
            .having((error) => error.statusCode, 'statusCode', 401),
      ),
    );
    await Future<void>.delayed(Duration.zero);
    expect(storage.cleared, isTrue);
    expect(unauthorizedCalled, isTrue);
  });

  test('既存Serviceが使う低レベル通信でも401時にセッションを削除する', () async {
    final storage = _ApiStorageService();
    var unauthorizedCalled = false;
    final service = ApiService(
      client: MockClient((_) async => http.Response('', 401)),
      storageService: storage,
      onUnauthorized: () async => unauthorizedCalled = true,
    );

    final response = await service.get(
      '/private',
      headers: {'Authorization': 'Bearer saved-token'},
    );

    expect(response.statusCode, 401);
    expect(storage.cleared, isTrue);
    expect(unauthorizedCalled, isTrue);
  });

  test('Laravelバリデーションエラーの先頭メッセージを共通例外へ変換する', () async {
    final service = ApiService(
      client: MockClient(
        (_) async => http.Response(
          jsonEncode({
            'errors': {
              'amount': ['金額は1円以上で入力してください。'],
            },
          }),
          422,
          headers: {'content-type': 'application/json; charset=utf-8'},
        ),
      ),
    );

    expect(
      () => service.postJson('/test', body: {'amount': 0}),
      throwsA(
        isA<ApiException>().having(
          (error) => error.message,
          'message',
          '金額は1円以上で入力してください。',
        ),
      ),
    );
  });
}

class _ApiStorageService extends AuthStorageService {
  bool cleared = false;

  @override
  Future<String?> readToken() async => 'saved-token';

  @override
  Future<void> clearSession() async => cleared = true;
}
