import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../providers/auth_state.dart';
import '../../services/auth_service.dart';
import '../../widgets/app_button.dart';
import '../../widgets/app_card.dart';
import '../../widgets/app_error_message.dart';
import '../../widgets/app_screen_title.dart';
import '../../widgets/app_text_field.dart';
import 'parent_registration_screen.dart';
import 'password_reset_screen.dart';

class ParentLoginScreen extends StatefulWidget {
  const ParentLoginScreen({super.key, this.authService});

  final AuthService? authService;

  @override
  State<ParentLoginScreen> createState() => _ParentLoginScreenState();
}

class _ParentLoginScreenState extends State<ParentLoginScreen> {
  final _formKey = GlobalKey<FormState>();
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();

  late final AuthService _authService;
  bool _rememberEmail = false;
  bool _obscurePassword = true;
  bool _isLoading = false;
  String? _errorMessage;

  @override
  void initState() {
    super.initState();
    _authService = widget.authService ?? context.read<AuthService>();
    _loadRememberedEmail();
  }

  Future<void> _loadRememberedEmail() async {
    try {
      final email = await _authService.getRememberedParentEmail();
      if (!mounted || email == null || email.isEmpty) return;
      setState(() {
        _emailController.text = email;
        _rememberEmail = true;
      });
    } catch (_) {
      // 保存済みメールの読み込み失敗はログイン操作を妨げない。
    }
  }

  @override
  void dispose() {
    _emailController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  Future<void> _login() async {
    FocusScope.of(context).unfocus();
    if (!_formKey.currentState!.validate()) return;

    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      await context.read<AuthState>().loginParent(
        email: _emailController.text.trim(),
        password: _passwordController.text,
        rememberEmail: _rememberEmail,
      );
      if (!mounted) return;
      Navigator.of(context).popUntil((route) => route.isFirst);
    } on AuthException catch (error) {
      if (!mounted) return;
      setState(() => _errorMessage = error.message);
    } catch (_) {
      if (!mounted) return;
      setState(() {
        _errorMessage = 'ログイン中に問題が発生しました。時間をおいてお試しください。';
      });
    } finally {
      if (mounted) {
        setState(() => _isLoading = false);
      }
    }
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

  @override
  Widget build(BuildContext context) {
    final horizontalPadding = MediaQuery.sizeOf(context).width < 360
        ? 16.0
        : 24.0;

    return Scaffold(
      appBar: AppBar(title: const Text('保護者ログイン')),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: EdgeInsets.fromLTRB(
            horizontalPadding,
            16,
            horizontalPadding,
            32,
          ),
          child: AutofillGroup(
            child: Form(
              key: _formKey,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  const AppScreenTitle(
                    title: '保護者ログイン',
                    subtitle: '登録したメールアドレスでログインします。',
                  ),
                  const SizedBox(height: 20),
                  AppCard(
                    child: Column(
                      children: [
                        AppTextField(
                          label: 'メールアドレス',
                          controller: _emailController,
                          keyboardType: TextInputType.emailAddress,
                          textInputAction: TextInputAction.next,
                          autocorrect: false,
                          autofillHints: const [AutofillHints.username],
                          prefixIcon: const Icon(Icons.mail_outline),
                          validator: _validateEmail,
                        ),
                        const SizedBox(height: 16),
                        AppTextField(
                          label: 'パスワード',
                          controller: _passwordController,
                          textInputAction: TextInputAction.done,
                          obscureText: _obscurePassword,
                          autocorrect: false,
                          autofillHints: const [AutofillHints.password],
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
                        CheckboxListTile(
                          contentPadding: EdgeInsets.zero,
                          controlAffinity: ListTileControlAffinity.leading,
                          title: const Text('メールアドレスを保存する'),
                          value: _rememberEmail,
                          onChanged: _isLoading
                              ? null
                              : (value) {
                                  setState(
                                    () => _rememberEmail = value ?? false,
                                  );
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
                    label: 'ログイン',
                    isLoading: _isLoading,
                    onPressed: _isLoading ? null : _login,
                  ),
                  const SizedBox(height: 8),
                  TextButton(
                    onPressed: _isLoading
                        ? null
                        : () {
                            Navigator.of(context).push(
                              MaterialPageRoute<void>(
                                builder: (_) => const PasswordResetScreen(),
                              ),
                            );
                          },
                    child: const Text('パスワードを忘れた方'),
                  ),
                  TextButton(
                    onPressed: _isLoading
                        ? null
                        : () {
                            Navigator.of(context).push(
                              MaterialPageRoute<void>(
                                builder: (_) =>
                                    const ParentRegistrationScreen(),
                              ),
                            );
                          },
                    child: const Text('初めての方は新規登録'),
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
