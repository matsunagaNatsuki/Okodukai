import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../providers/auth_state.dart';
import 'app_button.dart';

class LogoutButton extends StatelessWidget {
  const LogoutButton({super.key, this.compact = false});

  final bool compact;

  @override
  Widget build(BuildContext context) {
    final isLoading = context.select<AuthState, bool>(
      (state) => state.isLoggingOut,
    );
    if (compact) {
      return IconButton(
        tooltip: 'ログアウト',
        onPressed: isLoading ? null : context.read<AuthState>().logout,
        icon: isLoading
            ? const SizedBox.square(
                dimension: 20,
                child: CircularProgressIndicator(strokeWidth: 2),
              )
            : const Icon(Icons.logout_rounded),
      );
    }
    return AppButton(
      label: 'ログアウト',
      style: AppButtonStyle.secondary,
      icon: Icons.logout_rounded,
      isLoading: isLoading,
      onPressed: isLoading ? null : context.read<AuthState>().logout,
    );
  }
}
