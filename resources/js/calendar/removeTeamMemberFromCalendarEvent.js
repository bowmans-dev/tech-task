export function removeTeamMemberFromCalendarEvent(eventId, userId) {

    fetch(`/calendar/event/${eventId}/team-members/${userId}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ eventId, userId }),
    })
        .then(response => {
            if (response.ok) {
                // Remove the userDiv visually
                const userDiv = document.querySelector(`.team-members[data-user-id="${userId}"]`);
                
                if (userDiv) {
                    userDiv.classList.toggle('hidden');
                    userDiv.remove();
                }

                // Remove the user from the teamMembers array
                calendarState.teamMembers = calendarState.teamMembers.filter(member => member.userId !== userId);
                calendarState.existingTeamMembers = calendarState.existingTeamMembers.filter(member => member.userId !== userId);

                console.log(`Team member with ID ${userId} removed from event ${eventId}`);
            } else {
                console.error('Failed to remove team member');
            }
        })
        .catch(error => {
            console.error('Error while removing team member:', error);
        });
}