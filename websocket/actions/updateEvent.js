import { getEventId } from "../utils/getEventId.js";
import { hasEventAccess } from "../utils/hasEventAccess.js";

export default function updateEvent(ws, jsonData, eventScopedConnections, wss) {
  const eventId = getEventId(jsonData);
  if (!hasEventAccess(ws, eventId)) {
    return console.warn(`User ${ws.userId} not authorized to update event ${eventId}`);
  }

  if (!eventScopedConnections.has(eventId)) {
    return console.warn(`No subscriptions found for event ${eventId}`);
  }

  wss.clients.forEach(client => {
    if (client.readyState === 1 && client.connectedEvent?.some(sub => sub.eventId === eventId)) {
      client.send(JSON.stringify({
        action: "update_event",
        event_id: eventId,
        teamMembers: jsonData.teamMembers,
        files: jsonData.files
      }));
    }
  });
}
