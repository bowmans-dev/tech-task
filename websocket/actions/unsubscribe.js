export default function unsubscribe(ws, jsonData, eventScopedConnections, wss) {
    const userId = String(jsonData.userId);
    const eventId = String(jsonData.event_id);

    if (eventScopedConnections[eventId]) {
        eventScopedConnections[eventId] = eventScopedConnections[eventId].filter(id => id !== userId);
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
