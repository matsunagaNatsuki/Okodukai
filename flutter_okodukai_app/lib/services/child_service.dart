import '../models/child_home_data.dart';
import '../models/parent_child.dart';
import '../models/parent_child_management_data.dart';
import 'child/child_home_service.dart';
import 'parent/parent_child_management_service.dart';
import 'parent/parent_child_service.dart';

class ChildService {
  ChildService({
    ChildHomeService? homeService,
    ParentChildService? parentChildService,
    ParentChildManagementService? managementService,
  }) : _homeService = homeService ?? ChildHomeService(),
       _parentChildService = parentChildService ?? ParentChildService(),
       _managementService = managementService ?? ParentChildManagementService();

  final ChildHomeService _homeService;
  final ParentChildService _parentChildService;
  final ParentChildManagementService _managementService;

  Future<ChildHomeData> fetchCurrentChildHome() => _homeService.fetchHome();
  Future<List<ParentChild>> fetchFamilyChildren() =>
      _parentChildService.fetchChildren();
  Future<ParentChildManagementData> fetchManagementData(int childUserId) =>
      _managementService.fetchChild(childUserId);
}
