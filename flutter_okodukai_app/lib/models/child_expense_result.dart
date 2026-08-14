class ChildExpenseResult {
  const ChildExpenseResult({
    required this.id,
    required this.description,
    required this.amount,
    required this.usedOn,
    required this.currentBalance,
  });

  final int id;
  final String description;
  final int amount;
  final DateTime usedOn;
  final int currentBalance;

  factory ChildExpenseResult.fromJson(Map<String, dynamic> json) {
    final data = json['data'] is Map<String, dynamic>
        ? json['data'] as Map<String, dynamic>
        : json;
    final expense = data['expense'] is Map<String, dynamic>
        ? data['expense'] as Map<String, dynamic>
        : data;
    final usedOn = DateTime.tryParse(
      _requiredString(expense['used_on'] ?? expense['spent_at'], 'used_on'),
    );
    if (usedOn == null) {
      throw const FormatException('使用日の形式が正しくありません。');
    }

    return ChildExpenseResult(
      id: _requiredInt(expense['id'], 'id'),
      description: _requiredString(
        expense['description'] ?? expense['title'],
        'description',
      ),
      amount: _requiredInt(expense['amount'], 'amount'),
      usedOn: usedOn,
      currentBalance: _requiredInt(
        data['current_balance'] ?? data['balance'],
        'current_balance',
      ),
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
