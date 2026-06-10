class DeliveryProofModel {
  const DeliveryProofModel({
    required this.deliveryId,
    this.proofImage = '',
    this.note = '',
    this.deliveredAt,
  });

  final int deliveryId;
  final String proofImage;
  final String note;
  final DateTime? deliveredAt;

  Map<String, dynamic> toJson() {
    return {
      'delivery_id': deliveryId,
      'note': note,
      'delivered_at': deliveredAt?.toIso8601String(),
    };
  }
}
