import 'package:flutter/material.dart';
import 'package:intl/intl.dart';

import '../../models/child_chore_history.dart';
import '../../models/child_expense_history.dart';
import '../../models/parent_child_history.dart';
import '../../services/parent/parent_child_history_service.dart';
import '../../theme/app_colors.dart';
import '../../utils/currency_formatter.dart';
import '../../widgets/app_button.dart';
import '../../widgets/app_card.dart';
import '../../widgets/app_error_message.dart';
import '../../widgets/app_loading_indicator.dart';

class ParentExpenseHistoryScreen extends StatefulWidget {
  const ParentExpenseHistoryScreen({
    required this.childUserId,
    super.key,
    this.historyService,
  });
  final int childUserId;
  final ParentChildHistoryService? historyService;

  @override
  State<ParentExpenseHistoryScreen> createState() =>
      _ParentExpenseHistoryScreenState();
}

class _ParentExpenseHistoryScreenState
    extends State<ParentExpenseHistoryScreen> {
  final _scrollController = ScrollController();
  late final ParentChildHistoryService _service;
  List<ChildExpenseHistoryItem> _items = [];
  String _childName = '';
  int _balance = 0;
  int _currentPage = 0;
  int _lastPage = 1;
  bool _initialLoading = true;
  bool _loadingMore = false;
  String? _error;

  @override
  void initState() {
    super.initState();
    _service = widget.historyService ?? ParentChildHistoryService();
    _scrollController.addListener(_onScroll);
    _loadFirst();
  }

  @override
  void dispose() {
    _scrollController
      ..removeListener(_onScroll)
      ..dispose();
    super.dispose();
  }

  void _onScroll() {
    if (_scrollController.hasClients &&
        _scrollController.position.extentAfter < 240) {
      _loadNext();
    }
  }

  Future<void> _loadFirst() async {
    setState(() {
      _initialLoading = true;
      _error = null;
    });
    try {
      final page = await _service.fetchExpenses(
        childUserId: widget.childUserId,
      );
      if (mounted) setState(() => _replace(page));
    } on ParentChildHistoryException catch (error) {
      if (mounted) setState(() => _error = error.message);
    } finally {
      if (mounted) setState(() => _initialLoading = false);
    }
  }

  Future<void> _refresh() async {
    try {
      final page = await _service.fetchExpenses(
        childUserId: widget.childUserId,
      );
      if (mounted) setState(() => _replace(page));
    } on ParentChildHistoryException catch (error) {
      if (mounted) {
        ScaffoldMessenger.of(
          context,
        ).showSnackBar(SnackBar(content: Text(error.message)));
      }
    }
  }

  Future<void> _loadNext() async {
    if (_loadingMore || _currentPage >= _lastPage) return;
    setState(() => _loadingMore = true);
    try {
      final page = await _service.fetchExpenses(
        childUserId: widget.childUserId,
        page: _currentPage + 1,
      );
      if (!mounted) return;
      setState(() {
        _balance = page.currentBalance;
        _currentPage = page.currentPage;
        _lastPage = page.lastPage;
        _items = [..._items, ...page.expenses]
          ..sort((a, b) => b.usedOn.compareTo(a.usedOn));
      });
    } on ParentChildHistoryException catch (error) {
      if (mounted) {
        ScaffoldMessenger.of(
          context,
        ).showSnackBar(SnackBar(content: Text(error.message)));
      }
    } finally {
      if (mounted) setState(() => _loadingMore = false);
    }
  }

  void _replace(ParentExpenseHistoryPage page) {
    _childName = page.childName;
    _balance = page.currentBalance;
    _currentPage = page.currentPage;
    _lastPage = page.lastPage;
    _items = [...page.expenses]..sort((a, b) => b.usedOn.compareTo(a.usedOn));
  }

  @override
  Widget build(BuildContext context) => Scaffold(
    appBar: AppBar(title: const Text('支出履歴')),
    body: SafeArea(child: _body()),
  );

  Widget _body() {
    if (_initialLoading) {
      return const AppLoadingIndicator(message: '支出履歴を読み込み中...');
    }
    if (_error != null) {
      return _HistoryError(message: _error!, onRetry: _loadFirst);
    }
    return RefreshIndicator(
      onRefresh: _refresh,
      child: ListView.builder(
        controller: _scrollController,
        physics: const AlwaysScrollableScrollPhysics(),
        padding: const EdgeInsets.fromLTRB(20, 16, 20, 32),
        itemCount: 1 + (_items.isEmpty ? 1 : _items.length) + 1,
        itemBuilder: (context, index) {
          if (index == 0) {
            return _HeaderCard(name: _childName, balance: _balance);
          }
          if (_items.isEmpty && index == 1) {
            return const _EmptyCard(message: 'まだ支出履歴がありません');
          }
          if (_items.isNotEmpty && index <= _items.length) {
            return _ExpenseCard(item: _items[index - 1]);
          }
          return _MoreIndicator(visible: _loadingMore);
        },
      ),
    );
  }
}

