import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import 'providers/auth_state.dart';
import 'screens/auth/auth_gate.dart';
import 'services/auth_service.dart';
import 'theme/app_theme.dart';

void main() {
  runApp(const OkodukaiApp());
}

class OkodukaiApp extends StatelessWidget {
  const OkodukaiApp({super.key, this.authService});

  final AuthService? authService;

  @override
  Widget build(BuildContext context) {
    return MultiProvider(
      providers: [
        Provider<AuthService>(create: (_) => authService ?? AuthService()),
        ChangeNotifierProvider<AuthState>(
          create: (context) =>
              AuthState(context.read<AuthService>())..initialize(),
        ),
      ],
      child: MaterialApp(
        title: 'おこづかい',
        debugShowCheckedModeBanner: false,
        theme: AppTheme.light,
        home: const AuthGate(),
      ),
    );
  }
}
