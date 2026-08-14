import 'package:flutter/material.dart';

import '../../models/parent_registration_result.dart';
import '../../theme/app_colors.dart';
import '../../widgets/app_card.dart';
import '../../widgets/app_screen_title.dart';

class ParentRegistrationSuccessScreen extends StatelessWidget {
  const ParentRegistrationSuccessScreen({required this.result, super.key});

  final ParentRegistrationResult result;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('登録完了')),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(24),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              const Icon(
                Icons.check_circle_rounded,
                size: 72,
                color: AppColors.primary,
              ),
              const SizedBox(height: 20),
              AppScreenTitle(
                title: '${result.parent.name}さん、登録が完了しました',
                subtitle: '子どもの登録やログインに使う家族コードです。',
              ),
              const SizedBox(height: 24),
              AppCard(
                child: Column(
                  children: [
                    Text(
                      '家族コード',
                      style: Theme.of(context).textTheme.labelLarge,
                    ),
                    const SizedBox(height: 8),
                    SelectableText(
                      result.familyCode,
                      style: Theme.of(context).textTheme.headlineMedium
                          ?.copyWith(
                            color: AppColors.primaryDark,
                            fontWeight: FontWeight.w800,
                            letterSpacing: 4,
                          ),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
