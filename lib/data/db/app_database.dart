import 'dart:convert';

import 'package:flutter/foundation.dart';
import 'package:path/path.dart' as p;
import 'package:sqflite/sqflite.dart';

import '../models/entities.dart';

class AppDatabase {
  AppDatabase({DatabaseFactory? factory, this.databasePath})
      : _factory = factory ?? databaseFactory;

  static const name = 'mimoo_rider.db';
  static const version = 1;
  final DatabaseFactory _factory;
  final String? databasePath;
  Database? _database;

  Database get database {
    final value = _database;
    if (value == null) {
      throw StateError('AppDatabase.open() must complete before use.');
    }
    return value;
  }

  Future<void> open() async {
    if (kIsWeb) {
      throw UnsupportedError(
          'Local database storage is supported on Android and iOS only.');
    }
    final dbPath = databasePath ?? p.join(await getDatabasesPath(), name);
    _database = await _factory.openDatabase(
      dbPath,
      options: OpenDatabaseOptions(
        version: version,
        onConfigure: (db) => db.execute('PRAGMA foreign_keys = ON'),
        onCreate: (db, _) => _migrate(db, 0, version),
        onUpgrade: _migrate,
      ),
    );
    await seedIfNeeded();
  }

  Future<void> close() async {
    await _database?.close();
    _database = null;
  }

  Future<void> _migrate(
      DatabaseExecutor db, int oldVersion, int newVersion) async {
    if (oldVersion < 1 && newVersion >= 1) {
      await _createVersionOne(db);
    }
  }

