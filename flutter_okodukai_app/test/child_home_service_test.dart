import 'dart:convert';

import 'package:flutter_test/flutter_test.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';

import 'package:flutter_okodukai_app/config/api_config.dart';
import 'package:flutter_okodukai_app/services/api_service.dart';
import 'package:flutter_okodukai_app/services/auth_storage_service.dart';
import 'package:flutter_okodukai_app/services/child/child_home_service.dart';

void main() {
  test('子どもホームAPIの応答をModelへ変換する', () async {
    final client = MockClient((request) async {
      expect(request.url.path, '/api${ApiConfig.childHomeEndpoint}');
      expect(request.method, 'GET');
      expect(request.headers['Authorization'], 'Bearer child-token');

      return http.Response(
        jsonEncode({
          'data': {
            'child': {
              'id': 2,
              'name': 'たろう',
              'profile_image_url': 'https://example.com/profile.png',
            },
            'balance': 1250,
            'savings_goal': {
              'title': 'ゲームを買う',
              'target_amount': 5000,
              'saved_amount': 2000,
            },
            'recent_transactions': [
              {
                'id': 1,
                'title': 'おやつ',
                'amount': 150,
                'type': 'expense',
                'occurred_at': '2026-08-10',
              },
            ],
          },
        }),
        200,
        headers: {'content-type': 'application/json; charset=utf-8'},
      );
    });
    final service = ChildHomeService(
      apiService: ApiService(client: client),
      storageService: _TokenStorageService(),
    );

    final data = await service.fetchHome();

    expect(data.name, 'たろう');
    expect(data.balance, 1250);
    expect(data.savingsGoal?.remainingAmount, 3000);
    expect(data.savingsGoal?.progressPercent, 40);
    expect(data.recentTransactions.single.title, 'おやつ');
  });
}

class _TokenStorageService extends AuthStorageService {
  @override
  Future<String?> readToken() async => 'child-token';
}
