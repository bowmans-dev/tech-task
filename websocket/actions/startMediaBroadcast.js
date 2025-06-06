export default function startMediaBroadcast(ws, data, eventScopedConnections, WebSocket, wss) {
  const { type, eventId, userId, currentUser } = data.payload;

  const allConnections = eventScopedConnections[eventId] || [];

  allConnections.forEach(clientUserId => {
    if (String(clientUserId) === String(userId)) {
      return;
    }

    ws.send(JSON.stringify({
      action: "send_offer",
      payload: {
        type: type,
        toUserId: clientUserId,
        eventId,
        broadcastingUserDetails: currentUser,
      }
    }));
  });
}
