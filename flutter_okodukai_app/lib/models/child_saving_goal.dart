import 'saving_goal.dart';

class ChildSavingGoalData {
  const ChildSavingGoalData({required this.currentBalance, this.savingGoal});

  final int currentBalance;
  final ChildSavingGoal? savingGoal;

  factory ChildSavingGoalData.fromJson(Map<String, dynamic> json) {
    final data = json['data'] is Map<String, dynamic>
        ? json['data'] as Map<String, dynamic>
        : json;
    final goalJson = data['saving_goal'];
    final currentBalance = _requiredInt(
      data['current_balance'],
      'current_balance',
    );
    return ChildSavingGoalData(
      currentBalance: currentBalance,
      savingGoal: goalJson is Map<String, dynamic>
          ? ChildSavingGoal.fromJson(goalJson, currentBalance: currentBalance)
          : null,
    );
  }
}

class ChildSavingGoal extends SavingGoal {
  const ChildSavingGoal({
    required super.id,
    required super.wantedItem,
    required super.targetAmount,
    required super.currentBalance,
  });

  factory ChildSavingGoal.fromJson(
    Map<String, dynamic> json, {
    int? currentBalance,
  }) {
    final goal = SavingGoal.fromJson(json, currentBalance: currentBalance);
    return ChildSavingGoal(
      id: goal.id,
      wantedItem: goal.wantedItem,
      targetAmount: goal.targetAmount,
      currentBalance: goal.currentBalance,
    );
  }
}

int _requiredInt(Object? value, String field) {
  if (value is int) return value;
  if (value is num) return value.toInt();
  throw FormatException('$fieldの形式が正しくありません。');
}
