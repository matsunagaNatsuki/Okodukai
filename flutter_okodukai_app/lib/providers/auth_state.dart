import 'package:flutter/foundation.dart';

import '../services/auth_service.dart';

enum AuthStatus { initializing, authenticated, unauthenticated }

class AuthState extends ChangeNotifier {
  AuthState(this._authService);

  final AuthService _authService;

  AuthStatus _status = AuthStatus.initializing;
  String? _role;
  String? _logoutError;
  bool _isLoggingOut = false;

  AuthStatus get status => _status;
  String? get role => _role;
  String? get logoutError => _logoutError;
  bool get isLoggingOut => _isLoggingOut;
  bool get isLoggedIn =>
      _status == AuthStatus.authenticated &&
      (_role == 'parent' || _role == 'child');

  Future<void> initialize() async {
    try {
      final token = await _authService.getSavedToken();
      final role = await _authService.getSavedRole();
      if (token != null &&
          token.isNotEmpty &&
          (role == 'parent' || role == 'child')) {
        _role = role;
        _status = AuthStatus.authenticated;
      } else {
        await _authService.clearLocalSession();
        _setUnauthenticated();
      }
    } catch (_) {
      _setUnauthenticated();
    }
    notifyListeners();
  }

  Future<void> loginParent({
    required String email,
    required String password,
    required bool rememberEmail,
  }) async {
    await _authService.loginParent(
      email: email,
      password: password,
      rememberEmail: rememberEmail,
    );
    _role = 'parent';
    _status = AuthStatus.authenticated;
    notifyListeners();
  }

  Future<void> loginChild({
    required String familyCode,
    required String loginId,
    required String password,
    required bool rememberLogin,
  }) async {
    await _authService.loginChild(
      familyCode: familyCode,
      loginId: loginId,
      password: password,
      rememberLogin: rememberLogin,
    );
    _role = 'child';
    _status = AuthStatus.authenticated;
    notifyListeners();
  }

  Future<void> logout() async {
    if (_isLoggingOut) return;
    _isLoggingOut = true;
    _logoutError = null;
    notifyListeners();

    try {
      await _authService.logout();
    } on AuthException catch (error) {
      _logoutError = error.message;
    } finally {
      _isLoggingOut = false;
      _setUnauthenticated();
      notifyListeners();
    }
  }

  void _setUnauthenticated() {
    _role = null;
    _status = AuthStatus.unauthenticated;
  }
}
