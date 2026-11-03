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
  /// Upload a photo file and return the server-side storage path.
  Future<String> uploadPhoto(String filePath, {String purpose = 'pod'});
  /// Send the delivery OTP to the buyer. Returns expiry ISO string and
  /// resend cooldown (seconds). In debug mode also returns `debugOtp`.
  Future<Map<String, dynamic>> sendDeliveryOtp(String trackingNo);
  /// Verify the buyer's 6-digit delivery OTP. Throws [ApiException] on
  /// wrong code (422) or lockout (429).
  Future<void> verifyDeliveryOtp(String trackingNo, String code);
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
  Future<void> changePassword({
    required String currentPassword,
    required String newPassword,
  });
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
