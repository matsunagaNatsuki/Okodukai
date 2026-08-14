import 'package:flutter/material.dart';
import 'package:intl/intl.dart';

import '../../models/child_expense_history.dart';
import '../../services/child/child_expense_history_service.dart';
import '../../theme/app_colors.dart';
import '../../utils/currency_formatter.dart';
import '../../widgets/app_button.dart';
import '../../widgets/app_card.dart';
import '../../widgets/app_error_message.dart';
import '../../widgets/app_loading_indicator.dart';

class ChildExpenseHistoryScreen extends StatefulWidget {
  const ChildExpenseHistoryScreen({super.key, this.historyService});

  final ChildExpenseHistoryService? historyService;

  @override
  State<ChildExpenseHistoryScreen> createState() =>
      _ChildExpenseHistoryScreenState();
}

class _ChildExpenseHistoryScreenState extends State<ChildExpenseHistoryScreen> {
  final _scrollController = ScrollController();
  late final ChildExpenseHistoryService _historyService;

  List<ChildExpenseHistoryItem> _expenses = [];
  int _currentBalance = 0;
  int _currentPage = 0;
  int _lastPage = 1;
  bool _isInitialLoading = true;
  bool _isLoadingMore = false;
  String? _errorMessage;

  @override
  void initState() {
    super.initState();
    _historyService = widget.historyService ?? ChildExpenseHistoryService();
    _scrollController.addListener(_onScroll);
    _loadFirstPage();
  }

  @override
  void dispose() {
    _scrollController
      ..removeListener(_onScroll)
      ..dispose();
    super.dispose();
  }

  void _onScroll() {
    if (!_scrollController.hasClients ||
        _scrollController.position.extentAfter > 240) {
      return;
    }
    _loadNextPage();
  }

  Future<void> _loadFirstPage() async {
    setState(() {
      _isInitialLoading = true;
      _errorMessage = null;
    });
    try {
      final page = await _historyService.fetchExpenses();
      if (!mounted) return;
      setState(() => _replaceWith(page));
    } on ChildExpenseHistoryException catch (error) {
      if (!mounted) return;
      setState(() => _errorMessage = error.message);
    } catch (_) {
      if (!mounted) return;
      setState(() => _errorMessage = '支出履歴を取得できませんでした。');
    } finally {
      if (mounted) setState(() => _isInitialLoading = false);
    }
  }

  Future<void> _refresh() async {
    try {
      final page = await _historyService.fetchExpenses();
      if (!mounted) return;
      setState(() {
        _errorMessage = null;
        _replaceWith(page);
      });
    } on ChildExpenseHistoryException catch (error) {
      if (!mounted) return;
      ScaffoldMessenger.of(
        context,
      ).showSnackBar(SnackBar(content: Text(error.message)));
    }
  }

  Future<void> _loadNextPage() async {
    if (_isLoadingMore || _currentPage >= _lastPage) return;
    setState(() => _isLoadingMore = true);
    try {
      final page = await _historyService.fetchExpenses(page: _currentPage + 1);
      if (!mounted) return;
      setState(() {
        _currentBalance = page.currentBalance;
        _currentPage = page.currentPage;
        _lastPage = page.lastPage;
        _expenses = [..._expenses, ...page.expenses]
          ..sort((a, b) => b.usedOn.compareTo(a.usedOn));
      });
    } on ChildExpenseHistoryException catch (error) {
      if (!mounted) return;
      ScaffoldMessenger.of(
        context,
      ).showSnackBar(SnackBar(content: Text(error.message)));
    } finally {
      if (mounted) setState(() => _isLoadingMore = false);
    }
  }

  void _replaceWith(ChildExpenseHistoryPage page) {
    _currentBalance = page.currentBalance;
    _currentPage = page.currentPage;
    _lastPage = page.lastPage;
    _expenses = [...page.expenses]
      ..sort((a, b) => b.usedOn.compareTo(a.usedOn));
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('これまでの支出履歴')),
      body: SafeArea(child: _buildBody()),
    );
  }

  Widget _buildBody() {
    if (_isInitialLoading) {
      return const AppLoadingIndicator(message: '支出履歴を読み込み中...');
    }
    if (_errorMessage != null) {
      return _HistoryError(message: _errorMessage!, onRetry: _loadFirstPage);
    }

    return RefreshIndicator(
      onRefresh: _refresh,
      child: ListView.builder(
        controller: _scrollController,
        physics: const AlwaysScrollableScrollPhysics(),
        padding: const EdgeInsets.fromLTRB(20, 16, 20, 32),
        itemCount: 1 + (_expenses.isEmpty ? 1 : _expenses.length) + 1,
        itemBuilder: (context, index) {
          if (index == 0) {
            return Padding(
              padding: const EdgeInsets.only(bottom: 20),
              child: _CurrentBalanceCard(balance: _currentBalance),
            );
          }
          if (_expenses.isEmpty) {
            if (index == 1) {
              return const AppCard(
                child: Padding(
                  padding: EdgeInsets.symmetric(vertical: 28),
                  child: Center(child: Text('まだ支出履歴がありません')),
                ),
              );
            }
          } else if (index <= _expenses.length) {
            return Padding(
              padding: const EdgeInsets.only(bottom: 10),
              child: _ExpenseCard(expense: _expenses[index - 1]),
            );
          }

          return _isLoadingMore
              ? const Padding(
                  padding: EdgeInsets.all(16),
                  child: Center(child: CircularProgressIndicator()),
                )
              : const SizedBox(height: 1);
        },
      ),
    );
  }
}

class _CurrentBalanceCard extends StatelessWidget {
  const _CurrentBalanceCard({required this.balance});

  final int balance;

  @override
  Widget build(BuildContext context) {
    return AppCard(
      child: Column(
        children: [
          Text('現在のおこづかい残高', style: Theme.of(context).textTheme.titleMedium),
          const SizedBox(height: 6),
          FittedBox(
            child: Text(
              CurrencyFormatter.yen(balance),
              style: Theme.of(context).textTheme.headlineMedium?.copyWith(
                color: AppColors.primaryDark,
                fontWeight: FontWeight.w900,
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _ExpenseCard extends StatelessWidget {
  const _ExpenseCard({required this.expense});

  final ChildExpenseHistoryItem expense;

  @override
  Widget build(BuildContext context) {
    return AppCard(
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(10),
            decoration: const BoxDecoration(
              color: AppColors.background,
              shape: BoxShape.circle,
            ),
            child: const Icon(
              Icons.shopping_bag_outlined,
              color: AppColors.primaryDark,
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  expense.description,
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                  style: const TextStyle(fontWeight: FontWeight.w700),
                ),
                const SizedBox(height: 4),
                Text(
                  DateFormat('yyyy年M月d日').format(expense.usedOn),
                  style: Theme.of(context).textTheme.bodySmall?.copyWith(
                    color: AppColors.textSecondary,
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(width: 10),
          Text(
            '-${CurrencyFormatter.yen(expense.amount)}',
            style: const TextStyle(fontWeight: FontWeight.w800),
          ),
        ],
      ),
    );
  }
}

class _HistoryError extends StatelessWidget {
  const _HistoryError({required this.message, required this.onRetry});

  final String message;
  final VoidCallback onRetry;

  @override
  Widget build(BuildContext context) {
    return Center(
      child: SingleChildScrollView(
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            AppErrorMessage(message: message),
            const SizedBox(height: 16),
            AppButton(label: 'もう一度読み込む', onPressed: onRetry),
          ],
        ),
      ),
    );
  }
}