class ParentChoreHistoryScreen extends StatefulWidget {
  const ParentChoreHistoryScreen({
    required this.childUserId,
    super.key,
    this.historyService,
  });
  final int childUserId;
  final ParentChildHistoryService? historyService;

  @override
  State<ParentChoreHistoryScreen> createState() =>
      _ParentChoreHistoryScreenState();
}

class _ParentChoreHistoryScreenState extends State<ParentChoreHistoryScreen> {
  final _scrollController = ScrollController();
  late final ParentChildHistoryService _service;
  List<ChildChoreHistoryItem> _items = [];
  String _childName = '';
  int _balance = 0;
  int _currentPage = 0;
  int _lastPage = 1;
  bool _initialLoading = true;
  bool _loadingMore = false;
  String? _error;

  @override
  void initState() {
    super.initState();
    _service = widget.historyService ?? ParentChildHistoryService();
    _scrollController.addListener(_onScroll);
    _loadFirst();
  }

  @override
  void dispose() {
    _scrollController
      ..removeListener(_onScroll)
      ..dispose();
    super.dispose();
  }

  void _onScroll() {
    if (_scrollController.hasClients &&
        _scrollController.position.extentAfter < 240) {
      _loadNext();
    }
  }

  Future<void> _loadFirst() async {
    setState(() {
      _initialLoading = true;
      _error = null;
    });
    try {
      final page = await _service.fetchChores(childUserId: widget.childUserId);
      if (mounted) setState(() => _replace(page));
    } on ParentChildHistoryException catch (error) {
      if (mounted) setState(() => _error = error.message);
    } finally {
      if (mounted) setState(() => _initialLoading = false);
    }
  }

  Future<void> _refresh() async {
    try {
      final page = await _service.fetchChores(childUserId: widget.childUserId);
      if (mounted) setState(() => _replace(page));
    } on ParentChildHistoryException catch (error) {
      if (mounted) {
        ScaffoldMessenger.of(
          context,
        ).showSnackBar(SnackBar(content: Text(error.message)));
      }
    }
  }

  Future<void> _loadNext() async {
    if (_loadingMore || _currentPage >= _lastPage) return;
    setState(() => _loadingMore = true);
    try {
      final page = await _service.fetchChores(
        childUserId: widget.childUserId,
        page: _currentPage + 1,
      );
      if (!mounted) return;
      setState(() {
        _balance = page.currentBalance;
        _currentPage = page.currentPage;
        _lastPage = page.lastPage;
        _items = [..._items, ...page.chores]
          ..sort((a, b) => b.completedOn.compareTo(a.completedOn));
      });
    } on ParentChildHistoryException catch (error) {
      if (mounted) {
        ScaffoldMessenger.of(
          context,
        ).showSnackBar(SnackBar(content: Text(error.message)));
      }
    } finally {
      if (mounted) setState(() => _loadingMore = false);
    }
  }

  void _replace(ParentChoreHistoryPage page) {
    _childName = page.childName;
    _balance = page.currentBalance;
    _currentPage = page.currentPage;
    _lastPage = page.lastPage;
    _items = [...page.chores]
      ..sort((a, b) => b.completedOn.compareTo(a.completedOn));
  }

