import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import { Draggable } from '@fullcalendar/interaction';

let droppedFiles = [];

window.droppedFiles = droppedFiles;

export function handleDrop(event) {
    event.preventDefault();
    const files = Array.from(event.dataTransfer.files);
    const previewContainer = document.getElementById('file-preview');

    for (const file of files) {
        droppedFiles.push(file);

        const fileType = file.type;
        const fileName = file.name;
        const fileSize = (file.size / 1024).toFixed(1);

        const fileDiv = document.createElement('div');
        fileDiv.className = 'p-3 rounded bg-gray-100 flex items-center space-x-3';

        if (fileType.startsWith('image/')) {
            const img = document.createElement('img');
            img.className = 'w-12 h-12 object-cover rounded';
            img.src = URL.createObjectURL(file);
            fileDiv.appendChild(img);
        } else {
            const icon = document.createElement('div');
            icon.className = 'w-12 h-12 bg-gray-300 rounded flex items-center justify-center text-gray-700 font-bold';
            icon.textContent = fileType.split('/')[1]?.toUpperCase() || 'FILE';
            fileDiv.appendChild(icon);
        }

        const info = document.createElement('div');
        info.innerHTML = `
            <p class="text-sm font-medium text-gray-700">${fileName}</p>
            <p class="text-xs text-gray-500">${fileType || 'Unknown type'} · ${fileSize} KB</p>
        `;

        fileDiv.appendChild(info);
        previewContainer.appendChild(fileDiv);
    }
}

window.handleDrop = handleDrop;


document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('calendar');
    const sidebarEl = document.getElementById('groups-accordion');

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
    

    function openModalToCreateEvent(userId = null, profilePicture = null, firstName = null, lastName = null, date = null) {
        if (userId) {
            if (profilePicture == './storage/') { profilePicture = '/storage/default_profile_image.png' }
            document.getElementById('modal-profile-picture').src = profilePicture;
            document.getElementById('modal-user-name').textContent = `${firstName} ${lastName}`;
            document.getElementById('modal-event-date').textContent = date;
            document.getElementById('event-modal').dataset.date = date;
            console.log("DATE: ", date);
        }
    
        document.getElementById('event-modal').classList.remove('hidden');
        document.getElementById('event-modal').classList.add('flex');
        document.getElementById('event-modal').dataset.userId = userId;
    
        const event = calendar.getEventById(userId);
    
        const eventNameInput = document.getElementById('eventName');
        eventNameInput.value = event.title || 'New Event';
        eventNameInput.oninput = function () {
            event.setProp('title', this.value);
        };
    }

    function showModalWithEvent({ id, title, userId, date, time, files }) {
        const modal = document.getElementById('event-modal');
    
        document.getElementById('eventName').value = title;
        document.getElementById('modal-event-time').value = time || '';
        document.getElementById('modal-event-date').textContent = date;
        document.getElementById('eventId').value = id;
    
        modal.dataset.userId = userId;
        modal.dataset.date = date;
    
        const filePreview = document.getElementById('file-preview');
        filePreview.innerHTML = '';
        files.forEach(file => {
            const a = document.createElement('a');
            const filePath = `/storage/${file.file_path}`;
            const fileType = file.file_path.split('.').pop().toLowerCase(); // Extract file extension
            const isImage = ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(fileType); 
        
            a.className = 'p-3 rounded bg-gray-100 flex items-center space-x-3';
            a.href = filePath;
            a.target = '_blank';
            
            a.innerHTML = `
                ${isImage
                    ? `<img class="w-12 h-12 object-cover rounded" src="/storage/${file.file_path}" alt="file preview">`
                    : `<svg xmlns="http://www.w3.org/2000/svg" height="24px" class="w-12 h-12 text-gray-500" viewBox="0 -960 960 960" width="24px" fill="#6a7282"><path d="M320-240h320v-80H320v80Zm0-160h320v-80H320v80ZM240-80q-33 0-56.5-23.5T160-160v-640q0-33 23.5-56.5T240-880h320l240 240v480q0 33-23.5 56.5T720-80H240Zm280-520v-200H240v640h480v-440H520ZM240-800v200-200 640-640Z"/></svg>`
                }
                <div class="file-text">
                    <p class="text-sm font-medium text-gray-700">${file.file_name}</p>
                    <p class="text-xs text-gray-500">${fileType}</p>
                </div>
            `;
            
            filePreview.appendChild(a);
        });
    
        modal.classList.remove('hidden');

        const event = calendar.getEventById(id);
    
        const eventNameInput = document.getElementById('eventName');
        eventNameInput.value = event.title || 'New Event';
        eventNameInput.oninput = function () {
            event.setProp('title', this.value);
        };
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
        
            const userId = info.event.id;
            const profilePicture = info.event.extendedProps.profilePicture;
            const [firstName, lastName] = info.event.title.split(' ');
        
            openModalToCreateEvent(userId, profilePicture, firstName, lastName, info.event.startStr);
        },
        eventClick: function(info) {
            const event = info.event;
            const id = event.id;
            const title = event.title;
            const userId = event.extendedProps.user_id;
            const date = event.startStr.split('T')[0];
            const time = event.extendedProps.time;
            const files = event.extendedProps.files || [];
            const user = event.extendedProps.user;
        
            document.getElementById('modal-user-name').textContent = user.first_name + " " + user.last_name ;
    
            if (!user.profile_picture) {
                document.getElementById('modal-profile-picture').src = '/storage/default_profile_image.png';
            } else {
                document.getElementById('modal-profile-picture').src = `/storage/${user.profile_picture}`;
            }


            showModalWithEvent({ id, title, userId, date, time, files });
        }

    });

    calendar.render();
 
});