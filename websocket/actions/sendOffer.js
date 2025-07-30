export default function sendOffer(ws, data, eventScopedConnections, WebSocket, wss) {
  const { type, offer, eventId, userId, broadcastingUserDetails } = data.payload;

  const recipients = eventScopedConnections.get(eventId) ?? new Set();

  recipients.forEach(viewerId => {
    if (viewerId === userId) return;

    wss.clients.forEach(client => {
      if (
        client.readyState === WebSocket.OPEN &&
        client.userId === viewerId &&
        client.connectionType === 'event'
      ) {
        client.send(JSON.stringify({
          action: "receive_offer",
          payload: {
            type,
            offer,
            fromUserId: userId,
            toUserId: viewerId,
            broadcastingUserDetails,
            eventId
          }
        }));
      }
    });
  });
}
