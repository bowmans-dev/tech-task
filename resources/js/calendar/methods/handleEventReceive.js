import { state } from "../state";
import { saveCalendarEvent } from "./saveCalendarEvent";

export async function handleEventReceive(info) {
    console.log('[FullCalendar] Event received:', info.event);
                
    document.getElementById('messages').innerHTML = "";
    
    const teamMembersDiv = document.getElementById('team-members');
    teamMembersDiv.innerHTML = '';
    
    const teamMembersLabel = document.createElement('div');
    teamMembersLabel.innerHTML = `<p>Team Members</p>`;
    teamMembersDiv.appendChild(teamMembersLabel);
    
    const filePreview = document.getElementById('file-preview');
    filePreview.innerHTML = '';
    
    const eventOwner = {
        eventOwnerId: info.event.extendedProps.eventOwnerDetails.eventOwnerId,
        profilePicture: info.event.extendedProps.eventOwnerDetails.profilePicture,
        firstName: info.event.extendedProps.eventOwnerDetails.firstName,
        lastName: info.event.extendedProps.eventOwnerDetails.lastName,
    };

    document.getElementById('eventName').value = `${eventOwner.firstName} ${eventOwner.lastName}`;
    
    // Prepare state before saving event
    state.currentEvent = {
        id: null, 
        eventOwnerDetails: eventOwner,
        title: 'New Event',
        date: info.event.startStr,
        time: null,
        allDay: info.event.allDay,
        isNew: true,
        teamMembers: [eventOwner],
    };


    state.teamMembers = [eventOwner];
    state.existingTeamMembers.push(eventOwner);

    const storedId = await saveCalendarEvent();

    info.event.setProp("id", storedId);

    state.currentEvent.id = storedId;

    console.log(`Event successfully saved with ID ${storedId}`);
} 