  Future<void> _createVersionOne(DatabaseExecutor db) async {
    final batch = db.batch();
    batch.execute('''
      CREATE TABLE rider_profile (
        id INTEGER PRIMARY KEY,
        user_id TEXT NOT NULL UNIQUE,
        name TEXT NOT NULL,
        phone TEXT NOT NULL,
        address TEXT NOT NULL,
        vehicle_type TEXT NOT NULL,
        plate_no TEXT NOT NULL,
        license_no TEXT NOT NULL,
        license_expires_on TEXT,
        or_cr_expires_on TEXT,
        kyc_status TEXT NOT NULL,
        area_id TEXT,
        area_name TEXT,
        fleet_name TEXT,
        hub_name TEXT,
        is_active INTEGER NOT NULL DEFAULT 1,
        checked_in_at TEXT,
        suspended_at TEXT,
        suspension_reason TEXT,
        photo_path TEXT
      )
    ''');
    batch.execute('''
      CREATE TABLE parcels (
        id INTEGER PRIMARY KEY,
        tracking_no TEXT NOT NULL UNIQUE,
        job_type TEXT NOT NULL CHECK (job_type IN ('pickup', 'delivery')),
        status TEXT NOT NULL CHECK (status IN (
          'ready_for_pickup', 'assigned', 'picked_up', 'at_hub',
          'out_for_delivery', 'delivered', 'failed_delivery',
          'return_to_sender', 'returned'
        )),
        seller_name TEXT NOT NULL DEFAULT '',
        seller_address TEXT NOT NULL DEFAULT '',
        buyer_name TEXT NOT NULL DEFAULT '',
        buyer_phone TEXT NOT NULL DEFAULT '',
        address TEXT NOT NULL DEFAULT '',
        area_id TEXT,
        area_name TEXT,
        cod_amount REAL NOT NULL DEFAULT 0,
        is_prepaid INTEGER NOT NULL DEFAULT 0,
        cod_collected INTEGER NOT NULL DEFAULT 0,
        cod_remitted INTEGER NOT NULL DEFAULT 0,
        delivery_attempts INTEGER NOT NULL DEFAULT 0,
        failure_reason TEXT,
        pickup_fee REAL NOT NULL DEFAULT 0,
        delivery_fee REAL NOT NULL DEFAULT 0,
        delivery_otp_verified INTEGER NOT NULL DEFAULT 0,
        pod_photo_path TEXT,
        assigned_at TEXT,
        picked_up_at TEXT,
        out_for_delivery_at TEXT,
        delivered_at TEXT,
        sla_due_at TEXT,
        updated_at TEXT NOT NULL,
        synced INTEGER NOT NULL DEFAULT 0,
        route_code TEXT,
        landmark TEXT,
        carrier TEXT,
        item TEXT,
        weight_kg REAL,
        received_at TEXT,
        sorted_at TEXT
      )
    ''');
    batch.execute('''
      CREATE TABLE parcel_status_logs (
        id INTEGER PRIMARY KEY,
        parcel_id INTEGER NOT NULL REFERENCES parcels(id) ON DELETE CASCADE,
        from_status TEXT,
        to_status TEXT NOT NULL,
        reason TEXT,
        created_at TEXT NOT NULL
      )
    ''');
    batch.execute('''
      CREATE TABLE job_offers (
        id INTEGER PRIMARY KEY,
        parcel_id TEXT NOT NULL,
        area_name TEXT NOT NULL,
        payout REAL NOT NULL,
        cod_amount REAL NOT NULL,
        km REAL NOT NULL,
        expires_at TEXT NOT NULL,
        state TEXT NOT NULL CHECK (state IN ('pending', 'accepted', 'declined', 'expired'))
      )
    ''');
    batch.execute('''
      CREATE TABLE cod_remittances (
        id INTEGER PRIMARY KEY,
        amount REAL NOT NULL,
        collected REAL NOT NULL,
        difference REAL NOT NULL,
        status TEXT NOT NULL CHECK (status IN ('pending', 'confirmed')),
        reference TEXT NOT NULL,
        method TEXT NOT NULL,
        created_at TEXT NOT NULL,
        confirmed_at TEXT
      )
    ''');
    batch.execute('''
      CREATE TABLE notifications (
        id INTEGER PRIMARY KEY,
        title TEXT NOT NULL,
        body TEXT NOT NULL,
        kind TEXT NOT NULL,
        parcel_id TEXT,
        read_at TEXT,
        created_at TEXT NOT NULL
      )
    ''');
    batch.execute('''
      CREATE TABLE conversations (
        id INTEGER PRIMARY KEY,
        title TEXT NOT NULL,
        last_message_at TEXT
      )
    ''');
    batch.execute('''
      CREATE TABLE messages (
        id INTEGER PRIMARY KEY,
        conversation_id INTEGER REFERENCES conversations(id) ON DELETE CASCADE,
        sender TEXT NOT NULL CHECK (sender IN ('rider', 'hub')),
        body TEXT NOT NULL,
        created_at TEXT NOT NULL,
        read_at TEXT
      )
    ''');
    batch.execute('''
      CREATE TABLE emergency_contacts (
        id INTEGER PRIMARY KEY,
        name TEXT NOT NULL,
        relationship TEXT NOT NULL,
        phone TEXT NOT NULL
      )
    ''');
    batch.execute('''
      CREATE TABLE outbox (
        id INTEGER PRIMARY KEY,
        entity TEXT NOT NULL,
        entity_id TEXT NOT NULL,
        action TEXT NOT NULL,
        payload_json TEXT NOT NULL,
        created_at TEXT NOT NULL,
        attempts INTEGER NOT NULL DEFAULT 0,
        last_error TEXT
      )
    ''');
    batch.execute(
        'CREATE TABLE app_meta (key TEXT PRIMARY KEY, value TEXT NOT NULL)');
    batch.execute('CREATE INDEX idx_parcels_status ON parcels(status)');
    batch.execute(
        'CREATE INDEX idx_parcel_logs_parcel ON parcel_status_logs(parcel_id, created_at)');
    batch.execute('CREATE INDEX idx_outbox_created ON outbox(created_at)');
    await batch.commit(noResult: true);
  }

