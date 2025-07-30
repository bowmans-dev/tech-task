export default function updateEvent(jsonData, eventScopedConnections, wss) {
    const eventId = jsonData.event_id;
    if (!eventScopedConnections.has(eventId)) {
        console.warn(`No subscriptions found for event ${eventId}`);
        return;
    }

    wss.clients.forEach(client => {
        if (client.connectedEvent?.some(sub => sub.eventId === eventId)) {
            client.send(JSON.stringify({
                action: "update_event",
                event_id: eventId,
                teamMembers: jsonData.teamMembers,
                files: jsonData.files
            }));
        }
    });
}
