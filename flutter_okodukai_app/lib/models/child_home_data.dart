import 'transaction.dart';

class ChildHomeData {
  const ChildHomeData({
    required this.id,
    required this.name,
    required this.balance,
    required this.recentTransactions,
    this.profileImageUrl,
    this.savingsGoal,
  });

  final int id;
  final String name;
  final String? profileImageUrl;
  final int balance;
  final ChildSavingsGoal? savingsGoal;
  final List<ChildTransaction> recentTransactions;

  ChildHomeData copyWith({int? balance}) {
    return ChildHomeData(
      id: id,
      name: name,
      profileImageUrl: profileImageUrl,
      balance: balance ?? this.balance,
      savingsGoal: savingsGoal,
      recentTransactions: recentTransactions,
    );
  }

  factory ChildHomeData.fromJson(Map<String, dynamic> json) {
    final data = json['data'] is Map<String, dynamic>
        ? json['data'] as Map<String, dynamic>
        : json;
    final child = data['child'] is Map<String, dynamic>
        ? data['child'] as Map<String, dynamic>
        : data;
    final transactionsJson = data['recent_transactions'];

    return ChildHomeData(
      id: _requiredInt(child['id'], 'id'),
      name: _requiredString(child['name'], 'name'),
      profileImageUrl: _optionalString(child['profile_image_url']),
      balance: _requiredInt(data['balance'] ?? child['balance'], 'balance'),
      savingsGoal: data['savings_goal'] is Map<String, dynamic>
          ? ChildSavingsGoal.fromJson(
              data['savings_goal'] as Map<String, dynamic>,
            )
          : null,
      recentTransactions: transactionsJson is List
          ? transactionsJson
                .map((item) {
                  if (item is! Map<String, dynamic>) {
                    throw const FormatException('取引履歴の形式が正しくありません。');
                  }
                  return ChildTransaction.fromJson(item);
                })
                .toList(growable: false)
          : const [],
    );
  }
}

class ChildSavingsGoal {
  const ChildSavingsGoal({
    required this.title,
    required this.targetAmount,
    required this.savedAmount,
  });

  final String title;
  final int targetAmount;
  final int savedAmount;

  int get remainingAmount =>
      (targetAmount - savedAmount).clamp(0, targetAmount);

  double get progress {
    if (targetAmount <= 0) return 0;
    return (savedAmount / targetAmount).clamp(0, 1);
  }

  int get progressPercent => (progress * 100).round();

  factory ChildSavingsGoal.fromJson(Map<String, dynamic> json) {
    return ChildSavingsGoal(
      title: _requiredString(
        json['title'] ?? json['name'],
        'savings_goal.title',
      ),
      targetAmount: _requiredInt(
        json['target_amount'],
        'savings_goal.target_amount',
      ),
      savedAmount: _requiredInt(
        json['saved_amount'] ?? json['current_amount'],
        'savings_goal.saved_amount',
      ),
    );
  }
}

class ChildTransaction extends Transaction {
  const ChildTransaction({
    required super.id,
    required super.title,
    required super.amount,
    required super.type,
    required super.occurredAt,
  });

  factory ChildTransaction.fromJson(Map<String, dynamic> json) {
    final transaction = Transaction.fromJson(json);
    return ChildTransaction(
      id: transaction.id,
      title: transaction.title,
      amount: transaction.amount,
      type: transaction.type,
      occurredAt: transaction.occurredAt,
    );
  }
}

int _requiredInt(Object? value, String field) {
  if (value is int) return value;
  if (value is num) return value.toInt();
  throw FormatException('$fieldの形式が正しくありません。');
}

String _requiredString(Object? value, String field) {
  if (value is String && value.isNotEmpty) return value;
  throw FormatException('$fieldの形式が正しくありません。');
}

String? _optionalString(Object? value) {
  return value is String && value.isNotEmpty ? value : null;
}
