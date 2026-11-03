import 'package:flutter_test/flutter_test.dart';
import 'package:mimoo_rider/data/models/entities.dart';

/// Tests for the POD flow's data model layer.
/// Full widget tests require AppRepositoriesScope + ConnectivityMonitor
/// which need significant mock infrastructure. These unit tests cover
/// the model behavior that the POD flow depends on.

void main() {
  test('Parcel.isCod returns true for non-prepaid with positive COD', () {
    final parcel = Parcel(
      trackingNo: 'TRK-001',
      jobType: ParcelJobType.delivery,
      status: ParcelStatus.outForDelivery,
      sellerName: 'Seller',
      sellerAddress: 'Hub',
      buyerName: 'Buyer',
      buyerPhone: '0918',
      address: 'Address',
      codAmount: 500,
      isPrepaid: false,
      updatedAt: DateTime.now(),
    );
    expect(parcel.isCod, isTrue);
  });

  test('Parcel.isCod returns false for prepaid', () {
    final parcel = Parcel(
      trackingNo: 'TRK-002',
      jobType: ParcelJobType.delivery,
      status: ParcelStatus.outForDelivery,
      sellerName: 'Seller',
      sellerAddress: 'Hub',
      buyerName: 'Buyer',
      buyerPhone: '0918',
      address: 'Address',
      codAmount: 0,
      isPrepaid: true,
      updatedAt: DateTime.now(),
    );
    expect(parcel.isCod, isFalse);
  });

  test('Parcel.isCod returns false for zero COD non-prepaid', () {
    final parcel = Parcel(
      trackingNo: 'TRK-003',
      jobType: ParcelJobType.delivery,
      status: ParcelStatus.outForDelivery,
      sellerName: 'Seller',
      sellerAddress: 'Hub',
      buyerName: 'Buyer',
      buyerPhone: '0918',
      address: 'Address',
      codAmount: 0,
      isPrepaid: false,
      updatedAt: DateTime.now(),
    );
    expect(parcel.isCod, isFalse);
  });

  test('Parcel lifecycleStep is 3 for out_for_delivery', () {
    final parcel = Parcel(
      trackingNo: 'TRK-004',
      jobType: ParcelJobType.delivery,
      status: ParcelStatus.outForDelivery,
      sellerName: 'S',
      sellerAddress: 'H',
      buyerName: 'B',
      buyerPhone: '0',
      address: 'A',
      updatedAt: DateTime.now(),
    );
    expect(parcel.lifecycleStep, 3);
  });

  test('Parcel lifecycleStep is 4 for delivered', () {
    final parcel = Parcel(
      trackingNo: 'TRK-005',
      jobType: ParcelJobType.delivery,
      status: ParcelStatus.delivered,
      sellerName: 'S',
      sellerAddress: 'H',
      buyerName: 'B',
      buyerPhone: '0',
      address: 'A',
      updatedAt: DateTime.now(),
    );
    expect(parcel.lifecycleStep, 4);
  });

  test('Parcel status labels are human-readable', () {
    expect(ParcelStatus.outForDelivery.label, 'Out for Delivery');
    expect(ParcelStatus.delivered.label, 'Delivered');
    expect(ParcelStatus.failedDelivery.label, 'Overdue');
    expect(ParcelStatus.returnToSender.label, 'Return to Sender');
  });

  test('Parcel canTransition enforces the delivery lifecycle', () {
    expect(canTransition(ParcelStatus.assigned, ParcelStatus.pickedUp), isTrue);
    expect(canTransition(ParcelStatus.pickedUp, ParcelStatus.atHub), isTrue);
    expect(canTransition(ParcelStatus.atHub, ParcelStatus.outForDelivery), isTrue);
    expect(canTransition(ParcelStatus.outForDelivery, ParcelStatus.delivered), isTrue);
    expect(canTransition(ParcelStatus.outForDelivery, ParcelStatus.failedDelivery), isTrue);
    expect(canTransition(ParcelStatus.outForDelivery, ParcelStatus.returnToSender), isTrue);
    expect(canTransition(ParcelStatus.outForDelivery, ParcelStatus.atHub), isTrue);
    expect(canTransition(ParcelStatus.failedDelivery, ParcelStatus.atHub), isTrue);
    expect(canTransition(ParcelStatus.failedDelivery, ParcelStatus.returnToSender), isTrue);
    expect(canTransition(ParcelStatus.returnToSender, ParcelStatus.returned), isTrue);
  });

  test('Parcel cannot skip lifecycle steps', () {
    expect(canTransition(ParcelStatus.assigned, ParcelStatus.delivered), isFalse);
    expect(canTransition(ParcelStatus.assigned, ParcelStatus.outForDelivery), isFalse);
    expect(canTransition(ParcelStatus.pickedUp, ParcelStatus.delivered), isFalse);
    expect(canTransition(ParcelStatus.delivered, ParcelStatus.atHub), isFalse);
    expect(canTransition(ParcelStatus.returned, ParcelStatus.delivered), isFalse);
  });

  test('Parcel sellerPhone is nullable and round-trips through toMap/fromMap', () {
    final now = DateTime.now();
    final parcel = Parcel(
      trackingNo: 'TRK-006',
      jobType: ParcelJobType.delivery,
      status: ParcelStatus.outForDelivery,
      sellerName: 'Seller',
      sellerAddress: 'Hub',
      sellerPhone: '09171234567',
      buyerName: 'Buyer',
      buyerPhone: '0918',
      address: 'A',
      codAmount: 500,
      isPrepaid: false,
      updatedAt: now,
    );

    final map = parcel.toMap();
    expect(map['seller_phone'], '09171234567');

    final restored = Parcel.fromMap(map);
    expect(restored.sellerPhone, '09171234567');
    expect(restored.trackingNo, 'TRK-006');
  });

  test('Parcel with null sellerPhone round-trips correctly', () {
    final now = DateTime.now();
    final parcel = Parcel(
      trackingNo: 'TRK-007',
      jobType: ParcelJobType.delivery,
      status: ParcelStatus.assigned,
      sellerName: 'S',
      sellerAddress: 'H',
      sellerPhone: null,
      buyerName: 'B',
      buyerPhone: '0',
      address: 'A',
      updatedAt: now,
    );

    final map = parcel.toMap();
    expect(map['seller_phone'], isNull);

    final restored = Parcel.fromMap(map);
    expect(restored.sellerPhone, isNull);
  });
}
