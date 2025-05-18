import { state, getCurrentUser, renderTeamMembers } from "./state";
export async function handleDateClick(info) {

  // 1. Reset the state for the new event and get current user
  state.currentEvent.teamMembers = [];
  state.existingTeamMembers = [];
  
  const eventOwner = getCurrentUser();

  // 2. Update your global state for the new event
  state.currentEvent = {
    id: null, 
    eventOwnerId: eventOwner.userId,
    title: 'New Event',
    date: info.dateStr,
    time: null,
    allDay: info.allDay,
    isNew: true,
    teamMembers: [eventOwner],
  };
  state.teamMembers = [eventOwner];
  state.existingTeamMembers.push(eventOwner);

  // 3. Reset team members and file preview containers
  const teamMembersDiv = document.getElementById('team-members');
  teamMembersDiv.innerHTML = '';
  const teamMembersLabel = document.createElement('div');
  teamMembersLabel.innerHTML = `<p>Team Members</p>`;
  teamMembersDiv.appendChild(teamMembersLabel);

  renderTeamMembers();

  const filePreview = document.getElementById('file-preview');
  filePreview.innerHTML = '';;

  // 4. Update modal dataset attributes
  const modal = document.getElementById('event-modal');
  modal.dataset.date = info.dateStr;
  modal.dataset.userId = eventOwner.userId;

  modal.classList.add('hidden');

  
  // 5. Trigger auto-save of the event
  const storedId = await saveCalendarEvent();
  
  // 6. Add event to calendar
  let event = calendar.addEvent({
    id: storedId,
    title: 'New Event',
    start: info.date,
    allDay: info.allDay,
    extendedProps: {
        userId: eventOwner.userId,
        team_members: [
            {
                userId: Number(eventOwner.userId),
                profilePicture: eventOwner.profilePicture,
                firstName: eventOwner.firstName,
                lastName: eventOwner.lastName,
                debug: "test"
            }
        ],
        files: []
    }
  });

  // 7. Setup event name input field
  const eventNameInput = document.getElementById('eventName');
  eventNameInput.oninput = null;
  eventNameInput.value = 'New Event';
  eventNameInput.oninput = function () {
    event.setProp('title', this.value);
  };

  state.teamMembers = [];
}