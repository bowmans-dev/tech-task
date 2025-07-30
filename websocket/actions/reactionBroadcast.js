export default function reactionBroadcast(data, eventScopedConnections, wss) {
    const { message_id, emoji, user, event_id, html } = data;

    const senderId = String(user.id);
    const eventId = String(event_id);

    // Use Map.get() to get the Set
    let recipients = eventScopedConnections.get(eventId);
    if (!recipients) {
        recipients = new Set();
        eventScopedConnections.set(eventId, recipients);
    }

    // Add sender to recipients set if not already present
    if (!recipients.has(senderId)) recipients.add(senderId);

    const payload = JSON.stringify({
        action: 'reaction_broadcast',
        message_id,
        emoji,
        user,
        html
    });

    wss.clients.forEach((client) => {
        if (
            client.connectedEvent?.some(
                sub => sub.eventId === eventId && recipients.has(sub.userId)
            )
        ) {
            if (client.readyState === client.OPEN) {
                client.send(payload);
            }
        }
    });
}
