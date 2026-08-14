import 'package:flutter_test/flutter_test.dart';

import 'package:flutter_okodukai_app/models/allowance.dart';
import 'package:flutter_okodukai_app/models/chore.dart';
import 'package:flutter_okodukai_app/models/chore_record.dart';
import 'package:flutter_okodukai_app/models/family.dart';
import 'package:flutter_okodukai_app/models/saving_goal.dart';
import 'package:flutter_okodukai_app/models/transaction.dart';
import 'package:flutter_okodukai_app/models/user.dart';

void main() {
  test('UserとFamilyのnullable項目を安全に変換する', () {
    final user = User.fromJson({'id': '2', 'name': 'たろう'});
    final family = Family.fromJson({'family_code': '12345678'});
    expect(user.id, 2);
    expect(user.email, isNull);
    expect(user.toJson().containsKey('email'), isFalse);
    expect(family.id, isNull);
    expect(family.toJson()['code'], '12345678');
  });

  test('Chore・ChoreRecord・Allowanceを相互変換する', () {
    final chore = Chore.fromJson({
      'id': 1,
      'description': 'お皿洗い',
      'reward_amount': '100',
    });
    final record = ChoreRecord.fromJson({
      'id': 2,
      'chore_setting_id': 1,
      'description': 'お皿洗い',
      'reward_amount': 100,
      'performed_on': '2026-08-12',
    });
    final allowance = Allowance.fromJson({
      'id': 3,
      'amount': 1500,
      'payment_day': 15,
      'is_active': 1,
    });
    expect(chore.toJson()['reward_amount'], 100);
    expect(record.toJson()['performed_on'], '2026-08-12');
    expect(allowance.toJson()['is_active'], isTrue);
  });

  test('TransactionとSavingGoalの計算値を維持する', () {
    final transaction = Transaction.fromJson({
      'id': 4,
      'title': 'お手伝い',
      'amount': 100,
      'type': 'income',
      'occurred_at': '2026-08-12',
    });
    final goal = SavingGoal.fromJson({
      'id': 5,
      'wanted_item': 'ゲーム',
      'target_amount': 5000,
      'current_balance': 2000,
    });
    expect(transaction.isIncome, isTrue);
    expect(transaction.toJson()['type'], 'income');
    expect(goal.remainingAmount, 3000);
    expect(goal.achievementPercent, 40);
    expect(goal.toJson()['wanted_item'], 'ゲーム');
  });
}
