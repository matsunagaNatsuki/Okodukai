import 'package:flutter/material.dart';

import '../../services/auth_service.dart';
import '../../widgets/app_button.dart';
import '../../widgets/app_card.dart';
import '../../widgets/app_error_message.dart';
import '../../widgets/app_screen_title.dart';
import '../../widgets/app_text_field.dart';
import 'parent_registration_success_screen.dart';

class ParentRegistrationScreen extends StatefulWidget {
  const ParentRegistrationScreen({super.key, this.authService});

  final AuthService? authService;

  @override
  State<ParentRegistrationScreen> createState() =>
      _ParentRegistrationScreenState();
}

class _ParentRegistrationScreenState extends State<ParentRegistrationScreen> {
  final _formKey = GlobalKey<FormState>();
  final _nameController = TextEditingController();
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();
  final _passwordConfirmationController = TextEditingController();

  late final AuthService _authService;
  bool _isLoading = false;
  bool _obscurePassword = true;
  bool _obscurePasswordConfirmation = true;
  String? _errorMessage;

  @override
  void initState() {
    super.initState();
    _authService = widget.authService ?? AuthService();
  }

  @override
  void dispose() {
    _nameController.dispose();
    _emailController.dispose();
    _passwordController.dispose();
    _passwordConfirmationController.dispose();
    super.dispose();
  }

  Future<void> _register() async {
    FocusScope.of(context).unfocus();
    if (!_formKey.currentState!.validate()) {
      return;
    }

    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final result = await _authService.registerParent(
        name: _nameController.text.trim(),
        email: _emailController.text.trim(),
        password: _passwordController.text,
        passwordConfirmation: _passwordConfirmationController.text,
      );
      if (!mounted) return;

      await Navigator.of(context).pushReplacement(
        MaterialPageRoute<void>(
          builder: (_) => ParentRegistrationSuccessScreen(result: result),
        ),
      );
    } on AuthException catch (error) {
      if (!mounted) return;
      setState(() => _errorMessage = error.message);
    } catch (_) {
      if (!mounted) return;
      setState(() {
        _errorMessage = '登録中に問題が発生しました。時間をおいてお試しください。';
      });
    } finally {
      if (mounted) {
        setState(() => _isLoading = false);
      }
    }
  }

  String? _validateName(String? value) {
    if (value == null || value.trim().isEmpty) {
      return '名前を入力してください。';
    }
    return null;
  }

  String? _validateEmail(String? value) {
    final email = value?.trim() ?? '';
    if (email.isEmpty) {
      return 'メールアドレスを入力してください。';
    }
    if (!RegExp(r'^[^\s@]+@[^\s@]+\.[^\s@]+$').hasMatch(email)) {
      return '正しいメールアドレスを入力してください。';
    }
    return null;
  }

  String? _validatePassword(String? value) {
    if (value == null || value.isEmpty) {
      return 'パスワードを入力してください。';
    }
    return null;
  }

  String? _validatePasswordConfirmation(String? value) {
    if (value != _passwordController.text) {
      return 'パスワードが一致しません。';
    }
    return null;
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('保護者新規登録')),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: EdgeInsets.fromLTRB(
            MediaQuery.sizeOf(context).width < 360 ? 16 : 24,
            16,
            MediaQuery.sizeOf(context).width < 360 ? 16 : 24,
            32,
          ),
          child: AutofillGroup(
            child: Form(
              key: _formKey,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  const AppScreenTitle(
                    title: 'はじめての保護者登録',
                    subtitle: '登録後に8桁の家族コードが発行されます。',
                  ),
                  const SizedBox(height: 20),
                  AppCard(
                    child: Column(
                      children: [
                        AppTextField(
                          label: '名前',
                          controller: _nameController,
                          textInputAction: TextInputAction.next,
                          textCapitalization: TextCapitalization.words,
                          autofillHints: const [AutofillHints.name],
                          prefixIcon: const Icon(Icons.person_outline),
                          validator: _validateName,
                        ),
                        const SizedBox(height: 16),
                        AppTextField(
                          label: 'メールアドレス',
                          controller: _emailController,
                          keyboardType: TextInputType.emailAddress,
                          textInputAction: TextInputAction.next,
                          autocorrect: false,
                          autofillHints: const [AutofillHints.email],
                          prefixIcon: const Icon(Icons.mail_outline),
                          validator: _validateEmail,
                        ),
                        const SizedBox(height: 16),
                        AppTextField(
                          label: 'パスワード',
                          controller: _passwordController,
                          textInputAction: TextInputAction.next,
                          obscureText: _obscurePassword,
                          autocorrect: false,
                          autofillHints: const [AutofillHints.newPassword],
                          prefixIcon: const Icon(Icons.lock_outline),
                          suffixIcon: IconButton(
                            tooltip: _obscurePassword ? 'パスワードを表示' : 'パスワードを隠す',
                            onPressed: () => setState(
                              () => _obscurePassword = !_obscurePassword,
                            ),
                            icon: Icon(
                              _obscurePassword
                                  ? Icons.visibility_outlined
                                  : Icons.visibility_off_outlined,
                            ),
                          ),
                          validator: _validatePassword,
                        ),
                        const SizedBox(height: 16),
                        AppTextField(
                          label: 'パスワード確認',
                          controller: _passwordConfirmationController,
                          textInputAction: TextInputAction.done,
                          obscureText: _obscurePasswordConfirmation,
                          autocorrect: false,
                          autofillHints: const [AutofillHints.newPassword],
                          prefixIcon: const Icon(Icons.lock_outline),
                          suffixIcon: IconButton(
                            tooltip: _obscurePasswordConfirmation
                                ? '確認用パスワードを表示'
                                : '確認用パスワードを隠す',
                            onPressed: () => setState(
                              () => _obscurePasswordConfirmation =
                                  !_obscurePasswordConfirmation,
                            ),
                            icon: Icon(
                              _obscurePasswordConfirmation
                                  ? Icons.visibility_outlined
                                  : Icons.visibility_off_outlined,
                            ),
                          ),
                          validator: _validatePasswordConfirmation,
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
                    onPressed: _isLoading ? null : _register,
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}
