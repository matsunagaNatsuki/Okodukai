import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:provider/provider.dart';

import 'package:flutter_okodukai_app/main.dart';
import 'package:flutter_okodukai_app/models/child_home_data.dart';
import 'package:flutter_okodukai_app/models/child_expense_history.dart';
import 'package:flutter_okodukai_app/models/child_chore_history.dart';
import 'package:flutter_okodukai_app/models/child_login_result.dart';
import 'package:flutter_okodukai_app/models/parent_login_result.dart';
import 'package:flutter_okodukai_app/models/parent_registration_result.dart';
import 'package:flutter_okodukai_app/providers/auth_state.dart';
import 'package:flutter_okodukai_app/screens/child/child_expense_record_screen.dart';
import 'package:flutter_okodukai_app/screens/child/child_expense_history_screen.dart';
import 'package:flutter_okodukai_app/screens/child/child_chore_history_screen.dart';
import 'package:flutter_okodukai_app/screens/child/child_home_screen.dart';
import 'package:flutter_okodukai_app/services/auth_service.dart';
import 'package:flutter_okodukai_app/services/auth_storage_service.dart';
import 'package:flutter_okodukai_app/services/child/child_home_service.dart';
import 'package:flutter_okodukai_app/services/child/child_expense_history_service.dart';
import 'package:flutter_okodukai_app/services/child/child_chore_history_service.dart';
import 'package:flutter_okodukai_app/theme/app_theme.dart';

