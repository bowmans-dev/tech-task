import { showTeamMemberNotification } from '../utils/notifications.js';

export function handleTeamMemberAdded(payload) {
  window.calendar.refetchEvents();

  if (!payload) return;
  
  setTimeout(() => {
    showTeamMemberNotification(payload);
    const event = window.calendar.getEventById(payload.eventId);
    if (event) {
      event.setProp("backgroundColor", "#2D89EF");
    }
  }, 2000);
}
