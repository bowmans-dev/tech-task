export default function internal(ws, wss, eventScopedConnections, notifyTeamMembers, globalConnectedUsers) {
    ws.isInternal = true;

    ws.on("message", (message) => {
        const data = JSON.parse(message.toString());
        if (data.action === "message_broadcast") {
            const senderId = String(data.user.id);
            const eventId = data.event_id;

            // Ensure the sender's WebSocket is aware of the event
            const senderClient = [...wss.clients].find(c => String(c.userId) === senderId);
            if (senderClient) {
                // Update per-client subscription state
                if (!senderClient.connectedEvent) senderClient.connectedEvent = [];
                if (!senderClient.connectedEvent.some(sub => sub.eventId === eventId)) {
                    senderClient.connectedEvent.push({ userId: senderId, eventId });
                }

                // Update eventScopedConnections map
                if (!eventScopedConnections[eventId]) eventScopedConnections[eventId] = [];
                if (!eventScopedConnections[eventId].includes(senderId)) {
                    eventScopedConnections[eventId].push(senderId);
                }

                // Ensure senderClient.allEventIds includes this event
                if (!senderClient.allEventIds) senderClient.allEventIds = [];
                if (!senderClient.allEventIds.includes(eventId)) {
                    senderClient.allEventIds.push(eventId);
                }

                // Update globalConnectedUsers map for event tracking
                if (globalConnectedUsers[senderId]) {
                    if (!globalConnectedUsers[senderId].allEventIds.includes(eventId)) {
                        globalConnectedUsers[senderId].allEventIds.push(eventId);
                    }
                }
            }

            // Broadcast to all clients currently viewing the event
            wss.clients.forEach((client) => {
                if (!client.isInternal &&
                    client.connectedEvent?.some(sub => sub.eventId === eventId)
                ) {
                    client.send(JSON.stringify(data));
                }
            });

            // Notify online team members not actively viewing the event (for UI badge notification updates)
            notifyTeamMembers(eventId, "message_broadcast", {
                eventId,
                eventName: data.event_name,
                message: data.message,
                user: data?.user
            });

            // Internal connections are one-time use — close after sending
            ws.terminate();
        }
    });
}