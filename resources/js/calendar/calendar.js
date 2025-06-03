// Imports: Core FullCalendar Modules
import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import { Draggable } from '@fullcalendar/interaction';
import { start } from '@hotwired/turbo';
start();
import { state, currentUser } from './state.js';

// Calendar Actions
import { saveCalendarEvent } from './methods/saveCalendarEvent.js';
import { deleteCalendarEvent } from './methods/deleteCalendarEvent.js';
import { removeTeamMemberFromCalendarEvent } from './modal/dropZone/teamMembers/removeTeamMemberFromCalendarEvent.js';
import { handleDateClick } from './methods/handleDateClick.js';
import { handleEventClick } from './methods/handleEventClick.js';

// Calendar Modal
import { closeModal } from './modal/actions/closeModal.js';
import { setupSearchModalInput } from '../navigation/searchModal.js';

// Calendar Toolbar
import { handleToolbarClick } from './modal/toolbar/setupToolbar.js';
import { toggleGroupAudioRoomWithVisualizer } from './modal/toolbar/toggleGroupAudioRoomWithVisualizer.js';
import { toggleGroupVideoCall } from './modal/toolbar/toggleGroupVideoCall.js';
import { toggleGroupScreenShare } from './modal/toolbar/toggleGroupScreenShare.js';

// Calendar DropZone
import { initializeUserListDragHandlers } from './modal/dropZone/initializeUserListDragHandlers.js'; 
import { handleDrop } from './modal/dropZone/handleDrop.js';
import { clickAddUserToDropZone } from './modal/dropZone/teamMembers/clickAddUserToDropZone.js';

// Calendar Messaging
import { openReactionPicker } from './modal/messages/react.js';
import { setUpSendMessageButton } from './modal/messages/setUpSendMessageButton.js';
import { initializePollFeature } from './modal/messages/setUpPollForEvent.js';
import { submitPoll } from './modal/messages/submitPollForEvent.js';
import { submitPollVote } from './modal/messages/submitPollVote.js';

// Global Calendar WebSocket Notifications
import { globalCalendarEventsWS } from './global/globalCalendarEventsWS.js';

// Global Variables: Calendar Actions
window.removeTeamMemberFromCalendarEvent = removeTeamMemberFromCalendarEvent;
window.saveCalendarEvent = saveCalendarEvent;
window.deleteCalendarEvent = deleteCalendarEvent;
window.handleDateClick = handleDateClick;
window.handleEventClick = handleEventClick;

// Global Variables: Calendar Modal
window.closeModal = closeModal;

// Global Variables: Calendar Toolbar
window.handleToolbarClick = handleToolbarClick;

window.toggleGroupAudioRoomWithVisualizer = toggleGroupAudioRoomWithVisualizer;
window.toggleGroupVideoCall = toggleGroupVideoCall;
window.toggleGroupScreenShare = toggleGroupScreenShare;
window.initializePollFeature = initializePollFeature;
window.submitPoll = submitPoll;
window.submitPollVote = submitPollVote;

// Global Variables: Calendar DropZone
window.handleDrop = handleDrop;
window.clickAddUserToDropZone = clickAddUserToDropZone;

window.openReactionPicker = openReactionPicker;

function renderCalendar() {

    const calendarEl = document.getElementById('calendar');
    const sidebarEl = document.getElementById('groups-accordion');

    initializeUserListDragHandlers();

    if (sidebarEl) {
        new Draggable(sidebarEl, {
            itemSelector: '.user-link',
            eventData: function (eventEl) {
                let profilePicture = eventEl.dataset.profilePicture;
                profilePicture = profilePicture.replace(/^\.\/storage\//, '');

                return {
                    id: null,
                    title: `${eventEl.dataset.firstName} ${eventEl.dataset.lastName}`,
                    extendedProps: {
                        eventOwnerId: eventEl.dataset.userId,
                        eventOwnerDetails: {
                            userId: eventEl.dataset.userId,
                            profilePicture: profilePicture,
                            firstName: eventEl.dataset.firstName,
                            lastName: eventEl.dataset.lastName,
                        },
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

    let fetchEvents = currentUser.userId === "admin" 
    ? "/calendar/events/all" 
    : "/calendar/events";

    window.calendar = new Calendar(calendarEl, {
        plugins: [dayGridPlugin, interactionPlugin],
        initialView: 'dayGridMonth',
        timeZone: 'Europe/London', 
        events: fetchEvents,
        editable: true,
        droppable: true,
        eventReceive: async function (info) {
            console.log('[FullCalendar] Event received:', info.event);
            
            document.getElementById('messages').innerHTML = "";
            
            const teamMembersDiv = document.getElementById('team-members');
            teamMembersDiv.innerHTML = '';
            
            const teamMembersLabel = document.createElement('div');
            teamMembersLabel.innerHTML = `<p>Team Members</p>`;
            teamMembersDiv.appendChild(teamMembersLabel);
            
            const filePreview = document.getElementById('file-preview');
            filePreview.innerHTML = '';
            
            const eventOwner = {
                userId: info.event.extendedProps.eventOwnerId,
                profilePicture: info.event.extendedProps.eventOwnerDetails.profilePicture,
                firstName: info.event.extendedProps.eventOwnerDetails.firstName,
                lastName: info.event.extendedProps.eventOwnerDetails.lastName,
            };

            document.getElementById('eventName').value = `${eventOwner.firstName} ${eventOwner.lastName}`;
            
            // Prepare state before saving event
            state.currentEvent = {
                id: null, 
                eventOwnerId: eventOwner.userId,
                eventOwnerDetails: eventOwner,
                title: 'New Event',
                date: info.event.startStr,
                time: null,
                allDay: info.event.allDay,
                isNew: true,
                teamMembers: [eventOwner],
            };


            state.teamMembers = [eventOwner];
            state.existingTeamMembers.push(eventOwner);

            const storedId = await saveCalendarEvent();

            info.event.setProp("id", storedId);

            state.currentEvent.id = storedId;

            console.log(`Event successfully saved with ID ${storedId}`);
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

    globalCalendarEventsWS();
    setupSearchModalInput();

    const sendButton = document.getElementById('send-message-button');
    sendButton.addEventListener('click', setUpSendMessageButton);
}); 
initializePollFeature();