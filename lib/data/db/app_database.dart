import 'dart:convert';

import 'package:crypto/crypto.dart';
import 'package:flutter/foundation.dart';
import 'package:path/path.dart' as p;
import 'package:sqflite/sqflite.dart';

import '../models/entities.dart';

/// One-way SHA-256 password hash (no salt needed for a local demo account;
/// upgrade to bcrypt if deploying to production).
String hashPassword(String password) =>
    sha256.convert(utf8.encode(password)).toString();

class AppDatabase {
  AppDatabase({DatabaseFactory? factory, this.databasePath})
      : _factory = factory ?? databaseFactory;

  static const name = 'mimoo_rider.db';
  // Bumped to 2 to add rider_accounts, issue_reports, and email column.
  static const version = 2;
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
    final dbPath = databasePath ??
        (kIsWeb ? name : p.join(await getDatabasesPath(), name));
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
    if (oldVersion < 2 && newVersion >= 2) {
      await _upgradeToVersionTwo(db);
    }
  }

  Future<void> _createVersionOne(DatabaseExecutor db) async {
    final batch = db.batch();
    batch.execute('''
      CREATE TABLE rider_profile (
        id INTEGER PRIMARY KEY,
        user_id TEXT NOT NULL UNIQUE,
        name TEXT NOT NULL,
        email TEXT,
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
      CREATE TABLE rider_accounts (
        id INTEGER PRIMARY KEY,
        email TEXT NOT NULL UNIQUE,
        password_hash TEXT NOT NULL,
        rider_user_id TEXT NOT NULL REFERENCES rider_profile(user_id) ON DELETE CASCADE
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
        seller_phone TEXT,
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
        state TEXT NOT NULL CHECK (state IN ('pending', 'accepted', 'declined', 'expired')),
        decline_count INTEGER NOT NULL DEFAULT 0
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
      CREATE TABLE issue_reports (
        id INTEGER PRIMARY KEY,
        category TEXT NOT NULL,
        description TEXT NOT NULL,
        screenshot_path TEXT,
        created_at TEXT NOT NULL
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

  /// Upgrade from version 1 → 2: add missing tables/columns to existing DBs.
  Future<void> _upgradeToVersionTwo(DatabaseExecutor db) async {
    // Add email column if migrating from old v1 (no email field).
    try {
      await db.execute('ALTER TABLE rider_profile ADD COLUMN email TEXT');
    } catch (_) {/* column may already exist in freshly created v1 DBs */}

    // Create tables that didn't exist in v1.
    await db.execute('''
      CREATE TABLE IF NOT EXISTS rider_accounts (
        id INTEGER PRIMARY KEY,
        email TEXT NOT NULL UNIQUE,
        password_hash TEXT NOT NULL,
        rider_user_id TEXT NOT NULL
      )
    ''');
    await db.execute('''
      CREATE TABLE IF NOT EXISTS issue_reports (
        id INTEGER PRIMARY KEY,
        category TEXT NOT NULL,
        description TEXT NOT NULL,
        screenshot_path TEXT,
        created_at TEXT NOT NULL
      )
    ''');
    // Add decline_count to job_offers if it doesn't exist.
    try {
      await db
          .execute('ALTER TABLE job_offers ADD COLUMN decline_count INTEGER NOT NULL DEFAULT 0');
    } catch (_) {}
  }

  Future<void> seedIfNeeded() async {
    await database.transaction((txn) async {
      final seeded = await txn.query('app_meta',
          columns: ['value'], where: 'key = ?', whereArgs: ['seeded']);
      if (seeded.isNotEmpty && seeded.first['value'] == '1') return;

      final now = DateTime.now();

      // --- Rider profile ---
      await txn.insert('rider_profile', {
        'user_id': 'RDR-2026-0842',
        'name': 'Pedro Jose Garcia',
        'email': 'rider@mimoo.ph',
        'phone': '+639171234567',
        'address': 'Poblacion I, Santa Cruz, Laguna',
        'vehicle_type': 'Motorcycle',
        'plate_no': '123ABC',
        'license_no': 'N01-23-456789',
        'license_expires_on': '2027-10-30',
        'or_cr_expires_on': '2027-10-30',
        'kyc_status': 'verified',
        'area_id': 'poblacion-i',
        'area_name': 'Poblacion I',
        'fleet_name': 'Bilis Hatid Express Inc.',
        'hub_name': 'Quezon City Hub',
        'is_active': 1,
      });

      // --- Rider account (demo password: "password") ---
      await txn.insert('rider_accounts', {
        'email': 'rider@mimoo.ph',
        'password_hash': hashPassword('password'),
        'rider_user_id': 'RDR-2026-0842',
      });

      // --- Parcels ---
      final parcels = _seedParcels(now);
      for (final parcel in parcels) {
        final map = parcel.toMap()..remove('id');
        final id = await txn.insert('parcels', map);
        final logStatuses = _statusesTo(parcel.status);
        String? previous;
        for (final status in logStatuses) {
          await txn.insert('parcel_status_logs', {
            'parcel_id': id,
            'from_status': previous,
            'to_status': status.value,
            'reason': status == ParcelStatus.failedDelivery
                ? parcel.failureReason
                : null,
            'created_at': parcel.updatedAt.toIso8601String(),
          });
          previous = status.value;
        }
      }

      // --- Active job offer ---
      await txn.insert('job_offers', {
        'parcel_id': 'TRK-0012450',
        'area_name': 'Poblacion I',
        'payout': 30.0,
        'cod_amount': 500.0,
        'km': 2.4,
        'expires_at': now.add(const Duration(seconds: 60)).toIso8601String(),
        'state': 'pending',
        'decline_count': 0,
      });

      // --- Notifications ---
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
        'created_at': now.subtract(const Duration(minutes: 22)).toIso8601String(),
      });
      await txn.insert('notifications', {
        'title': 'Seller marked parcels ready',
        'body': 'Santa Cruz Handicrafts has 2 parcels ready.',
        'kind': 'seller_ready',
        'created_at': now.subtract(const Duration(minutes: 8)).toIso8601String(),
      });
      await txn.insert('notifications', {
        'title': 'COD remittance confirmed',
        'body': 'Your ₱1,300.00 remittance was verified.',
        'kind': 'remittance',
        'created_at': now.subtract(const Duration(hours: 1)).toIso8601String(),
        'read_at': now.subtract(const Duration(hours: 1)).toIso8601String(),
      });

      // --- Conversations & messages ---
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
        'created_at': now.subtract(const Duration(minutes: 5)).toIso8601String(),
      });
      await txn.insert('messages', {
        'conversation_id': conversationIds[0],
        'sender': 'rider',
        'body': 'On my way — ETA 8 mins.',
        'created_at': now.subtract(const Duration(minutes: 3)).toIso8601String(),
      });
      await txn.insert('messages', {
        'conversation_id': conversationIds[1],
        'sender': 'hub',
        'body': 'Please return uncollected parcels before 7:00 PM.',
        'created_at': now.subtract(const Duration(minutes: 15)).toIso8601String(),
      });
      await txn.insert('messages', {
        'conversation_id': conversationIds[2],
        'sender': 'hub',
        'body': 'The parcels are sealed and ready on the counter.',
        'created_at': now.subtract(const Duration(minutes: 35)).toIso8601String(),
      });

      // --- Emergency contacts ---
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

      // --- COD remittance history ---
      await txn.insert('cod_remittances', {
        'amount': 1300.0,
        'collected': 1300.0,
        'difference': 0.0,
        'status': 'confirmed',
        'reference': 'GCASH-20261031-001',
        'method': 'gcash',
        'created_at': now.subtract(const Duration(days: 1)).toIso8601String(),
        'confirmed_at': now.subtract(const Duration(hours: 1)).toIso8601String(),
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
        'issue_reports',
        'cod_remittances',
        'job_offers',
        'parcel_status_logs',
        'parcels',
        'emergency_contacts',
        'rider_accounts',
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
        pickedUpAt: type == ParcelJobType.pickup ||
                status != ParcelStatus.assigned
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
        item: item,
        weightKg: weight,
      );
    }

    final result = <Parcel>[
      // Active pickup offer (also has job_offer row)
      parcel('TRK-0012450', ParcelJobType.pickup, ParcelStatus.readyForPickup,
          area: 'Poblacion I', seller: 'Santa Cruz Handicrafts'),
      // Active assigned pickup
      parcel('TRK-0012388', ParcelJobType.pickup, ParcelStatus.pickedUp,
          area: 'Palasan', pickupFee: 30, seller: 'Santa Cruz Handicrafts'),
      // Active delivery
      parcel('TRK-0012345', ParcelJobType.delivery,
          ParcelStatus.outForDelivery,
          cod: 500, area: 'Poblacion I', item: 'Handwoven table runner'),
      parcel('TRK-0012390', ParcelJobType.delivery, ParcelStatus.assigned,
          cod: 650, area: 'Bubukal', buyer: 'Liza Ramos'),
      // Failed delivery
      parcel('TRK-0012301', ParcelJobType.delivery,
          ParcelStatus.failedDelivery,
          cod: 650,
          area: 'Gatid',
          failure: 'Buyer not home',
          buyer: 'Nena Cruz'),
      // Delivered today
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
      // History (past days)
      parcel('TRK-0011982', ParcelJobType.delivery, ParcelStatus.returned,
          area: 'Magsaysay', daysAgo: 3, failure: 'Refused by buyer'),
      parcel('TRK-0011850', ParcelJobType.delivery, ParcelStatus.delivered,
          cod: 300,
          collected: true,
          deliveryFee: 50,
          area: 'Nanhaya',
          daysAgo: 1,
          buyer: 'Rosa Villanueva'),
      parcel('TRK-0011851', ParcelJobType.pickup, ParcelStatus.returned,
          area: 'Bagong Pook', daysAgo: 1, pickupFee: 30),
      parcel('TRK-0011720', ParcelJobType.delivery, ParcelStatus.delivered,
          cod: 450,
          collected: true,
          deliveryFee: 50,
          area: 'Lumban',
          daysAgo: 2,
          buyer: 'Nico Santos'),
      // Extra "today" entries: total today earnings = 3×50 + 2×30 = 210.
      // Named today parcels so far: TRK-0012322(50)+TRK-0012308(50)+TRK-0012388(30) = 130.
      // Need 80 more: one delivery (50) + one pickup that is returned
      // (contributes pickupFee via pickedUpAt but is NOT active).
      parcel('TRK-0012501', ParcelJobType.pickup, ParcelStatus.returned,
          area: 'Bubukal', pickupFee: 30, daysAgo: 0,
          seller: 'Bubukal Farms'),
      parcel('TRK-0012502', ParcelJobType.delivery, ParcelStatus.delivered,
          cod: 200, collected: true, deliveryFee: 50, area: 'Palasan',
          daysAgo: 0, buyer: 'Elena Cruz'),
    ];

    // Add bulk history entries for earnings/history coverage.
    // Carefully designed so:
    //   - active.length == 3 (no active-status parcels added here)
    //   - earningsSince(today) == 210  (no today entries added here)
    //   - earningsSince(7-days) == 1180 → past 6 days need 970 total.
    //     named past: TRK-0011850(50)+TRK-0011851(30)+TRK-0011720(50)=130
    //     bulk past: 12 deliveries×50 + 8 pickups×30 = 600+240 = 840
    //     130 + 840 = 970 ✓ → today(210) + 970 = 1180 ✓
    // Pickups use 'returned' status to avoid being counted as active.
    final areas = [
      'Poblacion I', 'Bubukal', 'Palasan', 'Gatid', 'Duhat',
      'Buenavista', 'Magsaysay', 'Nanhaya', 'Bagong Pook', 'Lumban',
    ];
    // 12 bulk deliveries, daysAgo cycles 1-6.
    for (var i = 0; i < 12; i++) {
      final area = areas[i % areas.length];
      final daysAgo = (i % 6) + 1;
      result.add(parcel(
        'SDLV-${(i + 1).toString().padLeft(4, '0')}',
        ParcelJobType.delivery,
        ParcelStatus.delivered,
        area: area,
        daysAgo: daysAgo,
        deliveryFee: 50,
        cod: i.isEven ? 0 : 200,
        collected: i.isOdd,
        buyer: 'Demo Buyer ${i + 1}',
      ));
    }
    // 8 bulk pickups (returned status = not active, but pickedUpAt contributes to earnings).
    for (var i = 0; i < 8; i++) {
      final area = areas[i % areas.length];
      final daysAgo = (i % 6) + 1;
      result.add(parcel(
        'SPKU-${(i + 1).toString().padLeft(4, '0')}',
        ParcelJobType.pickup,
        ParcelStatus.returned,
        area: area,
        daysAgo: daysAgo,
        pickupFee: 30,
        seller: 'Demo Seller ${i + 1}',
      ));
    }

    return result;
  }
}
