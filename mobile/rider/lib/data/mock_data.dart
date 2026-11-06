// PLACEHOLDER DATA ONLY.
//
// These values stand in for the real Mimoo Rider API so every screen can be
// navigated while the backend is not connected. Replace each mock with a
// repository/API call and keep the screen constructors unchanged.

import '../screens/history_job_detail.dart';
import '../screens/job_offer_screen.dart';
import '../screens/notifications_screen.dart';
import '../screens/pickup_detail_screen.dart';

/// Mock account state. The login screen routes on this instead of always going
/// to Home. TODO(api): replace with the status returned by the auth endpoint.
enum AccountStatus { active, suspended, pending, rejected }

// TODO: replace with real API response
const AccountStatus mockAccountStatus = AccountStatus.active;

/// Rider area shown on Home. TODO(api): resolve from GPS / reverse geocode.
const String mockRiderArea = 'Fairview, Quezon City';

/// Money figures shared by Home and Earnings so both screens agree.
/// TODO(api): replace with the rider earnings endpoint.
const double mockTodayEarnings = 210;
const int mockTodayJobs = 5;
const double mockWeekEarnings = 1180;
const int mockWeekJobs = 26;
const int mockPickupJobsToday = 2;
const int mockDeliveryJobsToday = 3;
const int mockWeekPickups = 6;
const int mockWeekDeliveries = 20;

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
      expiresAt: DateTime.now().add(const Duration(minutes: 3)),
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

/// Closed jobs shown in History. TODO(api): replace with the job-history
/// endpoint; keep isToday in sync with the server's timezone.
const List<HistoryJob> mockHistory = [
  HistoryJob(
    tracking: 'TRK-0012345',
    type: 'Delivery',
    status: 'Delivered',
    time: 'Oct 30, 2026, 2:15 PM',
    label: 'Buyer location',
    location: '45 Kalayaan Ave., Brgy. Central, QC',
    earnings: 50,
    cod: 'COD ₱500.00',
  ),
  HistoryJob(
    tracking: 'TRK-0012388',
    type: 'Pickup',
    status: 'Picked Up',
    time: 'Oct 30, 2026, 11:30 AM',
    label: 'Seller hub',
    location: 'Maria Clara Handicrafts, Commonwealth',
    earnings: 30,
  ),
  HistoryJob(
    tracking: 'TRK-0012301',
    type: 'Delivery',
    status: 'Failed (Attempt 1)',
    time: 'Oct 30, 2026, 09:15 AM',
    label: 'Incident note',
    location: 'Buyer not home · Rescheduled',
    earnings: 0,
  ),
  HistoryJob(
    tracking: 'TRK-0011982',
    type: 'Delivery',
    status: 'Return to Sender',
    time: 'Oct 28, 2026, 4:40 PM',
    label: 'RTS reason',
    location: 'Refused by buyer · Returned to hub',
    earnings: 0,
    isToday: false,
  ),
];

/// Unread badge counts for the shell app bar. TODO(api): read from the inbox
/// and support threads; notifications derive from the seeded list below.
const int mockUnreadMessages = 2;

int get mockUnreadNotifications =>
    mockNotifications.where((n) => n.unread).length;

const List<AppNotification> mockNotifications = [
  AppNotification(
    NotifKind.jobOffer,
    'New job offer near you',
    'Maria Clara Handicrafts · ₱30 pickup pay · Expires in 3m',
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
