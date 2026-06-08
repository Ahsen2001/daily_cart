import 'package:intl/intl.dart';

class CurrencyFormatter {
  static final _lkr = NumberFormat.currency(
    locale: 'en_LK',
    symbol: 'LKR ',
    decimalDigits: 2,
  );

  static String lkr(num amount) => _lkr.format(amount);
}
