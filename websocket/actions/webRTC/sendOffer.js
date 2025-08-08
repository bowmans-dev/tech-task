export default function sendOffer(ws, data, eventScopedConnections, wss) {
  const { type, offer, eventId, broadcastingUserDetails } = data.payload;

  const recipients = eventScopedConnections.get(eventId) ?? new Set();

  recipients.forEach(viewerId => {
    if (viewerId === ws.userId) return;

    wss.clients.forEach(client => {
      if (
        client.readyState === 1 &&
        client.userId === viewerId &&
        client.connectionType === 'event'
      ) {
        client.send(JSON.stringify({
          action: "receive_offer",
          payload: {
            type,
            offer,
            fromUserId: ws.userId,
            toUserId: viewerId,
            broadcastingUserDetails,
            eventId
          }
        }));
      }
    });
  });
}
