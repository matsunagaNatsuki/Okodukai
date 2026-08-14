class Transaction {
  const Transaction({
    required this.id,
    required this.title,
    required this.amount,
    required this.type,
    required this.occurredAt,
    this.userId,
  });

  final int id;
  final int? userId;
  final String title;
  final int amount;
  final String type;
  final DateTime occurredAt;

  bool get isIncome => type == 'income' || type == 'allowance';

  factory Transaction.fromJson(Map<String, dynamic> json) {
    final id = _int(json['id']);
    final amount = _int(json['amount']);
    final title = json['title'] ?? json['description'];
    final type = json['type'];
    final date = DateTime.tryParse(
      (json['occurred_at'] ?? json['date'])?.toString() ?? '',
    );
    if (id == null ||
        amount == null ||
        title is! String ||
        title.isEmpty ||
        type is! String ||
        date == null) {
      throw const FormatException('取引履歴の形式が正しくありません。');
    }
    return Transaction(
      id: id,
      userId: _int(json['user_id'] ?? json['child_user_id']),
      title: title,
      amount: amount.abs(),
      type: type,
      occurredAt: date,
    );
  }

  Map<String, dynamic> toJson() => {
    'id': id,
    if (userId != null) 'user_id': userId,
    'title': title,
    'amount': amount,
    'type': type,
    'occurred_at': occurredAt.toIso8601String(),
  };
}

int? _int(Object? value) =>
    value is num ? value.toInt() : int.tryParse(value?.toString() ?? '');
