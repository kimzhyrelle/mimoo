import 'package:flutter/material.dart';
import '../widgets/shell.dart';
import 'home_tab.dart';
import 'jobs_tab.dart';
import 'history_tab.dart';
import 'earnings_tab.dart';
import 'account_tab.dart';

class MainShell extends StatefulWidget {
  const MainShell({super.key});
  @override
  State<MainShell> createState() => _MainShellState();
}

class _MainShellState extends State<MainShell> {
  int _i = 0;
  static const _tabs = [HomeTab(), JobsTab(), HistoryTab(), EarningsTab(), AccountTab()];
  @override
  Widget build(BuildContext context) => Scaffold(
        appBar: const MimooAppBar(),
        body: IndexedStack(index: _i, children: _tabs),
        bottomNavigationBar: MimooBottomNav(index: _i, onTap: (v) => setState(() => _i = v)),
      );
}
