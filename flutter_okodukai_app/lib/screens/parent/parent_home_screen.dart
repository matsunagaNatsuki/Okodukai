import 'package:flutter/material.dart';

import '../../models/parent_child.dart';
import '../../services/parent/parent_child_service.dart';
import '../../theme/app_colors.dart';
import '../../widgets/app_card.dart';
import '../../widgets/app_error_message.dart';
import '../../widgets/app_loading_indicator.dart';
import '../../widgets/app_screen_title.dart';
import '../../widgets/logout_button.dart';
import 'child_management_screen.dart';
import 'parent_chore_setting_screen.dart';

class ParentHomeScreen extends StatefulWidget {
  const ParentHomeScreen({super.key, this.childService});

  final ParentChildService? childService;

  @override
  State<ParentHomeScreen> createState() => _ParentHomeScreenState();
}

class _ParentHomeScreenState extends State<ParentHomeScreen> {
  late final ParentChildService _service;
  late Future<List<ParentChild>> _childrenFuture;

  @override
  void initState() {
    super.initState();
    _service = widget.childService ?? ParentChildService();
    _reload();
  }

  void _reload() {
    _childrenFuture = _service.fetchChildren();
  }

  void _retry() {
    setState(_reload);
  }

  void _openChild(ParentChild child) {
    Navigator.of(context).push(
      MaterialPageRoute<void>(
        builder: (_) => ChildManagementScreen(childUserId: child.id),
      ),
    );
  }

  void _openChoreSettings() {
    Navigator.of(context).push(
      MaterialPageRoute<void>(builder: (_) => const ParentChoreSettingScreen()),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('お子様一覧'),
        actions: const [
          Padding(
            padding: EdgeInsets.only(right: 8),
            child: LogoutButton(compact: true),
          ),
        ],
      ),
      body: SafeArea(
        child: FutureBuilder<List<ParentChild>>(
          future: _childrenFuture,
          builder: (context, snapshot) {
            if (snapshot.connectionState == ConnectionState.waiting) {
              return const AppLoadingIndicator(message: 'お子様情報を読み込み中...');
            }
            if (snapshot.hasError) {
              final error = snapshot.error;
              final message = error is ParentChildException
                  ? error.message
                  : 'お子様一覧を取得できませんでした。';
              return _ErrorView(message: message, onRetry: _retry);
            }
            return _ChildList(
              children: snapshot.data ?? const [],
              onChildTap: _openChild,
              onOpenChoreSettings: _openChoreSettings,
            );
          },
        ),
      ),
    );
  }
}

class _ChildList extends StatelessWidget {
  const _ChildList({
    required this.children,
    required this.onChildTap,
    required this.onOpenChoreSettings,
  });

  final List<ParentChild> children;
  final ValueChanged<ParentChild> onChildTap;
  final VoidCallback onOpenChoreSettings;

  @override
  Widget build(BuildContext context) {
    return ListView(
      padding: const EdgeInsets.all(20),
      children: [
        const AppScreenTitle(title: 'お子様一覧', subtitle: '管理するお子様を選んでください。'),
        const SizedBox(height: 20),
        if (children.isEmpty)
          const AppCard(
            child: Column(
              children: [
                Icon(
                  Icons.family_restroom_rounded,
                  size: 52,
                  color: AppColors.primary,
                ),
                SizedBox(height: 12),
                Text('お子様がまだ登録されていません。', textAlign: TextAlign.center),
                SizedBox(height: 6),
                Text('家族アカウント画面からお子様を追加してください。', textAlign: TextAlign.center),
              ],
            ),
          )
        else
          ...children.map(
            (child) => Padding(
              padding: const EdgeInsets.only(bottom: 12),
              child: AppCard(
                onTap: () => onChildTap(child),
                child: Row(
                  children: [
                    CircleAvatar(
                      radius: 30,
                      backgroundColor: AppColors.accent,
                      foregroundImage: child.profileImageUrl == null
                          ? null
                          : NetworkImage(child.profileImageUrl!),
                      child: child.profileImageUrl == null
                          ? const Icon(
                              Icons.face_rounded,
                              size: 30,
                              color: AppColors.primaryDark,
                            )
                          : null,
                    ),
                    const SizedBox(width: 14),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            child.name,
                            style: Theme.of(context).textTheme.titleMedium,
                          ),
                          const SizedBox(height: 4),
                          Text(
                            'ログインID：${child.loginId}',
                            style: Theme.of(context).textTheme.bodyMedium
                                ?.copyWith(color: AppColors.textSecondary),
                          ),
                        ],
                      ),
                    ),
                    const Icon(Icons.chevron_right_rounded),
                  ],
                ),
              ),
            ),
          ),
        const SizedBox(height: 20),
        AppCard(
          onTap: onOpenChoreSettings,
          child: const Row(
            children: [
              Icon(
                Icons.volunteer_activism_rounded,
                color: AppColors.primaryDark,
              ),
              SizedBox(width: 14),
              Expanded(
                child: Text(
                  'お手伝い報酬設定',
                  style: TextStyle(fontWeight: FontWeight.bold),
                ),
              ),
              Icon(Icons.chevron_right_rounded),
            ],
          ),
        ),
        const SizedBox(height: 12),
        const LogoutButton(),
      ],
    );
  }
}

class _ErrorView extends StatelessWidget {
  const _ErrorView({required this.message, required this.onRetry});

  final String message;
  final VoidCallback onRetry;

  @override
  Widget build(BuildContext context) {
    return ListView(
      padding: const EdgeInsets.all(20),
      children: [
        AppErrorMessage(message: message),
        const SizedBox(height: 12),
        FilledButton.icon(
          onPressed: onRetry,
          icon: const Icon(Icons.refresh_rounded),
          label: const Text('再読み込み'),
        ),
        const SizedBox(height: 20),
        const LogoutButton(),
      ],
    );
  }
}
