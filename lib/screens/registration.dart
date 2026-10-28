import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';
import '../core/theme.dart';
import '../widgets/common.dart';

/// 4-step self-registration (ERP flow). Steps 1–4 share one scaffold.
class RegistrationScreen extends StatefulWidget {
  const RegistrationScreen({super.key});
  @override
  State<RegistrationScreen> createState() => _RegistrationScreenState();
}

class _RegistrationScreenState extends State<RegistrationScreen> {
  int step = 0;
  int vehicle = 0;
  DateTime? birthday;
  bool agreed = false, licenseUploaded = false, orcrUploaded = false;
  static const titles = ['Personal Details', 'Address Details', 'Vehicle & License', 'Documents'];

  int? get age {
    if (birthday == null) return null;
    final n = DateTime(2026, 10, 28); // use DateTime.now() in production
    var a = n.year - birthday!.year;
    if (n.month < birthday!.month || (n.month == birthday!.month && n.day < birthday!.day)) a--;
    return a;
  }

  Future<void> _discard() async {
    final out = await showDialog<bool>(
      context: context,
      builder: (c) => AlertDialog(
        title: Text('Discard your registration?', style: MT.headlineMd),
        content: const Text('Your entered details will be lost.'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(c, false), child: const Text('Keep Editing')),
          FilledButton(style: FilledButton.styleFrom(backgroundColor: MC.tertiaryContainer), onPressed: () => Navigator.pop(c, true), child: const Text('Discard')),
        ],
      ),
    );
    if (out == true && mounted) Navigator.pop(context);
  }

  @override
  Widget build(BuildContext context) => Scaffold(
        appBar: AppBar(
          backgroundColor: MC.lowest,
          leading: IconButton(icon: const Icon(Icons.arrow_back), onPressed: () => step == 0 ? Navigator.pop(context) : setState(() => step--)),
          title: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [Text('Rider Registration', style: MT.headlineMd), Text('Step ${step + 1} of 4 · ${titles[step]}', style: MT.bodySm)]),
          actions: [TextButton(onPressed: _discard, child: Text('Discard', style: MT.labelMd.copyWith(color: MC.tertiary)))],
          bottom: PreferredSize(preferredSize: const Size.fromHeight(6), child: LinearProgressIndicator(value: (step + 1) / 4, minHeight: 6, backgroundColor: MC.high, color: MC.primaryContainer)),
        ),
        body: ListView(padding: const EdgeInsets.all(16), children: [
          MCard(padding: const EdgeInsets.all(20), child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            ...switch (step) { 0 => _s1(), 1 => _s2(), 2 => _s3(), _ => _s4() },
          ].expand((w) => [w, const SizedBox(height: 14)]).toList()..removeLast())),
          const SizedBox(height: 16),
          PillButton(step == 3 ? 'Submit Registration' : 'Next', onPressed: step == 3 && !(agreed && licenseUploaded && orcrUploaded) ? null : () => step == 3 ? Navigator.pushReplacementNamed(context, '/pending') : setState(() => step++)),
        ]),
      );

  List<Widget> _s1() => [
        const MField('Last name', required: true),
        const MField('First name', required: true),
        const MField('Middle initial'),
        _dropdown('Sex', ['Male', 'Female'], required: true),
        const MField('Email', required: true, keyboard: TextInputType.emailAddress),
        const MField('Contact number', required: true, keyboard: TextInputType.phone, hint: '09XXXXXXXXX or +639XXXXXXXXX'),
        Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Text.rich(TextSpan(text: 'Birthday', style: MT.labelMd, children: const [TextSpan(text: ' *', style: TextStyle(color: MC.tertiaryContainer))])),
          const SizedBox(height: 6),
          InkWell(
            borderRadius: BorderRadius.circular(99),
            onTap: () async {
              final d = await showDatePicker(context: context, initialDate: DateTime(1995, 10, 24), firstDate: DateTime(1940), lastDate: DateTime(2026, 10, 28));
              if (d != null) setState(() => birthday = d);
            },
            child: Container(width: double.infinity, padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14), decoration: BoxDecoration(color: MC.low, borderRadius: BorderRadius.circular(99)), child: Text(birthday == null ? 'MM/DD/YYYY' : '${birthday!.month}/${birthday!.day}/${birthday!.year}', style: MT.bodyMd)),
          ),
          if (age != null && age! < 18) Padding(padding: const EdgeInsets.only(top: 4, left: 12), child: Text('You must be at least 18 years old', style: MT.bodySm.copyWith(color: MC.error))),
        ]),
        // Age is read-only, auto-filled from birthday.
        Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Text('Age', style: MT.labelMd),
          const SizedBox(height: 6),
          Container(width: double.infinity, padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14), decoration: BoxDecoration(color: MC.high, borderRadius: BorderRadius.circular(99)), child: Text(age == null ? '—' : '$age years old', style: MT.labelLg.copyWith(color: MC.primary))),
        ]),
      ];

  // TODO: wire Province → City → Barangay to PSGC data; disable child until parent chosen.
  List<Widget> _s2() => [
        _dropdown('Province', ['Metro Manila (NCR)', 'Cavite', 'Cebu'], required: true),
        _dropdown('Municipality / City', ['Quezon City', 'Pasig', 'Manila'], required: true),
        _dropdown('Barangay', ['Brgy. Central', 'Brgy. Kamuning'], required: true),
        const MField('Street', required: true),
        Row(children: const [Expanded(child: MField('House / Unit no.', required: true)), SizedBox(width: 12), Expanded(child: MField('Postal code', required: true, keyboard: TextInputType.number))]),
      ];

  List<Widget> _s3() => [
        Text('Choose vehicle *', style: MT.labelLg),
        Row(children: [
          for (final v in [(Icons.two_wheeler, 'Motorcycle', 'Up to 20kg'), (Icons.directions_car, 'Sedan / MPV', 'Up to 200kg'), (Icons.airport_shuttle, 'Van / Cargo', 'Up to 800kg')].asMap().entries)
            Expanded(child: GestureDetector(
              onTap: () => setState(() => vehicle = v.key),
              child: Container(
                margin: const EdgeInsets.symmetric(horizontal: 3), padding: const EdgeInsets.all(10),
                decoration: BoxDecoration(color: vehicle == v.key ? MC.high : MC.lowest, borderRadius: BorderRadius.circular(12), border: Border.all(color: vehicle == v.key ? MC.primary : MC.outlineVariant)),
                child: Column(children: [IconDot(v.value.$1, size: 40, bg: vehicle == v.key ? MC.primary : MC.low, fg: vehicle == v.key ? Colors.white : MC.onSurfaceVariant), const SizedBox(height: 6), Text(v.value.$2, style: MT.labelMd, textAlign: TextAlign.center), Text(v.value.$3, style: MT.bodySm.copyWith(fontSize: 10))]),
              ),
            )),
        ]),
        const MField('Plate number', required: true, hint: 'ABC123'),
        const MField("Driver's License No.", required: true, hint: 'A00-00-000000'),
        const MField('License expiry date', required: true, hint: 'MM/DD/YYYY'),
        // Validation messages (wire to backend):
        // "Plate number already registered" · "Driver's license number already registered"
        // "License is expired" · "License number format is A00-00-000000"
      ];

  List<Widget> _s4() => [
        _upload("Upload ID / Driver's License", licenseUploaded, () => setState(() => licenseUploaded = !licenseUploaded)),
        _upload('Upload Vehicle OR/CR', orcrUploaded, () => setState(() => orcrUploaded = !orcrUploaded)),
        CheckboxListTile(
          value: agreed, onChanged: (v) => setState(() => agreed = v ?? false), controlAffinity: ListTileControlAffinity.leading, contentPadding: EdgeInsets.zero,
          title: Text('I agree to the Terms and Privacy Policy *', style: MT.bodySm.copyWith(color: MC.onSurface)),
        ),
      ];

  Widget _upload(String t, bool done, VoidCallback onTap) => InkWell(
        borderRadius: BorderRadius.circular(12),
        onTap: () => showModalBottomSheet(context: context, backgroundColor: MC.lowest, shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(28))), builder: (c) => SafeArea(child: Column(mainAxisSize: MainAxisSize.min, children: [
          const SizedBox(height: 12),
          for (final o in [(Icons.photo_camera, 'Take Photo'), (Icons.photo_library, 'Choose from Gallery'), (Icons.picture_as_pdf, 'Browse Files (PDF)')])
            ListTile(leading: IconDot(o.$1, size: 40), title: Text(o.$2, style: MT.labelLg), onTap: () { Navigator.pop(c); onTap(); }),
          Padding(padding: const EdgeInsets.all(12), child: Text('JPG, PNG or PDF, max 5 MB', style: MT.bodySm)),
        ]))),
        child: Container(
          width: double.infinity, padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(color: MC.low, borderRadius: BorderRadius.circular(12)),
          child: Row(children: [
            IconDot(done ? Icons.check : Icons.cloud_upload, bg: done ? MC.greenBg : MC.high, fg: done ? MC.green : MC.primary),
            const SizedBox(width: 12),
            Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [Text('$t *', style: MT.labelLg), Text(done ? 'file_selected.jpg · ready' : 'Tap to upload · JPG, PNG, PDF · max 5 MB', style: MT.bodySm)])),
          ]),
        ),
      );

  Widget _dropdown(String label, List<String> items, {bool required = false}) => Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        Text.rich(TextSpan(text: label, style: MT.labelMd, children: [if (required) const TextSpan(text: ' *', style: TextStyle(color: MC.tertiaryContainer))])),
        const SizedBox(height: 6),
        DropdownButtonFormField<String>(
          items: items.map((e) => DropdownMenuItem(value: e, child: Text(e))).toList(),
          onChanged: (_) {},
          decoration: InputDecoration(filled: true, fillColor: MC.low, contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12), border: OutlineInputBorder(borderRadius: BorderRadius.circular(99), borderSide: BorderSide.none)),
        ),
      ]);
}

