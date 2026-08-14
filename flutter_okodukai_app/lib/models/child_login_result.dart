import 'user.dart';

class ChildLoginResult {
  const ChildLoginResult({
    required this.token,
    required this.role,
    required this.child,
  });

  final String token;
  final String role;
  final LoggedInChild child;

  factory ChildLoginResult.fromJson(Map<String, dynamic> json) {
    final data = json['data'] is Map<String, dynamic>
        ? json['data'] as Map<String, dynamic>
        : json;
    final childJson = data['child'] ?? data['user'];
    final token = data['token'] ?? data['access_token'];
    final role =
        data['role'] ??
        (childJson is Map<String, dynamic> ? childJson['role'] : null);

    if (token is! String || token.isEmpty) {
      throw const FormatException('認証トークンが含まれていません。');
    }
    if (role != 'child') {
      throw const ChildRoleException();
    }
    if (childJson is! Map<String, dynamic>) {
      throw const FormatException('子ども情報が含まれていません。');
    }

    return ChildLoginResult(
      token: token,
      role: role as String,
      child: LoggedInChild.fromJson(childJson),
    );
  }
}

class LoggedInChild extends User {
  const LoggedInChild({
    required super.id,
    required super.name,
    required String super.loginId,
  }) : super(role: 'child');

  factory LoggedInChild.fromJson(Map<String, dynamic> json) {
    final user = User.fromJson(json);
    if (user.loginId == null) {
      throw const FormatException('子ども情報の形式が正しくありません。');
    }
    return LoggedInChild(id: user.id, name: user.name, loginId: user.loginId!);
  }
}

class ChildRoleException implements FormatException {
  const ChildRoleException();

  @override
  String get message => '子どもアカウントではありません。';

  @override
  int? get offset => null;

  @override
  Object? get source => null;
}
