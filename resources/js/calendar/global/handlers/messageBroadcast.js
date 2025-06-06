import { showMessageNotification } from '../utils/notifications.js';

export function handleMessageBroadcast(payload) {
  if (!payload) return;

  showMessageNotification(payload);

  const event = window.calendar.getEventById(payload.eventId);
  if (event) {
    event.setProp("backgroundColor", "#2D89EF");
  }
}