// Imports: Core FullCalendar Modules
import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import { Draggable } from '@fullcalendar/interaction';

// Calendar Actions
import { saveCalendarEvent } from './saveCalendarEvent.js';
import { removeTeamMemberFromCalendarEvent } from '../calendar/removeTeamMemberFromCalendarEvent.js';
import { handleEventClick } from '../calendar/handleEventClick.js';

// Calendar Modal 
import { closeModal } from '../calendar/modal/closeModal.js';
import { openModalToCreateEvent } from '../calendar/modal/openModalToCreateEvent.js';
import { showModalWithEvent } from '../calendar/modal/showModalWithEvent.js';

// Calendar Toolbar
import {toggleToolbarHighlight, toggleSearch, toggleLabel } from '../calendar/modal/toolbar/setupToolbar.js';

// Calendar DropZone
import { initializeUserListDragHandlers } from '../calendar/modal/dropZone/initializeUserListDragHandlers.js';
import { handleDrop } from '../calendar/modal/dropZone/handleDrop.js';
import { clickAddUserToDropZone } from '../calendar/modal/dropZone/clickAddUserToDropZone.js';

// Calendar Messaging
// import { fetchMessagesForEvent } from '../calendar/modal/messages/fetchMessagesForEvent.js';
// import { sendMessageForEvent } from '../calendar/modal/messages/sendMessageForEvent.js';

// Global Variables: Calendar Actions
window.removeTeamMemberFromCalendarEvent = removeTeamMemberFromCalendarEvent;
window.saveCalendarEvent = saveCalendarEvent;
window.handleEventClick = handleEventClick;

// Global Variables: Calendar Modal
window.closeModal = closeModal;
window.openModalToCreateEvent = openModalToCreateEvent;
window.showModalWithEvent = showModalWithEvent;

// Global Variables: Calendar Toolbar
window.toggleToolbarHighlight = toggleToolbarHighlight;
window.toggleSearch = toggleSearch;
window.toggleLabel = toggleLabel;

// Global Variables: Calendar DropZone
window.handleDrop = handleDrop;
window.clickAddUserToDropZone = clickAddUserToDropZone;


function renderCalendar() {

    const calendarEl = document.getElementById('calendar');
    const sidebarEl = document.getElementById('groups-accordion');

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
        events: '/calendar/events/all',
        editable: true,
        droppable: true,
        eventReceive: function (info) {
            console.log('[FullCalendar] Event received:', info.event);

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
            handleEventClick(info)
        },

    });

    calendar.render();

};

document.addEventListener("turbo:load", renderCalendar());
 
// document.addEventListener('DOMContentLoaded', function () {
//     const sendButton = document.getElementById('send-message-button');
//     const textarea = document.getElementById('message-content');
//     const eventIdInput = document.getElementById('event-id');

//     sendButton.addEventListener('click', async (event) => {
//         event.preventDefault(); // Prevent default form submission

//         const content = textarea.value.trim();
//         const eventId = eventIdInput.value;
//         const csrfToken = document.querySelector('input[name="_token"]').value;

//         const result = await sendMessageForEvent(content, eventId, csrfToken);

//         if (result.success) {
//             textarea.value = '';
//         }
//     });
// });

document.addEventListener('turbo:before-cache', function () {
    window.calendar = null;
});