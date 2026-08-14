import 'package:flutter_secure_storage/flutter_secure_storage.dart';

class AuthStorageService {
  AuthStorageService({FlutterSecureStorage? storage})
    : _storage = storage ?? const FlutterSecureStorage();

  static const _tokenKey = 'auth_token';
  static const _userTypeKey = 'user_type';
  static const _userIdKey = 'user_id';
  static const _familyCodeKey = 'family_code';
  static const _rememberedParentEmailKey = 'remembered_parent_email';
  static const _rememberedChildFamilyCodeKey = 'remembered_child_family_code';
  static const _rememberedChildLoginIdKey = 'remembered_child_login_id';

  final FlutterSecureStorage _storage;

  Future<String?> readToken() => _storage.read(key: _tokenKey);

  Future<String?> readUserRole() => _storage.read(key: _userTypeKey);

  Future<void> clearSession() async {
    await Future.wait([
      _storage.delete(key: _tokenKey),
      _storage.delete(key: _userTypeKey),
      _storage.delete(key: _userIdKey),
      _storage.delete(key: _familyCodeKey),
    ]);
  }

  Future<void> saveParentSession({
    required String token,
    required int parentId,
    String? familyCode,
  }) async {
    final writes = <Future<void>>[
      _storage.write(key: _tokenKey, value: token),
      _storage.write(key: _userTypeKey, value: 'parent'),
      _storage.write(key: _userIdKey, value: parentId.toString()),
    ];
    if (familyCode != null) {
      writes.add(_storage.write(key: _familyCodeKey, value: familyCode));
    }
    await Future.wait(writes);
  }

  Future<String?> readRememberedParentEmail() {
    return _storage.read(key: _rememberedParentEmailKey);
  }

  Future<void> setRememberedParentEmail(String? email) {
    if (email == null) {
      return _storage.delete(key: _rememberedParentEmailKey);
    }
    return _storage.write(key: _rememberedParentEmailKey, value: email);
  }

  Future<void> saveChildSession({
    required String token,
    required int childId,
    required String familyCode,
  }) async {
    await Future.wait([
      _storage.write(key: _tokenKey, value: token),
      _storage.write(key: _userTypeKey, value: 'child'),
      _storage.write(key: _userIdKey, value: childId.toString()),
      _storage.write(key: _familyCodeKey, value: familyCode),
    ]);
  }

  Future<RememberedChildLogin> readRememberedChildLogin() async {
    final values = await Future.wait([
      _storage.read(key: _rememberedChildFamilyCodeKey),
      _storage.read(key: _rememberedChildLoginIdKey),
    ]);
    return RememberedChildLogin(familyCode: values[0], loginId: values[1]);
  }

  Future<void> setRememberedChildLogin({
    String? familyCode,
    String? loginId,
  }) async {
    if (familyCode == null || loginId == null) {
      await Future.wait([
        _storage.delete(key: _rememberedChildFamilyCodeKey),
        _storage.delete(key: _rememberedChildLoginIdKey),
      ]);
      return;
    }

    await Future.wait([
      _storage.write(key: _rememberedChildFamilyCodeKey, value: familyCode),
      _storage.write(key: _rememberedChildLoginIdKey, value: loginId),
    ]);
  }
}

class RememberedChildLogin {
  const RememberedChildLogin({this.familyCode, this.loginId});

  final String? familyCode;
  final String? loginId;
}
