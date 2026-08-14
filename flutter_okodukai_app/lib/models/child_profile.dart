import 'user.dart';

class ChildProfile extends User {
  const ChildProfile({
    required super.id,
    required super.name,
    super.profileImageUrl,
  }) : super(role: 'child');

  factory ChildProfile.fromJson(Map<String, dynamic> json) {
    final data = json['data'] is Map<String, dynamic>
        ? json['data'] as Map<String, dynamic>
        : json;
    final profile = data['child'] is Map<String, dynamic>
        ? data['child'] as Map<String, dynamic>
        : data;
    final user = User.fromJson(profile);
    return ChildProfile(
      id: user.id,
      name: user.name,
      profileImageUrl: user.profileImageUrl,
    );
  }
}
