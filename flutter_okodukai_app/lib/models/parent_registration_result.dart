import 'family.dart';
import 'user.dart';

class ParentRegistrationResult {
  const ParentRegistrationResult({
    required this.token,
    required this.familyCode,
    required this.parent,
  });

  final String token;
  final String familyCode;
  final RegisteredParent parent;
  Family get family => Family(code: familyCode);

  factory ParentRegistrationResult.fromJson(Map<String, dynamic> json) {
    final data = json['data'] is Map<String, dynamic>
        ? json['data'] as Map<String, dynamic>
        : json;
    final parentJson = data['parent'] ?? data['user'];
    final familyJson = data['family'];
    final token = data['token'] ?? data['access_token'];
    final familyCode =
        data['family_code'] ??
        (familyJson is Map<String, dynamic> ? familyJson['code'] : null);

    if (token is! String || token.isEmpty) {
      throw const FormatException('認証トークンが含まれていません。');
    }
    if (familyCode is! String || !RegExp(r'^\d{8}$').hasMatch(familyCode)) {
      throw const FormatException('8桁の家族コードが含まれていません。');
    }
    if (parentJson is! Map<String, dynamic>) {
      throw const FormatException('保護者情報が含まれていません。');
    }

    return ParentRegistrationResult(
      token: token,
      familyCode: familyCode,
      parent: RegisteredParent.fromJson(parentJson),
    );
  }
}

class RegisteredParent extends User {
  const RegisteredParent({
    required super.id,
    required super.name,
    required String super.email,
  }) : super(role: 'parent');

  factory RegisteredParent.fromJson(Map<String, dynamic> json) {
    final user = User.fromJson(json);
    if (user.email == null) {
      throw const FormatException('保護者情報の形式が正しくありません。');
    }
    return RegisteredParent(id: user.id, name: user.name, email: user.email!);
  }
}
