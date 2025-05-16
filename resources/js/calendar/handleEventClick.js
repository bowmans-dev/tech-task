import { fetchMessagesForEvent } from './modal/messages/fetchMessagesForEvent';
import { state, getCurrentUser, connectToEventWebSocket } from './state';

// Update the global state (state.currentEvent) from the existing saved fullcalendar (db) event.
function updateCurrentEvent(event) {

  let currentTeamMembers = event.extendedProps.team_members || [];

  if (event.id == null || state.currentEvent.isNew) {
    let currentUser = getCurrentUser();
    let currentUserId = currentUser.userId;

    if (!state.teamMembers.some(member => member.userId === currentUserId) && !state.existingTeamMembers.some(member => member.userId === currentUserId)) {

      // Only add current user if there are no existing team members
      if (currentTeamMembers.length === 0 && currentUserId !== "admin") {
        currentTeamMembers.push(currentUser);
      }
    }
  }

  return {
    id: event.id,
    title: event.title,
    date: event.startStr.split('T')[0],
    time: event.extendedProps.time,
    allDay: event.allDay,
    files: event.extendedProps.files || [],
    teamMembers: currentTeamMembers,
    isNew: false
  };
}

// Sets the event ID on the drop zone element
function setDropZoneEventId(eventId) {
  const dropZone = document.getElementById('drop-zone');
  dropZone.setAttribute('data-event-id', eventId);
}

// Updates a hidden input field with the event ID
function updateModalHiddenFields(eventId) {
  const messageFormEventIdInput = document.getElementById('event-id');
  messageFormEventIdInput.value = eventId;
}

// Updates the modal UI with the event's user information obtained from the event
function updateModalUserInfo(user) {
  let profilePicture = user.profile_picture;

  if (!profilePicture) {
    profilePicture = '/storage/default_profile_image.png';
  } else if (!profilePicture.startsWith('http')) {
    profilePicture = `/storage/${profilePicture}`;
  }
  
  document.getElementById('modal-profile-picture').src = profilePicture;
  document.getElementById('modal-user-name').textContent =
    user.first_name + " " + user.last_name;
}

// Updates the modal UI with user info from the calendar wrapper
function updateModalUserInfoFallback() {
  const calendarWrapper = document.getElementById('calendar-wrapper');
  if (calendarWrapper) {
    const userId = calendarWrapper.getAttribute('data-user-id');
    const firstName = calendarWrapper.getAttribute('data-first-name');
    const lastName = calendarWrapper.getAttribute('data-last-name');
    let profilePicture = calendarWrapper.getAttribute('data-profile-picture');

    if (!profilePicture) {
      profilePicture = '/storage/default_profile_image.png';
    } else if (!profilePicture.startsWith('http')) {
      profilePicture = `/storage/${profilePicture}`;
    }

    document.getElementById('modal-profile-picture').src = profilePicture;
    document.getElementById('modal-user-name').textContent = firstName + " " + lastName;
    return userId;
  }
}

// Smoothly scrolls the modal into view
function scrollModalIntoView() {
  const targetElement = document.querySelector('#event-modal');
  if (targetElement) {
    targetElement.scrollIntoView({ behavior: 'smooth' });
  }
}

// Main Function

// update current event state, update the UI, fetch messages, scroll the modal into view, and set up Websocket connection.
export async function handleEventClick(info) {
  const event = info.event;

  console.groupCollapsed(`🔍[EVENT CLICK] Handling click for event ID: ${event.id}`);

  // 1. Parse current user and event data
  const currentUserId = document.getElementById('calendar-wrapper')?.getAttribute('data-user-id');
  console.log("👤Current User ID (from DOM):", currentUserId);

  const rawEventData = {
    id: event.id,
    title: event.title,
    startStr: event.startStr,
    extendedProps: event.extendedProps,
  };
  console.log("📦Raw Event Data:", rawEventData);

  // 2. Update the global state
  if (event.id) {
    state.currentEvent = updateCurrentEvent(event);
  }

  console.log("🌍🗓️ Updated Global State (state.currentEvent):", state.currentEvent);

  const eventId = state.currentEvent.id;

  // 3. Set up DOM fields
  setDropZoneEventId(eventId);
  updateModalHiddenFields(eventId);

  // 4. Render team members section
  const teamMembersDiv = document.getElementById('team-members');
  teamMembersDiv.innerHTML = '';
  const teamMembersLabel = document.createElement('div');
  teamMembersLabel.innerHTML = `<p>Team Members</p>`;
  teamMembersDiv.appendChild(teamMembersLabel);

  const user = event.extendedProps.user || {};
  let userId = event.extendedProps.user_id;
  const title = event.title;
  const date = event.startStr.split('T')[0];
  const time = event.extendedProps.time;
  const files = event.extendedProps.files || [];
  const teamMembers = event.extendedProps.team_members || [];

  // 5. Fetch messages for this event
  await fetchMessagesForEvent(eventId);

  // 6. Update modal user info (fallback if needed)
  if (user && user.first_name && user.last_name) {
    updateModalUserInfo(user);
    console.log("🧑‍🏫 Modal user updated using event user data.");
  } else {
    console.warn("Event user data missing or incomplete. Falling back to DOM user data.");
    userId = updateModalUserInfoFallback();
  }
  
  if (!userId) {
    const eventModal = document.getElementById('event-modal');
    if (eventModal) {
        userId = eventModal.getAttribute('data-user-id');
    } else {
        console.warn("Element with ID 'event-modal' not found.");
    }
  }
  
  // 7. Update modal UI with event details
  showModalWithEvent({
    eventId,
    title,
    userId,
    date,
    time,
    files,
    teamMembers
  });

  console.log("🗓️📝Modal populated with event details.");

  // 8. Scroll modal into view
  scrollModalIntoView();

  // 9. WebSocket connection
  console.log("Attempting WebSocket subscription:");
  console.log("Subscribing with userId:", currentUserId, "eventId:", eventId);

  connectToEventWebSocket();

  console.groupEnd();
}
