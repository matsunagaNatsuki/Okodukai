import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

import '../../models/child_saving_goal.dart';
import '../../services/child/child_saving_goal_service.dart';
import '../../theme/app_colors.dart';
import '../../utils/currency_formatter.dart';
import '../../widgets/app_button.dart';
import '../../widgets/app_card.dart';
import '../../widgets/app_error_message.dart';
import '../../widgets/app_loading_indicator.dart';
import '../../widgets/app_screen_title.dart';
import '../../widgets/app_text_field.dart';

class ChildSavingGoalScreen extends StatefulWidget {
  const ChildSavingGoalScreen({super.key, this.savingGoalService});

  final ChildSavingGoalService? savingGoalService;

  @override
  State<ChildSavingGoalScreen> createState() => _ChildSavingGoalScreenState();
}

class _ChildSavingGoalScreenState extends State<ChildSavingGoalScreen> {
  final _formKey = GlobalKey<FormState>();
  final _wantedItemController = TextEditingController();
  final _targetAmountController = TextEditingController();
  late final ChildSavingGoalService _service;

  ChildSavingGoalData? _data;
  bool _isLoading = true;
  bool _isSaving = false;
  bool _isEditing = false;
  String? _errorMessage;

  @override
  void initState() {
    super.initState();
    _service = widget.savingGoalService ?? ChildSavingGoalService();
    _load();
  }

  @override
  void dispose() {
    _wantedItemController.dispose();
    _targetAmountController.dispose();
    super.dispose();
  }

  Future<void> _load() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });
    try {
      final data = await _service.fetchSavingGoal();
      if (!mounted) return;
      setState(() {
        _data = data;
        _isEditing = data.savingGoal == null;
        _fillForm(data.savingGoal);
      });
    } on ChildSavingGoalException catch (error) {
      if (!mounted) return;
      setState(() => _errorMessage = error.message);
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  void _fillForm(ChildSavingGoal? goal) {
    _wantedItemController.text = goal?.wantedItem ?? '';
    _targetAmountController.text = goal?.targetAmount.toString() ?? '';
  }

  Future<void> _save() async {
    if (_isSaving || !_formKey.currentState!.validate()) return;
    setState(() {
      _isSaving = true;
      _errorMessage = null;
    });
    try {
      final data = await _service.saveSavingGoal(
        wantedItem: _wantedItemController.text.trim(),
        targetAmount: int.parse(_targetAmountController.text),
        isEditing: _data?.savingGoal != null,
      );
      if (!mounted) return;
      setState(() {
        _data = data;
        _isEditing = false;
      });
      ScaffoldMessenger.of(
        context,
      ).showSnackBar(const SnackBar(content: Text('貯金目標を保存しました。')));
    } on ChildSavingGoalException catch (error) {
      if (!mounted) return;
      setState(() => _errorMessage = error.message);
    } finally {
      if (mounted) setState(() => _isSaving = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('貯金目標')),
      body: SafeArea(child: _buildBody()),
    );
  }

  Widget _buildBody() {
    if (_isLoading) {
      return const AppLoadingIndicator(message: '貯金目標を読み込み中...');
    }
    if (_data == null) {
      return _GoalError(
        message: _errorMessage ?? '貯金目標を取得できませんでした。',
        onRetry: _load,
      );
    }

    return SingleChildScrollView(
      padding: const EdgeInsets.fromLTRB(20, 16, 20, 32),
      child: Form(
        key: _formKey,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            AppScreenTitle(
              title: _data!.savingGoal == null ? '貯金目標をつくろう' : '貯金目標',
              subtitle: '現在残高：${CurrencyFormatter.yen(_data!.currentBalance)}',
            ),
            if (_data!.savingGoal != null && !_isEditing) ...[
              const SizedBox(height: 20),
              _GoalSummary(goal: _data!.savingGoal!),
              const SizedBox(height: 16),
              AppButton(
                label: '目標を編集する',
                style: AppButtonStyle.secondary,
                onPressed: () => setState(() => _isEditing = true),
              ),
            ] else ...[
              const SizedBox(height: 20),
              AppCard(
                child: Column(
                  children: [
                    AppTextField(
                      label: '欲しいもの',
                      controller: _wantedItemController,
                      enabled: !_isSaving,
                      prefixIcon: const Icon(Icons.card_giftcard_rounded),
                      validator: (value) =>
                          value == null || value.trim().isEmpty
                          ? '欲しいものを入力してください。'
                          : null,
                    ),
                    const SizedBox(height: 16),
                    AppTextField(
                      label: '目標金額',
                      controller: _targetAmountController,
                      enabled: !_isSaving,
                      keyboardType: TextInputType.number,
                      inputFormatters: [FilteringTextInputFormatter.digitsOnly],
                      prefixIcon: const Icon(Icons.currency_yen_rounded),
                      validator: (value) {
                        final amount = int.tryParse(value ?? '');
                        if (value == null || value.isEmpty) {
                          return '目標金額を入力してください。';
                        }
                        return amount == null || amount < 1
                            ? '目標金額は1円以上で入力してください。'
                            : null;
                      },
                    ),
                  ],
                ),
              ),
              if (_errorMessage != null) ...[
                const SizedBox(height: 16),
                AppErrorMessage(message: _errorMessage!),
              ],
              const SizedBox(height: 20),
              AppButton(
                label: _data!.savingGoal == null ? '登録する' : '更新する',
                isLoading: _isSaving,
                onPressed: _isSaving ? null : _save,
              ),
            ],
          ],
        ),
      ),
    );
  }
}

class _GoalSummary extends StatelessWidget {
  const _GoalSummary({required this.goal});

  final ChildSavingGoal goal;

  @override
  Widget build(BuildContext context) {
    return AppCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          if (goal.isAchieved) ...[
            const Row(
              children: [
                Icon(Icons.celebration_rounded, color: AppColors.primaryDark),
                SizedBox(width: 8),
                Expanded(
                  child: Text(
                    '目標達成！おめでとう！',
                    style: TextStyle(fontWeight: FontWeight.w900),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 16),
          ],
          _Value(label: '欲しいもの', value: goal.wantedItem),
          _Value(
            label: '目標金額',
            value: CurrencyFormatter.yen(goal.targetAmount),
          ),
          _Value(
            label: '現在残高',
            value: CurrencyFormatter.yen(goal.currentBalance),
          ),
          _Value(
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
            style: const TextStyle(fontWeight: FontWeight.w800),
          ),
        ],
      ),
    );
  }
}

class _Value extends StatelessWidget {
  const _Value({required this.label, required this.value});
  final String label;
  final String value;

  @override
  Widget build(BuildContext context) => Padding(
    padding: const EdgeInsets.only(bottom: 8),
    child: Row(
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
    ),
  );
}

class _GoalError extends StatelessWidget {
  const _GoalError({required this.message, required this.onRetry});
  final String message;
  final VoidCallback onRetry;

  @override
  Widget build(BuildContext context) => Center(
    child: Padding(
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
