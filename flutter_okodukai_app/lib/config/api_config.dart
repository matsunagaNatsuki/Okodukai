abstract final class ApiConfig {
  static const String baseUrl = String.fromEnvironment(
    'API_BASE_URL',
    defaultValue: 'http://localhost:8080/api',
  );

  static const Duration timeout = Duration(seconds: 15);

  static const String parentRegisterEndpoint = '/parent/register';
  static const String parentLoginEndpoint = '/parent/login';
  static const String passwordResetCodeEndpoint = '/password-reset/code';
  static const String passwordResetVerifyEndpoint = '/password-reset/verify';
  static const String passwordResetEndpoint = '/password-reset';
  static const String childLoginEndpoint = '/child/login';
  static const String logoutEndpoint = '/logout';
  static const String parentChildrenEndpoint = '/parent/children';

  static String parentChildDetailEndpoint(int childUserId) =>
      '$parentChildrenEndpoint/$childUserId';

  static String parentRegularAllowanceEndpoint(int childUserId) =>
      '$parentChildrenEndpoint/$childUserId/regular-allowance';
  static const String parentChoreSettingsEndpoint = '/parent/chore-settings';

  static String parentChoreSettingEndpoint(int settingId) =>
      '$parentChoreSettingsEndpoint/$settingId';

  static String parentChildChoreRecordsEndpoint(int childUserId) =>
      '$parentChildrenEndpoint/$childUserId/chore-records';
  static String parentChildExpensesEndpoint(int childUserId) =>
      '$parentChildrenEndpoint/$childUserId/expenses';
  static String parentChildChoreHistoryEndpoint(int childUserId) =>
      '$parentChildrenEndpoint/$childUserId/chores';
  static String parentChildSavingGoalEndpoint(int childUserId) =>
      '$parentChildrenEndpoint/$childUserId/saving-goal';
  static const String childHomeEndpoint = '/child/home';
  static const String childExpensesEndpoint = '/child/expenses';
  static const String childChoreHistoryEndpoint = '/child/chores';
  static const String childSavingGoalEndpoint = '/child/saving-goal';
  static const String childProfileEndpoint = '/child/profile';
}
