class ParentChildManagementData {
  const ParentChildManagementData({
    required this.childUserId,
    required this.name,
    required this.loginId,
    required this.currentBalance,
    this.profileImageUrl,
  });

  final int childUserId;
  final String name;
  final String loginId;
  final int currentBalance;
  final String? profileImageUrl;

  factory ParentChildManagementData.fromJson(Map<String, dynamic> json) {
    final data = json['data'] is Map<String, dynamic>
        ? json['data'] as Map<String, dynamic>
        : json;
    final child = data['child'] is Map<String, dynamic>
        ? data['child'] as Map<String, dynamic>
        : data;
    final id = child['id'] ?? child['user_id'];
    final name = child['name'];
    final loginId = child['login_id'];
    final balance =
        data['current_balance'] ?? data['balance'] ?? child['balance'];
    final imageUrl = child['profile_image_url'];
    if (id is! int ||
        name is! String ||
        loginId is! String ||
        balance is! num) {
      throw const FormatException('お子様管理データの形式が正しくありません。');
    }
    return ParentChildManagementData(
      childUserId: id,
      name: name,
      loginId: loginId,
      currentBalance: balance.toInt(),
      profileImageUrl: imageUrl is String && imageUrl.isNotEmpty
          ? imageUrl
          : null,
    );
  }
}
