import '../models/child_chore_history.dart';
import '../models/parent_child_history.dart';
import '../models/parent_chore_record.dart';
import '../models/parent_chore_setting.dart';
import 'child/child_chore_history_service.dart';
import 'parent/parent_child_history_service.dart';
import 'parent/parent_chore_record_service.dart';
import 'parent/parent_chore_setting_service.dart';

class ChoreService {
  ChoreService({
    ChildChoreHistoryService? childHistoryService,
    ParentChildHistoryService? parentHistoryService,
    ParentChoreSettingService? settingService,
    ParentChoreRecordService? recordService,
  }) : _childHistoryService = childHistoryService ?? ChildChoreHistoryService(),
       _parentHistoryService =
           parentHistoryService ?? ParentChildHistoryService(),
       _settingService = settingService ?? ParentChoreSettingService(),
       _recordService = recordService ?? ParentChoreRecordService();

  final ChildChoreHistoryService _childHistoryService;
  final ParentChildHistoryService _parentHistoryService;
  final ParentChoreSettingService _settingService;
  final ParentChoreRecordService _recordService;

  Future<ChildChoreHistoryPage> fetchCurrentChildHistory({int page = 1}) =>
      _childHistoryService.fetchChores(page: page);
  Future<ParentChoreHistoryPage> fetchChildHistory({
    required int childUserId,
    int page = 1,
  }) => _parentHistoryService.fetchChores(childUserId: childUserId, page: page);
  Future<List<ParentChoreSetting>> fetchSettings() =>
      _settingService.fetchSettings();
  Future<ParentChoreSetting> createSetting({
    required String description,
    required int rewardAmount,
  }) => _settingService.createSetting(
    description: description,
    rewardAmount: rewardAmount,
  );
  Future<ParentChoreSetting> updateSetting({
    required int settingId,
    required String description,
    required int rewardAmount,
  }) => _settingService.updateSetting(
    settingId: settingId,
    description: description,
    rewardAmount: rewardAmount,
  );
  Future<void> deleteSetting(int settingId) =>
      _settingService.deleteSetting(settingId);
  Future<ParentChoreRecordFormData> fetchRecordForm(int childUserId) =>
      _recordService.fetchFormData(childUserId);
  Future<ParentChoreRecordResult> createRecord({
    required int childUserId,
    required int choreSettingId,
    required int rewardAmount,
    required DateTime performedOn,
  }) => _recordService.createRecord(
    childUserId: childUserId,
    choreSettingId: choreSettingId,
    rewardAmount: rewardAmount,
    performedOn: performedOn,
  );
}
