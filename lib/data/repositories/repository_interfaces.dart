import 'package:flutter/foundation.dart';

import '../models/entities.dart';

abstract interface class ParcelRepository implements Listenable {
  List<Parcel> get parcels;
  List<Parcel> get active;
  List<Parcel> get unremitted;
  List<ParcelStatusLog> get statusLogs;
  Parcel get focus;
  double get codOnHand;
  double get pendingRemittance;
  int get outboxCount;
  double earningsSince(DateTime from);
  int completedJobsSince(DateTime from);
  Parcel? byTracking(String tracking);
  Parcel? fromArgs(Object? argument);
  Parcel? get assignedPickup;
  Map<String, List<Parcel>> groupedByArea(Iterable<Parcel> parcels);
  Future<void> refresh();
  Future<bool> transition(
    String trackingNo,
    ParcelStatus to, {
    String? reason,
  });
  Future<bool> completeDelivery(
    String trackingNo, {
    required String photoPath,
    required bool otpVerified,
    required double codCollected,
  });
  Future<CodRemittance> submitRemittance({
    required double amount,
    required String method,
    required String reference,
  });
}

abstract interface class RiderProfileRepository implements Listenable {
  RiderProfile? get profile;
  Future<void> refresh();
  Future<void> saveProfile(RiderProfile profile);
  Future<void> setCheckedIn(bool checkedIn);
}

abstract interface class JobOfferRepository implements Listenable {
  List<JobOffer> get offers;
  Future<void> refresh();
  Future<void> setState(int id, String state);
}

abstract interface class CodRemittanceRepository implements Listenable {
  List<CodRemittance> get remittances;
  Future<void> refresh();
}

abstract interface class NotificationRepository implements Listenable {
  List<AppNotification> get notifications;
  Future<void> refresh();
  Future<void> markAllRead();
}

abstract interface class ConversationRepository implements Listenable {
  List<Conversation> get conversations;
  List<Message> messagesFor(int conversationId);
  Future<void> refresh();
  Future<void> send(int conversationId, String body);
}

abstract interface class EmergencyContactRepository implements Listenable {
  List<EmergencyContact> get contacts;
  Future<void> refresh();
  Future<void> saveContact(EmergencyContact contact);
  Future<void> delete(int id);
}

abstract interface class OutboxRepository implements Listenable {
  int get count;
  List<OutboxEntry> get entries;
  Future<void> refresh();
}