class PendingApprovalScreen extends StatelessWidget {
  const PendingApprovalScreen({super.key});
  @override
  Widget build(BuildContext context) => Scaffold(
        body: SafeArea(child: ListView(padding: const EdgeInsets.all(16), children: [
          MCard(color: MC.low, radius: 16, padding: const EdgeInsets.all(24), child: Column(children: [
            const IconDot(Icons.hourglass_top, size: 64, bg: MC.highest, fg: MC.primaryContainer),
            const SizedBox(height: 12),
            const MChip('Pending approval', bg: MC.amberBg, fg: MC.amberText, icon: Icons.schedule),
            const SizedBox(height: 8),
            Text('Registration submitted', style: MT.headlineLg),
            const SizedBox(height: 6),
            Text('Please wait for the approval of Bilis Hatid Express Inc. (Logistics / Sorting Center). The decision will be sent to your email.', style: MT.bodyMd.copyWith(color: MC.onSurfaceVariant), textAlign: TextAlign.center),
          ])),
          const SizedBox(height: 12),
          MCard(child: Column(children: [
            for (final r in [('Rider', 'Pedro Jose Garcia'), ('Vehicle', 'Motorcycle'), ('Plate', '123ABC')])
              Padding(padding: const EdgeInsets.symmetric(vertical: 4), child: Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [Text(r.$1, style: MT.bodySm), Text(r.$2, style: MT.labelMd)])),
          ])),
          const SizedBox(height: 16),
          PillButton('Back to Login', onPressed: () => Navigator.pushNamedAndRemoveUntil(context, '/login', (_) => false)),
          const SizedBox(height: 8),
          PillButton.secondary('Contact Logistics', leading: Icons.support_agent, trailing: null, onPressed: () => launchUrl(Uri.parse('tel:+639171234567'))),
          if (kDebugMode) ...[
            const SizedBox(height: 8),
            Row(mainAxisAlignment: MainAxisAlignment.center, children: [
              TextButton(onPressed: () => Navigator.pushNamed(context, '/registration/approved'), child: const Text('Demo: Approved')),
              TextButton(onPressed: () => Navigator.pushNamed(context, '/registration/rejected'), child: const Text('Demo: Rejected')),
            ]),
          ],
        ])),
      );
}
