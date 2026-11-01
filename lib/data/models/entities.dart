typedef DbMap = Map<String, Object?>;

DateTime? _date(Object? value) =>
    value == null ? null : DateTime.parse(value as String);

String? _iso(DateTime? value) => value?.toIso8601String();

enum ParcelStatus {
  readyForPickup('ready_for_pickup'),
  assigned('assigned'),
  pickedUp('picked_up'),
  atHub('at_hub'),
  outForDelivery('out_for_delivery'),
  delivered('delivered'),
  failedDelivery('failed_delivery'),
  returnToSender('return_to_sender'),
  returned('returned');

  const ParcelStatus(this.value);
  final String value;

  static ParcelStatus fromValue(String value) =>
      ParcelStatus.values.firstWhere((status) => status.value == value,
          orElse: () => throw FormatException('Unknown parcel status: $value'));
}

bool canTransition(ParcelStatus from, ParcelStatus to) => switch (from) {
      ParcelStatus.readyForPickup => to == ParcelStatus.assigned,
      ParcelStatus.assigned => to == ParcelStatus.pickedUp,
      ParcelStatus.pickedUp => to == ParcelStatus.atHub,
      ParcelStatus.atHub => to == ParcelStatus.outForDelivery,
      ParcelStatus.outForDelivery => to == ParcelStatus.delivered ||
          to == ParcelStatus.failedDelivery ||
          to == ParcelStatus.returnToSender ||
          to == ParcelStatus.atHub,
      ParcelStatus.failedDelivery =>
        to == ParcelStatus.atHub || to == ParcelStatus.returnToSender,
      ParcelStatus.returnToSender => to == ParcelStatus.returned,
      ParcelStatus.delivered || ParcelStatus.returned => false,
    };

enum ParcelJobType { pickup, delivery }

extension ParcelStatusLabel on ParcelStatus {
  String get label => switch (this) {
        ParcelStatus.readyForPickup => 'Ready for Pickup',
        ParcelStatus.assigned || ParcelStatus.atHub => 'At Hub',
        ParcelStatus.pickedUp => 'Picked Up',
        ParcelStatus.outForDelivery => 'Out for Delivery',
        ParcelStatus.delivered => 'Delivered',
        ParcelStatus.failedDelivery => 'Overdue',
        ParcelStatus.returnToSender => 'Return to Sender',
        ParcelStatus.returned => 'Returned',
      };
}

class CustodyEvent {
  final String title, body;
  final DateTime at;
  const CustodyEvent(this.title, this.body, this.at);
}

class RiderProfile {
  final int? id;
  final String userId, name, phone, address, vehicleType, plateNo, licenseNo;
  final String? licenseExpiresOn, orCrExpiresOn, areaId, areaName, fleetName;
  final String? hubName, suspensionReason, photoPath;
  final String kycStatus;
  final bool isActive;
  final DateTime? checkedInAt, suspendedAt;

  const RiderProfile({
    this.id,
    required this.userId,
    required this.name,
    required this.phone,
    required this.address,
    required this.vehicleType,
    required this.plateNo,
    required this.licenseNo,
    this.licenseExpiresOn,
    this.orCrExpiresOn,
    required this.kycStatus,
    this.areaId,
    this.areaName,
    this.fleetName,
    this.hubName,
    this.isActive = true,
    this.checkedInAt,
    this.suspendedAt,
    this.suspensionReason,
    this.photoPath,
  });

  factory RiderProfile.fromMap(DbMap map) => RiderProfile(
        id: map['id'] as int?,
        userId: map['user_id'] as String,
        name: map['name'] as String,
        phone: map['phone'] as String,
        address: map['address'] as String,
        vehicleType: map['vehicle_type'] as String,
        plateNo: map['plate_no'] as String,
        licenseNo: map['license_no'] as String,
        licenseExpiresOn: map['license_expires_on'] as String?,
        orCrExpiresOn: map['or_cr_expires_on'] as String?,
        kycStatus: map['kyc_status'] as String,
        areaId: map['area_id'] as String?,
        areaName: map['area_name'] as String?,
        fleetName: map['fleet_name'] as String?,
        hubName: map['hub_name'] as String?,
        isActive: map['is_active'] == 1,
        checkedInAt: _date(map['checked_in_at']),
        suspendedAt: _date(map['suspended_at']),
        suspensionReason: map['suspension_reason'] as String?,
        photoPath: map['photo_path'] as String?,
      );

