import { fetchMessagesForEvent } from '../modal/messages/fetchMessagesForEvent';
import { state, currentUser, connectToEventWebSocket } from '../state';
import { showEventModal } from '../modal/actions/showEventModal';

// Update the global state (state.currentEvent) from the existing saved fullcalendar (db) event.
function updateCurrentEvent(event) {

  // Give admin global delete permissions
  if (currentUser.userId == "admin") {
    const deleteButton = document.querySelector('.delete-event-button');
    deleteButton.setAttribute('data-user-id', event.extendedProps.eventOwnerDetails.eventOwnerId);
  }

  // Set the event's background color to null if it was highlighted by notification
  event.setProp("backgroundColor", null);
  return {
    id: event.id,
    eventOwnerDetails: event.extendedProps.eventOwnerDetails || {},
    title: event.title,
    date: event.startStr.split('T')[0],
    time: event.extendedProps.time,
    allDay: event.allDay,
    files: event.extendedProps.files || [],
    teamMembers: event.extendedProps.team_members,
    isNew: false
  };
}


// update current event state, fetch messages, display event modal, and set up Websocket connection.
export async function handleEventClick(info) {

  const event = info.event;
  const eventId = event.id;
  
  state.existingTeamMembers = event.extendedProps.team_members || [];
  
  state.currentEvent = updateCurrentEvent(event);

  await fetchMessagesForEvent(eventId);

  const modal = showEventModal();

  modal.show(state.currentEvent);

  connectToEventWebSocket();
}