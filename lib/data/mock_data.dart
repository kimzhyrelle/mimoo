// PLACEHOLDER DATA ONLY.
//
// These values stand in for the real Mimoo Rider API so every screen can be
// navigated while the backend is not connected. Replace each mock with a
// repository/API call and keep the screen constructors unchanged.

import '../screens/cod_remittance_screen.dart';
import '../screens/job_offer_screen.dart';
import '../screens/notifications_screen.dart';
import '../screens/out_for_delivery_screen.dart';
import '../screens/pickup_detail_screen.dart';

/// Mock account state. The login screen routes on this instead of always going
/// to Home. TODO(api): replace with the status returned by the auth endpoint.
enum AccountStatus { active, suspended, pending, rejected }

// TODO: replace with real API response
const AccountStatus mockAccountStatus = AccountStatus.active;

/// Mock job offer. The expiry is relative to now so the countdown is always live.
JobOffer mockJobOffer({String id = 'OFFER-1001'}) => JobOffer(
      id: id,
      tracking: 'TRK-0012345',
      merchant: 'Maria Clara Handicrafts',
      pickupArea: 'Blk 12 Lot 8, Sampaguita St., Commonwealth, QC',
      dropArea: 'Katipunan Ave, Loyola Heights, QC',
      parcelLabel: '2 parcels · 4.5 kg',
      pickupPay: 30,
      cod: 500,
      km: 7.4,
      expiresAt: DateTime.now().add(const Duration(seconds: 60)),
    );

const PickupJob mockPickupJob = PickupJob(
  tracking: 'TRK-0012345',
  seller: 'Maria Clara Handicrafts',
  merchantId: 'MER-88421',
  address: 'Blk 12 Lot 8, Sampaguita St., Commonwealth, Quezon City',
  phone: '+639171234567',
  parcelLabel: '2 parcels · 4.5 kg',
  pay: 30,
  km: 1.2,
  etaMins: 6,
);

/// Pickup currently assigned to the rider. Null means there is no active
/// pickup, in which case Home hides the "Navigate to Pickup" button.
PickupJob? get mockActivePickup => mockPickupJob;

const HubParcel mockHubParcel = HubParcel(
  tracking: 'TRK-0012388',
  hubName: 'Quezon City Hub',
  recipient: 'Juan Miguel D.',
  phone: '+639181234567',
  address: '45 Kalayaan Ave., Brgy. Central, Quezon City',
  landmark: 'Blue gate beside bakery, ring the small doorbell twice',
  parcelLabel: '1 parcel · 2 kg',
  cod: 500,
  weightKg: 2,
  arrivedAt: '9:42 AM',
);

const List<CollectedParcel> mockCollectedParcels = [
  CollectedParcel('TRK-0012345', 'Handwoven table runner', 500, '2:15 PM', '45 Kalayaan Ave., QC'),
  CollectedParcel('TRK-0012322', 'Rattan storage basket', 650, '1:05 PM', 'Batasan Hills, QC'),
  CollectedParcel('TRK-0012308', 'Set of 4 placemats', 650, '11:48 AM', 'Fairview, QC'),
];

const List<AppNotification> mockNotifications = [
  AppNotification(
    NotifKind.jobOffer,
    'New job offer near you',
    'Maria Clara Handicrafts · ₱30 pickup pay · Expires in 40s',
    'Just now',
    tag: 'Offer',
    unread: true,
  ),
  AppNotification(
    NotifKind.sellerReady,
    'Seller marked parcels ready',
    'QC Books & Stationery has 2 parcels waiting at the counter.',
    '8 mins ago',
    tag: 'Pickup',
    unread: true,
  ),
  AppNotification(
    NotifKind.parcelAssigned,
    'Parcel assigned to you',
    'TRK-0012388 is now at Quezon City Hub. Mark it out for delivery.',
    '22 mins ago',
    tag: 'Delivery',
    unread: true,
  ),
  AppNotification(
    NotifKind.remittance,
    'COD remittance confirmed',
    'Your ₱1,300.00 remittance was verified by Bilis Hatid Express Inc.',
    '1 hr ago',
    unread: false,
  ),
  AppNotification(
    NotifKind.weather,
    'Heavy rain advisory',
    'Expect delays along the Commonwealth corridor this afternoon.',
    '2 hrs ago',
    unread: false,
  ),
];
