export default function sendAnswer(ws, data, eventScopedConnections, WebSocket, wss) {
  const { answer, toUserId, eventId } = data.payload;

  wss.clients.forEach(client => {
    if (
      client.readyState === WebSocket.OPEN &&
      client.userId === toUserId &&
      eventScopedConnections.get(eventId)?.has(toUserId)
    ) {
      client.send(JSON.stringify({
        action: "receive_answer",
        payload: {
          answer,
          fromUserId: ws.userId,
          eventId
        }
      }));
    }
  });
}
