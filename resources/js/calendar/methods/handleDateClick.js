import { state, getCurrentUser } from "../state";
export async function handleDateClick(info) {

  // Reset the state for the new event and get current user
  state.teamMembers = []; // transient team members state (to add to database)
  state.existingTeamMembers = []; // existing team members state (already in database)
  state.currentEvent.teamMembers = []; // total list (unsaved) and existing (database) team members


  const eventOwner = getCurrentUser();


  state.currentEvent = {
    id: null, 
    eventOwnerId: eventOwner.userId,
    eventOwnerDetails: eventOwner,
    title: 'New Event',
    date: info.dateStr,
    time: null,
    allDay: info.allDay,
    isNew: true,
    teamMembers: [eventOwner],
  };
  state.teamMembers = [eventOwner];
  state.existingTeamMembers.push(eventOwner);
  

  const storedId = await saveCalendarEvent();
  

  calendar.addEvent({
    id: storedId,
    title: 'New Event',
    start: info.date,
    allDay: info.allDay,
    extendedProps: {
        eventOwnerId: eventOwner.userId,
        eventOwnerDetails: eventOwner,
        team_members: [
            {
                userId: Number(eventOwner.userId),
                profilePicture: eventOwner.profilePicture,
                firstName: eventOwner.firstName,
                lastName: eventOwner.lastName,
            }
        ],
        files: []
    }
  });

}