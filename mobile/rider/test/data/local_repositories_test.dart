import 'package:flutter_test/flutter_test.dart';
import 'package:mimoo_rider/data/db/app_database.dart';
import 'package:mimoo_rider/data/models/entities.dart';
import 'package:mimoo_rider/data/repositories/local_repositories.dart';
import 'package:sqflite/sqflite.dart';
import 'package:sqflite_common_ffi/sqflite_ffi.dart';

void main() {
  TestWidgetsFlutterBinding.ensureInitialized();
  late AppRepositories repositories;

  setUpAll(() {
    sqfliteFfiInit();
  });

  setUp(() async {
    repositories = await AppRepositories.open(
      database: AppDatabase(
        factory: databaseFactoryFfi,
        databasePath: inMemoryDatabasePath,
      ),
    );
  });

  tearDown(() async {
    await repositories.close();
  });

  test('creates all version-one tables and seeds once', () async {
    final tables = await repositories.database.database.rawQuery(
      "SELECT name FROM sqlite_master WHERE type = 'table'",
    );
    final names = tables.map((row) => row['name']).toSet();
    expect(
      names,
      containsAll([
        'rider_profile',
        'parcels',
        'parcel_status_logs',
        'job_offers',
        'cod_remittances',
        'notifications',
        'conversations',
        'messages',
        'emergency_contacts',
        'outbox',
        'app_meta',
      ]),
    );
    expect(repositories.parcels.parcels, isNotEmpty);
    expect(
      repositories.offers.offers.where((offer) => offer.state == 'pending'),
      hasLength(8),
    );
    await repositories.database.seedIfNeeded();
    final count = Sqflite.firstIntValue(await repositories.database.database
        .rawQuery('SELECT COUNT(*) FROM parcels'));
    expect(count, repositories.parcels.parcels.length);
    final offerCount = Sqflite.firstIntValue(await repositories
        .database.database
        .rawQuery("SELECT COUNT(*) FROM job_offers WHERE state = 'pending'"));
    expect(offerCount, 8);
  });

  test('transition table accepts only the defined edges', () {
    expect(canTransition(ParcelStatus.readyForPickup, ParcelStatus.assigned),
        isTrue);
    expect(canTransition(ParcelStatus.assigned, ParcelStatus.pickedUp), isTrue);
    expect(canTransition(ParcelStatus.assigned, ParcelStatus.outForDelivery),
        isFalse);
    expect(canTransition(ParcelStatus.pickedUp, ParcelStatus.outForDelivery),
        isFalse);
    expect(canTransition(ParcelStatus.pickedUp, ParcelStatus.atHub), isTrue);
    expect(
        canTransition(ParcelStatus.atHub, ParcelStatus.outForDelivery), isTrue);
    expect(
        canTransition(ParcelStatus.outForDelivery, ParcelStatus.failedDelivery),
        isTrue);
    expect(canTransition(ParcelStatus.delivered, ParcelStatus.atHub), isFalse);
    expect(
        canTransition(ParcelStatus.returned, ParcelStatus.delivered), isFalse);
  });

  test('accepts a pickup offer when three parcels are already active',
      () async {
    expect(repositories.parcels.active.length, 3);

    final offer = repositories.offers.offers
        .firstWhere((offer) => offer.parcelId == 'TRK-0012450');
    await repositories.offers.setState(offer.id!, 'accepted');

    expect(repositories.parcels.active.length, 4);
    expect(
      repositories.offers.offers
          .firstWhere((item) => item.id == offer.id)
          .state,
      'accepted',
    );
  });

  test('earnings are derived from parcel fees', () {
    final now = DateTime.now();
    expect(
      repositories.parcels
          .earningsSince(DateTime(now.year, now.month, now.day)),
      210,
    );
    expect(
        repositories.parcels
            .earningsSince(now.subtract(const Duration(days: 7))),
        1180);
  });

  test('COD on hand subtracts pending remittances', () async {
    final before = repositories.parcels.codOnHand;
    expect(before, greaterThan(0));
    await repositories.parcels.submitRemittance(
      amount: before,
      method: 'gcash',
      reference: 'GCASH-TEST-001',
    );
    expect(repositories.parcels.pendingRemittance, before);
    expect(repositories.parcels.codOnHand, 0);
  });

  test('remitCollected clears the balance and records a confirmed remittance',
      () async {
    final before = repositories.parcels.codOnHand;
    expect(before, greaterThan(0));
    expect(repositories.parcels.unremitted, isNotEmpty);

    await repositories.parcels.remitCollected(
      method: 'gcash',
      reference: 'GCASH-REMIT-001',
    );
    await repositories.remittances.refresh();

    expect(repositories.parcels.codOnHand, 0);
    expect(repositories.parcels.unremitted, isEmpty);
    expect(repositories.parcels.outboxCount, 1);
    final remittance = repositories.remittances.remittances
        .firstWhere((r) => r.reference == 'GCASH-REMIT-001');
    expect(remittance.method, 'gcash');
    expect(remittance.reference, 'GCASH-REMIT-001');
    expect(remittance.status, 'confirmed');
    expect(remittance.confirmedAt, isNotNull);
    expect(remittance.amount, closeTo(before, 0.001));
  });

  test('status changes write a log and enqueue an outbox entry', () async {
    expect(
      await repositories.parcels.transition(
        'TRK-0012390',
        ParcelStatus.pickedUp,
      ),
      isTrue,
    );
    expect(
      await repositories.parcels.transition(
        'TRK-0012390',
        ParcelStatus.atHub,
      ),
      isTrue,
    );
    final changed = await repositories.parcels.transition(
      'TRK-0012390',
      ParcelStatus.outForDelivery,
    );
    expect(changed, isTrue);
    expect(repositories.parcels.outboxCount, 3);
    expect(
      repositories.parcels.statusLogs
          .any((log) => log.toStatus == ParcelStatus.outForDelivery.value),
      isTrue,
    );
  });

  test('delivery completion requires COD exact to the cent', () async {
    for (final amount in [499.99, 500.01]) {
      expect(
        await repositories.parcels.completeDelivery(
          'TRK-0012345',
          photoPath: 'proof.jpg',
          otpVerified: true,
          codCollected: amount,
        ),
        isFalse,
      );
    }
    expect(
      await repositories.parcels.completeDelivery(
        'TRK-0012345',
        photoPath: 'proof.jpg',
        otpVerified: true,
        codCollected: 500,
      ),
      isTrue,
    );
  });

  test('refused and third failed deliveries return to sender', () async {
    expect(
      await repositories.parcels.transition(
        'TRK-0012345',
        ParcelStatus.failedDelivery,
        reason: 'Refused',
      ),
      isTrue,
    );
    expect(
      repositories.parcels.byTracking('TRK-0012345')!.status,
      ParcelStatus.returnToSender,
    );

    final tracking = 'TRK-0012301';
    expect(
      await repositories.parcels.transition(
        tracking,
        ParcelStatus.atHub,
      ),
      isTrue,
    );
    for (var attempt = 0; attempt < 2; attempt++) {
      expect(
        await repositories.parcels.transition(
          tracking,
          ParcelStatus.outForDelivery,
        ),
        isTrue,
      );
      final failed = await repositories.parcels.transition(
        tracking,
        ParcelStatus.failedDelivery,
        reason: 'Buyer not home',
      );
      expect(failed, isTrue);
      if (attempt == 0) {
        expect(
          await repositories.parcels.transition(
            tracking,
            ParcelStatus.atHub,
          ),
          isTrue,
        );
      }
    }
    expect(
      repositories.parcels.byTracking(tracking)!.status,
      ParcelStatus.returnToSender,
    );
  });
}
