import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../providers/auth_state.dart';
import '../../services/auth_service.dart';
import '../../widgets/app_button.dart';
import '../../widgets/app_card.dart';
import '../../widgets/app_error_message.dart';
import '../../widgets/app_screen_title.dart';
import '../../widgets/app_text_field.dart';

class ChildLoginScreen extends StatefulWidget {
  const ChildLoginScreen({super.key, this.authService});

  final AuthService? authService;

  @override
  State<ChildLoginScreen> createState() => _ChildLoginScreenState();
}

class _ChildLoginScreenState extends State<ChildLoginScreen> {
  final _formKey = GlobalKey<FormState>();
  final _familyCodeController = TextEditingController();
  final _loginIdController = TextEditingController();
  final _passwordController = TextEditingController();

  late final AuthService _authService;
  bool _rememberLogin = false;
  bool _obscurePassword = true;
  bool _isLoading = false;
  String? _errorMessage;

  @override
  void initState() {
    super.initState();
    _authService = widget.authService ?? context.read<AuthService>();
    _loadRememberedLogin();
  }

  Future<void> _loadRememberedLogin() async {
    try {
      final saved = await _authService.getRememberedChildLogin();
      if (!mounted || saved.familyCode == null || saved.loginId == null) return;
      setState(() {
        _familyCodeController.text = saved.familyCode!;
        _loginIdController.text = saved.loginId!;
        _rememberLogin = true;
      });
    } catch (_) {
      // 保存済み情報の読み込み失敗はログイン操作を妨げない。
    }
  }

  @override
  void dispose() {
    _familyCodeController.dispose();
    _loginIdController.dispose();
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
      await context.read<AuthState>().loginChild(
        familyCode: _familyCodeController.text.trim(),
        loginId: _loginIdController.text.trim(),
        password: _passwordController.text,
        rememberLogin: _rememberLogin,
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
      if (mounted) setState(() => _isLoading = false);
    }
  }

  String? _validateFamilyCode(String? value) {
    final familyCode = value?.trim() ?? '';
    if (familyCode.isEmpty) return '家族コードを入力してください。';
    if (!RegExp(r'^\d{8}$').hasMatch(familyCode)) {
      return '家族コードは8桁の数字で入力してください。';
    }
    return null;
  }

  String? _validateLoginId(String? value) {
    if (value == null || value.trim().isEmpty) {
      return 'ログインIDを入力してください。';
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
      appBar: AppBar(title: const Text('子どもログイン')),
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
                    title: '子どもログイン',
                    subtitle: 'おうちの人から教えてもらった情報でログインします。',
                  ),
                  const SizedBox(height: 20),
                  AppCard(
                    child: Column(
                      children: [
                        AppTextField(
                          label: '家族コード',
                          controller: _familyCodeController,
                          keyboardType: TextInputType.number,
                          textInputAction: TextInputAction.next,
                          autocorrect: false,
                          prefixIcon: const Icon(
                            Icons.family_restroom_outlined,
                          ),
                          validator: _validateFamilyCode,
                        ),
                        const SizedBox(height: 16),
                        AppTextField(
                          label: 'ログインID',
                          controller: _loginIdController,
                          textInputAction: TextInputAction.next,
                          autocorrect: false,
                          autofillHints: const [AutofillHints.username],
                          prefixIcon: const Icon(Icons.badge_outlined),
                          validator: _validateLoginId,
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
                          title: const Text('ログイン情報を保存する'),
                          subtitle: const Text('家族コードとログインIDのみ保存します'),
                          value: _rememberLogin,
                          onChanged: _isLoading
                              ? null
                              : (value) => setState(
                                  () => _rememberLogin = value ?? false,
                                ),
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
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}
