import { state, currentUser } from "../../state";
import { isUserAlreadyInTeam } from "../../state";
import { isUserInDatabase } from "../../state";
import { addTeamMember } from "../../state";
import { notifyNewTeamMembers } from "../../state";
import { sendEventUpdate } from "../../state";
import { saveCalendarEvent } from "../../methods/saveCalendarEvent";
import { renderTeamMembers } from "./teamMembers/renderTeamMembers";

export function handleDrop(event) {
    event.preventDefault();
    event.stopPropagation();
    
    const eventId = state.currentEvent.id; 
    const rawData = event.dataTransfer.getData('text/plain'); // Dragged user data
    const files = Array.from(event.dataTransfer.files); // Dragged files

    // Handle dropped users
    if (rawData) {
        try {
            const data = JSON.parse(rawData);
            const { userId, firstName, lastName } = data;

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

            renderTeamMembers(eventId, state.teamMembers)

            addTeamMember(data);
            
            saveCalendarEvent();

            if (state.teamMembers.length > 0) {
                notifyNewTeamMembers();
            }
            state.teamMembers = [];

            sendEventUpdate();
            
        } catch (error) {
            console.error("Error parsing user data:", error);
        }
    }

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
                    userId: currentUser.userId,
                    fileType: file.type,
                    fileSize: (file.size / 1024).toFixed(1), // Convert size to KB
                    file_name: file.name,
                    file_path: `events/${eventId}/currentUser.userId}/${file.name}`, 
                };

                calendarEvent.extendedProps.files.push(fileData);

            } else {
                console.log("File already exists in droppedFiles:", file.name);
            }
        });
        saveCalendarEvent();
        sendEventUpdate();
    }

}