import 'package:flutter/material.dart';

import '../../models/child_saving_goal.dart';
import '../../models/parent_child_saving_goal.dart';
import '../../services/parent/parent_child_saving_goal_service.dart';
import '../../theme/app_colors.dart';
import '../../utils/currency_formatter.dart';
import '../../widgets/app_button.dart';
import '../../widgets/app_card.dart';
import '../../widgets/app_error_message.dart';
import '../../widgets/app_loading_indicator.dart';
import '../../widgets/app_screen_title.dart';

class ParentChildSavingGoalScreen extends StatefulWidget {
  const ParentChildSavingGoalScreen({
    required this.childUserId,
    super.key,
    this.savingGoalService,
  });

  final int childUserId;
  final ParentChildSavingGoalService? savingGoalService;

  @override
  State<ParentChildSavingGoalScreen> createState() =>
      _ParentChildSavingGoalScreenState();
}

class _ParentChildSavingGoalScreenState
    extends State<ParentChildSavingGoalScreen> {
  late final ParentChildSavingGoalService _service;
  late Future<ParentChildSavingGoalData> _goalFuture;

  @override
  void initState() {
    super.initState();
    _service = widget.savingGoalService ?? ParentChildSavingGoalService();
    _reload();
  }

  void _reload() {
    _goalFuture = _service.fetchSavingGoal(widget.childUserId);
  }

  void _retry() => setState(_reload);

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('貯金目標')),
      body: SafeArea(
        child: FutureBuilder<ParentChildSavingGoalData>(
          future: _goalFuture,
          builder: (context, snapshot) {
            if (snapshot.connectionState == ConnectionState.waiting) {
              return const AppLoadingIndicator(message: '貯金目標を読み込み中...');
            }
            if (snapshot.hasError) {
              final error = snapshot.error;
              return _GoalError(
                message: error is ParentChildSavingGoalException
                    ? error.message
                    : '貯金目標を取得できませんでした。',
                onRetry: _retry,
              );
            }
            return _GoalContent(data: snapshot.data!);
          },
        ),
      ),
    );
  }
}

class _GoalContent extends StatelessWidget {
  const _GoalContent({required this.data});

  final ParentChildSavingGoalData data;

  @override
  Widget build(BuildContext context) {
    return ListView(
      padding: const EdgeInsets.fromLTRB(20, 16, 20, 32),
      children: [
        AppScreenTitle(
          title: '${data.childName}さんの貯金目標',
          subtitle: 'お子様が設定した目標を確認できます。',
        ),
        const SizedBox(height: 20),
        if (data.savingGoal == null)
          AppCard(
            child: Column(
              children: [
                const Icon(
                  Icons.savings_outlined,
                  size: 52,
                  color: AppColors.primary,
                ),
                const SizedBox(height: 12),
                const Text('貯金目標はまだ設定されていません。', textAlign: TextAlign.center),
                const SizedBox(height: 8),
                Text(
                  '現在残高：${CurrencyFormatter.yen(data.currentBalance)}',
                  style: const TextStyle(fontWeight: FontWeight.bold),
                ),
              ],
            ),
          )
        else
          _GoalCard(goal: data.savingGoal!),
      ],
    );
  }
}

class _GoalCard extends StatelessWidget {
  const _GoalCard({required this.goal});

  final ChildSavingGoal goal;

  @override
  Widget build(BuildContext context) {
    return AppCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          if (goal.isAchieved) ...[
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: AppColors.background,
                borderRadius: BorderRadius.circular(12),
              ),
              child: const Row(
                children: [
                  Icon(Icons.celebration_rounded, color: AppColors.primaryDark),
                  SizedBox(width: 8),
                  Expanded(
                    child: Text(
                      '目標を達成しています！',
                      style: TextStyle(fontWeight: FontWeight.bold),
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 18),
          ],
          _GoalValue(label: '欲しいもの', value: goal.wantedItem),
          _GoalValue(
            label: '目標金額',
            value: CurrencyFormatter.yen(goal.targetAmount),
          ),
          _GoalValue(
            label: '現在残高',
            value: CurrencyFormatter.yen(goal.currentBalance),
          ),
          _GoalValue(
            label: '目標まであと',
            value: CurrencyFormatter.yen(goal.remainingAmount),
          ),
          const SizedBox(height: 12),
          LinearProgressIndicator(
            value: goal.progress,
            minHeight: 14,
            borderRadius: BorderRadius.circular(99),
            backgroundColor: AppColors.border,
          ),
          const SizedBox(height: 8),
          Text(
            '達成率 ${goal.achievementPercent}%',
            textAlign: TextAlign.end,
            style: const TextStyle(fontWeight: FontWeight.bold),
          ),
        ],
      ),
    );
  }
}

class _GoalValue extends StatelessWidget {
  const _GoalValue({required this.label, required this.value});
  final String label;
  final String value;

  @override
  Widget build(BuildContext context) => Padding(
    padding: const EdgeInsets.only(bottom: 12),
    child: Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Expanded(child: Text(label)),
        const SizedBox(width: 12),
        Flexible(
          child: Text(
            value,
            textAlign: TextAlign.end,
            style: const TextStyle(fontWeight: FontWeight.bold),
          ),
        ),
      ],
    ),
  );
}

class _GoalError extends StatelessWidget {
  const _GoalError({required this.message, required this.onRetry});
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
