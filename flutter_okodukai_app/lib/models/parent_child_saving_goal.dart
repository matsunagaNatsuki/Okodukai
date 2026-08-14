import 'child_saving_goal.dart';

class ParentChildSavingGoalData {
  const ParentChildSavingGoalData({
    required this.childUserId,
    required this.childName,
    required this.currentBalance,
    this.savingGoal,
  });

  final int childUserId;
  final String childName;
  final int currentBalance;
  final ChildSavingGoal? savingGoal;

  factory ParentChildSavingGoalData.fromJson(Map<String, dynamic> json) {
    final data = json['data'] is Map<String, dynamic>
        ? json['data'] as Map<String, dynamic>
        : json;
    final child = data['child'];
    final balance = data['current_balance'] ?? data['balance'];
    if (child is! Map<String, dynamic> || balance is! num) {
      throw const FormatException('お子様の貯金目標情報が正しくありません。');
    }
    final childUserId = child['id'] ?? child['user_id'];
    final childName = child['name'];
    if (childUserId is! int || childName is! String || childName.isEmpty) {
      throw const FormatException('お子様情報の形式が正しくありません。');
    }
    final currentBalance = balance.toInt();
    final goal = data['saving_goal'];
    return ParentChildSavingGoalData(
      childUserId: childUserId,
      childName: childName,
      currentBalance: currentBalance,
      savingGoal: goal is Map<String, dynamic>
          ? ChildSavingGoal.fromJson(goal, currentBalance: currentBalance)
          : null,
    );
  }
}
