import 'package:intl/intl.dart';

abstract final class CurrencyFormatter {
  static final NumberFormat _formatter = NumberFormat('#,##0');

  static String yen(int amount) => '${_formatter.format(amount)}円';
}
