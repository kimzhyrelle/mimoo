import 'repository_interfaces.dart';

abstract interface class RepositoryBundle {
  ParcelRepository get parcels;
  RiderProfileRepository get riderProfile;
  JobOfferRepository get offers;
  CodRemittanceRepository get remittances;
  NotificationRepository get notifications;
  ConversationRepository get conversations;
  EmergencyContactRepository get emergencyContacts;
  OutboxRepository get outbox;
  Future<void> signIn(String email, String password);
  Future<void> signOut();
  Future<void> resetLocalData();
  Future<void> close();
}