  DbMap toMap() => {
        'id': id,
        'user_id': userId,
        'name': name,
        'phone': phone,
        'address': address,
        'vehicle_type': vehicleType,
        'plate_no': plateNo,
        'license_no': licenseNo,
        'license_expires_on': licenseExpiresOn,
        'or_cr_expires_on': orCrExpiresOn,
        'kyc_status': kycStatus,
        'area_id': areaId,
        'area_name': areaName,
        'fleet_name': fleetName,
        'hub_name': hubName,
        'is_active': isActive ? 1 : 0,
        'checked_in_at': _iso(checkedInAt),
        'suspended_at': _iso(suspendedAt),
        'suspension_reason': suspensionReason,
        'photo_path': photoPath,
      };
}

class Parcel {
  final int? id;
  final String trackingNo;
  final ParcelJobType jobType;
  final ParcelStatus status;
  final String sellerName, sellerAddress, buyerName, buyerPhone, address;
  final String? areaId, areaName, failureReason, podPhotoPath;
  final double codAmount, pickupFee, deliveryFee;
  final bool isPrepaid, codCollected, codRemitted, deliveryOtpVerified, synced;
  final int deliveryAttempts;
  final DateTime? assignedAt, pickedUpAt, outForDeliveryAt, deliveredAt;
  final DateTime? slaDueAt;
  final DateTime updatedAt;
  final String? routeCode, landmark, carrier, item;
  final double? weightKg;
  final DateTime? receivedAt, sortedAt;

  const Parcel({
    this.id,
    required this.trackingNo,
    required this.jobType,
    required this.status,
    required this.sellerName,
    required this.sellerAddress,
    required this.buyerName,
    required this.buyerPhone,
    required this.address,
    this.areaId,
    this.areaName,
    this.codAmount = 0,
    this.isPrepaid = false,
    this.codCollected = false,
    this.codRemitted = false,
    this.deliveryAttempts = 0,
    this.failureReason,
    this.pickupFee = 0,
    this.deliveryFee = 0,
    this.deliveryOtpVerified = false,
    this.podPhotoPath,
    this.assignedAt,
    this.pickedUpAt,
    this.outForDeliveryAt,
    this.deliveredAt,
    this.slaDueAt,
    required this.updatedAt,
    this.synced = false,
    this.routeCode,
    this.landmark,
    this.carrier,
    this.item,
    this.weightKg,
    this.receivedAt,
    this.sortedAt,
  });

  factory Parcel.fromMap(DbMap map) => Parcel(
        id: map['id'] as int?,
        trackingNo: map['tracking_no'] as String,
        jobType: ParcelJobType.values.byName(map['job_type'] as String),
        status: ParcelStatus.fromValue(map['status'] as String),
        sellerName: map['seller_name'] as String,
        sellerAddress: map['seller_address'] as String,
        buyerName: map['buyer_name'] as String,
        buyerPhone: map['buyer_phone'] as String,
        address: map['address'] as String,
        areaId: map['area_id'] as String?,
        areaName: map['area_name'] as String?,
        codAmount: (map['cod_amount'] as num).toDouble(),
        isPrepaid: map['is_prepaid'] == 1,
        codCollected: map['cod_collected'] == 1,
        codRemitted: map['cod_remitted'] == 1,
        deliveryAttempts: map['delivery_attempts'] as int,
        failureReason: map['failure_reason'] as String?,
        pickupFee: (map['pickup_fee'] as num).toDouble(),
        deliveryFee: (map['delivery_fee'] as num).toDouble(),
        deliveryOtpVerified: map['delivery_otp_verified'] == 1,
        podPhotoPath: map['pod_photo_path'] as String?,
        assignedAt: _date(map['assigned_at']),
        pickedUpAt: _date(map['picked_up_at']),
        outForDeliveryAt: _date(map['out_for_delivery_at']),
        deliveredAt: _date(map['delivered_at']),
        slaDueAt: _date(map['sla_due_at']),
        updatedAt: _date(map['updated_at'])!,
        synced: map['synced'] == 1,
        routeCode: map['route_code'] as String?,
        landmark: map['landmark'] as String?,
        carrier: map['carrier'] as String?,
        item: map['item'] as String?,
        weightKg: (map['weight_kg'] as num?)?.toDouble(),
        receivedAt: _date(map['received_at']),
        sortedAt: _date(map['sorted_at']),
      );

