class Message {
  const Message({
    required this.id,
    required this.senderId,
    required this.receiverId,
    required this.content,
    required this.sentAt,
    this.isRead = false,
  });
  final String id;
  final String senderId;
  final String receiverId;
  final String content;
  final DateTime sentAt;
  final bool isRead;
}

class Conversation {
  const Conversation({
    required this.id,
    required this.participantId,
    required this.participantName,
    required this.lastMessage,
    required this.lastMessageAt,
    this.unreadCount = 0,
  });
  final String id;
  final String participantId;
  final String participantName;
  final String lastMessage;
  final DateTime lastMessageAt;
  final int unreadCount;
}
