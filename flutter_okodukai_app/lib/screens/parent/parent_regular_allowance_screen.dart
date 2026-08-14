import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

import '../../models/parent_regular_allowance.dart';
import '../../services/parent/parent_regular_allowance_service.dart';
import '../../widgets/app_button.dart';
import '../../widgets/app_card.dart';
import '../../widgets/app_error_message.dart';
import '../../widgets/app_loading_indicator.dart';
import '../../widgets/app_screen_title.dart';
import '../../widgets/app_text_field.dart';

class ParentRegularAllowanceScreen extends StatefulWidget {
  const ParentRegularAllowanceScreen({
    required this.childUserId,
    super.key,
    this.allowanceService,
  });

  final int childUserId;
  final ParentRegularAllowanceService? allowanceService;

  @override
  State<ParentRegularAllowanceScreen> createState() =>
      _ParentRegularAllowanceScreenState();
}

class _ParentRegularAllowanceScreenState
    extends State<ParentRegularAllowanceScreen> {
  final _formKey = GlobalKey<FormState>();
  final _amountController = TextEditingController();
  late final ParentRegularAllowanceService _service;

  ParentRegularAllowanceData? _data;
  int _paymentDay = 1;
  bool _isActive = true;
  bool _isLoading = true;
  bool _isSaving = false;
  String? _errorMessage;

  @override
  void initState() {
    super.initState();
    _service = widget.allowanceService ?? ParentRegularAllowanceService();
    _load();
  }

  @override
  void dispose() {
    _amountController.dispose();
    super.dispose();
  }

  Future<void> _load() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });
    try {
      final data = await _service.fetchSetting(widget.childUserId);
      if (!mounted) return;
      setState(() {
        _data = data;
        _amountController.text = data.setting?.amount.toString() ?? '';
        _paymentDay = data.setting?.paymentDay ?? 1;
        _isActive = data.setting?.isActive ?? true;
      });
    } on ParentRegularAllowanceException catch (error) {
      if (mounted) setState(() => _errorMessage = error.message);
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  Future<void> _save() async {
    if (_isSaving || !_formKey.currentState!.validate()) return;
    setState(() {
      _isSaving = true;
      _errorMessage = null;
    });
    try {
      final data = await _service.saveSetting(
        childUserId: widget.childUserId,
        amount: int.parse(_amountController.text),
        paymentDay: _paymentDay,
        isActive: _isActive,
        isEditing: _data?.setting != null,
      );
      if (!mounted) return;
      setState(() {
        _data = data;
        _amountController.text = data.setting!.amount.toString();
        _paymentDay = data.setting!.paymentDay;
        _isActive = data.setting!.isActive;
      });
      ScaffoldMessenger.of(
        context,
      ).showSnackBar(const SnackBar(content: Text('定期おこづかい設定を保存しました。')));
    } on ParentRegularAllowanceException catch (error) {
      if (mounted) setState(() => _errorMessage = error.message);
    } finally {
      if (mounted) setState(() => _isSaving = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('定期おこづかい設定')),
      body: SafeArea(child: _buildBody()),
    );
  }

  Widget _buildBody() {
    if (_isLoading) {
      return const AppLoadingIndicator(message: '設定を読み込み中...');
    }
    if (_data == null) {
      return ListView(
        padding: const EdgeInsets.all(20),
        children: [
          AppErrorMessage(message: _errorMessage ?? '設定を取得できませんでした。'),
          const SizedBox(height: 12),
          FilledButton.icon(
            onPressed: _load,
            icon: const Icon(Icons.refresh_rounded),
            label: const Text('再読み込み'),
          ),
        ],
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
              title: '${_data!.childName}さん',
              subtitle: '毎月のおこづかいを設定します。',
            ),
            const SizedBox(height: 20),
            AppCard(
              child: Column(
                children: [
                  AppTextField(
                    label: '毎月のおこづかい金額',
                    controller: _amountController,
                    enabled: !_isSaving,
                    keyboardType: TextInputType.number,
                    inputFormatters: [FilteringTextInputFormatter.digitsOnly],
                    prefixIcon: const Icon(Icons.currency_yen_rounded),
                    validator: (value) {
                      if (value == null || value.isEmpty) {
                        return 'おこづかい金額を入力してください。';
                      }
                      final amount = int.tryParse(value);
                      return amount == null || amount < 1
                          ? 'おこづかい金額は1円以上で入力してください。'
                          : null;
                    },
                  ),
                  const SizedBox(height: 16),
                  DropdownButtonFormField<int>(
                    initialValue: _paymentDay,
                    decoration: const InputDecoration(
                      labelText: '支給日',
                      prefixIcon: Icon(Icons.event_rounded),
                    ),
                    items: List.generate(
                      31,
                      (index) => DropdownMenuItem(
                        value: index + 1,
                        child: Text('毎月${index + 1}日'),
                      ),
                    ),
                    onChanged: _isSaving
                        ? null
                        : (value) => setState(() => _paymentDay = value ?? 1),
                  ),
                  const SizedBox(height: 12),
                  SwitchListTile.adaptive(
                    contentPadding: EdgeInsets.zero,
                    title: const Text('定期おこづかいを有効にする'),
                    subtitle: Text(_isActive ? '有効' : '無効'),
                    value: _isActive,
                    onChanged: _isSaving
                        ? null
                        : (value) => setState(() => _isActive = value),
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
              label: '保存する',
              isLoading: _isSaving,
              onPressed: _isSaving ? null : _save,
            ),
          ],
        ),
      ),
    );
  }
}
