import 'package:flutter/foundation.dart';

import '../models/entities.dart';
import 'api_client.dart';
import 'repository_bundle.dart';
import 'repository_interfaces.dart';

typedef _JsonMap = Map<String, dynamic>;

_JsonMap _map(Object? value) {
  if (value is! Map) {
    throw const FormatException('The server returned an invalid object.');
  }
  return value.map((key, value) => MapEntry(key.toString(), value));
}

List<_JsonMap> _maps(Object? value) {
  if (value is! List) {
    throw const FormatException('The server returned an invalid list.');
  }
  return value.map(_map).toList(growable: false);
}

DbMap _modelMap(_JsonMap json) => json.map((key, value) {
      if (value is bool) return MapEntry(key, value ? 1 : 0);
      if (value is DateTime) return MapEntry(key, value.toIso8601String());
      if (value is num && value.isFinite && value == value.roundToDouble()) {
        return MapEntry(key, value.toInt());
      }
      return MapEntry(key, value);
    });

class ApiRepositories implements RepositoryBundle {
  ApiRepositories({ApiClient? client})
      : _store = _ApiStore(client ?? ApiClient()) {
    parcels = _ApiParcelRepository(_store);
    riderProfile = _ApiRiderProfileRepository(_store);
    offers = _ApiJobOfferRepository(_store);
    remittances = _ApiCodRemittanceRepository(_store);
    notifications = _ApiNotificationRepository(_store);
    conversations = _ApiConversationRepository(_store);
    emergencyContacts = _ApiEmergencyContactRepository(_store);
    outbox = _ApiOutboxRepository(_store);
  }

  final _ApiStore _store;

  @override
  late final ParcelRepository parcels;
  @override
  late final RiderProfileRepository riderProfile;
  @override
  late final JobOfferRepository offers;
  @override
  late final CodRemittanceRepository remittances;
  @override
  late final NotificationRepository notifications;
  @override
  late final ConversationRepository conversations;
  @override
  late final EmergencyContactRepository emergencyContacts;
  @override
  late final OutboxRepository outbox;

  @override
  Future<void> signIn(String email, String password) async {
    await _store.client.signIn(email, password);
    try {
      await _store.refresh();
    } catch (_) {
      await _store.client.signOut();
      rethrow;
    }
  }

  Future<bool> restoreSession() async {
    if (!await _store.client.hasSavedSession()) return false;
    try {
      await _store.refresh();
      return true;
    } on ApiException catch (error) {
      if (error.statusCode != 401) rethrow;
      await _store.client.signOut();
      return false;
    }
  }

  @override
  Future<void> signOut() => _store.client.signOut();

  @override
  Future<void> resetLocalData() async {
    await _store.client.post('/api/dev/reset');
    await _store.refresh();
  }

  @override
  Future<void> close() async => _store.client.close();
}

class _ApiStore extends ChangeNotifier {
  _ApiStore(this.client);
  final ApiClient client;
  RiderProfile? profile;
  List<Parcel> parcels = const [];
  List<ParcelStatusLog> statusLogs = const [];
  List<JobOffer> offers = const [];
  List<CodRemittance> remittances = const [];
  List<AppNotification> notifications = const [];
  List<Conversation> conversations = const [];
  List<Message> messages = const [];
  List<EmergencyContact> emergencyContacts = const [];

  Future<void> refresh() async {
    final response = await client.get('/api/bootstrap');
    final profileValue = _map(response['profile']);
    profile = RiderProfile.fromMap(_modelMap(profileValue));
    parcels = _maps(response['parcels'])
        .map((row) => Parcel.fromMap(_modelMap(row)))
        .toList(growable: false);
    statusLogs = _maps(response['status_logs'])
        .map((row) => ParcelStatusLog.fromMap(_modelMap(row)))
        .toList(growable: false);
    offers = _maps(response['offers'])
        .map((row) => JobOffer.fromMap(_modelMap(row)))
        .toList(growable: false);
    remittances = _maps(response['remittances'])
        .map((row) => CodRemittance.fromMap(_modelMap(row)))
        .toList(growable: false);
    notifications = _maps(response['notifications'])
        .map((row) => AppNotification.fromMap(_modelMap(row)))
        .toList(growable: false);
    conversations = _maps(response['conversations'])
        .map((row) => Conversation.fromMap(_modelMap(row)))
        .toList(growable: false);
    messages = _maps(response['messages'])
        .map((row) => Message.fromMap(_modelMap(row)))
        .toList(growable: false);
    emergencyContacts = _maps(response['emergency_contacts'])
        .map((row) => EmergencyContact.fromMap(_modelMap(row)))
        .toList(growable: false);
    notifyListeners();
  }
}

