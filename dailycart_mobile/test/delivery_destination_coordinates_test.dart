import 'package:dailycart_mobile/models/address_model.dart';
import 'package:dailycart_mobile/models/delivery_model.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  test('rider navigation accepts only valid order destination coordinates', () {
    const exactDestination = DeliveryModel(
      id: 10,
      orderNumber: 'DC-10',
      status: 'accepted',
      latitude: 7.9167,
      longitude: 81.0500,
    );
    const missingDestination = DeliveryModel(
      id: 11,
      orderNumber: 'DC-11',
      status: 'accepted',
    );
    const invalidDestination = DeliveryModel(
      id: 12,
      orderNumber: 'DC-12',
      status: 'accepted',
      latitude: 100,
      longitude: 81.0500,
    );

    expect(exactDestination.hasValidDestinationCoordinates, isTrue);
    expect(missingDestination.hasValidDestinationCoordinates, isFalse);
    expect(invalidDestination.hasValidDestinationCoordinates, isFalse);
  });

  test('checkout address requires a valid customer map pin', () {
    const pinnedAddress = AddressModel(
      id: 1,
      fullName: 'Customer',
      phoneNumber: '0000000000',
      addressLine1: 'Delivery address',
      city: 'City',
      district: 'District',
      latitude: 7.9167,
      longitude: 81.0500,
    );
    const addressWithoutPin = AddressModel(
      id: 2,
      fullName: 'Customer',
      phoneNumber: '0000000000',
      addressLine1: 'Delivery address',
      city: 'City',
      district: 'District',
    );

    expect(pinnedAddress.hasValidCoordinates, isTrue);
    expect(addressWithoutPin.hasValidCoordinates, isFalse);
  });
}
