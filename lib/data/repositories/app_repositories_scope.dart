import 'package:flutter/widgets.dart';

import 'repository_bundle.dart';

class AppRepositoriesScope extends InheritedWidget {
  const AppRepositoriesScope({
    super.key,
    required this.repositories,
    required super.child,
  });

  final RepositoryBundle repositories;

  static RepositoryBundle of(BuildContext context) {
    final scope =
        context.dependOnInheritedWidgetOfExactType<AppRepositoriesScope>();
    assert(scope != null, 'AppRepositoriesScope is missing above this widget');
    return scope!.repositories;
  }

  @override
  bool updateShouldNotify(AppRepositoriesScope oldWidget) =>
      !identical(repositories, oldWidget.repositories);
}
