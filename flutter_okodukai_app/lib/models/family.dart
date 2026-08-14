class Family {
  const Family({required this.code, this.id, this.name});

  final int? id;
  final String code;
  final String? name;

  factory Family.fromJson(Map<String, dynamic> json) {
    final code = json['code'] ?? json['family_code'];
    if (code is! String || !RegExp(r'^\d{8}$').hasMatch(code)) {
      throw const FormatException('家族コードの形式が正しくありません。');
    }
    final id = json['id'];
    return Family(
      id: id is num ? id.toInt() : int.tryParse(id?.toString() ?? ''),
      code: code,
      name: json['name'] is String && (json['name'] as String).isNotEmpty
          ? json['name'] as String
          : null,
    );
  }

  Map<String, dynamic> toJson() => {
    if (id != null) 'id': id,
    'code': code,
    if (name != null) 'name': name,
  };
}
