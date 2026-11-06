class User {
  const User({
    required this.id,
    required this.lastName,
    required this.firstName,
    this.middleInitial,
    required this.sex,
    required this.email,
    required this.contactNo,
    required this.birthday,
    required this.age,
    required this.province,
    required this.municipality,
    required this.barangay,
    this.street,
    this.houseNumber,
    this.uploadedIdPath,
    this.isApproved = false,
  });

  final String id;
  final String lastName;
  final String firstName;
  final String? middleInitial;
  final String sex;
  final String email;
  final String contactNo;
  final DateTime birthday;
  final int age;
  final String province;
  final String municipality;
  final String barangay;
  final String? street;
  final String? houseNumber;
  final String? uploadedIdPath;
  final bool isApproved;
}
