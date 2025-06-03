export default function subscribe(ws, jsonData, eventScopedConnections) {
    
    const userId = String(jsonData.userId);
    const eventId = String(jsonData.event_id);
    
    if (!userId || !eventId) {
        console.warn("Invalid subscribe request — missing userId or eventId");
        return;
    }
    
    ws.connectionType = 'event';
    ws.userId = userId;

    if (!ws.allEventIds) ws.allEventIds = [];
    if (!ws.allEventIds.includes(eventId)) {
        ws.allEventIds.push(eventId);
    }

    ws.connectedEvent.push({ userId, eventId });
    if (!eventScopedConnections[eventId]) eventScopedConnections[eventId] = [];
    if (!eventScopedConnections[eventId].includes(userId)) eventScopedConnections[eventId].push(userId);

    const allUsers = jsonData.existingTeamMembers || [];
    const user = allUsers.find(u => String(u.userId) === userId);

    if (!ws.userDetails) ws.userDetails = {};
    if (!ws.eventDetails) ws.eventDetails = {};

    if (user) {
        ws.userDetails[userId] = {
            id: user.userId,
            first_name: user.firstName,
            last_name: user.lastName,
            profile_picture: user.profilePicture
        };
    }

    if (jsonData.currentEvent) {
        const { title, date } = jsonData.currentEvent;
        ws.eventDetails[eventId] = {
            title: title || `Event ${eventId}`,
            date: date || null,
        };
    }
}
