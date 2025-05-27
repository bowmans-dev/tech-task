import { state } from "../../../state";
import { isUserAlreadyInTeam } from "../../../state";
import { isUserInDatabase } from "../../../state";
import { addTeamMember } from "../../../state";
import { removeTeamMember } from "../../../state";
import { removeTeamMemberFromCalendarEvent } from "./removeTeamMemberFromCalendarEvent";
import { sendEventUpdate } from "../../../state";
import { notifyNewTeamMembers } from "../../../state";

export function clickAddUserToDropZone(user) {

    const teamMembersDiv = document.getElementById('team-members');
    
    // Check if the user is already in the DBs stored existing members
    if (isUserInDatabase(user.userId)) {
        alert(`${user.firstName} ${user.lastName} is already in the list.`);
        return;
    }

    // Check if the user is already in the teamMembers array
    if (isUserAlreadyInTeam(user.userId)) {
        alert(`${user.firstName} ${user.lastName} is already in the list.`);
        return;
    }

    const userDiv = document.createElement('div');
        userDiv.className = 'team-members relative flex items-center mb-2 mt-2 border border-gray-900/25 rounded-full p-1';
        userDiv.setAttribute('data-user-id', user.userId);
        userDiv.innerHTML = `
            <a href="/users/${user.userId}" class="cursor-pointer flex items-center">
                <img src="${user.profilePicture}" 
                    alt="${user.firstName} ${user.lastName}" 
                    class="w-8 h-8 rounded-full object-cover mr-2">
                <span class="text-sm font-medium text-gray-700">${user.firstName} ${user.lastName}</span>
            </a>
            <button
                class="absolute cursor-pointer top-2 right-4 text-gray-500 hover:text-gray-700 z-50" aria-label="Close Modal">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 z-50" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        `;
        
    // Add the user to the teamMembers div
    teamMembersDiv.appendChild(userDiv);

    // Add the user to the teamMembers array
    addTeamMember(user);

    // Save the calendar event after adding the user
    saveCalendarEvent();

    if (state.teamMembers.length > 0) {
        notifyNewTeamMembers();
    }
    state.teamMembers = [];

    
    sendEventUpdate();


    const eventId = state.currentEvent.id;
    
    let userId = userDiv.getAttribute('data-user-id');

    // Add a click event listener to the user div buttom to remove team member from drop zone
    const removeButton = userDiv.querySelector('button');
    removeButton.addEventListener('click', () => {

        // Remove the userDiv from the DOM
        userDiv.remove(userId);

        // Update the teamMembers array in state
        removeTeamMember(userId);
        
        // Send request to remove team member from the event in the database
        removeTeamMemberFromCalendarEvent(eventId, userId)
    }); 
}