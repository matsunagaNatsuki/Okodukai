import 'dart:async';
import 'dart:convert';
import 'dart:typed_data';

import 'package:http/http.dart' as http;

import '../../config/api_config.dart';
import '../../models/child_profile.dart';
import '../api_service.dart';
import '../auth_storage_service.dart';

class ChildProfileService {
  ChildProfileService({
    ApiService? apiService,
    AuthStorageService? storageService,
  }) : _apiService = apiService ?? ApiService(),
       _storageService = storageService ?? AuthStorageService();

  final ApiService _apiService;
  final AuthStorageService _storageService;

  Future<ChildProfile> fetchProfile() async {
    final token = await _token();
    try {
      final response = await _apiService.get(
        ApiConfig.childProfileEndpoint,
        headers: {'Authorization': 'Bearer $token'},
      );
      return _decode(response);
    } on TimeoutException {
      throw const ChildProfileException('通信がタイムアウトしました。');
    } on http.ClientException {
      throw const ChildProfileException('サーバーに接続できません。');
    } on FormatException catch (error) {
      throw ChildProfileException(error.message);
    }
  }

  Future<ChildProfile> updateProfile({
    required String name,
    Uint8List? imageBytes,
    String? imageFileName,
  }) async {
    final token = await _token();
    try {
      final files = <http.MultipartFile>[];
      if (imageBytes != null && imageFileName != null) {
        files.add(
          http.MultipartFile.fromBytes(
            'profile_image',
            imageBytes,
            filename: imageFileName,
          ),
        );
      }
      final response = await _apiService.multipart(
        'POST',
        ApiConfig.childProfileEndpoint,
        headers: {'Authorization': 'Bearer $token'},
        fields: {'name': name, '_method': 'PUT'},
        files: files,
      );
      return _decode(response);
    } on TimeoutException {
      throw const ChildProfileException('通信がタイムアウトしました。');
    } on http.ClientException {
      throw const ChildProfileException('サーバーに接続できません。');
    } on FormatException catch (error) {
      throw ChildProfileException(error.message);
    }
  }

  Future<String> _token() async {
    final token = await _storageService.readToken();
    if (token == null || token.isEmpty) {
      throw const ChildProfileException('ログイン情報が見つかりません。');
    }
    return token;
  }

  ChildProfile _decode(http.Response response) {
    final decoded = jsonDecode(utf8.decode(response.bodyBytes));
    if (decoded is! Map<String, dynamic>) {
      throw const FormatException('JSONの形式が正しくありません。');
    }
    if (response.statusCode < 200 || response.statusCode >= 300) {
      final message = decoded['message'];
      throw ChildProfileException(
        message is String && message.isNotEmpty
            ? message
            : 'プロフィールを更新できませんでした。',
      );
    }
    return ChildProfile.fromJson(decoded);
  }
}

class ChildProfileException implements Exception {
  const ChildProfileException(this.message);
  final String message;
}
