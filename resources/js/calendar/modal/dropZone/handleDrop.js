import { state } from "../../state";
import { isUserAlreadyInTeam } from "../../state";
import { isUserInDatabase } from "../../state";
import { addTeamMember } from "../../state";
import { removeTeamMember } from "../../state";
import { removeTeamMemberFromCalendarEvent } from "./teamMembers/removeTeamMemberFromCalendarEvent";
import { sendEventUpdate } from "../../state";

export function handleDrop(event) { // Clear the dropped files array
    event.preventDefault(); // Prevent default browser behavior
    event.stopPropagation(); // Stop the event from bubbling up

    const rawData = event.dataTransfer.getData('text/plain'); // Dragged user data
    const files = Array.from(event.dataTransfer.files); // Dragged files

    // Handle dropped users
    if (rawData) {
        try {
            const data = JSON.parse(rawData);
            const { userId, profilePicture, firstName, lastName } = data;
            
            const teamMembersDiv = document.getElementById('team-members');

            // Check if the user is already in the DBs stored existing members
            if (isUserInDatabase(userId)) {
                alert(`${firstName} ${lastName} is already in the list.`);
                return;
            }

            // Check if the user is already in the teamMembers array
            if (isUserAlreadyInTeam(userId)) {
                alert(`${firstName} ${lastName} is already in the list.`);
                return;
            }

            // Check if the user is already added to the teamMembers array

            const userDiv = document.createElement('div');
                userDiv.className = 'team-members relative flex items-center mb-2 mt-2 border border-gray-900/25 rounded-full p-1';
                userDiv.setAttribute('data-user-id', userId);
                userDiv.innerHTML = `
                    <a href="/users/${userId}" class="cursor-pointer flex items-center">
                        <img src="${profilePicture}" 
                            alt="${firstName} ${lastName}" 
                            class="w-8 h-8 rounded-full object-cover mr-2">
                        <span class="text-sm font-medium text-gray-700">${firstName} ${lastName}</span>
                    </a>
                    <button class="absolute cursor-pointer top-2 right-4 text-gray-500 hover:text-gray-700 z-50" aria-label="Close Modal">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 z-50" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                `;
            
            // Add the user to the teamMembers div
            teamMembersDiv.appendChild(userDiv);

            // Add the user to the teamMembers array
            addTeamMember(data);
            
            // Save the calendar event after adding the user
            saveCalendarEvent();

            sendEventUpdate();


            const eventId = state.currentEvent.id; 

            // Add a click event listener to the user div buttom to remove team member from dropzone
            const removeButton = userDiv.querySelector('button');
            removeButton.addEventListener('click', () => {
                // Access the closest userDiv and the userId from its data attribute
                const userId = userDiv.getAttribute('data-user-id');

                // Remove the userDiv from the DOM
                userDiv.remove();

                // Update the teamMembers array in state
                removeTeamMember(userId);

                // Send request to remove team member from the event in the database
                removeTeamMemberFromCalendarEvent(eventId, userId)

            });
            
        } catch (error) {
            console.error("Error parsing user data:", error);
        }
    }

    const eventId = state.currentEvent.id;
    const calendarEvent = calendar.getEventById(eventId);

    if (!calendarEvent) {
        console.error(`Event not found: ${eventId}`);
        return;
    }

    // Ensure `extendedProps.files` exists
    if (!calendarEvent.extendedProps.files) {
        calendarEvent.extendedProps.files = [];
    }

    // Handle dropped files
    if (files.length > 0) {

        files.forEach(file => {
            // Check if the file is already added to the droppedFiles array
            if (!state.droppedFiles.some(f => f.name === file.name && f.size === file.size)) {
                // Append the file to the droppedFiles array
                state.droppedFiles.push(file);

                const fileData = {
                    eventId: eventId,
                    userId: state.currentEvent.eventOwnerId,
                    fileType: file.type,
                    fileSize: (file.size / 1024).toFixed(1), // Convert size to KB
                    file_name: file.name,
                    file_path: `events/${eventId}/${state.currentEvent.eventOwnerId}/${file.name}`, 
                };

                calendarEvent.extendedProps.files.push(fileData);

            } else {
                console.log("File already exists in droppedFiles:", file.name);
            }
        });
        state.teamMembers = [];
        saveCalendarEvent();
        sendEventUpdate();
        state.droppedFiles = [];
    }

}