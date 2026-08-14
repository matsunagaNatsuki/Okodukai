import 'package:flutter/material.dart';
import 'package:intl/intl.dart';

import '../../models/child_home_data.dart';
import '../../models/child_expense_result.dart';
import '../../services/child/child_home_service.dart';
import '../../theme/app_colors.dart';
import '../../utils/currency_formatter.dart';
import '../../widgets/app_button.dart';
import '../../widgets/app_card.dart';
import '../../widgets/app_error_message.dart';
import '../../widgets/app_loading_indicator.dart';
import '../../widgets/logout_button.dart';
import 'child_feature_placeholder_screen.dart';
import 'child_expense_record_screen.dart';
import 'child_expense_history_screen.dart';
import 'child_chore_history_screen.dart';
import 'child_saving_goal_screen.dart';
import 'child_profile_screen.dart';

class ChildHomeScreen extends StatefulWidget {
  const ChildHomeScreen({super.key, this.homeService});

  final ChildHomeService? homeService;

  @override
  State<ChildHomeScreen> createState() => _ChildHomeScreenState();
}

class _ChildHomeScreenState extends State<ChildHomeScreen> {
  late final ChildHomeService _homeService;
  late Future<ChildHomeData> _homeFuture;

  @override
  void initState() {
    super.initState();
    _homeService = widget.homeService ?? ChildHomeService();
    _homeFuture = _homeService.fetchHome();
  }

  void _retry() {
    setState(() => _homeFuture = _homeService.fetchHome());
  }

  void _openFeature(String title) {
    if (title == 'プロフィール') {
      Navigator.of(context).push(
        MaterialPageRoute<void>(builder: (_) => const ChildProfileScreen()),
      );
      return;
    }
    Navigator.of(context).push(
      MaterialPageRoute<void>(
        builder: (_) => ChildFeaturePlaceholderScreen(title: title),
      ),
    );
  }

  Future<void> _openExpenseRecord(ChildHomeData currentData) async {
    final result = await Navigator.of(context).push<ChildExpenseResult>(
      MaterialPageRoute<ChildExpenseResult>(
        builder: (_) =>
            ChildExpenseRecordScreen(currentBalance: currentData.balance),
      ),
    );
    if (!mounted || result == null) return;

    setState(() {
      _homeFuture = Future.value(
        currentData.copyWith(balance: result.currentBalance),
      );
    });
    ScaffoldMessenger.of(
      context,
    ).showSnackBar(const SnackBar(content: Text('支出を登録しました。')));
  }

  void _openExpenseHistory() {
    Navigator.of(context).push(
      MaterialPageRoute<void>(
        builder: (_) => const ChildExpenseHistoryScreen(),
      ),
    );
  }

  void _openChoreHistory() {
    Navigator.of(context).push(
      MaterialPageRoute<void>(builder: (_) => const ChildChoreHistoryScreen()),
    );
  }

  void _openSavingGoal() {
    Navigator.of(context).push(
      MaterialPageRoute<void>(builder: (_) => const ChildSavingGoalScreen()),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('おこづかい')),
      body: SafeArea(
        child: FutureBuilder<ChildHomeData>(
          future: _homeFuture,
          builder: (context, snapshot) {
            if (snapshot.connectionState == ConnectionState.waiting) {
              return const AppLoadingIndicator(message: 'ホームを読み込み中...');
            }
            if (snapshot.hasError) {
              final message = snapshot.error is ChildHomeException
                  ? (snapshot.error! as ChildHomeException).message
                  : 'ホーム情報を取得できませんでした。';
              return _HomeError(message: message, onRetry: _retry);
            }

            return _HomeContent(
              data: snapshot.requireData,
              onOpenFeature: _openFeature,
              onRecordExpense: () => _openExpenseRecord(snapshot.requireData),
              onOpenExpenseHistory: _openExpenseHistory,
              onOpenChoreHistory: _openChoreHistory,
              onOpenSavingGoal: _openSavingGoal,
            );
          },
        ),
      ),
    );
  }
}

class _HomeContent extends StatelessWidget {
  const _HomeContent({
    required this.data,
    required this.onOpenFeature,
    required this.onRecordExpense,
    required this.onOpenExpenseHistory,
    required this.onOpenChoreHistory,
    required this.onOpenSavingGoal,
  });

  final ChildHomeData data;
  final ValueChanged<String> onOpenFeature;
  final VoidCallback onRecordExpense;
  final VoidCallback onOpenExpenseHistory;
  final VoidCallback onOpenChoreHistory;
  final VoidCallback onOpenSavingGoal;

