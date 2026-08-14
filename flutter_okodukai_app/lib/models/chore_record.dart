class ChoreRecord {
  const ChoreRecord({
    required this.id,
    required this.description,
    required this.rewardAmount,
    required this.performedOn,
    this.childUserId,
    this.choreId,
  });

  final int id;
  final int? childUserId;
  final int? choreId;
  final String description;
  final int rewardAmount;
  final DateTime performedOn;

  factory ChoreRecord.fromJson(Map<String, dynamic> json) {
    final id = _int(json['id']);
    final reward = _int(json['reward_amount'] ?? json['amount']);
    final description =
        json['description'] ?? json['chore_name'] ?? json['title'];
    final date = DateTime.tryParse(
      (json['performed_on'] ?? json['completed_on'] ?? json['date'])
              ?.toString() ??
          '',
    );
    if (id == null ||
        reward == null ||
        description is! String ||
        description.isEmpty ||
        date == null) {
      throw const FormatException('お手伝い実績の形式が正しくありません。');
    }
    return ChoreRecord(
      id: id,
      childUserId: _int(json['child_user_id'] ?? json['user_id']),
      choreId: _int(json['chore_id'] ?? json['chore_setting_id']),
      description: description,
      rewardAmount: reward.abs(),
      performedOn: date,
    );
  }

  Map<String, dynamic> toJson() => {
    'id': id,
    if (childUserId != null) 'child_user_id': childUserId,
    if (choreId != null) 'chore_id': choreId,
    'description': description,
    'reward_amount': rewardAmount,
    'performed_on': _date(performedOn),
  };
}

int? _int(Object? value) =>
    value is num ? value.toInt() : int.tryParse(value?.toString() ?? '');
String _date(DateTime value) =>
    '${value.year.toString().padLeft(4, '0')}-${value.month.toString().padLeft(2, '0')}-${value.day.toString().padLeft(2, '0')}';
