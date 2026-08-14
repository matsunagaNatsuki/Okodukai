import 'dart:typed_data';

import '../models/child_profile.dart';
import 'child/child_profile_service.dart';

class UserService {
  UserService({ChildProfileService? childProfileService})
    : _childProfileService = childProfileService ?? ChildProfileService();

  final ChildProfileService _childProfileService;

  Future<ChildProfile> fetchChildProfile() =>
      _childProfileService.fetchProfile();

  Future<ChildProfile> updateChildProfile({
    required String name,
    Uint8List? imageBytes,
    String? imageFileName,
  }) => _childProfileService.updateProfile(
    name: name,
    imageBytes: imageBytes,
    imageFileName: imageFileName,
  );
}