  DbMap toMap() => {
        'id': id,
        'tracking_no': trackingNo,
        'job_type': jobType.name,
        'status': status.value,
        'seller_name': sellerName,
        'seller_address': sellerAddress,
        'buyer_name': buyerName,
        'buyer_phone': buyerPhone,
        'address': address,
        'area_id': areaId,
        'area_name': areaName,
        'cod_amount': codAmount,
        'is_prepaid': isPrepaid ? 1 : 0,
        'cod_collected': codCollected ? 1 : 0,
        'cod_remitted': codRemitted ? 1 : 0,
        'delivery_attempts': deliveryAttempts,
        'failure_reason': failureReason,
        'pickup_fee': pickupFee,
        'delivery_fee': deliveryFee,
        'delivery_otp_verified': deliveryOtpVerified ? 1 : 0,
        'pod_photo_path': podPhotoPath,
        'assigned_at': _iso(assignedAt),
        'picked_up_at': _iso(pickedUpAt),
        'out_for_delivery_at': _iso(outForDeliveryAt),
        'delivered_at': _iso(deliveredAt),
        'sla_due_at': _iso(slaDueAt),
        'updated_at': _iso(updatedAt),
        'synced': synced ? 1 : 0,
        'route_code': routeCode,
        'landmark': landmark,
        'carrier': carrier,
        'item': item,
        'weight_kg': weightKg,
        'received_at': _iso(receivedAt),
        'sorted_at': _iso(sortedAt),
      };

  String get tracking => trackingNo;
  String get buyer => buyerName;
  String get phone => buyerPhone;
  double get cod => codAmount;
  bool get isCod => !isPrepaid && codAmount > 0;
  String? get failReason => failureReason;
  int get lifecycleStep => switch (status) {
        ParcelStatus.readyForPickup => 0,
        ParcelStatus.assigned => 2,
        ParcelStatus.pickedUp || ParcelStatus.atHub => 2,
        ParcelStatus.outForDelivery ||
        ParcelStatus.failedDelivery ||
        ParcelStatus.returnToSender =>
          3,
        ParcelStatus.delivered || ParcelStatus.returned => 4,
      };
  String get carrierLabel => carrier ?? 'Carrier not specified';
  String get weightLabel =>
      weightKg == null ? 'Not weighed' : '${weightKg!.toStringAsFixed(2)} kg';
  List<CustodyEvent> get custody {
    final events = <CustodyEvent>[
      if (receivedAt != null)
        CustodyEvent('Inbound Scan', 'Received at the hub dock', receivedAt!),
      if (sortedAt != null)
        CustodyEvent(
            'Sorted', 'Allocated to ${areaName ?? 'your area'}', sortedAt!),
      if (assignedAt != null)
        CustodyEvent(
            status == ParcelStatus.assigned ? 'Assigned' : 'Dispatched',
            status == ParcelStatus.assigned
                ? 'Assigned to you at the hub'
                : 'Parcel dispatched to you',
            assignedAt!),
      if (deliveredAt != null)
        CustodyEvent('Delivered', 'Handed over to $buyerName', deliveredAt!),
    ];
    return events.reversed.toList(growable: false);
  }
}

class ParcelStatusLog {
  final int? id;
  final int parcelId;
  final String? fromStatus, reason;
  final String toStatus;
  final DateTime createdAt;

  const ParcelStatusLog({
    this.id,
    required this.parcelId,
    this.fromStatus,
    required this.toStatus,
    this.reason,
    required this.createdAt,
  });

  factory ParcelStatusLog.fromMap(DbMap map) => ParcelStatusLog(
        id: map['id'] as int?,
        parcelId: map['parcel_id'] as int,
        fromStatus: map['from_status'] as String?,
        toStatus: map['to_status'] as String,
        reason: map['reason'] as String?,
        createdAt: _date(map['created_at'])!,
      );

  DbMap toMap() => {
        'id': id,
        'parcel_id': parcelId,
        'from_status': fromStatus,
        'to_status': toStatus,
        'reason': reason,
        'created_at': _iso(createdAt),
      };
}

class JobOffer {
  final int? id;
  final String parcelId, areaName, state;
  final double payout, codAmount, km;
  final DateTime expiresAt;

  const JobOffer({
    this.id,
    required this.parcelId,
    required this.areaName,
    required this.payout,
    required this.codAmount,
    required this.km,
    required this.expiresAt,
    this.state = 'pending',
  });

  factory JobOffer.fromMap(DbMap map) => JobOffer(
        id: map['id'] as int?,
        parcelId: map['parcel_id'] as String,
        areaName: map['area_name'] as String,
        payout: (map['payout'] as num).toDouble(),
        codAmount: (map['cod_amount'] as num).toDouble(),
        km: (map['km'] as num).toDouble(),
        expiresAt: _date(map['expires_at'])!,
        state: map['state'] as String,
      );

  DbMap toMap() => {
        'id': id,
        'parcel_id': parcelId,
        'area_name': areaName,
        'payout': payout,
        'cod_amount': codAmount,
        'km': km,
        'expires_at': _iso(expiresAt),
        'state': state,
      };
}

class CodRemittance {
  final int? id;
  final double amount, collected, difference;
  final String status, reference, method;
  final DateTime createdAt;
  final DateTime? confirmedAt;

