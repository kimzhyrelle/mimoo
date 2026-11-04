/// Turns raw exceptions into short, user-facing messages.
///
/// Repository code throws `Exception('...')` with human-readable copy; this
/// strips the type prefix and hides low-level database errors.
String friendlyError(Object error,
    {String fallback = 'Something went wrong. Please try again.'}) {
  var message = error.toString().trim();
  const prefix = 'Exception: ';
  if (message.startsWith(prefix)) message = message.substring(prefix.length).trim();
  const statePrefix = 'Bad state: ';
  if (message.startsWith(statePrefix)) {
    message = message.substring(statePrefix.length).trim();
  }
  if (message.isEmpty) return fallback;
  final lower = message.toLowerCase();
  if (lower.contains('databaseexception') ||
      lower.contains('sqlite') ||
      lower.contains('syntax error') ||
      lower.contains('no such table')) {
    return 'We could not complete that request. Please try again.';
  }
  return message;
}
