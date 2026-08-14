import 'chore_record.dart';

class ChildChoreHistoryPage {
  const ChildChoreHistoryPage({
    required this.chores,
    required this.currentPage,
    required this.lastPage,
  });

  final List<ChildChoreHistoryItem> chores;
  final int currentPage;
  final int lastPage;

  bool get hasNextPage => currentPage < lastPage;

  factory ChildChoreHistoryPage.fromJson(Map<String, dynamic> json) {
    final responseData = json['data'];
    final pagination =
        responseData is Map<String, dynamic> &&
            responseData['chores'] is Map<String, dynamic>
        ? responseData['chores'] as Map<String, dynamic>
        : json;
    final list = pagination['data'];
    if (list is! List) {
      throw const FormatException('お手伝い履歴の一覧が含まれていません。');
    }

    return ChildChoreHistoryPage(
      chores: list
          .map((item) {
            if (item is! Map<String, dynamic>) {
              throw const FormatException('お手伝い履歴の形式が正しくありません。');
            }
            return ChildChoreHistoryItem.fromJson(item);
          })
          .toList(growable: false),
      currentPage: _requiredInt(pagination['current_page'], 'current_page'),
      lastPage: _requiredInt(pagination['last_page'], 'last_page'),
    );
  }
}

class ChildChoreHistoryItem extends ChoreRecord {
  const ChildChoreHistoryItem({
    required super.id,
    required super.description,
    required super.rewardAmount,
    required this.completedOn,
  }) : super(performedOn: completedOn);

  final DateTime completedOn;

  factory ChildChoreHistoryItem.fromJson(Map<String, dynamic> json) {
    final record = ChoreRecord.fromJson(json);
    return ChildChoreHistoryItem(
      id: record.id,
      description: record.description,
      rewardAmount: record.rewardAmount,
      completedOn: record.performedOn,
    );
  }
}

int _requiredInt(Object? value, String field) {
  if (value is int) return value;
  if (value is num) return value.toInt();
  throw FormatException('$fieldの形式が正しくありません。');
}
