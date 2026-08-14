import 'dart:typed_data';

import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';

import '../../models/child_profile.dart';
import '../../services/child/child_profile_service.dart';
import '../../theme/app_colors.dart';
import '../../widgets/app_button.dart';
import '../../widgets/app_card.dart';
import '../../widgets/app_error_message.dart';
import '../../widgets/app_loading_indicator.dart';
import '../../widgets/app_screen_title.dart';
import '../../widgets/app_text_field.dart';

class ChildProfileScreen extends StatefulWidget {
  const ChildProfileScreen({super.key, this.profileService, this.imagePicker});

  final ChildProfileService? profileService;
  final ImagePicker? imagePicker;

  @override
  State<ChildProfileScreen> createState() => _ChildProfileScreenState();
}

class _ChildProfileScreenState extends State<ChildProfileScreen> {
  final _formKey = GlobalKey<FormState>();
  final _nameController = TextEditingController();
  late final ChildProfileService _service;
  late final ImagePicker _imagePicker;

  ChildProfile? _profile;
  Uint8List? _selectedImageBytes;
  String? _selectedImageName;
  bool _isLoading = true;
  bool _isSaving = false;
  String? _errorMessage;

  @override
  void initState() {
    super.initState();
    _service = widget.profileService ?? ChildProfileService();
    _imagePicker = widget.imagePicker ?? ImagePicker();
    _load();
  }

  @override
  void dispose() {
    _nameController.dispose();
    super.dispose();
  }

  Future<void> _load() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });
    try {
      final profile = await _service.fetchProfile();
      if (!mounted) return;
      setState(() {
        _profile = profile;
        _nameController.text = profile.name;
      });
    } on ChildProfileException catch (error) {
      if (!mounted) return;
      setState(() => _errorMessage = error.message);
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  Future<void> _pickImage() async {
    if (_isSaving) return;
    try {
      final image = await _imagePicker.pickImage(
        source: ImageSource.gallery,
        imageQuality: 85,
        maxWidth: 1200,
      );
      if (image == null || !mounted) return;
      final bytes = await image.readAsBytes();
      if (!mounted) return;
      setState(() {
        _selectedImageBytes = bytes;
        _selectedImageName = image.name;
        _errorMessage = null;
      });
    } catch (_) {
      if (!mounted) return;
      setState(() => _errorMessage = '画像を選択できませんでした。');
    }
  }

  Future<void> _save() async {
    if (_isSaving || !_formKey.currentState!.validate()) return;
    setState(() {
      _isSaving = true;
      _errorMessage = null;
    });
    try {
      final profile = await _service.updateProfile(
        name: _nameController.text.trim(),
        imageBytes: _selectedImageBytes,
        imageFileName: _selectedImageName,
      );
      if (!mounted) return;
      setState(() {
        _profile = profile;
        _nameController.text = profile.name;
        _selectedImageBytes = null;
        _selectedImageName = null;
      });
      ScaffoldMessenger.of(
        context,
      ).showSnackBar(const SnackBar(content: Text('プロフィールを更新しました。')));
    } on ChildProfileException catch (error) {
      if (!mounted) return;
      setState(() => _errorMessage = error.message);
    } finally {
      if (mounted) setState(() => _isSaving = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('プロフィール')),
      body: SafeArea(child: _buildBody()),
    );
  }

  Widget _buildBody() {
    if (_isLoading) {
      return const AppLoadingIndicator(message: 'プロフィールを読み込み中...');
    }
    if (_profile == null) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              AppErrorMessage(message: _errorMessage ?? 'プロフィールを取得できませんでした。'),
              const SizedBox(height: 16),
              AppButton(label: 'もう一度読み込む', onPressed: _load),
            ],
          ),
        ),
      );
    }

    return SingleChildScrollView(
      padding: const EdgeInsets.fromLTRB(20, 16, 20, 32),
      child: Form(
        key: _formKey,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            const AppScreenTitle(
              title: 'プロフィール',
              subtitle: '名前とプロフィール画像を変更できます。',
            ),
            const SizedBox(height: 20),
            AppCard(
              child: Column(
                children: [
                  _ProfileImage(
                    selectedBytes: _selectedImageBytes,
                    imageUrl: _profile!.profileImageUrl,
                  ),
                  const SizedBox(height: 12),
                  TextButton.icon(
                    onPressed: _isSaving ? null : _pickImage,
                    icon: const Icon(Icons.photo_library_outlined),
                    label: const Text('写真ライブラリから選ぶ'),
                  ),
                  const SizedBox(height: 12),
                  AppTextField(
                    label: '名前',
                    controller: _nameController,
                    enabled: !_isSaving,
                    prefixIcon: const Icon(Icons.person_outline),
                    validator: (value) => value == null || value.trim().isEmpty
                        ? '名前を入力してください。'
                        : null,
                  ),
                ],
              ),
            ),
            if (_errorMessage != null) ...[
              const SizedBox(height: 16),
              AppErrorMessage(message: _errorMessage!),
            ],
            const SizedBox(height: 20),
            AppButton(
              label: '更新する',
              isLoading: _isSaving,
              onPressed: _isSaving ? null : _save,
            ),
          ],
        ),
      ),
    );
  }
}

class _ProfileImage extends StatelessWidget {
  const _ProfileImage({this.selectedBytes, this.imageUrl});
  final Uint8List? selectedBytes;
  final String? imageUrl;

  @override
  Widget build(BuildContext context) {
    const fallback = ColoredBox(
      color: AppColors.accent,
      child: Icon(Icons.face_rounded, size: 60, color: AppColors.textPrimary),
    );
    final Widget image;
    if (selectedBytes != null) {
      image = Image.memory(selectedBytes!, fit: BoxFit.cover);
    } else if (imageUrl != null) {
      image = Image.network(
        imageUrl!,
        fit: BoxFit.cover,
        errorBuilder: (_, _, _) => fallback,
      );
    } else {
      image = fallback;
    }
    return ClipOval(child: SizedBox.square(dimension: 128, child: image));
  }
}