void main() {
  testWidgets('初期画面が表示される', (WidgetTester tester) async {
    await tester.pumpWidget(OkodukaiApp(authService: _LoggedOutAuthService()));
    await tester.pumpAndSettle();

    expect(find.text('おこづかい'), findsOneWidget);
    expect(find.text('利用するユーザーを選んでください。'), findsOneWidget);
    expect(find.byIcon(Icons.savings_rounded), findsOneWidget);
    expect(find.text('保護者としてログイン'), findsOneWidget);
    expect(find.text('子どもとしてログイン'), findsOneWidget);
  });

  testWidgets('小さな画面でもレイアウトが崩れない', (WidgetTester tester) async {
    tester.view.physicalSize = const Size(320, 568);
    tester.view.devicePixelRatio = 1;
    addTearDown(tester.view.resetPhysicalSize);
    addTearDown(tester.view.resetDevicePixelRatio);

    await tester.pumpWidget(OkodukaiApp(authService: _LoggedOutAuthService()));
    await tester.pumpAndSettle();

    expect(tester.takeException(), isNull);
    expect(find.text('おこづかい'), findsOneWidget);
  });

  testWidgets('保護者ログイン画面へ遷移できる', (WidgetTester tester) async {
    await tester.pumpWidget(OkodukaiApp(authService: _LoggedOutAuthService()));
    await tester.pumpAndSettle();

    await tester.tap(find.text('保護者としてログイン'));
    await tester.pumpAndSettle();

    expect(find.text('保護者ログイン'), findsNWidgets(2));
    expect(find.text('メールアドレス'), findsOneWidget);
    expect(find.text('パスワード'), findsOneWidget);
    expect(find.text('メールアドレスを保存する'), findsOneWidget);
  });

  testWidgets('子どもログイン画面へ遷移できる', (WidgetTester tester) async {
    await tester.pumpWidget(OkodukaiApp(authService: _LoggedOutAuthService()));
    await tester.pumpAndSettle();

    await tester.tap(find.text('子どもとしてログイン'));
    await tester.pumpAndSettle();

    expect(find.text('子どもログイン'), findsNWidgets(2));
    expect(find.text('家族コード'), findsOneWidget);
    expect(find.text('ログインID'), findsOneWidget);
    expect(find.text('ログイン情報を保存する'), findsOneWidget);
  });

  testWidgets('保護者新規登録画面で必須項目を検証できる', (WidgetTester tester) async {
    await tester.pumpWidget(OkodukaiApp(authService: _LoggedOutAuthService()));
    await tester.pumpAndSettle();
    await tester.tap(find.text('保護者としてログイン'));
    await tester.pumpAndSettle();
    await tester.tap(find.text('初めての方は新規登録'));
    await tester.pumpAndSettle();

    expect(find.text('保護者新規登録'), findsOneWidget);
    expect(find.text('名前'), findsOneWidget);
    expect(find.text('メールアドレス'), findsOneWidget);
    expect(find.text('パスワード'), findsOneWidget);
    expect(find.text('パスワード確認'), findsOneWidget);

    await tester.ensureVisible(find.text('登録する'));
    await tester.tap(find.text('登録する'));
    await tester.pump();

    expect(find.text('名前を入力してください。'), findsOneWidget);
    expect(find.text('メールアドレスを入力してください。'), findsOneWidget);
    expect(find.text('パスワードを入力してください。'), findsOneWidget);
  });

  testWidgets('保護者ログイン成功後に保護者ホームへ遷移する', (WidgetTester tester) async {
    await tester.pumpWidget(OkodukaiApp(authService: _SuccessfulAuthService()));
    await tester.pumpAndSettle();
    await tester.tap(find.text('保護者としてログイン'));
    await tester.pumpAndSettle();

    final fields = find.byType(TextFormField);
    await tester.enterText(fields.at(0), 'parent@example.com');
    await tester.enterText(fields.at(1), 'password');
    await tester.ensureVisible(find.text('ログイン'));
    await tester.tap(find.text('ログイン'));
    await tester.pump();
    await tester.pump(const Duration(milliseconds: 300));

    expect(find.text('お子様一覧'), findsOneWidget);
  });

  testWidgets('子どもログイン成功後に子どもホームへ遷移する', (WidgetTester tester) async {
    await tester.pumpWidget(
      OkodukaiApp(authService: _SuccessfulChildAuthService()),
    );
    await tester.pumpAndSettle();
    await tester.tap(find.text('子どもとしてログイン'));
    await tester.pumpAndSettle();

    final fields = find.byType(TextFormField);
    await tester.enterText(fields.at(0), '12345678');
    await tester.enterText(fields.at(1), 'taro');
    await tester.enterText(fields.at(2), 'password');
    await tester.ensureVisible(find.text('ログイン'));
    await tester.tap(find.text('ログイン'));
    await tester.pump();
    await tester.pump(const Duration(milliseconds: 300));

    expect(find.text('おこづかい'), findsOneWidget);
  });

  testWidgets('保存済みroleに応じて保護者画面を表示しログアウトで戻る', (WidgetTester tester) async {
    final service = _RestoredAuthService(role: 'parent');
    await tester.pumpWidget(OkodukaiApp(authService: service));
    await tester.pump();
    await tester.pump(const Duration(milliseconds: 300));

    expect(find.text('お子様一覧'), findsOneWidget);
    await tester.tap(find.byTooltip('ログアウト'));
    await tester.pumpAndSettle();

    expect(service.logoutCalled, isTrue);
    expect(find.text('利用するユーザーを選んでください。'), findsOneWidget);
  });

  testWidgets('保存済みroleがchildの場合は子ども画面を表示する', (WidgetTester tester) async {
    await tester.pumpWidget(
      OkodukaiApp(authService: _RestoredAuthService(role: 'child')),
    );
    await tester.pump();
    await tester.pump(const Duration(milliseconds: 300));

    expect(find.text('おこづかい'), findsOneWidget);
  });

  testWidgets('子どもホームに残高・貯金目標・取引・メニューを表示する', (WidgetTester tester) async {
    final authService = _LoggedOutAuthService();
    await tester.pumpWidget(
      ChangeNotifierProvider<AuthState>(
        create: (_) => AuthState(authService),
        child: MaterialApp(
          theme: AppTheme.light,
          home: ChildHomeScreen(homeService: _FakeChildHomeService()),
        ),
      ),
    );
    await tester.pumpAndSettle();

    expect(find.text('たろうさん'), findsOneWidget);
    expect(find.text('1,250円'), findsOneWidget);
    expect(find.text('ゲームを買う'), findsOneWidget);
    expect(find.text('3,000円'), findsOneWidget);
    expect(find.text('達成率 40%'), findsOneWidget);
    expect(find.text('おやつ'), findsOneWidget);
    expect(find.text('つかったものを記録する'), findsOneWidget);

    await tester.ensureVisible(find.text('プロフィール'));
    await tester.tap(find.text('プロフィール'));
    await tester.pump();
    await tester.pump(const Duration(milliseconds: 300));
    expect(find.text('プロフィール'), findsWidgets);
  });

  testWidgets('支出記録画面で残高超過と使用日を検証する', (WidgetTester tester) async {
    await tester.pumpWidget(
      MaterialApp(
        theme: AppTheme.light,
        home: const ChildExpenseRecordScreen(currentBalance: 1000),
      ),
    );

    final fields = find.byType(TextFormField);
    await tester.enterText(fields.at(0), 'おやつ');
    await tester.enterText(fields.at(1), '1001');
    await tester.ensureVisible(find.text('登録する'));
    await tester.tap(find.text('登録する'));
    await tester.pump();

    expect(find.text('現在の残高を超える金額は登録できません。'), findsOneWidget);
    expect(find.text('使用日を選択してください。'), findsOneWidget);
  });

  testWidgets('支出履歴を新しい順で表示する', (WidgetTester tester) async {
    final service = _FakeExpenseHistoryService(
      expenses: [
        ChildExpenseHistoryItem(
          id: 1,
          description: '古い支出',
          amount: 100,
          usedOn: DateTime(2026, 8, 1),
        ),
        ChildExpenseHistoryItem(
          id: 2,
          description: '新しい支出',
          amount: 200,
          usedOn: DateTime(2026, 8, 10),
        ),
      ],
    );
    await tester.pumpWidget(
      MaterialApp(
        theme: AppTheme.light,
        home: ChildExpenseHistoryScreen(historyService: service),
      ),
    );
    await tester.pumpAndSettle();

    expect(find.text('1,250円'), findsOneWidget);
    expect(
      tester.getTopLeft(find.text('新しい支出')).dy,
      lessThan(tester.getTopLeft(find.text('古い支出')).dy),
    );
    expect(find.text('-200円'), findsOneWidget);
  });

  testWidgets('支出履歴が0件の場合に案内を表示しPull to Refreshで再取得する', (
    WidgetTester tester,
  ) async {
    final service = _FakeExpenseHistoryService(expenses: const []);
    await tester.pumpWidget(
      MaterialApp(
        theme: AppTheme.light,
        home: ChildExpenseHistoryScreen(historyService: service),
      ),
    );
    await tester.pumpAndSettle();

    expect(find.text('まだ支出履歴がありません'), findsOneWidget);
    await tester.fling(find.byType(ListView), const Offset(0, 300), 1000);
    await tester.pumpAndSettle();
    expect(service.fetchCount, 2);
  });

  testWidgets('お手伝い履歴を新しい順と円表示で表示する', (WidgetTester tester) async {
    await tester.pumpWidget(
      MaterialApp(
        theme: AppTheme.light,
        home: ChildChoreHistoryScreen(
          historyService: _FakeChoreHistoryService(
            chores: [
              ChildChoreHistoryItem(
                id: 1,
                description: '古いお手伝い',
                rewardAmount: 50,
                completedOn: DateTime(2026, 8, 1),
              ),
              ChildChoreHistoryItem(
                id: 2,
                description: '新しいお手伝い',
                rewardAmount: 100,
                completedOn: DateTime(2026, 8, 11),
              ),
            ],
          ),
        ),
      ),
    );
    await tester.pumpAndSettle();

    expect(find.text('100円'), findsOneWidget);
    expect(
      tester.getTopLeft(find.text('新しいお手伝い')).dy,
      lessThan(tester.getTopLeft(find.text('古いお手伝い')).dy),
    );
  });

  testWidgets('お手伝い履歴が0件の場合に案内を表示する', (WidgetTester tester) async {
    await tester.pumpWidget(
      MaterialApp(
        theme: AppTheme.light,
        home: ChildChoreHistoryScreen(
          historyService: _FakeChoreHistoryService(chores: const []),
        ),
      ),
    );
    await tester.pumpAndSettle();

    expect(find.text('まだお手伝い履歴がありません'), findsOneWidget);
  });
}

