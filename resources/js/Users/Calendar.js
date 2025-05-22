// Imports: Core FullCalendar Modules
import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import { Draggable } from '@fullcalendar/interaction';
import { start } from "@hotwired/turbo";
start();

// Calendar State Management
import '../calendar/state';

// Calendar Actions
import { saveCalendarEvent } from '../calendar/saveCalendarEvent.js';
import { deleteCalendarEvent } from '../calendar/deleteCalendarEvent.js';
import { removeTeamMemberFromCalendarEvent } from '../calendar/removeTeamMemberFromCalendarEvent.js';
import { handleDateClick } from '../calendar/handleDateClick.js';
import { handleEventClick } from '../calendar/handleEventClick.js';

// Calendar Modal 
import { closeModal } from '../calendar/modal/closeModal.js';
import { openModalToCreateEvent } from '../calendar/modal/openModalToCreateEvent.js';

// Calendar DropZone
import { initializeUserListDragHandlers } from '../calendar/modal/dropZone/initializeUserListDragHandlers.js';
import { handleDrop } from '../calendar/modal/dropZone/handleDrop.js';
import { clickAddUserToDropZone } from '../calendar/modal/dropZone/clickAddUserToDropZone.js';

// Calendar Messaging
import { setUpSendMessageButton } from '../calendar/modal/messages/setUpSendMessageButton.js';

// Global Calendar WebSocket Notifications
import { globalCalendarEventsWS } from '../calendar/global/globalCalendarEventsWS.js';

// Global Variables: Calendar Actions
window.removeTeamMemberFromCalendarEvent = removeTeamMemberFromCalendarEvent;
window.saveCalendarEvent = saveCalendarEvent;
window.deleteCalendarEvent = deleteCalendarEvent;
window.handleDateClick = handleDateClick;
window.handleEventClick = handleEventClick;

// Global Variables: Calendar Modal
window.closeModal = closeModal;
window.openModalToCreateEvent = openModalToCreateEvent;

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
            handleEventClick(info)
        },
        dateClick: async function (info) {
            handleDateClick(info);
        }
    });

    calendar.render();
 
};

document.removeEventListener("turbo:load", renderCalendar);
document.removeEventListener("turbo:render", renderCalendar);
document.addEventListener("turbo:load", renderCalendar);
document.addEventListener("turbo:render", renderCalendar);

document.addEventListener('DOMContentLoaded', () => {

    renderCalendar();
    globalCalendarEventsWS();
    

    const sendButton = document.getElementById('send-message-button');
    sendButton.addEventListener('click', setUpSendMessageButton);
}); 