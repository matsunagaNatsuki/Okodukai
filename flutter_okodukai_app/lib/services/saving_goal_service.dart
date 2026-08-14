import '../models/child_saving_goal.dart';
import '../models/parent_child_saving_goal.dart';
import 'child/child_saving_goal_service.dart';
import 'parent/parent_child_saving_goal_service.dart';

class SavingGoalService {
  SavingGoalService({
    ChildSavingGoalService? childService,
    ParentChildSavingGoalService? parentService,
  }) : _childService = childService ?? ChildSavingGoalService(),
       _parentService = parentService ?? ParentChildSavingGoalService();

  final ChildSavingGoalService _childService;
  final ParentChildSavingGoalService _parentService;

  Future<ChildSavingGoalData> fetchCurrentChildGoal() =>
      _childService.fetchSavingGoal();
  Future<ChildSavingGoalData> saveCurrentChildGoal({
    required String wantedItem,
    required int targetAmount,
    required bool isEditing,
  }) => _childService.saveSavingGoal(
    wantedItem: wantedItem,
    targetAmount: targetAmount,
    isEditing: isEditing,
  );
  Future<ParentChildSavingGoalData> fetchChildGoal(int childUserId) =>
      _parentService.fetchSavingGoal(childUserId);
}
