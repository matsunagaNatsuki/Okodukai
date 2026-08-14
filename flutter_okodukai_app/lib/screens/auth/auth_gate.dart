import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../providers/auth_state.dart';
import '../../widgets/app_loading_indicator.dart';
import '../child/child_home_screen.dart';
import '../parent/parent_home_screen.dart';
import 'user_type_selection_screen.dart';

class AuthGate extends StatelessWidget {
  const AuthGate({super.key});

  @override
  Widget build(BuildContext context) {
    final authState = context.watch<AuthState>();

    if (authState.status == AuthStatus.initializing) {
      return const Scaffold(body: AppLoadingIndicator(message: '起動中...'));
    }
    if (!authState.isLoggedIn) {
      return const UserTypeSelectionScreen();
    }

    return switch (authState.role) {
      'parent' => const ParentHomeScreen(),
      'child' => const ChildHomeScreen(),
      _ => const UserTypeSelectionScreen(),
    };
  }
}