abstract class _ApiRepository extends ChangeNotifier {
  _ApiRepository(this.store) {
    store.addListener(notifyListeners);
  }
  final _ApiStore store;

  @override
  void dispose() {
    store.removeListener(notifyListeners);
    super.dispose();
  }
}

class _ApiParcelRepository extends _ApiRepository implements ParcelRepository {
  _ApiParcelRepository(super.store);

  @override
  List<Parcel> get parcels => List.unmodifiable(store.parcels);
  @override
  List<Parcel> get active => store.parcels
      .where((parcel) => const {
            ParcelStatus.assigned,
            ParcelStatus.pickedUp,
            ParcelStatus.outForDelivery,
          }.contains(parcel.status))
      .toList(growable: false);
  @override
  List<Parcel> get unremitted => store.parcels
      .where((parcel) =>
          parcel.status == ParcelStatus.delivered &&
          !parcel.isPrepaid &&
          parcel.codCollected &&
          !parcel.codRemitted)
      .toList(growable: false);
  @override
  List<ParcelStatusLog> get statusLogs => List.unmodifiable(store.statusLogs);
  @override
  int get outboxCount => 0;
  @override
  Parcel get focus {
    for (final status in [
      ParcelStatus.outForDelivery,
      ParcelStatus.assigned,
      ParcelStatus.failedDelivery,
      ParcelStatus.pickedUp,
    ]) {
      for (final parcel in store.parcels) {
        if (parcel.status == status) return parcel;
      }
    }
    if (store.parcels.isEmpty) {
      throw StateError('No parcels are available for this rider.');
    }
    return store.parcels.first;
  }

  @override
  Parcel? get assignedPickup {
    for (final parcel in store.parcels) {
      if (parcel.jobType == ParcelJobType.pickup &&
          const {ParcelStatus.assigned, ParcelStatus.pickedUp}
              .contains(parcel.status)) {
        return parcel;
      }
    }
    return null;
  }

  @override
  double get pendingRemittance => store.remittances
      .where((remittance) => remittance.status == 'pending')
      .fold(0, (total, remittance) => total + remittance.amount);
  @override
  double get codOnHand {
    final collected =
        unremitted.fold<double>(0, (sum, parcel) => sum + parcel.codAmount);
    return (collected - pendingRemittance).clamp(0, double.infinity);
  }

  @override
  double earningsSince(DateTime from) => store.parcels.fold<double>(
        0,
        (sum, parcel) =>
            sum +
            (parcel.pickedUpAt != null && !parcel.pickedUpAt!.isBefore(from)
                ? parcel.pickupFee
                : 0) +
            (parcel.deliveredAt != null && !parcel.deliveredAt!.isBefore(from)
                ? parcel.deliveryFee
                : 0),
      );
  @override
  int completedJobsSince(DateTime from) => store.parcels.fold<int>(
        0,
        (count, parcel) =>
            count +
            (parcel.pickedUpAt != null && !parcel.pickedUpAt!.isBefore(from)
                ? 1
                : 0) +
            (parcel.deliveredAt != null && !parcel.deliveredAt!.isBefore(from)
                ? 1
                : 0),
      );
  @override
  Parcel? byTracking(String tracking) {
    for (final parcel in store.parcels) {
      if (parcel.trackingNo == tracking) return parcel;
    }
    return null;
  }

  @override
  Parcel? fromArgs(Object? argument) {
    if (argument is Parcel) return byTracking(argument.trackingNo);
    if (argument is String) return byTracking(argument);
    return store.parcels.isEmpty ? null : focus;
  }

  @override
  Map<String, List<Parcel>> groupedByArea(Iterable<Parcel> parcels) {
    final grouped = <String, List<Parcel>>{};
    for (final parcel in parcels) {
      final key = '${parcel.areaName ?? ''}|${parcel.routeCode ?? ''}';
      grouped.putIfAbsent(key, () => []).add(parcel);
    }
    return grouped;
  }

  @override
  Future<void> refresh() => store.refresh();
  @override
  Future<bool> transition(
    String trackingNo,
    ParcelStatus to, {
    String? reason,
  }) async {
    await store.client.patch(
      '/api/parcels/${Uri.encodeComponent(trackingNo)}/status',
      body: {'to_status': to.value, if (reason != null) 'reason': reason},
    );
    await store.refresh();
    return true;
  }

