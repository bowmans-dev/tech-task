export function hasEventAccess(ws, eventId) {
  return ws.allEventIds?.includes(eventId);
}