import 'package:dailycart_mobile/models/order_model.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  test('customer tracking exposes the stale-location state', () {
    final order = OrderModel.fromJson({
      'id': 2,
      'order_number': 'DC-2',
      'placed_at': '2026-11-03T12:00:00Z',
      'order_status': 'out_for_delivery',
      'payment_status': 'paid',
      'delivery_address': 'Oddamavadi',
      'delivery': {'status': 'on_the_way', 'tracking_status': 'active'},
      'rider': {
        'name': 'DailyCart Rider',
        'latitude': 7.717,
        'longitude': 81.7,
        'location_recorded_at': '2026-11-03T11:55:00Z',
        'location_age_seconds': 300,
        'location_is_stale': true,
        'location_is_outdated': true,
        'location_message': 'Location may be outdated',
      },
    });

    expect(order.trackingActive, isTrue);
    expect(order.riderLocationIsStale, isTrue);
    expect(order.riderLocationIsOutdated, isTrue);
    expect(order.riderLocationMessage, 'Location may be outdated');
    expect(order.riderLocationRecordedAt, isNotNull);
  });
}
