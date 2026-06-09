import 'package:intl/intl.dart';

class CurrencyFormatter {
  static final _formatter = NumberFormat.currency(
    locale: 'en_LK',
    symbol: 'Rs. ',
    decimalDigits: 2,
  );

  static String lkr(num value) {
    return _formatter.format(value);
  }
}
