export default function disconnectFromEvent(data) {
  console.log(`User ${data.userId} has unsubscribed from ${data.event_id}`);
}