  Future<void> seedIfNeeded() async {
    await database.transaction((txn) async {
      final seeded = await txn.query('app_meta',
          columns: ['value'], where: 'key = ?', whereArgs: ['seeded']);
      if (seeded.isNotEmpty && seeded.first['value'] == '1') return;

      final now = DateTime.now();
      await txn.insert(
          'rider_profile',
          RiderProfile(
            userId: 'RDR-2026-0842',
            name: 'Pedro Jose Garcia',
            phone: '+639171234567',
            address: 'Poblacion I, Santa Cruz, Laguna',
            vehicleType: 'Motorcycle',
            plateNo: '123ABC',
            licenseNo: 'N01-23-456789',
            licenseExpiresOn: '2027-10-30',
            orCrExpiresOn: '2027-10-30',
            kycStatus: 'verified',
            areaId: 'poblacion-i',
            areaName: 'Poblacion I',
            fleetName: 'Bilis Hatid Express Inc.',
            hubName: 'Santa Cruz, Laguna Hub',
          ).toMap());

      final parcels = _seedParcels(now);
      for (final parcel in parcels) {
        final id = await txn.insert('parcels', parcel.toMap());
        final logStatuses = _statusesTo(parcel.status);
        String? previous;
        for (final status in logStatuses) {
          await txn.insert(
            'parcel_status_logs',
            ParcelStatusLog(
              parcelId: id,
              fromStatus: previous,
              toStatus: status.value,
              reason: status == ParcelStatus.failedDelivery
                  ? parcel.failureReason
                  : null,
              createdAt: parcel.updatedAt,
            ).toMap(),
          );
          previous = status.value;
        }
      }

      await txn.insert('job_offers', {
        'parcel_id': 'TRK-0012450',
        'area_name': 'Poblacion I',
        'payout': 30.0,
        'cod_amount': 500.0,
        'km': 2.4,
        'expires_at': now.add(const Duration(seconds: 60)).toIso8601String(),
        'state': 'pending',
      });
      await txn.insert('notifications', {
        'title': 'New job offer near you',
        'body': 'Poblacion I · ₱30 pickup pay · Expires in 60s',
        'kind': 'job_offer',
        'parcel_id': 'TRK-0012450',
        'created_at': now.toIso8601String(),
      });
      await txn.insert('notifications', {
        'title': 'Parcel assigned to you',
        'body': 'TRK-0012390 is at Santa Cruz Hub.',
        'kind': 'parcel_assigned',
        'parcel_id': 'TRK-0012390',
        'created_at':
            now.subtract(const Duration(minutes: 22)).toIso8601String(),
      });
      await txn.insert('notifications', {
        'title': 'Seller marked parcels ready',
        'body': 'Santa Cruz Handicrafts has 2 parcels ready.',
        'kind': 'seller_ready',
        'created_at':
            now.subtract(const Duration(minutes: 8)).toIso8601String(),
      });
      final conversationIds = <int>[];
      for (final title in [
        'Juan Miguel D.',
        'Santa Cruz Hub Dispatch',
        'Santa Cruz Handicrafts',
      ]) {
        conversationIds.add(await txn.insert('conversations', {
          'title': title,
          'last_message_at': now.toIso8601String(),
        }));
      }
      await txn.insert('messages', {
        'conversation_id': conversationIds[0],
        'sender': 'hub',
        'body': "Yes po, I'm waiting at the blue gate now.",
        'created_at':
            now.subtract(const Duration(minutes: 5)).toIso8601String(),
      });
      await txn.insert('messages', {
        'conversation_id': conversationIds[1],
        'sender': 'hub',
        'body': 'Please return uncollected parcels before 7:00 PM.',
        'created_at':
            now.subtract(const Duration(minutes: 15)).toIso8601String(),
      });
      await txn.insert('messages', {
        'conversation_id': conversationIds[2],
        'sender': 'hub',
        'body': 'The parcels are sealed and ready on the counter.',
        'created_at':
            now.subtract(const Duration(minutes: 35)).toIso8601String(),
      });
      await txn.insert('emergency_contacts', {
        'name': 'Ana Dela Cruz',
        'relationship': 'Coordinator',
        'phone': '+639181234567',
      });
      await txn.insert('emergency_contacts', {
        'name': 'Maria Garcia',
        'relationship': 'Family',
        'phone': '+639191234567',
      });
      await txn.insert('app_meta', {'key': 'seeded', 'value': '1'});
    });
  }

  Future<void> resetLocalData() async {
    await database.transaction((txn) async {
      for (final table in [
        'messages',
        'conversations',
        'notifications',
        'cod_remittances',
        'job_offers',
        'parcel_status_logs',
        'parcels',
        'emergency_contacts',
        'rider_profile',
        'outbox',
      ]) {
        await txn.delete(table);
      }
      await txn.delete('app_meta', where: 'key = ?', whereArgs: ['seeded']);
    });
    await seedIfNeeded();
  }

