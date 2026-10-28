import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';
import '../core/theme.dart';
import '../data/mock_data.dart';
import '../widgets/common.dart';

class ChatListScreen extends StatelessWidget {
  const ChatListScreen({super.key});
  @override
  Widget build(BuildContext context) {
    Widget row(String name, String role, String msg, String time, String sub, {int unread = 0, IconData? icon}) => Padding(
          padding: const EdgeInsets.only(bottom: 10),
          child: MCard(
            onTap: () => Navigator.pushNamed(context, '/chat/thread'),
            padding: const EdgeInsets.all(14),
            child: Row(children: [
              icon != null ? IconDot(icon, size: 48) : const CircleAvatar(radius: 24, backgroundColor: MC.highest, child: Icon(Icons.person, color: MC.primary)),
              const SizedBox(width: 12),
              Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                Row(children: [Flexible(child: Text(name, style: MT.labelLg, overflow: TextOverflow.ellipsis)), const SizedBox(width: 6), MChip(role, bg: MC.secondaryFixed, fg: const Color(0xFF24005B)), const Spacer(), Text(time, style: MT.bodySm)]),
                Row(children: [Expanded(child: Text(msg, style: MT.bodySm.copyWith(fontWeight: unread > 0 ? FontWeight.w600 : FontWeight.w400, color: unread > 0 ? MC.onSurface : MC.onSurfaceVariant), overflow: TextOverflow.ellipsis)), if (unread > 0) CircleAvatar(radius: 10, backgroundColor: MC.tertiaryContainer, child: Text('$unread', style: MT.badge.copyWith(color: Colors.white, fontSize: 10)))]),
                Text(sub, style: MT.bodySm.copyWith(fontSize: 11)),
              ])),
            ]),
          ),
        );
    return Scaffold(
      appBar: AppBar(backgroundColor: MC.header, foregroundColor: Colors.white, title: Text('Messages', style: MT.headlineMd.copyWith(color: Colors.white))),
      body: ListView(padding: const EdgeInsets.all(16), children: [
        TextField(decoration: InputDecoration(hintText: 'Search messages, orders, or contacts...', prefixIcon: const Icon(Icons.search), filled: true, fillColor: MC.lowest, border: OutlineInputBorder(borderRadius: BorderRadius.circular(99), borderSide: BorderSide.none))),
        const SizedBox(height: 12),
        row('Juan Miguel D.', 'Buyer', "Yes po, I'm waiting at the blue gate now.", '2:12 PM', 'Order #MM-88491 · COD ₱500.00', unread: 1),
        row('QC Hub Dispatch', 'Logistics', 'Please return uncollected parcels before 7:00 PM.', '1:45 PM', 'Route North B', unread: 2, icon: Icons.warehouse),
        row('Maria Clara Handicrafts', 'Seller', 'The parcels are sealed and ready on the counter.', '11:20 AM', 'Pickup Zone A'),
      ]),
    );
  }
}

class ChatThreadScreen extends StatefulWidget {
  const ChatThreadScreen({super.key});
  @override
  State<ChatThreadScreen> createState() => _ChatThreadScreenState();
}

class _Msg {
  final String text, time;
  final bool mine, failed;
  _Msg(this.text, this.time, {this.mine = false, this.failed = false});
}

class _ChatThreadScreenState extends State<ChatThreadScreen> {
  final ctrl = TextEditingController();
  final msgs = <_Msg>[
    _Msg('Hello kuya rider, are you near na po?', '2:10 PM'),
    _Msg('Opo sir, on the way na po. Around 5 minutes ETA.', '2:11 PM', mine: true),
    _Msg('Message not sent. Check internet connection.', '2:13 PM', mine: true, failed: true),
  ];
  void send(String t) {
    if (t.trim().isEmpty) return;
    setState(() => msgs.add(_Msg(t.trim(), 'now', mine: true)));
    ctrl.clear();
  }

  Future<void> _callBuyer() async {
    final messenger = ScaffoldMessenger.of(context);
    final uri = Uri.parse('tel:${mockHubParcel.phone}');
    var opened = false;
    try {
      opened = await canLaunchUrl(uri) && await launchUrl(uri);
    } catch (_) {
      opened = false;
    }
    if (!opened && mounted) {
      messenger.showSnackBar(const SnackBar(content: Text("Couldn't start the call")));
    }
  }

