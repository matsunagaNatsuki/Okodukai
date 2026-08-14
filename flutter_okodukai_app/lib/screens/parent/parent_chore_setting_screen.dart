import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

import '../../models/parent_chore_setting.dart';
import '../../services/parent/parent_chore_setting_service.dart';
import '../../theme/app_colors.dart';
import '../../utils/currency_formatter.dart';
import '../../widgets/app_button.dart';
import '../../widgets/app_card.dart';
import '../../widgets/app_error_message.dart';
import '../../widgets/app_loading_indicator.dart';
import '../../widgets/app_screen_title.dart';
import '../../widgets/app_text_field.dart';

class ParentChoreSettingScreen extends StatefulWidget {
  const ParentChoreSettingScreen({super.key, this.settingService});

  final ParentChoreSettingService? settingService;

  @override
  State<ParentChoreSettingScreen> createState() =>
      _ParentChoreSettingScreenState();
}

class _ParentChoreSettingScreenState extends State<ParentChoreSettingScreen> {
  late final ParentChoreSettingService _service;
  List<ParentChoreSetting> _settings = const [];
  bool _isLoading = true;
  bool _isMutating = false;
  String? _errorMessage;

  @override
  void initState() {
    super.initState();
    _service = widget.settingService ?? ParentChoreSettingService();
    _load();
  }

  Future<void> _load() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });
    try {
      final settings = await _service.fetchSettings();
      if (mounted) setState(() => _settings = settings);
    } on ParentChoreSettingException catch (error) {
      if (mounted) setState(() => _errorMessage = error.message);
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  Future<void> _openForm([ParentChoreSetting? setting]) async {
    if (_isMutating) return;
    final input = await showDialog<_ChoreInput>(
      context: context,
      builder: (_) => _ChoreFormDialog(setting: setting),
    );
    if (input == null || !mounted) return;
    setState(() {
      _isMutating = true;
      _errorMessage = null;
    });
    try {
      final saved = setting == null
          ? await _service.createSetting(
              description: input.description,
              rewardAmount: input.rewardAmount,
            )
          : await _service.updateSetting(
              settingId: setting.id,
              description: input.description,
              rewardAmount: input.rewardAmount,
            );
      if (!mounted) return;
      setState(() {
        if (setting == null) {
          _settings = [..._settings, saved];
        } else {
          _settings = _settings
              .map((item) => item.id == saved.id ? saved : item)
              .toList(growable: false);
        }
      });
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(setting == null ? 'お手伝いを登録しました。' : 'お手伝いを更新しました。'),
        ),
      );
    } on ParentChoreSettingException catch (error) {
      if (mounted) setState(() => _errorMessage = error.message);
    } finally {
      if (mounted) setState(() => _isMutating = false);
    }
  }

  Future<void> _confirmDelete(ParentChoreSetting setting) async {
    if (_isMutating) return;
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('お手伝い設定を削除しますか？'),
        content: Text('「${setting.description}」を削除すると元に戻せません。'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('キャンセル'),
          ),
          FilledButton(
            onPressed: () => Navigator.pop(context, true),
            child: const Text('削除する'),
          ),
        ],
      ),
    );
    if (confirmed != true || !mounted) return;
    setState(() {
      _isMutating = true;
      _errorMessage = null;
    });
    try {
      await _service.deleteSetting(setting.id);
      if (!mounted) return;
      setState(() {
        _settings = _settings
            .where((item) => item.id != setting.id)
            .toList(growable: false);
      });
      ScaffoldMessenger.of(
        context,
      ).showSnackBar(const SnackBar(content: Text('お手伝い設定を削除しました。')));
    } on ParentChoreSettingException catch (error) {
      if (mounted) setState(() => _errorMessage = error.message);
    } finally {
      if (mounted) setState(() => _isMutating = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('お手伝い報酬設定')),
      body: SafeArea(child: _buildBody()),
    );
  }

  Widget _buildBody() {
    if (_isLoading) {
      return const AppLoadingIndicator(message: 'お手伝い設定を読み込み中...');
    }
    if (_errorMessage != null && _settings.isEmpty) {
      return ListView(
        padding: const EdgeInsets.all(20),
        children: [
          AppErrorMessage(message: _errorMessage!),
          const SizedBox(height: 12),
          AppButton(label: '再読み込み', onPressed: _load),
        ],
      );
    }
    return ListView(
      padding: const EdgeInsets.fromLTRB(20, 16, 20, 32),
      children: [
        const AppScreenTitle(
          title: 'お手伝い報酬設定',
          subtitle: '家族で使うお手伝いと報酬を管理します。',
        ),
        if (_errorMessage != null) ...[
          const SizedBox(height: 16),
          AppErrorMessage(message: _errorMessage!),
        ],
        const SizedBox(height: 20),
        if (_settings.isEmpty)
          const AppCard(
            child: Column(
              children: [
                Icon(
                  Icons.volunteer_activism_rounded,
                  size: 48,
                  color: AppColors.primary,
                ),
                SizedBox(height: 12),
                Text('お手伝い設定がまだありません。'),
              ],
            ),
          )
        else
          ..._settings.map(
            (setting) => Padding(
              padding: const EdgeInsets.only(bottom: 12),
              child: AppCard(
                child: Row(
                  children: [
                    const CircleAvatar(
                      backgroundColor: AppColors.accent,
                      child: Icon(
                        Icons.task_alt_rounded,
                        color: AppColors.primaryDark,
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            setting.description,
                            style: Theme.of(context).textTheme.titleMedium,
                          ),
                          const SizedBox(height: 4),
                          Text(
                            CurrencyFormatter.yen(setting.rewardAmount),
                            style: const TextStyle(
                              color: AppColors.primaryDark,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ],
                      ),
                    ),
                    IconButton(
                      tooltip: '編集',
                      onPressed: _isMutating ? null : () => _openForm(setting),
                      icon: const Icon(Icons.edit_rounded),
                    ),
                    IconButton(
                      tooltip: '削除',
                      onPressed: _isMutating
                          ? null
                          : () => _confirmDelete(setting),
                      icon: const Icon(Icons.delete_outline_rounded),
                    ),
                  ],
                ),
              ),
            ),
          ),
        const SizedBox(height: 8),
        AppButton(
          label: '新しいお手伝いを登録',
          icon: Icons.add_rounded,
          isLoading: _isMutating,
          onPressed: _isMutating ? null : _openForm,
        ),
      ],
    );
  }
}

