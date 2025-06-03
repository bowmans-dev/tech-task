export default function sendOffer(ws, data, eventScopedConnections, WebSocket, wss) {
  const { offer, eventId, userId } = data.payload;

  const recipients = eventScopedConnections[eventId] || [];

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
            offer,
            fromUserId: userId,
            toUserId: viewerId,
            eventId
          }
        }));
      }
    });
  });
}
