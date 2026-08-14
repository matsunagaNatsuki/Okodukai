import 'child_chore_history.dart';
import 'child_expense_history.dart';

class ParentExpenseHistoryPage {
  const ParentExpenseHistoryPage({
    required this.childUserId,
    required this.childName,
    required this.currentBalance,
    required this.expenses,
    required this.currentPage,
    required this.lastPage,
  });

  final int childUserId;
  final String childName;
  final int currentBalance;
  final List<ChildExpenseHistoryItem> expenses;
  final int currentPage;
  final int lastPage;

  factory ParentExpenseHistoryPage.fromJson(Map<String, dynamic> json) {
    final data = _data(json);
    final child = _child(data);
    final pagination = _pagination(data['expenses'], '支出履歴');
    return ParentExpenseHistoryPage(
      childUserId: _requiredInt(child['id'] ?? child['user_id'], 'child.id'),
      childName: _requiredString(child['name'], 'child.name'),
      currentBalance: _requiredInt(
        data['current_balance'] ?? data['balance'],
        'current_balance',
      ),
      expenses: _items(
        pagination,
        '支出履歴',
      ).map(ChildExpenseHistoryItem.fromJson).toList(growable: false),
      currentPage: _requiredInt(pagination['current_page'], 'current_page'),
      lastPage: _requiredInt(pagination['last_page'], 'last_page'),
    );
  }
}

class ParentChoreHistoryPage {
  const ParentChoreHistoryPage({
    required this.childUserId,
    required this.childName,
    required this.currentBalance,
    required this.chores,
    required this.currentPage,
    required this.lastPage,
  });

  final int childUserId;
  final String childName;
  final int currentBalance;
  final List<ChildChoreHistoryItem> chores;
  final int currentPage;
  final int lastPage;

  factory ParentChoreHistoryPage.fromJson(Map<String, dynamic> json) {
    final data = _data(json);
    final child = _child(data);
    final pagination = _pagination(
      data['chores'] ?? data['chore_records'],
      'お手伝い履歴',
    );
    return ParentChoreHistoryPage(
      childUserId: _requiredInt(child['id'] ?? child['user_id'], 'child.id'),
      childName: _requiredString(child['name'], 'child.name'),
      currentBalance: _requiredInt(
        data['current_balance'] ?? data['balance'],
        'current_balance',
      ),
      chores: _items(
        pagination,
        'お手伝い履歴',
      ).map(ChildChoreHistoryItem.fromJson).toList(growable: false),
      currentPage: _requiredInt(pagination['current_page'], 'current_page'),
      lastPage: _requiredInt(pagination['last_page'], 'last_page'),
    );
  }
}

Map<String, dynamic> _data(Map<String, dynamic> json) =>
    json['data'] is Map<String, dynamic>
    ? json['data'] as Map<String, dynamic>
    : json;

Map<String, dynamic> _child(Map<String, dynamic> data) {
  final child = data['child'];
  if (child is! Map<String, dynamic>) {
    throw const FormatException('お子様情報が含まれていません。');
  }
  return child;
}

Map<String, dynamic> _pagination(Object? value, String label) {
  if (value is! Map<String, dynamic>) {
    throw FormatException('$labelのページ情報が含まれていません。');
  }
  return value;
}

List<Map<String, dynamic>> _items(
  Map<String, dynamic> pagination,
  String label,
) {
  final items = pagination['data'];
  if (items is! List) throw FormatException('$labelの一覧が含まれていません。');
  return items
      .map((item) {
        if (item is! Map<String, dynamic>) {
          throw FormatException('$labelの形式が正しくありません。');
        }
        return item;
      })
      .toList(growable: false);
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
