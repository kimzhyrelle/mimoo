import 'package:sqflite/sqflite.dart';
import 'package:sqflite_common_ffi_web/sqflite_ffi_web.dart';

/// Browsers use the sqlite3 WASM factory persisted in IndexedDB.
void configureWebDatabaseFactory() {
  databaseFactory = databaseFactoryFfiWeb;
}
