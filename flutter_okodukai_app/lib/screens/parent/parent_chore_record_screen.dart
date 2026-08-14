import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:intl/intl.dart';

import '../../models/parent_chore_record.dart';
import '../../models/parent_chore_setting.dart';
import '../../services/parent/parent_chore_record_service.dart';
import '../../theme/app_colors.dart';
import '../../utils/currency_formatter.dart';
import '../../widgets/app_button.dart';
import '../../widgets/app_card.dart';
import '../../widgets/app_error_message.dart';
import '../../widgets/app_loading_indicator.dart';
import '../../widgets/app_screen_title.dart';
import '../../widgets/app_text_field.dart';

class ParentChoreRecordScreen extends StatefulWidget {
  const ParentChoreRecordScreen({
    required this.childUserId,
    super.key,
    this.recordService,
  });

  final int childUserId;
  final ParentChoreRecordService? recordService;

  @override
  State<ParentChoreRecordScreen> createState() =>
      _ParentChoreRecordScreenState();
}

class _ParentChoreRecordScreenState extends State<ParentChoreRecordScreen> {
  final _formKey = GlobalKey<FormState>();
  final _rewardController = TextEditingController();
  final _dateController = TextEditingController();
  late final ParentChoreRecordService _service;

  ParentChoreRecordFormData? _data;
  ParentChoreSetting? _selectedSetting;
  DateTime _performedOn = DateTime.now();
  bool _isLoading = true;
  bool _isSubmitting = false;
  String? _errorMessage;

  @override
  void initState() {
    super.initState();
    _service = widget.recordService ?? ParentChoreRecordService();
    _setDate(DateTime.now());
    _load();
  }

  @override
  void dispose() {
    _rewardController.dispose();
    _dateController.dispose();
    super.dispose();
  }

  void _setDate(DateTime date) {
    _performedOn = DateTime(date.year, date.month, date.day);
    _dateController.text = DateFormat('yyyy年M月d日').format(_performedOn);
  }

  Future<void> _load() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });
    try {
      final data = await _service.fetchFormData(widget.childUserId);
      if (mounted) setState(() => _data = data);
    } on ParentChoreRecordException catch (error) {
      if (mounted) setState(() => _errorMessage = error.message);
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  void _selectSetting(ParentChoreSetting? setting) {
    setState(() {
      _selectedSetting = setting;
      _rewardController.text = setting?.rewardAmount.toString() ?? '';
    });
  }

  Future<void> _pickDate() async {
    if (_isSubmitting) return;
    final selected = await showDatePicker(
      context: context,
      initialDate: _performedOn,
      firstDate: DateTime(2000),
      lastDate: DateTime.now(),
      helpText: '実施日を選択',
    );
    if (selected != null && mounted) setState(() => _setDate(selected));
  }

  Future<void> _submit() async {
    if (_isSubmitting || !_formKey.currentState!.validate()) return;
    final setting = _selectedSetting;
    if (setting == null) return;
    setState(() {
      _isSubmitting = true;
      _errorMessage = null;
    });
    try {
      final result = await _service.createRecord(
        childUserId: widget.childUserId,
        choreSettingId: setting.id,
        rewardAmount: int.parse(_rewardController.text),
        performedOn: _performedOn,
      );
      if (!mounted) return;
      setState(() {
        _selectedSetting = null;
        _rewardController.clear();
      });
      _formKey.currentState!.reset();
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            'お手伝い実績を登録しました。現在残高：${CurrencyFormatter.yen(result.currentBalance)}',
          ),
        ),
      );
    } on ParentChoreRecordException catch (error) {
      if (mounted) setState(() => _errorMessage = error.message);
    } finally {
      if (mounted) setState(() => _isSubmitting = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('お手伝い実績登録')),
      body: SafeArea(child: _buildBody()),
    );
  }

  Widget _buildBody() {
    if (_isLoading) {
      return const AppLoadingIndicator(message: 'お手伝い設定を読み込み中...');
    }
    if (_data == null) {
      return ListView(
        padding: const EdgeInsets.all(20),
        children: [
          AppErrorMessage(message: _errorMessage ?? 'お手伝い設定を取得できませんでした。'),
          const SizedBox(height: 12),
          AppButton(label: '再読み込み', onPressed: _load),
        ],
      );
    }
    final settings = _data!.choreSettings;
    return SingleChildScrollView(
      padding: const EdgeInsets.fromLTRB(20, 16, 20, 32),
      child: Form(
        key: _formKey,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            AppScreenTitle(
              title: '${_data!.childName}さん',
              subtitle: 'がんばったお手伝いを登録します。',
            ),
            const SizedBox(height: 20),
            if (settings.isEmpty)
              const AppCard(
                child: Column(
                  children: [
                    Icon(
                      Icons.info_outline_rounded,
                      size: 42,
                      color: AppColors.primary,
                    ),
                    SizedBox(height: 10),
                    Text('お手伝い報酬設定がまだありません。'),
                    SizedBox(height: 4),
                    Text('先に家族のお手伝い報酬を登録してください。'),
                  ],
                ),
              )
            else ...[
              AppCard(
                child: Column(
                  children: [
                    DropdownButtonFormField<ParentChoreSetting>(
                      initialValue: _selectedSetting,
                      decoration: const InputDecoration(
                        labelText: 'お手伝い種類',
                        prefixIcon: Icon(Icons.task_alt_rounded),
                      ),
                      items: settings
                          .map(
                            (setting) => DropdownMenuItem(
                              value: setting,
                              child: Text(setting.description),
                            ),
                          )
                          .toList(growable: false),
                      onChanged: _isSubmitting ? null : _selectSetting,
                      validator: (value) =>
                          value == null ? 'お手伝い種類を選択してください。' : null,
                    ),
                    const SizedBox(height: 16),
                    AppTextField(
                      label: '報酬金額',
                      controller: _rewardController,
                      enabled: !_isSubmitting,
                      keyboardType: TextInputType.number,
                      inputFormatters: [FilteringTextInputFormatter.digitsOnly],
                      prefixIcon: const Icon(Icons.currency_yen_rounded),
                      validator: (value) {
                        if (value == null || value.isEmpty) {
                          return '報酬金額を入力してください。';
                        }
                        final amount = int.tryParse(value);
                        return amount == null || amount < 1
                            ? '報酬金額は1円以上で入力してください。'
                            : null;
                      },
                    ),
                    const SizedBox(height: 16),
                    AppTextField(
                      label: '実施日',
                      controller: _dateController,
                      readOnly: true,
                      enabled: !_isSubmitting,
                      onTap: _pickDate,
                      prefixIcon: const Icon(Icons.event_rounded),
                      suffixIcon: const Icon(Icons.arrow_drop_down_rounded),
                      validator: (value) => value == null || value.isEmpty
                          ? '実施日を選択してください。'
                          : null,
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
                label: '登録する',
                isLoading: _isSubmitting,
                onPressed: _isSubmitting ? null : _submit,
              ),
            ],
          ],
        ),
      ),
    );
  }
}
