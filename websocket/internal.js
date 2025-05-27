export default function internal(ws, wss, eventScopedConnections, notifyTeamMembers, globalConnectedUsers) {
    ws.isInternal = true;

    ws.on("message", (message) => {
        const data = JSON.parse(message.toString());

        if (data.action === "message_broadcast") {
            const senderId = String(data.user.id);
            const eventId = data.event_id;

            const senderClient = [...wss.clients].find(c => String(c.userId) === senderId);
            if (senderClient) {

                if (!senderClient.connectedEvent) senderClient.connectedEvent = [];
                if (!senderClient.connectedEvent.some(sub => sub.eventId === eventId)) {
                    senderClient.connectedEvent.push({ userId: senderId, eventId });
                }

                if (!senderClient.allEventIds) senderClient.allEventIds = [];
                if (!senderClient.allEventIds.includes(eventId)) {
                    senderClient.allEventIds.push(eventId);
                }

                if (!eventScopedConnections[eventId]) eventScopedConnections[eventId] = [];
                if (!eventScopedConnections[eventId].includes(senderId)) {
                    eventScopedConnections[eventId].push(senderId);
                }

                if (globalConnectedUsers[senderId]) {
                    if (!globalConnectedUsers[senderId].allEventIds.includes(eventId)) {
                        globalConnectedUsers[senderId].allEventIds.push(eventId);
                    }
                }
            } else {
                console.log(`Sender client not found in wss.clients`);
            }

            // Broadcast to those currently viewing
            wss.clients.forEach((client) => {
                const isSubscribed = client.connectedEvent?.some(sub => sub.eventId === eventId);
                if (!client.isInternal && isSubscribed) {
                    client.send(JSON.stringify(data));
                }
            });

            notifyTeamMembers(eventId, senderId, "message_broadcast", {
                message_id: data.message_id,
                eventName: data.event_name,
                message: data.message,
                created_at: data.created_at,
                user: data.user
            }, wss);

            // Internal socket is one-time use
            ws.terminate();
        }
    });
}