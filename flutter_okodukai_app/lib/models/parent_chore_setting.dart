import 'chore.dart';

class ParentChoreSetting extends Chore {
  const ParentChoreSetting({
    required super.id,
    required super.description,
    required super.rewardAmount,
  });

  factory ParentChoreSetting.fromJson(Map<String, dynamic> json) {
    final chore = Chore.fromJson(json);
    return ParentChoreSetting(
      id: chore.id,
      description: chore.description,
      rewardAmount: chore.rewardAmount,
    );
  }
}

class ParentChoreSettingList {
  const ParentChoreSettingList({required this.settings});

  final List<ParentChoreSetting> settings;

  factory ParentChoreSettingList.fromJson(Map<String, dynamic> json) {
    final data = json['data'];
    final Object? rawSettings;
    if (data is List) {
      rawSettings = data;
    } else if (data is Map<String, dynamic>) {
      rawSettings = data['chore_settings'] ?? data['chores'] ?? data['data'];
    } else {
      rawSettings = json['chore_settings'];
    }
    if (rawSettings is! List) {
      throw const FormatException('お手伝い設定一覧の形式が正しくありません。');
    }
    return ParentChoreSettingList(
      settings: rawSettings
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