class _SuccessfulAuthService extends AuthService {
  @override
  Future<String?> getSavedToken() async => null;

  @override
  Future<String?> getSavedRole() async => null;

  @override
  Future<void> clearLocalSession() async {}

  @override
  Future<String?> getRememberedParentEmail() async => null;

  @override
  Future<ParentLoginResult> loginParent({
    required String email,
    required String password,
    required bool rememberEmail,
  }) async {
    return ParentLoginResult(
      token: 'test-token',
      role: 'parent',
      parent: RegisteredParent(id: 1, name: '山田太郎', email: email),
    );
  }
}

class _LoggedOutAuthService extends AuthService {
  @override
  Future<String?> getSavedToken() async => null;

  @override
  Future<String?> getSavedRole() async => null;

  @override
  Future<void> clearLocalSession() async {}

  @override
  Future<String?> getRememberedParentEmail() async => null;

  @override
  Future<RememberedChildLogin> getRememberedChildLogin() async {
    return const RememberedChildLogin();
  }
}

class _SuccessfulChildAuthService extends AuthService {
  @override
  Future<String?> getSavedToken() async => null;

  @override
  Future<String?> getSavedRole() async => null;

  @override
  Future<void> clearLocalSession() async {}

  @override
  Future<RememberedChildLogin> getRememberedChildLogin() async {
    return const RememberedChildLogin();
  }

