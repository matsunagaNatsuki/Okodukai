import 'dart:convert';

import 'package:flutter_test/flutter_test.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';

import 'package:flutter_okodukai_app/config/api_config.dart';
import 'package:flutter_okodukai_app/services/api_service.dart';
import 'package:flutter_okodukai_app/services/auth_service.dart';
import 'package:flutter_okodukai_app/services/auth_storage_service.dart';

void main() {
  test('保護者登録APIの応答を保存して返す', () async {
    final storage = _FakeAuthStorageService();
    final client = MockClient((request) async {
      expect(request.url.path, '/api${ApiConfig.parentRegisterEndpoint}');
      expect(request.method, 'POST');
      expect(jsonDecode(request.body), {
        'name': '山田太郎',
        'email': 'parent@example.com',
        'password': 'password',
        'password_confirmation': 'password',
      });

      return http.Response(
        jsonEncode({
          'token': 'test-token',
          'family_code': '12345678',
          'user': {'id': 1, 'name': '山田太郎', 'email': 'parent@example.com'},
        }),
        201,
        headers: {'content-type': 'application/json; charset=utf-8'},
      );
    });
    final service = AuthService(
      apiService: ApiService(client: client),
      storageService: storage,
    );

    final result = await service.registerParent(
      name: '山田太郎',
      email: 'parent@example.com',
      password: 'password',
      passwordConfirmation: 'password',
    );

    expect(result.familyCode, '12345678');
    expect(result.parent.name, '山田太郎');
    expect(storage.savedToken, 'test-token');
    expect(storage.savedFamilyCode, '12345678');
  });

  test('保護者ログイン時にroleを確認してトークンを保存する', () async {
    final storage = _FakeAuthStorageService();
    final client = MockClient((request) async {
      expect(request.url.path, '/api${ApiConfig.parentLoginEndpoint}');
      expect(jsonDecode(request.body), {
        'email': 'parent@example.com',
        'password': 'password',
      });
      return http.Response(
        jsonEncode({
          'access_token': 'login-token',
          'role': 'parent',
          'user': {'id': 1, 'name': '山田太郎', 'email': 'parent@example.com'},
        }),
        200,
        headers: {'content-type': 'application/json; charset=utf-8'},
      );
    });
    final service = AuthService(
      apiService: ApiService(client: client),
      storageService: storage,
    );

    final result = await service.loginParent(
      email: 'parent@example.com',
      password: 'password',
      rememberEmail: true,
    );

    expect(result.role, 'parent');
    expect(storage.savedToken, 'login-token');
    expect(storage.rememberedEmail, 'parent@example.com');
  });

  test('roleがparent以外の場合はログインを拒否する', () async {
    final storage = _FakeAuthStorageService();
    final client = MockClient((_) async {
      return http.Response(
        jsonEncode({
          'token': 'child-token',
          'role': 'child',
          'user': {'id': 2, 'name': '子ども', 'email': 'child@example.com'},
        }),
        200,
        headers: {'content-type': 'application/json; charset=utf-8'},
      );
    });
    final service = AuthService(
      apiService: ApiService(client: client),
      storageService: storage,
    );

    expect(
      () => service.loginParent(
        email: 'child@example.com',
        password: 'password',
        rememberEmail: false,
      ),
      throwsA(
        isA<AuthException>().having(
          (error) => error.message,
          'message',
          '保護者アカウントではありません。',
        ),
      ),
    );
  });

  test('子どもログイン時にroleを確認して必要な情報だけ保存する', () async {
    final storage = _FakeAuthStorageService();
    final client = MockClient((request) async {
      expect(request.url.path, '/api${ApiConfig.childLoginEndpoint}');
      expect(jsonDecode(request.body), {
        'family_code': '12345678',
        'login_id': 'taro',
        'password': 'password',
      });
      return http.Response(
        jsonEncode({
          'token': 'child-token',
          'role': 'child',
          'child': {'id': 2, 'name': 'たろう', 'login_id': 'taro'},
        }),
        200,
        headers: {'content-type': 'application/json; charset=utf-8'},
      );
    });
    final service = AuthService(
      apiService: ApiService(client: client),
      storageService: storage,
    );

    final result = await service.loginChild(
      familyCode: '12345678',
      loginId: 'taro',
      password: 'password',
      rememberLogin: true,
    );

    expect(result.role, 'child');
    expect(storage.savedToken, 'child-token');
    expect(storage.savedFamilyCode, '12345678');
    expect(storage.rememberedChildFamilyCode, '12345678');
    expect(storage.rememberedChildLoginId, 'taro');
  });

  test('roleがchild以外の場合は子どもログインを拒否する', () async {
    final storage = _FakeAuthStorageService();
    final client = MockClient((_) async {
      return http.Response(
        jsonEncode({
          'token': 'parent-token',
          'role': 'parent',
          'user': {'id': 1, 'name': '保護者', 'login_id': 'parent'},
        }),
        200,
        headers: {'content-type': 'application/json; charset=utf-8'},
      );
    });
    final service = AuthService(
      apiService: ApiService(client: client),
      storageService: storage,
    );

    expect(
      () => service.loginChild(
        familyCode: '12345678',
        loginId: 'parent',
        password: 'password',
        rememberLogin: false,
      ),
      throwsA(
        isA<AuthException>().having(
          (error) => error.message,
          'message',
          '子どもアカウントではありません。',
        ),
      ),
    );
  });

  test('ログアウトAPIへトークンを送り端末セッションを削除する', () async {
    final storage = _FakeAuthStorageService()..storedToken = 'saved-token';
    final client = MockClient((request) async {
      expect(request.url.path, '/api${ApiConfig.logoutEndpoint}');
      expect(request.method, 'POST');
      expect(request.headers['Authorization'], 'Bearer saved-token');
      return http.Response('', 204);
    });
    final service = AuthService(
      apiService: ApiService(client: client),
      storageService: storage,
    );

    await service.logout();

    expect(storage.sessionCleared, isTrue);
  });

  test('確認コード送信・検証・パスワード再設定を実行する', () async {
    var call = 0;
    final client = MockClient((request) async {
      call++;
      if (call == 1) {
        expect(request.url.path, '/api${ApiConfig.passwordResetCodeEndpoint}');
        expect(jsonDecode(request.body), {'email': 'parent@example.com'});
        return http.Response(
          jsonEncode({'message': '送信しました'}),
          200,
          headers: {'content-type': 'application/json; charset=utf-8'},
        );
      }
      if (call == 2) {
        expect(
          request.url.path,
          '/api${ApiConfig.passwordResetVerifyEndpoint}',
        );
        expect(jsonDecode(request.body), {
          'email': 'parent@example.com',
          'code': '1234',
        });
        return http.Response(
          jsonEncode({
            'data': {'reset_token': 'temporary-reset-token'},
          }),
          200,
          headers: {'content-type': 'application/json; charset=utf-8'},
        );
      }
      expect(request.url.path, '/api${ApiConfig.passwordResetEndpoint}');
      expect(jsonDecode(request.body), {
        'email': 'parent@example.com',
        'reset_token': 'temporary-reset-token',
        'password': 'new-password',
        'password_confirmation': 'new-password',
      });
      return http.Response(
        jsonEncode({'message': '再設定しました'}),
        200,
        headers: {'content-type': 'application/json; charset=utf-8'},
      );
    });
    final service = AuthService(
      apiService: ApiService(client: client),
      storageService: _FakeAuthStorageService(),
    );

    await service.requestPasswordResetCode(email: 'parent@example.com');
    final token = await service.verifyPasswordResetCode(
      email: 'parent@example.com',
      code: '1234',
    );
    await service.resetPassword(
      email: 'parent@example.com',
      resetToken: token,
      password: 'new-password',
      passwordConfirmation: 'new-password',
    );

    expect(token, 'temporary-reset-token');
    expect(call, 3);
  });

  test('確認コード期限切れなどのLaravelエラーを返す', () async {
    final client = MockClient(
      (request) async => http.Response(
        jsonEncode({'message': '確認コードの有効期限が切れています。'}),
        422,
        headers: {'content-type': 'application/json; charset=utf-8'},
      ),
    );
    final service = AuthService(
      apiService: ApiService(client: client),
      storageService: _FakeAuthStorageService(),
    );

    expect(
      () => service.verifyPasswordResetCode(
        email: 'parent@example.com',
        code: '1234',
      ),
      throwsA(
        isA<AuthException>().having(
          (error) => error.message,
          'message',
          '確認コードの有効期限が切れています。',
        ),
      ),
    );
  });
}

