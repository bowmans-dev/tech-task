// Imports: Core FullCalendar Modules
import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import { Draggable } from '@fullcalendar/interaction';
import { start } from '@hotwired/turbo';
start();
import { currentUser } from './state.js';

// Calendar Actions
import { handleDateClick } from './methods/handleDateClick.js';
import { handleEventClick } from './methods/handleEventClick.js';
import { handleEventReceive } from './methods/handleEventReceive.js';
import { deleteCalendarEvent } from './methods/deleteCalendarEvent.js';

// Calendar Modal
import { closeModal } from './modal/actions/closeEventModal.js';
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
import { setUpSendMessageButton } from './modal/messages/setUpSendMessageButton.js';
import { initializePollFeature } from './modal/messages/setUpPollForEvent.js';
import { submitPoll } from './modal/messages/submitPollForEvent.js';
import { submitPollVote } from './modal/messages/submitPollVote.js';

// Global Calendar WebSocket Notifications
import { globalCalendarEventsWS } from './global/globalCalendarEventsWS.js';


window.handleDrop = handleDrop;
window.clickAddUserToDropZone = clickAddUserToDropZone;

const actionMap = {
  deleteCalendarEvent,
  closeModal,
  handleToolbarClick,
  toggleGroupAudioRoomWithVisualizer,
  toggleGroupVideoCall,
  toggleGroupScreenShare,
  setupSearchModalInput,
  initializePollFeature,
  submitPoll,
  submitPollVote,
  setUpSendMessageButton
};

document.addEventListener('click', (e) => {
  const actionEl = e.target.closest('[data-action]');
  if (!actionEl) return;

  const actions = actionEl.dataset.action.split(' ');
  actions.forEach((actionName) => {
    const fn = actionMap[actionName];
    if (typeof fn === 'function') {
      fn(e, actionEl); 
    }
  });
});

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
                        eventOwnerDetails: {
                            eventOwnerId: eventEl.dataset.userId,
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
            handleEventReceive(info);
        },
        eventClick: async function(info) {
            handleEventClick(info);
        },
        dateClick: async function (info) {
            handleDateClick(info);
        }

    });

    calendar.render();
    
    globalCalendarEventsWS();
};

document.removeEventListener("turbo:load", renderCalendar);
document.addEventListener("turbo:load", renderCalendar);