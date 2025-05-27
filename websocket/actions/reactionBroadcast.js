export default function reactionBroadcast(data, eventScopedConnections, wss) {
    const { message_id, emoji, user, event_id, html } = data;

    const senderId = String(user.id);
    const eventId = String(event_id);

    let recipients = eventScopedConnections[eventId] || [];
    if (!recipients.includes(senderId)) recipients.push(senderId);

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
                sub => sub.eventId === eventId && recipients.includes(sub.userId)
            )
        ) {
            if (client.readyState === client.OPEN) {
                client.send(payload);
            }
        }
    });
}