  @override
  Widget build(BuildContext context) => Scaffold(
    appBar: AppBar(title: const Text('お手伝い履歴')),
    body: SafeArea(child: _body()),
  );

  Widget _body() {
    if (_initialLoading) {
      return const AppLoadingIndicator(message: 'お手伝い履歴を読み込み中...');
    }
    if (_error != null) {
      return _HistoryError(message: _error!, onRetry: _loadFirst);
    }
    return RefreshIndicator(
      onRefresh: _refresh,
      child: ListView.builder(
        controller: _scrollController,
        physics: const AlwaysScrollableScrollPhysics(),
        padding: const EdgeInsets.fromLTRB(20, 16, 20, 32),
        itemCount: 1 + (_items.isEmpty ? 1 : _items.length) + 1,
        itemBuilder: (context, index) {
          if (index == 0) {
            return _HeaderCard(name: _childName, balance: _balance);
          }
          if (_items.isEmpty && index == 1) {
            return const _EmptyCard(message: 'まだお手伝い履歴がありません');
          }
          if (_items.isNotEmpty && index <= _items.length) {
            return _ChoreCard(item: _items[index - 1]);
          }
          return _MoreIndicator(visible: _loadingMore);
        },
      ),
    );
  }
}

class _HeaderCard extends StatelessWidget {
  const _HeaderCard({required this.name, required this.balance});
  final String name;
  final int balance;

  @override
  Widget build(BuildContext context) => Padding(
    padding: const EdgeInsets.only(bottom: 20),
    child: AppCard(
      child: Row(
        children: [
          const CircleAvatar(
            backgroundColor: AppColors.accent,
            child: Icon(Icons.face_rounded, color: AppColors.primaryDark),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text('$nameさん', style: Theme.of(context).textTheme.titleMedium),
                const SizedBox(height: 4),
                Text('現在残高 ${CurrencyFormatter.yen(balance)}'),
              ],
            ),
          ),
        ],
      ),
    ),
  );
}

class _ExpenseCard extends StatelessWidget {
  const _ExpenseCard({required this.item});
  final ChildExpenseHistoryItem item;
  @override
  Widget build(BuildContext context) => Padding(
    padding: const EdgeInsets.only(bottom: 10),
    child: AppCard(
      child: ListTile(
        contentPadding: EdgeInsets.zero,
        title: Text(item.description),
        subtitle: Text(DateFormat('yyyy年M月d日').format(item.usedOn)),
        trailing: Text(
          '-${CurrencyFormatter.yen(item.amount)}',
          style: const TextStyle(fontWeight: FontWeight.bold),
        ),
      ),
    ),
  );
}

class _ChoreCard extends StatelessWidget {
  const _ChoreCard({required this.item});
  final ChildChoreHistoryItem item;
  @override
  Widget build(BuildContext context) => Padding(
    padding: const EdgeInsets.only(bottom: 10),
    child: AppCard(
      child: ListTile(
        contentPadding: EdgeInsets.zero,
        title: Text(item.description),
        subtitle: Text(DateFormat('yyyy年M月d日').format(item.completedOn)),
        trailing: Text(
          CurrencyFormatter.yen(item.rewardAmount),
          style: const TextStyle(
            color: AppColors.primaryDark,
            fontWeight: FontWeight.bold,
          ),
        ),
      ),
    ),
  );
}

class _EmptyCard extends StatelessWidget {
  const _EmptyCard({required this.message});
  final String message;
  @override
  Widget build(BuildContext context) => AppCard(
    child: Padding(
      padding: const EdgeInsets.symmetric(vertical: 28),
      child: Center(child: Text(message)),
    ),
  );
}

class _MoreIndicator extends StatelessWidget {
  const _MoreIndicator({required this.visible});
  final bool visible;
  @override
  Widget build(BuildContext context) => visible
      ? const Padding(
          padding: EdgeInsets.all(16),
          child: Center(child: CircularProgressIndicator()),
        )
      : const SizedBox(height: 1);
}

class _HistoryError extends StatelessWidget {
  const _HistoryError({required this.message, required this.onRetry});
  final String message;
  final VoidCallback onRetry;
  @override
  Widget build(BuildContext context) => Center(
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
