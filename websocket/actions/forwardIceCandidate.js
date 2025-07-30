export default function forwardIceCandidate(ws, data, eventScopedConnections, WebSocket, wss) {
  const { candidate, toUserId, eventId } = data.payload;

  if (toUserId === ws.userId) {
    return;
  }

  wss.clients.forEach(client => {
    if (
      client.readyState === WebSocket.OPEN &&
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