class _FakeAuthStorageService extends AuthStorageService {
  String? savedToken;
  String? savedFamilyCode;
  String? rememberedEmail;
  String? rememberedChildFamilyCode;
  String? rememberedChildLoginId;
  String? storedToken;
  bool sessionCleared = false;

  @override
  Future<String?> readToken() async => storedToken;

  @override
  Future<void> clearSession() async {
    sessionCleared = true;
    storedToken = null;
  }

  @override
  Future<void> saveParentSession({
    required String token,
    required int parentId,
    String? familyCode,
  }) async {
    savedToken = token;
    savedFamilyCode = familyCode;
  }

  @override
  Future<String?> readRememberedParentEmail() async => rememberedEmail;

  @override
  Future<void> setRememberedParentEmail(String? email) async {
    rememberedEmail = email;
  }

  @override
  Future<void> saveChildSession({
    required String token,
    required int childId,
    required String familyCode,
  }) async {
    savedToken = token;
    savedFamilyCode = familyCode;
  }

  @override
  Future<RememberedChildLogin> readRememberedChildLogin() async {
    return RememberedChildLogin(
      familyCode: rememberedChildFamilyCode,
      loginId: rememberedChildLoginId,
    );
  }

  @override
  Future<void> setRememberedChildLogin({
    String? familyCode,
    String? loginId,
  }) async {
    rememberedChildFamilyCode = familyCode;
    rememberedChildLoginId = loginId;
  }
}
