import 'allowance.dart';

class ParentRegularAllowanceData {
  const ParentRegularAllowanceData({
    required this.childUserId,
    required this.childName,
    this.setting,
  });

  final int childUserId;
  final String childName;
  final ParentRegularAllowance? setting;

  factory ParentRegularAllowanceData.fromJson(Map<String, dynamic> json) {
    final data = json['data'] is Map<String, dynamic>
        ? json['data'] as Map<String, dynamic>
        : json;
    final child = data['child'];
    if (child is! Map<String, dynamic>) {
      throw const FormatException('お子様情報の形式が正しくありません。');
    }
    final id = child['id'] ?? child['user_id'];
    final name = child['name'];
    final settingJson = data['regular_allowance'] ?? data['allowance_setting'];
    if (id is! int || name is! String || name.isEmpty) {
      throw const FormatException('お子様情報の形式が正しくありません。');
    }
    return ParentRegularAllowanceData(
      childUserId: id,
      childName: name,
      setting: settingJson is Map<String, dynamic>
          ? ParentRegularAllowance.fromJson(settingJson)
          : null,
    );
  }
}

class ParentRegularAllowance extends Allowance {
  const ParentRegularAllowance({
    required super.id,
    required super.amount,
    required super.paymentDay,
    required super.isActive,
  });

  factory ParentRegularAllowance.fromJson(Map<String, dynamic> json) {
    final allowance = Allowance.fromJson(json);
    return ParentRegularAllowance(
      id: allowance.id,
      amount: allowance.amount,
      paymentDay: allowance.paymentDay,
      isActive: allowance.isActive,
    );
  }
}
