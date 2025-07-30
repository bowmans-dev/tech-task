export default function notifyTeamMembers(eventId, senderId, actionType, payload, wss) {
    const { eventName, message = "", created_at = null, user } = payload;

    for (const client of wss.clients) {
        const isNotSender = String(client.userId) !== String(senderId);
        const isSubscribed = client.allEventIds?.includes(eventId);

        if (client.readyState === WebSocket.OPEN && isSubscribed && isNotSender) {
            client.send(JSON.stringify({
                action: actionType,
                payload: {
                    eventId,
                    eventName,
                    message,
                    created_at,
                    user
                }
            }));
        }
    }
}