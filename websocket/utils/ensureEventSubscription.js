export function ensureEventSubscription(ws, userId, eventId, eventScopedConnections) {
  if (!eventScopedConnections.has(eventId)) {
    eventScopedConnections.set(eventId, new Set());
  }
  eventScopedConnections.get(eventId).add(userId);
}