  @override
  Widget build(BuildContext context) {
    final horizontalPadding = MediaQuery.sizeOf(context).width < 360
        ? 16.0
        : 20.0;

    return RefreshIndicator(
      onRefresh: () async {
        final state = context.findAncestorStateOfType<_ChildHomeScreenState>();
        state?._retry();
      },
      child: SingleChildScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        padding: EdgeInsets.fromLTRB(
          horizontalPadding,
          16,
          horizontalPadding,
          32,
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            _ProfileHeader(data: data),
            const SizedBox(height: 16),
            _BalanceCard(balance: data.balance),
            const SizedBox(height: 16),
            _SavingsGoalCard(goal: data.savingsGoal),
            const SizedBox(height: 24),
            Text(
              'メニュー',
              style: Theme.of(
                context,
              ).textTheme.titleLarge?.copyWith(fontWeight: FontWeight.w800),
            ),
            const SizedBox(height: 12),
            _MenuGrid(
              onOpenFeature: onOpenFeature,
              onRecordExpense: onRecordExpense,
              onOpenExpenseHistory: onOpenExpenseHistory,
              onOpenChoreHistory: onOpenChoreHistory,
              onOpenSavingGoal: onOpenSavingGoal,
            ),
            const SizedBox(height: 24),
            Text(
              '最近の取引履歴',
              style: Theme.of(
                context,
              ).textTheme.titleLarge?.copyWith(fontWeight: FontWeight.w800),
            ),
            const SizedBox(height: 12),
            _RecentTransactions(transactions: data.recentTransactions),
            const SizedBox(height: 24),
            const LogoutButton(),
          ],
        ),
      ),
    );
  }
}

class _ProfileHeader extends StatelessWidget {
  const _ProfileHeader({required this.data});

  final ChildHomeData data;

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        _ProfileImage(imageUrl: data.profileImageUrl),
        const SizedBox(width: 14),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                'おかえり！',
                style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                  color: AppColors.textSecondary,
                ),
              ),
              Text(
                '${data.name}さん',
                maxLines: 2,
                overflow: TextOverflow.ellipsis,
                style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                  fontWeight: FontWeight.w800,
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }
}

class _ProfileImage extends StatelessWidget {
  const _ProfileImage({this.imageUrl});

  final String? imageUrl;

  @override
  Widget build(BuildContext context) {
    const fallback = ColoredBox(
      color: AppColors.accent,
      child: Icon(Icons.face_rounded, size: 42, color: AppColors.textPrimary),
    );

    return ClipOval(
      child: SizedBox.square(
        dimension: 76,
        child: imageUrl == null
            ? fallback
            : Image.network(
                imageUrl!,
                fit: BoxFit.cover,
                errorBuilder: (_, _, _) => fallback,
              ),
      ),
    );
  }
}

class _BalanceCard extends StatelessWidget {
  const _BalanceCard({required this.balance});

  final int balance;

  @override
  Widget build(BuildContext context) {
    return AppCard(
      child: Column(
        children: [
          Text('現在のおこづかい残高', style: Theme.of(context).textTheme.titleMedium),
          const SizedBox(height: 6),
          FittedBox(
            fit: BoxFit.scaleDown,
            child: Text(
              CurrencyFormatter.yen(balance),
              style: Theme.of(context).textTheme.displaySmall?.copyWith(
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

class _SavingsGoalCard extends StatelessWidget {
  const _SavingsGoalCard({this.goal});

  final ChildSavingsGoal? goal;

  @override
  Widget build(BuildContext context) {
    if (goal == null) {
      return const AppCard(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('貯金目標', style: TextStyle(fontWeight: FontWeight.w800)),
            SizedBox(height: 8),
            Text('まだ貯金目標が設定されていません。'),
          ],
        ),
      );
    }

    return AppCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Text('貯金目標', style: Theme.of(context).textTheme.titleMedium),
          const SizedBox(height: 4),
          Text(
            goal!.title,
            style: Theme.of(
              context,
            ).textTheme.titleLarge?.copyWith(fontWeight: FontWeight.w800),
          ),
          const SizedBox(height: 12),
          _LabelValue(
            label: '目標金額',
            value: CurrencyFormatter.yen(goal!.targetAmount),
          ),
          const SizedBox(height: 6),
          _LabelValue(
            label: '目標まであと',
            value: CurrencyFormatter.yen(goal!.remainingAmount),
          ),
          const SizedBox(height: 14),
          LinearProgressIndicator(
            value: goal!.progress,
            minHeight: 12,
            borderRadius: BorderRadius.circular(99),
            backgroundColor: AppColors.border,
          ),
          const SizedBox(height: 8),
          Text(
            '達成率 ${goal!.progressPercent}%',
            textAlign: TextAlign.end,
            style: const TextStyle(fontWeight: FontWeight.w700),
          ),
        ],
      ),
    );
  }
}

class _LabelValue extends StatelessWidget {
  const _LabelValue({required this.label, required this.value});

  final String label;
  final String value;

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Expanded(child: Text(label)),
        const SizedBox(width: 12),
        Flexible(
          child: Text(
            value,
            textAlign: TextAlign.end,
            style: const TextStyle(fontWeight: FontWeight.w800),
          ),
        ),
      ],
    );
  }
}

