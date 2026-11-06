import '../models/buyer_profile.dart';
import 'api_service.dart';

/// Thin repository over [ApiService] for buyer profile operations.
class ProfileRepository {
  ProfileRepository._();
  static final ProfileRepository instance = ProfileRepository._();

  Future<BuyerProfile> fetchProfile() async {
    final res = await ApiService.get('/buyer/profile') as Map<String, dynamic>;
    return BuyerProfile.fromJson(res['data'] as Map<String, dynamic>);
  }

  Future<BuyerProfile> updateProfile(Map<String, dynamic> fields) async {
    final res = await ApiService.put('/buyer/profile', body: fields)
        as Map<String, dynamic>;
    return BuyerProfile.fromJson(res['data'] as Map<String, dynamic>);
  }
}
