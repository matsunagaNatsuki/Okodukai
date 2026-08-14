import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:provider/provider.dart';

import '../../services/auth_service.dart';
import '../../widgets/app_button.dart';
import '../../widgets/app_card.dart';
import '../../widgets/app_error_message.dart';
import '../../widgets/app_screen_title.dart';
import '../../widgets/app_text_field.dart';

enum _ResetStep { email, code, password }

class PasswordResetScreen extends StatefulWidget {
  const PasswordResetScreen({super.key, this.authService});

  final AuthService? authService;

  @override
  State<PasswordResetScreen> createState() => _PasswordResetScreenState();
}

class _PasswordResetScreenState extends State<PasswordResetScreen> {
  final _formKey = GlobalKey<FormState>();
  final _emailController = TextEditingController();
  final _codeController = TextEditingController();
  final _passwordController = TextEditingController();
  final _confirmationController = TextEditingController();
  late final AuthService _authService;

  _ResetStep _step = _ResetStep.email;
  String? _resetToken;
  String? _errorMessage;
  bool _isLoading = false;
  bool _obscurePassword = true;
  bool _obscureConfirmation = true;

  @override
  void initState() {
    super.initState();
    _authService = widget.authService ?? context.read<AuthService>();
  }

  @override
  void dispose() {
    _emailController.dispose();
    _codeController.dispose();
    _passwordController.dispose();
    _confirmationController.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    FocusScope.of(context).unfocus();
    if (_isLoading || !_formKey.currentState!.validate()) return;
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });
    try {
      switch (_step) {
        case _ResetStep.email:
          await _authService.requestPasswordResetCode(
            email: _emailController.text.trim(),
          );
          if (mounted) setState(() => _step = _ResetStep.code);
        case _ResetStep.code:
          final token = await _authService.verifyPasswordResetCode(
            email: _emailController.text.trim(),
            code: _codeController.text,
          );
          if (mounted) {
            setState(() {
              _resetToken = token;
              _step = _ResetStep.password;
            });
          }
        case _ResetStep.password:
          await _authService.resetPassword(
            email: _emailController.text.trim(),
            resetToken: _resetToken!,
            password: _passwordController.text,
            passwordConfirmation: _confirmationController.text,
          );
          if (!mounted) return;
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('パスワードを再設定しました。新しいパスワードでログインしてください。')),
          );
          Navigator.of(context).pop();
      }
    } on AuthException catch (error) {
      if (mounted) setState(() => _errorMessage = error.message);
    } catch (_) {
      if (mounted) setState(() => _errorMessage = '処理中に問題が発生しました。もう一度お試しください。');
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('パスワード再設定')),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.fromLTRB(20, 16, 20, 32),
          child: Form(
            key: _formKey,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                AppScreenTitle(title: _title, subtitle: _subtitle),
                const SizedBox(height: 12),
                Text(
                  'ステップ ${_step.index + 1} / 3',
                  textAlign: TextAlign.center,
                ),
                const SizedBox(height: 16),
                LinearProgressIndicator(value: (_step.index + 1) / 3),
                const SizedBox(height: 20),
                AppCard(child: _fields()),
                if (_errorMessage != null) ...[
                  const SizedBox(height: 16),
                  AppErrorMessage(message: _errorMessage!),
                ],
                const SizedBox(height: 20),
                AppButton(
                  label: _buttonLabel,
                  isLoading: _isLoading,
                  onPressed: _isLoading ? null : _submit,
                ),
                if (_step == _ResetStep.code) ...[
                  const SizedBox(height: 8),
                  TextButton(
                    onPressed: _isLoading
                        ? null
                        : () => setState(() {
                            _step = _ResetStep.email;
                            _codeController.clear();
                            _errorMessage = null;
                          }),
                    child: const Text('メールアドレスを変更する'),
                  ),
                ],
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _fields() => switch (_step) {
    _ResetStep.email => AppTextField(
      label: '保護者のメールアドレス',
      controller: _emailController,
      enabled: !_isLoading,
      keyboardType: TextInputType.emailAddress,
      autocorrect: false,
      prefixIcon: const Icon(Icons.mail_outline_rounded),
      validator: (value) {
        final email = value?.trim() ?? '';
        if (email.isEmpty) return 'メールアドレスを入力してください。';
        return RegExp(r'^[^\s@]+@[^\s@]+\.[^\s@]+$').hasMatch(email)
            ? null
            : '正しいメールアドレスを入力してください。';
      },
    ),
    _ResetStep.code => AppTextField(
      label: '4桁の確認コード',
      controller: _codeController,
      enabled: !_isLoading,
      keyboardType: TextInputType.number,
      inputFormatters: [
        FilteringTextInputFormatter.digitsOnly,
        LengthLimitingTextInputFormatter(4),
      ],
      prefixIcon: const Icon(Icons.pin_outlined),
      validator: (value) => RegExp(r'^\d{4}$').hasMatch(value ?? '')
          ? null
          : '4桁の確認コードを入力してください。',
    ),
    _ResetStep.password => Column(
      children: [
        _passwordField(
          label: '新しいパスワード',
          controller: _passwordController,
          obscure: _obscurePassword,
          onToggle: () => setState(() => _obscurePassword = !_obscurePassword),
          validator: (value) =>
              value == null || value.isEmpty ? '新しいパスワードを入力してください。' : null,
        ),
        const SizedBox(height: 16),
        _passwordField(
          label: '新しいパスワード確認',
          controller: _confirmationController,
          obscure: _obscureConfirmation,
          onToggle: () =>
              setState(() => _obscureConfirmation = !_obscureConfirmation),
          validator: (value) =>
              value != _passwordController.text ? 'パスワードが一致しません。' : null,
        ),
      ],
    ),
  };

  Widget _passwordField({
    required String label,
    required TextEditingController controller,
    required bool obscure,
    required VoidCallback onToggle,
    required String? Function(String?) validator,
  }) => AppTextField(
    label: label,
    controller: controller,
    enabled: !_isLoading,
    obscureText: obscure,
    autocorrect: false,
    prefixIcon: const Icon(Icons.lock_outline_rounded),
    suffixIcon: IconButton(
      onPressed: _isLoading ? null : onToggle,
      icon: Icon(
        obscure ? Icons.visibility_outlined : Icons.visibility_off_outlined,
      ),
    ),
    validator: validator,
  );

  String get _title => switch (_step) {
    _ResetStep.email => 'メールアドレスを入力',
    _ResetStep.code => '確認コードを入力',
    _ResetStep.password => '新しいパスワードを設定',
  };

  String get _subtitle => switch (_step) {
    _ResetStep.email => '家族コードを作成した保護者のメールアドレスを入力してください。',
    _ResetStep.code => '${_emailController.text.trim()} に送信された確認コードを入力してください。',
    _ResetStep.password => '今後ログインに使用するパスワードを入力してください。',
  };

  String get _buttonLabel => switch (_step) {
    _ResetStep.email => '確認コードを送信',
    _ResetStep.code => '確認する',
    _ResetStep.password => 'パスワードを再設定',
  };
}
