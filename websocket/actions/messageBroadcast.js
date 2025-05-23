// for testing purposes only

export default function messageBroadcast(data, eventScopedConnections, wss) {
    const senderId = String(data.user.id);
    const eventId = String(data.event_id);

    let recipients = eventScopedConnections[eventId] || [];
    if (!recipients.includes(senderId)) recipients.push(senderId);

    wss.clients.forEach((client) => {
        if (
            client.connectedEvent?.some(sub => sub.eventId === eventId && recipients.includes(sub.userId))
        ) {
            if (client.readyState === 1) {
                client.send(JSON.stringify({
                    action: "message_broadcast",
                    message: data.message,
                    event_id: eventId,
                    userId: senderId,
                }));
            }
        }
    });
}
