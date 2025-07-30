export default function teamMemberAdded(ws, data, globalConnectedUsers, wss, notifyTeamMembers, WebSocket) {
    const eventId = data.event_id;
    const newMemberId = String(data.newMemberId);
    ws.userId = ws.userId || newMemberId;

    if (!ws.allEventIds) ws.allEventIds = [];
    if (!ws.allEventIds.includes(eventId)) {
        ws.allEventIds.push(eventId);
    }

    if (!ws.connectedEvent) ws.connectedEvent = [];
    if (!ws.connectedEvent.some(sub => sub.eventId === eventId)) {
        ws.connectedEvent.push({ userId: newMemberId, eventId });
    }

    if (!globalConnectedUsers.has(newMemberId)) {
        return;
    }

    // Find the WebSocket connection for the user
    const targetClient = [...wss.clients].find(client =>
        String(client.userId) === newMemberId && client.readyState === WebSocket.OPEN
    );

    if (!targetClient) {
        console.warn(`WebSocket for user ${newMemberId} is not open`);
        return;
    }

    if (!targetClient.allEventIds) targetClient.allEventIds = [];
    if (!targetClient.allEventIds.includes(eventId)) {
        targetClient.allEventIds.push(eventId);
    }

    if (!targetClient.connectedEvent) targetClient.connectedEvent = [];
    if (!targetClient.connectedEvent.some(sub => sub.eventId === eventId)) {
        targetClient.connectedEvent.push({ userId: newMemberId, eventId });
    }

    // notifyTeamMembers(eventId, senderId, actionType)
    notifyTeamMembers(eventId, null, "team_member_added", {
        eventName: data.eventName,
        message: `You were added to ${data.eventName}`,
        user: data.user
    }, wss);
}