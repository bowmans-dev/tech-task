import { state } from "./state";
export async function handleDateClick(info) {

  // 1. Get user info from the calendar wrapper
  const calendarWrapper = document.getElementById('calendar-wrapper');
  let userId = calendarWrapper.getAttribute('data-user-id');
  const profilePicture = calendarWrapper.getAttribute('data-profile-picture');
  const firstName = calendarWrapper.getAttribute('data-first-name');
  const lastName = calendarWrapper.getAttribute('data-last-name');

  const eventOwner = { userId, profilePicture, firstName, lastName };

  // 2. Update your global state for the new event
  state.currentEvent = {
    id: null,           // Will be updated when the event is saved
    title: 'New Event',
    date: info.dateStr,
    time: null,
    allDay: info.allDay,
    isNew: true,
    teamMembers: [eventOwner],
  };
  state.teamMembers = [eventOwner];

  // 3. Reset team members and file preview containers
  const teamMembersDiv = document.getElementById('team-members');
  teamMembersDiv.innerHTML = '';
  const teamMembersLabel = document.createElement('div');
  teamMembersLabel.innerHTML = `<p>Team Members</p>`;
  teamMembersDiv.appendChild(teamMembersLabel);

  const filePreview = document.getElementById('file-preview');
  filePreview.innerHTML = '';
  

  // 4. Update modal elements with user info
  document.getElementById('modal-profile-picture').src = `/storage/${profilePicture}`;
  document.getElementById('modal-user-name').innerText = `${firstName} ${lastName}`;

  // 5. Update modal dataset attributes
  const modal = document.getElementById('event-modal');
  modal.dataset.date = info.dateStr;
  modal.dataset.userId = userId;

  modal.classList.add('hidden');

  
  // 6. Trigger auto-save of the event
  const storedId = await saveCalendarEvent();
  
  // 7. Add event to calendar
  let event = calendar.addEvent({
    id: storedId,
    title: 'New Event',
    start: info.date,
    allDay: info.allDay
  });

  // 8. Setup event name input field
  const eventNameInput = document.getElementById('eventName');
  eventNameInput.oninput = null;
  eventNameInput.value = 'New Event';
  eventNameInput.oninput = function () {
    event.setProp('title', this.value);
  };

  window.location.reload();
}