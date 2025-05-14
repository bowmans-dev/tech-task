export function openModalToCreateEvent(userId = null, profilePicture = null, firstName = null, lastName = null, date = null) {
    document.getElementById('messages').innerHTML = "";

    document.getElementById('event-modal').dataset.date = date;
    document.getElementById('event-modal').dataset.userId = userId;

    const profilePictureElement = document.getElementById('modal-profile-picture');

    if (profilePicture == "./storage/") {
        profilePicture = '/storage/default_profile_image.png';
    }

    profilePictureElement.src = profilePicture;
    
    document.getElementById('modal-user-name').textContent = firstName + " " + lastName;

    const event = calendar.getEventById(userId);

    const eventNameInput = document.getElementById('eventName');
    eventNameInput.value = 'New Event';
    eventNameInput.oninput = function () {
        event.setProp('title', this.value);
    };
}