class _ChoreFormDialog extends StatefulWidget {
  const _ChoreFormDialog({this.setting});
  final ParentChoreSetting? setting;

  @override
  State<_ChoreFormDialog> createState() => _ChoreFormDialogState();
}

class _ChoreFormDialogState extends State<_ChoreFormDialog> {
  final _formKey = GlobalKey<FormState>();
  late final TextEditingController _descriptionController;
  late final TextEditingController _rewardController;

  @override
  void initState() {
    super.initState();
    _descriptionController = TextEditingController(
      text: widget.setting?.description ?? '',
    );
    _rewardController = TextEditingController(
      text: widget.setting?.rewardAmount.toString() ?? '',
    );
  }

  @override
  void dispose() {
    _descriptionController.dispose();
    _rewardController.dispose();
    super.dispose();
  }

  void _submit() {
    if (!_formKey.currentState!.validate()) return;
    Navigator.pop(
      context,
      _ChoreInput(
        description: _descriptionController.text.trim(),
        rewardAmount: int.parse(_rewardController.text),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return AlertDialog(
      title: Text(widget.setting == null ? 'お手伝いを登録' : 'お手伝いを編集'),
      content: SingleChildScrollView(
        child: Form(
          key: _formKey,
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              AppTextField(
                label: 'お手伝い内容',
                controller: _descriptionController,
                validator: (value) => value == null || value.trim().isEmpty
                    ? 'お手伝い内容を入力してください。'
                    : null,
              ),
              const SizedBox(height: 16),
              AppTextField(
                label: '報酬金額',
                controller: _rewardController,
                keyboardType: TextInputType.number,
                inputFormatters: [FilteringTextInputFormatter.digitsOnly],
                validator: (value) {
                  if (value == null || value.isEmpty) return '報酬金額を入力してください。';
                  final amount = int.tryParse(value);
                  return amount == null || amount < 1
                      ? '報酬金額は1円以上で入力してください。'
                      : null;
                },
              ),
            ],
          ),
        ),
      ),
      actions: [
        TextButton(
          onPressed: () => Navigator.pop(context),
          child: const Text('キャンセル'),
        ),
        FilledButton(onPressed: _submit, child: const Text('保存する')),
      ],
    );
  }
}

class _ChoreInput {
  const _ChoreInput({required this.description, required this.rewardAmount});
  final String description;
  final int rewardAmount;
}
