export function broadcastToEvent(ws, data, eventScopedConnections, wss, action, payloadData) {
    const senderId = String(ws.userId);
    const eventId = String(data.event_id);

    let recipients = eventScopedConnections.get(eventId);
    if (!recipients) {
        recipients = new Set();
        eventScopedConnections.set(eventId, recipients);
    }
    // always add sender (safe for voteBroadcast too)
    recipients.add(senderId);

    const payload = JSON.stringify({ action, ...payloadData });

    wss.clients.forEach(client => {
        if (
            client.connectedEvent?.some(sub => sub.eventId === eventId && recipients.has(sub.userId))
            && client.readyState === 1
        ) {
            client.send(payload);
        }
    });
}
