export function openModalToCreateEvent(userId = null, profilePicture = null, firstName = null, lastName = null, date = null) {
    document.getElementById('messages').innerHTML = "";
    console.log(firstName, lastName);
    if (userId) {
        if (profilePicture == './storage/') { profilePicture = '/storage/default_profile_image.png' }
        document.getElementById('modal-profile-picture').src = profilePicture;
        document.getElementById('modal-user-name').textContent = `${firstName} ${lastName}`;
        document.getElementById('modal-event-date').textContent = date;
        document.getElementById('event-modal').dataset.date = date;
    }

    document.getElementById('event-modal').classList.remove('hidden');
    document.getElementById('event-modal').classList.add('flex');
    document.getElementById('event-modal').dataset.userId = userId;

    const event = calendar.getEventById(userId);

    const eventNameInput = document.getElementById('eventName');
    eventNameInput.value = 'New Event';
    eventNameInput.oninput = function () {
        event.setProp('title', this.value);
    };
}