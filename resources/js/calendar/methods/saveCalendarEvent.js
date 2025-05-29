import { state } from "../state";
// Function to debounce API calls
function debounce(func, delay) {
    let timeout;
    return function (...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func(...args), delay);
    };
}

export async function saveCalendarEvent() {
  const formData = new FormData();
  const dropZone = document.getElementById('drop-zone'); 
  const calendarWrapper = document.getElementById('calendar-wrapper'); 

  let eventName = document.getElementById('eventName').value.trim();
  if (!eventName) {
    eventName = "New Event";
  }

  let id = state.currentEvent.id;

  let event = calendar.getEventById(id);


  // Get user info from the calendar wrapper.
  console.log(event);
  let userId = calendarWrapper?.getAttribute('data-user-id') ?? event.extendedProps.eventOwnerId;
  const profilePicture = calendarWrapper?.getAttribute('data-profile-picture') ?? event.extendedProps.eventOwnerDetails.profilePicture;
  const firstName = calendarWrapper?.getAttribute('data-first-name') ?? event.extendedProps.eventOwnerDetails.firstName;
  const lastName = calendarWrapper?.getAttribute('data-last-name') ?? event.extendedProps.eventOwnerDetails.lastName;
  
  let date;
  
  if (event) {
    date = event.start.toISOString().split("T")[0];
  } else {
    date = state.currentEvent.date;
  }

  const time = document.getElementById('modal-event-time').value.trim();
  const allDay = time === '';

  const eventIdStr = allDay ? date : `${date}T${time}`;

  // Append all event information into formData.
  formData.append('event_name', eventName);
  formData.append('user_id', userId);
  formData.append('profile_picture', profilePicture);
  formData.append('first_name', firstName);
  formData.append('last_name', lastName);
  formData.append('date', date);
  formData.append('time', time);
  formData.append('allDay', allDay);
  formData.append('team_members', JSON.stringify(state.teamMembers));

  // Pass the event ID only if it exists (for event update).
  if (id) {
    formData.append('id', id);
  }

  // Append any dropped files.
  state.droppedFiles.forEach(file => {
    const customFileName = `${eventIdStr}/${userId}/${file.name}`;
    formData.append('files[]', file, customFileName);
  });

  try {
    const response = await fetch('/calendar/events/save', {
      method: "POST",
      body: formData
    });
    const data = await response.json();

    // If this is a new event, get ID from the backend:
    if (!id && data.event && data.event.id) {
      
      dropZone.setAttribute('data-event-id', data.event.id);
      console.log("Event ID saved for future updates:", data.event.id);

      // Update global state with the new ID.
      state.currentEvent.id = data.event.id;
      console.log("Updated current event ID:", state.currentEvent.id);

      state.droppedFiles = [];
      state.teamMembers = [];

      return data.event.id; // Return the new ID.
    }
    // Return the existing id if this was an update.
    return id;
  } catch (error) {
    console.error('Error saving event:', error);
    throw error;
  }
}

// Function to set up auto-saving triggers
function setupAutoSave() {
    document.getElementById('eventName').addEventListener('input', debounce(saveCalendarEvent, 1000));
    document.getElementById('modal-event-time').addEventListener('change', saveCalendarEvent);
}

// Ensure auto-save activates when the page loads
document.addEventListener("DOMContentLoaded", setupAutoSave);