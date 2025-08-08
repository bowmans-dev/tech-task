import notifyTeamMembers from "../utils/notifyTeamMembers.js";
import { findClientByUserId } from "../utils/findClientByUserId.js";
import { getEventId } from "../utils/getEventId.js";
import { addEventSubscription } from "../utils/addEventSubscription.js";
import { ensureEventSubscription } from "../utils/ensureEventSubscription.js";

export default function teamMemberAdded(ws, data, globalConnectedUsers, wss) {
  const eventId = getEventId(data);
  const newMemberId = String(data.newMemberId);
  ws.userId ||= newMemberId;

  addEventSubscription(ws, newMemberId, eventId);
  ensureEventSubscription(ws, newMemberId, eventId, globalConnectedUsers);

  if (!globalConnectedUsers.has(newMemberId)) return;

  const target = findClientByUserId(wss, newMemberId);
  if (!target) return console.warn(`User ${newMemberId} socket not open`);

  addEventSubscription(target, newMemberId, eventId);
  ensureEventSubscription(target, newMemberId, eventId, globalConnectedUsers);

  notifyTeamMembers(eventId, null, "team_member_added", {
    eventName: data.eventName,
    message: `You were added to ${data.eventName}`,
    user: data.user
  }, wss);
}