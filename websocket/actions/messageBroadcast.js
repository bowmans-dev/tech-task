// for testing purposes only

export default function messageBroadcast(data, eventScopedConnections, wss) {
    const senderId = String(data.user.id);
    const eventId = String(data.event_id);

    // Get the Set of recipients from the Map
    let recipients = eventScopedConnections.get(eventId);
    if (!recipients) {
        recipients = new Set();
        eventScopedConnections.set(eventId, recipients);
    }
    // Add the sender if not already present
    if (!recipients.has(senderId)) recipients.add(senderId);

    wss.clients.forEach((client) => {
        // Check if client is subscribed to event and their userId is in recipients Set
        if (
            client.connectedEvent?.some(sub => sub.eventId === eventId && recipients.has(sub.userId))
        ) {
            if (client.readyState === 1) {
                client.send(JSON.stringify({
                    action: "message_broadcast",
                    message_id: data.message_id,
                    message: data.message,
                    event_id: eventId,
                    userId: senderId,
                }));
            }
        }
    });
}
