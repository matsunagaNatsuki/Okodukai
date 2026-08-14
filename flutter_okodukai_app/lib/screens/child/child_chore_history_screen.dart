import 'package:flutter/material.dart';
import 'package:intl/intl.dart';

import '../../models/child_chore_history.dart';
import '../../services/child/child_chore_history_service.dart';
import '../../theme/app_colors.dart';
import '../../utils/currency_formatter.dart';
import '../../widgets/app_button.dart';
import '../../widgets/app_card.dart';
import '../../widgets/app_error_message.dart';
import '../../widgets/app_loading_indicator.dart';

class ChildChoreHistoryScreen extends StatefulWidget {
  const ChildChoreHistoryScreen({super.key, this.historyService});

  final ChildChoreHistoryService? historyService;

  @override
  State<ChildChoreHistoryScreen> createState() =>
      _ChildChoreHistoryScreenState();
}

class _ChildChoreHistoryScreenState extends State<ChildChoreHistoryScreen> {
  final _scrollController = ScrollController();
  late final ChildChoreHistoryService _historyService;

  List<ChildChoreHistoryItem> _chores = [];
  int _currentPage = 0;
  int _lastPage = 1;
  bool _isInitialLoading = true;
  bool _isLoadingMore = false;
  String? _errorMessage;

  @override
  void initState() {
    super.initState();
    _historyService = widget.historyService ?? ChildChoreHistoryService();
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
      final page = await _historyService.fetchChores();
      if (!mounted) return;
      setState(() => _replaceWith(page));
    } on ChildChoreHistoryException catch (error) {
      if (!mounted) return;
      setState(() => _errorMessage = error.message);
    } catch (_) {
      if (!mounted) return;
      setState(() => _errorMessage = 'お手伝い履歴を取得できませんでした。');
    } finally {
      if (mounted) setState(() => _isInitialLoading = false);
    }
  }

  Future<void> _loadNextPage() async {
    if (_isLoadingMore || _currentPage >= _lastPage) return;
    setState(() => _isLoadingMore = true);
    try {
      final page = await _historyService.fetchChores(page: _currentPage + 1);
      if (!mounted) return;
      setState(() {
        _currentPage = page.currentPage;
        _lastPage = page.lastPage;
        _chores = [..._chores, ...page.chores]
          ..sort((a, b) => b.completedOn.compareTo(a.completedOn));
      });
    } on ChildChoreHistoryException catch (error) {
      if (!mounted) return;
      ScaffoldMessenger.of(
        context,
      ).showSnackBar(SnackBar(content: Text(error.message)));
    } finally {
      if (mounted) setState(() => _isLoadingMore = false);
    }
  }

  void _replaceWith(ChildChoreHistoryPage page) {
    _currentPage = page.currentPage;
    _lastPage = page.lastPage;
    _chores = [...page.chores]
      ..sort((a, b) => b.completedOn.compareTo(a.completedOn));
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('これまでのお手伝い')),
      body: SafeArea(child: _buildBody()),
    );
  }

  Widget _buildBody() {
    if (_isInitialLoading) {
      return const AppLoadingIndicator(message: 'お手伝い履歴を読み込み中...');
    }
    if (_errorMessage != null) {
      return _HistoryError(message: _errorMessage!, onRetry: _loadFirstPage);
    }

    return ListView.builder(
      controller: _scrollController,
      physics: const AlwaysScrollableScrollPhysics(),
      padding: const EdgeInsets.fromLTRB(20, 16, 20, 32),
      itemCount: (_chores.isEmpty ? 1 : _chores.length) + 1,
      itemBuilder: (context, index) {
        if (_chores.isEmpty && index == 0) {
          return const AppCard(
            child: Padding(
              padding: EdgeInsets.symmetric(vertical: 28),
              child: Center(child: Text('まだお手伝い履歴がありません')),
            ),
          );
        }
        if (_chores.isNotEmpty && index < _chores.length) {
          return Padding(
            padding: const EdgeInsets.only(bottom: 10),
            child: _ChoreCard(chore: _chores[index]),
          );
        }
        return _isLoadingMore
            ? const Padding(
                padding: EdgeInsets.all(16),
                child: Center(child: CircularProgressIndicator()),
              )
            : const SizedBox(height: 1);
      },
    );
  }
}

class _ChoreCard extends StatelessWidget {
  const _ChoreCard({required this.chore});

  final ChildChoreHistoryItem chore;

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
              Icons.volunteer_activism_rounded,
              color: AppColors.primaryDark,
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  chore.description,
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                  style: const TextStyle(fontWeight: FontWeight.w700),
                ),
                const SizedBox(height: 4),
                Text(
                  DateFormat('yyyy年M月d日').format(chore.completedOn),
                  style: Theme.of(context).textTheme.bodySmall?.copyWith(
                    color: AppColors.textSecondary,
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(width: 10),
          Text(
            CurrencyFormatter.yen(chore.rewardAmount),
            style: const TextStyle(
              color: AppColors.primaryDark,
              fontWeight: FontWeight.w800,
            ),
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
