export default function taskCompletedBroadcast(data, eventScopedConnections, wss) {
    const { message_id, task_id, user, event_id, html } = data;

    const senderId = String(user.id);
    const eventId = String(event_id);

    let recipients = eventScopedConnections[eventId] || [];
    if (!recipients.includes(senderId)) recipients.push(senderId);

    const payload = JSON.stringify({
        action: "task_completed_broadcast",
        message_id,
        task_id,
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