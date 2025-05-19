import { renderFiles } from "./dropZone/files/renderFiles";
import { renderTeamMembers } from "./dropZone/teamMembers/renderTeamMembers";

export const showEventModal = (modalSelector = '#event-modal') => {
  const modal = document.querySelector(modalSelector);
  const eventNameInput = document.getElementById('eventName');
  const eventTimeInput = document.getElementById('modal-event-time');
  const eventDateElement = document.getElementById('modal-event-date');

  const dropZone = document.getElementById('drop-zone');
  const filePreviewContainer = document.getElementById('file-preview');
  const teamMembersContainer = document.getElementById('team-members');
  const messagesContainer = document.getElementById('messages');

  const profilePictureElement = document.getElementById('modal-profile-picture');
  const userNameElement = document.getElementById('modal-user-name');

  const hiddenEventIdInput = document.getElementById('event-id');

  const clearContainer = (element) => {
    if (element) {
      element.innerHTML = '';
    }
  };

  const setDropZoneEventId = (eventId) => {
    if (dropZone) {
      dropZone.setAttribute('data-event-id', eventId);
    }
  };

  const updateHiddenFields = (eventId) => {
    if (hiddenEventIdInput) {
      hiddenEventIdInput.value = eventId;
    }
  };

  const updateEventOwnerInfo = (eventOwner) => {
    const profilePicture = eventOwner.profilePicture;
    const fullName = `${eventOwner.firstName} ${eventOwner.lastName}`;
    if (profilePictureElement) profilePictureElement.src = `/storage/${profilePicture}`;
    if (userNameElement) userNameElement.textContent = fullName;
  };

  const scrollIntoView = () => {
    if (modal) modal.scrollIntoView({ behavior: 'smooth' });
  };

  const updateEventDetails = ({ title, date, time }) => {
    if (eventNameInput) eventNameInput.value = title;
    if (eventTimeInput) eventTimeInput.value = time || '';
    if (eventDateElement) eventDateElement.textContent = date;
  };

  // Return the object with modal functions
  return {
    show: ({ id, eventOwnerId, eventOwnerDetails, title, date, time, files, teamMembers }) => {
      setDropZoneEventId(id);
      updateHiddenFields(id);
      updateEventOwnerInfo(eventOwnerDetails);
      updateEventDetails({ id, title, date, time });
      if (modal) {
        modal.dataset.userId = eventOwnerId;
        modal.dataset.date = date;
      }
      clearContainer(messagesContainer);
      renderFiles(filePreviewContainer, files);
      renderTeamMembers(id, teamMembersContainer, teamMembers);

      if (modal) modal.classList.remove('hidden');

      // Hook up an input listener for editing event title if the event exists.
      const event = calendar.getEventById(id);

      if (event) {
        eventNameInput.value = event.title;
        eventNameInput.oninput = () => {
          event.setProp('title', eventNameInput.value);
        };
      } else {
        eventNameInput.value = 'New Event';
      }

      scrollIntoView();
    }
  };
};