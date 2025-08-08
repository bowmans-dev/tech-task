export default function forwardIceCandidate(ws, data, eventScopedConnections, wss) {
  const { candidate, toUserId, eventId } = data.payload;

  if (toUserId === ws.userId) {
    return;
  }

  wss.clients.forEach(client => {
    if (
      client.readyState === 1 &&
      client.connectionType !== 'global' && 
      client.userId === toUserId &&        
      eventScopedConnections.get(eventId)?.has(toUserId)
    ) {
      client.send(JSON.stringify({
        action: "ice_candidate",
        payload: {
          candidate,
          fromUserId: ws.userId,
          eventId
        }
      }));
    }
  });
}
