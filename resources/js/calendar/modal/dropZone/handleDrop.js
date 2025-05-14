import { state } from "../../state";
import { isUserAlreadyInTeam } from "../../state";
import { isUserInDatabase } from "../../state";
import { addTeamMember } from "../../state";
import { removeTeamMember } from "../../state";
import { unsubscribeUserFromEvent } from "../../state";
import { removeTeamMemberFromCalendarEvent } from "../../removeTeamMemberFromCalendarEvent";

export function handleDrop(event) {
    event.preventDefault(); // Prevent default browser behavior
    event.stopPropagation(); // Stop the event from bubbling up

    const rawData = event.dataTransfer.getData('text/plain'); // Dragged user data
    const files = Array.from(event.dataTransfer.files); // Dragged files

    console.log("existingTeamMembers: ", state.existingTeamMembers);

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
                
                // Inform the server via WebSocket that this team member should be unsubscribed.
                unsubscribeUserFromEvent(userId);

                // Send request to remove team member from the event in the database
                removeTeamMemberFromCalendarEvent(eventId, userId)

            });



            console.log("User added to teamMembers:", state.teamMembers);
            
        } catch (error) {
            console.error("Error parsing user data:", error);
        }
    }

    // Handle dropped files
    if (files.length > 0) {
        const previewContainer = document.getElementById('file-preview');

        files.forEach(file => {
            // Check if the file is already added to the droppedFiles array
            if (!state.droppedFiles.some(f => f.name === file.name && f.size === file.size)) {
                // Append the file to the droppedFiles array
                state.droppedFiles.push(file);

                // Update the UI
                const fileType = file.type;
                const fileName = file.name;
                const fileSize = (file.size / 1024).toFixed(1); // File size in KB

                const fileDiv = document.createElement('div');
                fileDiv.className = 'p-3 rounded bg-gray-100 flex items-center space-x-3';

                if (fileType.startsWith('image/')) {
                    const img = document.createElement('img');
                    img.className = 'w-12 h-12 object-cover rounded';
                    img.src = URL.createObjectURL(file);
                    fileDiv.appendChild(img);
                } else {
                    const icon = document.createElement('div');
                    icon.className = 'w-12 h-12 bg-gray-300 rounded flex items-center justify-center text-gray-700 font-bold';
                    icon.textContent = fileType.split('/')[1]?.toUpperCase() || 'FILE';
                    fileDiv.appendChild(icon);
                }

                const info = document.createElement('div');
                info.innerHTML = `
                    <p class="text-sm font-medium text-gray-700 max-w-[200px] truncate">${fileName}</p>
                    <p class="text-xs text-gray-500">${fileType || 'Unknown type'} · ${fileSize} KB</p>
                `;

                fileDiv.appendChild(info);
                previewContainer.appendChild(fileDiv);

                console.log("File added to droppedFiles:", { fileName, fileType, fileSize });
            } else {
                console.log("File already exists in droppedFiles:", file.name);
            }
        });
        saveCalendarEvent();
        state.droppedFiles = [];
    }

}