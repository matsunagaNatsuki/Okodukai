class ChildExpenseHistoryPage {
  const ChildExpenseHistoryPage({
    required this.currentBalance,
    required this.expenses,
    required this.currentPage,
    required this.lastPage,
  });

  final int currentBalance;
  final List<ChildExpenseHistoryItem> expenses;
  final int currentPage;
  final int lastPage;

  bool get hasNextPage => currentPage < lastPage;

  factory ChildExpenseHistoryPage.fromJson(Map<String, dynamic> json) {
    final responseData = json['data'];
    final container = responseData is Map<String, dynamic>
        ? responseData
        : json;
    final paginationSource =
        container['expenses'] ?? (responseData is List ? json : null);
    if (paginationSource is! Map<String, dynamic>) {
      throw const FormatException('支出履歴のページ情報が含まれていません。');
    }

    final list = paginationSource['data'];
    if (list is! List) {
      throw const FormatException('支出履歴の一覧が含まれていません。');
    }

    return ChildExpenseHistoryPage(
      currentBalance: _requiredInt(
        container['current_balance'] ?? json['current_balance'],
        'current_balance',
      ),
      expenses: list
          .map((item) {
            if (item is! Map<String, dynamic>) {
              throw const FormatException('支出履歴の形式が正しくありません。');
            }
            return ChildExpenseHistoryItem.fromJson(item);
          })
          .toList(growable: false),
      currentPage: _requiredInt(
        paginationSource['current_page'],
        'current_page',
      ),
      lastPage: _requiredInt(paginationSource['last_page'], 'last_page'),
    );
  }
}

class ChildExpenseHistoryItem {
  const ChildExpenseHistoryItem({
    required this.id,
    required this.description,
    required this.amount,
    required this.usedOn,
  });

  final int id;
  final String description;
  final int amount;
  final DateTime usedOn;

  factory ChildExpenseHistoryItem.fromJson(Map<String, dynamic> json) {
    final usedOn = DateTime.tryParse(
      _requiredString(json['used_on'] ?? json['spent_at'], 'used_on'),
    );
    if (usedOn == null) {
      throw const FormatException('使用日の形式が正しくありません。');
    }

    return ChildExpenseHistoryItem(
      id: _requiredInt(json['id'], 'id'),
      description: _requiredString(
        json['description'] ?? json['title'],
        'description',
      ),
      amount: _requiredInt(json['amount'], 'amount').abs(),
      usedOn: usedOn,
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
