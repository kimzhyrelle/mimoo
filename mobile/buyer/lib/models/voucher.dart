class Voucher {
  const Voucher({
    required this.code,
    required this.discountPercent,
    required this.minOrderAmount,
    required this.expiresAt,
    this.isUsed = false,
  });
  final String code;
  final double discountPercent;
  final double minOrderAmount;
  final DateTime expiresAt;
  final bool isUsed;
}
