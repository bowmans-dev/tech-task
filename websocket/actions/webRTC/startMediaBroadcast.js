export default function startMediaBroadcast(ws, data, eventScopedConnections, wss) {
  const { type, eventId, userId, currentUser } = data.payload;

  const allConnections = eventScopedConnections.get(eventId) ?? new Set();

  allConnections.forEach(clientUserId => {
    if (String(clientUserId) === String(userId)) {
      return;
    }

    ws.send(JSON.stringify({
      action: "send_offer",
      payload: {
        type,
        toUserId: clientUserId,
        eventId,
        broadcastingUserDetails: currentUser,
      }
    }));
  });
}
