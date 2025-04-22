// resources/js/userGroup.js

// Get CSRF token from a meta tag in the document head.
const csrfToken = document
  .querySelector('meta[name="csrf-token"]')
  .getAttribute('content');

/**
 * Creates a new group and updates the UI.
 * @param {Event} event
 * @param {number|string} userId
 */
export function createGroup(event, userId) {
    event.preventDefault();
  
    // Get the input value for the new group name.
    const inputElement = document.getElementById(`new-group-name-${userId}`);
    const newGroupName = inputElement.value.trim();
  
    if (!newGroupName) {
      alert('Group name cannot be empty.');
      return;
    }
  
    fetch('/user-groups/add-group', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json', // Expect JSON response.
        'X-CSRF-TOKEN': csrfToken,
      },
      body: JSON.stringify({ type: 'add-group', name: newGroupName }),
    })
      .then((response) => {
        if (!response.ok) {
          throw new Error(`Failed to create group: ${response.statusText}`);
        }
        return response.json(); // Parse JSON response.
      })
      .then((data) => {
        const newGroupId = data.new_group_id; // Use the returned group ID.
        const turboStreamHtml = data.turbo_stream; // Extract Turbo Stream HTML.
  
        // Render the Turbo Stream (updates the sidebar).
        Turbo.renderStreamMessage(turboStreamHtml);
  
        // Manually update the sub-context menu with the new group.
        const newGroupContainer = document.createElement('div');
        newGroupContainer.className =
          'relative block px-4 py-2 whitespace-nowrap bg-white-100';
        newGroupContainer.setAttribute(
          'onclick',
          `addUserToGroup(event, ${newGroupId}, ${userId})`
        );
        newGroupContainer.innerHTML = `
          ${newGroupName}
          <div class="absolute right-6 top-0 bottom-0 flex items-center remove-user-from-group" style="display: none;">
            <svg xmlns="http://www.w3.org/2000/svg"
                 height="20px"
                 viewBox="0 -960 960 960"
                 width="20px"
                 fill="#5D0E07"
                 onclick="removeUserFromGroup(event, ${newGroupId}, ${userId})"
                 class="cursor-pointer hover:fill-red-600">
              <path d="M288-444h384v-72H288v72Z M480.28-96Q401-96 331-126t-122.5-82.5Q156-261 126-330.96t-30-149.5Q96-560 126-629.5q30-69.5 82.5-122T330.96-834q69.96-30 149.5-30t149.04 30q69.5 30 122 82.5T834-629.28q30 69.73 30 149Q864-401 834-331t-82.5 122.5Q699-156 629.28-126q-69.73 30-149 30Zm-.28-72q130 0 221-91t91-221q0-130-91-221t-221-91q-130 0-221 91t-91 221q0 130 91 221t221 91Zm0-312Z"/>
            </svg>
          </div>
        `;
  
        // Append the new group to the sub-context menu.
        const subContextMenu = inputElement.closest('.sub-context-menu');
        if (subContextMenu) {
          subContextMenu.appendChild(newGroupContainer);
        } else {
          console.error('Error: sub-context menu not found for user ID:', userId);
        }
  
        // Clear the input field.
        inputElement.value = '';
      })
      .catch((error) => {
        console.error('Error in createGroup:', error);
      });
  }
/**
 * Adds a user to a group and updates the UI.
 * @param {Event} event
 * @param {number|string} groupId
 * @param {number|string} userId
 */
export function addUserToGroup(event, groupId, userId) {
  fetch('/user-groups/add-user', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'text/vnd.turbo-stream.html',
      'X-CSRF-TOKEN': csrfToken,
    },
    body: JSON.stringify({ type: 'add-user', user_id: userId, group_id: groupId }),
  })
    .then((response) => {
      if (!response.ok) {
        throw new Error(`Failed to process Turbo Stream: ${response.statusText}`);
      }
      return response.text();
    })
    .then((html) => {
      console.log('Turbo Stream response (addUserToGroup):', html);
      Turbo.renderStreamMessage(html);

      // Manual DOM updates based on the clicked element.
      const groupElement = event.target.closest('.relative');
      if (groupElement) {
        groupElement.classList.add('bg-green-100');
        // Show the "Remove User from Group" button.
        const removeButton = groupElement.querySelector('.remove-user-from-group');
        if (removeButton) {
          removeButton.style.display = 'flex';
        }
      }
    })
    .catch((error) => {
      console.error('Error in addUserToGroup:', error);
    });
}

/**
 * Removes a user from a group and updates the UI.
 * @param {Event} event
 * @param {number|string} groupId
 * @param {number|string} userId
 */
export function removeUserFromGroup(event, groupId, userId) {
  // Prevent click event propagation.
  event.stopPropagation();

  fetch('/user-groups/remove-user', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'text/vnd.turbo-stream.html',
      'X-CSRF-TOKEN': csrfToken,
    },
    body: JSON.stringify({ user_id: userId, group_id: groupId }),
  })
    .then((response) => {
      if (response.ok) {
        const groupElement = event.target.closest('.relative');
        if (groupElement) {
          groupElement.classList.remove('bg-green-100');
          groupElement.classList.add('bg-white-100');
          // Hide the "Remove User from Group" button.
          const removeButton = groupElement.querySelector('.remove-user-from-group');
          if (removeButton) {
            removeButton.style.display = 'none';
          }
        }
        return response.text();
      } else {
        throw new Error(`Failed to process Turbo Stream: ${response.statusText}`);
      }
    })
    .then((html) => {
      console.log('Turbo Stream response (removeUserFromGroup):', html);
      Turbo.renderStreamMessage(html);
    })
    .catch((error) => {
      console.error('Error in removeUserFromGroup:', error);
    });
}