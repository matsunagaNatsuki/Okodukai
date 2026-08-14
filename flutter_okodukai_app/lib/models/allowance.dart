class Allowance {
  const Allowance({
    required this.id,
    required this.amount,
    required this.paymentDay,
    required this.isActive,
    this.childUserId,
  });

  final int id;
  final int? childUserId;
  final int amount;
  final int paymentDay;
  final bool isActive;

  factory Allowance.fromJson(Map<String, dynamic> json) {
    final id = _int(json['id']);
    final childId = _int(json['child_user_id'] ?? json['user_id']);
    final amount = _int(json['amount'] ?? json['monthly_amount']);
    final day = _int(json['payment_day']);
    final active = _bool(json['is_active'] ?? json['enabled']);
    if (id == null ||
        amount == null ||
        day == null ||
        active == null ||
        amount < 1 ||
        day < 1 ||
        day > 31) {
      throw const FormatException('定期おこづかい設定の形式が正しくありません。');
    }
    return Allowance(
      id: id,
      childUserId: childId,
      amount: amount,
      paymentDay: day,
      isActive: active,
    );
  }

  Map<String, dynamic> toJson() => {
    'id': id,
    if (childUserId != null) 'child_user_id': childUserId,
    'amount': amount,
    'payment_day': paymentDay,
    'is_active': isActive,
  };
}

int? _int(Object? value) =>
    value is num ? value.toInt() : int.tryParse(value?.toString() ?? '');
bool? _bool(Object? value) => value is bool
    ? value
    : value is num
    ? value != 0
    : null;
