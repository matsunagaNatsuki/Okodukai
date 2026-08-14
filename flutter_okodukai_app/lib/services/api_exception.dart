enum ApiExceptionType {
  unauthorized,
  timeout,
  network,
  server,
  invalidResponse,
}

class ApiException implements Exception {
  const ApiException(this.message, {required this.type, this.statusCode});

  final String message;
  final ApiExceptionType type;
  final int? statusCode;

  bool get isUnauthorized => type == ApiExceptionType.unauthorized;

  @override
  String toString() => message;
}
