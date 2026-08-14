import 'family.dart';
import 'parent_registration_result.dart';

class ParentLoginResult {
  const ParentLoginResult({
    required this.token,
    required this.role,
    required this.parent,
    this.familyCode,
  });

  final String token;
  final String role;
  final RegisteredParent parent;
  final String? familyCode;
  Family? get family => familyCode == null ? null : Family(code: familyCode!);

  factory ParentLoginResult.fromJson(Map<String, dynamic> json) {
    final data = json['data'] is Map<String, dynamic>
        ? json['data'] as Map<String, dynamic>
        : json;
    final parentJson = data['parent'] ?? data['user'];
    final familyJson = data['family'];
    final token = data['token'] ?? data['access_token'];
    final role =
        data['role'] ??
        (parentJson is Map<String, dynamic> ? parentJson['role'] : null);
    final familyCode =
        data['family_code'] ??
        (familyJson is Map<String, dynamic> ? familyJson['code'] : null);

    if (token is! String || token.isEmpty) {
      throw const FormatException('認証トークンが含まれていません。');
    }
    if (role != 'parent') {
      throw const ParentRoleException();
    }
    if (parentJson is! Map<String, dynamic>) {
      throw const FormatException('保護者情報が含まれていません。');
    }
    if (familyCode != null &&
        (familyCode is! String || !RegExp(r'^\d{8}$').hasMatch(familyCode))) {
      throw const FormatException('家族コードの形式が正しくありません。');
    }

    return ParentLoginResult(
      token: token,
      role: role as String,
      parent: RegisteredParent.fromJson(parentJson),
      familyCode: familyCode as String?,
    );
  }
}

class ParentRoleException implements FormatException {
  const ParentRoleException();

  @override
  String get message => '保護者アカウントではありません。';

  @override
  int? get offset => null;

  @override
  Object? get source => null;
}