  Future<void> enqueue(
    DatabaseExecutor db, {
    required String entity,
    required String entityId,
    required String action,
    required Map<String, Object?> payload,
  }) async {
    await db.insert('outbox', {
      'entity': entity,
      'entity_id': entityId,
      'action': action,
      'payload_json': jsonEncode(payload),
      'created_at': DateTime.now().toIso8601String(),
      'attempts': 0,
    });
  }

  List<ParcelStatus> _statusesTo(ParcelStatus target) => switch (target) {
        ParcelStatus.readyForPickup => [ParcelStatus.readyForPickup],
        ParcelStatus.assigned => [
            ParcelStatus.readyForPickup,
            ParcelStatus.assigned
          ],
        ParcelStatus.pickedUp => [
            ParcelStatus.readyForPickup,
            ParcelStatus.assigned,
            ParcelStatus.pickedUp
          ],
        ParcelStatus.atHub => [
            ParcelStatus.readyForPickup,
            ParcelStatus.assigned,
            ParcelStatus.pickedUp,
            ParcelStatus.atHub
          ],
        ParcelStatus.outForDelivery => [
            ParcelStatus.readyForPickup,
            ParcelStatus.assigned,
            ParcelStatus.pickedUp,
            ParcelStatus.atHub,
            ParcelStatus.outForDelivery
          ],
        ParcelStatus.delivered => [
            ParcelStatus.readyForPickup,
            ParcelStatus.assigned,
            ParcelStatus.pickedUp,
            ParcelStatus.atHub,
            ParcelStatus.outForDelivery,
            ParcelStatus.delivered
          ],
        ParcelStatus.failedDelivery => [
            ParcelStatus.readyForPickup,
            ParcelStatus.assigned,
            ParcelStatus.pickedUp,
            ParcelStatus.atHub,
            ParcelStatus.outForDelivery,
            ParcelStatus.failedDelivery
          ],
        ParcelStatus.returnToSender => [
            ParcelStatus.readyForPickup,
            ParcelStatus.assigned,
            ParcelStatus.pickedUp,
            ParcelStatus.atHub,
            ParcelStatus.outForDelivery,
            ParcelStatus.returnToSender
          ],
        ParcelStatus.returned => [
            ParcelStatus.readyForPickup,
            ParcelStatus.assigned,
            ParcelStatus.pickedUp,
            ParcelStatus.atHub,
            ParcelStatus.outForDelivery,
            ParcelStatus.returnToSender,
            ParcelStatus.returned
          ],
      };

