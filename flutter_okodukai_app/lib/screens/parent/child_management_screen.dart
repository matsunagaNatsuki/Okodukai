import 'package:flutter/material.dart';

import '../../models/parent_child_management_data.dart';
import '../../services/parent/parent_child_management_service.dart';
import '../../theme/app_colors.dart';
import '../../utils/currency_formatter.dart';
import '../../widgets/app_card.dart';
import '../../widgets/app_error_message.dart';
import '../../widgets/app_loading_indicator.dart';
import '../../widgets/app_screen_title.dart';
import 'parent_child_history_screens.dart';
import 'parent_child_saving_goal_screen.dart';
import 'parent_chore_record_screen.dart';
import 'parent_regular_allowance_screen.dart';

class ChildManagementScreen extends StatefulWidget {
  const ChildManagementScreen({
    required this.childUserId,
    super.key,
    this.managementService,
  });

  final int childUserId;
  final ParentChildManagementService? managementService;

  @override
  State<ChildManagementScreen> createState() => _ChildManagementScreenState();
}

class _ChildManagementScreenState extends State<ChildManagementScreen> {
  late final ParentChildManagementService _service;
  late Future<ParentChildManagementData> _dataFuture;

  @override
  void initState() {
    super.initState();
    _service = widget.managementService ?? ParentChildManagementService();
    _reload();
  }

  void _reload() => _dataFuture = _service.fetchChild(widget.childUserId);

  void _retry() => setState(_reload);

  void _open(Widget screen) {
    Navigator.of(context).push(MaterialPageRoute<void>(builder: (_) => screen));
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('お子様管理')),
      body: SafeArea(
        child: FutureBuilder<ParentChildManagementData>(
          future: _dataFuture,
          builder: (context, snapshot) {
            if (snapshot.connectionState == ConnectionState.waiting) {
              return const AppLoadingIndicator(message: 'お子様情報を読み込み中...');
            }
            if (snapshot.hasError) {
              final error = snapshot.error;
              return _ErrorView(
                message: error is ParentChildManagementException
                    ? error.message
                    : 'お子様の情報を取得できませんでした。',
                onRetry: _retry,
              );
            }
            return _ManagementContent(data: snapshot.data!, onOpen: _open);
          },
        ),
      ),
    );
  }
}

class _ManagementContent extends StatelessWidget {
  const _ManagementContent({required this.data, required this.onOpen});

  final ParentChildManagementData data;
  final ValueChanged<Widget> onOpen;

  @override
  Widget build(BuildContext context) {
    final menuItems = <({String label, IconData icon, Widget screen})>[
      (
        label: '定期おこづかい',
        icon: Icons.calendar_month_rounded,
        screen: ParentRegularAllowanceScreen(childUserId: data.childUserId),
      ),
      (
        label: 'お手伝い実績登録',
        icon: Icons.add_task_rounded,
        screen: ParentChoreRecordScreen(childUserId: data.childUserId),
      ),
      (
        label: 'お手伝い履歴',
        icon: Icons.fact_check_rounded,
        screen: ParentChoreHistoryScreen(childUserId: data.childUserId),
      ),
      (
        label: '支出履歴',
        icon: Icons.receipt_long_rounded,
        screen: ParentExpenseHistoryScreen(childUserId: data.childUserId),
      ),
      (
        label: '貯金目標',
        icon: Icons.savings_rounded,
        screen: ParentChildSavingGoalScreen(childUserId: data.childUserId),
      ),
    ];
    return ListView(
      padding: const EdgeInsets.all(20),
      children: [
        AppScreenTitle(title: data.name, subtitle: 'お子様の管理データ'),
        const SizedBox(height: 20),
        AppCard(
          child: Row(
            children: [
              CircleAvatar(
                radius: 36,
                backgroundColor: AppColors.accent,
                foregroundImage: data.profileImageUrl == null
                    ? null
                    : NetworkImage(data.profileImageUrl!),
                child: data.profileImageUrl == null
                    ? const Icon(
                        Icons.face_rounded,
                        size: 38,
                        color: AppColors.primaryDark,
                      )
                    : null,
              ),
              const SizedBox(width: 16),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      data.name,
                      style: Theme.of(context).textTheme.titleLarge,
                    ),
                    const SizedBox(height: 4),
                    Text(
                      'ログインID：${data.loginId}',
                      style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                        color: AppColors.textSecondary,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
        const SizedBox(height: 12),
        AppCard(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text('現在残高', style: Theme.of(context).textTheme.titleMedium),
              const SizedBox(height: 8),
              Text(
                CurrencyFormatter.yen(data.currentBalance),
                style: Theme.of(context).textTheme.headlineMedium?.copyWith(
                  color: AppColors.primaryDark,
                  fontWeight: FontWeight.bold,
                ),
              ),
            ],
          ),
        ),
        const SizedBox(height: 24),
        Text('管理メニュー', style: Theme.of(context).textTheme.titleLarge),
        const SizedBox(height: 12),
        ...menuItems.map(
          (item) => Padding(
            padding: const EdgeInsets.only(bottom: 10),
            child: AppCard(
              onTap: () => onOpen(item.screen),
              child: Row(
                children: [
                  Icon(item.icon, color: AppColors.primaryDark),
                  const SizedBox(width: 14),
                  Expanded(
                    child: Text(
                      item.label,
                      style: Theme.of(context).textTheme.titleMedium,
                    ),
                  ),
                  const Icon(Icons.chevron_right_rounded),
                ],
              ),
            ),
          ),
        ),
      ],
    );
  }
}

class _ErrorView extends StatelessWidget {
  const _ErrorView({required this.message, required this.onRetry});
  final String message;
  final VoidCallback onRetry;

  @override
  Widget build(BuildContext context) {
    return ListView(
      padding: const EdgeInsets.all(20),
      children: [
        AppErrorMessage(message: message),
        const SizedBox(height: 12),
        FilledButton.icon(
          onPressed: onRetry,
          icon: const Icon(Icons.refresh_rounded),
          label: const Text('再読み込み'),
        ),
      ],
    );
  }
}
