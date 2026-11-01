import 'dart:async';

import 'package:flutter/material.dart';
import '../core/rider_status_controller.dart';
import '../data/repositories/app_repositories_scope.dart';
import '../widgets/mimoo_drawer.dart';
import '../widgets/shell.dart';
import 'home_tab.dart';
import 'jobs_tab.dart';
import 'history_tab.dart';
import 'earnings_tab.dart';

class MainShell extends StatefulWidget {
  const MainShell({super.key});
  @override
  State<MainShell> createState() => _MainShellState();
}

class _MainShellState extends State<MainShell> with WidgetsBindingObserver {
  int _i = 0;
  RiderStatusController? _status;
  bool _refreshedLocation = false;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);
  }

  @override
  void didChangeDependencies() {
    super.didChangeDependencies();
    _status = RiderStatusScope.of(context);
    if (_refreshedLocation) return;
    _refreshedLocation = true;
    unawaited(_status!.refreshLocation());
  }

  @override
  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
    super.dispose();
  }

  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    final status = _status;
    if (state == AppLifecycleState.resumed && status != null) {
      unawaited(status.syncLocationOnResume());
    }
  }

  @override
  Widget build(BuildContext context) {
    final repositories = AppRepositoriesScope.of(context);
    return ListenableBuilder(
      listenable: Listenable.merge([
        repositories.notifications,
        repositories.conversations,
      ]),
      builder: (context, _) {
        final unreadNotifications = repositories.notifications.notifications
            .where((notification) => notification.readAt == null)
            .length;
        final unreadMessages =
            repositories.conversations.conversations.fold<int>(
          0,
          (count, conversation) =>
              count +
              repositories.conversations
                  .messagesFor(conversation.id ?? -1)
                  .where((message) =>
                      message.sender != 'rider' && message.readAt == null)
                  .length,
        );
        return Scaffold(
          appBar: MimooAppBar(
            unreadMessages: unreadMessages,
            unreadNotifications: unreadNotifications,
          ),
          drawer: const MimooDrawer(),
          body: IndexedStack(index: _i, children: [
            HomeTab(onOpenEarnings: () => setState(() => _i = 2)),
            const JobsTab(),
            const EarningsTab(),
            const HistoryTab(),
          ]),
          bottomNavigationBar: MimooBottomNav(
            index: _i,
            onTap: (v) => setState(() => _i = v),
            onScan: () => Navigator.pushNamed(context, '/scan-waybill'),
          ),
        );
      },
    );
  }
}
