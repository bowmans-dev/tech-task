export async function fetchUserEventIds() {
  const response = await fetch("/calendar/events");
  const events = await response.json();
  return events.map(event => event.id);
}