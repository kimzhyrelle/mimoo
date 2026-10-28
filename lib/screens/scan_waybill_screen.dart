import 'package:flutter/material.dart';
import 'package:mobile_scanner/mobile_scanner.dart';
import '../core/theme.dart';

enum ScanOutcome { ok, mismatch, alreadyPickedUp }
class ScanResult { final ScanOutcome outcome; final String code; final String? detail; const ScanResult(this.outcome, this.code, [this.detail]); }

/// onValidate must call your API: ok -> mark picked up (+₱ pickup pay); mismatch/duplicate leave the job unchanged.
class ScanWaybillScreen extends StatefulWidget {
  final String expectedTracking; final double pickupPay, cod; final Future<ScanResult> Function(String code) onValidate;
  final ValueChanged<String> onConfirmed; final VoidCallback onBack;
  const ScanWaybillScreen({super.key, required this.expectedTracking, required this.pickupPay, required this.cod, required this.onValidate, required this.onConfirmed, required this.onBack});
  @override State<ScanWaybillScreen> createState() => _ScanState();
}

class _ScanState extends State<ScanWaybillScreen> {
  final _ctrl = MobileScannerController(formats: const [BarcodeFormat.code128, BarcodeFormat.qrCode]);
  final _manual = TextEditingController(); ScanResult? _result; bool _busy = false, _torch = false;

  @override void dispose() { _ctrl.dispose(); _manual.dispose(); super.dispose(); }

  Future<void> _submit(String raw) async {
    final code = raw.trim().toUpperCase();
    if (code.isEmpty || _busy) return;
    setState(() => _busy = true);
    await _ctrl.stop();
    final r = await widget.onValidate(code);
    if (!mounted) return;
    setState(() { _busy = false; _result = r; });
    if (r.outcome == ScanOutcome.ok) { widget.onConfirmed(code); } else { await _ctrl.start(); }
  }

  @override
  Widget build(BuildContext context) => MScaffold(
        title: 'Waybill Scan', onBack: widget.onBack,
        actions: [TextButton.icon(onPressed: () { _ctrl.toggleTorch(); setState(() => _torch = !_torch); },
            icon: Icon(_torch ? Icons.flashlight_on : Icons.flashlight_off, color: Colors.amber[200], size: 18),
            label: Text(_torch ? 'FLASH ON' : 'FLASH OFF', style: ts(11, w: FontWeight.w800, c: Colors.white)))],
        body: Column(children: [
          SizedBox(height: 300, child: Stack(fit: StackFit.expand, children: [
            MobileScanner(controller: _ctrl, onDetect: (cap) { final v = cap.barcodes.firstOrNull?.rawValue; if (v != null) _submit(v); },
              errorBuilder: (ctx, err, _) => _cameraDenied()),
            IgnorePointer(child: Center(child: Container(width: 280, height: 190, decoration: BoxDecoration(border: Border.all(color: MC.violet, width: 4), borderRadius: BorderRadius.circular(16))))),
            Positioned(bottom: 12, left: 0, right: 0, child: Center(child: Container(padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
              decoration: BoxDecoration(color: Colors.black54, borderRadius: BorderRadius.circular(99)),
              child: Text('Align barcode or QR within frame', style: ts(12, c: Colors.white, w: FontWeight.w500))))),
          ])),
          Expanded(child: Container(
            transform: Matrix4.translationValues(0, -16, 0),
            decoration: const BoxDecoration(color: Colors.white, borderRadius: BorderRadius.vertical(top: Radius.circular(24))),
            child: ListView(padding: const EdgeInsets.all(16), children: [
              Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
                Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                  Text('Or type tracking number', style: ts(18, w: FontWeight.w700)),
                  Text('Manual fallback if barcode is smudged or torn', style: ts(12, c: MC.muted))])),
                const Pill('Step 1 of 2')]),
              const SizedBox(height: 12),
              TextField(controller: _manual, textCapitalization: TextCapitalization.characters, onSubmitted: _submit,
                decoration: InputDecoration(hintText: 'e.g. ${widget.expectedTracking}', filled: true, fillColor: MC.tintLow,
                  prefixIcon: const Icon(Icons.qr_code_scanner, color: MC.violetDark),
                  suffixIcon: IconButton(icon: const Icon(Icons.close), onPressed: _manual.clear),
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(99), borderSide: BorderSide.none))),
              if (_result != null && _result!.outcome != ScanOutcome.ok) ...[const SizedBox(height: 12), _banner(_result!)],
              const SizedBox(height: 12),
              PillButton('Confirm Pickup', icon: Icons.arrow_forward, loading: _busy, onPressed: () => _submit(_manual.text)),
              const SizedBox(height: 8),
              Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
                Text('Pickup Pay: ${peso(widget.pickupPay)}', style: ts(12, w: FontWeight.w600)),
                Text('COD ${peso(widget.cod)}', style: ts(12, w: FontWeight.w800, c: MC.violetDark))]),
            ]))),
        ]));

  Widget _banner(ScanResult r) => switch (r.outcome) {
    ScanOutcome.mismatch => InfoBanner(Icons.error, 'Tracking number does not match this job',
        body: 'Scanned ${r.code}${r.detail != null ? ' ${r.detail}' : ''}. This assignment remains active and unchanged under status Assigned.', bg: MC.coral, fg: Colors.white),
    _ => InfoBanner(Icons.warning, 'This parcel is already picked up', body: r.detail ?? 'Duplicate scans do not generate redundant drop-offs.', bg: MC.amberSoft, fg: MC.amber),
  };

  Widget _cameraDenied() => Container(color: MC.deep, padding: const EdgeInsets.all(20), child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [
    const Icon(Icons.videocam_off, color: MC.tintHi, size: 40), const SizedBox(height: 8),
    Text('Camera access is needed to scan', style: ts(18, w: FontWeight.w700, c: Colors.white)),
    Text('You can still enter the tracking code manually below.', textAlign: TextAlign.center, style: ts(12, c: MC.tintHi)),
  ]));
}
