export function addEventSubscription(client, userId, eventId) {
  client.allEventIds ??= [];
  if (!client.allEventIds.includes(eventId)) client.allEventIds.push(eventId);

  client.connectedEvent ??= [];
  if (!client.connectedEvent.some(sub => sub.eventId === eventId)) {
    client.connectedEvent.push({ userId, eventId });
  }
}