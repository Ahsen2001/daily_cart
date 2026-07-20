enum PaymentMethodType {
  cashOnDelivery,
  payHere,
  bankTransfer,
  wallet;

  String get apiValue {
    return switch (this) {
      PaymentMethodType.cashOnDelivery => 'cash_on_delivery',
      PaymentMethodType.payHere => 'card',
      PaymentMethodType.bankTransfer => 'bank_transfer',
      PaymentMethodType.wallet => 'wallet',
    };
  }

  String get title {
    return switch (this) {
      PaymentMethodType.cashOnDelivery => 'Cash on Delivery',
      PaymentMethodType.payHere => 'PayHere Card Payment',
      PaymentMethodType.bankTransfer => 'Bank Transfer',
      PaymentMethodType.wallet => 'Wallet',
    };
  }

  String get subtitle {
    return switch (this) {
      PaymentMethodType.cashOnDelivery => 'Pay in LKR when your order arrives.',
      PaymentMethodType.payHere => 'Pay securely by card through PayHere.',
      PaymentMethodType.bankTransfer => 'Placeholder for bank transfer.',
      PaymentMethodType.wallet => 'Placeholder for DailyCart wallet.',
    };
  }
}

class PaymentMethodModel {
  const PaymentMethodModel({
    required this.type,
  });

  final PaymentMethodType type;

  static const availableMethods = [
    PaymentMethodModel(type: PaymentMethodType.cashOnDelivery),
    PaymentMethodModel(type: PaymentMethodType.payHere),
    PaymentMethodModel(type: PaymentMethodType.bankTransfer),
    PaymentMethodModel(type: PaymentMethodType.wallet),
  ];
}
