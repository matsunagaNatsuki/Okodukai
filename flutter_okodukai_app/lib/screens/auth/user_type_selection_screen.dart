import 'package:flutter/material.dart';

import '../../theme/app_colors.dart';
import '../../widgets/app_button.dart';
import '../../widgets/app_card.dart';
import '../../widgets/app_screen_title.dart';
import 'child_login_screen.dart';
import 'parent_login_screen.dart';

class UserTypeSelectionScreen extends StatelessWidget {
  const UserTypeSelectionScreen({super.key});

  void _openParentLogin(BuildContext context) {
    Navigator.of(
      context,
    ).push(MaterialPageRoute<void>(builder: (_) => const ParentLoginScreen()));
  }

  void _openChildLogin(BuildContext context) {
    Navigator.of(
      context,
    ).push(MaterialPageRoute<void>(builder: (_) => const ChildLoginScreen()));
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: SafeArea(
        child: LayoutBuilder(
          builder: (context, constraints) {
            final horizontalPadding = constraints.maxWidth < 360 ? 16.0 : 24.0;

            return SingleChildScrollView(
              padding: EdgeInsets.fromLTRB(
                horizontalPadding,
                24,
                horizontalPadding,
                24,
              ),
              child: ConstrainedBox(
                constraints: BoxConstraints(
                  minHeight: constraints.maxHeight - 48,
                ),
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    const Icon(
                      Icons.savings_rounded,
                      size: 72,
                      color: AppColors.primary,
                    ),
                    const SizedBox(height: 20),
                    const AppScreenTitle(
                      title: 'おこづかい',
                      subtitle: '利用するユーザーを選んでください。',
                    ),
                    const SizedBox(height: 28),
                    AppCard(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.stretch,
                        children: [
                          AppButton(
                            label: '保護者としてログイン',
                            icon: Icons.family_restroom_rounded,
                            onPressed: () => _openParentLogin(context),
                          ),
                          const SizedBox(height: 12),
                          AppButton(
                            label: '子どもとしてログイン',
                            icon: Icons.face_rounded,
                            style: AppButtonStyle.secondary,
                            onPressed: () => _openChildLogin(context),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
            );
          },
        ),
      ),
    );
  }
}
