import 'package:flutter/foundation.dart';
import 'package:intl/intl.dart';

import 'models/entities.dart';
import 'repositories/repository_interfaces.dart';

export 'models/entities.dart'
    show CustodyEvent, Parcel, ParcelJobType, ParcelStatus, ParcelStatusLabel;

typedef DispatchParcel = Parcel;

const Map<String, String> carrierLabels = {
  'jnt': 'J&T Express',
  'lbc': 'LBC',
  'lalamove': 'Lalamove',
};

const List<String> failureReasons = [
  'Buyer not home',
  'Wrong address',
  'Refused',
];

@Deprecated('Rider availability is persisted by RiderProfileRepository.')
final ValueNotifier<bool> riderOnline = ValueNotifier<bool>(true);

String clock(DateTime dateTime) => DateFormat('h:mm a', 'en').format(dateTime);

enum RemittanceState { pending, confirmed, short }

@Deprecated('Use CodRemittance from the repository layer.')
class RemittanceRecord {
  final String reference;
  final double expected, collected;
  RemittanceState state;

  RemittanceRecord(this.reference, this.expected, this.collected, this.state);

  double get outstanding {
    final gap = expected - collected;
    return gap <= 0 ? 0 : double.parse(gap.toStringAsFixed(2));
  }
}

@Deprecated('Use AppRepositoriesScope and ParcelRepository.')
class DispatchStore extends ChangeNotifier {
  DispatchStore._(this.repository) {
    repository.addListener(notifyListeners);
  }

  static DispatchStore? _shared;

  static DispatchStore install(ParcelRepository repository) =>
      _shared ??= DispatchStore._(repository);

  static DispatchStore get instance {
    final value = _shared;
    if (value == null) {
      throw StateError('DispatchStore.install() must run before app startup.');
    }
    return value;
  }

  final ParcelRepository repository;

  List<Parcel> get parcels => repository.parcels;
  List<Parcel> get active => repository.active;
  List<Parcel> get forDelivery => repository.parcels
      .where((parcel) => parcel.jobType == ParcelJobType.delivery)
      .toList(growable: false);
  List<Parcel> get unremitted => repository.unremitted;
  double get unremittedCod => repository.codOnHand;
  Parcel get focus => repository.focus;

  Parcel? byTracking(String? tracking) =>
      tracking == null ? null : repository.byTracking(tracking);

  Parcel? fromArgs(Object? argument) => repository.fromArgs(argument);

  Map<String, List<Parcel>> groupedByArea(Iterable<Parcel> parcels) =>
      repository.groupedByArea(parcels);

  Future<bool> markFailed(Parcel parcel, String reason) =>
      repository.transition(
        parcel.trackingNo,
        ParcelStatus.failedDelivery,
        reason: reason,
      );
}

@Deprecated('Use AppRepositoriesScope and ParcelRepository.')
DispatchStore get dispatchStore => DispatchStore.instance;