  List<Parcel> _seedParcels(DateTime now) {
    Parcel parcel(String tracking, ParcelJobType type, ParcelStatus status,
        {String area = 'Poblacion I',
        double cod = 0,
        int daysAgo = 0,
        double pickupFee = 0,
        double deliveryFee = 0,
        String? failure,
        bool collected = false,
        String seller = 'Santa Cruz Local Goods',
        String buyer = 'Juan Miguel D.',
        String? route,
        String? item,
        String? carrier,
        double? weight,
        String address = 'Poblacion I, Santa Cruz, Laguna'}) {
      final stamp = now.subtract(Duration(days: daysAgo, hours: 2));
      final isPrepaid = cod == 0;
      return Parcel(
        trackingNo: tracking,
        jobType: type,
        status: status,
        sellerName: seller,
        sellerAddress: 'Santa Cruz Public Market, $area',
        buyerName: buyer,
        buyerPhone: '+639181234567',
        address: address,
        areaId: area.toLowerCase().replaceAll(' ', '-'),
        areaName: area,
        codAmount: cod,
        isPrepaid: isPrepaid,
        codCollected: collected,
        deliveryAttempts: status == ParcelStatus.failedDelivery ? 1 : 0,
        failureReason: failure,
        pickupFee: pickupFee,
        deliveryFee: deliveryFee,
        deliveryOtpVerified: status == ParcelStatus.delivered,
        podPhotoPath:
            status == ParcelStatus.delivered ? 'local/demo-pod.jpg' : null,
        assignedAt: stamp,
        pickedUpAt:
            type == ParcelJobType.pickup || status != ParcelStatus.assigned
                ? stamp.add(const Duration(minutes: 20))
                : null,
        outForDeliveryAt: status == ParcelStatus.outForDelivery ||
                status == ParcelStatus.delivered ||
                status == ParcelStatus.failedDelivery ||
                status == ParcelStatus.returnToSender ||
                status == ParcelStatus.returned
            ? stamp.add(const Duration(minutes: 30))
            : null,
        deliveredAt: status == ParcelStatus.delivered
            ? stamp.add(const Duration(hours: 2))
            : null,
        updatedAt: status == ParcelStatus.delivered
            ? stamp.add(const Duration(hours: 2))
            : stamp,
        routeCode: route ?? 'SL-${area.substring(0, 1).toUpperCase()}01',
        landmark: 'Near Santa Cruz Public Market',
        carrier: carrier ?? 'jnt',
        item: item ?? 'Local goods parcel',
        weightKg: weight ?? 1.5,
        receivedAt: stamp.subtract(const Duration(hours: 2)),
        sortedAt: stamp.subtract(const Duration(hours: 1)),
      );
    }

    final result = <Parcel>[
      parcel('TRK-0012345', ParcelJobType.delivery, ParcelStatus.outForDelivery,
          cod: 500, area: 'Poblacion I', item: 'Handwoven table runner'),
      parcel('TRK-0012450', ParcelJobType.pickup, ParcelStatus.readyForPickup,
          area: 'Poblacion I', seller: 'Santa Cruz Handicrafts'),
      parcel('TRK-0012390', ParcelJobType.delivery, ParcelStatus.assigned,
          cod: 650, area: 'Bubukal', buyer: 'Liza Ramos'),
      parcel('TRK-0012388', ParcelJobType.pickup, ParcelStatus.pickedUp,
          area: 'Palasan', pickupFee: 30, seller: 'Santa Cruz Handicrafts'),
      parcel('TRK-0012301', ParcelJobType.delivery, ParcelStatus.failedDelivery,
          cod: 650,
          area: 'Gatid',
          failure: 'Buyer not home',
          buyer: 'Nena Cruz'),
      parcel('TRK-0012322', ParcelJobType.delivery, ParcelStatus.delivered,
          cod: 650,
          collected: true,
          deliveryFee: 50,
          area: 'Duhat',
          buyer: 'Mario Santos'),
      parcel('TRK-0012308', ParcelJobType.delivery, ParcelStatus.delivered,
          cod: 650,
          collected: true,
          deliveryFee: 50,
          area: 'Buenavista',
          buyer: 'Carla Reyes'),
      parcel('TRK-0011982', ParcelJobType.delivery, ParcelStatus.returned,
          area: 'Magsaysay', daysAgo: 3, failure: 'Refused by buyer'),
    ];

    final areas = [
      'Nanhaya',
      'Bagong Pook',
      'Lumban',
      'Poblacion II',
      'Poblacion I',
      'Bubukal',
      'Palasan',
      'Gatid',
      'Duhat',
      'Buenavista',
      'Magsaysay',
      'Nanhaya',
      'Bagong Pook',
      'Lumban',
      'Poblacion II',
      'Poblacion I',
      'Bubukal',
      'Palasan',
      'Gatid',
      'Duhat',
      'Buenavista',
      'Magsaysay',
      'Nanhaya',
      'Bagong Pook',
      'Lumban',
      'Poblacion II',
    ];
    for (var i = 0; i < 23; i++) {
      final isPickup = i == 0 || i == 2 || i == 7 || i == 12 || i == 17;
      final isToday = i < 2;
      final status = isPickup ? ParcelStatus.atHub : ParcelStatus.delivered;
      final codAmount = isPickup || i.isEven ? 0.0 : 250.0;
      result.add(
        parcel(
          'SEED-${(i + 1).toString().padLeft(4, '0')}',
          isPickup ? ParcelJobType.pickup : ParcelJobType.delivery,
          status,
          area: areas[i],
          daysAgo: isToday ? 0 : (i % 6) + 1,
          pickupFee: isPickup ? 30 : 0,
          deliveryFee: isPickup ? 0 : 50,
          cod: codAmount,
          collected: !isPickup && codAmount > 0,
          buyer: 'Rider demo customer ${i + 1}',
        ),
      );
    }
    return result;
  }
}
