import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:intl/intl.dart';

import '../../services/child/child_expense_service.dart';
import '../../utils/currency_formatter.dart';
import '../../widgets/app_button.dart';
import '../../widgets/app_card.dart';
import '../../widgets/app_error_message.dart';
import '../../widgets/app_screen_title.dart';
import '../../widgets/app_text_field.dart';

class ChildExpenseRecordScreen extends StatefulWidget {
  const ChildExpenseRecordScreen({
    required this.currentBalance,
    super.key,
    this.expenseService,
  });

  final int currentBalance;
  final ChildExpenseService? expenseService;

  @override
  State<ChildExpenseRecordScreen> createState() =>
      _ChildExpenseRecordScreenState();
}

class _ChildExpenseRecordScreenState extends State<ChildExpenseRecordScreen> {
  final _formKey = GlobalKey<FormState>();
  final _descriptionController = TextEditingController();
  final _amountController = TextEditingController();
  final _dateController = TextEditingController();

  late final ChildExpenseService _expenseService;
  DateTime? _usedOn;
  bool _isLoading = false;
  String? _errorMessage;

  @override
  void initState() {
    super.initState();
    _expenseService = widget.expenseService ?? ChildExpenseService();
  }

  @override
  void dispose() {
    _descriptionController.dispose();
    _amountController.dispose();
    _dateController.dispose();
    super.dispose();
  }

  Future<void> _selectDate() async {
    if (_isLoading) return;
    final now = DateTime.now();
    final selected = await showDatePicker(
      context: context,
      initialDate: _usedOn ?? now,
      firstDate: DateTime(now.year - 10),
      lastDate: now,
      helpText: '使用日を選択',
      cancelText: 'キャンセル',
      confirmText: '決定',
    );
    if (selected == null || !mounted) return;
    setState(() {
      _usedOn = selected;
      _dateController.text = DateFormat('yyyy年M月d日').format(selected);
    });
  }

  Future<void> _submit() async {
    FocusScope.of(context).unfocus();
    if (!_formKey.currentState!.validate()) return;

    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final result = await _expenseService.createExpense(
        description: _descriptionController.text.trim(),
        amount: int.parse(_amountController.text),
        usedOn: _usedOn!,
      );
      if (!mounted) return;
      Navigator.of(context).pop(result);
    } on ChildExpenseException catch (error) {
      if (!mounted) return;
      setState(() => _errorMessage = error.message);
    } catch (_) {
      if (!mounted) return;
      setState(() {
        _errorMessage = '支出の登録中に問題が発生しました。';
      });
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  String? _validateDescription(String? value) {
    if (value == null || value.trim().isEmpty) {
      return '使った内容を入力してください。';
    }
    return null;
  }

  String? _validateAmount(String? value) {
    final amount = int.tryParse(value ?? '');
    if (value == null || value.isEmpty) {
      return '金額を入力してください。';
    }
    if (amount == null || amount < 1) {
      return '金額は1円以上で入力してください。';
    }
    if (amount > widget.currentBalance) {
      return '現在の残高を超える金額は登録できません。';
    }
    return null;
  }

  String? _validateDate(String? _) {
    return _usedOn == null ? '使用日を選択してください。' : null;
  }

  @override
  Widget build(BuildContext context) {
    final horizontalPadding = MediaQuery.sizeOf(context).width < 360
        ? 16.0
        : 24.0;

    return Scaffold(
      appBar: AppBar(title: const Text('つかったものを記録する')),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: EdgeInsets.fromLTRB(
            horizontalPadding,
            16,
            horizontalPadding,
            32,
          ),
          child: Form(
            key: _formKey,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                AppScreenTitle(
                  title: 'おこづかい使用記録',
                  subtitle:
                      '現在の残高：${CurrencyFormatter.yen(widget.currentBalance)}',
                ),
                const SizedBox(height: 20),
                AppCard(
                  child: Column(
                    children: [
                      AppTextField(
                        label: '使った内容',
                        controller: _descriptionController,
                        textInputAction: TextInputAction.next,
                        enabled: !_isLoading,
                        prefixIcon: const Icon(Icons.edit_note_rounded),
                        validator: _validateDescription,
                      ),
                      const SizedBox(height: 16),
                      AppTextField(
                        label: '金額',
                        controller: _amountController,
                        keyboardType: TextInputType.number,
                        textInputAction: TextInputAction.done,
                        enabled: !_isLoading,
                        inputFormatters: [
                          FilteringTextInputFormatter.digitsOnly,
                        ],
                        prefixIcon: const Icon(Icons.currency_yen_rounded),
                        validator: _validateAmount,
                      ),
                      const SizedBox(height: 16),
                      AppTextField(
                        label: '使用日',
                        controller: _dateController,
                        readOnly: true,
                        enabled: !_isLoading,
                        onTap: _selectDate,
                        prefixIcon: const Icon(Icons.calendar_today_rounded),
                        suffixIcon: IconButton(
                          tooltip: '使用日を選択',
                          onPressed: _isLoading ? null : _selectDate,
                          icon: const Icon(Icons.arrow_drop_down_rounded),
                        ),
                        validator: _validateDate,
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
                  isLoading: _isLoading,
                  onPressed: _isLoading ? null : _submit,
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