class _MenuGrid extends StatelessWidget {
  const _MenuGrid({
    required this.onOpenFeature,
    required this.onRecordExpense,
    required this.onOpenExpenseHistory,
    required this.onOpenChoreHistory,
    required this.onOpenSavingGoal,
  });

  final ValueChanged<String> onOpenFeature;
  final VoidCallback onRecordExpense;
  final VoidCallback onOpenExpenseHistory;
  final VoidCallback onOpenChoreHistory;
  final VoidCallback onOpenSavingGoal;

  static const items = [
    ('つかったものを記録する', Icons.add_shopping_cart_rounded),
    ('これまでの支出履歴', Icons.receipt_long_rounded),
    ('これまでのお手伝い', Icons.volunteer_activism_rounded),
    ('貯金目標', Icons.flag_rounded),
    ('プロフィール', Icons.person_rounded),
  ];

  @override
  Widget build(BuildContext context) {
    return GridView.builder(
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      itemCount: items.length,
      gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
        crossAxisCount: 2,
        crossAxisSpacing: 12,
        mainAxisSpacing: 12,
        childAspectRatio: 1.35,
      ),
      itemBuilder: (context, index) {
        final item = items[index];
        return AppCard(
          onTap: switch (index) {
            0 => onRecordExpense,
            1 => onOpenExpenseHistory,
            2 => onOpenChoreHistory,
            3 => onOpenSavingGoal,
            _ => () => onOpenFeature(item.$1),
          },
          padding: const EdgeInsets.all(12),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(item.$2, color: AppColors.primaryDark, size: 30),
              const SizedBox(height: 8),
              Flexible(
                child: Text(
                  item.$1,
                  textAlign: TextAlign.center,
                  maxLines: 3,
                  overflow: TextOverflow.ellipsis,
                  style: const TextStyle(fontWeight: FontWeight.w700),
                ),
              ),
            ],
          ),
        );
      },
    );
  }
}

class _RecentTransactions extends StatelessWidget {
  const _RecentTransactions({required this.transactions});

  final List<ChildTransaction> transactions;

  @override
  Widget build(BuildContext context) {
    if (transactions.isEmpty) {
      return const AppCard(child: Center(child: Text('まだ取引履歴はありません。')));
    }

    return AppCard(
      padding: EdgeInsets.zero,
      child: Column(
        children: [
          for (var index = 0; index < transactions.length; index++) ...[
            _TransactionTile(transaction: transactions[index]),
            if (index != transactions.length - 1)
              const Divider(height: 1, indent: 16, endIndent: 16),
          ],
        ],
      ),
    );
  }
}

class _TransactionTile extends StatelessWidget {
  const _TransactionTile({required this.transaction});

  final ChildTransaction transaction;

  @override
  Widget build(BuildContext context) {
    final color = transaction.isIncome
        ? const Color(0xFF2E7D32)
        : AppColors.textPrimary;
    final sign = transaction.isIncome ? '+' : '-';

    return ListTile(
      leading: CircleAvatar(
        backgroundColor: AppColors.background,
        child: Icon(
          transaction.isIncome
              ? Icons.south_west_rounded
              : Icons.north_east_rounded,
          color: color,
        ),
      ),
      title: Text(
        transaction.title,
        maxLines: 1,
        overflow: TextOverflow.ellipsis,
      ),
      subtitle: Text(DateFormat('M/d').format(transaction.occurredAt)),
      trailing: Text(
        '$sign${CurrencyFormatter.yen(transaction.amount)}',
        style: TextStyle(color: color, fontWeight: FontWeight.w800),
      ),
    );
  }
}

class _HomeError extends StatelessWidget {
  const _HomeError({required this.message, required this.onRetry});

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
