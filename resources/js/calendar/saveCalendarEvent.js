export function saveCalendarEvent() {
    const formData = new FormData();
    const modal = document.getElementById('event-modal');

    const eventName = document.getElementById('eventName').value.trim();
    const id = document.getElementById('eventId').value;

    const userId = modal.dataset.userId;
    const date = modal.dataset.date;
    const time = document.getElementById('modal-event-time').value.trim();

    const allDay = time === '';
    const eventId = allDay ? date : `${date}T${time}`;

    formData.append('event_name', eventName);
    formData.append('user_id', userId);
    formData.append('date', date);
    formData.append('time', time);
    formData.append('allDay', allDay);

    if (id) {
        formData.append('id', id);
    }

    formData.append('team_members', JSON.stringify(calendarState.teamMembers));

    calendarState.droppedFiles.forEach(file => {
        const customFileName = `${eventId}/${userId}/${file.name}`;
        formData.append('files[]', file, customFileName);
    });

    fetch('/calendar/events/save', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) throw new Error('Upload failed');
        return response.json();
    })
    .then(data => {
        alert('Event saved!');
        
        document.getElementById('eventName').value = '';
        document.getElementById('file-preview').innerHTML = '';
        document.getElementById('modal-profile-picture').src = '';
        document.getElementById('modal-user-name').textContent = '';
        document.getElementById('modal-event-date').textContent = '';
        document.getElementById('modal-event-time').value = '';

        calendarState.droppedFiles = [];

        modal.classList.remove('flex');
        modal.classList.add('hidden');

        window.location.reload();
    })
    .catch(error => {
        console.error('Error saving event:', error);
        alert('There was a problem saving the event.');
    });


}