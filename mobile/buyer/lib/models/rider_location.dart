/// Represents a rider's live location, returned by the Laravel API's
/// rider tracking endpoint.
///
/// Expected JSON shape:
///   {
///     "order_id": "MM-89241029",
///     "lat": 14.2779,
///     "lng": 121.4587,
///     "updated_at": "2026-10-27T12:00:00Z"
///   }
class RiderLocation {
  const RiderLocation({
    required this.orderId,
    required this.lat,
    required this.lng,
    required this.updatedAt,
  });

  final String orderId;
  final double lat;
  final double lng;
  final DateTime updatedAt;

  factory RiderLocation.fromJson(Map<String, dynamic> json) {
    return RiderLocation(
      orderId: json['order_id'] as String,
      lat: (json['lat'] as num).toDouble(),
      lng: (json['lng'] as num).toDouble(),
      updatedAt: DateTime.parse(json['updated_at'] as String),
    );
  }
}