  @override
  Widget build(BuildContext context) => Scaffold(
        appBar: AppBar(
          backgroundColor: MC.header, foregroundColor: Colors.white,
          title: Row(children: [const CircleAvatar(radius: 18, backgroundColor: MC.highest, child: Icon(Icons.person, color: MC.primary)), const SizedBox(width: 8), Column(crossAxisAlignment: CrossAxisAlignment.start, children: [Text('Juan Miguel D.', style: MT.labelLg.copyWith(color: Colors.white)), Text('Online · In transit', style: MT.bodySm.copyWith(color: Colors.white70))])]),
          actions: [IconButton(onPressed: _callBuyer, icon: const Icon(Icons.call))],
        ),
        body: Column(children: [
          Container(
            width: double.infinity, color: MC.container, padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
            child: Row(children: [const Icon(Icons.inventory_2, size: 18, color: MC.primary), const SizedBox(width: 8), Text('TRK-0012345', style: MT.labelLg.copyWith(color: MC.primary)), const SizedBox(width: 6), Expanded(child: Text('· 45 Kalayaan Ave.', style: MT.bodySm, overflow: TextOverflow.ellipsis)), const MChip('COD ₱500.00', bg: MC.amberBg, fg: MC.amberText)]),
          ),
          Expanded(child: ListView(padding: const EdgeInsets.all(16), children: [
            for (final m in msgs)
              Align(
                alignment: m.mine ? Alignment.centerRight : Alignment.centerLeft,
                child: ConstrainedBox(
                  constraints: BoxConstraints(maxWidth: MediaQuery.of(context).size.width * .78),
                  child: Padding(padding: const EdgeInsets.only(bottom: 10), child: Column(crossAxisAlignment: m.mine ? CrossAxisAlignment.end : CrossAxisAlignment.start, children: [
                    Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(color: m.failed ? MC.errorContainer : (m.mine ? MC.primaryContainer : MC.lowest), borderRadius: BorderRadius.circular(16)),
                      child: Text(m.text, style: MT.bodyMd.copyWith(color: m.failed ? const Color(0xFF93000A) : (m.mine ? Colors.white : MC.onSurface))),
                    ),
                    const SizedBox(height: 2),
                    m.failed
                        ? Row(mainAxisSize: MainAxisSize.min, children: [Text('Delivery failed · ${m.time}  ', style: MT.bodySm.copyWith(color: MC.error)), InkWell(onTap: () {}, child: Text('Retry', style: MT.labelMd.copyWith(color: MC.tertiaryContainer)))])
                        : Row(mainAxisSize: MainAxisSize.min, children: [Text(m.time, style: MT.bodySm), if (m.mine) const Icon(Icons.done_all, size: 14, color: MC.primaryContainer)]),
                  ])),
                ),
              ),
          ])),
          SizedBox(height: 40, child: ListView(scrollDirection: Axis.horizontal, padding: const EdgeInsets.symmetric(horizontal: 16), children: [
            for (final q in ["I'm on my way po.", "I've arrived at the gate po.", 'Please prepare the parcel po.'])
              Padding(padding: const EdgeInsets.only(right: 8), child: ActionChip(label: Text(q.replaceAll(' po.', '')), onPressed: () => send(q), backgroundColor: MC.high, labelStyle: MT.labelMd.copyWith(color: MC.primary), shape: const StadiumBorder(), side: BorderSide.none)),
          ])),
          SafeArea(child: Padding(padding: const EdgeInsets.all(12), child: Row(children: [
            IconButton.filledTonal(onPressed: () {}, icon: const Icon(Icons.add_a_photo)),
            const SizedBox(width: 8),
            Expanded(child: TextField(controller: ctrl, onSubmitted: send, decoration: InputDecoration(hintText: 'Type a message...', filled: true, fillColor: MC.low, contentPadding: const EdgeInsets.symmetric(horizontal: 16), border: OutlineInputBorder(borderRadius: BorderRadius.circular(99), borderSide: BorderSide.none)))),
            const SizedBox(width: 8),
            IconButton.filled(onPressed: () => send(ctrl.text), style: IconButton.styleFrom(backgroundColor: MC.primaryContainer), icon: const Icon(Icons.send)),
          ]))),
        ]),
      );
}