  const CodRemittance({
    this.id,
    required this.amount,
    required this.collected,
    required this.difference,
    required this.status,
    required this.reference,
    required this.method,
    required this.createdAt,
    this.confirmedAt,
  });

  factory CodRemittance.fromMap(DbMap map) => CodRemittance(
        id: map['id'] as int?,
        amount: (map['amount'] as num).toDouble(),
        collected: (map['collected'] as num).toDouble(),
        difference: (map['difference'] as num).toDouble(),
        status: map['status'] as String,
        reference: map['reference'] as String,
        method: map['method'] as String,
        createdAt: _date(map['created_at'])!,
        confirmedAt: _date(map['confirmed_at']),
      );

  DbMap toMap() => {
        'id': id,
        'amount': amount,
        'collected': collected,
        'difference': difference,
        'status': status,
        'reference': reference,
        'method': method,
        'created_at': _iso(createdAt),
        'confirmed_at': _iso(confirmedAt),
      };
}

class AppNotification {
  final int? id;
  final String title, body, kind;
  final String? parcelId;
  final DateTime? readAt;
  final DateTime createdAt;

  const AppNotification({
    this.id,
    required this.title,
    required this.body,
    required this.kind,
    this.parcelId,
    this.readAt,
    required this.createdAt,
  });

  factory AppNotification.fromMap(DbMap map) => AppNotification(
        id: map['id'] as int?,
        title: map['title'] as String,
        body: map['body'] as String,
        kind: map['kind'] as String,
        parcelId: map['parcel_id'] as String?,
        readAt: _date(map['read_at']),
        createdAt: _date(map['created_at'])!,
      );

  DbMap toMap() => {
        'id': id,
        'title': title,
        'body': body,
        'kind': kind,
        'parcel_id': parcelId,
        'read_at': _iso(readAt),
        'created_at': _iso(createdAt),
      };
}

class Conversation {
  final int? id;
  final String title;
  final DateTime? lastMessageAt;

  const Conversation({this.id, required this.title, this.lastMessageAt});

  factory Conversation.fromMap(DbMap map) => Conversation(
        id: map['id'] as int?,
        title: map['title'] as String,
        lastMessageAt: _date(map['last_message_at']),
      );

  DbMap toMap() =>
      {'id': id, 'title': title, 'last_message_at': _iso(lastMessageAt)};
}

class Message {
  final int? id, conversationId;
  final String sender, body;
  final DateTime createdAt;
  final DateTime? readAt;

  const Message({
    this.id,
    required this.conversationId,
    required this.sender,
    required this.body,
    required this.createdAt,
    this.readAt,
  });

  factory Message.fromMap(DbMap map) => Message(
        id: map['id'] as int?,
        conversationId: map['conversation_id'] as int?,
        sender: map['sender'] as String,
        body: map['body'] as String,
        createdAt: _date(map['created_at'])!,
        readAt: _date(map['read_at']),
      );

  DbMap toMap() => {
        'id': id,
        'conversation_id': conversationId,
        'sender': sender,
        'body': body,
        'created_at': _iso(createdAt),
        'read_at': _iso(readAt),
      };
}

class EmergencyContact {
  final int? id;
  final String name, relationship, phone;

  const EmergencyContact({
    this.id,
    required this.name,
    required this.relationship,
    required this.phone,
  });

  factory EmergencyContact.fromMap(DbMap map) => EmergencyContact(
        id: map['id'] as int?,
        name: map['name'] as String,
        relationship: map['relationship'] as String,
        phone: map['phone'] as String,
      );

  DbMap toMap() => {
        'id': id,
        'name': name,
        'relationship': relationship,
        'phone': phone,
      };
}

class OutboxEntry {
  final int? id;
  final String entity, entityId, action, payloadJson;
  final DateTime createdAt;
  final int attempts;
  final String? lastError;

  const OutboxEntry({
    this.id,
    required this.entity,
    required this.entityId,
    required this.action,
    required this.payloadJson,
    required this.createdAt,
    this.attempts = 0,
    this.lastError,
  });

  factory OutboxEntry.fromMap(DbMap map) => OutboxEntry(
        id: map['id'] as int?,
        entity: map['entity'] as String,
        entityId: map['entity_id'] as String,
        action: map['action'] as String,
        payloadJson: map['payload_json'] as String,
        createdAt: _date(map['created_at'])!,
        attempts: map['attempts'] as int,
        lastError: map['last_error'] as String?,
      );

  DbMap toMap() => {
        'id': id,
        'entity': entity,
        'entity_id': entityId,
        'action': action,
        'payload_json': payloadJson,
        'created_at': _iso(createdAt),
        'attempts': attempts,
        'last_error': lastError,
      };
}
