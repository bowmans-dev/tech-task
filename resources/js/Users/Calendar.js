// Imports: Core FullCalendar Modules
import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import { Draggable } from '@fullcalendar/interaction';

// Calendar State Management
import { state } from '../calendar/state';

// Calendar Actions
import { saveCalendarEvent } from '../calendar/saveCalendarEvent.js';
import { removeTeamMemberFromCalendarEvent } from '../calendar/removeTeamMemberFromCalendarEvent.js';

// Calendar Modal 
import { closeModal } from '../calendar/modal/closeModal.js';
import { openModalToCreateEvent } from '../calendar/modal/openModalToCreateEvent.js';
import { showModalWithEvent } from '../calendar/modal/showModalWithEvent.js';

// Calendar DropZone
import { initializeUserListDragHandlers } from '../calendar/modal/dropZone/initializeUserListDragHandlers.js';
import { handleDrop } from '../calendar/modal/dropZone/handleDrop.js';
import { clickAddUserToDropZone } from '../calendar/modal/dropZone/clickAddUserToDropZone.js';

// Calendar Messaging
import { fetchMessagesForEvent } from '../calendar/modal/messages/fetchMessagesForEvent';
import { sendMessageForEvent } from '../calendar/modal/messages/sendMessageForEvent';


// Global Variables: Calendar State
window.calendarState = null;
window.calendarState = state;

// Global Variables: Calendar Actions
window.removeTeamMemberFromCalendarEvent = removeTeamMemberFromCalendarEvent;
window.saveCalendarEvent = saveCalendarEvent;

// Global Variables: Calendar Modal
window.closeModal = closeModal;
window.openModalToCreateEvent = openModalToCreateEvent;
window.showModalWithEvent = showModalWithEvent;

// Global Variables: Calendar DropZone
window.handleDrop = handleDrop;
window.clickAddUserToDropZone = clickAddUserToDropZone;


function renderCalendar() {

    const calendarEl = document.getElementById('calendar');
    const sidebarEl = document.getElementById('groups-accordion');

    if (!calendarEl) {
        console.warn("Calendar script executed, but #calendar element does not exist.");
        return; // Exit early to prevent errors
    }

    initializeUserListDragHandlers();

    if (sidebarEl) {
        new Draggable(sidebarEl, {
            itemSelector: '.user-link',
            eventData: function (eventEl) {
                return {
                    id: eventEl.dataset.userId,
                    title: `${eventEl.dataset.firstName} ${eventEl.dataset.lastName}`,
                    extendedProps: {
                        profilePicture: eventEl.dataset.profilePicture
                    }
                };
            },
            dragStart: function (event) {
                event.dataTransfer.setData('userId', event.target.dataset.userId);
                event.dataTransfer.setData('profilePicture', event.target.dataset.profilePicture);
                event.dataTransfer.setData('firstName', event.target.dataset.firstName);
                event.dataTransfer.setData('lastName', event.target.dataset.lastName);
            }

        });
    }
    
    window.calendar = new Calendar(calendarEl, {
        plugins: [dayGridPlugin, interactionPlugin],
        initialView: 'dayGridMonth',
        timeZone: 'Europe/London', 
        events: '/calendar/events',
        editable: true,
        droppable: true,
        eventReceive: function (info) {
            console.log('[FullCalendar] Event received:', info.event);
            document.getElementById('messages').innerHTML = "";

            const teamMembersDiv = document.getElementById('team-members');
            teamMembersDiv.innerHTML = '';

            const teamMembersLabel = document.createElement('div');
            teamMembersLabel.innerHTML = `<p>Team Members</p>`;

            teamMembersDiv.appendChild(teamMembersLabel);

            const filePreview = document.getElementById('file-preview');
            filePreview.innerHTML = '';
        
            const userId = info.event.id;
            const profilePicture = info.event.extendedProps.profilePicture;
            const [firstName, lastName] = info.event.title.split(' ');
        
            openModalToCreateEvent(userId, profilePicture, firstName, lastName, info.event.startStr);
        },
        eventClick: async function(info) {
            const event = info.event;
            const id = event.id;
            const title = event.title;
            const userId = event.extendedProps.user_id;
            const date = event.startStr.split('T')[0];
            const time = event.extendedProps.time;
            const files = event.extendedProps.files || [];
            const user = event.extendedProps.user || {};
            const teamMembers = event.extendedProps.team_members || [];
        
            const dropZone = document.getElementById('drop-zone');
            dropZone.setAttribute('data-event-id', id);
        
            // Update the hidden input field in the form
            const messageFormEventIdInput = document.getElementById('event-id');
            messageFormEventIdInput.value = id;
        
            // Fetch and render messages
            await fetchMessagesForEvent(id);
        
            document.getElementById('modal-user-name').textContent = user.first_name + " " + user.last_name;
        
            const profilePicture = user.profile_picture ? `/storage/${user.profile_picture}` : '/storage/default_profile_image.png';
            document.getElementById('modal-profile-picture').src = profilePicture;

            document.getElementById('messages').innerHTML = "";
        
            showModalWithEvent({ id, title, userId, date, time, files, teamMembers });

            const targetElement = document.querySelector(`#event-modal`);
            if (targetElement) {
                targetElement.scrollIntoView({
                    behavior: 'smooth',
                });
            }
        },
        dateClick: function (info) {

            calendar.addEvent({
                title: 'New Event',
                start: info.date,
                allDay: info.allDay
            });
        
            const teamMembersDiv = document.getElementById('team-members');
            teamMembersDiv.innerHTML = '';

            const teamMembersLabel = document.createElement('div');
            teamMembersLabel.innerHTML = `<p>Team Members</p>`;

            teamMembersDiv.appendChild(teamMembersLabel);

            const filePreview = document.getElementById('file-preview');
            filePreview.innerHTML = '';

            document.getElementById('modal-profile-picture').style.display = "none";
            document.getElementById('modal-user-name').style.display = "none";

            document.getElementById('messages').innerHTML = "";
        
            openModalToCreateEvent(null, null, null, null, info.event.startStr);

        }

    });

    calendar.render();
 
};

document.removeEventListener("turbo:load", renderCalendar);
document.removeEventListener("turbo:render", renderCalendar);
document.addEventListener("turbo:load", renderCalendar);
document.addEventListener("turbo:render", () => {
    window.calendarState = null;
    renderCalendar();
});

document.addEventListener('DOMContentLoaded', () => {

    renderCalendar();

    const sendButton = document.getElementById('send-message-button');
    const textarea = document.getElementById('message-content');
    const eventIdInput = document.getElementById('event-id');

    sendButton.addEventListener('click', async (event) => {
        event.preventDefault();

        const content = textarea.value.trim();
        const eventId = eventIdInput.value;
        const csrfToken = document.querySelector('input[name="_token"]').value;

        const result = await sendMessageForEvent(content, eventId, csrfToken);

        if (result.success) {
            textarea.value = '';
        }
    });
});