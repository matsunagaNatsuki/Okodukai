import '../models/child_expense_history.dart';
import '../models/child_expense_result.dart';
import '../models/parent_child_history.dart';
import 'child/child_expense_history_service.dart';
import 'child/child_expense_service.dart';
import 'parent/parent_child_history_service.dart';

class TransactionService {
  TransactionService({
    ChildExpenseService? expenseService,
    ChildExpenseHistoryService? childHistoryService,
    ParentChildHistoryService? parentHistoryService,
  }) : _expenseService = expenseService ?? ChildExpenseService(),
       _childHistoryService =
           childHistoryService ?? ChildExpenseHistoryService(),
       _parentHistoryService =
           parentHistoryService ?? ParentChildHistoryService();

  final ChildExpenseService _expenseService;
  final ChildExpenseHistoryService _childHistoryService;
  final ParentChildHistoryService _parentHistoryService;

  Future<ChildExpenseResult> createExpense({
    required String description,
    required int amount,
    required DateTime usedOn,
  }) => _expenseService.createExpense(
    description: description,
    amount: amount,
    usedOn: usedOn,
  );

  Future<ChildExpenseHistoryPage> fetchCurrentChildExpenses({int page = 1}) =>
      _childHistoryService.fetchExpenses(page: page);

  Future<ParentExpenseHistoryPage> fetchChildExpenses({
    required int childUserId,
    int page = 1,
  }) =>
      _parentHistoryService.fetchExpenses(childUserId: childUserId, page: page);
}
