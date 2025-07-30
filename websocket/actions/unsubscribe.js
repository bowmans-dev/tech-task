export default function unsubscribe(ws, jsonData, eventScopedConnections, wss) {
    const userId = String(jsonData.userId);
    const eventId = String(jsonData.event_id);

    const usersSet = eventScopedConnections.get(eventId);
    if (usersSet) {
        usersSet.delete(userId);
        // if set becomes empty, remove the key from the Map:
        if (usersSet.size === 0) {
            eventScopedConnections.delete(eventId);
        }
    }

    [...wss.clients].forEach(client => {
        if (client.connectedEvent?.some(sub => sub.userId === userId && sub.eventId === eventId)) {
            client.send(JSON.stringify({
                action: "unsubscribed",
                userId,
                event_id: eventId
            }));
            client.connectedEvent = client.connectedEvent.filter(sub =>
                sub.userId !== userId || sub.eventId !== eventId
            );
            if (client.connectedEvent.length === 0) client.terminate();
        }
    });
}