  @override
  Future<ChildLoginResult> loginChild({
    required String familyCode,
    required String loginId,
    required String password,
    required bool rememberLogin,
  }) async {
    return ChildLoginResult(
      token: 'child-token',
      role: 'child',
      child: LoggedInChild(id: 2, name: 'たろう', loginId: loginId),
    );
  }
}

class _RestoredAuthService extends AuthService {
  _RestoredAuthService({required this.role});

  final String role;
  bool logoutCalled = false;

  @override
  Future<String?> getSavedToken() async => 'saved-token';

  @override
  Future<String?> getSavedRole() async => role;

  @override
  Future<void> logout() async {
    logoutCalled = true;
  }
}

class _FakeChildHomeService extends ChildHomeService {
  @override
  Future<ChildHomeData> fetchHome() async {
    return ChildHomeData(
      id: 2,
      name: 'たろう',
      balance: 1250,
      savingsGoal: const ChildSavingsGoal(
        title: 'ゲームを買う',
        targetAmount: 5000,
        savedAmount: 2000,
      ),
      recentTransactions: [
        ChildTransaction(
          id: 1,
          title: 'おやつ',
          amount: 150,
          type: 'expense',
          occurredAt: DateTime(2026, 8, 10),
        ),
      ],
    );
  }
}

class _FakeExpenseHistoryService extends ChildExpenseHistoryService {
  _FakeExpenseHistoryService({required this.expenses});

  final List<ChildExpenseHistoryItem> expenses;
  int fetchCount = 0;

  @override
  Future<ChildExpenseHistoryPage> fetchExpenses({int page = 1}) async {
    fetchCount++;
    return ChildExpenseHistoryPage(
      currentBalance: 1250,
      expenses: expenses,
      currentPage: page,
      lastPage: 1,
    );
  }
}

class _FakeChoreHistoryService extends ChildChoreHistoryService {
  _FakeChoreHistoryService({required this.chores});

  final List<ChildChoreHistoryItem> chores;

  @override
  Future<ChildChoreHistoryPage> fetchChores({int page = 1}) async {
    return ChildChoreHistoryPage(
      chores: chores,
      currentPage: page,
      lastPage: 1,
    );
  }
}
