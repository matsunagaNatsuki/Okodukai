class User {
  const User({
    required this.id,
    required this.name,
    this.role,
    this.email,
    this.loginId,
    this.familyId,
    this.profileImageUrl,
    this.balance,
  });

  final int id;
  final String name;
  final String? role;
  final String? email;
  final String? loginId;
  final int? familyId;
  final String? profileImageUrl;
  final int? balance;

  factory User.fromJson(Map<String, dynamic> json) => User(
    id: _requiredInt(json['id'] ?? json['user_id'], 'user.id'),
    name: _requiredString(json['name'], 'user.name'),
    role: _optionalString(json['role']),
    email: _optionalString(json['email']),
    loginId: _optionalString(json['login_id']),
    familyId: _optionalInt(json['family_id']),
    profileImageUrl: _optionalString(
      json['profile_image_url'] ?? json['avatar_url'],
    ),
    balance: _optionalInt(json['balance'] ?? json['current_balance']),
  );

  Map<String, dynamic> toJson() => {
    'id': id,
    'name': name,
    if (role != null) 'role': role,
    if (email != null) 'email': email,
    if (loginId != null) 'login_id': loginId,
    if (familyId != null) 'family_id': familyId,
    if (profileImageUrl != null) 'profile_image_url': profileImageUrl,
    if (balance != null) 'balance': balance,
  };
}

int _requiredInt(Object? value, String field) {
  final parsed = _optionalInt(value);
  if (parsed != null) return parsed;
  throw FormatException('$fieldの形式が正しくありません。');
}

int? _optionalInt(Object? value) {
  if (value is int) return value;
  if (value is num) return value.toInt();
  if (value is String) return int.tryParse(value);
  return null;
}

String _requiredString(Object? value, String field) {
  final parsed = _optionalString(value);
  if (parsed != null) return parsed;
  throw FormatException('$fieldの形式が正しくありません。');
}

String? _optionalString(Object? value) =>
    value is String && value.trim().isNotEmpty ? value : null;
