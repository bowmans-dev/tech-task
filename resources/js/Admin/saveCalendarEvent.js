import { state } from '../calendar/state.js';

export async function saveCalendarEvent() {
    state.currentEvent.id = null;
    const formData = new FormData();
    const modal = document.getElementById('event-modal');

    const eventName = document.getElementById('eventName').value.trim();
    let id = document.getElementById('eventId').value;

    let userId = modal.dataset.userId;
    const date = modal.dataset.date;
    const time = document.getElementById('modal-event-time').value.trim();

    const allDay = time === '';
    const eventId = allDay ? date : `${date}T${time}`;

    const saveButton = document.querySelector('.save-event-button');

    // If the button has a valid data-user-id attribute, override the default
    if (saveButton.hasAttribute('data-user-id') && saveButton.getAttribute('data-user-id')) {
        userId = saveButton.getAttribute('data-user-id');
    }

    if (id == userId) {
        id = null;
    }

    formData.append('event_name', eventName);
    formData.append('user_id', userId);
    formData.append('date', date);
    formData.append('time', time);
    formData.append('allDay', allDay);

    if (id) {
        formData.append('id', id);
    }

    formData.append('team_members', JSON.stringify(state.teamMembers));

    state.droppedFiles.forEach(file => {
        const customFileName = `${eventId}/${userId}/${file.name}`;
        formData.append('files[]', file, customFileName);
    });

    try {
        const response = await fetch('/calendar/events/save', {
            method: 'POST',
            body: formData
        });

        if (!response.ok) throw new Error('Upload failed');

        const data = await response.json();
        console.log("Event saved successfully:", data);

        // If this is a new event, update state.currentEvent.id and return the new ID
        if (!id && data.event && data.event.id) {
            console.log("Event ID saved for future updates:", data.event.id);
            state.currentEvent.id = data.event.id;
            console.log("Updated current event ID:", state.currentEvent.id);
            return data.event.id; // Return the new event ID
        }

        // return id; // Return existing event ID if updating
        document.getElementById('eventName').value = '';
        document.getElementById('file-preview').innerHTML = '';
        document.getElementById('modal-profile-picture').src = '';
        document.getElementById('modal-user-name').textContent = '';
        document.getElementById('modal-event-date').textContent = '';
        document.getElementById('modal-event-time').value = '';

        state.droppedFiles = [];

        modal.classList.remove('flex');
        modal.classList.add('hidden');

        window.location.reload();

    } catch (error) {
        console.error('Error saving event:', error);
        alert('There was a problem saving the event.');
        throw error; 
    }
}