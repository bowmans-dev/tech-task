import { getEventId } from "../utils/getEventId.js";

export default function unsubscribe(ws, jsonData, eventScopedConnections, wss) {
  const userId = String(jsonData.userId);
  const eventId = String(getEventId(jsonData));

  const usersSet = eventScopedConnections.get(eventId);
  if (usersSet?.delete(userId) && usersSet.size === 0) {
    eventScopedConnections.delete(eventId);
  }
  
  wss.clients.forEach(client => {
    if (
      client.connectedEvent?.some(sub => {
        const match = sub.userId === userId && sub.eventId === eventId;
        return match;
      })
    ) {
      client.send(JSON.stringify({ action: "disconnect_from_event", userId, event_id: eventId }));
      client.connectedEvent = client.connectedEvent.filter(sub =>
        sub.userId !== userId || sub.eventId !== eventId
      );
      if (client.connectedEvent.length === 0) client.terminate();
    }
  });
}
