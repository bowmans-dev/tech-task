import { getUserId } from "../utils/getUserId.js";
import { getEventId } from "../utils/getEventId.js";
import { addEventSubscription } from "../utils/addEventSubscription.js";
import { ensureEventSubscription } from "../utils/ensureEventSubscription.js";

export default function subscribe(ws, jsonData, eventScopedConnections) {
  const userId = getUserId(ws);
  const eventId = getEventId(jsonData);
  if (!userId || !eventId) return console.warn("Invalid subscribe request");

  ws.connectionType = 'event';
  addEventSubscription(ws, userId, eventId);
  ensureEventSubscription(ws, userId, eventId, eventScopedConnections);

  const user = (jsonData.existingTeamMembers ?? []).find(u => String(u.userId) === userId);
  if (!user) return console.warn(`User ${userId} not authorized for event ${eventId}`);

  ws.userDetails ??= {};
  ws.userDetails[userId] = {
    id: user.userId,
    first_name: user.firstName,
    last_name: user.lastName,
    profile_picture: user.profilePicture,
  };

  if (jsonData.currentEvent) {
    const { title, date } = jsonData.currentEvent;
    ws.eventDetails ??= {};
    ws.eventDetails[eventId] = { title: title || `Event ${eventId}`, date: date || null };
  }
}