  @override
  Future<bool> completeDelivery(
    String trackingNo, {
    required String photoPath,
    required bool otpVerified,
    required double codCollected,
  }) async {
    // TODO(api): upload the live camera photo and verify the buyer OTP server-side.
    await store.client.post(
      '/api/parcels/${Uri.encodeComponent(trackingNo)}/delivery',
      body: {
        'photo_path': photoPath,
        'otp_verified': otpVerified,
        'cod_collected': codCollected,
      },
    );
    await store.refresh();
    return true;
  }

  @override
  Future<CodRemittance> submitRemittance({
    required double amount,
    required String method,
    required String reference,
  }) async {
    final result = _map(await store.client.post(
      '/api/remittances',
      body: {'amount': amount, 'method': method, 'reference': reference},
    ));
    await store.refresh();
    return CodRemittance.fromMap(_modelMap(result));
  }
}

class _ApiRiderProfileRepository extends _ApiRepository
    implements RiderProfileRepository {
  _ApiRiderProfileRepository(super.store);
  @override
  RiderProfile? get profile => store.profile;
  @override
  Future<void> refresh() => store.refresh();
  @override
  Future<void> saveProfile(RiderProfile profile) async {
    await store.client.patch('/api/profile', body: {
      'phone': profile.phone,
      'address': profile.address,
      if (profile.photoPath != null) 'photo_path': profile.photoPath,
    });
    await store.refresh();
  }

  @override
  Future<void> setCheckedIn(bool checkedIn) async {
    await store.client.put(
      '/api/profile/check-in',
      body: {'checked_in': checkedIn},
    );
    await store.refresh();
  }
}

class _ApiJobOfferRepository extends _ApiRepository
    implements JobOfferRepository {
  _ApiJobOfferRepository(super.store);
  @override
  List<JobOffer> get offers => List.unmodifiable(store.offers);
  @override
  Future<void> refresh() => store.refresh();
  @override
  Future<void> setState(int id, String state) async {
    await store.client.patch('/api/offers/$id', body: {'state': state});
    await store.refresh();
  }
}

class _ApiCodRemittanceRepository extends _ApiRepository
    implements CodRemittanceRepository {
  _ApiCodRemittanceRepository(super.store);
  @override
  List<CodRemittance> get remittances => List.unmodifiable(store.remittances);
  @override
  Future<void> refresh() => store.refresh();
}

class _ApiNotificationRepository extends _ApiRepository
    implements NotificationRepository {
  _ApiNotificationRepository(super.store);
  @override
  List<AppNotification> get notifications =>
      List.unmodifiable(store.notifications);
  @override
  Future<void> refresh() => store.refresh();
  @override
  Future<void> markAllRead() async {
    await store.client.post('/api/notifications/read-all');
    await store.refresh();
  }
}

class _ApiConversationRepository extends _ApiRepository
    implements ConversationRepository {
  _ApiConversationRepository(super.store);
  @override
  List<Conversation> get conversations =>
      List.unmodifiable(store.conversations);
  @override
  List<Message> messagesFor(int conversationId) => store.messages
      .where((message) => message.conversationId == conversationId)
      .toList(growable: false);
  @override
  Future<void> refresh() => store.refresh();
  @override
  Future<void> send(int conversationId, String body) async {
    final text = body.trim();
    if (text.isEmpty) return;
    await store.client.post(
      '/api/conversations/$conversationId/messages',
      body: {'body': text},
    );
    await store.refresh();
  }
}

class _ApiEmergencyContactRepository extends _ApiRepository
    implements EmergencyContactRepository {
  _ApiEmergencyContactRepository(super.store);
  @override
  List<EmergencyContact> get contacts =>
      List.unmodifiable(store.emergencyContacts);
  @override
  Future<void> refresh() => store.refresh();
  @override
  Future<void> saveContact(EmergencyContact contact) async {
    final payload = {
      'name': contact.name,
      'relationship': contact.relationship,
      'phone': contact.phone,
    };
    if (contact.id == null) {
      await store.client.post('/api/emergency-contacts', body: payload);
    } else {
      await store.client.put(
        '/api/emergency-contacts/${contact.id}',
        body: payload,
      );
    }
    await store.refresh();
  }

  @override
  Future<void> delete(int id) async {
    await store.client.delete('/api/emergency-contacts/$id');
    await store.refresh();
  }
}

class _ApiOutboxRepository extends _ApiRepository implements OutboxRepository {
  _ApiOutboxRepository(super.store);
  @override
  int get count => 0;
  @override
  List<OutboxEntry> get entries => const [];
  @override
  Future<void> refresh() async {}
}
