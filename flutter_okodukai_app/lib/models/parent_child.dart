import 'user.dart';

class ParentChild extends User {
  const ParentChild({
    required super.id,
    required super.name,
    required String super.loginId,
    super.profileImageUrl,
  }) : super(role: 'child');

  factory ParentChild.fromJson(Map<String, dynamic> json) {
    final user = User.fromJson(json);
    if (user.loginId == null) {
      throw const FormatException('お子様情報の形式が正しくありません。');
    }
    return ParentChild(
      id: user.id,
      name: user.name,
      loginId: user.loginId!,
      profileImageUrl: user.profileImageUrl,
    );
  }
}

class ParentChildList {
  const ParentChildList({required this.children});

  final List<ParentChild> children;

  factory ParentChildList.fromJson(Map<String, dynamic> json) {
    final data = json['data'];
    final Object? rawChildren;
    if (data is List) {
      rawChildren = data;
    } else if (data is Map<String, dynamic>) {
      rawChildren = data['children'] ?? data['users'] ?? data['data'];
    } else {
      rawChildren = json['children'] ?? json['users'];
    }
    if (rawChildren is! List) {
      throw const FormatException('お子様一覧の形式が正しくありません。');
    }
    return ParentChildList(
      children: rawChildren
          .where((item) {
            if (item is! Map<String, dynamic>) return true;
            final role = item['role'];
            return role == null || role == 'child';
          })
          .map((item) {
            if (item is! Map<String, dynamic>) {
              throw const FormatException('お子様情報の形式が正しくありません。');
            }
            return ParentChild.fromJson(item);
          })
          .where((child) => child.name.isNotEmpty)
          .toList(growable: false),
    );
  }
}
