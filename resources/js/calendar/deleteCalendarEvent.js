import { state } from "./state";
export function deleteCalendarEvent() {

  const dropZone = document.getElementById('drop-zone');
  const eventId = dropZone.getAttribute('data-event-id');

  if (!eventId) {
    alert("No event selected.");
    return;
  }

  const deleteButton = document.querySelector('.delete-event-button');
  const currentUserId = deleteButton.getAttribute('data-user-id');

  if (!confirm("Are you sure you want to delete this event?")) {
    return;
  }

  const csrfToken = document.querySelector('input[name="_token"]').value;

  fetch(`/calendar/events/${eventId}/delete`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': csrfToken
    },
    body: JSON.stringify({
      currentUserId: currentUserId
    })
  })
    .then(response => response.json())
    .then(data => {
      if (data.success) {

        alert("Event deleted successfully!");

        const modal = document.getElementById('event-modal');
        modal.classList.add('hidden');

        const eventToRemove = calendar.getEventById(eventId);
        if (eventToRemove) {
          eventToRemove.remove();
        }
        state.teamMembers = [];
        state.existingTeamMembers = [];
        state.currentEvent.teamMembers = [];
      } else {
        alert(data.message || "You are not authorized to delete this event.");
      }
    })
    .catch(error => {
      console.error("Error deleting event:", error);
      alert("An error occurred while trying to delete the event.");
    });
}