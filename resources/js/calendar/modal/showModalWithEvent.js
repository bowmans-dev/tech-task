export function showModalWithEvent({ id, title, userId, date, time, files, teamMembers }) {
    const modal = document.getElementById('event-modal');

    calendarState.existingTeamMembers = teamMembers;

    console.log(calendarState.existingTeamMembers);

    document.getElementById('eventName').value = title;
    document.getElementById('modal-event-time').value = time || '';
    document.getElementById('modal-event-date').textContent = date;
    document.getElementById('eventId').value = id;

    modal.dataset.userId = userId;
    modal.dataset.date = date;

    const teamMembersDiv = document.getElementById('team-members');
    teamMembersDiv.innerHTML = '';

    const teamMembersLabel = document.createElement('div');
    teamMembersLabel.innerHTML = `<p>Team Members</p>`;

    teamMembersDiv.appendChild(teamMembersLabel);

    const filePreview = document.getElementById('file-preview');
    filePreview.innerHTML = '';

    files.forEach(file => {
        const a = document.createElement('a');
        const filePath = `/storage/${file.file_path}`;
        const fileType = file.file_path.split('.').pop().toLowerCase(); // Extract file extension
        const isImage = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'].includes(fileType); 
    
        a.className = 'p-3 rounded bg-gray-100 flex items-center space-x-3';
        a.href = filePath;
        a.target = '_blank';
        
        a.innerHTML = `
            ${isImage
                ? `<img class="w-12 h-12 object-cover rounded" src="/storage/${file.file_path}" alt="file preview">`
                : `<svg xmlns="http://www.w3.org/2000/svg" height="24px" class="w-12 h-12 text-gray-500" viewBox="0 -960 960 960" width="24px" fill="#6a7282"><path d="M320-240h320v-80H320v80Zm0-160h320v-80H320v80ZM240-80q-33 0-56.5-23.5T160-160v-640q0-33 23.5-56.5T240-880h320l240 240v480q0 33-23.5 56.5T720-80H240Zm280-520v-200H240v640h480v-440H520ZM240-800v200-200 640-640Z"/></svg>`
            }
            <div class="file-text">
                <p class="text-sm font-medium text-gray-700  max-w-[200px] truncate">${file.file_name}</p>
                <p class="text-xs text-gray-500">${fileType}</p>
            </div>
        `;
        
        filePreview.appendChild(a);
    });

    // Render team members in drop-zone
    if (teamMembers && teamMembers.length > 0) {
        teamMembers.forEach(({ userId, profilePicture, firstName, lastName }) => {
            if (profilePicture !== '/storage/default_profile_image.png') {
                profilePicture = `/storage/${profilePicture}`;
            }
            const userDiv = document.createElement('div');
            userDiv.className = 'team-members relative flex items-center mb-2 mt-2 border border-gray-900/25 rounded-full p-1';
            userDiv.setAttribute('data-user-id', userId);
            userDiv.innerHTML = `
                <a href="/users/${userId}" class="cursor-pointer flex items-center">
                    <img src="${profilePicture}" 
                        alt="${firstName} ${lastName}" 
                        class="w-8 h-8 rounded-full object-cover mr-2">
                    <span class="text-sm font-medium text-gray-700">${firstName} ${lastName}</span>
                </a>
                <button
                    onclick="removeTeamMemberFromCalendarEvent(${id}, ${userId})" class="absolute cursor-pointer top-2 right-4 text-gray-500 hover:text-gray-700 z-50" aria-label="Close Modal">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 z-50" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            `;

            teamMembersDiv.appendChild(userDiv);
        });
    } 

    modal.classList.remove('hidden');

    const event = calendar.getEventById(id);

    const eventNameInput = document.getElementById('eventName');
    eventNameInput.value = event.title || 'New Event';
    eventNameInput.oninput = function () {
        event.setProp('title', this.value);
    };
}