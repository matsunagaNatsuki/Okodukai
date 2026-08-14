import '../models/parent_regular_allowance.dart';
import 'parent/parent_regular_allowance_service.dart';

class AllowanceService {
  AllowanceService({ParentRegularAllowanceService? regularAllowanceService})
    : _service = regularAllowanceService ?? ParentRegularAllowanceService();

  final ParentRegularAllowanceService _service;

  Future<ParentRegularAllowanceData> fetchSetting(int childUserId) =>
      _service.fetchSetting(childUserId);
  Future<ParentRegularAllowanceData> saveSetting({
    required int childUserId,
    required int amount,
    required int paymentDay,
    required bool isActive,
    required bool isEditing,
  }) => _service.saveSetting(
    childUserId: childUserId,
    amount: amount,
    paymentDay: paymentDay,
    isActive: isActive,
    isEditing: isEditing,
  );
}
