class SavingGoal {
  const SavingGoal({
    required this.id,
    required this.wantedItem,
    required this.targetAmount,
    required this.currentBalance,
    this.childUserId,
  });

  final int id;
  final int? childUserId;
  final String wantedItem;
  final int targetAmount;
  final int currentBalance;

  int get remainingAmount =>
      (targetAmount - currentBalance).clamp(0, targetAmount);
  double get achievementRate =>
      targetAmount <= 0 ? 0 : currentBalance / targetAmount * 100;
  int get achievementPercent => achievementRate.round();
  double get progress => (achievementRate / 100).clamp(0, 1);
  bool get isAchieved => currentBalance >= targetAmount;

  factory SavingGoal.fromJson(
    Map<String, dynamic> json, {
    int? currentBalance,
  }) {
    final id = _int(json['id']);
    final target = _int(json['target_amount']);
    final balance =
        currentBalance ?? _int(json['current_balance'] ?? json['saved_amount']);
    final wantedItem = json['wanted_item'] ?? json['title'] ?? json['name'];
    if (id == null ||
        target == null ||
        balance == null ||
        wantedItem is! String ||
        wantedItem.isEmpty ||
        target < 1) {
      throw const FormatException('貯金目標の形式が正しくありません。');
    }
    return SavingGoal(
      id: id,
      childUserId: _int(json['child_user_id'] ?? json['user_id']),
      wantedItem: wantedItem,
      targetAmount: target,
      currentBalance: balance,
    );
  }

  Map<String, dynamic> toJson() => {
    'id': id,
    if (childUserId != null) 'child_user_id': childUserId,
    'wanted_item': wantedItem,
    'target_amount': targetAmount,
    'current_balance': currentBalance,
  };
}

int? _int(Object? value) =>
    value is num ? value.toInt() : int.tryParse(value?.toString() ?? '');
