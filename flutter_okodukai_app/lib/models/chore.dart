class Chore {
  const Chore({
    required this.id,
    required this.description,
    required this.rewardAmount,
    this.familyId,
    this.isActive,
  });

  final int id;
  final String description;
  final int rewardAmount;
  final int? familyId;
  final bool? isActive;

  factory Chore.fromJson(Map<String, dynamic> json) {
    final id = _int(json['id']);
    final description = json['description'] ?? json['name'] ?? json['title'];
    final reward = _int(json['reward_amount'] ?? json['amount']);
    if (id == null ||
        description is! String ||
        description.isEmpty ||
        reward == null) {
      throw const FormatException('お手伝いの形式が正しくありません。');
    }
    return Chore(
      id: id,
      description: description,
      rewardAmount: reward,
      familyId: _int(json['family_id']),
      isActive: _bool(json['is_active']),
    );
  }

  Map<String, dynamic> toJson() => {
    'id': id,
    'description': description,
    'reward_amount': rewardAmount,
    if (familyId != null) 'family_id': familyId,
    if (isActive != null) 'is_active': isActive,
  };
}

int? _int(Object? value) => value is num
    ? value.toInt()
    : value is String
    ? int.tryParse(value)
    : null;

bool? _bool(Object? value) => value is bool
    ? value
    : value is num
    ? value != 0
    : null;
