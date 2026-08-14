import 'parent_chore_setting.dart';

class ParentChoreRecordFormData {
  const ParentChoreRecordFormData({
    required this.childUserId,
    required this.childName,
    required this.choreSettings,
  });

  final int childUserId;
  final String childName;
  final List<ParentChoreSetting> choreSettings;

  factory ParentChoreRecordFormData.fromJson(Map<String, dynamic> json) {
    final data = json['data'] is Map<String, dynamic>
        ? json['data'] as Map<String, dynamic>
        : json;
    final child = data['child'];
    final rawSettings = data['chore_settings'] ?? data['chores'];
    if (child is! Map<String, dynamic> || rawSettings is! List) {
      throw const FormatException('お手伝い実績フォームの形式が正しくありません。');
    }
    final childUserId = child['id'] ?? child['user_id'];
    final childName = child['name'];
    if (childUserId is! int || childName is! String || childName.isEmpty) {
      throw const FormatException('お子様情報の形式が正しくありません。');
    }
    return ParentChoreRecordFormData(
      childUserId: childUserId,
      childName: childName,
      choreSettings: rawSettings
          .map((item) {
            if (item is! Map<String, dynamic>) {
              throw const FormatException('お手伝い設定の形式が正しくありません。');
            }
            return ParentChoreSetting.fromJson(item);
          })
          .toList(growable: false),
    );
  }
}

class ParentChoreRecordResult {
  const ParentChoreRecordResult({
    required this.recordId,
    required this.currentBalance,
  });

  final int recordId;
  final int currentBalance;

  factory ParentChoreRecordResult.fromJson(Map<String, dynamic> json) {
    final data = json['data'] is Map<String, dynamic>
        ? json['data'] as Map<String, dynamic>
        : json;
    final record = data['chore_record'];
    final balance = data['current_balance'] ?? data['balance'];
    final recordId = record is Map<String, dynamic> ? record['id'] : null;
    if (recordId is! int || balance is! num) {
      throw const FormatException('お手伝い実績登録結果の形式が正しくありません。');
    }
    return ParentChoreRecordResult(
      recordId: recordId,
      currentBalance: balance.toInt(),
    );
  }
}
