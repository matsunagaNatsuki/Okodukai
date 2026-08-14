import 'package:flutter/material.dart';

import '../../widgets/app_screen_title.dart';

class ChildFeaturePlaceholderScreen extends StatelessWidget {
  const ChildFeaturePlaceholderScreen({required this.title, super.key});

  final String title;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text(title)),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(24),
          child: AppScreenTitle(title: title, subtitle: 'この機能は次の段階で実装します。'),
        ),
      ),
    );
  }
}
