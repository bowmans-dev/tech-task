export function handleDrop(event) {
    event.preventDefault(); // Prevent default browser behavior
    event.stopPropagation(); // Stop the event from bubbling up

    const dropZone = event.currentTarget; // Get the drop zone element
    const eventId = dropZone.dataset.eventId; // Access the eventId

    const rawData = event.dataTransfer.getData('text/plain'); // Dragged user data
    const files = Array.from(event.dataTransfer.files); // Dragged files

    console.log("existingTeamMembers: ", calendarState.existingTeamMembers);

    // Handle dropped users
    if (rawData) {
        try {
            const data = JSON.parse(rawData);
            const { userId, profilePicture, firstName, lastName } = data;

            // Normalize userId comparison to ensure type consistency
            const normalizedUserId = String(userId); // Convert dropped userId to a string

            // Check if the user already exists in the database
            const alreadyExistsInDatabase = calendarState.existingTeamMembers.some(
                member => String(member.userId) === normalizedUserId // Convert database userId to string
            );
            if (alreadyExistsInDatabase) {
                alert(`${firstName} ${lastName} is already in the list.`);
                return; // Exit if the user already exists in the database
            }

            // Check if the user is already in the teamMembers array
            const alreadyExistsInTeam = calendarState.teamMembers.some(
                member => String(member.userId) === normalizedUserId
            );
            if (alreadyExistsInTeam) {
                alert(`${firstName} ${lastName} is already in the list.`);
                return; // Exit if the user is already added
            }

            console.log("User dropped:", data);
            
            const teamMembersDiv = document.getElementById('team-members');

            // Check if the user is already added to the teamMembers array
            if (!calendarState.teamMembers.some(member => member.userId === userId)) {
                // Append the user to the teamMembers array
                calendarState.teamMembers.push({ userId, profilePicture, firstName, lastName });

                // Update the UI
                // const dropZone = document.getElementById('drop-zone');
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
                    <button class="absolute cursor-pointer top-2 right-4 text-gray-500 hover:text-gray-700 z-50" aria-label="Close Modal">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 z-50" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                `;

                teamMembersDiv.appendChild(userDiv);



                // Add a click event listener to the user div buttom to remove team member from dropzone
                const removeButton = userDiv.querySelector('button');
                removeButton.addEventListener('click', () => {
                    // Access the closest userDiv and the userId from its data attribute
                    const userId = userDiv.getAttribute('data-user-id');

                    // Remove the userDiv from the DOM
                    userDiv.remove();

                    // Update the teamMembers array in calendarState
                    calendarState.teamMembers = calendarState.teamMembers.filter(
                        (member) => member.userId != userId
                    );

                });



                console.log("User added to teamMembers:", calendarState.teamMembers);
            } else {
                alert(`${firstName} ${lastName} is already in the list.`);
            }
        } catch (error) {
            console.error("Error parsing user data:", error);
        }
    }

    // Handle dropped files
    if (files.length > 0) {
        const previewContainer = document.getElementById('file-preview');

        files.forEach(file => {
            // Check if the file is already added to the droppedFiles array
            if (!calendarState.droppedFiles.some(f => f.name === file.name && f.size === file.size)) {
                // Append the file to the droppedFiles array
                calendarState.droppedFiles.push(file);

                // Update the UI
                const fileType = file.type;
                const fileName = file.name;
                const fileSize = (file.size / 1024).toFixed(1); // File size in KB

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
                    <p class="text-sm font-medium text-gray-700 max-w-[200px] truncate">${fileName}</p>
                    <p class="text-xs text-gray-500">${fileType || 'Unknown type'} · ${fileSize} KB</p>
                `;

                fileDiv.appendChild(info);
                previewContainer.appendChild(fileDiv);

                console.log("File added to droppedFiles:", { fileName, fileType, fileSize });
            } else {
                console.log("File already exists in droppedFiles:", file.name);
            }
        });
    }

}